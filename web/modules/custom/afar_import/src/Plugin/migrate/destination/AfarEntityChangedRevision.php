<?php

namespace Drupal\afar_import\Plugin\migrate\destination;

use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

/**
 * Contains custom afar_import logic.
 *
 * Wraps \Drupal\afar_import\Plugin\migrate\destination\EntityChangedRevision
 * to contain some custom logic:
 *
 * - ignore status field changes, this is handled by the UI
 * - log any change
 *
 * @MigrateDestination(
 *   id = "afar_entity_changed_revision",
 *   deriver = "Drupal\afar_import\Plugin\migrate\Derivative\AfarMigrateEntityChangedRevision"
 * )
 */
class AfarEntityChangedRevision extends EntityChangedRevision {

  /** @var \Symfony\Component\Serializer\Serializer */
  protected $serializer;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityStorageInterface $storage, array $bundles, EntityManagerInterface $entity_manager, FieldTypePluginManagerInterface $field_type_manager, Request $request, ControllerResolverInterface $controller_resolver, Serializer $serializer, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage, $bundles, $entity_manager, $field_type_manager, $request, $controller_resolver);

    $this->serializer = $serializer;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    $entity_type = static::getEntityTypeId($plugin_id);
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorage($entity_type),
      array_keys($container->get('entity.manager')->getBundleInfo($entity_type)),
      $container->get('entity.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('controller_resolver'),
      $container->get('serializer'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    foreach ($row->getDestination() as $field_name => $values) {
      // Don't override the status property, this is supposed to be inherited
      // from the previous revision and not overridden by the import.
      if ($field_name === 'status') {
        // @todo Is there a better way to add this logic? Should we maybe set
        //
        continue;
      }

      $field = $entity->$field_name;
      if ($field instanceof TypedDataInterface) {
        $field->setValue($values);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity($row, $old_destination_id_values);

    // The entity is created when we cannot load yet an unchanged entity.
    $created = !($entity->id() && $unchanged_entity = $this->storage->load($entity->id()));

    $entity->set('afar_status', $created ? DC_AFAR_STATUS_NEW : DC_AFAR_STATUS_REVISED);

    $this->updateEntityRevision($entity);
    $result = $this->save($entity, $old_destination_id_values);

    $disabled = \Drupal::state()->get('hal_log.disabled') ?: 0;

    if (! $disabled ) {
        if ($created) {
          $context = [
            '@time' => time(),
            '@type' => 'afar_entity_create',
            '@entity_changed' => $entity->id(),
            '@entity_changed_data' => $this->serializer->serialize($entity, 'hal_json'),
          ];
          $this->loggerFactory->get('hal_log')
            ->info('Afar entity created at @time', $context);
        }
        else {
          $context = [
            '@time' => time(),
            '@type' => 'afar_entity_update',
            '@entity_changed' => $entity->id(),
            '@entity_changed_data' => $this->serializer->serialize($entity, 'hal_json'),
            '@entity_original_data' => $this->serializer->serialize($this->storage->load($entity->id()), 'hal_json'),
          ];
          $this->loggerFactory->get('hal_log')
            ->info('Afar entity updated at @time', $context);
        }
    }

    return $result;
  }

}

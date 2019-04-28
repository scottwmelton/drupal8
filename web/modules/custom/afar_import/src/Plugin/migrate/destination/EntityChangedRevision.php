<?php

/**
 * @file
 * Contains
 *   \Drupal\afar_import\Plugin\migrate\destination\EntityChangedRevision.
 */

namespace Drupal\afar_import\Plugin\migrate\destination;

use Drupal\content_entity_base\Entity\EntityRevisionLogInterface;
use Drupal\content_entity_base\Entity\ExpandedEntityRevisionInterface;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @MigrateDestination(
 *   id = "entity_changed_revision",
 *   deriver = "Drupal\afar_import\Plugin\migrate\Derivative\MigrateEntityChangedRevision"
 * )
 */
class EntityChangedRevision extends EntityContentBase {

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /** @var \Drupal\Core\Controller\ControllerResolverInterface  */
  protected $controllerResolver;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityStorageInterface $storage, array $bundles, EntityManagerInterface $entity_manager, FieldTypePluginManagerInterface $field_type_manager, Request $request, ControllerResolverInterface $controller_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage, $bundles, $entity_manager, $field_type_manager);

    $this->request = $request;
    $this->controllerResolver = $controller_resolver;
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
      $container->get('controller_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    list(, $entity_type_id) = explode(':', $plugin_id);
    return $entity_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity($row, $old_destination_id_values);

    $this->updateEntityRevision($entity);

    return $this->save($entity, $old_destination_id_values);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntity(Row $row, array $old_destination_id_values) {
    $entity = parent::getEntity($row, $old_destination_id_values);

    // @todo Ideally this should be fixed in the parent class.
    // In case you provide a process mapping of id => id, there is a small issue
    // with updated entities. For that particular usecase the entity ID might be
    // still the old one, aka. not the one determined by Drupal inside the
    // entity object.
    // For this enforce the ID in the entity.
    $entity_id = $old_destination_id_values ? reset($old_destination_id_values) : $this->getEntityId($row);
    if ($entity_id) {
      $entity->set($entity->getEntityType()->getKey('id'), $entity_id);
    }

    return $entity;
  }

  /**
   * Determines whether the change should cause a new revision and does so.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   */
  protected function updateEntityRevision(ContentEntityInterface $entity) {
    if (!$entity->isNew()) {
      if (isset($this->configuration['new_revision_callback'])) {
        $callback = $this->configuration['new_revision_callback'];
        $controller = $this->controllerResolver->getControllerFromDefinition($callback);

        if (!is_callable($controller)) {
          throw new \InvalidArgumentException('Invalid callback');
        }
        // For whatever obscure reason we need to clear the cache first.
        // @todo figure out why.
        $this->storage->resetCache();
        $old_entity = clone $this->storage->load($entity->id());
        $result = call_user_func($controller, $old_entity, $entity);

        $entity->setNewRevision($result);
      }
      else {
        // We always create a new revision for this kind of migration.
        $entity->setNewRevision(TRUE);
      }

      if ($entity->isNewRevision()) {
        if ($entity instanceof EntityRevisionLogInterface) {
          // @todo Provide some helpful revision in the future?
          /** @var \Drupal\content_entity_base\Entity\EntityRevisionLogInterface $entity */
          $entity->setRevisionLog('');
        }
        if ($entity instanceof ExpandedEntityRevisionInterface) {
          /** @var \Drupal\content_entity_base\Entity\ExpandedEntityRevisionInterface $entity */
          $entity->setRevisionCreationTime((int) \Drupal::request()->server->get('REQUEST_TIME'));
        }
      }
    }
  }

}

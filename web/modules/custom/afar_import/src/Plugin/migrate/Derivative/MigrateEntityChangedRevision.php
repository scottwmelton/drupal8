<?php

/**
 * @file
 * Contains
 *   \Drupal\afar_import\Plugin\migrate\Derivative\MigrateEntityChangedRevision.
 */

namespace Drupal\afar_import\Plugin\migrate\Derivative;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrateEntityChangedRevision implements ContainerDeriverInterface {

  /**
   * List of derivative definitions.
   *
   * @var array
   */
  protected $derivatives = [];

  /**
   * The entity definitions
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface[]
   */
  protected $entityDefinitions;

  /**
   * Constructs a MigrateEntity object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface[] $entity_definitions
   *   A list of entity definition objects.
   */
  public function __construct(array $entity_definitions) {
    $this->entityDefinitions = $entity_definitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager')->getDefinitions()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinition($derivative_id, $base_plugin_definition) {
    if (!empty($this->derivatives) && !empty($this->derivatives[$derivative_id])) {
      return $this->derivatives[$derivative_id];
    }
    $this->getDerivativeDefinitions($base_plugin_definition);
    return $this->derivatives[$derivative_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->entityDefinitions as $entity_type => $entity_info) {
      if ($entity_info->isSubclassOf('Drupal\Core\Config\Entity\ConfigEntityInterface')) {
        continue;
      }
      $this->derivatives[$entity_type] = array(
        'id' => "entity_changed_revision:$entity_type",
        'class' => 'Drupal\afar_import\Plugin\migrate\destination\EntityChangedRevision',
        'requirements_met' => 1,
        'provider' => $entity_info->getProvider(),
      );
    }
    return $this->derivatives;
  }

}

<?php

/**
 * @file
 * Contains \Drupal\dc\Entity\Access\EntityFieldAccessCheck.
 */

namespace Drupal\dc\Entity\Access;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;

class EntityFieldAccessCheck implements AccessInterface {

  /** @var \Drupal\Core\Entity\EntityManagerInterface */
  protected $entityManager;

  /**
   * Creates a new EntityFieldAccessCheck instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   */
  public function __construct(\Drupal\Core\Entity\EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  public function access(RouteMatchInterface $route_match) {
    $route = $route_match->getRouteObject();

    if (!$route->hasRequirement('_entity_field_access')) {
      throw new \InvalidArgumentException();
    }



    $requirement = $route->getRequirement('_entity_field_access');
    $split = explode('.', $requirement, 3);
    if (count($split) !== 3) {
      throw new \InvalidArgumentException('You need to specify entity type, field name and operation.');
    }
    list($entity_type_id, $field, $operation) = explode('.', $requirement, 3);

    $parameters = $route_match->getParameters();
    if ($parameters->has($entity_type_id)) {
      $entity = $parameters->get($entity_type_id);
      if ($entity instanceof FieldableEntityInterface) {
        return $this->entityManager->getAccessControlHandler($entity_type_id)
          ->fieldAccess($operation, $entity->getFieldDefinition($field), NULL, $entity->{$field}, TRUE);
      }
    }
    throw new \InvalidArgumentException("No upcasted parameter '$entity_type_id'");
  }

}

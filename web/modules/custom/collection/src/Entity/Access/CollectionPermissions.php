<?php

/**
 * @file
 *   Contains \Drupal\collection\Entity\Access\CollectionPermissions.
 */

namespace Drupal\collection\Entity\Access;

use Drupal\content_entity_base\Entity\Access\EntityBasePermissions;
use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Defines a class containing permission callbacks.
 */
class CollectionPermissions extends EntityBasePermissions {

  /**
   * @inheritdoc{}
   */
  public function entityPermissions(ContentEntityTypeInterface $entity_type = NULL) {
    return parent::entityPermissions(\Drupal::entityManager()->getDefinition('collection'));
  }
}

<?php

/**
 * @file
 *   Contains \Drupal\dc\Entity\Access\DCContentPermissions.
 */

namespace Drupal\dc\Entity\Access;

use Drupal\content_entity_base\Entity\Access\EntityBasePermissions;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines a class containing permission callbacks.
 */
class DCContentPermissions extends EntityBasePermissions {

  /**
   * @inheritdoc{}
   */
  public function entityPermissions(ContentEntityTypeInterface $entity_type = NULL) {
    $entity_type = $entity_type ?: \Drupal::entityManager()->getDefinition('dc_content');
    $perms = parent::entityPermissions($entity_type);

    return $perms;
  }

}

<?php

/**
 * @file
 * Contains \Drupal\dc\Entity\Access\DCContentAccessControlHandler.
 */

namespace Drupal\dc\Entity\Access;

use Drupal\content_entity_base\Entity\Access\EntityBaseAccessControlHandler;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a DC content specific entity access checking:
 */
class DCContentAccessControlHandler extends EntityBaseAccessControlHandler {

}

<?php

/**
 * @file
 * Contains \Drupal\dc\DCContentInterface.
 */

namespace Drupal\dc;

use Drupal\content_entity_base\Entity\EntityBaseInterface;
use Drupal\content_entity_base\Entity\ExpandedEntityRevisionInterface;

interface DCContentInterface extends EntityBaseInterface, ExpandedEntityRevisionInterface {

}

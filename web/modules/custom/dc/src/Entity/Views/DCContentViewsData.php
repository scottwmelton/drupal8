<?php

/**
 * @file
 *   Contains Drupal\dc\Entity\Views\DCContentViewsData.
 */

namespace Drupal\dc\Entity\Views;

use Drupal\content_entity_base\Entity\Views\EntityBaseViewsData;

/**
 * Provides the views data for the DC content entity type.
 */
class DCContentViewsData extends EntityBaseViewsData {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    return parent::getViewsData($this->entityManager->getDefinition('dc_content'));
  }
}

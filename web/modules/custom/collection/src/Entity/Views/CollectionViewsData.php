<?php

/**
 * @file
 *   Contains Drupal\collection\Entity\Views\CollectionViewsData.
 */

namespace Drupal\collection\Entity\Views;

use Drupal\content_entity_base\Entity\Views\EntityBaseViewsData;

/**
 * Provides the views data for the Collection entity type.
 */
class CollectionViewsData extends EntityBaseViewsData {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    return parent::getViewsData($this->entityManager->getDefinition('collection'));
  }
}

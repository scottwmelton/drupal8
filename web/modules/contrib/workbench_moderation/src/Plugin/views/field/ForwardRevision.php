<?php

/**
 * @file
 * Definition of Drupal\workbench_moderation\Plugin\views\field\ForwardRevision
 */

namespace Drupal\workbench_moderation\Plugin\views\field;

use Drupal\views\Plugin\views\field\Boolean;
use Drupal\views\ResultRow;

/**
 * Field handler to determine if the entity has a forward revision.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("forward_revision")
 */
class ForwardRevision extends Boolean {

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * @{inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    $entity = $values->_entity;
    $moderation_info = \Drupal::getContainer()->get('workbench_moderation.moderation_information');
    return $moderation_info->hasForwardRevision($entity);
  }

}

<?php

/**
 * @file
 * Contains \Drupal\dc\Controller\DcRevisionController.
 */

namespace Drupal\dc\Controller;

use Drupal\content_entity_base\Entity\Controller\RevisionController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Replaces the string for the reverting of a revision.
 */
class DcRevisionController extends RevisionController {

  /**
   * {@inheritdoc}
   */
  protected function buildRevertRevisionLink(EntityInterface $entity_revision) {
    return [
      'title' => new TranslatableMarkup('Approve'),
      'url' => $entity_revision->urlInfo('revision-revert'),
    ];
  }

}

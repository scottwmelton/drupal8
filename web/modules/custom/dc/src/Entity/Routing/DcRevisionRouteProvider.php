<?php

/**
 * @file
 * Contains \Drupal\dc\Entity\Routing\DcRevisionRouteProvider.
 */

namespace Drupal\dc\Entity\Routing;

use Drupal\content_entity_base\Entity\Routing\RevisionHtmlRouteProvider;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Sets a custom revision overview controller.
 */
class DcRevisionRouteProvider extends RevisionHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function revisionHistoryRoute(EntityTypeInterface $entity_type) {
    $route = parent::revisionHistoryRoute($entity_type);

    $route->setDefault('_controller', '\Drupal\dc\Controller\DcRevisionController::revisionOverviewController');
    return $route;
  }

}

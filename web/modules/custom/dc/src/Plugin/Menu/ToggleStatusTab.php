<?php

/**
 * @file
 * Contains Drupal\dc\Plugin\Menu\ToggleStatusTab.
 */

namespace Drupal\dc\Plugin\Menu;

use Drupal\Core\Menu\LocalTaskDefault;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Toggle status tab for dc_content entity types.
 */
class ToggleStatusTab extends LocalTaskDefault {

  use StringTranslationTrait;

  /**
   * Entity storage handler.
   *
   * @var \Drupal\dc\Entity\DCContent
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    // Override the node here with the latest revision.
    $this->entity = $route_match->getParameter('dc_content');
    return parent::getRouteParameters($route_match);
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request = NULL) {
    return $this->entity->get('status')[0]->value ? $this->t('Unpublish') : $this->t('Publish');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = parent::getCacheTags();
    $tags = array_merge($tags, $this->entity->getCacheTags());
    return $tags;
  }

}

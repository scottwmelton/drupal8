<?php

/**
 * @file
 * Contains \Drupal\rest_fragments\Plugin\views\style\RestFragmentStyleInterface.
 */

namespace Drupal\rest_fragments\Plugin\views\style;

/**
 * Interface for HAL compatible serializer views styles.
 */
interface RestFragmentStyleInterface {

  /**
   * Gets the data array for the objects in this view.
   *
   * @return array
   */
  public function getRenderData();
}

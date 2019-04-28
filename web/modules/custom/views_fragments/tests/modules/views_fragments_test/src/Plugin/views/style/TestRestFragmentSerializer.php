<?php

/**
 * @file
 * Contains
 *   \Drupal\views_fragments_test\Plugin\views\style\TestRestFragmentSerializer.
 */

namespace Drupal\views_fragments_test\Plugin\views\style;

use Drupal\rest_fragments\Plugin\views\style\RestFragmentStyleInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * @ViewsStyle(
 *   id = "test_rest_fragment_serializer",
 *   title = @Translation("test serializer"),
 *   help = @Translation(""),
 *   display_types = {"data"}
 * )
 */
class TestRestFragmentSerializer extends StylePluginBase implements RestFragmentStyleInterface {

  /**
   * {@inheritdoc}
   */
  public function getRenderData() {
    return [
      'test_key' => 'test_data',
    ];
  }

}

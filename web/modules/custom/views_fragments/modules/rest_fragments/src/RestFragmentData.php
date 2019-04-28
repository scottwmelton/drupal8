<?php

/**
 * @file
 * Contains \Drupal\rest_fragments\RestFragmentData.
 */

namespace Drupal\rest_fragments;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

class RestFragmentData implements MarkupInterface, CacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The stored data.
   *
   * @var array
   */
  protected $data;

  /**
   * Creates a new RestFragmentData instance.
   *
   * @param array|string $data
   */
  public function __construct($data) {

    if ( is_array($data) && (count($data)>0) ) {
      $keys = array_keys($data);
      if ($keys[0] == '') {
        $values = array_values($data);
        $data = array();
        foreach ($values as $value) {
          $data[] = $value;
        }
      }
    }

    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->jsonSerialize();
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return json_encode($this->data);
  }

}

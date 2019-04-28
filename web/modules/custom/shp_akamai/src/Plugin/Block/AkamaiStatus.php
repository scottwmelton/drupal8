<?php

namespace Drupal\shp_akamai\Plugin\Block;

use Drupal\shp_akamai\Akamai;
use Drupal\Core\Block\BlockBase;

/**
 * Provides 'AkamaiStatus' block.
 *
 * @Block(
 *  id = "akamai_status",
 *  admin_label = @Translation("Akamai Status"),
 * )
 */
class AkamaiStatus extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $time = time();
    $messages = Akamai::getActiveMessages();

    $build = [];

    if (count($messages)) {
      $output = '<span class="messages__wrapper layout-container">';
      foreach ($messages as $values) {
        $remaining = $values['finished'] - $time;
        $output .= '<div class="alert messages messages--status">' . $values['name'] . ' Akamai cache clearing, will finish within ' . $remaining . ' seconds. </div> ';
      }
      $output .= '</span>';
      $build['akamai_status_akamai_field']['#markup'] = $output;
      $build['#cache']['max-age'] = 10;
    }
    else {
      $build['#cache']['max-age'] = 30;
    }

    return $build;
  }

}

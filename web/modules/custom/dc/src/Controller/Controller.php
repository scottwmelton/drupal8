<?php

/**
 * @file
 * Contains \Drupal\dc\Controller\Controller.
 */

namespace Drupal\dc\Controller;

use Drupal\dc\Entity\DCContent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Controller {

  /**
   * Toggles the status of the DCContent entity.
   *
   * @param \Drupal\dc\Entity\DCContent $dc_content
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function toggleStatus(DCContent $dc_content) {

    $status = $dc_content->status->value;
    $new_status = !$status;
    $dc_content->status->value = $new_status;
    $dc_content->save();

    // To save JSON, call remote function
    if (\Drupal::moduleHandler()->moduleExists('dc_utils') ) {
        _dc_utils_toggle_status( $dc_content) ;
    }

    return new RedirectResponse($dc_content->url('collection'));
  }

}

<?php

/**
 * @file
 * Contains \Drupal\rest_fragments\Plugin\views\display\RestFragment.
 */

namespace Drupal\rest_fragments\Plugin\views\display;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rest\Plugin\views\display\RestExport;

/**
 * The plugin that creates data fragments for views.
 *
 * @todo Given that this doesn't deal with paths it might be more sensible
 *   to not extend RestExport / PathPluginBase.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "rest_fragment",
 *   title = @Translation("Data Fragment"),
 *   help = @Translation("Create a fragment of REST data for REST export resources."),
 *   uses_route = FALSE,
 *   admin = @Translation("Data Fragment"),
 *   returns_response = FALSE
 * )
 */
class RestFragment extends RestExport {

  /**
   * {@inheritdoc}
   */
  public function hasPath() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    unset($options['path'], $options['route_name']);

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    unset($options['path'], $categories['path']);
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    $section = $form_state->get('section');

    if ($section !== 'path') {
      parent::validateOptionsForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    $section = $form_state->get('section');

    if ($section !== 'path') {
      parent::submitOptionsForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function validatePath($path) {
    return [];
  }

}

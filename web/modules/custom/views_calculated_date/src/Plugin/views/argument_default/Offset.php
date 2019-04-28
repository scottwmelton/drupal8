<?php

/**
 * @file
 * Contains \Drupal\views_calculated_date\Plugin\views\argument_default\Offset.
 */

namespace Drupal\views_calculated_date\Plugin\views\argument_default;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;

/**
 * The offset argument default handler.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "offset",
 *   title = @Translation("Offset Date")
 * )
 */
class Offset extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['argument'] = array('default' => '');

    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['argument'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Offset Date'),
      '#default_value' => $this->options['argument'],
      '#description' => $this->t('An offset from the current time such as "!example1" or "!example2"',
        ['!example1' => '+1 day', '!example2' => '-2 hours -30 minutes']),
    );
  }

  /**
   * Return the default argument.
   */
  public function getArgument() {
    return $this->options['argument'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function access() {
    // Don't show this plugin unless the.
    return ($this->argument->canUseOffsetDate ?: FALSE);
  }


}

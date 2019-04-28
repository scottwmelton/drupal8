<?php

namespace Drupal\shp_akamai\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'AkamaiStatus' block.
 *
 * @Block(
 *   id = "akamai_make_call",
 *   admin_label = @Translation("Akamai Purge Cache"),
 * )
 */
class AkamaiMakeCall extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'server' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['server'] = [
      '#type' => 'radios',
      '#options' => [
        'hal' => $this->t('HAL'),
        'sbn' => $this->t('SBN'),
      ],
      '#title' => $this->t('Server'),
      '#description' => $this->t('Choose which server cache purge buttons to show. If none are selected, all will be shown.'),
      '#default_value' => $this->configuration['server'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['server'] = $form_state->getValue('server');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $server = $this->configuration['server'];
    // @codingStandardsIgnoreLine
    return \Drupal::formBuilder()->getForm('Drupal\shp_akamai\Form\MakeCall', $server);
  }

}

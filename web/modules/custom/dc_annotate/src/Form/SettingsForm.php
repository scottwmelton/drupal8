<?php

/**
 * @file
 * Contains \Drupal\dc_annotate\Form\SettingsForm.
 */

namespace Drupal\dc_annotate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a settings form for dc_annotate specific settings.
 *
 * This includes:
 *
 *  - afar email address
 *  - afar email subject
 *  - afar email body
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dc_annotate.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dc_annotate_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $settings = $this->config('dc_annotate.settings');

    // We don't use '.' for the keys, due to this form API bug:
    // https://www.drupal.org/node/2611640.
    $form['afar_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Afar address'),
      '#default_value' => $settings->get('afar.address'),
    ];
    $form['afar_from'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From address'),
      '#default_value' => $settings->get('afar.from'),
    ];
    $form['afar_cc'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CC address'),
      '#default_value' => $settings->get('afar.cc'),
    ];
    $form['afar_bcc'] = [
      '#type' => 'textfield',
      '#title' => $this->t('bcc address'),
      '#default_value' => $settings->get('afar.bcc'),
    ];
    $form['afar_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mail subject'),
      '#default_value' => $settings->get('afar.subject'),
    ];
    $form['afar_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail body'),
      '#default_value' => $settings->get('afar.body'),
    ];

    $form['token_help'] = [
      '#theme' => 'token_tree',
      '#token_types' => ['comment', 'dc_content'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('dc_annotate.settings')
      ->set('afar.address', $form_state->getValue('afar_address'))
      ->set('afar.from', $form_state->getValue('afar_from'))
      ->set('afar.cc', $form_state->getValue('afar_cc'))
      ->set('afar.bcc', $form_state->getValue('afar_bcc'))
      ->set('afar.subject', $form_state->getValue('afar_subject'))
      ->set('afar.body', $form_state->getValue('afar_body'))
      ->save();
  }

}

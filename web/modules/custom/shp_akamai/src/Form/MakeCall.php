<?php

namespace Drupal\shp_akamai\Form;

use Drupal\shp_akamai\Akamai;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;

/**
 * Class MakeCall.
 *
 * @package Drupal\shp_akamai\Form
 */
class MakeCall extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'shp_akamai.makecall',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'make_call';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $server = NULL) {
    $route = \Drupal::routeMatch()->getRouteName();
    $timestamp = time();
    $busy_cp_codes = [];

    $akamai_config = Akamai::getCpCodes($server);

    $messages = Akamai::getActiveMessages();
    $busy_cp_codes = array_keys($messages);

    $trigger = $form_state->getTriggeringElement();

    if ($trigger && $trigger['#default_value']) {
      $trigger['#disabled'] = TRUE;
      $trigger['#default_value'] = FALSE;
      $trigger['#value'] = $this->t('Processing: ETA 240 Seconds');
      return $trigger;
    }
    else {

    }

    foreach ($akamai_config as $key => $value) {

      if (in_array($key, $busy_cp_codes)) {
        $button_text = $this->t('In Progress @value', ['@value' => $value]);
        $busy = $messages[$key];

        $fin = $busy['finished'];

        if ($fin) {
          $seconds = $fin - $timestamp;
          $button_text .= ': ' . $seconds . " seconds remaining";
        }

        $form['button_' . $key] = [
          '#type' => 'button',
          '#value' => $button_text,
          '#callback' => [$this, 'doNothing'],
          '#default_value' => FALSE,
          '#disabled' => TRUE,
        ];

      }
      else {
        $form['button_' . $key] = [
          '#type' => 'button',
          '#value' => $value,
          '#callback' => [$this, 'buttonFun'],
          '#default_value' => $key,
          '#prefix' => '<span id="wrap_' . $key . '" >',
          '#suffix' => '</span>',
          '#limit_validation_errors' => TRUE,
          '#ajax' => [
            'wrapper' => 'wrap_' . $key,
            'progress' => [
              'type' => 'throbber',
              'message' => $this->t('..starting...'),
            ],
          ],
        ];
        // If on the block form, then use a callback.
        if ($route != 'shp_akamai.make_call') {
          $form['button_' . $key]['#ajax']['callback'] = '::buttonFun';
        }
      } // end if else
    } // end foreach
    $built = parent::buildForm($form, $form_state);

    $built['#title'] = 'Clear Akamai Cache';
    $built['actions'] = [];

    return $built;
  }

  /**
   * Callback if no action to do.
   */
  public function doNothing(array &$form, FormStateInterface $form_state) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // If on the make_call page, then trigger here.
    $route = \Drupal::routeMatch()->getRouteName();
    if ($route == 'shp_akamai.make_call') {
      return $this->buttonFun($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Button AJAX.
   */
  public function buttonFun(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();

    $cp_code = $trigger['#default_value'];
    if (!$cp_code) {
      return;
    }

    $timestamp = time();
    $until = time() + 240;
    $message = [
      $cp_code => [
        'finished' => $until,
        'cp_code' => $cp_code,
        'name' => $trigger['#value'],
        'return' => [],
        'uri' => '',
      ],
    ];
    Akamai::putMessage($message, $timestamp);
    Akamai::postREST($cp_code, $trigger['#value'], $timestamp);

    // If in the block form, send the trigger element back.
    $route = \Drupal::routeMatch()->getRouteName();
    if ($route != 'shp_akamai.make_call') {
      $trigger['#value'] = $this->t('Cache purge in Progress...');
      // @todo Figure why the #disabled property isn't working.
      $trigger['#disabled'] = TRUE;
      return $trigger;
    }

    $id = '#wrap_' . $cp_code;
    $field = $form['button_' . $cp_code];
    $field['#value'] = 'Starting';
    $changed = new ChangedCommand($id, $field);
    $ajax_response = new AjaxResponse();
    $ajax_response->addCommand($changed);
    return $ajax_response;
  }

}

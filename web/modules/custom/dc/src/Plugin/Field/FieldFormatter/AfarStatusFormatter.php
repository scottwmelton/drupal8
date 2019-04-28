<?php

/**
 * @file
 * Contains \Drupal\dc\Plugin\Field\FieldFormatter\AfarStatusFormatter.
 */

namespace Drupal\dc\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'afar_status' formatter.
 *
 * @FieldFormatter(
 *   id = "afar_status",
 *   label = @Translation("Afar import status"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class AfarStatusFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'status_text_new' => 'new',
      'status_text_revised' => 'revised',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['status_text'] = [
      '#title' => $this->t('Status Text'),
      'status_text_new' => [
        '#title' => $this->t('New'),
        '#type' => 'textfield',
        '#default_value' => $this->getSetting('status_text_new'),
      ],
      'status_text_revised' => [
        '#title' => $this->t('New'),
        '#type' => 'textfield',
        '#default_value' => $this->getSetting('status_text_revised'),
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Status new: %text',
      ['%text' => $this->getSetting('status_text_new')]);
    $summary[] = $this->t('Status revised: %text',
      ['%text' => $this->getSetting('status_text_revised')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'dc_afar_status',
        '#value' => $item->value,
        '#langcode' => $item->getLangcode(),
      ];

      switch ($item->value) {

        case DC_AFAR_STATUS_NEW:
          $elements[$delta]['#text'] = $this->getSetting('status_text_new');
          $elements[$delta]['#class'] = 'dc-afar-status-new';
          break;

        case DC_AFAR_STATUS_REVISED:
          $elements[$delta]['#text'] = $this->getSetting('status_text_revised');
          $elements[$delta]['#class'] = 'dc-afar-status-revised';
          break;

        default:
          $elements[$delta]['#text'] = '';
          $elements[$delta]['#class'] = 'dc-afar-status-none';
          break;

      }

    }

    return $elements;
  }

}

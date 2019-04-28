<?php

/**
 * @file
 * Contains \Drupal\afar_tweaks\Plugin\Field\FieldFormatter\TextAndSummary.
 */

namespace Drupal\afar_tweaks\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldFormatter(
 *   id = "afar_text_and_summary",
 *   label = @Translation("Text and summary"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary"
 *   },
 *   quickedit = {
 *     "editor" = "form"
 *   }
 * )
 */
class TextAndSummary extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['summary_label'] = 'Summary label';
    $settings['value_label'] = 'Value label';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['summary_label'] = [
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('summary_label'),
      '#title' => $this->t('Summary label'),
    ];

    $form['value_label'] = [
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('value_label'),
      '#title' => $this->t('Value label'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = $this->t('Summary label: @label', ['@label' => $this->getSetting('summary_label')]);
    $summary[] = $this->t('Value label: @label', ['@label' => $this->getSetting('value_label')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $elements[$delta]['value']['text'] = $elements[$delta]['summary']['text'] = [
        '#type' => 'processed_text',
        '#text' => NULL,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];
      $elements[$delta]['value']['prefix'] = $elements[$delta]['summary']['prefix'] = [
        '#weight' => -10,
        '#type' => 'inline_template',
        '#template' => '<div class="field__label">{{ label }}</div>',
      ];

      if ($item->summary) {
        $elements[$delta]['summary']['text']['#text'] = $item->summary;
        $elements[$delta]['summary']['prefix']['#context']['label'] = $this->getSetting('summary_label');
      }
      if ($item->value) {
        $elements[$delta]['value']['text']['#text'] = $item->value;
        $elements[$delta]['value']['prefix']['#context']['label'] = $this->getSetting('value_label');
      }
    }

    return $elements;
  }

}

<?php

/**
 * @file
 *   Contains \Drupal\key_value_field\Plugin\Field\KeyValueWidgetTrait.
 */

namespace Drupal\key_value_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Common traits for key value field widgets which inherit from different widgets.
 */
trait KeyValueWidgetTrait {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'key_size' => 60,
      'key_placeholder' => '',
      'description_enabled' => TRUE,
      'description_placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Get the default textfield form.
    $parent_form = parent::settingsForm($form, $form_state);
    // Change the title for the Rows field.
    $parent_form['rows']['#title'] = t('Value Rows');
    // Change the title for the Placeholder field.
    $parent_form['placeholder']['#title'] = t('Value Placeholder');
    // Get the field machine_id.
    $field_machine = $this->fieldDefinition->getName();
    // Add an element for the size of the key field.
    $element['key_size'] = [
      '#type' => 'number',
      '#title' => t('Size of key textfield'),
      '#default_value' => $this->getSetting('key_size'),
      '#required' => TRUE,
      '#weight' => -2,
      '#min' => 1,
    ];
    // Add a placeholder field for the key text field.
    $element['key_placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Key Placeholder'),
      '#default_value' => $this->getSetting('key_placeholder'),
      '#description' => t('Text that will be shown inside the "Key" field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
      '#weight' => -1,
    ];
    // Let the description field be hidden.
    $element['description_enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Description'),
      '#default_value' => $this->getSetting('description_enabled'),
      '#description' => t('Enable the description field (Generally used for administrative purposes).'),
      '#weight' => 2,
    ];
    // Add a placeholder for teh description field.
    $element['description_placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Description Placeholder'),
      '#default_value' => $this->getSetting('description_placeholder'),
      '#description' => t('Text that will be shown inside the "Description" field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
      '#weight' => 3,
      '#states' => [
        'visible' => [
          ':input[name="fields['.$field_machine.'][settings_edit_form][settings][description_enabled]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    return $element + $parent_form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    // Add a summary for the key field.
    $summary[] = t('Key textfield size: !size', ['!size' => $this->getSetting('key_size')]);
    if (($placeholder = $this->getSetting('key_placeholder')) && !empty($placeholder)) {
      $summary[] = t('Key Placeholder: "@placeholder"', ['@placeholder' => $placeholder]);
    }
    // Add a summary for the value placeholder.
    if (($placeholder = $this->getSetting('placeholder')) && !empty($placeholder)) {
      $summary[] = t('Value Placeholder: "@placeholder"', ['@placeholder' => $placeholder]);
    }

    // Add a summary for the description if it is enabled.
    if ($this->getSetting('description_enabled') && ($placeholder = $this->getSetting('description_placeholder')) && !empty($placeholder)) {
      $summary[] = t('Description: Enabled', ['@placeholder' => $placeholder]);
      $summary[] = t('- Placeholder: @placeholder', ['@placeholder' => $placeholder]);
    }

    return $summary + parent::settingsSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Get the textfield form element.
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Check for an empty format on formattable items.
    if (array_key_exists('#format', $element) && empty($element['#format'])) {
      // Get the default format.
      $default_format = $this->getFieldSetting('default_format');
      // Get the formats available to the current user.
      $available_formats = filter_formats();
      // set the format to the default if empty
      if (!empty($default_format) && array_key_exists($default_format, $available_formats)) {
        $element['#format'] = $default_format;
      }
    }

    // Display the title for key_value fields.
    if (array_key_exists('#title_display', $element)) {
      unset($element['#title_display']);
      $element['#title'] = $this->t('Value');
    }

    // Grab settings for all
    $key_size                 = $this->getSetting('key_size');
    $key_placeholder          = $this->getSetting('key_placeholder');
    $description_enabled      = $this->getSetting('description_enabled');
    $description_placeholder  = $this->getSetting('description_placeholder');

    // Create a description field if it is enabled.
    $description = !$description_enabled ? [] : [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->description : NULL,
      '#placeholder' => $description_placeholder,
      '#maxlength' => 255,
      '#weight' => 2,
      '#attributes' => ['class' => ['js-text-full', 'text-full', 'key-value-widget-description']],
    ];

    // Return computed form.
    return [
      // Add the key field.
      'key' => [
        '#title' => t('Key'),
        '#type' => 'textfield',
        '#default_value' => isset($items[$delta]->key) ? $items[$delta]->key : NULL,
        '#size' => $key_size,
        '#placeholder' => $key_placeholder,
        '#maxlength' => $this->getFieldSetting('key_max_length'),
        '#attributes' => ['class' => ['js-text-full', 'text-full', 'key-value-widget-key']],
        '#weight' => -1,
        '#states' => [
          'required' => [
            ':input[name="'.$this->fieldDefinition->getName().'['.$delta.'][value]"]' => ['empty' => FALSE],
          ],
        ],
      ],
      // Add the description field
      'description' => $description,
      // Add a class to the widget form.
      '#attributes' => ['class' => ['key-value-widget', 'js-text-full', 'text-full']],
      '#title' => t('Value'),
      '#title_display' => 'before',
    ]
    // Add the textarea form.
    + $element;
  }
}

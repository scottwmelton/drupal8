<?php

/**
 * @file
 * Contains
 *   \Drupal\afar_tweaks\Plugin\Field\FieldWidget\TextareaWithExistingSummaryWidget.
 */

namespace Drupal\afar_tweaks\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget;

/**
 * Plugin implementation of the 'text_textarea_with_existing_summary' widget.
 *
 * In contrast to 'text_textarea_with_summary' this always shows a summary area.
 *
 * @FieldWidget(
 *   id = "text_textarea_with_existing_summary",
 *   label = @Translation("Text area with a always existing summary"),
 *   field_types = {
 *     "text_with_summary"
 *   }
 * )
 */
class TextareaWithExistingSummaryWidget extends TextareaWithSummaryWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Remove the text.js javascript which automatically hides the summary
    // field.
    $element['summary']['#attached']['library'] = [];
    return $element;
  }

}

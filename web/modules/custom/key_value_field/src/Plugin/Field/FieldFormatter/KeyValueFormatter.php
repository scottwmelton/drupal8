<?php

/**
 * @file
 *   Contains \Drupal\key_value_field\Plugin\Field\KeyValueFormatter.
 */

namespace Drupal\key_value_field\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\text\Plugin\Field\FieldFormatter\TextDefaultFormatter;

/**
 * Plugin implementation of the 'key_value' formatter.
 *
 * @FieldFormatter(
 *   id = "key_value",
 *   label = @Translation("Key Value"),
 *   field_types = {
 *     "key_value",
 *     "key_value_long",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class KeyValueFormatter extends TextDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Get the value elements from the TextDefaultFormatter class.
    $value_elements = parent::viewElements($items, $langcode);
    // Buffer the return value.
    $elements = [];
    // Loop through all items.
    foreach ($items as $delta => $item) {
      // Add the key element to the render array.
      $elements[$delta]['key'] = [
        '#markup' => nl2br(SafeMarkup::checkPlain($item->key . ' : ')),
      ];
      // Add the value to the render array.
      $elements[$delta]['value'] = $value_elements[$delta];
    }
    return $elements;
  }
}

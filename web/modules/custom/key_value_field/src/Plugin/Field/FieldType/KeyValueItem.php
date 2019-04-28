<?php

/**
 * @file
 * Contains Drupal\key_value_field\Plugin\Field\FieldType\KeyValueItem.
 */

namespace Drupal\key_value_field\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;

/**
 * Plugin implementation of the 'key_value' field type.
 *
 * @FieldType(
 *   id = "key_value",
 *   label = @Translation("Key / Value (plain)"),
 *   description = @Translation("This field stores key value pairs."),
 *   category = @Translation("Key / Value"),
 *   default_widget = "key_value_textfield",
 *   default_formatter = "key_value"
 * )
 */
class KeyValueItem extends StringItem {

  /**
   * Add overrides from the common trait.
   */
  use KeyValueFieldTypeTrait;

}

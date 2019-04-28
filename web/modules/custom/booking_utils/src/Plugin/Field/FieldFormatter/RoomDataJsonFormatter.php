<?php

/**
 * @file
 * Contains \Drupal\booking_utils\Plugin\Field\FieldFormatter\RoomDataJsonFormatter.
 */

namespace Drupal\booking_utils\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'basic_string' formatter.
 *
 * @FieldFormatter(
 *   id = "basic_string_class_id",
 *   label = @Translation("Room JSON text"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class RoomDataJsonFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = array();
    $settings = $this->getSettings();

    $summary[] = t('Displays the json css.');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

      $room_fields = array('roomShape', 'category', 'roomNumber', 'roomWidth', 'roomHeight', 'roomLeft', 'roomTop', 'roomTour', 'roomVideo');

      $json_text = $items[0]->getValue()['value'];

      $room_entries = json_decode($json_text, TRUE);

      $rooms = array();

      foreach ($room_entries as $room_entry) {
        $roomNumber = $room_entry['roomNumber'];
        $rooms[$roomNumber] = $room_entry ;
      }

      foreach ($rooms as $num => $room) {
        $id = 'rm ' . $num ;
      }

      $element = array();
      $element[0] = array(
          '#theme'  => 'deck_image_rooms',
          '#value' => array( 'rooms' => $rooms ),
       );

      return $element;
  }

}


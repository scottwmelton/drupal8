<?php

/**
 * @file
 * Contains \Drupal\rest_fragments\Plugin\views\row\RestFragmentsDataFieldRow.
 */

namespace Drupal\rest_fragments\Plugin\views\row;

use Drupal\Component\Utility\Html;
use Drupal\rest\Plugin\views\row\DataFieldRow;
use Drupal\views\ResultRow;

/**
 * Provides cacheable metadata fixes for DataFieldRow.
 */
class RestFragmentsDataFieldRow extends DataFieldRow {

  /**
   * Executes the actual rendering.
   *
   * It tries to take into account row level caching.
   *
   * @param \Drupal\views\ResultRow $row
   *   THe result row.
   *
   * @return array
   */
  protected function doRender(ResultRow $row) {
    $output = array();

    foreach ($this->view->field as $id => $field) {
      // If this is not unknown and the raw output option has been set, just get
      // the raw value.
      if (($field->field_alias != 'unknown') && !empty($this->rawOutputOptions[$id])) {
        $value = $field->sanitizeValue($field->getValue($row), 'xss_admin');
      }
      // Otherwise, pass this through the field advancedRender() method.
      else {
        $value = $this->view->style_plugin->getField($row->index, $id);

        // Render caching converts RestDataFragment objects to its encoded json.
        // In order to get the data back, try to decode the json array.

        // First reset the last json error.
        json_decode(1);

        $data = @json_decode($value, TRUE);
        if (json_last_error() === 0) {
          if (is_array($data)) {
            array_walk_recursive($data, function (&$val) {
              $val = Html::decodeEntities((string) $val);
            });
          }
          $value = $data;
        }
      }

      $output[$this->getFieldKeyAlias($id)] = $value;
    }

    return $output;
  }

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::render().
   */
  public function render($row) {
    // Override the parent rendering in order to use the row level field
    // caching.
    // @todo Provide a upstream patch/issue.
    $output = $this->doRender($row);

    return $output;
  }

}

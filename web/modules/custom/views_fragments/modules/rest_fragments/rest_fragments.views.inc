<?php

/**
 * @file
 * Provide views data that isn't tied to any other module.
 */

use Drupal\rest_fragments\Plugin\views\row\RestFragmentsDataFieldRow;

/**
 * Implements hook_views_data().
 */
function rest_fragments_views_data() {
  $data['rest_fragments']['table']['group'] = t('Global');
  // #global is a special flag which allows a table to appear all the time.
  $data['rest_fragments']['table']['join'] = ['#global' => []];

  $data['rest_fragments']['data_fragment'] = [
    'title' => t('Data Fragment'),
    'help' => t('Include a rest fragment in your results.'),
    'field' => [
      'id' => 'data_fragment',
    ],
  ];

  return $data;
}

/**
 * Implements hook_views_plugins_row_alter().
 */
function rest_fragments_views_plugins_row_alter(&$plugins) {
  // Replace the data_field plugin with our own implementation.
  $plugins['data_field']['class'] = RestFragmentsDataFieldRow::class;
}

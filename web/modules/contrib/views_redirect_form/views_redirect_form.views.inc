<?php

/**
 * Implements hook_views_data().
 */
function views_redirect_form_views_data() {
  $data['views']['views_redirect_form__compare_from'] = [
    'title' => t('Redirect compare from'),
    'field' => [
      'id' => 'views_redirect_form__compare_from',
    ],
  ];

  $data['views']['views_redirect_form__compare_to'] = [
    'title' => t('Redirect compare to'),
    'field' => [
      'id' => 'views_redirect_form__compare_to',
    ],
  ];

  return $data;
}

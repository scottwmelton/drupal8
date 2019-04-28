<?php

namespace Drupal\shp_tilepage\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'remote_image_uri_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "remote_image_uri_formatter",
 *   label = @Translation("Remote Image Uri Formatter"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class RemoteImageUriFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'remote_uri_base' => '//www.hollandamerica.com',
      'extra_classes' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public  function getSettings() {
      $sets = parent::getSettings();
      return $sets;
 
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $element = [];
    $element['remote_uri_base'] = [
      '#type' => 'textfield',
      '#size' => 30,
      '#max_length' => 100,
      '#title' => t('Return URI Base'),
      '#descriptions' => t('Required. Start with protocol. No trailing slash.'),
      '#default_value' => $settings['remote_uri_base'],
      '#weight' => 1,
    ];
    $element['extra_classes'] = [
      '#type' => 'textfield',
      '#size' => 20,
      '#max_length' => 100,
      '#title' => t('Extra CSS Classes'),
      '#descriptions' => t('Optional. Class names to add to attributes. Separate by spaces.'),
      '#default_value' => $settings['extra_classes'],
      '#weight' => 2,
    ];

    return $element + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary = [];
    // Implement settings summary.
    $summary['title'] = t('Remote URI: ') . $settings['remote_uri_base'];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
     $remote_uri = $item->value ;
     $settings = $this->getSettings();

     if (! strpos('//', ' ' . $remote_uri) ) {
        $remote_uri = $settings['remote_uri_base'] . $remote_uri ;
     }

     $image = [
        '#theme'=> 'image',
        '#uri'=> $remote_uri,
        '#alt' => ' alt not shown in this version ',
        '#title' => ' title not shown in this version ',
     ]; 

     if ($settings['extra_classes']) $image['#attributes'] = ['class' => $settings['extra_classes']] ;

     return $image;

  }

}

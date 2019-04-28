<?php

/**
 * @file
 * Contains \Drupal\afar_guzzle_helper\Form\SettingsForm.
 */

namespace Drupal\afar_guzzle_helper\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dc\Entity\DCContent;
use Drupal\migrate\Entity\Migration;

class SettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'afar_guzzle_helper_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['revisions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Force new revision'),
    ];
    $form['revisions']['place'] = [
      '#title' => $this->t('Place'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'dc_content',
      '#selection_settings' => [
        'target_bundles' => ['place'],
      ],
      '#default_value' => ($id = \Drupal::keyValue('afar_guzzle_helper_revisions')->get('place')) ? DCContent::load($id) : NULL,
      '#description' => $this->t('Entity ID of the place'),
    ];
    $form['revisions']['region'] = [
      '#title' => $this->t('Region'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'dc_content',
      '#selection_settings' => [
        'target_bundles' => ['region'],
      ],
      '#default_value' => ($id = \Drupal::keyValue('afar_guzzle_helper_revisions')->get('region')) ? DCContent::load($id) : NULL,
      '#description' => $this->t('Entity ID of the region'),
    ];
    $form['revisions']['port'] = [
      '#title' => $this->t('Port'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'dc_content',
      '#selection_settings' => [
        'target_bundles' => ['port'],
      ],
      '#default_value' => ($id = \Drupal::keyValue('afar_guzzle_helper_revisions')->get('port')) ? DCContent::load($id) : NULL,
      '#description' => $this->t('Entity ID of the port'),
    ];
    $form['revisions']['destination'] = [
      '#title' => $this->t('Destination'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'dc_content',
      '#selection_settings' => [
        'target_bundles' => ['destination'],
      ],
      '#default_value' => ($id = \Drupal::keyValue('afar_guzzle_helper_revisions')->get('destination')) ? DCContent::load($id) : NULL,
      '#description' => $this->t('Entity ID of the destination'),
    ];

    $form['force_resave'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Force resaves'),
    ];
    $form['force_resave']['resave_dc_content'] = [
      '#title' => $this->t('DC Content'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'dc_content',
      '#selection_settings' => [
        'target_bundles' => ['port', 'destination', 'place', 'region'],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach (['place', 'region', 'port', 'destination'] as $type) {
      \Drupal::keyValue('afar_guzzle_helper_revisions')->set($type, $form_state->getValue($type));
    }

    if ($resave_id = $form_state->getValue('resave_dc_content')) {
      $resave_entity = DCContent::load($resave_id);
      $migration = Migration::load('afar_dc_' . $resave_entity->bundle());

      /** @var \Drupal\migrate\Plugin\MigratePluginManager $map_manager */
      $map_manager = \Drupal::service('plugin.manager.migrate.id_map');
      /** @var \Drupal\migrate\Plugin\MigrateIdMapInterface $map */

      $map = $map_manager->createInstance('sql', [], $migration);

      $source_id = $map->lookupSourceID(['id' => $resave_id]);
      $map->setUpdate($source_id);
    }
  }


}

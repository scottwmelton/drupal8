<?php

/**
 * @file
 * Contains \Drupal\diff\Plugin\views\field\DiffFrom.
 */

namespace Drupal\diff\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;

/**
 * @ViewsField("diff__to")
 */
class DiffTo extends DiffPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['label']['default'] = t('To');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function viewsForm(&$form, FormStateInterface $form_state) {
    $use_revision = array_key_exists('revision', $this->view->getQuery()->getEntityTableInfo());

    if (!empty($this->view->result)) {
      $form[$this->options['id']]['#tree'] = TRUE;
      foreach ($this->view->result as $row_index => $row) {
        $entity = $row->_entity;
        $form[$this->options['id']][$row_index] = [
          '#type' => 'radio',
          '#parents' => [$this->options['id']],
          '#title' => $this->t('Compare this item'),
          '#title_display' => 'invisible',
          '#return_value' => $this->calculateEntityBulkFormKey($entity, $use_revision),
        ];
      }
    }
  }

}

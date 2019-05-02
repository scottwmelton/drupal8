<?php

namespace Drupal\views_redirect_form\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;

/**
 * @ViewsField("views_redirect_form__compare_to")
 */
class RedirectCompareTo extends RedirectComparePluginBase {

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
    if (!empty($this->view->result)) {
      $form[$this->options['id']]['#tree'] = TRUE;
      foreach ($this->view->result as $row_index => $row) {
        $entity = $row->_entity;
        $form[$this->options['id']][$row_index] = [
          '#type' => 'radio',
          '#parents' => [$this->options['id']],
          '#title' => $this->t('Compare this item'),
          '#title_display' => 'invisible',
          '#return_value' => $this->calculateEntityFormKey($entity),
        ];
      }
    }
  }

}

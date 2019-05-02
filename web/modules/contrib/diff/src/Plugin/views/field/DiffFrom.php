<?php

/**
 * @file
 * Contains \Drupal\diff\Plugin\views\field\DiffFrom.
 */

namespace Drupal\diff\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\node\NodeInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("diff__from")
 */
class DiffFrom extends DiffPluginBase {

  use RedirectDestinationTrait;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['label']['default'] = t('From');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function viewsForm(&$form, FormStateInterface $form_state) {
    // Replace the form submit button label.
    $form['actions']['submit']['#value'] = $this->t('Compare');

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

  protected function getToFieldId() {
    $to_fields = array_filter($this->view->field, function (FieldPluginBase $field) {
      return $field instanceof DiffTo;
    });
    return array_keys($to_fields)[0];
  }

  /**
   * Submit handler for the bulk form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the user tried to access an action without access to it.
   */
  public function viewsFormSubmit(&$form, FormStateInterface $form_state) {
    if ($form_state->get('step') == 'views_form_views_form') {
      $diff_from = $form_state->getValue($this->options['id']);
      $diff_from_entity = $this->loadEntityFromBulkFormKey($diff_from);

      $diff_to = $form_state->getValue($this->getToFieldId());
      $diff_to_entity = $this->loadEntityFromBulkFormKey($diff_to);

      $options = array(
        'query' => $this->getDestinationArray(),
      );
      $entity_type_id = $diff_from_entity->getEntityTypeId();


      if ($diff_from_entity instanceof NodeInterface) {
        $form_state->setRedirect('diff.revisions_diff', [$entity_type_id => $diff_from_entity->id(),'left_vid' => $diff_from_entity->getRevisionId(), 'right_vid' => $diff_to_entity->getRevisionId()], $options);
      }
      else {
        $route_name = 'entity.' . $entity_type_id . '.revisions_diff';
        $form_state->setRedirect($route_name, [$entity_type_id => $diff_from_entity->id(), 'left_revision' => $diff_from_entity->getRevisionId(), 'right_revision' => $diff_to_entity->getRevisionId()], $options);
      }
    }
  }


}

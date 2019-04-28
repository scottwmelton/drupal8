<?php

/**
 * @file
 * Contains \Drupal\dc_annotate\Form\DcEditRequestFormatAlter.
 */

namespace Drupal\dc_annotate\Form;

use Drupal\dc\Entity\DCContent;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Afar edit request comment custom functionality.
 */
class DcEditRequestFormatAlter {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alter(array &$form, FormStateInterface $form_state) {
    $form['actions']['submit']['#value'] = $this->t('Save and send edit request');
    $form['actions']['submit']['#submit'][] = [get_called_class(), 'sendToAfar'];
  }

  /**
   * {@inheritdoc}
   */
  public static function sendToAfar($form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\comment\CommentInterface $comment */
    $comment = $form_object->getEntity();

    // Update entity status flags.
    $commented_entity = $comment->getCommentedEntity();
    if ($commented_entity instanceof DCContent) {
      // Flag as edit pending.
      $commented_entity->set('afar_edit_request', $comment->getChangedTime());
      // Unset new afar_status.
      if ($commented_entity->get('afar_status')[0]->value == DC_AFAR_STATUS_NEW) {
        $commented_entity->set('afar_status', NULL);
      }
      $commented_entity->save();
    }

    /** @var \Drupal\dc_annotate\AfarEmailSender $afar_mail_send */
    $afar_mail_send = \Drupal::service('dc_annotate.afar_sender');
    $afar_mail_send->send($comment);
  }

}

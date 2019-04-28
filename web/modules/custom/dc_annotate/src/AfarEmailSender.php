<?php

/**
 * @file
 * Contains \Drupal\dc_annotate\AfarEmailSender.
 */

namespace Drupal\dc_annotate;

use Drupal\comment\CommentInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Utility\Token;

/**
 * Sends out edit requests to afar.
 */
class AfarEmailSender {

  /** @var \Drupal\Core\Mail\MailManagerInterface */
  protected $mail;

  /** @var \Drupal\Core\Config\Config */
  protected $config;

  /** @var \Drupal\Core\Utility\Token */
  protected $token;

  /**
   * Creates a new AfarEmailSender instance.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail
   *   The mail manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Utility\Token $token
   *   The token system.
   */
  public function __construct(MailManagerInterface $mail, ConfigFactoryInterface $config_factory, Token $token) {
    $this->mail = $mail;
    $this->config = $config_factory->get('dc_annotate.settings');
    $this->token = $token;
  }

  /**
   * Send a edit request to afar.
   *
   * @param \Drupal\comment\CommentInterface $comment
   *   The comment of this edit request.
   */
  public function send(CommentInterface $comment) {
    $parameters = [
      'comment' => $comment,
      'dc_content' => $comment->getCommentedEntity(),
    ];
    $to = $this->config->get('afar.address');
    $to = $this->token->replace($to, $parameters);

    $this->mail->mail('dc_annotate', 'afar_edit_request', $to, 'en', $parameters);
  }

}

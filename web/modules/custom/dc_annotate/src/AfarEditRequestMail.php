<?php

/**
 * @file
 * Contains \Drupal\dc_annotate\AfarEditRequestMail.
 */

namespace Drupal\dc_annotate;

use Drupal\Core\Config\Config;
use Drupal\Core\Utility\Token;

class AfarEditRequestMail {

  /** @var \Drupal\Core\Utility\Token */
  protected $token;

  /** @var \Drupal\Core\Config\Config */
  protected $config;

  /**
   * Creates a new AfarEditRequestMail instance.
   *
   * @param \Drupal\Core\Utility\Token $token
   * @param \Drupal\Core\Config\Config $config
   */
  public function __construct(Token $token, Config $config) {
    $this->token = $token;
    $this->config = $config;
  }

  /**
   * @param array $message
   *   An array to be filled in.
   * @param array $params
   *   An array of parameters supplied by the caller of
   *   MailManagerInterface->mail().
   */
  public function mail(array &$message, $params) {
    $data = [
      'comment' => $params['comment'],
      'dc_content' => $params['dc_content']
    ];
    $message['subject'] = $this->token->replace($this->config->get('afar.subject'), $data);
    $message['body'][] = $this->token->replace($this->config->get('afar.body'), $data);

    if ($from = $this->config->get('afar.from')) {
      $from = $this->token->replace($from, $data);
      $message['headers']['From'] = $from;
    }
    if ($cc = $this->config->get('afar.cc')) {
      $cc = $this->token->replace($cc, $data);
      $message['headers']['Cc'] = $cc;
    }
    if ($bcc = $this->config->get('afar.bcc')) {
      $bcc = $this->token->replace($bcc, $data);
      $message['headers']['Bcc'] = $bcc;
    }
  }

}

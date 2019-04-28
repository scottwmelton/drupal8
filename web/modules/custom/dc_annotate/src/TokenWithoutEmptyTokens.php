<?php

namespace Drupal\dc_annotate;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Utility\Token;

/**
 * Provides a token utility implementation without any empty tokens.
 */
class TokenWithoutEmptyTokens extends Token {

  /**
   * {@inheritdoc}
   */
  public function replace($text, array $data = array(), array $options = array(), BubbleableMetadata $bubbleable_metadata = NULL) {
    $result = parent::replace($text, $data, $options, $bubbleable_metadata);

    $remaining_text_tokens_by_type = $this->scan($result);
    $token_names = [];
    array_walk_recursive($remaining_text_tokens_by_type, function ($value) use (&$token_names) {
      $token_names[] = $value;
    });

    $result = str_replace($token_names, '', $result);
    return $result;
  }


}

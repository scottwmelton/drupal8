<?php
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Site\Settings;

/**
 * Implements hook_token_info().
 */
function afar_tweaks_token_info() {
  $info['tokens']['site']['environment_name'] = [
    'name' => t('Environment'),
    'description' => ('The environment indicator'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function afar_tweaks_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type == 'site') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'environment_name':
          $replacements[$original] = Settings::get('environment_name');
          break;
      }
    }
  }

  return $replacements;

}

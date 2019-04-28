<?php
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function dc_token_info() {
  $type = [
    'name' => t('DC Content'),
    'description' => t('..'),
    'needs-data' => 'dc_content',
  ];

  $dc_content['id'] = [
    'name' => t('DC Content ID'),
    'description' => t('The unique ID of the dc content item, or "dc_content".'),
  ];

  $dc_content['name'] = [
    'name' => t('DC Content name'),
    'description' => t('The name'),
  ];

  return [
    'types' => ['dc_content' => $type],
    'tokens' => ['dc_content' => $dc_content],
  ];
}

/**
 * Implements hook_tokens().
 */
function dc_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type != 'dc_content') {
    return $replacements;
  }

  /** @var \Drupal\dc\DCContentInterface $dc_content */
  $dc_content = $data['dc_content'];
  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'id':
        $replacements[$original] = $dc_content->id();
        break;
      case 'name':
        $replacements[$original] = $dc_content->name->value;
        break;
    }
  }

  return $replacements;
}

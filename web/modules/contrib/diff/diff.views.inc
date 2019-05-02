<?php
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Implements hook_views_data().
 */
function diff_views_data() {
  $data = [];
  $entity_types = \Drupal::entityTypeManager()->getDefinitions();
  $revisionable_entity_types = array_filter($entity_types, function (EntityTypeInterface $entity_type) {
    return $entity_type->isRevisionable();
  });

  /** @var \Drupal\Core\Entity\EntityTypeInterface $entity_type */
  foreach ($revisionable_entity_types as $entity_type) {
    $revision_base_table = $entity_type->getRevisionDataTable() ?: $entity_type->getRevisionTable();

    $data[$revision_base_table]['diff_from'] = [
      'title' => t('Diff from'),
      'help' => '',
      'field' => [
        'id' => 'diff__from',
      ],
    ];

    $data[$revision_base_table]['diff_to'] = [
      'title' => t('Diff to'),
      'help' => '',
      'field' => [
        'id' => 'diff__to',
      ],
    ];
  }

  return $data;
}

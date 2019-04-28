<?php

/**
 * @file
 * Contains \Drupal\afar_import\EntityReferencesChanged.
 */

namespace Drupal\afar_import;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

class EntityReferencesChanged {

  /**
   * {@inheritdoc}
   */
  protected $allowedChangedFields = ['field_places', 'field_top_picks'];

  /**
   * Compares two entities to figure out whether a new revision should be created.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $old_entity
   *   The entity before the update.
   * @param \Drupal\Core\Entity\ContentEntityInterface $new_entity
   *   The entity after the update.
   *
   * @return bool
   *   Returns TRUE, if changed.
   */
  public function entityChanged(ContentEntityInterface $old_entity, ContentEntityInterface $new_entity) {
    $changed_fields = $this->determineChangedFields($old_entity, $new_entity);

    return (bool) array_diff($changed_fields, $this->allowedChangedFields);
  }

  protected function determineChangedFields(ContentEntityInterface $original, ContentEntityInterface $entity) {
    $entity_manager = \Drupal::entityManager();
    $definitions = $entity_manager->getFieldDefinitions($original->getEntityTypeId(), $original->bundle());

    $changed_fields = [];
    foreach ($definitions as $field_name => $field_definition) {
      if ($this->hasFieldValueChanged($field_definition, $entity, $original)) {
        $changed_fields[] = $field_name;
      }
    }

    return $changed_fields;
  }

  /**
   * Checks whether the field values changed compared to the original entity.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Field definition of field to compare for changes.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to check for field changes.
   * @param \Drupal\Core\Entity\ContentEntityInterface $original
   *   Original entity to compare against.
   *
   * @return bool
   *   True if the field value changed from the original entity.
   */
  protected function hasFieldValueChanged(FieldDefinitionInterface $field_definition, ContentEntityInterface $entity, ContentEntityInterface $original) {
    $field_name = $field_definition->getName();
    $langcodes = array_keys($entity->getTranslationLanguages());
    if ($langcodes !== array_keys($original->getTranslationLanguages())) {
      // If the list of langcodes has changed, we need to save.
      return TRUE;
    }
    foreach ($langcodes as $langcode) {
      $items = $entity->getTranslation($langcode)->get($field_name)->filterEmptyItems();
      $original_items = $original->getTranslation($langcode)->get($field_name)->filterEmptyItems();
      // If the field items are not equal, we need to save.
      if (!$items->equals($original_items)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}

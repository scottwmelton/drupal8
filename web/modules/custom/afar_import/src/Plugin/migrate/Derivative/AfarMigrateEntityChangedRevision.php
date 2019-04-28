<?php

namespace Drupal\afar_import\Plugin\migrate\Derivative;

class AfarMigrateEntityChangedRevision extends MigrateEntityChangedRevision {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->entityDefinitions as $entity_type => $entity_info) {
      if ($entity_info->isSubclassOf('Drupal\Core\Config\Entity\ConfigEntityInterface')) {
        continue;
      }
      $this->derivatives[$entity_type] = array(
        'id' => "afar_entity_changed_revision:$entity_type",
        'class' => 'Drupal\afar_import\Plugin\migrate\destination\AfarEntityChangedRevision',
        'requirements_met' => 1,
        'provider' => $entity_info->getProvider(),
      );
    }
    return $this->derivatives;
  }

}

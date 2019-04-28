<?php

/**
 * @file
 * Contains \Drupal\dc\InstallationHelper.
 */

namespace Drupal\dc;

class InstallationHelper {

  public function installDiffConfiguration() {
    // Store default settings for support ticket diffs. We do it manually as this configuration
    // lives in diff.settings, a configuration file we don't manage.
    $config = \Drupal::configFactory()->getEditable('diff.settings');
    $base_fields = \Drupal::entityManager()->getBaseFieldDefinitions('dc_content');
    foreach ($base_fields as $field_key => $field) {
      // Compare every field for now?
      $config->set('entity.dc_content' . '.' . $field_key, TRUE);
    }
    $config->save();
  }

}

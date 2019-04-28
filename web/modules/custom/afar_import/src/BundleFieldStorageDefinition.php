<?php

/**
 * @file
 * Contains \Drupal\afar_import\BundleFieldStorageDefinition.
 */

namespace Drupal\afar_import;

use Drupal\Core\Field\BaseFieldDefinition;

class BundleFieldStorageDefinition extends BaseFieldDefinition {

  /**
   * {@inheritdoc}
   */
  public function isBaseField() {
    return FALSE;
  }

}

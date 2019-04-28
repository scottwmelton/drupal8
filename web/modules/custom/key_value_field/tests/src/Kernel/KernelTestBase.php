<?php

/**
 * @file
 * Contains \Drupal\Tests\key_value_field\Kernel\KernelTestBase.
 */

namespace Drupal\Tests\key_value_field\Kernel;

use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Core\Site\Settings;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase as DrupalKernelTestBase;

abstract class KernelTestBase extends DrupalKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['key_value_field', 'field', 'user', 'entity_test', 'filter'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    FileCacheFactory::setPrefix(Settings::getApcuPrefix('file_cache', $this->root));
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');
    $this->installConfig(['filter']);
  }

  protected function createTestField($field_type, $field_storage_properties = [], $field_properties = []) {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test_key_value_field',
      'entity_type' => 'entity_test',
      'type' => $field_type,
    ] + $field_storage_properties);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_name' => 'test_key_value_field',
      'entity_type' => 'entity_test',
      'type' => $field_type,
      'bundle' => 'entity_test',
    ] + $field_properties);
    $field->save();
  }

}

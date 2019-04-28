<?php

/**
 * @file
 * Contains \Drupal\Tests\afar_import\Kernel\EntityReferencesChangedTest.
 */

namespace Drupal\Tests\afar_import\Kernel;

use Drupal\afar_import\EntityReferencesChanged;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\afar_import\EntityReferencesChanged
 * @group afar_import
 */
class EntityReferencesChangedTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['user', 'system', 'field', 'entity_test'];

  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');

    $er_field_storage = FieldStorageConfig::create([
      'field_name' => 'field_places',
      'entity_type' => 'entity_test',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'entity_test',
      ],
    ]);
    $er_field_storage->save();

    $er_field = FieldConfig::create([
      'field_name' => 'field_places',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ]);
    $er_field->save();

    $integer_field_storage = FieldStorageConfig::create([
      'field_name' => 'test_integer',
      'entity_type' => 'entity_test',
      'type' => 'integer',
    ]);
    $integer_field_storage->save();

    $integer_field = FieldConfig::create([
      'field_name' => 'test_integer',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ]);
    $integer_field->save();
  }


  /**
   * @covers ::entityChanged
   */
  public function testEntityChangedChangedNumericField() {
    $entity1 = EntityTest::create([]);
    $entity2 = EntityTest::create([]);

    $entity = EntityTest::create([
      'test_integer' => 2,
      'field_places' => [$entity1->id()],
    ]);
    $entity->save();
    $old_entity = clone $entity;

    $entity->test_integer->value = 3;

    $entity_reference_changed = new EntityReferencesChanged();
    $this->assertTrue($entity_reference_changed->entityChanged($old_entity, $entity));
  }

  /**
   * @covers ::entityChanged
   */
  public function testEntityChangedChangedEntityReferenceField() {
    $entity1 = EntityTest::create([]);
    $entity2 = EntityTest::create([]);

    $entity = EntityTest::create([
      'test_integer' => 2,
      'field_places' => [$entity1->id()],
    ]);
    $old_entity = clone $entity;

    $entity->field_places->target_id = $entity2->id();

    $entity_reference_changed = new EntityReferencesChanged();
    $this->assertFalse($entity_reference_changed->entityChanged($old_entity, $entity));
  }

  /**
   * @covers ::entityChanged
   */
  public function testEntityChangedChangedEntityReferenceAndNumericField() {
    $entity1 = EntityTest::create([]);
    $entity2 = EntityTest::create([]);

    $entity = EntityTest::create([
      'test_integer' => 2,
      'field_places' => [$entity1->id()],
    ]);
    $old_entity = clone $entity;

    $entity->test_integer->value = 3;
    $entity->field_places->target_id = $entity2->id();

    $entity_reference_changed = new EntityReferencesChanged();
    $this->assertTrue($entity_reference_changed->entityChanged($old_entity, $entity));
  }

}

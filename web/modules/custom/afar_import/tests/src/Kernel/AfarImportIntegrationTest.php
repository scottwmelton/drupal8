<?php

/**
 * @file
 * Contains \Drupal\Tests\afar_import\Kernel\AfarImportIntegrationTest.
 */

namespace Drupal\Tests\afar_import\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Entity\Migration;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_tools\MigrateExecutable;

/**
 * Tests the afar import functionality.
 *
 * @group afar_import
 */
class AfarImportIntegrationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['afar_import', 'dc', 'afar_guzzle_helper', 'migrate', 'field', 'afar_import_test', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('dc_content');
    $this->installEntitySchema('user');
    $this->installConfig(['afar_import', 'afar_import_test']);
  }



  public function testFoo() {
    $this->markTestSkipped();
    /** @var \Drupal\migrate\Entity\MigrationInterface $migration */
    $migration = Migration::load('afar_dc_place');

    $count = \Drupal::entityQuery('dc_content')->count()->execute();
    $this->assertEquals(0, $count);

    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();

    $count = \Drupal::entityQuery('dc_content')->count()->execute();
    $this->assertGreaterThan(0, $count, 'No content is imported.');

    $all_entities = \Drupal::entityManager()->getStorage('dc_content')->loadMultiple();
    $map = array_map(function($item) {
      return $item->field_sid->value;
    }, $all_entities);
    print_r($map);
//    $entities = \Drupal::entityManager()->getStorage('dc_content')->loadMultiple(\Drupal::entityQuery('dc_content')->condition('field_sid', 71350)->execute());
//    $this->assertCount(1, $entities);
    $example_entity = reset($entities);
    // Check at least some basic import.
  }

}

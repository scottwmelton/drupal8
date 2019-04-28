<?php

namespace Drupal\Tests\afar_import\Kernel\Plugin\migrate\process;

use Drupal\afar_import\Plugin\migrate\process\TermByProperty;
use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Entity\Migration;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Row;

/**
 * @coversDefaultClass \Drupal\afar_import\Plugin\migrate\process\TermByProperty
 * @group afar_import
 */
class TermByPropertyTest extends KernelTestBase implements MigrateMessageInterface {

  public static $modules = ['taxonomy', 'afar_import', 'migrate', 'text'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('taxonomy_term');
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Missing vocabulary ID
   */
  public function testMissingVocabularyId() {
    $term_storage = \Drupal::entityManager()->getStorage('taxonomy_term');
    $process = new TermByProperty([], 'afar_import__term_by_property', [], $term_storage);

    $migrate = Migration::create([]);
    $migrate_executable = new MigrateExecutable($migrate, $this);

    $row = new Row([], []);
    $process->transform('', $migrate_executable, $row, 'field_port_code');
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Missing property configuration
   */
  public function testMissingProperty() {
    $term_storage = \Drupal::entityManager()->getStorage('taxonomy_term');
    $process = new TermByProperty(['vocabulary_id' => 'port'], 'afar_import__term_by_property', [], $term_storage);

    $migrate = Migration::create([]);
    $migrate_executable = new MigrateExecutable($migrate, $this);

    $row = new Row([], []);
    $process->transform('', $migrate_executable, $row, 'field_port_code');
  }

  public function testTransformWithValidData() {
    $term_storage = \Drupal::entityManager()->getStorage('taxonomy_term');
    $process = new TermByProperty(['vocabulary_id' => 'port', 'property' => 'name'], 'afar_import__term_by_property', [], $term_storage);

    $term1 = $term_storage->create([
      'name' => 'test_name1',
      'vid' => 'port',
    ]);
    $term_storage->save($term1);
    $term2 = $term_storage->create([
      'name' => 'test_name2',
      'vid' => 'port',
    ]);
    $term_storage->save($term2);

    $migrate = Migration::create([]);
    $migrate_executable = new MigrateExecutable($migrate, $this);

    $row = new Row([], []);
    $result = $process->transform('test_name1', $migrate_executable, $row, 'field_port_code');
    $this->assertEquals($term1->id(), $result);
  }

  /**
   * {@inheritdoc}
   */
  public function display($message, $type = 'status') {
    $this->assert($type == 'status', $message, 'migrate');
  }

}

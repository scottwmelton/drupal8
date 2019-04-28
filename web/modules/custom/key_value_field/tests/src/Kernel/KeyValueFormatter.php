<?php

/**
 * @file
 * Contains \Drupal\Tests\key_value_field\Kernel\KeyValueFormatterTest.
 */

namespace Drupal\Tests\key_value_field\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\entity_test\Entity\EntityTest;

/**
 * @coversDefaultClass \Drupal\key_value_field\Plugin\Field\FieldFormatter\KeyValueFormatter
 * @group key_value_field
 */
class KeyValueFormatterTest extends KernelTestBase {


  public function testFormatter() {
    $this->createTestField('key_value');

    $entity_view_display = EntityViewDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
    ]);
    $entity_view_display->setComponent('test_key_value_field', ['type' => 'key_value']);
    $entity_view_display->save();

    $entity = EntityTest::create([
      'test_key_value_field' => ['value' => "orange", 'key' => 'apple'],
    ]);
    $entity->save();

    $build = $entity_view_display->build($entity);
    $output = $this->render($build);
    $this->assertEquals("apple : <p>orange</p>\n", $output);
  }

}

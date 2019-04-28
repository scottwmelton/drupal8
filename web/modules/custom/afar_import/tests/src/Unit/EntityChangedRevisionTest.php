<?php

/**
 * @file
 * Contains
 *   \Drupal\Tests\afar_import\Plugin\migration\destination\EntityChangedRevisionTest.
 */

namespace Drupal\Tests\afar_import\Plugin\migration\destination;

use Drupal\afar_import\Plugin\migrate\destination\EntityChangedRevision;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityType;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\afar_import\Plugin\migrate\destination\EntityChangedRevision
 * @group afar_import
 */
class EntityChangedRevisionTest extends \PHPUnit_Framework_TestCase {

  /** @var \Drupal\afar_import\Plugin\migrate\destination\EntityChangedRevision */
  protected $entityChangedRevision;

  protected function setupEntityChangedRevision($entity_mock = NULL, $storage = NULL, array $configuration = [], $controller_resolver = NULL) {
    $configuration = $configuration ?: [];
    $plugin_definition = [];
    $migration = $this->prophesize(MigrationInterface::class);

    if (!$entity_mock) {
      $entity_mock = $this->prophesize(ContentEntityInterface::class);
    }

    if (!$storage) {
      $storage = $this->prophesize(EntityStorageInterface::class);
      $storage->getEntityType()->willReturn(new EntityType([
        'id' => 'test_entity',
        'entity_keys' => ['id' => 'id'],
      ]));
      $storage->create([])->willReturn($entity_mock);
    }

    $entity_manager = $this->prophesize(EntityManagerInterface::class);
    $field_type_manager = $this->prophesize(FieldTypePluginManagerInterface::class);
    $controller_resolver = $controller_resolver ?: $this->prophesize(ControllerResolverInterface::class);

    $request = Request::create('/test');

    $this->entityChangedRevision = new EntityChangedRevision(
      $configuration,
      'test',
      $plugin_definition,
      $migration->reveal(),
      $storage->reveal(),
      [],
      $entity_manager->reveal(),
      $field_type_manager->reveal(),
      $request,
      $controller_resolver->reveal()
    );
  }

  /**
   * @covers ::import
   */
  public function testImportWithNewEntityWithExistingID() {
  $entity_mock = $this->prophesize(ContentEntityInterface::class);
    $entity_mock->setNewRevision(TRUE)->shouldNotBeCalled();
    $entity_mock->enforceIsNew()->shouldBeCalled();
    $entity_mock->isNew()->willReturn(TRUE);
    $entity_mock->save()->shouldBeCalled();
    $entity_mock->id()->willReturn(1);

    $entity_type = new EntityType([
      'id' => 'test_entity',
      'entity_keys' => ['id' => 'id'],
    ]);
    $entity_mock->getEntityType()->willReturn($entity_type);
    $entity_mock->set('id', 2)->shouldBeCalled();

    $storage = $this->prophesize(EntityStorageInterface::class);
    $storage->getEntityType()->willReturn($entity_type);
    $storage->load(2)->willReturn(FALSE);
    $storage->create(['id' => 2])->willReturn($entity_mock);

    $this->setupEntityChangedRevision($entity_mock, $storage);

    $row = new Row(['id' => 1], ['id' => TRUE]);
    $row->setDestinationProperty('id', 2);

    $this->entityChangedRevision->import($row);
  }

  /**
   * @covers ::import
   */
  public function testImportWithNewEntityWithNotExistingID() {
    $entity_mock = $this->prophesize(ContentEntityInterface::class);
    $entity_mock->setNewRevision(TRUE)->shouldNotBeCalled();
    $entity_mock->enforceIsNew()->shouldBeCalled();
    $entity_mock->isNew()->willReturn(TRUE);
    $entity_mock->save()->shouldBeCalled();
    $entity_mock->id()->willReturn(NULL);

    $entity_type = new EntityType([
      'id' => 'test_entity',
      'entity_keys' => ['id' => 'id'],
    ]);
    $entity_mock->getEntityType()->willReturn($entity_type);
    $entity_mock->set('id', 2)->shouldNotBeCalled();

    $storage = $this->prophesize(EntityStorageInterface::class);
    $storage->getEntityType()->willReturn($entity_type);
    $storage->load(2)->willReturn(FALSE);
    $storage->create([])->willReturn($entity_mock);

    $this->setupEntityChangedRevision($entity_mock, $storage);

    $row = new Row(['id' => 1], ['id' => TRUE]);

    $this->entityChangedRevision->import($row);
  }

  /**
   * @covers ::import
   */
  public function testImportWithExistingEntity() {
    $entity_mock = $this->prophesize(ContentEntityInterface::class);
    $entity_mock->setNewRevision(TRUE)->shouldBeCalled();
    $entity_mock->isNewRevision()->willReturn(TRUE)->shouldBeCalled();
    $entity_mock->enforceIsNew()->shouldNotBeenCalled();
    $entity_mock->isNew()->willReturn(FALSE);
    $entity_mock->save()->shouldBeCalled();
    $entity_mock->id()->willReturn(1);
    $entity_mock->id = 0;

    $entity_type = new EntityType([
      'id' => 'test_entity',
      'entity_keys' => ['id' => 'id'],
    ]);
    $entity_mock->getEntityType()->willReturn($entity_type);
    $entity_mock->set('id', 2)->shouldBeCalled();

    $storage = $this->prophesize(EntityStorageInterface::class);
    $storage->getEntityType()->willReturn($entity_type);
    $storage->load(2)->willReturn($entity_mock->reveal());
    $storage->resetCache()->willReturn();
    $storage->create()->shouldNotBeenCalled();

    $this->setupEntityChangedRevision($entity_mock, $storage);

    $row = new Row(['id' => 1], ['id' => TRUE]);
    $row->setDestinationProperty('id', 2);

    $this->entityChangedRevision->import($row);
  }

  /**
   * @covers ::import
   * @dataProvider providerBoolean
   */
  public function testImportWithExistingEntityAndRevisionCallback($boolean, $callback, $controller_instance) {
    $entity_mock = $this->prophesize(ContentEntityInterface::class);

    if ($boolean) {
      $entity_mock->setNewRevision(TRUE)->shouldBeCalled();
      $entity_mock->setNewRevision(FALSE)->shouldNotBeCalled();
      $entity_mock->isNewRevision()->willReturn(TRUE)->shouldBeCalled();
    }
    else {
      $entity_mock->setNewRevision(TRUE)->shouldNotBeCalled();
      $entity_mock->setNewRevision(FALSE)->shouldBeCalled();
      $entity_mock->isNewRevision()->willReturn(FALSE)->shouldBeCalled();
    }

    $entity_mock->isNew()->willReturn(FALSE);
    $entity_mock->enforceIsNew()->shouldNotBeCalled();
    $entity_mock->save()->shouldBeCalled();
    $entity_mock->id()->willReturn(2);
    $entity_mock->id = 0;

    $entity_type = new EntityType([
      'id' => 'test_entity',
      'entity_keys' => ['id' => 'id'],
    ]);
    $entity_mock->getEntityType()->willReturn($entity_type);
    $entity_mock->set('id', 2)->shouldBeCalled();

    $storage = $this->prophesize(EntityStorageInterface::class);

    $storage->getEntityType()->willReturn($entity_type);
    $storage->load(2)->will(function() use ($entity_mock) {
      $entity_mock_clone = clone $entity_mock;
      return $entity_mock_clone->reveal();
    });
    $storage->resetCache()->willReturn();
    $storage->create()->shouldNotBeenCalled();

    $configuration = [
      'new_revision_callback' => $callback,
    ];

    $controller_resolver = $this->prophesize(ControllerResolverInterface::class);
    $controller_resolver->getControllerFromDefinition($callback)->willReturn($controller_instance);

    $this->setupEntityChangedRevision($entity_mock, $storage, $configuration, $controller_resolver);

    $row = new Row(['id' => 1], ['id' => TRUE]);
    $row->setDestinationProperty('id', 2);

    $this->entityChangedRevision->import($row);
  }

  public function providerBoolean() {
    return [
      [FALSE, '\Drupal\Tests\afar_import\Plugin\migration\destination\Test::entityChangedFalse', [new Test(), 'entityChangedFalse']],
      [TRUE, '\Drupal\Tests\afar_import\Plugin\migration\destination\Test::entityChangedTrue', [new Test(), 'entityChangedTrue']],
      [TRUE, '\Drupal\Tests\afar_import\Plugin\migration\destination\TestWithNonStaticMethod::entityChanged', [new TestWithNonStaticMethod(), 'entityChanged']]
    ];
  }

}

class Test {

  public static function entityChangedTrue(ContentEntityInterface $old_entity, ContentEntityInterface $new_entity) {
    if (spl_object_hash($old_entity) === spl_object_hash($new_entity)) {
      throw new \InvalidArgumentException();
    }
    return TRUE;
  }

  public static function entityChangedFalse(ContentEntityInterface $old_entity, ContentEntityInterface $new_entity) {
    if (spl_object_hash($old_entity) === spl_object_hash($new_entity)) {
      throw new \InvalidArgumentException();
    }
    return FALSE;
  }

}

class TestWithNonStaticMethod {

  protected $id = 123;

  public function __construct() {
    $this->id = 2;
  }

  public function entityChanged(ContentEntityInterface $old_entity, ContentEntityInterface $new_entity) {
    return $new_entity->id() === 2;
  }

}

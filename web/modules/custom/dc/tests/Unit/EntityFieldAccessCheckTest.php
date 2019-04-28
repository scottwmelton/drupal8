<?php

namespace Drupal\Tests\dc_content\Unit;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\dc\Entity\Access\EntityFieldAccessCheck;
use Prophecy\Argument;
use Symfony\Component\Routing\Route;

/**
 * @coversDefaultClass \Drupal\dc\Entity\Access\EntityFieldAccessCheck
 */
class EntityFieldAccessCheckTest extends \PHPUnit_Framework_TestCase {

  /** @var \Drupal\dc\Entity\Access\EntityFieldAccessCheck */
  protected $entityFieldAccess;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setupEntityFieldAccessCheck();
  }

  protected function setupEntityFieldAccessCheck(EntityManagerInterface $em = NULL) {
    $em  = $em ?: $this->prophesize(EntityManagerInterface::class)->reveal();
    $this->entityFieldAccess = new EntityFieldAccessCheck($em);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAccessWithOutRequirement() {
    $this->setupEntityFieldAccessCheck();

    $route = new Route('/test', [], ['_meh']);
    $route_match = new RouteMatch('test_route', $route, []);
    $this->entityFieldAccess->access($route_match);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAccessWithWrongRequirement() {
    $this->setupEntityFieldAccessCheck();

    $route = new Route('/test', [], ['_entity_field_access' => 'non-existing-type']);
    $route_match = new RouteMatch('test_route', $route, []);
    $this->entityFieldAccess->access($route_match);
  }

  public function testAccess() {
    $entity_access = $this->prophesize(EntityAccessControlHandlerInterface::class);
    $entity_access->fieldAccess('view', Argument::any(), Argument::any(), Argument::any(), TRUE)
      ->willReturn(AccessResult::allowed());

    $em = $this->prophesize(EntityManagerInterface::class);
    $em->getAccessControlHandler('test_entity')->willReturn($entity_access->reveal());
    $this->setupEntityFieldAccessCheck($em->reveal());

    $entity = $this->prophesize(FieldableEntityInterface::class);
    $field_definition = $this->prophesize(FieldDefinitionInterface::class);
    $entity->getFieldDefinition('test_field')->willReturn($field_definition->reveal());
    $entity->test_field = $this->prophesize(FieldItemListInterface::class)->reveal();
    $entity = $entity->reveal();

    $route = new Route('/test/{test_entity}', [], ['_entity_field_access' => 'test_entity.test_field.view']);
    $route_match = new RouteMatch('test_route', $route, ['test_entity' => $entity]);
    $result = $this->entityFieldAccess->access($route_match);
    $this->assertInstanceOf(AccessResultAllowed::class, $result);
  }

}

<?php

/**
 * @file
 * Contains \Drupal\Tests\views_fragments\Kernel\RestFragmentsIntegrationTest.
 */

namespace Drupal\Tests\views_fragments\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\KernelTestBase;
use Drupal\views\Tests\ViewTestData;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group rest_fragments
 */
class RestFragmentsIntegrationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rest_fragments', 'rest', 'system', 'views', 'views_fragments_test', 'serialization', 'entity_test', 'user'];


  /**
   * {@inheritdoc}
   */
  public static $testViews = ['test_rest_fragment_view_entity_test', 'test_rest_fragment_view_entity_test_argument', 'test_rest_fragment_with_custom_serializer'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', 'router');
    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');

    ViewTestData::createTestViews(get_class($this), ['views_fragments_test']);

    $entity = EntityTest::create([
      'name' => 'First entity',
    ]);
    $entity->save();
    $entity = EntityTest::create([
      'name' => 'Second entity',
    ]);
    $entity->save();
  }

  public function testBasic() {
    $view = Views::getView('test_rest_fragment_view_entity_test');
    $result = $view->executeDisplay('rest_export_1');

    $expected_result = [];
    $expected_result[] = [
      'data_fragment' => [
        [
          'id' => 1
        ],
        [
          'id' => 2
        ],
      ],
      'name' => 'First entity',
    ];
    $expected_result[] = [
      'data_fragment' => [
        [
          'id' => 1
        ],
        [
          'id' => 2
        ],
      ],
      'name' => 'Second entity',
    ];

    $this->assertEquals($expected_result, json_decode($result['#markup'], TRUE));
    $this->assertResponseJson($expected_result, '/test-path');
  }

  public function testWithAdditionalArguments() {
    $view = Views::getView('test_rest_fragment_view_entity_test_argument');
    $result = $view->executeDisplay('rest_export_1');

    $expected_result = [];
    $expected_result[] = [
      'id' => 1,
      'data_fragment' => [
        [
          'id' => 1
        ],
      ],
      'name' => 'First entity',
    ];
    $expected_result[] = [
      'id' => 2,
      'data_fragment' => [
        [
          'id' => 2
        ],
      ],
      'name' => 'Second entity',
    ];

    $this->assertEquals($expected_result, json_decode($result['#markup'], TRUE));

    $this->assertResponseJson($expected_result, '/test-path-argument');
  }

  protected function assertResponseJson(array $expected_result, $path) {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel */
    $http_kernel = \Drupal::service('http_kernel');
    $request = Request::create($path);
    $request->query->set('_format', 'json');
    $response = $http_kernel->handle($request);

    $this->assertEquals($expected_result, json_decode($response->getContent(), TRUE));
  }

  public function testWithCustomSerializer() {
    $view = Views::getView('test_rest_fragment_with_custom_serializer');
    $result = $view->executeDisplay('rest_export_1');

    $expected_result = [];
    $expected_result[] = [
      'id' => 1,
      'data_fragment' => [
        'test_key' => 'test_data',
      ],
      'name' => 'First entity',
    ];
    $expected_result[] = [
      'id' => 2,
      'data_fragment' => [
        'test_key' => 'test_data',
      ],
      'name' => 'Second entity',
    ];

    $this->assertEquals($expected_result, json_decode($result['#markup'], TRUE));

    $this->assertResponseJson($expected_result, '/test-custom-serializer');
  }

}

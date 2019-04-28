<?php

/**
 * @file
 * Contains \Drupal\Tests\views_fragments\Kernel\RestFragmentsConfigSaveTest.
 */

namespace Drupal\Tests\views_fragments\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\views\Entity\View;
use Drupal\views\Tests\ViewTestData;

/**
 * @group rest_fragments
 */
class RestFragmentsConfigSaveTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rest_fragments', 'rest', 'system', 'views', 'views_fragments_test', 'serialization'];

  /**
   * {@inheritdoc}
   */
  public static $testViews = ['test_rest_fragment_view'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', 'router');

    ViewTestData::createTestViews(get_class($this), ['views_fragments_test']);
  }

  public function testSchema() {
    /** @var \Drupal\views\Entity\View $view */
    $view = View::load('test_rest_fragment_view');
    $view->save();
  }

}

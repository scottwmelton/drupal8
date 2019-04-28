<?php

/**
 * @file
 * Contains \Drupal\rest_fragments\Tests\DataFragmentUITest.
 */

namespace Drupal\rest_fragments\Tests;

use Drupal\views\Entity\View;
use Drupal\views_ui\Tests\UITestBase;

/**
 * Tests the UI of the rest_fragments UI.
 *
 * @group rest_fragments
 */
class DataFragmentUITest extends UITestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rest_fragments', 'rest'];

  public function testUI() {
    // Create some example content.
    $this->drupalCreateContentType(['type' => 'test']);
    $this->drupalCreateNode(['type' => 'test']);

    $view = [];
    $view['page[create]'] = FALSE;
    $view['rest_export[create]'] = TRUE;
    $view['rest_export[path]'] = 'test-path';
    $view['id'] = 'test_view';

    $this->randomView($view);

    $this->drupalPostForm(NULL, [], 'Add Data Fragment');
    $this->clickLink('Entity');

    $edit = [
      'row[type]' => 'data_field'
    ];
    $this->drupalPostForm(NULL, $edit, t('Apply'));
    $this->drupalPostForm(NULL, [], t('Apply'));

    $this->drupalGet('admin/structure/views/view/test_view/edit/rest_export_1');
    $this->clickLink('Entity');

    // Row
    $edit = [
      'row[type]' => 'data_field'
    ];
    $this->drupalPostForm(NULL, $edit, t('Apply'));

    // Row options
    $edit = [];
    $this->drupalPostForm(NULL, [], t('Apply'));

    // Adding fields
    $this->drupalGet('admin/structure/views/nojs/add-handler/test_view/rest_export_1/field');
    $edit = [
      'override[dropdown]' => 'rest_export_1',
      'name[rest_fragments.data_fragment]' => 'rest_fragments.data_fragment',
    ];
    $this->drupalPostForm(NULL, $edit, 'Add and configure fields');
    $edit = [
      'options[fragment_display]' => 'rest_fragment_1',
    ];
    $this->drupalPostForm(NULL, $edit, 'Apply');
    $this->drupalPostForm(NULL, [], 'Save');

    /** @var \Drupal\views\Entity\View $view */
    $view = View::load('test_view');

    $display = $view->getDisplay('rest_export_1');

    $this->assertEqual('rest_fragment_1', $display['display_options']['fields']['data_fragment']['fragment_display']);
    $this->assertEqual('', $display['display_options']['fields']['data_fragment']['additional_arguments']);
  }

}

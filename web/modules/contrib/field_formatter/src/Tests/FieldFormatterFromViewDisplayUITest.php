<?php

/**
 * @file
 * Contains \Drupal\field_formatter\Tests\FieldFormatterFromViewDisplayUITest.
 */

namespace Drupal\field_formatter\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that field_formatter UI work correctly.
 *
 * @group field_formatter
 */
class FieldFormatterFromViewDisplayUITest extends WebTestBase {

    /**
     * The test user.
     *
     * @var \Drupal\User\UserInterface
     */
    protected $adminUser;

    /**
     * Modules to enable.
     *
     * @var array
     */
    public static $modules = ['field_formatter_test'];

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();

        $this->adminUser = $this->drupalCreateUser([
            'administer taxonomy',
            'bypass node access'
        ]);
        $this->drupalLogin($this->adminUser);
    }

    /**
     * Tests a field_formatter from view display.
     */
    public function testFieldFormatterFromViewDisplay()  {
        // Add term.
        $this->drupalGet('admin/structure/taxonomy/manage/test_vocabulary/add');
        $term_name = strtolower($this->randomMachineName());
        $field = strtolower($this->randomMachineName());
        $edit_term = [
            'name[0][value]' => $term_name,
            'field_test_field[0][value]' => $field
        ];
        $this->drupalPostForm(NULL, $edit_term, t('Save'));
        $this->assertText(t("Created new term $term_name."), t("Created term."));

        // Add content.
        $this->drupalGet('node/add/test_content_type');
        $content_name = strtolower($this->randomMachineName());
        $edit_content = [
            'title[0][value]' => $content_name,
            'field_field_test_ref[0][target_id]' => $term_name
        ];
        $this->drupalPostForm(NULL, $edit_content, t('Save'));
        $this->assertRaw('<div class="field__label">test_field</div>', 'Field is correctly displayed on node page.');
        $this->assertRaw('<div class="field__item">' . $field . '</div>', "Field's content was found.");
    }

}

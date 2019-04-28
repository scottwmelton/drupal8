<?php

/**
 * @file
 * Contains \Drupal\rest_fragments\Plugin\views\field\DataFragment.
 */

namespace Drupal\rest_fragments\Plugin\views\field;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rest_fragments\RestFragmentData;
use Drupal\rest_fragments\Plugin\views\style\RestFragmentStyleInterface;
use Drupal\views\Plugin\views\display\DisplayPluginInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\Views;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("data_fragment")
 */
class DataFragment extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function allowAdvancedRender() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['fragment_display'] = ['default' => ''];
    $options['additional_arguments'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Remove incompatible options.
    unset(
      $form['alter'],
      $form['style_settings'],
      $form['hide_alter_empty'],
      $form['exclude'],
      $form['custom_label'],
      $form['label'],
      $form['element_label_colon']
    );
    foreach ($form as $name=>$element) {
      if (!empty($element['#fieldset'])
        && in_array($element['#fieldset'], ['style_settings', 'alter'])
      ) {
        // Remove incompatible options.
        $form[$name]['#access'] = AccessResult::forbidden();
      }
    }

    // Get all REST displays except itself.
    $fragment_options = ['--none--' => '--none--'] + $this->getCompatibleDisplayIDs();

    // Add a selector for the fragment display.
    $form['fragment_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Fragment Display'),
      '#description' => $this->t('The display to use for this fragment.'),
      '#options' => $fragment_options,
      '#default_value' => $this->options['fragment_display'],
    ];
    // Add a option for additional arguments.
    $form['additional_arguments'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Additional Arguments'),
      '#description' => $this->t('Additional arguments to pass to the fragment view. Use "/" as a separator for multiple arguments. Note additional arguments come before the views arguments.'),
      '#default_value' => $this->options['additional_arguments'],
    ];

    $form['replacement_keys'] = [
      '#type' => 'details',
      '#title' => $this->t('Argument Tokens'),
      '#value' => $this->getTokenInfo(),
      '#open' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $fragment_display = $this->options['fragment_display'];
    // Get arguments from tokens.
    $additional_arguments = $this->getTokenArguments($values);

    $result = '';
    if (!empty($fragment_display) && $fragment_display !== '--none--' && $this->view->displayHandlers->get($fragment_display) instanceof DisplayPluginInterface) {
      // Load and execute the category view.
      $fragment_view = Views::getView($this->view->id());
      $fragment_view->setDisplay($fragment_display);
      $fragment_view->setArguments($additional_arguments);
      $fragment_view->preExecute();
      $fragment_view->execute();

      if (!empty($this->build_info['fail'])) {
        \Drupal::logger('hal')->error("Fragment view: '{$this->view->id()}' failed to be executed.");
        return [];
      }

      // Try to return the data from the stype plugin first.
      if ($fragment_view->style_plugin instanceof RestFragmentStyleInterface) {
        $result = $fragment_view->style_plugin->getRenderData();
      }
      else {
        // Make sure there is something to add.
        if (!empty($fragment_view->result)) {
          // Render each result with the row plugin.
          foreach ($fragment_view->result as $key=>$fragment_result_row) {
            /** @var \Drupal\views\ResultRow $row * */
            $result[] = $fragment_view->rowPlugin->render($fragment_result_row);
          }
        }
      }

      /** @var \Drupal\views\Plugin\views\cache\CachePluginBase $cache_plugin_base */
      $cache_plugin_base = $fragment_view->getDisplay()->getPlugin('cache');

      $data = new RestFragmentData($result);
      $data->addCacheTags($cache_plugin_base->getCacheTags());
      $data->mergeCacheMaxAge($cache_plugin_base->getCacheMaxAge());
      $build = [
        '#markup' => $data->jsonSerialize(),
      ];

      CacheableMetadata::createFromObject($data)->applyTo($build);

      return $build;
    }

    // Return the text, so the code never thinks the value is empty.
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function advancedRender(ResultRow $values) {
    return $this->render($values);
  }

  /**
   * {@inheritdoc}
   */
  public function adminLabel($short = FALSE) {
    // Get the default value.
    $default_value = $this->getField(parent::adminLabel($short));
    // check for a valid
    if ($default_value == 'Global: Data Fragment'
      && empty($this->options['label'])
      && !empty($this->options['fragment_display'])
      && $this->options['fragment_display'] !== '--none--'
      && $this->view->getDisplay($this->options['fragment_display'])
      && ($fragment_display = $this->view->displayHandlers->get($this->options['fragment_display']))
      && $fragment_display instanceof DisplayPluginInterface
    ) {
      //
      return $this->t('@group: @label (Fragment)', [
        '@group' => $this->definition['group'],
        '@label' => $fragment_display->display['display_title']
      ]);
    }

    return $default_value;
  }

  /**
   * Gets values for each argument for this fragment.
   *
   * @param \Drupal\views\ResultRow $values
   *   The result row.
   *
   * @return array
   *   An array of argument values.
   */
  public function getTokenArguments(ResultRow $values) {
    // Change any legacy commas to slashes.
    $args = str_replace(',', '/', $this->options['additional_arguments']);
    $args = array_map('trim', explode('/', $args));

    // Fix views bug that lets row tokens persist across rows.
    $last_field = end($this->view->field);
    unset($last_field->last_tokens);
    unset($this->view->style_plugin->render_tokens);

    // Replace the argument tokens with values.
    array_walk($args, function (&$value, $k, $params){
      $value = !empty($value) ? $this->tokenizeValue($value, $params->index) : NULL;
    }, $values);

    return $args;
  }

  /**
   * Gets available field tokens.
   *
   * @return array()
   *   An item list of available tokens.
   */
  public function getTokenInfo() {
    // Get tokens from fields that appear before the current field.
    foreach ($this->getPreviousFieldLabels() as $id => $label) {
      $options['field']["{{ $id }}"] = substr(strrchr($label, ":"), 2 );
    }

    // Add available argument tokens.
    foreach ($this->view->display_handler->getHandlers('argument') as $arg => $handler) {
      $options['argument']["{{ arguments.$arg }}"] = $this->t('@argument title', ['@argument' => $handler->adminLabel()]);
      $options['argument']["{{ raw_arguments.$arg }}"] = $this->t('@argument input', ['@argument' => $handler->adminLabel()]);
    }

    // Descriptions for groups of tokens.
    $prefix_text = [
      'field' => $this->t('<p><strong>The following <em>field</em> tokens are available for use as arguments. Note that due to rendering order, you cannot use fields that come after this field. If you need a field that isn\'t listed, try re-arranging  your fields.</strong></p>'),
      'argument' => $this->t('<p><strong>The following <em>argument</em> tokens are available.</strong></p>'),
    ];

    // loop though each group of tokens.
    foreach ($options as $type => $tokens) {
      // Replace the argument tokens with values.
      $items = array_map(function ($k) use ($tokens){
        return $this->t('%key == %value', ['%key' => $k,'%value' => $tokens[$k]]);
      }, array_keys($tokens));

      $output[] = empty($items) ? NULL : [
        '#theme' => 'item_list',
        '#items' => array_filter($items),
        '#prefix' => !empty($prefix_text[$type]) ? $prefix_text[$type] : null,
      ];
    }

    return $output;
  }

  /**
   * Gets all "data" displays for this view except itself.
   *
   * @return array $compatible_displays
   */
  protected function getCompatibleDisplayIDs() {
    // Buffer displays.
    $compat_displays = [];
    // Loop through all displays except itself.
    foreach (array_diff($this->view->displayHandlers->getInstanceIds(), [$this->displayHandler->display['id']]) as $display_id) {
      // Make sure the display is a data display.
      // @TODO Make sure these are "data" type plugins.
      if ($this->view->displayHandlers->get($display_id)->display['display_plugin'] === 'rest_fragment') {
        // Add it to the list.
        $compat_displays[$display_id] = $this->view->displayHandlers->get($display_id)->display['display_title'];
      }
    }

    return $compat_displays;
  }
}

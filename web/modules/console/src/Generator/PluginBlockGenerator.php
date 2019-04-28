<?php
/**
 * @file
 * Contains \Drupal\AppConsole\Generator\PluginBlockGenerator.
 */

namespace Drupal\AppConsole\Generator;

class PluginBlockGenerator extends Generator
{
  /**
   * Generator Plugin Block
   * @param  $module
   * @param  $class_name
   * @param  $plugin_label
   * @param  $plugin_id
   * @param  $services
   */
  public function generate($module, $class_name, $plugin_label, $plugin_id, $services)
  {

    $parameters = [
      'module'   => $module,
      'class_name' => $class_name,
      'plugin_label' => $plugin_label,
      'plugin_id' => $plugin_id,
      'services'    => $services,
    ];

    $this->renderFile(
      'module/plugin-block.php.twig',
      $this->getPluginPath($module, 'Block').'/'.$class_name.'.php',
      $parameters
    );
  }
}

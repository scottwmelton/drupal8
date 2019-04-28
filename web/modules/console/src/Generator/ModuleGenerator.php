<?php
/**
 *@file
 * Contains \Drupal\AppConsole\Generator\ModuleGenerator.
 */

namespace Drupal\AppConsole\Generator;

class ModuleGenerator extends Generator
{

  private $defaultDirectoryStructure = ['src', 'src/Controller', 'src/Form', 'src/Plugin', 'src/Tests', 'templates' ];

  public function generate($module, $dir, $description, $core, $package, $controller, $tests, $setting, $structure, $skip_root)
  {
    $dir .= '/' . $module;
    if (file_exists($dir)) {
      if (!is_dir($dir)) {
        throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
      }
      $files = scandir($dir);
      if ($files != array('.', '..') && !$skip_root) {
        throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
      }
      if (!is_writable($dir)) {
        throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
      }
    }

    $parameters = array(
      'module' => $module,
      'type'    => 'module',
      'core'    => $core,
      'description'    => $description,
      'package' => $package,
    );

    // help to port module
    if ($skip_root) {
      $dot_info = $dir . '/' . $module . '.info';

      if (!file_exists($dot_info)) {
          throw new \RuntimeException(sprintf('Don\'t exist info file in "%s".', $dot_info ));
      }

      $info = file($dot_info);
      foreach ($info as $id => $line) {
        $data = explode('=',$line);
        switch (str_replace(' ','', $data[0])) {
          case 'name':
              $parameters['module'] = trim($data[1]);
          break;
          case 'description':
              $parameters['description'] = trim($data[1]);
          break;
          case 'package':
              $parameters['package'] = trim($data[1]);
          break;
        }
      }
      $this->renderFile('module/module.info.yml.twig', $dir.'/'.$module.'.info.yml', $parameters);
      unlink($dot_info);
    } else {
      $this->renderFile('module/module.info.yml.twig', $dir.'/'.$module.'.info.yml', $parameters);
      $this->renderFile('module/module.module.twig', $dir.'/'.$module.'.module', $parameters);
    }

    if ($setting) {
      $this->renderFile('module/module.settings.yml.twig', $dir.'/config/'.$module.'.settings.yml',$parameters);
    }

    if ($controller) {
      $class_name = 'DefaultController';
      $parameters = array(
        'class_name' => $class_name,
        'module' => $module,
        'method_name' => 'hello',
        'class_machine_name' => 'default_controller',
        'route' => $module . '/hello/{name}',
      );
      $this->renderFile(
          'module/module.controller.php.twig',
          $dir.'/src/Controller/'.$class_name.'.php',
          $parameters
      );

      $this->renderFile('module/controller-routing.yml.twig', $dir.'/'.$module.'.routing.yml', $parameters);
    }

    if ($tests) {
      $this->renderFile(
        'module/module.tests.twig',
        $dir.'/src/Tests/'. $module .'Test.php',
        $parameters
      );
    }

    if ($structure) {
      foreach ($this->defaultDirectoryStructure as $directory) {
        if (!file_exists($dir.'/'.$directory)) {
          drupal_mkdir($dir.'/'.$directory);
        }
      }
    }
  }
}

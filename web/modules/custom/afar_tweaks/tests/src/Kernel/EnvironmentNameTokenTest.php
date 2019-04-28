<?php

namespace Drupal\Tests\afar_tweaks\Kernel;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group afar_tweaks
 */
class EnvironmentNameTokenTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['afar_tweaks', 'system'];

  public function testToken() {
    $this->assertEquals('', \Drupal::token()->replace('[site:environment_name]'));

    $settings = [
      'environment_name' => 'dev',
    ];
    new Settings($settings);
    $this->assertEquals('dev', \Drupal::token()->replace('[site:environment_name]'));
  }

}

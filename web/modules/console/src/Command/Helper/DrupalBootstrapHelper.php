<?php
namespace Drupal\AppConsole\Command\Helper;

use Symfony\Component\Console\Helper\Helper;

class DrupalBootstrapHelper extends Helper
{
  /**
   * @param string $pathToBootstrapFile
   */
  public function bootstrapConfiguration($pathToBootstrapFile)
  {
    require_once $pathToBootstrapFile;
    \drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);
  }

  public function bootstrapCode()
  {
    \drupal_bootstrap(DRUPAL_BOOTSTRAP_CODE);
  }

  public function getDrupalRoot()
  {
    return DRUPAL_ROOT;
  }

  /**
   * @see \Symfony\Component\Console\Helper\HelperInterface::getName()
   */
  public function getName()
  {
    return 'bootstrap';
  }
}

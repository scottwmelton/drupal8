<?php
/**
 * @file
 * Contains Drupal\AppConsole\Command\Helper\BootstrapFinderHelper.
 */

namespace Drupal\AppConsole\Command\Helper;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Finder\Finder;

class BootstrapFinderHelper extends Helper
{
  /**
   * @var Finder
   */
  protected $finder;

  /**
   * @param Finder $finder
   */
  public function __construct(Finder $finder)
  {
    $this->finder = $finder;
  }

  public function findBootstrapFile(OutputInterface $output)
  {
    $currentPath = getcwd() . '/';
    $relativePath = '';
    $filesFound = 0;

    while ($filesFound === 0) {
      $path = $currentPath . $relativePath . 'core/includes';

      try {
        $iterator = $this->finder
                         ->files()
                         ->name('bootstrap.inc')
                         ->in($path);
        $filesFound = $iterator->count();
      } catch (\InvalidArgumentException $e) {
        $relativePath .= '../';

        if (realpath($currentPath . $relativePath) === '/') {
          throw new \InvalidArgumentException('Cannot find Drupal bootstrap file.');
        }
      }
    }

    foreach ($iterator as $file) {
      $bootstrapRealPath = $file->getRealpath();
      break;
    }

    return $bootstrapRealPath;
  }

  public function getName()
  {
    return 'finder';
  }
}

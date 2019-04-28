<?php
/**
 * @file
 * Contains \Drupal\AppConsole\Command\DrushCommand.
 */

namespace Drupal\AppConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrushCommand extends Command
{
  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
    $this
      ->setName('drush')
      ->setDescription('Run drush into console')
      ->addArgument('args', InputArgument::IS_ARRAY, 'Drush arguments.')
      ->setHelp(<<<EOT
Use the interactive mode for a better experience
./bin/console --shell
EOT
      )
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $args = '';
    if ($arguments = $input->getArgument('args')) {
      $args .= ' '.implode(' ', $arguments);
      $c_args = preg_replace('/[^a-z0-9-= ]/i', '', $args);
    }

    if (`which drush`) {
      system('drush'.$c_args);
    } else {
      $output->write("<error>Drush command not found.</error>");
    }
  }
}

<?php
/**
 * @file
 * Contains \Drupal\AppConsole\Command\GeneratorCommandCommand.
 */

namespace Drupal\AppConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\AppConsole\Command\Helper\ModuleTrait;
use Drupal\AppConsole\Generator\CommandGenerator;

class GeneratorCommandCommand extends GeneratorCommand
{
  use ModuleTrait;

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
    $this
      ->setDefinition(array(
        new InputOption('module','',InputOption::VALUE_REQUIRED, 'The name of the module.'),
        new InputOption('class-name','',InputOption::VALUE_OPTIONAL, 'Commmand Name'),
        new InputOption('command','',InputOption::VALUE_OPTIONAL, 'Commmand Name'),
        new InputOption('container', '', InputOption::VALUE_NONE, 'Get access to the services container'),
      ))
    ->setDescription('Generate commands for the console')
    ->setHelp('The <info>generate:command</info> command helps you generate a new command.')
    ->setName('generate:command');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $dialog = $this->getDialogHelper();

    if ($input->isInteractive()) {
      if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
        $output->writeln('<error>Command aborted</error>');
        return 1;
      }
    }

    $module = $input->getOption('module');
    $class_name = $input->getOption('class-name');
    $command = $input->getOption('command');
    $container = $input->getOption('container');

    $this
      ->getGenerator()
      ->generate($module, $command, $class_name, $container)
    ;

    $errors = [];
    $dialog->writeGeneratorSummary($output, $errors);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output)
  {
    $dialog = $this->getDialogHelper();
    $dialog->writeSection($output, 'Welcome to the Drupal Command generator');

    // --module option
    $module = $input->getOption('module');
    if (!$module) {
      // @see Drupal\AppConsole\Command\Helper\ModuleTrait::moduleQuestion
      $module = $this->moduleQuestion($output, $dialog);
    }
    $input->setOption('module', $module);

    // --command
    $command = $input->getOption('command');
    if (!$command) {
      $command = $dialog->ask($output,
        $dialog->getQuestion('Enter the command name', $module.':default'), 
        $module.':default'
      );
    }
    $input->setOption('command', $command);

    // --name option
    $class_name = $input->getOption('class-name');
    if (!$class_name) {
      $class_name = $dialog->ask($output,
        $dialog->getQuestion('Enter the class command name', 'DefaultCommand'),
        'DefaultCommand'
      );
    }
    $input->setOption('class-name', $class_name);

    // --container option
    $container = $input->getOption('container');
    if (!$container && $dialog->askConfirmation($output,
      $dialog->getQuestion('Access to services container', 'yes', '?'),
      TRUE)
    ) {
      $container = TRUE;
    }
    $input->setOption('container', $container);
  }

  protected function createGenerator()
  {
    return new CommandGenerator();
  }
}

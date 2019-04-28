<?php
/**
 *@file
 * Contains \Drupal\AppConsole\Test\Command\GeneratorServiceCommandTest.
 */

namespace Drupal\AppConsole\Test\Command;

use Symfony\Component\Console\Tester\CommandTester;

class GeneratorServiceCommandTest extends GenerateCommandTest
{
  /**
   * @dataProvider getInteractiveData
   */
  public function testInteractive($options, $expected, $input)
  {
    list($module, $service_name, $class_name, $services) = $expected;

    $generator = $this->getGenerator();
    $generator
      ->expects($this->once())
      ->method('generate')
      ->with($module, $service_name, $class_name, $services)
    ;

    $command = $this->getCommand($generator,$input);
    $cmd = new CommandTester($command);
    $cmd->execute($options);
  }

  public function getInteractiveData()
  {
    $services = [
      'twig' => [
        'name' => 'twig',
        'machine_name' => 'twig',
        'class' => 'Twig_Environment',
        'short'=>'Twig_Environment',
      ]
    ];

    return [
      // case one
      [
        // Inline options
        [],
        // Expected options
        ['foo', 'foo.default','DefaultService', $services],
        // User input options
        "foo\nfoo.default\nDefaultService\nyes\ntwig\n\n",
      ],
      // case two
      [
        // Inline options
        ['--module'=>'foo'],
        // Expected options
        ['foo', 'foo.default','DefaultService', null],
        // User input options
        "foo.default\nDefaultService\nno\n",
      ],
      // case three
      [
        // Inline options
        ['--module'=>'foo', '--service-name'=>'foo.default'],
        // Expected options
        ['foo', 'foo.default','DefaultService', null],
        // User input options
        "DefaultService\nno\n",
      ],
      // case three
      [
        // Inline options
        ['--module'=>'foo', '--service-name'=>'foo.default', '--class-name'=>'DefaultService'],
        // Expected options
        ['foo', 'foo.default','DefaultService', $services],
        // User input options
        "yes\ntwig\n\n",
      ],
    ];
  }

  protected function getCommand($generator, $input)
  {
    $command = $this
      ->getMockBuilder('Drupal\AppConsole\Command\GeneratorServiceCommand')
      ->setMethods(['getModules','getServices'])
      ->getMock()
    ;

    $command->expects($this->any())
      ->method('getModules')
      ->will($this->returnValue(['foo']));
    ;

    $command->expects($this->any())
      ->method('getServices')
      ->will($this->returnValue(['twig','database']));
    ;

    $command->setContainer($this->getContainer());
    $command->setHelperSet($this->getHelperSet($input));
    $command->setGenerator($generator);

    return $command;
  }

  private function getGenerator()
  {
    return $this
      ->getMockBuilder('Drupal\AppConsole\Generator\ServiceGenerator')
      ->disableOriginalConstructor()
      ->setMethods(['generate'])
      ->getMock()
    ;
  }
}

<?php

namespace Drupal\Tests\dc_annotate\Unit;

use Drupal\dc_annotate\TokenWithoutEmptyTokens;

/**
 * @coversDefaultClass \Drupal\dc_annotate\TokenWithoutEmptyTokens
 * @group dc_annotate
 */
class TokenWithoutEmptyTokensTest extends \PHPUnit_Framework_TestCase {

  /**
   * The cache used for testing.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

  /**
   * The language manager used for testing.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  /**
   * The module handler service used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The language interface used for testing.
   *
   * @var \Drupal\Core\Language\LanguageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $language;

  /**
   * The token service under test.
   *
   * @var \Drupal\Core\Utility\Token|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $token;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cacheTagsInvalidator;

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $cacheContextManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->cache = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $this->languageManager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->language = $this->getMock('\Drupal\Core\Language\LanguageInterface');

    $this->cacheTagsInvalidator = $this->getMock('\Drupal\Core\Cache\CacheTagsInvalidatorInterface');

    $this->renderer = $this->getMock('Drupal\Core\Render\RendererInterface');

    $this->token = new TokenWithoutEmptyTokens($this->moduleHandler, $this->cache, $this->languageManager, $this->cacheTagsInvalidator, $this->renderer);
  }

  /**
   * @covers ::replace
   */
  public function testReplaceWithEmptyTokens() {
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn(['[node:title]' => 'hello world']);

    $result = $this->token->replace('[node:non-existing] [node:title]');
    $this->assertEquals(' hello world', $result);
  }

}

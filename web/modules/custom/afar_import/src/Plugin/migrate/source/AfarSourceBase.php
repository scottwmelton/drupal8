<?php

/**
 * @file
 * Contains \Drupal\afar_import\Plugin\migrate\source\AfarSourceBase.
 */

namespace Drupal\afar_import\Plugin\migrate\source;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AfarSourceBase extends SourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * The base URL for the afar HTTP request.
   *
   * See afar_import.settings:base_url
   *
   * @var string
   */
  protected $afarBaseUrl;

  /**
   * Contains the afar base URL + the suffix for the actual data, like
   * 'locations' as well as the auth_token.
   *
   * @var string
   */
  protected $baseUrl;

  /** @var \Drupal\Core\Logger\LoggerChannelFactoryInterface */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, Client $client, $afar_base_url, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->client = $client;
    $this->afarBaseUrl = $afar_base_url;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('http_client'),
      $container->get('config.factory')->get('afar_import.settings')->get('base_url'),
      $container->get('logger.factory')
    );
  }

  /**
   * Log a HTTP request.
   */
  protected function logHttpRequest($uri, $data) {
    $disabled = \Drupal::state()->get('hal_log.disabled') ?: 0;
    if ($disabled) return;

    $context = [
      '@time' => time(),
      '@type' => 'afar_http_request',
      '@request_uri' => (string) $uri,
      '@json_data' => json_encode($data, JSON_PRETTY_PRINT),
      '@migration' => $this->migration->id(),
    ];
    $this->loggerFactory->get('hal_log')
      ->info('Import at @time', $context);
  }

  /**
   * Log a bad HTTP request.
   *
   * @param string $uri
   *   The requested URI.
   */
  protected function logBadHttpRequest($uri, BadResponseException $e) {
    $disabled = \Drupal::state()->get('hal_log.disabled') ?: 0;
    if ($disabled) return;


    $context = [
      '@time' => time(),
      '@status_code' => $e->getResponse()->getStatusCode(),
      '@type' => 'afar_http_request',
      '@request_uri' => (string) $uri,
      '@migration' => $this->migration->id(),
    ];
    $this->loggerFactory->get('hal_log')
      ->info('Bad request @status_code at @time', $context);
  }

  /**
   * {@inheritdoc}
   */
  public function count($refresh = FALSE) {
    // We just import all of them always.
    return -1;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->baseUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['id']['type'] = 'integer';
    return $ids;
  }

}

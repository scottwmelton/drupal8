<?php

/**
 * @file
 * Contains \Drupal\afar_import\Plugin\migrate\source\AfarDcLocations.
 */

namespace Drupal\afar_import\Plugin\migrate\source;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\migrate\Entity\MigrationInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Uri;

/**
 * @MigrateSource(
 *   id = "afar_dc_locations"
 * )
 */
class AfarDcLocations extends AfarSourceBase {

  protected $oldPager = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, Client $client, $afar_base_url, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $client, $afar_base_url, $logger_factory);

    $this->baseUrl = isset($configuration['base_url']) ? $configuration['base_url'] : $this->afarBaseUrl . 'locations?auth_token=Mpbk15p9WKeh62hnFnqD&last_update=2013-07-16-00:00:00-UTC&limit=50';
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $page = 1;
    $offset = 0;

    $has_data = TRUE;
    while ($has_data == TRUE) {
      $uri = new Uri($this->baseUrl . '&page=' . $page++);
      try {
        $result = $this->client->get($uri);
      }
      catch (BadResponseException $e) {
        $this->logBadHttpRequest($uri, $e);
        return;
      }
      $data = json_decode($result->getBody(), TRUE);
      $this->logHttpRequest($uri, $data);
      $has_data = !empty($data['locations']);

      if ($has_data) {
        $offset += count($data['locations']);
        foreach ($data['locations'] as $location) {
          if (!empty($this->configuration['type'])) {
            if ($location['type'] !== $this->configuration['type']) {
              continue;
            }
          }
          yield $location;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'code',
      'description',
      'short_description',
      'id',
      'large_image_url',
      'priority',
      'title',
      'type',
    ];
  }

}

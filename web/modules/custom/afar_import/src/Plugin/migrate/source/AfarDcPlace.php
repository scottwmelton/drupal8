<?php

/**
 * @file
 * Contains \Drupal\afar_import\Plugin\migrate\source\AfarDcPlace.
 */

namespace Drupal\afar_import\Plugin\migrate\source;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Uri;

/**
 * @MigrateSource(
 *   id = "afar_dc_place"
 * )
 */
class AfarDcPlace extends AfarSourceBase implements ContainerFactoryPluginInterface {

  /**
   * @todo ... how to deal with last_update ...
   */
  protected $baseUrl = 'http://www.afar.com/syndication/partners/holland_america/places?auth_token=Mpbk15p9WKeh62hnFnqD&last_update=2013-07-16-00:00:00-UTC&limit=50';


  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, Client $client, $afar_base_url, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $client, $afar_base_url, $logger_factory);

    $this->baseUrl = $this->afarBaseUrl . 'places?auth_token=Mpbk15p9WKeh62hnFnqD&last_update=2013-07-16-00:00:00-UTC&limit=50';
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $page = \Drupal::state()->get('afar_dc_place_page') ?: 1;
    $has_data = TRUE;
    while ($has_data == TRUE) {
      $uri = new Uri($this->baseUrl . '&page=' . $page);
      try {
        $result = $this->client->get($uri);
      }
      catch (BadResponseException $e) {
        $this->logBadHttpRequest($uri, $e);
        return;
      }
      \Drupal::state()->set('afar_dc_place_page', ++$page);
      $data = json_decode($result->getBody(), TRUE);
      $this->logHttpRequest($uri, $data);
      $has_data = !empty($data['places']);

      if ($has_data) {

        // Count the place entries by place ID, see below for more information.
        $entries_by_place_id = [];
        foreach ($data['places'] as $place) {
          // Initialize the counter in order to avoid notices.
          $entries_by_place_id += [
            $place['id'] => 0,
          ];
          $entries_by_place_id[$place['id']]++;
        }

        foreach ($data['places'] as $place) {
          // For some undefined reason there are entries returned which are
          // entirely empty.
          if (empty($place) || empty($place['id']) ) {
            continue;
          }

          // For whatever reason there are places which appear twice, once with
          // and once without the short description. We want to skip the entries
          // without the short description. Additional we need to keep in mind
          // that there are places which don't have any short description in the
          // first place, so we just skip entries with > 1 entries.
          if ($entries_by_place_id[$place['id']] > 1 && !isset($place['short_description'])) {
            continue;
          }

          yield $place;
        }
      }
    }
    \Drupal::state()->set('afar_dc_place_page', 1);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'code',
      'description',
      'id',
      'title',
      'type',
      'location_address',
      'location_city',
      'location_country',
      'location_phone_number',
      'location_name',
      'location_region',
      'locations_id',
      'longitude',
      'short_description',
      'text',
      'original_image_url',
      'latitude',
      'longitude'
    ];
  }

}

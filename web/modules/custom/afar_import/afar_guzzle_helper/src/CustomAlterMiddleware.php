<?php

/**
 * @file
 * Contains \Drupal\afar_guzzle_helper\CustomAlterMiddleware.
 */

namespace Drupal\afar_guzzle_helper;

use Drupal\migrate\Entity\Migration;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides a random title for one of the places to force an update every time.
 */
class CustomAlterMiddleware {

  /**
   * {@inheritdoc}
   */
  public function __invoke() {
    return function (callable $handler) {
      return function (RequestInterface $request, array $options) use ($handler) {

        return $handler($request, $options)->then(
          function (ResponseInterface $response) use ($request, $handler, $options) {
            return $this->alter($request, $options, $response);
          }
        );
      };
    };
  }

  public function alter(RequestInterface $request, $options, $response) {
    $body = $response->getBody();
    if ($data = @json_decode($body, TRUE)) {
      if (!empty($data['places'])) {
        $new_revision_entity_id = \Drupal::keyValue('afar_guzzle_helper_revisions')->get('place');
        if ($new_revision_entity_id) {
          // Find the corresponding source ID.
          /** @var \Drupal\migrate\Plugin\MigrateIdMapInterface $map */
          $migration = Migration::load('afar_dc_place');
          $map = \Drupal::service('plugin.manager.migrate.id_map')->createInstance('sql', [], $migration);
          $result = $map->lookupSourceID(['id' => $new_revision_entity_id]);
          $source_id = $result['id'];

          foreach ($data['places'] as &$place) {
            if ($place['id'] == $source_id) {
              $place['title'] = 'Custom test place' . rand(0, 10e20);
            }
          }
        }
      }
      if (!empty($data['locations'])) {
        foreach ($data['locations'] as &$location) {
          $type_mapping = [
            'destinations' => 'destination',
            'region' => 'region',
            'port' => 'port',
          ];
          $bundle = $type_mapping[$location['type']];
          $new_revision_entity_id = \Drupal::keyValue('afar_guzzle_helper_revisions')->get($bundle);
          if (!$new_revision_entity_id) {
            continue;
          }

          // Find the corresponding source ID.
          /** @var \Drupal\migrate\Plugin\MigrateIdMapInterface $map */
          $migration = Migration::load('afar_dc_' . $bundle);
          $map = \Drupal::service('plugin.manager.migrate.id_map')->createInstance('sql', [], $migration);
          $result = $map->lookupSourceID(['id' => $new_revision_entity_id]);
          $source_id = $result['id'];

          if ($location['id'] == $source_id) {
            $location['title'] = 'Custom test place' . rand(0, 10e20);
          }
        }
      }
      $response = $response->withBody(\GuzzleHttp\Psr7\stream_for(json_encode($data)));
    }

    return $response;
  }

}

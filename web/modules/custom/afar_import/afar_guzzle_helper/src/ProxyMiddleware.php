<?php

/**
 * @file
 * Contains \Drupal\afar_guzzle_helper\ProxyMiddleware.
 */

namespace Drupal\afar_guzzle_helper;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class ProxyMiddleware {

  /**
   * @var \Psr\Http\Message\ResponseInterface[]
   */
  protected $cachedRequests = [];

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->addCachedRequest('http://fake.com/regions&page=1', json_encode([
        'locations' => [
          [
            "id" => 234,
            "type" => "region",
            "title" => "French Riviera & Corsica2",
            "code" => "FRCO",
            "description" => "<p>Where the locals cool off in summer, the best place to see the sun set, delicious craft brews, and more.</p>",
            "images" => [
              [
                "title" => "2x1 Image",
                "url" => "http://media.afar.com/uploads/images/post_images/images/vXgLJugPh8/original_ad2dfe6fa7911bc83aa3e810317d1110?1383780970",
                "height" => "600",
                "width" => "1200"
              ],
              [
                "tile" => "1x1 Image",
                "url" => "http://media.afar.com/uploads/images/post_images/images/vXgLJugPh8/original_ad2dfe6fa7911bc83aa3e810317d1110?1383780970",
                "height" => "600",
                "width" => "600"
              ],
            ],
          ],
        ],
      ]
    ));
    $this->addCachedRequest('http://fake.com/regions&page=2', json_encode([]));
    $this->addCachedRequest('http://fake.com/destinations&page=1', json_encode([
        'locations' => [[
          'id' => 345,
          'type' => 'destinations',
          'title' => 'Europe',
          'code' => 'E',
          'description' => '<p>Where the locals cool off in summer, the best place to see the sun set, delicious craft brews, and more.</p>',
          'images' => [
            [
              'title' => '2x1 Image',
              'url' => 'http://media.afar.com/uploads/images/post_images/images/vXgLJugPh8/original_ad2dfe6fa7911bc83aa3e810317d1110?1383780970',
              'height' => '600',
              'width' => '1200'
            ],
            [
              'title' => '1x1 Image',
              'url' => 'http://media.afar.com/uploads/images/post_images/images/vXgLJugPh8/original_ad2dfe6fa7911bc83aa3e810317d1110?1383780970',
              'height' => '600',
              'width' => '600',
            ]
          ],
        ]]
      ]
    ));
    $this->addCachedRequest('http://fake.com/destinations&page=2', json_encode([]));
  }

  public function addCachedRequest($uri, $response) {
    $this->cachedRequests[$uri] = $response;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke() {
    return function (callable $handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        $uri = (string) $request->getUri();
        if (isset($this->cachedRequests[$uri])) {
          return new FulfilledPromise(new Response(200, [], $this->cachedRequests[$uri]));
        }
        else {
          return $handler($request, $options);
        }
      };
    };
  }

}

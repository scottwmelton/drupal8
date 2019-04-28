<?php

namespace Drupal\shp_akamai;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Class Akamai.
 *
 * @package Drupal\shp_akamai
 *
 * Contains common functions for clearing Akamai cache
 */
class Akamai {

  /**
   * CP Codes.
   */
  public static function getCpCodes($server = NULL) {
    $label = t('Flush production endpoint cache');

    switch ($server) {
      case 'hal':
        return [506300 => $label];
        break;

      case 'sbn':
        return [506301 => $label];
        break;

      default:
        return [
          354777 => 'qa/CMS 354777',
          508049 => 'qa-content.seabourn.com 508049',
          363401 => 'stagebook.seabourn.com 363401',
          386551 => 'qabook.seabourn.com 386551',
          386351 => 'qabook.seabourn.com 386351',
          508048 => 'qabook.seabourn.com 508048',
          481098 => 'qa.hollandamerica.com JSON 481098',
          481099 => 'qa.seabourn.com JSON 481099',
          500141 => 'QA images 500141',
          506300 => 'book2.hollandamerica.com 506300',
          506301 => 'book2.seabourn.com 506301',
          506302 => 'www.seabourn.com 506302 cmsapi',
          506303 => 'www.seabourn.com 506303 cmsassets',
        ];
        break;
    }
  }

  /**
   * Messages stored as array, keyed by timestamp.
   */
  public static function getMessages() {
    $current_json = \Drupal::state()->get('current-akamai-jobs');
    if (is_string($current_json)) {
      $decode = json_decode($current_json, TRUE);
      return $decode;
    }
    return [];
  }

  /**
   * Scans messages, returns recent or one specific timestamp.
   */
  public static function getActiveMessages($match_timestamp = FALSE) {
    $timestamp = time();
    // 4 minutes.
    $waiting = $timestamp - 240;
    $active = [];
    $messages = self::getMessages();

    // If we want a specific message, use its timestamp.
    if ($match_timestamp) {

      if (in_array($match_timestamp, $messages)) {
        $message = $messages[$match_timestamp];
        reset($message);
        $cp_code = key($message);
        $active[$cp_code] = $message[$cp_code];
        return $active;
      }

    }

    // If we want active messages, keep recent timestamps.
    foreach ($messages as $time => $message) {
      if ($time >= $waiting) {
        reset($message);
        $cp_code = key($message);
        $active[$cp_code] = $message[$cp_code];
      }
    }

    return $active;
  }

  /**
   * Adds message to stored array.  Removes any past a time threshhold.
   */
  public static function putMessage($message_text, $time = NULL) {
    // Add at timestamp if one not included.
    $timestamp = $time ?: time();

    $day_seconds = 60 * 60 * 24;
    $day_seconds = 60 * 60 * 2;
    $yesterday = $timestamp - $day_seconds;
    $still_valid = [];

    $current = self::getMessages();

    // Only keep messages within the last 24 hours.
    foreach ($current as $time => $text) {
      if ($time > $yesterday) {
        $still_valid[$time] = $text;
      }
    }

    $still_valid[$timestamp] = $message_text;

    $encode = json_encode($still_valid);

    \Drupal::state()->set('current-akamai-jobs', $encode);

  }

  /**
   * Post REST response to Akamai.
   */
  public static function postREST($cp_code = 0, $name = "Akamai Entry", $time = 0) {
    if (!$cp_code) {
      return;
    }

    // Add at timestamp if one not included.
    $timestamp = $time ?: time();

    $uri = '/ccu/v2/queues/default';
    $server = 'https://api.ccu.akamai.com';
    $user = 'hagapi@hollandamericagroup.com';
    $pass = '3DdGusYOslA';

    // $request = Request->create();
    $response = NULL;
    $content = '';

    $until = $timestamp + 300;

    $body = '{ "type" : "cpcode", "objects" : [ "' . $cp_code . '" ] }';

    // Send.
    try {
      $response = \Drupal::httpClient()
        ->post($server . $uri, [
          'auth' => [$user, $pass],
          'body' => $body,
          'timeout' => 10,
          'headers' => [
            'Accept' => 'json',
            'Content-Type' => 'application/json',
          ],
        ]);

      $statusCode = $response->getStatusCode();

      // Success.
      if ($statusCode == 201) {

        $uri = '';
        $content = $response->getHeaders();
        // $body = $response->getBody();
        if (isset($content) && isset($content['Content-Location'])) {
          $uri = $content['Content-Location'];
        }

        $message = [
          $cp_code => [
            'finished' => $until,
            'cp_code' => $cp_code,
            'name' => 'AKAMAI CACHE: ' . $name,
            'return' => [],
            'uri' => $uri,
          ],
        ];

        self::putMessage($message, $timestamp);
        \Drupal::logger('shp_akamai')->notice('Akamai purge call: ' . serialize($message));
        return;
      }
      elseif ($statusCode == 401) {
        throw new UnauthorizedHttpException("Invalid request");
        return;
      }
      elseif ($statusCode == 403) {
        throw new AccessDeniedHttpException("Invalid user");
        return;
      }
      else {
        throw new Exception("Error Processing Request");
        return;
      }

      // ConnectException.
    }
    catch (ClientException $e) {
      $this->errorHandling($e);
    }
    catch (ConnectException $e) {
      $this->errorHandling($e);
    }
    catch (AccessDeniedHttpException $e) {
      $this->errorHandling($e);
    }
    catch (ConflictHttpException $e) {
      $this->errorHandling($e);
    }
    catch (NotFoundHttpException $e) {
      $this->errorHandling($e);
    }
    catch (PreconditionFailedHttpException $e) {
      $this->errorHandling($e);
    }
    catch (UnauthorizedHttpException $e) {
      $this->errorHandling($e);
    }
    catch (Exception $e) {
      $this->errorHandling($e);
    }

  }

  /**
   * Logs the error.
   *
   * @param object $e
   */
  protected function errorHandling($e) {
    $code = $e->getCode();
    $message = $e->getMessage();
    $display_message =  t(
      '@code @exception_type @message',
      [
        '@code' => $code,
        '@exception_type' => get_class($e),
        '@message' => $message,
      ]
    );

    drupal_set_message($display_message);
    \Drupal::logger('shp_akamai')->error($message);
  }

}

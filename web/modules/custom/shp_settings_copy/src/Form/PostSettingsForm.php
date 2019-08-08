<?php

namespace Drupal\shp_settings_copy\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup ;

use Drupal\Core\Ajax\HtmlCommand ;
use Drupal\Core\Ajax\AjaxResponse ;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Uri;


use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;


/**
 * Class PostSettingsForm.
 *
 * @package Drupal\shp_settings_copy\Form
 */
class PostSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'shp_settings_copy.postsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'post_settings_form';
  }

  public $rest_token = NULL;
  public $rest_tokens = array();
  public $message_queue = array();
  public $servers_info_array = array();

  /**
   * Class data custom function
   */
  public function add_message($message) {
      $messages = $this->message_queue;
      if (is_array($message) ) {
        $this->message_queue = array_merge($messages, $message);
      } else {
        $messages[] = $message;
        $this->message_queue = $messages;
      }
  }

  /**
   * Class data custom function
   */
  public function get_messages($clear_queue = FALSE) {
    $messages = $this->message_queue;
    if ($clear_queue)  $this->message_queue = array();
    return $messages;
  }

  /**
   * Class data custom function
   */
  public function get_token($server = NULL) {

    if (! isset($this->rest_tokens[$server]) ) {
      $this->rest_tokens[$server] = $this->collect_rest_token($server) ;
    }

      return $this->rest_tokens[$server] ;

  }

  /**
   * Class data custom function
   */
  public function set_token($token, $server = NULL) {
    $this->rest_tokens[$server] = $token;
  }

  public function get_servers() {

    $server_info = $this->servers_info_array ; 
    if ( count($server_info) ) return $server_info ;

    $entity_manager = \Drupal::entityManager();
    $list =  $entity_manager->getStorage($entity_manager->getEntityTypeFromClass( 'Drupal\relaxed\Entity\Remote' ))->loadMultiple(NULL);

    $server_info = array();

    foreach ($list as $item) {
      // break down user:pass@domain  string to parts
      $uri =  $item->uri();
      $pieces = explode('/', $uri);
      $protocol = $pieces[0];
      $subpieces = explode('@' , $pieces[2] );
      $domain = $subpieces[1];
      $signon = explode(':' , $subpieces[0] );
      $user = $signon[0];
      $pass = $signon[1];

      $params = array(
        'user'  => $user,
        'pass'  => $pass,
        'url' => $protocol . '//' . $domain,
        );

      $server_info[$domain] = $params;

    }

    $this->servers_info_array = $server_info; 
    return $server_info;

  }

  public function get_tasks() {
    return array(
      'check' => 'Modules enabled check', 
      'enable' => 'Enable modules list', 
      'dependencies' => 'Apply dependency settings', 
      'settings' => 'Apply main settings', 
      'cache' =>'clear cache',
      '' => 'Erase',
      );
  }



  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $blacklist_terms = array('dev', 'prod', 'book' );

    global $base_url;

    // crude way to disable on some servers.

    foreach ($blacklist_terms as $bl_term) {
      if ( strpos($base_url, $bl_term) ) {
        $form = array();
        $form['#markup'] = 'Not on this server.';
        return;
      }
    }

    // enc password:  aG9sbGFuZDEyMw==

    $config = $this->config('shp_settings_copy.postsettings');
    $settings_keys = str_replace(' ', '', $config->get('settings_keys') );
    $keys = explode(',', trim($settings_keys) );
    $tasks = $this->get_tasks() ;

    $dependencies = array();
    $modules = array();

    foreach ($keys as $key) {
      $data = NULL;
      $key = trim($key);

      try {
        $data = \Drupal::config( $key) ;
      } catch (Exception $e) {         continue ;       }

      if ($data) {
        $data = $data->getRawData();
        if ( isset($data['dependencies'])  ) {
            if ( isset($data['dependencies']['config']) ) {
              foreach($data['dependencies']['config'] as $term) {
                if (! in_array($term, $keys) ) $dependencies[$term] = $term;
              }
            }

            if ( isset($data['dependencies']['module']) ) {
              foreach($data['dependencies']['module'] as $module) {
                $modules[$module] = $module;
              }
            }
        }
      }
    }

    $servers = $this->get_servers();

    $options = array();
    foreach ($servers as $id => $param_array ) {
      $options[$id] = $param_array['url'];
    }

        $form['server'] = array(
            '#type' => 'radios',
            '#default_value'  => $form_state->getValue('server'),
            '#options' => $options,
            '#title'  => 'Server Endpoint',
        );

    $form['fajax'] = array(
        '#type' => 'radios',
        '#name' => 'command',
        '#options' => $tasks,
        '#title'  => 'Ajax Commands',
        '#description'  => 'Commands. Generally do top down. Dependencies prior to main settings.',
        '#suffix' => '<div class="ajax-message"> Status Messages </div>'
    );

      $form['fajax']['#ajax']  = array (
        'callback' => '::fajax',
        'event' => 'click',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Updating...'),
        ),
      );

    $form['settings_keys'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Settings keys'),
      '#description' => $this->t('Settings key names. Like: image.style.tiny32x32 .  Same as YML file, without .yml .'),
      '#default_value' => $settings_keys,
    ];

    $form['modules'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Modules'),
      '#description' => $this->t('Filled automatically'),
      '#default_value' => implode(',' , array_keys($modules) ),
      '#size' => 2,
      '#disabled' => TRUE,
    );


    $form['dependencies'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Dependencies'),
      '#description' => $this->t('Filled automatically'),
      '#default_value' => implode(', ' , array_keys($dependencies) ),
      '#disabled' => TRUE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('shp_settings_copy.postsettings')
      ->set('settings_keys', $form_state->getValue('settings_keys'))
      ->save();
  }

  /**
   * Custom,  Form AJAX
   */
  public function fajax(array &$form, FormStateInterface $form_state) {

    $action = $form_state->getValue('fajax') ;
    $settings_keys = $form_state->getValue('settings_keys') ;
    $dependencies = $form_state->getValue('dependencies') ;

    $server_id = $form_state->getValue('server') ;

    $server_list = $this->get_servers();
    $server_inf = is_array($server_list[$server_id]) ? $server_list[$server_id] : array() ;
    $server = isset($server_inf['url']) ? $server_inf['url'] : NULL ;

    if (! isset($server_id) ) {
        $this->add_message('Need server');
      } else {

        if ( ($action == 'check') || ($action == 'enable') ) {
              $data = $form_state->getValue('modules') ;
              if ($data) {
                $reply = $this->send_post_request($action, $data, $server);
                $this->add_message($reply);
              } else {
                $this->add_message(' No modules. ');
              }

          } elseif ($action == '') {

          } elseif ($action == 'cache') {
            $reply = $this->send_post_request($action, ' ', $server);
            $this->add_message( $reply );
          } else {
            if ($action == 'dependencies') {
              $list = $dependencies ;
              $this->add_message('dependencies:  ' . $list);
            } else {
              $list = $settings_keys;
              $this->add_message('settings_keys: ' . $list);
            }
            $clean_str = str_replace(' ', '', $list );
            $keys = explode(',', $settings_keys );

            foreach ($keys as $key) {

                $data = \Drupal::config( $key ) ;
                if ($data->isNew() ) {
                  continue;
                } else {

                    $raw = $data->getRawData();
                    if ( count($raw) < 1 ) {
                        $this->add_message('Could not get config for ' . $key);
                        continue;
                    } else {
                        $this->add_message('Sending config for ' . $key);
                        $replies = $this->send_post_request( $key, $raw , $server);
                        $this->add_message( $replies);
                    }
                }
            }
          }

    } // end if server

    $ajax_response = new AjaxResponse();
    $trigger  = $form_state->getTriggeringElement();

    $class_messages = $this->get_messages() ;

    $message_text = 'Messages: ' .  implode(',  ' , $class_messages ) ;

    $ajax_response->addCommand(new HtmlCommand('.ajax-message', $message_text) );    

    return $ajax_response;

  }



//@return array
public function send_post_request($action, $data = NULL, $server = NULL) {

  $this->add_message( 'Calling remote server for ' . $action);

  $user='';
  $pass='';

  $server_info = $this->get_servers();
  foreach ($server_info as $si) {
    if ($server == $si['url'] ) {
      $user = $si['user'];
      $pass = $si['pass'];
      break;
    }
  }

  $url_action = $action;
  $token = $this->get_token($server);

  if (! isset($token) ) $token = $this->collect_rest_token($server) ;

  $payload = '';
  $serialized = '';

  if ( ($action == 'check') || ($action == 'enable') ) {

    $payload = array( $action => $data );
    $serialized = json_encode( $payload ) ;

  } elseif ($action == 'cache') {
    $url_action = 'cache';
    $payload = array('clear'=>'cache');
    $serialized = json_encode( $payload ) ;
  } else {

    $url_action = 'settings';
    $payload = array( $action => $data );
    $serialized = json_encode( $payload );
  }

  $response = NULL;

        $path = '/api/settings?_format=json&_task=' . $url_action;

        try {

          $client = new Client([
              'base_uri' => $server,
              'cookies' => true,
          ]);


            $promise = $client->requestAsync('POST', $path, [

                'auth' => [$user, $pass],
                'body' => $serialized,
                'headers' => [
                'Basic' => 'cmVsYXhlZDpob2xsYW5kMTIz',
                'Accept' => 'json',
                'Content-Type' => 'application/json',
                'X-CSRF-Token' => $token,
                ],



            ]);

            $local_messages = array();

            $promise->then(
                function (ResponseInterface $res) {
                  $msg = "Response: " . $res->getBody()->getContents() . " [" . $res->getStatusCode() . "] . " ;
                  $this->add_message($msg);
                },
                function (RequestException $e) {
                    $this->add_message($e->getMessage() );
                    $this->add_message($e->getRequest()->getMethod());
                }
            );

            $promise->wait();

        } catch (ClientException $e) {
                    $this->add_message($e->getMessage() );
                    $this->add_message($e->getRequest()->getMethod());
        }

  return ;

  }




  public function collect_rest_token($server = NULL) {

        $response = \Drupal::httpClient()
          ->get( $server . '/rest/session/token', array(
            )
          );

        if ($response && ($response->getStatusCode() == 200 ) ) {
          $t = $response->getBody()->getContents() ;
          $this->add_message('Token collected for ' . $server);
          $this->set_token($t, $server);
          return $t;
        } else {
          $code = $response->getStatusCode() ;
          $phrase = $response->getReasonPhrase() ;
          $this->add_message('Could not get REST Token for ' . $server);
          $this->add_message( $code .  ' POSTSettings Form Rest Token Error: ' . $phrase );

          return FALSE;

        }

  }




}



<?php

namespace Drupal\shp_settings_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig ;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request ;
use Drupal\Core\Extension\MissingDependencyException ;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "shp_settings_rest_resource",
 *   label = @Translation("Settings rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/settings",
 *     "https://www.drupal.org/link-relations/create" = "/api/settings"
 *   },
 *   serialization_class = "Drupal\shp_settings_rest\Normalizer\JsonDenormalizer"
 * )
 */
class SettingsRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('shp_settings_rest'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to a POST request.
   *
   * @param array $data
   *   An array with the payload.
   *
   * @return \Drupal\rest\ResourceResponse
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($settings_data = NULL, $settings_request = NULL) {

    $response_messages = array();

    $request_parameters = array();
    $query_string = $settings_request->getQueryString();
    $query_pairs = explode('&' , $query_string ) ;
    foreach ($query_pairs as $query_pair) {
      $pair = explode('=', $query_pair);
      $request_parameters[$pair[0]] = $pair[1] ;
    }

    $task = $request_parameters['_task'];
    $keys = is_array($settings_data) ? array_keys($settings_data) : array();

    switch ($task) {
      case 'check':
        // check for modules enabled
            $not_enabled = array();
            $module_string =  str_replace(' ', '', $settings_data[ $task ]  );
            $mods = explode(',' , $module_string);
            foreach ($mods as $mod) {
                if (! \Drupal::moduleHandler()->moduleExists($mod) ) $not_enabled[] = $mod;
            }

            if ( count($not_enabled) ) {
              $response_messages[] = "Modules not enabled: " . implode(', ' , $not_enabled);
            } else {
              $response_messages[] = 'All modules enabled';
            }

        break;
      case 'enable':
        // Install modules
            $module_string = str_replace(' ', '', $settings_data[ $task ]  ) ;
            $mods = explode(',' , $module_string);
            try {
              $bool = \Drupal::service('module_installer')->install($mods, true);

              if ($bool) {
                  $response_messages[] = 'All modules now enabled.';
              } else {
                  $response_messages[] = 'Modules could not be enabled. Check log on destination server.';
              }
            } catch (MissingDependencyException $e) {
                  $response_messages[] = 'Modules could not be enabled because of dependency issues: ' . $e->getMessaage();
            } catch (Exception $e) {
                  $response_messages[] = 'Modules could not be enabled because of issues: ' . $e->getMessaage();
            }


        break;
      case 'cache':
        $response_messages[] = 'Clearing cache.';
        drupal_flush_all_caches() ;
        break;
      case 'dependencies':
        //handled by settings instead

      case 'settings':
        // only doing 1/time
        $key = $keys[0];

          if (! $key) {
              $response_messages[] = ' No config name  ' ;
              break;
          }

          $conf = \Drupal::configFactory()
                  ->getEditable($key);
          $values = $settings_data[$key]  ;

          if (! $values) {
              $response_messages[] = ' No config data for : ' . $key ;
              break;
              return;
          }

          $response_messages[] = ' Applying settings for: ' . $key ;

          try {
            $conf->setData($values);
            $save = $conf->save();
            $response_messages[] = ' Settings saved ' ;
          } catch (Exception $e) {
            \Drupal::logger('hs_settings_copy')->notice( $key . ' not updated, error ' . $e->getMessage() );
          }

        break;
      default:
        // Shouldn't see this.
        $response_messages[] = 'Unknown task.'; 
    } // end switch
   

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
  //    throw new AccessDeniedHttpException();
    }

    $status =  200;
    $headers = array();

    $response = new ResourceResponse( implode(' ;; ' , $response_messages) , $status , $headers );
  //  $response->addCacheableDependency($account);
    return $response;

  }

  /**
   * Implements ResourceInterface::permissions().
   *
   * Every plugin operation method gets its own user permission. Example:
   * "restful delete entity:node" with the title "Access DELETE on Node
   * resource".
   */
  public function permissions() {
    $permissions = array();
    $definition = $this->getPluginDefinition();
    foreach ($this->availableMethods() as $method) {
      $lowered_method = strtolower($method);
      $permissions["restful $lowered_method $this->pluginId"] = array(
        'title' => $this->t('Access @method on %label . Devops give to replicator.', array('@method' => $method, '%label' => $definition['label'])),
      );
    }
    return $permissions;
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $collection = new RouteCollection();

    $definition = $this->getPluginDefinition();
    $canonical_path = isset($definition['uri_paths']['canonical']) ? $definition['uri_paths']['canonical'] : '/' . strtr($this->pluginId, ':', '/') . '/{id}';
    $create_path = isset($definition['uri_paths']['https://www.drupal.org/link-relations/create']) ? $definition['uri_paths']['https://www.drupal.org/link-relations/create'] : '/' . strtr($this->pluginId, ':', '/');

    $route_name = strtr($this->pluginId, ':', '.');

    $methods = $this->availableMethods();
    foreach ($methods as $method) {
      $route = $this->getBaseRoute($canonical_path, $method);

      switch ($method) {
        case 'POST':
          $route->setPath($create_path);
          // Restrict the incoming HTTP Content-type header to the known
          // serialization formats.
          $route->addRequirements(array('_content_type_format' => implode('|', $this->serializerFormats)));
          $collection->add("$route_name.$method", $route);
          break;

        default:
          $collection->add("$route_name.$method", $route);
          break;
      }
    }

    return $collection;
  }

  /**
   * Provides predefined HTTP request methods.
   *
   * Plugins can override this method to provide additional custom request
   * methods.
   *
   * @return array
   *   The list of allowed HTTP request method strings.
   */
  protected function requestMethods() {
    return array(
      'POST',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function availableMethods() {
    $methods = $this->requestMethods();
    $available = array();
    foreach ($methods as $method) {
      // Only expose methods where the HTTP request method exists on the plugin.
      if (method_exists($this, strtolower($method))) {
        $available[] = $method;
      }
    }
    return $available;
  }

  /**
   * Setups the base route for all HTTP methods.
   *
   * @param string $canonical_path
   *   The canonical path for the resource.
   * @param string $method
   *   The HTTP method to be used for the route.
   *
   * @return \Symfony\Component\Routing\Route
   *   The created base route.
   */
  protected function getBaseRoute($canonical_path, $method) {
    $lower_method = strtolower($method);

    $route = new Route($canonical_path, array(
      '_controller' => 'Drupal\rest\RequestHandler::handle',
      // Pass the resource plugin ID along as default property.
      '_plugin' => $this->pluginId,
    ), array(
      '_permission' => "restful $lower_method $this->pluginId",
    ),
      array(),
      '',
      array(),
      // The HTTP method is a requirement for this route.
      array($method)
    );
    return $route;
  }

}

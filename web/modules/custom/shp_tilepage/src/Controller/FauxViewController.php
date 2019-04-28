<?php

namespace Drupal\shp_tilepage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\language\ConfigurableLanguageManager;
use Drupal\views\ViewsData;
use Drupal\views\EventSubscriber\RouteSubscriber;
use Drupal\views\ViewExecutableFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Config\ConfigManager;
use Drupal\content_translation\ContentTranslationManager;

/**
 * Class FauxViewController.
 *
 * @package Drupal\shp_tilepage\Controller
 */
class FauxViewController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityManager definition.
   *
   * @var Drupal\Core\Entity\EntityManager
   */
  protected $entity_manager;

  /**
   * Drupal\language\ConfigurableLanguageManager definition.
   *
   * @var Drupal\language\ConfigurableLanguageManager
   */
  protected $language_manager;

  /**
   * Drupal\views\ViewsData definition.
   *
   * @var Drupal\views\ViewsData
   */
  protected $views_views_data;

  /**
   * Drupal\views\EventSubscriber\RouteSubscriber definition.
   *
   * @var Drupal\views\EventSubscriber\RouteSubscriber
   */
  protected $views_route_subscriber;

  /**
   * Drupal\views\ViewExecutableFactory definition.
   *
   * @var Drupal\views\ViewExecutableFactory
   */
  protected $views_executable;

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entity_type_manager;

  /**
   * Drupal\Core\Config\ConfigManager definition.
   *
   * @var Drupal\Core\Config\ConfigManager
   */
  protected $config_manager;

  /**
   * Drupal\content_translation\ContentTranslationManager definition.
   *
   * @var Drupal\content_translation\ContentTranslationManager
   */
  protected $content_translation_manager;
  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManager $entity_manager, ConfigurableLanguageManager $language_manager, ViewsData $views_views_data, RouteSubscriber $views_route_subscriber, ViewExecutableFactory $views_executable, EntityTypeManager $entity_type_manager, ConfigManager $config_manager, ContentTranslationManager $content_translation_manager) {
    $this->entity_manager = $entity_manager;
    $this->language_manager = $language_manager;
    $this->views_views_data = $views_views_data;
    $this->views_route_subscriber = $views_route_subscriber;
    $this->views_executable = $views_executable;
    $this->entity_type_manager = $entity_type_manager;
    $this->config_manager = $config_manager;
    $this->content_translation_manager = $content_translation_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('views.views_data'),
      $container->get('views.route_subscriber'),
      $container->get('views.executable'),
      $container->get('entity_type.manager'),
      $container->get('config.manager'),
      $container->get('content_translation.manager')
    );
  }

  public $languages;

  public $module_name='shp_tilepage';

  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function main($sectionPath, $nodeTitle, $langCode) {

//      $stuff = $this->getLanguages($langcode);
      $stuff = $this->getLanguages();



      return ['#markup' => 'just some text and stuff',];


    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: hello with parameter(s): $sectionPath, $nodeTitle, $langCode'),
    ];
  }

  public function getLanguages($cc = 'US') {

    $man = $this->language_manager;
    $module = $this->module_name;

    $langs = $this->entity_type_manager->getStorage('configurable_language')->loadByProperties([]);

    if (!isset($langs[$cc] ) ) {
        die($cc . 'is not a country code');
    }


    foreach ($langs as $lang) {
      $tps = $lang->getThirdPartySettings($module);



    }



//    kint($man);

    kint($man->getLanguages(1) );

    $items = \Drupal::entityTypeManager()->getStorage('configurable_language')->loadByProperties([]);

    kint($items);

//    $all = $this->language_manager->loadByProperties( [] );

  //  kint($all);

    die('lm');

  }

}

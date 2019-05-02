<?php

namespace Drupal\views_redirect_form\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Utility\Token;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ViewsField("views_redirect_form__compare_from")
 */
class RedirectCompareFrom extends RedirectComparePluginBase {

  use RedirectDestinationTrait;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);

    $this->moduleHandler = $module_handler;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['label']['default'] = t('From');
    $options['route_name'] = ['default' => ''];
    $options['route_parameters'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['route_name'] = [
      '#type' => 'textfield',
      '#title' => t('Route name'),
      '#default_value' => $this->options['route_name'],
    ];

    $form['route_parameters'] = [
      '#type' => 'textfield',
      '#title' => t('Route parameters'),
      '#maxlength' => 255,
      '#default_value' => $this->options['route_parameters'],
      '#description' => t('Route parameters format: key|[token],key2|[token]. You can use from and to as prefix for the tokens'),
    ];

    if ($this->moduleHandler->moduleExists('token')) {
      $form['token_help'] = [
        '#theme' => 'token_tree',
        '#token_types' => [$this->getEntityType()],
        '#prefix' => '<h5>' . $this->t('Available tokens') . '</h5>',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewsForm(&$form, FormStateInterface $form_state) {
    // Replace the form submit button label.
    $form['actions']['submit']['#value'] = $this->t('Compare');

    if (!empty($this->view->result)) {
      $form[$this->options['id']]['#tree'] = TRUE;
      foreach ($this->view->result as $row_index => $row) {
        $entity = $row->_entity;
        $form[$this->options['id']][$row_index] = [
          '#type' => 'radio',
          '#parents' => [$this->options['id']],
          '#title' => $this->t('Compare this item'),
          '#title_display' => 'invisible',
          '#return_value' => $this->calculateEntityFormKey($entity),
        ];
      }
    }
  }

  protected function getToFieldId() {
    $to_fields = array_filter($this->view->field, function (FieldPluginBase $field) {
      return $field instanceof RedirectCompareTo;
    });
    return array_keys($to_fields)[0];
  }

  /**
   * Submit handler for the bulk form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the user tried to access an action without access to it.
   */
  public function viewsFormSubmit(&$form, FormStateInterface $form_state) {
    if ($form_state->get('step') == 'views_form_views_form') {
      $from = $form_state->getValue($this->options['id']);
      $from_entity = $this->loadEntityFromFormKey($from);

      $to = $form_state->getValue($this->getToFieldId());
      $to_entity = $this->loadEntityFromFormKey($to);

      $options = array(
        'query' => $this->getDestinationArray(),
      );

      $route_name = $this->options['route_name'];
      $route_parameters = $this->getRedirectRouteParameters($from_entity, $to_entity);

      $form_state->setRedirect($route_name, $route_parameters, $options);
    }
  }

  /**
   * Generates the needed route parameters for the redirect.
   *
   * The configured route parameters looks like:
   * @code
   *   from_vid|[from_node:vid],to_vid[to_node:vid],node|[from_node:id]
   * @endcode
   *
   * @param \Drupal\Core\Entity\EntityInterface $from_entity
   *   The entity which got selected in the from field.
   * @param \Drupal\Core\Entity\EntityInterface $to_entity
   *   The entity which got selected in the to field.
   *
   * @return array
   *   The determined route parameters, key by parameter name.
   */
  protected function getRedirectRouteParameters(EntityInterface $from_entity, EntityInterface $to_entity) {
    $resolved_parameters = [];
    $parameters = explode(',', $this->options['route_parameters']);
    foreach ($parameters as $configuration) {
      list($parameter_name, $parameter_token_value) = array_map('trim',explode('|', $configuration));
      if (strpos($parameter_token_value, '[from_') === 0) {
        $parameter_token_value = str_replace('[from_', '[', $parameter_token_value);
        $value = $this->token->replace($parameter_token_value, [$this->getEntityType() => $from_entity]);
      }
      elseif (strpos($parameter_token_value, '[to_') === 0) {
        $parameter_token_value = str_replace('[to_', '[', $parameter_token_value);
        $value = $this->token->replace($parameter_token_value, [$this->getEntityType() => $to_entity]);
      }
      else {
        $value = $this->token->replace($parameter_token_value);
      }

      $resolved_parameters[$parameter_name] = $value;
    }

    return $resolved_parameters;
  }


}

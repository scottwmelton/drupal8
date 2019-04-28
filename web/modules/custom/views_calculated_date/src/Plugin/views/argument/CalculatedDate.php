<?php
/**
 * @file
 * Contains \Drupal\views_calculated_date\Plugin\views\argument\CalculatedDate.
 */

namespace Drupal\views_calculated_date\Plugin\views\argument;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\views\FieldAPIHandlerTrait;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use \Drupal\field\FieldStorageConfigInterface;

/**
 * Abstract argument handler for dates.
 *
 * Adds an option to set a default argument based on the current date.
 *
 * Definitions terms:
 * - many to one: If true, the "many to one" helper will be used.
 *
 * @see \Drupal\views\ManyTonOneHelper
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("datetime_calculated")
 */
class CalculatedDate extends NumericArgument {

  use FieldAPIHandlerTrait;

  // Flag to allow the use of date offsets for default arguments.
  public $canUseOffsetDate = TRUE;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Date format for SQL conversion.
   *
   * @var string
   *
   * @see \Drupal\views\Plugin\views\query\Sql::getDateFormat()
   */
  protected $dateFormat = DATETIME_DATETIME_STORAGE_FORMAT;

  /**
   * The request stack used to determin current time.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new Date handler.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @param \Symfony\Component\HttpFoundation\RequestStack
   *   The request stack used to determine the current time.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatter $date_formatter, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $date_formatter;
    $this->requestStack = $request_stack;

    // Date format depends on field storage format.
    $definition = $this->getFieldStorageDefinition();
    if ($definition instanceof FieldStorageConfigInterface && $definition->getSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      $this->dateFormat = DATETIME_DATE_STORAGE_FORMAT;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDateField() {
    // Return the real field, since it is already in string format.
    return "$this->tableAlias.$this->realField";
  }

  /**
   * {@inheritdoc}
   */
  public function getDateFormat($format) {
    // Pass in the string-field option.
    return $this->query->getDateFormat($this->getDateField(), $format, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['type']['default'] = 'date';
    $options['operator']['default'] = '=';

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Unset unusable options.
    unset($form['default_argument_type']['#options']['node']);
    unset($form['default_argument_type']['#options']['taxonomy_tid']);
    unset($form['default_argument_type']['#options']['current_user']);
    unset($form['default_argument_type']['#options']['user']);
    unset($form['argument_default']['node']);
    unset($form['argument_default']['taxonomy_tid']);
    unset($form['argument_default']['current_user']);
    unset($form['argument_default']['user']);

    // Unset the default numeric argument options.
    unset($form['break_phrase']);
    unset($form['not']);

    $form['argument_default']['fixed']['argument']['#description'] = $this->t('A date in any machine readable format. CCYY-MM-DD HH:MM:SS is preferred.');

    $this->operatorForm($form, $form_state);

    // Display the options under the more details.
    $form['operator']['#fieldset'] = 'more';
    // Make sure the more fieldset is rendered.
    $form['more']['#optional'] = FALSE;
    $form['more']['#open'] = TRUE;

    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Value type'),
      '#options' => [
        'date' => $this->t('A date in any machine readable format. CCYY-MM-DD_HH:MM:SS is preferred.'),
        'offset' => $this->t('An offset from the current time such as "!example1" or "!example2"',
          ['!example1' => '+1 day', '!example2' => '-2 hours -30 minutes']),
      ],
      '#default_value' => !empty($this->options['type']) ? $this->options['type'] : 'date',
      '#fieldset' => 'more',
    ];
  }

  function title() {
    $title = parent::title();
    $this->operator = $this->options['operator'] ?: '=';
    $this->value = $this->argument ?: '';

    return $title;
  }

  public function query($group_by = FALSE) {
    $this->ensureMyTable();
    $field = "$this->tableAlias.$this->realField";

    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }


  /**
   * Options form subform for setting the operator.
   *
   * This may be overridden by child classes, and it must
   * define $form['operator'];
   *
   * @see buildOptionsForm()
   */
  protected function operatorForm(&$form, FormStateInterface $form_state) {
    $options = $this->operatorOptions();
    if (!empty($options)) {
      $form['operator'] = array(
        '#type' => count($options) < 10 ? 'radios' : 'select',
        '#title' => $this->t('Operator'),
        '#default_value' => !empty($this->options['operator']) ? $this->options['operator'] : '=',
        '#options' => $options,
      );
    }
  }

  function operators() {
    $operators = array(
      '<' => array(
        'title' => $this->t('Is less than'),
        'method' => 'opSimple',
        'short' => $this->t('<'),
        'values' => 1,
      ),
      '<=' => array(
        'title' => $this->t('Is less than or equal to'),
        'method' => 'opSimple',
        'short' => $this->t('<='),
        'values' => 1,
      ),
      '=' => array(
        'title' => $this->t('Is equal to'),
        'method' => 'opSimple',
        'short' => $this->t('='),
        'values' => 1,
      ),
      '!=' => array(
        'title' => $this->t('Is not equal to'),
        'method' => 'opSimple',
        'short' => $this->t('!='),
        'values' => 1,
      ),
      '>=' => array(
        'title' => $this->t('Is greater than or equal to'),
        'method' => 'opSimple',
        'short' => $this->t('>='),
        'values' => 1,
      ),
      '>' => array(
        'title' => $this->t('Is greater than'),
        'method' => 'opSimple',
        'short' => $this->t('>'),
        'values' => 1,
      ),
      'regular_expression' => array(
        'title' => $this->t('Regular expression'),
        'short' => $this->t('regex'),
        'method' => 'op_regex',
        'values' => 1,
      ),
    );

    // if the definition allows for the empty operator, add it.
    if (!empty($this->definition['allow empty'])) {
      $operators += array(
        'empty' => array(
          'title' => $this->t('Is empty (NULL)'),
          'method' => 'opEmpty',
          'short' => $this->t('empty'),
          'values' => 0,
        ),
        'not empty' => array(
          'title' => $this->t('Is not empty (NOT NULL)'),
          'method' => 'opEmpty',
          'short' => $this->t('not empty'),
          'values' => 0,
        ),
      );
    }

    return $operators;
  }

  /**
   * Provide a list of all the numeric operators
   */
  public function operatorOptions($which = 'title') {
    $options = array();
    foreach ($this->operators() as $id => $info) {
      $options[$id] = $info[$which];
    }

    return $options;
  }

  protected function operatorValues($values = 1) {
    $options = array();
    foreach ($this->operators() as $id => $info) {
      if ($info['values'] == $values) {
        $options[] = $id;
      }
    }

    return $options;
  }

  /**
   * Override parent method, which deals with dates as integers.
   */
  protected function opSimple($field) {
    $origin =  (!empty($this->options['type']) && $this->options['type'] == 'offset') ? $this->requestStack->getCurrentRequest()->server->get('REQUEST_TIME') : 0;
    $value = intval(strtotime($this->argument, $origin));

    // Convert to ISO. UTC is used since dates are stored in UTC.
    $value = $this->query->getDateFormat("'" . $this->dateFormatter->format($value, 'custom', DATETIME_DATETIME_STORAGE_FORMAT, DATETIME_STORAGE_TIMEZONE) . "'", $this->dateFormat, TRUE);

    // This is safe because we are manually scrubbing the value.
    $field = $this->query->getDateFormat($field, $this->dateFormat, TRUE);
    $this->query->addWhereExpression($this->options['group'], "$field $this->operator $value");
  }

  protected function opEmpty($field) {
    if ($this->operator == 'empty') {
      $operator = "IS NULL";
    }
    else {
      $operator = "IS NOT NULL";
    }

    $this->query->addWhere($this->options['group'], $field, NULL, $operator);
  }

  /**
   * Filters by a regular expression.
   *
   * @param string $field
   *   The expression pointing to the queries field, for example "foo.bar".
   */
  protected function opRegex($field) {
    $this->query->addWhere($this->options['group'], $field, $this->value, 'REGEXP');
  }
}

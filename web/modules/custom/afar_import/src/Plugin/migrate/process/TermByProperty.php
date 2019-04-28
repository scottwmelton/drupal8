<?php

/**
 * @file
 * Contains \Drupal\afar_import\Plugin\migrate\process\TermByProperty.
 */

namespace Drupal\afar_import\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\TermStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a process plugin that determines a entityref by term property.
 *
 * @MigrateProcessPlugin(
 *   id = "afar_import__term_by_property"
 * )
 */
class TermByProperty extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TermStorageInterface $term_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->termStorage = $term_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('taxonomy_term')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($this->configuration['vocabulary_id'])) {
      throw new \InvalidArgumentException('Missing vocabulary ID');
    }
    if (empty($this->configuration['property'])) {
      throw new \InvalidArgumentException('Missing property configuration');
    }

    $property = $this->configuration['property'];
    $terms = $this->termStorage->loadByProperties([$property => $value, 'vid' => $this->configuration['vocabulary_id']]);
    if (!$terms) {
      return;
    }
    /** @var \Drupal\taxonomy\TermInterface $term */
    $term = end($terms);
    return $term->id();
  }

}

<?php

/**
 * @file
 * Contains \Drupal\afar_import\Cron\AfarImportCron.
 */

namespace Drupal\afar_import\Cron;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_tools\MigrateExecutable;

/**
 * Imports all the migrations on cron run.
 */
class AfarImportCron {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Creates a new AfarImportCron instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  public function run() {
    $migration_storage = $this->entityManager->getStorage('migration');

    foreach (['afar_dc_place', 'afar_dc_port'] as $entity_id) {
      /** @var \Drupal\migrate\Entity\MigrationInterface $migration */
      $migration = $migration_storage->load($entity_id);
      $migration_executable = new MigrateExecutable($migration, new MigrateMessage());

      // Only proceed if the migrations is Idle
      if ( $migration->getStatusLabel() == 'Idle') {
         $migration_executable->import();
       } else {
        return FALSE;
       }

      // If Places still processing, do not go on to Ports
      if ( $migration->getStatusLabel() != 'Idle'  ) {
        return FALSE;
      }
    }
    return TRUE;
  }

}

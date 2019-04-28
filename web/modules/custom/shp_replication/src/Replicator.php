<?php

namespace Drupal\shp_replication;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;
use Drupal\replication\Entity\ReplicationLogInterface;
use Drupal\workspace\Entity\Replication;
use Drupal\workspace\ReplicatorInterface;
use Drupal\replication\ReplicationTask\ReplicationTask;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\deploy_individual\Form\IndividualPushConfirm;

/**
 * Replicates an enitity and all its referenced child entities.
 */
class Replicator extends IndividualPushConfirm {

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\workspace\ReplicatorInterface $replicator_manager
   *   The replicator manager.
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface $workspace_manager
   *   The workspace manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ReplicatorInterface $replicator_manager,
    WorkspaceManagerInterface $workspace_manager
    MultiversionManagerInterface $multiversion_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->replicatorManager = $replicator_manager;
    $this->workspaceManager = $workspace_manager;
    $this->multiversionManager = $multiversion_manager;
    $this->replicableEntities = $multiversion_manager->getSupportedEntityTypes();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('workspace.replicator_manager'),
      $container->get('workspace.manager')
      $container->get('multiversion.manager')
    );
  }

  /**
   * Deploys the entity.
   *
   * Based on \Drupal\deploy_individual\Form\IndividualPushConfirm::deploy().
   */
  public function shpDeploy(EntityInterface $entity) {
    // Get an referenced child entities and put node at top.
    $selected_entities = $this->getReplicableReferencedEntities($entity);
    array_unshift($selected_entities, $entity);

    $organized_uuids = [
      'node' => [
        $entity->uuid() => $entity->uuid(),
      ]
    ];
    dpm($organized_uuids);
    // Reload entities to put their label in the description of the
    // Replication.
    $prepared_entities = [];
    foreach ($organized_uuids as $entity_type => $uuids) {
      $entities = $this->entityTypeManager
        ->getStorage($entity_type)
        ->loadByProperties(array('uuid' => $uuids));

      foreach ($entities as $entity) {
        $prepared_entities[] = $entity;
      }
    }
    dpm($prepared_entities);

    $this->deploy($prepared_entities);
  }

}

<?php

namespace Drupal\deploy;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\workspace\Entity\Replication;

/**
 * Defines a class to build a listing of Replication entities.
 *
 * @ingroup workspace
 */
class ReplicationListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['replication_status'] = $this->t('Status');
    $header['name'] = $this->t('Title');
    $header['source'] = $this->t('Source');
    $header['target'] = $this->t('Target');
    $header['changed'] = $this->t('Updated');
    $header['created'] = $this->t('Created');
    $header['user'] = $this->t('User');
    $header['description'] = $this->t('Description');
    $header['operations'] = $this->t('Operations');
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $formatter = \Drupal::service('date.formatter');
    /* @var $entity \Drupal\workspace\Entity\Replication */
    $row['replication_status'] = $this->getReplicationStatusIcon($entity->getReplicationStatus(), $entity->id());
    $row['name'] = $entity->label();
    $row['source'] = $entity->get('source')->entity ? $entity->get('source')->entity->label() : $this->t('<em>Unknown</em>');
    $row['target'] = $entity->get('target')->entity ? $entity->get('target')->entity->label() : $this->t('<em>Unknown</em>');
    $user = User::load($entity->uid->target_id);
    $row['changed'] = $formatter->format($entity->getChangedTime());
    $row['created'] = $formatter->format($entity->getCreatedTime());
    $row['user'] = $user->getAccountName();
    $row['description'] = $entity->description->value;
    // Set operations.
    $links = [];
    if ($entity->hasLinkTemplate('delete-form')
      && in_array($entity->getReplicationStatus(), [Replication::REPLICATED, Replication::FAILED])) {
      $links['delete'] = [
        'title' => t('Delete'),
        'url' => $entity->toUrl('delete-form', ['absolute' => TRUE]),
      ];
    }
    $row[] = [
      'data' => [
        '#type' => 'operations',
        '#links' => $links,
      ],
    ];
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    // Determine when cron last ran.
    $cron_last = \Drupal::state()->get('system.cron_last');
    if (!is_numeric($cron_last)) {
      $cron_last = \Drupal::state()->get('install_time', 0);
    }

    $build = [];
    $build['#markup'] = '';
    if (\Drupal::state()->get('workspace.last_replication_failed', FALSE)) {
      $message = $this->generateMessageRenderArray('warning', $this->t('Creation of new deployments has been blocked due to a failure. Contact your administrator to get the issue resolved and deployments unblocked.'));
      $user_has_access = \Drupal::currentUser()->hasPermission('administer site configuration');
      if ($user_has_access) {
        $message = $this->generateMessageRenderArray('warning', $this->t('Creation of new deployments has been blocked due to a failure. After resolving the issue, go to the <a href="@url">replication settings</a> page to unblock deployments.', ['@url' => '/admin/config/replication/settings']));
      }
      elseif ($support_email = Settings::get('support_email_address', NULL)) {
        $message = $this->generateMessageRenderArray('warning', $this->t('Creating new deployments is not allowed at the moment. Please contact the <a href="mailto:@url">support team</a> to unblock creating new content deployments.', ['@url' => $support_email]));
      }
      $build['#markup'] .= \Drupal::service('renderer')->render($message);
    }

    $build['#markup'] .= $this->t('Last cron ran @time ago', ['@time' => \Drupal::service('date.formatter')->formatTimeDiffSince($cron_last)]);
    $build += parent::render();
    return $build;
  }

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->sort('changed', 'DESC');

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

  protected function getReplicationStatusIcon($status, $id) {
    $status = (int) $status;
    $icons = [
      Replication::QUEUED => $this->t('&#x231A Queued'),
      Replication::REPLICATING => $this->t('In progress'),
      Replication::REPLICATED => $this->t('&#10004; Done'),
    ];
    if ($status === Replication::FAILED) {
      $link_url = Url::fromUserInput('/admin/structure/deployment/' . $id . '/fail-info');
      $link_url->setOptions(array(
          'attributes' => array(
            'class' => array('use-ajax'),
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode(array(
              'width' => 700,
            )),
          ))
      );
      $icons[Replication::FAILED] = Link::fromTextAndUrl($this->t('&#10006; Failed'), $link_url);
    }
    /** @var Replication $entity */
    $entity = $this->getStorage()->load($id);
    if ($status === Replication::QUEUED && !empty($entity->getReplicationFailInfo())) {
      $link_url = Url::fromUserInput('/admin/structure/deployment/' . $id . '/requeue-info');
      $link_url->setOptions(array(
          'attributes' => array(
            'class' => array('use-ajax'),
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode(array(
              'width' => 700,
            )),
          ))
      );
      $icons[Replication::QUEUED] = Link::fromTextAndUrl($this->t('&#x231A Queued'), $link_url);
    }
    return $icons[$status];
  }

  /**
   * Generate a message render array with the given text.
   *
   * @param string $type
   *   The type of message: status, warning, or error.
   * @param string $message
   *   The message to create with.
   *
   * @return array
   *   The render array for a status message.
   *
   * @see \Drupal\Core\Render\Element\StatusMessages
   */
  protected function generateMessageRenderArray($type, $message) {
    return [
      '#theme' => 'status_messages',
      '#message_list' => [
        $type => [Markup::create($message)],
      ],
    ];
  }

}

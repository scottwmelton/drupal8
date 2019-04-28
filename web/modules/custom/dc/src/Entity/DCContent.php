<?php

/**
 * @file
 *   Contains Drupal\dc\Entity\DCContent.
 */

namespace Drupal\dc\Entity;

use Drupal\content_entity_base\Entity\EntityBase;
use Drupal\content_entity_base\Entity\TimestampedRevisionInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationWrapper;
use Drupal\dc\DCContentInterface;

/**
 * Defines a custom entity class.
 *
 * @ContentEntityType(
 *   id                      = "dc_content",
 *   label                   = @Translation("Destination Central content"),
 *   bundle_label            = @Translation("Destination Central content type"),
 *   base_table              = "dc_content",
 *   revision_table          = "dc_content_revision",
 *   revision_data_table     = "dc_content_field_revision",
 *   data_table              = "dc_content_field_data",
 *   translatable            = TRUE,
 *   admin_permission        = "administer dc_content",
 *   bundle_entity_type      = "dc_content_type",
 *   field_ui_base_route     = "entity.dc_content_type.edit_form",
 *   common_reference_target = TRUE,
 *   permission_granularity  = "bundle",
 *   render_cache            = TRUE,
 *   handlers = {
 *     "storage"      = "\Drupal\content_entity_base\Entity\Storage\ContentEntityBaseStorage",
 *     "access"       = "\Drupal\dc\Entity\Access\DCContentAccessControlHandler",
 *     "translation"  = "\Drupal\content_translation\ContentTranslationHandler",
 *     "list_builder" = "\Drupal\content_entity_base\Entity\Listing\EntityBaseListBuilder",
 *     "view_builder" = "\Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data"   = "\Drupal\dc\Entity\Views\DCContentViewsData",
 *     "form" = {
 *       "add"        = "\Drupal\content_entity_base\Entity\Form\EntityBaseForm",
 *       "edit"       = "\Drupal\content_entity_base\Entity\Form\EntityBaseForm",
 *       "default"    = "\Drupal\content_entity_base\Entity\Form\EntityBaseForm",
 *       "delete"     = "\Drupal\content_entity_base\Entity\Form\EntityBaseDeleteForm",
 *     },
 *     "route_provider" = {
 *       "revision" = "\Drupal\dc\Entity\Routing\DcRevisionRouteProvider",
 *     },
 *   },
 *   entity_keys = {
 *     "id"               = "id",
 *     "bundle"           = "type",
 *     "status"           = "status",
 *     "label"            = "name",
 *     "langcode"         = "langcode",
 *     "default_langcode" = "default_langcode",
 *     "uuid"             = "uuid",
 *     "revision"         = "revision_id",
 *   },
 *   links = {
 *     "collection"   = "/admin/dc_content/",
 *     "canonical"    = "/admin/dc_content/{dc_content}",
 *     "delete-form"  = "/admin/dc_content/{dc_content}/delete",
 *     "edit-form"    = "/admin/dc_content/{dc_content}/edit",
 *     "version-history" = "/admin/dc_content/{dc_content}/revisions",
 *     "revision" = "/admin/dc_content/{dc_content}/revisions/{dc_content_revision}/view",
 *     "revision-revert" = "/admin/dc_content/{dc_content}/revisions/{dc_content_revision}/revert",
 *     "revision-delete" = "/admin/dc_content/{dc_content}/revisions/{dc_content_revision}/delete",
 *     "toggle-status" = "/admin/dc_content/{dc_content}/toggle-status",
 *   },
 * )
 */
class DCContent extends EntityBase implements DCContentInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Get fields from content entity base.
    $fields = parent::baseFieldDefinitions($entity_type);

    // Change the name label to Title.
    $fields['name']->setLabel(new TranslationWrapper('Title'));

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Revision timestamp'))
      ->setDescription(new TranslatableMarkup('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setDefaultValue(0)
      ->setRevisionable(TRUE);

    // @see dc_update_8004()
    $fields['afar_edit_request'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Pending changes'))
      ->setDescription(t('Time of outstanding Afar edit request.'))
      ->setDefaultValue(NULL)
      ->setReadOnly(TRUE)
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE);

    // @see dc_update_8004()
    $fields['afar_status'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Afar import status'))
      ->setDescription(t('New/revised status of entity data from Afar.'))
      ->setDefaultValue(NULL)
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('display', [
        'type' => 'afar_status',
      ]);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    $created = $this->revision_timestamp->value;
    // Make sure to return a numeric value for DateTimePlus::createFromTimestamp.
    return is_numeric($created) ? $created : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->set('revision_uid', $uid);
    return $this;
  }

}

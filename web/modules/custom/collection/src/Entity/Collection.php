<?php

/**
 * @file
 * Contains Drupal\collection\Entity\Collection.
 */

namespace Drupal\collection\Entity;

use Drupal\content_entity_base\Entity\EntityBase;

/**
 * Defines a custom entity class.
 *
 * @ContentEntityType(
 *   id                      = "collection",
 *   label                   = @Translation("Collection"),
 *   bundle_label            = @Translation("Collection type"),
 *   base_table              = "collection",
 *   revision_table          = "collection_revision",
 *   data_table              = "collection_field_data",
 *   revision_data_table     = "collection_field_revision",
 *   translatable            = TRUE,
 *   admin_permission        = "administer collection",
 *   bundle_entity_type      = "collection_type",
 *   field_ui_base_route     = "entity.collection_type.edit_form",
 *   common_reference_target = TRUE,
 *   permission_granularity  = "bundle",
 *   render_cache            = TRUE,
 *   handlers = {
 *     "storage"      = "\Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "access"       = "\Drupal\content_entity_base\Entity\Access\EntityBaseAccessControlHandler",
 *     "translation"  = "\Drupal\content_translation\ContentTranslationHandler",
 *     "list_builder" = "\Drupal\content_entity_base\Entity\Listing\EntityBaseListBuilder",
 *     "view_builder" = "\Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data"   = "\Drupal\collection\Entity\Views\CollectionViewsData",
 *     "form" = {
 *       "add"        = "\Drupal\content_entity_base\Entity\Form\EntityBaseForm",
 *       "edit"       = "\Drupal\content_entity_base\Entity\Form\EntityBaseForm",
 *       "default"    = "\Drupal\content_entity_base\Entity\Form\EntityBaseForm",
 *       "delete"     = "\Drupal\content_entity_base\Entity\Form\EntityBaseDeleteForm",
 *     },
 *   },
 *   entity_keys = {
 *     "id"           = "id",
 *     "bundle"       = "type",
 *     "label"        = "name",
 *     "langcode"     = "langcode",
 *     "uuid"         = "uuid",
 *     "revision"     = "revision_id",
 *   },
 *   links = {
 *     "collection"   = "/admin/collection/",
 *     "canonical"    = "/admin/collection/{collection}",
 *     "delete-form"  = "/admin/collection/{collection}/delete",
 *     "edit-form"    = "/admin/collection/{collection}/edit",
 *   },
 * )
 */
class Collection extends EntityBase {
}

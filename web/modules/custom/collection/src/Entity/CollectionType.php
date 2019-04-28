<?php

/**
 * @file
 * Contains Drupal\collection\Entity\CollectionType.
 */

namespace Drupal\collection\Entity;

use Drupal\content_entity_base\Entity\EntityTypeBase;

/**
 * Defines the collection type configuration entity.
 *
 * @ConfigEntityType(
 *   id               = "collection_type",
 *   label            = @Translation("Collection type"),
 *   admin_permission = "administer collection",
 *   config_prefix    = "collection_type",
 *   bundle_of        = "collection",
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\content_entity_base\Entity\Form\EntityTypeBaseForm",
 *       "add"     = "Drupal\content_entity_base\Entity\Form\EntityTypeBaseForm",
 *       "edit"    = "Drupal\content_entity_base\Entity\Form\EntityTypeBaseForm",
 *       "delete"  = "Drupal\content_entity_base\Entity\Form\EntityTypeBaseDeleteForm",
 *     },
 *     "list_builder" = "Drupal\content_entity_base\Entity\Listing\EntityTypeBaseListBuilder",
 *   },
 *   entity_keys = {
 *     "id"           = "id",
 *     "label"        = "label",
 *   },
 *   links = {
 *     "edit-form"    = "/admin/structure/collections/manage/{collection_type}",
 *     "delete-form"  = "/admin/structure/collections/manage/{collection_type}/delete",
 *     "collection"   = "/admin/structure/collections",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "revision",
 *     "description",
 *   }
 * )
 */
class CollectionType extends EntityTypeBase {
}

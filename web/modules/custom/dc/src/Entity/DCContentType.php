<?php

/**
 * @file
 *   Contains Drupal\dc\Entity\DCContentType.
 */

namespace Drupal\dc\Entity;

use Drupal\content_entity_base\Entity\EntityTypeBase;

/**
 * Defines the dc_content type configuration entity.
 *
 * @ConfigEntityType(
 *   id               = "dc_content_type",
 *   label            = @Translation("Destination Central content type"),
 *   admin_permission = "administer dc_content",
 *   config_prefix    = "content_type",
 *   bundle_of        = "dc_content",
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
 *     "edit-form"    = "/admin/structure/dc_content/manage/{dc_content_type}",
 *     "delete-form"  = "/admin/structure/dc_content/manage/{dc_content_type}/delete",
 *     "collection"   = "/admin/structure/dc_content",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "revision",
 *     "description",
 *   }
 * )
 */
class DCContentType extends EntityTypeBase {
}

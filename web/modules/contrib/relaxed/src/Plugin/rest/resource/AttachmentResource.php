<?php

namespace Drupal\relaxed\Plugin\rest\resource;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\file\FileInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @RestResource(
 *   id = "relaxed:attachment",
 *   label = "Attachment",
 *   serialization_class = {
 *     "canonical" = "Drupal\file\Entity\File",
 *   },
 *   uri_paths = {
 *     "canonical" = "/{db}/{docid}/{field_name}/{delta}/{file_uuid}/{scheme}/{filename}",
 *   },
 *   uri_parameters = {
 *     "canonical" = {
 *       "file_uuid" = "entity_uuid:file",
 *     }
 *   }
 * )
 *
 * @todo {@link https://www.drupal.org/node/2600428 Implement real ETag.}
 */
class AttachmentResource extends ResourceBase {

  /**
   * @param string | \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   * @param string | \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param string $field_name
   * @param integer $delta
   * @param string | \Drupal\file\FileInterface $file
   * @param string $scheme
   * @param string $filename
   * @return ResourceResponse
   */
  public function head($workspace, $entity, $field_name, $delta, $file, $scheme, $filename) {
    $this->checkWorkspaceExists($workspace);
    if (!$entity instanceof ContentEntityInterface
      || !$file instanceof FileInterface) {
      throw new NotFoundHttpException(t('Specified document or attachment was not found.'));
    }

    if (!$entity->access('view') || !$entity->{$field_name}->access('view')) {
      throw new AccessDeniedHttpException();
    }
    return new ResourceResponse(NULL, 200, $this->responseHeaders($file, ['Content-Type', 'Content-Length', 'Content-MD5', 'X-Relaxed-ETag']));
  }

  /**
   * @param string | \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   * @param string | \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param integer $delta
   * @param string | \Drupal\file\FileInterface $file
   * @param string $scheme
   * @param string $filename
   * @return ResourceResponse
   */
  public function get($workspace, $entity, $field_name, $delta, $file, $scheme, $filename) {
    $this->checkWorkspaceExists($workspace);
    if (!$entity instanceof ContentEntityInterface
      || !$file instanceof FileInterface) {
      throw new NotFoundHttpException(t('Specified document or attachment was not found.'));
    }

    if (!$entity->access('view') || !$entity->{$field_name}->access('view')) {
      throw new AccessDeniedHttpException();
    }
    return new ResourceResponse($file, 200, $this->responseHeaders($file, ['Content-Type', 'Content-Length', 'Content-MD5', 'X-Relaxed-ETag']));
  }

  /**
   * @param string | \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   * @param string | \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param integer $delta
   * @param string | \Drupal\file\FileInterface $existing_file
   * @param string $scheme
   * @param string $filename
   * @param \Drupal\file\FileInterface $received_file
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   */
  public function put($workspace, $entity, $field_name, $delta, $existing_file, $scheme, $filename, FileInterface $received_file) {
    $this->checkWorkspaceExists($workspace);
    if (!$entity instanceof ContentEntityInterface) {
      throw new NotFoundHttpException(t('Specified document was not found.'));
    }

    // Check entity and field level access.
    if (!$entity->access('create') || !$entity->{$field_name}->access('create')) {
      throw new AccessDeniedHttpException();
    }

    // Start with the existing file and update values from the received file.
    $file = ($existing_file instanceof FileInterface) ? $existing_file : $received_file;

    // Validate the received data before saving.
    $this->validate($file);
    try {
      // The serializer created a temporary file. Move it to the received URI.
      $received_uri = "$scheme://$filename";
      file_unmanaged_move($received_file->getFileUri(), $received_uri);
      $file->setFileUri($received_uri);
      $file->save();

      // Update the entity with the new file.
      $entity->{$field_name}->get($delta)->target_id = $file->id();
      $entity->save();

      $data = ['ok' => TRUE, 'id' => $entity->uuid(), 'rev' => $entity->_rev->value];
      return new ModifiedResourceResponse($data, 200, $this->responseHeaders($file, ['Content-MD5', 'X-Relaxed-ETag']));
    }
    catch (\Exception $e) {
      throw new HttpException(500, t($e->getMessage()), $e);
    }
  }

  /**
   * @param string | \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   * @param string | \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param integer $delta
   * @param string | \Drupal\file\FileInterface $file
   * @param string $scheme
   * @param string $filename
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   */
  public function delete($workspace, $entity, $field_name, $delta, $file, $scheme, $filename) {
    $this->checkWorkspaceExists($workspace);
    if (!$entity instanceof ContentEntityInterface
      || !$file instanceof FileInterface) {
      throw new NotFoundHttpException(t('Specified document or attachment was not found.'));
    }

    // Check entity and field level access.
    if (!$entity->access('update') || !$entity->{$field_name}->access('delete')) {
      throw new AccessDeniedHttpException();
    }

    // Check that this file actually exists on $entity.
    if ($entity->{$field_name}[$delta]->target_id != $file->id()) {
      throw new BadRequestHttpException();
    }
    try {
      $file->delete();
      unset($entity->{$field_name}[$delta]);
      $entity->save();
      $rev = $entity->_rev->value;
      $data = ['ok' => TRUE, 'id' => $entity->uuid(), 'rev' => $rev];
      return new ModifiedResourceResponse($data, 200, ['X-Relaxed-ETag' => $rev]);
    }
    catch (\Exception $e) {
      throw new HttpException(500, $e->getMessage(), $e);
    }
  }

  /**
   * Helper method that returns the response headers.
   *
   * @param \Drupal\file\FileInterface $file
   * @param array $headers_to_include
   * @param int $rev
   * @return array
   */
  protected function responseHeaders(FileInterface $file, $headers_to_include = [], $rev = NULL) {
    $file_contents = file_get_contents($file->getFileUri());
    $encoded_digest = base64_encode(md5($file_contents));

    $all_headers = [
      'Content-Type' => $file->getMimeType(),
      'X-Relaxed-ETag' => $rev ?: $encoded_digest,
      'Content-Length' => $file->getSize(),
      'Content-MD5' => $encoded_digest,
    ];

    $return = [];
    foreach ($headers_to_include as $header) {
      $return[$header] = $all_headers[$header];
    }
    return $return;
  }

}

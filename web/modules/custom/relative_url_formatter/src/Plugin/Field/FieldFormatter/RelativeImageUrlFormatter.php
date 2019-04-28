<?php
/**
 * @file
 *   Contains \Drupal\relative_url_formatter\Plugin\Field\FieldFormatter\RelativeImageUrlFormatter.
 */

namespace Drupal\relative_url_formatter\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageUrlFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\Component\Utility\UrlHelper;

/**
 * Plugin implementation of the 'relative_image_url' formatter.
 *
 * @FieldFormatter(
 *   id = "relative_image_url",
 *   label = @Translation("Relative URL to image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class RelativeImageUrlFormatter extends ImageUrlFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    /** @var \Drupal\file\Entity\File[] $images */
    $images = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($images)) {
      return $elements;
    }

    /** @var \Drupal\image\Entity\ImageStyle|false $image_style */
    $image_style = ($image_style_setting = $this->getSetting('image_style')) && !empty($image_style_setting) ? $this->imageStyleStorage->load($image_style_setting) : NULL;
    $asset_prefix = $this->getSetting('image_path_prefix');
    foreach ($images as $delta => $image) {
      /** @var \Drupal\file\Entity\File $image */
      $file_uri = $image->getFileUri();

      //@todo research overriding the buildUrl method
/*    $image->getFileUri()
        $url = $image_style
        ? $image_style->buildUrl($image_uri)
        : file_create_url($image_uri);*/

      $url = $image_style ? $this->buildStyleUrl($image_style, $file_uri) : $this->buildOriginalUrl($file_uri);
      $image_url = $asset_prefix . $url;
      // Add cacheable metadata from the image and image style.
      $cacheable_metadata = CacheableMetadata::createFromObject($image);
      if ($image_style) {
        $cacheable_metadata = $cacheable_metadata->merge(CacheableMetadata::createFromObject($image_style));
      }
      $elements[$delta] = ['#markup' => $image_url];
      $cacheable_metadata->applyTo($elements[$delta]);
    }

    return $elements;
  }

  /**
   * Pseudo override of image_style buildUrl function
   */
  public function buildStyleUrl(ImageStyle $image_style, $path, $clean_urls = true){
    $uri = $image_style->buildUri($path);
    // The token query is added even if the
    // 'image.settings:allow_insecure_derivatives' configuration is TRUE, so
    // that the emitted links remain valid if it is changed back to the default
    // FALSE. However, sites which need to prevent the token query from being
    // emitted at all can additionally set the
    // 'image.settings:suppress_itok_output' configuration to TRUE to achieve
    // that (if both are set, the security token will neither be emitted in the
    // image derivative URL nor checked for in
    // \Drupal\image\ImageStyleInterface::deliver()).
    $token_query = array();
    if (!\Drupal::config('image.settings')->get('suppress_itok_output')) {
      // The passed $path variable can be either a relative path or a full URI.
      $original_uri = file_uri_scheme($path) ? file_stream_wrapper_uri_normalize($path) : file_build_uri($path);
      $token_query = array(IMAGE_DERIVATIVE_TOKEN => $image_style->getPathToken($original_uri));
    }

    if ($clean_urls === NULL) {
      // Assume clean URLs unless the request tells us otherwise.
      $clean_urls = TRUE;
      try {
        $request = \Drupal::request();
        $clean_urls = RequestHelper::isCleanUrl($request);
      }
      catch (ServiceNotFoundException $e) {
      }
    }

    // If not using clean URLs, the image derivative callback is only available
    // with the script path. If the file does not exist, use Url::fromUri() to
    // ensure that it is included. Once the file exists it's fine to fall back
    // to the actual file path, this avoids bootstrapping PHP once the files are
    // built.
    if ($clean_urls === FALSE && file_uri_scheme($uri) == 'public' && !file_exists($uri)) {
      $directory_path = \Drupal::service('stream_wrapper_manager')->getViaUri($uri)->getDirectoryPath();
      return Url::fromUri('base:' . $directory_path . '/' . file_uri_target($uri), array('absolute' => TRUE, 'query' => $token_query))->toString();
    }

    $file_url = $this->buildOriginalUrl($uri);

    // Append the query string with the token, if necessary.
    if ($token_query) {
      $file_url .= (strpos($file_url, '?') !== FALSE ? '&' : '?') . UrlHelper::buildQuery($token_query);
    }

    return $file_url;
  }

  /**
   * override of native file_create_url function
   */
  public function buildOriginalUrl($image_uri){
    $directory_path = \Drupal::service('stream_wrapper_manager')->getViaUri($image_uri)->getDirectoryPath();

    return Url::fromUri('base:' . $directory_path . '/' . file_uri_target($image_uri))->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['image_path_prefix'] = [
      '#title' => t('Image Path Prefix'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('image_path_prefix'),
      '#description' =>   [
          '#markup' => "Add a prefix to all image paths.  No trailing slash needed",
          '#access' => $this->currentUser->hasPermission('administer image styles')
        ],
    ];

    unset($element['image_link']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'image_path_prefix' => '',
    ];
  }
}

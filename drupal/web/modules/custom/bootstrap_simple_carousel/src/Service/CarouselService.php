<?php

namespace Drupal\bootstrap_simple_carousel\Service;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * CarouselService Class.
 *
 * Provides functions for the module.
 *
 * @category Class
 * @package Drupal\bootstrap_simple_carousel\Service
 */
class CarouselService extends ServiceProviderBase {
  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  /**
   * This will hold Renderer object.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * This will hold File object.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $file;

  /**
   * CarouselService constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager interface.
   */
  public function __construct(RendererInterface $renderer, EntityTypeManagerInterface $entityTypeManager) {
    $this->renderer = $renderer;
    $this->file = $entityTypeManager->getStorage('file');
  }

  /**
   * Return a rendered image.
   *
   * @param int $image_id
   *   The image id for the carousel.
   * @param string $image_style
   *   The image style for the carousel.
   * @param array $params
   *   An array of parameters.
   *
   * @throws \Exception
   *
   * @return string
   *   Rendered image
   */
  public function renderImageById($image_id, $image_style = 'thumbnail', array $params = []) {
    $image = '';
    $imageFile = $this->file->load($image_id);

    if (!empty($imageFile)) {
      $imageTheme = [
        '#theme' => 'image_style',
        '#style_name' => $image_style,
        '#uri' => $imageFile->getFileUri(),
        '#alt' => $params['alt'] ?? '',
        '#title' => $params['title'] ?? '',
      ];

      $image = $this->renderer->render($imageTheme);
    }

    return $image;
  }

  /**
   * Return a Render Link.
   *
   * @param string $url
   *   The url for the render link.
   * @param string $title
   *   The title for the render link.
   * @param array $attributes
   *   The array of attributes.
   *
   * @throws \Exception
   *
   * @return string
   *   Rendered link
   */
  public function renderLink($url, $title, array $attributes = []) {
    $linkTheme = [
      '#type' => 'link',
      '#title' => $title,
      '#url' => $url,
      '#options' => [
        'attributes' => $attributes,
        'html' => FALSE,
      ],
    ];

    $link = $this->renderer->render($linkTheme);

    return $link;
  }

  /**
   * Return the statuses.
   *
   * @return array
   *   Of statuses
   */
  public function getStatuses() {
    return [
      0 => $this->t('Inactive'),
      1 => $this->t('Active'),
    ];
  }

}

<?php

namespace Drupal\Tests\bootstrap_simple_carousel\Unit\Service;

use Drupal\bootstrap_simple_carousel\Service\CarouselService;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\file\FileInterface;
use Drupal\Tests\PhpunitCompatibilityTrait;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\bootstrap_simple_carousel\Service\CarouselService
 *
 * @group bootstrap_simple_carousel
 */
class CarouselServiceTest extends UnitTestCase {
  use PhpunitCompatibilityTrait;

  /**
   * The mocked renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $renderer;

  /**
   * The mocked entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $this->entityTypeManager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
  }

  /**
   * Tests the renderLink() method.
   *
   * @covers ::renderLink
   *
   * @dataProvider providerTestRenderLink
   */
  public function testRenderLink(string $url, string $title, array $attributes, array $with, string $expected) {
    $this->renderer->expects($this->exactly(1))
      ->method('render')
      ->with($with)
      ->willReturn($expected);

    $carouselService = new CarouselService($this->renderer, $this->entityTypeManager);
    $actual = $carouselService->renderLink($url, $title, $attributes);

    $this->assertSame($expected, $actual);
  }

  /**
   * Tests the renderImageById() method.
   *
   * @covers ::renderImageById
   *
   * @dataProvider providerTestRenderImageById
   */
  public function testRenderImageById(int $imageId, $file, string $imageStyle, array $params, array $with, int $renderCount, string $expected) {
    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->expects($this->once())
      ->method('load')
      ->with($imageId)
      ->willReturn($file);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->willReturn($storage);

    $this->renderer->expects($this->exactly($renderCount))
      ->method('render')
      ->with($with)
      ->willReturn($expected);

    $carouselService = new CarouselService($this->renderer, $this->entityTypeManager);
    $actual = $carouselService->renderImageById($imageId, $imageStyle, $params);

    $this->assertSame($expected, $actual);
  }

  /**
   * Tests the getStatuses() method.
   *
   * @covers ::getStatuses
   */
  public function testGetStatuses() {
    $stringTranslation = $this->createMock(TranslationInterface::class);
    $carouselService = new CarouselService($this->renderer, $this->entityTypeManager);
    $carouselService->setStringTranslation($stringTranslation);

    $this->assertSame(count(['Inactive', 'Active']), count($carouselService->getStatuses()));
  }

  /**
   * Provides test data for providerTestRenderImageById.
   *
   * @return array
   *   The test data.
   */
  public function providerTestRenderImageById() {
    $url = 'public://directory/file.jpg';
    $file = $this->createMock(FileInterface::class);
    $file->expects($this->once())
      ->method('getFileUri')
      ->willReturn($url);
    $imageStyle = 'style_slider';
    $params = [
      'alt' => 'alt',
      'title' => 'title',
    ];

    $with = [
      '#theme' => 'image_style',
      '#style_name' => $imageStyle,
      '#uri' => $url,
      '#alt' => $params['alt'],
      '#title' => $params['title'],
    ];

    return [
      [5, $file, $imageStyle, $params, $with, 1, '/directory/file.jpg'],
      [33, NULL, $imageStyle, $params, $with, 0, ''],
    ];
  }

  /**
   * Provides test data for providerTestRenderLink.
   *
   * @return array
   *   The test data.
   */
  public function providerTestRenderLink() {
    $url = 'http://example.com';
    $title = 'example';
    $expected = '<a href="http://example.com">example</a>';
    $with = [
      '#type' => 'link',
      '#title' => $title,
      '#url' => $url,
      '#options' => [
        'attributes' => [],
        'html' => FALSE,
      ],
    ];

    return [
      [$url, $title, [], $with, $expected],
    ];
  }

}

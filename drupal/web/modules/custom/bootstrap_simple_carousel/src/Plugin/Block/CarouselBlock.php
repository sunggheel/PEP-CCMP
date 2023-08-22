<?php

namespace Drupal\bootstrap_simple_carousel\Plugin\Block;

use Drupal\bootstrap_simple_carousel\Form\SettingsForm;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a 'Bootstrap simple carousel' Block.
 *
 * @Block(
 *   id = "bootstrap_simple_carousel_block",
 *   admin_label = @Translation("Bootstrap simple carousel block")
 * )
 */
class CarouselBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * This will hold ImmutableConfig object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $moduleSettings;

  /**
   * Image style service.
   *
   * @var \Drupal\image\ImageStyleInterface
   */
  protected $imageStyleService;

  /**
   * The database connection object.
   *
   * @var \Drupal\bootstrap_simple_carousel\CarouselItemStorage
   */
  protected $carouselItemStorage;

  /**
   * The entity manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleSettings = $config_factory->get('bootstrap_simple_carousel.settings');
    $this->carouselItemStorage = $entity_type_manager->getStorage('bootstrap_simple_carousel');
    $this->entityTypeManager = $entity_type_manager;
    $this->imageStyleService = $entity_type_manager->getStorage('image_style');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#items' => $this->getCarouselItems(),
      '#settings' => $this->moduleSettings,
      '#theme' => 'bootstrap_simple_carousel_block',
    ];

    if ($this->moduleSettings->get('assets')) {
      $build['#attached'] = [
        'library' => [
          'bootstrap_simple_carousel/bootstrap',
        ],
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * Returns an active carousel items.
   *
   * @return array|null
   *   Items list or null
   */
  protected function getCarouselItems() {
    $items = $this->carouselItemStorage->getCarouselItems();

    if (!empty($items)) {
      foreach ($items as &$item) {
        $file = $this->entityTypeManager->getStorage('file')->load($item->image_id);
        $image_style = $this->moduleSettings->get('image_style');
        if (empty($image_style) || $image_style == SettingsForm::ORIGINAL_IMAGE_STYLE_ID) {
          $item->image_url = file_url_transform_relative(file_create_url($file->getFileUri()));
        }
        else {
          $item->image_url = file_url_transform_relative($this->imageStyleService->load($image_style)
            ->buildUrl($file->getFileUri()));
        }
        if (!empty($item->image_link)) {
          $uri = parse_url($item->image_link);
          if (empty($uri['host'])) {
            if (strpos($item->image_link, '/') !== 0) {
              $item->image_link = '/' . $item->image_link;
            }
            $item->image_link = 'internal:' . $item->image_link;
          }
          $item->image_link = Url::fromUri($item->image_link);
        }
      }
    }

    return $items;
  }

}

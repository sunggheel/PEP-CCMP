<?php

namespace Drupal\bootstrap_simple_carousel\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm.
 *
 * Provides a settings form.
 *
 * @package Drupal\bootstrap_simple_carousel\Form
 */
class SettingsForm extends ConfigFormBase {

  const ORIGINAL_IMAGE_STYLE_ID = 'original';
  const DEFAULT_IMAGE_TYPE_ID = 'img-default';
  const FLUID_IMAGE_TYPE_ID = 'img-fluid';
  const CIRCLE_IMAGE_TYPE_ID = 'img-circle';

  /**
   * Image style service.
   *
   * @var \Drupal\image\ImageStyleInterface
   */
  protected $imageStyleService;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->imageStyleService = $entity_type_manager->getStorage('image_style');

    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bootstrap_simple_carousel_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bootstrap_simple_carousel.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bootstrap_simple_carousel.settings');

    $form['interval'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Interval'),
      '#description' => $this->t('The amount of time to delay between automatically cycling an item. If false, carousel will not automatically cycle.'),
      '#default_value' => $config->get('interval'),
    ];

    $form['wrap'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Wrap'),
      '#description' => $this->t('Whether the carousel should cycle continuously or have hard stops.'),
      '#default_value' => $config->get('wrap'),
    ];

    $form['pause'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Pause on hover'),
      '#description' => $this->t("If is checked, pauses the cycling of the carousel on mouseenter and resumes the cycling of the carousel on mouseleave. If is unchecked, hovering over the carousel won't pause it."),
      '#default_value' => $config->get('pause'),
    ];

    $form['indicators'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Indicators'),
      '#description' => $this->t('Show carousel indicators'),
      '#default_value' => $config->get('indicators'),
    ];

    $form['controls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Controls'),
      '#description' => $this->t('Show carousel arrows (next/prev).'),
      '#default_value' => $config->get('controls'),
    ];

    $form['assets'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Assets'),
      '#description' => $this->t("Includes bootstrap framework v4.0.0, don't check it, if you use the bootstrap theme, or the bootstrap framework are already included."),
      '#default_value' => $config->get('assets'),
    ];

    $form['image_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Bootstrap image type'),
      '#description' => $this->t('Bootstrap image type for carousel items.'),
      '#options' => $this->getImagesTypes(),
      '#default_value' => $config->get('image_type'),
    ];

    $form['image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Image style'),
      '#description' => $this->t('Image style for carousel items. If you will be use the image styles for bootstrap items, you need to set up the same width for the "bootstrap carousel" container.'),
      '#options' => $this->getImagesStyles(),
      '#default_value' => $config->get('image_style'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Return images styles.
   *
   * @return array
   *   List of image styles
   */
  protected function getImagesStyles() {
    $styles = $this->imageStyleService->loadMultiple();

    $options = [
      static::ORIGINAL_IMAGE_STYLE_ID => $this->t('Original image'),
    ];
    foreach ($styles as $key => $value) {
      $options[$key] = $value->get('label');
    }

    return $options;
  }

  /**
   * Return bootstrap images types.
   *
   * @return array
   *   Image types list
   */
  protected function getImagesTypes() {
    $options = [
      static::DEFAULT_IMAGE_TYPE_ID => $this->t('Image none'),
      static::FLUID_IMAGE_TYPE_ID => $this->t('Image fluid'),
      static::CIRCLE_IMAGE_TYPE_ID => $this->t('Image circle'),
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('bootstrap_simple_carousel.settings')
      ->set('interval', $form_state->getValue('interval'))
      ->set('wrap', $form_state->getValue('wrap'))
      ->set('pause', $form_state->getValue('pause'))
      ->set('indicators', $form_state->getValue('indicators'))
      ->set('controls', $form_state->getValue('controls'))
      ->set('assets', $form_state->getValue('assets'))
      ->set('image_type', $form_state->getValue('image_type'))
      ->set('image_style', $form_state->getValue('image_style'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

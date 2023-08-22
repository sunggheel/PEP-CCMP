<?php

namespace Drupal\bootstrap_simple_carousel\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DeleteForm.
 *
 * Delete item form.
 *
 * @package Drupal\bootstrap_simple_carousel\Form
 */
class DeleteForm extends ConfirmFormBase {
  use StringTranslationTrait;

  /**
   * The database connection object.
   *
   * @var \Drupal\bootstrap_simple_carousel\CarouselItemStorage
   */
  protected $carouselItemStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity.
   *
   * @var \Drupal\bootstrap_simple_carousel\Entity\CarouselItem
   */
  protected $entity;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->carouselItemStorage = $entityTypeManager->getStorage('bootstrap_simple_carousel');
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bootstrap_simple_carousel_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the item?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('bootstrap_simple_carousel.table');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $form = parent::buildForm($form, $form_state);

    if (is_numeric($id)) {
      $this->entity = $this->carouselItemStorage->load($id);
    }

    if (!is_null($this->entity)) {
      $form['cid'] = [
        '#type' => 'hidden',
        '#required' => FALSE,
        '#default_value' => $this->entity->id(),
      ];

      $form['image_id'] = [
        '#type' => 'hidden',
        '#required' => FALSE,
        '#default_value' => $this->entity->getImageId(),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $message = $this->t('Item has been removed!');
    if (!is_null($this->entity)) {
      $file = $this->entityTypeManager->getStorage('file')->load($form_state->getValue('image_id'));
      $file->setTemporary();
      $file->save();

      try {
        $this->entity->delete();
      }
      catch (\Exception $e) {
        $message = $this->t('Item was not removed!');
      }
    }

    $this->messenger()->addMessage($message);
    $form_state->setRedirect('bootstrap_simple_carousel.table');
  }

}

<?php

namespace Drupal\bootstrap_simple_carousel\Entity;

use Drupal\bootstrap_simple_carousel\CarouselItemInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the carousel item entity class.
 *
 * @ContentEntityType(
 *   id = "bootstrap_simple_carousel",
 *   label = @Translation("Carousel Item"),
 *   label_collection = @Translation("Carousel Items"),
 *   label_singular = @Translation("carousel item"),
 *   label_plural = @Translation("carousel items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count carousel item",
 *     plural = "@count carousel items"
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\bootstrap_simple_carousel\CarouselItemStorage",
 *     "form" = {
 *       "delete" = "Drupal\bootstrap_simple_carousel\Form\DeleteForm",
 *       "edit" = "Drupal\bootstrap_simple_carousel\Form\EditForm",
 *     }
 *   },
 *   base_table = "bootstrap_simple_carousel",
 *   entity_keys = {
 *     "id" = "cid",
 *     "image_id" = "image_id",
 *     "image_alt" = "image_alt",
 *     "image_title" = "image_title",
 *     "image_link" = "image_link",
 *     "caption_title" = "caption_title",
 *     "caption_text" = "caption_text",
 *     "weight" = "weight",
 *     "status" = "status",
 *   },
 *   links = {
 *     "settings-form" = "/admin/config/media/bootstrap_simple_carousel",
 *     "delete-form" = "/admin/structure/bootstrap_simple_carousel/delete/{id}",
 *     "create" = "/admin/structure/bootstrap_simple_carousel/add",
 *     "edit-form" = "/admin/structure/bootstrap_simple_carousel/edit/{id}",
 *     "list" = "/admin/structure/bootstrap_simple_carousel",
 *   }
 * )
 */
class CarouselItem extends ContentEntityBase implements CarouselItemInterface {

  /**
   * Return an entity value by name.
   */
  private function getValue($field_name) {
    if (!isset($this->values[$field_name][$this->activeLangcode])) {
      $list = $this->getTranslatedField($field_name, $this->activeLangcode)->first();

      return NULL !== $list ? $list->getString() : NULL;
    }
    return $this->values[$field_name][$this->activeLangcode] ?? NULL;
  }

  /**
   * Set an entity value by name.
   */
  private function setValue($field_name, $field_value) {
    if (isset($this->values[$field_name])) {
      $this->values[$field_name][$this->activeLangcode] = $field_value;
    }

    return $this;
  }

  /**
   * Return image id.
   */
  public function getImageId() {
    return $this->getValue('image_id');
  }

  /**
   * Set image id.
   */
  public function setImageId(int $imageId) {
    return $this->setValue('image_id', $imageId);
  }

  /**
   * Return image alt.
   */
  public function getImageAlt() {
    return $this->getValue('image_alt');
  }

  /**
   * Set image alt.
   */
  public function setImageAlt(string $imageAlt) {
    return $this->setValue('image_alt', $imageAlt);
  }

  /**
   * Return image title.
   */
  public function getImageTitle() {
    return $this->getValue('image_title');
  }

  /**
   * Set image title.
   */
  public function setImageTitle(string $imageTitle) {
    return $this->setValue('image_title', $imageTitle);
  }

  /**
   * Return image link.
   */
  public function getImageLink() {
    return $this->getValue('image_link');
  }

  /**
   * Set image link.
   */
  public function setImageLink(string $imageLink) {
    return $this->setValue('image_link', $imageLink);
  }

  /**
   * Return caption title.
   */
  public function getCaptionTitle() {
    return $this->getValue('caption_title');
  }

  /**
   * Set caption title.
   */
  public function setCaptionTitle(string $captionTitle) {
    return $this->setValue('caption_title', $captionTitle);
  }

  /**
   * Return caption text.
   */
  public function getCaptionText() {
    return $this->getValue('caption_text');
  }

  /**
   * Set caption text.
   */
  public function setCaptionText(string $captionText) {
    return $this->setValue('caption_text', $captionText);
  }

  /**
   * Return weight.
   */
  public function getWeight() {
    return $this->getValue('weight');
  }

  /**
   * Set weight.
   */
  public function setWeight(int $weight) {
    return $this->setValue('weight', $weight);
  }

  /**
   * Return status.
   */
  public function getStatus() {
    return $this->getValue('status');
  }

  /**
   * Set status.
   */
  public function setStatus(int $status) {
    return $this->setValue('status', $status);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['image_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Image id'))
      ->setDescription(t('The id of image file.'))
      ->setSetting('unsigned', TRUE);

    $fields['image_alt'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Image Alt'))
      ->setDescription(t('The alt of image.'))
      ->setSetting('max_length', 255);

    $fields['image_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Image Title'))
      ->setDescription(t('The title of image.'))
      ->setSetting('max_length', 255);

    $fields['image_link'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Image Link'))
      ->setDescription(t('The link of image.'))
      ->setSetting('max_length', 255);

    $fields['caption_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Caption Title'))
      ->setDescription(t('The title of caption.'))
      ->setSetting('max_length', 100);

    $fields['caption_text'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Caption Text'))
      ->setDescription(t('The text of caption.'))
      ->setSetting('max_length', 255);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of the item.'))
      ->setSetting('unsigned', TRUE);

    $fields['status'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of item.'))
      ->setSetting('unsigned', TRUE);

    return $fields;
  }

}

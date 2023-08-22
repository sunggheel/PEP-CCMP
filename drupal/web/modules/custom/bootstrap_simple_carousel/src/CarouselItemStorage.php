<?php

namespace Drupal\bootstrap_simple_carousel;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Carousel Item storage.
 */
class CarouselItemStorage extends SqlContentEntityStorage implements ContentEntityStorageInterface {

  /**
   * Returns all carousel items.
   */
  public function getItems() {
    $query = $this->database->select($this->getBaseTable(), 'u');
    $query->fields('u');
    $query->orderBy('weight', 'DESC');

    return $query->execute()->fetchAll();
  }

  /**
   * Returns all active carousel items.
   */
  public function getCarouselItems() {
    $query = $this->database->select($this->getBaseTable(), 'u');
    $query->fields('u');
    $query->condition('status', 1);
    $query->orderBy('weight', 'DESC');
    return $query->execute()->fetchAll();
  }

}

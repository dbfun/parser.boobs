<?php

/**
 * @package Сore
 */

/**
 * Приложение
 */

class PApplication {

  /**
   * Init common classes
   *
   */

  public function init($dir = '')
  {
    require('core/factory.php');
    PFactory::init($dir);
  }
}
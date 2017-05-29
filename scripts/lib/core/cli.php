<?php

/**
 *  @package Сore
 */

/**
 *  Приложение командной строки
 */

abstract class Cli {

  protected function showMan() {
    die(static::MANUAL.PHP_EOL);
  }

}
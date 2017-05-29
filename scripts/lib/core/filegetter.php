<?php
/**
 * @package Сore
 */
 
/**
 * Последовательное получение файлов с диска
 */

class FileGetter implements Iterator, Countable {
  private $position = 1;
  public function __construct() { $this->position = 1; }
  function rewind() { $this->position = $this->min; }
  function next() { ++$this->position; }
  function key() { return $this->position; }
  
  function valid() {
    return $this->position >= $this->min && $this->position <= $this->max;
  }
  
  function current() { 
    $uri = preg_replace('~#id#~', $this->position, $this->uriRule);
    return new File($uri);
  }
  
  function count() {
    return $this->max - $this->min + 1;
  }
  
  private $min = 0, $max = 0;
  public function setRange($min, $max) {
    $this->min = $min;
    $this->max = $max;
    return $this;
  }
  
  private $uriRule;
  public function setRule($uriRule) {
    $this->uriRule = $uriRule;
    return $this;
  }
}
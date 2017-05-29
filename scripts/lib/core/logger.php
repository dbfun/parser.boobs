<?php

/**
 * @package Сore
 */

/**
 * Логирование в файл
 */

class Logger {

  private $handle, $fileName;
  
  /**
   * Создание файла
   *
   * @param string $fileName имя файла
   */
   
	public function __construct($fileName) {
    $this->fileName = $fileName;
	}
  
  /**
   * Запись лога в файл
   * @see Messages::addError() Сообщения
   *
   * @param array $messages Сообщения 
   */
  
	public function log(array $messages) {
    if(count($messages) == 0) return;
    foreach($messages as $mess) {
      if($mess->isError()) fwrite($this->handle, $mess);
    }
	}
  
  /**
   * Открытие файла для записи
   */
  
  public function start() {
    $this->handle = fopen($this->fileName, 'w+');
    if($this->handle === false) throw new Exception('Error open log file');
  }
  
  /**
   * Закрытие файла
   */
  
  public function commit() {
    fclose($this->handle);
  }



}
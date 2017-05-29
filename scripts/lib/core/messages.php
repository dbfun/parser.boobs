<?php

/**
 * @package Сore
 */
 
/**
 * Сообщения для организации логов
 */

class Messages {

  private $data = array('success' => true, 'errors' => array(), 'err_codes' => array(), 'info' => array(), 'source');
 
  public function __construct() {}
  
  /**
   * Добавить ошибку
   *
   * @param string $text текст ошибки
   * @param int $errNo номер ошибки
   * @return Messages
   */
  public function addError($text, $errNo = 0) {
    $this->data['success'] = false;
    $this->data['errors'][] = $text;
    $this->data['err_codes'][] = $errNo;
    return $this;
  }
  
  /**
   * Добавить информационное сообщение
   *
   * @param string $text текст сообщения
   * @return Messages
   */
  
  public function addInfo($text) {
    $this->data['info'][] = $text;
    return $this;
  }
  
  /**
   * Добавить источник ошибки
   *
   * @param string $text источник ошибки
   * @return Messages
   */
  
  public function addSource($text) {
    $this->data['source'] = $text;
    return $this;
  }
  
  /**
   * Очистить стек ошибок
   *
   * @return Messages
   */
  
  public function reset() {
    $this->data['errors'] = array();
    $this->data['err_codes'] = array();
		$this->data['info'] = array();
    $this->data['success'] = true;
    return $this;
  }
  
  /**
   * Успешно (без ошибок)?
   *
   * @return bool
   */
  
  public function isSuccess() {
    return $this->data['success'];
  }
  
  /**
   * Есть ошибка?
   *
   * @return bool
   */

  public function isError() {
  	return !$this->isSuccess();
  }
  
  /**
   * Вывод лога (в строку)
   *
   * @return string
   */

  public function __toString() {
  	$data = $this->report(false);
    return 
      $data['source'].">".
      "\tSuccess: ".(int)$data['success'].
      "\tErr: ".(count($data['err_codes']) > 0 ? implode(', ', $data['err_codes']) : '-').
      "\tErrors: ".(count($data['errors']) > 0 ? implode(', ', $data['errors']) : '-').PHP_EOL;
  }
  
  /**
   * Вывод лога (массив)
   *
   * @param bool $isShowInfo показывать информационные сообщения?
   * @return array
   */
  
  public function report($isShowInfo = false) {
  	$data = array();
    $data['source'] = $this->data['source'];
    $data['success'] = $this->data['success'];
    $data['err_codes'] = array_unique($this->data['err_codes']);
    $data['errors'] = $this->data['errors'];
    if($isShowInfo) $data['info'] = implode(', ', $this->data['info']);
    return $data;
  }
  
   
  


}
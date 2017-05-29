<?php

/**
 * @package Сore
 */

/**
 * Документ по типу ActiveRecord с сохранением в MongoDB
 */

class PDocument {

  private $dbo, $dbmongo, $document, $file;
  public function __construct() {
    $this->dbo = PFactory::getDbo();
    $mongo = new MongoClient();
    $this->dbmongo = $mongo->ppdb;
    $this->reset();
  }

  public function __set($name, $val) {
    if(!isset($val)) return;
    if(is_array($val) && count($val) == 0) return;
    if(strpos($name, '.') !== false) throw new Exception("Can not set property '$name'");
    $this->document->$name = $val;
  }

  public function __toString() {
    return print_r($this->document, true);
  }

  public function getTotalCount() {
    $countDocs = $this->dbmongo->ppdb->count();
    return $countDocs;
  }

  public function getFullCollection() {
    $cursor = $this->dbmongo->ppdb->find();
    return $cursor;
  }

  private function reset() {
    $this->document = new stdClass();
    $this->file = null;
  }

  public function init($id, $file) {
    $this->reset();
    $this->file = $file;
    $this->document->id = $id;
    return $this;
  }

  public function load($id) {
    $document = $this->dbmongo->ppdb->findOne(array('id' => $id));
    if(!is_array($document)) throw new Exception("Can not load document $id");
    $this->document = (object)$document;
    return $this;
  }

  public function save() {
    if(!isset($this->document->id)) throw new Exception("Document id not set");
    unset($this->document->_id);
    $this->dbmongo->ppdb->update(array('id' => $this->document->id), array('$set' => $this->document), array('upsert'=>true));
  }

  private $plugins = array();

  public function loadPlugins($fileName) {
    if(!file_exists($fileName)) throw new Exception("File $fileName not exists");

    $config = json_decode(file_get_contents($fileName));
    if(count($config->plugins) > 0) foreach($config->plugins as $plugin) {
      if(!$plugin->enabled) continue;
      require(PFactory::getDir()."plugins/{$plugin->file}");
      $this->addPlugin($plugin);
    }
    return $this;
  }

  public function addPlugin($plugin) {
    $this->plugins[] = $plugin;
    return $this;
  }

  private static $messages = array();
  public function addMessage($messages) { self::$messages[] = $messages; }
  public function getMessages() { return self::$messages; }

  public function parse() {
    if(count($this->plugins) > 0) foreach($this->plugins as $plugin) {
      $messages = new Messages();
      $messages->addSource($this->document->id);
      call_user_func($plugin, $this->document, $this->file, $messages);
      $this->addMessage($messages);
    }
    return $this;
  }

  public function translate() {
    if(count($this->plugins) > 0) foreach($this->plugins as $plugin) {
      $messages = new Messages();
      $messages->addSource($this->document->id);
      call_user_func($plugin, $this->document, $this->dbmongo, $messages);
      $this->addMessage($messages);
    }
    return $this;
  }

}
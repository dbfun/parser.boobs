<?php

/**
 *
 * Справочник свойств
 *
**/

require('lib/application.php');

PApplication::init(__DIR__.'/');

class PropDictionary {

  private $db;
  public function __construct() {
    $this->db = PFactory::getDbo();
    define('ABS_PATH', dirname(__FILE__));
  }

  private $cats = array(), $ethnicity = array(), $nationality = array(), $bodyType = array(),
    $eyeColor = array(), $hairColor = array(), $hairLength = array();

  public function run() {
    $nameSpace = 'boobpedia.com';
    while ($chunk = $this->getChunk($nameSpace)) {
      foreach($chunk as $page) {
        $data = unserialize($page['prepared_data']);
        if(isset($data['cats']) && is_array($data['cats'])) $this->cats += $data['cats'];

        if($page['is_props']) {
          if(isset($data['props']['ethnicity'])) @$this->ethnicity[$data['props']['ethnicity']] += 1;
          if(isset($data['props']['nationality'])) @$this->nationality[$data['props']['nationality']] += 1;

          if(isset($data['props']['body type'])) @$this->bodyType[$data['props']['body type']] += 1;
          // if(isset($data['props']['eye color'])) @$this->eyeColor[$data['props']['eye color']] += 1;
          // if(isset($data['props']['hair color'])) @$this->hairColor[$data['props']['hair color']] += 1;
          // if(isset($data['props']['hair length'])) @$this->hairLength[$data['props']['hair length']] += 1;
        }
      }
    }

    echo var_dump($this->cats).PHP_EOL;
    echo var_dump($this->ethnicity).PHP_EOL;
    echo var_dump($this->nationality).PHP_EOL;
    echo var_dump($this->bodyType).PHP_EOL;
    // echo var_dump($this->eyeColor).PHP_EOL;
    // echo var_dump($this->hairColor).PHP_EOL;
    // echo var_dump($this->hairLength).PHP_EOL;

  }


  private $offset = 0, $limit = 100;
  private function getChunk($nameSpace) {
    $query = "SELECT * FROM `boob_pages` WHERE `is_processed` = 1 AND `type` = '".addslashes($nameSpace)."' LIMIT {$this->offset}, {$this->limit}";
    $results = $this->db->SelectSet($query);
    if(!is_array($results) || count($results) == 0) return false;
    $this->offset += $this->limit;
    return $results;
  }

}

$pd = new PropDictionary();
$pd->run();
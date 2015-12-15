<?php

/**
 *
 * Получение страниц
 *
**/

require('lib/application.php');

require('lib/core/datagetter.php');
// require('lib/extend/simple_html_dom/simple_html_dom.php');

PApplication::init(__DIR__.'/');

class PagePuller {

  private $db;
  public function __construct() {
    $this->db = PFactory::getDbo();
    define('ABS_PATH', dirname(__FILE__));
  }

  public function run() {
    $nameSpace = 'boobpedia.com';
    while ($chunk = $this->getChunk($nameSpace)) {
      foreach($chunk as $page) {
        echo $page['id'].PHP_EOL;
        $data = unserialize($page['data']);
        $extData = array();
        if(!is_object($data) || !isset($data->title) || !$data->title) {
          $this->save($page, $extData);
          continue;
        }

        $extData['api'] = $this->dataFromApi($data->title);
        $extData['wiki'] = $this->dataFromWiki($data->title);
        $this->save($page, $extData);
      }
    }
    echo 'Done'.PHP_EOL;
  }

  private function save($page, $extData) {
    $query = "UPDATE `boob_pages` SET `is_loaded` = 1, `data_ext` = '".addslashes(serialize($extData))."' WHERE `type` = '".addslashes($page['type'])."' AND `id` = ".(int)$page['id'];
    $this->db->query($query);
  }

  private $offset = 0, $limit = 1000;
  private function getChunk($nameSpace) {
    $query = "SELECT * FROM `boob_pages` WHERE `is_loaded` = 0 AND `type` = '".addslashes($nameSpace)."' LIMIT {$this->offset}, {$this->limit}";
    $results = $this->db->SelectSet($query);
    if(!is_array($results) || count($results) == 0) return false;
    $this->offset += $this->limit;
    return $results;
  }

  private function dataFromApi($title) {
    $uri = 'http://boobpedia.com/butler/api.php?action=parse&page='.urlencode($title).'&format=json';
    $page = ParseDataGetter::GetPageContent($uri);
    $data = json_decode($page);
    if(!is_object($data)) return false;
    return $data->parse;
  }

  private function dataFromWiki($title) {
    $uri = 'http://www.boobpedia.com/butler/index.php?title='.urlencode($title).'&redirect=no&action=edit';
    $page = ParseDataGetter::GetPageContent($uri);
    if(preg_match('~<textarea id="wpTextbox1".*?>(.*)?<\/textarea>~ms', $page, $m)) {
      $page = preg_replace('~&lt;~', '<', $m[1]);
      $page = preg_replace('~&amp;~', '&', $page);
      return $page;
    } else {
      return false;
    }
  }



}

$pp = new PagePuller();
$pp->run();
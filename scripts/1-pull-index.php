<?php

/**
 *
 * Получение списка страниц
 *
**/

require('lib/application.php');

require('lib/core/datagetter.php');

PApplication::init(__DIR__.'/');

class IndexPuller {

  private $db;
  public function __construct() {
    $this->db = PFactory::getDbo();
    define('ABS_PATH', dirname(__FILE__));
  }

  public function run() {
    $uri = $this->getIndexUri('"');
    while($data = $this->getIndexData($uri)) {
      $pages = (array)$data->query->pages;
      if(count($pages) == 0) break;
      foreach($pages as $id => $pageData) {
        $page_date = addslashes($pageData->touched);
        $serData = addslashes(serialize($pageData));
        $query = "INSERT INTO `boob_pages` (`type`, `id`, `data`, `date`, `page_date`)
          VALUES ('boobpedia.com', {$id}, '".$serData."', NOW(), '".$page_date."')
          ON DUPLICATE KEY UPDATE `data` = '".$serData."', `date` = NOW(), `page_date` = '".$page_date."'";
        $this->db->Query($query);
      }

      $gapfrom &= $data->{'query-continue'}->allpages->gapfrom;
      if(!isset($gapfrom) || !$gapfrom) break;
      $uri = $this->getIndexUri($gapfrom);
    }
    echo 'Done'.PHP_EOL;
  }

  private function getIndexUri($gapfrom) {
    return 'http://boobpedia.com/butler/api.php?action=query&generator=allpages&gaplimit=500&gapfrom='.urlencode($gapfrom).'&prop=info&format=json';
  }

  private function getIndexData($uri) {
    $page = ParseDataGetter::GetPageContent($uri);
    if(mb_strlen($page) == 0) return false;
    $data = json_decode($page);
    if(!is_object($data)) return false;
    return $data;
  }

}

$ip = new IndexPuller();
$ip->run();
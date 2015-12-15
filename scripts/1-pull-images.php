<?php

/**
 *
 * Получение ссылок на картинки
 *
**/

require('lib/application.php');

require('lib/core/datagetter.php');

PApplication::init(__DIR__.'/');

class ImagesPuller {

  private $db;
  public function __construct() {
    $this->db = PFactory::getDbo();
    define('ABS_PATH', dirname(__FILE__));
  }

  public function run() {
    $uri = $this->getIndexUri('"');
    while($data = $this->getIndexData($uri)) {
      $allimages = (array)$data->query->allimages;
      if(count($allimages) == 0) break;
      foreach($allimages as $id => $imgData) {
        $query = "INSERT IGNORE INTO `boob_images` (`type`, `name`, `url`)
          VALUES ('boobpedia.com', '".addslashes($imgData->name)."', '".addslashes($imgData->url)."')";
        $this->db->Query($query);
      }

      if(isset($data->{'query-continue'}->allimages->aifrom)) {
        $aifrom = $data->{'query-continue'}->allimages->aifrom;
      } else {
        break;
      }
      $uri = $this->getIndexUri($aifrom);
    }
    echo 'Done'.PHP_EOL;
  }

  private function getIndexUri($aifrom) {
    return 'http://boobpedia.com/butler/api.php?action=query&list=allimages&ailimit=500&aifrom='.urlencode($aifrom).'&format=json';
  }

  private function getIndexData($uri) {
    $page = ParseDataGetter::GetPageContent($uri);
    if(mb_strlen($page) == 0) return false;
    $data = json_decode($page);
    if(!is_object($data)) return false;
    return $data;
  }

}

$ip = new ImagesPuller();
$ip->run();
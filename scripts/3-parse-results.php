<?php

/**
 *
 * Парсинг страницы
 *
**/

require('lib/application.php');

PApplication::init(__DIR__.'/');

class PageParser {

  private $db;
  public function __construct() {
    $this->db = PFactory::getDbo();
    define('ABS_PATH', dirname(__FILE__));
  }

  private $rawData, $prepData, $hasProps, $counter = 0;
  public function run() {
    $nameSpace = 'boobpedia.com';
    while ($chunk = $this->getChunk($nameSpace)) {
      foreach($chunk as $page) {
        $this->counter++;
        $this->rawData = unserialize($page['data_ext']);
        $this->prepData = array();
        $this->hasProps = false;

        if(!is_array($this->rawData)) {
          $this->save($page, $this->prepData, $this->hasProps);
          continue;
        }

        $this->parse();
        $this->canonize();

        $this->save($page, $this->prepData, $this->hasProps);
      }
    }
    echo 'Done: '.$this->counter.PHP_EOL;
  }

  private function parse() {
    $this->parseName();
    $this->parseCats();
    $this->parseImages();
    $this->parseExtLinks();
    $this->parseWiki();
  }

  private function parseName() {
    $it =& $this->rawData['api']->displaytitle;
    if(!isset($it) || !$it) return;
    $this->prepData['name'] = $it;
  }

  private function parseCats() {
    $items =& $this->rawData['api']->categories;
    if(!isset($items) || !is_array($items)) return;
    foreach($items as $it) {
      $this->prepData['cats'][] = $it->{'*'};
    }
  }

  private function parseImages() {
    $items =& $this->rawData['api']->images;
    if(!isset($items) || !is_array($items)) return;
    foreach($items as $it) {
      $this->prepData['images'][] = $it;
    }
  }

  private function parseExtLinks() {
    $items =& $this->rawData['api']->externallinks;
    if(!isset($items) || !is_array($items)) return;
    foreach($items as $it) {
      $this->prepData['externallinks'][] = $it;
    }
  }

  private function parseWiki() {
    $wiki =& $this->rawData['wiki'];
    if(!isset($wiki) || !$wiki) return;
    if(preg_match('~\|photo\s*=\s*\[\[Image:(.*)\|~', $wiki, $m)) {
      $this->prepData['mainPhoto'] = trim($m[1]);
    }

    if(preg_match('~Biobox new~', $wiki)) {
      // ethnicity2|nationality2|hair color2
      if(preg_match_all('~\|\s*(name|alias|birth month|birth day|birth year|birth ref|birth location|death month|death day|death year|death ref|death location|years active|ethnicity|nationality|measurements|bra\/cup size|natural tits|natural tits ref|implant type|pierced nipples|height|weight|body type|eye color|hair color|hair length|hair shape|underarm hair|pubic hair|blood|playmatemonth|playmateyear|petmonth|petyear|topless|bush|frontal|full frontal|pink|solo|solodildo|solofisting|lesbian|lesbiandildo|lesbianfisting|blowjob|hardcore|anal|dp|creampie|fisting|bondage|watersports|fistingmen|homepage|blog|livejournal|twitter|tumblr|facebook|myspace|youtube|dailymotion|instagram|modelmayhem|onemodelplace|purestorm|deviantart|flickr|imdb|iafd|afdb|egafd|bgafd)\s*=(.*)?$~m', $wiki, $m)) {
        $this->hasProps = true;
        foreach($m[1] as $idx => $name) {
          $data = trim($m[2][$idx]);
          if($data) {
            $this->prepData['props'][$name] = $data;
          }
        }
      }
    }

    if(preg_match('~{{filmography start\|.*?}}(.*)?{{filmography end}}?~s', $wiki, $m)) {
      if(preg_match_all('~{{film\|\s*(.*)\s*?\|.*?}}~', $m[1], $m)) {
        $this->prepData['films'] = $m[1];
      }
    }

    // TODO "Big tit movies"
    // "External sites"
  }

  // update boob_pages set prepared_data = '', is_processed = 0, is_props = 0;

  private $boolProps = array('natural tits' => 1, 'pubic hair' => 1, 'topless' => 1, 'bush' => 1, 'full frontal' => 1, 'frontal' => 1,
      'pink' => 1, 'solo' => 1, 'solodildo' => 1, 'solofisting' => 1, 'lesbian' => 1, 'lesbiandildo' => 1, 'lesbianfisting' => 1,
      'blowjob' => 1, 'hardcore' => 1, 'creampie' => 1, 'anal' => 1, 'dp' => 1, 'fisting' => 1, 'bondage' => 1,
      'watersports' => 1, 'fistingmen' => 1);

  const FT_CM = 30.48;
  const IN_CM = 2.54;
  const LB_KG = 0.45359237;
  private function canonize() {
    if($this->hasProps) {

      // Для "тезок" - по свойству
      if(isset($this->prepData['props']['name'])) {
        $this->prepData['name'] = $this->prepData['props']['name'];
      }

      // ошибка в документации?
      if(isset($this->prepData['props']['full frontal'])) {
        $this->prepData['props']['frontal'] = $this->prepData['props']['full frontal'];
      }

      // канонизируем булевы значения
      $intersect = array_intersect_key($this->prepData['props'], $this->boolProps);

      if(count($intersect) > 0) {
        foreach($intersect as $key => $val) {
          $this->prepData['props'][$key] = $this->getBool($val);
        }
      }

      // размеры Bust-waist-hip
      if(isset($this->prepData['props']['measurements'])) {
        $this->prepData['props']['measurements'] = preg_replace('~\s*~', '', $this->prepData['props']['measurements']);
        if(preg_match('~([0-9A-Z".]*)-([0-9".?]*)-([0-9".?]*)~', $this->prepData['props']['measurements'], $m)) {
          $this->prepData['bust'] = round(preg_replace('~[^0-9.]~', '', $m[1]) * self::IN_CM);
          $this->prepData['waist'] = round(preg_replace('~[^0-9.]~', '', $m[2]) * self::IN_CM);
          $this->prepData['hip'] = round(preg_replace('~[^0-9.]~', '', $m[3]) * self::IN_CM);
        } else {
          $this->prepData['props']['measurements'].PHP_EOL;
          // echo $this->prepData['props']['measurements'].PHP_EOL;
        }
      }


      // рост
      if(isset($this->prepData['props']['height'])) {
        $this->prepData['props']['height'] = preg_replace('~\s*~', '', $this->prepData['props']['height']);
        if(preg_match('~{{height\|ft=([0-9.]*)(\|in=([0-9.]*))?}}~', $this->prepData['props']['height'], $m)) {
          if(!isset($m[3])) $m[3] = 0;
          $this->prepData['props']['height'] = round((self::FT_CM * $m[1]) + (self::IN_CM * $m[3]));
        } else {
          if(preg_match('~{{height\|m=([0-9.]*)}}~', $this->prepData['props']['height'], $m)) {
            $this->prepData['props']['height'] = round($m[1] * 100);
          } else {
            $this->prepData['props']['height'] = null;
            // echo $this->prepData['props']['height'].PHP_EOL;
          }
        }
      }


      // вес
      if(isset($this->prepData['props']['weight'])) {
        $this->prepData['props']['weight'] = preg_replace('~\s*~', '', $this->prepData['props']['weight']);

        if(preg_match('~{{(W|w)eight\|(lb|lbs|pounds)=([0-9.]*)}}~', $this->prepData['props']['weight'], $m)) {
          $this->prepData['props']['weight'] = round(self::LB_KG * $m[2]);
        } else {
          if(preg_match('~{{(W|w)eight\|kg=([0-9.]*)}}~', $this->prepData['props']['weight'], $m)) {
            $this->prepData['props']['weight'] = $m[2];
          } else {
            $this->prepData['props']['weight'] = null;
            // echo $this->prepData['props']['weight'].PHP_EOL;
          }
        }

      }

      // грудь
      if(isset($this->prepData['props']['bra/cup size'])) {
        if(preg_match('~{{(b|B)ra\|([0-9]*)\|([A-Z]*)(\|.*?)?}}~', $this->prepData['props']['bra/cup size'], $m)) {
          $this->prepData['props']['band'] = $m[2];
          $this->prepData['props']['cup'] = $m[3];
        } else {
          if(preg_match('~{{(b|B)ra\|([A-Z]*)( metric)?}}~', $this->prepData['props']['bra/cup size'], $m)) {
            $this->prepData['props']['cup'] = $m[2];
          } else {
            // echo $this->prepData['props']['bra/cup size'].PHP_EOL;
          }
        }
      }

      if(isset($this->prepData['props']['ethnicity'])) {
        $this->prepData['props']['ethnicity'] = mb_strtolower(preg_replace('~\s*~', '', $this->prepData['props']['ethnicity']));
        if(!preg_match('~^(Caucasian|Asian|Black|Latina|South Asian|Native American|Middle eastern|Jewish|Pacific Islander|Mixed race|Indian)$~i',
          $this->prepData['props']['ethnicity'])) {
            $this->prepData['props']['ethnicity'] = null;
        }
      }

      if(isset($this->prepData['props']['nationality'])) {
        $this->prepData['props']['nationality'] = ucfirst(mb_strtolower(preg_replace('~\s*~', '', $this->prepData['props']['nationality'])));
      }

      if(isset($this->prepData['props']['body type'])) {
        $this->prepData['props']['body type'] = mb_strtolower(preg_replace('~\s*~', '', $this->prepData['props']['body type']));
      }

      if(isset($this->prepData['props']['eye color'])) {
        $this->prepData['props']['eye color'] = mb_strtolower(preg_replace('~\s*~', '', $this->prepData['props']['eye color']));
        if(preg_match_all('~(Hazel|Brown|Blue|Green|Black|Grey)~i',
          $this->prepData['props']['eye color'], $m)) {
            $this->prepData['props']['eye color'] = $m[1];
        } else {
          $this->prepData['props']['eye color'] = null;
        }
      }

      if(isset($this->prepData['props']['hair color'])) {
        $this->prepData['props']['hair color'] = mb_strtolower(preg_replace('~\s*~', '', $this->prepData['props']['hair color']));
        if(preg_match_all('~(Brunette|Blond(?:e|)|Black|Brown|Redhead|Red)~i',
          $this->prepData['props']['hair color'], $m)) {
            foreach($m[1] as &$_m) {
              if($_m == 'blond') $_m = 'blonde';
            }
            unset($_m);
            $this->prepData['props']['hair color'] = $m[1];
        } else {
          $this->prepData['props']['hair color'] = null;
        }
      }

      if(isset($this->prepData['props']['hair length'])) {
        $this->prepData['props']['hair length'] = mb_strtolower(preg_replace('~\s*~', '', $this->prepData['props']['hair length']));
        $this->prepData['props']['hair length'] = preg_replace('~Shoulder( length)?~i', 'medium', $this->prepData['props']['hair length']);
        if(preg_match_all('~(Long|Short|Medium)~i',
          $this->prepData['props']['hair length'], $m)) {
            $this->prepData['props']['hair length'] = $m[1];
        } else {
          $this->prepData['props']['hair length'] = null;
        }
      }

      // удаляем те, что не удалось канонизировать
      $this->prepData['props'] = array_filter($this->prepData['props']);

      // die(var_dump($this->prepData));

    }
  }

  private function getBool($str) {
    if(preg_match('~^(N|n|NO|no|No|None|none|Nope|nope|0)$~', $str)) return false;
    if(preg_match('~^(Y|y|YES|yes|Yes|Yeah|yeah|1)$~', $str)) return true;
    return null;
  }



  private function save($page, $prepData, $hasProps) {
    $query = "UPDATE `boob_pages` SET `is_processed` = 1, `is_props` = ".(int)$hasProps.", `prepared_data` = '".addslashes(serialize($prepData))."' WHERE `type` = '".addslashes($page['type'])."' AND `id` = ".(int)$page['id'];
    $this->db->query($query);
  }

  private $offset = 0, $limit = 100;
  private function getChunk($nameSpace) {
    $query = "SELECT * FROM `boob_pages` WHERE `is_processed` = 0 AND `is_loaded` = 1 AND `type` = '".addslashes($nameSpace)."' LIMIT {$this->limit}";
    $results = $this->db->SelectSet($query);
    if(!is_array($results) || count($results) == 0) return false;
    // $this->offset += $this->limit;
    return $results;
  }

}

$pp = new PageParser();
$pp->run();
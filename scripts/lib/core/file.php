<?php

/**
 * @package Сore
 */
 
/**
 * Файл-объект с методами для обработки
 * @property object $JQuery JQuery-like документ-объект
 * @property string $tidy Текст документа, обработанный Tidy
 * @property string $line Текст документа в одну строку, обработанный Tidy
 * @property string $shortline Текст документа в одну строку, обработанный Tidy, с вырезанными свойствами
 */

class File {
  private $uri, $source, $tidy, $JQuery, $line, $shortline;
  
  /**
   * @param string $uri URI для загрузки файла (путь к файлу)
   */
  
  public function __construct($uri) {
    $this->uri = $uri;
  }
  
  public function __get($name) {
    if (isset($this->$name)) return $this->$name;
    $method = 'property'.ucfirst($name);
    if (method_exists($this, $method)) return $this->$method();
  }
  
  public function __toString() {
    return $this->uri.' Length:'.mb_strlen($this->source);
  }
  
  /**
   * Загрузка файла данных
   */
  
  public function load() {
    @$this->source = file_get_contents($this->uri);
    if($this->source === false) return false;
    $this->source = iconv('WINDOWS-1251', 'UTF-8', $this->source);
    return true;
  }
  
  private $tidyConfig = array(
      'clean' => false, 
      'wrap' => 0,
      'indent' => true,
      'drop-empty-paras' => true, // This option specifies if Tidy should discard empty paragraphs.
      'drop-font-tags' => true,
      'drop-proprietary-attributes' => true,
      'logical-emphasis' => true,
      'lower-literals' => true,
      'merge-divs' => true,
      'merge-spans' => true,
      'show-body-only' => true,
      'word-2000' => true,
      'quote-marks' => true,
      'char-encoding' => 'utf8', 
      'input-encoding' => 'utf8', 
      'output-encoding' => 'utf8'
  );
  
  private function getOneLine($text) {
    $text = preg_replace('~\r?\n~', ' ', $text); // удаляем перевод строки
    $text = preg_replace('~\s{2,}~', ' ', $text); // удаляем лишние пробелы
    $text = str_replace('= ', '=', $text);
    $text = str_replace(' =', '=', $text);
    $text = str_replace(' >', '>', $text);
    $text = str_replace('< ', '<', $text);
    return $text;
  }
      
  private function propertyTidy() {
    $tidy = tidy_parse_string($this->source, $this->tidyConfig, 'utf8');
    $tidy->cleanRepair();
    $tidy = $tidy->value;
    preg_match('~<div class="innertube" id="maincontent">\n(.*)<div class="nonPrintable innertube" id="footer">~is', $tidy, $matches);
    $tidy = $matches[1];
    
    $tidy = tidy_parse_string($tidy, $this->tidyConfig, 'utf8');
    $tidy->cleanRepair();
    $this->tidy = $tidy->value;

    return $this->tidy;
  }
  
  /**
   * JQuery-like parser
   *
   * @link http://xdan.ru/Uchimsya-parsit-saity-s-bibliotekoi-PHP-Simple-HTML-DOM-Parser.html Example
   * @return object
   */
  private function propertyJQuery() {
    $this->JQuery = $this->__get('line');
    $this->JQuery = str_get_html($this->JQuery);
    return $this->JQuery;
  }
  
  private function propertyLine() {
    $this->line = $this->getOneLine($this->__get('tidy'));
    return $this->line;
  }
  
  private function propertyShortline() {
    $this->shortline = $this->__get('line');
    $this->shortline = preg_replace('~\s*(style|width|border|cellspacing|align|valign|target|height)="[^"]*"~i', '', $this->shortline);
    return $this->shortline;
  }

}
<?php

/**
 * @package Сore
 */

/**
 * Заготовка парсера
 */

class Parser {

  protected $dbo;

  /**
   * Конструктор инициирует объект
   */

  public function __construct() {
    $this->dbo = PFactory::getDbo();
  }

  /**
   * Запуск парсера путем вызова parse() дочернего объекта
   */
  public static function run() {
    $parser = new static();
    $parser->parse();
  }

  /**
   * Установка загрузчика файла
   */

  protected $fileGetter;
  public function setFileGetter(FileGetter $fileGetter) {
    $this->fileGetter = $fileGetter;
  }


  // Helpers


  /**
   * Очистка массива от пустых значений с триммингом
   */

  private static function cleanArrayCallback($result, $_item) {
    $item = trim($_item);
    if(!empty($item)) $result[] = $item;
    return $result;
  }

  /**
   * Очистка массива от пустых элементов с триммингом
   */

  public static function cleanArray($_array) {
    if(!is_array($_array) || count($_array) == 0) return array();
    return array_reduce($_array, array('self', 'cleanArrayCallback'), array());
  }

  /**
   * Преобразование ul-списка в массив
   *
   * @param string $text html-текст, содержащий [ul]..[/ul]
   * @return array
   */

  public static function getUlArray($text) {
    $array = explode("</li>", $text);
    foreach ($array as &$val) {
      $val = preg_replace('~<li.*>|<\/?ul.*>~Ui', '', $val);
    }
    return self::cleanArray($array);
  }

  /**
   * Преобразование таблицы table в массив (без учета colspan и rowspan)
   *
   * @param string $text html-текст, содержащий [table]..[/table]
   * @return array
   */

  public static function getTableArray($text) {
    $array = explode("</tr>", $text);
    foreach ($array as &$val) {
      $val = preg_replace('~<tr.*>~Ui', '', $val);
      $tdArray = explode("</td>", $val);
      foreach ($tdArray as &$tdVal) {
        $tdVal = preg_replace('~<\/?td.*>~Ui', '', $tdVal);
      }
      $val = self::cleanArray($tdArray);
    }
    return array_filter($array);
  }

  /**
   * Преобразование массива-таблицы с шапкой по-горизонтали в структурированный массив
   *
   * Вход:
   *  [key1][val1]([val3], ...)
   *  [key2][val2]([val4], ...)
   *
   * Выход:
   *  array('key1' => 'val1', 'key2' => 'val2') или
   *  array('key1' => array('val1', 'val3'), 'key2' => array('val2', 'val4'))
   *
   * @param array $array массив (см описание)
   * @return array
   */

  public static function tableArrayToKeyValue($array) {
    $ret = array();
    foreach($array as $tr) {
      $values = array_slice($tr, 1);
      switch(count($values)) {
        case 0:
          break;
        case 1:
          $ret[$tr[0]] = $values[0];
          break;
        default:
          $ret[$tr[0]] = $values;
      }
    }
    return array_filter($ret);
  }

  /**
   * Преобразование массива-таблицы с шапкой по-вертикали в структурированный массив
   *
   * Вход:
   *  [key1][key2]
   *  [val1][val2]
   * ([val3][val4]) ...
   *
   * Выход:
   *  array('key1' => 'val1', 'key2' => 'val2') или
   *  array('key1' => array('val1', 'val3'), 'key2' => array('val2', 'val4'))
   *
   * @param array $array массив (см описание)
   * @return array
   */

  public static function tableArrayToKeyValueHor($array) {
    if(count($array) < 2) return null;
    $ret = array();
    $trValues = array_slice($array, 1);
    foreach($array[0] as $key => $val) {
      $val = trim(strip_tags($val));
      switch(count($trValues)) {
        case 1:
          $value = isset($trValues[0][$key]) ? $trValues[0][$key] : null; //strip_tags();
          break;
        default:
          $value = array();
          foreach($trValues as $trVal) {
            $value[] = $trVal[$key];
          }
      }
      $ret[$val] = $value;
    }
  return $ret;
  }

  /**
   * Преобразование массива-таблицы с двумя шапками в структурированный массив
   *
   * Вход:
   *  [key1][key2]
   *  [key3][val1]
   *  [key4][val2]
   *
   * Выход:
   *  array('key3' => array('key2' => 'val1'), 'key4' => array('key2' => 'val2'))
   *
   * @param array $array массив (см описание)
   * @param callable $callBackKeyHor замыкание для обработки горизонтальных ключей
   * @param callable $callBackKeyVert замыкание для обработки вертикальных ключей
   * @param callable $callBackVal замыкание для обработки значений
   * @return array
   */

  public static function tableArrayToKeyValueBoth($array, $callBackKeyHor = null, $callBackKeyVert = null, $callBackVal = null) {
    if(count($array) < 2) return null;
    $ret = array();
    $thHor = array_slice($array, 0, 1);
    $thHor = $thHor[0];
    $tdHor = array_slice($array, 1);

    if(isset($callBackKeyHor)) foreach ($thHor as $key => $val) {
      $thHor[$key] = call_user_func($callBackKeyHor, $key, $val);
    }

    foreach($tdHor as $tr) {
      switch(count($tr)) {
        case 1:
          return null;
          break;
        default:
          $structure = array();
          foreach($tr as $trKey => $trVal) {
            if($trKey == 0) {
              $keyName = isset($callBackKeyVert) ? call_user_func($callBackKeyVert, $trKey, $trVal) : $trVal;
              continue;
            }
            $trVal = isset($callBackVal) ? call_user_func($callBackVal, $trKey, $trVal) : $trVal;
            $structure[$thHor[$trKey]] = $trVal;
          }
      }
      $ret[$keyName] = $structure;
    }
    return $ret;
  }


}
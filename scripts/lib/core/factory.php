<?php

/**
 * @package Сore
 */

/**
 * Фабрика объектов
 */

abstract class PFactory {

  /**
   * Load additional classes
   *
   */

  public function load($_baseClassName)
  {
    if(is_array($_baseClassName) && count($_baseClassName) == 0) return false;
    if(empty($_baseClassName)) return false;
    $baseClassNames = (array)$_baseClassName;
    foreach($baseClassNames as $baseClassName)
    {
      $fullClassName = 'Parse'.ucfirst($baseClassName);
      require_once("../classes/$fullClassName.php");
    }
    return true;
  }

  /**
   * Init common classes
   *
   */

  private static $dir, $config;
  public static function init($dir = '')
  {
    self::$dir = $dir ? $dir : dirname(dirname(__DIR__)).'/';
    require_once(self::$dir.'../etc/config.php');

    require_once(self::$dir.'lib/core/mysql.php');
    require_once(self::$dir.'lib/core/parser.php');
    require_once(self::$dir.'lib/core/filegetter.php');
    require_once(self::$dir.'lib/core/file.php');
    require_once(self::$dir.'lib/core/document.php');
    require_once(self::$dir.'lib/core/logger.php');
    require_once(self::$dir.'lib/core/messages.php');
    require_once(self::$dir.'lib/core/cli.php');

    require_once(self::$dir.'lib/extend/simple_html_dom/simple_html_dom.php');
    self::$config = new PConfig();
  }

  /**
	 * Get library directory.
	 *
	 */

  public static function getDir()
  {
    return self::$dir;
  }

  /**
	 * Get a database object.
	 *
	 */

  public static $database = null;
	public static function getDbo()
	{
		if (!self::$database)
		{
			self::$database = new DataBaseMysql(self::$config);
		}

		return self::$database;
	}

  /**
   * Set global option
   *
   */

  private static $options = array();
  public static function setOpt($name, $value)
  {
    self::$options[$name] = $value;
  }

  /**
   * Get global option
   *
   */

  public static function getOpt($name)
  {
    return isset(self::$options[$name]) ? self::$options[$name] : null;
  }

}


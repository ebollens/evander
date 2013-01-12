<?php

/**
 * Config object that contains all configuration settings. These are stored
 * statically, though multiple instances of the config object may be defined
 * and all access the same set of data.
 *
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Config
{
    /**
     * Static container of configuration data.
     *
     * @var array
     */
    private static $_data = array();

    /**
     * Initializes $CONFIG global with all configuration settings in config.php.
     *
     * @global Config $CONFIG 
     */
    public static function init()
    {
        global $CONFIG;
        $CONFIG = new Config();
        include_once(Core::DIR.'/config.php');
    }

    /**
     * Magic method that stores data within the static data container.
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        self::$_data[$name] = $value;
    }

    /**
     * Magic method that returns data from the static data container.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return self::get($name);
    }

    /**
     * Returns data from the static data container.
     *
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        return self::$_data[$name];
    }
}

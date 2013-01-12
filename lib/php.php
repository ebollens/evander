<?php

/**
 * PHP version utility library.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class PHP
{
    /**
     * Returns true if the PHP engine has a newer version than the PHP version
     * formatted string $version.
     *
     * @param string $version
     * @return bool
     */
    public static function is_newer_than_version($version)
    {
        return self::is_newer_than_version_id(self::version_id_from_version($version));
    }

    /**
     * Returns true if the PHP engine has a newer version ID than $version_id.
     *
     * @param int $version_id
     * @return bool
     */
    public static function is_newer_than_version_id($version_id)
    {
        return $version_id < self::version_id();
    }

    /**
     * Return the PHP version formatted string for the PHP engine.
     *
     * @return string
     */
    public static function version()
    {
        return PHP_VERSION;
    }

    /**
     * Return the PHP version ID for the PHP engine.
     *
     * @return int
     */
    public static function version_id()
    {
        if(defined('PHP_VERSION_ID'))
        {
            return PHP_VERSION_ID;
        }

        return self::version_id_from_version(self::version());
    }

    /**
     * Generate a PHP version ID from a PHP version formatted string.
     *
     * @param string $version_string
     * @return string
     */
    public static function version_id_from_version($version_string)
    {
        $version_arr = explode('.', $version_string);
        return $version_arr[0]*10000 + $version_arr[1]*100 + $version_arr[2];
    }
}

<?php

/**
 * Helper that produces web-accessible URLs.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class URL
{
    /**
     * Web-accessible URL for $name under site root.
     * 
     * @param string $name
     * @return string 
     */
    public static function root($name)
    {
        return Config::get('site_url').'/'.$name;
    }

    /**
     * Web-accessible URL for $name under /asset.
     * 
     * @param string $name
     * @return string 
     */
    public static function asset($name)
    {
        return self::root('asset/'.$name);
    }

    /**
     * Web-accessible URL for $name under /asset/css.
     * 
     * @param string $name
     * @return string 
     */
    public static function css($name)
    {
        return self::asset('css/'.$name);
    }

    /**
     * Web-accessible URL for $name under /asset/img.
     * 
     * @param string $name
     * @return string 
     */
    public static function img($name)
    {
        return self::asset('img/'.$name);
    }

    /**
     * Web-accessible URL for $name under /asset/js.
     * 
     * @param string $name
     * @return string 
     */
    public static function js($name)
    {
        return self::asset('js/'.$name);
    }

    /**
     * Web-accessible URL for $name under /asset/template.
     * 
     * @param string $name
     * @return string 
     */
    public static function template($name)
    {
        return self::asset('template/'.$name);
    }
}
<?php

/**
 * Helper that produces filesystem paths.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Path
{
    /**
     * Filesystem path for $name under /asset.
     * 
     * @param string $name
     * @return string 
     */
    public static function asset($name)
    {
        return Core::DIR.'/'.Core::DIR_WEB.'/'.Core::DIR_WEB_ASSET.'/'.$name;
    }

    /**
     * Filesystem path for $name under /hook.
     * 
     * @param string $name
     * @return string 
     */
    public static function hook($name)
    {
        return Core::DIR.'/'.Core::DIR_HOOK.'/'.$name;
    }

    /**
     * Filesystem path for $name under /lib.
     * 
     * @param string $name
     * @return string 
     */
    public static function lib($name)
    {
        return Core::DIR.'/'.Core::DIR_LIB.'/'.$name;
    }
    
    /**
     * Filesystem path for $name under /mvc.
     * 
     * @param string $name
     * @return string 
     */
    public static function mvc($name)
    {
        return Core::DIR.'/'.MVC::get_base_dir().'/'.$name;
    }
    
    /**
     * Filesystem path for $name under /mvc/view.
     * 
     * @param string $name
     * @return string 
     */
    public static function mvc_view($name)
    {
        return self::mvc(MVC::get_view_dir().'/'.$name);
    }

    /**
     * Filesystem path for $name under /asset/template.
     * 
     * @param string $name
     * @return string 
     */
    public static function template($name)
    {
        return Core::DIR.'/'.Core::DIR_ASSET.'/'.Core::DIR_ASSET_TEMPLATE.'/'.$name;
    }

    /**
     * Filesystem path for $name view. If MVC is initialized and a view is
     * defined under /mvc/view, then it will be selected unless $use_mvc is 
     * false. Otherwise, returns the path under /asset/view.
     * 
     * @param string $name
     * @param bool $use_mvc
     * @return string 
     */
    public static function view($name, $use_mvc = true)
    {
        if($use_mvc && MVC::is_initialized() && file_exists(self::mvc_view($name)))
        {
            return self::mvc_view($name);
        }
        
        return Core::DIR.'/'.Core::DIR_ASSET.'/'.Core::DIR_ASSET_VIEW.'/'.$name;
    }
    
    public static function file($name)
    {
        global $CONFIG;
        return $CONFIG->file_root . '/' . $name;
    }
}
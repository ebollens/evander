<?php

/**
 * Class auto-loader.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

require_once(dirname(__FILE__).'/core.php');

class Loader
{
    public static function load_class($class)
    {
        if(class_exists($class))
        {
            return true;
        }
        
        if($class != 'MVC' && class_exists('MVC') && MVC::is_initialized() && MVC::load_class($class))
        {
            return true;
        }
        
        if(Core::load_class($class))
        {
            return true;
        }
        
        throw new Core_Class_Exception('Class_Lib::load could not load "'.$class.'"');
    }
}
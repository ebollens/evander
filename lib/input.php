<?php

/**
 * Input handler.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class Input
{
    public static function get($key, $sanitize = Sanitize::NO)
    {
        if(!isset($_GET[$key]))
            return false;

        $value = is_string($_GET[$key])
                        ? stripslashes($_GET[$key])
                        : $_GET[$key];

        if($sanitize != Sanitize::NO)
            return Sanitize::clean($raw, $type);
        
         return $value;
    }
    
    public static function post($key, $sanitize = Sanitize::NO)
    {
        if(!isset($_POST[$key]))
            return false;

        $value = is_string($_POST[$key])
                        ? stripslashes($_POST[$key])
                        : $_POST[$key];

        if($sanitize != Sanitize::NO)
            return Sanitize::clean($raw, $type);
        
        return $value;
    }
    
    public static function file($name)
    {
        return isset($_FILES[$name]) && $_FILES[$name]['size'] > 0 ? $_FILES[$name] : false;
    }
}
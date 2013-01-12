<?php

/**
 * Session management.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class Session
{
    public static function init()
    {
        if(headers_sent())
        {
            throw new Session_Exception('Session could not be initialized as headers have already been sent.');
        }
        
        session_start();
    }
    
    public static function get($name, $throw_exception = true)
    {
        if(!isset($_SESSION[$name]))
        {
            if($throw_exception)
            {
                throw new Session_Exception('Attempting to access undefined property of session.');
            }
            else
            {
                return false;
            }
        }
        
        return $_SESSION[$name];
    }
    
    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }
    
    public static function is_set($name)
    {
        return isset($_SESSION[$name]);
    }
    
    public static function reset()
    {
        session_unset();
        session_regenerate_id(true);
        session_destroy();
        session_start();
    }
}

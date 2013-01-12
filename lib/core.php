<?php

/**
 * Core application manager and loader.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

if(!defined('DIR_ROOT'))
    define('DIR_ROOT', dirname(dirname(__FILE__)));

class Core
{
    /**
     * Core major version number.
     * 
     * @var string
     */
    const VERSION_MAJOR = 3;
    
    /**
     * Core minor version number.
     * 
     * @var string
     */
    const VERSION_MINOR = 1;
    
    /**
     * Core revision number.
     * 
     * @var string
     */
    const VERSION_REVISION = 0;
    
    /**
     * Directory root for the core instance.
     * 
     * @var string
     */
    const DIR = DIR_ROOT;
    
    /**
     * Directory under the directory root for non-web-accessible assets.
     * 
     * @var string
     */
    const DIR_ASSET = 'asset';
    
    /**
     * Directory under the non-web-accessible asset root for template files.
     * 
     * @var string
     */
    const DIR_ASSET_TEMPLATE = 'template';
    
    /**
     * Directory under the non-web-accessible asset root for view files.
     * 
     * @var string
     */
    const DIR_ASSET_VIEW = 'view';
    
    /**
     * Diretory under the directory root for hook definitions.
     * 
     * @var string
     */
    const DIR_HOOK = 'hook';
    
    /**
     * Directory under the directory root for class definitions.
     * 
     * @var string
     */
    const DIR_LIB = 'lib';
    
    /**
     * Directory under the lib root for model definitions.
     * 
     * @var string
     */
    const DIR_LIB_MODEL = 'model';
    
    /**
     * Directory under the lib root for exception class definitions.
     * 
     * @var string
     */
    const DIR_LIB_EXCEPTION = 'exception';
    
    /**
     * Directory under the lib root for interface definitions.
     * 
     * @var string
     */
    const DIR_LIB_INTERFACE = 'interface';
    
    /**
     * Web-accessible directory.
     * 
     * @var string
     */
    const DIR_WEB = 'web';
    
    /**
     * Web-accessible assets under web-accessible directory.
     * 
     * @var string
     */
    const DIR_WEB_ASSET = 'asset';
    
    public static function load_class($class)
    {
        // All class names should be lower case.
        $class = strtolower($class);
        
        // Classes are stored under Core::DIR_LIB
        $path = DIR_ROOT.'/'.Core::DIR_LIB;

        // Exceptions are stored in Core::DIR_LIB/exception
        if($is_exception = (substr($class, strlen($class)-10, 10) == '_exception'))
        {
            $path .= '/'.Core::DIR_LIB_EXCEPTION;
        }
        // Interfaces are stored in Core::DIR_LIB/interface
        elseif($is_interface = (substr($class, strlen($class)-10, 10) == '_interface'))
        {
            $path .= '/'.Core::DIR_LIB_INTERFACE;
        }
        // Models are stored in Core::DIR_LIB/model
        elseif(substr($class, strlen($class)-6, 6) == '_model')
        {
            $path .= '/'.Core::DIR_LIB_MODEL;
        }
        
        // Explode class name into components separated by "__"
        $components = explode('__', $class);
        
        // Class name is the last component
        $filename = array_pop($components);
        
        // Append additional components as directory names
        while(count($components) > 0)
        {
            $path .= '/'.array_shift($components);
        }

        // If definition file exists, include it to load the class
        if(file_exists($path.'/'.$filename.'.php'))
        {
            include_once($path.'/'.$filename.'.php');
        }
        // If an undefined exception, create a generic.
        elseif(isset($is_exception) && $is_exception)
        {
            eval('class ' . $class . ' extends Exception {}');
        }
        // If an undefined interface, create a generic.
        elseif(isset($is_interface) && $is_interface)
        {
            eval('interface ' . $class . ' {}');
        }
        
        return true;
    }
    
    public static function get_version()
    {
        return Core::VERSION_MAJOR.'.'.Core::VERSION_MINOR.'.'.Core::VERSION_REVISION;
    }
}

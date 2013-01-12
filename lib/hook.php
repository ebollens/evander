<?php

/**
 * Hook manager that instantiates a single instance of each hook object, makes
 * it accessible, and allows one to execute methods within the hook object.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Hook
{
    /**
     * Array of defined hook objects keyyed by class name.
     *
     * @var array
     */
    public static $_hooks = array();

    /**
     * Execute the specified $method of the $class hook object (adds _hook
     * suffix if not provided) with $params defined as an array of parameters
     * that will be passed to the method call.
     *
     * @param string $class
     * @param string $method
     * @param array $params
     * @return bool
     */
    public static function execute($class, $method, $params = array())
    {
        $object =& self::object($class);

        if(!method_exists($object, $method))
            return false;

        call_user_func_array(array($object, $method), $params);
        return true;
    }

    /**
     * Allows one to set $object as the hook for its class.
     *
     * @param object $object 
     */
    public static function set_object(&$object)
    {
        self::$_hooks[strtolower(get_class($object))] = $object;
    }

    /**
     * Accessor for the hook object of $class, defining it if it is not yet
     * defined.
     *
     * @param string $class
     * @throws Hook_Exception
     * @return object
     */
    private static function &object($class)
    {
        // Hooks should be lower case for pattern matching.
        $class = strtolower($class);
        
        // Hook class objects should all be suffixed with "_hook"
        if(substr($class, strlen($class)-5, 5) != '_hook')
            $class .= '_hook';

        // If the object for the hook class does not yet exist, instantiate it.
        if(!isset(self::$_hooks[$class]))
        {
            // Throw an exception if the hook definition file does not exist.
            if(!file_exists(self::_path($class)))
                throw new Hook_Exception('Hook class definition file does not exist.');

            // Include the hook class definition file.
            include_once(self::_path($class));

            // Throw an exception if the hook definition file does not contain a class definition for the file.
            if(!class_exists($class))
                throw new Hook_Exception('Hook class definition file does not contain a class definition for the hook.');

            self::set_object(new $class());
        }

        // Return the hook object.
        return self::$_hooks[$class];
    }

    /**
     * Private member for getting a hook class definition file path.
     *
     * @param string $class
     * @return string
     */
    private static function _path($class)
    {
        return Path::hook(strtolower($class).'.php');
    }
}
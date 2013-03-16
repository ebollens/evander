<?php

/**
 * MVC router and handler that drives execution of a controller object. This is
 * generally turned on by setting $CONFIG->use_mvc, but can also be turned on by
 * manually calling MVC::init(). This generally executes during the
 * Bootstrap::shutdown() phase, but may be executed earlier through calling
 * MVC::execute() directly.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class MVC
{
    const FILE = 'index.php';
    
    /**
     * URI separator on first query string parameter.
     */
    const SEPARATOR = '/';
    
    /**
     * Default controller if no controller specified in URI.
     */
    const DEFAULT_CONTROLLER = 'main';
    
    /**
     * Default method if no method specified in URI.
     */
    const DEFAULT_METHOD = 'index';
    
    /**
     * True if MVC::init() has been executed. MVC::execute() will not execute
     * if this is false when called.
     * 
     * @var bool
     */
    private static $_init = false;
    
    /**
     * The first query string parameter that defines MVC rounting.
     * 
     * @var string 
     */
    private static $_path = false;
    
    /**
     * Exploded version of the first query string parameter for MVC routing.
     * 
     * @var array 
     */
    private static $_path_segments = false;
    
    /**
     * Controller object set up by MVC::init() and triggered by MVC::execute().
     * 
     * @var object 
     */
    private static $_controller = null;
    
    /**
     * True if MVC::execute() has been executed. MVC::execute() will not
     * execute additional times unless called as MVC::execute(false) if this
     * parameter is true.
     * 
     * @var bool
     */
    private static $_executed = false;
    
    private static $_dir_base = 'mvc';
    
    private static $_dir_controller = 'controller';
    
    private static $_dir_exception = 'exception';
    
    private static $_dir_interface = 'interface';
    
    private static $_dir_lib = 'lib';
    
    private static $_dir_model = 'model';
    
    private static $_dir_view = 'view';
    
    /**
     * Execute the controller method. This throws an exception if MVC::init()
     * has not been called first, or if the controller method does not exist.
     * This fires the MVC_Hook->preexecution() method before it calls the
     * controller method and fires the MVC_Hook->postexecution() method after
     * it calls the controller method. Unless $run_once is set false, this 
     * method will only execute once. It will return false on subsequent
     * executions. This method will always return true on the first successful
     * call to MVC::execute().
     * 
     * @param bool $run_once
     * @throws MVC_Exception
     * @uses MVC_Hook::preexecution()
     * @uses MVC_Hook::postexecution()
     * @return bool
     */
    public static function execute($run_once = true)
    {
        // Throws an exception if MVC::init() has not been called.
        if(!self::is_initialized())
        {
            throw new MVC_Exception('Cannot execute MVC when not initialized with MVC::init() first.');
        }
        
        // Return false if has executed already and $run_once is not false.
        if(self::has_executed() && $run_once)
        {
            return false;
        }
        
        // Execute the MVC_Hook->preexecution() method.
        try
        {
            Hook::execute('MVC', 'Preexecution');
        }
        catch(Hook_Exception $e){}
        
        // Throw an exception if controller method does not exist.
        if(!method_exists(self::$_controller, self::get_method_name()) && !method_exists(self::$_controller, '__call'))
        {
            throw new MVC_Exception('Controller method "'.self::get_method_name().'" does not exist.');
        }
        
        // Call the controller method with parameters.
        call_user_func_array(array(self::$_controller, self::get_method_name()), self::get_parameters());
        
        // Set executed true so that MVC::has_executed() returns true.
        self::$_executed = true;
            
        // Execute the MVC_Hook->postexecution() method.
        try
        {
            Hook::execute('MVC', 'Postexecution');
        }
        catch(Hook_Exception $e){}
        
        return true;
    }
	
	/**
     * Redirect the visitor to the MVC-driven page specified via an HTTP 
     * Location header. URL generation is derived directly from MVC::url().
     * This cannot be called after any output has been rendered.
     * 
     * @param string|null|array $controller
     * @param string|null $method
     * @param array|string $params
     */
    public static function redirect($controller = null, $method = null, $params = array())
    {
        if(headers_sent())
            throw new HTTP_Sent_Header_Exception('Cannot specify Location as HTTP headers already sent.');

        header('Location: '.MVC::url($controller, $method, $params));
		die();
    }
    
    /**
     * Returns the first query string parameter used for MVC routing.
     * 
     * @return string 
     */
    public static function get_path()
    {
        if(!self::$_path)
        {
            if(($pos = strpos($_SERVER['QUERY_STRING'], '&')) === false)
            {
                self::$_path = $_SERVER['QUERY_STRING'];
            }
            else
            {
                self::$_path = substr($_SERVER['QUERY_STRING'], 0, $pos);
            }
            
            self::$_path_segments = explode(MVC::SEPARATOR, self::$_path);
            
            if(count(self::$_path_segments) > 0 && self::$_path_segments[0] == '')
            {
                array_shift(self::$_path_segments);
            }
        }
        
        return self::$_path;
    }
    
    /**
     * Returns segment $i from the first query string parameter, as delimited by
     * the SEPARATOR constant.
     * 
     * @param int $i
     * @return string 
     */
    public static function get_path_segment($i)
    {
        self::get_path();
        
        if($i >= count(self::$_path_segments))
        {
            return false;
        }
        
        return self::$_path_segments[$i];
    }
    
    public static function get_path_segments()
    {
        self::get_path();
        
        return self::$_path_segments;
    }
    
    /**
     * Returns the controller name (first segment of the MVC routing).
     * 
     * @return string 
     */
    public static function get_controller_name()
    {
        if(!self::get_path_segment(0))
        {
            return MVC::DEFAULT_CONTROLLER.'_controller';
        }
        
        return self::get_path_segment(0).'_controller';
    }
    
    /**
     * Return the controller object if constructed or null otherwise.
     * 
     * @return object
     */
    public static function get_controller_object()
    {
        return self::$_controller;
    }
    
    /**
     * Returns the method name (second segment of the MVC routing).
     * 
     * @return string 
     */
    public static function get_method_name()
    {
        if(!self::get_path_segment(1))
        {
            return MVC::DEFAULT_METHOD;
        }
        
        return self::get_path_segment(1);
    }
    
    /**
     * Returns the parameters passed to the controller method (all segments of 
     * the MVC routing except the first two).
     * 
     * @return string 
     */
    
    public static function get_parameters()
    {
        $i = 2;
        
        $parameters = array();
        
		while(($parameter = self::get_path_segment($i++)) !== false)
        {
            $parameters[] = $parameter;
        }
        
        return $parameters;
    }
    
    /**
     * Returns true if MVC::execute() has already been called.
     * 
     * @return bool
     */
    public static function has_executed()
    {
        return self::$_executed;
    }
    
    /**
     * Sets up the controller if it exists or throws an exception. Triggers the
     * MVC_Hook->init() method after setting up the controller.
     * 
     * @throws MVC_Exception
     */
    public static function init()
    {
        // Set init to true so that MVC::is_initialized() returns true.
        self::$_init = true;
        
        // Get controller name.
        $controller_name = self::get_controller_name();
        
        // Throw an exception if controller class is not defined.
        if(!class_exists($controller_name))
        {
            throw new MVC_Exception('Controller "'.$controller_name.'" does not exist.');
        }
        
        // Define the controller object.
        self::$_controller = new $controller_name();
        
        // Execute the MVC_Hook->init() method.
        try
        {
            Hook::execute('MVC', 'Init');
        }
        catch(Hook_Exception $e){}
    }
    
    /**
     * Returns true if MVC::init() has been called.
     * 
     * @return bool
     */
    public static function is_initialized()
    {
        return self::$_init;
    }
    
    /**
     * Web-accessible URL for an MVC-driven page. This accempts either one
     * array of segments as the $controller parameter or else a controller name,
     * optionally a method name, and then optionally either an array of 
     * parameters or a parameterized string. This last option of a parameterized
     * string should be done carefully to make sure the construction includes
     * separations using MVC::SEPARATOR or else this may not be a valid path
     * depending on what MVC::SEPARATOR is set to.
     * 
     * @param string|null|array $controller
     * @param string|null $method
     * @param array|string $params
     * @return string 
     */
    public static function url($controller = null, $method = null, $params = array())
    {
        $path = MVC::FILE;
        if($controller)
        {
            if(is_array($controller))
            {
                return URL::root($path.'?'.implode(self::SEPARATOR, $controller));
            }
            $path .= '?'.$controller;
            if($method)
            {
                $path .= self::SEPARATOR.$method;
                if($params)
                {
                    if(is_array($params))
                    {
                        if(count($params) > 0)
                        {
                            $path .= self::SEPARATOR.implode(self::SEPARATOR, $params);
                        }
                    }
                    elseif(is_string($params))
                    {
                        $path .= self::SEPARATOR . $params;
                    }
                }
            }
        }
        return URL::root($path);
    }
    
    public static function set_base_dir($dir)
    {
        self::$_dir_base = $dir;
    }
    
    public static function get_base_dir()
    {
        return self::$_dir_base;
    }
    
    public static function set_controller_dir($dir)
    {
        self::$_dir_controller = $dir;
    }
    
    public static function get_controller_dir()
    {
        return self::$_dir_controller;
    }
    
    public static function set_lib_dir($dir)
    {
        self::$_dir_lib = $dir;
    }
    
    public static function get_lib_dir()
    {
        return self::$_dir_lib;
    }
    
    public static function set_exception_dir($dir)
    {
        self::$_dir_exception = $dir;
    }
    
    public static function get_exception_dir()
    {
        return self::$_dir_exception;
    }
    
    public static function set_interface_dir($dir)
    {
        self::$_dir_interface = $dir;
    }
    
    public static function get_interface_dir()
    {
        return self::$_dir_interface;
    }
    
    public static function set_model_dir($dir)
    {
        self::$_dir_model = $dir;
    }
    
    public static function get_model_dir()
    {
        return self::$_dir_model;
    }
    
    public static function set_view_dir($dir)
    {
        self::$_dir_view = $dir;
    }
    
    public static function get_view_dir()
    {
        return self::$_dir_view;
    }
    
    /**
     * Returns true if MVC autoload function loads class, or false otherwise.
     * This is invoked by Core::load_class().
     * 
     * @param string $class
     * @return bool 
     */
    public static function load_class($class)
    {
        $class = strtolower($class);
        if(substr($class, strlen($class)-11, 11) == '_controller')
        {
            $path = MVC::get_controller_dir();
            $class = substr($class, 0, strlen($class)-11);
        }
        else
        {
            $path = MVC::get_lib_dir();
            
            // Exceptions are stored in PATH_LIB/exception
            if($is_exception = (substr($class, strlen($class)-10, 10) == '_exception'))
            {
                $path .= '/'.MVC::get_exception_dir();
            }
            // Interfaces are stored in PATH_LIB/interface
            elseif($is_interface = (substr($class, strlen($class)-10, 10) == '_interface'))
            {
                $path .= '/'.MVC::get_interface_dir();
            }
            // Models are stored in PATH_LIB/model
            elseif(substr($class, strlen($class)-6, 6) == '_model')
            {
                $path .= '/'.MVC::get_model_dir();
            }
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
        if(file_exists(Path::mvc($path.'/'.strtolower($filename).'.php')))
        {
            include_once(Path::mvc($path.'/'.strtolower($filename).'.php'));
            return true;
        }
        
        return false;
    }
}

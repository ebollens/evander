<?php

/**
 * View encapsulates a page to render within an object to make it possible to
 * assign variables into the scope of the page before rendering the content.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class View
{
    /**
     * Path of the view file.
     * 
     * @var string
     */
    private $_path;
    
    /**
     * True if MVC view path should be considered before asset view path.
     * 
     * @var bool
     */
    private $_use_mvc;
    
    /**
     * Variables passed into the view by key name.
     * 
     * @var array
     */
    private $_vars = array();

    /**
     * View constructor. Unless $use_mvc is set false, the $path will consider
     * the MVC view first before the asset view.
     * 
     * @param string $path 
     * @param bool $use_mvc
     */
    public function __construct($path, $use_mvc = true)
    {
        $this->set_view($path, $use_mvc);
    }
    
    /**
     * View factory that operates in the same manner as the constructor except
     * allows for immediate method chaining.
     */
    public static function build($path, $use_mvc = true)
    {
        return new View($path, $use_mvc);
    }

    /**
     * Magic method setter to set a variable in the view.
     * 
     * @param string $name
     * @param mixed $value 
     */
    public function __set($name, $value)
    {
        $this->set_var($name, $value);
    }

    /**
     * Magic method getter to get a variable from the view.
     * 
     * @param string $name
     * @return mixed 
     */
    public function __get($name)
    {
        return $this->get_var($name);
    }
    
    /**
     * Magic method isset checker for a variable in the view.
     * 
     * @param string $name
     * @return bool 
     */
    public function __isset($name)
    {
        return $this->has_var($name);
    }

    /**
     * Setter that redefines the view.
     * 
     * @param string $path 
     */
    public function set_view($path, $use_mvc = true)
    {
        $this->_path = $path;
        $this->_use_mvc = (bool)$use_mvc;
    }

    /**
     * Getter that returns the view path.
     * 
     * @param string $path
     * @return string 
     */
    public function get_path()
    {
        return $this->_path;
    }

    /**
     * Setter to set a variable in the view.
     * 
     * @param string $name
     * @param mixed $value 
     */
    public function &set_var($name, $value)
    {
    	if($value instanceof View)
            $value = $value->render();
    	
        $this->_vars[$name] = $value;
		return $this;
    }
    
    /**
     * Getter that returns an array of variables from the view.
     * 
     * @return array 
     */
    public function get_all_vars()
    {
        return $this->_vars;
    }
    
    /**
     * Getter to get a variable from the view.
     * 
     * @param string $name
     * @return mixed 
     */
    public function get_var($name)
    {
        if(!$this->has_var($name))
            throw new View_Exception('Accessing undefined view variable ('.$this->_path.'::'.$name.').');
        
        return $this->_vars[$name];
    }
    
    /**
     * Isset checker for a variable in the view.
     * 
     * @param string $name
     * @return bool 
     */
    public function has_var($name)
    {
    	return isset($this->_vars[$name]);
    }

    /**
     * Renders the view. In the event that the view does not exist, this method
     * will either throw an exception if $throws is true or print an empty view
     * if $throws is false.
     * 
     * @param bool $throws
     * @throws View_Exception
     * @return string 
     */
    public function render($throws = true)
    {
        if(file_exists(Path::view($this->_path.'.php', MVC::is_initialized())))
        {
            // Start an output buffer.
            ob_start();
            
            // Extract all variables in the view object into local namespace.
            extract($this->get_all_vars());
            
            // Include the view file.
            include(Path::view($this->_path.'.php', MVC::is_initialized()));
            
            // Store output buffer contents in $contents for return.
            $contents = ob_get_contents();
            
            // Clean and end the output buffer.
            ob_end_clean();
            
            // Return the contents of the view.
            return $contents;
        }
        else
        {
            // Throw an exception or echo empty content if view does not exist.
            if($throws)
            {
                throw new View_Exception('View ('.$this->_path.') does not exist.');
            }
            else
            {
                return '';
            }
        }
    }
}
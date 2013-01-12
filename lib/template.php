<?php

/**
 * Template static object that loads all content printed to the page into the
 * $CONTENT variable of the page. It further considers several other variables,
 * namely $CONFIG from the defined configuration variables, $HEAD_ASSETS from
 * the Head_Assets static object, and $BODY_ASSETS from the Body_Assets static
 * object.
 *
 * Template::render() is called through the following call-chain:
 *
 *      (PHP Shutdown)
 *          -> Bootstrap::shutdown()
 *              -> Output::render()
 *                  -> Template::render()
 *
 * Template::render() cannot be called directly.
 *
 * Other static methods are available any point in the execution stack before
 * PHP shutdown begins and can be used to enable/disable the template, define
 * a template file to use in /asset/template, and to define variables that are
 * made available within that template file.
 *
 * Template files generally have the following variables available to them:
 *
 * (1) $CONTENT
 * (2) $CONFIG
 * (3) $HEAD_ASSETS
 * (4) $BODY_ASSETS
 *
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Template
{
    /**
     * True if the template is enabled, or false otherwise.
     *
     * @var bool
     */
    private static $_enabled = true;

    /**
     * Array of $key=>$val pairs that are made available as $$key for $val data.
     *
     * @var array
     */
    private static $_vars = array();

    /**
     * Name of the file in /asset/template that is used as the template file.
     * Must miminally define $CONTENT and should also define $CONFIG,
     * $BODY_ASSETS, $HEAD_ASSETS.
     *
     * @var string
     */
    private static $_template = 'default';

    /**
     * Renders the template file defined as $_template, loading all content
     * printed into the output buffer as $CONTENT within the file and
     * additionally defining $CONFIG, $HEAD_ASSETS and $BODY_ASSETS. It uses
     * asset/template/empty.php to load an empty template file without any
     * wrapping. This can only be called form the Output class given that
     * Output::__construct() is private.
     *
     * @param Output $output call only possible from Output class
     */
    public static function render(Output $output)
    {
        // The $CONTENT variable must be defined in any template. If it has not
        // been defined in any other way, then define it as an empty string.
        if(!self::has_var('CONTENT'))
            Template::set_var('CONTENT', '');
        
        // If template is enabled, it is required to have $CONFIG, $HEAD_ASSETS,
        // and $BODY_ASSETS; if they have not already been defined by the user,
        // then they take default values.
        if(self::$_enabled)
        {
            if(!self::has_var('CONFIG'))
                Template::set_var('CONFIG', new Config());

            if(!self::has_var('HEAD_ASSETS'))
                Template::set_var('HEAD_ASSETS', Head_Assets::render());

            if(!self::has_var('BODY_ASSETS'))
                Template::set_var('BODY_ASSETS', Body_Assets::render());
            
            if(Config::get('use_debug') && Config::get('use_debug_nonfatal') && !self::has_var('ERRORS'))
                Template::set_var('ERRORS', Error::render_nonfatal_errors());
        }
        // If template is not enabled, then use the empty template definition
        // which just echoes out $CONTENT without any additional content.
        else
        {
            self::set_template('empty');
        }
        
        // Start an output buffer to capture output.
        ob_start();
        
        // Extract all variables stored as $this->_vars into this call stack.
        extract(self::get_all_vars());
        
        // Include the template file that uses $this->_vars.
        include_once(Path::template(self::$_template.'.php'));
        
        // Flush the output buffer contents.
        ob_flush();
    }

    /**
     * Enable the template.
     */
    public static function enable()
    {
        self::$_enabled = true;
    }

    /**
     * Disable the template so that content is just output directly.
     */
    public static function disable()
    {
        self::$_enabled = false;
    }
    
    /**
     * Define a template filename (default is "default").
     * 
     * @param string $name 
     */
    public static function set_template($name = 'default')
    {
        self::$_template = $name;
    }
    
    /**
     * Return the currently defined template filename.
     * 
     * @return string 
     */
    public static function get_template()
    {
        return self::$_template;
    }
    
    /**
     * Returns all variables defined for the template file.
     * 
     * @return type 
     */
    public static function get_all_vars()
    {
        return self::$_vars;
    }
    
    /**
     * Set variable for the template file.
     * 
     * @param string $name
     * @param mixed $value 
     */
    public static function set_var($name, $value)
    {
        if($value instanceof View)
        {
            $value = $value->render();
        }
        
        self::$_vars[$name] = $value;
    }
    
    /**
     * Return a variable defined for the template.
     * 
     * @param string $name
     * @throws Template_Exception
     * @return mixed 
     */
    public static function get_var($name)
    {
        if(!self::has_var($name))
            throw new Template_Exception('Accessing undefined template variable.');
        
        return self::$_vars[$name];
    }
    
    /**
     * Returns true if variable is defined for the template.
     * 
     * @param string $name
     * @return bool 
     */
    public static function has_var($name)
    {
        return isset(self::$_vars[$name]);
    }
}
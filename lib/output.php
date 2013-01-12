<?php

/**
 * Once Output::init() is invoked, Output buffers all output until it is either
 * rendered to the screen with Output::render() or emptied from the buffer with
 * Output::clean(). 
 * 
 * This buffer is intended to be initialized once and rendered once; as such,
 * it provides  methods Output::is_initialized() and Output::is_rendered() that
 * return true once Output::init() and Output::render() have been called 
 * respectively.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Output
{
    /**
     * False until the Output::init() static method is called. Cannot call 
     * Output::render() if Output::init() has not been called, and cannot call 
     * Output::init() if Output::init() has already been called.
     * 
     * @var bool
     */
    private static $_initialized = false;
    
    /**
     * False until the Output::render() has executed successfully. At this 
     * point, content in the buffer has been flushed. However, it has not
     * necessarily been cleaned yet. Invoking Output::render() a second time 
     * may cause double output.
     * 
     * @var type 
     */
    private static $_rendered = false;

    /**
     * Private constructor prevents any code outside of Output from invoking
     * Template::render().
     */
    private function __construct()
    {
        
    }

    /**
     * Initilies the output buffer. No content written after this point will be
     * output until Output::render() [or ob_flush()] is called.
     * 
     * This method can only be called once, and, if called additional times,
     * it will exit early and return false. Output::is_initialized() will
     * return true once this method is invoked.
     * 
     * @return bool
     */
    public static function init()
    {
        // Exits early with false Output::init() has already been called.
        if(self::is_initialized())
            return false;

        // Start the output buffer.
        ob_start();
        
        // Set self::$_initialized for self::is_initialized().
        self::$_initialized = true;
        
        // Return true once buffer is started.
        return true;
    }

    /**
     * Empties the output buffer. Content written after Output::init() but
     * before this method is called will be discarded without being output.
     * 
     * @return bool 
     */
    public static function clean()
    {
        // If not initialized, cannot clean buffer. Exit early with false.
        if(!self::is_initialized())
            return false;
        
        // Clean the existing output buffer.
        ob_clean();
        
        // Return true if cleaning was successful.
        return true;
    }

    /**
     * Prints the contents of the output buffer. This can only be called from
     * the Bootstrap class, as it requires a Bootstrap object as a parameter
     * and Bootstrap::__construct() has protected access.
     * 
     * Bootstrap::__construct()
     * 
     * @param Bootstrap $bootstrap
     * @return bool
     */
    public static function render(Bootstrap $bootstrap)
    {   
        // If not initialized, cannot render buffer. Exit early with false.
        if(!self::is_initialized())
            return false;

        // Set variables to be made available within the template
        Template::set_var('CONTENT', ob_get_contents());

        // Flush the output buffer now that its contents are in $CONTENT
        ob_clean();

        // Render the template with $CONTENT as the contents of the output buffer
        Template::render(new Output());
        
        self::$_rendered = true;

        return true;
    }

    /**
     * Returns true if Output::init() has been called.
     * 
     * @return bool 
     */
    public static function is_initialized()
    {
        return (bool)self::$_initialized;
    }

    /**
     * Returns true if Output::render() has been called.
     * 
     * @return bool 
     */
    public static function is_rendered()
    {
        return (bool)self::$_rendered;
    }
}
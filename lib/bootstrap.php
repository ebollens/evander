<?php

/**
 * Bootstrap handles system initialization and shutdown.
 *
 * This class also invokes three hooks that can be defined in Bootstrap_Hook:
 *
 *  (1) "init" right before the conclusion of Bootstrap::init(),
 *  (2) "prerender" right before Output::render() in Bootstrap::shutdown(),
 *  (3) "postrender" right after Output::render() in Bootstrap::shutdown(),
 *  (4) "shutdown" right before the conclusion of Bootstrap::shutdown().
 *
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Bootstrap
{
    /**
     * Initializes the system, starting the output buffer and handling other
     * startup functionality, initiated by global.php. It triggers the "init"
     * hook if $use_hook.
     *
     * @param bool $use_hooks
     * @see global.php
     */
    public static function init($use_hooks = true)
    {
        global $CONFIG;
        
        Output::init();
        Config::init();
        DB::init();

        if(function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get'))
            @date_default_timezone_set($CONFIG->default_timezone);
        
        if(!$CONFIG->use_template)
            Template::disable();
        
        if($use_hooks)
        {
            try
            {
                Hook::execute('Bootstrap', 'Init');
            }
            catch(Hook_Exception $e){}
        }
    }

    /**
     * Executes at the end of PHP execution through register_shutdown_function,
     * rendering the output captured by the output buffer and handling other
     * shutdown functionality. It triggers the "prerender", "postrender" and
     * "shutdown" hooks if $use_hook.
     *
     * @param bool $use_hooks
     */
    public static function shutdown($use_hooks = true, $use_mvc = true)
    {
        if($use_hooks)
        {
            try
            {
                Hook::execute('Bootstrap', 'Prerender');
            }
            catch(Hook_Exception $e){}
        }
        
        Output::render(new Bootstrap());

        if($use_hooks)
        {
            try
            {
                Hook::execute('Bootstrap', 'Postrender');
            }
            catch(Hook_Exception $e){}
        }
        
        if($use_hooks)
        {
            try
            {
                Hook::execute('Bootstrap', 'Shutdown');
            }
            catch(Hook_Exception $e){}
        }
    }
}

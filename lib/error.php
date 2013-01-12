<?php

/**
 * Error handles all PHP fatal errors and exceptions.
 *
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Error
{
    private static $_nonfatal_errors_buffer = array();
    
    private static $_types = array(
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        8192 => 'E_DEPRECATED',
        16348 => 'E_USER_DEPRECATED',
        E_STRICT => 'E_STRICT'
    );
    
    /**
     * Exception handler registered through set_exception_handler() that catches
     * all uncaught exceptions and produces output through the error/details
     * view.
     *
     * Exception handler registered through set_exception_handler() that
     * produces an error through the error/details view.
     *
     * @param Exception $exception
     */
    public static function handle_exception($exception)
    {
        error_log('E_EXCEPTION: Uncaught exception \''.get_class($exception).'\' with message \'' . $exception->getMessage() . '\' ['.$exception->getFile().':'.$exception->getLine().']');
        
        Output::clean();

        $error_view = new View('error/details', false);
        if(Config::get('use_debug'))
        {
            $error_view->type = get_class($exception);
            $error_view->message = $exception->getMessage();
            $error_view->file = $exception->getFile();
            $error_view->line = $exception->getLine();
        }
        echo $error_view->render();

        Bootstrap::shutdown();

        die();
    }

    /**
     * Error handler registered through set_error_handler() that catches all
     * user and recoverable fatal errors and produces output through the
     * error/details view.
     *
     * @param int $error
     * @param string $message
     * @param string $file
     * @param string $line
     */
    public static function handle_error($error, $message, $file, $line)
    {
        error_log(self::errno_to_string($error).': ' . $message . ' ['.$file.':'.$line.']');
        
        switch ($error) 
        {
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:

                Output::clean();

                $error_view = new View('error/details', false);
                if(Config::get('use_debug'))
                {
                    $error_view->type = 'Fatal Error';
                    $error_view->message = $message;
                    $error_view->file = $file;
                    $error_view->line = $line;
                }

                echo $error_view->render();

                Bootstrap::shutdown();

                die();
                
            default:
                
                self::$_nonfatal_errors_buffer[] = self::errno_to_string($error).': ' . $message . ' ['.$file.':'.$line.']';
        }
    }

    /**
     * Error handler registered through register_shutdown_function() that
     * catches all fatal errors and produces output through the error/details
     * view. Requires "php_vlaue auto_prepend_file shutdown.php" in .htaccess.
     *
     * @see .htaccess
     */
    public static function handle_shutdown_error()
    {
        if (false === is_null($error = error_get_last()))
        {
            switch ($error['type'])
            {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                    
                    // Clean up exception string output (to make it one line and
                    // non-superfluous information and write it as log
                    if(strpos($error['message'], 'Uncaught exception ') !== false && ($pos = strpos($error['message'], '.\' in ')) !== false)
                        error_log(self::errno_to_string($error['type']).': ' . substr($error['message'], 0, $pos+2) . ' ['.$error['file'].':'.$error['line'].']');
                    // Otherwise write the error as log directly
                    else
                        error_log(self::errno_to_string($error['type']).': ' . $error['message'] . ' ['.$error['file'].':'.$error['line'].']');
                    
                    Output::clean();

                    Bootstrap::init(!Config::get('debug_hooks'));
                    
                    Template::set_var('BREADCRUMBS', false);
                    Template::set_var('PAGE_TITLE', false);

                    $error_view = new View('error/details', false);
                    if(Config::get('use_debug'))
                    {
                        $error_view->message = $error['message'];
                        $error_view->file = $error['file'];
                        $error_view->line = $error['line'];

                        switch ($error['type']) {
                            case E_PARSE: $error_view->type = 'Fatal Parse Error';
                                break;
                            case E_CORE_ERROR: $error_view->type = 'Fatal Core Error';
                                break;
                            case E_COMPILE_ERROR: $error_view->type = 'Fatal Compile Error';
                                break;
                            default: $error_view->type = 'Fatal Error';
                                break;
                        }
                    }

                    echo $error_view->render();

                    Bootstrap::shutdown(!Config::get('debug_hooks'), false);
            }
        }
    }
    
    public static function errno_to_string($number)
    {
        return isset(self::$_types[$number]) ? self::$_types[$number] : 'E_UNKNOWN';
    }
    
    public static function get_nonfatal_errors()
    {
        return self::$_nonfatal_errors_buffer;
    }
    
    public static function render_nonfatal_errors()
    {
        $nonfatal_errors_view = new View('error/nonfatal', false);
        $nonfatal_errors_view->errors = self::get_nonfatal_errors();
        return $nonfatal_errors_view->render();
    }
}

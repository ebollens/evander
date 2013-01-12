<?php

/**
 * Define configuration options leveraged within the framework.
 *
 * @package Evander
 * @author ebollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

/**
 * General configuration settings
 */

// Displayed in the template <title> tag
$CONFIG->site_title = '';

// URL used for all paths in the system
$CONFIG->site_url = '';

// Enables the template engine
$CONFIG->use_template = true;

// The default timezone as required in PHP 5
$CONFIG->default_timezone = 'America/Los_Angeles';

// Whether or not to provide fatal debug information (via error messages)
$CONFIG->use_debug = true;

// Whether or not to provide non-fatal debug information (via Template::$ERRORS)
// Must also have $CONFIG->use_debug set to show these errors
$CONFIG->use_debug_nonfatal = false;

// Path to the file root
$CONFIG->file_root = dirname(__FILE__).'/file';

/**
 * Database configuration settings
 * 
 * By default, one connection is defined below and is accessible as
 * DB::connection() or by the connection name. The connection named
 * 'default' is always available through DB::connection().
 * 
 * Additional database connections can be defined by constructing additional 
 * objects of the type DB_Connection_... and then using DB::add_connection().
 * These connections must be accessed as DB::connection($name).
 * 
 * No two database connections can have the same connection name.
 */

// Default connection
$conn = new DB_Connection_MySQLi(
          'localhost'       // hostname
        , 'root'            // username
        , 'root'            // password
        , 'database'        // database
        , '3306'            // port
        );
DB::add_connection('default', $conn);

/**
 * Advanced configuration settings
 */

// Prevents hooks from running during fatal error shutdown re-invocation.
$CONFIG->debug_hooks = false;

// MVC default path configuration
$CONFIG->mvc_base_dir = 'mvc';
$CONFIG->mvc_controller_dir = 'controller';
$CONFIG->mvc_exception_dir = 'exception';
$CONFIG->mvc_interface_dir = 'interface';
$CONFIG->mvc_lib_dir = 'lib';
$CONFIG->mvc_model_dir = 'model';
$CONFIG->mvc_view_dir = 'view';
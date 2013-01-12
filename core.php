<?php

/**
 * This is the only file that needs to be included to leverage the framework
 * within an application. It has several purposes:
 *
 * (1) define the framework class autoloader,
 * (2) initialize the bootstrap to start up the framework,
 * (3) set framework exception and error handlers,
 * (4) register the bootstrap shutdown function.
 *
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

// Load the core library
include_once(dirname(__FILE__).'/lib/loader.php');

// Register Core::load_class() as autoload handler
spl_autoload_register('Loader::load_class');

// Initialize the system
Bootstrap::init();

// Define exception handler for uncaught exceptions
set_exception_handler('Error::handle_exception');

// Define error handler for user-triggered errors
set_error_handler('Error::handle_error');

// Define handler to shut the system down
register_shutdown_function('Bootstrap::shutdown');

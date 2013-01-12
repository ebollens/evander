<?php

/**
 * This file can be set in Apache config for php_value auto_prepend_file to add
 * error checking against uncatchable errors.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

include_once(dirname(__FILE__).'/lib/core.php');

spl_autoload_register('Core::load_class');

register_shutdown_function('Error::handle_shutdown_error');

error_reporting(0);

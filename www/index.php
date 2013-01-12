<?php

/**
 * Web-accessible index file.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

/**
 * Include the Evander Core.
 */

require_once('../core.php');

/**
 * Include the Evander MVC.
 * 
 * Remove the require_once('../mvc.php') line if you do not wish to execute the
 * default MVC configuration here. This might be necessary for two reasons:
 * 
 *      (1) To run PHP files as individual SCRIPTS, not MVC.
 *      (2) To specify custom paths for a particular MVC execution.
 * 
 * For (2), see /www/examples/mvc-custom.php for customizing MVC behavior.
 */

require_once('../mvc.php');

?>
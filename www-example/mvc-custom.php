<?php 

require_once('../core.php');

/**
 * Custom MVC Configuration
 * 
 * The MVC object support custom paths for all controllers, objects and views.
 * As such, one may uncommet the below block to initialize and execute the MVC
 * using custom paths rather than the default set of paths.
 */

MVC::set_base_dir('mvc');
MVC::set_controller_dir('controllerr');
MVC::set_exception_dir('lib/exceptionn');
MVC::set_interface_dir('lib/interfacee');
MVC::set_lib_dir('lib');
MVC::set_model_dir('lib/model');
MVC::set_view_dir('view');
MVC::init();
MVC::execute();

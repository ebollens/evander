<?php

/**
 * This file is included to enable the MVC.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

require_once(dirname(__FILE__).'/core.php');

if($CONFIG->mvc_base_dir)
    MVC::set_base_dir($CONFIG->mvc_base_dir);

if($CONFIG->mvc_controller_dir)
    MVC::set_controller_dir($CONFIG->mvc_controller_dir);

if($CONFIG->mvc_exception_dir)
    MVC::set_exception_dir($CONFIG->mvc_exception_dir);

if($CONFIG->mvc_interface_dir)
    MVC::set_interface_dir($CONFIG->mvc_interface_dir);

if($CONFIG->mvc_lib_dir)
    MVC::set_lib_dir($CONFIG->mvc_lib_dir);

if($CONFIG->mvc_model_dir)
    MVC::set_model_dir($CONFIG->mvc_model_dir);

if($CONFIG->mvc_view_dir)
    MVC::set_view_dir($CONFIG->mvc_view_dir);

MVC::init();
MVC::execute();
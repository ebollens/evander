<?php

/**
 * Symlink model.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class Symlink_Model extends Filesystem_Model
{
    public function __construct($path)
    {
        parent::__construct($path);
    }
    
    public function get_target()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('Symlink does not exist to get target');
        
        return readlink($this->get_path());
    }
    
    public function overwrite($target_path, $target_must_exist = false, $link_must_exist = true)
    {
        if($link_must_exist && !$this->exists())
            throw new Filesystem_Model_Existance_Exception('Symlink does not exist to override target');
        
        return $this->create($target_path, $target_must_exist, true);
    }
    
    public function create($target_path, $target_must_exist = false, $allow_overwrite = false)
    {
        if(!$allow_overwrite && $this->exists())
            throw new Filesystem_Model_Write_Exception('Symlink write failure due to overwrite');

        if($target_must_exist && !file_exists($target_path))
            throw new Filesystem_Model_Existance_Exception('Symlink target does not exist');
        
        if(!symlink($target_path, $this->get_path()))
            throw new Filesystem_Model_Write_Exception('Symlink creation failure');
    }
    
    public function move($newpath, $allow_overwrite = false)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('Symlink does not exist to move');
        
        if(!$allow_overwrite && file_exists($newpath))
            throw new Filesystem_Model_Write_Exception('Symlink move failure due to overwrite');
        
        if(!rename($this->get_path(), $newpath))
            throw new Filesystem_Model_Write_Exception('Symlink move failure');
        
        $this->_set_path($newpath);
    }
    
    public function copy($newpath, $allow_overwrite = false)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('Symlink does not exist to move');
        
        if(!$allow_overwrite && file_exists($newpath))
            throw new Filesystem_Model_Write_Exception('Symlink copy failure due to overwrite');
        
        if(!copy($this->get_path(), $newpath))
            throw new Filesystem_Model_Write_Exception('Symlink copy failure');
        
        return new File_Model($newpath);
    }
    
    public function delete($must_exist = true)
    {
        if(!$this->exists())
        {
             if($must_exist)
                 throw new Filesystem_Model_Existance_Exception('Symlink does not exist to delete');
             
             return;
        }
        
        if(!unlink($this->get_path()))
            throw new Filesystem_Model_Write_Exception('Delete failure');
    }
}
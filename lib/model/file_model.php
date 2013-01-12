<?php

/**
 * File model.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class File_Model extends Filesystem_Model
{
    const MODE_READ = 'r';
    const MODE_WRITE = 'w';
    const MODE_APPEND = 'a';
    
    public function __construct($path)
    {
        parent::__construct($path);
    }
    
    public function move($newpath, $allow_overwrite = false)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to move');
        
        if(!$allow_overwrite && file_exists($newpath))
            throw new Filesystem_Model_Write_Exception('File write (copy) failure due to overwrite');
        
        if(!rename($this->get_path(), $newpath))
            throw new Filesystem_Model_Write_Exception('File write (rename) failure');
        
        $this->_set_path($newpath);
    }
    
    public function copy($newpath, $allow_overwrite = false)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to move');
        
        if(!$allow_overwrite && file_exists($newpath))
            throw new Filesystem_Model_Write_Exception('File write (copy) failure due to overwrite');
        
        if(!copy($this->get_path(), $newpath))
            throw new Filesystem_Model_Write_Exception('File write (copy) failure');
        
        return new File_Model($newpath);
    }
    
    public function delete($must_exist = true)
    {
        if(!$this->exists())
        {
             if($must_exist)
                 throw new Filesystem_Model_Existance_Exception('File does not exist to delete');
             
             return;
        }
        
        if(!unlink($this->get_path()))
            throw new Filesystem_Model_Write_Exception('Delete failure');
    }
    
    public function read($allow_create = false)
    {
        if(!$this->exists())
        {
            if(!$allow_create)
                throw new Filesystem_Model_Existance_Exception('File does not exist to read');
                
            $this->touch();
            return '';
        }
        
        if(($contents = file_get_contents($this->get_path())) === false)
            throw new Filesystem_Model_Read_Exception('File read failure');
        
        return $contents;
    }
    
    public function read_to_array($allow_create = false)
    {
        if(!$this->exists())
        {
            if(!$allow_create)
                throw new Filesystem_Model_Existance_Exception('File does not exist to read');
            
            $this->touch();
            return array();
        }
        
        if(($contents = file($this->get_path())) === false)
            throw new Filesystem_Model_Exception('File read to array failure');
            
        return $contents;
    }
    
    public function touch($time = null, $atime = null, $allow_create = true)
    {
        if(!$allow_create && !$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to touch');
        
        if($time == null)
            $time = time();
        
        if($atime == null)
            $atime = $time;
        
        if(!touch($this->get_path(), $time, $atime))
            throw new Filesystem_Model_Write_Exception('File write (touch) failure');
    }
    
    public function append($data, $allow_create = true)
    {
        return self::write($data, self::MODE_APPEND, $allow_create);
    }
    
    public function overwrite($data, $allow_create = true)
    {
        return self::write($data, self::MODE_WRITE, $allow_create);
    }
    
    public function write($data, $mode = self::MODE_WRITE, $allow_create = true)
    {
        if(!$allow_create && !$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to write'); 
        
        if(self::MODE_APPEND)
        {
            if(file_put_contents($this->get_path(), $data, FILE_APPEND) === false)
                throw new Filesystem_Model_Exception('File write (append) failure');
        }
        else
        {
            if(file_put_contents($this->get_path(), $data) === false)
                throw new Filesystem_Model_Exception('File write (write) failure');
        }
    }
}

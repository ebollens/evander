<?php

/**
 * Directory model.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Directory_Model extends Filesystem_Model
{
    public function __construct($path)
    {
        parent::__construct($path);
    }
    
    public function move($newpath, $allow_overwrite = false)
    {
        // Cannot move a directory with contents so perform a copy
        $this->copy($newpath, $allow_overwrite);
        
        // Delete the original once move is successful
        $this->delete();
        
        // Set path to the new copy of the directory
        $this->_set_path($newpath);
    }
    
    public function move_into(Filesystem_Model $node, $allow_overwrite = false)
    {
        $node->move($newpath, $allow_overwrite);
    }
    
    public function copy($newpath, $create_path = true, $allow_overwrite = false)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('Directory does not exist to move');
        
        if(!$allow_overwrite && file_exists($newpath))
            throw new Filesystem_Model_Write_Exception('Directory write (copy) failure due to overwrite');

        if(!mkdir($newpath, $this->get_mode(), true))
            throw new Filesystem_Model_Write_Exception('Directory creation failure');
        
        foreach($this->get_contents() as $node)
            $node->copy($newpath.'/'.$node->get_name());
        
        return new Directory_Model($newpath);
    }
    
    public function copy_into(Filesystem_Model $node, $allow_overwrite)
    {
        $node->copy($newpath, $allow_overwrite);
    }
    
    public function delete($must_exist = true)
    {
        if(!$this->exists())
        {
             if($must_exist)
                 throw new Filesystem_Model_Existance_Exception('Directory does not exist to delete');
             
             return;
        }
        
        foreach($this->get_contents() as $node)
            $node->delete();
        
        if(!rmdir($this->get_path()))
             throw new Filesystem_Model_Write_Exception('Directory delete failure');
    }
    
    public function create($mode = 0777, $create_path = true, $must_not_exist = true)
    {
        if($this->exists())
        {
             if($must_not_exist)
                 throw new Filesystem_Model_Existance_Exception('File does not exist to delete');
             
             return;
        }
        
        if(!mkdir($this->get_path(), $mode, $create_path))
            throw new Filesystem_Model_Write_Exception('Delete failure');
    }
    
    public function get_contents_names()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('Directory does not exist to get contents');
        
        $arr = array();
        
        if(($handle = opendir($this->get_path())) == false)
            throw new Filesystem_Model_Read_Exception('Directory could not be read');
        
        while(($filename = readdir($handle)) !== false)
            if($filename != '.' && $filename != '..')
                $arr[] = $filename;
            
        closedir($handle);
        
        return $arr;
    }
    
    public function get_contents()
    {
        $arr = array();
        
        foreach($this->get_contents_names() as $filename)
            $arr[] = Filesystem_Model::build($this->get_path().'/'.$filename);
        
        return $arr;
    }
    
    public function get_contents_map()
    {
        $arr = array();
        foreach($this->get_contents() as $node)
        {
            if($node->is_dir())
                $arr[$node->get_name()] = $node->get_contents();
            else
                $arr[$node->get_name()] = $node;
        }
        return $arr;
    }
    
    public function set_group($group, $recursive = false)
    {
        if($recursive)
            foreach($this->get_contents() as $node)
                $node->set_group($group, true);
        
        return parent::set_group($group);
    }
    
    public function set_owner($owner, $recursive = false)
    {
        if($recursive)
            foreach($this->get_contents() as $node)
                $node->set_owner($owner, true);
        
        return parent::set_owner($owner);
    }
    
    public function set_mode($octal, $recursive = false)
    {
        if($recursive)
            foreach($this->get_contents() as $node)
                $node->set_mode($octal, true);
        
        return parent::set_mode($octal);
    }
    
    public function set_mode_string($string, $recursive = false)
    {
        $octal = self::mode_string_to_octal($string);
        return $this->set_mode($octal, $recursive);
    }
}

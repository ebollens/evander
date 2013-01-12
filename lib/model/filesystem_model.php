<?php

/**
 * Abstract filesystem node model.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

abstract class Filesystem_Model
{
    private $_path;
    
    public function __construct($path)
    {
        $this->_path = $path;
    }
    
    public abstract function move($newpath, $allow_overwrite = false);
    
    public abstract function copy($newpath, $allow_overwrite = false);
    
    public abstract function delete($must_exist = true);
    
    protected function _set_path($newpath)
    {
        $this->_path = $newpath;
    }
    
    public function get_path()
    {
        return $this->_path;
    }
    
    public function get_realpath()
    {
        return realpath($this->get_path());
    }
    
    public function get_name()
    {
        return basename($this->get_path());
    }
    
    public function exists()
    {
        return file_exists($this->get_path());
    }
    
    public function is_dir()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to get group');
        
        return is_dir($this->get_path());
    }
    
    public function is_symlink()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to get group');
        
        return is_link($this->get_path());
    }
    
    public function is_file()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to get group');
        
        return is_file($this->get_path());
    }
    
    public function get_parent_directory_name()
    {
        if($this->get_path() == '/')
            return false;
        
        return dirname($this->get_path());
    }
    
    public function get_parent_directory()
    {
        if(($path = $this->get_parent_directory_name()) === false)
            return false;
        
        return new Directory_Model($path);
    }
    
    public function get_group()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to get group');
        
        return filegroup($this->get_path());
    }
    
    public function set_group($group, $recursive = false)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to set group');
        
        return chgrp($this->get_path(), $group);
    }
    
    public function get_owner()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to get owner');
        
        return fileowner($this->get_path());
    }
    
    public function set_owner($owner)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to set owner');
        
        return chown($this->get_path(), $owner);
    }
    
    public function get_mode()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to get mode');
        
        return fileperms($this->get_path());
    }
    
    public function get_mode_numeric()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to get mode');
        
        return substr(sprintf('%o', $this->get_mode()), -4);
    }
    
    public function get_mode_string()
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to get mode');
        
        return self::mode_octal_to_string($this->get_mode());
    }
    
    public function set_mode($octal)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to set mode');
        
        return chmod($this->get_path(), $octal);
    }
    
    public function set_mode_string($string, $recursive = false)
    {
        if(!$this->exists())
            throw new Filesystem_Model_Existance_Exception('File does not exist to set mode');
        
        $octal = self::mode_string_to_octal($string);
        return $this->set_mode($octal);
    }
    
    public static function mode_octal_to_string($perms)
    {
        if (($perms & 0xC000) == 0xC000) $info = 's';
        elseif (($perms & 0xA000) == 0xA000)  $info = 'l';
        elseif (($perms & 0x8000) == 0x8000) $info = '-';
        elseif (($perms & 0x6000) == 0x6000) $info = 'b';
        elseif (($perms & 0x4000) == 0x4000) $info = 'd';
        elseif (($perms & 0x2000) == 0x2000) $info = 'c';
        elseif (($perms & 0x1000) == 0x1000) $info = 'p';
        else $info = 'u';

        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
                    (($perms & 0x0800) ? 's' : 'x' ) :
                    (($perms & 0x0800) ? 'S' : '-'));

        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
                    (($perms & 0x0400) ? 's' : 'x' ) :
                    (($perms & 0x0400) ? 'S' : '-'));

        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
                    (($perms & 0x0200) ? 't' : 'x' ) :
                    (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }
    
    public static function mode_string_to_octal($string)
    {
        if (!preg_match("/[-d]?([-r][-w][-xsS]){2}[-r][-w][-xtT]/", $string) )
        {
            throw new Filesystem_Node_Model_Exception('Invalid mode "'.$string.'" specified.');
        }
        
        $rwx = substr($string, -9);
        $str = (preg_match("/[sS]/",$rwx[2]))?4:0;
        $str .= (preg_match("/[sS]/",$rwx[5]))?2:0;
        $str .= (preg_match("/[tT]/",$rwx[8]))?1:0;
        
        $octal = $str[0]+$str[1]+$str[2];
        
        $rwx = str_replace(array('s','t'), "x", $rwx);
        $rwx = str_replace(array('S','T'), "-", $rwx);
        $trans = array('-'=>'0','r'=>'4','w'=>'2','x'=>'1');
        $str .= strtr($rwx,$trans);
        
        $octal .= $str[3]+$str[4]+$str[5];
        $octal .= $str[6]+$str[7]+$str[8];
        $octal .= $str[9]+$str[10]+$str[11];
        
        return $octal;
    }
    
    public static function build($path)
    {
        if(is_dir($path))
            return new Directory_Model($path);
        elseif(is_file($path))
            return new File_Model($path);
        elseif(is_link($path))
            return new Symlink_Model($path);
        else
            throw new Filesystem_Exception('No node representation for path');
    }
}

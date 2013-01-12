<?php

/**
 * Cookie model.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class Cookie_Model
{
    private $_name;
    private $_lifetime = false;
    private $_domain = false;
    private $_path = false;
    private $_value = false;

    public function __construct($name)
    {
        $this->_name = $name;
    }

    public function &set_domain($domain)
    {
        $this->_domain = $domain;
        return $this;
    }

    public function &set_lifetime($time_to_expire = 3600)
    {
        $this->_lifetime = $time_to_expire;
        return $this;
    }

    public function &set_max_lifetime()
    {
        $this->_lifetime = 1893456000; // 60 years
    }

    public function &set_path($path = '/')
    {
        $this->_path = $path;
        return $this;
    }

    public function &set_value($value)
    {
        $this->_value = $value;
        return $this;
    }

    public function exists()
    {
        return isset($_COOKIE[$this->_name]);
    }

    public function is_empty()
    {
        return !$this->exists() ? true : $this->get() == '' ;
    }

    public function write($value = false)
    {
        if(headers_sent())
            return false;

        if($value)
            $this->_value = $value;
        else if(!$this->_value)
        {
            if(!$this->exists())
                return false;
            else
                $this->_value = $this->get();
        }

        if(!$this->_lifetime)
            $this->_lifetime = 3600;

        if(!$this->_domain)
            $this->_domain = $_SERVER['HTTP_HOST'];

        if(($pos = strpos($this->_domain, ':')) !== false)
            $this->_domain = substr($this->_domain, 0, $pos);

        if(!$this->_path)
            $this->_path = '/';

        if(!@setcookie($this->_name,
                       $this->_value,
                       $this->_lifetime + time(),
                       $this->_path,
                       $this->_domain))
            return false;

        return true;
    }
    
    public function get_name()
    {
        return $this->_name;
    }

    public function get_value()
    {
        return isset($_COOKIE[$this->_name]) ? $_COOKIE[$this->_name] : false;
    }

    public function delete($immediate = true)
    {
        if(headers_sent())
            return false;

        if(!$this->exists())
            return true;

        if(!@setcookie($this->_name,
                       '',
                       time()-86400,
                       '/',
                       $_SERVER['HTTP_HOST']))
            return false;

        if($immediate)
            unset($_COOKIE[$this->_name]);

        return true;
    }
}

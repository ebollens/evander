<?php

/**
 * Sanitization library.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class Sanitize
{
    const NO                                    = 0xFFFF;   // 11111111 11111111
    const NOT_EMPTY                             = 0xFFFE;   // 11111111 11111110
    const BOOLEAN                               = 0xFFFD;   // 11111111 11111101
    const NUMERIC                               = 0xFFFB;   // 11111111 11111011
    const DECIMAL                               = 0xFFF3;   // 11111111 11110011
    const INTEGER                               = 0xFFE3;   // 11111111 11100011
    const STRING                                = 0xFF7F;   // 11111111 01111111
    
    const STRING_ALLOW_EXTENDED                 = 0xFE7F;   // 11111110 01111111
    const STRING_ALLOW_SPACE                    = 0xFD7F;   // 11111101 01111111
    const STRING_ALLOW_NUMERIC                  = 0xFB7F;   // 11111011 01111111
    const STRING_ALLOW_NUMERIC_EXTENDED         = 0xFA7F;   // 11111010 01111111
    const STRING_ALLOW_ALPHA                    = 0xF77F;   // 11110111 01111111
    const STRING_ALLOW_ALPHA_EXTENDED           = 0xF67F;   // 11110110 01111111
    const STRING_ALLOW_ALPHANUMERIC             = 0xF37F;   // 11110011 01111111
    const STRING_ALLOW_ALPHANUMERIC_EXTENDED    = 0xF27F;   // 11110010 01111111
    
    const STRING_CLEAN_NO_HTML                  = 0x0F7F;   // 00001111 01111111
    
    /**
     * @todo add sanitization for cleaning of scripts, objects and all html
     */
    
    //const STRING_CLEAN_SCRIPT                 = 0x1080;   // 00010000 00010000
    //const STRING_CLEAN_OBJECT                 = 0x2080;   // 00100000 00010000
    //const STRING_CLEAN_HTML                   = 0x7080;   // 01110000 00010000
    
    const BOOL                                  = Sanitize::BOOLEAN;
    const INT                                   = Sanitize::INTEGER;
    const FLOAT                                 = Sanitize::DECIMAL;
    const DOUBLE                                = Sanitize::DECIMAL;
    
    /**
     * Return true if value could be sanitized successfully, or false otherwise.
     * 
     * @param mixed $var
     * @param int $type from Sanitize constants
     * @return true|false
     */
    public static function validate($var, $type = Sanitize::NO)
    {
        return self::clean($var, $type) !== null;
    }
    
    /**
     * Returns a properly formatted and sanitized version of $var, unless this
     * is not possible, in which case it returns null.
     * 
     * @param mixed $var 
     * @param int $type from Sanitize constants
     * @return mixed|null 
     */
    public static function clean($var, $type = Sanitize::NO)
    {
        // Return raw if no sanitization
        if($type == self::NO | !$type)
            return $var;
        
        // Deep clean (recursive)
        if(is_array($var))
        {
            $var_arr = array();
            foreach($var as $name=>$val)
                $var_arr[$name] = self::clean($val, $type);
            return $var_arr;
        }
        
        if(($type & self::NOT_EMPTY) == self::NOT_EMPTY)
        {
            if(is_string($var) && strlen(trim($var)) == 0)
                return null;
                
            if(is_array($var) && count($var) == 0)
                return null;
        }
        
        // Boolean check is handled after integer/decimal in case we want to
        // convert an existing number into some numeric form first.
        if(($type & self::BOOLEAN) == $type)
        {
            if(($var = self::to_boolean($var)) === null)
                return null;
        }
        
        if(($type & self::INTEGER) == $type)
        {
            if(($var = self::to_integer($var)) === null)
                return null;
        }
        elseif(($type & self::DECIMAL) == $type)
        {
            if(($var = self::to_decimal($var)) === null)
                return null;
        }
        
        if(($type & self::NUMERIC) == $type)
        {
            if(!is_numeric($var))
                return null;
        }
        
        if(($type & self::STRING) == $type)
        {
            $var = self::to_string($var);
            
            // Determine if we're doing any checks that limit the subset of
            // characters well below that of HTML-like code.
            if(($type | 0xF0FF) != 0xF0FF)
            {
                $allowed = '';
                
                if(($type & self::STRING_ALLOW_ALPHA) == $type)
                    $allowed .= 'a-zA-Z';
                
                if(($type & self::STRING_ALLOW_NUMERIC) == $type)
                    $allowed .= '0-9\.';
                
                if(($type & self::STRING_ALLOW_EXTENDED) == $type)
                    $allowed .= '/_-';
                
                if(($type & self::STRING_ALLOW_SPACE) == $type)
                    $allowed .= ' ';
                
                if(!preg_match('/^['.$allowed.']*$/', $var))
                    return null;
            }
            else
            {
                if(($type & self::STRING_CLEAN_NO_HTML) == $type)
                {
                    $var = strip_tags($var);
                }
                else
                {
                    /**
                     * @todo determine safe way to kill all script tags see 
                     * documentation in strip_tags for excision of 
                     * javascript: in tags need to account also for 
                     * onload-like attributes
                     */
                    //if(($type & self::STRING_CLEAN_SCRIPT) == $type)
                    //{
                    //}
                    
                    /**
                     * @todo determine safe way to remove all object/embed 
                     * tags
                     */
                    //if(($type & self::STRING_CLEAN_OBJECT) == $type)
                    //{
                    //}
                    
                    /**
                     * @todo determine how to strip away any extra dangerous
                     * tags
                     */
                    //if(($type & self::STRING_CLEAN_HTML) == $type)
                    //{
                    //}
                }
            }
        }
        
        return $var;
    }
    
    public static function to_boolean($var)
    {
        // Deep search for a true value
        if(is_array($var))
        {
            foreach($var as $val)
                if(self::to_boolean($val))
                    return true;
            return false;
        }
        
        // Support string values "true" and "1" as boolean TRUE
        if(is_string($var))
        {
            $var = trim(strtolower($var));
            
            if($var == 'true' || self::to_decimal($var))
                return true;
            elseif($var == 'false' || self::to_integer($var) === 0)
                return false;
            
            if(strval(self::to_integer($var)) == $var)
                return $var != 0 ? true : false;
            
            return null;
        }
        
        // Returns false on empty: 0, 0.0, null, false, var $var;
        return !empty($var);
    }
    
    /**
     * WARNING: Be careful passing string greater than 2^31 on 32-bit system.
     * 
     * @param type $var
     * @return type 
     */
    public static function to_integer($var)
    {
        if(is_object($var) || is_array($var))
            return null;
        
        if(is_string($var))
        {
            if(!is_numeric($var))
                return null;
            
            // Remove leading zeroes to avoid "octal" behavior
            while(strlen($var) > 0 && substr($var, 0, 1) == '0')
                $var = substr($var, 1);
            
            $floatvar = floatval($var);
            $var = intval($floatvar);
            
            if($floatvar != $var)
                return null;
        }
        elseif(is_float($var))
        {
            if(intval($var) != $var)
                return null;
        }
        
        // Return int if casting didn't loose precision (such as float) and so
        // long as the conversion didn't encounter any strangeness
        return intval($var);
    }
    
    public static function to_decimal($var)
    {
        if(is_object($var) || is_array($var))
            return null;
        
        return floatval($var);
    }
    
    public static function to_string($var)
    {
        // Converting object to string should leverage __toString() if defined
        // or else return null
        if(is_object($var))
        {
            if(in_array('__toString', get_class_methods($var)))
                return $var->__toString();
            return null;
        }
        
        // Cannot sanitize an array to string
        if(is_array($var))
            return null;
        
        // Ensure boolean 0 returns "0" and not ""
        if(is_bool($var))
            return $var ? '1' : '0';
        
        return strval($var);
    }
}
<?php

/**
 * PHP runtime telemety library.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class PHP_Runtime
{
    /**
     * Info about the callee for the function where this call is invoked. If
     * $key is set, then it returns just the info pertaining to the key, or
     * otherwise it returns an array with all pertinent info. The key value
     * may be "function", "line", "file", "class", "object", "type" or "args".
     *
     * @param string|null $key
     * @throws Runtime_Exception
     * @return mixed
     */
    public static function get_callee($key = null)
    {
        return self::_get_frame(1, $key);
    }

    /**
     * Info about the callee for the function where this call is invoked. If
     * $key is set, then it returns just the info pertaining to the key, or
     * otherwise it returns an array with all pertinent info. The key value
     * may be "function", "line", "file", "class", "object", "type" or "args".
     * This function additionally requires that the callee must be in the
     * object defined as $inObject.
     *
     * @param string|null $key
     * @param object|null $in_object
     * @throws Runtime_Exception
     * @return mixed
     */
    public static function get_callee_in_object($key = null, Object_Interface &$in_object = null)
    {
        return self::_get_frame(1, $key, $in_object);
    }

    /**
     * Info about the caller for the function where this call is invoked. If
     * $key is set, then it returns just the info pertaining to the key, or
     * otherwise it returns an array with all pertinent info. The key value
     * may be "function", "line", "file", "class", "object", "type" or "args".
     *
     * @param string|null $key
     * @throws Runtime_Exception
     * @return mixed
     */
    public static function get_caller($key = null)
    {
        return self::_get_frame(2, $key);
    }

    /**
     * Info about the caller for the function where this call is invoked. If
     * $key is set, then it returns just the info pertaining to the key, or
     * otherwise it returns an array with all pertinent info. The key value
     * may be "function", "line", "file", "class", "object", "type" or "args".
     * This function additionally requires that the caller must be in the
     * object defined as $inObject.
     *
     * @param string|null $key
     * @param object|null $in_object
     * @throws Runtime_Exception
     * @return mixed
     */
    public static function get_caller_in_object($key = null, &$in_object = null)
    {
        return self::_get_frame(2, $key, $in_object);
    }

    /**
     * Returns information about the stack frame specified by $num. If $num is
     * not set, then this function returns information about the stack frame
     * where this is invoked. The $key value defines if one desires only info
     * about a particular portion of the stack frame, and $in_object requires
     * that the stack frame relate to the particular object. The key value
     * may be "function", "line", "file", "class", "object", "type" or "args".
     *
     * @param int $num
     * @param string|null $key
     * @param object|null $in_object
     * @throws Runtime_Exception
     * @return mixed
     */
    private static function _get_frame($num = 0, $key = null, &$in_object = null)
    {
        $num++;

        /**
         * Performance trick for PHP 5.4.0 and newer to minimize the nubmer of
         * stack frames retrieved; for older versions, backtrace returns all
         * stack frames.
         */
        if(PHP::is_newer_than_version('5.4.0'))
        {
            $trace = debug_backtrace($key == 'object' || $in_object != null, $num);
        }
        else
        {
            $trace = debug_backtrace($key == 'object' || $in_object != null);
        }

        /**
         * If the specified $num is greater than the stack frames in question,
         * then an error has been encountered.
         */
        if(count($trace) < $num)
        {
            throw new Runtime_Exception('No trace information for ['.$num.'] in runtime.');
        }

        /**
         * If a $key was not specified, then return the array of information
         * about the $num frame.
         */
        if(!$key)
        {
            return $trace[$num];
        }

        /**
         * If the specified $key is not contained within information about the
         * stack frame in question, then an error has been encountered.
         */
        if(!isset($trace[$num][$key]))
        {
            throw new Runtime_Exception('No trace information for ['.$num.']["'.$key.'"] in runtime.');
        }

        /**
         * If the specified stack frame is not in the object $in_object, if the
         * $in_object object was specified at all, then an error has been
         * encountered.
         */
        if($in_object != null && (!isset($trace[$num]['object']) || Core_Object::compare_class($in_object, $trace[$num]['object'])))
        {
            throw new Runtime_Exception('Trace ['.$num.'] is not in provided object of class '.Core_Object::get_class_name($in_object), 1);
        }

        /**
         * Return information about $key for the $num frame.
         */
        return $trace[$num][$key];
    }
}

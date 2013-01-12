<?php

/**
 * Array utility library.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class Array_Library
{
    /**
     * Returns true if there are differences between $array and $witharray in
     * the same style as array_diff_assoc() except with a deep recursive
     * comparison of all objects.
     *
     * If set, the $typecheck variable defines if this comparison should regard
     * different type objects as different types or if it should just compare
     * values within the objects. It defaults to just comparing values within.
     * If set as a boolean, the $scalar_typecheck variable determines whether
     * scalar types should be compared via type or compared via cast to
     * (string). If not defined or set null, then it defaults to the $typecheck
     * value, and consequently it $typecheck isn't set, it defaults to a weak
     * comparison casting to (string).
     *
     * @param array $array
     * @param array $witharray
     * @param bool $typecheck
     * @param null|bool $scalar_typecheck
     * @return bool
     */
    public static function is_diff(&$array, &$witharray, $typecheck = false, $scalar_typecheck = null)
    {
        return count($array) != count($witharray)   // for performance
            || (self::_diff($array, $witharray, $typecheck) ? true : false); // deep comparison
    }

    /**
     * Returns an array of differences between $array and $witharray in the
     * same style as array_diff_assoc() except with a deep recursive comparison
     * of all objects.
     *
     * If set, the $typecheck variable defines if this comparison should regard
     * different type objects as different types or if it should just compare
     * values within the objects. It defaults to just comparing values within.
     * If set as a boolean, the $scalar_typecheck variable determines whether
     * scalar types should be compared via type or compared via cast to
     * (string). If not defined or set null, then it defaults to the $typecheck
     * value, and consequently it $typecheck isn't set, it defaults to a weak
     * comparison casting to (string).
     *
     * @param array $array
     * @param array $witharray
     * @param bool $typecheck
     * @param null|bool $scalar_typecheck
     * @return array
     */
    public static function diff(&$array, &$witharray, $typecheck = false, $scalar_typecheck = null)
    {
        return ($diff = self::_diff($array, $witharray, $typecheck, $scalar_typecheck)) ? $diff : array();
    }

    /**
     * Private member that performs the actual comparison of diff/is_diff() and
     * returns mixed result - either an array if there are differences or false
     * otherwise.
     *
     * @param array $array
     * @param array $witharray
     * @param bool $typecheck
     * @param null|bool $scalar_typecheck
     * @return array|false
     */
    private static function _diff(&$array, &$witharray, $typecheck = false, $scalar_typecheck = null)
    {
        // if $scalar_typecheck is null, it defaults to $typecheck
        if($scalar_typecheck === null)
        {
            $scalar_typecheck = $typecheck;
        }

        $diff = false;

        foreach($array as $key=>$val)
        {
            // $array has $key that $witharray does not
            if(!array_key_exists($key, $witharray))
            {
                $diff[$key] = $val;
            }
            // $array and $witharray both have $key
            else
            {
                $withval = $witharray[$key];
                // if $val is an array, do array comparison
                if(is_array($val))
                {
                    if(!is_array($withval))
                    {
                        $diff[$key] = $val;
                    }
                    else
                    {
                        if(self::is_diff($val, $withval, $typecheck, $scalar_typecheck))
                        {
                            $diff[$key] = $val;
                        }
                    }
                }
                // if $val is object, do object comparison
                elseif(is_object($val))
                {
                    if(!is_object($withval))
                    {
                        $diff[$key] = $val;
                    }
                    // different if typechecking and the class is not the same for both values
                    elseif($typecheck && get_class($val) != get_class($withval))
                    {
                        $diff[$key] = $val;
                    }
                    // different if values are not the same between the two objects
                    elseif(self::is_diff(Object_Library::to_array($val), Object_Library::to_array($withval), $typecheck, $scalar_typecheck))
                    {
                        $diff[$key] = $val;
                    }
                }
                // otherwise, do scalar comparison
                else
                {
                    // different if type checking and $val is not a typematch to $withval
                    if($scalar_typecheck && $val !== $withval)
                    {
                        $diff[$key] = $val;
                    }
                    // different if $withval is not scalar
                    elseif(is_object($withval) || is_array($withval))
                    {
                        $diff[$key] = $val;
                    }
                    // different if both $val and $withval are cast to string and different
                    elseif((string)$val != (string)$withval)
                    {
                        $diff[$key] = $val;
                    }
                }
            }
        }

        foreach($witharray as $key=>$val)
        {
            // $witharray has $key that $array does not
            if(!array_key_exists($key, $array))
            {
                $diff[$key] = $val;
            }
        }

        return $diff;
    }
}
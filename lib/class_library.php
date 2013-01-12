<?php

/**
 * Class utility library.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class Class_Library
{
    /**
     * Returns true if $object1 and $object2 have $ancestor as a common ancestor.
     *
     * @param object $object1
     * @param object $object2
     * @param string $ancestor
     * @return bool
     */
    public static function compare_ancestor($class_name1, $class_name2, $ancestor)
    {
        return self::is_a($class_name1, $ancestor) && self::is_a($class_name2, $ancestor);
    }
    
    /**
     * Determine if class by name $class_name conforms to the interface by name
     * $interface_name, either by directly implementing it or by providing all
     * of the same methods. This allows for an implicit implementation of an
     * interface without the implements keyword.
     * 
     * @param string $class_name
     * @param string $interface_name
     * @return bool 
     */
    public static function conforms_to_interface($class_name, $interface_name)
    {
        self::_check_class($class_name);
        
        /**
         * Check interface implementation via reflection.
         */
        $reflection = new ReflectionClass($class_name);
        if($reflection->implementsInterface($interface_name))
            return true;
        
        /**
         * Get array of class methods for implicit implementation check.
         */
        $class_methods = array();
        foreach($reflection->getMethods() as $method)
            $class_methods[] = strtolower($method->name);

        /**
         * Perform comparison of class methods with interface methods.
         */
        $interface = new ReflectionClass($interface_name);
        foreach($interface->getMethods() as $method)
            if(!in_array($method->name, $class_methods))
                return false;

        return true;
    }

    /**
     * Returns an ordered array of class names for all classes in the
     * inheritance tree.
     *
     * @param object $object
     * @return array
     */
    public static function get_inheritance_class_names($class_name)
    {
        self::_check_class($class_name);
        
        return array_merge(array($class_name), self::get_parent_class_names($class_name));
    }

    /**
     * Returns an array of interfaces in child-to-parent implementation order.
     *
     * @param object $class_name
     * @return array
     */
    public static function get_interface_names($class_name)
    {
        self::_check_class($class_name);
        
        $reflection = new ReflectionClass($class_name);
        
        $array = array();
        
        foreach($reflection->getInterfaces() as $reflection_interface)
            $array[] = strtolower($reflection_interface->getName());
        
        return $array;
    }
    
    /**
     * Returns an array of interfaces in child-to-parent inheritance order
     *
     * @param string $class_name
     * @return array
     */
    public static function get_parent_class_names($class_name)
    {
        self::_check_class($class_name);
        
        $reflection = new ReflectionClass($class_name);
        
        $array = array();
        
        while($reflection = $reflection->getParentClass())
            $array[] = strtolower($reflection->getName());
        
        return $array;
    }
    /**
     * Determine if class by name $class_name directly implements the interface 
     * by name $interface_name.
     * 
     * @param string $class_name
     * @param string $interface_name
     * @return bool 
     */
    public static function has_interface($class_name, $interface_name)
    {
        self::_check_class($class_name);
        
        /**
         * Check interface implementation via reflection.
         */
        $reflection = new ReflectionClass($class_name);
        return $reflection->implementsInterface($interface_name);
    }
    
    /**
     * Return true if $object has method $method. If $__call is set true,
     * then it will also return true if object has defined the magic method
     * __call().
     *
     * @param object $object
     * @param string $method
     * @param bool $__call
     * @return bool
     */
    public static function has_method($class_name, $method_name, $__call = true)
    {
        return in_array(strtolower($method_name), array_map('strtolower', get_class_methods($class_name)))
               || $__call && in_array('__call', array_map('strtolower', get_class_methods($class_name)));
    }
    
    /**
     * Determine if class by name $class_name is the same or extends/implements
     * the class/interface by name $class_or_interface_name. This is similar to
     * the instanceof keyword, except without requiring an instantiation of an
     * object of the class by name $class_name.
     * 
     * @param string $class_name
     * @param string $class_or_interface_name
     * @return bool 
     */
    public static function is_a($class_name, $class_or_interface_name)
    {
        self::_check_class($class_name);
        
        if($class_name == $class_or_interface_name)
            return true;
        
        $reflection = new ReflectionClass($class_name);
        return $reflection->isSubclassOf($class_or_interface_name);
    }
    
    /**
     * Returns true if this object can be accessed like an array with [].
     *
     * @return bool
     */
    public static function is_array_accessible($class)
    {
        return self::is_a($class, 'arrayaccess');
    }
    
    /**
     * Returns true if this object can be treated in full like an array:
     * 
     *      - Accessing it with []
     *      - Counting it with count()
     *      - Serializing it with serialize()/unserialize()
     *      - Traversing it with foreach
     *
     * @return bool
     */
    public static function is_array($class)
    {
        return self::is_arraylike($class) 
                && self::is_countable($class)
                && self::is_serializable($class)
                && self::is_traversable($class);
    }
    
    /**
     * Returns true if this object can be used with count().
     *
     * @return bool
     */
    public static function is_countable($class)
    {
        return self::is_a($class, 'countable');
    }
    
    /**
     * Returns true if this object is an iterator with current(), key(), next(),
     * rewind() and valid() methods. This is a low-level check and often it is
     * better to just use is_traversible().
     *
     * @return bool
     */
    public static function is_iterator($class)
    {
        return self::is_a($class, 'iterator');
    }
    
    /**
     * Returns true if this object has a getIterator() method. This is a
     * low-level check and often it is better to just use is_traversible().
     *
     * @return bool
     */
    public static function is_iterator_aggregate($class)
    {
        return self::is_a($class, 'iteratoraggregate');
    }
    
    /**
     * Returns true if this object is observable.
     *
     * @return bool
     */
    public static function is_observable($class)
    {
        return self::is_a($class, 'splsubject');
    }
    
    /**
     * Returns true if this object can be an observer.
     *
     * @return bool
     */
    public static function is_observer($class)
    {
        return self::is_a($class, 'splobserver');
    }
    
    /**
     * Returns true if this object can be used with serialize/unserialize().
     *
     * @return bool
     */
    public static function is_serializable($class)
    {
        return self::is_a($class, 'serializable');
    }
    
    /**
     * Returns true if this object can be used with foreach.
     *
     * @return bool
     */
    public static function is_traversable($class)
    {
        return self::is_a($class, 'traversable');
    }
    
    /**
     * Throw an exception if requested class does not exist.
     * 
     * @param type $class_name
     * @return type 
     */
    private static function _check_class($class_name)
    {
        if(!class_exists($class_name))
            throw new Class_Library_Exception('Class_Library requires class "'.$class_name.'" to be a loadable or defined class');
        
        return true;
    }
}
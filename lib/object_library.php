<?php

/**
 * Object utility library.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class Object_Library
{
    
    /**
     * Returns true if $object1 and $object2 have $ancestor as a common ancestor.
     *
     * @param object $object1
     * @param object $object2
     * @param string $ancestor
     * @return bool
     */
    public static function compare_ancestor(&$object1, &$object2, $ancestor)
    {
        return self::is_a($object1, $ancestor) && self::is_a($object2, $ancestor);
    }
    
    /**
     * Returns true if $object1 and $object2 are the same final class.
     *
     * @param object $object1
     * @param object $object2
     * @return bool
     */
    public static function compare_class(&$object1, &$object2)
    {
        return $object1 == $object2;
    }
    
    /**
     * Returns true if $object1 and $object2 contain the same content and are
     * the same final class.
     *
     * @param object $object1
     * @param object $object2
     * @return bool
     */
    public static function compare_class_content(&$object1, &$object2)
    {
        return self::compare_class($object1, $object2) && self::compare_content($object1, $object2);
    }
    
    /**
     * Returns true if $this and $that contain the same content.
     *
     * @param object $object1
     * @param object $object2
     * @return bool
     */
    public static function compare_content(&$object1, &$object2)
    {
        $object1_instance_vars = self::get_instance_vars($object1);
        $object2_instance_vars = self::get_instance_vars($object2);

        $object1_static_vars = self::get_static_vars($object1);
        $object2_static_vars = self::get_static_vars($object2);

        return !Array_Library::is_diff($object1_instance_vars, $object2_instance_vars, false)
            && !Array_Library::is_diff($object1_static_vars, $object2_static_vars, false);
    }
    
    /**
     * Returns true if $object1 and $object2 are references to the same object.
     *
     * @param \Core\Object_Interface $that
     * @return bool
     */
    public static function compare_object(&$object1, &$object2)
    {
        return $object1 === $object2;
    }
    
    /**
     * Returns true if $object_or_class_name conforms to the interface by 
     * $interface_name either explicitly via implements or implicitly via
     * definition of all methods in interface by $interface_name.
     * @param type $object_or_class_name
     * @param type $interface_name
     * @return type 
     */
    public static function conforms_to_interface($object_or_class_name, $interface_name)
    {
        if(is_string($object_or_class_name))
            return Class_Library::conforms_to_interface($object_or_class_name, $interface_name);
        
        if(!is_object($object_or_class_name))
            throw new Object_Library_Exception('Object_Lib::conforms_to_interface requires $object of type object');
        
        $reflection = new ReflectionObject($object_or_class_name);

        if($reflection->implementsInterface($interface_name))
            return true;
        
        $object_methods = array_map('strtolower', get_class_methods($object_or_class_name));

        $interface = new ReflectionClass($interface_name);

        foreach($interface->getMethods() as $method)
            if(!in_array($method->name, $object_methods))
                return false;

        return true;
    }
    
    /**
     * Returns an array $key=>$val of public static & instance variables.
     * 
     * @param object $object
     * @return array
     */
    public static function get_all_vars(&$object)
    {
        return array_merge(self::get_instance_vars($object), self::get_static_vars($object));
    }
    
    /**
     * Returns the name of the class defined by $object.
     * 
     * @param object $object
     * @return string 
     */
    public static function get_class_name(&$object)
    {
        return strtolower(get_class($object));
    }

    /**
     * Returns a string if there is a common ancestor before $ancestor.
     *
     * @param object $object1
     * @param object $object2
     * @param string $ancestor
     * @return string|false
     */
    public static function get_common_ancestor_class_name(&$object1, &$object2, $ancestor)
    {
        /**
         * Return true immediately if $that is the exact same class as $this.
         */
        if(self::compare_class($object1, $object2))
        {
            return true;
        }

        /**
         * Get an array of all parent class names for $this.
         */
        $object1_parent_class_names = self::get_inheritance_class_names($object1);

        /**
         * Catch where $idx is false because $ancestor is not a parent of $this
         */
        if(($idx = array_search($ancestor, $object1_parent_class_names)) === false)
            return false;

        /**
         * Get an array of all parent class names for $that.
         */
        $object2_parent_class_names = self::get_inheritance_class_names($object2);

        /**
         * For each parent class name of $this that is a descendant of the
         * $idx numbered parent, determine if $that has the class, as this
         * will be the nearest shared ancestor.
         */
        for($i = 0; $i <= $idx; $i++)
            if(array_search($object1_parent_class_names[$i], $object2_parent_class_names) !== false)
                return $object1_parent_class_names[$i];

        return false;
    }

    /**
     * Returns an ordered array of class names for all classes in the
     * inheritance tree.
     *
     * @param object $object
     * @return array
     */
    public static function get_inheritance_class_names(&$object)
    {
        return array_merge(array(self::get_class_name($object)), self::get_parent_class_names($object));
    }
    
    /**
     * Returns an array $key=>$val of public instance variables.
     * 
     * @param object $object
     * @return array
     */
    public static function get_instance_vars(&$object)
    {
        return self::_get_vars($object, true);
    }

    /**
     * Returns an array of interfaces in child-to-parent implementation order.
     *
     * @param object $object
     * @return array
     */
    public static function get_interface_names(&$object)
    {
        $array = array();
        foreach(class_implements($object) as $parent)
            $array[] = strtolower($parent);
        return $array;
    }
    
    /**
     * Returns an ordered array of parent class names for all parent classes
     *
     * @param object $object
     * @return array
     */
    public static function get_parent_class_names(&$object)
    {
        $array = array();
        foreach(class_parents($object) as $parent)
            $array[] = strtolower($parent);
        return $array;
    }
    
    /**
     * Returns an array $key=>$val of public static variables.
     * 
     * @param object $object
     * @return array
     */
    public static function get_static_vars(&$object)
    {
        return self::_get_vars($object, false);
    }
    
    /**
     * Determine if class by name $class_name directly implements the interface 
     * by name $interface_name.
     * 
     * @param type $object_or_class_name
     * @param type $interface_name
     * @return type 
     */
    public static function has_interface($object_or_class_name, $interface_name)
    {
        if(is_string($object_or_class_name))
            return Class_Library::has_interface($object_or_class_name, $interface_name);
        
        if(!is_object($object_or_class_name))
        {
            throw new Object_Library_Exception('Object_Library::object_has_interface requires $object of type object');
        }
        
        $reflection = new ReflectionObject($object_or_class_name);
        return $reflection->implementsInterface($interface_name);
    }
    
    public static function is_a($object_or_class_name, $class_or_interface_name)
    {
        if(is_string($object_or_class_name))
            return Class_Library::is_a($object_or_class_name, $class_or_interface_name);
        
        return $object_or_class_name instanceof $class_or_interface_name;
    }
    
    /**
     * Returns true if this object can be accessed like an array with [].
     *
     * @return bool
     */
    public static function is_array_accessible($object)
    {
        return self::is_a($object, 'arrayaccess');
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
    public static function is_array($object)
    {
        return self::is_arraylike($object) 
                && self::is_countable($object)
                && self::is_serializable($object)
                && self::is_traversable($object);
    }
    
    /**
     * Returns true if this object can be used with count().
     *
     * @return bool
     */
    public static function is_countable($object)
    {
        return self::is_a($object, 'countable');
    }
    
    /**
     * Returns true if this object is an iterator with current(), key(), next(),
     * rewind() and valid() methods. This is a low-level check and often it is
     * better to just use is_traversible().
     *
     * @return bool
     */
    public static function is_iterator($object)
    {
        return self::is_a($object, 'iterator');
    }
    
    /**
     * Returns true if this object has a getIterator() method. This is a
     * low-level check and often it is better to just use is_traversible().
     *
     * @return bool
     */
    public static function is_iterator_aggregate($object)
    {
        return self::is_a($object, 'iteratoraggregate');
    }
    
    /**
     * Returns true if this object is observable.
     *
     * @return bool
     */
    public static function is_observable($object)
    {
        return self::is_a($object, 'splsubject');
    }
    
    /**
     * Returns true if this object can be an observer.
     *
     * @return bool
     */
    public static function is_observer($object)
    {
        return self::is_a($object, 'splobserver');
    }
    
    /**
     * Returns true if this object can be used with serialize/unserialize().
     *
     * @return bool
     */
    public static function is_serializable($object)
    {
        return self::is_a($object, 'serializable');
    }
    
    /**
     * Returns true if this object can be used with foreach.
     *
     * @return bool
     */
    public static function is_traversable($object)
    {
        return self::is_a($object, 'traversable');
    }
    
    /**
     * Returns an array of object variables by extracting all public variables
     * via reflection. Use with caution when $__traverse is true, as it will
     * overwrite public member variables with traversed variables if they share
     * a common name.
     * 
     * @param type $object
     * @return type 
     */
    public static function to_array(&$object, $__traverse = false)
    {
        // stdclass can be cast to (array)
        if(strtolower(get_class($object)) == 'stdclass')
        {
            $array = (array)$object;
        }
        else
        {
            $array = self::get_all_vars($object);
            if($__traverse && self::is_traversable($object))
                foreach($object as $key=>$value)
                    $array[$key] = $value;
        }

        foreach($array as $key=>$val)
            if(is_object($val))
                $array[$key] = self::to_array($val);

        return $array;
    }
    
    /**
     * Returns an array of all public variables (either instance or static),
     * considering both the defined-on-creation public attributes as well as
     * the dynamic attributes defined through __set during execution. Set
     * $instance_variables true to get instance variables, or false to get
     * static variables.
     *
     * @param bool $instance_variables
     * @return array
     */
    private static function _get_vars(&$object, $instance_variables)
    {
        /**
         * Use reflection object to get a list of public properties.
         */
        $reflection_object = new ReflectionObject($object);
        $reflection_properties = $reflection_object->getProperties(ReflectionProperty::IS_PUBLIC);

        /**
         * Traverse public properties and store either all static variables
         * or all instance variables in $propertiesArray based on $ivars.
         */
        $properties_array = array();
        foreach ($reflection_properties as $reflection_property)
        {
            if($instance_variables ^ $reflection_property->isStatic())
            {
                $properties_array[$reflection_property->getName()] = $reflection_property->getValue($object);
            }
        }

        ksort($properties_array);

        return $properties_array;
    }
}

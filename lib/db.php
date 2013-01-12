<?php

/**
 * DB serves as a static storage container for database connections. 
 * 
 * They are added to the storage container via DB::add_connection($conn)
 * and accessed through DB::connection($name). Database connections can
 * also be removed through name as DB::remove_connection($name).
 * 
 * Besides through static accessors, the DB instance also has an interface
 * for accessing connections. The DB::init() function, executed during core
 * startup through Bootstrap::init(), defines a global instance object $DB.
 * 
 * The $DB object implements ArrayAccess so that database connections can be
 * accessed through $DB[$name] or, for the default connection, as $DB[0]
 * or through call-forwarding using $DB->method(... on the object. Care
 * should be taken with call-forwarding that the default connection supports
 * the methods invoked upon $DB. These exist merely for convinience.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class DB implements ArrayAccess
{
    /**
     * Static storage container of database connections keyed by connection
     * name as registered through DB::add_connection($name, $conn).
     * 
     * @var array
     */
    private static $_conns = array();
    
    /**
     * Initialization defines a global $DB instance.
     * 
     * @global DB $DB 
     */
    public static function init()
    {
        global $DB;
        $DB = new DB();
    }
    
    /**
     * Adds connection $conn to storage container under $name.
     * 
     * @param string $name
     * @param DB_Connection $conn 
     */
    public static function add_connection($name, DB_Connection &$conn)
    {
        $conn->set_name($name);
        self::$_conns[$name] =& $conn;
    }
    
    /**
     * Removes connection stored as $name from storage container.
     * 
     * @param string $name 
     */
    public static function remove_connection($name)
    {
        unset(self::$_conns[$name]);
        
        if(!is_array(self::$_conns))
            self::$_conns = array();
    }
    
    /**
     * Determines if connection $name exists within the storage container.
     * 
     * @param string $name
     * @return bool 
     */
    public static function connection_exists($name)
    {
        return isset(self::$_conns[$name]);
    }
    
    /**
     * Returns by reerence the connection in storage container by $name. Throws
     * DB_Exception in the event that the requested connection is not available.
     * For ease of use, DB::connection() returns DB::connection('default')
     * 
     * @param string $name
     * @throws DB_Exception
     * @return DB_Connection
     */
    public static function &connection($name = 'default')
    {
        if(!self::connection_exists($name))
        {
            throw new DB_Exception('DB connection "'.$name.'" does not exist.');
        }
        
        return self::$_conns[$name];
    }
    
    /**
     * Magic method that forwards method call to default connection.
     * 
     * @param string $name
     * @param array $arguments
     * @return mixed 
     */
    public function __call($name, $arguments = array())
    {
        return call_user_func_array(array(self::connection(), $name), $arguments);
    }
    
    /**
     * Magic method that forwards parameter getter to default connection.
     * 
     * @param string $name
     * @return mixed 
     */
    public function __get($name)
    {
        return self::connection()->$name;
    }
    
    /**
     * Required by ArrayAccess for $DB[$name] = $conn.
     * 
     * @see ArrayAccess
     * @param string $name
     * @param DB_Connection $conn 
     * @throws DB_Exception
     */
    public function offsetSet($name, $conn)
    {
        if(is_a($conn, 'DB_Connection'))
        {
             throw new DB_Exception('DB container can only store DB_Connection objects.');
        }
        
        self::add_connection($name, $conn);
    }
    
    /**
     * Required by ArrayAccess for isset($DB[$name]).
     * 
     * @see ArrayAccess
     * @param string $name
     * @throws DB_Exception
     * @return bool 
     */
    public function offsetExists($name)
    {
        return self::connection_exists($name);
    }
    
    /**
     * Required by ArrayAccess for $conn = $DB[$name].
     *
     * @see ArrayAccess
     * @param string $name
     * @throws DB_Exception
     * @return type 
     */
    public function offsetGet($name)
    {
        return self::connection($name);
    }
    
    /**
     * Required by ArrayAccess for unset($DB[$name]).
     * 
     * @see ArrayAccess
     * @param string $name 
     * @throws DB_Exception
     */
    public function offsetUnset($name)
    {
        self::remove_connection($name);
    }
}
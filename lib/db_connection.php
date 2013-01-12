<?php

/**
 * DB_Connection is an abstract class that all connections stored in DB must
 * extend. It enforces a common interface that makes it possible to perform
 * queries and get errors and general information about the database without
 * concern for the actual connection handle type in use.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

abstract class DB_Connection
{
    /**
     * Connection name for reference (should be set by DB::add_connection).
     * 
     * @var string
     */
    private $_name;
    
    /**
     * Connection type name for reference.
     * 
     * @var type 
     */
    private $_type;
    
    /**
     * Connection handle that is used by PHP to identify the connection. This
     * should not be used directly to perform operations through the connection,
     * but rather is stored internally so that this object can perform 
     * operations on it. However, for flexibility, it can be accessed when
     * needed through DB_Connection::get_handle_object().
     * 
     * @var type 
     */
    private $_handle = null;
    
    private $_database = false;
    
    /**
     * The $type specifier should be the database connection type such as
     * MySQLi, PDO, ODBC, etc, while the $handle should be the database
     * handle established for this connection. 
     * 
     * Most classes that extend this class overload this class and do not allow 
     * the user to specify either of these, but rather connection properties 
     * like hostname, username, password, database and port.
     * 
     * @param string $type
     * @param object|null $handle 
     */
    public function __construct($type, $handle = null, $database_name = false)
    {
        $this->_type = $type;
        $this->_handle = $handle;
        $this->_database = $database_name;
    }
    
    /**
     * Set the connection name.
     * 
     * @param string $name 
     */
    final public function set_name($name)
    {
        $this->_name = $name;
    }
    
    /**
     * Get the connection name.
     * 
     * @return string 
     */
    final public function get_name()
    {
        if(!isset($this->_name) || !$this->_name)
            throw new DB_Exception('Database connection not named');
        
        return $this->_name;
    }
    
    /**
     * Get the name of the database represented by connection.
     * @return string
     */
    public function get_database()
    {
        if(!$this->_database)
            throw new DB_Exception('Database name unknown for connection');
        
        return $this->_database;
    }
    
    /**
     * Get the connection type.
     * 
     * @return string
     */
    final public function get_type()
    {
        return $this->_type;
    }
    
    /**
     * Get a reference to the connection handle object. This should be used with
     * caution, and generally this should not be called directly but rather
     * accessed through accessor methods defined as abstract in this class that
     * each DB_Connection adapter defines.
     * 
     * @throws DB_Connection_Exception
     * @return mixed 
     */
    final public function &get_handle_object()
    {
        if(!$this->_handle)
        {
            throw new DB_Connection_Exception('Connection handle not defined for "'.$this->get_name().'" of type '.$this->get_type());
        }
        else
        {
            return $this->_handle;
        }
    }
    
    /**
     * Query the database.
     * 
     * @param string $query
     * @return DB_Result
     */
    abstract public function query($query);
    
    /**
     * Escape a value properly to sanitize against malicious input.
     * 
     * @param string $value
     * @return string
     */
    abstract public function escape($value);
    
    /**
     * Get syntax used by the database.
     */
    abstract public function get_syntax();
    
    /**
     * Get a query error if one exists or false otherwise.
     * 
     * @return string|bool
     */
    abstract public function get_query_error();
    
    /**
     * Get a query errno if one exists or false otherwise.
     * 
     * @return int|bool
     */
    abstract public function get_query_errno();
    
    /**
     * Get a connection error if one exists or false otherwise.
     * 
     * @return string|bool
     */
    abstract public function get_connection_error();
    
    /**
     * Get a connection errno if one exists or false otherwise.
     * 
     * @return int|bool
     */
    abstract public function get_connection_errno();
    
    /**
     * Get tables in the database (should be cached after the first check).
     * 
     * @param bool $use_cache
     * @return array
     */
    abstract public function get_tables($use_cache = true);
    
    /**
     * Get fields for $table (should be cached after the first check).
     * 
     * @param string $table
     * @param bool $use_cache
     * @return array
     */
    abstract public function get_fields($table, $use_cache = true);
    
    /**
     * Get primary key for $table (should be cached after the first check).
     * 
     * @param string $table
     * @param bool $use_cache
     * @return string
     */
    abstract public function get_primary_key($table, $use_cache = true);
    
    /**
     * Get autoincrement key for $table (should be cached after the first check).
     * 
     * @param string $table
     * @param bool $use_cache
     * @return string
     */
    abstract public function get_autoincrement_key($table, $use_cache = true);
}
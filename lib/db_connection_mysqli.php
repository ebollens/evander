<?php

/**
 * DB_Connection handler that encapsulates a MySQLi object.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class DB_Connection_MySQLi extends DB_Connection
{
    /**
     * Array of table names.
     * 
     * @var array
     */
    private $_tables = null;
    
    /**
     * Array of arrays of fields, keyed by table name.
     * 
     * @var array
     */
    private $_fields = array();
    
    /**
     * Array of primary keys, keyed by table name.
     * 
     * @var array
     */
    private $_primary_keys = array();
    
    /**
     * Array of autoimcrementing keys, keyed by table.
     * 
     * @var array
     */
    private $_autoincrement_key = array();
    
    /**
     * Constructor that accepts parameters to create a new MySQLi handle that it
     * will then encapsulate. To use a default for any parameter, just set it
     * null or do not define it and this class will fall back to using the
     * default value that MySQLi::construct() itself uses for the parameter.
     * 
     * @param string|null $hostname
     * @param string|null $username
     * @param string|null $password
     * @param string|null $database
     * @param string|int $port
     * @param string|null $socket 
     */
    public function __construct($hostname = null, $username = null, $password = null, $database = null, $port = null, $socket = null) 
    {
        // Use the default mysqli fallbacks if not set.
        
        if(!$hostname)
        {
            $host = ini_get('mysqli.default_host');
        }
        
        if(!$username)
        {
            $user = ini_get('mysqli.default_user');
        }
        
        if(!$password)
        {
            $password = ini_get('mysqli.default_pw');
        }
        
        if(!$database)
        {
            $database = '';
        }
        
        if(!$port)
        {
            $port = ini_get('mysqli.default_port');
        }
        
        if(!$socket)
        {
            $socket = ini_get('mysqli.default_socket');
        }
        
        // Define the MySQLi handle
        $handle = new MySQLi($hostname, $username, $password, $database, $port, $socket);
        
        // Pass to constructor to finish defining database connection
        parent::__construct('MySQLi', $handle, $database);
    }
    
    /**
     * Perform a query using MySQL syntax. If the query is successful, it will
     * return a DB_Result_MySQLi object if it is a SELECT, an int of the insert
     * id (primary key) if it is an INSERT, or a boolean if it is an UPDATE or
     * DELETE. If the query fauls, it will throw DB_Connection_MySQLi_Exception
     * with the query error message and number.
     * 
     * @param string $query
     * @throws DB_Connection_MySQLi_Exception
     * @return DB_Result_MySQLi|int|bool
     */
    public function query($query)
    {
        // Get the raw result from the MySQLi handle
        $result = $this->get_handle_object()->query($query);
        
        // A response of true occurs for INSERT, UPDATE and DELETE.
        if($result === true)
        {
            // The insert id in MySQLi handle will be > 0 if INSERT, in which 
            // case, return it as the primary key for the new row.
            if(($insert_id = $this->get_handle_object()->insert_id) > 0)
            {
                return $insert_id;
            }
            // Otherwise for UPDATE or DELETE return true.
            else
            {
                return true;
            }
        }
        
        // A false result implies that the query failed, so throw exception.
        if(!$result)
        {
            throw new DB_Connection_MySQLi_Exception('MySQLi DB query failed: '.$this->get_query_error().' ['.$this->get_query_errno().']', $this->get_query_errno());
        }
        
        // Otherwise, return the query result set as a DB_Result_MySQLi object.
        return new DB_Result_MySQLi($this, $result, $query);
    }
    
    /**
     * Escape a value properly to sanitize against malicious input.
     * 
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        return $this->get_handle_object()->real_escape_string($value);
    }
    
    /**
     * Syntax used by this handle is "mysql".
     * 
     * @return string
     */
    public function get_syntax()
    {
        return 'mysql';
    }
    
    /**
     * Return the error message of the last query.
     * 
     * @return string|false
     */
    public function get_query_error()
    {
        return $this->get_handle_object()->error == '' ? false : $this->get_handle_object()->error;
    }
    
    /**
     * Return the error number of the last query.
     * 
     * @return int|false
     */
    public function get_query_errno()
    {
        return $this->get_handle_object()->errno == 0 ? false : $this->get_handle_object()->errno;
    }
    
    /**
     * Return the error message of the connection.
     * 
     * @return string|false
     */
    public function get_connection_error()
    {
        return $this->get_handle_object()->connect_error == 0 ? false : $this->get_handle_object()->connect_error;
    }
    
    /**
     * Return the error message of the connection.
     * 
     * @return int|false 
     */
    public function get_connection_errno()
    {
        return $this->get_handle_object()->connect_errno == 0 ? false : $this->get_handle_object()->connect_errno;
    }
    
     /**
     * Get tables in the database (should be cached after the first check).
     * 
     * @param bool $use_cache
     * @return array
     */
    public function get_tables($use_cache = true)
    {
        if(!$this->_tables || !$use_cache)
        {
            $result = $this->query('SHOW TABLES;');

            $this->_tables = array();
            while($table = $result->get_next_row())
            {
                $this->_tables[] = array_shift($table);
            }
        }
        
        return $this->_tables;
    }
    
    /**
     * Get fields for $table (should be cached after the first check).
     * 
     * @param string $table
     * @param bool $use_cache
     * @return array
     */
    public function get_fields($table, $use_cache = true)
    {
        if(!isset($this->_fields[$table]) || !$use_cache)
        {
            $result = $this->query('SHOW COLUMNS IN `'.$table.'`;');

            $this->_fields[$table] = array();
            
            while($field = $result->get_next_row())
            {
                $this->_fields[$table][] = $field['Field'];
            }
        }
        
        return $this->_fields[$table];
    }
    
    /**
     * Get primary key for $table (should be cached after the first check).
     * 
     * @param string $table
     * @param bool $use_cache
     * @return string
     */
    public function get_primary_key($table, $use_cache = true)
    {
        if(!isset($this->_primary_keys[$table]) || !$use_cache)
        {
            $result = $this->query('SHOW INDEX FROM `'.$table.'` WHERE `Key_name` = "Primary";');
            
            $this->_primary_keys[$table] = array();
            while($row = $result->get_next_row())
            {
                $this->_primary_keys[$table][] = $row['Column_name'];
            }
        }
        
        return $this->_primary_keys[$table];
    }
    
    public function get_autoincrement_key($table, $use_cache = true)
    {
        if(!isset($this->_autoincrement_key[$table]) || !$use_cache)
        {
            $result = $this->query('SELECT column_name 
                                    FROM information_schema.columns 
                                    WHERE `table_name`="'.$table.'" 
                                        AND `table_schema`="'.$this->get_database().'"
                                        AND `extra` = "auto_increment";');
            
            if($result->count() == 0)
            {
                $this->_autoincrement_key[$table] = false;
            }
            else
            {
                $row = $result->get_row();
                
                if($row['column_name'])
                    $this->_autoincrement_key[$table] = $row['column_name'];
                else
                    $this->_autoincrement_key[$table] = false;
            }
        }
        
        return $this->_autoincrement_key[$table];
    }
}
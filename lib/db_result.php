<?php

/**
 * DB_Result is an abstract class that all results returned by DB connections
 * must extend. It enforces a common interface that makes it possible to
 * interpret results without concern for the connection handle used to generate
 * the results.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

abstract class DB_Result
{
    /**
     * Reference to the connection object that generated this result set.
     * 
     * @var DB_Connection 
     */
    private $_conn;
    
    /**
     * Object that contains the result that needs to be parsed. This can be
     * accessed by reference through DB_Result::get_result_object() and should
     * be used by DB_Result methods to produce the correct responses based on
     * interpreting this object. This object should rarely, if ever, be used
     * directly outside the class.
     * 
     * @var object
     */
    private $_result;
    
    /**
     * The string that generated this query.
     * 
     * @var string 
     */
    private $_query;
    
    /**
     * Accepts a $conn reference and a $result to parse, as well as optionally
     * a query.
     * 
     * @param DB_Connection $conn
     * @param object $result
     * @param string|false $query 
     */
    public function __construct(DB_Connection &$conn, $result, $query = false)
    {
        $this->_conn =& $conn;
        $this->_result = $result;
        $this->_query = $query;
    }
    
    /**
     * Release the result object.
     */
    public function free()
    {
        $this->_result = null;
    }
    
    /**
     * Returns the object that contains the result that needs to be parsed. This
     * should be used by DB_Result methods to produce the correct responses 
     * based on interpreting this object. This object should rarely, if ever, be
     * used directly outside the class.
     * 
     * @var object
     */
    public function &get_result_object()
    {
        if(!$this->_result)
        {
            throw new DB_Result_Exception('DB result is not available (either null or freed).');
        }
        
        return $this->_result;
    }
    
    /**
     * Return a reference to the connection that spawned this result.
     * 
     * @return DB_Connection
     */
    public function &get_connection()
    {
        return $this->_conn;
    }
    
    /**
     * Return the query string that generated this result, if one is known.
     * 
     * @return string|false 
     */
    public function get_query()
    {
        return $this->_query;
    }
    
    /**
     * Return a numeric array of associative arrays of all rows in the result.
     * 
     * @return array
     */
    abstract public function get_all_rows();
    
    /**
     * Return an associative array of the next row in the result. Increments the
     * internal pointer forward by one. Returns false if the pointer is off the
     * end of the result set.
     * 
     * @return array|false
     */
    abstract public function get_next_row();
    
    /**
     * Return an associative array of the previous row in the result. Decrements
     * the internal pointer backwards by one. Returns false if the pointer is
     * before the start of the result set.
     */
    abstract public function get_prev_row();
    
    /**
     * Return an associative array of a row in the result. If null, it will 
     * increment the internal pointer forward by one and return the result. If
     * not null, it will return the result at index $i and set the internal
     * pointer to $i. Returns false if the considered $i is off the end of the
     * result set.
     * 
     * @return array|false
     */
    abstract public function get_row($i=null);
    
    /**
     * Return an array of field names for all fields (columns) in the result.
     * 
     * @return array
     */
    abstract public function get_fields();
    
    /**
     * Return the size of the result set.
     * 
     * @return int
     */
    abstract public function count();
}
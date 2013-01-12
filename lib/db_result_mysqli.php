<?php

/**
 * DB_Result implementation for MySQLi.
 * 
 * @see DB_Result
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0  
 */

class DB_Result_MySQLi extends DB_Result
{
    /**
     * Internal pointer of current position in result set.
     * 
     * @var int
     */
    private $_cur = -1;
    
    /**
     * Return a numeric array of associative arrays of all rows in the result.
     * 
     * @return array
     */
    public function get_all_rows()
    {
        
        // Available with mysqlnd for optimized retrieval
        if(method_exists($this->get_result_object(), 'fetch_all'))
        {
            // Update $this->_cur to simulate moving pointer all the way through result
            $this->_cur = $this->count();
            
            // Return using the mysqlnd fetch_all() method on the result object
            return $this->get_result_object()->fetch_all(MYSQLI_ASSOC);
        }
        
        // Set pointer to in front of the result set
        $this->_cur = -1;
        
        // Iterate through all rows in the array and store in $result for return
        $result = array();
        while($row = $this->get_next_row())
        {
            $result[] = $row;
        }
        return $result;
    }
    
    /**
     * Return an associative array of the next row in the result. Increments the
     * internal pointer forward by one. Returns false if the pointer is off the
     * end of the result set.
     * 
     * @return array|false
     */
    public function get_next_row()
    {
        // Advance the pointer and return the row.
        return $this->get_row(++$this->_cur);
    }
    
    /**
     * Return an associative array of the previous row in the result. Decrements
     * the internal pointer backwards by one. Returns false if the pointer is
     * before the start of the result set.
     */
    public function get_prev_row()
    {
        // If beyond the last value, need to retreat an extra step
        if($this->_cur == $this->count())
        {
            $this->_cur--;
        }
        
        // Retreat the pointer and return the row.
        $this->_cur--;
        return $this->get_row();
    }
    
    /**
     * Return an associative array of a row in the result. If null, it will 
     * increment the internal pointer forward by one and return the result. If
     * not null, it will return the result at index $i and set the internal
     * pointer to $i. Returns false if the considered $i is off the end of the
     * result set.
     * 
     * @return array|false
     */
    public function get_row($i=null)
    {
        // If $i is not specified, then consider the next row.
        if($i === null)
        {
            $i = $this->_cur++;
        }
        
        // If $i is right in front of the result, consider the first row.
        if($i == -1)
        {
            $i = 0;
        }
        
        // If $i is outside bounds, return false.
        if($i >= $this->count() || $i < 0)
        {
            return false;
        }
        
        // Set cur pointer to row being considered.
        $this->_cur = $i;
        
        // Seek to this row in the result object.
        $this->get_result_object()->data_seek($this->_cur);
        
        // Return the associative array for the object.
        return $this->get_result_object()->fetch_assoc();
    }
    
    /**
     * Return an array of field names for all fields (columns) in the result.
     * 
     * @return array
     */
    public function get_fields()
    {
        $fields = array();
        foreach($this->get_result_object()->fetch_fields() as $field)
        {
            $fields[] = $field->name;
        }
        return $fields;
    }
    
    /**
     * Return the size of the result set.
     * 
     * @return int
     */
    public function count()
    {
        return $this->get_result_object()->num_rows;
    }
    
    /**
     * Free the result contents and release the result object.
     */
    public function free()
    {
        $this->get_result_object()->free();
        return parent::free();
    }
}
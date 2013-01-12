<?php

/**
 * Active_Record_Model represents a tuple in a entity set, roughly equivalent to
 * a row in a database table. An Active_Record_Model may be bound to an existing
 * database row, or it may be a new entity that can be added to the database.
 * 
 * This model includes the standard CRUD (ccreate-read-update-delete) operations
 * and performs these actions with the assistance of a buffer to minimize total
 * database operations. Data is retrieved from the database in a lazy-loading
 * manner when needed, or when loaded manually with Active_Record_Model::load().
 * Similarly, data to be written is buffered until Active_Record_Model::create()
 * is called for an unbounded object or Active_Record_Model::update() is called
 * for an object bound to an existing row.
 * 
 * This model also includes several static factory methods to build objects in
 * particular ways including a new object, an existing object, and an array of
 * existing objects from a result set.
 * 
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Active_Record_Model
{
    /**
     * Name of the entity set (table) this objects is bounded to.
     * 
     * @var string
     */
    private $_table;
    
    /**
     * Key of the entity (row) this object is bounded to.
     * 
     * @var string
     */
    private $_key;
    
    /**
     * Unique field (column) of the entity (row) this object is bounded to.
     * 
     * @var string 
     */
    private $_col;
    
    /**
     * Reference to the connection with access to the entity set (table).
     * 
     * @var DB_Connection 
     */
    private $_db;
    
    /**
     * Null if the row has not been retrieved, or boolean if it has, true if the 
     * entity is bounded to an existing row or false otherwise.
     * 
     * @var bool|null
     */
    private $_status_exists = null;
    
    /**
     * Null if the row has not been retrieved, or an array of data from the row
     * if it is bounded to an existing row and has been retrieved.
     * 
     * @var array|null
     */
    private $_data_current = null;
    
    /**
     * Array containing data waiting to be written to the database.
     * 
     * @var array
     */
    private $_data_buffer = array();
    
    /**
     * Cached array of fields (columns) that the entity contains.
     * 
     * @var type 
     */
    private $_fields = null;
    
    /**
     * Constructor for the Active_Record_Model. Requires a $table to be defined.
     * If a $key is defined, then this model is bounded to a particular row in
     * the database. If a $key is not defined, then this is unbounded and needs
     * to be added to the table. The $col specifies the name of the primary or
     * unique column used to uniquely identify this entity in the table, and
     * defaults to "id" if not specified.If $col is an array, then the binding
     * is applied to a $key (array) that is paired up against the $col (array)
     * specified. In this case, $key must either be false or an array with the
     * same number of arguments as $col. Finally, the $conn is the string name
     * of the DB_Connection object stored in DB to use to access the entity set.
     * This defaults to "default" if not explicitly set.
     * 
     * @param string $table name of the table for this entity set
     * @param scalar|false|array $key value for key column or false if unbounded
     * @param string|array $col unique/primary key column that identifies this entity
     * @param string $conn name of the connection to use for this entity
     * @throws Active_Record_Model_Exception
     */
    public function __construct($table, $key = false, $col = false, $conn = 'default')
    {
        // Define a reference to the specified $conn or throw exception.
        try
        {
            $this->_db =& DB::connection($conn);
        }
        catch(DB_Exception $e)
        {
            throw new Active_Record_Model_Exception('DB connection failure: '.$e->getMessage());
        }
        
        // Set private members defining this entity.
        $this->_table = $table;
        
        if(!$col)
        {
            $col = $this->_db->get_primary_key($table);
        }
        
        if(is_array($col))
        {
            if(count($col) == 0)
            {
                throw new Active_Record_Model_Exception('Cannot bind to zero columns specified');
            }
            elseif(is_array($key))
            {
                if(count($key) == 0)
                    $key = false;
                elseif(count($key) != count($col))
                    throw new Active_Record_Model_Exception('Mismatching number of keys specified based on key columns array');
            }
            else
            {
                if(!$key)
                    $key = false;
                elseif(count($col) == 1)
                    $key = array($key);
                else
                    throw new Active_Record_Model_Exception('Mismatching number of keys specified based on key columns array');
            }
        }
        else
        {
            $col = array($col);
            
            if($key)
            {
                if(is_array($key))
                    throw new Active_Record_Model_Exception('Mismatching number of keys specified based on key columns array');
                else
                    $key = array($key);
            }
        }
        
        $this->_key = $key;
        $this->_col = $col;
    }
    
    /**
     * Magic method that writes a value into the buffer.
     * 
     * @param string $field
     * @param scalar $value
     */
    public function __set($field, $value)
    {
        $this->set($field, $value);
    }
    
    /**
     * Magic method that accesses a value from the buffer or the original data
     * if not in the buffer. Throws an Active_Record_Model_Exception if the
     * $field is neither in the buffer or the original data.
     * 
     * @param string $field
     * @throws Active_Record_Model_Exception
     * @return mixed
     */
    public function __get($field)
    {
        return $this->get($field);
    }
    
    /**
     * Returns true if the field is available in this model or false otherwise.
     * 
     * @param string $field
     * @return bool
     */
    public function __isset($field)
    {
        return $this->is_set($field);
    }
    
    /**
     * Static factory to build a model for an existing row.
     * 
     * @param string $table name of the table for this entity set
     * @param scalar|false|array $key value for key column or false if unbounded
     * @param string|array $col unique/primary key column that identifies this entity
     * @param string $conn name of the connection to use for this entity
     * @return Active_Record_Model 
     */
    public static function build($table, $key = false, $col = false, $conn = 'default')
    {
        return new Active_Record_Model($table, $key, $col, $conn);
    }
    
    /**
     * Static factory to build an array of models from a result set. This result
     * should include all columns in the table, and no additional columns from 
     * other tables. Consequently, if table is tbl, it should be a SELECT tbl.* 
     * with some set of qualifying statements for the query. This throws an
     * Active_Record_Model_Exception if the result includes any columns not in
     * the table or if the result is missing any columns in the table.
     * 
     * @param type $table name of the table for this entity set
     * @param DB_Result $result contains rows to bind to models
     * @param false|string|array $col unique/primary key column that identifies each entity
     * @throws Active_Record_Model_Exception
     * @return array 
     */
    public static function build_from_result($table, DB_Result $result, $col = false)
    {
        // Get the connection and fields from result (reference to connection).
        $conn = $result->get_connection()->get_name();
        $fields = $result->get_connection()->get_fields($table);
        
        if(!$col)
            $col = $result->get_connection()->get_primary_key($table);
        
        // Iterate through all rows and construct a model for each to return.
        // Use Active_Record_Model::load() as this data is already available
        // and does not need to be lazy loaded (mimize DBq).
        $objects = array();
        foreach($result->get_all_rows() as $row)
        {
            if(is_array($col))
            {
                $key = array();
                foreach($col as $name)
                    if(isset($row[$name]))
                        $key[] = $row[$name];
                    else
                        throw new Active_Record_Model_Exception('Mismatching keys based on key columns array');
            }
            else
            {
                $key = $row->$col;
            }
            
            $object = new Active_Record_Model($table, $key, $col, $conn);
            $object->load($row, $fields, $fields);
            $objects[] = $object;
        }
        return $objects;
    }
    
    /**
     * Static factory to build an array of models from a set of where equals
     * conditions as specified by the $where_conditions array given $col=>$val
     * for each column in the where condition. Limit and offset functionality
     * is exposed through $limit and $offset to minimize the result set if 
     * necessary, and $col allows for a uniquely-identifying key besides the
     * primary key to be defined.
     * 
     * @param string $table
     * @param array $where_conditions
     * @param false|int $limit
     * @param int $offset
     * @param false|string|array $col
     * @param string $conn
     * @return array 
     */
    public static function build_where_equals($table, $where_conditions = array(), $limit = false, $offset = 0, $col = false, $conn = 'default')
    {
        $arr = array();
        foreach($where_conditions as $name=>$value)
        {
            if($value === false)
                continue;
            
            $str = '`'.$name.'` ';
            
            if(is_numeric($value))
                $str .= '= '.$value;
            elseif($value === null || $value == 'NULL')
                $str .= 'IS NULL';
            elseif($value == 'NOT NULL')
                $str .= 'IS NOT NULL';
            else
                $str .= '= "'.DB::connection($conn)->escape($value).'"';
            
            $arr[] = $str;
        }
        
        $query = 'SELECT * FROM `'.$table.'` WHERE '.implode(' AND ', $arr);
        
        if($limit)
        {
            $query .= ' LIMIT ';
            if($offset > 0)
                $query .= intval($offset).',';
            $query .= intval($limit);
        }
        
        $result = DB::connection($conn)->query($query);
        return self::build_from_result($table, $result, $col);
    }
    
    /**
     * Static factory to build a new model that can be added to the entity set.
     * 
     * @param string $table name of the table for this entity set
     * @param string $conn name of the connection to use for this entity
     * @return Active_Record_Model 
     */
    public static function build_new($table, $conn = 'default')
    {
        return new Active_Record_Model($table, false, false, $conn);
    }
    
    /**
     * Write a new entity to the database. Throws Active_Record_Model_Exception
     * either if the entity is already bound to an existing row or if object
     * contains attributes in the buffer that do not exist as fields in the 
     * table.
     * 
     * @throws Active_Record_Model_Exception
     * @return bool 
     */
    public function create()
    {
        // Throw exception if the object is already bound to a table row.
        if($this->_key)
        {
            throw new Active_Record_Model_Exception('Cannot add existing active record to database.');
        }
        
        // Throw exception if the buffer includes attributes that do not exist 
        // as columns in the table.
        if(count(array_diff(array_keys($this->_data_buffer), $this->get_fields())) > 0)
        {
            throw new Active_Record_Model_Exception('Cannot add active record with fields not in database table.');
        }
        
        // Generate the fields and values specifiers for insert, transmuting the
        // value definitions for special cases including numeric, NULL, and
        // booleans.
        $fields = array();
        $values = array();
        foreach($this->_data_buffer as $field => $value)
        {
            $fields[] = '`'.$field.'`';
            if(is_numeric($value))
                $values[] = $value;
            elseif($value === null)
                $values[] = 'NULL';
            else
                $values[] = '"'.$this->_db->escape($value).'"';
        }
        
        // Query will yield the autoincrement id.
        $autoincrement = $this->_db->query('INSERT INTO `'.$this->_table.'` ('.implode(',', $fields).') VALUES ('.implode(',', $values).');');

        // Data in the buffer has been written to the database.
        $this->_data_current = $this->_data_buffer;
        
        // Buffer is empty as all data has been written to the database.
        $this->_data_buffer = array();
        
        // Row exists given the insertion.
        $this->_status_exists = null;
        
        // Update autoincrement key if it was set.
        if(is_int($autoincrement))
            $this->_data_current[$this->_db->get_autoincrement_key($this->_table)] = $autoincrement;
        
        // If $_col is not defined, ascertain it from primary key columns.
        if(!$this->_col)
            $this->_col = $this->_db->get_primary_key($this->_table);
        
        // Derive $_key array from columns defined by $_col.
        $this->_key = array();
        foreach($this->_col as $col)
            if(isset($this->_data_current[$col]))
                $this->_key[] = $this->_data_current[$col];
            else
                $this->_key[] = false;
        
        return true;
    }
    
    /**
     * Deletes an existing row from the database. Active_Record_Model_Exception
     * is thrown if the row does not exist in the database.
     * 
     * @throws Active_Record_Model_Exception
     * @return bool 
     */
    public function delete()
    {
        // Throw an exception if the row does not exist.
        if(!$this->exists())
            throw new Active_Record_Model_Exception('Cannot delete active record that does not exist in the database.');
        
        // Delete the record and throw away the key as it is no longer valid.
        $this->_db->query('DELETE FROM `'.$this->_table.'` WHERE '.$this->get_key_columns_sql());
        $this->_key = false;
        
        // Reset buffer and cache so that the object can be reused.
        $this->reset();
        
        return true;
    }
    
    /**
     * Returns true if the row exists in the database or false otherwise. A
     * false response indicates that either the row hasn't been added, the
     * entity is bounded to a key that does not exist in the database, or the
     * row it was bound to has been deleted from the database.
     * 
     * @return bool 
     */
    public function exists()
    {
        return $this->_load_current_data();
    }
    
    /**
     * Method that accesses a value from the buffer or the original data if not 
     * in the buffer. Throws an Active_Record_Model_Exception if the $field is 
     * neither in the buffer or the original data.
     * 
     * @param string $field
     * @return mixed 
     */
    public function get($field)
    {
        // Return buffered data if set.
        if(isset($this->_data_buffer[$field]))
            return $this->_data_buffer[$field];
        
        // Fallback to return current data if available. The $this->exists()
        // check makes sure that $this->_data_current is populated.
        if($this->exists() && isset($this->_data_current[$field]))
            return $this->_data_current[$field];
        
        // Final fallback to check reference key-value pairs.
        if(($idx = array_search($field, $this->_col)) !== false)
            if($this->_key && isset($this->_key[$idx]))
                return $this->_key[$idx];
        
        // Throw an exception otherwise as data isn't in buffer or table.
        throw new Active_Record_Model_Exception('Attempting to access undefined property on active record.');
    }
    
    /**
     * Returns an array of data in the buffer to be written.
     * 
     * @return array
     */
    public function get_buffered_data()
    {
        return $this->_data_buffer;
    }
    
    /**
     * Returns an array of entity attributes (columns in the table).
     * 
     * @return array 
     */
    public function get_fields()
    {
        if(!$this->_fields)
            $this->_fields = $this->_db->get_fields($this->get_table());
        
        return $this->_fields;
    }
    
    public function get_key_columns_sql()
    {
        $arr = array();
        foreach($this->get_key_columns() as $name=>$value)
        {
            if($value === false)
                continue;
            
            $str = '`'.$name.'` ';
            
            if(is_numeric($value))
                $str .= '= '.$value;
            elseif($value === null || $value == 'NULL')
                $str .= 'IS NULL';
            elseif($value == 'NOT NULL')
                $str .= 'IS NOT NULL';
            else
                $str .= '= "'.$this->_db->escape($value).'"';
            
            $arr[] = $str;
        }
        
        return implode(' AND ', $arr);
    }
    
    public function get_key_columns()
    {
        if(count($this->_col) != count($this->_key))
            throw new Active_Record_Model_Exception('Cannot bind to zero columns specified');
        
        $array = array();
        for($i=0; $i < count($this->_col); $i++)
            $array[$this->_col[$i]] = $this->_key[$i];
        
        return $array;
    }
    
    /**
     * Returns an array of data currently in the table for the entity (row).
     * 
     * @return array 
     */
    public function get_current_data()
    {
        if($this->_key === false)
            return false;
        
        $this->_load_current_data();
        
        return $this->_data_current;
    }
    
    /**
     * Returns an array of what will be in the table for the entity (row) once
     * Active_Record::update() is called. This is a merger of current data and
     * buffered data, where buffered data takes precedent.
     * 
     * @return array 
     */
    public function get_data()
    {
        if(!($data = $this->get_current_data()))
            $data = array();
        
        return array_merge($data, $this->get_buffered_data());
    }
    
    /**
     * Returns the name of the table that contains this entity.
     * 
     * @return string
     */
    public function get_table()
    {
        return $this->_table;
    }
    
    /**
     * Returns true if the field is an entity attribute (column in the table).
     * 
     * @param string $column
     * @return bool 
     */
    public function has_field($column)
    {
        return in_array($column, $this->get_fields());
    }
    
    /**
     * Returns true if the field is available in this model or false otherwise.
     * 
     * @param string $column
     * @return bool 
     */
    public function is_set($column)
    {
        try
        {
            $this->get($column);
        }
        // Return false if get() throws an exception because it the column
        // does not exist as an attribute for the object.
        catch(Active_Record_Model_Exception $e)
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Accepts an array $fields of attributes to load into the object. These
     * should be valid columns in the table, or else CRUD operations may produce
     * erroneous results. To assist with accuracy, $allowed_fields may be an
     * array of fields that may be loaded in through $fields, while
     * $required_fields may be an array of fields that must be loaded in. If
     * either of these are violated, then an Active_Record_Model_Exception is
     * thrown. If either is set null, then this method won't check the 
     * condition.
     * 
     * @param array $fields
     * @param array|null $allowed_fields
     * @param array|null $required_fields 
     * @throws Active_Record_Model_Exception
     */
    public function load($fields, $allowed_fields = null, $required_fields = null)
    {
        if(!$allowed_fields || count(array_diff(array_keys($fields), $allowed_fields)) == 0)
        {
            if(!$required_fields || count(array_diff($required_fields, array_keys($fields))) == 0)
            {
                $this->_data_current = $fields;
            }
            else
            {
                throw new Active_Record_Model_Exception('Active record load missing required fields.');
            }
        }
        else
        {
            throw new Active_Record_Model_Exception('Active record load includes non-allowed fields.');
        }
    }
    
    /**
     * Update the bounded entity in the database. Active_Record_Model_Exception
     * is thrown either if the entity is not bound to an existing row or if 
     * the object contains attributes in the buffer that do not exist as fields 
     * in the table.
     * 
     * @return bool 
     */
    public function update()
    {
        // Throw an exception if key isn't set or row doesn't exist.
        if(!$this->_key || !$this->exists())
            throw new Active_Record_Model_Exception('Cannot update non-existant active record in the database.');
        
        // Throw na exception if buffer has any attributes not in table.
        if(count(array_diff(array_keys($this->_data_buffer), $this->get_fields())) > 0)
            throw new Active_Record_Model_Exception('Cannot update active record with fields not in database table.');
        
        // Return true without write if buffer doesn't have anything to update.
        if(count($this->_data_buffer) == 0)
            return true;
        
        // Construct changes, considering special values.
        $changes = array();
        foreach($this->_data_buffer as $field => $value)
        {
            $change = '`'.$field.'` = ';
            
            if(is_numeric($value))
                $change .= $value;
            elseif($value === null || $value == 'NULL')
                $change .= 'NULL';
            else
                $change .= '"'.$this->_db->escape($value).'"';
            
            $changes[] = $change;
        }
        
        // Issue the update in the database.
        $this->_key = $this->_db->query('UPDATE `'.$this->_table.'` SET '.implode(',', $changes).' WHERE '.$this->get_key_columns_sql().';');
        
        // Overwrite old values in $this->_data_current with thos in the buffer.
        foreach($this->_data_buffer as $field => $value)
            $this->_data_current[$field] = $value;
        
        // All buffer values have been written so reset it to empty.
        $this->_data_buffer = array();
        
        return true;
    }
    
    /**
     * Reset the buffer and the cache.
     */
    public function reset()
    {
        $this->reset_buffer();
        $this->reset_cache();
    }
    
    /**
     * Reset the buffer to empty without writing values to table.
     */
    public function reset_buffer()
    {
        $this->_data_buffer = array();
    }
    
    /**
     * Reset the cache so that the record needs to do a lookup in the table.
     */
    public function reset_cache()
    {
        $this->_data_current = null;
        $this->_status_exists = null;
    }
    
    /**
     * Method that writes a value into the buffer.
     * 
     * @param string $field
     * @param scalar $value 
     */
    public function set($field, $value)
    {
        $this->_data_buffer[$field] = $value;
    }
    
    /**
     * Protected helper method that fetches data from the database for the row
     * and stores it in $this->_data_current, also defining $this->_fields from
     * it and setting the status_exists to true, or setting everything false
     * otherwise. This returns whether the row exists or not.
     * 
     * @return bool 
     */
    protected function _load_current_data($use_cache = true)
    {
        if($this->_key === false)
            $this->_status_exists = false;
        
        if($use_cache && $this->_status_exists !== null)
            return $this->_status_exists;
        
        $result = $this->_db->query('SELECT * FROM `'.$this->_table.'` WHERE '.$this->get_key_columns_sql().' LIMIT 2;');

        if($result->count() > 1)
        {
            throw new Active_Record_Model('Multiple records match active record binding');
        }
        if($result->count() == 1)
        {
            $this->_data_current = $result->get_row();
            $this->_fields = $result->get_fields();
            $this->_status_exists = true;
        }
        else
        {
            $this->_data_current = false;
            $this->_status_exists = false;
        }
        
        return $this->_status_exists;
    }
}

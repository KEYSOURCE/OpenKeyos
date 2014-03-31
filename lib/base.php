<?php

/**
* Abstract class to be inherited by any class doing objects manipulation.
*
* This allows easy implementation for storing persistent objects into
* the database. Before anything else, each inheriting class needs to
* overload the $table and $fields attributes, which represent the
* database table in which objects are to be stored and, respectively,
* the array with field names to be loaded/saved to the database.
*
* By default, each object has an unique $id, which is generated 
* automatically by the database. This implies that each such table
* must have an 'id' field, of type 'auto_increment' and which also
* needs to be the primary key.
*
* If the ID field is empty, null or false, the object is considered 
* a new one which has not been saved yet to the database.
*
* This class implements the basic load, save and delete operations.
* The inheriting classes can overload these for their special needs.
* There are also blank methods for validating data and for checking
* if an object is safe to delete from database. They return True
* by default. Any class that needs to do data validation should 
* always overload these methods.
*
* @abstract
* @package
*/
require_once('db.php');

class Base extends Db
{
	/** The object ID, tied to the related id field in the database table 
	* @var int */
	var $id = '';
	
	/** The fields to use as primary key; If empty, default is 'id'
	* @var array */
	var $primary_key = null;

	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = '';
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array();
	
	/** Associative array with various text strings needed by the class (e.g. error messages). Note that
	* these strings are not loaded together with the object data. Thery are only loaded upon
	* request, with the load_strings() method. 
	* The actual strings are loaded from a ini file, which is specified in $GLOBALS['CLASSES_STRINGS_FILES']
	* @var array */
	var $strings = array ();
	
	/** Tells if the strings have been loaded or not
	* @var bool */
	var $strings_loaded = false;
	
	/** The language of the last loaded strings
	* @var int */
	var $strings_lang = false;
        
	
	/**
	* Loads the data from the database into the object, using the fields specified in $this->fields 
	*
	* It is assumed that table holding the object data has the "id" field as primary key. 
	* If this is not the case, then the method must be overloaded by the interiting class.
	*
	* @returns	bool	TRUE or FALSE if loading data was succesfull
	*/
	function load_data ()
	{
		$ret = false;
		
		// Check if the object is properly identifiable
		$valid_id = true;
		if ($this->primary_key)
		{
			foreach ($this->primary_key as $key) $valid_id = ($valid_id and !empty($this->$key) );
		}
		else $valid_id = !empty($this->id);

		if (!empty($this->table) and $valid_id)
		{
			$q = 'SELECT * FROM '.$this->table.' WHERE ';
			if ($this->primary_key)
			{
				foreach ($this->primary_key as $key) $q.= $key.'='.(is_numeric($this->$key) ? $this->$key : '"'.$this->$key.'"').' AND ';
				$q = preg_replace('/AND\s*$/', ' ', $q);
			}
			else
			{
				$q.= 'id='.(is_numeric($this->id) ? $this->id : '"'.db::db_escape($this->id).'"');
			}
			$res = $this->db_fetch_row($q);
			
			foreach ($this->fields as $field) $this->$field = $res[$field];
			$ret = (!$this->db_error());
		}
		return $ret;
	}
	
	
	/**
	* Saves the data from the object into the database, using the fields specified in $this->fields 
	*
	* It is assumed that table holding the object data has the "id" field as primary key. 
	* If this is not the case, then the method must be overloaded by the interiting class.
	*
	* @returns	bool	TRUE or FALSE if saving the data was successfull or not.
	*/
	function save_data ()
	{
		$ret = true;
		if (!empty($this->table))
		{
			$q = 'REPLACE INTO '.$this->table.' SET ';
			foreach ($this->fields as $field)
			{
				if (is_string($this->$field) and $this->$field == "NULL") $q.= '`'.$field.'`=NULL, ';
				else $q.= '`'.$field.'`="'.db::db_escape($this->$field).'", ';
			}
			$q = preg_replace('/,\s*$/', '', $q);

			$this->db_query($q);
			
			$ret = (!$this->db_error());

			if (empty($this->id))
			{
				$this->id = $this->db_insert_id();
				$ret = ($ret and !empty($this->id));
			}
		}
        $_SESSION['cache_dirty'] = array('class'=>$_REQUEST['cl'], 'op' => $_REQUEST['op']);
		return $ret;
	}
	
	/**
	* Loads the object data from an array, e.g. an array with fields from a form 
	* @params	array $data	The data to load into the object
	*/
	function load_from_array ($data = array())
	{
		foreach ($this->fields as $field)
		{
			if (isset($data[$field])) $this->$field = $data[$field];
		}
	}
	
	/**
	* Returns true or false if the product data is valid. 
	* Default result is true. Inheriting classes should override this as needed.
	* @return	bool	TRUE or FALSE if the object data is valid
	*/
	function is_valid_data ()
	{
		$ret = true;
		return $ret;
	}
	
	/**
	* Returns true or false if the object can be deleted
	* Default result is true. Inheriting classes should override this as needed.
	* @return	bool	True or False if the object can be deleted or not
	*/
	function can_delete ()
	{
		$ret = true;
		return $ret;
	}
	
	/** Deletes an object from the database */
	function delete ()
	{
		// Check if the object is properly identifiable
		$valid_id = true;
		if ($this->primary_key)
		{
			foreach ($this->primary_key as $key) $valid_id = ($valid_id and !empty($this->$key));
		}
		else $valid_id = !empty($this->id);
	
		if ($valid_id)
		{
			$q = 'DELETE FROM '.$this->table.' WHERE ';
			if ($this->primary_key)
			{
				foreach ($this->primary_key as $key) $q.= $key.'="'.$this->$key.'" AND ';
				$q = preg_replace('/AND\s*$/', ' ', $q);
			}
			else $q.= 'id='.$this->id;
			$this->db_query($q);
		}
	}
	
	/** Loads the 'strings' attribute with the array of strings defined for this class - if any and if not already loaded */
	function load_strings ($lang = null, $force = false)
	{
		// Load global strings
		/*
		$file = dirname(__FILE__).'/../../'.MODULES_DIR_TEMPLATES.'/strings.ini';
		if (file_exists($file))
		{
			$this->strings = @parse_ini_file($file);
			$this->strings_loaded = true;
		}
		*/
		
		if (!$lang) $lang = $_SESSION['USER_LANG'];
		if (!$lang) $lang = LANG_EN;
		$lang_ext = '.'.$GLOBALS['LANGUAGE_CODES'][$lang];		
		if (!$this->strings_loaded or $force or ($this->strings_loaded and $this->strings_lang!=$lang))
		{
			// Load the class-specific strings
			$class_name = strtolower(get_class ($this));
			if (isset($GLOBALS['CLASSES_STRINGS_FILES'][$class_name]))
			{
				$file = $GLOBALS['CLASSES_STRINGS_FILES'][$class_name].$lang_ext;
                               
				if (file_exists($file))
				{
                                        
					$this->strings = array_merge ($this->strings, @parse_ini_file ($file));
					$this->strings_loaded = true;
					$this->strings_lang = $lang;
				}
			}
		}
	}

	
	/** 
	* Returns a string from $this->strings, optionally replacing parts of it
	* @param	string	$name 			The name (key in $this->strings) of the string to get
	* @param	string	$_any_			You can pass any number of additional strings, which will
	*						replace, in the specified order, any %s markers in the string
	* @return	string				The matched string 
	*/
	function get_string ($name)
	{
		$this->load_strings ();
	
		$ret = '';                
		if (isset($this->strings[$name]))
		{
			$ret = $this->strings[$name];
			
			$args = func_get_args();
			if (count($args) > 1)
			{
				// There are extra params to replace in the string
				$patterns = array ();
				for ($i=0; $i<count($args); $i++) $patterns[] = '/\%s/';
				unset ($args[0]);
				$ret = preg_replace ($patterns, $args, $ret, 1);
			}
		}
		
		// Just in case the string is not present, return at least the string's name
		$ret = trim($ret);
		if (!$ret) $ret = $name;
		
		return $ret;
	}
}

?>
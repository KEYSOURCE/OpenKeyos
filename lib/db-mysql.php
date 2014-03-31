<?php
/**
* Abstract class for doing database operations
*
* @todo	Enhanced error handling
*/
#require_once 'db-to-rethink.php';
class DbMysql
{

	/**
	* Initializes the database connection. It uses the global constant DB_CONNECTED so this is done only once
	*/
	public static function db_connect()
	{
		if (!defined('DB_CONNECTED'))
		{
			mysql_pconnect(DB_HOST, DB_USER, DB_PWD);
			mysql_select_db(DB_NAME);
			define('DB_CONNECTED', true);
			mysql_query("SET NAMES latin1");
		}

	}

	/**
	* Performs a query and returns the resource handle.
	* @param	string $q	The query to perform
	* @return	resource	The MySQL resource handle
	* @todo		Better handling of errors
	*/
	public static function db_query($q = '')
	{
		// This is needed to differentiate between when this is called as an object method or as a class method
		//if (isset($this) and $this!=null and method_exists($this, 'db_connect')) $this->db_connect();
		DbMysql::db_connect();
		
		$ret = null;
		if ($q)
		{
			$t1 = time();
			$ret = mysql_query($q);
			if (mysql_error())
			{
				$msg = 'Query error: '.mysql_error().'; query: '.$q;
				if (function_exists('debug_backtrace'))
				{
					$trace = debug_backtrace();
					$msg.= "\n".'  CALL TRACE: ';
					for ($i=count($trace)-1; $i>=0; $i--)
					{
						$msg.= "\n    ".$trace[$i]['class'].$trace[$i]['type'].$trace[$i]['function'].' ';
						$msg.= '('.$trace[$i]['line'].': '.$trace[$i]['file'].')';
					}
				}
				do_log ($msg, LOG_LEVEL_ERRORS);
				
				error_msg ('Query error, check error log: '.mysql_error());
			}
			else
			{
				$t2 = time();
				if ($t2 - $t1 > 5) do_log ('Long query: '.($t2-$t1).' :: '.$q, LOG_LEVEL_ERRORS);
			}
		}
		return $ret; 
	}

	/**
	* Performs a query and returns an associative array with the first record resulted from the query
	* @param	string $q	The query to perform
	* @return	array		An associative array with the field names as keys. 
	*				If there are no results or in case of error the array will be empty.
	*/
	public static function db_fetch_row ($q = '')
	{
		$ret = array();
		if ($q)
		{
			// This is needed to differentiate between when this is called as an object method or as a class method
			//if (isset($this) and $this!=null and method_exists($this, 'db_query')) $h = $this->db_query($q);
			$h = DbMysql::db_query($q);

			if ($h) 
			{
			    $ret = mysql_fetch_assoc($h);
			    mysql_free_result($h);
			}
			
			if (mysql_error())
			{
				do_log ('Query error: '.$q.'<br/>:: '.mysql_error(), LOG_LEVEL_ERRORS);
			}
			
		}
		return $ret;
	}
	
	
	/**
	* Performs a query and returns an array with all the records 
	* @param	string $q	The query to perform
	* @return	array		Array with all records, represented as objects. The array is empty in case of error or if there are no results.
	*/
	public static function db_fetch_array ($q = '')
	{
		$ret = array();
		if ($q)
		{
			// This is needed to differentiate between when this is called as an object method or as a class method
			//if (isset($this) and $this!=null and method_exists($this, 'db_query')) $h = $this->db_query($q);
			$h = DbMysql::db_query($q);
			
			if (mysql_error())
			{
				do_log ('Query error: '.$q.'<br/>:: '.mysql_error(), LOG_LEVEL_ERRORS);
			}
			
			while (($row = mysql_fetch_object($h))) 
			{
				$ret[] = $row;
			}

			mysql_free_result($h);
			if (mysql_error())
			{
				do_log ('Query error: '.$q.'<br/>:: '.mysql_error(), LOG_LEVEL_ERRORS);
				echo ('Query error: '.$q.'<br/>:: '.mysql_error());
			}
		}
		return $ret;
	}
	
	
	/** 
	* Performs a query and returns the values in an associative array, where the key is 
	* the first resulting field from the query and the value the second field.
	* @param	string	$q	The query to perform. It must return two fields. If it returns more, only
	*				the first two are used.
	* @return	array		Associative array with the query results
	*/
	public static function db_fetch_list ($q = '')
	{
		$ret = array();
		if ($q)
		{
			// This is needed to differentiate between when this is called as an object method or as a class method
			//if (isset($this) and $this!=null and method_exists($this, 'db_query')) $h = $this->db_query($q);
			//else $h = DbMysql::db_query($q);
			$h = DbMysql::db_query($q);
			if (mysql_error())
			{
				do_log ('Query error: '.$q.'<br/>:: '.mysql_error(), LOG_LEVEL_ERRORS);
			}
			
			while (($row = mysql_fetch_row($h))) 
			{
				$ret[$row[0]] = $row[1];
			}

			mysql_free_result($h);
			if (mysql_error())
			{
				do_log ('Query error: '.$q.'<br/>:: '.mysql_error(), LOG_LEVEL_ERRORS);
				echo ('Query error: '.$q.'<br/>:: '.mysql_error());
			}
		}
		return $ret;
	}
	
	
	/** 
	* Performs a query and returns the values in an array (vector), each element being a resulting record
	* from the query.
	* @param	string	$q	The query to perform. It must return one field. If it returns more, only
	*				one field is used.
	* @return	array		Array with the query results
	*/
	public static function db_fetch_vector ($q = '')
	{
		$ret = array();
		if ($q)
		{
			// This is needed to differentiate between when this is called as an object method or as a class method
			//if (isset($this) and $this!=null and method_exists($this, 'db_query')) $h = $this->db_query($q);
			$h = DbMysql::db_query($q);
			
			if (mysql_error())
			{
				do_log ('Query error: '.$q.' :: '.mysql_error(), LOG_LEVEL_ERRORS);
			}
			
			while (($row = mysql_fetch_row($h)))
			{
				$ret[] = $row[0];
			}

			mysql_free_result($h);
			if (mysql_error())
			{
				do_log ('Query error: '.$q.' :: '.mysql_error(), LOG_LEVEL_ERRORS);
				echo ('Query error: '.$q.' :: '.mysql_error());
			}
		}
		return $ret;
	}
	
	
	/**
	* Performs a query and returns the value of the specified field
	* @param	string $q	The query to perform
	* @param	string $field	The field whos value to return
	* @return	mixed		The value of the field
	*/
	public static function db_fetch_field ($q = '', $field = '')
	{
		$ret = null;
		if ($q and $field)
		{
			$h = DbMysql::db_query($q);
			$row = DbMysql::db_fetch_row($q);
			$ret = $row[$field];
			
			//mysql_free_result($h);
			if (mysql_error())
			{
				do_log ('Query error: '.$q.' :: '.mysql_error(), LOG_LEVEL_ERRORS);
			}
		}
		return $ret;
	}
	
	/**
	* Returns the next record from an existing resource, returning an object with the record
	* @param	resource $h	A MySQL resource handle
	* @return	object 		An object having the record fields as attrbutes or FALSE if there are no records
	*/
	public static function db_get_next ($h = null)
	{
		$ret = false;
		if ($h)
		{
			$ret = mysql_fetch_object($h);
			if (mysql_error())
			{
				do_log ('Query error: '.mysql_error(), LOG_LEVEL_ERRORS);
			}
		}
		return $ret;
	}
	
	
	/** Returns the last autogenerated id from the database */
	public static function db_insert_id ()
	{
		return mysql_insert_id();
	}
	
	
	/** Returns true or false if there was or not an MySQL error */
	public static function db_error ()
	{
		$ret = mysql_error();
		$ret = (empty($ret));
		return $ret;
	}

    public static function get_client_encoding(){
        return mysql_client_encoding();
    }
}

?>

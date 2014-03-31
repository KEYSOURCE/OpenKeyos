<?php
/**
 * Abstract class for doing database operations
 *
 * @todo	Enhanced error handling
 */
class Db
{
    /**
     * Initializes the database connection. It uses the global constant DB_CONNECTED so this is done only once
     */

    public static function db_connect()
    {
        if (!isset($GLOBALS['db_link']) or !mysqli_ping($GLOBALS['db_link']))
        {
            $GLOBALS['db_link'] = mysqli_connect(DB_HOST, DB_USER, DB_PWD);
            mysqli_select_db($GLOBALS['db_link'], DB_NAME);
            //define('DB_CONNECTED', true);
            mysqli_query($GLOBALS['db_link'], "SET NAMES latin1");
        }

    }

    /**
     * Performs a query and returns the resource handle.
     * @param	string $q	The query to perform
     * @return	mysqli_result	The MySQL result
     * @todo		Better handling of errors
     */
    public static function db_query($q = '')
    {
        Db::db_connect();
        $ret = null;
        if ($q)
        {
            $t1 = time();
            $ret = mysqli_query($GLOBALS['db_link'], $q);
            if (mysqli_error($GLOBALS['db_link']))
            {
                echo 'Query error: '.$q.'<br/>:: '.mysqli_error($GLOBALS['db_link']);
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
            $h = Db::db_query($q);

            if ($h)
            {
                $ret = mysqli_fetch_assoc($h);
                mysqli_free_result($h);
            }

            if (mysqli_error($GLOBALS['db_link']))
            {
                echo 'Query error: '.$q.'<br/>:: '.mysqli_error($GLOBALS['db_link']);
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
            $h = Db::db_query($q);
            if($h){
                while (($row = mysqli_fetch_object($h)))
                {
                    $ret[] = $row;
                }

                mysqli_free_result($h);
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
            //else $h = Db::db_query($q);
            $h = Db::db_query($q);
            if($h){

                while (($row = mysqli_fetch_row($h)))
                {
                    $ret[$row[0]] = $row[1];
                }

                mysqli_free_result($h);
            }
            if (mysqli_error($GLOBALS['db_link']))
            {
                echo ('Query error: '.$q.'<br/>:: '.mysqli_error($GLOBALS['db_link']));
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
            $h = Db::db_query($q);
            if($h){

                while (($row = mysqli_fetch_row($h)))
                {
                    $ret[] = $row[0];
                }

                mysqli_free_result($h);
            }
            if (mysqli_error($GLOBALS['db_link']))
            {
                echo ('Query error: '.$q.' :: '.mysqli_error($GLOBALS['db_link']));
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
            $row = Db::db_fetch_row($q);
            $ret = $row[$field];

            if (mysqli_error($GLOBALS['db_link']))
            {
                echo ('Query error: '.$q.' :: '.mysqli_error($GLOBALS['db_link']));
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
            $ret = mysqli_fetch_object($h);
            if (mysqli_error($GLOBALS['db_link']))
            {
                echo('Query error: '.mysqli_error($GLOBALS['db_link']));
            }
        }
        return $ret;
    }


    /** Returns the last autogenerated id from the database */
    public static function db_insert_id ()
    {
        return mysqli_insert_id($GLOBALS['db_link']);
    }


    /** Returns true or false if there was or not an MySQL error */
    public static function db_error ()
    {
        $ret = mysqli_error($GLOBALS['db_link']);
        $ret = (empty($ret));
        return $ret;
    }

    public static function db_escape($s) {
        return mysqli_real_escape_string($GLOBALS['db_link'], $s);
    }
}

?>

<?php

/** Class for managing the valid name duplicates for computers.
*
* When two computers in Keyos have the same name it usually means that a computer
* is recorded more than once in Keyos. However, there are situations when the 
* duplicate names are valid (e.g. generic names), and this class provides a 
* way of storing these valid duplicate names.
*
* The system will store one ValidDupName object for each computer which is allowed
* to use a specific name.
*/

class_load ('Computer');

class ValidDupName extends Base
{
	/** The unique object ID
	* @var int */
	var $id = null;
	
	/** The computer name which is allowed to be used more than once in Keyos
	* @var string */
	var $netbios_name = '';
	
	/** The ID of the computer which is allowed to use this name without being
	* counted as a duplicate
	* @var int */
	var $computer_id = null;
	
	
	/** The Computer associated with this object
	* @var Computer */
	var $computer = null;
	
	
	/** The databas table storing objects data 
	* @var string */
	var $table = TBL_VALID_DUP_NAMES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'netbios_name', 'computer_id');
	
	
	/** Constructor, also loads object data if an ID is specified */
	function ValidDupName ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	
	/** Loads the object data from database, as well as the associated computer */
	function load_data ()
	{
		parent::load_data ();
		if ($this->computer_id) $this->computer = new Computer ($this->computer_id);
	}
	
	
	/** [Class Method] Set the computers which are allowed to use a certain name. Any objects
	* that previously existed in the database for the specified and whose computers IDs don't
	* appear in the new list will be deleted.
	* @param	string			$name		The name to be allowed
	* @param	array			$computers_ids	Array with the IDs of the computers which are allowed
	*							to use this name
	*/
	function set_computers ($name, $computers_ids)
	{
		if ($name and is_array($computers_ids))
		{
			// Fetch the current list of computers IDs for this name
			$q = 'SELECT id, computer_id FROM '.TBL_VALID_DUP_NAMES.' WHERE netbios_name="'.mysql_escape_string($name).'"';
			$current_computers = DB::db_fetch_list ($q);
			
			// Delete the computers which are not allowed anymore to use the name
			$q = 'DELETE FROM '.TBL_VALID_DUP_NAMES.' WHERE id=';
			foreach ($current_computers as $id => $computer_id)
			{
				if (!in_array($computer_id, $computers_ids)) DB::db_query ($q.$computer_id);
			}
			
			// Now add the new computers
			foreach ($computers_ids as $computer_id)
			{
				if ($computer_id and !in_array($computer_id, $current_computers))
				{
					$dup = new ValidDupName ();
					$dup->netbios_name = $name;
					$dup->computer_id = $computer_id;
					$dup->save_data ();
				}
			}
		}
	}
	
	
	/** [Class Method] Returns the valid duplicate names defined in the system
	* @param	string			$name		If specified, it will return only the objects for this
	*							specific name
	* @return	array					Associative array, the keys being valid duplicate names
	*							and the values being arrays of ValidDupName objects using
	*							those names.
	*/
	function get_valid_dup_names ($name = '')
	{
		$ret = array ();
		
		$q = 'SELECT id, netbios_name FROM '.TBL_VALID_DUP_NAMES.' ';
		if ($name) $q.= 'WHERE netbios_name="'.mysql_escape_string($name).'" ';
		$q.= 'ORDER BY netbios_name, computer_id';
		$data = DB::db_fetch_array ($q);
		
		foreach ($data as $d) $ret[$d->netbios_name][] = new ValidDupName ($d->id);
		
		return $ret;
	}
	
	/** [Class Method] Returns all computers (from the computers table) having a specific name 
	* @param	string			$name		The computer name to search
	* @return	array(Computer)				Array with the found computers
	*/
	function get_computers_by_name ($name = '')
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_COMPUTERS.' WHERE netbios_name="'.mysql_escape_string($name).'" ORDER BY id';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Computer ($id);
		
		return $ret;
	}
	
}


?>
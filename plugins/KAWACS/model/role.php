<?php

/**
* Representations of computers roles
* 
*/
class Role extends Base
{
	/** The role ID
	* @var int */
	var $id = null;
	
	/** The role name
	* @var string */
	var $name = null;
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_ROLES;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'name');
	
	
	/** Contructor. Loads an object's values if an ID is specified */
	function Role ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify the name.'); $ret = false;}
		
		return $ret;
	}
	
	
	/** Check if a role can be deleted - meaning if it is not in use */
	function can_delete ()
	{
		$ret = false;
		if ($this->id)
		{
			$ret = true;
			
			$q = 'SELECT computer_id FROM '.TBL_COMPUTERS_ROLES.' WHERE role_id='.$this->id.' LIMIT 1';
			if ($this->db_fetch_field ($q, 'computer_id'))
			{
				error_msg ('This role is assigned to one or more computers and can\'t be deleted');
				$ret = false;
			}
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Returns a list with all the roles available in the system
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- computer_id: Return only the roles set for the specified computer
	* @return	array					Associative array, the keys being role IDs and the values being role names
	*/
	public static function get_roles_list ($filter = array())
	{
		$ret = array ();
		
		if ($filter['computer_id'])
		{
			$q = 'SELECT r.id, r.name FROM '.TBL_ROLES.' r INNER JOIN '.TBL_COMPUTERS_ROLES.' cr ';
			$q.= 'ON r.id=cr.role_id WHERE cr.computer_id='.$filter['computer_id'].' ';
			$q.= 'ORDER BY r.name';
			$ret = DB::db_fetch_list ($q);
		} 
		else
		{
			$q = 'SELECT id, name FROM '.TBL_ROLES.' ORDER BY name';
			$ret = DB::db_fetch_list ($q);
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Returns all the roles available in the system
	* @return	array(Role)				Array of Role objects defined in the system
	*/
	public static function get_roles ()
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_ROLES.' ORDER BY name ';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Role($id);
		return $ret;
	}
}

?>
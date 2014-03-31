<?php

/** Class for storing names of SNMP system objects IDs found during networks discoveries.
*
* When discoveries are made for SNMP-enabled objects, the SNMP system object ID will be
* retrieved (OID: 1.3.6.1.2.1.1.2.0). Through this class you can associate descriptive
* names with these IDs, to make it easier to identify the types of discovered objects.
*
*/

class SnmpSysobjid extends Base
{
	/** The unique object id
	* @var int */
	var $id = null;
	
	/** The SNMP system object ID
	* @var string */
	var $snmp_sys_object_id = '';
	
	/** The descriptive name for this type of objects
	* @var string */
	var $name = '';
	
	
	/** The database table storing objects data
	* @var string */
	var $table = TBL_SNMP_SYSOBJIDS;
	
	/** List of fields to be used when saving or loading objects from database
	* @var array */
	var $fields = array ('id', 'snmp_sys_object_id', 'name');
	
	
	/** Class constructor. Also loads an object data if an ID is specified */
	function SnmpSysobjid ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	
	/** Check if an object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg($this->get_string('NEED_SNMP_NAME')); $ret = false;}
		
		if (!$this->snmp_sys_object_id) {error_msg($this->get_string('NEED_SNMP_ID')); $ret = false;}
		else
		{
			// Check uniqueness
			$q = 'SELECT id FROM '.TBL_SNMP_SYSOBJIDS.' WHERE snmp_sys_object_id="'.mysql_escape_string($this->snmp_sys_object_id).'" ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			$q.= 'LIMIT 1';
			if ($this->db_fetch_field ($q, 'id'))
			{
				error_msg ($this->get_string('NEED_UNIQUE_SNMP_ID'));
				$ret = false;
			}
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns the descriptive name for a specified SNMP system object id */
	function get_name ($snmp_sys_object_id)
	{
		$q = 'SELECT name FROM '.TBL_SNMP_SYSOBJIDS.' WHERE snmp_sys_object_id="'.mysql_escape_string($snmp_sys_object_id).'" ';
		return DB::db_fetch_field ($q, 'name');
	}
	
	/** [Class Method] Returns a list with the known SNMP objects IDs
	* @return	array					Associative array, they keys being SNMP system objects ids and
	*							the values being their descriptive names
	*/
	function get_snmp_list ()
	{
		$q = 'SELECT snmp_sys_object_id, name FROM '.TBL_SNMP_SYSOBJIDS.' ORDER BY name, snmp_sys_object_id';
		return DB::db_fetch_list ($q);
	}
	
	/** [Class Method] Returns an array with the SnmpSysobjid objects defined in the database 
	* @return	array(SnmpSysobjid)
	*/
	function get_objects ()
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_SNMP_SYSOBJIDS.' ORDER BY name, snmp_sys_object_id';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new SnmpSysobjid ($id);
		
		return $ret;
	}
}

?>
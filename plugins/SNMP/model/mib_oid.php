<?php

class_load ('Mib');

/**
* Class for representing MIBs OIDs
*
**/

class MibOid extends Base
{
	/** The unique ID of the OID
	* @var int */
	var $id = null;
	
	/** The ID of the MIB to which this OID belongs to
	* @var int */
	var $mib_id = null;
	
	/** The numeric OID
	* @var string */
	var $oid = '';
	
	/** The name of this OID
	* @var string */
	var $name = '';
	
	/** The ID of the parent Oid object, if any
	* @var int */
	var $parent_id = 0;
	
	/** The "depth" of the OID in the tree. This is calculated when the MIB file is
	* loaded, to avoid the need of calculating the "depth" every time an item is requested
	* @var int */
	var $level = 0;
	
	/** The ordering index for this OID, to reflect the order in which the OIDs are listed 
	* in the original MIB file. Like the 'level' field, it is assigned when the file is uploaded
	* @var int */
	var $ord;
	
	/** The data type for this OID - see $GLOBALS['SNMP_TYPES']
	* @var int */
	var $data_type = 0;
	
	/** The type of this OID node - see the constants SNMP_NODE_*
	* @var int */
	var $node_type = SNMP_NODE_NONE;
	
	/** The access level for this OID - see $GLOBALS['SNMP_ACCESSES']
	* @var int */
	var $access = SNMP_ACCESS_NONE;
	
	/** The status for this OID - see $GLOBALS['SNMP_OID_STATS']
	* @var int */
	var $status = SNMP_OID_STAT_NONE;
	
	/** The syntax for this OID 
	* @var string */
	var $syntax = '';
	
	/** The description for this OID
	* @var string */
	var $description = '';
	
	
	/** Associative array with the description of values - if defined
	* @var array */
	var $vals = array ();
	
	
	/** Table storing OIDs data
	* @var string */
	var $table = TBL_MIBS_OIDS;
	
	/** The fields to load/save to database 
	* @var array */
	var $fields = array ('id', 'mib_id', 'oid', 'name', 'parent_id', 'level', 'ord', 'data_type', 'node_type', 'access', 'status', 'syntax', 'description');
	
	/** Constructor. Also loads the object data if an ID is specified */
	function MibOid ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	
	/** Sets the mapping of values-names for numeric OIDs 
	* @param	array			$vals		Associative array, the keys being the OID values and the array values being the corresponding names
	*/
	function set_vals ($vals = array ())
	{
		if ($this->id)
		{
			$this->db_query ('DELETE FROM '.TBL_MIBS_OIDS_VALS.' WHERE oid_id='.$this->id);
			if (is_array($vals) and count($vals)>0)
			{
				$q = 'INSERT INTO '.TBL_MIBS_OIDS_VALS.' (oid_id, val, name) VALUES ';
				foreach ($vals as $k=>$v) $q.= '('.$this->id.','.intval($k).',"'.mysql_escape_string($v).'"),';
				$q = preg_replace ('/,\s*$/', '', $q);
				$this->db_query ($q);
			}
		}
	}
	
	function load_data ()
	{
		parent::load_data ();
		if ($this->id)
		{
			$q = 'SELECT val, name FROM '.TBL_MIBS_OIDS_VALS.' WHERE oid_id='.$this->id.' ORDER BY val';
			$this->vals = $this->db_fetch_list ($q);
		}
	}
	
	/** Deletes the object and all associated values */
	function delete ()
	{
		if ($this->id)
		{
			// Delete the list of values
			$this->db_query ('DELETE FROM '.TBL_MIBS_OIDS_VALS.' WHERE oid_id='.$this->id);
			
			// Remove any reference to this from monitor items
			$this->db_query ('UPDATE '.TBL_MONITOR_ITEMS.' SET snmp_oid_id=0 WHERE snmp_oid_id='.$this->id);
			
			// Delete the object itself
			parent::delete ();
		}
	}
	
}


?>
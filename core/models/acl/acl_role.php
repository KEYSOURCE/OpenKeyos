<?php
class_load ('Acl');

/**
* Class for managing ACL roles
*
* An AclRole objects stores a collection of permissions (ACL Items),
* to facilitate managing user access rights.
*/

class AclRole extends Base
{
	/** Role ID
	* @var int */
	var $id = null;

	/** The name of the ACL role
	* @var string */
	var $name = '';
	
	/** The type of this role - see $GLOBALS['ACL_ROLE_TYPES']
	* @var integer */
	var $type = ACL_ROLE_TYPE_KEYSOURCE;
	

	/** The list of assigned ACL item IDs
	* @var items */
	var $items_list = array ();
	
	
	/** The database table storing objects data 
	* @var string */
	var $table = TBL_ACL_ROLES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'name', 'type');
	
	
	/** 
	* Constructor, also loads the object data if an ID is specified 
	*/
	function AclRole ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	
	/**
	* Loads the object data, as well as the assigned list of ACL Items
	*/
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				// Load the list of ACL item IDs
				$q = 'SELECT pi.acl_item_id FROM '.TBL_ACL_ROLES_ITEMS.' pi ';
				$q.= 'INNER JOIN '.TBL_ACL_ITEMS.' i on pi.acl_item_id=i.id ';
				$q.= 'INNER JOIN '.TBL_ACL_CATEGORIES.' c on i.category_id=c.id ';
				$q.= 'WHERE pi.acl_role_id='.$this->id.' ';
				$q.= 'ORDER BY c.name, i.name ';
				
				$ids = $this->db_fetch_array ($q);
				foreach ($ids as $id) $this->items_list[] = $id->acl_item_id;
			}
		}
	}
	
	
	/**
	* Loads the object data from an array
	*/
	function load_from_array ($data = array())
	{
		parent::load_from_array ($data);
		if (isset($data['items_list']) and is_array($data['items_list']))
		{
			$this->items_list = $data['items_list'];
		}
	}
	
	
	/**
	* Checks if the object data is valid
	*/
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('The ACL role must have a name'); $ret = false;}
		if (empty ($this->items_list)) {error_msg ('Please specify the permissions assigned to this role'); $ret = false;}
		
		return $ret;
	}
	
	
	/**
	* Saves the object data
	*/
	function save_data ()
	{
		// Save the object-specific data
		parent::save_data ();
		if ($this->id)
		{
			// Save the list of role items
			$this->db_query ('DELETE FROM '.TBL_ACL_ROLES_ITEMS.' WHERE acl_role_id='.$this->id);
			
			if (count($this->items_list) > 0)
			{
				$q = 'INSERT INTO '.TBL_ACL_ROLES_ITEMS.' (acl_role_id, acl_item_id) VALUES ';
				for ($i=0; $i<count($this->items_list); $i++)
				{
					$q.= '('.$this->id.', '.$this->items_list[$i].'), ';
				}
				$q = preg_replace ('/,\s*$/', '', $q);
				$this->db_query ($q);
			}
		}
	}
	

	/**
	* Returns a list of associated AclItem objects
	*/
	function get_items ()
	{
		$ret = array ();
		
		if (is_array($this->items_list))
		{
			for ($i=0; $i<count($this->items_list); $i++)
			{
				$ret[] = new AclItem ($this->items_list[$i]);
			}
		}
		
		return $ret;
	}
	
	
	/**
	* Checks if a role can be deleted
	*/
	function can_delete ()
	{
		$ret = true;
		
		if ($this->id)
		{
			// Check if this role is not in use
			$q = 'SELECT count(*) as cnt FROM '.TBL_ACL.' WHERE acl_role_id='.$this->id;
			$cnt = $this->db_fetch_field ($q);
			
			if ($cnt > 0)
			{
				error_msg ('This role is in use and can\'t be deleted');
				$ret = false;
			}
		}
		
		return $ret;
	}
	
	
	/**
	* Deletes a role
	*/
	function delete ()
	{
		if ($this->id)
		{
			parent::delete ();
			$q = 'DELETE FROM '.TBL_ACL_ROLES_ITEMS.' WHERE acl_role_id='.$this->id;
			$this->db_query ($q);
		}
	}
	
	
	/**
	* [Class Method] Returns a list of ACL role objects
	*/
	function get_roles ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_ACL_ROLES.' ';
		
		if ($filter['type']) $q.= 'WHERE type = '.$filter['type'].' ';
		
		$q.= 'ORDER BY name ';
		$ids = db::db_fetch_array ($q);
		
		foreach ($ids as $id)
		{
			$ret[] = new AclRole ($id->id);
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of role names
	* @return	array		Associative array, they keys being role IDs and the values role names
	*/
	function get_roles_list ()
	{
		$ret = array ();
		
		$q = 'SELECT id, name FRROM '.TBL_ACL_ROLES.' ORDER BY name ';
		$ids = db::db_fetch_array ($q);
		
		foreach ($ids as $id)
		{
			$ret[$id->id] = $id->name;
		}
	}
}

?>

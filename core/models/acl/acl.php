<?php
class_load ('AclCategory');
class_load ('AclItem');
class_load ('AclItemOperation');
class_load ('AclRole');

/**
* Class for managing access permissions for users. 
*
* The ACL objects will represent to which kind of operations each
* user will have access to.
*/


class Acl extends Base 
{
	/** The user to which this ACL  object belongs
	* @var int */
	var $user_id = null;
	
	/** The ID of the ACL item permitted for this user 
	* @var int */
	var $acl_role_id = null;
	
	
	/** The ACL role object 
	* @var AclRole */
	var $acl_role = null;
	
	
	/** The table storing object's data */
	var $table = TBL_ACL;
	
	/** The list of primary keys
	* @var array () */
	var $primary_key = array ('user_id', 'acl_role_id');
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('user_id', 'acl_role_id');
	
	
	/**
	* Class constructor, also loads the object data if an ID is specified
	*/
	function Acl ($user_id = null, $acl_role_id = null)
	{
		if ($user_id and $acl_role_id)
		{
			$this->user_id = $id;
			$this->acl_role_id = $id;
			$this->load_data ();
		}
	}
	
	
	/** Loads the object data, as well as the ACL role definition */
	function load_data ()
	{
		if ($this->user_id and $this->acl_role_id)
		{
			parent::load_data ();
			if ($this->user_id and $this->acl_role_id)
			{
				$this->acl_role = new AclRole ($this->acl_role_id);
			}
		}
	}
	
}

?>
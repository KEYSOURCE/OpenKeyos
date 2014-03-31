<?php
class_load ('Acl');

/**
* Class for representing a method/function (operation) belonging to a 
* specific AclItem object.
*/

class AclItemOperation extends Base
{
	/** Object ID
	* @var int */
	var $id = null;

	/** The ID of the AclItem to which this belongs
	* @var int */
	var $acl_item_id = '';
	
	/** The module (display class) name
	* @var string */
	var $module = '';
	
	/** The module function (display class method) name
	* @var string */
	var $function = '';

	
	
	/** The database table storing user data 
	* @var string */
	var $table = TBL_ACL_ITEMS_OPERATIONS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'acl_item_id', 'module', 'function');
	
	
	
	/** 
	* Constructor, also loads the object data if an ID is specified 
	*/
	function AclItemOperation ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
}


?>

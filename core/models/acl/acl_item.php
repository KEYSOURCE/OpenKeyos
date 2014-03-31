<?php
class_load ('Acl');

/**
* Class for managing user permissions to the system
*
* An AclItem object is basically a set of module/operation pairs, grouped
* under a common name, to facilitate granting permissions to users in a 
* more intuitive way.
*/

class AclItem extends Base
{
	/** Item ID
	* @var int */
	var $id = null;

	/** The name of the ACL item
	* @var string */
	var $name = '';
	
	/** Specifies if this a special item, meaning that 
	* it is not linked to a specific module/operation, but it
	* has a special synthetic meaning
	* @var boolean */
	var $special = false;
	
	/** The category to which this item belongs to
	* @var int */
	var $category_id = null;

	
	/** The operations which are part of this item
	* @var array(AclItemOperation) */
	var $operations = array ();
	
	/** The Category object to which this item belongs
	* @var Category */
	var $category = null;
	
	
	/** The database table storing user data 
	* @var string */
	var $table = TBL_ACL_ITEMS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'name', 'category_id', 'special');
	
	
	
	/** 
	* Constructor, also loads the object data if an ID is specified 
	*/
	function AclItem ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	
	/**
	* Loads the object data, as well as the assigned list of operations and the category definition
	*/
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				// Load the list of operations for this item
				if (!$this->special)
				{
					// Load the list of operations belonging to this item
					$q = 'SELECT id FROM '.TBL_ACL_ITEMS_OPERATIONS.' WHERE ';
					$q.= 'acl_item_id='.$this->id.' ';
					$q.= 'ORDER BY module, function ';
					
					$ids = $this->db_fetch_array ($q);
					foreach ($ids as $id)
					{
						$this->operations[] = new AclItemOperation ($id->id);
					}
				}
				
				// Load the category definition
				if ($this->category_id)
				{
					$this->category = new AclCategory ($this->category_id);
				}
			}
		}
	}
	
	
	/**
	* Checks if the object data is valid
	*/
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('The ACL item must have a name'); $ret = false;}
		if (!$this->category_id) {error_msg ('The ACL item must belong to a category'); $ret = false;}
		
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
			// First, delete from database the operations which are not assigned anymore
			$q = 'SELECT id, module, function FROM '.TBL_ACL_ITEMS_OPERATIONS.' ';
			$q.= 'WHERE acl_item_id = '.$this->id;
			$current_ops = $this->db_fetch_array ($q);
			foreach ($current_ops as $op)
			{
				if (!$this->has_operation($op->module, $op->function))
				{
					$operation = new AclItemOperation ($op->id);
					$operation->delete ();
				}
			}
			
			$this->operations = array_values ($this->operations);
			//debug ($this->operations);
			// Save now the information about the assigned operations
			for ($i=0; $i<count ($this->operations); $i++)
			{
				$this->operations[$i]->save_data ();
			}
		}
	}
	
	
	/**
	* Checks if a specific operation is set for this ACL item
	* @param	string	$class		The class name
	* @param	string	$method		The method name
	* @return	boolean			True or False if the operation exists or not
	*/
	function has_operation ($class = '', $method = '')
	{
		$ret = false;
		
		if ($class and $method)
		{
			for ($i=0; ($i<count($this->operations) and !$ret); $i++)
			{
				if ($this->operations[$i]->module==$class and $this->operations[$i]->function==$method)
				{
					$ret = true;
				}
			}
		}
		
		return $ret;
	}
	
	
	/** Deletes an ACL item and its associated operations */
	function delete ()
	{
		if ($this->id)
		{
			if (is_array ($this->operations))
			{
				for ($i=0; $i<count($this->operations); $i++)
				{
					$this->operations[$i]->delete ();
				}
			}
			parent::delete ();
		}
	}
	
	/**
	* Loads the list of operations for the specified class
	* @param	string	$class		The class for which operations will be specified
	* @param	array	$operations	The list of class methods to include
	*/
	function set_class_operations ($class = '', $operations = array())
	{
		if (!$operations) $operations = array ();
		if ($class)
		{
			// First, eliminate the operations that don't exist in the list anymore
			for ($i=0; $i<count($this->operations); $i++)
			{
				if ($this->operations[$i]->module==$class and !in_array($this->operations[$i]->function, $operations))
				{
					array_splice ($this->operations, $i, 1);
					$i--;
				}
			}
		
			// Now add the new operations
			for ($i=0; $i<count($operations); $i++)
			{
			
				if (!$this->has_operation ($class, $operations[$i]))
				{
					$operation = new AclItemOperation ();
					$operation->load_from_array (array(
						'acl_item_id' => $this->id,
						'module' => $class,
						'function' => $operations[$i]
					));
					$this->operations[] = $operation;
				}
			}
		}
	}
	
	/**
	* Returns a list of assigned functions for a specific class
	* @param	string	$class		The class name
	* @return	array			The list of functions
	*/
	function get_class_operations ($class = '')
	{
		$ret = array ();
		
		if ($class)
		{
			for ($i=0; $i<count ($this->operations); $i++)
			{
				if ($this->operations[$i]->module == $class)
				{
					$ret[] = $this->operations[$i]->function;
				}
			}
		}
		
		return $ret;
	}
	
	
}


?>
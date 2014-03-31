<?php
class_load ('Acl');

/**
* Class for representing categories for grouping ACL items
*
*/

class AclCategory extends Base
{
	/** Category ID
	* @var int */
	var $id = null;

	/** The category name
	* @var string */
	var $name = '';
	
		
	/** The database table storing object data 
	* @var string */
	var $table = TBL_ACL_CATEGORIES;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'name');

	
		
	/** 
	* Constructor, also loads the object data if an ID is specified 
	*/
	function AclCategory ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}

	
	/**
	* Checks if the object data is valid
	*/
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('The ACL category must have a name.'); $ret=false;}
		
		return $ret;
	}
	
	
	/**
	* Returns an array with the list of ACL item names in this category
	* @return	array		Associative array, the keys are item IDs and the values are item names
	*/
	function get_items_list ()
	{
		$ret = array ();
		
		if ($this->id)
		{
			$q = 'SELECT id, name FROM '.TBL_ACL_ITEMS.' WHERE category_id='.$this->id.' ORDER BY id ';
			$ids = $this->db_fetch_array ($q);
			
			foreach ($ids as $id)
			{
				$ret[$id->id] = $id->name;
			}
		}
		
		return $ret;
	}
	
	
	/**
	* Checks if the object can be deleted - meaning if there aren't any items referencing this category
	*/
	function can_delete ()
	{
		$ret = true;
		
		if ($this->id)
		{
			$q = 'SELECT count(*) AS cnt FROM '.TBL_ACL_ITEMS.' WHERE category_id='.$this->id;
			$cnt = $this->db_fetch_field ($q, 'cnt');
			if ($cnt > 0)
			{
				error_msg ('The category can\'t be deleted, it is referenced by existing ACL items');
				$ret = false;
			}
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of ACL categories objects
	* @return	array(AclCategory)		Array of AclCategory objects
	*/
	function get_categories ()
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_ACL_CATEGORIES.' ORDER BY name ';
		
		$ids = db::db_fetch_array ($q);
		
		foreach ($ids as $id)
		{
			$ret[] = new AclCategory ($id->id);
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of ACL categories
	* @return	array		Associative array, with they keys being category IDs and the values category names
	*/
	function get_categories_list ()
	{
		$ret = array ();
		$q = 'SELECT id, name FROM '.TBL_ACL_CATEGORIES.' ORDER BY name';
		
		$cats = db::db_fetch_array ($q);
		
		foreach ($cats as $cat)
		{
			$ret[$cat->id] = $cat->name;
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of all categories and their ACL items
	* @return	array		Associative array, with the keys being category IDs and 
	*				the values being arrays of AclItem objects.
	*/
	function get_categories_items ()
	{
		$ret = array ();
		
		$q = 'SELECT i.id, c.id as category_id FROM '.TBL_ACL_CATEGORIES.' c LEFT OUTER JOIN '.TBL_ACL_ITEMS.' i ';
		$q.= 'ON i.category_id = c.id ';
		$q.= 'ORDER BY c.name, i.id ';
		
		$items = db::db_fetch_array ($q);
		
		foreach ($items as $item)
		{
			if ($item->id)
			{
				$ret[$item->category_id][] = new AclItem ($item->id);
			}
			else
			{
				$ret[$item->category_id] = null;
			}
		}
		
		return $ret;
	}
}

?>
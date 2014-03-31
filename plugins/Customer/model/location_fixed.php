<?php

/**
* Class for managing "fixed" locations - meaning countries, cities and provinces.
*
* These customer-independent locations are used in the definition of customer-specific
* locations (Location class).
*
*/

class LocationFixed extends Base
{
	/** Location ID
	* @var int */
	var $id = null;
	
	/** The name of the location
	* @var string */
	var $name = '';
	
	/** The type of fixed location - see $GLOBALS['LOCATION_FIXED_TYPES']
	* @var int */
	var $type = LOCATION_FIXED_TYPE_COUNTRY;
	
	/** The ID of the parent location, if this is not a country
	* @var int */
	var $parent_id = 0;
	
	
	/** Array with the child locations. This is loaded on request only, with load_children
	* @var array(LocationFixed) */
	var $children = array ();
	
	
	var $table = TBL_LOCATIONS_FIXED;
	var $fields = array ('id', 'name', 'type', 'parent_id');


	/**
	* Constructor. Also loads the object data if an object ID is provided
	* @param	int	$id		The ID of the fixed location
	*/
	function LocationFixed ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	
	function load_data ()
	{
		parent::load_data ();
		if ($this->id)
		{
			// For towns, count the number of customer locations
			if ($this->type == LOCATION_FIXED_TYPE_TOWN)
			{
				$q = 'SELECT count(*) as cnt FROM '.TBL_LOCATIONS.' WHERE town_id='.$this->id.' AND parent_id=0';
				$this->locations_count = db::db_fetch_field ($q, 'cnt');
			}
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify the name.'); $ret=false;}
		if (!$this->type) {error_msg ('Please specify the type.'); $ret = false;}
		elseif ($this->type != LOCATION_FIXED_TYPE_COUNTRY and !$this->parent_id)
		{
			error_msg ('Please specify the parent location.');
			$ret = false;
		}
		
		if ($ret)
		{
			// Check name unicity among locations of the same type and under the same parent
			$q = 'SELECT id FROM '.TBL_LOCATIONS_FIXED.' WHERE name="'.mysql_escape_string($this->name).'" AND type='.$this->type.' ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			if ($this->parent_id) $q.= 'AND parent_id='.$this->parent_id.' ';
			$q.= 'LIMIT 1';
			
			if (db::db_fetch_field ($q, 'id'))
			{
				error_msg ('The name is not unique, please check again.');
				$ret = false;
			}
		}
		
		return $ret;
	}
	
	
	/** Load the children location, recursively */
	function load_children ()
	{
		if ($this->id)
		{
			$this->children = array ();
			$q = 'SELECT id FROM '.TBL_LOCATIONS_FIXED.' WHERE parent_id='.$this->id.' ORDER BY name';
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id) $this->children[] = new LocationFixed ($id);
			
			// Load the sub-children too
			for ($i=0; $i<count($this->children); $i++) $this->children[$i]->load_children ();
		}
	}
	
	
	/**
	* [Class Method] Returns a list with all the towns and the province/cities to which they belong
	*/
	public static function get_towns_list ()
	{
		$ret = array ();
		$ret = self::get_locations_list (array('type' => LOCATION_FIXED_TYPE_TOWN));
		
		// Build the list of countries and provinces
		$countries = self::get_locations_list (array('type' => LOCATION_FIXED_TYPE_COUNTRY));
		$provinces = self::get_locations_list (array('type' => LOCATION_FIXED_TYPE_PROVINCE));
		
		// Make lists with the parent countries for provinces and parent provinces for towns
		$q = 'SELECT id, parent_id FROM '.TBL_LOCATIONS_FIXED.' WHERE type='.LOCATION_FIXED_TYPE_PROVINCE;
		$prov_countries = DB::db_fetch_list ($q);
		$q = 'SELECT id, parent_id FROM '.TBL_LOCATIONS_FIXED.' WHERE type='.LOCATION_FIXED_TYPE_TOWN;
		$towns_prov = DB::db_fetch_list ($q);
		
		foreach ($ret as $id => $name)
		{
			$ret[$id].= ' / '.$provinces[$towns_prov[$id]].' / '.$countries[$prov_countries[$towns_prov[$id]]];
		}
		
		return $ret;
		
	}
	
	
	/** [Class Method] Returns a list with fixed locations according to some criteria 
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- parent_id: Return only descendants of this fixed location
	*							- type: Return only fixed location of this type
	* @return	array()					Associative array, the keys being location IDs and the values being location names
	*/
	public static function get_locations_list ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id, name FROM '.TBL_LOCATIONS_FIXED.' WHERE ';
		if ($filter['parent_id']) $q.= 'parent_id='.$filter['parent_id'].' AND ';
		if ($filter['type']) $q.= 'type='.$filter['type'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);
		$q.= 'ORDER BY name ';
		
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/** 
	* [Class Method] Returns fixed locations according to some criteria
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- parent_id: Return only descendants of this fixed location
	*							- type: Return only fixed location of this type
	*							- load_children: If TRUE, load all the children and sub-children for the found locations.
	* @return	array(LocationFixed)			Array with the found objects
	*/
	public static function get_locations ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_LOCATIONS_FIXED.' WHERE ';
		
		if ($filter['parent_id']) $q.= 'parent_id='.$filter['parent_id'].' AND ';
		if ($filter['type']) $q.= 'type='.$filter['type'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);
		$q.= 'ORDER BY name ';
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new LocationFixed ($id);
		
		if ($filter['load_children'])
		{
			for ($i=0; $i<count($ret); $i++) $ret[$i]->load_children ();
		}
		
		return $ret;
	}
	
}
?>
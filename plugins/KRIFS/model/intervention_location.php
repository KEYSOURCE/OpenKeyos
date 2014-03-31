<?php

/**
* Class for managing the locations where the tickets activities can be performed
*
*/

class InterventionLocation extends Base
{
	/** Location ID
	* @var int */
	var $id = null;
	
	/** The name of the location 
	* @var string */
	var $name = '';
	
	/** Specifies if this location is to be considered "on site" (meaning at customer's location)
	* @var bool */
	var $on_site = false;
	
	/** Specifies if this is a helpdesk location
	* @var bool */
	var $helpdesk = false;
	
	var $table = TBL_INTERVENTION_LOCATIONS;
	var $fields = array ('id', 'name', 'on_site', 'helpdesk');


	/**
	* Constructor. Also loads the data if an ID has been specified
	* @param	int	$id		The ID of the object to load
	*/
	function InterventionLocation ($id = null)
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
	
	
	/** Checks if the location can be deleted - meaning to check if it's not already used
	* by tickets details or timesheets details */
	function can_delete ()
	{
		$ret = false;
		
		if ($this->id)
		{
			$ret = true;
			$q = 'SELECT id FROM '.TBL_TICKETS_DETAILS.' WHERE location_id='.$this->id.' LIMIT 1';
			$td_id = $this->db_fetch_field ($q, 'id');
			$q = 'SELECT id FROM '.TBL_TIMESHEETS_DETAILS.' WHERE location_id='.$this->id.' LIMIT 1';
			$tsd_id = $this->db_fetch_field ($q, 'id');
			
			if ($td_id or $tsd_id)
			{
				$ret = false;
				error_msg ('This location is already in use and can\'t be deleted.');
			}
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns the name of an intervention location specified by ID */
	public static function get_location_name ($location_id)
	{
		$ret = '';
		if ($location_id)
		{
			$q = 'SELECT name FROM '.TBL_INTERVENTION_LOCATIONS.' WHERE id='.$location_id;
			$ret = DB::db_fetch_field ($q, 'name');
		}
		return $ret;
	}
	
	/** [Class Method] Returns a list with the currently defined locations 
	* @param	array		$filter		Associative array with filtering criteria. Can contain:
	*						- on_site: If specified and True, return only "on site" locations.
	*						  If False, return only locations which are not "on site".
	*						- helpdesk: If specified and True/False, return only locations which are or are not helpdesk
	* @return	array				Associative array, the keys being location IDs and the values being
	*						location names.
	*/
	public static function get_locations_list ($filter = array())
	{
		//$ret = array ();

		if (isset($filter['on_site'])) $filter['on_site'] = ($filter['on_site'] ? 1 : 0);
		if (isset($filter['helpdesk'])) $filter['helpdesk'] = ($filter['helpdesk'] ? 1 : 0);
		
		$q = 'SELECT id, name FROM '.TBL_INTERVENTION_LOCATIONS.' WHERE ';
		if (isset($filter['on_site'])) $q.= 'on_site='.$filter['on_site'].' AND ';
		if (isset($filter['helpdesk'])) $q.= 'helpdesk='.$filter['helpdesk'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= ' ORDER BY name ';
		
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/** Returns the locations currently defined 
	* @param	array		$filter		Associative array with filtering criteria. Can contain:
	*						- on_site: If specified and True, return only "on site" locations.
	*						  If False, return only locations which are not "on site".
	* @return	array(InterventionLocation)	Array with the matched InterventionLocation objects
	*/
	function get_locations ($filter = array())
	{
		$ret = array ();
		if (isset($filter['on_site'])) $filter['on_site'] = ($filter['on_site'] ? 1 : 0);
		if (isset($filter['helpdesk'])) $filter['helpdesk'] = ($filter['helpdesk'] ? 1 : 0);
		
		$q = 'SELECT id FROM '.TBL_INTERVENTION_LOCATIONS.' WHERE ';
		if (isset($filter['on_site'])) $q.= 'on_site='.$filter['on_site'].' AND ';
		if (isset($filter['helpdesk'])) $q.= 'on_site='.$filter['helpdesk'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= ' ORDER BY name ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new InterventionLocation ($id);
		
		return $ret;
	}
}
?>
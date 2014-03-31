<?php

class_load ('LocationFixed');
class_load ('LocationComment');

/**
* Class for representing customer locations.
*
* There are two main types of locations: top-level locations, identified by a street 
* address, and sub-locations, e.g. buildings, floors, rooms etc. Each top-level
* location must be linked to a country, province and town (LocationFixed objects).
*
*/

class Location extends Base
{
	/** Location ID
	* @var int */
	var $id = null;
	
	/** The ID of the customer to which this location belongs
	* @var int */
	var $customer_id = null;
	
	/** The type of location - see $GLOBALS['LOCATION_TYPES']
	* @var int */
	var $type = LOCATION_TYPE_BUILDING;
	
	/** The name of the location
	* @var string */
	var $name = '';
	
	/** The ID of the parent location, if this is not a top-level location
	* @var int */
	var $parent_id = 0;
	
	/** The ID of the town (LocationFixed) to which this location belongs. 
	* This is stored only for top-level locations.
	* @var int */
	var $town_id = 0;
	
	/** The street address of the location. Only for top-level locations
	* @var text */
	var $street_address = '';
	
	
	/** The number of customer locations in this town
	* @var int */
	var $locations_count = 0;
	
	/** Array with the comments for this location
	* @var array(LocationComment) */
	var $comments = array ();
	
	/** Array with the child locations, if any. Note that this is loaded only on request, with load_children()
	* @var array(Location) */
	var $chilren = array ();
	
	/** Array with the parent locations, if any. Note that this is loaded only on request, with load_children()
	* @var array(Location) */
	var $parents = array ();
	
	/** Array with the list of computers assigned to this location, if any. The link between computers and locations is
	* done via the location_id field in the computers table. Keys are computer IDs and the values 
	* are computer names. Note that this is loaded only on request with load_computers_list()
	* @var array */
	var $computers_list = array ();
	
	/** Array with the list of peripherals assigned to this location, if any. The link between peripherals and
	* locations is done via the location_id field in the peripherals table. Keys are peripherals IDs and the values
	* are peripheral names. Note that this is loaded only on request with load_peripherals_list ()
	* @var array */
	var $peripherals_list = array ();
	
	/** Array with the list of AD Printers assigned to this location, if any. Unlike for computers and peripherals,
	* the link between AD Printers and locations is done via the ad_locations_table, because the AD Printers are
	* not stored as individual objects in the database. Furthermore, they are not referenced by numeric IDs but
	* by canonical names. The array keys are canonical names and the values are printer names. Note that this is
	* loaded only on request with load_ad_printers_list ()
	* @var array */
	var $ad_printers_list = array ();
	
	/** Array with the photos associated with this location, if any. Note that this is loaded only on request,
	* with load_photos() method 
	* @var array(CustomerPhoto) */
	var $photos = array ();
	
	
	var $table = TBL_LOCATIONS;
	var $fields = array ('id', 'customer_id', 'type', 'name', 'parent_id', 'town_id', 'street_address');
	
	
	/**
	* Constructor. Also loads the object data if an object ID is provided
	* @param	int	$id		The ID of the location
	*/
	function Location ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
                        //$this->verify_access();
		}
	}
	
	
	/** Loads the object data and calculates the images size */
	function load_data ()
	{
		parent::load_data ();
		if ($this->id)
		{
			// If this is a top-level location, load the country, province and town
			if ($this->town_id)
			{
				$q = 'SELECT id FROM '.TBL_LOCATIONS_FIXED.' WHERE id='.$this->town_id;
				$id = $this->db_fetch_field ($q, 'id');
				if ($id)
				{
					$this->town = new LocationFixed ($id);
					$this->province = new LocationFixed ($this->town->parent_id);
					$this->country = new LocationFixed ($this->province->parent_id);
				}
			}
			
			// Load the comments for this location
			$this->comments = LocationComment::get_comments (array('location_id'=>$this->id));
		}
	}
	
	
	/** Load the children for this location, recursively */
	function load_children ()
	{
		if ($this->id)
		{
			$this->children = array ();
			$q = 'SELECT id FROM '.TBL_LOCATIONS.' WHERE parent_id='.$this->id.' ORDER BY name';
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id) $this->children[] = new Location($id);
			
			// Load the children of the children
			for ($i=0; $i<count($this->children); $i++) $this->children[$i]->load_children ();
		}
	}
	
	
	/** Load the parents for this location */
	function load_parents ()
	{
		if ($this->parent_id)
		{
			$this->parents = array ();
			$parent_id = $this->parent_id;
			
			while ($parent_id)
			{
				$this->parents[] = new Location($parent_id);
				$parent_id = $this->parents[count($this->parents)-1]->parent_id;
			}
			$this->parents = array_reverse($this->parents);
		}
	}
	
	
	/** Loads the list of computers assigned to this location
	* @param	bool			$recursive	If True and if the list of children has been loaded, then ask all the
	*							children to load their computers list too.
	*/
	function load_computers_list ($recursive = false)
	{
		if ($this->id)
		{
			class_load ('Computer');
			$this->computers_list = Computer::get_computers_list (array('location_id' => $this->id));
			
			if ($recursive and count($this->children>0))
			{
				for ($i=0; $i<count($this->children); $i++) $this->children[$i]->load_computers_list (true);
			}
		}
	}
	
	
	/** Loads the list of peripherals assigned to this location 
	* @param	bool			$recursive	If True and if the list of children has been loaded, then ask all the
	*							children to load their peripherals list too.
	*/
	function load_peripherals_list ($recursive = false)
	{
		if ($this->id)
		{
			class_load ('Peripheral');
			$this->peripherals_list = Peripheral::get_peripherals_list (array('location_id' => $this->id));
			
			if ($recursive and count($this->children>0))
			{
				for ($i=0; $i<count($this->children); $i++) $this->children[$i]->load_peripherals_list (true);
			}
		}
	}
	
	
	/** Loads the list of peripherals assigned to this location 
	* @param	bool			$recursive	If True and if the list of children has been loaded, then ask all the
	*							children to load their AD Printers list too.
	*/
	function load_ad_printers_list ($recursive = false)
	{
		if ($this->id and $this->customer_id)
		{
			class_load ('AD_Printer');
			$this->ad_printers_list = array ();
			
			// Load the list with all AD printers for this customer
			$ad_printers_list = AD_Printer::get_ad_printers_list_canonical (array('customer_id' => $this->customer_id));
			
			// Now load the assigned printers to the location and fetch the name from the above array
			$q = 'SELECT canonical_name FROM '.TBL_AD_PRINTERS_EXTRAS.' WHERE location_id='.$this->id;
			$cns = $this->db_fetch_vector ($q);
			
			foreach ($ad_printers_list as $cn => $name)
			{
				if (in_array($cn, $cns)) $this->ad_printers_list[$cn] = $name;
			}
			
			if ($recursive and count($this->children>0))
			{
				for ($i=0; $i<count($this->children); $i++) $this->children[$i]->load_ad_printers_list (true);
			}
		}
	}
	
	
	/** Loads the photos for this location, if any */
	function load_photos ()
	{
		if ($this->id)
		{
			class_load ('CustomerPhoto');
			$this->photos = CustomerPhoto::get_photos (array('object_class'=>PHOTO_OBJECT_CLASS_LOCATION, 'object_id'=>$this->id));
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify the name.'); $ret = false;}
		if (!$this->customer_id) {error_msg ('Please spcify the customer.'); $ret = false;}
		if (!$this->type) {error_msg ('Please specify the type.'); $ret = false;}
		if (!$this->parent_id and !$this->town_id) {error_msg ('Please specify the parent location or town.'); $ret = false;}
		
		return $ret;
	}
	
	
	/** Saves the location data. Also makes sure that each children (if) any are updated with the current
	* customer and town and street address */
	function save_data ()
	{
		parent::save_data ();
		if ($this->id) $this->update_children_customer ();
	}
	
	
	/** Updates the children of the current location with the customer, town and street address from the current customer */
	function update_children_customer ()
	{
		if ($this->id)
		{
			$q = 'SELECT id FROM '.TBL_LOCATIONS.' WHERE parent_id='.$this->id;
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$location = new Location ($id);
				$location->customer_id = $this->customer_id;
				$location->town_id = $this->town_id;
				$location->street_address = $this->street_address;
				
				// Saving data for child objects will also cascade the modifications to their children as well
				$location->save_data ();
			}
		}
	}
	
	
	/** Set the computers assigned to this location.
	* @param	array			$ids		Array with the IDs of the assigned computers
	*/
	function set_computers ($ids = array ())
	{
		if ($this->id)
		{
			// First, delete the currently assigned computers
			$q = 'UPDATE '.TBL_COMPUTERS.' SET location_id=0 WHERE location_id='.$this->id;
			$this->db_query ($q);
			
			if (count($ids) > 0)
			{
				foreach ($ids as $id)
				{
					$this->db_query ('UPDATE '.TBL_COMPUTERS.' SET location_id='.$this->id.' WHERE id='.$id);
				}
			}
		}
	}
	
	
	/** Set the peripherals assigned to this location.
	* @param	array			$ids		Array with the IDs of the assigned peripherals
	*/
	function set_peripherals ($ids = array ())
	{
		if ($this->id)
		{
			// First, delete the currently assigned peripherals
			$q = 'UPDATE '.TBL_PERIPHERALS.' SET location_id=0 WHERE location_id='.$this->id;
			$this->db_query ($q);
			
			if (count($ids) > 0)
			{
				foreach ($ids as $id)
				{
					$this->db_query ('UPDATE '.TBL_PERIPHERALS.' SET location_id='.$this->id.' WHERE id='.$id);
				}
			}
		}
	}
	
	
	/** Set the AD Printers assigned to this location
	* @param	array			$cns		Array with the canonical names of the assinged AD Printers
	*/
	function set_ad_printers ($cns = array())
	{
		if ($this->id and $this->customer_id)
		{
			// First, delete the currently assigned AD Printers to this location
			$this->db_query ('UPDATE '.TBL_AD_PRINTERS_EXTRAS.' SET location_id=0 WHERE location_id='.$this->id);
			if (count($cns) > 0)
			{
				foreach ($cns as $cn)
				{
					$q = 'UPDATE '.TBL_AD_PRINTERS_EXTRAS.' SET location_id='.$this->id.' ';
					$q.= 'WHERE canonical_name="'.mysql_escape_string($cn).'"';
					$this->db_query ($q);
				}
			}
		}
	}
	
	
	/** Delete a location and all children and associated objects */
	function delete ()
	{
		if ($this->id)
		{
			// First, delete recursively all children
			$q = 'SELECT id FROM '.TBL_LOCATIONS.' WHERE parent_id='.$this->id;
			$ids = $this->db_fetch_vector ($q);
			
			foreach ($ids as $id)
			{
				$child = new Location ($id);
				$child->delete ();
			}
			
			// Delete associated objects
			$q = 'DELETE FROM '.TBL_LOCATIONS_COMMENTS.' WHERE location_id='.$this->id;
			$this->db_query ($q);
			$q = 'UPDATE '.TBL_COMPUTERS.' SET location_id=0 WHERE location_id='.$this->id;
			$this->db_query ($q);
			$q = 'UPDATE '.TBL_REMOVED_COMPUTERS.' SET location_id=0 WHERE location_id='.$this->id;
			$this->db_query ($q);
			$q = 'UPDATE '.TBL_PERIPHERALS.' SET location_id=0 WHERE location_id='.$this->id;
			$this->db_query ($q);
			$q = 'UPDATE '.TBL_AD_PRINTERS_EXTRAS.' SET location_id=0 WHERE location_id='.$this->id;
			$this->db_query ($q);
			$q = 'DELETE FROM '.TBL_CUSTOMERS_PHOTOS.' WHERE object_class='.PHOTO_OBJECT_CLASS_LOCATION.' AND object_id='.$this->id;
			$this->db_query ($q);
			
			// Finally, delete the object itself
			parent::delete ();
		}
	}
	
	
	function make_list ($indent, &$list)
	{
		if ($this->id)
		{
			$list[$this->id] = $indent.$this->name;
			for ($i=0; $i<count($this->children); $i++)
			{
				$this->children[$i]->make_list ($indent.'&nbsp;&nbsp;&nbsp;', $list);
			}
		}
	}
	
	/** [Class Method] Returns a list with the customer locations according to some criteria
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- customer_id: Return only locations for the specified customer
	*							- indent: prepend the locations with spaces corresponding to their places
	*/
	function get_locations_list ($filter = array ())
	{
		$ret = array ();
		
		if ($filter['indent'])
		{
			$towns_list = LocationFixed::get_towns_list ();
			$filter['top_only'] = true;
			$filter['load_children'] = true;
			$filter['order_by'] = 'town';
			$locations = Location::get_locations ($filter);
			
			$indent = '&nbsp;&nbsp;&nbsp;';
			$last_town_id = 0;
			$town_index = -1;
			for ($i=0; $i<count($locations); $i++)
			{
				if ($last_town_id != $locations[$i]->town_id)
				{
					$last_town_id = $locations[$i]->town_id;
					$ret[$town_index--] = '['.$towns_list[$last_town_id].']';
				}
				$locations[$i]->make_list ($indent.'&nbsp;&nbsp;&nbsp;', $ret);
			}
		}
		else
		{
			$q = 'SELECT id, name FROM '.TBL_LOCATIONS.' ';
			if ($filter['customer_id']) $q.= 'WHERE customer_id='.$filter['customer_id'].' ';
			$ret = DB::db_fetch_list ($q);
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Return customer locations according to some criteria 
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- customer_id: Return only locations for the specified customer
	*							- town_id: Return only locations for the specified town
	*							- order_by: Can be 'customer', 'name' or 'town', to sort by customer name, location name or town name
	*							- top_only: If True, only top-level locations will be loaded
	*							- load_children: If True, the child locations will be loaded for each found location
	*							- load_objects: If True, load the computers, peripherals and AD printers for
	*							  each found location
	* @return	array (Location)			Array with the found Location objects
	*/
	function get_locations ($filter = array ())
	{
		$ret = array ();
		
		if (!$filter['order_by']) $filter['order_by'] = 'name';
		
		$q = 'SELECT l.id FROM '.TBL_LOCATIONS.' l ';
		if ($filter['order_by'] == 'customer') $q.= 'INNER JOIN '.TBL_CUSTOMERS.' c ON l.customer_id=c.id ';
		elseif ($filter['order_by'] == 'town') $q.= 'INNER JOIN '.TBL_LOCATIONS_FIXED.' lf ON l.town_id=lf.id ';
		$q.= 'WHERE ';
		
		if ($filter['customer_id']) $q.= 'l.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['top_only']) $q.= 'l.parent_id=0 AND ';
		if ($filter['town_id']) $q.= 'l.town_id='.$filter['town_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);
		$q.= 'ORDER BY ';
		if ($filter['order_by'] == 'customer') $q.= 'c.name, l.name ';
		elseif ($filter['order_by'] == 'town') $q.= 'lf.name, l.name ';
		else $q.= 'l.name ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Location($id);
		
		if ($filter['load_children'])
		{
			for ($i=0; $i<count($ret); $i++) $ret[$i]->load_children ();
		}
		if ($filter['load_objects'])
		{
			for ($i=0; $i<count($ret); $i++)
			{
				$ret[$i]->load_computers_list (true);
				$ret[$i]->load_peripherals_list (true);
				$ret[$i]->load_ad_printers_list (true);
			}
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns a string with all parents for a location, up to (and including) town */
	function get_location_str ($location_id)
	{
		$ret = '';
		$town_id = 0;
		while ($location_id)
		{
			$q = 'SELECT town_id, parent_id, name FROM '.TBL_LOCATIONS.' WHERE id='.$location_id;
			$res = DB::db_fetch_row ($q);
			
			$ret = $res['name'] . ', ' .$ret;
			
			$location_id = $res['parent_id'];
			$town_id = $res['town_id'];
		}
		if ($town_id)
		{
			$q = 'SELECT name FROM '.TBL_LOCATIONS_FIXED.' WHERE id='.$town_id;
			$ret = DB::db_fetch_field ($q, 'name').', '.$ret;
		}
		$ret = preg_replace ('/\,\s*$/', '', $ret);
		
		return $ret;
	}

        function verify_access() {
            $uid = get_uid();
            class_load('User');
            $user = new User($uid);
            if($user->type == USER_TYPE_CUSTOMER) {
                if($this->customer_id != $user->customer_id) {
                    $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
                    header("Location: $url\n\n");
                    exit;
                }
            }
        }
}
?>
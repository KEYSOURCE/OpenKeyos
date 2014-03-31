<?php

class_load ('PeripheralClassField');
class_load ('MonitorProfilePeriph');

/**
* Represent definitions of peripherals
* 
*/

class PeripheralClass extends Base
{
	/** The class ID
	* @var int */
	var $id = null;
	
	/** The class name
	* @var string */
	var $name = '';
	
	/** The ID of the field which will store the peripheral name
	* @var int */
	var $name_width = 1;
	
	/** The position index when displaying list of classes 
	* @var int */
	var $position = 0;
	
	/** Specifies if this class of peripherals can be linked to computers 
	* @var bool */
	var $link_computers = false;
	
	/** Specifies if warranties are used for this class of peripherals
	* @var bool */
	var $use_warranty = false;
	
	/** The id of the field specifying when the warranty starts (if warranty is used)
	* @var int */
	var $warranty_start_field = null;
	
	/** The id of the field specifying when the warranty ends (if warranty is used)
	* @var int */
	var $warranty_end_field = null;
	
	/** The ID of the field storing the service package ID (if warranty is used)
	* @var int */
	var $warranty_service_package_field = null;
	
	/** The ID of the field storing the service level ID (if warranty is used)
	* @var int */
	var $warranty_service_level_field = null;
	
	/** The ID of the field storing the service contract number (if warranty is used)
	* @var int */
	var $warranty_contract_number_field = null;
	
	/** The ID of the field storing HW product ID (if warranty is used)
	* @var int */
	var $warranty_hw_prodct_id_field = null;
	
	/** Specifies if serial numbers are used for this class of peripherals
	* @var bool */
	var $use_sn = false;
	
	/** The ID of the field specifying the serial number (if serial numbers are used)
	* @var bool */
	var $sn_field = null;
	
	/** Specifies if this peripheral class is accessible by Web
	* @var bool */
	var $use_web_access = false;
	
	/** The ID of the field specifying the URL for web access (if web access is used)
	* @var int */
	var $web_access_field = null;
	
	/** Specifies if the network access wil be used for this class of peripherals 
	* @var bool */
	var $use_net_access = false;
	
	/** If net access is used, this specifies the ID of the field storing the access IP address
	* @var int */
	var $net_access_ip_field = null;
	
	/** If net access is used, this specifies the ID of the field storing the access port
	* @var int */
	var $net_access_port_field = null;
	
	/** If net access is used, this specifies the ID of the field storing the access login name
	* @var int */
	var $net_access_login_field = null;
	
	/** If net access is used, this specifies the ID of the field storing the access password
	* @var int */
	var $net_access_password_field = null;
	
	/** Specifies if SNMP data collection is available for this peripheral class
	* @var bool */
	var $use_snmp = false;
	
	
	/** Array with the possible values for the relative display width
	* @var array */
	var $width_options = array (1, 2, 3, 4, 5, 6, 7, 8, 9);
	
	
	/** Array with the field definition objects 
	* @var array(PeripheralClassField) */
	var $field_defs = array ();
	
	/** Helper array, storing in the keys field IDs and in the values storing the 
	* corresponding index from $this->field_defs
	* @var array */
	var $field_ids_idx = array ();
	
	/** Array with the IDs of the peripherals monitor profiles that can be used
	* for this class of peripherals 
	* @var array */
	var $profiles_ids = array ();
	
	/** Array with the peripherals monitor profiles that can be used for this
	* class of peripherals. Loaded on request only, with load_profiles() method
	* @var array (MonitorProfilePeriph) */
	var $profiles = array ();
	
	/** Array with the mappings between peripheral class fields and monitoring items from
	* related profiles. It is an associative array, the keys being profile IDs and the values
	* being associative arrays with class fields IDs as keys and values being associative arrays
	* with two fields: 'item_id' and 'item_id' (which is non-zero only if the mapping points to
	* a field from a monitor item of type "structure"
	* @var array */
	var $profiles_fields_ids = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PERIPHERALS_CLASSES;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'name', 'name_width', 'position', 'link_computers', 'use_warranty', 'warranty_start_field', 'warranty_end_field', 'use_sn', 'sn_field', 'use_web_access', 'web_access_field', 'use_net_access', 'net_access_ip_field', 'net_access_port_field', 'net_access_login_field', 'net_access_password_field', 'warranty_service_package_field', 'warranty_service_level_field', 'warranty_contract_number_field', 'warranty_hw_product_id_field', 'use_snmp');
	
	
	/** Contructor. Loads an object's values if an ID is specified */
	function PeripheralClass ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Loads the peripheral definition data, together with the definitions for its fields */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			
			if ($this->id)
			{
				// Load the fields too
				$q = 'SELECT id FROM '.TBL_PERIPHERALS_CLASSES_FIELDS.' WHERE class_id='.$this->id.' ORDER BY ord, id ';
				$ids = db::db_fetch_vector ($q);
				
				$cnt = 0;
				foreach ($ids as $id)
				{
					$this->field_defs[$cnt] = new PeripheralClassField ($id);
					$this->field_defs[$cnt]->ord = $cnt;
					$this->field_ids_idx[$id] = $cnt;
					$cnt++;
				}
				
				// Load the IDs of the monitor profiles that can be used
				$q = 'SELECT DISTINCT pp.profile_id FROM '.TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS.' pp INNER JOIN '.TBL_MONITOR_PROFILES_PERIPH.' p ';
				$q.= 'ON pp.profile_id=p.id WHERE class_id='.$this->id.' ORDER BY p.name ';
				$this->profiles_ids = db::db_fetch_vector ($q);
				
				// Load the mappings between peripheral class fields and monitoring profiles items fields
				$this->profiles_fields_ids = array ();
				foreach ($this->profiles_ids as $id) $this->profiles_fields_ids[$id] = array (); // Preserve the order of profiles
				$q = 'SELECT * FROM '.TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS.' WHERE class_id='.$this->id;
				$data = db::db_fetch_array ($q);
				foreach ($data as $d)
				{
					$this->profiles_fields_ids[$d->profile_id][$d->class_field_id] = array ('item_id' => $d->item_id, 'item_field_id' => $d->item_field_id);
				}
			}
		}
	}
	
	/** Loads the MonitorProfilePeriph associated with this peripheral class */
	function load_profiles ()
	{
		if ($this->id)
		{
			$this->profiles = array ();
			foreach ($this->profiles_ids as $id) $this->profiles[] = new MonitorProfilePeriph ($id);
		}
	}

	
	/** Checks if the class definition is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify the class name.'); $ret = false;}
		else
		{
			// Check name uniqueness
			$q = 'SELECT id FROM '.TBL_PERIPHERALS_CLASSES.' WHERE name="'.db::db_escape ($this->name).'" ';
			if ($this->id) $q.= 'AND id<>'.$this->id;
			$exist_id = db::db_fetch_field ($q, 'id');
			
			if ($exist_id) {error_msg ('A peripheral class with the same name already exists, please choose another one.'); $ret = false;}
		}
		
		if ($this->use_warranty)
		{
			if (!$this->warranty_start_field or !$this->warranty_end_field)
			{
				error_msg ('Please specify the fields storing the start and end of the warranty period.');
				$ret = false;
			}
			elseif ($this->warranty_start_field==$this->warranty_end_field)
			{
				error_msg ('The fields specifying the warranty interval can\'t be the same.');
				$ret = false;
			}
			
		}
		
		if ($this->use_sn and !$this->sn_field)
		{
			error_msg ('Please specify the field storing the serial number.'); $ret = false;
		}
		
		if ($this->use_web_access and !$this->web_access_field)
		{
			error_msg ('Please specify the field storing the web access url.'); $ret = false;
		}
		
		return $ret;
	}
	
	
	/** Saves the peripheral definition data, as well as the definitions of the fields (if any) */
	function save_data ()
	{
		$is_new = (!$this->id);
		parent::save_data ();

		for ($i=0; $i<count($this->field_defs); $i++)
		{
			$this->field_defs[$i]->save_data ();
		}
		
		// If this was a new class, make sure the positions are in consecutive order
		if ($is_new) $this->set_positions ();

	}

	
	/** Saves the order of the fields
	* @param	array		$order		Array with the order of the fields, the keys representing the
	*						positions and the values representing the field ids
	*/
	function set_fields_order ($order)
	{
		if ($this->id and is_array ($order))
		{
			foreach ($order as $ord => $field_id)
			{
				$q = 'UPDATE '.TBL_PERIPHERALS_CLASSES_FIELDS.' SET ord='.$ord.' WHERE id='.$field_id;
				db::db_query ($q);
			}
		}
	}
	
	/** Sets the mapping between the fields for this class and the monitoring items for a given peripherals monitoring profile
	* @param	int			$profile_id	The ID of the monitoring profile to which the mapping is done
	* @param	array			$item_ids	Associative array with the fields mappings, the keys being
	*							classe's fields IDs and the values being monitoring items IDs.
	*							Where a class field is mapped to an item field from a structure,
	*							the value will contain both the item and the field id, separated by '_'
	*/
	function set_profile_items_fields ($profile_id, $item_ids)
	{
		if ($this->id and $profile_id)
		{
			// First, delete all the previous settings
			$this->remove_profile_references ($profile_id);
			
			// Now set the new references
			foreach ($item_ids as $field_id => $item_id) if ($item_id)
			{
				list ($item_id, $item_field_id) = split ('_', $item_id);
				
				$q = 'INSERT INTO '.TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS.' (class_id, profile_id, class_field_id, item_id, item_field_id) VALUES ';
				$q.= '('.$this->id.','.$profile_id.','.$field_id.','.$item_id.','. ($item_field_id ? $item_field_id : '0') .')';
				db::db_query ($q);
			}
		}
	}
	
	
	/** Checks if there a mapping between the specified monitor item ID and any of the peripheral class fields (or a specific field)
	* @param	mixed			$item_id	A monitor item id OR a string made of an item id and an item field id, separated by '_'
	* @param	int			$profile_id	(Optional) If specified, the check is limited to this profile ID
	* @param	int			$field_id	(Optional) If specified, the check is done specifically for this class field ID
	* @return	bool					True or False if there is a peripheral class field mapped to the specified item
	*/
	function has_monitor_item ($item_id, $profile_id=false, $field_id=false)
	{
		$ret = false;
		
		if ($this->id and $item_id)
		{
			list ($item_id, $item_field_id) = split ('_', $item_id);
			$item_id = intval($item_id);
			$item_field_id = intval($item_field_id);
			
			if ($profile_id)
			{
				// Check a specific profile
				if (is_array($this->profiles_fields_ids[$profile_id]))
				{
					$ids = &$this->profiles_fields_ids[$profile_id];
					if ($field_id) $ret = ($ids[$field_id]['item_id'] == $item_id and $ids[$field_id]['item_field_id'] == $item_field_id);
					else
					{
						foreach ($ids as $field_id => $item)
						{
							if ($item['item_id'] == $item_id and $item['item_field_id'] == $item_field_id)
							{
								$ret = true;
								break;
							}
						}
					}
				}
			}
			else
			{
				// Check all linked profiles
				if ($field_id)
				{
					// Check a specific field
					foreach ($this->profiles_fields_ids as $profile_id => $fields_items)
					{
						if ($fields_items[$field_id]['item_id'] == $item_id and $fields_items[$field_id]['item_field_id'] == $item_field_id)
						{
							$ret = true;
							break;
						}
					}
				}
				else
				{
					// Check all fields
					foreach ($this->profiles_fields_ids as $profile_id => $fields_items)
					{
						foreach ($fields_items as $field_id => $item)
						{
							if ($item['item_id'] == $item_id and $item['item_field_id'] == $item_field_id)
							{
								$ret = true;
								break;
							}
						}
					}
				}
			}
		}
		
		return $ret;
	}
	
	/** Tells if peripherals of this class can be SNMP monitored - meaning if there are any SNMP monitor profiles associated with it */
	function can_snmp_monitor ()
	{
		return ($this->id and count($this->profiles_ids)>0);
	}
	
	/** Removes all the mappings to a given monitoring profile, basically eliminating all the associations between the
	* peripheral class and the peripheral monitoring profile 
	* @param	int			$profile_id	The ID of the monitoring profile to which the references are removed
	*/
	function remove_profile_references ($profile_id)
	{
		if ($this->id and $profile_id)
		{
			$q = 'DELETE FROM '.TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS.' WHERE class_id='.$this->id.' AND profile_id='.$profile_id;
			db::db_query ($q);
		}
	}
	
	/**
	* Returns the list with the fields of type STRING
	* @return	array				Array with the indexes from $this->field_defs
	*						which store fields of STRING type.
	*/
	function get_string_fields ()
	{
		$ret = array ();
		
		for ($i=0; $i<count($this->field_defs); $i++)
		{
			if ($this->field_defs[$i]->type == MONITOR_TYPE_STRING or $this->field_defs[$i]->type == MONITOR_TYPE_TEXT) $ret[] = $i;
		}
		
		return $ret;
	}
	
	
	/**
	* Returns the list with the fields of type INTEGER
	* @return	array				Array with the indexes from $this->field_defs
	*						which store fields of INTEGER type.
	*/
	function get_int_fields ()
	{
		$ret = array ();
		
		for ($i=0; $i<count($this->field_defs); $i++)
		{
			if ($this->field_defs[$i]->type == MONITOR_TYPE_INT) $ret[] = $i;
		}
		
		return $ret;
	}
	
	/**
	* Returns the list with the fields of type DATE
	* @return	array				Array with the indexes from $this->field_defs
	*						which store fields of DATE type.
	*/
	function get_date_fields ()
	{
		$ret = array ();
		
		for ($i=0; $i<count($this->field_defs); $i++)
		{
			if ($this->field_defs[$i]->type == MONITOR_TYPE_DATE) $ret[] = $i;
		}
		
		return $ret;
	}
	
	
	/** Deletes a peripheral class definition, along with the definition of its fields */
	function delete ()
	{
		if ($this->id)
		{
			class_load ('Peripheral');
			// Delete all peripherals of this class
			$peripherals = Peripheral::get_peripherals (array ('class_id' => $this->id, 'no_group' => true));
			for ($i=0; $i<count($peripherals); $i++)
			{
				$peripherals[$i]->delete ();
			}
			
			// Delete the fields definitions
			for ($i=0; $i<count($this->field_defs); $i++)
			{
				$this->field_defs[$i]->delete ();
			}
			
			
			parent::delete (); 
			
			// Rearange the positions
			$this->set_positions ();
		}
	}
	
	/**
	* Returns the field widths (in percents) to be used when displaying a list of peripherals. Note
	* that it will take into account only the fields which are selected to be included in 
	* listings.
	* @param	int	$max_width		The maximum width used
	* @param	int	$name_width		(By reference) It will be loaded with the relative
	*						display width to be used for the name, since the peripheral name is
	*						not in the list of fields.
	* @return 	array				Array with the display widths (in percent). The 
	*						indexes are the same as the indexes from $this->field_defs
	*/
	function get_display_widths ($max_width = 100, &$name_width)
	{
		$ret = array ();
		
		$widths_sum = $this->name_width;
		$precision = ($max_width > 1 ? 0 : 2);
		
		for ($i=0; $i<(count($this->field_defs)); $i++)
		{
			if ($this->field_defs[$i]->in_listings) $widths_sum+= $this->field_defs[$i]->display_width;
		}
		
		if ($widths_sum > 0) $factor = $max_width/$widths_sum;
		else $factor = 1;
		
		for ($i=0; $i<(count($this->field_defs)); $i++)
		{
			if ($this->field_defs[$i]->in_listings) $ret[$i] = round ($factor * $this->field_defs[$i]->display_width, $precision);
		}
		$name_width = round ($factor * $this->name_width, $precision);
		
		return $ret;
	}
	
	
	/** Returns the number of customers which are using this peripheral class */
	function get_customers_count ()
	{
		$ret = 0;
		if ($this->id)
		{
			$q = 'SELECT count(DISTINCT customer_id) as cnt FROM '.TBL_PERIPHERALS.' WHERE class_id='.$this->id;
			$ret = db::db_fetch_field ($q, 'cnt');
		}
		
		return $ret;
	}
	
	/**
	* Returns a list of customers using this peripheral class 
	* @return 	array			Associative array, with the keys being customer IDs and the values
	*					being the number of peripherals of this class that each customer has
	*/
	function get_customers_list ()
	{
		$ret = array ();
		
		if ($this->id)
		{
			$q = 'SELECT p.customer_id, count(*) as cnt FROM '.TBL_PERIPHERALS.' p ';
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' c ON p.customer_id=c.id ';
			$q.= 'WHERE p.class_id='.$this->id.' ';
			$q.= 'GROUP BY p.customer_id ORDER BY c.name ';
			$ret = db::db_fetch_list ($q);
		}
		
		return $ret;
	}
	
	
	/** Returns the number of peripherals of this class current defined */
	function get_peripherals_count ()
	{
		$ret = 0;
		if ($this->id)
		{
			$q = 'SELECT count(id) as cnt FROM '.TBL_PERIPHERALS.' WHERE class_id='.$this->id;
			$ret = db::db_fetch_field ($q, 'cnt');
		}
		
		return $ret;
	}
	
	/** Returns a list with the peripherals of this class.
	* @return	array				Associative array, the keys being customer IDs and the values being
	*						associative arrays with the peripherals IDs/names of this class that
	*						belong to each customer
	*/
	function get_peripherals_list ()
	{
		$ret = array ();
		
		if ($this->id)
		{
			$q = 'SELECT c.id as customer_id, p.id, p.name FROM '.TBL_PERIPHERALS.' p INNER JOIN '.TBL_CUSTOMERS.' c ON p.customer_id=c.id ';
			$q.= 'WHERE p.class_id='.$this->id.' ORDER BY c.name, p.name';
			$data = db::db_fetch_array ($q);
			foreach ($data as $d) $ret[$d->customer_id][$d->id] = $d->name;
		}
		return $ret;
	}
	
	/**
	* [Class Method] Sets the order of the peripherals classes
	* @param	array	$positions		Array containing the order of the classes,
	*						the indexes are positions and the values are classes IDs.
	*						If no array is passed, then this will rearrange all the positions,
	*						ensuring the numbers are in consecutive order.
	*/
	public static function set_positions ($positions = null)
	{
		if (is_array($positions) and !empty($positions))
		{
			foreach ($positions as $position => $id)
			{
				$q = 'UPDATE '.TBL_PERIPHERALS_CLASSES.' SET position='.($position+1).' ';
				$q.= 'WHERE id='.$id;
				db::db_query ($q);
			}
		}
		else
		{
			$q = 'SELECT id FROM '.TBL_PERIPHERALS_CLASSES.' ORDER BY position, id ';
			$ids = db::db_fetch_vector ($q);
			
			$pos = 1;
			foreach ($ids as $id)
			{
				$q = 'UPDATE '.TBL_PERIPHERALS_CLASSES.' SET position='.($pos++).' ';
				$q.= 'WHERE id='.$id;
				db::db_query ($q);
			}
		}
	}
	
	
	
	/**
	* [Class Method] Returns the peripheral classes defined in the system
	* @param	array		$filter		Array with filtering criteria. Can contain:
	*						- sort_by: The field by which to sort
	*						- sort_dir: The sorting direction
	* @return	array(PeripheralClass)		Array with PeripheralClass objects
	*/
	public static function get_classes ($filter = array())
	{
		$ret = array ();
		
		if (!$filter['sort_by']) $filter['sort_by'] = 'position';
		if (!$filter['sort_dir']) $filter['sort_dir'] = 'ASC';
		
		$q = 'SELECT DISTINCT id FROM '.TBL_PERIPHERALS_CLASSES.' ';
		$q.= 'ORDER BY '.$filter['sort_by'].' '.$filter['sort_dir'].' ';
		
		$ids = db::db_fetch_vector ($q);
		
		foreach ($ids as $id) $ret[] = new PeripheralClass ($id);
		
		return $ret;
	}
		
	
	/**
	* [Class Method] Returns a list of available peripherals classes 
	* @return	array				Associative array, with the key being class IDs and
	*						the values being class names
	*/
	public static function get_classes_list ()
	{
		$ret = array ();
		
		$q = 'SELECT id, name FROM '.TBL_PERIPHERALS_CLASSES.' ORDER BY position ';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}

    public static function get_classes_list_from_ids_array($ids)
	{
		$ret = array ();
		$lst = "(";
		$ind =0;
		foreach ($ids as $pid) $lst .= $pid.",";
		$lst = substr($lst, 0, strlen($lst)-1);
		$lst .= ")";
		$q = 'SELECT id, name FROM '.TBL_PERIPHERALS_CLASSES.' where id in '.$lst.' ORDER BY position ';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
}
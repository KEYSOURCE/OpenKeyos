<?php

class_load ('PeripheralClass');
class_load ('Customer');
class_load ('Computer');

/**
* Stores data about customer peripherals.
*
* Information about the peripherals is stored using the fields structure
* defined by the peripherals classes. The data for these fields can either
* be manually entered or collected with SNMP.
*
* In order for SNMP data collection to be done for a peripheral, the following
* need to be done:
* - SNMP monitoring items need to be defined for the OIDs supported by this 
*   peripheral type
* - a monitoring profile needs to be created in order to group these monitoring
*   items.
* - the monitoring profile must be "enabled" for the class definition to which
*   this peripheral belongs to
* - finally, a computer must be assigned to do the actual gathering of SNMP data.
* 
*/
class Peripheral extends Base
{
	/** The peripheral ID
	* @var int */
	var $id = null;
	
	/** The customer ID
	* @var int */
	var $customer_id = null;
	
	/** The ID of the peripheral class
	* @var int */
	var $class_id = null;
	
	/** The peripheral name
	* @var string */
	var $name = '';
	
	/** The date since when the peripheral should be considered as being managed in Keyos
	* @var timestamp */
	var $date_created = 0;
	
	/** The ID of the customer location to which this peripheral is assigned, if any
	* @var int */
	var $location_id = 0;
	
	/** The ID of the monitoring profile that should be used for this
	* peripheral, if any monitoring is done 
	* @var int */
	var $profile_id = 0;
	
	/** Tells if SNMP monitoring is enabled for this peripheral or not. This
	* flag allows supending the monitoring without removing the monitoring
	* details.
	* @var bool */
	var $snmp_enabled = false;
	
	/** Specifies the ID of the computer which will handle the collecting of
	* SNMP data about this peripheral - if any.
	* @var int */
	var $snmp_computer_id = 0;
	
	/** Specifies the IP address from which SNMP data for this peripheral
	* can be collected by the computer which was assigned to do SNMP gathering - if any
	* @var string */
	var $snmp_ip = '';
	
	
	/** PeripheralClass object with the definition of the peripheral class
	* @var PeripheralClass */
	var $class_def = null;
	
	/** The asset number of this peripheral, generated on-the-fly based on the peripheral ID
	* @var asset_no */
	var $asset_no = '';
	
	/** Array with the field values for this peripheral. The indexes will always
	* match the indexes with the array of field definitions from the class definition
	*/
	var $values = array ();
	
	/** Associative array showing the last update times for the peripheral fields which 
	* are automatically collected. The keys are field IDs and the values are timestamps.
	* The information is loaded from TBL_PERIPHERALS_ITEMS
	* @var arraray */
	var $fields_last_updated = array ();
	
	/** Array with the computer IDs to which this peripheral is linked 
	* @var array */
	var $computers = array ();
	
	/** Associative array with the fields which are SNMP collected, based on the settings from the peripheral class and profile (if any)
	* The keys are fields IDs and the values are associative array with the fields 'item_id' and 'item_field_id'
	* @var array */
	var $fields_items_ids = array ();
	
	/** The list of photos for this peripheral. Note that this is loaded only on request, with load_photos() method
	* @var array(CustomerPhoto) */
	var $photos = array ();
	
	/** The associated Location object, if any. Note that this is loaded only on request,
	* with load_location() method
	* @var Location */
	var $location = null;
	
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PERIPHERALS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'customer_id', 'class_id', 'name', 'date_created', 'location_id', 'profile_id', 'snmp_enabled', 'snmp_computer_id', 'snmp_ip', 'last_contact');
	
	
	/** Contructor. Loads an object's values if an ID is specified */
	function Peripheral ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
                        //$this->verify_access();
		}
	}
	
	
	/** Loads the peripheral data */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				// Load peripheral class definition 
				if ($this->class_id)
				{
					$this->class_def = new PeripheralClass ($this->class_id);
					
					// Load the field values - the simple ones
					$q = 'SELECT field_id, value, nrc FROM '.TBL_PERIPHERALS_FIELDS.' WHERE peripheral_id='.$this->id.' ORDER BY field_id, nrc';
					$values_list = db::db_fetch_list ($q);
					for ($i=0; $i<count($this->class_def->field_defs); $i++)
					{
						$this->values[$i] = $values_list[$this->class_def->field_defs[$i]->id];
					}
					
					// Load the list of computers - we're using the customer's computers list for speed
					$computers_list = Computer::get_computers_list (array('customer_id' => $this->customer_id));
					$q = 'SELECT computer_id FROM '.TBL_PERIPHERALS_COMPUTERS.' WHERE peripheral_id='.$this->id.' ';
					$peripheral_ids = db::db_fetch_vector ($q);
					
					foreach ($computers_list as $id => $name)
					{
						if (in_array ($id, $peripheral_ids)) $this->computers[] = $id;
					}
				}
				
				// Set the asset number
				$this->asset_no = get_asset_no_periph ($this->id);
				
				// Load the mappings between fields IDd and SNMP monitoring items IDd
				if ($this->snmp_enabled and $this->profile_id and $this->class_id)
				{
					$q = 'SELECT class_field_id, item_id, item_field_id FROM '.TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS.' ';
					$q.= 'WHERE class_id='.$this->class_id.' AND profile_id='.$this->profile_id;
					$data = db::db_fetch_array ($q);
					foreach ($data as $d) $this->fields_items_ids[$d->class_field_id] = array('item_id'=>$d->item_id, 'item_field_id'=>$d->item_field_id);
				}
				
				// If the peripheral is SNMP enabled, load the SNMP data too
				if ($this->snmp_enabled) $this->load_snmp_vals ();
			}
		}
	}
	
	/** If there are any SNMP fields, load the detailed SNMP values (including multi-fields) */
	function load_snmp_vals ()
	{
		$this->values_snmp = array ();
		if ($this->id and count($this->fields_items_ids))
		{
			foreach ($this->fields_items_ids as $field_id => $d)
			{
				$q = 'SELECT value, reported FROM '.TBL_PERIPHERALS_ITEMS.' WHERE obj_id='.$this->id.' AND obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' AND ';
				$q.= 'item_id='.$d['item_id'].' AND field_id='.$d['item_field_id'].' ORDER BY nrc';
				$data = db::db_fetch_array ($q);
				
				if ($d['item_field_id']) $item = new MonitorItem($d['item_field_id']);
				else $item = new MonitorItem($d['item_id']);
				
				foreach ($data as $d1)
				{
					$this->values_snmp[$field_id][] = $item->get_formatted_value ($d1->value);
					$this->fields_last_updated[$field_id] = $d1->reported;
				}
				/*
				$vals = db::db_fetch_vector ($q);
				
				if ($d['item_field_id']) $item = new MonitorItem($d['item_field_id']);
				else $item = new MonitorItem($d['item_id']);
				
				foreach ($vals as $val) $this->values_snmp[$field_id][] = $item->get_formatted_value ($val);
				*/
			}
		}
	}
	
	
	/** Loads the photos for this computer, if any */
	function load_photos ()
	{
		if ($this->id)
		{
			class_load ('CustomerPhoto');
			$this->photos = CustomerPhoto::get_photos (array('object_class'=>PHOTO_OBJECT_CLASS_PERIPHERAL, 'object_id'=>$this->id));
		}
	}
	
	
	/** Load the associated location, if any */
	function load_location ()
	{
		if ($this->location_id)
		{
			class_load ('Location');
			$this->location = new Location ($this->location_id);
			$this->location->load_parents ();
		}
	}
	
	/**
	* Loads the object data from an array, e.g. an array with fields from a form.
	* Optionally it also loads field values as well
	* @param	array	$data		The data to load into the object
	* @param	bool	$load_values	If TRUE and if the data array contains a field 'values', 
	*					then the function will load the array from that field into
	*					the $this->values array. Same for the list of computers.
	* @param	bool	$by_field_ids	If TRUE, the keys in $data['values'] represent field IDs;
	*					if FALSE, the keys represent indexes that match the indexes
	*					from $this->class_def->field_defs
	*/
	function load_from_array($data = array(), $load_values = false, $by_field_ids = true)
	{
		parent::load_from_array ($data);
		
		if ($load_values and is_array($data['values']))
		{
			if ($by_field_ids)
			{
				foreach ($data['values'] as $field_id => $val)
				{
					$idx = $this->class_def->field_ids_idx[$field_id];
					$this->values[$idx] = $val;
				}
			}
			else
			{
				foreach ($data['values'] as $idx => $val)
				{
					$this->values[$idx] = $val;
				}
			}
		}

		if ($load_values and is_array($data['computers']))
		{
			$this->computers = $data['computers'];
		}
	}
	
	
	/** Checks if the data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ($this->get_string('NEED_PERIPHERAL_NAME')); $ret = false;}
		if (!$this->class_id) {error_msg ($this->get_string('NEED_PERIPHERAL_CLASS')); $ret = false;}
		if (!$this->customer_id) {error_msg ($this->get_string('NEED_CUSTOMER')); $ret = false;}
		
		// If SNMP is enabled, make sure all required fields for this are set
		if ($this->snmp_enabled)
		{
			if (!$this->profile_id) {error_msg($this->get_string('NEED_PROFILE')); $ret = false;}
			if (!$this->snmp_computer_id) {error_msg($this->get_string('NEED_SNMP_COMPUTER')); $ret = false;}
			if (!$this->snmp_ip) {error_msg($this->get_string('NEED_SNMP_IP')); $ret = false;}
			elseif (!preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $this->snmp_ip))
			{
				error_msg ($this->get_string('NEED_VALID_IP')); $ret = false;
			}
			else
			{
				// Make sure this is the only peripheral for this customer with this IP
				$q = 'SELECT id FROM '.TBL_PERIPHERALS.' WHERE snmp_ip="'.db::db_escape($this->snmp_ip).'" AND customer_id='.$this->customer_id;
				if ($this->id) $q.= ' AND id<>'.$this->id.' ';
				$q.= 'LIMIT 1';
				if ($exist_id = db::db_fetch_field($q,'id'))
				{
					error_msg ($this->get_string('NEED_UNIQUE_SNMP_IP', $exist_id));
					$ret = false;
				}
			}
		}
		
		return $ret;
	}
	
	
	/** Saves the peripheral data, optionally saving its field values and computers list too */
	function save_data ($save_values = false)
	{
		if (!$this->id) $is_new = true;
		parent::save_data ();
		
		if ($is_new)
		{
			// Make sure it doesn't overlap over a RemovedComputer ID
			$exist_id = db::db_fetch_field ('SELECT id FROM '.TBL_REMOVED_PERIPHERALS.' WHERE id='.$this->id, 'id');
			if ($exist_id)
			{
				$max_id = db::db_fetch_field ('SELECT max(id) as id FROM '.TBL_PERIPHERALS, 'id');
				$max_id_removed = db::db_fetch_field ('SELECT max(id) as id FROM '.TBL_REMOVED_PERIPHERALS, 'id');
				$new_id = max ($max_id, $max_id_removed) + 1;
				
				db::db_query ('UPDATE '.TBL_PERIPHERALS.' SET id='.$new_id.' WHERE id='.$this->id);
				$this->id = $new_id;
			}
		}
		
		// Save the values
		if ($this->id and $save_values and count($this->class_def->field_defs)>0)
		{
			$q = 'REPLACE INTO '.TBL_PERIPHERALS_FIELDS.' (peripheral_id, field_id, value) VALUES ';
			for ($i=0; $i<count($this->class_def->field_defs); $i++)
			{
				$q.= '('.$this->id.', '.$this->class_def->field_defs[$i]->id.', ';
				$q.= '"'.db::db_escape ($this->values[$i]).'"), ';
			}
			$q = preg_replace ('/\,\s*$/', '', $q);
			db::db_query ($q);
		}
		
		// Save the computers
		if ($this->id and $save_values)
		{
			db::db_query ('DELETE FROM '.TBL_PERIPHERALS_COMPUTERS.' WHERE peripheral_id='.$this->id);
			if (count($this->computers) > 0)
			{
				$q = 'INSERT INTO '.TBL_PERIPHERALS_COMPUTERS.' (peripheral_id, computer_id) VALUES ';
				for ($i=0; $i<count($this->computers); $i++)
				{
					$q.= '('.$this->id.', '.$this->computers[$i].'), ';
				}
				$q = preg_replace ('/\,\s*$/', '', $q);
				db::db_query ($q);
			}
		}
		
	}
	
	/** Deletes a peripheral and its associated data */
	function delete ()
	{
		if ($this->id)
		{
			db::db_query ('DELETE FROM '.TBL_PERIPHERALS_FIELDS.' WHERE peripheral_id='.$this->id);
			db::db_query ('DELETE FROM '.TBL_PERIPHERALS_ITEMS.' WHERE obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' AND obj_id='.$this->id);
			db::db_query ('DELETE FROM '.TBL_PERIPHERALS_COMPUTERS.' WHERE peripheral_id='.$this->id);
			db::db_query ('UPDATE '.TBL_DISCOVERIES.' SET matched_obj_id=0 WHERE matched_obj_id='.$this->id.' AND matched_obj_class='.SNMP_OBJ_CLASS_PERIPHERAL);
			
			parent::delete ();
		}
	}
	
	/** Tells if the field with the specified ID corresponds to an SNMP collected item for this peripheral */
	function is_snmp_field ($field_id)
	{
		return isset($this->fields_items_ids[$field_id]);
	}
	
	/**
	* Returns a formatted value according to its specified type
	* @param	int	$idx		The index from $this->values for which the display value should be composed
	* @return	string			The formatted value
	*/
	function get_formatted_value ($idx)
	{
        $ret = FALSE;
		if (isset($this->values[$idx]))
		{
            $service_packages_list = array();
            $service_levels_list = array();
			if ($this->class_def->warranty_service_package_field or $this->class_def->warranty_service_level_field)
			{
				class_load ('Supplier');
				$service_packages_list = SupplierServicePackage::get_service_packages_list (array('prefix_supplier' => true));
				$service_levels_list = ServiceLevel::get_service_levels_list ();
			}
		
			switch ($this->class_def->field_defs[$idx]->type)
			{
				case MONITOR_TYPE_INT:
					if ($this->class_def->field_defs[$idx]->id == $this->class_def->warranty_service_package_field)
                        if(array_key_exists($this->values[$idx], $service_packages_list))
						    $ret = $service_packages_list[$this->values[$idx]];
					elseif ($this->class_def->field_defs[$idx]->id == $this->class_def->warranty_service_level_field)
                        if(array_key_exists($this->values[$idx], $service_levels_list))
						    $ret = $service_levels_list[$this->values[$idx]];
					else
						$ret = number_format (doubleval($this->values[$idx]), 0);
					break;
				case MONITOR_TYPE_FLOAT:
					$ret = number_format ($this->values[$idx], 2);
					break;
				case MONITOR_TYPE_MEMORY:
					$ret = get_memory_string ($this->values[$idx]);
					break;
				case MONITOR_TYPE_DATE:
					if (is_numeric($this->values[$idx]) and $this->values[$idx]>0)
						$ret = date (DATE_FORMAT_SHORT, $this->values[$idx]);
					break;
				
				default:					
					$ret = iconv(db::get_client_encoding(), "ISO-8859-1//IGNORE", $this->values[$idx]);
					//debug(db::get_client_encoding());
			}
		}
		return $ret;
	}
	
	
	/** For peripherals with Web access, returns a properly formatted URL */
	function get_access_url ()
	{
		$ret = '';

		if ($this->class_def->use_web_access)
		{
			$web_field_id = $this->class_def->web_access_field;
			$web_field_idx = $this->class_def->field_ids_idx[$web_field_id];
			$ret = $this->values[$web_field_idx];
			
			if ($ret and !preg_match('/^http[s]*\:\/\//i', $ret))
			{
				$ret = 'http://'.$ret;
			}
		}
		
		return $ret;
	}
	
	/** For peripherals with Web access, return the protocol (http or https) */
	function get_access_url_protocol ()
	{
		$ret = 'http';
		
		if (preg_match('/^https\:\/\//i', $this->get_access_url())) $ret = 'https';
		
		return $ret;
	}
	
	/** For peripherals with Web access, return the base URL (without IP part) */
	function get_access_url_base ()
	{
		$ret = $this->get_access_url();
		$ret = preg_replace ('/^(http(s*):\/\/)?([^\/]+)/i', '', $ret);
		return $ret;
	}
		
	
	/** For peripherals with network access, get the access IP */
	function get_net_access_ip ()
	{
		$ret = '';
		
		if ($this->class_def->use_net_access)
		{
			$ip_field_id = $this->class_def->net_access_ip_field;
			$ip_field_idx = $this->class_def->field_ids_idx[$ip_field_id];
			$ret = $this->values[$ip_field_idx];
		}
		
		return $ret;
	}

        function get_sn() {
            $ret = null;
            if($this->class_def->use_sn) {
                $sn_field_id = $this->class_def->sn_field;
                $sn_field_idx = $this->class_def->field_ids_idx[$sn_field_id];
                $ret = $this->values[$sn_field_idx];
            }
            return $ret;
        }

        function get_login() {
            $login_field_id = $this->class_def->net_access_login_field;
            $login_field_idx = $this->class_def->field_ids_idx[$login_field_id];
            return $this->values[$login_field_idx];
        }

        function get_password() {
            $password_field_id = $this->class_def->net_access_password_field;
            $password_field_idx = $this->class_def->field_ids_idx[$password_field_id];
            return $this->values[$password_field_idx];
        }
	
	
	/** For peripherals with network access, get the access port */
	function get_net_access_port ()
	{
		$ret = '';
		
		if ($this->class_def->use_net_access)
		{
			$port_field_id = $this->class_def->net_access_port_field;
			$port_field_idx = $this->class_def->field_ids_idx[$port_field_id];
			$ret = $this->values[$port_field_idx];
		}
		
		return $ret;
	}
	
	/** For SNMP-enabled peripherals, return the computer which does the SNMP monitoring */
	function get_snmp_computer ()
	{
		$ret = null;
		if ($this->id and $this->snmp_computer_id) $ret = new Computer ($this->snmp_computer_id);
		return $ret;
	}
	
	/** For SNMP-enabled peripherals, returns the assigned monitoring profile, if any */
	function get_monitoring_profile ()
	{
		$ret = null;
		if ($this->id and $this->profile_id) $ret = new MonitorProfilePeriph ($this->profile_id);
		return $ret;
	}
	
	/**
	* Returns a list of months for which monitor item logs are available
	* @param	int		$field_id	(Optional) Restrict the list only to months on which
	*						this field ID has been logged (do not confuse with an item ID)
	* @return	array				Array of strings with the month for which logs
	*						are available, in the format "YYYY_DD"
	*/
	function get_log_months ($field_id = null)
	{
		$ret = array ();
		
		if ($this->id)
		{
			// Get the whole list of available log months
			$q = 'SHOW TABLES like "'.TBL_PERIPHERALS_ITEMS_LOG.'_%" ';
			$months = db::db_fetch_vector ($q);
			
			if ($field_id)
			{
				if ($this->fields_items_ids[$field_id]) list($item_id, $item_field_id) = $this->fields_items_ids[$field_id];
				else return $ret; // If there are no mappings on this field ID there is no point in further checks
			}
			
			// Check which months actually have logs for this computer
			if (count($months) > 0)
			{
				arsort($months);
				
				foreach ($months as $month)
				{
					$q = 'SELECT obj_id FROM '.$month.' WHERE obj_id='.$this->id.' AND obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' ';
					if ($item_id) $q.= 'AND item_id='.$item_id.' AND field_id='.$field_id;
					$q.= 'LIMIT 1';
					
					if ($this->id == db::db_fetch_field ($q, 'obj_id'))
					{
						$ret[] = ereg_replace (TBL_PERIPHERALS_ITEMS_LOG.'_', '', $month);
					}
				}
			}
		}
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns a list of peripherals according to the specified criteria
	* @param	array	$filter			Associative array with filtering criteria. Fields can be:
	*						- customer_id: Return peripherals only for this customer ID
	*						- class_id: Return only peripherals of this class
	*						- location_id: Return only peripherals in this customer location
	*						- profile_id: Return only peripherals with the specified monitoring profile
	*						- append_id : if True, the ID of the computers will be appended to names
	*						- order_by: If 'customer', the peripherals will be ordered by customer name first
	* @return	array				Associative array, with the keys being peripheral IDs and the 
	*						values being peripheral names.
	*/
	public static function get_peripherals_list($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT p.id, p.name FROM '.TBL_PERIPHERALS.' p INNER JOIN ';
		$q.= TBL_PERIPHERALS_CLASSES.' pc ON p.class_id=pc.id ';
		if ($filter['order_by']=='customer') $q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON p.customer_id=cust.id ';
		$q.= 'WHERE ';
		
		if ($filter['customer_id']) $q.= 'p.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['class_id']) $q.= 'p.class_id='.$filter['class_id'].' AND ';
		if ($filter['location_id']) $q.= 'p.location_id='.$filter['location_id'].' AND ';
		if ($filter['profile_id']) $q.= 'p.profile_id='.$filter['profile_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		if ($filter['order_by'] == 'customer') $q.= 'ORDER BY cust.name, pc.position, p.name ';
		else $q.= 'ORDER BY pc.position, p.name ';
		
		$ret = db::db_fetch_list ($q);
		
		if ($filter['append_id'])
		{
			foreach ($ret as $id => $name) $ret[$id] = $name.' ('.$id.')';
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns peripherals according to the specified criteria
	* @param	array	$filter			Associative array with filtering criteria. Fields can be:
	*						- customer_id: Return peripherals only for the specified customer ID
	*						- class_id: Return only peripherals of the specified class.
	*						- computer_id: Return peripherals for a specific computer
	*						- no_group: if TRUE, the peripherals will not be returned grouped
	*						  by classes.
	* @return	array				Associative array. If 'no_group' is not specified in $filter, then the
	*						array keys will be peripheral class IDs and the values will be array
	*						with the found Peripheral objects. If 'no_group' is set to TRUE, then
	*						the result will be a simple array with all the peripheral objects matched.
	*/
	public static function get_peripherals($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT DISTINCT p.id ';
		if ($filter['order_by'] == 'asset_no') $q.= ', concat("'.ASSET_PREFIX_PERIPHERAL.'",lpad(id,'.ASSET_NUM_LENGTH.',"0")) as asset_no ';
		$q.= 'FROM '.TBL_PERIPHERALS.' p ';
		if ($filter['computer_id']) $q.= 'INNER JOIN '.TBL_PERIPHERALS_COMPUTERS.' pc on p.id=pc.peripheral_id ';
		$q.= 'WHERE ';
		if ($filter['customer_id']) $q.= 'p.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['class_id'] and $filter['no_group']) $q.= 'p.class_id='.$filter['class_id'].' AND ';
		if ($filter['computer_id']) $q.= 'pc.computer_id='.$filter['computer_id'].' AND ';
		
		if ($filter['no_group'])
		{
			$q = preg_replace ('/AND\s*$/', ' ', $q);
			$q = preg_replace ('/WHERE\s*$/', ' ', $q);
			$q.= ' ORDER BY '.($filter['order_by'] == 'asset_no' ? 'asset_no' : 'p.name').' ';
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new Peripheral ($id);
		}
		else
		{
			// Fetch the list of peripherals classes
			$q_classes = 'SELECT DISTINCT c.id FROM '.TBL_PERIPHERALS_CLASSES.' c ';
			$q_classes.= 'INNER JOIN '.TBL_PERIPHERALS.' p ON c.id=p.class_id ';
			if ($filter['customer_id']) $q_classes.= 'WHERE p.customer_id='.$filter['customer_id'].' ';
			$q_classes.= 'ORDER BY c.position ';

			$classes_list = db::db_fetch_vector ($q_classes);
			foreach ($classes_list as $class_id)
			{
				$ids = db::db_fetch_vector ($q.' p.class_id='.$class_id.' ORDER BY '.($filter['order_by'] == 'asset_no' ? 'asset_no' : 'p.name'));
				foreach ($ids as $id) $ret[$class_id][] = new Peripheral ($id);
			}
		}
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns a list of customer IDs associated with peripherals
	* @param	array	$filter		Associative array with filtering criteria. Fields can be:
	*					- class_id : returns peripherals of specified class
	*					- profile_id : returns peripherals of specified monitoring profile
	* @return	array			Associative array, the keys are peripherals IDs and the values are their
	*					customer IDs.
	*/
	public static function get_peripherals_customer_ids ($filter = array ())
	{
		$ret = array ();
		$q = 'SELECT id, customer_id FROM '.TBL_PERIPHERALS.' WHERE ';
		
		if ($filter['profile_id']) $q.= 'profile_id='.$filter['profile_id'].' AND ';
		if ($filter['class_id']) $q.= 'class_id='.$filter['class_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);
		
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	/**
	 * [Class method]
	 *
	 * @param string $serial
	 * @param string $type 		can have values 1. sn -- search for serial numbers
	 * 											2. pn -- search for product number
	 * @return array
	 */
	public static function get_serials_numbers($serial="", $type="sn")
	{
		$data = array();
		
		//get the id's for serial number fields
		if($type != "sn" and $type != "pn") $type="sn";
		if($type == "sn")
			$query = " (select id from ".TBL_PERIPHERALS_CLASSES_FIELDS." where name like 'serial number') ";
		if($type == "pn")
			$query = " (select id from ".TBL_PERIPHERALS_CLASSES_FIELDS." where name like 'product number') ";
		$query = "select cust.name, cust.id as cid, p.id, p.name as pname, pi.value from ".TBL_PERIPHERALS_FIELDS." pi inner join ".TBL_PERIPHERALS." p on pi.peripheral_id=p.id inner join ".TBL_CUSTOMERS." cust on p.customer_id=cust.id where pi.field_id in ".$query;
		$query .= " AND value like '".$serial."%'";
		$data = db::db_fetch_array($query);
		return $data;
	}
	
	/** [Class Method] Returns the warranties of all peripherals for a specified customer, but only peripherals
	* for which the classes specify that warranties info are stored.
	* @param	array				$filter		Associative array with filtering criteria. Fields can be:
	* 								- customer_id: (Required) The customer for which to return warranties
	*								- order_by: 'asset_no' to sort the results by asset_number
	* @return	array(Warranty)					Array with the matched Warranty objects, sorted by
	*								class name and peripheral name.
	*/
	public static function get_warranties($filter = array ())
	{
		class_load ('Warranty');
		$ret = array ();
		$customer_id = $filter['customer_id'];
		
		if ($customer_id)
		{
			// Fetch the ID of peripheral classes which are using warranties.
			$q = 'SELECT id FROM '.TBL_PERIPHERALS_CLASSES.' WHERE use_warranty=1';
			$classes_warranties = DB::db_fetch_vector ($q);
			
			$q = 'SELECT p.id, p.class_id ';
			if ($filter['order_by'] == 'asset_no')
			{
				$q.= ', concat("'.ASSET_PREFIX_PERIPHERAL.'",lpad(p.id,'.ASSET_NUM_LENGTH.',"0")) as asset_no ';
			}
			$q.= 'FROM '.TBL_PERIPHERALS.' p INNER JOIN '.TBL_PERIPHERALS_CLASSES.' pc ';
			$q.= 'ON p.class_id=pc.id WHERE p.customer_id='.$customer_id.' AND pc.use_warranty=1 ';
			if ($filter['order_by'] == 'asset_no') $q.= 'ORDER BY pc.name, asset_no ';
			else $q.= 'ORDER BY pc.name, p.name ';
			$data = DB::db_fetch_array ($q);
			
			foreach ($data as $d) $ret[] = new Warranty (WAR_OBJ_PERIPHERAL, $d->id, $d->class_id);
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns all notifications related to peripherals and AD Printers, also loading the respective
	* object details into the notifications
	* @return	array					The found notifications, sorted by date
	*/
	public static function get_peripherals_notifications ()
	{
		class_load ('Notification');
		$ret = array ();
		
		$notifs_periphs = Notification::get_notifications (array('object_class' => NOTIF_OBJ_CLASS_PERIPHERAL));
		for ($i=0; $i<count($notifs_periphs); $i++) $notifs_periphs[$i]->load_linked_object ();
		$notifs_ad_printers = Notification::get_notifications (array('object_class' => NOTIF_OBJ_CLASS_AD_PRINTER));
		for ($i=0; $i<count($notifs_ad_printers); $i++) $notifs_ad_printers[$i]->load_linked_object ();
		
		$ret = array_merge ($notifs_periphs, $notifs_ad_printers);
		usort($ret, array('Notification', 'cmp_notifs_raised'));
		
		return $ret;
	}
	
	/** [Class Method] Checks if there are any alerts that need to be raised for any of the Peripherals AND AD Printers. 
	* If any alert conditions are found to be true, this will automatically raise the necessary notifications.
	*/
	public static function check_monitor_alerts ($no_increment = true)
	{
		class_load ('Alert');
		class_load ('Notification');
		
		$customer_ok = true;
		$alerts = Alert::get_alerts (array('peripherals_only'=>true));
		
		// Get the list of away users, if any
		$away_users_ids = User::get_away_ids ();
		
		$today_day_code = pow(2, date('w'));
		
		// Loop through the alerts list and check each alert and the relevant peripherals/AD printers
		// to see if any of them the alert conditions.
		for ($i=0; $i<count ($alerts); $i++)
		{
			$alert_id = $alerts[$i]->id;
			// Check if this day should be ignored or not
			if (($alerts[$i]->ignore_days & $today_day_code)!=$today_day_code)
			{
				$q = '';
				$q_fields = array();		// The array of fields which will be involved in the current query
				$found_alerts = array();	// The matched alerts

				// Loop through each alert condition to compose the query which
				// will be used in fetching from the database the list of computers meeting the alert conditions
				for ($j=0; $j<count ($alerts[$i]->conditions); $j++)
				{
					$cond = $alerts[$i]->conditions[$j];
					
					$v_type = 1;
					switch ($cond->value_type)
					{
						case CRIT_VAL_TYPE_MEM_KB: $v_type = 1024; break; //'*1024 '; break;
						case CRIT_VAL_TYPE_MEM_MB: $v_type = (1024*1024); break; //'*1024*1024 '; break;
						case CRIT_VAL_TYPE_MEM_GB: $v_type = (1024*1024*1024); break; //'*1024*1024*1024 '; break;
						case CRIT_VAL_TYPE_MEM_TB: $v_type = (1024*1024*1024*1024); break; //'*1024*1024*1024*1024 '; break;
					}

					$prefix = '';
					$cond->value = preg_replace ('/\\\\/','\\\\\\\\', $cond->value);	// Get rid of unwanted backslashes
					
					// Compose the condition to be used in the query based on the type of alert condition
					switch ($cond->criteria)
					{
						case CRIT_DATE_OLDER_THAN:
							$q_fields[$cond->field_id][] = ')<='.(time() - $cond->value*60*60*24);
							break;
						case CRIT_DATE_EXPIRES:
							$q_fields[$cond->field_id][] = ')<='.(time() + $cond->value*60*60*24);
							break;
						case CRIT_STRING_MATCHES:
							$q_fields[$cond->field_id][] = ')= "'.db::db_escape($cond->value).'" ';
							break;
						case CRIT_STRING_STARTS:
							$q_fields[$cond->field_id][] = ')like "'.db::db_escape($cond->value).'%" ';
							break;
						case CRIT_STRING_ENDS:
							$q_fields[$cond->field_id][] = ')like "%'.db::db_escape($cond->value).'" ';
							break;
						case CRIT_STRING_CONTAINS:
							$q_fields[$cond->field_id][] = ')like "%'.db::db_escape($cond->value).'%" ';
							break;
						case CRIT_STRING_EMPTY:
							$q_fields[$cond->field_id][] = ')="" ';
							break;
						case CRIT_STRING_NOT_EMPTY:
							$q_fields[$cond->field_id][] = ')<>"" ';
							break;
						case CRIT_STRING_NOT_CONTAINS:
							$q_fields[$cond->field_id][] = ')not like "%'.db::db_escape($cond->value).'%" ';
							break;
						case CRIT_NUMBER_EQUALS:
							$q_fields[$cond->field_id][] = '+0.0) = ('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_DIFFERENT:
							$q_fields[$cond->field_id][] = '+0.0) <> ('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_HIGHER:
							$q_fields[$cond->field_id][] = '+0.0) > ('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_HIGHER_EQUAL: 
							$q_fields[$cond->field_id][] = '+0.0) >=('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_SMALLER:
							$q_fields[$cond->field_id][] = '+0.0) <('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_SMALLER_EQUAL:
							$q_fields[$cond->field_id][] = '+0.0) <=('.($cond->value * $v_type).') ';
							break;
							
						case CRIT_LIST_EQUALS:
							$vals_list = '(';
							foreach ($cond->list_values as $list_val) $vals_list.='"'.$list_val.'",';
							$vals_list = preg_replace ('/,$/','', $vals_list).')';
							$q_fields[$cond->field_id][] = ') in '.$vals_list.' ';
							break;
							
						case CRIT_LIST_DIFFERS:
							$vals_list = '(';
							foreach ($cond->list_values as $list_val) $vals_list.='"'.$list_val.'",';
							$vals_list = preg_replace ('/,$/','', $vals_list).')';
							$q_fields[$cond->field_id][] = ') not in '.$vals_list.' ';
							break;
					}
				}
				
				if (!empty($q_fields))
				{
					// Build the query for finding the peripherals meeting the conditions for the current alert
					$q_p = 'SELECT p.id, p.snmp_computer_id, i.nrc, i.obj_class, count(*) as cnt FROM '.TBL_PERIPHERALS_ITEMS.' i ';	// For peripherals
					$q_a = 'SELECT a.id, a.snmp_computer_id, i.nrc, i.obj_class, count(*) as cnt FROM '.TBL_PERIPHERALS_ITEMS.' i ';	// For AD Printers
					
					$q_p.= 'INNER JOIN '.TBL_PERIPHERALS.' p ON i.obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' AND i.obj_id=p.id ';
					$q_a.= 'INNER JOIN '.TBL_AD_PRINTERS_EXTRAS.' a ON i.obj_class='.SNMP_OBJ_CLASS_AD_PRINTER.' AND i.obj_id=a.id ';
					$q_a.= 'INNER JOIN '.TBL_AD_PRINTERS_WARRANTIES.' aw ON a.canonical_name=aw.canonical_name ';
					
					// Make sure to include only relevant computers
					$q_p.= 'INNER JOIN '.TBL_PROFILES_PERIPH_ALERTS.' pa ON p.profile_id=pa.profile_id AND pa.alert_id='.$alert_id.' ';
					$q_a.= 'INNER JOIN '.TBL_PROFILES_PERIPH_ALERTS.' pa ON a.profile_id=pa.profile_id AND pa.alert_id='.$alert_id.' ';
					
					// Work only with active customers
					$q_p.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON p.customer_id=cust.id AND (cust.active=1 AND cust.has_kawacs=1 AND cust.onhold=0) ';
					$q_a.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON aw.customer_id=cust.id AND (cust.active=1 AND cust.has_kawacs=1 AND cust.onhold=0) ';
					
					$q = 'WHERE item_id='.$alerts[$i]->item_id.' AND (';
					
					// Determine how the conditions should be joined ('AND' or 'OR')
					$join_cond = ($alerts[$i]->join_type == JOIN_CONDITION_OR ? 'OR' : 'AND');
					
					// Add to query the field conditions determined previously
					foreach ($q_fields as $field => $conds)
					{
						$q.= '(';
						if ($cond->fielddef->id == $alerts[$i]->item_id)
						{
							// This is not a struct item, don't impose condition on field_id
							$q.= 'field_id=0 ';
						}
						else
						{
							// This is part of a struct item
							$q.= 'field_id='.$field.' ';
						}
						
						$q.= 'AND (';
						foreach ($conds as $c) $q.= '(value '.$c.' '.$join_cond.' ';
						$q = preg_replace ('/'.$join_cond.' $/', '', $q).')) OR '; 
					}
					$q = preg_replace ('/OR\s*$/', '', $q).') GROUP BY i.obj_id, i.obj_class, item_id, nrc ';
					
					// Since the item values are stored in separate records, an alert condition is being met only
					// if the returned number of rows matches the number of items involved in this alert
					$q.= 'HAVING cnt= '.count ($q_fields).' ORDER BY i.obj_class, i.obj_id, nrc, field_id ';
					
					// Check if called as class or object method
					$found_alerts_p = db::db_fetch_array ($q_p . $q);
					$found_alerts_a = db::db_fetch_array ($q_a . $q);
					$found_alerts = $found_alerts_p + $found_alerts_a;
				}
				
				// Get the list of IDs for already existing alerts of this type (if any)
				$filter_p = array ('object_class' => NOTIF_OBJ_CLASS_PERIPHERAL, 'object_event_code' => $alert_id);
				$existing_notifs_p = Notification::get_notifications_list ($filter_p); 
				$filter_a = array ('object_class' => NOTIF_OBJ_CLASS_AD_PRINTER, 'object_event_code' => $alert_id);
				$existing_notifs_a = Notification::get_notifications_list ($filter_a); 
				$existing_notifs = $existing_notifs_p + $existing_notifs_a;
				
				// Raise the found alerts, if any have been found
				// $raised_alerts will store the notifications that have been raised, as an associative array with the
				// raised notifications grouped by computer ID, alert ID and notification recipient ID.
				// Storing the notification recipient ID helps with removing the existing notifications for recipients
				// which are not assigned anymore to recive those specific notifications.
				$raised_alerts = array ();
				
				if (!empty ($found_alerts))
				{
					// Keep track of devices which have been processed for this alert
					// This is needed because the query above might return many hits for the same device
					$notified_devs = array (); //$notified_comps = array ();
					$notified_devs[SNMP_OBJ_CLASS_PERIPHERAL] = array ();
					$notified_devs[SNMP_OBJ_CLASS_AD_PRINTER] = array ();
					
					for ($j=0; $j<count ($found_alerts); $j++)
					{
						$obj_class = $found_alerts[$j]->obj_class;
						$obj_id = $found_alerts[$j]->id;
						$comp_id = $found_alerts[$j]->snmp_computer_id;
						
						// Raise (or re-raise) notifications only for alerts which haven't been already raised in this cycle
						if (
							!$raised_alerts[$obj_class][$obj_id][$alert_id] and
							!in_array ($obj_id, $notified_devs[$obj_class])
						)
						{
							$recips_ks = array ();
							$recips_cust = array ();
							
							// Mark that the computer ID has been processed for this alert
							$notified_devs[$obj_class][] = $obj_id;
							
							// Fetch the Keysource users which need to receive this notification, if any.
							// Also append to the recipients any recipients specifically designated for this alert type.
							if (($alerts[$i]->send_to & ALERT_SEND_KEYSOURCE) == ALERT_SEND_KEYSOURCE)
							{
								$recips_ks = Computer::get_notification_recipients ($comp_id, ALERT_SEND_KEYSOURCE);
								$recips_ks = array_merge($recips_ks, $alerts[$i]->recipients_ids);
								
								// Check if any user is away and add the alternate recipient to the list
								foreach ($recips_ks as $ck_id) 
								{
									if (isset($away_users_ids[$ck_id])) $recips_ks[] = $away_users_ids[$ck_id];
								}
								
							}
							// Fetch the customer recipient which need to receive this notification, if any
							if (($alerts[$i]->send_to & ALERT_SEND_CUSTOMER) == ALERT_SEND_CUSTOMER)
							{
								// Fetch the  list of customer notifications recipients for this computer
								$recips_cust = Computer::get_notification_recipients ($comp_id, ALERT_SEND_CUSTOMER);
							}
							
							// Add extra parts to the subject if the alert definition requires it
							// XXX: To be implemented in the future
							$extra_subject = '';
							/*
							if (count($alerts[$i]->send_fields) > 0)
							{
								$item = new ComputerItem ($obj_id, $alerts[$i]->item_id, false);
								foreach ($alerts[$i]->send_fields as $send_field_id)
								{
									$extra_subject.= $item->get_formatted_value ($found_alerts[$j]->nrc, $send_field_id).', ';
								}
								$extra_subject = ' ('.preg_replace('/, $/', '', $extra_subject).')';
							}*/
							
							// Create the notification and keep track of IDs
							$notif_obj_class = ($obj_class==SNMP_OBJ_CLASS_PERIPHERAL ? NOTIF_OBJ_CLASS_PERIPHERAL : NOTIF_OBJ_CLASS_AD_PRINTER);
							$notification_id = Notification::raise_notification_array (array(
									'event_code' => $alerts[$i]->event_code,
									'level' => $alerts[$i]->level,
									'object_class' => $notif_obj_class,
									'object_id' => $obj_id,
									'object_event_code' => $alert_id,
									'item_id' => $alerts[$i]->item_id,
									'user_ids' => array_merge($recips_ks, $recips_cust),
									'text' => $alerts[$i]->name . $extra_subject,
									'no_increment' => false,
									'template' => ''
							));
							$raised_alerts[] = $notification_id;
							
							// For customer users, if any, set the dedicated texts
							if (count($recips_cust)>0)
							{
								$notification = new Notification ($notification_id);
								$text = ($alerts[$i]->subject ? $alerts[$i]->subject : $alerts[$i]->name);
								
								foreach ($recips_cust as $cust_usr_id)
								{
									$notification->set_notification_recipient_text ($cust_usr_id, $text, true, '_classes_templates/notification/msg_customer_alert.tpl');
								}
							}
						}
					}
				}
				
				// Now delete older notifications which haven't been raised again
				foreach ($existing_notifs as $old_notif_id => $obj_id)
				{
					// Check that the notification ID is not in the list of notification which have been raised,
					if (!in_array($old_notif_id,$raised_alerts))
					{
						$old_notif = new Notification ($old_notif_id);
						$old_notif->delete ();
					}
				}
			}
		}
	}
	
	
	/**
	* [Class Method] Updates the monthly item logs and cleans up peripherals_items_log
	*
	* This function should be run at regular intervals from cron. It will loop over the months,
	* starting with the earliest date found in the log, and will copy data from the  
	* peripherals_items_log table to peripherals_items_YYYY_MM table.
	*
	* Also, all items older than 1 week are deleted from peripherals_items_log, to keep
	* the table small.
	*/
	public static function update_monthly_logs ($quick = false)
	{
		$time_current = time ();
		
		if ($quick)
		{
			$time_barrier = strtotime ('2 hours ago');
			$time_min = $time_current - (2 * 3600);
		}
		else
		{
			$time_barrier = strtotime ('1 week ago');

			// Check what's the earliest time in the items log
			$q = 'SELECT min(reported) as time_min FROM '.TBL_PERIPHERALS_ITEMS_LOG.' ';
			$q.= 'WHERE reported<'.$time_barrier;
			$time_min = db::db_fetch_field ($q, 'time_min');
		}

		if ($time_min != 0 and $time_min < $time_current)
		{
			$month_current = intval(date ('m', $time_current));
			$year_current = intval(date ('Y', $time_current));
			
			if ($quick)
			{
				$month_min = $month_current;
				$year_min = $year_current;
			}
			else
			{
				$month_min = intval(date ('m', $time_min));
				$year_min = intval(date ('Y', $time_min));
			}
			
			$month = $month_min; $year = $year_min;
			while ($year < $year_current or ($year == $year_current and $month <= $month_current))
			{
				if ($quick)
				{
					$t_start = $time_min;
					$year_next = $year + ($month==12 ? 1 : 0);
					$month_next = ($month % 12) + 1;
					$t_end = $time_current;
				}
				else
				{
					$t_start = mktime (0,0,0,$month,1,$year);
					$year_next = $year + ($month==12 ? 1 : 0);
					$month_next = ($month % 12) + 1;
					$t_end = mktime (0,0,0,$month_next,1,$year_next);
				}
				
				$tbl_name = TBL_PERIPHERALS_ITEMS_LOG.'_'.$year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT);
				
				$table_exist = db::db_fetch_row ('SHOW TABLES LIKE "'.$tbl_name.'"');
				if (!$table_exist)
				{
					$struct = db::db_fetch_field ('SHOW CREATE TABLE '.TBL_PERIPHERALS_ITEMS_LOG, 'Create Table');
					$struct = ereg_replace (TBL_PERIPHERALS_ITEMS_LOG, $tbl_name, $struct);
					db::db_query ($struct);
				}
			
				// Because we are using INSERT IGNORE we don't have to worry about duplicate records,
				// they are silently discarded. And because the primary key is composed only of numeric
				// fields, identifying the duplicates doesn't consume too much extra time
				$q = 'INSERT IGNORE INTO '.$tbl_name.' SELECT * FROM '.TBL_PERIPHERALS_ITEMS_LOG.' ';
				$q.= 'WHERE reported>='.$t_start.' AND reported<'.$t_end;
			
				db::db_query ($q);
		
				$month = $month_next; $year = $year_next;
			}
			
			if (!$quick)
			{
				// Finally, delete all items older than 1 week
				$q = 'DELETE FROM '.TBL_PERIPHERALS_ITEMS_LOG.' WHERE reported<'.$time_barrier;
				db::db_query ($q);
			}
		}
	}


    public static function search_by_condition($condition = array())
	{
		$ret = array();
		$query = "";
		//debug($condition);
		if($condition['customer_id'] && $condition['customer_id'] != COMPUTERS_FILTER_ALL)
			$customer_id = $condition['customer_id'];
		if($condition['search'] && $condition['search'] != '') 
		{
			$search = $condition['search'];
			$b_advanced = false;
			if($condition['current_peripheral_class'] && is_array($condition['current_peripheral_class']) &&  count($condition['current_peripheral_class']) > 0) 
			{
				$look_in = $condition['current_peripheral_class']; 
				$b_advanced = true;
			}
			
			
			if(!isset($customer_id))
				$query = "select distinct pf.peripheral_id from ".TBL_PERIPHERALS_FIELDS." pf inner join ".TBL_PERIPHERALS." p on p.id=pf.peripheral_id where (value like '".$search."%' or p.name like '".$search."%') ";
			else 
				$query = "select distinct pf.peripheral_id from ".TBL_PERIPHERALS_FIELDS." pf inner join ".TBL_PERIPHERALS." p on p.id=pf.peripheral_id where p.customer_id=".$customer_id." and (pf.value like '".$search."%' or p.name like '".$search."%') ";
				
			if($b_advanced)
			{
				$qq = "select distinct id from ".TBL_PERIPHERALS_CLASSES_FIELDS." where class_id in ";
				$q_add = "(";
				foreach ($look_in as $citem)
				{
					
					$q_add .= $citem.",";
				}
				$q_add = substr($q_add, 0, strlen($q_add)-1);
				$q_add .= ")";
				$qq .= $q_add;
				//$qq .= " and name in ('Brand', 'Comment', 'Commemnts', 'Model', 'Media Information', 'Product Number') or name like 'Outlet%' ";
				$query .= " and pf.field_id in ( ".$qq.")";
			}
		}
		
		//extract an array with all this computers
		if($query!="")
			$ret = db::db_fetch_vector($query);

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
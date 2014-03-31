<?php

class_load ('PeripheralClass');
class_load ('Customer');
class_load ('Peripheral');

/**
* Stores data about removed peripherals.
*
*/
class RemovedPeripheral extends Peripheral
{
	/** The date when the peripheral was removed
	* @var timestamp */
	var $date_removed = 0;
	
	/** The removal reason
	* @var text */
	var $reason_removed = '';
	
	/** The ID of the user who done the removal
	* @var int */
	var $removed_by = 0;
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_REMOVED_PERIPHERALS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'customer_id', 'date_created', 'date_removed', 'reason_removed', 'removed_by', 'class_id', 'name', 'location_id', 'profile_id', 'snmp_enabled', 'snmp_computer_id', 'snmp_ip', 'last_contact');
	
	
	/** Contructor. Loads an object's values if an ID is specified */
	function RemovedPeripheral ($id = null)
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
			Base::load_data ();
			if ($this->id)
			{
				// Load peripheral class definition 
				if ($this->class_id)
				{
					$this->class_def = new PeripheralClass ($this->class_id);
					
					// Load the field values - the simple ones
					$q = 'SELECT field_id, value, nrc FROM '.TBL_REMOVED_PERIPHERALS_FIELDS.' WHERE peripheral_id='.$this->id.' ORDER BY field_id, nrc';
					$values_list = $this->db_fetch_list ($q);
					for ($i=0; $i<count($this->class_def->field_defs); $i++)
					{
						$this->values[$i] = $values_list[$this->class_def->field_defs[$i]->id];
					}
				}
				
				// Set the asset number
				$this->asset_no = get_asset_no_periph ($this->id);
				
				// Load the mappings between fields IDd and SNMP monitoring items IDd
				if ($this->snmp_enabled and $this->profile_id and $this->class_id)
				{
					$q = 'SELECT class_field_id, item_id, item_field_id FROM '.TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS.' ';
					$q.= 'WHERE class_id='.$this->class_id.' AND profile_id='.$this->profile_id;
					$data = $this->db_fetch_array ($q);
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
				$q = 'SELECT value, reported FROM '.TBL_REMOVED_PERIPHERALS_ITEMS.' WHERE obj_id='.$this->id.' AND obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' AND ';
				$q.= 'item_id='.$d['item_id'].' AND field_id='.$d['item_field_id'].' ORDER BY nrc';
				$data = $this->db_fetch_array ($q);
				
				if ($d['item_field_id']) $item = new MonitorItem($d['item_field_id']);
				else $item = new MonitorItem($d['item_id']);
				
				foreach ($data as $d1)
				{
					$this->values_snmp[$field_id][] = $item->get_formatted_value ($d1->value);
					$this->fields_last_updated[$field_id] = $d1->reported;
				}
			}
		}
	}
	
	
	/** Not used for this class */
	function load_photos () {return false;}
	
	
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
	function load_from_array ($data = array(), $load_values = false, $by_field_ids = true)
	{
		Base::load_from_array ($data);
		
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
	}
	
	
	/** Checks if the data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->date_removed) {error_msg ($this->get_string('NEED_DATE_REMOVED')); $ret = false;}
		if (!$this->reason_removed) {error_msg ($this->get_string('NEED_REASON_REMOVED')); $ret = false;}
		
		return $ret;
	}
	
	
	/** Saves the peripheral data, optionally saving its field values and computers list too */
	function save_data ($save_values = false)
	{
		Base::save_data ();
		
		// Save the values
		if ($this->id and $save_values and count($this->class_def->field_defs)>0)
		{
			$q = 'REPLACE INTO '.TBL_REMOVED_PERIPHERALS_FIELDS.' (peripheral_id, field_id, value) VALUES ';
			for ($i=0; $i<count($this->class_def->field_defs); $i++)
			{
				$q.= '('.$this->id.', '.$this->class_def->field_defs[$i]->id.', ';
				$q.= '"'.mysql_escape_string ($this->values[$i]).'"), ';
			}
			$q = preg_replace ('/\,\s*$/', '', $q);
			$this->db_query ($q);
		}
	}
	
	/** Deletes a peripheral and its associated data */
	function delete ()
	{
		if ($this->id)
		{
			$this->db_query ('DELETE FROM '.TBL_REMOVED_PERIPHERALS_FIELDS.' WHERE peripheral_id='.$this->id);
			$this->db_query ('DELETE FROM '.TBL_REMOVED_PERIPHERALS_ITEMS.' WHERE obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' AND obj_id='.$this->id);
			Base::delete ();
		}
	}
	
	
	/** [Class Method] Removes a peripheral, by creating a new RemovedPeripheral, copying all relevant data from
	* the associated Peripheral object and then deleting the Peripheral object.
	* @param	Peripheral		$peripheral		The peripheral to remove
	* @param	int			$user_id		The ID of the user who made the operation
	* @param	text			$reason			The reason given for the deletion
	* @param	timestamp		$date_removed		The date at which to mark that the computer has been removed.
	*								If not specified, the current time will be used
	* @return	RemovedPeripheral				The newly created RemovedPeripheral
	*/
	function remove_peripheral ($peripheral, $user_id, $reason, $date_removed = 0)
	{
		$ret = null;
		
		if ($peripheral->id)
		{
			// Copy the main computer data
			$ret = new RemovedPeripheral ();
			foreach ($ret->fields as $field) if (isset($peripheral->$field)) $ret->$field = $peripheral->$field;
			$ret->date_removed = ($date_removed ? $date_removed : time ());
			$ret->reason_removed = $reason;
			$ret->removed_by = $user_id;
			$ret->save_data ();
			
			// Copy the peripheral field values and SNMP collected data
			DB::db_query ('DELETE FROM '.TBL_REMOVED_PERIPHERALS_FIELDS.' WHERE peripheral_id='.$peripheral->id); // Just in case
			DB::db_query ('INSERT INTO '.TBL_REMOVED_PERIPHERALS_FIELDS.' SELECT * FROM '.TBL_PERIPHERALS_FIELDS.' WHERE peripheral_id='.$peripheral->id);
			DB::db_query ('DELETE FROM '.TBL_REMOVED_PERIPHERALS_ITEMS.' WHERE obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' AND obj_id='.$peripheral->id);
			DB::db_query ('INSERT INTO '.TBL_REMOVED_PERIPHERALS_ITEMS.' SELECT * FROM '.TBL_PERIPHERALS_ITEMS.' WHERE obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' AND obj_id='.$peripheral->id);
			
			// Update the references in tickets
			$q = 'UPDATE '.TBL_TICKETS_OBJECTS.' SET object_class='.TICKET_OBJ_CLASS_REMOVED_PERIPHERAL.' ';
			$q.= 'WHERE object_class='.TICKET_OBJ_CLASS_PERIPHERAL.' AND object_id='.$peripheral->id;
			DB::db_query ($q);
			
			// Delete the Peripheral object
			$peripheral->delete ();
		}
		
		return $ret;
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
	function get_formatted_value ($idx) {return parent::get_formatted_value ($idx);}
	
	
	/** For peripherals with Web access, returns a properly formatted URL */
	function get_access_url () {return parent::get_access_url ();}
	
	/** For peripherals with Web access, return the protocol (http or https) */
	function get_access_url_protocol () {return parent::get_access_url_protocol ();}
	
	/** For peripherals with Web access, return the base URL (without IP part) */
	function get_access_url_base () {return parent::get_access_url_base ();}
	
	/** For peripherals with network access, get the access IP */
	function get_net_access_ip () {return parent::get_net_access_ip ();}
	
	/** For peripherals with network access, get the access port */
	function get_net_access_port () {return parent::get_net_access_port ();}
	
	/** For SNMP-enabled peripherals, return the computer which does the SNMP monitoring */
	function get_snmp_computer () {return parent::get_snmp_computer ();}
	
	/** For SNMP-enabled peripherals, returns the assigned monitoring profile, if any */
	function get_monitoring_profile () {return parent::get_monitoring_profile ();}
	
	/** Not used in this class */
	function get_log_months () {return false;}
	
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
	function get_peripherals_list ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT p.id, p.name FROM '.TBL_REMOVED_PERIPHERALS.' p INNER JOIN ';
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
		
		$ret = DB::db_fetch_list ($q);
		
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
	*						- no_group: if TRUE, the peripherals will not be returned grouped
	*						  by classes.
	* @return	array				Associative array. If 'no_group' is not specified in $filter, then the
	*						array keys will be peripheral class IDs and the values will be array
	*						with the found Peripheral objects. If 'no_group' is set to TRUE, then
	*						the result will be a simple array with all the peripheral objects matched.
	*/
	function get_peripherals ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT DISTINCT p.id ';
		if ($filter['order_by'] == 'asset_no') $q.= ', concat("'.ASSET_PREFIX_PERIPHERAL.'",lpad(id,'.ASSET_NUM_LENGTH.',"0")) as asset_no ';
		$q.= 'FROM '.TBL_REMOVED_PERIPHERALS.' p ';
		$q.= 'WHERE ';
		if ($filter['customer_id']) $q.= 'p.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['class_id'] and $filter['no_group']) $q.= 'p.class_id='.$filter['class_id'].' AND ';
		
		if ($filter['no_group'])
		{
			$q = preg_replace ('/AND\s*$/', ' ', $q);
			$q = preg_replace ('/WHERE\s*$/', ' ', $q);
			$q.= ' ORDER BY '.($filter['order_by'] == 'asset_no' ? 'asset_no' : 'p.name').' ';
			$ids = DB::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new RemovedPeripheral ($id);
		}
		else
		{
			// Fetch the list of peripherals classes
			$q_classes = 'SELECT DISTINCT c.id FROM '.TBL_PERIPHERALS_CLASSES.' c ';
			$q_classes.= 'INNER JOIN '.TBL_REMOVED_PERIPHERALS.' p ON c.id=p.class_id ';
			if ($filter['customer_id']) $q_classes.= 'WHERE p.customer_id='.$filter['customer_id'].' ';
			$q_classes.= 'ORDER BY c.position ';

			$classes_list = DB::db_fetch_vector ($q_classes);
			foreach ($classes_list as $class_id)
			{
				$ids = DB::db_fetch_vector ($q.' p.class_id='.$class_id.' ORDER BY '.($filter['order_by'] == 'asset_no' ? 'asset_no' : 'p.name'));
				foreach ($ids as $id) $ret[$class_id][] = new RemovedPeripheral ($id);
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
	function get_peripherals_customer_ids ($filter = array ())
	{
		$ret = array ();
		$q = 'SELECT id, customer_id FROM '.TBL_REMOVED_PERIPHERALS.' WHERE ';
		
		if ($filter['profile_id']) $q.= 'profile_id='.$filter['profile_id'].' AND ';
		if ($filter['class_id']) $q.= 'class_id='.$filter['class_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);
		
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
	
	/** [Class Method] Returns all inactive customers which still have active peripherals
	* @return	array			Associative array, the keys being IDs of customers and the values being the
	*					number of active peripherals they still have. The array will contain only
	*					those inactive customers which still have active peripherals. The customer IDs
	*					are ordered by the customers names.
	*/
	function get_inactive_customers_with_peripherals ()
	{
		$ret = array ();
		
		$q = 'SELECT p.customer_id, count(DISTINCT p.id) as cnt FROM '.TBL_PERIPHERALS.' p INNER JOIN '.TBL_CUSTOMERS.' cust ';
		$q.= 'ON p.customer_id=cust.id AND cust.active=0 GROUP BY 1 ORDER BY cust.name';
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}

	
	
	/** [Class Method] Returns the warranties of all peripherals for a specified customer, but only peripherals
	* for which the classes specify that warranties info are stored.
	* @param	array				$filter		Associative array with filtering criteria. Fields can be:
	* 								- customer_id: (Required) The customer for which to return warranties
	*								- order_by: 'asset_no' to sort the results by asset_number
	* @return	array(Warranty)					Array with the matched Warranty objects, sorted by
	*								class name and peripheral name.
	*/
	function get_warranties ($filter = array ())
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
			$q.= 'FROM '.TBL_REMOVED_PERIPHERALS.' p INNER JOIN '.TBL_PERIPHERALS_CLASSES.' pc ';
			$q.= 'ON p.class_id=pc.id WHERE p.customer_id='.$customer_id.' AND pc.use_warranty=1 ';
			if ($filter['order_by'] == 'asset_no') $q.= 'ORDER BY pc.name, asset_no ';
			else $q.= 'ORDER BY pc.name, p.name ';
			$data = DB::db_fetch_array ($q);
			
			foreach ($data as $d) $ret[] = new Warranty (WAR_OBJ_REMOVED_PERIPHERAL, $d->id, $d->class_id);
		}
		
		return $ret;
	}
	
	/** Not needed for this class */
	function get_peripherals_notifications () {return false;}
	
	/** Not needed for this class */
	function check_monitor_alerts ($no_increment = true) {return false;}
	
	/** Not needed for this class */
	function update_monthly_logs ($quick = false) {return false;}


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
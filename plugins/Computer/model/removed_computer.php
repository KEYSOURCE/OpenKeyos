<?php

class_load ('Computer');
class_load ('MonitorItem');
class_load ('MonitorProfile');
class_load ('RemovedComputerItem');
class_load ('RemovedComputerNote');

/**
* Class for representing removed computers.
*
* These are computers which are not in used anymore, but the information about
* them is kept for reference.
*
* Note that the RemovedComputer class extends Computer, not Base.
*
* A removed computer will preserve the same ID from the associated Computer object,
* which means that when creating a new Computer object in the database the system
* should make sure that it is not re-using and ID from a RemovedComputer.
*
* Since the IDs and reporting infos for computers are preserved, it is also possible
* to restore removed computers back to active state.
*/

class RemovedComputer extends Computer
{
	/** The date when the computer was removed from use
	* @var timestamp */
	var $date_removed = 0;
	
	/** The reason for which the computer was removed
	* @var text */
	var $reason_removed = '';
	
	/** The ID of the user who marked the computer as removed
	* @var int */
	var $removed_by = 0;
	
	
	/** The database table storing removed computers data 
	* @var string */
	var $table = TBL_REMOVED_COMPUTERS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'customer_id', 'profile_id', 'last_contact', 'mac_address', 'type', 'remote_ip', 'comments', 'location_id', 'is_manual', 'date_created', 'netbios_name', 'date_removed', 'reason_removed', 'removed_by');
	
	
	/**
	* Constructor, also loads the removed computer data from the database if an ID is specified
	* @param	int $id		The computer id
	*/
	function RemovedComputer ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
                        //$this->verify_access();
		}
	}
	
	/** Loads the computer data from the database */
	function load_data ()
	{
		$ret = false;
		if ($this->id)
		{
			Base::load_data();
			if ($this->id)
			{
				$ret = true;
				
				// Build the asset number
				$this->asset_no = get_asset_no_comp ($this->id, $this->type);
			}
		}
		return $ret;
	}
	
	function save_data ()
	{
		Base::save_data ();
	}
	
	/** Loads the associated computer profile */
	function load_profile ()
	{
		if ($this->profile_id) $this->profile = new MonitorProfile ($this->profile_id);
	}
	
	/** Load the associated location, if any */
	function load_location ()
	{
		if ($this->id and $this->location_id)
		{
			class_load ('Location');
			$this->location = new Location ($this->location_id);
			$this->location->load_parents ();
		}
	}
	
	/** Checks if a computer's data is valid - used for manually creating computers */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->date_removed) {error_msg ($this->get_string('NEED_REMOVED_DATE')); $ret = false; }
		if (!trim($this->reason_removed)) {error_msg ($this->get_string('NEED_REMOVED_REASON')); $ret = false; }
		
		return $ret;
	}
	
	/** Delete the computer data and all associated information */
	function delete ()
	{
		if ($this->id)
		{
			// Delete all notes
			$this->db_query ('DELETE FROM '.TBL_REMOVED_COMPUTERS_NOTES.' WHERE computer_id='.$this->id);
			
			// Delete all referenced from tickets
			$this->db_query ('DELETE FROM '.TBL_TICKETS_OBJECTS.' WHERE object_class='.TICKET_OBJ_CLASS_REMOVED_COMPUTER.' AND object_id='.$this->id);
			
			// Delete the removed computer object itself
			Base::delete ();
		}
	}
	
	
	/** [Class Method] Removes a computer, by creating a new RemovedComputer, copying all relevant data from
	* the associated Computer object and then deleting the Computer object.
	* @param	Computer		$computer		The computer to remove
	* @param	int			$user_id		The ID of the user who made the operation
	* @param	text			$reason			The reason given for the deletion
	* @param	timestamp		$date_removed		The date at which to mark that the computer has been removed.
	*								If not specified, the current time will be used
	* @return	RemovedComputer					The newly created RemovedComputer
	*/
	function remove_computer ($computer, $user_id, $reason, $date_removed = 0)
	{
		$ret = null;
		
		if ($computer->id)
		{
			// Copy the main computer data
			$ret = new RemovedComputer ();
			foreach ($ret->fields as $field) if (isset($computer->$field)) $ret->$field = $computer->$field;
			$ret->date_removed = ($date_removed ? $date_removed : time ());
			$ret->reason_removed = $reason;
			$ret->removed_by = $user_id;
			$ret->save_data ();
			
			// Copy the computer items and notes
			DB::db_query ('DELETE FROM '.TBL_REMOVED_COMPUTERS_ITEMS.' WHERE computer_id='.$computer->id); // Just in case
			DB::db_query ('INSERT INTO '.TBL_REMOVED_COMPUTERS_ITEMS.' SELECT * FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$computer->id);
			
			DB::db_query ('DELETE FROM '.TBL_REMOVED_COMPUTERS_NOTES.' WHERE computer_id='.$computer->id); // Just in case
			DB::db_query ('INSERT INTO '.TBL_REMOVED_COMPUTERS_NOTES.' SELECT * FROM '.TBL_COMPUTERS_NOTES.' WHERE computer_id='.$computer->id);
			
			// Update the references in tickets
			$q = 'UPDATE '.TBL_TICKETS_OBJECTS.' SET object_class='.TICKET_OBJ_CLASS_REMOVED_COMPUTER.' ';
			$q.= 'WHERE object_class='.TICKET_OBJ_CLASS_COMPUTER.' AND object_id='.$computer->id;
			DB::db_query ($q);
			
			
			// Delete the Computer object
			$computer->delete ();
		}
		
		return $ret;
	}
	
	/** Restores a removed computer backup to active state, by recreating the associated Computer object
	* and deleting the RemovedComputer object.
	* Note that, unlike the remove_computer() method, this is an object method, NOT a class method
	* @return	Computer						The newly restored Computer object
	*/
	function restore_computer ()
	{
		$ret = null;
		if ($this->id)
		{
			// Copy the main computer data
			$ret = new Computer ();
			foreach ($ret->fields as $field) if (isset($this->$field)) $ret->$field = $this->$field;
			$ret->save_data ();
			
			// Copy the computer items and notes
			DB::db_query ('DELETE FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id); // Just in case
			DB::db_query ('INSERT INTO '.TBL_COMPUTERS_ITEMS.' SELECT * FROM '.TBL_REMOVED_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id);
			
			DB::db_query ('DELETE FROM '.TBL_COMPUTERS_NOTES.' WHERE computer_id='.$this->id); // Just in case
			DB::db_query ('INSERT INTO '.TBL_COMPUTERS_NOTES.' SELECT * FROM '.TBL_REMOVED_COMPUTERS_NOTES.' WHERE computer_id='.$this->id);
			
			// Update the references in tickets
			$q = 'UPDATE '.TBL_TICKETS_OBJECTS.' SET object_class='.TICKET_OBJ_CLASS_COMPUTER.' ';
			$q.= 'WHERE object_class='.TICKET_OBJ_CLASS_REMOVED_COMPUTER.' AND object_id='.$this->id;
			DB::db_query ($q);
			
			// Delete the RemovedComputer object
			$this->delete ();
		}
		return $ret;
	}
	
	
	/** [Not needed for this class] */
	function set_customer () {return false;}
	
	/** [Not needed for this class] */
	function contact_made () {return false;}
	
	/** [Not needed for this class] */
	function check_update_needed () {return false;}
	
	/** [Not needed for this class] */ 
	function get_storing_value () {return false;}
	
	/** [Not needed for this class] */ 
	function translate_direct_data () {return false;}
	
	/** [Not needed for this class] */ 
	function add_reported_items () {return false;}
	
	/** [Not needed for this class] */ 
	function are_values_changed () {return false;}
	
	/**
	* Returns an array of monitor items according to this computer's monitor profile.
	* It is similare with the same method in Computer class, except that it won't look
	* for logged/archival items, since they are not preserved for removed computers.
	*/
	function get_reported_items ()
	{
		$ret = array();
		if ($this->id)
		{
			$items_list = MonitorProfile::get_profile_items_list ($this->profile_id);
			
			// We use this method in order to get the items ordered by category
			$items = $this->db_fetch_vector ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE parent_id=0 ORDER BY category_id, id ');
			foreach ($items as $item_id)
			{
				// XXXX Temporary only, until 1030 is replaced in Agent with 1046 and 1047
				if ($item_id<>1030)
					if (isset($items_list[$item_id])) $ret[] = new RemovedComputerItem($this->id, $item_id);
			}
		} 
		return $ret;
	}

        /**
         * Return additional info for this computer (brand, model, sn).
         * @param int $computer_id
         * @return array
         */
        function get_additional_info($computer_id = null) {
            $ret = null;
            $computer_id = ($computer_id ? $computer_id : $this->id);

            if($computer_id) {
                $brand = $this->get_item('computer_brand', $computer_id);
                $ret['computer_brand'] = $brand;
                $ret['computer_model'] = $this->get_item('computer_model', $computer_id);
                $ret['computer_sn'] = $this->get_item('computer_sn', $computer_id);
            }
            return $ret;
        }
	
	/**
	* Returns the value for a monitoring item for this computer, by the item short name.
	* Can be called as class method too, in which case computer_id must be specified.
	* @param	string	$item_name		The item's short name
	* @param	string	$computer_id		A computer ID, if called as class method
	* @return	array				An array with the current values.
	*/
	function get_item ($item_name = '', $computer_id = null)
	{
		$ret = null;
		$computer_id = ($computer_id ? $computer_id : $this->id);
		
		if ($item_name and $computer_id)
		{
			if (method_exists($this, 'db_fetch_field'))
				$id = $this->db_fetch_field ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE short_name = "'.$item_name.'"', 'id');
			else
				$id = db::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE short_name = "'.$item_name.'"', 'id');
				
			$item = new RemovedComputerItem ($computer_id, $id);
			
			if ($item->itemdef->multi_values == MONITOR_MULTI_NO)
			{
				$ret = $item->val[0]->value;
			}
			else
			{
				for ($i=0; $i<count($item->val); $i++) $ret[] = $item->val[$i]->value;
			}
		}
		return $ret;
	}
	
	
	/**
	* Returns the value for a monitoring item for this computer, by the item ID.
	* @param	string	$item_name		The item's short name
	* @return	ComputerItem			The ComputerItem object with the collected values
	*/
	function get_item_by_id ($item_id = 0)
	{
		$ret = null;
		if ($item_id)
		{
			$ret = new RemovedComputerItem ($this->id, $item_id);
		}
		return $ret;
	}
	
	
	/** Returns the numeric ID for an item name. Can be called as class method or object method */
	function get_item_id ($item_name, $parent_id = '')
	{
		parent::get_item_id ($item_name, $parent_id = '');
	}

	
	/** Returns the last user that logged in on this computer */
	function get_last_login ()
	{
		$ret = '';
		
		if ($this->id)
		{
			// Try first in the current items
			$q = 'SELECT max(reported), value FROM '.TBL_REMOVED_COMPUTERS_ITEMS.' ';
			$q.= 'WHERE computer_id='.$this->id.' AND item_id='.CURRENT_USER_ITEM_ID.' and value<>"" GROUP BY computer_id';
			$ret = $this->db_fetch_field ($q, 'value');
			
			if (!$ret)
			{
				// Try the logs
				$q = 'SELECT max(reported), value FROM '.TBL_REMOVED_COMPUTERS_ITEMS_LOG.' ';
				$q.= 'WHERE computer_id='.$this->id.' AND item_id='.CURRENT_USER_ITEM_ID.' and value<>"" GROUP BY computer_id';
				$ret = $this->db_fetch_field ($q, 'value');
			}
		}
		
		return $ret;
	}
	
	
	/** [Not needed for this class] */ 
	function get_logged_data () {return false;}
	
	/** [Not needed for this class] */ 
	function clear_logged_data () {return false;}
	
	/** [Not needed for this class] */ 
	function get_needed_items () {return false;}
	
	/** [Not needed for this class] */ 
	function get_needed_items_snmp () {return false;}
	
	/** [Not needed for this class] */ 
	function get_needed_events_report () {return false;}
	
	/** [Not needed for this class] */ 
	function get_needed_discoveries () {return false;}
	
	/** [Not needed for this class] */ 
	function get_identical_macs () {return false;}
	
	/** [Not needed for this class] */ 
	function get_identical_names () {return false;}
	
	/** [Not needed for this class] */ 
	function merge_with_computer () {return false;}
	
	/** [Not needed for this class] */ 
	function check_monitor_alerts () {return false;}
	
	/** [Not needed for this class] */ 
	function get_notifications () {return false;}
	
	/** [Not needed for this class] */ 
	function get_notification_recipients () {return false;}
	
	/** [Not needed for this class] */ 
	function get_by_mac () {return false;}
	
	/**
	* [Class Method] Returns a list of removed computers 
	* @param	array		$filter		Filtering criteria to apply. Possible key/values are:
	*						- order_by, order_dir : The field by which to sort the computers an 
	*						  the direction of the sorting. Special values: netbios_name, current_user, 
	*						  computer_brand, computer_model, os_name (which are Kawacs collected fields),
	*						  customer, profile; asset_no (sort by asset number)
	*						- customer_id : Return computers only for the specified customer_id.
	*						- type : Return computers of a specific type - see $GLOBALS['COMP_TYPE_NAMES']
	*						- profile_id : Return computers using the specified profile.
	*						- assigned_user_id : Return only computers to which the user with this ID has access.
	*						- search_text: Return computers having the specified text in the name
	*						- limit, start : How many computers to return and from where to start
	*						  the count.
	* @param	int		$count		(By reference) If defined at the time of call, it will 
	*						store the total number of matching computers found.
	* @return	array(RemovedComputer)		List of computers matching the specified criteria
	*/
	function get_removed_computers ($filter = array(), &$count)
	{
		$ret = array();

		$q = 'FROM '.TBL_REMOVED_COMPUTERS.' c ';
		
		$customers_joined = false;
		if ($filter['order_by'])
		{
			$filter['order_dir'] = ($filter['order_dir'] ? $filter['order_dir'] : 'ASC');
			
			if (in_array ($filter['order_by'], array ('current_user', 'computer_brand', 'computer_model', 'os_name', 'computer_sn')))
			{
				// For these sorting columns the sorting value comes from the monitor items values
				$q.= 'LEFT OUTER JOIN '.TBL_REMOVED_COMPUTERS_ITEMS.' ci ON c.id=ci.computer_id AND ci.item_id='.MonitorItem::get_item_id ($filter['order_by']).' ';
				$filter['order_by'] = 'ci.value';
			}
			elseif ($filter['order_by'] == 'customer')
			{
				$customers_joined = true;
				$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' cust on c.customer_id=cust.id ';
				$filter['order_by'] = 'cust.name';
			}
			elseif ($filter['order_by'] == 'profile')
			{
				$q.= 'LEFT OUTER JOIN '.TBL_MONITOR_PROFILES.' p on c.profile_id=p.id ';
				$filter['order_by'] = 'p.name';
			}
			elseif ($filter['order_by'] == 'asset_no')
			{
				$filter['order_by'] == 'asset_no'; // Just to prevent adding the 'c.' prefix
			}
			else $filter['order_by'] = 'c.'.$filter['order_by'];
			
		}
		if ($filter['assigned_user_id'])
		{
			if (!$customers_joined) $q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' cust on c.customer_id=cust.id ';
			
			// Check both direct user assignment and group assignment
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_CUSTOMERS_ASSIGNED.' ac ON cust.id=ac.customer_id ';
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON ac.user_id=ug.group_id ';
		}
		
		$q.= 'WHERE ';

		if ($filter['customer_id'] > 0) $q.= 'c.customer_id='.$filter['customer_id'].' AND ';
		
		if ($filter['assigned_user_id'])
		{
			$q.= '(ac.user_id='.$filter['assigned_user_id'].' OR ';
			$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$filter['assigned_user_id'].')) AND ';
		}
		
		if (is_numeric($filter['type']) and $filter['type']>=0) $q.= 'c.type='.$filter['type'].' AND ';
		if ($filter['profile_id']) $q.= 'c.profile_id='.$filter['profile_id'].' AND ';
		if ($filter['search_text']) $q.= 'c.netbios_name like "%'.mysql_escape_string($filter['search_text']).'%" AND ';
			
		$q = preg_replace ('/WHERE\s*AND/', 'WHERE ', $q);
		$q = preg_replace ('/WHERE\s*$/', '', $q);
		$q = preg_replace ('/AND\s*$/', '', $q);
		
		// Calculate the total number, if it was requested
		if (isset ($count))
		{
			$q_count = 'SELECT count(distinct c.id) as cnt '.$q;
			$count = db::db_fetch_field ($q_count, 'cnt');
		}
		
		// Now fetch the requested list
		if ($filter['order_by']=='asset_no')
		{
			$q_asset = 'concat(if(type='.COMP_TYPE_SERVER.',"'.ASSET_PREFIX_SERVER.'","'.ASSET_PREFIX_WORKSTATION.'"),lpad(id,'.ASSET_NUM_LENGTH.',"0"))';
			$q = 'SELECT DISTINCT c.id, '.$q_asset.' as asset_no '.$q;
		}
		else $q = 'SELECT DISTINCT c.id '.$q;
		if ($filter['order_by'])
		{
			$q.= 'ORDER BY '.($filter['group_by_type']?'c.type DESC,':'').' '.$filter['order_by'].' '.$filter['order_dir'].' ';
		}
		
		if (isset($filter['start']) and isset($filter['limit']))
		{
			$q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
		}
		$ids = db::db_fetch_vector($q);
		
		foreach ($ids as $id) $ret[] = new RemovedComputer($id);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of removed computers according to the specified criteria
	* @param	array	$filter		Associative array with filtering criteria. Fields can be:
	*					- customer_id : returns computers for the specified customer
	*					- type_id : returns computers of specified type
	*					- profile_id : returns computers of specified type
	*					- location_id: returns only computers assigned to this location
	*					- order_by : the sorting criteria, can be 'name' (default), 'id', 'asset_no'
	*					  'type' (to sort by computer type and then computer name), or can be 'customer', 
	*					  to sort by customer name
	*					- append_id : if True, the ID of the computers will be appended to names
	* @return	array			Associative array with the results, the keys being removed computer IDs,
	*					and the values being computer names
	*/
	function get_removed_computers_list ($filter = array())
	{
		$ret = array ();
		$q = 'SELECT DISTINCT c.id, c.netbios_name ';
		if ($filter['order_by'] == 'asset_no')
		{
			$q.= ', concat(if(type='.COMP_TYPE_SERVER.',"'.ASSET_PREFIX_SERVER.'","'.ASSET_PREFIX_WORKSTATION.'"),lpad(id,'.ASSET_NUM_LENGTH.',"0")) as asset_no ';
		}
		$q.= 'FROM '.TBL_REMOVED_COMPUTERS.' c ';
		
		if ($filter['order_by'] == 'customer')
		{
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
		}
		
		$q.= 'WHERE ';
		
		if ($filter['customer_id']) $q.= 'c.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['type_id']) $q.= 'c.type='.$filter['type_id'].' AND ';
		if ($filter['profile_id']) $q.= 'c.profile_id='.$filter['profile_id'].' AND ';
		if ($filter['location_id']) $q.= 'c.location_id='.$filter['location_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);
		
		if ($filter['order_by'] == 'customer') $q.= 'ORDER BY cust.name, c.netbios_name ';
		elseif ($filter['order_by'] == 'id') $q.= 'ORDER BY c.id ';
		elseif ($filter['order_by'] == 'type') $q.= 'ORDER BY c.type DESC, c.netbios_name ';
		elseif ($filter['order_by'] == 'asset_no') $q.= 'ORDER BY asset_no ';
		else $q.= 'ORDER BY c.netbios_name ';
		
		$ret = db::db_fetch_list ($q);
		if ($filter['append_id'])
		{
			foreach ($ret as $id => $name) $ret[$id] = $name.' ('.$id.')';
		}
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns a list with the types for each computer for a customer
	* @param	int	$customer_id	The ID of the customer
	* @return	array			Associative array, the keys being computer IDs and the values being their types
	*/
	function get_computers_types ($customer_id)
	{
		$q = 'SELECT id, type FROM '.TBL_REMOVED_COMPUTERS.' WHERE customer_id='.$customer_id;
		return DB::db_fetch_list ($q);
	}
	
	/** [Not needed for this class] */ 
	function get_list_monitored_peripherals () {return false;}
	
	/**
	* [Class Method] Returns a list of customer IDs associated with computers
	* @param	array	$filter		Associative array with filtering criteria. Fields can be:
	*					- type_id : returns computers of specified type
	*					- profile_id : returns computers of specified type
	* @return	array			Associative array, the keys are computer IDs and the values are their
	*					customer IDs.
	*/
	function get_computers_customer_ids ($filter = array ())
	{
		$ret = array ();
		$q = 'SELECT c.id, c.customer_id FROM '.TBL_REMOVED_COMPUTERS.' c WHERE ';
		
		if ($filter['profile_id']) $q.= 'c.profile_id='.$filter['profile_id'].' AND ';
		if ($filter['type_id']) $q.= 'c.type='.$filter['type_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);
		
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	/** [Class Method] Returns all inactive customers which still have active computers
	* @return	array			Associative array, the keys being IDs of customers and the values being the
	*					number of active computers they still have. The array will contain only
	*					those inactive customers which still have active computers. The customer IDs
	*					are ordered by the customers names.
	*/
	function get_inactive_customers_with_computers ()
	{
		$ret = array ();
		
		$q = 'SELECT c.customer_id, count(DISTINCT c.id) as cnt FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_CUSTOMERS.' cust ';
		$q.= 'ON c.customer_id=cust.id AND cust.active=0 GROUP BY 1 ORDER BY cust.name';
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
		
	/**
	* [Class Method] Returns a list of disk partitions 
	* @param	array	$filter		Associative array with filtering criteria (similar with get_computers_list()).
	*					Fields can be:
	*					- customer_id : returns computers for the specified customer
	*					- type_id : returns computers of specified type
	*					- order_by : the sorting criteria, can be 'name' (default) or 'id'
	*					- append_id : if True, the ID of the computers will be appended to names
	*					- with_logs : if True, will return only those computers/partitions for which
	*					  logging of disk space is enabled.
	* @return	array			Associative array with the results, the keys being strings generated from 
	*					computer ID and partition UNC (joined with '_'), and the values being
	*					computer names + partition names
	*/
	function get_disks_list ($filter = array ())
	{
		$ret = array ();
		
		$partitions_item_id = RemovedComputer::get_item_id ('partitions');
		$path_field_id = RemovedComputer::get_item_id ('unc', $partitions_item_id);
		
		$q = 'SELECT concat(c.id, "_", i.value), c.id FROM '.TBL_REMOVED_COMPUTERS.' c INNER JOIN '.TBL_REMOVED_COMPUTERS_ITEMS.' i ';
		$q.= 'ON c.id=i.computer_id ';
		$q.= 'WHERE i.item_id='.$partitions_item_id.' AND field_id='.$path_field_id.' ';
		
		if ($filter['customer_id']) $q.= 'AND c.customer_id='.$filter['customer_id'].' ';
		if ($filter['type_id']) $q.= 'AND c.type='.$filter['type_id'].' '; 
		
		$partitions_list = DB::db_fetch_list ($q);
		$computers_list = RemovedComputer::get_removed_computers_list ($filter);
		
		foreach ($partitions_list as $partition => $id)
		{
			$partition_name = split ('_', $partition, 2);
			$ret[$partition] = $computers_list[$id].' ['.$partition_name[1].']';
		}
		
		if ($filter['order_by'] == 'id') ksort ($ret);
		else asort($ret);
		
		return $ret;
	}
	
	
	/** [Not needed for this class] */
	function get_computers_missed () {return false;}
	
	/** [Not needed for this class] */
	function get_alert_computers () {return false;}
	
	/** [Not needed for this class] */
	function get_computer_alerts_stat () {return false;}
	
	/** [Not needed for this class] */
	function get_new_computers () {return false;}
	
	/** [Not needed for this class] */
	function get_oldest_contacts () {return false;}
	
	/** [Not needed for this class] */
	function update_monthly_logs () {return false;}
	
	/** [Not needed for this class] */
	function get_log_months () {return false;}
	
	/** [Not needed for this class] */
	function get_all_log_months () {return false;}
	
	/** [Not needed for this class] */
	function get_partitions_history () {return false;}
	
	/** [Not needed for this class] */
	function get_backups_history () {return false;}
	
	/** [Not needed for this class] */
	function get_backups_sizes () {return false;}
	
	/** [Not needed for this class] */
	function get_av_history () {return false;}
	
	/** [Not needed for this class] */
	function get_av_status () {return false;}
	
	
	/** [Class Method] Gets warranties information for removed computers belonging to a certain customer. The returned 
	* computers are sorted by type and then by name.
	* NOTE: Only physical computers are returned. VMWare machines are ignored.
	*/
	function get_warranties ($filter = array ())
	{
		class_load ('Warranty');
		$ret = array ();
		
		// Fetch the list of VMWare computers for the customer
		$q = 'SELECT DISTINCT i.computer_id FROM '.TBL_REMOVED_COMPUTERS_ITEMS.' i ';
		if ($filter['customer_id']) $q.= ' INNER JOIN '.TBL_REMOVED_COMPUTERS.' c ON i.computer_id=c.id AND c.customer_id='.$filter['customer_id'].' ';
		$q.= 'WHERE i.item_id='.BRAND_ITEM_ID.' AND i.value="'.VMWARE_BRAND_MARKER.'" ';
		$vmware_ids = DB::db_fetch_vector ($q);
		
		// Get the IDs and NRCs for all computers that have warranty information defined
		$q = 'SELECT DISTINCT i.computer_id, i.nrc FROM '.TBL_REMOVED_COMPUTERS_ITEMS.' i ';
		if ($filter['customer_id']) $q.= ' INNER JOIN '.TBL_REMOVED_COMPUTERS.' c ON i.computer_id=c.id AND c.customer_id='.$filter['customer_id'].' ';
		
		$q.= 'WHERE i.item_id='.WARRANTY_ITEM_ID.' ';
		if ($filter['computer_id']) $q.= ' AND i.computer_id='.$filter['computer_id'].' ';
		$q.= 'ORDER BY i.computer_id, i.nrc';
		
		$data = DB::db_fetch_array ($q);
		$warranties = array ();
		foreach ($data as $d) 
		{
			if (!in_array($d->computer_id, $vmware_ids))
			{
				$warranties[$d->computer_id][] = new Warranty (WAR_OBJ_REMOVED_COMPUTER, $d->computer_id, $d->nrc);
			}
		}
		
		$computers_count = count(array_unique(array_keys($warranties)));
		
		// If a customer ID is specified and there is more than a computer, sort the list by computer name. We use this method because it's faster 
		// than ordering in the query (would required additional joins)
		if ($filter['customer_id'] and $computers_count > 1)
		{
			$filter_computers = array('customer_id' => $filter['customer_id'], 'order_by'=>'type');
			if ($filter['order_by'] == 'asset_no') $filter_computers['order_by'] = 'asset_no';
			$computers_list = RemovedComputer::get_computers_list ($filter_computers);
			foreach ($computers_list as $computer_id => $computer_name)
			{
				// Include in result only non-VMWare machines
				if (!in_array($computer_id, $vmware_ids))
				{
					if (isset($warranties[$computer_id]))
					{
						foreach ($warranties[$computer_id] as $w) $ret[] = $w;
					}
					// XXXX set new object type
					else $ret[] = new Warranty (WAR_OBJ_REMOVED_COMPUTER, $computer_id, 0);
				}
			}
		}
		else
		{
			foreach ($warranties as $computer_id => $w)
			{
				// Include in result only non-VMWare machines
				if (!in_array($computer_id, $vmware_ids))
				{
					foreach ($w as $warranty) $ret[] = $warranty;
				}
			}
		}
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
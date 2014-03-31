<?php

class_load ('MonitorProfile');
class_load ('MonitorProfileItemPeriph');
class_load ('Alert');
class_load ('EventLogRequested');

/**
* Class for representing monitoring profiles that can be attributed to peripherals.
*
* Normally these profiles group together SNMP monitor items which are specific to a certain
* type and/or brand of device.
*
* The class has a similar structure with the MonitorProfile which is used for computers.
*
* However, a key difference for peripherals monitoring is that assigning a profile to a peripheral
* is not enough for the actual monitoring to begin. To do this, it is necessary to assign the
* peripheral to a computer which will collect and report the SNMP information about the peripheral.
* 
*/

class MonitorProfilePeriph extends MonitorProfile
{
	/** This inherited field has no relevance for peripherals
	* @var bool */
	var $is_default = false;
	
	/** This inherited field has no relevance for peripherals, as there is no alerting system (yet) for peripherals
	* @var int */
	var $alert_missed_cycles = 0;
	
	
	/** The list of monitor items defined in this profile 
	* @var array(MonitorProfileItemPeriph) */
	var $items = array();

	/** This inherited field has no relevance for peripherals, as there is no alerting system (yet) for peripherals.
	* @var array(Alert) */
	var $alerts = array();
	
	/** This inherited field has no relevance for peripherals
	* @var array */
	var $default_events_types_requested = array ();
	
	/** This inherited field has no relevance for peripherals
	* @var array(EventLogRequested) */
	var $events_types_requested = array ();
	
	
	/** The name of the database table which stores data for this object
	* @var string */
	var $table = TBL_MONITOR_PROFILES_PERIPH;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'name', 'report_interval', 'description');
	
	/** Contructor. Loads the profile definition if an ID is specified */
	function MonitorProfilePeriph ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/**
	* Loads the profile data, as well as the details for the items included in this profile
	*/
	function load_data ()
	{
		Base::load_data(); // Calling the Base's method, to avoid the extra actions in MonitorProfile
		
		if ($this->id)
		{
			// Load the items for this profile
			$this->items = array ();
			$item_ids = $this->db_fetch_vector ('SELECT item_id FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$this->id.' ORDER BY item_id');
			foreach ($item_ids as $item_id) $this->items[$item_id] = new MonitorProfileItemPeriph ($this->id, $item_id);
			
			// Load the alerts for this profile
			$q = 'SELECT pa.alert_id FROM '.TBL_PROFILES_PERIPH_ALERTS.' pa INNER JOIN '.TBL_ALERTS.' a ON pa.alert_id=a.id ';
			$q.= 'WHERE pa.profile_id='.$this->id.' ORDER BY a.level DESC, a.name ';
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id) $this->alerts[] = new Alert ($id);
		}
	}
	
	/** This method has no relevance for peripherals */
	function load_events_settings () {return false;}
	
	
	/** Checks if the profile data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify the profile name.'); $ret = false;}
		else
		{
			// Check name uniqueness
			$q = 'SELECT id FROM '.TBL_MONITOR_PROFILES_PERIPH.' WHERE name="'.mysql_escape_string($this->name).'" ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			$q.= 'LIMIT 1';
			if (DB::db_fetch_field ($q, 'id')) {error_msg ('This name is already in use for another profile.'); $ret = false;}
		}
		
		return $ret;
	}
	
	
	/**
	* Saves the profile data to the database
	*/
	function save_data ()
	{
		Base::save_data();
		if ($this->id)
		{
			// Get the current list of profile items from database to see if there are any changes
			$q = 'SELECT item_id, update_interval FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$this->id;
			$list_update = $this->db_fetch_list ($q);
			$q = 'SELECT item_id, log_type FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$this->id;
			$list_log = $this->db_fetch_list ($q);
			
			// Save only the changes that have been made, if any
			foreach ($this->items as $item_id => $item)
			{
				if (!isset($list_update[$item_id]) or 
				$list_update[$item_id]!=$item->update_interval or $list_log[$item_id]!=$item->log_type
				) $item->save_data ();
			}
			// Now delete the items which are not anymore in the list
			foreach ($list_update as $item_id => $update)
			{
				if (!isset($this->items[$item_id]))
				{
					$this->db_query ('DELETE FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$this->id.' AND item_id='.$item_id);
				}
			}
		}
	}
	
	/**
	* Loads the profile data from an array, optionally including the data for the profile items
	* @param	array	$profile_data		The info about the profile, in an associative array
	* @param	array	$items_data		The data about the items in the profile. If specified,
	*						then $profile data must include a field "items" with the IDs
	*						of the items that need to be included from $items_data.
	*						This can be set only for existing profiles.
	*/
	function load_from_array ($profile_data = array(), $items_data = null)
	{
		Base::load_from_array ($profile_data);
		if ($this->id and !is_null($items_data) and is_array($items_data))
		{
			$this->items = array ();
			foreach ($items_data as $id => $v)
			{
				if (isset($items_data[$id]) and $items_data[$id]['update_interval']>0)
				{
					$profile_item = new MonitorProfileItemPeriph ($this->id, $id);
					$profile_item->profile_id = $this->id;
					$profile_item->item_id = $id;
					$profile_item->update_interval = $items_data[$id]['update_interval'];
					$profile_item->log_type = $items_data[$id]['log_type'];
					$this->items[$id] = $profile_item;
				}
			}
		}
	}
	
	
	/** Sets the items to be used for this profile. Note that it does NOT save anything to the database 
	* @param	array			$items_ids	Array with the IDs of the items to set for this profile.
	*							The items which did not exist before will be added with
	*							the default reporting interval and log settings. The
	*							items which existed but are not specified in this 
	*							array will be removed.
	*/
	function set_items_ids ($items_ids)
	{
		if ($this->id and is_array($items_ids))
		{
			// First, add the new items, if any
			foreach ($items_ids as $item_id)
			{
				if (!isset($this->items[$item_id]))
				{
					$item_def = new MonitorItem ($item_id);
					$item = new MonitorProfileItemPeriph ();
					$item->profile_id = $this->id;
					$item->item_id = $item_id;
					$item->update_interval = $item_def->default_update;
					$item->log_type = $item_def->default_log;
					$this->items[$item_id] = $item;
				}
			}
			
			// Now remove all items which should not be used anymore
			foreach ($this->items as $item_id => $item)
			{
				if (!in_array($item_id, $items_ids)) unset ($this->items[$item_id]);
			}
		}
	}
	
	
	/** This inherited method has no relevance for peripherals */
	function get_events_types_str ($cat_id) {return false;}
	
	/** This inherited method has no relevance for peripherals */
	function set_default_events_reporting ($types) {return false;}
	
	/** Checks if the profile can be deleted - meaning if no peripherals are using it */
	function can_delete ()
	{
		$ret = false;
		if ($this->id)
		{
			$q = 'SELECT id FROM '.TBL_PERIPHERALS.' WHERE profile_id='.$this->id.' LIMIT 1';
			$exist_id = DB::db_fetch_field ($q, 'id');
			if (!$exist_id) $ret = true;
			else
			{
				$ret = false;
				error_msg ('This monitoring profile is already in use and it can\'t be deleted.');
			}
		}
		return $ret;
	}
	
	
	/** Deletes the profile */
	function delete()
	{
		if ($this->id)
		{
			// Delete the associated definitions for this profile's items
			$this->db_query ('DELETE FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$this->id);
			
			// Delete the object itself
			Base::delete();
		}
	}
	
	
	/** This inherited method has no relevance for peripherals */
	function set_as_default () {return false;}
	
	
	/**
	* Copies an existing profile (with all its attached data) into a new profile
	* @param	string				$new_name		The name for the new profile
	* @return	MonitorProfilePeriph					The newly created profile
	*/
	function copy_to ($new_name = '')
	{
		$new_profile = null;
		if ($this->id and $new_name)
		{
			$new_profile = $this;
			
			$new_profile->id = null;
			$new_profile->name = $new_name;
			$new_profile->save_data ();
			
			if ($new_profile->id)
			{
				// Copy the monitor profile items definitions
				$q = 'INSERT INTO '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' (profile_id, item_id, update_interval, log_type) ';
				$q.= 'SELECT '.$new_profile->id.' as profile_id, item_id, update_interval, log_type ';
				$q.= 'FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' p WHERE p.profile_id = '.$this->id;
				$this->db_query($q);
				
				// Copy the alerts definitions
				$alert_ids = array ();
				foreach ($this->alerts as $alert) $alert_ids[] = $alert->id;
				$new_profile->load_data ();
				$new_profile->alerts = array();
				$new_profile->set_alerts ($alert_ids);
			}
		}
		return $new_profile;
	}
	
	
	/** Sets the alerts which are assigned to this profile 
	* @param	array		$alerts		Array with the IDs of the assigned alerts
	*/
	function set_alerts ($alerts = array ())
	{
		if ($this->id and is_array($alerts))
		{
			// Compose a list with the list of currently assigned alerts IDs
			$assigned_alerts_ids = array ();
			for ($i=0; $i<count($this->alerts); $i++) $assigned_alerts_ids[] = $this->alerts[$i]->id;
			
			// First, delete all assignments no longer valid
			foreach ($assigned_alerts_ids as $alert_id)
			{
				if (!in_array($alert_id, $alerts))
				{
					$this->db_query ('DELETE FROM '.TBL_PROFILES_PERIPH_ALERTS.' WHERE alert_id='.$alert_id.' AND profile_id='.$this->id);
				}
			}
			
			// Then add the alerts which where not set before
			foreach ($alerts as $alert_id)
			{
				if (!in_array($alert_id, $assigned_alerts_ids))
				{
					$this->db_query ('REPLACE INTO '.TBL_PROFILES_PERIPH_ALERTS.'(profile_id,alert_id) VALUES ('.$this->id.','.$alert_id.')');
				}
			}
			
		}
	}
	
	
	/** [Class Method] Returns a list with the monitoring items and their logging settings for a specified profile
	* @param	int			$profile_id	The ID of the profile.
	* @return	array					Associative array, keys being item IDs and the values being the logging settings.
	*/
	public static function get_profile_items_list ($profile_id=0)
	{
		$ret = array ();
		if ($profile_id)
		{
			$q = 'SELECT item_id, log_type FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$profile_id;
			$ret = DB::db_fetch_list ($q);
		}
		return $ret;
	}
	
	
	/** [Class Method] Returns a list with the monitoring items and their update intervals for a specified profile
	* @param	int			$profile_id	The ID of the profile.
	* @return	array					Associative array, keys being item IDs and the values being their update intervals
	*/
	public static function get_profile_items_intervals ($profile_id=0)
	{
		$ret = array ();
		if ($profile_id)
		{
			$q = 'SELECT item_id, update_interval FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$profile_id;
			$ret = DB::db_fetch_list ($q);
		}
		return $ret;
	}
	
	
	/** [Class Method] This inherited method has no relevance for peripherals */
	public static function get_default_profile () {return ret;}
	
	
	/**
	* [Class Method] Returns a list with the current peripherals profiles objects
	*/
	public static function get_profiles ($filter = array())
	{
		$ret = array();
		
		$ids = DB::db_fetch_vector ('SELECT id FROM '.TBL_MONITOR_PROFILES_PERIPH.' ORDER BY name');
		foreach ($ids as $id) $ret[] = new MonitorProfilePeriph($id);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list with the peripherals monitor profiles defined in the system
	*/
	public static function get_profiles_list ($filter = array ())
	{
		return DB::db_fetch_list ('SELECT id, name FROM '.TBL_MONITOR_PROFILES_PERIPH.' ORDER BY name ');
	}
	
	/** [Class Method] This method has no relevance for peripherals profiles */
	public static function get_computers_count () {return false;}

	
	/**
	* [Class Method] Returns the number of peripherals which use each profile
	* @return	array				Associative array, the keys being profile IDs,
	*						and the values being the number of peripherals using those profiles
	*/
	public static function get_peripherals_count ()
	{
		// Count the peripherals
		$ret = DB::db_fetch_list ('SELECT profile_id, count(*) FROM '.TBL_PERIPHERALS.' WHERE profile_id<>0 GROUP BY profile_id');
		
		// Count the AD Printers too
		$ret_ad = DB::db_fetch_list ('SELECT profile_id, count(*) FROM '.TBL_AD_PRINTERS_EXTRAS.' WHERE profile_id<>0 GROUP BY profile_id');
		foreach ($ret_ad as $profile_id => $cnt)
		{
			$ret[$profile_id]+= $cnt;
		}
		
		return $ret;
	}
	
}

?>
<?php

class_load ('MonitorProfileItem');
class_load ('Alert');
class_load ('EventLogRequested');

/**
* Class for representing monitoring profiles that can be attributed to monitored objects (computers by default).
*
* These profiles specify the items that have to be collected,  how often they should be collected, 
* what should be kept in the logs, what alerts should be raised and what events from the computers' 
* events log should be reported.
*
* Regarding events, note that for individual computers additional settings can be specified,
* to exclude or include certain classes of events.
*
* The same goes for monitoring items collected with SNMP, where additional rules can be defined
* on a per-computer basis.
* 
*/

class MonitorProfile extends Base
{
	/** The profile ID
	* @var int */
	var $id = null;
	
	/** If this is the default profile to be used for new computers 
	* @var bool */
	var $is_default = false;
	
	/** The profile name 
	* @var string */
	var $name = '';
	
	
	/** The report interval for this profile (in minutes)
	* @var float */
	var $report_interval = '';
	
	/** The profile description
	* @var string */
	var $description = '';
	
	/** The number of missed cycles on which to raised an alert
	* @var int */
	var $alert_missed_cycles = 0;
	

	/** The list of monitor items defined in this profile 
	* @var array(MonitorProfileItem) */
	var $items = array();

	/** The list of monitor alerts defined for this profile
	* @var array(Alert) */
	var $alerts = array();
	
	/** Associative array with the default types of computer events to request. Loaded on request with load_events_settings() method.
	* The keys are categories IDs and the values are sums of EVENTLOG_* constants
	* @var array */
	var $default_events_types_requested = array ();
	
	/** Array with the events types and sources which will be requested from computers, other than the default ones 
	* requested through the default types.
	* Loaded on request with the load_events_settings() method
	* @var array(EventLogRequested) */
	var $events_types_requested = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_MONITOR_PROFILES;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'is_default', 'name', 'report_interval', 'description', 'alert_missed_cycles');
	
	
	
	/** Contructor. Loads the profile definition if an ID is specified */
	function MonitorProfile ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	function __destruct()
	{
		if(isset($this->items)) unset($this->items);
		if(isset($this->alerts)) unset($this->alerts);
		if(isset($this->default_events_types_requested)) unset($this->default_events_types_requested);
		if(isset($this->events_types_requested)) unset($this->events_types_requested);
	}
	
	/**
	* Loads the profile data, as well as the details for the item included in this profile
	*/
	function load_data ()
	{
		parent::load_data();
		
		if ($this->id)
		{
			// Load the items for this profile
			$this->items = array ();
			$item_ids = $this->db_fetch_vector ('SELECT item_id FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE profile_id='.$this->id.' ORDER BY item_id');
			foreach ($item_ids as $item_id) $this->items[$item_id] = new MonitorProfileItem($this->id, $item_id);
			
			// Load the alerts for this profile
			$q = 'SELECT pa.alert_id FROM '.TBL_PROFILES_ALERTS.' pa INNER JOIN '.TBL_ALERTS.' a ON pa.alert_id=a.id ';
			$q.= 'WHERE pa.profile_id='.$this->id.' ORDER BY a.level DESC, a.name ';
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id) $this->alerts[] = new Alert ($id);
		}
	}
	
	/** Loads into the object the settings for computer events reporting for this profile */
	function load_events_settings ()
	{
		if ($this->id)
		{
			$this->default_events_types_requested = EventLogRequested::get_profile_default_types ($this->id);
			$this->events_types_requested = EventLogRequested::get_profile_events_types ($this->id);
		}
	}
	
	
	/** Checks if the profile data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify the profile name.'); $ret = false;}
		else
		{
			// Check name uniqueness
			$q = 'SELECT id FROM '.TBL_MONITOR_PROFILES.' WHERE name="'.mysql_escape_string($this->name).'" ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			$q.= 'LIMIT 1';
			if (DB::db_fetch_field ($q, 'id')) {error_msg ('This name is already in use for another profile.'); $ret = false;}
		}
		
		return $ret;
	}
	
	
	/**
	* Saves the profile data, 
	*/
	function save_data ()
	{
		parent::save_data();
		if ($this->id)
		{
			// Get the current list of profile items from database to see if there are any changes
			$q = 'SELECT item_id, update_interval FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE profile_id='.$this->id;
			$list_update = $this->db_fetch_list ($q);
			$q = 'SELECT item_id, log_type FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE profile_id='.$this->id;
			$list_log = $this->db_fetch_list ($q);
			
			// Save only the changes that have been made
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
					$this->db_query ('DELETE FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE profile_id='.$this->id.' AND item_id='.$item_id);
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
		parent::load_from_array ($profile_data);
		if ($this->id and !is_null($items_data) and is_array($profile_data['items']) and is_array($items_data))
		{
			$this->items = array();
			foreach ($profile_data['items'] as $id => $v)
			{
			
				if (isset($items_data[$id]))
				{
					$profile_item = new MonitorProfileItem ($this->id, $id);
					$profile_item->profile_id = $this->id;
					$profile_item->item_id = $id;
					$profile_item->update_interval = $items_data[$id]['update_interval'];
					$profile_item->log_type = $items_data[$id]['log_type'];
					$this->items[$id] = $profile_item;
				}
			}
		}
	}
	
	
	/** Returns a string with the default events types to report for a given category */
	function get_events_types_str ($cat_id)
	{
		$ret = '';
		if ($this->id and isset($this->default_events_types_requested[$cat_id]))
		{
			$types = $this->default_events_types_requested[$cat_id];
			foreach ($GLOBALS['EVENTLOG_TYPES'] as $type_id => $type_name)
			{
				if (($types & $type_id) == $type_id) $ret.= $type_name.', ';
			}
			$ret = preg_replace ('/\,\s*$/', '', $ret);
		}
		return $ret;
	}
	
	/** Sets the default types of events to record for this profile.
	* @param	array					$types		Associative array with the types to record. The keys
	*									are categories IDs and the values are the types of events,
	*									specified either as array with type codes or as sum of
	*									type codes (EVENTLOG_* constants).
	*/
	function set_default_events_reporting ($types)
	{
		if ($this->id)
		{
			EventLogRequested::set_profile_default_types ($this->id, $types);
			$this->load_events_settings ();
		}
	}
	
	/** Checks if the profile can be deleted - meaning if no computers are using it */
	function can_delete ()
	{
		$ret = false;
		
		if ($this->id)
		{
			$q_ck = 'SELECT count(*) as cnt FROM '.TBL_COMPUTERS.' WHERE profile_id='.$this->id;
			$cnt = $this->db_fetch_field ($q_ck, 'cnt');
			
			if ($cnt == 0) $ret = true;
			else
			{
				$ret = false;
				error_msg ('This profile can\'t be deleted, there are still computers using it.');
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
			$this->db_query ('DELETE FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE profile_id='.$this->id);
			
			// Delete the list of alerts for the profile
			$this->db_query ('DELETE FROM '.TBL_PROFILES_ALERTS.' WHERE profile_id='.$this->id);
			
			// Delete the object itself
			parent::delete();
		}
	}
	
	
	/** Marks the current profile as being the default one */
	function set_as_default ()
	{
		if ($this->id)
		{
			$this->db_query ('UPDATE '.$this->table.' SET is_default=0 ');
			$this->db_query ('UPDATE '.$this->table.' SET is_default=1 WHERE id='.$this->id);
		}
	}
	
	
	/**
	* Copies an existing profile (with all its attached data) into a new profile
	* @param	string	$new_name		The name for the new profile
	* @return	Profile				The newly created profile
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
				$q = 'INSERT INTO '.TBL_MONITOR_PROFILES_ITEMS.' (profile_id, item_id, update_interval, log_type) ';
				$q.= 'SELECT '.$new_profile->id.' as profile_id, item_id, update_interval, log_type ';
				$q.= 'FROM '.TBL_MONITOR_PROFILES_ITEMS.' p WHERE p.profile_id = '.$this->id;
				$this->db_query($q);
				
				// Copy the alerts definitions
				$alert_ids = array ();
				foreach ($this->alerts as $alert) $alert_ids[] = $alert->id;
				$new_profile->load_data ();
				$new_profile->alerts = array();
				$new_profile->set_alerts ($alert_ids);
				
				// Copy the settings for events logging
				$q = 'INSERT INTO '.TBL_EVENTS_LOG_REQUESTED.' (profile_id, category_id, source_id, types) ';
				$q.= 'SELECT '.$new_profile->id.' as profile_id, category_id, source_id, types ';
				$q.= 'FROM '.TBL_EVENTS_LOG_REQUESTED.' p WHERE p.profile_id = '.$this->id;
				$this->db_query($q);
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
					$this->db_query ('DELETE FROM '.TBL_PROFILES_ALERTS.' WHERE alert_id='.$alert_id.' AND profile_id='.$this->id);
				}
			}
			
			// Then add the alerts which where not set before
			foreach ($alerts as $alert_id)
			{
				if (!in_array($alert_id, $assigned_alerts_ids))
				{
					$this->db_query ('REPLACE INTO '.TBL_PROFILES_ALERTS.'(profile_id,alert_id) VALUES ('.$this->id.','.$alert_id.')');
				}
			}
			
		}
	}
	
	
	/** [Class Method] Returns a list with the monitoring items and their logging settings for a specified profile
	* @param	int			$profile_id	The ID of the profile. If zero or not specified, the values for the default profile are returned
	* @return	array					Associative array, keys being item IDs and the values being the logging settings.
	*/
	public static function get_profile_items_list ($profile_id = 0)
	{
		if (!$profile_id) $profile_id = db::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_PROFILES.' WHERE is_default=1 ', 'id');
		$q = 'SELECT item_id, log_type FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE profile_id='.$profile_id;
		return db::db_fetch_list ($q);
	}
	
	
	/** [Class Method] Returns a list with the monitoring items and their update intervals for a specified profile
	* @param	int			$profile_id	The ID of the profile. If zero or not specified, the values for the default profile are returned
	* @return	array					Associative array, keys being item IDs and the values being their update intervals
	*/
	public static function get_profile_items_intervals ($profile_id = 0)
	{
		if (!$profile_id) $profile_id = DB::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_PROFILES.' WHERE is_default=1 ', 'id');
		$q = 'SELECT item_id, update_interval FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE profile_id='.$profile_id;
		return DB::db_fetch_list ($q);
	}
	
	
	/** [Class Method] Returns the profile which is set as the default one */
	public static function get_default_profile ()
	{
		$ret = null;
		$id = db::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_PROFILES.' WHERE is_default=1 ', 'id');
		if ($id)
		{
			$ret = new MonitorProfile ($id);
			if (!$ret->id) $ret = null;	// Just in case the profile that was marked as default has been deleted
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list with the current profiles objects
	*/
	public static function get_profiles ($filter = array())
	{
		$ret = array();
		
		$q = 'SELECT id FROM '.TBL_MONITOR_PROFILES.' ';
		
		$ids = db::db_fetch_array($q);
		foreach ($ids as $id) $ret[] = new MonitorProfile($id->id);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns an associative array of profile IDs and names
	*/
	public static function get_profiles_list ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id, name FROM '.TBL_MONITOR_PROFILES.' ';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}

	/**
	 * [Class Method] gets a list of profiles which have backup reporting
	 * 
	 * @return array $ret	returns an associative array of profile id's and names 
	 */
	public static function get_profiles_with_backup()
	{
		$ret = array();
		$q = 'SELECT p.id, p.name FROM '.TBL_MONITOR_PROFILES.' p INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS.' mip on p.id = mip.profile_id, monitor_items mi WHERE mip.item_id = mi.id and mi.id in (1044, 2004)';
		$ret = db::db_fetch_list($q); 
		return $ret;
	}
	
	/**
	 * [Class Method] gets a list of profiles which have antivirus reporting
	 * 
	 * @return array $ret	returns an associative array of profile id's and names 
	 */
	public static function get_profiles_with_antivirus()
	{
		$ret = array();
		$q = 'SELECT p.id, p.name FROM '.TBL_MONITOR_PROFILES.' p INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS.' mip on p.id = mip.profile_id, monitor_items mi WHERE mip.item_id = mi.id and mi.id=1025';
		$ret = db::db_fetch_list($q); 
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns the number of computers which use each profile
	* @return	array				Associative array, the keys being profile IDs,
	*						and the values being the number of computers using those profiles
	*/
	public static function get_computers_count ()
	{
		$ret = array ();
		
		$q = 'SELECT profile_id, count(*) as cnt FROM '.TBL_COMPUTERS.' ';
		$q.= 'GROUP BY profile_id ';
		
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
}

?>
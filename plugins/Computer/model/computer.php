<?php

class_load ('MonitorItem');
class_load ('MonitorProfile');
class_load ('ComputerItem');
class_load ('Role');

/**
* Class for representing computers.
*
* This class only stores basic information about computers. The computers
* data reported by Kawacs Agent are stored and manipulated through related
* ComputerItem objects.
*
* Usually the computers are created automatically, when the Kawacs Agent
* reports information about a computer that has not been seen before.
* However, it is also possible to create computers manually through
* the Web interface.
*
* Detecting if a computer is a new one is done based on the MAC address.
* The Agent will report all the MACs on the computer, to cope with the
* situation where one of the interfaces is a dynamic one (e.g. Wi-Fi).
*
* Once the Agent receives confirmation that the computer has been registered
* in the system, it will store in that computer the "reported MAC address"
* and will use that one as means of identification in future connections.
* This allows keeping correct track of computers even if their network
* cards are changed.
*
*/

class Computer extends Base
{
	/** Computer ID
	* @var int*/
	var $id = null;

	/** Customer's ID
	* @var int */
	var $customer_id = null;

	/** The ID of the monitoring profile assigned to this computer
	* @var int */
	var $profile_id = null;

	/** The MAC address used for uniquely identifying a computer
	* @var string */
	var $mac_address = '';

	/** The type of computer - See $GLOBALS['COMP_TYPE_NAMES']
	* @var int */
	var $type = 0;

	/** The current alert level (if any) raised for this computer
	* @var int */
	var $alert = 0;

	/** The number of reporting cycles it missed
	* @var int */
	var $missed_cycles = 0;

	/** The remote IP address of the computer
	* @var string */
	var $remote_ip = '';

	/** If there is any pending request for a full update
	* @var boolean */
	var $request_full_update = false;

	/** Additional comments about the computer
	* @var string */
	var $comments = '';

	/** A flag signaling if the Internet connection for this computer is down - in
	* which case the field contains the ID of the corresponding MonitoredIP object.
	* If the internet connection is OK (or is not monitored), then the field contains 0
	* @var int */
	var $internet_down = 0;

	/** ID of the customer location to which this computer is assigned, if any.
	* @var int */
	var $location_id = 0;

	/** True or fals if the computer is manuall created
	* @var int */
	var $is_manual = false;

	/** The date when the computer was first created
	* @var timestamp */
	var $date_created = 0;

	/** The netbios name of the computer. This is actually a redundancy of
	* the computer item 'netbios_name' (1001), but we accept the redundancy for performance.
	* The field will be updated every time the Agent reports a new value for item 1001
	* @var string */
	var $netbios_name = '';


	/** The asset number of the computer. This is built on-the-fly based on the computer ID and computer type
	* @var string */
	var $asset_no = '';

	/** The assigned profile, if any. Note that this is loaded only on request with load_profile() method
	* @var MonitorProfile */
	var $profile = null;

	/** Specifies if the contact with this computer has been lost - based on the settings
	* for report cycles in the monitor profile or, if those are not set, based on the
	* default contact_lost settings in the .ini file
	* @var bool */
	var $contact_lost = false;

	/** The list of roles assigned to this computer, stores as an associative array, the keys
	* being role IDs and the values role names. Note that this is loaded only request, with load_roles() method
	* @var array */
	var $roles = array ();

	/** The list of photos for this computer. Note that this is loaded only on request, with load_photos() method
	* @var array(CustomerPhoto) */
	var $photos = array ();

	/** The Location object to which this computer belongs, if any. Note that this is loaded on request,
	* with the load_location() method.
	* @var Location */
	var $location = null;


	/** Specifies if default events types to request have been set specifically for this computer, other than the
	* ones defined for the profile
	* @var bool */
	var $has_default_events_settings = false;

	/** Associative array with the default types of computer events to request. Loaded on request with load_events_settings() method.
	* The keys are categories IDs and the values are sums of EVENTLOG_* constants
	* @var array */
	var $default_events_types_requested = array ();

	/** Array with the events types and sources which will be requested from computers, other than the default ones
	* requested through the default types.
	* Loaded on request with the load_events_settings() method
	* @var array(EventLogRequested) */
	var $events_types_requested = array ();


	/** The databas table storing computers data
	* @var string */
	var $table = TBL_COMPUTERS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'customer_id', 'profile_id', 'mac_address', 'type', 'last_contact', 'alert', 'missed_cycles', 'remote_ip', 'request_full_update', 'comments', 'internet_down', 'location_id', 'is_manual', 'date_created', 'netbios_name');


	/**
	* Constructor, also loads the computer data from the database if an ID is specified
	* @param	int $id		The computer id
	*/
	function __construct($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
            $this->verify_access();
		}
	}
	function __destruct()
	{
		if($this->profile) $this->profile = null;
		if($this->roles)
		{
			foreach($this->roles as $rol) $rol=null;
			$this->roles=null;
		}
		if($this->default_events_types_requested) unset($this->default_events_types_requested);
		if($this->events_types_requested) unset($this->events_types_requested);
		if($this->id) $this->id = null;
		if($this->customer_id) $this->customer_id = null;
		if($this->profile_id) $this->profile_id = null;
		if($this->mac_address) $this->mac_address=null;
		if($this->type) $this->type = null;
		if($this->alert) $this->alert = null;
		if($this->missed_cycles) $this->missed_cycles = null;
		if($this->remote_ip) $this->remote_ip = null;
		if($this->request_full_update) $this->request_full_update = null;
		if($this->comments) $this->comments = null;
		if($this->internet_down) $this->internet_down = null;
		if($this->location_id) $this->location_id = null;
		if($this->is_manual) $this->is_manual = null;
		if($this->date_created) $this->date_created = null;
		if($this->netbios_name) $this->netbios_name = null;
		if($this->asset_no) $this->asset_no = null;
		if($this->profile) $this->profile = null;
		if($this->contact_lost) $this->contact_lost = null;
		if($this->location) $this->location = null;
	}

	/** Saves the object data. For new computers will check that the generated ID does not overlap with an ID for RemovedComputer */
	function save_data ()
	{
		if (!$this->id) $is_new = true;
		parent::save_data ();

		if ($is_new and $this->id)
		{
			$q = 'SELECT id FROM '.TBL_REMOVED_COMPUTERS.' WHERE id='.$this->id.' LIMIT 1';
			if (db::db_fetch_field ($q, 'id'))
			{
				// This ID already exists for a RemovedComputer, find the max possible values
				$id_max_computer = db::db_fetch_field ('SELECT max(id) as max FROM '.TBL_COMPUTERS, 'max');
				$id_max_removed = db::db_fetch_field ('SELECT max(id) as max FROM '.TBL_REMOVED_COMPUTERS, 'max');
				$new_id = max ($id_max_computer, $id_max_removed) + 1;

				db::db_query ('UPDATE '.TBL_COMPUTERS.' SET id='.$new_id.' WHERE id='.$this->id);
				$this->id = $new_id;
			}
		}
	}

	/** Loads the computer data from the database */
	function load_data ()
	{
		$ret = false;
		if ($this->id)
		{
			parent::load_data();
			if ($this->id)
			{
				$ret = true;

				// Load the highest alert level from the notifications for this computer
				$this->alert = db::db_fetch_field ('SELECT max(level) as alert FROM '.TBL_NOTIFICATIONS.' WHERE object_id='.$this->id.' AND object_class='.NOTIF_OBJ_CLASS_COMPUTER, 'alert');

				// Check if this is in "lost contact" state. We use direct query of the database in order to avoid
				// the time-consuming operation of loading the entire profile
				if (!$this->is_manual)
				{
					$prof = array ();
					if ($this->profile_id)
					{
						$q = 'SELECT report_interval, alert_missed_cycles FROM '.TBL_MONITOR_PROFILES.' WHERE id='.$this->profile_id;
						$prof = db::db_fetch_row ($q);
					}

					if ($prof['report_interval'] and $prof['alert_missed_cycles'])
					{
						$minimum_contact = intval($prof['report_interval'] * $prof['alert_missed_cycles'] * 60) + 5;
					}
					elseif ($prof['report_interval'])
					{
						$minimum_contact = intval($prof['report_interval'] * DEFAULT_CONTACT_LOST_INTERVAL * 60);
					}
					else
					{
						$minimum_contact = intval (DEFAULT_CONTACT_LOST_INTERVAL * DEFAULT_MONITOR_INTERVAL * 60);
					}
					$this->contact_lost = ((time()-$this->last_contact) > $minimum_contact);
				}
				else $this->contact_lost = false;

				// Build the asset number
				$this->asset_no = get_asset_no_comp ($this->id, $this->type);
			}
		}
		return $ret;
	}


	/** Loads the associated computer profile */
	function load_profile ()
	{
		if ($this->profile_id) $this->profile = new MonitorProfile ($this->profile_id);
	}

	/** Loads the list of roles defined for this computer, if any */
	function load_roles ()
	{
		if ($this->id)
		{
			$this->roles = Role::get_roles_list (array('computer_id' => $this->id));
		}
	}

	/** Loads into the object the settings for computer events reporting for this computer */
	function load_events_settings ()
	{
		if ($this->id)
		{
			$this->has_default_events_settings = EventLogRequested::computer_has_default_settings ($this->id);
			$this->default_events_types_requested = EventLogRequested::get_computer_default_types ($this->id);
			$this->events_types_requested = EventLogRequested::get_computer_events_types ($this->id);
		}
	}

	/** Sets the default types of events to record for this computer. Note that the method will check
	* if this is different than the default setting for the profile, and if they are not different then
	* no save will be performed and a notification will be raised through error_msg ()
	* @param	array					$types		Associative array with the types to record. The keys
	*									are categories IDs and the values are the types of events,
	*									specified either as array with type codes or as sum of
	*									type codes (EVENTLOG_* constants).
	* @return	bool							True or False if the saving was done or not
	*/
	function set_default_events_reporting ($types)
	{
		$ret = false;
		if ($this->id and $this->profile_id)
		{
			// Cleanup the types, if needed
			$types = EventLogRequested::cleanup_default_settings ($types);

			// Compare the received settings with the default ones for the profile
			$profile_types = EventLogRequested::get_profile_default_types ($this->profile_id);
			$no_difference = EventLogRequested::are_defaults_identical ($types, $profile_types);

			if ($no_difference) error_msg ('Note: These settings are the same as the profile\'s settings. No save was performed.');
			else
			{
				EventLogRequested::set_computer_default_types ($this->id, $types);
				$this->load_events_settings ();
				$ret = true;
			}
		}

		return $ret;
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

	/** Sets and saved the list of roles assigned to this computer */
	function set_roles ($roles = array())
	{
		if ($this->id)
		{
			if (!is_array($roles)) $roles = array ();
			// First, delete the old selection
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_ROLES.' WHERE computer_id='.$this->id);
			// Set the new roles, if any
			if (count($roles) > 0)
			{
				foreach ($roles as $role_id)
				{
					$q = 'INSERT INTO '.TBL_COMPUTERS_ROLES.' (computer_id, role_id) VALUES ('.$this->id.','.$role_id.')';
					db::db_query ($q);
				}
			}
		}
	}

	/** Loads the photos for this computer, if any */
	function load_photos ()
	{
		if ($this->id)
		{
			class_load ('CustomerPhoto');
			$this->photos = CustomerPhoto::get_photos (array('object_class'=>PHOTO_OBJECT_CLASS_COMPUTER, 'object_id'=>$this->id));
		}
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

		// This is set during manual creation or editing
		if ($this->is_manual)
		{
			if (!$this->netbios_name) {$ret = false; error_msg ('Please specify the computer name.');}
		}

		if ($this->customer_id<=0) {$ret=false; error_msg('Please specify the customer.');}
		if (!$this->type) {$ret=false; error_msg('Please specify a type.');}
		if (!$this->profile_id) {$ret=false; error_msg('Please specify a profile.');}
		if (!$this->mac_address) {$ret=false; error_msg('Please specify the MAC address.');}
		else
		{
			// Check uniqueness
			$q = 'SELECT id FROM '.TBL_COMPUTERS.' WHERE mac_address="'.db::db_escape($this->mac_address).'" ';
			if ($this->id) $q.= 'AND id<>'.$this->id;
			if (db::db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('This MAC address already exists in our database for another computer.');
			}
		}

		return $ret;
	}

	/** Delete the computer data and all associated information */
	function delete ()
	{
		if ($this->id)
		{
			// Delete any notifications that might be for this computer
			$q = 'DELETE FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_COMPUTER.' AND object_id='.$this->id;
			db::db_query ($q);

			// Delete all the items recorded for this computer
			$q = 'DELETE FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id;
			db::db_query ($q);
			$q = 'DELETE FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE computer_id='.$this->id;
			db::db_query ($q);
			// Note: Shouldn't we also delete older logs and notifications?

			// Delete the records for agent versions for this computer
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_AGENT_VERSIONS.' WHERE computer_id='.$this->id);
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_AGENT_LINUX_VERSIONS.' WHERE computer_id='.$this->id);

			// Delete the records about peripherals connected to this computer
			db::db_query ('DELETE FROM '.TBL_PERIPHERALS_COMPUTERS.' WHERE computer_id='.$this->id);

			// Remove this computer from all the peripherals and AD Printers set to monitor
			db::db_query ('UPDATE '.TBL_PERIPHERALS.' SET snmp_computer_id=0 WHERE snmp_computer_id='.$this->id);
			db::db_query ('UPDATE '.TBL_AD_PRINTERS_EXTRAS.' SET snmp_computer_id=0 WHERE snmp_computer_id='.$this->id);

			// Delete the passwords and remote services for this computer
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_PASSWORDS.' WHERE computer_id='.$this->id);
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_REMOTE_SERVICES.' WHERE computer_id='.$this->id);

			// Delete phone number associations
			$q = 'UPDATE '.TBL_ACCESS_PHONES.' SET object_id=0 ';
			$q.= 'WHERE object_id='.$this->id.' AND device_type='.PHONE_ACCESS_DEV_COMPUTER;
			db::db_query ($q);

			// Delete the blackouts and tracking of updating
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_BLACKOUTS.' WHERE computer_id='.$this->id);
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_UPDATING.' WHERE computer_id='.$this->id);

			// Delete all tickets objects relating to this computer
			$q = 'DELETE FROM '.TBL_TICKETS_OBJECTS.' WHERE object_id='.$this->id.' AND object_class='.TICKET_OBJ_CLASS_AD_COMPUTER;
			db::db_query ($q);

			// Delete the Plink information for this computer
			db::db_query ('DELETE FROM '.TBL_PLINK.' WHERE computer_id='.$this->id);
			db::db_query ('DELETE FROM '.TBL_PLINK_SERVICES.' WHERE computer_id='.$this->id);

			// Delete all photos for this computer
			$q = 'DELETE FROM '.TBL_CUSTOMERS_PHOTOS.' WHERE object_id='.$this->id.' AND object_class='.PHOTO_OBJECT_CLASS_COMPUTER;
			db::db_query ($q);

			// Delete all roles and notes
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_ROLES.' WHERE computer_id='.$this->id);
			db::db_query ('DELETE FROM '.TBL_COMPUTERS_NOTES.' WHERE computer_id='.$this->id);

			// Delete the computer from the list of valid duplicate names
			db::db_query ('DELETE FROM '.TBL_VALID_DUP_NAMES.' WHERE computer_id='.$this->id);

			// Delete all references to discovered devices
			db::db_query ('UPDATE '.TBL_DISCOVERIES.' SET matched_obj_id=0 WHERE matched_obj_id='.$this->id.' AND matched_obj_class='.SNMP_OBJ_CLASS_COMPUTER);
			db::db_query ('UPDATE '.TBL_DISCOVERIES_SETTINGS_DETAILS.' SET computer_id=0 WHERE computer_id='.$this->id);

			// Delete the computer object itself
			parent::delete ();
		}
	}

	/** Changes the customer to which a computer is assigned. It will check if the customer ID is valid and raise an error if not
	* @return	bool					True or False if the change was done or not
	*/
	function set_customer ($customer_id)
	{
		$ret = false;
		if ($this->id)
		{
			$customer = new Customer ($customer_id);
			if ($customer->id)
			{
				$this->customer_id = $customer_id;
				$this->save_data ();
				$ret = true;
			}
			else
			{
				error_msg ('Please specify a valid customer');
			}
		}
		return $ret;
	}

	/**
	* Marks that a contact from the KAWACS Agent has been received
	* It also clears the alert if there was any, and sets the name, remote_ip and creation date if
	* new values have been reported
	*/
	function contact_made ($remote_ip = '', $netbios_name = '')
	{
		if ($this->id)
		{
			$netbios_name = trim($netbios_name);

			// Update the computer information
			$q = 'UPDATE '.$this->table.' SET last_contact = '.time().', alert=0, missed_cycles=0 ';

			if ($remote_ip and ($this->remote_ip!=$remote_ip or $this->remote_ip==''))
				$q.= ',remote_ip="'.db::db_escape($remote_ip).'" ';
			if ($netbios_name and ($this->netbios_name!=$netbios_name or $this->netbios_name==''))
				$q.= ',netbios_name="'.db::db_escape($netbios_name).'" ';
			if (!$this->date_created)
				$q.= ',date_created='.time().' ';

			$q.= 'WHERE id='.$this->id;
			db::db_query($q);
			
			//do_log("computer ".$this->id." made contact");
			//$bStolen = Computer::is_computer_stolen($this->id);
			//if($bStolen){
			//	do_log("this one is marked stolen");
			//	$this->raise_stolen_reporting_alert();
			//}
		}
	}
	
	 function check_stolen()
	 {
	 	//do_log("in check stolen");
	 	$bStolen = Computer::is_computer_stolen($this->id);
        if($bStolen){
          //do_log("this one is marked stolen");
          $this->raise_stolen_reporting_alert();
        }
	 }
	

	/**
	* Checks if an update is needed for this computer.
	* @param	array	$comp_data		Array containing the versions for the current file
	* @return	array				Array with the files needing updates
	*/
	function check_update_needed ($comp_data = array())
	{
		$ret = array();

		if (!isset($comp_data['version_linux_agent']))
		{
			# This is a request from a Windows computer
			class_load ('KawacsAgentUpdate');
			$comp_versions = array(
				FILE_NAME_AGENT => $comp_data['version_agent'],
				FILE_NAME_LIB => $comp_data['version_library'],
				FILE_NAME_KAWACS => $comp_data['version_kawacs'],
				FILE_NAME_MANAGER => $comp_data['version_manager'],
				FILE_NAME_ZIPDLL => $comp_data['version_zipdll']
			);

			if ($this->id)
			{
				// Save to the database the details about the current versions
				$q = 'REPLACE INTO '.TBL_COMPUTERS_AGENT_VERSIONS.' VALUES ';
				$q.= '('.$this->id.', '.FILE_NAME_AGENT.', "'.db::db_escape($comp_versions[FILE_NAME_AGENT]).'"), ';
				$q.= '('.$this->id.', '.FILE_NAME_LIB.', "'.db::db_escape($comp_versions[FILE_NAME_LIB]).'"), ';
				$q.= '('.$this->id.', '.FILE_NAME_KAWACS.', "'.db::db_escape($comp_versions[FILE_NAME_KAWACS]).'"), ';
				$q.= '('.$this->id.', '.FILE_NAME_MANAGER.', "'.db::db_escape($comp_versions[FILE_NAME_MANAGER]).'"), ';
				$q.= '('.$this->id.', '.FILE_NAME_ZIPDLL.', "'.db::db_escape($comp_versions[FILE_NAME_ZIPDLL]).'") ';
				db::db_query ($q);
			}

			$current_release = KawacsAgentUpdate::get_current_release($this->id);

			foreach ($comp_versions as $file_id => $version)
			{
				if ($current_release->is_lower_version($version, $file_id))
				{
					$ret[] = array (
						'zip_name' => $GLOBALS['KAWACS_AGENT_FILES'][$file_id].'.zip',
						'file_name' => $GLOBALS['KAWACS_AGENT_FILES'][$file_id],
						'url' => 'http://'.$current_release->files[$file_id]->get_download_url(),
						'md5_checksum' => $current_release->files[$file_id]->md5
					);
				}
			}
		}
		else
		{
			# This is a request from a Linux computer
			class_load ('KawacsAgentLinuxUpdate');


			// Save to the database the details about the current versions
			$q = 'REPLACE INTO '.TBL_COMPUTERS_AGENT_LINUX_VERSIONS.' VALUES ('.$this->id.', "'.$comp_data['version_linux_agent'].'") ';
			db::db_query ($q);

			$current_release = KawacsAgentLinuxUpdate::get_current_release ();

			if ($current_release->is_lower_version ($comp_data['version_linux_agent']))
			{
				$ret[] = array (
					'zip_name' => FILE_NAME_KAWACS_INSTALLER_LINUX,
					'url' => $current_release->get_download_url(),
					'md5_checksum' => $current_release->md5
				);
			}
		}

		return $ret;
	}


	/**
	* Converts a value received from a Kawacs Agent into the value that will be stored in the database.
	* For "File" type fields it will also create the needed file with the file content and will
	* return the MD5 checksum of the file's content.
	*
	* @param 	int	$type			The type of the value
	* @param	mixed	$value			The value to convert
	* @param	int	$item_id		The ID of the item, required for 'File' items
	* @param	int 	$field_name		The short name of the field, needed for files - when the
	*						value is part of a structure
	*/
	function get_storing_value ($type, $value, $item_id = null, $field_name = '')
	{
		$ret = $value;

		switch ($type)
		{
			case MONITOR_TYPE_DATE:
				// The values reported by Linux Agent from a omreport have a format
				// which is not supported by all PHP versions, make sure the time
				// is at the beginning of the string.

				if (!is_numeric($value))
				{
					$m = array();
					if (preg_match('/^(.*[^0-9]+)([0-9]+\:[0-9]+\:[0-9]+\s)(.*)$/', $value, $m))
					{
						$value = $m[2].' '.$m[1].' '.$m[3];
					}
					$ret = strtotime ($value);
				}
				else
				{
					$ret = $value;
				}

				#$ret = (is_numeric($value) ? $value : strtotime($value));
				break;

			case MONITOR_TYPE_FILE:
				$fname = DIR_MONITOR_ITEMS_FILE.'/'. $this->id.'_'.$item_id.'_'.$field_name;

				$value = base64_decode ($value);

				// The MD5 is appended to the name in order to different between files in case this is for an item that
				// requires logging.
				$fname.= '_'.md5($value);

				$ret = basename($fname);

				if ($fname)
				{
					$fp = @fopen ($fname.'.gz', 'w');
					if ($fp)
					{
						fwrite ($fp, $value);
						fclose ($fp);
						//flush ();

						// Pass it through gzip, in case it was compressed
						$fp = gzopen ($fname.'.gz', 'rb');
						if ($fp)
						{
							$fw = @fopen ($fname, 'w');
							if ($fw)
							{
								while (($s = gzread($fp, 400000)))
								{
									fwrite ($fw, $s);
								}
								fclose ($fw);
							}
							@fclose ($fp);
							@unlink ($fname.'.gz');
						}
						elseif (file_exists($fname.'.gz'))
						{
							@rename ($fname.'.gz', $fname.'.gz');
						}
					}
				}
				break;
		}

		return $ret;
	}


	/** Takes an array of items data as received by kawacs.php from a script and converts it
	* to the format needed by Computer::add_reported_items */
	function translate_direct_data ($data = array ())
	{
		$items = array ();
		foreach ($data['items'] as $item_id => $vals)
		{
			$item = array (
				'id' => $item_id,
				'value' => array ()
			);
			foreach ($vals as $val)
			{
				$item_val = array ();
				foreach ($val as $k=>$v)
				{
					$item_val ['field_names'][] = $k;
					$item_val ['field_values'][] = $v;
				}
				$item['value'][] = $item_val;
			}
			$items[] = $item;
		}
		return $items;
	}


	/**
	* Adds a set of monitor item values that have been reported by a KAWACS agent
	*
	* This method uses direct writing to the database (as opposed to using objects)
	* in order to increase the updating speed.
	*
	* @param	array	$items		The list of reported items, as receivced by SOAP server
	*/
	function add_reported_items ($items = array())
	{
		class_load ('ComputerReporting');
		$q_vals = '';
		$time = time();

		//xxxxx TESTING AND Debugging
		//$test_file = dirname(__FILE__).'/../../logs/testlog_addReportedItems';
		//$fp = fopen ($test_file, 'w');
		//fwrite($fp, "after computers_items_log insert ".$time."\n\n");
		//fclose($fp);

		///XXXXXXXXXXXXXXXXXX

		do_log("\nREPORTED ITEMS: ".$this->id."\n");
		// Make sure the customer for this computer is not disable, doesn't have KAWACS or is on-hold
		$q = 'SELECT active, has_kawacs, onhold FROM '.TBL_CUSTOMERS.' WHERE id='.$this->customer_id;
		$cust_data = db::db_fetch_array ($q);
		$cust_data = $cust_data[0];

		if ($cust_data->active and $cust_data->has_kawacs and !$cust_data->on_hold)
		{
			// Mark that the computer is doing updates, so it is ignored when checking for notifications
			db::db_query ('UPDATE '.TBL_COMPUTERS_UPDATING.' SET update_time='.time().' WHERE computer_id='.$this->id);

			// Compose the list of item IDs which are required by this computer's profile (or the default profile if no profile assigned)
			$required_items = MonitorProfile::get_profile_items_list ($this->profile_id);
			$update_ids = array ();
			$update_ids_time_only = array ();
			$name_reported = false; // Will contain the reported Netbios name, if it was reported

			// XXXX TEMP ONLY!!!!!!
			// If AD Computers are reported (#1030), break the values into items #1046 and #1047
			$idx = -1;
			for ($i=0; ($i<count($items) and $idx < 0); $i++) if ($items[$i]['id'] == 1030) $idx = $i;
			if ($idx >=0)
			{
				$tmp = $items[$idx];
				$tmp['id'] = '1046'; $items[] = $tmp;
				$tmp['id'] = '1047'; $items[] = $tmp;
				$items[$idx] = array ();
			}
			/// XXXX End temp section

			// Special processing of items reporting Events Log
			$idx = -1;
			for ($i=0, $i_max=count($items); $i<$i_max; $i++) if ($items[$i]['id'] == EVENTS_ITEM_ID) {$idx = $i; break;}
			if ($idx >= 0)
			{
				ComputerReporting::process_item_events ($this, $items[$idx]);
				array_splice ($items, $idx, 1); // Remove the item from the array so it is not processed further
			}

			// Special processing of items reporting network discoveries - note that for there can be more than 1 such items
			for ($i=0; $i<count($items); $i++)
			{
				if ($items[$i]['id'] == DISCOVERY_ITEM_ID)
				{
					ComputerReporting::process_item_discoveries ($this, $items[$i]);
					array_splice ($items, $i--, 1);
				}
			}

			// Special processing for SNMP items collected about peripherals
			//$collected_periphs = array (); // Will keep track of the the peripherals for which this computer collected SNMP items
			for ($i=0; $i<count($items); $i++)
			{
				if ($items[$i]['obj_class'] > SNMP_OBJ_CLASS_COMPUTER)
				{
					ComputerReporting::process_item_periph ($this, $items[$i]);
					array_splice ($items, $i--, 1);
				}
			}

			// Normal processing of computer reported items
			for ($i=0; $i<count($items); $i++)
			{
				$q_val_item = '';
				$q_val_item_log = '';
				$needed_item = false;	// Will tell if this item is actually needed by this computer
				$record_item = false;	// Will tell if this reported item will be actually saved in computers_items table or if only the time needs updating

				$new_values_arr = array (); // Will be used for comparing with existing values, to see if logging is needed

				$item_id = $items[$i]['id'];

				// XXX: For Agent versions prior to 2.0.1.0, there was a typo in the network mask field,
				// it was sent as 'mak' instead of 'mask', so fix this. When all computers are migrated
				// to version 2.0.1.0 or higher, this piece of code can be removed
				if ($item_id == NET_ADAPTERS_ITEM_ID)
				{
					for ($im=0; $im<count($items[$i]['value']); $im++)
					{
						$pos_mask = array_search('mak', $items[$i]['value'][$im]['field_names']);
						if ($pos_mask !== false) $items[$i]['value'][$im]['field_names'][$pos_mask] = 'mask';
					}
				}

				if ($item_id and isset($required_items[$item_id]))
				{
					$needed_item = true;
					$record_item = true;

					// For computers that don't have an assigned profile there will be no logging
					if (!$this->profile_id) $need_logging = false;
					else $need_logging = ($required_items[$item_id]==MONITOR_LOG_ALL or $required_items[$item_id]==MONITOR_LOG_CHANGES);

					$monitor_item = new MonitorItem ($item_id);
					$max_values = ($monitor_item->multi_values == MONITOR_MULTI_NO ? 1 : count ($items[$i]['value']));

					switch ($monitor_item->type)
					{
						case MONITOR_TYPE_INT:
						case MONITOR_TYPE_STRING:
						case MONITOR_TYPE_TEXT:
						case MONITOR_TYPE_FLOAT:
						case MONITOR_TYPE_DATE:
						case MONITOR_TYPE_FILE:

							for ($j=0; $j<$max_values; $j++)
							{
								$val = $this->get_storing_value ($monitor_item->type, $items[$i]['value'][$j]['field_values'][0], $item_id);
								$q_val_item.= '('.$this->id.','.$item_id.','.$j.',0,"'.db::db_escape($val).'",'.$time.'), ';
								$new_values_arr[$j][0] = $val;
							}
							break;

						case MONITOR_TYPE_STRUCT:

							for ($j=0; $j<$max_values; $j++)
							{
								// Set the field values for easier access
								$field_values = array();
								for ($k=0; $k<count($items[$i]['value'][$j]['field_names']); $k++)
								{
									foreach ($monitor_item->struct_fields as $field)
									{
										if ($field->short_name == $items[$i]['value'][$j]['field_names'][$k])
										{
											// Pass all fields values through the filters
											$field_values [$field->id] = $this->get_storing_value($field->type, $items[$i]['value'][$j]['field_values'][$k], $item_id, $field->short_name);
										}
									}
								}

								foreach ($field_values as $key => $val)
								{
									$q_val_item.= '('.$this->id.','.$item_id.','.$j.','.$key.',';
									$q_val_item.= '"'.db::db_escape($val).'",'.$time.'), ';

									$new_values_arr[$j][$key] = $val;
								}
                                if($field_values) $field_values = null;
							}
							break;
					}
				}

				// Check if a name has been reported
				if ($item_id==NAME_ITEM_ID){
					$name_reported = $val;
					$this->contact_made('', $name_reported);
				}

				// Check if there is anything needing logging.
				if ($need_logging)
				{
					$record_item = false;	// The item will be recorded for the computer only if it is also logged,
								// otherwise only the reported time will be updated, since there were no changes.
					$q_val_item_log = trim (preg_replace ('/,\s*$/', '', $q_val_item));

					if ($q_val_item_log != '')
					{
						$q_val_item_log = 'INSERT INTO '.TBL_COMPUTERS_ITEMS_LOG.' (computer_id, item_id, nrc, field_id, value, reported) VALUES '.$q_val_item_log;

						if ($required_items[$item_id] == MONITOR_LOG_ALL)
						{
							// Log all values
							db::db_query ($q_val_item_log);
							$record_item = true;
						}
						else
						{
							// Check if there were previously logged values. Using "Limit 1" instead of "count()" for speed
							$q_ck_log = 'SELECT computer_id FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE ';
							$q_ck_log.= 'computer_id='.$this->id.' AND item_id='.$item_id.' LIMIT 1';
							$existing_log = db::db_fetch_field ($q_ck_log, 'computer_id');

							if ($existing_log)
							{
								// Log only changes, so check if current values are different
								$q_ck_changes = 'SELECT nrc, field_id, value FROM '.TBL_COMPUTERS_ITEMS.' WHERE ';
								$q_ck_changes.= 'computer_id='.$this->id.' AND item_id='.$item_id.' ORDER BY nrc ';

								$arr = db::db_fetch_array ($q_ck_changes);
								$old_values_arr = array();
								foreach ($arr as $old_val) $old_values_arr[$old_val->nrc][$old_val->field_id] = $old_val->value;

								if ($this->are_values_changed ($monitor_item, $old_values_arr, $new_values_arr))
								{
									// There are changes, so log the item
									db::db_query ($q_val_item_log);
									// Since there are changed
									$record_item = true;
								}
                                if($arr) $arr = null;
                                if($old_values_arr) $old_values_arr = null;
							}
							else
							{
								// There hasn't been any logs before, so save it
								db::db_query ($q_val_item_log);
								$record_item = true;
							}
						}
						//if($q_val_item_log) unset($q_val_item_log);
					}
				}

				if ($needed_item)
				{
					// At this point, all items without logging and all items which log all values have, by default, $record_item set to True.
					// The items logging only changes will have $record_item True only if there has been a change.
					if (!$record_item) $update_ids_time_only[] = $monitor_item->id;
					else
					{
						$q_vals.= $q_val_item;
						$update_ids[] = $monitor_item->id;
					}
				}
			}
            
			$q_vals = trim (preg_replace ('/,\s*$/', '', $q_vals));

			// First, record the items which have actually changed
			if ($q_vals)
			{
				if (count($update_ids) > 0)
				{
					// Delete first previous values for reported IDs
					$q_del = 'DELETE FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id.' AND (';
					foreach ($update_ids as $id) $q_del.= 'item_id='.$id.' OR ';
					$q_del = preg_replace ('/OR\s*$/', '', $q_del).')';
					db::db_query($q_del);
				}

				$q_vals = 'REPLACE INTO '.TBL_COMPUTERS_ITEMS.' (computer_id, item_id, nrc, field_id, value, reported) VALUES '.$q_vals;
				db::db_query ($q_vals);				
			}

			// Then update the report time for the items which haven't changed
			if (count($update_ids_time_only) > 0)
			{
				$q_upd = 'UPDATE '.TBL_COMPUTERS_ITEMS.' SET reported='.$time.' WHERE computer_id='.$this->id.' AND (';
				foreach ($update_ids_time_only as $id) $q_upd.= 'item_id='.$id.' OR ';
				$q_upd = preg_replace ('/OR\s*$/', '', $q_upd).')';

				db::db_query($q_upd);				
			}
            
			// Update the heartbeat (contact time);
			$this->contact_made('', $name_reported);

			// Mark that updating has ended
			db::db_query ('UPDATE '.TBL_COMPUTERS_UPDATING.' SET update_time=0 WHERE computer_id='.$this->id);
            
            //deallocate memory
            if($required_items) $required_items = null;
            if($update_ids) $update_ids = null;
            if($update_ids_time_only) $update_ids_time_only = null;
		}
                do_log("\nEND REPORTED ITEMS: ".$this->id."\n");
        if($cust_data) $cust_data = null;
        //unset the items for this call -- outside this function call the items will remain intact
        unset($items);
		
	}


	/** Called from add_reported_items(), to determine if a newly reported value is different than
	* the last one, to determine if it needs logging or not.
	* @param	MonitorItem		$monitor_item		The monitor item definition for which the check is done
	* @param	array			$old_values		Array with the old reported values, containing associative
	*								arrays in which the keys are field item IDs and the values
	*								are the reported values stored in the system.
	* @param	array			$new_values		Array with the newly reported values, with the same structure
	*								as $old_values.
	* @return	bool						True or False if the values have changed or not.
	*/
	function are_values_changed ($monitor_item, $old_values, $new_values)
	{
		$ret = true;

		// There is no point in doing more sofisticated checks if the values contain different number of elements
		if (count($old_values) == count($new_values))
		{
			// If this is a structure item, check if any of the fields have a change treshold defined
			// $treshold_fields: associative array, keys are field items IDs and the values are the treshold (in bytes)
			$treshold_fields = array ();
			if ($monitor_item->type == MONITOR_TYPE_STRUCT and $monitor_item->main_field_id > 0)
			{
				for ($j=0; $j<count($monitor_item->struct_fields); $j++)
				{
					$fld = &$monitor_item->struct_fields[$j];
					if ($fld->type == MONITOR_TYPE_MEMORY and $fld->treshold > 0)
					{
						$treshold_fields[$fld->id] = $fld->treshold * pow (1024, ($fld->treshold_type-1));
					}
				}
			}

			if (count($treshold_fields) == 0)
			{
				// There are no treshold fields, use simple comparison
				$ret = ($old_values != $new_values);
			}
			else
			{
				// There are treshold fields, make sure to make proper comparisons, using also the 'main_field'
				$has_changed = false;
				$main_field_id = $monitor_item->main_field_id;
				for ($i=0; ($i<count($old_values) and !$has_changed); $i++)
				{
					// For each values set from $old_values, locate the corresponding values set in $new_values,
					// using the 'main_field' as comparison
					$main_val = $old_values[$i][$main_field_id];
					$main_found = false;
					for ($j=0; ($j<count($new_values) and !$main_found); $j++)
					{
						$main_found = ($main_val == $new_values[$j][$main_field_id]);
					}
					if (!$main_found)
					{
						// The 'main fields' are not the same in the old and new value, so something is definetly changed
						$has_changed = true;
					}
					else
					{
						$old_set = &$old_values[$i];
						$new_set = &$new_values[$j-1];

						// For each of the treshold fields, replace in the arrays the new values with the new ones,
						// if the treshold is not exceeded
						foreach ($treshold_fields as $fld_id => $limit)
						{
							if (abs($new_set[$fld_id] - $old_set[$fld_id]) < $limit) $new_set[$fld_id] = $old_set[$fld_id];
						}

						// Finally, use standard comparison between the arrays
						$has_changed = ($old_set != $new_set);
					}
				}

				$ret = $has_changed;
			}
            
            if($treshold_fields) $treshold_fields = null;
		}

		return $ret;
	}

	/**
	* Returns an array of monitor items according to this computer's monitor profile
	*/
	function get_reported_items ()
	{
		$ret = array();
		if ($this->id)
		{
			$items_list = MonitorProfile::get_profile_items_list ($this->profile_id);

			// We use this method in order to get the items ordered by category
			$items = db::db_fetch_vector ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE parent_id=0 ORDER BY category_id, id ');

			foreach ($items as $item_id)
			{
			// XXXX Temporary only, until 1030 is replaced in Agent with 1046 and 1047
				if ($item_id<>1030)
				{
					if (isset($items_list[$item_id]))
					{
						$ret[] = new ComputerItem($this->id, $item_id);
					}
				}
			}

			// Check the items for which there are logs
			$q = 'SELECT DISTINCT item_id FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE computer_id='.$this->id;
			$logged_items = db::db_fetch_vector ($q);

			for ($i=0; $i<count($ret); $i++)
			{
				$item_id = $ret[$i]->item_id;
				if ($items_list[$item_id]==MONITOR_LOG_CHANGES or $items_list[$item_id]==MONITOR_LOG_ALL)
				{
					$ret[$i]->log_enabled = true;
					$ret[$i]->has_logs = in_array ($item_id, $logged_items);
				}
			}
			$items = null;
			$logged_items = null;
			$items_list = null;
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
                $ret['computer_model'] = self::get_item('computer_model', $computer_id);
                $ret['computer_sn'] = self::get_item('computer_sn', $computer_id);
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
	public static function get_item ($item_name = '', $computer_id = null)
	{
		$ret = null;
		//$computer_id = ($computer_id ? $computer_id : $this->id);
        $computer_id = (!empty($computer_id)) ? $computer_id : null;

		if ($item_name and $computer_id)
		{
			$id = db::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE short_name = "'.$item_name.'"', 'id');
			$item = new ComputerItem ($computer_id, $id);
			if ($item->itemdef->multi_values == MONITOR_MULTI_NO)
			{
				$ret = $item->val[0]->value;
			}
			else
			{
				for ($i=0; $i<count($item->val); $i++) $ret[] = $item->val[$i]->value;
			}
            if($item) $item = null;
		}
		return $ret;
	}

    public static function get_item_ex($item_name, $computer_id){
        $ret = null;

        if ($item_name and $computer_id)
        {
            $id = db::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE short_name = "'.$item_name.'"', 'id');
            $item = new ComputerItem($computer_id, $id);
            if ($item->itemdef->multi_values == MONITOR_MULTI_NO)
            {
                $ret = $item->val[0]->value;
            }
            else
            {
                for ($i=0; $i<count($item->val); $i++) $ret[] = $item->val[$i]->value;
            }
            if($item) $item = null;
        }
        return $ret;
    }

	function get_formatted_item ($item_name = '', $computer_id = null)
	{
		$ret = null;
		$computer_id = ($computer_id ? $computer_id : $this->id);

		if ($item_name and $computer_id)
		{
			if (method_exists($this, 'db_fetch_field'))
				$id = db::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE short_name = "'.$item_name.'"', 'id');
			else
				$id = db::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE short_name = "'.$item_name.'"', 'id');

			$item = new ComputerItem ($computer_id, $id);

			if ($item->itemdef->multi_values == MONITOR_MULTI_NO)
			{
				$ret = $item->get_formatted_value(0);
			}
			else
			{
				for ($i=0; $i<count($item->val); $i++)
				{
					$s = array();
					if(is_array($item->val[$i]->value))
					{
					foreach($item->val[$i]->value as $k=>$v)
					{
						$s[$k] = $item->get_formatted_value($i, $k);
					}
					$ret[] = $s;
					}
					else{
						$ret[] = $item->val[$i]->value;
					}

				}
			}
            if($item) $item = null;
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
			$ret = new ComputerItem ($this->id, $item_id);
		}
		return $ret;
	}


	/** Returns the numeric ID for an item name. Can be called as class method or object method */
    public static function get_item_id ($item_name, $parent_id = '')
	{
		$ret = '';
		$q = 'SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE short_name="'.db::db_escape($item_name).'" ';
		if ($parent_id) $q.= ' AND parent_id="'.$parent_id.'" ';


		$ret = db::db_fetch_field ($q, 'id');		// Called as class method

		return $ret;
	}


	/** Returns the last user that logged in on this computer */
	function get_last_login ()
	{
		$ret = '';

		if ($this->id)
		{
			// Try first in the current items
			$q = 'SELECT max(reported), value FROM '.TBL_COMPUTERS_ITEMS.' ';
			$q.= 'WHERE computer_id='.$this->id.' AND item_id='.CURRENT_USER_ITEM_ID.' and value<>"" GROUP BY computer_id';
			$ret = db::db_fetch_field ($q, 'value');

			if (!$ret)
			{
				// Try the logs
				$q = 'SELECT max(reported), value FROM '.TBL_COMPUTERS_ITEMS_LOG.' ';
				$q.= 'WHERE computer_id='.$this->id.' AND item_id='.CURRENT_USER_ITEM_ID.' and value<>"" GROUP BY computer_id';
				$ret = db::db_fetch_field ($q, 'value');
			}
		}

		return $ret;
	}


	/**
	* Returns an array with the data logged for a specific item
	* @param	int	$item_id	The item for which the list of logs is requested
	* @param	array	$filter		Criterias for filtering the results
	* @param	int	$items_count	(By referrence) If set, it will be loaded with the total
	*					number of log items in the database.
	*/
	function get_logged_data ($item_id, $filter = array(), &$items_count)
	{
		$ret = array();
		if ($this->id and $item_id)
		{
			$page = ($filter['page'] ? $filter['page'] : 0);
			$per_page = ($filter['per_page'] ? $filter['per_page'] : 30);

			if ($filter['month']) $tbl = TBL_COMPUTERS_ITEMS_LOG.'_'.$filter['month'];
			else $tbl = TBL_COMPUTERS_ITEMS_LOG;

			$q = 'SELECT DISTINCT reported FROM '.$tbl.' WHERE ';
			$q.= 'computer_id='.$this->id.' AND item_id='.$item_id.' ';
			$q.= 'ORDER BY reported DESC ';
			$q.= 'LIMIT '.$page.', '.$per_page;
			
            $raw_results = db::db_fetch_array($q);
            
			$item = new MonitorItem ($item_id);
			$fld_names = array();
			$fld_short_names = array();

			if (!empty($item->struct_fields))
			{
				foreach ($item->struct_fields as $fld)
				{
					$fld_names[$fld->id] = $fld->name;
					$fld_short_names[$fld->id] = $fld->short_name;
				}
			}
			else
			{
				$fld_names[] = $item->name;
				$fld_short_names[] = $item->short_name;
			}

			for ($i=0; $i<count($raw_results); $i++)
			{
				// Normally these fields could have been set direcly by the object,
				// but since the list can be quite long we pre-load them, in order
				// to increase processing speed
				$comp_item = new ComputerItem ();
				$comp_item->computer_id = $this->id;
				$comp_item->item_id = $item_id;
				$comp_item->fld_names = $fld_names;
				$comp_item->fld_short_names = $fld_short_names;
				$comp_item->itemdef = $item;
				$comp_item->load_from_log ($raw_results[$i]->reported, $filter['month']);
				$ret[] = $comp_item;
                
                $comp_item = null;
			}

			if (isset($items_count))
			{
				$q_count = 'SELECT count(*) AS cnt FROM '.$tbl.' WHERE ';
				$q_count.= 'computer_id='.$this->id.' AND item_id='.$item_id.' AND nrc=0 ';
				$q_count.= 'GROUP BY nrc, field_id ';
				$items_count = db::db_fetch_field ($q_count, 'cnt');
			}
            
            if($raw_results) $raw_results = null;
            if($item) $item = null;
            if($fld_names) $fld_names = null;
            if($fld_short_names) $fld_short_names = null; 
		}
		return $ret;
	}



	/** Deletes the logged data for a certain item of this computer */
	function clear_logged_data ($item_id)
	{
		if ($this->id and $item_id)
		{
			$q = 'DELETE FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE ';
			$q.= 'computer_id='.$this->id.' AND item_id='.$item_id;
			db::db_query ($q);
		}
	}


	/**
	* Returns the items which need to be reported by the computer at the current moment.
	* Determining this is based on the items update interval from the profile and the
	* time of the last report for each item
	* IMPORTANT NOTE: This does NOT return SNMP items, which have special treatment due
	* to OIDs and are fetched using get_needed_items_snmp() method
	* @param	mixed	$full_update		When set to anything else than False, it will force the
	*						server to request an update of all the monitoring items for the assigned profile
	* @return	array				Array with the monitoring item IDs which are needed from the computer
	*/
	function get_needed_items ($full_update = false)
	{
		$ret = array();
		if ($full_update) do_log ('Full update requested for computer # '.$this->id, LOG_LEVEL_TRACE);

		// Only active customers
		$q = 'SELECT active, has_kawacs, onhold FROM '.TBL_CUSTOMERS.' WHERE id='.$this->customer_id;
		$cust_data = db::db_fetch_array ($q);
		$cust_data = $cust_data[0];

		if ($this->id and ($cust_data->active and $cust_data->has_kawacs and !$cust_data->on_hold))
		{
			$profile_items = MonitorProfile::get_profile_items_list ($this->profile_id);
			$profile_items_intervals = MonitorProfile::get_profile_items_intervals ($this->profile_id);

			// Fetch the list with the last reported times for each item of this computer
			// Using direct database access for speed
			$q = 'SELECT DISTINCT item_id, reported FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id.' ';
			$q.= 'AND ((item_id>='.ITEM_ID_COLLECTED_MIN.' AND item_id<='.ITEM_ID_COLLECTED_MAX.') OR ';
			$q.= '(item_id>='.ITEM_ID_EVENTS_MIN.' AND item_id<='.ITEM_ID_EVENTS_MAX.'))';
			$items_reported = db::db_fetch_list ($q);

			// Fetch the list of SNMP items IDs (parents only)
			$snmp_ids = db::db_fetch_vector ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE is_snmp=1 and parent_id=0');

			if ($full_update)
			{
				foreach ($profile_items as $item_id=>$log_type)
				{
					if (!in_array($item_id,$snmp_ids) and (($item_id>=ITEM_ID_COLLECTED_MIN and $item_id<=ITEM_ID_COLLECTED_MAX) or
					($item_id>=ITEM_ID_EVENTS_MIN and item_id<=ITEM_ID_EVENTS_MAX))) $ret[] = $item_id;
				}
			}
			else
			{
				$time_now = time ();

				/// XXXX Special handling of item 1030, which is in fact divided into 1046 and 1047
				if (isset($items_reported[1046]) or isset($items_reported[1047]))
				{
					$items_reported['1030'] = max ($items_reported[1046], $items_reported[1046]);
				}

				foreach ($profile_items as $item_id=>$log_type)
				{
					// Select only automatically collected items
					if (($item_id>=ITEM_ID_COLLECTED_MIN and $item_id<=ITEM_ID_COLLECTED_MAX) or
					($item_id>=ITEM_ID_EVENTS_MIN and item_id<=ITEM_ID_EVENTS_MAX))
					{
						$elapsed = ($time_now - $items_reported[$item_id])/60; // minutes
						if ($elapsed >= $profile_items_intervals[$item_id] and !in_array($item_id,$snmp_ids)) $ret[] = $item_id;
					}
				}
			}
			if($items_reported) $items_reported = null;
			if($profile_items) $profile_items = null;
            if($profile_items_intervals) $profile_items_intervals = null;
			if($snmp_ids) $snmp_ids = null;
		}

		return $ret;
	}


	/** Returns ONLY the SNMP items that should be reported by the computer. This includes both SNMP items that the computer
	* needs to report about itself and SNMP items to be collected from other IPs (e.g. peripherals)
	* @param	bool			$full_update			If to do a full update or not
	* @return	array							Array with the SNMP items to reports. The array elements are associative arrays
	*									with the following fields:
	*									- item_id: The ID of the item to report
	*									- is_self: 1/0 If the item needs to be collected about itself or other IP
	*									- ip_address: The IP address from which to collect the info
	*									- obj_class: 0-Computer, 1-Peripheral
	*									- obj_id: The object ID of the computer or peripheral
	*									- is_struct: 1/0 If this is a structure or not
	*									- is_multi: 1/0 If this is a multi-value item or not
	*									- oid_top: The OID to collect for single-value simple items, or the top-level
	*									  OID for fetching a tree for structure multi-value items.
	*									- oid_fields: Array with all the OIDs to collect. For single-value simple items
	*									  it contains only the top OID with the field ID set to 0. For structure items
	*									  (both single and multi values) it contains the mapping of fields to OID. It
	*									  is an array of associative array with the following fields: field_id and field_oid
	*/
	function get_needed_items_snmp ($full_update = false)
	{
		$ret = array();

		// Only active customers
		$q = 'SELECT active, has_kawacs, onhold FROM '.TBL_CUSTOMERS.' WHERE id='.$this->customer_id;
		$cust_data = db::db_fetch_array ($q);
		$cust_data = $cust_data[0];

		if ($this->id and ($cust_data->active and $cust_data->has_kawacs and !$cust_data->on_hold))
		{
			$profile_items = MonitorProfile::get_profile_items_list ($this->profile_id);
			$profile_items_intervals = MonitorProfile::get_profile_items_intervals ($this->profile_id);

			// Fetch the list with the last reported times for each item of this computer
			// Using direct database access for speed
			$q = 'SELECT DISTINCT item_id, reported FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id.' ';
			$q.= 'AND ((item_id>='.ITEM_ID_COLLECTED_MIN.' AND item_id<='.ITEM_ID_COLLECTED_MAX.'))';
			$items_reported = db::db_fetch_list ($q);

			// Fetch the list of SNMP items IDs (parents only)
			$snmp_ids = db::db_fetch_vector ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE is_snmp=1 and parent_id=0');

			$report_ids = array (); // Will collect the IDs of the items to report, which will then be processed later

			if ($full_update)
			{
				foreach ($profile_items as $item_id=>$log_type)
				{
					if (in_array($item_id,$snmp_ids) and (($item_id>=ITEM_ID_COLLECTED_MIN and $item_id<=ITEM_ID_COLLECTED_MAX) or
					($item_id>=ITEM_ID_EVENTS_MIN and item_id<=ITEM_ID_EVENTS_MAX))) $report_ids[] = $item_id;
				}
			}
			else
			{
				$time_now = time ();
				foreach ($profile_items as $item_id=>$log_type)
				{
					// Select only automatically collected items
					if (($item_id>=ITEM_ID_COLLECTED_MIN and $item_id<=ITEM_ID_COLLECTED_MAX) or
					($item_id>=ITEM_ID_EVENTS_MIN and item_id<=ITEM_ID_EVENTS_MAX))
					{
						$elapsed = ($time_now - $items_reported[$item_id])/60; // minutes
						if ($elapsed >= $profile_items_intervals[$item_id] and in_array($item_id,$snmp_ids)) $report_ids[] = $item_id;
					}
				}
			}

			foreach ($report_ids as $item_id)
			{
				$item = new MonitorItem ($item_id);
				$item_ar = array(
					'item_id' => $item_id,
					'is_self' => 1,
					'ip_address' => '127.0.0.1',
					'obj_class' => 0,
					'obj_id' => $this->id,
					'is_struct' => ($item->type==MONITOR_TYPE_STRUCT ? 1 : 0),
					'is_multi' => ($item->multi_values==MONITOR_MULTI_YES ? 1 : 0),
					'oid_top' => ($item->snmp_oid),
					'oid_fields' => array ()
				);

				if ($item->type!=MONITOR_TYPE_STRUCT) $item_ar['oid_fields'][] = array ('field_id'=>0, 'field_oid'=>$item->snmp_oid, 'field_name'=>'');
				else
				{
					foreach ($item->struct_fields as $field)
						$item_ar['oid_fields'][] = array('field_id'=>$field->id, 'field_oid'=>$field->snmp_oid, 'field_name'=>$field->short_name);
				}
				$ret[] = $item_ar;
                
                if($item) $item = null;
                if($item_ar) $item_ar = null;
			}


			// Now see if there are any peripherals about which this computer needs to collect data via SNMP
			$has_snmp_periphs = false;
			$q = 'SELECT id FROM '.TBL_PERIPHERALS.' WHERE snmp_computer_id='.$this->id.' AND snmp_enabled=1 LIMIT 1';
			if (db::db_fetch_field ($q, 'id')) $has_snmp_periphs = true;
			else
			{
				// Check AD printers too
				$q = 'SELECT id FROM '.TBL_AD_PRINTERS_EXTRAS.' WHERE snmp_computer_id='.$this->id.' AND snmp_enabled=1 LIMIT 1';
				if (db::db_fetch_field ($q, 'id')) $has_snmp_periphs = true;
			}

			if ($has_snmp_periphs)
			{
				$report_ids = array (); // Will collect the IDs of the items to report, which will then be processed later
							// It is an associative array, the keys being peripherals IDs and the values being
							// arrays with the item IDs that need to be reported for those peripherals
				$snmp_ips = array (); // Associative array, keys are peripheral IDs and the values the IP addresses for SNMP gathering
				if ($full_update)
				{
					// If full update was requested, add all the items from all monitored peripherals
					$q = 'SELECT p.id, p.snmp_ip, pi.item_id FROM '.TBL_PERIPHERALS.' p INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' pi ON ';
					$q.= 'p.profile_id=pi.profile_id AND p.snmp_enabled=1 and p.snmp_computer_id='.$this->id;
					$data = db::db_fetch_array ($q);
					foreach ($data as $d)
					{
						$report_ids[SNMP_OBJ_CLASS_PERIPHERAL][$d->id][] = $d->item_id;
						$snmp_ips[SNMP_OBJ_CLASS_PERIPHERAL][$d->id] = $d->snmp_ip;
					}
					$q = 'SELECT p.id, p.snmp_ip, pi.item_id FROM '.TBL_AD_PRINTERS_EXTRAS.' p INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' pi ON ';
					$q.= 'p.profile_id=pi.profile_id AND p.snmp_enabled=1 and p.snmp_computer_id='.$this->id;
					$data = db::db_fetch_array ($q);
					foreach ($data as $d)
					{
						$report_ids[SNMP_OBJ_CLASS_AD_PRINTER][$d->id][] = $d->item_id;
						$snmp_ips[SNMP_OBJ_CLASS_AD_PRINTER][$d->id] = $d->snmp_ip;
					}
                    $data = null; 
				}
				else
				{
					$time_now = time ();
					// Peripherals
					$q = 'SELECT DISTINCT p.id, '.SNMP_OBJ_CLASS_PERIPHERAL.' as obj_class, pmi.item_id, p.snmp_ip FROM '.TBL_PERIPHERALS.' p ';
					$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' pmi ';
					$q.= 'ON p.profile_id=pmi.profile_id AND p.snmp_computer_id='.$this->id.' AND p.snmp_enabled=1 ';
					$q.= 'LEFT OUTER JOIN '.TBL_PERIPHERALS_ITEMS.' pi ';
					$q.= 'ON p.id=pi.obj_id AND pmi.item_id=pi.item_id AND pi.obj_class='.SNMP_OBJ_CLASS_PERIPHERAL.' ';
					$q.= 'WHERE pi.item_id IS NULL OR ('.$time_now.'-pi.reported)>=(pmi.update_interval*60)';
					$data = db::db_fetch_array ($q);
					foreach ($data as $d)
					{
						$report_ids[$d->obj_class][$d->id][] = $d->item_id;
						$snmp_ips[$d->obj_class][$d->id] = $d->snmp_ip;
					}

					// AD Printers
					$q = 'SELECT DISTINCT a.id, '.SNMP_OBJ_CLASS_AD_PRINTER.' as obj_class, pmi.item_id, a.snmp_ip FROM '.TBL_AD_PRINTERS_EXTRAS.' a ';
					$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' pmi ';
					$q.= 'ON a.profile_id=pmi.profile_id AND a.snmp_computer_id='.$this->id.' AND a.snmp_enabled=1 ';
					$q.= 'LEFT OUTER JOIN '.TBL_PERIPHERALS_ITEMS.' pi ';
					$q.= 'ON a.id=pi.obj_id AND pmi.item_id=pi.item_id AND pi.obj_class='.SNMP_OBJ_CLASS_AD_PRINTER.' ';
					$q.= 'WHERE pi.item_id IS NULL OR ('.$time_now.'-pi.reported)>=(pmi.update_interval*60)';
					$data = db::db_fetch_array ($q);
					foreach ($data as $d)
					{
						$report_ids[$d->obj_class][$d->id][] = $d->item_id;
						$snmp_ips[$d->obj_class][$d->id] = $d->snmp_ip;
					}
                    $data = null;
				}

				$items = array (); // Will keep track of the instantiated items, to reduce load
				foreach ($report_ids as $obj_class => $class_report_ids)
				{
					foreach ($class_report_ids as $peripheral_id => $items_ids) if (is_array($items_ids))
					{
						foreach ($items_ids as $item_id)
						{
							if (!isset($items[$item_id])) $items[$item_id] = new MonitorItem ($item_id);
							$item = &$items[$item_id];
							$item_ar = array(
								'item_id' => $item_id,
								'is_self' => 0,
								'ip_address' => $snmp_ips[$obj_class][$peripheral_id],
								'obj_class' => $obj_class,
								'obj_id' => $peripheral_id,
								'is_struct' => ($item->type==MONITOR_TYPE_STRUCT ? 1 : 0),
								'is_multi' => ($item->multi_values==MONITOR_MULTI_YES ? 1 : 0),
								'oid_top' => ($item->snmp_oid),
								'oid_fields' => array ()
							);

							if ($item->type!=MONITOR_TYPE_STRUCT) $item_ar['oid_fields'][] = array ('field_id'=>0, 'field_oid'=>$item->snmp_oid, 'field_name'=>'');
							else
							{
								foreach ($item->struct_fields as $field)
									$item_ar['oid_fields'][] = array('field_id'=>$field->id, 'field_oid'=>$field->snmp_oid, 'field_name'=>$field->short_name);
							}
							$ret[] = $item_ar;
                            if($item) $item = null;
                            if($item_ar) $item_ar = null;
						}
					}
				}
			}
            
            if($profile_items) $profile_items = null;
            if($profile_items_intervals) $profile_items_intervals = null;
            if($items_reported) $items_reported = null;
            if($snmp_ids) $snmp_ids = null;
            if($report_ids) $report_ids = null;
		}

		return $ret;
	}

	/**
	* Returns an array with the events which the computer needs to report from its events log.
	* This is called from kawacs_server.php only for computers which have an ID and profile ID assigned,
	* and which need to report their events log.
	*/
	function get_needed_events_report ()
	{
		$ret = array ();

		if ($this->id and $this->profile_id)
		{
			$this->load_events_settings ();
			$sources = EventLogRequested::get_events_sources_list_extended ();

			// First specify the default events types for each category
			foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $cat_name)
			{
				$ret[] = array ('category' => $cat_id, 'event_source' => '', 'report_level' => $this->default_events_types_requested[$cat_id]);
			}

			// Add any additional sources that might have been defined for the computer
			$computer_sources = array (); // Keep track of all the computer specific sources, to not overwrite them with the profile sources
			foreach ($this->events_types_requested as $src)
			{
				$cat_id = $src->category_id;
				$source_id = $src->source_id;
				if ($sources[$cat_id][$source_id])
				{
					$ret[] = array ('category'=>$src->category_id, 'event_source'=>$sources[$cat_id][$source_id], 'report_level'=>$src->types);
					$computer_sources[] = $source_id;
				}
			}

			// Finally add the sources defined in the profile, if they are not overwritten by the computer settings
			// Don't use the profile directly anymore, because it is more expensive
			/*$profile = new MonitorProfile ($this->profile_id);
			$profile->load_events_settings ();
			foreach ($profile->events_types_requested as $src) */

			$profile_events_types_requested = EventLogRequested::get_profile_events_types ($this->profile_id);
			foreach ($profile_events_types_requested as $src)
			{
				$cat_id = $src->category_id;
				$source_id = $src->source_id;
				if ($sources[$cat_id][$source_id] and !in_array($source_id,$computer_sources))
				{
					$ret[] = array ('category'=>$src->category_id, 'event_source'=>$sources[$cat_id][$source_id], 'report_level'=>$src->types);
				}
			}
			//foreach ($profile_events_types_requested as $src)
			//{
			//	unset($src);
			//}
			if($sources) $sources = null;
			if($profile_events_types_requested) $profile_events_types_requested = null;
            if($computer_sources) $computer_sources = null;
		}

		return $ret;
	}


	/** Returns any discoveries requests, if any, which this computer needs to report */
	function get_needed_discoveries ()
	{
		$ret = array ();
		class_load ('DiscoverySetting');

		if ($this->id and $this->customer_id)
		{
			// Fetch the discovery settings for this customer
			$setting = DiscoverySetting::get_by_customer ($this->customer_id, false);
			if ($setting->id)
			{
				if ($setting->is_enabled())
				{
					// See if this computer is designated to do discoveries
					foreach ($setting->details as $detail)
					{
						if ($detail->computer_id == $this->id and $detail->needs_update())
						{
							$ret[] = array ('item_id' => DISCOVERY_ITEM_ID, 'detail_id' => $detail->id,
							 'ip_start' => $detail->ip_start, 'ip_end' => $detail->ip_end,
							 'use_snmp' => !$detail->disable_snmp, 'use_wmi' => !$detail->disable_wmi,
							 'wmi_login' => $detail->wmi_login, 'wmi_password' => $detail->wmi_password,
							 'max_threads' => DISCOVERY_MAX_THREADS, 'default_timeout' => DISCOVERY_DEFAULT_TIMEOUT,
							 'batch_timeout' => ($detail->disable_wmi ? DISCOVERY_BATCH_TIMEOUT_NO_WMI : DISCOVERY_BATCH_TIMEOUT_WMI)
							 );
						}
					}
				}
			}
            if($setting) $setting = null;
		}

		return $ret;
	}


	/**
	* Returns all computers that have the same MAC address as this computer
	* @return	array(Computer)				Array with the matching computers found
	*/
	function get_identical_macs ()
	{
		$ret = array ();
		if ($this->id)
		{
			$q = 'SELECT id FROM '.TBL_COMPUTERS.' WHERE ';
			$q.= 'mac_address="'.$this->mac_address.'" AND id<>'.$this->id;
			$ids = DB::db_fetch_vector ($q);

			foreach ($ids as $id) $ret[] = new Computer ($id);
            if($ids) $ids = null;
		}

		return $ret;
	}


	/**
	* Returns all computers that have the same netbios name as this computer
	* @return	array(Computer)				Array with the matching computers found
	*/
	function get_identical_names ()
	{
		$ret = array ();
		if ($this->id)
		{
			$self_name = $this->netbios_name;
			$q = 'SELECT computer_id as id FROM '.TBL_COMPUTERS_ITEMS.' WHERE ';
			$q.= 'item_id=1001 AND computer_id<>'.$this->id.' ';
			$q.= 'AND value="'.db::db_escape ($self_name).'" ';

			$ids = DB::db_fetch_vector ($q);

			foreach ($ids as $id) $ret[] = new Computer ($id);
            if($ids) $ids = null;
		}

		return $ret;
	}


	/**
	* Merge this computer with another one
	*/
	function merge_with_computer ($id = null)
	{
		class_load ('Notification');
		$ret = false;
		$merge = new Computer ($id);

		if ($this->id and $merge->id and $this->id != $merge->id)
		{
			// Re-assigned logged notifications
			$logs = array ();
			$months = Notification::get_log_months ();
			foreach ($months as $month) $logs[] = TBL_NOTIFICATIONS.'_'.$month;
			foreach ($logs as $log)
			{
				$q = 'UPDATE '.$log.' SET object_id='.$this->id.' WHERE ';
				$q.= 'object_class='.NOTIF_OBJ_CLASS_COMPUTER.' AND object_id='.$merge->id;
				DB::db_query ($q);
			}

			// Re-assign tickets
			$q = 'UPDATE '.TBL_TICKETS_OBJECTS.' SET object_id='.$this->id.' WHERE ';
			$q.= 'object_class='.TICKET_OBJ_CLASS_COMPUTER.' AND object_id='.$merge->id;
			DB::db_query ($q);

			// Re-assign computers notes
			$q = 'UPDATE '.TBL_COMPUTERS_NOTES.' SET computer_id='.$this->id.' WHERE computer_id='.$merge->id;
			DB::db_query ($q);

			// Re-assign peripherals
			$q = 'UPDATE '.TBL_PERIPHERALS_COMPUTERS.' SET computer_id='.$this->id.' WHERE computer_id='.$merge->id;
			DB::db_query ($q);

			// Re-assign passwords
			$q = 'UPDATE '.TBL_COMPUTERS_PASSWORDS.' SET computer_id='.$this->id.' WHERE computer_id='.$merge->id;
			DB::db_query ($q);

			// Re-assign remote_services
			$q = 'UPDATE '.TBL_COMPUTERS_REMOTE_SERVICES.' SET computer_id='.$this->id.' WHERE computer_id='.$merge->id;
			DB::db_query ($q);

			// Re-assign phone numbers
			$q = 'UPDATE '.TBL_ACCESS_PHONES.' SET object_id='.$this->id.' ';
			$q.= 'WHERE object_id='.$merge->id.' AND device_type='.PHONE_ACCESS_DEV_COMPUTER;
			DB::db_query ($q);

			// Re-assign logged items
			$logs = array ();
			$months = $merge->get_log_months ();
			foreach ($months as $month) $logs[] = TBL_COMPUTERS_ITEMS_LOG.'_'.$month;
			$logs[] = TBL_COMPUTERS_ITEMS_LOG;

			foreach ($logs as $log)
			{
				$q = 'UPDATE '.$log.' SET computer_id='.$this->id.' WHERE computer_id='.$merge->id;
				DB::db_query ($q);
			}

			// Finally, delete the old computer
			$merge->delete ();

			$ret = true;
            if($logs) $logs = null;
            if($months) $months = null;
		}

        if($merge) $merge = null;
		return $ret;
	}


	/**
	* Checks if there are any alerts that need to be raised for this computer.
	* If any alert conditions are found to be true, this will automatically raise
	* the necessary notifications.
	* NOTE: This can be called  as object method or as class method. If called
	* as class method, it will check all the computers in the system
	*/
	function check_monitor_alerts ($no_increment = true)
	{
		show_elapsed ('Start checking');
		class_load ('Alert');
		class_load ('Notification');
		class_load ('ComputerBlackout');
		//$profiles = array();

		$customer_ok = true;
		if (isset($this) and $this!=null and $this->id)
		{
			// This was called as object method

			// Check if the computer is not actually blocked out
			$blackout = new ComputerBlackout ($this->id);
			$blackout->check_blackouts ();

			if ($blackout->computer_id and $blackout->is_active ())
			{
				// This computer is blacked out, no further checks are performed
				return;
			}

			// Check if the profile is not already loaded
			if ($this->profile_id)
			{
				$alerts = Alert::get_alerts (array('profile_id' => $this->profile_id));

				// Just in case, make sure there are no accidently left "new computer" notifications for this computer
				$filter = array('object_class' => NOTIF_OBJ_CLASS_COMPUTER, 'event_code' => NOTIF_CODE_NEW_COMPUTER, 'object_id' => $this->id);
				$old_notifs = Notification::get_notifications_list ($filter);
				foreach ($old_notifs as $n_id => $n_object_id)
				{
					$n = new Notification ($n_id);
					$n->delete ();
					if($n) $n=null;
				}				
				if($old_notifs) $old_notifs = null;
			}

			// Check if the customer is active, has Kawacs and is not on-hold
			$customer = new Customer ($this->customer_id);
			$customer_ok = ($customer->active and $customer->has_kawacs and !$customer->onhold);
            if($customer) $customer=null;
		}
		else
		{
			// This was called as class method
			$alerts = Alert::get_alerts (array('computers_only'=>true));

			// Make sure all computers a present in the COMPUTERS_UPDATING table
			$q_check = 'SELECT c.id FROM '.TBL_COMPUTERS.' c LEFT OUTER JOIN '.TBL_COMPUTERS_UPDATING.' cu ';
			$q_check.= 'ON c.id=cu.computer_id WHERE cu.computer_id IS NULL ';
			$not_listed_computers = db::db_fetch_vector ($q_check);
			if (!empty($not_listed_computers))
			{
				foreach ($not_listed_computers as $id)
				{
					$q_ins = 'INSERT INTO '.TBL_COMPUTERS_UPDATING.' (computer_id, update_time) VALUES ('.$id.', 0)';
					db::db_query ($q_ins);
				}
			}			
			if($not_listed_computers) $not_listed_computers = null;
		}

		// Get the list of away users, if any
		$away_users_ids = User::get_away_ids ();

		$today_day_code = pow(2, date('w'));

		// Loop through the alerts list and check each alert and the relevant computers
		// to see if any of the computers meet the alert conditions.
		// If this method was called as an object method, the list of alerts will only
		// contain the profile of this computer.

		for ($i=0; $i<count ($alerts); $i++)
		{
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
							//$q_fields[$cond->field_id][] = '+0.0) = ('.db::db_escape($cond->value).$v_type.') ';
							$q_fields[$cond->field_id][] = '+0.0) = ('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_DIFFERENT:
							//$q_fields[$cond->field_id][] = '+0.0) <> ('.db::db_escape($cond->value).$v_type.') ';
							$q_fields[$cond->field_id][] = '+0.0) <> ('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_HIGHER:
							//$q_fields[$cond->field_id][] = '+0.0) > ('.db::db_escape($cond->value).$v_type.') ';
							$q_fields[$cond->field_id][] = '+0.0) > ('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_HIGHER_EQUAL:
							//$q_fields[$cond->field_id][] = '+0.0) >=('.db::db_escape($cond->value).$v_type.') ';
							$q_fields[$cond->field_id][] = '+0.0) >=('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_SMALLER:
							//$q_fields[$cond->field_id][] = '+0.0) <('.db::db_escape($cond->value).$v_type.') ';
							$q_fields[$cond->field_id][] = '+0.0) <('.($cond->value * $v_type).') ';
							break;
						case CRIT_NUMBER_SMALLER_EQUAL:
							//$q_fields[$cond->field_id][] = '+0.0) <=('.db::db_escape($cond->value).$v_type.') ';
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
                    
                    if($cond) $cond = null;
				}

				if (!empty($q_fields))
				{
					// Build the query for finding computers meeting the conditions for the current alert
					// It will not include computers which are marked as being currently updating
					/*
					$last_update_time = time()-(2*60); // If a computer is marked as being updating for more than this,
									// then it will be included in check, because probably there was an error
									// with the updating

					$q = 'SELECT i.*, c.*, count(*) as cnt, cu.update_time ';
					$q.= 'FROM '.TBL_COMPUTERS_ITEMS.' i INNER JOIN '.TBL_COMPUTERS.' c ON i.computer_id=c.id ';
					if (!isset($this) or !$this->id)
					{
						// Checking a class of alerts, make sure to include only relevant computers
						$q.= 'INNER JOIN '.TBL_PROFILES_ALERTS.' pa ON c.profile_id=pa.profile_id AND pa.alert_id='.$alerts[$i]->id.' ';
					}

					$q.= 'INNER JOIN '.TBL_COMPUTERS_UPDATING.' cu ON i.computer_id=cu.computer_id ';
					// Exclude blacked out computers
					$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_BLACKOUTS.' b ON i.computer_id=b.computer_id ';
					$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES.' p ON c.profile_id=p.id '; //XXX Can delete this?
					// Work only with active customers
					$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND ';
					$q.= '(cust.active=1 AND cust.has_kawacs=1 AND cust.onhold=0) ';

					$q.= 'WHERE ';
					$q.= '(b.computer_id IS NULL OR ';
					$q.= '((b.start_date<>0 AND b.start_date>'.time().') OR ((b.end_date<>0 AND b.end_date<'.time().')))) AND ';
					*/
					
					
					////victor mod 19022010
					
					//WE NEED TO OPTIMIZE THIS BECAUSE IS TAKING TOO LONG
					//so first of all we should get a list of all the computers that are blackouted
					$q = "select computer_id from ".TBL_COMPUTERS_BLACKOUTS;
					$ids = db::db_fetch_vector($q);
					$q_blk = "(";
					$idx = 0;
					foreach($ids as $id)
					{
						if($idx!=count($ids)-1) $q_blk.=$id.", ";
						else $q_blk.=$id.") ";
						$idx+=1;
					}

                    if($ids) $ids = null;

					$last_update_time = time()-(2*60); // If a computer is marked as being updating for more than this,
									// then it will be included in check, because probably there was an error
									// with the updating

					$q = 'SELECT i.*, c.*, count(*) as cnt, cu.update_time ';
					$q.= 'FROM '.TBL_COMPUTERS_ITEMS.' i INNER JOIN '.TBL_COMPUTERS.' c ON i.computer_id=c.id ';
					if (!isset($this) or !$this->id)
					{
						// Checking a class of alerts, make sure to include only relevant computers
						$q.= 'INNER JOIN '.TBL_PROFILES_ALERTS.' pa ON c.profile_id=pa.profile_id AND pa.alert_id='.$alerts[$i]->id.' ';
					}

					$q.= 'INNER JOIN '.TBL_COMPUTERS_UPDATING.' cu ON i.computer_id=cu.computer_id ';
					// Exclude blacked out computers
					//$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_BLACKOUTS.' b ON i.computer_id=b.computer_id ';
					$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES.' p ON c.profile_id=p.id '; //XXX Can delete this?
					// Work only with active customers
					$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND ';
					$q.= '(cust.active=1 AND cust.has_kawacs=1 AND cust.onhold=0) ';

					$q.= 'WHERE ';
					$q.= 'i.computer_id not in '.$q_blk.' AND ';
					//$q.= '(b.computer_id IS NULL OR ';
					//$q.= '((b.start_date<>0 AND b.start_date>'.time().') OR ((b.end_date<>0 AND b.end_date<'.time().')))) AND ';

					
					////end victor mod
					
					
						
					// If called as object method, check the alert only for this computer
					if (isset($this) and $this!=null and $this->id) $q.= 'i.computer_id='.$this->id.' AND ';

					// For alerts of type 'On contact only', check that the computer has made a recent contact
					if ($alerts[$i]->on_contact_only)
					{
						$q.= '(c.last_contact>=('.time().' - (60*';
						$q.= '(if(p.report_interval>0,p.report_interval,'.DEFAULT_MONITOR_INTERVAL.')*';
						$q.= 'if(p.alert_missed_cycles>0,p.alert_missed_cycles,'.DEFAULT_CONTACT_LOST_INTERVAL.'))))) AND ';
					}

					$q.= 'item_id='.$alerts[$i]->item_id.' AND (';

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
					$q = preg_replace ('/OR\s*$/', '', $q).') ';

					$q.= 'GROUP BY i.computer_id, item_id, nrc ';

					// Since the item values are stored in separate records, an alert condition is being met only
					// if the returned number of rows matches the number of items involved in this alert
					$q.= 'HAVING cnt= '.count ($q_fields).' ';

					$q.= 'ORDER BY i.computer_id, nrc, field_id ';

					// Check if called as class or object method
					if (isset($this) and $this->id) $found_alerts = db::db_fetch_array ($q);
					else $found_alerts = db::db_fetch_array ($q);
				}

				// Get the list of IDs for already existing alerts of this type (if any)
				$filter = array (
					'object_class' => NOTIF_OBJ_CLASS_COMPUTER,
					'object_event_code' => $alerts[$i]->id
				);
				if (isset($this) and $this->id) $filter['object_id'] = $this->id;
				$existing_notifs = Notification::get_notifications_list ($filter);

				// Raise the found alerts, if any have been found
				// $raised_alerts will store the notifications that have been raised, as an associative array with the
				// raised notifications grouped by computer ID, alert ID and notification recipient ID.
				// Storing the notification recipient ID helps with removing the existing notifications for recipients
				// which are not assigned anymore to recive those specific notifications.
				$raised_alerts = array ();

				// $updating_computers will store the computers which are marked as currently being updating,
				// to avoid deleting notifications for them - since those computers are not checked for alerts
				$updating_computers = array ();

				if (!empty ($found_alerts))
				{
					// Keep track of computers which have been processed for this alert
					// This is needed because the query above might return many hits for the same computer (e.g. in the case of events logs)
					$notified_comps = array ();

					for ($j=0; $j<count ($found_alerts); $j++)
					{
						// Raise (or re-raise) notifications only for alerts which haven't been already raised
						// in this cycle, and only for computers which are not marked as currently being
						// updated
						if (
							!$raised_alerts[$found_alerts[$j]->computer_id][$alerts[$i]->id] and
							$found_alerts[$j]->update_time<$last_update_time and
							!in_array ($found_alerts[$j]->computer_id, $notified_comps)
						)
						{
							$recips_ks = array ();
							$recips_cust = array ();

							// Mark that the computer ID has been processed for this alert
							$notified_comps[] = $found_alerts[$j]->computer_id;

							// Fetch the Keysource users which need to receive this notification, if any.
							// Also append to the recipients any recipients specifically designated for this alert type.
							if (($alerts[$i]->send_to & ALERT_SEND_KEYSOURCE) == ALERT_SEND_KEYSOURCE)
							{
								$recips_ks = Computer::get_notification_recipients (
									$found_alerts[$j]->computer_id, ALERT_SEND_KEYSOURCE);

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
								$recips_cust = Computer::get_notification_recipients (
									$found_alerts[$j]->computer_id, ALERT_SEND_CUSTOMER);
							}

							// Add extra parts to the subject if the alert definition requires it
							$extra_subject = '';
							if (count($alerts[$i]->send_fields) > 0)
							{
								$item = new ComputerItem ($found_alerts[$j]->computer_id, $alerts[$i]->item_id, false);
								foreach ($alerts[$i]->send_fields as $send_field_id)
								{
									$extra_subject.= $item->get_formatted_value ($found_alerts[$j]->nrc, $send_field_id).', ';
								}
								$extra_subject = ' ('.preg_replace('/, $/', '', $extra_subject).')';
                                if($item) $item=null;
							}

							// Create the notification and keep track of IDs
							$notification_id = Notification::raise_notification_array (array(
									'event_code' => $alerts[$i]->event_code,
									'level' => $alerts[$i]->level,
									'object_class' => NOTIF_OBJ_CLASS_COMPUTER,
									'object_id' => $found_alerts[$j]->computer_id,
									'object_event_code' => $alerts[$i]->id,
									'item_id' => $alerts[$i]->item_id,
									'user_ids' => array_merge($recips_ks, $recips_cust),
									'text' => $alerts[$i]->name . $extra_subject,
									'no_increment' => false,
									'template' => ''
							));
							$raised_alerts[] = $notification_id; // Is this correct?

							// For customer users, if any, set the dedicated texts
							if (count($recips_cust)>0)
							{
								$notification = new Notification ($notification_id);
								$text = ($alerts[$i]->subject ?
									$alerts[$i]->subject : $alerts[$i]->name);

								foreach ($recips_cust as $cust_usr_id)
								{
									$notification->set_notification_recipient_text ($cust_usr_id, $text, true, '_classes_templates/notification/msg_customer_alert.tpl');
								}
							}
                            
                            if($recips_cust) $recips_cust = null;
                            if($recips_ks) $recips_ks = null;
                            
						}
						elseif ($found_alerts[$j]->update_time>=$last_update_time)
						{
							$updating_computers[] = $found_alerts[$j]->computer_id;
						}
					}
                    
                    if($notified_comps) $notified_comps = null;
				}
				
				// Now delete older notifications which haven't been raised again
				foreach ($existing_notifs as $old_notif_id => $computer_id)
				{
					// Check that the notification ID is not in the list of notification which have been raised,
					// and also make sure it's not one of the computers which were not checked becaus they were in
					// the process of being updated.
					if (!in_array($old_notif_id,$raised_alerts) and !in_array($computer_id,$updating_computers))
					{
						$old_notif = new Notification ($old_notif_id);
						$old_notif->delete ();
						if($old_notif) $old_notif=null;
					}
				}
                
                
				if($found_alerts) $found_alerts = null;
				if($existing_notifs) $existing_notifs = null;
                if($found_alerts) $found_alerts = null;
                if($updating_computers) $updating_computers = null;
                if($q_fields) $q_fields = null;
                
			}
		}

		if($alerts) $alerts = null;
		// Check for missed heartbeats
		if (isset($this) and $this->id)
		{
			$missed = 0;
			// This was called as object method, check only for this computer. Load profile settings for speed directly from database
			$prof = db::db_fetch_row ('SELECT alert_missed_cycles, report_interval FROM '.TBL_MONITOR_PROFILES.' WHERE id='.$this->profile_id);

			// Will check only if the profile requires it and if the internet connection is not down
			if ($customer_ok and !$this->is_manual and $prof['alert_missed_cycles']>0 and $prof['report_interval']>0 and $this->internet_down==0)
			{
				$missed = intval (time() - $this->last_contact)/($prof['report_interval'] * 60);
				if ($missed > $prof['alert_missed_cycles'])
				{
					$q = 'UPDATE '.TBL_COMPUTERS.' SET alert='.ALERT_CRITICAL.', missed_cycles='.$missed.' WHERE id='.$this->id;
					db::db_query ($q);

					// Raise the notification too
					// Fetch the  list of notifications recipients for this computer
					$notif_recipients = Computer::get_notification_recipients ($this->id);

					Notification::raise_notification_array (array(
						'event_code' => NOTIF_CODE_MISSED_HEARTBEATS,
						'level' => ALERT_CRITICAL,
						'object_class' => NOTIF_OBJ_CLASS_COMPUTER,
						'object_id' => $this->id,
						'object_event_code' => 0,
						'item_id' => 0,
						'user_ids' => $notif_recipients,
						'text' => '',
						'no_increment' => $no_increment,
					));
                    
                    if($notif_recipients) $notif_recipients = null;
				}
				else $missed = 0;
			}

			// Clear previous notification if the alert is no longer valid
			if (!$missed)
			{
				$old_notifs = Notification::get_notifications (array(
					'object_class' => NOTIF_OBJ_CLASS_COMPUTER,
					'event_code' => NOTIF_CODE_MISSED_HEARTBEATS,
					'object_id' => $this->id
				));
				for ($i=0; $i<count($old_notifs); $i++){
                    $old_notifs[$i]->delete ();
                }
				
				if($old_notifs) $old_notifs = null;
			}
		}
		else
		{
			// This was called as a class method, check all computers

			// Get the existing notifications. The notifications which are still valid will be
			// removed from array, so the ones remaining in the array can be deleted.
			$notifs = Notification::get_notifications_list (array('object_class'=>NOTIF_OBJ_CLASS_COMPUTER, 'event_code'=>NOTIF_CODE_MISSED_HEARTBEATS));

			// Get computers with missed heartbeats - except those that are blacked  out
			$q = 'SELECT c.id, ('.time().'-c.last_contact)/(p.report_interval*60) as missed ';
			$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_MONITOR_PROFILES.' p ON c.profile_id=p.id ';
			// Work only with active customers
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND ';
			$q.= '(cust.active=1 AND cust.has_kawacs=1 AND cust.onhold=0) ';
			$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_BLACKOUTS.' b ON c.id=b.computer_id ';
			$q.= 'WHERE p.alert_missed_cycles > 0 AND c.is_manual=0 AND ';
			$q.= '(b.computer_id IS NULL OR ';
			$q.= '((b.start_date<>0 AND b.start_date>'.time().') OR ((b.end_date<>0 AND b.end_date<'.time().')))) AND ';
			$q.= time().'-c.last_contact > (p.report_interval*60*p.alert_missed_cycles) AND ';
			$q.= 'c.internet_down=0 ';

			$ids = db::db_fetch_array($q);
			foreach ($ids as $id)
			{
				// Update the computer information with the missed cycles info. Use direct database access for speed
				$q = 'UPDATE '.TBL_COMPUTERS.' SET missed_cycles='.intval($id->missed).', alert='.ALERT_CRITICAL.' WHERE id='.$id->id;
				DB::db_query ($q);

				// Raise the notification
				$notif_recipients = Computer::get_notification_recipients ($id->id);

				$notif_id = Notification::raise_notification_array (array(
					'event_code' => NOTIF_CODE_MISSED_HEARTBEATS,
					'level' => ALERT_CRITICAL,
					'object_class' => NOTIF_OBJ_CLASS_COMPUTER,
					'object_id' => $id->id,
					'object_event_code' => 0,
					'item_id' => 0,
					'user_ids' => $notif_recipients,
					'text' => '',
					'no_increment' => $no_increment,
				));

				//if (isset($notifs[$notif_id])) unset ($notifs[$notif_id]);
                if($notif_recipients) $notif_recipients = null;
			}
            
            if($ids) $ids = null;

			// The notifications left in $notifs need to have their alerts cleared
			foreach ($notifs as $notif_id => $comp_id)
			{
				$notification = new Notification ($notif_id);
				$notification->delete ();
				if($notification) $notification=null;
			}
            if($notifs) $notifs = null;

			// Check for new computers - meaning computers without a profile (if this was called as class method)
			$q = 'SELECT id FROM '.TBL_COMPUTERS.' WHERE profile_id = 0 ORDER BY customer_id ';
			$new_computers = db::db_fetch_array ($q);

			foreach ($new_computers as $id)
			{
				// Fetch the  list of notifications recipients for this computer
				$notif_recipients = Computer::get_notification_recipients ($id->id);
				Notification::raise_notification_array (array(
					'event_code' => NOTIF_CODE_NEW_COMPUTER,
					'level' => ALERT_NOTICE,
					'object_class' => NOTIF_OBJ_CLASS_COMPUTER,
					'object_id' => $id->id,
					'object_event_code' => 0,
					'item_id' => 0,
					'user_ids' => $notif_recipients,
					'text' => '',
					'no_increment' => $no_increment,
				));
                if($notif_recipients) $notif_recipients = null;
			}
            
            if($new_computers) $new_computers = null;

			// Remove "new computer" alerts for computers for which profiles have been assigned
			$q = 'SELECT n.id FROM '.TBL_NOTIFICATIONS.' n INNER JOIN '.TBL_COMPUTERS.' c ';
			$q.= 'ON n.object_id=c.id ';
			$q.= 'WHERE n.object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' and n.event_code='.NOTIF_CODE_NEW_COMPUTER.' ';
			$q.= 'and c.profile_id<>0 ';
			$ids = db::db_fetch_array ($q);
			foreach ($ids as $id)
			{
				$notification = new Notification ($id);
				$notification->delete ();
				if($notification) $notification=null;
			}
            if($ids) $ids = null;
		}

		// Check for reporting issues - only when invoked as class method
		if (!isset($this) or !$this->id)
		{
			// Collect the reporting issues
			class_load ('Discovery');
			class_load ('InfoRecipients');
			$conflicting_macs = ComputerReporting::get_conflicting_macs ();
			$conflicting_names = ComputerReporting::get_conflicting_names ();
			$name_swingers = ComputerReporting::get_name_swingers ();
			$conflicting_ips = ComputerReporting::get_conflicting_reporting_ips ();
			$late_discoveries = Discovery::get_non_reporting_computers (false);
			$unmatched_discoveries = Discovery::get_customers_without_matches ();

			// Collect the notification IDs related to reporting issues
			$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' AND ';
			$q.= 'event_code in ('.NOTIF_CODE_MAC_CONFLICT.', '.NOTIF_CODE_NAME_CONFLICT.', '.NOTIF_CODE_NAME_SWINGERS.', ';
			$q.= NOTIF_CODE_REPORTING_IP_CONFLICT.', '.NOTIF_CODE_LATE_DISCOVERY.', '.NOTIF_CODE_UNMATCHED_DISCOVERIES.')';
			$ids = DB::db_fetch_vector ($q);
			$raised_ids = array ();

			// Raise the notifications for the found issues
			foreach ($conflicting_macs as $mac => $comps)
			{
				foreach ($comps as $comp)
				{
					// Fetch the  list of notifications recipients for this computer
					$notif_recipients = Computer::get_notification_recipients ($comp->id);
					$raised_ids[] = Notification::raise_notification_array (array(
						'event_code' => NOTIF_CODE_MAC_CONFLICT, 'level' => ALERT_CRITICAL,
						'object_class' => NOTIF_OBJ_CLASS_COMPUTER, 'object_id' => $comp->id, 'object_event_code' => 0,
						'item_id' => 0, 'user_ids' => $notif_recipients, 'text' => '', 'no_increment' => true,
					));
                    if($notif_recipients) $notif_recipients = null;
				}
			}

			foreach ($conflicting_names as $name => $comps)
			{
				foreach ($comps as $comp)
				{
					$notif_recipients = Computer::get_notification_recipients ($comp->id);
					$raised_ids[] = Notification::raise_notification_array (array(
						'event_code' => NOTIF_CODE_NAME_CONFLICT, 'level' => ALERT_CRITICAL,
						'object_class' => NOTIF_OBJ_CLASS_COMPUTER, 'object_id' => $comp->id, 'object_event_code' => 0,
						'item_id' => 0, 'user_ids' => $notif_recipients, 'text' => '', 'no_increment' => true,
					));
                    if($notif_recipients) $notif_recipients = null;
				}
			}

			foreach ($name_swingers as $comp_id => $names)
			{
				$notif_recipients = Computer::get_notification_recipients ($comp_id);
				$raised_ids[] = Notification::raise_notification_array (array(
					'event_code' => NOTIF_CODE_NAME_SWINGERS, 'level' => ALERT_CRITICAL,
					'object_class' => NOTIF_OBJ_CLASS_COMPUTER, 'object_id' => $comp_id, 'object_event_code' => 0,
					'item_id' => 0, 'user_ids' => $notif_recipients, 'text' => '', 'no_increment' => true,
				));
                if($notif_recipients) $notif_recipients = null;
			}

			foreach ($conflicting_ips as $remote_ip => $conflict_ip)
			{
				foreach ($conflict_ip as $customer_id => $comps)
				{
					foreach ($comps as $comp)
					{
						$notif_recipients = Computer::get_notification_recipients ($comp->id);
						$raised_ids[] = Notification::raise_notification_array (array(
							'event_code' => NOTIF_CODE_REPORTING_IP_CONFLICT, 'level' => ALERT_CRITICAL,
							'object_class' => NOTIF_OBJ_CLASS_COMPUTER, 'object_id' => $comp->id, 'object_event_code' => 0,
							'item_id' => 0, 'user_ids' => $notif_recipients, 'text' => '', 'no_increment' => true,
						));
                        if($notif_recipients) $notif_recipients = null;
					}
				}
			}

			foreach ($late_discoveries as $comp_id)
			{
				$notif_recipients = Computer::get_notification_recipients ($comp->id);
				$raised_ids[] = Notification::raise_notification_array (array(
					'event_code' => NOTIF_CODE_LATE_DISCOVERY, 'level' => ALERT_WARNING,
					'object_class' => NOTIF_OBJ_CLASS_COMPUTER, 'object_id' => $comp->id, 'object_event_code' => 0,
					'item_id' => 0, 'user_ids' => $notif_recipients, 'text' => '', 'no_increment' => true,
				));
                if($notif_recipients) $notif_recipients = null;
			}

			foreach ($unmatched_discoveries as $cust_id)
			{
				$no_count = 0;
				$notif_recipients = InfoRecipients::get_customer_recipients (array('customer_id'=>$cust_id), $no_count);
				if (count($notif_recipients[$cust_id][NOTIF_OBJ_CLASS_CUSTOMER]) > 0) $notif_recipients = $notif_recipients[$cust_id][NOTIF_OBJ_CLASS_CUSTOMER];
				else $notif_recipients = InfoRecipients::get_type_recipients (NOTIF_OBJ_CLASS_CUSTOMER);

				$raised_ids[] = Notification::raise_notification_array (array(
					'event_code' => NOTIF_CODE_UNMATCHED_DISCOVERIES, 'level' => ALERT_WARNING,
					'object_class' => NOTIF_OBJ_CLASS_CUSTOMER, 'object_id' => $cust_id, 'object_event_code' => 0,
					'item_id' => 0, 'user_ids' => $notif_recipients, 'text' => '', 'no_increment' => true,
				));
                if($notif_recipients) $notif_recipients = null;
			}

			// Now remove the notifications which are not valid anymore
			foreach ($ids as $id)
			{
				if (!in_array ($id, $raised_ids))
				{
					$notification = new Notification ($id);
					$notification->delete ();
					if($notification) $notification=null;
				}
			}
            if($ids) $ids = null;
            
            if($raised_alerts) $raised_alerts = null;
            if($conflicting_macs) $conflicting_macs = null;
            if($conflicting_names) $conflicting_names = null;
            if($conflicting_ips) $conflicting_ips = null;
            if($name_swingers) $name_swingers = null;
            if($late_discoveries) $late_discoveries = null;
            if($unmatched_discoveries) $unmatched_discoveries = null;
		}

		show_elapsed ('End checking');
	}


	/**
	* Returns an array of notifications which have been raised for this computer
	* @return array (Notification)		The notifications which exist for this computer
	*/
	function get_notifications ()
	{
		$ret = array ();
		if (isset($this) and $this->id)
		{
			class_load ('Notification');
			$filter = array(
				'object_class' => NOTIF_OBJ_CLASS_COMPUTER,
				'object_id' => $this->id,
				'object_unique' => true
			);

			$ret = Notification::get_notifications ($filter);
		}
		return $ret;
	}


	/**
	* [Class Method] Returns an array with the user and group IDs who should receive notifications - either Keysource
	* recipients or customer recipients.
	* Can be invoked as class method or object method.
	* The data is fetched directly from database, for reducing response time.
	*
	* @param	integer		$id		The computer ID. If not specified, it tries to use the object ID (if this
	*						was called as object method.
	* @param	bool		$send_to	The type of recipients. Can be: ALERT_SEND_KEYSOURCE (default), to get
	*						Keysource recipients, ALERT_SEND_CUSTOMER, to get customer recipients,
	*						or their sum, to send to both Keysource and customer.
	* @return	array				Array with the found user and group IDs
	*/
	public static function get_notification_recipients ($id = null, $send_to = ALERT_SEND_KEYSOURCE)
	{
		$ret = array ();
		//if (!$id and isset ($this->id)) $id = $this->id;

		if ($id)
		{
			// See if Keysource recipients are needed
			if (($send_to & ALERT_SEND_KEYSOURCE) == ALERT_SEND_KEYSOURCE)
			{
				// Try first to determine if there are Keysource recipients defined for this computer
				$q = 'SELECT r.user_id FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' r ';
				$q.= 'ON c.customer_id=r.customer_id WHERE c.id='.$id.' AND r.notif_obj_class='.NOTIF_OBJ_CLASS_COMPUTER;

				$ret = db::db_fetch_vector ($q);

				if (count($ret) == 0)
				{
					// This customer has no specific recipients assigned to it, so use the
					// generic ones.
					$q = 'SELECT user_id FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' WHERE notif_obj_class='.NOTIF_OBJ_CLASS_COMPUTER;
					$ret = db::db_fetch_vector ($q);
				}

				// Check if any of the recipients is away and, if yes, add the alternate recipients to the list
				$alternate_ids = array ();
				foreach ($ret as $id)
				{
					if (($alternate_id = User::user_is_away($id)))
					{
						$alternate_ids[] = $id;
					}
				}
				$ret = array_unique(array_merge($ret, $alternate_ids));
                if($alternate_ids) $alternate_ids = null;
			}

			// See if customer recipients are needed
			if (($send_to & ALERT_SEND_CUSTOMER) == ALERT_SEND_CUSTOMER)
			{
				// Try first to determine if there are customer recipients defined for this computer
				$q = 'SELECT r.user_id FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS.' r ';
				$q.= 'ON c.customer_id=r.customer_id WHERE c.id='.$id;

				$ret_cust = db::db_fetch_vector ($q);
				if (count($ret_cust) == 0)
				{
					// This customer has no specific recipients assigned to it,
					// check if there is at least one user defined for this customer
					$q = 'SELECT u.id FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_USERS.' u ';
					$q.= 'ON c.customer_id=u.customer_id ';
					$q.= 'WHERE c.id='.$id.' AND u.type='.USER_TYPE_CUSTOMER.' ';
					$q.= 'ORDER BY id LIMIT 1';

					$ret_cust = db::db_fetch_vector ($q);
					if (count($ret_cust) == 0)
					{
						// This customer has no customer users defined, therefore select a Keysource user
						// to receive the notification.
						$ret_cust = Computer::get_notification_recipients ($id, ALERT_SEND_KEYSOURCE);
					}
				}
				$ret = $ret + $ret_cust;
			}
		}
		return $ret;
	}


	/**
	* [Class Method] Returns a computer based on its MAC address
	* @param	string	$mac_address		The MAC address searched
	* @return	Computer			The found Computer object (if any)
	*/
	public static function get_by_mac ($mac_address = '')
	{
		$id = db::db_fetch_field ('SELECT id FROM '.TBL_COMPUTERS.' WHERE mac_address="'.$mac_address.'" ', 'id');
		$computer = new Computer($id);
		return $computer;
	}


	/**
	* [Class Method] Returns a list of available computers
	* @param	array		$filter		Filtering criteria to apply. Possible key/values are:
	*						- order_by, order_dir : The field by which to sort the computers an
	*						  the direction of the sorting. Special values: netbios_name, current_user,
	*						  computer_brand, computer_model, os_name (which are Kawacs collected fields),
	*						  customer, profile; asset_no (sort by asset number)
	*						- customer_id : Return computers only for the specified customer_id, or computers
	*						  with a certain status: COMPUTERS_FILTER_ALERTS, COMPUTERS_FILTER_MISSED_BEATS,
	*						  COMPUTERS_FILTER_NEW.
	*						- type : Return computers of a specific type - see $GLOBALS['COMP_TYPE_NAMES']
	*						- profile_id : Return computers using the specified profile.
	*						- assigned_user_id : Return only computers to which the user with this ID has access.
	*						- search_text: Return computers having the specified text in the name
	*						- limit, start : How many computers to return and from where to start
	*						  the count.
	*						- load_roles : If true, load the list of roles for each computer
	* @param	int		$count		(By reference) If defined at the time of call, it will
	*						store the total number of matching computers found.
	* @return	array(Computer)			List of computers matching the specified criteria
	*/
	public static function get_computers ($filter = array(), &$count)
	{
		$ret = array();

		$q = 'FROM '.TBL_COMPUTERS.' c ';

		// If filtering by alert level, we need to update the alert level for computers
		if ($filter['order_by'] == 'alert')
		{
			$q_upd = 'SELECT c.id, c.alert, max(n.level) as max FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_NOTIFICATIONS.' n ';
			$q_upd.= 'ON c.id=n.object_id AND n.object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' ';
			$q_upd.= 'GROUP BY c.id HAVING (c.alert<>max(n.level))';
			$upd_data = DB::db_fetch_array ($q_upd);
			for ($i=0; $i<count($upd_data); $i++)
			{
				DB::db_query ('UPDATE '.TBL_COMPUTERS.' SET alert='.$upd_data[$i]->max.' WHERE id='.$upd_data[$i]->id);
			}

			$q_upd = 'SELECT DISTINCT c.id FROM '.TBL_COMPUTERS.' c LEFT OUTER JOIN '.TBL_NOTIFICATIONS.' n ';
			$q_upd.= 'ON c.id=n.object_id AND n.object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' ';
			$q_upd.= 'WHERE c.alert>0 AND n.id IS NULL';
			$ids = DB::db_fetch_vector ($q_upd);
			foreach ($ids as $id) DB::db_query ('UPDATE '.TBL_COMPUTERS.' SET alert=0  WHERE id='.$id);
            if($ids) $ids = null;
		}

		$customers_joined = false;
		if ($filter['order_by'])
		{
			$filter['order_dir'] = ($filter['order_dir'] ? $filter['order_dir'] : 'ASC');

			if (in_array ($filter['order_by'], array ('current_user', 'computer_brand', 'computer_model', 'os_name', 'computer_sn')))
			{
				// For these sorting columns the sorting value comes from the monitor items values
				$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_ITEMS.' ci ON c.id=ci.computer_id AND ci.item_id='.MonitorItem::get_item_id ($filter['order_by']).' ';
				$filter['order_by'] = 'ci.value';
			}
			elseif ($filter['order_by'] == 'customer')
			{
				if(!$customers_joined)
				{
					$customers_joined = true;
					$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' cust on c.customer_id=cust.id ';
				}
				$filter['order_by'] = 'cust.name';
			}
			elseif ($filter['order_by'] == 'profile')
			{
				$q.= 'LEFT OUTER JOIN '.TBL_MONITOR_PROFILES.' p on c.profile_id=p.id ';
				$filter['order_by'] = 'p.name';
			}
			elseif ($filter['order_by'] == 'alert_raised')
			{
				$filter['order_by'] = 'n.raised '.$filter['order_dir'].', n.level DESC, c.id ';
			}
			elseif ($filter['order_by'] == 'asset_no')
			{
				$filter['order_by'] = 'asset_no'; // Just to prevent adding the 'c.' prefix
			}
			else $filter['order_by'] = 'c.'.$filter['order_by'];

		}
		if ($filter['customer_id']==COMPUTERS_FILTER_MISSED_BEATS or $filter['customer_id']==COMPUTERS_FILTER_ALERTS or $filter['order_by']=='alert_raised')
		{
			$q.= 'INNER JOIN '.TBL_NOTIFICATIONS.' n ON c.id=n.object_id ';
		}
		if ($filter['assigned_user_id'])
		{
			if (!$customers_joined) 
			{
				$customers_joined = true;
				$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' cust on c.customer_id=cust.id ';
			}

			// Check both direct user assignment and group assignment
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_CUSTOMERS_ASSIGNED.' ac ON cust.id=ac.customer_id ';
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON ac.user_id=ug.group_id ';
		}

		if($filter['account_manager'] > 0)
		{
			if(!$customers_joined)
			{
				$customers_joined = true;
				$q.=" LEFT OUTER JOIN ".TBL_CUSTOMERS." cust on c.customer_id=cust.id ";
			}
		}

		$q.= 'WHERE ';

		$current_user = $GLOBALS['CURRENT_USER'];
		if($current_user and $current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $current_user->get_assigned_customers_list();
			$q.= 'c.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$q.=") AND ";
			//.$filter['customer_id'].' AND ';
            if($cc) $cc = null;
		}
		if($filter['account_manager'] > 0)
		{
			$q .= "cust.account_manager = ".$filter['account_manager']." AND ";
		}

		if($filter['exclude_blackouts'])
		{
			$q.=" c.id not in (select computer_id from ".TBL_COMPUTERS_BLACKOUTS.") AND ";
		}
		if ($filter['customer_id'] > 0)
		{
			$q.= 'c.customer_id='.$filter['customer_id'].' AND ';
		}
		elseif ($filter['customer_id'] == COMPUTERS_FILTER_ALERTS)
		{
			$q.= 'n.object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' AND ';
		}
		elseif ($filter['customer_id'] == COMPUTERS_FILTER_MISSED_BEATS)
		{
			$q.= 'n.object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' AND n.event_code='.NOTIF_CODE_MISSED_HEARTBEATS.' AND ';
		}
		elseif ($filter['customer_id'] == COMPUTERS_FILTER_NEW)
		{
			$q.= 'c.profile_id = 0 AND ';
		}

		if ($filter['assigned_user_id'])
		{
			$q.= '(ac.user_id='.$filter['assigned_user_id'].' OR ';
			$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$filter['assigned_user_id'].')) AND ';
		}
		
		if(isset($filter['show_in_console']) and $filter['show_in_console']>=0)
		{
			$q.= 'n.show_in_console = '.$filter['show_in_console'].' AND ';
		}

		if (is_numeric($filter['type']) and $filter['type']>=0) $q.= 'c.type='.$filter['type'].' AND ';
		if ($filter['profile_id']) $q.= 'c.profile_id='.$filter['profile_id'].' AND ';
		if ($filter['search_text']) $q.= 'c.netbios_name like "%'.db::db_escape($filter['search_text']).'%" AND ';

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
			$order = "";
			if($filter['group_by_type']) $order = "c.type DESC,";
			$q.= "ORDER BY ".$order." ".$filter['order_by']." ".$filter['order_dir']." ";
		}

		if (isset($filter['start']) and isset($filter['limit']))
		{
			$q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
		}
		$ids = db::db_fetch_array($q);

		foreach ($ids as $id) $ret[] = new Computer($id->id);
		if ($filter['load_roles'])
		{
			for ($i=0, $i_max=count($ret); $i<$i_max; $i++) $ret[$i]->load_roles ();
		}

        if($ids) $ids = null;

		return $ret;
	}

	public static function get_computers_ex($filter = array())
	{
		class_load("ComputerBlackout");
		$ret = array();
		$cnt = 0;
		$computers = Computer::get_computers($filter,$cnt);
		$two_month_ago = strtotime('-2 months');
		$now = strtotime("now");
		$i =0 ; $j=0; $k=0;
		foreach($computers as $comp)
		{
			$blackout = false;
			$c_blkout = new ComputerBlackout($comp->id);
			//debug($c_blkout);
			if($c_blkout->computer_id)
			{
			  $blackout = true;
			}
			if($blackout)
			{
				$c_blkout->load_computer();
				$ret['blackout'][$k] = $c_blkout;
				$k++;
			}
			else
			{
				if($now - $comp->last_contact < $now - $two_month_ago)
				{
					$ret['current'][$i] = $comp;
					$i++;
				}
				else
				{
					$ret['old'][$j] = $comp;
					$j++;
				}
			}
		}
		if($computers) $computers = null;
		return $ret;
	}

	/**
	* [Class Method] Returns a list of computers according to the specified criteria
	* @param	array	$filter		Associative array with filtering criteria. Fields can be:
	*					- customer_id : returns computers for the specified customer
	*					- type_id : returns computers of specified type
	*					- profile_id : returns computers of specified type
	*					- location_id: returns only computers assigned to this location
	*					- order_by : the sorting criteria, can be 'name' (default), 'id', 'asset_no'
	*					  'type' (to sort by computer type and then computer name), or can be 'customer',
	*					  to sort by customer name
	*					- append_id : if True, the ID of the computers will be appended to names
	*					- logging_item : if a monitoring item ID is specified, then the list
	*					  will only return computers whose profiles are set to log the specified item
	* @return	array			Associative array with the results, the keys being computer IDs,
	*					and the values being computer names
	*/
	public static function get_computers_list ($filter = array())
	{
		$ret = array ();
		$q = 'SELECT DISTINCT c.id, c.netbios_name ';
		if ($filter['order_by'] == 'asset_no')
		{
			$q.= ', concat(if(type='.COMP_TYPE_SERVER.',"'.ASSET_PREFIX_SERVER.'","'.ASSET_PREFIX_WORKSTATION.'"),lpad(id,'.ASSET_NUM_LENGTH.',"0")) as asset_no ';
		}
		$q.= 'FROM '.TBL_COMPUTERS.' c ';

		if ($filter['logging_item'])
		{
			$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS.' mi ';
			$q.= 'ON c.profile_id=mi.profile_id AND mi.item_id='.$filter['logging_item'].' ';
		}
		if ($filter['order_by'] == 'customer')
		{
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
		}

		$q.= 'WHERE ';

		if ($filter['customer_id']) $q.= 'c.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['type_id']) $q.= 'c.type='.$filter['type_id'].' AND ';
		if ($filter['profile_id']) $q.= 'c.profile_id='.$filter['profile_id'].' AND ';
		if ($filter['location_id']) $q.= 'c.location_id='.$filter['location_id'].' AND ';

		if($filter['exclude_blackouts'])
		{
			$q.=" c.id not in (select computer_id from ".TBL_COMPUTERS_BLACKOUTS.") AND ";
		}

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
	public static function get_computers_types ($customer_id)
	{
		$q = 'SELECT id, type FROM '.TBL_COMPUTERS.' WHERE customer_id='.$customer_id;
		return DB::db_fetch_list ($q);
	}

	/**
	* [Class Method] Returns a list of computers which have been assigned for peripherals SNMP monitoring.
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- customer_id: Return only computers for the given customer
	*							- computer_id: Return only the monitored peripherals for this computer
	*							- ad_printers: 0=(Default) include AD printers too; 1=don't include AD printers; 2=AD printers only
	* @return	array					Associative array, the keys being computer IDs and the
	*							values being associative array with the IDs/names of the
	*							peripherals being monitored.
	*/
	public static function get_list_monitored_peripherals ($filter = array ())
	{
		$ret = array ();
		$r = array ();
		if (!$filter['ad_printers']) $filter['ad_printers'] = 0;

		$q_cond = '';
		if ($filter['customer_id']) $q_cond.= 'c.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['computer_id']) $q_cond.= 'c.id='.$filter['computer_id'].' AND ';
		if ($q_cond) $q_cond = 'WHERE '.preg_replace('/AND\s*$/', ' ', $q_cond);

		if ($filter['ad_printers']==0 or $filter['ad_printers']==2)
		{
			// List first the computers monitoring AD printers
			$q = 'SELECT c.id as computer_id, p.asset_number, p.canonical_name as name FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_AD_PRINTERS_EXTRAS.' p ';
			$q.= 'ON c.id=p.snmp_computer_id '.$q_cond.' ORDER BY c.netbios_name, p.canonical_name ';
			$data = DB::db_fetch_array ($q);
			foreach ($data as $d) $r[$d->computer_id][$d->asset_number] = preg_replace ('/.*\//', '', $d->name); // Will extract the name direct from CN
            if($data) $data = null;
		}
		if ($filter['ad_printers']==0 or $filter['ad_printers']==1)
		{
			// List the computers monitoring peripherals
			$q = 'SELECT c.id as computer_id, p.id, p.name FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_PERIPHERALS.' p ';
			$q.= 'ON c.id=p.snmp_computer_id '.$q_cond.' ORDER BY c.netbios_name, p.name ';
			$data = DB::db_fetch_array ($q);
			foreach ($data as $d) $r[$d->computer_id][$d->id] = $d->name;
            if($data) $data = null;
		}

		// If both peripherals and AD printers were requested, make sure the computers are sorted by name
		if ($filter['ad_printers'] != 0 or $filter['computer_id']) $ret = $r;
		else
		{
			$q = 'SELECT id, netbios_name FROM '.TBL_COMPUTERS.' '.($filter['customer_id'] ? 'WHERE customer_id='.$filter['customer_id'] : '').' ORDER BY 2';
			$computers_list = DB::db_fetch_list ($q);
			foreach ($computers_list as $computer_id => $computer_name)
			{
				if (isset($r[$computer_id]))
				{
					$ret[$computer_id] = $r[$computer_id];
				}
			}
            if($computers_list) $computers_list = null;
		}

        if($r) $r = null;
        
		return $ret;
	}

	/**
	* [Class Method] Returns a list of customer IDs associated with computers
	* @param	array	$filter		Associative array with filtering criteria. Fields can be:
	*					- type_id : returns computers of specified type
	*					- profile_id : returns computers of specified type
	* @return	array			Associative array, the keys are computer IDs and the values are their
	*					customer IDs.
	*/
	public static function get_computers_customer_ids ($filter = array ())
	{
		$ret = array ();
		$q = 'SELECT c.id, c.customer_id FROM '.TBL_COMPUTERS.' c WHERE ';

		if ($filter['profile_id']) $q.= 'c.profile_id='.$filter['profile_id'].' AND ';
		if ($filter['type_id']) $q.= 'c.type='.$filter['type_id'].' AND ';

		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);

		$ret = db::db_fetch_list ($q);

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
	public static function get_disks_list ($filter = array ())
	{
		$ret = array ();

		$partitions_item_id = Computer::get_item_id ('partitions');
		$path_field_id = Computer::get_item_id ('unc', $partitions_item_id);

		$q = 'SELECT concat(c.id, "_", i.value), c.id FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_COMPUTERS_ITEMS.' i ';
		$q.= 'ON c.id=i.computer_id ';
		$q.= 'WHERE i.item_id='.$partitions_item_id.' AND field_id='.$path_field_id.' ';

		if ($filter['customer_id']) $q.= 'AND c.customer_id='.$filter['customer_id'].' ';
		if ($filter['type_id']) $q.= 'AND c.type='.$filter['type_id'].' ';

		$partitions_list = db::db_fetch_list ($q);
		$computers_list = Computer::get_computers_list ($filter);

		if ($filter['with_logs'])
		{
			$q = 'SELECT DISTINCT c.id FROM '.TBL_COMPUTERS.' c ';
			$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS.' p ON c.profile_id=p.profile_id ';
			$q.= 'WHERE item_id='.$partitions_item_id.' AND log_type<>'.MONITOR_LOG_NONE.' ';
			if ($filter['customer_id']) $q.= 'AND c.customer_id='.$filter['customer_id'].' ';
			if ($filter['type_id']) $q.= 'AND c.type='.$filter['type_id'].' ';

			$computers_with_log = db::db_fetch_vector ($q);

			foreach ($partitions_list as $partition => $id)
			{
				if (!in_array($id, $computers_with_log)) unset ($partitions_list[$partition]);
			}
            if($computers_with_log) $computers_with_log=null;
		}

		foreach ($partitions_list as $partition => $id)
		{
			$partition_name = split ('_', $partition, 2);
			$ret[$partition] = $computers_list[$id].' ['.$partition_name[1].']';
		}

		if ($filter['order_by'] == 'id') ksort ($ret);
		else asort($ret);
        
        if($partitions_list) $partitions_list = null;
        if($computers_list) $computers_list = null;

		return $ret;
	}


	/**
	* [Class Method] Returns a list of computers which have missed reporting cycles
	* @return	array(Computer)		The list of computers which missed more cycles than the
	*					maximum allowed by their profiles
	*/
	public static function get_computers_missed ()
	{
		$ret = array ();
		$q = 'SELECT DISTINCT c.id FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_NOTIFICATIONS.' n ';
		$q.= 'ON c.id=n.object_id LEFT JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id = cust.id ';
		$q.= 'WHERE n.object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' AND n.event_code='.NOTIF_CODE_MISSED_HEARTBEATS.' ';
		$q.= 'ORDER BY c.type DESC, cust.name ';

		$ids = db::db_fetch_array($q);
		foreach ($ids as $id)
		{
			$ret[] = new Computer($id->id);
		}
		return $ret;
	}


	/**
	* [Class Method] Returns a list of computers which have alerts/notifications
	*/
	public static function get_alert_computers ()
	{
		$ret = array ();
		$q = 'SELECT DISTINCT c.id FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_NOTIFICATIONS.' n ';
		$q.= 'ON c.id=n.object_id LEFT JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id = cust.id ';
		$q.= 'WHERE n.object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' ';
		$q.= 'ORDER BY c.type DESC, cust.name ';

		$ids = db::db_fetch_array($q);

		foreach ($ids as $id)
		{
			$ret[] = new Computer($id->id);
		}
		return $ret;
	}


	/**
	* [Class Method] Returns stats about how many computers with alerts there are
	* @return	array			Associative array, with the keys being severities
	*					levels and the values being the number of computers
	*					having that level of alert
	*/
	public static function get_computer_alerts_stat ()
	{
		$ret = array ();

		// For speed, get the info straight from Notifications table, not need to join with Computers
		$q = 'SELECT n.level, count(DISTINCT n.object_id) as cnt FROM '.TBL_NOTIFICATIONS.' n ';
		$q.= 'WHERE n.object_class = '.NOTIF_OBJ_CLASS_COMPUTER.' ';
		$q.= 'GROUP BY n.level ORDER BY n.level DESC';

		$ret = db::db_fetch_list ($q);

		return $ret;
	}


	/**
	* [Class Method] Returns a list of new computers (computers with no assigned profile)
	*/
	public static function get_new_computers ()
	{
		$ret = array ();
		$q = 'SELECT DISTINCT c.id FROM '.TBL_COMPUTERS.' c LEFT JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id = cust.id ';
		$q.= 'WHERE c.profile_id=0 ORDER BY c.type DESC, cust.name ';

		$ids = db::db_fetch_array($q);

		foreach ($ids as $id)
		{
			$ret[] = new Computer($id->id);
		}
		return $ret;
	}

	/** [Class Method] Returns a list of computers who have not reported in long time, only for
	* active customers who have Kawacs enabled.
	* @param	int	$days		The number of days to have passed since last contact
	* @return	array			Array of Computer objects, ordered ascending by contact date.
	*/
	public static function get_oldest_contacts ($days = 1)
	{
		$ret = array ();

		$q = 'SELECT c.id, to_days(now())-to_days(from_unixtime(c.last_contact)) as days FROM '.TBL_COMPUTERS.' c ';
		$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND cust.active=1 ';
		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_BLACKOUTS.' b ON c.id=b.computer_id ';
		$q.= 'WHERE c.is_manual=0 AND ';
		$q.= '(b.computer_id IS NULL OR ((b.start_date<>0 AND b.start_date>'.time().') OR ((b.end_date<>0 AND b.end_date<'.time().')))) ';
		$q.= 'HAVING days>='.$days.' ORDER BY days DESC';

		$list = db::db_fetch_list ($q);
		foreach ($list as $id => $days) $ret[] = new Computer($id);

		return $ret;
	}


	/**
	* [Class Method] Updates the monthly item logs and cleans up computers_items_log
	*
	* This function should be run daily after midnight. It will loop over the months,
	* starting with the earliest date found in the log, and will copy data from the
	* computers_items_log table  to computers_items_YYYY_MM table.
	*
	* Also, all items older than 1 week are deleted from comuters_items_log, to keep
	* the table small.
	*/
	public static function update_monthly_logs ($quick = false)
	{
		ini_set('memory_limit', '700M');
		$time_current = time ();

		if ($quick)
		{
			$time_barrier = strtotime ('1 hours ago');
			$time_min = $time_current - 3600;
		}
		else
		{
			$time_barrier = strtotime ('1 week ago');

			// Check what's the earliest time in the items log
			$q = 'SELECT min(reported) as time_min FROM '.TBL_COMPUTERS_ITEMS_LOG.' ';
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

				$tbl_name = TBL_COMPUTERS_ITEMS_LOG.'_'.$year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT);

				$table_exist = db::db_fetch_row ('SHOW TABLES LIKE "'.$tbl_name.'"');
				if (!$table_exist)
				{
					$struct = db::db_fetch_field ('SHOW CREATE TABLE '.TBL_COMPUTERS_ITEMS_LOG, 'Create Table');
					$struct = str_replace (TBL_COMPUTERS_ITEMS_LOG, $tbl_name, $struct);
					db::db_query ($struct);
				}

				// Because we are using INSERT IGNORE we don't have to worry about duplicate records,
				// they are silently discarded. And because the primary key is composed only of numeric
				// fields, identifying the duplicates doesn't consume too much extra time
				$q = 'INSERT IGNORE INTO '.$tbl_name.' SELECT * FROM '.TBL_COMPUTERS_ITEMS_LOG.' ';
				$q.= 'WHERE reported>='.$t_start.' AND reported<'.$t_end;
				//
				db::db_query ($q);

				//if($q)  unset($q);
				
				
				
				
				
				
				//the above is taking a lot of time to work so we'll try in the first phase to make it in 2 separate steps.
				//first we'll make the select and the result will be inserted	
				//$q = 'SELECT * FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE reported>='.$t_start.' AND reported<'.$t_end;
				//$qlogs = db::db_fetch_array($q);
				// now create the insert statement		
				//$q = 'INSERT IGNORE INTO '.$tbl_name.' VALUES ';			
				//$tx="";
				//foreach($qlogs as $ql)
				//{
				//	$q = "INSERT IGNORE INTO ".$tbl_name.' VALUES ';
				//	$q .= "(".$ql->computer_id.", ".$ql->item_id.", ".$ql->nrc.", ".$ql->field_id.", '".db::db_escape($ql->value)."', ".$ql->reported.")";
				//	db::db_query($q);
 			
				//}
                                //if($qlogs) $qlogs = null;
				//$tx = preg_replace("/,\s$/", "", $tx);			
				//$q.=$tx;				
				//db::db_query($q);	
				//if($q) $q = null;
 				/**************************end************************/

				$month = $month_next; $year = $year_next;	
			}
			if (!$quick)
			{
				
				// Finally, delete all it
//				ems older than 1 week
				$q = 'DELETE FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE reported<'.$time_barrier;
				db::db_query ($q);
			}
		}
	}


	/**
	* Returns a list of months for which monitor item logs are available
	* @param	int		$item_id	(Optional) Restrict the list only to months on which
	*						this item has been logged.
	* @return	array				Array of strings with the month for which logs
	*						are available, in the format "YYYY_DD"
 	*/
	function get_log_months ($item_id = null)
	{
		$ret = array ();

		if ($this->id)
		{
			// Get the whole list of available log months
			$q = 'SHOW TABLES like "'.TBL_COMPUTERS_ITEMS_LOG.'_%" ';
			$months = db::db_fetch_vector ($q);

			// Check which months actually have logs for this computer
			if (count($months) > 0)
			{
				arsort($months);

				foreach ($months as $month)
				{
					$q = 'SELECT computer_id FROM '.$month.' WHERE computer_id='.$this->id.' ';
					if ($item_id) $q.= 'AND item_id='.$item_id.' ';
					$q.= 'LIMIT 1';

					if ($this->id == db::db_fetch_field($q, 'computer_id'))
					{
						$ret[] = str_replace(TBL_COMPUTERS_ITEMS_LOG.'_', '', $month);
					}
				}
			}
            if($months) $months = null;
		}
		return $ret;
	}


	/**
	* [Class Method] Returns a list of months for which there are logs
	* @param	array	$filter			Filtering criteria. Fields can be:
	*						- customer_id : Return the log months for this customer
	*						- item_id : Return log months which include this item
	*						- computer_id : Return log months for this computer only
	*						- max_date : Return log months only up to this date
	*/
	public static function get_all_log_months ($filter = array ())
	{
		$ret = array ();

		// Get the whole list of available log months
		$q = 'SHOW TABLES like "'.TBL_COMPUTERS_ITEMS_LOG.'_%" ';
		$months = db::db_fetch_vector ($q);

		if ($filter['max_date']) $max_date = date('Y_m', $filter['max_date']);

		if (count($months) > 0)
		{
			arsort($months);
			foreach ($months as $month)
			{
				if ($filter['customer_id'])
				{
					$q = 'SELECT m.reported FROM '.$month.' m ';
					$q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON c.id=m.computer_id ';
					$q.= 'WHERE c.customer_id='.$filter['customer_id'].' AND ';
					if ($filter['computer_id']) $q.= 'm.computer_id='.$filter['computer_id'].' AND ';
					if ($filter['item_id']) $q.= 'm.item_id='.$filter['item_id'].' AND ';
					if(!$filter['computer_id'])					
					{						
						$c_list = "(";						
						//get a list of customer's computers to reduce the search range						
						$c_query = "select id from ".TBL_COMPUTERS.' c where c.customer_id='.$filter['customer_id'];						
						$comps_ids = db::db_fetch_vector($c_query);						
						if(count($comps_ids) > 0)						
						{							
							foreach($comps_ids as $cid)
							{								
								$c_list .= $cid.", ";							
							}							
							$c_list = preg_replace('/,\s$/', ')', $c_list);							
						}						
						else{							
							$c_list.=")";						
						}												
						$q .= "m.computer_id in ".$c_list." AND ";		
                        if($comps_ids) $comps_ids = null;			
					}
				}
				else				
				{					
					$q = 'SELECT m.reported FROM '.$month.' m WHERE ';					
					if ($filter['computer_id']) $q.= 'm.computer_id='.$filter['computer_id'].' AND ';					
					if ($filter['item_id']) $q.= 'm.item_id='.$filter['item_id'].' AND ';				
				}				
				$q = preg_replace ('/AND\s*$/', ' ', $q);				
				$q = preg_replace ('/WHERE\s*$/', ' ', $q);
				
				$q.= 'LIMIT 1';
				if (db::db_fetch_field ($q, 'reported'))
				{
					if (!isset($max_date) or (isset($max_date) and $month <= TBL_COMPUTERS_ITEMS_LOG.'_'.$max_date))
					$ret[] = str_replace (TBL_COMPUTERS_ITEMS_LOG.'_', '', $month);
				}
			}
		}
        if($months) $months = null;

		return $ret;
	}


	/**
	* [Class Method] Returns an array with the history of the free disk space. Can be
	* called as class method or object method
	* @param	array	$filter			Filtering criteria, the fields can be:
	*						- computer_id : a computer ID; required when called as class method
	*						- month: a log table suffix (YYYY_DD)
	*						- month_start, month_end : an interval for which to retrieve the
	*						  the logs (YYYY_DD). Can't be specified at the same time with 'month'
	*						- sort_dir: the direction of sorting (ASC/DESC)
	*						- interval: on what intervals to repor (hour/day); default is 'hour'
	*						- partition : return the log only for this partition
	* @return	array				Associative array, the keys being partition paths and the values
	*						being generic objects with the fields:
	*						- size: The total size of the partition
	*						- log: The free space history, represented as associative
	*						  array with timestamps as keys and free space as values.
	*/
	public static function get_partitions_history ($filter = array())
	{
		$ret = array ();

		// Get partitions
		$computer_id = (!empty($filter['computer_id'])) ? $filter['computer_id'] : null;
        if(!$computer_id) return $ret;
        $partition = $filter['partition'];
		if (preg_match('/^[A-Za-z]\:$/', $partition)) $partition.= '\\';		// For windows disks make sure we have the trailing slash
		$item_id = 1013;		// The ID of the item storing partitions info
		$field_path_id = 9;		// The monitoring item field ID for storing the path
		$field_size_id = 12;		// The monitoring item field ID for storing the partition size
		$field_free_id = 13;		// The monitoring item field ID for storing the free space

        $filter['partition'] = $partition;
		// Set default filtering
		if (!$filter['interval']) $filter['interval'] = 'hour';

		// The limits, as timestamps, for the interval
		$time_start = 0;
		$time_end = 0;

		if ($filter['month']) $months = array(TBL_COMPUTERS_ITEMS_LOG.'_'.$filter['month']);
		elseif ($filter['month_start'] and $filter['month_end'])
		{
			$months = array ();
			$month = $filter['month_start'];
			$month_first = $month;
			$month_last = $month;
            $runaway_check = 0;
			while ($month <= $filter['month_end'] and ($runaway_check++ < 100))
			{
				$months[] = TBL_COMPUTERS_ITEMS_LOG.'_'.$month;
				$month_last = $month;
				//$months_numbers[] = $month;
				list ($year, $month) = preg_split('/_/', $month, 2);
				if ($month++ > 11) {$year++; $month=1;}
				$month = $year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT);
			}

			// Calculate the start and end timestamps
			list ($year, $month) = preg_split('/_/', $month_first, 2);
			$time_start = mktime(0, 0, 0, $month, 1, $year);
			list ($year, $month) = preg_split('/_/', $month_last, 2);
			$time_end = mktime(0, 0, 0, ($month+1), 1, $year);
			// Make sure we don't exceed current date.
			if ($time_end > time()) $time_end = mktime(0, 0, 0, date('m'), (date('d')+1), date('Y'));
		}
		else
		{
			$months = array(TBL_COMPUTERS_ITEMS_LOG);
		}

		// Get the list of volumes and their sizes
		$computer_item = new ComputerItem($computer_id, $item_id);
        $volumes = array();
		for ($i=0; $i<count($computer_item->val); $i++)
		{
            if(!$volumes[$computer_item->val[$i]->value[$field_path_id]]) $volumes[$computer_item->val[$i]->value[$field_path_id]] = new StdClass;
			$volumes[$computer_item->val[$i]->value[$field_path_id]]->size = $computer_item->val[$i]->value[$field_size_id];
		}


		if ($filter['partition']) $volumes = array($filter['partition'] => $volumes[$filter['partition']]);

		$min_time = time ();
		$max_time = 0;
		foreach ($volumes as $volume => $size)
		{
			$volumes[$volume]->log = array();
			$history = array ();
			foreach ($months as $tbl)
			{
                if(!db::db_fetch_vector("show tables like '" . $tbl . "'")) continue;
				$q = 'SELECT l1.reported as date, min(l2.value + 0) as value, ';
				$q.= 'year(from_unixtime(l1.reported)) as year, month(from_unixtime(l1.reported)) as month, dayofmonth(from_unixtime(l1.reported)) as day ';
				if ($filter['interval']=='hour') $q.= ', hour(from_unixtime(l1.reported)) as hour ';
				$q.= 'FROM '.$tbl.' l1 INNER JOIN '.$tbl.' l2 ';
				$q.= 'ON l1.computer_id=l2.computer_id AND l1.item_id=l2.item_id AND l1.reported=l2.reported AND l1.nrc=l2.nrc ';
				$q.= 'WHERE ';
				$q.= 'l1.computer_id='.$computer_id.' AND l1.item_id='.$item_id.' AND l1.field_id='.$field_path_id.' AND ';
				$q.= 'l1.value="'.db::db_escape($volume).'" AND l2.field_id='.$field_free_id.' ';
				if ($time_start>0 and $time_end>0)
				{
					$q.= 'AND l1.reported>='.$time_start.' AND l1.reported<='.$time_end.' ';
				}

				if ($filter['interval']=='hour') $q.= 'GROUP BY year, month, day, hour ';
				else $q.= 'GROUP BY year, month, day ';

                $history+= db::db_fetch_list ($q);	// Called as class method
			}

			if (!empty($history))
			{
				// Round all the times to hour/day
				foreach ($history as $time => $free)
				{
					if ($filter['interval'] == 'day')
						$round_time = strtotime(date ('d M Y 00:00', $time));
					else
						$round_time = strtotime(date ('d M Y H:00', $time));
					$volumes[$volume]->log[$round_time] = $free;
				}

				$min_time_volume = min(array_keys($volumes[$volume]->log));
				$max_time_volume = max(array_keys($volumes[$volume]->log));

				if ($min_time > $min_time_volume) $min_time = $min_time_volume;
				if ($max_time < $max_time_volume) $max_time = $max_time_volume;
			}
            if($history) $history = null;            
		}

		// Compose the result, filling in the hours/days where there weren't reports
		foreach ($volumes as $volume => $data)
		{
			$last_size = 0;
			if (is_array($data->log) and count($data->log)>0) $last_size = $data->log[max(array_keys($data->log))];
			if(!$ret[$volume]) $ret[$volume] = new StdClass;
            $ret[$volume]->size = $data->size;
			$ret[$volume]->log = array ();

			// If start and end timestamps are defined, add them at the beginning and  the end of the interval, if needed
			if ($time_start>0 and $time_end>0)
			{
				$min_time = $time_start;
				$max_time = $time_end;
			}

			for ($time=$max_time; $time>=$min_time; $time = strtotime ('-1 '.$filter['interval'], $time))
			// $time-=($step_interval) - This was wrong, due to shorter days when switching to/from daylight saving time
			{
				if (isset($data->log[$time]))
				{
					$ret[$volume]->log[$time] = $data->log[$time];
					$last_size = $data->log[$time];
				}
				else
				{
					$ret[$volume]->log[$time] = $last_size;
				}
			}

			if ($filter['sort_dir'] == 'DESC') krsort($ret[$volume]->log);
			else ksort($ret[$volume]->log);
		}

        if($volumes) $volumes = null;
        if($computer_item) $computer_item = null;
        
		return $ret;
	}


	/**
	* Returns an array with the history of missed backups for the computer.
	* Can be called as class method too.
	* @param	array	$filter			Filtering criteria, the fields can be:
	*						- computer_id: the computer ID, required when called as class method
	*						- month: a log table suffix (YYYY_DD)
	*						- sort_dir: the direction of sorting (ASC/DESC)

	* @return	array				Associative array, the keys being dates and the
	*						values being the age of the backup at those specific dates
	*/
	public static function get_backups_history ($filter = array())
	{
		$ret = array ();

		$computer_id = (!empty($filter['computer_id'])) ? $filter['computer_id'] : null;
		$item_id = 1044;		// The ID of the item storing backup info
		$field_good_id = 206;		// The monitoring item field ID for storing the last good date

		$divisor = 24 * 3600;

		// Set default filtering
		if ($filter['month']) $months = array (TBL_COMPUTERS_ITEMS_LOG.'_'.$filter['month']);
		elseif ($filter['month_start'] and $filter['month_end'])
		{
			$months = array ();
			$month = $filter['month_start'];
            $runaway_check = 0;
			while ($month <= $filter['month_end'] and ($runaway_check++ < 100))
			{
				$months[] = TBL_COMPUTERS_ITEMS_LOG.'_'.$month;
				list ($year, $month) = preg_split('/_/', $month, 2);
				if ($month++ > 11) {$year++; $month=1;}
				$month = $year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT);

			}
		}
		else $months = array(TBL_COMPUTERS_ITEMS_LOG);

		$history = array ();

		foreach ($months as $tbl)
		{
            if(!db::db_fetch_vector("show tables like '" . $tbl . "'")) continue;
			$q = 'SELECT max(reported) as date, max(value), ';
			$q.= 'year(from_unixtime(reported)) as year, month(from_unixtime(reported)) as month, dayofmonth(from_unixtime(reported)) as day ';
			$q.= 'FROM '.$tbl.' WHERE ';
			$q.= 'computer_id='.$computer_id.' AND item_id='.$item_id.' AND field_id='.$field_good_id.' ';
			$q.= 'GROUP BY year, month, day ';
			$q.= 'ORDER BY year, month, day';

            $history+= db::db_fetch_list ($q);
		}

		$backups = array ();
		$prev_bk = time();
		if (!empty($history))
		{

			// Round all the times to day
			foreach ($history as $reported => $last_bk)
			{
				$reported = strtotime(date ('d M Y 00:00', $reported));
				$last_bk = strtotime(date ('d M Y 00:00', $last_bk));

				$backups[$reported] = $last_bk;
				if ($last_bk and $last_bk<$prev_bk) $prev_bk = $last_bk;
			}

			$max_time = max(array_keys($backups));
			$min_time = min(array_keys($backups));

			for ($time=$min_time; $time<=$max_time; $time = strtotime ('+1 day', $time))
			{
				if ($backups[$time])
				{
					$age = round (($time-$backups[$time])/$divisor);
					$prev_bk = $backups[$time];
				}
				else
				{
					$age = round (($time-$prev_bk)/$divisor);
				}
				if ($age<0) $age= 0;
				$ret[$time] = $age;
			}
		}

        if($history) $history = null;
        if($backups) $backups = null;
		if ($filter['sort_dir'] == 'DESC') krsort ($ret);
		else ksort ($ret);

		return $ret;
	}

	/**
	* Returns an array with the history of backup sizes (per day).
	* Can be called as class method too.
	* @param	array	$filter			Filtering criteria, the fields can be:
	*						- computer_id: the computer ID, required when called as class method
	*						- month: a log table suffix (YYYY_DD)
	*						- sort_dir: the direction of sorting (ASC/DESC)
	* @return	array				Associative array, the keys being dates and the
	*						values being the backup size at those dates.
	*/
	public static function get_backups_sizes ($filter = array())
	{
		$ret = array ();

		$computer_id = (!empty($filter['computer_id'])) ? $filter['computer_id'] : null;
		$item_id = 1044;		// The ID of the item storing backup info
		$field_good_id = 202;		// The monitoring item field ID for storing the backup size

		// Set default filtering
		if ($filter['month']) $months = array (TBL_COMPUTERS_ITEMS_LOG.'_'.$filter['month']);
		elseif ($filter['month_start'] and $filter['month_end'])
		{
			$months = array ();
			$month = $filter['month_start'];
            $runaway_check = 0;
			while ($month <= $filter['month_end'] and ($runaway_check++ < 100))
			{
				$months[] = TBL_COMPUTERS_ITEMS_LOG.'_'.$month;
				list ($year, $month) = preg_split ('/_/', $month, 2);
				if ($month++ > 11) {$year++; $month=1;}
				$month = $year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT);

			}
		}
		else $months = array(TBL_COMPUTERS_ITEMS_LOG);

		// Collect the max backup sizes, for each month, by day
		$history = array ();
		foreach ($months as $tbl)
		{
            if(!db::db_fetch_vector("show tables like '" . $tbl . "'")) continue;
			$q = 'SELECT max(reported) as date, max(value), ';
			$q.= 'year(from_unixtime(reported)) as year, month(from_unixtime(reported)) as month, dayofmonth(from_unixtime(reported)) as day ';
			$q.= 'FROM '.$tbl.' WHERE ';
			$q.= 'computer_id='.$computer_id.' AND item_id='.$item_id.' AND field_id='.$field_good_id.' ';
			$q.= 'GROUP BY year, month, day ';

			$history+= db::db_fetch_list ($q);
		}

		$backups = array ();
		$prev_size = 0;
		if (!empty($history))
		{
			// Round all the times to day
			foreach ($history as $time => $size)
			{
				$round_time = strtotime(date ('d M Y 00:00', $time));
				$backups[$round_time] = $size;
			}

			// Fill in the gaps for the days where we don't have backups
			// In $ret, the dates will automatically be generated in ascending order
			$min_time = min(array_keys($backups));
			$max_time = max(array_keys($backups));
			for ($time=$min_time; $time<=$max_time; $time = strtotime ('+1 day', $time))
			{
				if (isset($backups[$time])) $prev_size = $ret[$time] = $backups[$time];
				else $ret[$time] = $prev_size;
			}
		}

		if ($filter['sort_dir'] == 'DESC') krsort ($ret);

        if($history) $history = null;
        if($backups) $backups = null;
        
		return $ret;
	}


	/**
	* Returns an array with the antivirus updates ages. Can be called as class method too.
	* @param	array	$filter			Filtering criteria, the fields can be:
	*						- computer_id: the computer ID, required when called as class method
	*						- month: a log table suffix (YYYY_DD)
	*						- sort_dir: the direction of sorting (ASC/DESC)

	* @return	array				Associative array, the keys being dates and the
	*						values being the age of the backup at those specific dates
	*/
	public static function get_av_history ($filter = array())
	{
		$ret = array ();

		$computer_id = (!empty($filter['computer_id'])) ? $filter['computer_id'] : null;
		$item_id = 1025;		// The ID of the item storing AV updates info
		$field_good_id = 24;		// The monitoring item field ID for storing the last good date

		$divisor = 24 * 3600;

		// Set default filtering
		if ($filter['month']) $months = array (TBL_COMPUTERS_ITEMS_LOG.'_'.$filter['month']);
		elseif ($filter['month_start'] and $filter['month_end'])
		{
			$months = array ();
			$month = $filter['month_start'];
            $runaway_check = 0;
			while ($month <= $filter['month_end'] and ($runaway_check++ < 100))
			{
				$months[] = TBL_COMPUTERS_ITEMS_LOG.'_'.$month;
				list ($year, $month) = preg_split('/_/', $month, 2);
				if ($month++ > 11) {$year++; $month=1;}
				$month = $year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT);

			}
		}
		else $months = array(TBL_COMPUTERS_ITEMS_LOG);

		$history = array ();

		foreach ($months as $tbl)
		{
            if(!db::db_fetch_vector("show tables like '" . $tbl . "'")) continue;
			$q = 'SELECT max(reported) as date, max(value), ';
			$q.= 'year(from_unixtime(reported)) as year, month(from_unixtime(reported)) as month, dayofmonth(from_unixtime(reported)) as day ';
			$q.= 'FROM '.$tbl.' WHERE ';
			$q.= 'computer_id='.$computer_id.' AND item_id='.$item_id.' AND field_id='.$field_good_id.' ';
			$q.= 'GROUP BY year, month, day ';
			$q.= 'ORDER BY year, month, day';

            $history+= db::db_fetch_list ($q);
		}

		$updates = array ();
		$prev_update = time ();
		if (!empty($history))
		{
			// Round all the times to day
			foreach ($history as $reported => $last_update)
			{
				$reported = strtotime(date ('d M Y 00:00', $reported));
				$last_update = strtotime(date ('d M Y 00:00', $last_update));

				$updates[$reported] = $last_update;
				if ($last_update and $last_update<$prev_update) $prev_update = $last_update;
			}

			$max_time = max(array_keys($updates));
			$min_time = min(array_keys($updates));

			for ($time=$min_time; $time<=$max_time; $time = strtotime ('+1 day', $time))
			{
				if ($updates[$time])
				{
					$age = round (($time-$updates[$time])/$divisor);
					$prev_update = $updates[$time];
				}
				else
				{
					$age = round (($time-$prev_update)/$divisor);
				}
				if ($age<0) $age= 0;
				$ret[$time] = $age;
			}
		}

		if ($filter['sort_dir'] == 'DESC') krsort ($ret);
		else ksort ($ret);

		return $ret;
	}


	/** [Class Method] Returns the status of the latest AV updates for a customer
	* @param	array	$filter				Associative array with filtering criteria. Can contain:
	*							- customer_id: (Required) The ID of a customer - will fetch data for all
	*							  computers that have AV monitoring in their profiles
	*							- date: Specifies the date for which to fetch the data. If not
	*							  specified, it will fetch the current data.
	* @return	array					Associative array, they keys being computer IDs and the values being the
	*							dates when the AV was last updated on those computers.
	*/
	public static function get_av_status ($filter = array ())
	{
		$ret = array ();
		$av_item_id = 1025;
		$av_update_field_id = 24;

		$customer_id = $filter['customer_id'];
		if (!$filter['date']) $filter['date'] = time ();
		$date = $filter['date'];

		$computer_ids = array ();
		if ($customer_id)
		{
			// Get the list of computers that have AV monitoring enabled
			$q = 'SELECT DISTINCT c.id FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS.' pi ';
			$q.= 'ON c.profile_id=pi.profile_id AND item_id='.$av_item_id.' ';
			$q.= 'WHERE c.customer_id='.$customer_id.' ORDER BY c.id ';
			$computer_ids = db::db_fetch_vector ($q);

			// Create the list which will store the dates
			foreach ($computer_ids as $id) $ret[$id] = null;

			// Compose the list of database tables which might contain the needed info, depending on
			// the date set for filtering. Try the current items first
			$tables = array ();
			$q = 'SELECT min(reported) as min_date FROM '.TBL_COMPUTERS_ITEMS.' WHERE item_id='.$av_item_id;
			$min_date = db::db_fetch_field ($q, 'min_date');
			if ($min_date and $min_date <= $date) $tables[] = TBL_COMPUTERS_ITEMS;
			//$tables = array_merge ($tables, Computer::get_all_log_months (array ('customer_id' => $customer_id, 'item_id' => $av_item_id, 'max_date' => $date)));
			$tbls = Computer::get_all_log_months (array ('customer_id' => $customer_id, 'item_id' => $av_item_id, 'max_date' => $date));
			foreach($tbls as $t)
			{
			    $tables[] = TBL_COMPUTERS_ITEMS_LOG.'_'.$t;
			}

			// Now go over the list of tables with data, fetching the data
			// for each computer, until all computers have data or the tables list is exhausted
			foreach ($tables as $table)
			{
				foreach ($computer_ids as $id)
				{
					if (is_null($ret[$id]))
					{
						$q = 'SELECT max(i.value) as value FROM '.$table.' i WHERE ';
						$q.= 'i.computer_id='.$id.' AND i.item_id='.$av_item_id.' AND i.field_id='.$av_update_field_id.' ';
						$q.= 'AND i.reported<='.$date;

 						if (($value = db::db_fetch_field ($q, 'value')))
 						{
 							$ret[$id] = $value;
 						}
					}
				}

				// Check if there are still computer for which the last AV update date is not known
				if (!in_array (null, $ret)) break;
			}
		}

		return $ret;
	}

	/**
	 * [Class method]
	 *
	 * @param string $serial
	 */
	public static function get_serials_numbers($serial="")
	{
		$query = "SELECT cust.name, cust.id as cid, c.id, c.netbios_name, ci.value FROM ".TBL_COMPUTERS_ITEMS." ci inner join ".TBL_COMPUTERS." c on c.id=ci.computer_id inner join ".TBL_CUSTOMERS." cust on c.customer_id=cust.id";
		$query.= " WHERE ci.field_id=207 ";
		if($serial != "")
			$query.= "AND value like '".$serial."%' ";
		$query.= "AND ci.item_id=".WARRANTY_ITEM_ID." AND ci.nrc=0";

		$data = DB::db_fetch_array($query);
		return $data;

	}

	/** [Class Method] Gets warranties information for computers belonging to a certain customer. The returned
	* computers are sorted by type and then by name.
	* NOTE: Only physical computers are returned. VMWare machines are ignored.
	*/
	public static function get_warranties ($filter = array ())
	{
		class_load ('Warranty');
		$ret = array ();

		// Fetch the list of VMWare computers for the customer
		$q = 'SELECT DISTINCT i.computer_id FROM '.TBL_COMPUTERS_ITEMS.' i ';
		if ($filter['customer_id']) $q.= ' INNER JOIN '.TBL_COMPUTERS.' c ON i.computer_id=c.id AND c.customer_id='.$filter['customer_id'].' ';
		$q.= 'WHERE i.item_id='.BRAND_ITEM_ID.' AND i.value="'.VMWARE_BRAND_MARKER.'" ';
		$vmware_ids = DB::db_fetch_vector ($q);

		// Get the IDs and NRCs for all computers that have warranty information defined
		$q = 'SELECT DISTINCT i.computer_id, i.nrc FROM '.TBL_COMPUTERS_ITEMS.' i ';
		if ($filter['customer_id']) $q.= ' INNER JOIN '.TBL_COMPUTERS.' c ON i.computer_id=c.id AND c.customer_id='.$filter['customer_id'].' ';

		$q.= 'WHERE i.item_id='.WARRANTY_ITEM_ID.' ';
		if ($filter['computer_id']) $q.= ' AND i.computer_id='.$filter['computer_id'].' ';
		$q.= 'ORDER BY i.computer_id, i.nrc';

		//debug($q);

		$data = DB::db_fetch_array ($q);
		//debug($data);
		$warranties = array ();
		foreach ($data as $d)
		{
			if (!in_array($d->computer_id, $vmware_ids))
			{
				$warranties[$d->computer_id][] = new Warranty (WAR_OBJ_COMPUTER, $d->computer_id, $d->nrc);
			}
		}
        if($data) $data = null;

		$computers_count = count(array_unique(array_keys($warranties)));

		$warranties = array_unique(array_keys($warranties));
		// If a customer ID is specified and there is more than a computer, sort the list by computer name. We use this method because it's faster
		// than ordering in the query (would required additional joins)
		if ($filter['customer_id'] and $computers_count > 1)
		{
			$filter_computers = array('customer_id' => $filter['customer_id'], 'order_by'=>'type');
			if ($filter['order_by'] == 'asset_no') $filter_computers['order_by'] = 'asset_no';
			$computers_list = Computer::get_computers_list ($filter_computers);
			foreach ($computers_list as $computer_id => $computer_name)
			{
				// Include in result only non-VMWare machines
				if (!in_array($computer_id, $vmware_ids))
				{
					if (isset($warranties[$computer_id]))
					{
						foreach ($warranties[$computer_id] as $w) $ret[] = $w;
					}
					else $ret[] = new Warranty (WAR_OBJ_COMPUTER, $computer_id, 0);
				}
			}
            if($computers_list) $computers_list = null;
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
		if($warranties) $warranties = null;
		return $ret;
	}

	/**
	 * [Class Method]
	 * Returns a list with all the computers which have backup reporting in the profile
	 *
	 *
	 * @param array $filter				- a search criteria
	 * @param int $count				- the number of records
	 * @param int $alert_level			- an alert level
	 *
	 * @return array(Computer)			- an array with the requested computers
	 */
	public static function get_computers_backup_statuses($filter = array(), $alert_level, &$count)
	{
		//xxxx removable
		//the ideea would be to get the bcakup statuses by the alert level
		//this means that we
		$ret = array();

		//XXX 1.get all profiles with backup reporting enabled
		$b_profs = array();
		$query_profs = "select p.id from ".TBL_MONITOR_PROFILES." p, ".TBL_MONITOR_PROFILES_ITEMS." mip, ".TBL_MONITOR_ITEMS." mi ";
		$query_profs .= "where p.id = mip.profile_id and mip.item_id = mi.id and mi.id in (1044, 2004)";

		$b_profs = db::db_fetch_vector($query_profs);



		$q = 'select DISTINCT c.id from '.TBL_COMPUTERS.' c INNER JOIN '.TBL_COMPUTERS_ITEMS.' cil ON c.id = cil.computer_id INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id = cust.id WHERE cil.item_id in (1044, 2004) ';

		$query  = 'SELECT c.id ';
		$query .= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_MONITOR_PROFILES.' p ON c.profile_id = p.id INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id = cust.id ';
		if(!$filter['profile_id'])
		{
			//get all profiles with backup reporting
			$query .= 'WHERE c.profile_id in (';
			$cnt =  count($b_profs);
			for($i=0; $i<$cnt;$i++)
			{
				if($i != $cnt-1)
					$query .= $b_profs[$i].", ";
				else
					$query .= $b_profs[$i].")";

			}
		}
		else
		{
			$q .= ' AND c.profile_id = '.$filter['profile_id'];
			$query .= ' WHERE c.profile_id = '.$filter['profile_id'];
		}
        $current_user = $GLOBALS['CURRENT_USER'];
		if(isset($current_user) and $current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $current_user->get_assigned_customers_list();
			$q.= ' AND c.customer_id in (';
			$query.= ' AND c.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) {
					$q.=$k.", ";
					$query.=$k.", ";
				}
				else
				{
					$q.=$k;
					$query.=$k;
				}
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$query = trim (preg_replace ('/,\s*$/', '', $query));
			$q.=")";
			$query.=")";
		}
		if($filter['customer_id'] > 0) //we have a customer filter set
		{
			$q .= ' AND c.customer_id = '.$filter['customer_id'];
			$query .= ' AND c.customer_id = '.$filter['customer_id'];
		}
		if($filter['order_by'] and $filter['order_dir'])
		{
			if($filter['order_by'] == 'customer')
			{
				$q .= ' ORDER BY cust.name '.$filter['order_dir'];
				$query .= ' ORDER BY cust.name '.$filter['order_dir'];
			}
			if($filter['order_by'] == 'computer_id')
			{
				$q .= ' ORDER BY c.id '.$filter['order_dir'];
				$query .= ' ORDER BY c.id '.$filter['order_dir'];
			}
			if($filter['order_by'] == 'computer_name')
			{
				$q .= ' ORDER BY c.netbios_name '.$filter['order_dir'];
				$query .= ' ORDER BY c.netbios_name '.$filter['order_dir'];
			}
		}

		//run this query and get a list of computers
		$ret_r = db::db_fetch_vector($q);
		$ret_a = db::db_fetch_vector($query);
		foreach($ret_r as $id)
		{
			//$ret[] = new Computer($id);
			$computer = new Computer($id);
			$status = $computer->backup_status();
			$sts = $status[0]->get_specific_value('Status');
			if($alert_level == BACKUP_STATUS_SUCCESS)
			{
				if($sts == 'success' || $sts =='info')
				{
					$ret[] = $computer;
				}
			}
			elseif($alert_level == BACKUP_STATUS_TAPE_ERROR)
			{
				if($sts == 'error' || $sts == 'unexpected')
				{
					$msg = $status[0]->get_specific_value('Message');
					$needle1 = 'tape';
					$needle2 = 'Tape';
					$isO1 = strpos($msg, $needle1);
					$isO2 = strpos($msg, $needle2);
					if($isO1 !== FALSE || $isO2 !== FALSE ) $ret[] = $computer;
				}
			}
			elseif ($alert_level == BACKUP_STATUS_ERROR)
			{
				if($sts == 'error' || $sts == 'unexpected')
				{
					$msg = $status[0]->get_specific_value('Message');
					$needle1 = 'tape';
					$needle2 = 'Tape';
					$isO1 = strpos($msg, $needle1);
					$isO2 = strpos($msg, $needle2);
					if($isO1 === FALSE && $isO2 === FALSE) $ret[] = $computer;
				}
			}
		}
		if($alert_level == BACKUP_STATUS_NOT_REPORTING)
		{
			foreach ($ret_a as $idd)
			{
				if(!in_array($idd, $ret_r))
				{
					$ret[] = new Computer($idd);
				}
			}
		}
		return $ret;
	}

	function  backup_status()
	{
		$ret = array();
		class_load('ComputerItem');
		class_load('MonitorProfile');

		$this->profile_id == 14 ? $item_id=1044 : $item_id = 2004;

		$q = 'SELECT item_id, log_type FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE item_id='.$item_id;
		$m_profiles = DB::db_fetch_list ($q);
		$m_profile = $m_profiles[$item_id];

		$ret[] = new ComputerItem($this->id, $item_id);

		// Check the items for which there are logs
		$q = 'SELECT DISTINCT item_id FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id;
		$logged_items = db::db_fetch_vector ($q);

		if ($m_profile==MONITOR_LOG_CHANGES or $m_profile==MONITOR_LOG_ALL)
		{
			$ret[0]->log_enabled = true;
			$ret[0]->has_logs = in_array ($item_id, $logged_items);
		}
		return $ret;
	}

	/**
	 * [Class Method]
	 * Returns a list with all the computers which have antivirus reporting in the profile
	 *
	 *
	 * @param array $filter				- a search criteria
	 * @param int $alert_level			- an alert level
	 * 									possible values
	 * 									ANTIVIRUS_UPD_SUCCESS - computers with the antivirus updates that don't exceed one day (g	 *															reen)
	 * 									ANTIVIRUS_UPD_ONE_DAY - computers with antivirus updates that are older than one day but 	 *															don't exceed one week (orange)
	 * 									ANTIVIRUS_UPD_ONE_WEEK - computers with antivirus updates that are older than one week (r	 * 															ed)
	 * 									ANTIVIRUS_UPD_NOT_REPORTING - computers with antivirus updates in the profiles that are n	 *																	ot reporting anything
	 * @param int $count				- the number of records
	 *
	 * @return array(Computer)			- an array with the requested computers
	 */
	public static function get_computers_antivirus_statuses($filter = array(), $alert_level, &$count)
	{
		$ret = array();

		$one_day = 86400;
		$one_week = 604800;

		//XXX 1.get all profiles with antivirus reporting enabled
		$b_profs = array();
		$query_profs = "select p.id from ".TBL_MONITOR_PROFILES." p, ".TBL_MONITOR_PROFILES_ITEMS." mip, ".TBL_MONITOR_ITEMS." mi ";
		$query_profs .= "where p.id = mip.profile_id and mip.item_id = mi.id and mi.id = 1025";

		$b_profs = db::db_fetch_vector($query_profs);



		$q = 'select DISTINCT c.id from '.TBL_COMPUTERS.' c INNER JOIN '.TBL_COMPUTERS_ITEMS.' cil ON c.id = cil.computer_id  INNER JOIN '.TBL_CUSTOMERS.' cust on c.customer_id = cust.id WHERE cil.item_id = 1025 ';

		$query  = 'SELECT c.id ';
		$query .= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_MONITOR_PROFILES.' p ON c.profile_id = p.id INNER JOIN '.TBL_CUSTOMERS.' cust on c.customer_id = cust.id ';
		if(!$filter['profile_id'])
		{
			$query .= 'WHERE c.profile_id in (';
			$cnt =  count($b_profs);
			for($i=0; $i<$cnt;$i++)
			{
				if($i != $cnt-1)
					$query .= $b_profs[$i].", ";
				else
					$query .= $b_profs[$i].")";

			}
		}
		else
		{
			$q .= ' AND c.profile_id = '.$filter['profile_id'];
			$query .= ' WHERE c.profile_id = '.$filter['profile_id'];
		}

        $current_user = $GLOBALS['CURRENT_USER'];

		if(isset($current_user) and $current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $current_user->get_assigned_customers_list();
			$q.= ' AND c.customer_id in (';
			$query.= ' AND c.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) {
					$q.=$k.", ";
					$query.=$k.", ";
				}
				else
				{
					$q.=$k;
					$query.=$k;
				}
			}
			$q.=")";
			$query.=")";
		}

		if($filter['customer_id'] > 0) //we have a customer filter set
		{
			$q .= ' AND c.customer_id = '.$filter['customer_id'];
			$query .= ' AND c.customer_id = '.$filter['customer_id'];
		}
		if($filter['order_by'] and $filter['order_dir'])
		{
			if($filter['order_by'] == 'customer')
			{
				$q .= ' order by cust.name '.$filter['order_dir'];
				$query .= ' order by cust.name '.$filter['order_dir'];
			}
			if($filter['order_by'] == 'computer_id')
			{
				$q .= ' order by c.id '.$filter['order_dir'];
				$query .= ' order by c.id '.$filter['order_dir'];
			}
			if($filter['order_by'] == 'computer_name')
			{
				$q .= ' order by c.netbios_name '.$filter['order_dir'];
				$query .= ' order by c.netbios_name '.$filter['order_dir'];
			}
		}

		//run this query and get a list of computers
		$ret_r = db::db_fetch_vector($q);
		$ret_a = db::db_fetch_vector($query);
		foreach($ret_r as $id)
		{
			$computer = new Computer($id);
			if($alert_level == ANTIVIRUS_UPD_SUCCESS)
			{
				$citem = new ComputerItem($id, 1025);
				$is_success = true;
				foreach($citem->val as $valobj)
				{
					$last_upd = $valobj->value[24];
					$now = time();
					if($now - $last_upd <= $one_day)
						$is_success &= true;
					else
						$is_success &= false;
				}
			}
			if($alert_level == ANTIVIRUS_UPD_ONE_DAY)
			{
				$citem = new ComputerItem($id, 1025);
				$is_success = false;
				foreach($citem->val as $valobj)
				{
					$last_upd = $valobj->value[24];
					$now = time();
					$tm = $now - $last_upd;
					if($tm > $one_day && $tm <= $one_week)
					{
						$is_success = true;
					}
					else if($tm < $one_day && $is_success)
					{
						$is_success = true;
					}
					else if($tm > $one_week && $is_success)
					{
						$is_success = false;
					}
					else
					{
						$is_success &= false;
					}
				}
			}
			if($alert_level == ANTIVIRUS_UPD_ONE_WEEK)
			{
				$citem = new ComputerItem($id, 1025);
				$is_success = false;
				foreach($citem->val as $valobj)
				{
					$last_upd = $valobj->value[24];
					$now = time();
					$tm = $now - $last_upd;
					if($tm > $one_week)
						$is_success = true;
				}
			}
			if($is_success)
			{
				$infos = array('computer'=>$computer, 'av_infos'=>$citem->val);
				$ret[] = $infos;
			}
		}
		if($alert_level == ANTIVIRUS_UPD_NOT_REPORTING)
		{
			foreach ($ret_a as $idd)
			{
				if(!in_array($idd, $ret_r))
				{
					$citem = new ComputerItem($idd, 1025);
					$computer = new Computer($idd);
					$infos = array('computer'=>$computer, 'av_infos'=>$citem->val);
					$ret[] = $infos;
				}
			}
		}
		return $ret;
	}

	/**
	 * Search tool for computers by a specified criteria
	 *
	 * @param associative array $condition
	 * @return array 				Returns an array with the computers id's
	 */
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
			if($condition['current_computer_items'] && is_array($condition['current_computer_items']) &&  count($condition['current_computer_items']) > 0)
			{
				$look_in = $condition['current_computer_items'];
				$b_advanced = true;
			}
			if(!isset($customer_id))
				$query = "select distinct computer_id from ".TBL_COMPUTERS_ITEMS." where value like '".$search."%' ";
			else
				$query = "select distinct ci.computer_id from ".TBL_COMPUTERS_ITEMS." ci inner join ".TBL_COMPUTERS." c on c.id=ci.computer_id  where c.customer_id=".$customer_id." and value like '".$search."%' ";

			if($b_advanced)
			{
				$q_add = "(";
				foreach ($look_in as $citem)
				{
					$q_add .= $citem.",";
				}
				$q_add = substr($q_add, 0, strlen($q_add)-1);
				$q_add .= ")";
				$query .= " and item_id in ".$q_add;
			}
		}

		//extract an array with all this computers
		if($query!="")
			$ret = db::db_fetch_vector($query);
		return $ret;
	}

	function search_items($condition = array())
	{
		$ret = array();
		$query = "select distinct mi.name, ci.value from ".TBL_COMPUTERS_ITEMS." ci inner join ".TBL_MONITOR_ITEMS." mi on mi.id = ci.item_id and value like '".$condition['search']."%'";
		if($condition['current_computer_items'])
		{
			$look_in = $condition['current_computer_items'];
			$q_add = "(";
			foreach ($look_in as $citem)
			{
				$q_add .= $citem.",";
			}
			$q_add = substr($q_add, 0, strlen($q_add)-1);
			$q_add .= ")";
			$query .= " and item_id in ".$q_add;
		}
		$ret = db::db_fetch_array($query);
		//debug($query);
		return $ret;

	}

	public static function generate_mremote_connection_file($filter=array())
	{
		//get the public servers (id and remote_ip)
		if(!$filter['customer_id'])
		{
			$query = "select distinct c.remote_ip, c.id, cust.id, cust.name from ".TBL_COMPUTERS." c inner_join ".TBL_CUSTOMERS." cust on c.customer_id=cust.id where c.profile_id in (1,5,7,8,14,15) and c.remote_ip<>'' and remote_ip not like '192.168.%' and c.remote_ip not like '0.%' order by cust.id";
		}
		else {
			$query = "select distinct c.remote_ip, c.id, cust.id, cust.name from ".TBL_COMPUTERS." c inner_join ".TBL_CUSTOMERS." cust on c.customer_id=cust.id where cust.id=".$filter['customer_id']." c.profile_id in (1,5,7,8,14,15) and c.remote_ip<>'' and remote_ip not like '192.168.%' and c.remote_ip not like '0.%' order by cust.id";
		}

		$comps = db::db_fetch_array($query);
		/*if(count($comps)!=0)
		{
			foreach($comps as $c)
			{
				//now we have all the computers, should get
			}
		}
		*/

	}

	/**
	 * [Class Method] gets a list of computers by their type
	 *
	 * @pram array(mixed) $filter	-	possible values are customer_id
	 * @return array(mixed)
	 * */
	function get_computers_list_by_type($filter=array())
	{
		$q = "select distinct type from ".TBL_COMPUTERS;
		$stats = db::db_fetch_vector($q);
		$ret = array();
		foreach($stats as $stat)
		{
			$query = "select id, netbios_name from ".TBL_COMPUTERS." where type=".$stat;
			if(isset($filter['customer_id'])) $query.=" AND customer_id=".$filter['customer_id'];
			$cl = db::db_fetch_list($query);
			if(count($cl) > 0)
				$ret[$stat] = $cl;
		}
		return $ret;
	}
	
	/**
	 * [Class Method] returns true if this computer is marked as stolen
	 * @param <int> $computer_id
	 */
	public static function is_computer_stolen($computer_id)
	{
		$ret = false;

		if($computer_id){
			$query = "select computer_id from ".TBL_COMPUTER_STOLEN." where computer_id=".$computer_id;
			$cid = db::db_fetch_field($query,  'computer_id');
			if($cid) $ret = true;
		}
		return $ret;
	}

	function mark_stolen()
	{
		$query = "replace into ".TBL_COMPUTER_STOLEN." values (".$this->id.", unix_timestamp(NOW()), 0, 0)";
		//debug($query);
		db::db_query($query);
	}
	function unmark_stolen($computer_id=null)
	{
		if($computer_id)
		{
			$query = "delete from ".TBL_COMPUTER_STOLEN." where computer_id=".$computer_id;
			db::db_query($query);
		}
	}
    
    public static function get_customer_computers_brands($customer_id)
     {
        class_load("ComputerItem");
        $ret = array();        
        $query = "select id from ".TBL_COMPUTERS." where customer_id=".$customer_id;
        $ids = db::db_fetch_vector($query);
        if(count($ids) > 0){
            $comps = "(";
            foreach($ids as $id){
                 $comps .= $id.",";
            }    
            $comps = preg_replace("/,$/", "", $comps);
            $comps .=") " ;
            $query = "select value, count(value) as cnt from ".TBL_COMPUTERS_ITEMS." WHERE computer_id in ".$comps." AND item_id=1002 GROUP BY value";             
            $ret = db::db_fetch_list($query);
        }
        return $ret;
    }
    
    public static function get_customer_computers_oses($customer_id)
     {
        class_load("ComputerItem");
        $ret = array();        
        $query = "select id from ".TBL_COMPUTERS." where customer_id=".$customer_id;
        $ids = db::db_fetch_vector($query);
        if(count($ids) > 0){
            $comps = "(";
            foreach($ids as $id){
                 $comps .= $id.",";
            }    
            $comps = preg_replace("/,$/", "", $comps);
            $comps .=") " ;
            $query = "select value, count(value) as cnt from ".TBL_COMPUTERS_ITEMS." WHERE computer_id in ".$comps." AND item_id=1008 GROUP BY value";  
            $items = db::db_fetch_array($query);
            $itx = array();
            
            foreach($items as $item){
                $item->value = preg_replace("/\sSN:.*$/", "", $item->value);                
                $itx[] = array('val'=>$item->value, 'count'=>$item->cnt);
            }         
                       
            foreach($itx as $it){
                if(!in_array($it['val'], array_keys($ret))){
                    $ret[$it['val']] = $it['count'];
                }
                else{
                    $ret[$it['val']] += $it['count'];
                }                
            }
            //$ret = db::db_fetch_list($query);
        }
        return $ret;
    }
    
    public static function get_customer_computers_disk_space($customer_id)
     {
        class_load("ComputerItem");
        $ret = array();        
        $query = "select id from ".TBL_COMPUTERS." where customer_id=".$customer_id;
        $ids = db::db_fetch_vector($query);
        foreach ($ids as $id){
            $citem = new ComputerItem($id, 1013);
            foreach($citem->val as $val){
                $ret[$id][$val->value[9]] = array('free'=>$val->value[13], 'size'=>$val->value[12]);
            }
        }
        return $ret;
    }
    
    public static function get_computers_evolution($customer_id){
        $mon_names = array(1=>"Jan", 2=>"Feb", 3=>"Mar", 4=>"Apr", 5=>"May", 6=>"Jun", 7=>"Jul", 8=>"Aug", 9=>"Sep", 10=>"Oct", 11=>"Nov", 12=>"Dec");
    
        $query = 'select min(date_created) as min_crd from '.TBL_COMPUTERS.' where customer_id='.$customer_id.' and date_created > 0';
        $min_crd = db::db_fetch_field($query, 'min_crd');
        
        $mk = getdate($min_crd);
        $current = mktime(0,0,0,date("m"), date("d"), date("Y"));
        $last_date = mktime(0,0,0, $mk['mon'], 1, $mk['year']);
        while($last_date < $current){
            $ld = getdate($last_date);
            $ret['months'][] =  date('M y', $last_date);//$ld['month']." ".$ld['year'];
            $end_date = mktime(0,0,0, $ld['mon']+1, 1, $ld['year']);            
            $query = "select count(id) as cnt from ".TBL_COMPUTERS." where customer_id=".$customer_id." AND date_created <".$end_date." AND type=10";
            //debug($query);
            $ret['servers'][] = intval(db::db_fetch_field($query, 'cnt'));
            $query = "select count(id) as cnt from ".TBL_COMPUTERS." where customer_id=".$customer_id." AND date_created <".$end_date." AND type=5";
            $ret['workstations'][]=intval(db::db_fetch_field($query, 'cnt'));
            $query = "select count(id) as cnt from ".TBL_COMPUTERS." where customer_id=".$customer_id." AND date_created <".$end_date." AND type<>5 and type<>10";
            $ret['other'][]=intval(db::db_fetch_field($query, 'cnt'));
            $last_date = $end_date;
        }    
    
        return $ret;
        
    }

	function stolen_messages_sent()
	{
		$query = "select alert_raised from ".TBL_COMPUTER_STOLEN." where computer_id=".$this->id;
		$alr = db::db_fetch_field($query, 'alert_raised');
		if($alr > 0) return true;
		return false;
	}

	function raise_stolen_reporting_alert(){
		//first we need to get the recipients for this notification
		//1. get the keysource recipients for the customer
		do_log("checking if alerts were sent");
		if(!$this->stolen_messages_sent()){
			do_log("sending the alerts");
			class_load('Customer');
			class_load('InfoRecipients');
			class_load('CustomerCCRecipient');
			class_load('User');
			class_load('MessageLog');

			do_log("customer: ".$this->customer_id);
			$recipients = array();
			$no_total = 0;
			$customer_notif_recips = InfoRecipients::get_customer_recipients (array('customer_id' => $this->customer_id), $no_total);
			$customer_notif_recips = $customer_notif_recips[$this->customer_id];

			do_log("customer recip notifs");
			foreach($customer_notif_recips as $cr)
			{
				$recipients = array_merge($recipients, $cr);
			}
			do_log("customer recip notifs merged");

			$default_recipients = InfoRecipients::get_customer_default_recipients ($this->customer_id);

			do_log("default recips");
			foreach($default_recipients as $dr)
			{
				$recipients = array_merge($recipients, $dr);
			}


			do_log("default recips merged");

			$recipients_customers = InfoRecipients::get_customer_recipients_customers (array('customer_id' => $this->customer_id), $no_total);
			$recipients = array_merge($recipients, $recipients_customers);
			$default_recipients_customers = InfoRecipients::get_customer_default_recipients_customers ($this->customer_id);
			$recipients = array_merge($recipients, $default_recipients_customers);
			$cc_recipients = CustomerCCRecipient::get_cc_recipients ($this->customer_id);
			foreach($cc_recipients as $cc) $recipients[] = $cc->id;

			$parser = new BaseDisplay();
			//do_log("parser ".$parser);
			$tpl = "_classes_templates/computer/msg_stolen.tpl";
			$tpl_subject = '_classes_templates/computer/msg_stolen_subject.tpl';
			$parser->assign('computer', $this);

			$mail_sent = false;
			do_log("recipients: ".$recipients);
			foreach($recipients as  $rid){
				$recip = new User($rid);
				if($recip->email and $recip->is_active_strict()){
					do_log("emailing: ".$recip->email);
					$parser->assign('recip', $recip);
					$subject = $parser->fetch ($tpl_subject);
					$msg = $parser->fetch ($tpl);

					do_log("Subject: ".$subject);
					do_log("Message: ".$msg);
					$headers = 'From: '.SENDER_NAME.' <'.SENDER_EMAIL.'>'."\n";
					$headers.= 'Date: '.date("D, j M Y G:i:s O")."\n";

					$ms = @mail ($recipient->email, $subject, $msg, $headers);
					$mail_sent=$mail_sent | $ms;
					if($ms)
					{
						//we log here
					}
				}
			}

			if($mail_sent){
				//now we update the
				$query = "update ".TBL_COMPUTER_STOLEN." set alert_raised=1, date_alert=unix_timestamp(NOW()) where computer_id=".$this->id;
				db::db_query($query);
			}
		}
	}

    public static function get_computers_reporting_evo($start_period, $end_period, $customer_id){
        $ret = array();
        $sdate = getdate($start_period);
        $edate = getdate($end_period);
        $last_date = $start_period;
        $log_tables_query = "SHOW TABLES LIKE 'computers_items_log_%'";
        $log_tables = db::db_fetch_vector($log_tables_query);
        debug($log_tables);
        while($last_date <= $end_period){
            $ld = getdate($last_date);
            $next_date = mktime(0,0,0, $ld['mon']+1, 1, $ld['year']);
            
            $mon = $ld['mon'] < 10 ? '0'.$ld['mon'] : $ld['mon'];
            $year = $ld['year'];
            $log_table = "computers_items_log_".$year."_".$mon;
            if(in_array($log_table, $log_tables)){
                $ret['months'][] = $ld['month'];
                $query = "select distinct(id), count(distinct(value)) from ".$log_table." where item_id=1001 AND computer_id in (select id from ".TBL_COMPUTERS." where customer_id=".$customer_id.") group by computer_id";
               debug($query);
                $rep = db::db_fetch_list($query);
                $ret['reported'] = count($rep);
            }
            $last_date = $next_date;
        }
        return $ret;
    }
    
    
    public static function get_repo_stats($repo_margin=2, $customer_id=0){
        //type=10 - server
        //type=5 - server    
        $ret = array();
        $today = time();
        $today_gd = getdate($today);
        $start_today = mktime(0,0,0, $today_gd['mon'], $today_gd['mday'], $today_gd['year']);
        $end_today = mktime(23, 59, 59, $today_gd['mon'], $today_gd['mday'], $today_gd['year']);
        $start_margin = mktime(0,0,0, $today_gd['mon'], $today_gd['mday'] - $repo_margin, $today_gd['year']);
        $one_year_ago = mktime(0,0,0,$today_gd['mon'], $today_gd['mday'], $today_gd['year']-1);
        
        $query = "select count(id) as cnt from ".TBL_COMPUTERS." where last_contact between ".$start_today." and ".$end_today." and type=10";
        if($customer_id){
            $query.=" and customer_id=".$customer_id;
        }
        $ret['today_servers'] = db::db_fetch_field($query, 'cnt');
        
        $query = "select count(id) as cnt from ".TBL_COMPUTERS." where last_contact between ".$start_today." and ".$end_today." and type=5";
        if($customer_id){
            $query.=" and customer_id=".$customer_id;
        }
        $ret['today_workstations'] = db::db_fetch_field($query, 'cnt');
        
        $query = "select count(id) as cnt from ".TBL_COMPUTERS." where last_contact between ".$start_margin." and ".$start_today." and type=10";
        if($customer_id){
            $query.=" and customer_id=".$customer_id;
        }
        $ret['margin_servers'] = db::db_fetch_field($query, 'cnt');
        
        $query = "select count(id) as cnt from ".TBL_COMPUTERS." where last_contact between ".$start_margin." and ".$start_today." and type=5";
        if($customer_id){
            $query.=" and customer_id=".$customer_id;
        }
        $ret['margin_workstations'] = db::db_fetch_field($query, 'cnt');
        
        $query = "select count(id) as cnt from ".TBL_COMPUTERS." where last_contact between ".$one_year_ago." and ".$start_margin." and type=10";
        if($customer_id){
            $query.=" and customer_id=".$customer_id;
        }
        $ret['notreporting_servers'] = db::db_fetch_field($query, 'cnt');
        
        $query = "select count(id) as cnt from ".TBL_COMPUTERS." where last_contact between ".$one_year_ago." and ".$start_margin." and type=5";
        if($customer_id){
            $query.=" and customer_id=".$customer_id;
        }
        $ret['notreporting_workstations'] = db::db_fetch_field($query, 'cnt');
        
        $query = "select count(id) as cnt from ".TBL_COMPUTERS." where last_contact < ".$one_year_ago." and type=10";
        if($customer_id){
            $query.=" and customer_id=".$customer_id;
        }
        $ret['moreyearnr_servers'] = db::db_fetch_field($query, 'cnt');
        
        $query = "select count(id) as cnt from ".TBL_COMPUTERS." where last_contact < ".$one_year_ago." and type=5";
        if($customer_id){
            $query.=" and customer_id=".$customer_id;
        }
        $ret['moreyearnr_workstations'] = db::db_fetch_field($query, 'cnt');
        
        
        return $ret;
        
    }

    function verify_access() {
        $uid = get_uid();
        class_load('User');
        $user = new User($uid);
        if($user->type == USER_TYPE_CUSTOMER) {
            $customers_list = $user->get_users_customer_list();
            if(!in_array($this->customer_id, $customers_list)){            
                $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
		        header("Location: $url\n\n");
                exit;
            }
        }
    }
}

?>

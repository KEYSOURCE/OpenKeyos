<?php

/**
* Class for representing detailed network discoveries settings for customers.
*
* These objects will store per-computer and per-range settings for network discoveries.
* In other words, each object will represent a pair of a computer and an IP range on
* which that computer has to do the discovery.
*
*/

class DiscoverySettingDetail extends Base
{
	/** The unique numeric ID
	* @var int */
	var $id = null;
	
	/** The customer to which this setting belongs to. It is also used as link
	* to DiscoverySetting objects
	* @var int */
	var $customer_id = null;
	
	/** True or False if discovery for this range is enabled or not
	* @var bool */
	var $enabled = true;
	
	/** Comments
	* @var text */
	var $comments = '';
	
	/** The ID of the computer designated to do the discovery
	* @var int */
	var $computer_id = null;
	
	/** The first IP address in the range
	* @var string */
	var $ip_start = '';
	
	/** The last IP address in the range
	* @var string */
	var $ip_end = '';
	
	/** If True, the Kawacs Agent will not try to use WMI for fetching more
	* details about the discovered devices.
	* @var bool */
	var $disable_wmi = true;
	
	/** If True, the Kawacs Agent will not try to use SNMP for fetching more
	* details about the discovered devices
	* @var bool */
	var $disable_snmp = false;
	
	/** The ID of a ComputerPassword object to be used for WMI authentication
	* @var int */
	var $wmi_login_id = 0;
	
	/** The date when the discovery was last performed 
	* @var timestamp */
	var $last_discovery = 0;
	
	/** The duration of the last discovery process
	* @var timestamp */
	var $duration = 0;
	
	/** Flag indicating if a discovery has been manually requested
	* @var bool */
	var $request_update = false;
	
	
	/** The Netbios name of the associated computer
	* @var sring */
	var $computer_name = '';
	
	/** True if any of the discovery actions have not finished Ok
	* @var bool */
	var $discovery_errors = false;
	
	/** The WMI login name, read from the related ComputerPassword object - if any
	* @var string */
	var $wmi_login = '';
	
	/** The WMI password, read from the related ComputerPassword object - if any
	* @var string */
	var $wmi_password = '';
	
	
	/** The database table storing objects data 
	* @var string */
	var $table = TBL_DISCOVERIES_SETTINGS_DETAILS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'customer_id', 'enabled', 'comments', 'computer_id', 'ip_start', 'ip_end', 'disable_wmi', 'disable_snmp', 'wmi_login_id',
		'last_discovery', 'duration', 'request_update');
	
	
	/** Constructor, also loads an object's data if an ID is provided */
	function DiscoverySettingDetail ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	
	/** Loads the object data and the name of the associated computer */
	function load_data ()
	{
		parent::load_data ();
		if ($this->computer_id)
		{
			// Read the computer name
			$this->computer_name = $this->db_fetch_field ('SELECT netbios_name FROM '.TBL_COMPUTERS.' WHERE id='.$this->computer_id, 'netbios_name');
			
			// Check if there were any discovery errors
			$q = 'SELECT id FROM '.TBL_DISCOVERIES.' WHERE detail_id='.$this->id.' AND finished_ok=0 LIMIT 1';
			if ($this->db_fetch_field($q, 'id')) $this->discovery_errors = true;
			else $this->discovery_errors = false;
			
			// Load the WMI login name and password
			if ($this->wmi_login_id)
			{
				$q = 'SELECT login, password FROM '.TBL_COMPUTERS_PASSWORDS.' WHERE id='.$this->wmi_login_id;
				$d = $this->db_fetch_array ($q);
				$this->wmi_login = $d[0]->login;
				$this->wmi_password = $d[0]->password;
			}
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->customer_id) {error_msg($this->get_string('NEED_CUSTOMER')); $ret = false;}
		
		if (!$this->computer_id) {error_msg($this->get_string('NEED_COMPUTER')); $ret = false;}
		else
		{
			// Make sure the selected computer has a Windows Kawacs Agent installed
			$q = 'SELECT computer_id FROM '.TBL_COMPUTERS_AGENT_VERSIONS.' WHERE computer_id='.$this->computer_id.' LIMIT 1';
			if (!$this->db_fetch_field($q, 'computer_id'))
			{
				error_msg($this->get_string('NEED_COMPUTER_WINDOWS_AGENT'));
				$ret = false;
			}
		}
		
		if (!$this->ip_start or !$this->ip_end) {error_msg($this->get_string('NEED_IPS')); $ret = false;}
		else
		{
			$cnt_ips = count_ips ($this->ip_start, $this->ip_end);
			if ($cnt_ips < 1) {error_msg($this->get_string('NEED_VALID_RANGE')); $ret = false;}
			elseif ($cnt_ips > DISCOVERY_MAX_IPS_COUNT) {error_msg($this->get_string('TOO_MANY_IPS', MAX_DISCOVERY_IPS_COUNT)); $ret = false;}
		}
		
		if (!$this->disable_wmi and !$this->wmi_login_id)
		//(!$this->wmi_login or !$this->wmi_password))
		{
			error_msg ($this->get_string('NEED_WMI_CREDENTIALS'));
			$ret = false;
		}
		
		// Check uniqueness
		if ($this->customer_id and $this->computer_id and $this->ip_start and $this->ip_end)
		{
			$q = 'SELECT id FROM '.TBL_DISCOVERIES_SETTINGS_DETAILS.' WHERE customer_id='.$this->customer_id.' AND computer_id='.$this->computer_id.' AND ';
			$q.= 'ip_start="'.mysql_escape_string($this->ip_start).'" AND ip_end="'.mysql_escape_string($this->ip_end).'" ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			if ($this->db_fetch_field($q, 'id'))
			{
				error_msg ($this->get_string('NEED_UNIQUE_COMP_IPS'));
				$ret = false;
			}
		}
		
		return $ret;
	}
	
	/** Deletes this setting detail, as well as any discoveries made from it */
	function delete ()
	{
		if ($this->id)
		{
			// Delete discoveries
			$this->db_query ('DELETE FROM '.TBL_DISCOVERIES.' WHERE detail_id='.$this->id);
			
			// Delete the object itself
			parent::delete ();
		}
	}
	
	/** Returns True or False if a discovery needs to be made for this detail at this time */
	function needs_update ()
	{
		$ret = false;
		
		if ($this->id and $this->enabled)
		{
			$ret = ($this->last_discovery < (time()-DISCOVERY_INTERVAL) or $this->request_update);
		}
		
		return $ret;
	}
	
	/** Places a flag on this detail that a manual discovery request has been made - and saves the object to the database */
	function request_make ()
	{
		if ($this->id)
		{
			$this->request_update = true;
			$this->save_data ();
		}
	}
	
	/** Removes the flag for the manual discovery request - and saves the object to the database */
	function request_cancel ()
	{
		if ($this->id)
		{
			$this->request_update = 0;
			$this->save_data ();
		}
	}
	
	/** Marks (and saves to database) that a discovery has been performed for this object. Will also remove
	* the flag for manual discovery requests.
	* @param	timestamp			$time		The time when the discovery was done (default is current time)
	* @param	int				$duration	The duration of the discovery process
	*/
	function mark_contact ($time = 0, $duration = 0)
	{
		if (!$time) $time = time ();
		$this->last_discovery = $time;
		$this->duration = $duration;
		$this->request_update = false;
		$this->save_data ();
	}
	
	/* [Class Method] Returns an array with all the defined DiscoverySettingDetail objects associated with a customer, 
	* oredered by computer name and IP range.
	* @return array(DiscoverySettingDetail)
	*/
	function get_settings_details ($customer_id)
	{
		$ret = array ();
		if ($customer_id)
		{
			$q = 'SELECT dd.id FROM '.TBL_DISCOVERIES_SETTINGS_DETAILS.' dd INNER JOIN '.TBL_COMPUTERS.' c ';
			$q.= 'ON dd.computer_id=c.id WHERE dd.customer_id='.$customer_id.' ORDER BY c.netbios_name, dd.ip_start, dd.ip_end ';
			$ids = DB::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new DiscoverySettingDetail ($id);
		}
		
		return $ret;
	}
}
?>
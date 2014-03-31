<?php
class_load ('Computer');
class_load ('Customer');

/**
* Classes for handling quick contacts received from remote computers, that is
* contacts send from Kawacs Manager > Computer Info screen, which helps the tech
* support personnel in properly identifying what computer the customer is working on.
*
*/

class ComputerQuickContact extends Base
{
	/** The quick report ID
	* @var int */
	var $id = null;
	
	/** The time when the contact was made
	* @var timestamp */
	var $contact_time = 0;
	
	/** The computer ID - as reported by the remote computer
	* @var int */
	var $computer_id = null;
	
	/** The user name on the remote computer
	* @var string */
	var $user_name = '';
	
	/** The NrtBios name of the remote computer
	* @var string */
	var $computer_name = '';
	
	/** The manufacturer of the computer
	* @var string */
	var $computer_manufacturer = '';
	
	/** The model of the computer
	* @var string */
	var $computer_model = '';
	
	/** The serial number of the computer
	* @var string */
	var $computer_sn = '';
	
	/** The local IP address of the computer
	* @var string */
	var $net_local_ip = '';
	
	/** The gateway IP address of the computer
	* @var string */
	var $net_gateway_ip = '';
	
	/** The MAC address of the computer
	* @var string */
	var $net_mac_address = '';
	
	/** The remote (public) IP address of the computer, as viewed from Internet
	* @var string */
	var $net_remote_ip = '';
	
	
	/** The databas table storing customer data 
	* @var string */
	var $table = TBL_COMPUTER_QUICK_CONTACTS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'contact_time', 'computer_id', 'user_name', 'computer_name', 'computer_manufacturer', 'computer_model', 'computer_sn', 
		'net_local_ip', 'net_gateway_ip', 'net_mac_address', 'net_remote_ip');

	
	/**
	* Constructor, also loads the data from the database if an ID is specified
	* @param	int $id		The quick contact ID
	*/
	function ComputerQuickContact ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/**
	* Loads the quick contact data, as well as the computer data, if any is found
	*/
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data();
			if ($this->id)
			{
				if ($this->computer_id) $this->computer = new Computer ($this->computer_id);
				elseif ($this->net_mac_address) $this->computer = Computer::get_by_mac ($this->net_mac_address);
				
				if ($this->computer->customer_id)
				{
					$this->customer = new Customer($this->computer->customer_id);
				}
			}
		}
	}
	

	/** 
	* Saves data about from a quick report
	*/
	function save_data ()
	{
		if (!$this->id) $this->contact_time = time();
		$this->purge_old_contacts ();
		parent::save_data();
		//if($this->computer) $this->computer->contact_made($this->net_remote_ip, $this->computer_name);		
		if ($this->computer_id) 
		{
			$computer = new Computer ($this->computer_id);
			$computer->contact_made($this->net_remote_ip, $this->computer_name);		
		}
		elseif ($this->net_mac_address) 
		{
			$computer = Computer::get_by_mac($this->net_mac_address);
			$computer->contact_made($this->net_remote_ip, $this->computer_name);		
		}
	}
	
	
	/** [Class Method] Removes old contacts from the database */
	function purge_old_contacts ()
	{
		$q = 'DELETE FROM '.TBL_COMPUTER_QUICK_CONTACTS.' WHERE contact_time < ';
		$q.= (time() - (QUICK_CONTACTS_KEEP * 3600));
		db::db_query ($q);
	}
	
	
	/** 
	* [Class Method] Returns a list with the current contacts in the database
	*/
	function get_quick_contacts ()
	{
		$ret = array ();
		ComputerQuickContact::purge_old_contacts ();
		$q = 'SELECT id FROM '.TBL_COMPUTER_QUICK_CONTACTS.' ORDER BY contact_time DESC ';
		
		$ids = db::db_fetch_array ($q);
		foreach ($ids as $id) $ret[] = new ComputerQuickContact ($id->id);
		
		return $ret;
	}
	
}

?>

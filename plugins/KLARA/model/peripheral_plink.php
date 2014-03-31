<?php
/**
* Class for storing Plink settings used by users for peripherals.
*
* Each time a user makes a Plink tunnel from a Peripheral Remote Access page, the system
* will save the selection of services, ports etc. that he used, so they can be automatically
* reloaded next time the user visits that page.
* 
*/

class_load ('PeripheralPlinkService');

class PeripheralPlink extends Base
{
	/** Primary keys for plink table: user ID and peripheral ID
	* @var array */
	var $primary_key = array ('user_id', 'peripheral_id');
	
	/** The user ID
	* @var int */
	var $user_id = null;
	
	/** The peripheral ID
	* @var int */
	var $peripheral_id = null;
	
	/** The WAN IP gateway for port forwarding
	* @var string */
	var $public_ip = '';
	
	/** Port used for port forwarding 
	* @var string */
	var $pf_port = '22';
	
	/** Login name for port forwarding
	* @var string */
	var $pf_login = 'root';
	
	/** Password for port forwarding 
	* @var string */
	var $pf_password = '';
	
	/** The location of the Plink command
	* @var string */
	var $command_base = '';
	
	/** The base local port 
	* @var string */
	var $local_port = '';

	
	/** List of services - an associative array, keys being service IDs (0-net access, 1-web access) and 
	* the values being PeripheralPlinkService objects
	* @var array(PlinkService) */
	var $services = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PERIPHERAL_PLINK;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('user_id', 'peripheral_id', 'public_ip', 'pf_port', 'pf_login', 'pf_password', 'command_base', 'local_port');
	
	
	/** 
	* Contructor. Loads an object's values if user ID and computer ID are specified
	* @param	int		$user_id	A user ID
	* @param	int		$peripheral_id	A peripheral ID
	*/
	function PeripheralPlink ($user_id = null, $peripheral_id = null)
	{
		if ($user_id and $peripheral_id);
		{
			$this->user_id = $user_id;
			$this->peripheral_id = $peripheral_id;
			$this->load_data ();
		}
	}
	
	
	/** Load the object data, as well as its services */
	function load_data ()
	{
		parent::load_data ();
		if ($this->user_id and $this->peripheral_id)
		{
			$q = 'SELECT service_id FROM '.TBL_PERIPHERAL_PLINK_SERVICES.' WHERE user_id='.$this->user_id.' AND peripheral_id='.$this->peripheral_id.' ORDER BY 1';
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $service_id)
			{
				$this->services[$service_id] = new PeripheralPlinkService ($this->user_id, $this->peripheral_id, $service_id);
			}
		}
	}
	
	
	/** Sets and saves the list of services for this peripheral Plink setting */
	function set_services ($data = array ())
	{
		if ($this->user_id and $this->peripheral_id and is_array ($data))
		{
			// First, delete the previous saved services
			$q = 'DELETE FROM '.TBL_PERIPHERAL_PLINK_SERVICES.' WHERE user_id='.$this->user_id.' AND peripheral_id='.$this->peripheral_id;
			$this->db_query ($q);
			
			if (!is_array($data)) $data = array ();
			
			for ($i=1; $i<=2; $i++)
			{
				$service = new PeripheralPlinkService ();
				$service->user_id = $this->user_id;
				$service->peripheral_id = $this->peripheral_id;
				$service->service_id = $i;
				$service->load_from_array ($data[$i]);
				$service->save_data ();
			}
			
			$this->load_data ();
		}
	}
}

?>
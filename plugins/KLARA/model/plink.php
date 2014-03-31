<?php
/**
* Class for storing Plink settings used by users for computers.
*
* Each time a user makes a Plink tunnel from a Computer Remote Access page, the system
* will save the selection of services, ports etc. that he used, so they can be automatically
* reloaded next time the user visits that page.
* 
*/

class_load ('PlinkService');

class Plink extends Base
{
	/** Primary keys for plink table: user ID and computer ID
	* @var array */
	var $primary_key = array ('user_id', 'computer_id');
	
	/** The user ID
	* @var int */
	var $user_id = null;
	
	/** The computer ID
	* @var int */
	var $computer_id = null;
	
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

	
	/** List of services - an associative array, keys being service IDs (ComputerRemoteService) and 
	* the values being PlinkService objects
	* @var array(PlinkService) */
	var $services = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PLINK;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('user_id', 'computer_id', 'public_ip', 'pf_port', 'pf_login', 'pf_password', 'command_base', 'local_port');
	
	
	/** 
	* Contructor. Loads an object's values if user ID and computer ID are specified
	* @param	int		$user_id	A user ID
	* @param	int		$computer_id	A computer ID
	*/
	function Plink ($user_id = null, $computer_id = null)
	{
		if ($user_id and $computer_id);
		{
			$this->user_id = $user_id;
			$this->computer_id = $computer_id;
			$this->load_data ();
		}
	}
	
	
	/** Load the object data, as well as its services */
	function load_data ()
	{
		parent::load_data ();
		if ($this->user_id and $this->computer_id)
		{
			$q = 'SELECT service_id FROM '.TBL_PLINK_SERVICES.' WHERE user_id='.$this->user_id.' AND computer_id='.$this->computer_id.' ORDER BY 1';
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $service_id)
			{
				$this->services[$service_id] = new PlinkService ($this->user_id, $this->computer_id, $service_id);
				
			}
		}
	}
	
	
	/** Sets and saves the list of services for this Plink setting */
	function set_services ($data = array ())
	{
		if ($this->user_id and $this->computer_id and is_array ($data))
		{
			// First, delete the previous saved services
			$q = 'DELETE FROM '.TBL_PLINK_SERVICES.' WHERE user_id='.$this->user_id.' AND computer_id='.$this->computer_id;
			db::db_query ($q);
			
			if (is_array ($data['ids_map']))
			{
				if (!is_array($data['services'])) $data['services'] = array ();
				foreach ($data['ids_map'] as $idx => $service_id)
				{
					$service = new PlinkService ();
					$service->user_id = $this->user_id;
					$service->computer_id = $this->computer_id;
					$service->service_id = $service_id;
					$service->computer_ip = $data['computer_ip'][$idx];
					$service->computer_port = $data['computer_port'][$idx];
					$service->selected = in_array ($service_id, $data['services']);
					$service->save_data ();
				}
			}
			
			$this->load_data ();
		}
	}
}

?>
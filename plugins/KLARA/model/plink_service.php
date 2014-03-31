<?php
/**
* Class for representing the services selected for Plink objects
* 
*/
class PlinkService extends Base
{
	/** Primary keys for plink services table: user ID, computer ID and service ID
	* @var array */
	var $primary_key = array ('user_id', 'computer_id', 'service_id');

	/** The user ID
	* @var int */
	var $user_id = null;
	
	/** The computer ID
	* @var int */
	var $computer_id = null;
	
	/** The service ID (ComputerRemoteService)
	* @var int */
	var $service_id = null;
	
	/** The computer IP
	* @var string */
	var $computer_ip = '';
	
	/** The computer/service port
	* @var string */
	var $computer_port = '';
	
	/** Specifies if the service was selected or not in the Remote Access page
	* @var bool */
	var $selected = false;
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PLINK_SERVICES;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('user_id', 'computer_id', 'service_id', 'computer_ip', 'computer_port', 'selected');
	
	
	/** 
	* Contructor. Loads an object's values if user ID, computer ID and service ID are specified
	* @param	int		$user_id	A user ID
	* @param	int		$computer_id	A computer ID
	* @param	int		$service_id	A service ID
	*/
	function PlinkService ($user_id = null, $computer_id = null, $service_id = null)
	{
		if ($user_id and $computer_id and $service_id)
		{
			$this->user_id = $user_id;
			$this->computer_id = $computer_id;
			$this->service_id = $service_id;
			$this->load_data ();
		}
	}
}

?>
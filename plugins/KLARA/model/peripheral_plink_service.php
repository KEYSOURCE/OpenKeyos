<?php
/**
* Class for representing the services selected for PeripheralPlink objects
* 
*/
class PeripheralPlinkService extends Base
{
	/** Primary keys for plink services table: user ID, peripheral ID and service ID
	* @var array */
	var $primary_key = array ('user_id', 'peripheral_id', 'service_id');

	/** The user ID
	* @var int */
	var $user_id = null;
	
	/** The peripheral ID
	* @var int */
	var $peripheral_id = null;
	
	/** The service ID (1-net access, 2-web access)
	* @var int */
	var $service_id = null;
	
	/** The peripheral IP
	* @var string */
	var $peripheral_ip = '';
	
	/** The peripheral/service port
	* @var string */
	var $peripheral_port = '';
	
	/** Specifies if the service was selected or not in the Peripheral Remote Access page
	* @var bool */
	var $selected = false;
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PERIPHERAL_PLINK_SERVICES;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('user_id', 'peripheral_id', 'service_id', 'peripheral_ip', 'peripheral_port', 'selected');
	
	
	/** 
	* Contructor. Loads an object's values if user ID, peripheral ID and service ID are specified
	* @param	int		$user_id	A user ID
	* @param	int		$peripheral_id	A peripheral ID
	* @param	int		$service_id	A service ID
	*/
	function PeripheralPlinkService ($user_id = null, $peripheral_id = null, $service_id = null)
	{
		if ($user_id and $peripheral_id and $service_id)
		{
			$this->user_id = $user_id;
			$this->peripheral_id = $peripheral_id;
			$this->service_id = $service_id;
			$this->load_data ();
		}
	}
}
?>
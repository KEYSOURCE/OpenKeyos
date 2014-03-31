<?php
class_load ('Customer');
class_load ('Computer');

/**
* Stores data about services and their port numbers that can be
* remote accessed on remote computers.
* 
*/
class ComputerRemoteService extends Base
{
	/** The ID of the object
	* @var int */
	var $id = null;
	
	/** The computer ID
	* @var int */
	var $computer_id = null;
	
	/** The service ID - see $GLOBALS['REMOTE_SERVICE_NAMES']
	* @var int */
	var $service_id = 0;
	
	/** The port number
	* @var string */
	var $port = '';
	
	/** Comments about the service
	* @var text */
	var $comments = '';
	
	/** Specifies if this is a custom service
	* @var bool */
	var $is_custom = false;
	
	/** For custom services, specifies their name
	* @var string */
	var $name = '';
	
	/** For custom services, specify if they are Web services
	* @var bool */
	var $is_web = false;
	
	/** For custom Web services, specifies the URL 
	* @var text */
	var $url = '';
	
	/** For custom Web services, specified if to use HTTPS instead of HTTP
	* @var bool */
	var $use_https = false;
	
	
	/** If a private key is needed, stores the ID of the private key to use
	* @var int */
	var $private_key_id = 0;
	
	/** Password for gateway access
	* @var string */
	var $password = '';
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_COMPUTERS_REMOTE_SERVICES;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'computer_id', 'service_id', 'port', 'comments', 'is_custom', 'name', 'is_web', 'url', 'use_https');
	
	
	/** 
	* Contructor. Loads an object's values if an ID is specified 
	* @param	int		$id		An object ID 
	*/
	function ComputerRemoteService ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->computer_id) {error_msg ('Please specify the computer.'); $ret = false;}
		if (!$this->service_id) {error_msg ('Please specify the service.'); $ret = false;}
		if (!$this->port) {error_msg ('Please specify the port number.'); $ret = false;}
		
		if ($this->is_custom)
		{
			if (!$this->name) {error_msg ('Please specify the service name.'); $ret = false;}
			if ($this->is_web)
			{
				if (!$this->url) {error_msg ('Please specify the URL.'); $ret = false;}
			}
		}
		
		return $ret;
	}
	
	
	/** Load the object data from an array */
	function load_from_array ($data = array ())
	{
		if ($data['service_id'] == -1) $data['is_custom'] = 1;
	
		if (isset($data['is_custom']))
		{
			if ($data['is_custom'])
			{
				$data['service_id']=-1;
				if (!$data['is_web']) $data['url'] = '';
				else
				{
					//Make sure there is a starting slash
					if (!preg_match ('/^\//', $data['url']) and $data['url']!='')
					{
						$data['url'] = '/' . trim($data['url']);
					}
				}
			}
			else
			{
				$data['name'] = '';
				$data['is_web'] = 0;
				$data['url'] = '';
			}
		}

		parent::load_from_array ($data);
	}
	

	
	/**
	* [Class Method] Returns computers remote services information based  on some criteria
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- customer_id: Return information about services on computers for this customer
	*							- computer_id: Return information on services for this computer
	*							- by_customer: The results array will be grouped by customer ID
	*							- by_computer: The results array will be grouped by computer ID
	* @return	array					Array with the matching remote services information. If 'by_customer'
	*							is specified, it will be an associative array, with the keys being customer IDs
	*							and the values being arrays of ComputerRemoteService objects for that customer. 
	*							If 'by_computer' is specified, it will be an associative array, with the keys
	*							being computer IDs and the values being arrays of ComputerRemoteService objects
	*							for those computers.
	*/
    public static function get_services ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT s.id, s.computer_id ';
		if ($filter['customer_id']) $q.= ', c.customer_id ';
		$q.= 'FROM '.TBL_COMPUTERS_REMOTE_SERVICES.' s ';
		if ($filter['customer_id']) $q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON s.computer_id=c.id ';
		$q.= 'WHERE ';
		
		if ($filter['customer_id']) $q.= 'c.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['computer_id']) $q.= 's.computer_id='.$filter['computer_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY s.computer_id, s.port ';
		
		$ids = DB::db_fetch_array ($q);
		foreach ($ids as $id)
		{
			if ($filter['by_customer'])
				$ret[$id->customer_id][] = new ComputerRemoteService ($id->id);
			elseif ($filter['by_computer'])
				$ret[$id->computer_id][] = new ComputerRemoteService ($id->id);
			else
				$ret[] = new ComputerRemoteService ($id->id);
		}
		
		return $ret;
	}
}

?>
<?php
class_load ('PeripheralClass');
class_load ('Customer');
class_load ('Computer');

/**
* Stores data associated with remote (public) IP addresses used for customers 
* 
*/
class RemoteAccess extends Base
{
	/** The ID of the remote access address
	* @var int */
	var $id = null;
	
	/** The customer ID
	* @var int */
	var $customer_id = null;
	
	/** The public IP address
	* @var string */
	var $public_ip = '';
	
	/** Specifies if this can be used as port forwarding gateway
	* @var bool */
	var $has_port_forwarding = true;
	
	/** Specifies if a private key is needed for port forwarding 
	* @var bool */
	var $needs_private_key = false;
	
	/** If a private key is needed, stores the ID of the private key to use
	* @var int */
	var $private_key_id = 0;
	
	/** Port used for port forwarding 
	* @var string */
	var $pf_port = '22';
	
	/** Login name for port forwarding
	* @var string */
	var $pf_login = 'root';
	
	/** Password for port forwarding */
	var $pf_password = '';

		
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_REMOTE_ACCESS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'customer_id', 'public_ip', 'has_port_forwarding', 'needs_private_key', 'private_key_id', 'pf_port', 'pf_login', 'pf_password');
	
	
	/** 
	* Contructor. Loads an object's values if an ID or IP address is specified 
	* @param	mixed		$id		A RemoteAccess object ID or an IP address (in which case a customer ID must be specified)
	*/
	function RemoteAccess ($id = null, $customer_id = null)
	{
		if ($id or $customer_id)
		{
			if ($id and !$customer_id)
			{
				// An object ID has been passed
				$this->id = $id;
				$this->load_data();
			}
			elseif ($customer_id)
			{
				// And IP address and a customer ID has been passed
				$q = 'SELECT id FROM '.TBL_REMOTE_ACCESS.' WHERE public_ip="'.mysql_escape_string ($id).'" AND customer_id='.$customer_id;
				$obj_id = $this->db_fetch_field ($q, 'id');
				if ($obj_id)
				{
					$this->id = $obj_id;
					$this->load_data ();
				}
				else
				{
					$this->public_ip = $id;
					$this->customer_id = $customer_id;
				}
			}
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		if (!$this->customer_id) {error_msg ('Please specify the customer.'); $ret = false;}
		if (!$this->public_ip) {error_msg ('Please specify the public IP address.'); $ret = false;}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns RemoteAccess objects according to a specified criteria
	* @param	array		$filter				Array with filtering criteria. Can contain:
	*								- customer_id: Return objects only for this customer
	* @return	array(RemoteAccess)				Array with the matched RemoteAccess objects
	*/
	public static function get_ips ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT r.id FROM '.TBL_REMOTE_ACCESS.' r WHERE ';
		
		if ($filter['customer_id']) $q.= 'r.customer_id='.$filter['customer_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s$/', ' ', $q);
		$q.= 'ORDER BY r.public_ip ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new RemoteAccess ($id);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns all public IP addresses for a customer, as RemoteAccess objects.
	* For the IPs that don't have such objects defined yet, it will return "blank" RemoteAccess
	* objects, pre-populated with the IPs and the customer IDs
	* @param	int		$customer_id			The ID of the customer
	* @return	array(RemoteAccess)				Array with the matching RemoteAccess objects
	*								for each found IP address for that customer
	*/
    public static function get_all_ips($customer_id)
	{
		$ret = array ();
		
		if ($customer_id)
		{
			$ips = RemoteAccess::get_customer_computers_ips ($customer_id);
			foreach ($ips as $ip)
			{
				$ret[] = new RemoteAccess ($ip, $customer_id);
			}
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of public IP addresses defined for a certain customer, 
	* by checking the list of computers of that customer.
	* 
	* NOTE: Keep in mind that the addresses are fetched from the computers table, and NOT
	* from the table with remote access information.
	* @param	int		$customer_id			The ID of the customer
	* @return	array						Array with the found public IP addresses
	*/
    public static function get_customer_computers_ips ($customer_id)
	{
		$ret = array ();
		
		if ($customer_id)
		{
			$q = 'SELECT DISTINCT remote_ip FROM '.TBL_COMPUTERS.' WHERE customer_id='.$customer_id.' ORDER BY remote_ip ';
			$ret = DB::db_fetch_vector ($q);
		}
		
		return $ret;
	}
}

?>
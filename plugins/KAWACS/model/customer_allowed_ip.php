<?php

/** Class for representing the remote IPs from which Kawacs Agent is allowed to send 
* reports for each customer.
*
* If a Kawacs Agent submits data through a public IP which is not in the list of allowed
* IPs for that respective customer, the data is not rejected. However, a notification
* will be raised in the system later, so an engineer can either re-assign the computer
* to the correct customer or add the IP address to the list of allowed IPs for this
* customer.
*/

class CustomerAllowedIP extends Base
{
	/** The object's ID
	* @var int */
	var $id = null;
	
	/** The remote IP address
	* @var string */
	var $remote_ip = '';
	
	/** The ID of the customer for which this IP is allowed
	* @var int */
	var $customer_id = null;
	
	/** The ID of the user who approved or modified this IP for this customer
	* @var int */
	var $updated_by_id = null;
	
	/** The date when the IP was added or modified
	* @var timestamp */
	var $updated_date = 0;
	
	
	/** The databas table storing objects data 
	* @var string */
	var $table = TBL_CUSTOMERS_ALLOWED_IPS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'remote_ip', 'customer_id', 'updated_by_id', 'updated_date');
	
	
	/** Class constructor. Also loads an object data if an ID is specified */
	function CustomerAllowedIp ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		if (!$this->customer_id) {error_msg ('Please specify the customer.'); $ret = false;}
		if (!$this->remote_ip) {error_msg ('Please specify the remote IP address.'); $ret = false;}
		
		// Check for uniqueness
		if ($this->customer_id and $this->remote_ip)
		{
			$q = 'SELECT id FROM '.TBL_CUSTOMERS_ALLOWED_IPS.' WHERE remote_ip="'.mysql_escape_string($this->remote_ip).'" ';
			$q.= 'AND customer_id='.$this->customer_id.' ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			$q.= 'LIMIT 1';
			if (DB::db_fetch_field ($q, 'id')) {error_msg ('This IP address is already specified for this customer.'); $ret = false;}
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns a list with the allowed IPs currently defined in the system 
	* @return	array					Associative array, the keys being allowed remote IPs and the values
	*							being array with the customer IDs for which those IPs are allowed
	*/
    public static function get_allowed_ips_list ()
	{
		$ret = array ();
		
		$q = 'SELECT remote_ip, customer_id FROM '.TBL_CUSTOMERS_ALLOWED_IPS.' ai ORDER BY remote_ip, customer_id';
		$data = DB::db_fetch_array ($q);
		foreach ($data as $d) $ret[$d->remote_ip][] = $d->customer_id;
		
		return $ret;
	}
	
	/** [Class Method] Return the allowed customer IPs currently defined in the system */
    public static function get_allowed_ips ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT ai.id FROM '.TBL_CUSTOMERS_ALLOWED_IPS.' ai INNER JOIN '.TBL_CUSTOMERS.' cust ';
		$q.= 'ON ai.customer_id=cust.id ';
		if ($filter['customer_id']) $q.= 'WHERE ai.customer_id='.$filter['customer_id'].' ';
		$q.= 'ORDER BY cust.name, ai.remote_ip';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new CustomerAllowedIP ($id);
		
		return $ret;
	}
	
	/** [Class Method] Returns a list with the remote ips and the computers.
	* NOTE: This list comes from the computers table, not from the allowed IPs table
	* @return	array					Associative array, the keys being remote IPs and
	*							the values being arrays of generic objects representing
	*							the computers which are reporting through those IPs,
	*							with the fields: id, netbios_name, customer_id, customer_name, remote_ip
	*/
    public static function get_ips_computers_list ()
	{
		$ret = array ();
		$q = 'SELECT c.id, c.netbios_name, c.remote_ip, c.customer_id, cust.name as customer_name FROM '.TBL_COMPUTERS.' c ';
		$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
		$q.= 'WHERE c.remote_ip<>"" and c.remote_ip<>"0.0.0.0" ';
		$q.= 'ORDER BY c.remote_ip, c.netbios_name ';
		$data = DB::db_fetch_array ($q);
		
		foreach ($data as $d) $ret[$d->remote_ip][] = $d;
		
		return $ret;
	}
	
	
	/** [Class Method] Returns the ID of the customer who is marked as being allowed to use a specified remote IP.
	* This is normally used when receiving reports from Kawacs Agent for new computers, for determining the customer
	* to whom a computer should be assigned.
	* IMPORTANT NOTE: If the IP is allowed for more than one customer, the function will NOT return a customer ID.
	* @param	string			$remote_ip	The remote IP to check
	* @return	int					The ID of the customer for this IP, or 0 if there are no
	*							customers or more than one customers for this IP.
	*/
    public static function get_customer_for_ip ($remote_ip)
	{
		$ret = 0;
		$q = 'SELECT DISTINCT customer_id FROM '.TBL_CUSTOMERS_ALLOWED_IPS.' WHERE remote_ip="'.mysql_escape_string($remote_ip).'"';
		$ids = DB::db_fetch_vector ($q);
		if (count($ids) == 1) $ret = $ids[0];
		return $ret;
	}
	
}

/*
drop table if exists customers_allowed_ips;
CREATE TABLE `customers_allowed_ips` (
  `id` int(11) NOT NULL auto_increment,
  `remote_ip` varchar(50) NOT NULL default '',
  `customer_id` int(11) NOT NULL default '0',
  `updated_by_id` int(11) NOT NULL default '0',
  `updated_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`), key(customer_id), key(remote_ip), key(updated_by_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

delete from customers_allowed_ips;
insert into customers_allowed_ips(customer_id, remote_ip, updated_by_id, updated_date) 
select c.customer_id, c.remote_ip, count(distinct c.customer_id) as cnt, 1154944049 as created_date
from computers c inner join customers cust on c.customer_id=cust.id 
where remote_ip<>'' and remote_ip<>'0.0.0.0' group by remote_ip having cnt=1;
update customers_allowed_ips set updated_by_id=1;
delete  from customers_allowed_ips using customers_allowed_ips,customers where customers_allowed_ips.customer_id=customers.id and customers.active=0;

*/
?>
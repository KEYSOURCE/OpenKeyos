<?php

/**
* Class for storing and manipulating customer information
*
*/

class_load ('CustomerContact');
class_load ('CustomerComment');

class SupplierCustomer extends Base 
{
	/** Customer ID
	* @var int*/
	var $id = null;
	
	/** Customer name
	* @var string */
	var $name = '';
	
	/** Specifies if the customer has access to Kawacs
	* @var boolean */
	var $has_kawacs = true;
	
	/** Specifies if the customer has access to Krifs
	* @var boolean */
	var $has_krifs = true;
	
	/** Specifies the SLA interval for the customer - the number of hours after which "New"
	* Krifs tickets are escalated. 0 means "never"
	* @var int */
	var $sla_hours = 0;
	
	/** Specifies if the customer is active or not 
	* @var bool */
	var $active = true;
	
	/** Specifies if the customer is on-hold 
	* @var bool */
	var $onhold = false;
	
	/** Specifies if e-mail alerts should be suspended for this customer, which means
	* that Keysource users will not receive any e-mail notifications for this customers.
	* But the notifications are still generated
	* @var bool */
	var $no_email_alerts = false;
	
	/** The price type for this customer (Basic, Keypro, TC 1, TC 2, TC 3) - see $GLOBALS['CUST_PRICETYPES']
	* @var int */
	var $price_type = null;
	
	/** Specifies the ERP contract number for this customer - imported from ERP
	* @var string */
	var $erp_subscription_no = '';
	
	/** The country code for this customer - countries which are defined through fixed Locations objects
	* @var int */
	var $Country_D = 0;
	
	
	/** The databas table storing customer data 
	* @var string */
	var $table = TBL_SUPPLIER_CUSTOMERS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'name', 'has_kawacs', 'has_krifs', 'sla_hours', 'active', 'onhold', 'no_email_alerts', 'price_type', 'Country_D');

	
	/**
	* Constructor, also loads the customer data from the database if a user ID is specified
	* @param	int $id		The user's id
	*/
	function ClientCustomer($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Loads the customer data from the database into the current object  */
	function load_data()
	{
		$ret = false;
		if ($this->id)
		{
			parent::load_data();
			$ret = (!empty($this->id));
			
			// Make sure UTF-8 string are converted to ISO-8859-1
			//if (mb_detect_encoding($this->name) == 'UTF-8') $this->name = utf8_decode ($this->name);
		}
		return $ret;
	}
	
	
	/** Checks if the customer data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg('Please specify the customer name'); $ret = false;}
		
		return $ret;
	}
	
	
	/** Checks if a customer can be deleted */
	function can_delete ()
	{
		$ret = true;
		
		if ($this->id)
		{
			// Check if there are tickets for this customer
			$q = 'SELECT id FROM '.TBL_TICKETS.' WHERE customer_id='.$this->id.' LIMIT 1';
			if ($this->db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('This customer has associated tickets and can\'t be deleted. You should either disable the customer, or delete those tickets first.');
			}
			
			// Check if there are computers for this customer
			$q = 'SELECT id FROM '.TBL_COMPUTERS.' WHERE customer_id='.$this->id.' LIMIT 1';
			if ($this->db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('This customer has associated computers and can\'t be deleted. You should either disable the customer, or delete those computers first.');
			}
		}
		
		return $ret;
	}
	
	
	/** Deletes a customer and all its associated resources */
	function delete ()
	{
		class_load ('Computer');
		class_load ('Ticket');
		class_load ('CustomerContact');
		class_load ('CustomerOrder');
		if ($this->id)
		{
			// Delete customers' computers and associated items
			$computers = Computer::get_computers (array('customer_id' => $this->id), $no_count);
			for ($i=0; $i<count($computers); $i++) $computers[$i]->delete ();
			
			// Delete the customers' tickets
			$tickets = Ticket::get_tickets (array('customer_id' => $this->id), $no_count);
			for ($i=0; $i<count($tickets); $i++) $tickets[$i]->delete ();
			
			// Delete the customers' users
			$users = User::get_users (array('customer_id' => $this->id), $nocount);
			for ($i=0; $i<count($users); $i++) $users[$i]->delete ();
			
			// Delete the software packages for this customer
			$this->db_query ('DELETE FROM '.TBL_SOFTWARE_LICENSES.' WHERE customer_id='.$this->id);
			
			// Delete the references for notification recipients
			$this->db_query ('DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' WHERE customer_id='.$this->id);
			
			// Delete the customer from the lists of assigned and favorites customers for users
			$this->db_query ('DELETE FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' WHERE customer_id='.$this->id);
			$this->db_query ('DELETE FROM '.TBL_USERS_CUSTOMERS_FAVORITES.' WHERE customer_id='.$this->id);
			
			// Delete the peripherals for this customer
			$this->db_query ('DELETE FROM '.TBL_PERIPHERALS.' WHERE customer_id='.$this->id);
			
			// Delete all the access phones for this customer
			$this->db_query ('DELETE FROM '.TBL_ACCESS_PHONES.' WHERE customer_id='.$this->id);
			
			// Delete all the contacts for this customer 
			$q = 'SELECT id FROM '.TBL_CUSTOMERS_CONTACTS.' WHERE customer_id='.$this->id;
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$contact = new CustomerContact ($id);
				$contact->delete ();
			}
			
			// Delete all customer orders for this customer
			$q = 'SELECT id FROM '.TBL_CUSTOMER_ORDERS.' WHERE customer_id='.$this->id;
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$order = new CustomerOrder ($id);
				$order->delete ();
			}
			
			parent::delete ();
		}
	}
	
	
	/** 
	* Returns a list with the users and group who have the customer on their list of assigned customers.
	* Can be called as object method or class method, in which case $customer_id is mandatory.
	* @param	bool	$ignore_group		If True, returns only users, without groups. If false,
	*						returns both users and groups.
	* @param	string	$group_prefix		A prefix to add in front of the group names.
	* @param	int	$customer_id		The customer ID. Ignored if called as object method.
	* @return	array				Associative array with the users and groups which have the
	*						customer in their list of assigned customers. The array keys
	*						are user IDs and the values are their names.
	*/
	function get_assigned_users_list ($ignore_group = false, $group_prefix = '[G] ', $customer_id = null)
	{
		$ret = array ();
		if ($this->id) $customer_id = $this->id;
		
		if ($customer_id)
		{
			$q = 'SELECT u.id, u.fname, u.lname, u.type FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' a ';
			$q.= 'INNER JOIN '.TBL_USERS.' u ON a.user_id=u.id ';
			$q.= 'WHERE a.customer_id='.$customer_id.' ';
			if ($ignore_group) $q.= 'AND u.type=USER_TYPE_KEYSOURCE ';
			$q.= 'ORDER BY u.fname, u.lname ';

			$users = db::db_fetch_array ($q);
			
			foreach ($users as $user)
			{
				$ret[$user->id] = ($user->type==USER_TYPE_KEYSOURCE_GROUP ? $group_prefix : '');
				$ret[$user->id].= trim ($user->fname.' '.$user->lname);
			}
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Returns the number of customers for which the alert e-mails has been suspended */
	function get_suspended_customers_alerts_count ()
	{
		$q = 'SELECT count(*) as cnt FROM '.TBL_CUSTOMERS.' WHERE no_email_alerts=1 ';
		return DB::db_fetch_field ($q, 'cnt');
	}
	
	
	/** [Class Method] Returns the customers for which the alert e-mails has been suspended  */
	function get_suspended_customers_alerts ()
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_CUSTOMERS.' WHERE no_email_alerts=1 ORDER BY name';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Customer ($id);
		
		return $ret;
	}
	
	/** [Class Method] Returns the name of a customer with a given ID */
	function get_customer_name ($customer_id)
	{
		$ret = '';
		if ($customer_id)
		{
			$q = 'SELECT name FROM '.TBL_CUSTOMERS.' WHERE id='.$customer_id;
			$ret = DB::db_fetch_field ($q, 'name');
		}
		return $ret;
	}
	
	/**
	* [Class Method] Returns an array of customer objects
	*
	* @param	array	$filter		Filtering criteria
	* @param	int	$count		(By reference) If is defined, the total number of customers matching criteria
	* @return	array			Array of customer objects 
	*/
	function get_supplier_customers ($filter = array(), &$count)
	{
		$ret = array();
		
		if (!isset($filter['order_by'])) $filter['order_by'] = 'name';
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'ASC';
		if (!isset($filter['active'])) $filter['active'] = 1;
		$filter['order_by'] = 'c.'.$filter['order_by'];
		
		$q = ' FROM '.TBL_SUPPLIER_CUSTOMERS.' c ';
		
		if ($filter['assigned_user_id'])
		{
			// Check both direct user assignment and group assignment
			$q.= 'INNER JOIN '.TBL_USERS_CUSTOMERS_ASSIGNED.' ac ON c.id=ac.customer_id ';
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON ac.user_id=ug.group_id ';
		}
		
		$q.= 'WHERE ';
		
		if (isset($filter['has_kawacs']) and $filter['has_kawacs']!='') $q.= 'c.has_kawacs='.$filter['has_kawacs'].' AND ';
		if (isset($filter['has_krifs']) and $filter['has_krifs']!='') $q.= 'c.has_krifs='.$filter['has_krifs'].' AND ';
		if (isset($filter['active']) and $filter['active']!=-1) $q.= 'c.active='.$filter['active'].' AND ';
		if (isset($filter['onhold']) and $filter['onhold']!=-1) $q.= 'c.onhold='.$filter['onhold'].' AND ';
		if ($filter['search_text']) $q.= 'c.name like "%'.mysql_escape_string($filter['search_text']).'%" AND ';
		
		if ($filter['assigned_user_id'])
		{
			$q.= '(ac.user_id='.$filter['assigned_user_id'].' OR ';
			$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$filter['assigned_user_id'].')) AND ';
		}
		
		$q = preg_replace ('/AND\s*$/', '', $q);
		$q = preg_replace ('/WHERE\s*$/', '', $q);
		
		if (isset($count))
		{
			$count = db::db_fetch_field ('SELECT count(c.id) AS cnt '.$q, 'cnt');
		}
		
		$q = 'SELECT c.id '.$q.' ORDER BY '.$filter['order_by'].' '.$filter['order_dir'].' ';
		
		if (isset($filter['start']) and isset($filter['limit'])) $q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
		
		$ids = db::db_fetch_vector($q);
		for ($i=0; $i<count($ids); $i++) $ret[$i] = new Customer($ids[$i]);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns an array with customer names, with the array keys being the customer IDs
	*
	* @param	array	$filter		Associative array with filtering criteria. Can have the keys/values:
	*					- assigned_user_id : Only return customers assigned to this user ID,
	*					  either directly or via groups.
	*					- has_kawacs : Only return customers with Kawacs contracts.
	*					- has_krifs : Only return customers with Krifs contracts.
	*					- favorites_first : Contains an user ID and, if set, will return the favorite
	*					  customers at the top of the list.
	*					- show_ids : If set, the customer IDs will be appended to names
	* @return	array			List of customer names
	*/
	function get_supplier_customers_list ($filter = array())
	{
		$ret = array();
		
		if (!isset($filter['active'])) $filter['active'] = 1;
		
		$q = 'SELECT DISTINCT c.id, '. ($filter['show_ids'] ? 'concat(c.name, " (", id,")") as name ' : ' c.name ');
		$q.= ($filter['favorites_first'] ? ', f.user_id' : '').', c.active FROM '.TBL_SUPPLIER_CUSTOMERS.' c ';
		if ($filter['assigned_user_id'])
		{
			// Check both direct user assignment and group assignment
			$q.= 'INNER JOIN '.TBL_USERS_CUSTOMERS_ASSIGNED.' ac ON c.id=ac.customer_id ';
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON ac.user_id=ug.group_id ';
		}
		if ($filter['favorites_first'])
		{
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_CUSTOMERS_FAVORITES.' f ON (c.id=f.customer_id AND f.user_id='.$filter['favorites_first'].') ';
		}
		
		$q.= 'WHERE ';
		
		if ($filter['assigned_user_id'])
		{
			$q.= '(ac.user_id='.$filter['assigned_user_id'].' OR ';
			$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$filter['assigned_user_id'].')) AND ';
		}
		if (isset($filter['has_kawacs']) and $filter['has_kawacs']!='') $q.= 'c.has_kawacs='.$filter['has_kawacs'].' AND ';
		if (isset($filter['has_krifs']) and $filter['has_krifs']!='') $q.= 'c.has_krifs='.$filter['has_krifs'].' AND ';
		if (isset($filter['active']) and $filter['active']!=-1) $q.= 'c.active='.$filter['active'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
	
		if ($filter['favorites_first'])
		{
			$q.= 'ORDER BY f.user_id DESC, c.name ';
			$customers = db::db_fetch_array ($q);
			$done_favorites = true;
	
			foreach ($customers as $cust)
			{
				if (!$cust->user_id)
				{
					if (!$done_favorites) $ret[' '] = ' ';
					$done_favorites = true;
				}
				else $done_favorites = false;
				$ret[$cust->id] = $cust->name;
			}
		}
		else
		{
			$q.= 'ORDER BY c.name ';
			$ret = db::db_fetch_list ($q);
		}
		
		return $ret;
	}
}

?>
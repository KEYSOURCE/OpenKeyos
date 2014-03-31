<?php

/**
* Class for storing and manipulating customer information
*
*/

class_load ('CustomerContact');
class_load ('CustomerComment');

class Customer extends Base
{
	/** Customer ID
	* @var int*/
	var $id = null;

	/** ERP ID for the customer (Mercator)
	* @var string */
	var $erp_id = '';

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

	/** Specifies the contract type for this customer - see $GLOBALS['CONTRACT_TYPES']
	* @var int */
	var $contract_type = null;

	/** The contract sub-type (Basic, KeyPro, GlobalPro, RemoteAdmin etc.) - see $GLOBALS['CUST_SUBTYPES']
	* @var int */
	var $contract_sub_type = null;

	/** The price type for this customer (Basic, Keypro, TC 1, TC 2, TC 3) - see $GLOBALS['CUST_PRICETYPES']
	* @var int */
	var $price_type = null;

	/** Specifies the ERP contract number for this customer - imported from ERP
	* @var string */
	var $erp_subscription_no = '';

	/** The country code for this customer - countries which are defined through fixed Locations objects
	* @var int */
	var $Country_D = 0;

	/**
	 * the account manager for this customer, can be KS or MPI at the moment
	 * Please check the $GLOBALS['ACCOUNT_MANAGERS'] - for possible values
	 * @var int
	 * */
	var $account_manager = 6;

	/** The databas table storing customer data
	* @var string */
	var $table = TBL_CUSTOMERS;


	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'erp_id', 'erp_subscription_no', 'name', 'has_kawacs', 'has_krifs', 'sla_hours', 'active', 'onhold', 'no_email_alerts', 'contract_type', 'contract_sub_type', 'price_type', 'Country_D', 'account_manager');


	/**
	* Constructor, also loads the customer data from the database if a user ID is specified
	* @param	int $id		The user's id
	*/
	function __construct($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
            $this->verify_access();
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

		if ($this->id and $this->active)
		{
			//debug("checking if there are assigned tickets");
            // Check if there are tickets for this customer
			$q = 'SELECT id FROM '.TBL_TICKETS.' WHERE customer_id='.$this->id.' LIMIT 0,1';
			if ($this->db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('This customer has associated tickets and can\'t be deleted. You should either disable the customer, or delete those tickets first.');
			}
            //debug("checked");
            
            //debug("checking if there are assigned computers");
			// Check if there are computers for this customer
			$q = 'SELECT id FROM '.TBL_COMPUTERS.' WHERE customer_id='.$this->id.' LIMIT 0,1';
			if ($this->db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('This customer has associated computers and can\'t be deleted. You should either disable the customer, or delete those computers first.');
			}
            //debug("checked");
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
        //debug("in Delete");
		if ($this->id)
		{
            //debug("deleting computers");
			// Delete customers' computers and associated items
            $no_count = 0;
			$computers = Computer::get_computers (array('customer_id' => $this->id), $no_count);
			for ($i=0; $i<count($computers); $i++){
                            $computers[$i]->delete();
                        }

            //debug("finished computers..... starting tickets");
			// Delete the customers' tickets
			$tickets = Ticket::get_tickets (array('customer_id' => $this->id), $no_count);
                        for($i=0; $i<count($tickets);$i++) $tickets[$i]->delete();
            //debug("finishe tickets");
            /*
            $query = "select id from ".TBL_TICKETS." where customer_id=".$this->id;
            $tids = $this->db_fetch_vector($query); 
			//for ($i=0; $i<count($tickets); $i++){
            foreach($tids as $id){
                $ticket = new Ticket($id);                
                //$tickets[$i]->delete();
                //$tickets[$i]->destruct();
                $ticket->delete();
            }
            */
            //debug("finished tickets..... starting users");
			// Delete the customers' users
			$users = User::get_users (array('customer_id' => $this->id), $nocount);
			for ($i=0; $i<count($users); $i++) $users[$i]->delete ();

            //debug("finished users..... starting SW Licenses");
			// Delete the software packages for this customer
			$this->db_query ('DELETE FROM '.TBL_SOFTWARE_LICENSES.' WHERE customer_id='.$this->id);

            //debug("fnished SWL starting notifications recipients") ;
			// Delete the references for notification recipients
			$this->db_query ('DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' WHERE customer_id='.$this->id);

            
			// Delete the customer from the lists of assigned and favorites customers for users
			$this->db_query ('DELETE FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' WHERE customer_id='.$this->id);
			$this->db_query ('DELETE FROM '.TBL_USERS_CUSTOMERS_FAVORITES.' WHERE customer_id='.$this->id);

			// Delete the peripherals for this customer
			$this->db_query ('DELETE FROM '.TBL_PERIPHERALS.' WHERE customer_id='.$this->id);

			// Delete all the access phones for this customer
			$this->db_query ('DELETE FROM '.TBL_ACCESS_PHONES.' WHERE customer_id='.$this->id);

            //debug("deleting customers contacts");
			// Delete all the contacts for this customer
			$q = 'SELECT id FROM '.TBL_CUSTOMERS_CONTACTS.' WHERE customer_id='.$this->id;
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$contact = new CustomerContact ($id);
				$contact->delete ();
			}

            //debug("deleting customers orders");
			// Delete all customer orders for this customer
			$q = 'SELECT id FROM '.TBL_CUSTOMER_ORDERS.' WHERE customer_id='.$this->id;
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$order = new CustomerOrder ($id);
				$order->delete ();
			}

            //debug("finally .... delete the customer");
			parent::delete ();
            //debug("finished");
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
	public static function get_suspended_customers_alerts_count ()
	{
		$q = 'SELECT count(*) as cnt FROM '.TBL_CUSTOMERS.' WHERE no_email_alerts=1 ';
		return DB::db_fetch_field ($q, 'cnt');
	}


	/** [Class Method] Returns the customers for which the alert e-mails has been suspended  */
	public static function get_suspended_customers_alerts ()
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_CUSTOMERS.' WHERE no_email_alerts=1 ORDER BY name';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Customer ($id);

		return $ret;
	}

	/** [Class Method] Returns the name of a customer with a given ID */
	public static function get_customer_name ($customer_id)
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
	public static function get_customers ($filter = array(), &$count)
	{
		$ret = array();

		if (!isset($filter['order_by'])) $filter['order_by'] = 'name';
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'ASC';
		if (!isset($filter['active'])) $filter['active'] = 1;
		$filter['order_by'] = 'c.'.$filter['order_by'];

		$q = ' FROM '.TBL_CUSTOMERS.' c ';

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
		if (isset($filter['account_manager'])) $q.='c.account_manager='.$filter['account_manager'].' AND ';
		if (isset($filter['onhold']) and $filter['onhold']!=-1) $q.= 'c.onhold='.$filter['onhold'].' AND ';
		if ($filter['contract_type'] and $filter['contract_type']!=CONTRACT_ALL) $q.= 'c.contract_type='.$filter['contract_type'].' AND ';
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
	public static function get_customers_list ($filter = array())
	{
		$ret = array();
        $current_user = $GLOBALS['CURRENT_USER'];
		if($current_user and $current_user->is_customer_user() and $current_user->administrator and $current_user->type=USER_TYPE_CUSTOMER)
		{
			$query = "select uc.customer_id, c.name from ".TBL_USERS_CUSTOMERS." uc inner join ".TBL_CUSTOMERS." c on uc.customer_id=c.id ";
			$query .= " where uc.user_id=".$current_user->id." ORDER BY c.name";
			$ret = db::db_fetch_list($query);
		}
		else
		{
			if (!isset($filter['active'])) $filter['active'] = 1;

			$q = 'SELECT DISTINCT c.id, '. ($filter['show_ids'] ? 'concat(c.name, " (", id,")") as name ' : ' c.name ');
			$q.= ($filter['favorites_first'] ? ', f.user_id' : '').', c.active FROM '.TBL_CUSTOMERS.' c ';
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
			if (isset($filter['account_manager']) and $filter['account_manager']!='') $q.='c.account_manager='.$filter['account_manager'].' AND ';
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
		}
		return $ret;
	}
	/**
	 * [Class Method]
	 * Searched in all the phones available in keyos the customer to wich it belongs
	 *
	 * @param string $number
	 */
	public static function get_customers_by_phone_numbers($number)
	{
		//get all the phone numbers into an array
		$query = "select id, phone from ".TBL_USERS_PHONES;
		$phone_list = db::db_fetch_list($query);

		$phone_list_worked = array();
		//prepare the Phone list
		foreach ($phone_list as $key=>$phone)
		{
			//1.remove all the dots, slashes, brackets, dashes
			$phone = preg_replace('/[\-\.\(\)\/]/','',$phone);
			//2. replace the + sign by 00
			$phone = preg_replace('/\+/','00',$phone);
			//3. remove the whitespaces
			$phone = preg_replace('/[\s\t]/','',$phone);

			$phone_list[$key] = $phone;
		}

		//search with approximate match
		$shortest = -1;
		$closest = "";
		$uid = -1;
		foreach($phone_list as $key=>$phone)
		{
			//calculate the distance between the input number and the current phone
			$lev = levenshtein($number, $phone);
			//check for an exact match
			if($lev == 0)
			{
				//closest number is (exact match)
				$closest = $phone;
				$uid = $key;
				$shortest = 0;

				//break out, there is an exact match
				break;
			}
			if($lev <= $shortest || $shortest < 0)
			{
				//set the closest match and the closest distance
				$closest = $phone;
				$uid = $key;
				$shortest = $lev;
			}
		}
		if($shortest == 0)
		{

			$q = "select customer_id from ".TBL_USERS." where id=(select user_id from users_phones where id=".$uid.")";
			$cid = db::db_fetch_field($q, 'customer_id');
			if($cid == "0")
			{
			    $cid = "6";
			}
			return new Customer($cid);
		}
		else
		{
			if($shortest < 5)
			{
				$q = "select customer_id from ".TBL_USERS." where id=(select user_id from users_phones where id=".$uid.")";
				$cid = db::db_fetch_field($q, 'customer_id');
				if($cid == "0")
				{
				    $cid="6";
				}
				return new Customer($cid);
			}
			else {
				return null;
			}
		}
	}

	/**
	 * 	[Class Method] transfers the tickets from one customers to another
	 *	@param int $src_customer_id
	 *	@param int $dest_customer_id
	 * */

	public static function customer_transfer_tickets($src_customer_id, $dest_customer_id)
	{
		//first we need to get the tickets for $src_customer_id
		$query = "select id from ".TBL_TICKETS." where customer_id=".$src_customer_id;
		$ids = db::db_fetch_vector($query);

		$query = "update ".TBL_TICKETS." set customer_id=".$dest_customer_id." in (";
		$cnt = count($ids);
		$i=0;
		foreach($ids as $id)
		{
			if($i<$cnt-1)
				$query.= $id.", ";
			if($i==$cnt-1)
				$query.=$id;
		}
		db::db_query($query);
	}
    
    function merge_with($merged_customer){
        class_load("Computer");
        class_load("Ticket");
        class_load("Peripheral");
        class_load("CustomerContact");
        class_load("InterventionReport");
        class_load("CustomerOrder");
        class_load("Users");
        class_load("CustomerComment");
        
        if($this->id and $merged_customer->id and $this->id != $merged_customer->id){
            //transfer the computers from the merged customer to this customer
            $q = "select id from ".TBL_COMPUTERS." where customer_id=".$merged_customer->id;
            $comps_ids = db::db_fetch_vector($q);
            foreach($comps_ids as $comp_id){
                $comp = new Computer($comp_id);
                if($comp->id){
                    $comp->customer_id = $this->id;
                    $comp->save_data();
                }                
            }
            
            //transfer the peripherals form the merged customer to this customer
            
            $q = "select id from ".TBL_PERIPHERALS." where customer_id=".$merged_customer->id;
            $periphs_ids = db::db_fetch_vector($q);
            foreach($periphs_ids as $per_id){
                $periph = new Peripheral($per_id);
                if($periph->id){
                    $periph->customer_id = $this->id;
                    $periph->save_data();
                }
            }    
            
            //transfer the tickets from the merged customer to this one
            $q = "select id from ".TBL_TICKETS." where customer_id=".$merged_customer->id;
            $tickets_ids = db::db_fetch_vector($q);
            foreach($tickets_ids as $ticket_id){
                $ticket = new Ticket($ticket_id);
                if($ticket->id){
                    $ticket->customer_id = $this->id;
                    $ticket->save_data();
                }
            }
            
            //transfer InterventionReports
            $q = "select id from ".TBL_INTERVENTION_REPORTS." where customer_id=".$merged_customer->id;
            $irs_ids = db::db_fetch_vector($q);
            foreach($irs_ids as $ir_id){
                $intervention = new InterventionReport($ir_id);
                if($intervention->id){
                    $intervention->customer_id = $this->id;
                    $intervention->save_data();
                }
            }
            
            //transfer users
            $q = "select id from ".TBL_USERS." where customer_id=".$merged_customer->id;
            $users_ids = db::db_fetch_vector($q);
            foreach($users_ids as $id){
                $user = new User($id);
                if($user->id){
                    $user->customer_id = $this->id;
                    $user->save_data();
                }
            }
            
            //transfer sofware licenses
            $q = "update ".TBL_SOFTWARE_LICENSES." set customer_id=".$this->id." where customer_id=".$merged_customer->id;
            db::db_query($q);
            
            //transfer customer's notifications recipients
            $q = "update ".TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS." set customer_id=".$this->id." where customer_id=".$merged_customer->id;
            db::db_query($q);
            
            
            //transfer customer contacts and orders
            $q = "update ".TBL_ACCESS_PHONES." set customer_id=".$this->id." where customer_id=".$merged_customer->id;
            db::db_query($q);
            
            //transfer customer contacts
            $q = "select id from ".TBL_CUSTOMERS_CONTACTS." where customer_id=".$merged_customer->id;
            $cc_ids = db::db_fetch_vector($q);
            foreach($cc_ids as $cc_id){
                $cust_contact = new CustomerContact($cc_id);
                if($cust_contact->id){
                    $cust_contact->customer_id = $this->id;
                    $cust_contact->save_data();
                }
            }
             
            //transfer customer orders
            $q = "select id from ".TBL_CUSTOMER_ORDERS." where customer_id=".$merged_customer->id;
            $cust_orders_ids = db::db_fetch_vector($q);
            foreach($cust_orders_ids as $coid){
                $cust_order = new CustomerOrder($coid);
                if($cust_order->id){
                    $cust_order->customer_id = $this->id;
                    $cust_order->save_data();
                }
            }   
            
            //transfer internet contracts
            $q = "update ".TBL_CUSTOMERS_INTERNET_CONTRACTS." set customer_id=".$this->id." where customer_id=".$merged_customer->id;
            db::db_query($q);
            
            //transfer customer photos
            $q = "update ".TBL_CUSTOMERS_PHOTOS." set customer_id=".$this->id." where customer_id=".$merged_customer->id;
            db::db_query($q);
                                               
            //transfer locations                                                                                                                                 
            $q = "update ".TBL_LOCATIONS." set customer_id=".$this->id." where customer_id=".$merged_customer->id;
            db::db_query($q);
            
            
            //create merging comment
            $comment = new CustomerComment();
            $comment->customer_id = $this->id;
            $comment->subject = "Merging with customer (#".$merged_customer->id.") ".$merged_customer->name;
            $comment->comments = "Merging the data of customer (#".$merged_customer->id.") ".$merged_customer->name." into this customer";
            $comment->user_id = $this->current_user->id;
            $comment->created = time();
            $comment->save_data();
            
            //transfer comments
            $q = "update ".TBL_CUSTOMERS_COMMENTS." set customer_id=".$this->id." where customer_id=".$merged_customer->id;
            db::db_query($q);
            
            //create finish merging comment
            $comment = new CustomerComment();
            $comment->customer_id = $this->id;
            $comment->subject = "Merging with customer (#".$merged_customer->id.") ".$merged_customer->name." was finished";
            $comment->comments = "The data of customer (#".$merged_customer->id.") ".$merged_customer->name." was merged into this customer, deleting the old customer";
            $comment->user_id = $this->current_user->id;
            $comment->created = time();
            $comment->save_data();
            
            
            //we transfered everything
            //now we delete the merged customer
            $merged_customer->delete();                    
                        
        
        }
        
    }
    
    public static function search_customer($filter){
        $ret = array();
        $query = "select id from ".TBL_CUSTOMERS." where ";
        if(is_numeric($filter))
            $query .= " id=".$filter." ";
        else
            $query .= " name like '".$filter."%'";
        
        $ids = Db::db_fetch_vector($query);
        foreach($ids as $id){
            $ret[] = new Customer($id);
        }
        return $ret;
    }
    
    public static function get_active_customer_contract_types(){
        $ret = array();
        $query = "select contract_type, count(id) from ".TBL_CUSTOMERS." where active = 1 group by contract_type";
        $ret = db::db_fetch_list($query);
        return $ret;
    }

    function verify_access() {
        $uid = get_uid();
        class_load('User');
        $user = new User($uid);
        if($user->type == USER_TYPE_CUSTOMER) {
            $customers_list = $user->get_users_customer_list();
            //if($this->id != $user->customer_id) {
            if(!in_array($this->id, $customers_list)){
                $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
		        header("Location: $url\n\n");
                exit;
            }
        }
    }
}
?>
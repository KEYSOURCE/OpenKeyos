<?php
class_load('User');
/**
* Class for manipulating archived user accounts
* a user account is an inactive user, that cannot login into the platform
* a removed user can get restored into a full User
*/
class RemovedUser extends Base
{
	/** Removed User ID
	* @var int */
	var $id = null;

	/**
	 * the id of the user
	 */
	var $user_id = null;
	
	/** Login name
	* @var string */
	var $login = '';
	
	/** Password
	* @var string */
	var $password = '';
	
	/** If the user is a system administrator or not 
	* @var boolean */
	var $administrator = false;
	
	/** First name
	* @var string */
	var $fname = '';
	
	/** Last name
	* @var string */
	var $lname = '';
	
	/** User type: employee, customer, Keysource group or generic group - see $GLOBALS['USER_TYPES']
	* @var int */
	var $type = USER_TYPE_KEYSOURCE;
	
	/** For Keysource users, specifies if this is a manager or not
	* @var bool */
	var $is_manager = false;
	
	/** The ID of the customer (if this is a customer user)
	* @var int */
	var $customer_id = 0;
	
	/** E-mail address
	* @var string */
	var $email = '';
	
	/** Specifies if the account is active or not, or if the user is away - see $GLOBALS['USER_STATUSES'].
	* For customer users, only USER_STATUS_ACTIVE and USER_STATUS_INACTIVE are allowed
	* @var int */
	var $active = USER_STATUS_ACTIVE;
	
	/** Specifies if the user is allowed to see private entries.
	* This is for customer users only, internal users can always see private entries
	* @var boolean */
	var $allow_private = false;
	
	/** Specifies if the user will be restricted to accessing specific customers or
	* if he will be allowed to access any customer in the system. This applies only
	* to Keysource users, customers users will be allowed by default to access only
	* their customer data
	* @var boolean */
	var $restrict_customers = false;
	
	/** ERP name - from the OLD system (Dimitri)
	* @var string */
	var $erp_name = '';
	
	/** ERP id in the new system (Mercator)
	* @var string */
	var $erp_id = '';
	
	/** ERP id for the "1H service" article created in the ERP system for this user - if any
	* @var string */
	var $erp_id_service = '';
	
	/** ERP id for the "travel" article created in the ERP system for this user - if any
	* @var string */
	var $erp_id_travel = '';
	
	/** If the user is on an "Away" status, this must specify the ID of the alternative user to receive notifications
	* @var int */
	var $away_recipient_id = 0;
	
	/** The language preferred by user for interface and newsletter - see $GLOBALS['LANGUAGES']
	* @var int */
	var $language = LANG_FR;
	
	/** True or false if the user wants to receive or not the newsletter
	* @var bool */
	var $newsletter = false;
	
	/**
	 * true or false if the user ca see or not the dashboards
	 * @var bool
	 */
	var $allow_dashboard = true;
	
	
	/** The phone numbers of this user
	* @var array(UserPhone) */
	var $phones = array();
	
	/** The list of ACL role IDs assigned to this user
	* @var array */
	var $roles_list = array();
	
	/** The ACL roles assigned to this user 
	* @var array(AclRole) */
	var $roles = array();
	
	/** Exchange connection information for this user, if they have been defined
	* @var UserExchage */
	var $exchange = null;
	
	/**
	 * the id of the user that this account was merged into
	 */
	var $merged_into_user_id = null;
	
	/** The database table storing user data 
	* @var string */
	var $table = TBL_REMOVED_USERS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'user_id', 'login', 'password', 'administrator', 'fname', 'lname', 'type', 'is_manager', 'customer_id', 'email', 'active', 'allow_private', 'restrict_customers', 'erp_name', 'erp_id', 'erp_id_service', 'erp_id_travel', 'away_recipient_id', 'language', 'newsletter', 'allow_dashboard', 'merged_into_user_id');
	
	
	/**
	* Constructor, also loads the user data from the database if a user ID is specified
	* @param	int $id		The user's id
	*/
	function RemovedUser($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/**
	* Loads the user data from the database into the current object and also initializes the phone numbers
	*/
	function load_data()
	{
		$ret = false;
		if ($this->id)
		{
			parent::load_data();
			$ret = (!empty($this->id));
			
			if ($this->id)
			{
				// Load phone numbers
				$q = 'SELECT id FROM '.TBL_USERS_PHONES.' WHERE user_id='.$this->user_id;
				$ids = $this->db_fetch_array($q);
				foreach ($ids as $id) $this->phones[] = new UserPhone ($id->id);
				
				// Load the assigned roles
				$q = 'SELECT acl_role_id FROM '.TBL_ACL.' WHERE user_id='.$this->user_id;
				$ids = $this->db_fetch_array ($q);
				
				foreach ($ids as $id)
				{
					$this->roles[] = new AclRole ($id->acl_role_id);
					$this->roles_list[] = $id->acl_role_id;
				}
				
				// If this is a customer account, load the customer name, for easy access
				if ($this->customer_id)
				{
					$this->customer_name = $this->db_fetch_field ('SELECT name FROM '.TBL_CUSTOMERS.' WHERE id='.$this->customer_id, 'name');
				}
				else
				{
					$this->customer_name = $this->db_fetch_field ('SELECT name FROM '.TBL_CUSTOMERS.' WHERE id='.MANAGER_CUSTOMER_ID, 'name');
				}
				
				// See if there is any Exchange information for this user
				$this->exchange = new UserExchange ($this->user_id);
				if (!$this->exchange->id) $this->exchange = null;
				
				// Check for the encoding
				//if (mb_detect_encoding($this->fname) == 'UTF-8') $this->fname = utf8_decode ($this->fname);
				//if (mb_detect_encoding($this->lname) == 'UTF-8') $this->lname = utf8_decode ($this->lname);
			}
		}
		return $ret;
	}
	
	/**
	 * Create a RemovedUser from a user account
	 *
	 * @param User $user
	 */
	function create_from_user_account($user)
	{
		if(!$user->id) return FALSE;
		// copy each field form user to this
		foreach($user->fields as $fld)
		{
			if($fld == "id") $this->user_id = $user->id;
			else $this->$fld = $user->$fld;
		}
		return TRUE;
	}
	
	/**
	 * Create the removed user from the user_id
	 *
	 * @param int $user_id
	 */
	function create($user_id, $merged_into_id)
	{
		$user = new User($user_id);
		$ret = $this->create_from_user_account($user);
		if(!$merged_into_id) $ret = FALSE;
		$this->merged_into_user_id = $merged_into_id;
		return $ret;
	}
	
	function archive_account($user_id, $merged_into_id)
	{
		$this->create($user_id, $merged_into_id);
		$user = new User($user_id);
		$customers_list = $user->get_users_customer_list();
		
		foreach($customers_list as $cid)
		{
			$q = "insert into ".TBL_REMOVED_USERS_CUSTOMERS." values (".$user->id.", ".$cid.") ";
			$this->db_query($q);
		}
		
		$this->save_data();
		$user->delete();
	}
	
	/**
	 * restores a removed account
	 *
	 * @param unknown_type $id
	 */
	function restore_account($id = null)
	{
		//takes back everything from the account it mereged into
		
	}
	
	/**
	* [Class Method] Returns an array of the users defined in the system
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- type : Return only users of specified type
	*							- active: an active status code; or USER_FILTER_ACTIVE_AWAY or USER_FILTER_ALL.
	*							  If not specified, by default USER_FILTER_ACTIVE_AWAY is used.
	*							- start, limit : The "paging"
	*							- order_by: What to sort by: 'name'- by first/last name (default), 'id'- by customer id,
	*							  'customer'- by customer name, 
	*/
	function get_removed_users ($filter = array())
	{
		$ret = array();
		
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'ASC';
		switch ($filter['order_by'])
		{
			case 'user_id': $order_by = 'u.user_id '.$filter['order_dir'].' '; break;
			case 'login': $order_by = 'u.login '.$filter['order_dir'].' '; break;
			case 'customer': $order_by = 'c.name '.$filter['order_dir'].', u.fname, u.lname '; break;
			case 'email': $order_by = 'u.email '.$filter['order_dir'].', u.fname, u.lname '; break;
			case 'active': $order_by = 'u.active '.$filter['order_dir'].', u.fname, u.lname '; break;
			case 'restrict_customers': $order_by = 'u.restrict_customers '.$filter['order_dir'].', u.fname, u.lname '; break;
			
			default: $order_by = 'u.fname '.$filter['order_dir'].', u.lname '.$filter['order_dir'].' '; 
		}
		
		$q = 'FROM '.TBL_REMOVED_USERS.' u INNER JOIN '.TBL_REMOVED_USERS_CUSTOMERS.' uc ON u.user_id=uc.user_id ';
		
		if ($filter['type']==USER_TYPE_CUSTOMER and $filter['assigned_user_id'])
		{
			// Check both direct user assignment and group assignment
			$q.= 'INNER JOIN '.TBL_USERS_CUSTOMERS_ASSIGNED.' ac ON uc.customer_id=ac.customer_id ';
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON ac.user_id=ug.group_id ';
		}
		if ($filter['order_by'] == 'customer')
		{
			$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' c ON u.customer_id=c.id ';
		}
		$q.= 'WHERE ';
		
		if (isset($filter['type'])) $q.= 'u.type&'.$filter['type'].'=u.type AND ';
		if ($filter['customer_id'] > 0) $q.= 'uc.customer_id='.$filter['customer_id'].' AND ';
		
		if ($filter['type']==USER_TYPE_CUSTOMER and $filter['assigned_user_id'])
		{
			$q.= '(ac.user_id='.$filter['assigned_user_id'].' OR ';
			$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$filter['assigned_user_id'].')) AND ';
		}
		
		$q = preg_replace ('/\s*AND\s*$/', ' ', $q);
		$q = preg_replace ('/\s*WHERE\s*$/', ' ', $q);
		
		if (isset($count))
		{
			$count = db::db_fetch_field ('SELECT count(u.id) AS cnt '.$q, 'cnt');
		}
		
		$q = 'SELECT DISTINCT u.id '.$q.' ORDER BY '.$order_by;
		if (isset($filter['start']) and isset($filter['limit'])) $q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
		
		$ids = db::db_fetch_vector ($q);
		
		foreach ($ids as $id)
		{
			$ret[] = new RemovedUser($id);
		}
		return $ret;
	}
	
	/**
	* [Class Method] Returns an array with the user IDs and names
	* @param	array		$filter			Array with filtering criteria. Can contain:
	*							- active: an active status code; or USER_FILTER_ACTIVE_AWAY or USER_FILTER_ALL.
	*							  If not specified, by default USER_FILTER_ACTIVE_AWAY is used.
	*							- type: the user type
	*							- customer_id: return only users for this customer
	* @param	string		$group_prefix		A prefix to add to the group object names
	* @return	array					Associative array, with the keys being user IDs and the values their names
	*/
	function get_users_list ($filter = array(), $group_prefix = '[G] ')
	{
		$ret = array ();
		
		// Unless specified expressly, return only active users

		$q = 'SELECT id, u.user_id, fname, lname, type, login FROM '.TBL_REMOVED_USERS.' u INNER JOIN '.TBL_REMOVED_USERS_CUSTOMERS.' uc on u.user_id=uc.user_id WHERE ';
		
		if (isset($filter['type'])) $q.= 'type&'.$filter['type'].'=type AND ';
		if (isset($filter['customer_id'])) $q.= 'uc.customer_id='.$filter['customer_id'].' AND ';
		
		$q = preg_replace ('/\s*AND\s*$/', ' ', $q);
		$q = preg_replace ('/\s*WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY fname, lname ';
		//debug($q);
		$users = db::db_fetch_array ($q);

		
		foreach ($users as $usr)
		{
			$name = trim ($usr->fname.' '.$usr->lname);
			if (!$name) $name = trim ($usr->login);
			$ret[$usr->id] = (($usr->type==USER_TYPE_KEYSOURCE_GROUP OR $usr->type==USER_TYPE_GROUP) ? $group_prefix : '').$name;
		}
		return $ret;
	}
	
	function get_users_customer_list()
	{
		$customers = array();
		$q = "SELECT customer_id from ".TBL_REMOVED_USERS_CUSTOMERS." where user_id = ".$this->user_id;
		$customers = DB::db_fetch_vector($q);
		return $customers;
	}
	
	/**
	 * restore an unser account from an archived one
	 *
	 * @return bool
	 */
	function restore()
	{
		//first restore a user account from this archived one
		$user = new User();
		foreach($user->fields as $fld)
		{
			if($fld=="id") $user->id = $this->user_id;
			else $user->$fld = $this->$fld;
		}
		if(!$user->id) return FALSE;
		$user->save_data();
		$q = "select customer_id from ".TBL_REMOVED_USERS_CUSTOMERS." where user_id = ".$user->id;
		$customers_list = $this->db_fetch_vector($q);
		
		foreach($customers_list as $cid)
		{
			if($cid!=$user->customer_id)
			{
				$q = "insert into ".TBL_USERS_CUSTOMERS." values (".$user->id.",".$cid.")";
				$this->db_query($q);
				$q = "delete from ".TBL_REMOVED_USERS_CUSTOMERS." where user_id=".$user->id;
				$this->db_query($q);
			}
		}
		
		$this->delete();
		return TRUE;
		
	}
	
	function log_action_user($action_type, $user_id)
	{
			$q = "insert into ".TBL_USER_ACTION_LOG."(action, user_id, action_user, action_date) values (".$action_type.",".$user_id.", ".$this->user_id.", ".time().")";
			$this->db_query($q);
	}
}
?>
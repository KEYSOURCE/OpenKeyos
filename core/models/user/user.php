<?php

class_load('UserPhone');
class_load('UserExchange');

/**
* Class for storing and manipulating user information.
*
* An User object can represent an actual user (Keysource or customer user) or a 
* group of users (either Keysource only or any users).
*
*/
class User extends Base
{
	/** User ID
	* @var int */
	var $id = null;

	/** Login name
	* @var string */
	var $login = '';
	
	/** Password
	* @var string */
	var $password = '';
	var $password_f = '';
	
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
	
	var $has_kadeum = false;
	
	
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
	
	
	/** The database table storing user data 
	* @var string */
	var $table = TBL_USERS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'login', 'password', 'administrator', 'fname', 'lname', 'type', 'is_manager', 'customer_id', 'email', 'active', 'allow_private', 'restrict_customers', 'erp_name', 'erp_id', 'erp_id_service', 'erp_id_travel', 'away_recipient_id', 'language', 'newsletter', 'allow_dashboard', 'has_kadeum');
	
	
	/**
	* Constructor, also loads the user data from the database if a user ID is specified
	* @param	int $id		The user's id
	*/
	function User($id = null)
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
				$q = 'SELECT id FROM '.TBL_USERS_PHONES.' WHERE user_id='.$this->id;
				$ids = self::db_fetch_array($q);
				foreach ($ids as $id) $this->phones[] = new UserPhone ($id->id);
				
				// Load the assigned roles
				$q = 'SELECT acl_role_id FROM '.TBL_ACL.' WHERE user_id='.$this->id;
				$ids = self::db_fetch_array ($q);
				
				foreach ($ids as $id)
				{
					$this->roles[] = new AclRole ($id->acl_role_id);
					$this->roles_list[] = $id->acl_role_id;
				}
				
				// If this is a customer account, load the customer name, for easy access
				if ($this->customer_id)
				{
					$this->restrict_customers = true;
					$this->customer_name = self::db_fetch_field ('SELECT name FROM '.TBL_CUSTOMERS.' WHERE id='.$this->customer_id, 'name');
				}
				else
				{
					$this->customer_name = self::db_fetch_field ('SELECT name FROM '.TBL_CUSTOMERS.' WHERE id='.MANAGER_CUSTOMER_ID, 'name');
				}
				
				// See if there is any Exchange information for this user
				$this->exchange = new UserExchange ($this->id);
				if (!$this->exchange->id) $this->exchange = null;
				
				// Check for the encoding
				//if (mb_detect_encoding($this->fname) == 'UTF-8') $this->fname = utf8_decode ($this->fname);
				//if (mb_detect_encoding($this->lname) == 'UTF-8') $this->lname = utf8_decode ($this->lname);
			}
		}
		return $ret;
	}

	
	/** Loads the object data from an array */
	function load_from_array ($data = array())
	{
		parent::load_from_array ($data);
		if (isset($data['roles_list']))
		{
			$this->roles_list = $data['roles_list'];
		}
	}
	
	
	/**
	* Checks if the user data is valid
	*/
	function is_valid_data ()
	{
		$ret = true;
		
		// Ensure consistency of login and login_password fields
		$this->login_password = $this->login.$this->password;
		if ($this->type == USER_TYPE_KEYSOURCE_GROUP or $this->type == USER_TYPE_GROUP) $this->login = $this->fname;
		
		if (!$this->login) {error_msg ($this->get_string('NEED_LOGIN_NAME')); $ret = false;}
		if (!$this->password) {error_msg ($this->get_string('NEED_PASSWORD')); $ret = false;}
		if (!$this->fname) {error_msg ($this->get_string('NEED_FIRST_NAME')); $ret = false;}
		if (!$this->lname) {error_msg ($this->get_string('NEED_LAST_NAME')); $ret = false;}
		
		// Check uniqueness of login name
		if ($this->login)
		{
			$q = 'SELECT count(*) as cnt FROM '.TBL_USERS.' WHERE login="'.mysql_escape_string($this->login).'" ';
			if ($this->id) $q.= 'AND id<>'.$this->id;
			$cnt = self::db_fetch_field ($q, 'cnt');
			
			if ($cnt > 0) {error_msg ($this->get_string('NEED_UNIQUE_LOGIN_NAME')); $ret = false;}
		}
		
		if ($this->type == USER_TYPE_CUSTOMER and !$this->customer_id)
		{
			error_msg ($this->get_string('NEED_CUSTOMER_ACCOUNT'));
			$ret = false;
		}
		
		if ($this->customer_id and $this->type!=USER_TYPE_CUSTOMER)
		{
			error_msg ($this->get_string('MUST_BE_CUSTOMER_USER'));
			$ret = false;
		}
		
		// If the user's active status is "Away", make sure there is an alternate recipient defined,
		// and that user is not away or inactive
		if ($this->id and $this->is_away())
		{
			if (!$this->away_recipient_id) {error_msg ($this->get_string('NEED_ALTERNATE_RECIPIENT')); $ret = false;}
			elseif ($this->away_recipient_id==$this->id)
			{
				error_msg ($this->get_string('NEED_DIFFERENT_ALTERNATE_RECIP'));
				$ret = false;
			}
			elseif (!User::is_active_strict($this->away_recipient_id) or User::is_customer_user($this->away_recipient_id))
			{
				error_msg ($this->get_string('NEED_ACTIVE_RECIPIENT'));
				$ret = false;
			}
		}
		
		return $ret;
	}
	
	
	/**
	* Save the user data
	*/
	function save_data ()
	{
		// For not "Away" users make sure there is no alternate recipient defined
		if (!$this->is_away()) $this->away_recipient_id = 0;
	
		parent::save_data ();
		if(strlen($this->password) < 32)
		{
    		    $pp = md5($this->password);
    		    $this->password_f = $this->password;
		}
		else
		    $pp = $this->password;
		$qq = "update ".TBL_USERS." set password='".$pp."' where login='".mysql_escape_string($this->login)."' and password='".mysql_escape_string($this->password)."'";
		//debug($qq);
		self::db_query($qq);
		if(!$this->customer_id) $this->customer_id=0;
		$qq = "replace into ".TBL_USERS_CUSTOMERS." values (".$this->id.", ".$this->customer_id.")";
		//debug($qq);
		self::db_query($qq);
			
		// Ensure consistency of login and login_password fields
		$this->login_password = $this->login.$this->password;
		if ($this->type == USER_TYPE_KEYSOURCE_GROUP or $this->type == USER_TYPE_GROUP) $this->login = $this->fname;
		
		if ($this->id)
		{
			// Save the roles list
			self::db_query ('DELETE FROM '.TBL_ACL.' WHERE user_id='.$this->id);
			
			if (count($this->roles_list) > 0)
			{
				$q = 'INSERT INTO '.TBL_ACL.' (user_id, acl_role_id) VALUES ';
				for ($i=0; $i<count($this->roles_list); $i++)
				{
					$q.= '('.$this->id.', '.$this->roles_list[$i].'), ';
				}
				$q = preg_replace ('/,\s*$/', '', $q);
				self::db_query ($q);
			}
			
			// In case the user is set to have access to all customers, make sure to clear the list of assigned customers
			if (!$this->is_customer_user() and !$this->restrict_customers)
			{
				self::db_query ('DELETE FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' WHERE user_id='.$this->id);
			}
			
			// If the user is disabled, remove him from all groups and all notification recipients
			if ($this->is_inactive())
			{
				$q = 'DELETE FROM '.TBL_USERS_GROUPS.' WHERE user_id='.$this->id;
				self::db_query ($q);
				$q = 'DELETE FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' WHERE user_id='.$this->id;
				self::db_query ($q);
				$q = 'DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' WHERE user_id='.$this->id;
				self::db_query ($q);
				$q = 'DELETE FROM '.TBL_TICKETS_ESCALATION_RECIPIENTS.' WHERE user_id='.$this->id;
				self::db_query ($q);
				$q = 'DELETE FROM '.TBL_ALERTS_RECIPIENTS.' WHERE user_id='.$this->id;
				self::db_query ($q);
			}
		}
	}
	
	
	/**
	* Checks if the user is allowed to access the specified operation
	* Check is done directly in the database (as opposed to using objects) to increase speed
	*/
	function can_access ($class = '', $function = '')
	{
		$ret = false;

		if ($this->id)
		{
			if ($this->administrator)
			{
				// Administrator have full access;
				$ret = true;
			}
			else
			{                          
				// If a function name is not specified, fetch the default method from class
				if (!$function) $function = $GLOBALS['CLASSES_DISPLAY'][$class]['default_method'];
			
				// Check the individual permissions
				$function = preg_replace ('/_submit$/', '', $function);
				
				$q = 'SELECT module, function FROM '.TBL_ACL.' a ';
				$q.= 'INNER JOIN '.TBL_ACL_ROLES_ITEMS.' r ON a.acl_role_id=r.acl_role_id ';
				$q.= 'INNER JOIN '.TBL_ACL_ITEMS.' i ON r.acl_item_id = i.id ';
				$q.= 'INNER JOIN '.TBL_ACL_ITEMS_OPERATIONS.' o ON i.id=o.acl_item_id ';
				$q.= 'WHERE a.user_id='.$this->id.' AND ';
				$q.= 'o.module="'.mysql_escape_string($class).'" AND o.function="'.mysql_escape_string($function).'" ';
				$q.= 'LIMIT 1';
                                
				$perms = self::db_fetch_array ($q);

                                //debug($perms);

				$ret = (count($perms) > 0);
			}
		}
		
		return $ret;
	}
	
	
	/** Checks if the user can be deleted */
	function can_delete ()
	{
		$ret = true;
		
		if ($this->id)
		{
			// Check if there are no tickets assigned to this user
			$q = 'SELECT count(*) AS cnt FROM '.TBL_TICKETS.' WHERE owner_id='.$this->id.' OR assigned_id='.$this->id.' OR user_id='.$this->id;
			$cnt_tickets = self::db_fetch_field ($q, 'cnt');
			$q = 'SELECT count(*) AS cnt FROM '.TBL_TICKETS_DETAILS.' WHERE user_id='.$this->id.' OR assigned_id='.$this->id;
			$cnt_tickets+= self::db_fetch_field ($q, 'cnt');
			
			if ($cnt_tickets > 0) {error_msg ($this->get_string('CANT_DELETE_LINKED_USER')); $ret = false;}
			
			// Check if the user is not a default notifications recipient
			$q = 'SELECT count(*) as cnt FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' WHERE user_id='.$this->id;
			$cnt = self::db_fetch_field ($q, 'cnt');
			
			if ($cnt > 0) {error_msg ($this->get_string('CANT_DELETE_NOTIF_USER')); $ret = false;}
		}
		
		return $ret;
	}
	
	
	/** Deletes the user and its associated objects */
	function delete ()
	{
		if ($this->id)
		{
			// Delete the phone numbers
			self::db_query ('DELETE FROM '.TBL_USERS_PHONES.' WHERE user_id='.$this->id);
			
			// Delete the notifications 
			self::db_query ('DELETE FROM '.TBL_NOTIFICATIONS.' WHERE user_id='.$this->id);
			
			// Remove the user from the list of customer recipient_notifications
			self::db_query ('DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' WHERE user_id='.$this->id);
			
			// Delete the user reference from all groups;
			self::db_query ('DELETE FROM '.TBL_USERS_GROUPS.' WHERE user_id='.$this->id);
			
			// Delete the lists of assigned and favorite customers
			self::db_query ('DELETE FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' WHERE user_id='.$this->id);
			self::db_query ('DELETE FROM '.TBL_USERS_CUSTOMERS_FAVORITES.' WHERE user_id='.$this->id);
			
			parent::delete ();
		}
	}
	
	
	/** Returns the ticket on which the user is working now - if any */
	function get_now_working ()
	{
		$ret = false;
		if ($this->id)
		{
			$q = 'SELECT ticket_id FROM '.TBL_NOW_WORKING.' WHERE user_id='.$this->id;
			$ticket_id = self::db_fetch_field ($q, 'ticket_id');
			if ($ticket_id) $ret = new Ticket ($ticket_id);
		}
		return $ret;
	}

	
	/**
	* Returns true or false if this user object represents an actual user or a  group.
	* Can be called as object method or as class method - in which case a user ID must be specified
	*/
	public static function is_group ($user_id = null)
	{
//		if (!$user_id)
//		{
//			// This was called as class method
//			$ret = (($this->type == USER_TYPE_KEYSOURCE_GROUP) or ($this->type == USER_TYPE_GROUP));
//		}
//		else
//		{
        $type = db::db_fetch_field ('SELECT type FROM '.TBL_USERS.' WHERE id='.$user_id, 'type');
        $ret = (($type == USER_TYPE_KEYSOURCE_GROUP) or ($type == USER_TYPE_GROUP));
//		}
		return $ret;
	}

	
	
	/** Tells is the user is active - including if he is away. Can be called as class method too, with specifying $user_id
	* @param	int		$user_id	The ID of the user to check, if called as class method
	*/
	function is_active ($user_id = null)
	{
		if (!$user_id) $active = $this->active;
		else $active = db::db_fetch_field ('SELECT active FROM '.TBL_USERS.' WHERE id='.$user_id, 'active');
		
		return ($active==USER_STATUS_ACTIVE or $active==USER_STATUS_AWAY_BUSINESS or $active==USER_STATUS_AWAY_HOLIDAY);
	}
	
	
	/** Tells is the user is strictly active - excluding away stats. Can be called as class method too, with specifying $user_id
	* @param	int		$user_id	The ID of the user to check, if called as class method
	*/
	function is_active_strict ($user_id = null)
	{
		if (!$user_id) $active = $this->active;
		else $active = db::db_fetch_field ('SELECT active FROM '.TBL_USERS.' WHERE id='.$user_id, 'active');
		return ($active==USER_STATUS_ACTIVE);
	}

    public static function is_active_strict_ex($user_id = null)
    {
        $active = db::db_fetch_field ('SELECT active FROM '.TBL_USERS.' WHERE id='.$user_id, 'active');
        return ($active==USER_STATUS_ACTIVE);
    }
	
	
	/** Tells if the user's active status is "Away". Can be called as class method too, with specifying $user_id
	* @param	int		$user_id	The ID of the user to check, if called as class method
	* @return	int				The ID of the alternate recipient if the user is away or 0 if not.
	*						When called as class method, if the active status is Away but there is
	*						no assigned alternate recipient, the function returns True. This is is
	*						need for is_valid_data().
	*/
	function is_away ($user_id = null)
	{
		$ret = 0;
		if ($user_id)
		{
			$res = db::db_fetch_row ('SELECT active, away_recipient_id FROM '.TBL_USERS.' WHERE id='.$user_id);
			if ($res['active']==USER_STATUS_AWAY_BUSINESS or $res['active']==USER_STATUS_AWAY_HOLIDAY) $ret = $res['away_recipient_id'];
		}
		else
		{
			if ($this->active==USER_STATUS_AWAY_BUSINESS or $this->active==USER_STATUS_AWAY_HOLIDAY)
			{
				if ($this->away_recipient_id) $ret = $this->away_recipient_id;
				else $ret = true;
			}
		}
		
		return $ret;
	}

    public static function user_is_away($user_id){
        $ret = 0;
        if ($user_id)
        {
            $res = db::db_fetch_row ('SELECT active, away_recipient_id FROM '.TBL_USERS.' WHERE id='.$user_id);
            if ($res['active']==USER_STATUS_AWAY_BUSINESS or $res['active']==USER_STATUS_AWAY_HOLIDAY) $ret = $res['away_recipient_id'];
        }

        return $ret;
    }
	
	
	/** [Class Method] Returns an array with the IDs of away users and the corresponding alternate recipients
	* @return	array				Associative array, the keys being array of users who are away and  
	*						the values being the IDs of the alternate recipients
	*/
    public static function get_away_ids ()
	{
		$ret = array ();
		
		$q = 'SELECT id, away_recipient_id FROM '.TBL_USERS.' WHERE ';
		$q.= '(active='.USER_STATUS_AWAY_BUSINESS.' OR active='.USER_STATUS_AWAY_HOLIDAY.') AND away_recipient_id>0 ';
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/** Tells if the user account is disabled. Can be called as class method too, in which case $user_id must be specified */
	function is_inactive ($user_id = null)
	{
		if (!$user_id) $active = $this->active;
		else $active = db::db_fetch_field ('SELECT active FROM '.TBL_USERS.' WHERE id='.$user_id, 'active');
		
		return ($active==USER_STATUS_INACTIVE);
	}
	
	
	/** Checks if this user is defined as alternate recipient for another "Away" user
	* @return	array(User)					The user(s) for whom this user is alternate recipient, if any
	*/
	function get_away_recipient_for ()
	{
		$ret = array();
		
		if ($this->id)
		{
			$q = 'SELECT id FROM '.TBL_USERS.' WHERE away_recipient_id='.$this->id.' ORDER BY fname, lname';
			$ids = self::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new User ($id);
		}
		
		return $ret;
	}
	
	
	
	/**
	* Returns true or false if this is a customer user or a Keysource user.
	* Can be called as object method or as class method - in which case a user ID must be specified.
	*/
	function is_customer_user ($user_id = null)
	{
		$ret = false;
		if (!$user_id) $customer_id = $this->customer_id;
		else $customer_id = db::db_fetch_field ('SELECT customer_id FROM '.TBL_USERS.' WHERE id='.$user_id, 'customer_id');
		
		return ($customer_id>0);
	}
	
	
	/** Returns the full name for the user */
	function get_name ($group_prefix = '[G] ')
	{
		$ret = trim ($this->fname.' '.$this->lname);
		if (!$ret) $ret = trim ($this->login);
		if (self::is_group($this->id)) $ret = $group_prefix.$ret;
		return $ret;
	}
	
	/** Returns the short (e.g. login) name for the user */
	function get_short_name ($group_prefix = '[G] ')
	{
		if (self::is_group($this->id))
		{
			$ret = $group_prefix.trim ($this->fname);
		}
		else
		{
			$ret = trim ($this->login);
		}
		return $ret;
	}
	
	
	/** Returns the group membership for this user. 
	* @return	array				Associative array, key being group IDs and values being group names
	*/
	function get_groups_list ()
	{
		$ret = array();
		if ($this->id)
		{
			$q = 'SELECT DISTINCT g.group_id, u.fname FROM '.TBL_USERS_GROUPS.' g INNER JOIN '.TBL_USERS.' u ';
			$q.= 'ON g.group_id=u.id WHERE g.user_id='.$this->id.' ORDER BY u.fname ';
			$ret = db::db_fetch_list($q);
		}
		
		return $ret;
	}
	
	
	/** 
	* Adds customers to the list of customers assigned to this user.
	* Can be called as object method or class method, in which case the $user_id parameter needs
	* to be specified
	* @param	array	$customer_ids		Array with the customer IDs to add to the list
	* @param	int	$user_id		The user to which the customers will be added. Ignored
	*						if this is called as object method
	*/
	function add_assigned_customers ($customer_ids = array(), $user_id = null)
	{
		if ($this->id and strtolower(get_class($this))=='user') $user_id = $this->id;
		
		if (is_array($customer_ids) and count($customer_ids) and $user_id)
		{
			$q = '';
			foreach ($customer_ids as $customer_id) $q.= '('.$user_id.', '.$customer_id.'), ';
			$q = preg_replace ('/,\s*$/', '', $q);
			if ($q)
			{
				$q = 'REPLACE INTO '.TBL_USERS_CUSTOMERS_ASSIGNED.' (user_id, customer_id) VALUES '.$q;
				if ($this->id) self::db_query ($q);
				else db::db_query ($q);
			}
		}
	}
	
	
	/**
	* Sets the list of assigned customers for a user. Unlike add_assigned_customers(), this method
	* will replace completly the previous existing list of assigned customers.
	* Can be called as object method or class method, in which case the $user_id parameter needs
	* to be specified
	* @param	array	$customer_ids		Array with the customer IDs which make up the new list.
	* @param	int	$user_id		The user to which the customers will be added. Ignored
	*						if this is called as object method
	*/
	function set_assigned_customers ($customer_ids = array(), $user_id = null)
	{
		if ($this->id and strtolower(get_class($this))=='user') $user_id = $this->id;
		
		if ($user_id)
		{
			// Delete the previous list
			$q = 'DELETE FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' WHERE user_id='.$user_id;
			if ($this->id)
			{
				self::db_query ($q);
				$this->add_assigned_customers ($customer_ids, $user_id);
			}
			else
			{
				db::db_query ($q);
				User::add_assigned_customers ($customer_ids, $user_id);
			}
		}
	}
	
	
	/**
	* Returns the list of customer IDs assigned to a user.
	* Can be called as object method or class method, in which case the $user_id parameter needs
	* to be specified.
	* @param	bool	$ignore_group		If False, it will return both directly assigned and customers assigned
	*						via groups. If True, it will  return only directly assigned customers.
	* @param	int	$user_id		The user for which to return the list. Ignored if 
	*						this is called as object method.
	* @return	array				Associative array with the customers assigned to the user,
	*						the keys being customer IDs and the values being customer names.
	*/
	public static function get_assigned_customers_list ($ignore_group = false, $user_id = false)
	{
		$ret = array ();
		//if ($this->id and strtolower(get_class($this))=='user') $user_id = $this->id;
		
		if ($user_id)
		{
			$usr = new User($user_id);
			if($usr->is_customer_user() and  $usr->type == USER_TYPE_CUSTOMER)
			{
				$query = "select uc.customer_id, c.name from ".TBL_USERS_CUSTOMERS." uc inner join ".TBL_CUSTOMERS." c on uc.customer_id=c.id ";
				$query .= " where uc.user_id=".$usr->id." ORDER BY c.name";
				//debug($query);
				$ret = db::db_fetch_list ($query);
			}
			else 
			{
				if (!$ignore_group)
				{
					$q = 'SELECT a.customer_id, c.name FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' a ';
					$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON a.user_id=ug.group_id ';
					$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' c ON a.customer_id=c.id ';
					$q.= 'WHERE (a.user_id='.$user_id.' OR ';
					$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$user_id.'))';
					$q.= 'ORDER BY c.name ';
				}
				else
				{
					$q = 'SELECT a.customer_id, c.name FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' a ';
					$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' c ON a.customer_id = c.id ';
					$q.= 'WHERE a.user_id='.$user_id.' ORDER BY c.name ';
				}
			
				$ret = db::db_fetch_list ($q);
			}
		}
		return $ret;
	}
	
	 
	/**
	* Returns the list of customer objects assigned to a user.
	* Can be called as object method or class method, in which case the $user_id parameter needs
	* to be specified.
	* @param	bool	$ignore_group		If False, it will return both directly assigned and customers assigned
	*						via groups. If True, it will  return only directly assigned customers.
	* @param	int	$user_id		The user for which to return the list. Ignored if 
	*						this is called as object method.
	* @return	array(Customer)			Array of Customer objects with the customers assigned to the user.
	*/
	public function get_assigned_customers ($ignore_group = false, $user_id = null)
	{
		$ret = array ();
		$ids = array ();
		if ($this->id and strtolower(get_class($this))=='user'){
            $ids = $this->get_assigned_customers_list ($ignore_group);
        } else{
            $ids = User::get_assigned_customers_list ($ignore_group, $user_id);
        }
		
		if (is_array ($ids) and count($ids) > 0)
		{
			foreach (array_keys($ids) as $customer_id) $ret[] = new Customer($customer_id);
		}
		
		return $ret;
	}
	
	
	/**
	* Returns a list of customers assigned to a user via group assignments. It serves as
	* a complement to get_assigned_customers_list().
	* Can be called as object method or class method, in which case the $user_id parameter is mandatory
	* @param	int	$user_id		The ID of the user for whom to return the list. Ignored
	*						when called as object method.
	* @raturn	array				Associative array of generic objects. The keys are customer
	*						IDs and the values are generic objects with the following
	*						fields: customer_name, group_id, group_name.
	*/
	function get_group_assigned_customers_list ($user_id = null)
	{
		$ret = array ();
		if ($this->id and strtolower(get_class($this))=='user') $user_id = $this->id;
		
		if ($user_id)
		{
			$q = 'SELECT a.customer_id, c.name as customer_name, g.id as group_id, g.fname as group_name FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' a ';
			$q.= 'INNER JOIN '.TBL_USERS_GROUPS.' ug ON a.user_id=ug.group_id ';
			$q.= 'INNER JOIN '.TBL_USERS.' g ON ug.group_id=g.id ';
			$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' c ON a.customer_id=c.id ';
			$q.= 'WHERE ug.user_id='.$user_id.' ORDER BY c.name ';
			
			$vals = db::db_fetch_array ($q);
			
			foreach ($vals as $val) 
			{
				$ret[$val->customer_id] = $val;
				unset ($ret[$val->customer_id]->customer_id);
			}
		}
		
		return $ret;
	}
	
	
	/**
	* Checks if the specified customer is assigned to this user, either directly or via a group
	* (unless $ignore_group is set to false). 
	* Can be called as object or class method, in which case the $user_id parameter is mandatory.
	* @param	int	$customer_id		The customer to check.
	* @param	bool	$ignore_group		If True, it will only check for direct assignment, ignoring
	*						group assignment.
	* @param	int 	$user_id		The user ID for whom to check. Ignored when called as object method
	* @return	bool				True or false if the customer is assigned or not to this user. For
	*						users with unrestricted access to customers, it always returns true.
	*/
	function has_assigned_customer($customer_id, $ignore_group = false, $user_id = null)
	{
		$ret = false;
        //debug("HERExxxxxxxxxxx: ".$customer_id); die;
        if ($this->id and strtolower(get_class($this))=='user')
		{
			$user_id = $this->id;
			$ret = !$this->restrict_customers;

		}
		elseif ($user_id)
		{
			$q = 'SELECT restrict_customers FROM '.TBL_USERS.' WHERE id='.$user_id;
			$ret = !db::db_fetch_field ($q, 'restrict_customers');
		}

                
		if (!$ret and $customer_id and $user_id)
		{
                        
			if (!$ignore_group)
			{
				$q = 'SELECT a.customer_id FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' a ';
				$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON a.user_id=ug.group_id ';
				$q.= 'WHERE a.customer_id = '.$customer_id.' AND (';
				$q.= 'a.user_id='.$user_id.' OR ';
				$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$user_id.'))';
			}
			else
			{
				$q = 'SELECT customer_id FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' ';
				$q.= 'WHERE user_id='.$user_id;
			}
                        
			$ret = ($customer_id == db::db_fetch_field($q, 'customer_id'));
		}
		
		return $ret;
	}

    /**
     * [Class Method]
     * Static version of the has_assigned_customer
     * @param	int	$customer_id		The customer to check.
     * @param	bool	$ignore_group		If True, it will only check for direct assignment, ignoring
     *						group assignment.
     * @param	int 	$user_id		The user ID for whom to check. Ignored when called as object method
     * @return	bool				True or false if the customer is assigned or not to this user. For
     *						users with unrestricted access to customers, it always returns true.
     */
    public static function has_assigned_customer_ex($customer_id, $ignore_group = false, $user_id = null)
    {
        $ret = false;

        $q = 'SELECT restrict_customers FROM '.TBL_USERS.' WHERE id='.$user_id;
        $ret = !db::db_fetch_field ($q, 'restrict_customers');


        if (!$ret and $customer_id and $user_id)
        {

            if (!$ignore_group)
            {
                $q = 'SELECT a.customer_id FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' a ';
                $q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON a.user_id=ug.group_id ';
                $q.= 'WHERE a.customer_id = '.$customer_id.' AND (';
                $q.= 'a.user_id='.$user_id.' OR ';
                $q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$user_id.'))';
            }
            else
            {
                $q = 'SELECT customer_id FROM '.TBL_USERS_CUSTOMERS_ASSIGNED.' ';
                $q.= 'WHERE user_id='.$user_id;
            }

            $ret = ($customer_id == db::db_fetch_field($q, 'customer_id'));
        }

        return $ret;
    }
	
	
	/** 
	* Adds customers to the list of favorite customers of a user.
	* Can be called as object method or class method, in which case the $user_id parameter needs
	* to be specified
	* @param	array	$customer_ids		Array with the customer IDs to add to the list
	* @param	int	$user_id		The user to which the customers will be added. Ignored
	*						if this is called as object method
	*/
	function add_favorite_customers ($customer_ids = array(), $user_id = null)
	{
		if ($this->id and strtolower(get_class($this))=='user') $user_id = $this->id;
		
		if (is_array($customer_ids) and count($customer_ids) and $user_id)
		{
			$q = '';
			foreach ($customer_ids as $customer_id) $q.= '('.$user_id.', '.$customer_id.'), ';
			$q = preg_replace ('/,\s*$/', '', $q);
			
			if ($q)
			{
				//debug($q);
				$q = 'REPLACE INTO '.TBL_USERS_CUSTOMERS_FAVORITES.' (user_id, customer_id) VALUES '.$q;
				if ($this->id) 
				{
					self::db_query ($q);
					//debug($q);
				}				
				else db::db_query ($q);
			}
		}
	}
	
	
	/**
	* Sets the list of favorite customers for a user. Unlike add_favorite_customers(), this method
	* will replace completly the previous existing list of favorite customers.
	* Can be called as object method or class method, in which case the $user_id parameter needs
	* to be specified
	* @param	array	$customer_ids		Array with the customer IDs which make up the new list.
	* @param	int	$user_id		The user to which the customers will be added. Ignored
	*						if this is called as object method
	*/
	function set_favorite_customers ($customer_ids = array(), $user_id = null)
	{
		//debug($customer_ids);
		if ($this->id and strtolower(get_class($this))=='user') $user_id = $this->id;
	
		if ($user_id)
		{
			//debug($user_id);
			// Delete the previous list
			$q = 'DELETE FROM '.TBL_USERS_CUSTOMERS_FAVORITES.' WHERE user_id='.$user_id;
			if ($this->id)
			{
				self::db_query ($q);
				$this->add_favorite_customers ($customer_ids, $user_id);
			}
			else
			{
				db::db_query ($q);
				User::add_favorite_customers ($customer_ids, $user_id);
			}
		}
	}
	
	
	/**
	* Returns the list of favorite customer IDs for a user.
	* Can be called as object method or class method, in which case the $user_id parameter needs
	* to be specified.
	* @param	int	$user_id		The user for which to return the list. Ignored if 
	*						this is called as object method.
	* @return	array				Associative array with the favorite customers of the user,
	*						the keys being customer IDs and the values being customer names.
	*/
	function get_favorite_customers_list ($user_id = null)
	{
		$ret = array ();
		if ($this->id and strtolower(get_class($this))=='user') $user_id = $this->id;
		
		if ($user_id)
		{
			$q = 'SELECT a.customer_id, c.name FROM '.TBL_USERS_CUSTOMERS_FAVORITES.' a ';
			$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' c ON a.customer_id = c.id ';
			$q.= 'WHERE a.user_id='.$user_id.' ORDER BY c.name ';
			
			if ($this->id) $ret = self::db_fetch_list ($q);
			else $ret = db::db_fetch_list ($q);
		}
		return $ret;
	}
	
	
	/**
	* Returns the list of favorite customer objects for a user.
	* Can be called as object method or class method, in which case the $user_id parameter needs
	* to be specified.
	* @param	int	$user_id		The user for which to return the list. Ignored if 
	*						this is called as object method.
	* @return	array(Customer)			Array of Customer objects with the favorite customers for the user.
	*/
	function get_favorite_customers ($user_id = null)
	{
		$ret = array ();
		$ids = array ();
		if ($this->id and strtolower(get_class($this))=='user') $ids = $this->get_favorite_customers_list ();
		else $ids = User::get_favorite_customers_list ($user_id);
		
		if (is_array ($ids) and count($ids) > 0)
		{
			foreach (array_keys ($ids) as $customer_id) $ret[] = new Customer ($customer_id);
		}
		
		return $ret;
	}
	
	
	/**
	* Returns the notifications type where this user is assigned as recipient, either
	* directly or through a group.
	* @param	bool	$by_group		If False (default) will return only the notifications types
	*						where the user is directly assigned. If True, it will return
	*						only the notifications types where the user is assigned through
	*						groups.
	* @return	array				Associative array with the notification types where this user is recipient,
	*						the keys being notifications type IDs and the values being True or False
	*						if the user is default recipient or not.
	*/
	function get_assigned_notifications_types ($by_group = false)
	{
		$ret = array ();
		
		if ($by_group)
		{
			$q = 'SELECT r.notif_obj_class, max(is_default) FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' r ';
			$q.= 'INNER JOIN '.TBL_USERS_GROUPS.' g ON r.user_id=g.group_id ';
			$q.= 'WHERE g.user_id='.$this->id.' ';
			$q.= 'GROUP BY g.user_id ';
		}
		else
		{
			$q = 'SELECT r.notif_obj_class, is_default FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' r ';
			$q.= 'WHERE r.user_id='.$this->id.' ';
		}
		
		$q.= 'ORDER BY r.notif_obj_class ';
		$ret = self::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/**
	* Returns the customers for whom this user is assigned as notification recipient, either
	* directly or through a group.
	* @param	bool	$by_group		If False (default) will return only the customers
	*						where the user is directly assigned. If True, it will return
	*						only the customers where the user is assigned through
	*						groups.
	* @return	array				Associative array with the customers where this user is notifications recipient,
	*						the keys being customer IDs and the values being associative arrays, with
	*						notification type as keys and the values being True or False
	*						if the user is default recipient or not.
	*/
	function get_assigned_notifications_customers ($by_group = false)
	{
		$ret = array ();
		
		if ($by_group)
		{
			$q = 'SELECT r.customer_id, r.notif_obj_class, max(r.is_default) as is_default FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' r ';
			$q.= 'INNER JOIN '.TBL_USERS_GROUPS.' g ON r.user_id=g.group_id ';
			$q.= 'WHERE g.user_id='.$this->id.' ';
			$q.= 'GROUP BY g.user_id ';
		}
		else
		{
			$q = 'SELECT r.customer_id, r.notif_obj_class, is_default FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' r ';
			$q.= 'WHERE r.user_id='.$this->id.' ';
		}
		
		$q.= 'ORDER BY r.notif_obj_class ';
		$recips = self::db_fetch_array ($q);
		foreach ($recips as $recip)
		{
			$ret[$recip->customer_id][$recip->notif_obj_class] = $recip->is_default;
		}
		
		return $ret;
	}
	
	
	/** Sends an "invitation" e-mail to the user with the account details. Normally 
	* this is called after account creation
	*/
	function send_invitation_email ()
	{
		if ($this->id and $this->email)
		{
			$lang_ext = ($this->language != LANG_EN ? '.'.$GLOBALS['LANGUAGE_CODES'][$this->language] : '');
			$tpl = '_classes_templates/user/invitation_mail.tpl'.$lang_ext;
			$tpl_subject = '_classes_templates/user/invitation_mail_subject.tpl'.$lang_ext;
			
			$parser = new BaseDisplay ();
			$parser->assign ('user', $this);
			$parser->assign ('base_url', get_base_url());
			
			$msg = $parser->fetch ($tpl);
			$subject = $parser->fetch ($tpl_subject);
			
			$headers = 'From: '.SENDER_NAME.' <'.SENDER_EMAIL.'>'."\n";
			$headers.= 'Date: '.date("D, j M Y G:i:s O")."\n";
			
			@mail ($this->email, $subject, $msg, $headers);
		}
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
	public static function get_users ($filter = array(), &$count)
	{
		$ret = array();
		
		// Unless specified expressly, return only active users
		if (!isset($filter['active'])) $filter['active'] = USER_FILTER_ACTIVE_AWAY;
		elseif ($filter['active'] == USER_FILTER_ALL) unset ($filter['active']);
		elseif (!$filter['active']) $filter['active'] = USER_STATUS_INACTIVE;
		
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'ASC';
		switch ($filter['order_by'])
		{
			case 'id': $order_by = 'u.id '.$filter['order_dir'].' '; break;
			case 'login': $order_by = 'u.login '.$filter['order_dir'].' '; break;
			case 'customer': $order_by = 'c.name '.$filter['order_dir'].', u.fname, u.lname '; break;
			case 'email': $order_by = 'u.email '.$filter['order_dir'].', u.fname, u.lname '; break;
			case 'active': $order_by = 'u.active '.$filter['order_dir'].', u.fname, u.lname '; break;
			case 'restrict_customers': $order_by = 'u.restrict_customers '.$filter['order_dir'].', u.fname, u.lname '; break;
			
			default: $order_by = 'u.fname '.$filter['order_dir'].', u.lname '.$filter['order_dir'].' '; 
		}
		
		$q = 'FROM '.TBL_USERS.' u INNER JOIN '.TBL_USERS_CUSTOMERS.' uc ON u.id=uc.user_id ';
		
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
		if (isset($filter['active']))
		{
			if ($filter['active']>=0) $q.= 'active='.$filter['active'].' AND ';
			elseif ($filter['active']==USER_FILTER_ACTIVE_AWAY)
				$q.= '(u.active='.USER_STATUS_ACTIVE.' OR u.active='.USER_STATUS_AWAY_BUSINESS.' OR u.active='.USER_STATUS_AWAY_HOLIDAY.') AND ';
		}
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
		//debug($q);
		
		$ids = db::db_fetch_array ($q);
		
		foreach ($ids as $id)
		{
			$ret[] = new User ($id->id);
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
	public static function get_users_list ($filter = array(), $group_prefix = '[G] ')
	{
		$ret = array ();
		
		// Unless specified expressly, return only active users
		if (!isset($filter['active'])) $filter['active'] = USER_FILTER_ACTIVE_AWAY;
		elseif ($filter['active'] == USER_FILTER_ALL) unset ($filter['active']);
		elseif (!$filter['active']) $filter['active'] = USER_STATUS_INACTIVE;
		
		$q = 'SELECT id, fname, lname, type, login FROM '.TBL_USERS.' u INNER JOIN '.TBL_USERS_CUSTOMERS.' uc on u.id=uc.user_id WHERE ';
		
		if (isset($filter['type'])) $q.= 'type&'.$filter['type'].'=type AND ';
		if (isset($filter['active']))
		{
			if ($filter['active']>=0) $q.= 'active='.$filter['active'].' AND ';
			elseif ($filter['active']==USER_FILTER_ACTIVE_AWAY)
				$q.= '(active='.USER_STATUS_ACTIVE.' OR active='.USER_STATUS_AWAY_BUSINESS.' OR active='.USER_STATUS_AWAY_HOLIDAY.') AND ';
		}

        $current_user = $GLOBALS['CURRENT_USER'];

		if($current_user and $current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $current_user->get_assigned_customers_list();
			$q.= 'uc.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$q.=') AND type&'.USER_TYPE_CUSTOMER.'=type AND ';
		}
		else {
			if (isset($filter['type'])) $q.= 'type&'.$filter['type'].'=type AND ';
		}
		
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
	
	/**
	* [Class Method] Returns an array with the user IDs and their login names
	* @param	array		$filter			Array with filtering criteria
	* @return	array					Associative array, with the keys being user IDs and the values their logins
	*/
	public static function get_logins_list ($filter = array ())
	{
		$ret = array ();
		
		// Unless specified expressly, return only active users
		if (!isset($filter['active'])) $filter['active'] = 1;
		elseif ($filter['active'] == -1) unset ($filter['active']);
		elseif (!$filter['active']) $filter['active'] = 0;
		
		$q = 'SELECT id, login FROM '.TBL_USERS.' WHERE ';
		
		if (isset($filter['type'])) $q.= 'type&'.$filter['type'].'=type AND ';
		if (isset($filter['active'])) $q.= 'active='.$filter['active'].' AND ';
		if (isset($filter['customer_id'])) $q.= 'customer_id='.$filter['customer_id'].' AND ';
		
		$q = preg_replace ('/\s*AND\s*$/', ' ', $q);
		$q = preg_replace ('/\s*WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY fname, lname ';
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list with the user IDs defined for each customer
	* @return	array					Associative array, the keys being customer IDs and the values
	*							being associative array, with user IDs as keys and user names as values
	*/
	public static function get_customers_users_list ()
	{
		$ret = array ();
		
		$q = 'SELECT distinct id, fname, lname, type, uc.customer_id, login FROM '.TBL_USERS.' u inner join '.TBL_USERS_CUSTOMERS.' uc on u.id=uc.user_id ';
		$q.= 'WHERE type&'.USER_TYPE_CUSTOMER.'=type and uc.customer_id<>0 ORDER BY id';
		$users = DB::db_fetch_array ($q);
		
		foreach ($users as $usr)
		{
			$name = trim ($usr->fname.' '.$usr->lname);
			if (!$name) $name = $usr->login;
			$ret[$usr->customer_id][$usr->id] = $name;
		}
		return $ret;
	}
        
    /**
     * get a list of users that are assigned to the customers assigned to this user
     */
    function get_cusers_list(){
        $ret = array();
        $clist = $this->get_users_customer_list();
        foreach($clist as $cid){
            $query = "SELECT uc.user_id, concat(u.fname,' ',u.lname) FROM ".TBL_USERS_CUSTOMERS." uc INNER JOIN ";
            $query .= " ".TBL_USERS." u on u.id=uc.user_id WHERE uc.customer_id=".$cid;
            $ulist = self::db_fetch_list($query);
            $ret[$cid] = $ulist;
        }
        return $ret;
    }
	
	/**
	 * Returns a dlist with the customer_id defined for a customer
	 * 
	 * @return array	returns a list with the customer_id's assigned to this user
	 *
	 */
	function get_users_customer_list()
	{
		$customers = array();
		$q = "SELECT customer_id from ".TBL_USERS_CUSTOMERS." where user_id = ".$this->id;
		$customers = DB::db_fetch_vector($q);
		return $customers;
	}
	
	/**
	 * Adds a list of customer accounts associated with this user account
	 *
	 * @param  array $customer_ids     list of customer accounts id's
	 */
	function set_customers_account_list($customer_ids = array())
	{
		$ret = TRUE;
		if(!$customer_ids or !is_array($customer_ids) or empty($customer_ids))
			return FALSE;
		
		$allready_assigned = $this->get_users_customer_list();
		foreach($allready_assigned as $customer_id)
		{
			$removed_customers = array();
			if(!in_array($customer_id, $customer_ids))
			{
				$removed_customers[] = $customer_id; 
			}
			$tot_rem = count($removed_customers);
			if($tot_rem!=0)
			{
				for($i=0; $i<$tot_rem; $i++)
				{
					$q = "( ";
					$q.=$removed_customers[$i];
					if($i!=$tot_rem-1) $q.=",";
					if($i==$tot_rem-1) $q.=") ";
				}
				$q = "DELETE from ".TBL_USERS_CUSTOMERS." where user_id=".$this->id." and customer_id in ".$q;
				//debug($q);
				self::db_query($q);
			}
		}
		
		$query = "REPLACE into ".TBL_USERS_CUSTOMERS." values ";
		$tot_cust = count($customer_ids);
		for($i=0; $i<$tot_cust; $i++)
		{	
			$query.="(".$this->id.", ".$customer_ids[$i].")";
			if($i != $tot_cust-1) $query.=", ";
		}
		//debug($query);
		self::db_query($query);
		return $ret;
	}
	
	/**
	 * Merge more user accounts into this users account
	 * as a consequence, this user will have access to all the customers assigned to the user accounts that are merged,
	 * to all the tickets, and get all notifications for the merged accounts
	 *
	 * @param array $user_ids  List with the user id's to be merged
	 * @param bool $keep_accounts Boolean value that indicates if the merged accounts will be deleted permanently
	 * @return bool
	 */
	function merge_users_accounts($user_ids = array(), $keep_accounts_action = 1)
	{
		$ret = TRUE;
		if(!$user_ids or !is_array($user_ids) or empty($user_ids)) return FALSE;
		if(!$this->id) return FALSE;
		//TODO: Implement the merging function;
		//Main ideea
		//Get the customer_id (ids) for this user account
		//foreach customer_id, get the assigned users (and if there are any in this list, delete them)
		$cust_ids = $this->get_users_customer_list();
		foreach($cust_ids as $cid)
		{
			$query = "select user_id from ".TBL_USERS_CUSTOMERS." where customer_id=".$cid;
			$uids = self::db_fetch_vector($query);
			$query1 = "select user_id from ".TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS." where customer_id=".$cid;
			$uids1 = self::db_fetch_vector($query1);
			foreach($uids as $id)
			{
				if(in_array($id, $user_ids))
				{
					if($keep_accounts_action == 2)
					{
						$qq = "insert into ".TBL_REMOVED_USERS_CUSTOMERS." values (".$id.", ".$cid.")";
						self::db_query($qq);
					}
					$qq = "delete from ".TBL_USERS_CUSTOMERS." where user_id=".$id." and customer_id=".$cid;
					self::db_query($qq);
				}
			}  
			foreach($uids1 as $idn)
			{
				if(in_array($idn, $user_ids))
				{
					$qq1 = "delete from ".TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS." where user_id=".$idn." and customer_id=".$cid;
					self::db_query($qq1); 
				}
			}
		}
		if($keep_accounts_action == 2)
		{
			//archive the merged accounts
			class_load('RemovedUser');
			foreach($user_ids as $uid)
			{
				$rm_user = new RemovedUser();
				$rm_user->archive_account($uid, $this->id);
			}
		}
		//1. replace in TBL_USERS_CUSTOMERS the id's of the merging accounts with this account's id
		//this way, all the customer accounts from the merging users accounts will be transfered to this user account
		$users_count = count($user_ids);
		if($users_count!=0)
		{ 
			$q = "UPDATE ".TBL_USERS_CUSTOMERS." set user_id=".$this->id." where user_id in ";
			$ids = "(";
			for($i=0; $i<$users_count; $i++)
			{ 
				$ids.= $user_ids[$i];
				if($i!=$users_count-1) $ids.=", ";
				else $ids.=") ";   	
			}
		}
		else return FALSE;
		$q.=$ids;
		self::db_query($q);
		
		//2. in the notifications_customers_recipients_customers table we must opperate the same kind of change
		//we made shure above that we won't get a dupicatekey
		$q = "UPDATE ".TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS." set user_id = ".$this->id." where user_id in ".$ids;
		self::db_query($q);
		
		// delete the merged accounts if requested
		if($keep_accounts_action == 0)
		{
			//$qd = "DELETE from ".TBL_USERS." where id in ".$ids;
			//self::db_query($qd);
			foreach ($user_ids as $id)
			{
				$muser = new User($id);
				$muser->delete();
			}
		}
		return $ret;
	}
	
	/**
	 * logs the merging of an user or the adding or removing of a customer account
	 *
	 */
	function log_action_user($action_type, $user_id)
	{
			$q = "insert into ".TBL_USER_ACTION_LOG."(action, user_id, action_user, action_date) values (".$action_type.",".$user_id.", ".$this->id.", ".time().")";
			self::db_query($q);
	}
	
	/**
	 * [ClassMethod]
	 *checks if an email address belongs to a registered keyos user
	 * if the email is valid, return the user's ID, else return -1
	 */ 
	public static function checkEmailAddress($email)
	{
		$ret = -1;
		$query = "SELECT id, lname, fname from ".TBL_USERS." where email='".$email."'";
		$ret = db::db_fetch_field($query, 'id');
		return $ret;
	}

    function get_user_by_email($email) {
        $ret = $this->checkEmailAddress($email);
        if($ret) {
                        return new User($ret);
        }
        return null;
    }
}
?>
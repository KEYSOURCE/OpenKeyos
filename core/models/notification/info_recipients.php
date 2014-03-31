<?php

class_load ('Notification');

/**
* Class for managing notifications and information recipients.
*
* This class has only class methods, since it acts as a management interface, 
* there are no InfoRecipients objects to instantiate.
*
*/

class InfoRecipients
{

	/**
	* [Class Method] Returns an array with the recipients for a specific type of notifications
	*/
	public static function get_type_recipients ($type = null)
	{
		$ret = array ();
		
		if ($type)
		{
			$q = 'SELECT user_id FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' r ';
			$q.= 'INNER JOIN '.TBL_USERS.' u on r.user_id=u.id ';
			$q.= 'WHERE notif_obj_class = '.db::db_escape($type).' ';
			$q.= 'ORDER BY u.fname, u.lname ';
			$recips = db::db_fetch_array ($q);

			foreach ($recips as $recip) $ret[] = $recip->user_id;
		}

		return $ret;
	}
	
	/**
	* [Class Method] Returns an array with all the types of notifications and their recipients
	* @return	array			Associative array, with the keys being the notification
	*					classes and the values being arrays with the recipients user IDs
	*/
	public static function get_all_type_recipients ()
	{
		$ret = array ();
		
		$q = 'SELECT notif_obj_class, user_id FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' r ';
		$q.= 'INNER JOIN '.TBL_USERS.' u on r.user_id=u.id ';
		$q.= 'ORDER BY u.fname, u.lname ';
		
		$recips = db::db_fetch_array ($q);
		
		foreach ($recips as $recip) $ret[$recip->notif_obj_class][] = $recip->user_id;
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns an array with all the types of notifications and their default recipients
	* @return	array		Associative array, with the keys being the notification
	*				classes and the values being user IDs
	*/
	public static function get_all_type_default_recipients ()
	{
		$ret = array ();
		
		$q = 'SELECT notif_obj_class, user_id FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' ';
		$q.= 'WHERE is_default = 1';
		
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns all the account managers and their assigned customers. An account
	* manager is a Keysource user who has been designated as "Account manager" notification 
	* recipient for a customer
	* @return	array		Associative array, they keys being user IDs and the values being
	*				associative arrays with the customers for whom that user is
	*				account manager, the keys being customer IDs and the values being
	*				True or False if that user is also the default account manager.
	*/
	public static function get_accounts_managers ()
	{
		$ret = array ();
		
		$q = 'SELECT r.* FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' r LEFT OUTER JOIN '.TBL_USERS.' u ';
		$q.= 'ON r.user_id=u.id LEFT OUTER JOIN '.TBL_CUSTOMERS.' c ON r.customer_id=c.id ';
		$q.= 'WHERE notif_obj_class='.NOTIF_OBJ_CLASS_CUSTOMER.' ORDER BY u.fname, u.lname, c.name ';
		$data = db::db_fetch_array($q);
		
		foreach ($data as $d)
		{
			$ret[$d->user_id][$d->customer_id] = $d->is_default;
		}
		
		return $ret;
	}
	
	/**
	* [Class Method] Sets the customers for which a given user should be set as account manager
	* @param	int		$user_id		The ID of the user
	* @param	array		$assigned_customers	Array with ALL the IDs of the customers for which to be set as account manager
	* @param	array		$default_for		Array with the IDs of the customers for which the user should also be set as default account manager
	*/
	public static function set_account_manager ($user_id, $assigned_customers, $default_for)
	{
		if ($user_id and is_array($assigned_customers) and is_array($default_for))
		{
			// First, delete the entire old list of customers for which the user was previously set as default recipient
			$q = 'DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' WHERE user_id='.$user_id.' ';
			$q.= 'AND notif_obj_class='.NOTIF_OBJ_CLASS_CUSTOMER;
			DB::db_query ($q);
			
			// For all specified default customers, make sure they are not set as default for someone else
			// Work only on customers which are also present in $assigned_customers
			foreach ($default_for as $cust_id)
			{
				if (in_array($cust_id, $assigned_customers))
				{
					$q = 'UPDATE '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' SET is_default=0 ';
					$q.= 'WHERE notif_obj_class='.NOTIF_OBJ_CLASS_CUSTOMER.' AND customer_id='.$cust_id;
					//DB::db_query ($q);
				}
			}
			
			// Finally, set the new list of assigned customers
			foreach ($assigned_customers as $cust_id)
			{
				$q = 'INSERT INTO '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' (customer_id, notif_obj_class, user_id, is_default) VALUES ';
				$q.= '('.$cust_id.','.NOTIF_OBJ_CLASS_CUSTOMER.','.$user_id.','.(in_array($cust_id,$default_for) ? 1 : 0).')';
				DB::db_query ($q);
			}
			
			// Finally, check for customers for which none of the assigned account managers is set as default.
			$q = 'SELECT customer_id, sum(is_default) as is_default FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' ';
			$q.= 'WHERE notif_obj_class='.NOTIF_OBJ_CLASS_CUSTOMER.' GROUP BY customer_id HAVING is_default=0';
			$lst = DB::db_fetch_list ($q);
			
			if (count($lst) > 0)
			{
				foreach ($lst as $cust_id => $is_default)
				{
					// If the customer is assigned to the user we're currently working on, set this one as default,
					// otherwise simply select the smallest ID of the recipient and set that one as default
					if (in_array($cust_id, $assigned_customers)) $default_id = $user_id;
					else
					{
						$q = 'SELECT min(user_id) as user_id FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' ';
						$q.= 'WHERE notif_obj_class='.NOTIF_OBJ_CLASS_CUSTOMER.' AND customer_id='.$cust_id;
						$default_id = DB::db_fetch_field ($q, 'user_id');
					}
					
					$cust_name = DB::db_fetch_field ('SELECT name FROM '.TBL_CUSTOMERS.' WHERE id='.$cust_id, 'name');
					$user  = new User($default_id);
					
					error_msg ('NOTICE: The customer '.$cust_name.' ('.$cust_id.') didn\'t have a default account manager, it was set automatically to '.$user->get_name());
					
					$q = 'UPDATE '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' SET is_default=1 WHERE customer_id='.$cust_id.' AND ';
					$q.= 'user_id='.$user->id.' AND notif_obj_class='.NOTIF_OBJ_CLASS_CUSTOMER;
					DB::db_query ($q);
					
				}
			}
		}
	}
	
	
	/**
	* [Class Method] Set the customer users who will receive notifications for their respective customer
	* @param	integer		$customer_id		The ID of the customer for which the list is being set
	* @param	array		$recipients		Array with the user IDs of the user's who should receive these notifications
	* @param	integer		$default_recipient	The ID of the user who should be the default recipient
	*/
	public static function set_customer_recipients_customers ($customer_id, $recipients = array (), $default_recipient = 0)
	{
		if ($customer_id)
		{
			// First, delete the existing list for this customer and class
			$q = 'DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS.' WHERE customer_id='.$customer_id;
			DB::db_query ($q);

			// Set the new list of recipients - if any were defined
			if (is_array($recipients) and count($recipients)>0)
			{
				// If the default recipient is not specified, take the first one on the list
				if (!$default_recipient or ($default_recipient and !in_array($default_recipient, $recipients))) $default_recipient = $recipients[0];
			
				$q = 'INSERT INTO '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS.' (customer_id, user_id, is_default) VALUES ';
				foreach ($recipients as $user_id)
				{
					$q.= '('.$customer_id.', '.$user_id.', ';
					$q.= ($user_id == $default_recipient ? '1' : '0');
					$q.= '), ';
				}

				$q = preg_replace ('/\,\s*$/', '', $q);
				DB::db_query ($q);
			}
		}
	}
	
	/**
	* [Class Method] Set the Keysource users who will receive notifications of a certain type for a specific customer
	* @param	integer		$customer_id		The ID of the customer for which the list is being set
	* @param	integer		$notif_obj_class	The class of notifications
	* @param	array		$recipients		Array with the user IDs of the user's who should receive these notifications
	* @param	integer		$default_recipient	The ID of the user who should be the default recipient
	*/
	public static function set_customer_recipients ($customer_id, $notif_obj_class, $recipients = array (), $default_recipient = 0)
	{
		if ($customer_id and $notif_obj_class)
		{
			// First, delete the existing list for this customer and class
			$q = 'DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' WHERE ';
			$q.= 'customer_id='.$customer_id.' AND notif_obj_class='.$notif_obj_class;
			db::db_query ($q);
			
			// Set the new list of recipients - if any were defined
			if (is_array($recipients) and count($recipients)>0)
			{
				$q = 'INSERT INTO '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' (customer_id, notif_obj_class, user_id, is_default) VALUES ';
				foreach ($recipients as $user_id)
				{
					$q.= '('.$customer_id.', '.$notif_obj_class.', '.$user_id.', ';
					$q.= ($user_id == $default_recipient ? '1' : '0');
					$q.= '), ';
				}
				
				$q = preg_replace ('/\,\s*$/', '', $q);
				db::db_query ($q);
			}
		}
	}

	
	/**
	* [Class Method] Returns an array with the customer users who should receive notifications for their respective customers.
	* @param	array					$filter		Associative array with filtering criteria. Can contain:
	*									- customer_id: A customer ID for which to return the recipients.
	*									  If set to -1 will return only the customers that don't have
	*									  a specific recipient assigned.
	*									- assigned_user_id: (Only if no customer ID is specified) Returns
	*									  all customers which have the specified user ID assigned as recipient.
	*									- include_any: If true and if a customer ID is specified will return
	*									  the first customer user from that customer if a designated customer
	*									  user recipient was not found.
	* @param	int					$count		(By reference) If defined, will be loaded with the total number of
	*									matches found.
	* @return								Associative array, the keys being customer IDs and the values being
	*									array with the IDs of all matched customers user recipients.
	*/
	public static function get_customer_recipients_customers ($filter = array(), &$count)
	{
		$ret = array ();
		
		if ($filter['customer_id'] > 0)
		{
			// Return recipients for a specific customer
			$ret[$filter['customer_id']] = array ();
			if (isset($count)) $count = 1;
		}
		elseif ($filter['customer_id'] == -1)
		{
			// Return only the customers that don't have an assigned a recipient
			$q = 'FROM '.TBL_CUSTOMERS.' c LEFT OUTER JOIN '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS.' r ';
			$q.= 'ON c.id=r.customer_id WHERE c.active=1 AND r.customer_id IS NULL ORDER BY c.name ';
			
			if (isset($count))
			{
				$count = db::db_fetch_field ('SELECT count(*) cnt '.$q, 'cnt');
			}
			
			if (isset ($filter['start']) and isset ($filter['limit'])) $q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
			$ids = db::db_fetch_array ('SELECT c.id '.$q);
			
			foreach ($ids as $id) $ret[$id->id] = array ();
		}
		else 
		{
			// No customer ID was specified, so fetch a list of customers according to the filtering criteria
			$q = 'SELECT id FROM '.TBL_CUSTOMERS.' c ';
			if ($filter['assigned_user_id'])
			{
				// Check both direct user assignment and group assignment
				$q.= 'INNER JOIN '.TBL_USERS_CUSTOMERS_ASSIGNED.' ac ON c.id=ac.customer_id ';
				$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON ac.user_id=ug.group_id ';
			}
			$q.= 'WHERE ';
			if ($filter['assigned_user_id'])
			{
				$q.= '(ac.user_id='.$filter['assigned_user_id'].' OR ';
				$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$filter['assigned_user_id'].')) AND ';
			}
			
			$q.= 'c.active=1 ORDER BY name ';
			//if (isset ($filter['start']) and isset ($filter['limit'])) $q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
			$ids = db::db_fetch_array ($q);
			
			foreach ($ids as $id) $ret[$id->id] = array ();
			
			if (isset ($count))
			{
				if ($filter['assigned_user_id'])
				{
					$q_count = 'SELECT count(id) as cnt FROM '.TBL_CUSTOMERS.' c INNER JOIN '.TBL_USERS_CUSTOMERS_ASSIGNED.' ac ON c.id=ac.customer_id ';
					$q_count.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON ac.user_id=ug.group_id WHERE ';
					$q_count.= '(ac.user_id='.$filter['assigned_user_id'].' OR ';
					$q_count.= '(ug.group_id IS NOT NULL AND ug.user_id='.$filter['assigned_user_id'].')) AND ';
					$q_count.= 'c.active=1 ';
					$count = db::db_fetch_field ($q_count, 'cnt');
				}
				else $count = db::db_fetch_field ('SELECT count(*) as cnt FROM customers WHERE active=1', 'cnt');
			}
		}
		
		if ($filter['assigned_user_id'])
		{
			// The result should include only notifications for customers to which this user has access,
			// so fetch the list of the assigned customers and remove from $ret customers not on the list
			$assigned_customers = User::get_assigned_customers_list (false, $filter['assigned_user_id']);
			foreach (array_keys($ret) as $cid) if (!isset($assigned_customers[$cid])) unset ($ret[$cid]);
			
			if (isset($count)) $count = count ($ret);
		}
		
		// At this point the $ret array contains the keys for the customers to be included in the current result set
		// Now start populating the array with the actual recipients
		
		if ($filter['customer_id'] != -1)
		{
			// There is no point in doing the searches if $filter['customer_id'] = -1,
			// because those customers don't have any recipients defined
			foreach (array_keys ($ret) as $customer_id)
			{
				$q = 'SELECT distinct r.* FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS.' r ';
				$q.= 'INNER JOIN '.TBL_USERS.' u ON r.user_id = u.id ';
				$q.= 'INNER JOIN '.TBL_USERS_CUSTOMERS.' uc ON u.id = uc.user_id ';
				$q.= 'WHERE r.customer_id='.$customer_id.' ORDER BY u.fname, u.lname ';
				
				//debug($q);
				$recips = db::db_fetch_array ($q);
				foreach ($recips as $recip)
				{
					$ret[$customer_id][] = $recip->user_id;
				}
			}
		}
		
		if ($filter['include_any'] and $filter['customer_id']>0 and count($ret[$filter['customer_id']])==0)
		{
			// If a customer ID was specified and no designated recipient user was found, see if there is at ,
			// least a user for that customer and return that as recipient.
			$q = 'SELECT distict id FROM '.TBL_USERS.' u inner join '.TBL_USERS_CUSTOMERS.' uc on u.id=uc.user_id WHERE uc.customer_id='.$filter['customer_id'].' AND type='.USER_TYPE_CUSTOMER.' ORDER BY id LIMIT 1';
			$id = DB::db_fetch_field ($q, 'id');
			if ($id) $ret[$customer_id] = array($id);
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns an array with the notifications recipients for a specific customers or for all customers.
	* These recipients are Keysource users that have been designated to receive notifications for specific customers.
	* If no specific Keysource recipients have been assigned to a customer, then the result will be empty, and the
	* calling notification function should use the generic Keysource recipients.
	* @param	array		$filter		Associative array with fitering criteria. Can contain:
	*						- customer_id : the result will contain only matches for this customer ID. If set to -1,
	*						  will return only customers that don't have an assigned recipient.
	*						- recipient_id : the result will contain only the customer for which this user is recipient
	*						- start, limit: used to limit the number of returned results; $count will still return the total number
	* @param	integer		$count		(By reference) The total number of matching results (customers) the match the criteria
	* @return	array				Associative array with the results. If the filter criteria specifies a customer ID,
	*						the array will contain the information only for that customer, otherwise it will 
	*						contain the results for all customers. The array keys are the customer ID(s). The
	*						values are associative arrays, with the keys being notification classes and the
	*						values being arrays with the associated recipients user IDs
	*/
	public static function get_customer_recipients ($filter = array(), &$count)
	{
		$ret = array ();

		if ($filter['customer_id'] > 0)
		{
			// Return the recipients for a specific user
			$ret[$filter['customer_id']] = array();
			if (isset($count)) $count = 1;
		}
		elseif ($filter['customer_id'] == -1)
		{
			// Return only the customers that don't have assigned a recipient
			$q = 'FROM '.TBL_CUSTOMERS.' c LEFT OUTER JOIN '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' r ';
			$q.= 'ON c.id=r.customer_id WHERE c.active=1 AND r.customer_id IS NULL ORDER BY c.name ';
			$ids = db::db_fetch_array ('SELECT c.id '.$q);
			
			foreach ($ids as $id) $ret[$id->id] = array ();
			
			if (isset($count))
			{
				$count = db::db_fetch_field ('SELECT count(*) cnt '.$q, 'cnt');
			}
		}
		elseif (isset ($filter['recipient_id']))
		{
			// A user ID has been specified, so fetch only the customer IDs assigned to this user,
			// either directly or through a group
			$q = 'SELECT r.customer_id FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' ';
			$q.= 'LEFT JOIN '.TBL_USERS_GROUPS.' g ON r.user_id = g.id ';
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' c ON r.customer_id=c.id ';
			$q.= 'WHERE (r.user_id='.$filter['recipient_id'].' OR g.user_id='.$filter['recipient_id'].') ';
			$q.= 'ORDER BY ';
			// xxxxxxxxxxxxxxxxxxx - not implemented yet, not sure if it will be needed
		}
		else 
		{
			// No customer ID was specified, so fetch a list of customers according to the filtering criteria
			$q = 'SELECT id FROM '.TBL_CUSTOMERS.' c WHERE c.active=1 ORDER BY name ';
			if (isset ($filter['start']) and isset ($filter['limit'])) $q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
			$ids = db::db_fetch_array ($q);
			
			foreach ($ids as $id) $ret[$id->id] = array ();
			
			if (isset ($count))
			{
				$count = db::db_fetch_field ('SELECT count(*) as cnt FROM customers WHERE active=1', 'cnt');
			}
		}
		
		if ($filter['assigned_user_id'])
		{
			// The result should include only notifications for customers to which this user has access,
			// so fetch the list of the assigned customers and remove from $ret customers not on the list
			$assigned_customers = User::get_assigned_customers_list (false, $filter['assigned_user_id']);
			foreach (array_keys($ret) as $cid) if (!isset($assigned_customers[$cid])) unset ($ret[$cid]);
			
			if (isset($count)) $count = count ($ret);
		}
		
		// At this point the $ret array contains the keys for the customers to be included in the current result set
		// Now start populating the array with the actual recipients
		
		if ($filter['customer_id'] != -1)
		{
			// There is no point in doing the searches if $filter['customer_id'] = -1,
			// because those customers don't have any recipients defined
			foreach (array_keys ($ret) as $customer_id)
			{
				// Initialize first all the notification classes, in case 
				// the user doesn't have specific recipients assigned for a specific class
				foreach (array_keys($GLOBALS['NOTIF_OBJ_CLASSES']) as $class) $ret[$customer_id][$class] = array();
			
				$q = 'SELECT r.* FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' r ';
				$q.= 'INNER JOIN '.TBL_USERS.' u ON r.user_id = u.id ';
				$q.= 'WHERE r.customer_id='.$customer_id.' AND ';
				//if (isset ($filter['notif_obj_class'])) $q.= 'notif_obj_class&'.$filter['notif_obj_class'].'='.$filter['notif_obj_class'].' AND ';
				if (isset ($filter['notif_obj_class'])) $q.= 'notif_obj_class='.$filter['notif_obj_class'].' AND ';
				
				$q = preg_replace ('/AND\s*$/', ' ', $q);
				$q.= 'ORDER BY u.fname, u.lname ';
				
				$recips = db::db_fetch_array ($q);
				foreach ($recips as $recip)
				{
					$ret[$customer_id][$recip->notif_obj_class][] = $recip->user_id;
				}
			}
		}
		
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns an array with the customer users marked as default notification recipients
	* @param	integer		$customer_id	(Optional) To return the default recipients only for the specified customer
	* @return	array				Associative array, with the keys being the customer IDs and the values
	*						being default recipient IDs (if defaults were set)
	*/
	public static function get_customer_default_recipients_customers ($customer_id = 0)
	{
		$ret = array ();
		
		$q = 'SELECT customer_id, user_id FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS.' ';
		$q.= 'WHERE is_default = 1 ';
		if ($customer_id) $q.= 'AND customer_id='.$customer_id;
		
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns an array with all the types of notifications and their default Keysource recipients
	* @param	integer		$customer_id	(Optional) To return the default recipients only for the specified customer
	* @return	array				Associative array, with the keys being the customer IDs and the values
	*						being associative arrays with notification classes as keys and default 
	*						recipient IDs as values
	*/
	public static function get_customer_default_recipients ($customer_id = 0)
	{
		$ret = array ();
		
		$q = 'SELECT customer_id, notif_obj_class, user_id FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' ';
		$q.= 'WHERE is_default = 1 ';
		
		if ($customer_id) $q.= 'AND customer_id='.$customer_id;
		
		$recips = db::db_fetch_array ($q);
		
		foreach ($recips as $recip) 
		{
			$ret[$recip->customer_id][$recip->notif_obj_class] = $recip->user_id;
		}
		
		return $ret;
	}
	
	/** 
	* [Class Method] Sets the recipients for a class of notifications
	* @param	int	$class_id		The ID of the notifications class
	* @param	array	$recipients		Array of user IDs which are recipients for this class
	* @param	int	$default_recipient	The ID of the user who is the main recipient
	*/
	public static function set_recipients ($class_id, $recipients = array(), $default_recipient = 0)
	{
		if ($class_id and is_array($recipients))
		{
			$q = 'DELETE FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' ';
			$q.= 'WHERE notif_obj_class='.$class_id;
			db::db_query ($q);
			
			if (count($recipients)>0)
			{
				// Set the list of recipients
				$q = 'INSERT INTO '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' (notif_obj_class, user_id, is_default) VALUES ';
				foreach ($recipients as $user_id)
				{
					$q.= '('.$class_id.', '.$user_id.', ';
					$q.= ($user_id == $default_recipient ? '1' : '0');
					$q.= '), ';
				}
				$q = preg_replace ('/\,\s*$/', '', $q);
				db::db_query ($q);
			}
		}
	}
	
	
	/** [Class Method] Ensures that all notification recipient users exist and are active. Those which are not active anymore are removed */
	public static function check_active_users ()
	{
	
		// Check generic notification recipients
		$q = 'SELECT r.* FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' r LEFT OUTER JOIN '.TBL_USERS.' u ON r.user_id=u.id ';
		$q.= 'WHERE (u.id IS NULL or u.active=0)';
		$recips = db::db_fetch_array ($q);
		foreach ($recips as $recip)
		{
			$q = 'DELETE FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' WHERE ';
			$q.= 'notif_obj_class='.$recip->notif_obj_class.' AND user_id='.$recip->user_id;
			db::db_query($q);
		}
		
		// Check customer-specific notification recipients (KS users)
		$q = 'SELECT r.* FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' r LEFT OUTER JOIN '.TBL_USERS.' u ON r.user_id=u.id ';
		$q.= 'WHERE (u.id IS NULL or u.active=0)';
		$recips = db::db_fetch_array ($q);
		foreach ($recips as $recip)
		{
			$q = 'DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS.' WHERE user_id='.$recip->user_id;
			DB::db_query($q);
		}
		
		// Check customer-specific notification recipients (customers users)
		$q = 'SELECT r.* FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS.' r LEFT OUTER JOIN '.TBL_USERS.' u ON r.user_id=u.id ';
		$q.= 'WHERE (u.id IS NULL or u.active=0)';
		$recips = db::db_fetch_array ($q);
		foreach ($recips as $recip)
		{
			$q = 'DELETE FROM '.TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS.' WHERE user_id='.$recip->user_id;
			DB::db_query($q);
		}
		
		// Check escalation recipients
		$q = 'SELECT r.* FROM '.TBL_TICKETS_ESCALATION_RECIPIENTS.' r LEFT OUTER JOIN '.TBL_USERS.' u ON r.user_id=u.id ';
		$q.= 'WHERE (u.id IS NULL or u.active=0)';
		$recips = db::db_fetch_array ($q);
		foreach ($recips as $recip)
		{
			$q = 'DELETE FROM '.TBL_TICKETS_ESCALATION_RECIPIENTS.' WHERE user_id='.$recip->user_id;
			db::db_query($q);
		}
		
		// Check default tickets CC recipients
		$q = 'SELECT r.* FROM '.TBL_CUSTOMERS_CC_RECIPIENTS.' r LEFT OUTER JOIN '.TBL_USERS.' u ON r.user_id=u.id ';
		$q.= 'WHERE (u.id IS NULL or u.active=0)';
		$recips = db::db_fetch_array ($q);
		foreach ($recips as $recip)
		{
			$q = 'DELETE FROM '.TBL_CUSTOMERS_CC_RECIPIENTS.' WHERE user_id='.$recip->user_id;
			db::db_query($q);
		}
	}
}

?>
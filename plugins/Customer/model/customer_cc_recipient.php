<?php

/**
* Class for manipulating the default CC recipients for customers tickets
*
* The default CC recipients are users (either engineers or customers users)
* which are always added by default to the newly created tickets.
* This class has only class methods, operating mainly with users lists.
*/

class CustomerCCRecipient
{
	/** [Class Method] Returns the list of CC recipients for a customer 
	* @param	int			$customer_id		The ID of the customer
	* @return	array(User)					Array with the users which have been set
	*								as default CC recipients for this customer,
	*								ordered by user name.
	*/
	public static function get_cc_recipients ($customer_id)
	{
		$ret = array ();
		if ($customer_id)
		{
			$q = 'SELECT r.user_id FROM '.TBL_CUSTOMERS_CC_RECIPIENTS.' r INNER JOIN '.TBL_USERS.' u ';
			$q.= 'ON r.user_id=u.id WHERE r.customer_id='.$customer_id.' ';
			$q.= 'ORDER BY u.customer_id DESC, u.fname, u.lname ';
			$ids = db::db_fetch_vector ($q);
			
			foreach ($ids as $id) $ret[] = new User ($id);
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns the list with the IDs of CC recipients for a customer 
	* @param	int			$customer_id		The ID of the customer
	* @return	array						Array with the IDs of the users which have been set
	*								as default CC recipients for this customer,
	*								ordered by user name.
	*/
	public static function get_cc_recipients_ids($customer_id)
	{
		$ret = array ();
		if ($customer_id)
		{
			$q = 'SELECT r.user_id FROM '.TBL_CUSTOMERS_CC_RECIPIENTS.' r INNER JOIN '.TBL_USERS.' u ';
			$q.= 'ON r.user_id=u.id WHERE r.customer_id='.$customer_id.' ';
			$q.= 'ORDER BY u.customer_id DESC, u.fname, u.lname ';
			$ret = db::db_fetch_vector ($q);
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Sets the list of CC recipients for a customer
	* @param	int			$customer		The ID of the customer
	* @param	array			$user_ids		Array with the IDs of the users who need to be CC recipients for the customer
	*/
	public static function set_cc_recipients($customer_id, $user_ids = array ())
	{
		if ($customer_id and is_array($user_ids))
		{
			// First, delete the existing settings
			DB::db_query ('DELETE FROM '.TBL_CUSTOMERS_CC_RECIPIENTS.' WHERE customer_id='.$customer_id);
			
			// Now set the new recipients
			if (count($user_ids) > 0)
			{
				$q = 'INSERT INTO '.TBL_CUSTOMERS_CC_RECIPIENTS.' (customer_id, user_id) VALUES ';
				foreach ($user_ids as $user_id) $q.= '('.$customer_id.','.$user_id.'),';
				$q = preg_replace ('/\,\s*$/', '', $q);
				db::db_query ($q);
			}
		}
	}
	
	
	/** [Class Method] Returns all the customers that have default recipients set
	* @return	array						Associative array, the keys being customer IDs and
	*								the values being arrays of User objects with the 
	*								CC recipients for each customer
	*/
	public static function get_all_cc_recipients()
	{
		$ret = array ();
		
		$q = 'SELECT r.customer_id, r.user_id FROM '.TBL_CUSTOMERS_CC_RECIPIENTS.' r INNER JOIN '.TBL_CUSTOMERS.' c ';		
		$q.= 'ON r.customer_id=c.id INNER JOIN '.TBL_USERS.' u ON r.user_id=u.id ';

        $current_user = $GLOBALS['CURRENT_USER'];
		if($current_user)
		{
			if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
			{
				$cc = $current_user->get_assigned_customers_list();
				$q.= ' where c.id in (';
				$i=0;
				foreach($cc as $k=>$name)
				{
					if($i!=count($cc)-1) $q.=$k.", ";
					else $q.=$k;
				}
				$q = trim (preg_replace ('/,\s*$/', '', $q));
				$q.=") AND ";
			}
		}
		
		$q = preg_replace ('/WHERE\s*AND/', 'WHERE ', $q);
		$q = preg_replace ('/WHERE\s*$/', '', $q);
		$q = preg_replace ('/AND\s*$/', '', $q);
		
		
		$q.= 'ORDER BY c.name, u.customer_id DESC, u.fname, u.lname';
		$data = DB::db_fetch_array ($q);
		
		foreach ($data as $d) $ret[$d->customer_id][] = new User ($d->user_id);
		
		return $ret;
	}
	
	/**
	 * [Class Method] Returns all the customers that don't have at least one default recipient set
	 * @param array List of the allowd customers obtained from Customer::get_cusomers_list
	 * @return array	Associative arrays with the keys being the customer_id and the values the customer names
	 */
	public static function get_all_customers_without_cc($list)
	{
		$i = 0;
		foreach($list as $cust)
		{ 
			$keys = array_keys($list, $cust);
			if($keys[0]!=" ")
			{
				$query = "SELECT count(customer_id) as cnt from ".TBL_CUSTOMERS_CC_RECIPIENTS." where customer_id = ".$keys[0];
				$res =  Db::db_fetch_field($query, 'cnt');
				if($res == 0)
				{	
					$ret[$i]['id'] = $keys[0];
					$ret[$i]['name'] = $cust;
					$i++;
				}
			}
		}
		return $ret;
	}
}

?>
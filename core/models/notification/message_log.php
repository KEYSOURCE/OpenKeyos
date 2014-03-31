<?php

/**
* Class for representing e-mail messages (related to notifications) sent to customers
* users.
*
* These are stored in monthly logs tables (with the prefix TBL_MESSAGES_LOG_) and are
* automatically created from the Notification class when an e-mail for a notification
* is sent to a customer user.
*
*/

class MessageLog extends Base
{
	/** The unique ID of the log item
	* @var int */
	var $id = null;
	
	/** The month (in the form YYYY_MM) when the message was sent.
	* This is very important for determining the database table
	* storing the log item
	* @var string */
	var $month = '';
	
	/** The ID of the notification for which this message was sent
	* @var int */
	var $notification_id = 0;
	
	/** The date when the message was sent
	* @var timestamp */
	var $date_sent = 0;
	
	/** The ID of the user to whom the message was sent
	* @var int */
	var $user_id = 0;
	
	/** The ID of the customer to whom the user belongs - for convenience
	* @var int */
	var $customer_id = 0;
	
	/** The e-mail address of the user - for convenience (and also in case the e-mail is changed later
	* in the user account)
	* @var string */
	var $email = '';
	
	/** The subject of the message
	* @var string */
	var $subject = '';
	
	/** The body of the message
	* @var text */
	var $msg_body = '';
	
	
	/** The table storing the message log item - will be initialized in constructor based on the 
	* message's month 
	* @var string */
	var $table = '';
	
	/** The name of fields to use when loading/saving object
	* @var array */
	var $fields = array ('id', 'notification_id', 'date_sent', 'user_id', 'customer_id', 'email', 'subject', 'msg_body');
	
	
	/** Constructor, also loads an object's data if an ID and a month is provided
	* @param	int			$id		The object's ID
	* @param	string			$month		The month when the message was sent, in the form YYYY_MM
	*/
	function MessageLog ($id = null, $month = '')
	{
		if ($id and $month and preg_match('/^2[0-9]{3}_[0-9]{2}$/', $month))
		{
			$this->id = $id;
			$this->month = $month;
			$this->table = TBL_MESSAGES_LOG.'_'.$month;
			parent::load_data ();
		}
	}
	
	
	/** [Class Method] Returns log messages according to some filtering criteria.
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- month: (Mandatory) The log month from which to get messages, 
	*							  in YYYY_MM format
	*							- customer_id: A specific customer for whom to get messages
	*							- start, limit: Paging options
	* @param	int			$count		(By Reference) If defined, will be loaded with the total number
	*							of matched messages logs
	* @return	array(MessageLog)			Array with the matched MessageLog objects, ordered by date sent
	*							descending
	*/
	function get_messages_log ($filter, &$count)
	{
		$ret = array ();
		
		if (!$filter['month']) $filter['month'] = date ('Y_m');
		
		$q = 'FROM '.TBL_MESSAGES_LOG.'_'.$filter['month'].' ml ';
		
		if($this->current_user)
		{
			if($this->current_user->is_customer_user() and $this->current_user->administrator and $this->current_user->type==USER_TYPE_CUSTOMER)
			{
				$cc = $this->current_user->get_assigned_customers_list();
				$q.= ' INNER JOIN '.TBL_CUSTOMERS.' c on ml.customer_id=c.id where c.id in (';
				$i=0;
				foreach($cc as $k=>$name)
				{
					if($i!=count($cc)-1) $q.=$k.", ";
					else $q.=$k;
				}
				$q = trim (preg_replace ('/,\s*$/', '', $q));
				$q.=") AND ";
			}
			else {
				$q.=' WHERE ';
			}
		}
		else {
			$q.=' WHERE ';
		}
		
		if ($filter['customer_id']) $q.= 'customer_id='.$filter['customer_id'].' AND ';
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		
		if (isset($count))
		{
			$q_count = 'SELECT count(*) as cnt '.$q;
			$count = DB::db_fetch_field ($q_count, 'cnt');
		}
		
		$q = 'SELECT ml.id '.$q.' ORDER BY date_sent DESC, id DESC ';
		if (isset($filter['start']) and $filter['limit']) $q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new MessageLog ($id, $filter['month']);
		
		return $ret;
	}
	
	/** [Class Method] Returns the months for which there are logged messages */
	function get_messages_log_months ()
	{
		$ret = array ();
		$q = 'SHOW TABLES like "'.TBL_MESSAGES_LOG.'_2%" ';
		$months = DB::db_fetch_vector ($q);
		$this_month = date('Y_m');
		foreach ($months as $month)
		{
			$month = preg_replace ('/^'.TBL_MESSAGES_LOG.'_/', '', $month);
			// Make sure we don't return months from the future
			if ($month <= $this_month) $ret[$month] = $month;
		}
		arsort($ret);
		
		return $ret;
	}
	
	/** [Class Method] Checks if the specified table for storing customer users messages (notifications)
	* exists and, if not, creates it
	* @param	string		$log_table		The name of the table to check/create
	*/
	function check_exists_messages_log_table ($log_table)
	{
		if ($log_table and preg_match('/^'.TBL_MESSAGES_LOG.'_/', $log_table))
		{
			$q = 'SHOW TABLES like "'.$log_table.'" ';
			$tbls = DB::db_fetch_vector ($q);
			
			if (count($tbls) == 0)
			{
				$q = 'CREATE TABLE '.$log_table.' (id int not null auto_increment, notification_id int not null, ';
				$q.= 'date_sent int not null, user_id int not null, customer_id int not null, email varchar(100) not null, ';
				$q.= 'subject varchar(255), msg_body text not null, ';
				$q.= 'primary key(id), key(notification_id), key(date_sent), key(user_id), key(customer_id), ';
				$q.= 'key(email), key(subject)) ';
				DB::db_query ($q);
			}
		}
	}
	
}

?>
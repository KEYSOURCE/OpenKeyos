<?php

class_load ('Computer');

/**
* Class for managing the monitoring of customer IP addresses (Internet
* connections)
* 
*/


class MonitoredIP extends Base
{
	/** The object ID
	* @var int */
	var $id = null;
	
	/** The ID of the customer to which this IP belongs
	* @var int */
	var $customer_id = null;
	
	/** The ID of the associated Internet contract - if any
	* @var int */
	var $internet_contract_id = 0;
	
	/** The date when the monitoring started
	* @var timestamp */
	var $created = 0;
	
	/** The ID of the user who created the object
	* @var int */
	var $user_id = null;
	
	/** The IP address which is being monitored - it is one of the
	* addresses listed in the "remote_ip" fields in the computers table.
	* @var string */
	var $remote_ip = '';
	
	/** The target IP to be used in ping and traceroute. It doesn't have to
	* be the same as 'remote_ip', e.g. in case the remote_ip is actually
	* behind a firewall or not pingable. This 'target_ip' should be the last
	* reachable IP from Internet when running a traceroute for 'remote_ip'
	* @var string */
	var $target_ip = '';
	
	/** Specifies if monitoring of this IP is disabled or not
	* @var boolean */
	var $disabled = true;
	
	/** The current status of the connection for this IP - see $GLOBALS['MONITOR_STATS']
	* @var int */
	var $status = MONITOR_STAT_UNKNOWN;
	
	/** True or False if the target responded OK to ping
	* @var bool */
	var $ping_ok = false;
	
	/** Signals if this IP is currently being processed. Contains the timestamp when the
	* processing started or 0 if it is not being processed
	* @var timestamp */
	var $processing = 0;
	
	/** When was the last ping ran
	* @var timestamp */
	var $last_ping_test = 0;
	
	/** When was the last traceroute ran
	* @var timestamp */
	var $last_traceroute_test = 0;
	
	/** The last ping results
	* @var text */
	var $last_ping = '';
	
	/** The last traceroute results
	* @var text */
	var $last_traceroute = '';
	
	/** Comments
	* @var text */
	var $comments = '';
	
	/** The duration of the last test, in second
	* @var int */
	var $last_test_duration = 0;
	

	/** The associated customer object. Note that this is loaded only on request, with load_customer()
	* @var Customer */
	var $customer = null;
	
	/** The user who created this object. It is loaded only on request with load_user ()
	* @var User */
	var $user = null;
	
	/** The CustomerInternetContract object associated with this IP, if any. Loaded only on request with load_contract ()
	* @var CustomerInternetContract */
	var $internet_contract = null;
	
	/** The Notification object associated with this connection, if it is down. Loaded only on request with load_notification ()
	* @var Notification */
	var $notification = null;
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_MONITORED_IPS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'customer_id', 'internet_contract_id', 'created', 'user_id', 'remote_ip', 'target_ip', 'disabled', 'status', 'ping_ok', 'processing', 'last_ping_test', 'last_traceroute_test', 'last_ping', 'last_traceroute', 'comments', 'last_test_duration');
	
	
	/** Contructor. Also loads an object's data if an ID is specified*/
	function MonitoredIP ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Load the associated customer */
	function load_customer ()
	{
		if ($this->customer_id) $this->customer = new Customer ($this->customer_id);
	}
	
	
	/** Load the User who created this */
	function load_user ()
	{
		if ($this->user_id) $this->user = new User ($this->user_id);
	}
	
	
	/** Load the associated CustomerInternetContract object, if any */
	function load_contract ()
	{
		class_load ('CustomerInternetContract');
		if ($this->internet_contract_id)
		{
			$this->internet_contract = new CustomerInternetContract ($this->internet_contract_id);
			$this->internet_contract->load_details ();
		}
	}
	
	
	/** Load the associated notification object, if any */
	function load_notification ()
	{
		if ($this->id)
		{
			class_load ('Notification');
			$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_INTERNET.' AND object_id='.$this->id;
			$id = $this->db_fetch_field ($q, 'id');
			$this->notification = new Notification ($id);
		}
	}
	
	
	/** Check if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->customer_id) {error_msg('Please specify the customer.'); $ret = false;}
		if (!$this->remote_ip) {error_msg('Please specify the remote IP.'); $ret = false;}
		if (!$this->target_ip) {error_msg('Please specify the target IP.'); $ret = false;}
		
		// Make sure the target IP is unique
		if ($this->target_ip)
		{
			$q = 'SELECT id FROM '.TBL_MONITORED_IPS.' WHERE target_ip="'.db::db_escape($this->target_ip).'" ';
			if ($this->id) $q.= 'AND id<>'.$this->id;
			$q.= ' LIMIT 1 ';
			if ($this->db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('This target IP is already being monitored.'); 
			}
		}
		
		return $ret;
	}
	
	
	/** Returns all computers which are reporting through this remote IP 
	* @return	array					Associative array with the computers using this remote IP,
	*							the keys being computer IDs and the values being computers
	*							names.
	*/
	function get_computers ()
	{
		$ret = array ();
		class_load ('Computer');
		if ($this->remote_ip)
		{
			$q = 'SELECT id, netbios_name FROM '.TBL_COMPUTERS.' WHERE remote_ip="'.db::db_escape($this->remote_ip).'" ORDER BY netbios_name ';
			$ret = $this->db_fetch_list ($q);
		}
		
		return $ret;
	}
	
	
	/** Marks that processing is starting for this IP */
	function mark_processing_start ()
	{
		if ($this->id)
		{
			$this->processing = time ();
			$this->save_data ();
		}
	}
	
	
	/** Marks that processing has finished for this IP */
	function mark_processing_finished ()
	{
		if ($this->id)
		{
			$this->processing = 0;
			$this->save_data ();
		}
	}
	
	
	/** Return True or False if the connection is down */
	function is_down ()
	{
		return ($this->status == MONITOR_STAT_ERROR);
	}
	
	/** Performs the test and save the results. This is usually run from a crontab task. First
	* a ping test is perfomed. If failed, a traceroute is run too. If this fails too, then the
	* connection is marked as broken. A traceroute is also run if more than INTERVAL_TRACEROUTE_TESTS
	* have passed.
	*/
	function run_test ()
	{
		if ($this->id and $this->target_ip)
		{
			$test_start = time ();
			// Do a ping test first
			$command = '/bin/ping -c '.PING_TEST_PACKETS.' -i 0.2 -q '.$this->target_ip.' 2>&1 ';
			$last_ping_test = time ();
			$last_ping = '';
			$last_traceroute_test = $this->last_traceroute_test;
			$last_traceroute = $this->last_traceroute;
			$processing = $this->processing;
			$h = popen ($command, 'r');
			if ($h)
			{
				while ($s = fread($h, 1024)) $last_ping.= $s;
				pclose ($h);
				// Check if the result is valid
				if (preg_match('/[0-9]+\% packet loss/', $last_ping)) $ping_ok = preg_match ('/ 0\% packet loss/', $last_ping);
				else $ping_ok = false;
				$last_ping_test = time ();
			}
			else
			{
				$last_ping = '[Failed running ping command: '.$command.']';
				$ping_ok = false;
			}
			
			// Run a traceroute if needed
			if (!$ping_ok or (time()-$this->last_traceroute_test) > INTERVAL_TRACEROUTE_TESTS)
			{
				$command = '/bin/traceroute -w 2 -m 20 -q 2 '.$this->target_ip.' 2>&1';
				$target_reached = false;
				$last_traceroute_test = time ();
				$last_traceroute = '';
				$h = popen ($command, 'r');
				if ($h)
				{
					while ($s = fread($h, 1024)) $last_traceroute.= $s;
					$test_ip = preg_replace ('/\./', '\.', $this->target_ip);
					$target_reached = preg_match ('/\n\s*[0-9]+.*'.$test_ip.'/', $last_traceroute);
					pclose ($h);
				}
				else $last_traceroute = '[Failed running traceroute command: '.$command.']';
				$last_traceroute_test = time ();
				
				// Even if traceroute has not reached the target, if ping was OK then the connection is OK too.
				if ($target_reached or (!$target_reached and $ping_ok)) $status = MONITOR_STAT_OK;
				else $status = MONITOR_STAT_ERROR;
			}
			else $status = MONITOR_STAT_OK;
			
			
			
			// In order not to interfere with data edited in the Web interface, reload the object data
			// and only set here the values which are specific to monitoring
			$this->load_data ();
			$this->processing = $processing;
			$this->status = $status;
			$this->last_ping_test = $last_ping_test;
			$this->last_ping = $last_ping;
			$this->ping_ok = $ping_ok; 
			$this->last_traceroute_test = $last_traceroute_test;
			$this->last_traceroute = $last_traceroute;
			$this->last_test_duration = max (1, (time() - $test_start));
			
			// Finally, signal that processing has finished - this also saves the object data
			$this->mark_processing_finished ();
			
			// If the connection is down, raise the needed notifications
			if ($this->is_down())
			{
				// Raise the notifications about connections down
				class_load ('InfoRecipients');
				$recipients = InfoRecipients::get_customer_recipients (
					array ('customer_id' => $this->customer_id, 'notif_obj_class' => NOTIF_OBJ_CLASS_INTERNET), $no_total
				);
				if (count($recipients[$this->customer_id][NOTIF_OBJ_CLASS_INTERNET])==0)
				{
					$recipients = InfoRecipients::get_all_type_recipients ();
					$recipients = $recipients[NOTIF_OBJ_CLASS_INTERNET];
				}
				else $recipients = $recipients[$this->customer_id][NOTIF_OBJ_CLASS_INTERNET];
				
				$notif_id = Notification::raise_notification_array (array(
					'event_code' => NOTIF_CODE_INTERNET_DOWN,
					'level' => ALERT_CRITICAL,
					'object_class' => NOTIF_OBJ_CLASS_INTERNET,
					'object_id' => $this->id,
					'object_event_code' => 0,
					'item_id' => 0,
					'user_ids' => $recipients,
					'text' => '',
					'no_increment' => true,
				));
				
				// Flag all affected computers
				$computers_list = $this->get_computers ();
				foreach ($computers_list as $comp_id=>$comp_name)
				{
					$q = 'UPDATE '.TBL_COMPUTERS.' SET internet_down='.$this->id.' WHERE id='.$comp_id;
					$this->db_query ($q);
				}
			}
			else
			{
				// Since the connection is up, make sure to delete all associated notifications
				$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_INTERNET.' AND object_id='.$this->id;
				$ids = $this->db_fetch_vector ($q);
				foreach ($ids as $id)
				{
					$notification = new Notification ($id);
					$notification->delete ();
				}
				
				// Also, remove any flags that might have been in the computers table
				$q = 'UPDATE '.TBL_COMPUTERS.' SET internet_down=0 WHERE internet_down='.$this->id;
				$this->db_query ($q);
			}
		}
	}
	
	
	/** [Class Method] Usually called from crontab, it will return the IDs of the MonitoredIP objects
	* which should be verified */
	public static function get_ids_to_check ()
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_MONITORED_IPS.' WHERE disabled=0 AND ';
		$q.= '(processing=0 OR (processing>0 AND processing<'.(time()-INTERVAL_TESTS_TIMEOUT).')) AND ';
		$q.= '(last_ping_test<='.(time()-INTERVAL_PING_TESTS).' OR last_traceroute_test<='.(time()-INTERVAL_TRACEROUTE_TESTS).') ';
		$q.= 'ORDER BY last_traceroute_test ';
		
		
		$ret = DB::db_fetch_vector ($q);
		
		return $ret;
	}
	
	
	/** [Class Method] Return the MonitoredIP object associated with a specified remote IP
	* @param	string			$remote_ip	The IP address to look for
	* @return	MonitoredIP				The found object, or NULL if none was found
	*/
	public static function get_by_remote_ip ($remote_ip)
	{
		$ret = null;
		if ($remote_ip)
		{
			$q = 'SELECT id FROM '.TBL_MONITORED_IPS.' WHERE remote_ip="'.db::db_escape($remote_ip).'" ';
			$id = DB::db_fetch_field ($q, 'id');
			if ($id) $ret = new MonitoredIP ($id);
		}
		return $ret;
	}
	
	
	/** [Class Method] Returns an array with the reported remote IPs for customers, sorted
	* by customer name.
	* @param	int			$customer_id	(Optional) Return IPs only for this customer ID
	* @return	array					Array of generic objects, with the following
	*							fields: remote_ip, customer_id, customer_name, computers_count
	*/
	public static function get_customers_remote_ips ($customer_id = null)
	{
		$ret = array ();
		
		$q = 'SELECT c.remote_ip, c.customer_id, cu.name as customer_name, count(c.id) as computers_count ';
		$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_CUSTOMERS.' cu ON c.customer_id=cu.id ';
		if ($customer_id) $q.= 'WHERE c.customer_id='.db::db_escape($customer_id).' ';
		$q.= 'GROUP BY c.remote_ip ORDER BY cu.name, c.remote_ip ';
		
		$ret = db::db_fetch_array ($q);
		
		return $ret;
	}
	
	
	/** [Class Method] Returns a list with the monitored IP
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- customer_id: return only objects for a specified customer
	* @return	array					Associative array, keys being object IDs and the values
	*							being the monitored IP/target IP
	*/
	public static function get_monitored_ips_list ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT m.id, concat(m.remote_ip," / ",m.target_ip) FROM '.TBL_MONITORED_IPS.' m INNER JOIN '.TBL_CUSTOMERS.' c ON m.customer_id=c.id WHERE ';
		if ($filter['customer_id']) $q.= 'm.customer_id='.$filter['customer_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY c.name, m.remote_ip ';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/** [Class Method] Get monitored IPs defined in the system, according to some criteria.
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- customer_id: return only objects for a specified customer
	*							- status: return only objects with the specified status
	* @return	array(MonitoredIP)			Array with the matched objects
	*/
	public static function get_monitored_ips ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT m.id FROM '.TBL_MONITORED_IPS.' m INNER JOIN '.TBL_CUSTOMERS.' c ON m.customer_id=c.id WHERE ';

        $current_user = $GLOBALS['CURRENT_USER'];

		if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $current_user->get_assigned_customers_list();
			$q.= 'm.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$q.=") AND ";
		}
		
		if ($filter['customer_id']) $q.= 'm.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['status']) $q.= 'm.status='.$filter['status'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY c.name, m.remote_ip ';
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new MonitoredIP ($id);
		
		return $ret;
	}
}

?>
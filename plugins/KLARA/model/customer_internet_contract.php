<?php

/**
* Stores and manages information about contracts between customers and Internet providers.
*
* If a notification interval is defined and the contract goes into that interval,
* then a notification will be raised for it. If the contract is not managed by 
* Keysource, then the notification is sent to the customer as well.
* 
*/

class_load ('Provider');
class_load ('ProviderContract');
class_load ('Customer');
class_load ('CustomerInternetContractAttachment');
class_load ('Notification');

/** The default number of days before expiration to set for repeating the notification */
define ('EXP_CONTRACT_DEFAULT_FINAL_DAYS', 15);

class CustomerInternetContract extends Base
{
	/** The ID of the contract
	* @var int */
	var $id = null;
	
	/** The customer ID
	* @var int */
	var $customer_id = null;
	
	/** The ID of the provider contract
	* @var int */
	var $contract_id = null;
	
	/** The start date of the contract
	* @var time */
	var $start_date = 0;
	
	/** The end date of the contract
	* @var time */
	var $end_date = 0;
	
	/** True if the contract has been closed
	* @var bool */
	var $is_closed = false;
	
	/** Client number
	* @var string */
	var $client_number = '';
	
	/** ADSL line number 
	* @var string */
	var $adsl_line_number = '';
	
	/** The line type - see $GLOBALS['LINE_TYPES']
	* @var string  */
	var $line_type = null;

	var $ip_address = '';
	
	var $lan_ip = '';
	
	var $netmask = '';
		
	/** The range of IP LAN addresses
	* @var string */
	var $ip_range = ''; 
	
	/** Is a router connected
	* @var bool */
	var $has_router = false;
	
	/** Has SMTP feed 
	* @var bool */
	var $has_smtp_feed = false;
	
	/** Contract or login
	* @var string */
	var $contract_or_login = '';
	
	/** Password
	* @var password */
	var $password = '';
	
	/** Is the line managed by Keysource
	* @var bool */
	var $is_keysource_managed = false;
	
	/** Comments about this contract
	* @var text */
	var $comments = '';
	
	/** The maximum download speed (KB)
	* @var int */
	var $speed_max_down = 0;
	
	/** The maximum upload speed (KB)
	* @var int */
	var $speed_max_up = 0;
	
	/** The guaranteed download speed (KB)
	* @var int */
	var $speed_guaranteed_down = 0;
	
	/** The guaranteed upload speed (KB)
	* @var int */
	var $speed_guaranteed_up = 0;
	
	/** The number of months in advance to notify the expiration of the contract
	* @var int */
	var $notice_months = 0;
	
	/** The date when the notification was sent, or 0 if no notification was sent
	* @var timestamp */
	var $date_notified = 0;
	
	/** If True, no notifications will be generated even if the contract is 
	* inside the notification period
	* @var bool */
	var $suspend_notifs = 0;
	
	/** The number of days before contract expiration when to repeat the expiration notification,
	* if a notification was already sent. 
	* @var int */
	var $notice_days_again = 0;
	
	/** The date when the final ("again") notification was sent, if any
	* @var timestamp */
	var $notice_again_sent = 0;
	
	
	/** The attachments for this customer internet contract
	* @var array(CustomerInternetContractAttachment) */
	var $attachments = array ();
	
	/** The related Notification object for this contract, if it exists
	* @var Notification */
	var $notification = null;
	
	
	/** The associated Customer object. Note that this only loaded on request, with load_customer() method
	* @var Customer */
	var $customer = null;
	
	/** The associated ProviderContract object. Note that this is only loaded on request, through load_details() method
	* @var ProviderContract */
	var $provider_contract = null;
	
	/** The associated Provider object. Note that this is only loaded on request, through load_details() method
	* @var Provider */
	var $provider = null;
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_CUSTOMERS_INTERNET_CONTRACTS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'customer_id', 'contract_id', 'client_number', 'start_date', 'end_date', 'is_closed', 'adsl_line_number', 'line_type', 'ip_address', 'lan_ip', 'netmask', 'ip_range', 'has_router', 'has_smtp_feed', 'contract_or_login', 'is_keysource_managed', 'comments', 'password', 'speed_max_down', 'speed_max_up', 'speed_guaranteed_down', 'speed_guaranteed_up', 'notice_months', 'notice_days_again', 'date_notified', 'notice_again_sent', 'suspend_notifs');
	
	
	/** 
	* Contructor. Loads an object's values if an object ID is specified 
	* @param	int		$id		An object ID
	* @param	bool		$load_details	If true, the constructor will also load the associated ProviderContract and Provider objects
	*/
	function CustomerInternetContract ($id = null, $load_details = false)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
			if ($load_details) $this->load_details ();
		}
	}

	
	/** Loads the contract information and the related attachments */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				$this->attachments = CustomerInternetContractAttachment::get_attachments (array('customer_internet_contract_id'=>$this->id));
				
				$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_INTERNET_CONTRACT.' AND object_id='.$this->id;
				$id = db::db_fetch_field ($q, 'id');
				if ($id) $this->notification = new Notification ($id);
			}
		}
	}
	
	
	/** Checks if the information is valid for the contract */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->customer_id) {error_msg ('Please specify the customer.'); $ret = false;}
		if (!$this->contract_id) {error_msg ('Please specify the contract.'); $ret = false;}
		if (!$this->end_date or !$this->start_date) {error_msg ('Please specify the start and end dates.'); $ret = false;}
		if ($this->end_date>0 and $this->end_date<$this->start_date) {error_msg ('The start date can\'t be bigger than the end date.'); $ret = false;}
		if ($this->notice_days_again)
		{
			if (!$this->notice_months) {error_msg ('You can\'t specify "Notify again" without a "Notice period"'); $ret = false;}
			elseif ($this->notice_months*30 <= $this->notice_days_again)
			{
				error_msg ('The "Notify again" interval can\'t be longer than "Notice period"'); $ret = false;
			}
		}
		
		return $ret;
	}
	
	/** Loads the associated Customer object */
	function load_customer ()
	{
		if ($this->customer_id) $this->customer = new Customer ($this->customer_id);
	}
	
	/** Loads the associated Provider and ProviderContract objects */
	function load_details ()
	{
		if ($this->contract_id)
		{
			$this->provider_contract = new ProviderContract ($this->contract_id);
			$this->provider = new Provider ($this->provider_contract->provider_id);
			$this->customer = new Customer ($this->customer_id);
		}
	}
	
	/** Returns the name of this internet contract, composed of provider name and contract name */
	function get_name ()
	{
		$ret = '';
		if ($this->id)
		{
			// If the provider and contract objects are loaded, use them. Otherwise fetch the names from database
			if ($this->provider->id and $this->provider_contract->id) $ret = $this->provider->name.': '.$this->provider_contract->name;
			else
			{
				$q = 'SELECT concat(p.name,": ",pc.name) as name FROM '.TBL_PROVIDERS_CONTRACTS.' pc INNER JOIN '.TBL_PROVIDERS.' p ';
				$q.= 'ON pc.provider_id=p.id WHERE pc.id='.$this->contract_id;
				$ret = db::db_fetch_field ($q, 'name'); 
			}
		}
		return $ret;
	}
	
	/** Returns the name of the provider */
	function get_provider_name ()
	{
		$ret = '';
		if ($this->id)
		{
			if ($this->provider->id) $ret = $this->provider->name; // The provider was already loaded
			else
			{
				$q = 'SELECT p.name FROM '.TBL_PROVIDERS_CONTRACTS.' pc INNER JOIN '.TBL_PROVIDERS.' p ';
				$q.= 'ON pc.provider_id=p.id WHERE pc.id='.$this->contract_id;
				$ret = db::db_fetch_field ($q, 'name'); 
			}
		}
		return $ret;
	}
	
	/** Returns the name of the contract */
	function get_provider_contract_name ()
	{
		$ret = '';
		if ($this->id)
		{
			if ($this->provider_contract->id) $ret = $this->provider_contract->name; // The provider contract was already loaded
			else
			{
				$q = 'SELECT name FROM '.TBL_PROVIDERS_CONTRACTS.' WHERE id='.$this->contract_id;
				$ret = db::db_fetch_field ($q, 'name'); 
			}
		}
		return $ret;
	}
	
	/** Returns the name of the customer */
	function get_customer_name ()
	{
		$ret = '';
		if ($this->customer_id)
		{
			if ($this->customer->id) $ret = $this->customer->name; // The customer was already loaded
			else
			{
				$q = 'SELECT name FROM '.TBL_CUSTOMERS.' WHERE id='.$this->customer_id;
				$ret = db::db_fetch_field ($q, 'name');
			}
		}
		return $ret;
	}
	
	/** Deletes a contract and all associated attachments */
	function delete ()
	{
		if ($this->id)
		{
			for ($i=0; $i<count($this->attachments); $i++) 
			{
				$this->attachments[$i]->delete ();
			}
			parent::delete ();
		}
	}
	
	/** Returns TRUE if the contract is inside the notice period, but only if it is still marked
	* as active and it doesn't have 'suspend_notifs' set. */
	function is_in_notice_period($ignore_suspend_notfis = false)
	{
		$ret = false;
		if ($this->id and $this->start_date>0 and $this->end_date>0 and $this->notice_months>0 and !$this->is_closed)
		{
			if (!$this->suspend_notifs or $ignore_suspend_notfis)
			{
				$ret = (($this->end_date - time()) < $this->notice_months * 30 * 24 * 3600);
			}
		}
		return $ret;
	}
	
	/** Returns TRUE if the contract has expired (passed end_date), but only if it is still marked as active */
	function is_expired ()
	{
		$ret = false;
		if ($this->id and $this->start_date>0 and $this->end_date>0 and $this->notice_months>0 and !$this->is_closed)
		{
			$ret = ($this->end_date <= time());
		}
		return $ret;
	}
	
	/** Returns the interval (as string) in which the contract expires - only for contracts in notice period */
	function get_expiration_string ()
	{
		$ret = '';
		if ($this->is_in_notice_period(true))
		{
			if ($this->is_expired()) $ret = 'Contract expired';
			else
			{
				$exp_days = intval (($this->end_date - time()) / (24 * 3600));
				if ($exp_days > 30) $ret = 'Expires in '.intval($exp_days/30).' months';
				elseif ($exp_days > 1) $ret = 'Expires in '.$exp_days.' days';
				elseif ($exp_days == 1) $ret = 'Expires in 1 day';
				else $ret = 'Expires today';
			}
		}
		return $ret;
	}
	
	/** Marks the notifications as being suspended for this contract and saves the object. This implies deleting any notifications
	* that might exist linked to this contract. */
	function suspend_notifications ()
	{
		if ($this->id)
		{
			$this->suspend_notifs = true;
			$this->save_data ();
			
			// Delete any related notifications
			$q = 'SELECT DISTINCT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_INTERNET_CONTRACT.' AND object_id='.$this->id;
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$notification = new Notification ($id);
				$notification->delete ();
			}
		}
	}
	
	/** Marks the notifications as NOT being suspended for this contract and saves the object. Will also
	* run check_expirations(), which will create any needed notifications */
	function unsuspend_notifications ()
	{
		if ($this->id)
		{
			$this->suspend_notifs = false;
			$this->save_data ();
			
			CustomerInternetContract::check_expirations ();
		}
	}
	
	/** Clears the date_notified flag from the contract, which allows raising again notifications for this
	* contract if needed and if one doesn't exist already. Will delete any existing notifications, 
	* will save the object and will also run check_expirations(), so needed notifications are re-created right away() */
	function remove_notified_mark ()
	{
		if ($this->id)
		{
			$this->notice_again_sent = 0;
			$this->date_notified = 0;
			$this->save_data ();
			
			// Delete any related notifications
			$this->delete_notifications ();
			
			CustomerInternetContract::check_expirations ();
		}
	}
	
	/** Clears the notice_again_sent flag from the contract, which allows raising again notifications for this
	* contract if needed. Will delete any existing notifications, will save the object and will also run 
	* check_expirations(), so needed notifications are re-created right away() */
	function remove_notified_again_mark ()
	{
		if ($this->id)
		{
			$this->notice_again_sent = 0;
			$this->save_data ();
			
			// Delete any related notifications
			$this->delete_notifications ();
			
			CustomerInternetContract::check_expirations ();
		}
	}
	
	/** Deletes all Notification objects associated with this contract */
	function delete_notifications ()
	{
		if ($this->id)
		{
			$q = 'SELECT DISTINCT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_INTERNET_CONTRACT.' AND object_id='.$this->id;
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$notification = new Notification ($id);
				$notification->delete ();
			}
		}
	}
	
	/** Returns true if a customer user recipient is available for this contract's customer. This
	* is used mainly in notification e-mails, to check if the customer was notified */
	function has_customer_recipient ()
	{
		$ret = false;
		if ($this->customer_id)
		{
			class_load ('InfoRecipients');
			$cust_recipients = InfoRecipients::get_customer_recipients_customers (array('customer_id'=>$this->customer_id, 'include_any'=>true), $no_count);
			$ret = (count($cust_recipients) > 0);
		}
		return $ret;
	}
	
	
	/** Creates and dispatch expiration notifications for this contract 
	* @param	bool			$is_repeat	False or True if this is the first notification (triggered by 'notice_months') or
	*							a repeated notification (triggered by 'notice_days_again'. In the first case, the
	*							'notice_days_again' will be automatically set to EXP_CONTRACT_DEFAULT_FINAL_DAYS,
	*							unless the resulting date would be in the past.
	*/
	function dispatch_expiration_notification ($is_repeat = false)
	{
		if ($this->id)
		{
			class_load ('InfoRecipients');
			// See if there are any specific Keysource recipients for this customer
			$recipients = InfoRecipients::get_customer_recipients (
				array ('customer_id' => $this->customer_id, 'notif_obj_class' => NOTIF_OBJ_CLASS_INTERNET_CONTRACT), $no_total
			);
			$recipients = $recipients[$this->customer_id][NOTIF_OBJ_CLASS_INTERNET_CONTRACT];
			if (count($recipients)==0)
			{
				// There are no customer-specific recipients, so use the default Keysource recipients
				$recipients = InfoRecipients::get_all_type_recipients ();
				$recipients = $recipients[NOTIF_OBJ_CLASS_INTERNET_CONTRACT];
			}
			
			// Check what customers recipients are specified (or available, if no specific recipient was set), if any
			$cust_recipients = array ();
			if (!$this->is_keysource_managed)
			{
				$cust_recipients = InfoRecipients::get_customer_recipients_customers (array('customer_id'=>$this->customer_id, 'include_any'=>true), $no_count);
				$cust_recipients = $cust_recipients [$this->customer_id];
			}
			
			// Raise the notification with the found recipients
			$all_recipients = array_merge ($recipients, $cust_recipients); 
			$notif_id = Notification::raise_notification_array (array(
				'event_code' => NOTIF_CODE_INTERNET_CONTRACT_EXPIRES,
				'level' => ALERT_NONE,
				'object_class' => NOTIF_OBJ_CLASS_INTERNET_CONTRACT,
				'object_id' => $this->id,
				'object_event_code' => 0,
				'item_id' => 0,
				'user_ids' => $all_recipients,
				'text' => '',
				'no_increment' => true,
				'no_repeat' => true,
			));
			$tpl = '_classes_templates/klara/msg_internet_contract_expires.tpl';
			$tpl_customer = '_classes_templates/klara/msg_customer_internet_contract_expires.tpl';
			
			// Set the appropriate message templates for the notification
			$notification = new Notification ($notif_id);
			foreach ($recipients as $recip_id) $notification->set_notification_recipient_text ($recip_id, '', true, $tpl);
			foreach ($cust_recipients as $recip_id) $notification->set_notification_recipient_text ($recip_id, '', true, $tpl_customer);
			
			// Mark that notifications have been sent for this contract, depending on the type of notification
			if (!$is_repeat)
			{
				$this->notice_days_again = EXP_CONTRACT_DEFAULT_FINAL_DAYS;
				if (time() >= $this->end_date-($this->notice_days_again*24*3600)) $this->notice_days_again = 0;
				$this->notice_again_sent = 0;
				$this->date_notified = time ();
				$this->save_data ();
			}
			else
			{
				$this->notice_again_sent = time();
				$this->save_data ();
			}
		}
	}
	
	
	/** [Class Method] Returns a list with the customers Internet contracts defined in the system 
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- customer_id: Return only contracts for this customer
	* @return	array					Associative array, the keys being internet contracts IDs and the
	*							values being the names (provider/contract)
	*/
	public static function get_contracts_list ($filter = array ())
	{
		$ret = array ();
		$q = 'SELECT c.id, concat(p.name,": ",pc.name) as name FROM '.TBL_CUSTOMERS_INTERNET_CONTRACTS.' c ';
		$q.= 'INNER JOIN '.TBL_PROVIDERS_CONTRACTS.' pc ON c.contract_id=pc.id INNER JOIN '.TBL_PROVIDERS.' p ON pc.provider_id=p.id ';
		if ($filter['customer_id']) $q.= 'WHERE c.customer_id='.$filter['customer_id'].' ';
		$q.= 'ORDER BY 2 ';
		$ret = db::db_fetch_list ($q);
		return $ret;
		
	}
	
	/**
	* [Class Method] Return contracts according to a specified criteria
	* @param	array			$filter		Associative array with the filtering criteria. Can contain:
	*							- customer_id: Return only contracts for this customer. Can also be:
	*							  - -1: Return all contracts in notice period (same as specifying 'expiring_contracts')
	*							  - -2: Same as above, but include in results also contracts with 'suspend_notifs'
	*							        (same as specifying 'expiring contracts' and 'ignore_suspend_notifs')
	*							- expiring_contracts: If true, return all contracts that are inside the notification 
	*							  period, which are still marked as active and which don't have 'suspend_notifs' set.
	*							- ignore_suspend_notifs: (Only in combination with 'expiring_contracts'), will ignore
	*							  in search the 'suspend_notifs' flag.
	*							- notify_again: Return contracts for which the final notification needs to be sent.
	*							  Can't be used together with 'expiring_contracts'
	*							- provider_id: Return only contracts for this provider
	*							- load_details: If True, it will load the related Provider and ProviderContract objects
	*							- load_customers: If True, it will load the related Customer objects
	*							- order_by: Specified the sorting criteria. Can be: 'customer', 'provider', or 'date'.
	*							- order_dir: The sorting direction.
	* @return	array(CustomerInternetContract)		Array of CustomerInternetContract objects
	*/
	public static function get_contracts ($filter = array ())
	{
		$ret = array ();
		
		if (!$filter['order_by']) $filter['order_by'] = ($filter['customer_id'] ? 'provider' : 'customer');
		if (!$filter['order_dir']) $filter['order_dir'] = 'ASC';
		
		$q = 'SELECT c.id FROM '.TBL_CUSTOMERS_INTERNET_CONTRACTS.' c ';
		$q.= 'INNER JOIN '.TBL_PROVIDERS_CONTRACTS.' pc ON c.contract_id=pc.id ';
		$q.= 'INNER JOIN '.TBL_PROVIDERS.' p ON pc.provider_id=p.id ';
		$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id WHERE cust.active>0 AND ';
		
		if ($filter['provider_id']) $q.= 'pc.provider_id='.$filter['provider_id'].' AND ';
        $current_user = $GLOBALS['CURRENT_USER'];
		if($current_user)
		{
			if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
			{
				$cc = $current_user->get_assigned_customers_list();
				$q.= 'c.customer_id in (';
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
		
		if ($filter['customer_id']>0) $q.= 'c.customer_id='.$filter['customer_id'].' AND ';
		elseif ($filter['customer_id']==-1) $filter['expiring_contracts'] = true;
		elseif ($filter['customer_id']==-2) {$filter['expiring_contracts'] = true; $filter['ignore_suspend_notifs'] = true; }
		
		if ($filter['expiring_contracts'])
		{
			// Show contracts inside the notification period
			$q.= '(c.end_date>0 AND notice_months>0 AND is_closed=0 AND ';
			if (!$filter['ignore_suspend_notifs']) $q.= 'suspend_notifs=0 AND ';
			$q.= '((to_days(from_unixtime(end_date))-to_days(now()) < notice_months*30) OR ';
			$q.= '(notice_days_again>0 AND to_days(from_unixtime(end_date))-to_days(now()) < notice_days_again))) AND ';
		}
		elseif ($filter['notify_again'])
		{
			$q.= '(c.end_date>0 AND notice_months>0 AND is_closed=0 AND c.date_notified>0 AND notice_days_again>0 AND ';
			$q.= 'to_days(from_unixtime(end_date))-to_days(now()) < notice_days_again) AND ';
		}
		
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q) . 'ORDER BY ';
		 
		switch ($filter['order_by'])
		{
			case 'provider':	$q.= 'c.is_closed, p.name '.$filter['order_dir'].' '; break;
			case 'customer':	$q.= 'cust.name '.$filter['order_dir'].', c.is_closed, p.name '; break;
			default:		$q.= 'c.start_date '.$filter['order_dir'].', c.is_closed DESC '; break;
		}
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new CustomerInternetContract ($id);
		
		if ($filter['load_details']) for ($i=0; $i<count($ret); $i++) $ret[$i]->load_details ();
		if ($filter['load_customers']) for ($i=0; $i<count($ret); $i++) $ret[$i]->load_customer ();
		
		return $ret;
	}
	
	/** [Class Method] Will check all contracts which are inside the notification period and, where needed,
	* will raise the relevant notifications. This should normally be called from the daily crontab. */
	public static function check_expirations ()
	{
		$notif_contracts_ids = array ();
		
		// Detch contracts for which first notifications have already been sent and for which we need to send again the final notification
		$again_contracts = CustomerInternetContract::get_contracts (array('notify_again'=>true, 'load_details'=>true, 'load_customers'=>true));
		foreach ($again_contracts as $contract)
		{
			$notif_contracts_ids[] = $contract->id; // Keep track of processed expired contracts
			if (!$contract->notification->id)
			{
				$need_notify = ($contract->notice_again_sent==0 or ($contract->notice_again_sent>0 and !$contract->suspend_notifs));
				if ($need_notify)
				{
					// Delete any previous notification, just in case
					$contract->delete_notifications ();
					
					$contract->suspend_notifs = false;
					$contract->dispatch_expiration_notification (true);
				}
			}
		}
		
		// Fetch all expiring contracts
		$expiring_contracts = CustomerInternetContract::get_contracts (array('expiring_contracts'=>true, 'load_details'=>true, 'load_customers'=>true));
		
		// Raise notifications for all expiring contracts where notifications have not been sent yet or where there is no linked Notification object
		foreach ($expiring_contracts as $contract)
		{
			$notif_contracts_ids[] = $contract->id; // Keep track of processed expired contracts
			
			$need_notify = ($contract->is_in_notice_period() and !$contract->notification->id);
			if ($need_notify) $contract->dispatch_expiration_notification ();
		}
		
		// Now check for notifications linked to contracts which don't need notifying anymore
		$q = 'SELECT DISTINCT id, object_id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_INTERNET_CONTRACT;
		$notifs = db::db_fetch_array ($q);
		foreach ($notifs as $notif)
		{
			if (!in_array($notif->object_id, $notif_contracts_ids))
			{
				// This notification should be removed
				$notification = new Notification ($notif->id);
				$notification->delete ();
			}
		}
	}

}

?>
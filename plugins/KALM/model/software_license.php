<?php

class_load ('Software');
class_load ('SoftwareLicenseSN');
class_load ('Notification');
class_load ('SoftwareLicenseFile');

/**
* Class for storing and managing information about the software licenses
* purchased by customers.
*
*/

// The number of licenses to consider for unlimited licenses when sums or comparisions need to be made
define ('UNLIMITED_LICENSE_NUMBER', 999999);

class SoftwareLicense extends Base
{
	/** Object ID
	* @var int */
	var $id = null;
	
	/** The customer ID 
	* @var int */
	var $customer_id = null;
	
	/** The software package ID 
	* @var int */
	var $software_id = null;
	
	/** The license type - see $GLOBALS['LIC_TYPES_NAMES']
	* @var int */
	var $license_type = null;
	
	/** The number of license purchased 
	* @var int */
	var $licenses = 0;
	
	/** The purchase/issue date
	* @var time */
	var $issue_date = 0;
	
	/** The expiration date 
	* @var time */
	var $exp_date = 0;
	
	/** Comments about this license 
	* @var string */
	var $comments = '';
	
	/** The number of used licenses for per-client (CAL) licenses 
	* @var int */
	var $used = 0;
	
	/** If True, no notifications will be raised for this event if the number of used licenses exceeds the available licenses
	* IMPORTANT NOT: When set/unset, this flag will be automatically set for all similart software packages for the same customer
	* @var bool */
	var $no_notifications = false;
	
	
	/** The total number of licenses available - in case the same software package is defined multiple times for the same customer.
	* Is calculated as the sum of all licenses for the same software, same license type and same customer
	* @var int */
	var $licenses_all = 0;
	
	/** The actual Software object referred by this license 
	* @var Software */
	var $software = null;
	
	/** The number of actual used licenses (for 'Per seat' licenses. It is calculated only on request, using KAWACS information 
	* @var int */
	var $used_licenses = 0;
	
	/** The serial numbers defined for this license. Note that this is loaded only on request, with load_serials() method
	* @var array(SoftwareLicenseSN) */
	var $serials = array ();
	
	/** The files attached to this license. Note that this is loaded only on request, with load_files() method
	* @var array(SoftwareLicenseFile) */
	var $files = array ();
	
	
	/** The Notification object associated with this license (or any similar software for same customer).
	* This is loaded on request, with load_notification() 
	* @var Notification */
	var $notification = null;
	
	
	/** The database table for storing the licenses information
	* @var string */
	var $table = TBL_SOFTWARE_LICENSES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'customer_id', 'software_id', 'license_type', 'licenses', 'issue_date', 'exp_date', 'comments', 'used', 'no_notifications');
	
	
	
	/** Class constructor, also loads the object data if an ID is provided */
	function SoftwareLicense ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
                        //$this->verify_access();
		}
		else
		{
			$this->issue_date = time();
		}
	}

	
	/** Loads the object data, as well as the associated software object (if any) */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			
			if ($this->id and $this->software_id)
			{
				$this->software = new Software($this->software_id);
				
				// Calculate all licenses of the same type
				$q = 'SELECT sum(if(licenses>0,licenses,'.UNLIMITED_LICENSE_NUMBER.')) as licenses_all FROM '.TBL_SOFTWARE_LICENSES.' WHERE ';
				$q.= 'customer_id='.$this->customer_id.' AND software_id='.$this->software_id.' AND license_type='.$this->license_type;
				$this->licenses_all = db::db_fetch_field ($q, 'licenses_all');
			}
		}
	}
	
	
	/** Load the serial numbers for this license */
	function load_serials ()
	{
		if ($this->id)
		{
			$this->serials = SoftwareLicenseSN::get_serial_numbers(array('license_id' => $this->id));
		}
	}
	
	
	/** Load the files for this license */
	function load_files ()
	{
		if ($this->id)
		{
			$this->files = SoftwareLicenseFile::get_files(array('license_id' => $this->id));
		}
	}
	
	/** Load the associated notification */
	function load_notification ()
	{
		if ($this->id) $this->notification = $this->get_notification ();
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$valid = true;
		
		if (!$this->software_id) {error_msg ('Please select the type of software.'); $valid = false;}
		if (!$this->customer_id) {error_msg ('Please specify the customer.'); $valid = false;}
		if (!$this->license_type) {error_msg ('Please specify the licensing type.'); $valid = false;}
		if (!$this->licenses) {error_msg ('Please specify the number of licenses.'); $valid = false;}
		
		if (!$this->issue_date) {error_msg ('Please specify the license issue date.'); $valid = false;}
		
		return $valid;
	}
	
	/** Saves object data, also ensuring that the 'no_notifications' flag is set/unset the same for all similar software packages  */
	function save_data ()
	{
		parent::save_data ();
		if ($this->id)
		{
			$val = ($this->no_notifications ? 1 : 0);
			$q = 'UPDATE '.TBL_SOFTWARE_LICENSES.' SET no_notifications='.$val.' WHERE ';
			$q.= 'customer_id='.$this->customer_id.' AND software_id='.$this->software_id.' AND license_type='.$this->license_type;
			db::db_query($q);
		}
	}
	
	/** Deletes a license and all attached serial numbers and files */
	function delete ()
	{
		if ($this->id)
		{
			// Delete serial numbers
			$this->load_serials ();
			for ($i=0; $i<count($this->serials); $i++) $this->serials[$i]->delete ();
			
			// Delete files
			$this->load_files ();
			for ($i=0; $i<count($this->files); $i++) $this->files[$i]->delete ();
			
			// Delete the license itself
			parent::delete ();
			
			// Also run the checks for exceeded licenses for this customer
			SoftwareLicense::check_licenses_notifications ($this->customer_id);
		}
	}
	
	
	/** Returns true or false if for this license the number of used licenses should be determined
	* by counting the reported software in Kawacs */
	function need_kawacs_counting ()
	{
		return ($this->license_type == LIC_TYPE_SEAT or $this->license_type == LIC_TYPE_FREEWARE);
	}
	
	
	/**
	* Loads from the KAWACS information the number of used licenses. 
	* It returns the number of licenses found, but also sets the object's 'used_licenses' property
	*/
	function check_used_licenses ()
	{
		$licenses = 0;
		if ($this->id and $this->need_kawacs_counting());
		{
			$licenses = $this->software->get_used_licenses ($this->customer_id);
		}
		
		$this->used_licenses = $licenses;
		return $licenses;
	}
	
	
	/**
	* Returns an array with the IDs of the computers using this license 
	* @return	array(Computer)			Array of Computer objects, with the computers
	*						that use this software license
	*/
	function get_computers ()
	{
		$ret = array ();
		
		if ($this->id and $this->need_kawacs_counting ())
		{
			$ret = $this->software->get_computers ($this->customer_id);
		}
		
		return $ret;
	}
	
	/**
	* Returns a list of the computers using this license 
	* @param	bool			$by_asset_no		If True, then use the asset numbers as array keys instead of computer IDs
	* @return	array()						Associative array with the computers using this license.
	*								The keys are computer IDs or, if $by_asset_no is True, computer asset numbers,
	*								and the values are their netbios names.
	*/
	function get_computers_list($by_asset_no = false)
	{
		$ret = array ();
		
		if ($this->id and $this->need_kawacs_counting ())
		{
			$ret = $this->software->get_computers_list($this->customer_id, $by_asset_no);
		}
		
		return $ret;
	}
	
	
	/** Returns true or false if the provided name matches any of the name rules */
	function matches_name ($name = '')
	{
		$ret = false;
		if ($name and $this->id and $this->need_kawacs_counting ())
		{
			$ret = $this->software->matches_name ($name);
		}
		return $ret;
	}
	
	/** Returns an array of license IDs for any other licenses for the same software and the same customer.
	* @return	array					Array with the found IDs, INCLUDING the current object ID
	*/
	function get_ids_similar ()
	{
		$ret = array ();
		if ($this->id)
		{
			$q = 'SELECT id FROM '.TBL_SOFTWARE_LICENSES.' WHERE customer_id='.$this->customer_id;
			$q.= ' AND software_id='.$this->software_id.' AND license_type='.$this->license_type;
			$ret = db::db_fetch_vector ($q);
		}
		return $ret;
	}
	
	
	/** Returns the notification associated with this license, if any.
	* NOTE: If a notification is not linked strictly to this object, will also try to find
	* notifications linked to other packages of same type for the same customer
	* @return	Notification
	*/
	function get_notification ()
	{
		$ret = null;
		if ($this->id)
		{
			$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_SOFTWARE.' AND object_id=';
			$id = db::db_fetch_field ($q.$this->id, 'id');
			if ($id) $ret = new Notification ($id);
			else
			{
				// Look for other software packages of same type
				$q_other = 'SELECT id FROM '.TBL_SOFTWARE_LICENSES.' WHERE id<>'.$this->id.' AND ';
				$q_other.= 'customer_id='.$this->customer_id.' AND software_id='.$this->software_id.' AND ';
				$q_other.= 'license_type='.$this->license_type;
				$other_ids = db::db_fetch_vector($q_other, 'id');
				foreach ($other_ids as $other_id)
				{
					$id = db::db_fetch_field($q.$other_id, 'id');
					if ($id)
					{
						$ret = new Notification($id);
						break;
					}
				}
			}
		}
		return $ret;
	}
	
	/** 
	* [Class Method] Returns the list of licenses purchased by a customer.
	* @param	int	$customer_id		The ID of the customer
	* @param	boolean	$get_used_license	If to check from KAWACS the number of used licenses
	* @return	array (SoftwareLicense)
	*/
	public static function get_customer_licenses($customer_id = null, $get_used_licenses = false)
	{
		$ret = array ();
		
		if ($customer_id and is_numeric ($customer_id))
		{
			$q = 'SELECT l.id FROM '.TBL_SOFTWARE_LICENSES.' l INNER JOIN '.TBL_SOFTWARE.' s ';
			$q.= 'ON l.software_id=s.id WHERE l.customer_id='.$customer_id.' ';
			$q.= 'ORDER BY s.name ';
			
			$ids = db::db_fetch_array ($q);
			
			foreach ($ids as $id)
			{
				$lic = new SoftwareLicense ($id->id);
				if ($get_used_licenses) $lic->check_used_licenses ();
				$ret[] = $lic;
			}
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns a list with the software names for the licenses of customer
	* @param	int		$customer_id		The ID of the customer
	* @return	array					Associative array, the keys being software licenses IDs and the values being the names
	*							of the respective software packages
	*/
	public static function get_customer_software_list($customer_id)
	{
		$ret = array ();
		if ($customer_id)
		{
			$q = 'SELECT l.id, s.name FROM '.TBL_SOFTWARE_LICENSES.' l INNER JOIN '.TBL_SOFTWARE.' s ON l.software_id=s.id ';
			$q.= 'WHERE l.customer_id='.$customer_id.' ORDER BY s.name ';
			$ret = db::db_fetch_list($q);
		}
		return $ret;
	}
	
	/** [Class Method] Returns all notifications associated with exceeded software licenses (only those where "no_notifs" was not set 
	* @return	bool			$load_notifs	If True, will also load the associated Notification objects
	* @return	array					Associative array, the keys being customer IDs and the values being
	*							the software licenses which have notifications.
	*/
	public static function get_exceeded_notifications ($load_notifs = false)
	{
		$q = 'SELECT n.object_id FROM '.TBL_NOTIFICATIONS.' n INNER JOIN '.TBL_SOFTWARE_LICENSES.' l ';
		$q.= 'ON n.object_id=l.id INNER JOIN '.TBL_CUSTOMERS.' cust ON l.customer_id=cust.id ';
		$q.= 'INNER JOIN '.TBL_SOFTWARE.' s ON l.software_id=s.id ';
		$q.= 'WHERE n.object_class='.NOTIF_OBJ_CLASS_SOFTWARE.' ';//AND l.no_notifications=0 ';
		//debug($this->current_user);
        $current_user = $GLOBALS['CURRENT_USER'];
		if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			//we have a customer_administrator show him only what he needs to know
			$cc = $current_user->get_assigned_customers_list();
			$q.= 'AND l.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$q.=") ";
		}
		$q.= 'ORDER BY cust.name, s.name ';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id)
		{
			$lic = new SoftwareLicense ($id);
			if ($load_notifs) $lic->load_notification ();
			$ret[$lic->customer_id][] = $lic;
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns a list with all the licenses which have exceeded the available numbers,
	* either for all customers or for a specific customer.
	* @param	bool			$strict		If True, the comparison will be made strict. If False (default)
	*							the result will also include licenses where the used licenses equals
	*							the number of available licenses.
	* @param	int			$customer_id	If specified, the checks will be done only for this customer ID.
	* @return	array					Associative array with the exceeded licenses, the keys being 
	*							customer IDs and the values being arrays with the SoftwareLicense
	*							that have exceeded the number of available licenses.
	*/
	public static function get_exceeded_licenses ($strict=false, $customer_id = false)
	{
		class_load ('Computer');
		$ret = array ();
		
		$software_id = SOFTWARE_ITEM_ID;
		$os_id = OS_NAME_ITEM_ID;
		$t1 = time();
		// Fetch the list of defined customer licenses that need checking
		$q = 'SELECT DISTINCT l.id FROM '.TBL_SOFTWARE_LICENSES.' l INNER JOIN '.TBL_CUSTOMERS.' c ';
		$q.= 'ON l.customer_id=c.id INNER JOIN '.TBL_SOFTWARE.' s ON l.software_id=s.id WHERE ';

        $current_user = $GLOBALS['CURRENT_USER'];
		if($current_user)
		{
			if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
			{
				$cc = $current_user->get_assigned_customers_list();
				$q.= 'l.customer_id in (';
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
		if ($customer_id) $q.= 'l.customer_id='.$customer_id.' AND ';
		$q.= '(l.license_type='.LIC_TYPE_SEAT.' OR l.license_type='.LIC_TYPE_FREEWARE.' OR l.license_type='.LIC_TYPE_CLIENT.' ) ';
		$q.= 'AND l.licenses>=0 AND c.active>0 ';
		$q.= 'ORDER BY c.name, s.name, l.id ';
		$licenses_ids = db::db_fetch_vector ($q);
		
		$licenses = array ();
		foreach ($licenses_ids as $id)
		{
			$lic = new SoftwareLicense ($id);
			$licenses[] = $lic;
			$ret[$lic->customer_id] = false; // We do this so the results will be ordered by customer name
		}
		
		// Make separate lists with the licenses to check - those who need Kawacs counting and those who don't
		$licenses_kawacs = array ();
		$licenses_no_kawacs = array ();
		for ($i=0, $i_max=count($licenses); $i<$i_max; $i++)
		{
			if ($licenses[$i]->need_kawacs_counting()) $licenses_kawacs[] = $licenses[$i];
			else $licenses_no_kawacs[] = $licenses[$i];
		}
		
		// Make a list with all software rules to check, per software type, license type
		$software_checks = array ();
		$software_checks_customers = array ();
		$software_checks_available = array ();
		$licenses_grouped = array ();
		foreach ($licenses_kawacs as $lic)
		{
			$software_checks_customers[$lic->software_id][$lic->license_type][] =  $lic->customer_id;
			$software_checks_available[$lic->software_id][$lic->license_type][$lic->customer_id] = $lic->licenses_all;
			$licenses_grouped[$lic->software_id][$lic->license_type][$lic->customer_id] = $lic;
			if (!isset($software_checks[$lic->software_id][$lic->license_type]))
			{
				$cond = '';
				foreach ($lic->software->match_rules as $rule) $cond.= 'OR value '.$rule->get_query_condition();
				$cond = preg_replace ('/^OR\s*/', '', $cond);
				if ($cond) $software_checks[$lic->software_id][$lic->license_type] = $cond;
			}
		}
		
		foreach ($software_checks as $software_id => $lic_types)
		{
			foreach ($lic_types as $lic_type => $cond)
			{
				$customer_ids = $software_checks_customers[$software_id][$lic_type];
				if (count($customer_ids) > 0)
				{
					$q = 'SELECT c.customer_id, count(DISTINCT i.computer_id) as cnt FROM '.TBL_COMPUTERS.' c ';
					$q.= 'INNER JOIN '.TBL_COMPUTERS_ITEMS.' i ON c.id=i.computer_id WHERE ';
					if ($customer_id) $q.= 'c.customer_id='.$customer_id.' AND ';
					$q.= '(c.customer_id='.implode(' OR c.customer_id=', $customer_ids).') AND ';
					$q.= '(i.item_id='.SOFTWARE_ITEM_ID.' OR i.item_id='.OS_NAME_ITEM_ID.') AND ('.$cond.') ';
					$q.= 'GROUP BY c.customer_id ';
					$used = DB::db_fetch_list ($q);
					
					foreach ($used as $cust_id => $cnt)
					{
						if ($strict) $exceeded = ($cnt > $software_checks_available[$software_id][$lic_type][$cust_id]);
						else $exceeded = ($cnt >= $software_checks_available[$software_id][$lic_type][$cust_id]);
						
						if ($exceeded)
						{
							$lic = $licenses_grouped[$software_id][$lic_type][$cust_id];
							$lic->used_licenses = $cnt;
							$ret[$cust_id][] = $lic;
						}
					}
				}
			}
		}
		
		// Check the licenses which don't need Kawacs counting
		foreach ($licenses_no_kawacs as $lic)
		{
			if ($strict) $exceeded = ($lic->used > $lic->licenses);
			else $exceeded = ($lic->used >= $lic->licenses);
			
			if ($exceeded) $ret[$lic->customer_id][] = $lic;
		}
		
		/* Old method - more elegant but more expensive too
		for ($i=0, $i_max=count($licenses); $i<$i_max; $i++)
		{
			
			if ($licenses[$i]->need_kawacs_counting ())
			{
				$licenses[$i]->check_used_licenses ();
				if ($licenses[$i]->used_licenses >= $licenses[$i]->licenses) $ret[$licenses[$i]->customer_id][] = $licenses[$i];
			}
			else
			{
				if ($licenses[$i]->used > $licenses[$i]->licenses) $ret[$licenses[$i]->customer_id][] = $licenses[$i];
			}
		}
		*/
		
		// Clear empty elements that were placed in array at the beginning, for ordering
		foreach ($ret as $customer_id => $d) if (!is_array($d)) unset($ret[$customer_id]);
		
		return $ret;
	}
	
	
	/** [Class Method] Checks for exceeded licenses, either for all customers or a specific customer,
	* and then raises or deletes the notifications as needed. For inactive customers no alerts are raised
	* @param	int			$customer_id		If specified, the checks will be done only for this customer ID.
	* @param	array			$exceeded_licenses	Array with exceeded licenses, e.g. from the output of get_exceeded_licenses().
	*								This can be used if the checks were already performed before calling this 
	*								function, to avoid wasting time with a second call.
	*/
	public static function check_licenses_notifications ($customer_id=false, $exceed_licenses=false)
	{
		class_load ('InfoRecipients');
		
		// If the list of exceeded licenses is not provided, fetch it now
		if (!is_array($exceed_licenses)) $exceeded_licenses = SoftwareLicense::get_exceeded_licenses(true, $customer_id);
		
		// Keep track of all licenses that have or for which we have to raise notifications
		// Any notification linked to a license not in this list will be deleted afterwards
		$notif_licenses = array ();
		
		foreach ($exceed_licenses as $cust_id => $licenses)
		{
			foreach ($licenses as $license)
			{
				// Keep track of licenses for which notifications should exist
				if (!$license->no_notifications) $notif_licenses[] = $license;
				
				// See if there are any notifications already raised for this license
				$notification = $license->get_notification();
				
				if (!$notification->id and !$license->no_notifications)
				{
					// No notification exists, so create one
					// See if there are any specific Keysource recipients for this customer
					$recipients = InfoRecipients::get_customer_recipients (
						array ('customer_id' => $cust_id, 'notif_obj_class' => NOTIF_OBJ_CLASS_SOFTWARE), $no_total
					);
					
					$recipients = $recipients[$cust_id][NOTIF_OBJ_CLASS_SOFTWARE];
					if (count($recipients)==0)
					{
						// There are no customer-specific recipients, so use the default Keysource recipients
						$recipients = InfoRecipients::get_all_type_recipients ();
						$recipients = $recipients[NOTIF_OBJ_CLASS_SOFTWARE];
					}
					
					$notif_id = Notification::raise_notification_array (
                                        array(
                                            'event_code' => NOTIF_CODE_LICENSES_EXCEEDED,
                                            'level' => ALERT_NONE,
                                            'object_class' => NOTIF_OBJ_CLASS_SOFTWARE,
                                            'object_id' => $license->id,
                                            'object_event_code' => 0,
                                            'item_id' => 0,
                                            'user_ids' => $recipients,
                                            'text' => '',
                                            'no_increment' => true,
                                            'no_repeat' => true,
					                    )
                                );
				}
			}
		}
		
		// In case there are multiple licenses for the same software and the same customer,
		// build the list of IDs for similar licenses
		$notif_licenses_ids = array ();
		foreach ($notif_licenses as $license) $notif_licenses_ids[] = $license->get_ids_similar ();
		
		// Now fetch all notifications linked to licenses. If the linked license ID 
		// is not in $notif_licenses_ids, the notification will be deleted
		$q = 'SELECT DISTINCT n.id, n.object_id FROM '.TBL_NOTIFICATIONS.' n '; 
		if (!$customer_id) $q.= 'WHERE n.object_class='.NOTIF_OBJ_CLASS_SOFTWARE;
		else
		{
			$q.= 'INNER JOIN '.TBL_SOFTWARE_LICENSES.' s ON n.object_id=s.id ';
			$q.= 'WHERE n.object_class='.NOTIF_OBJ_CLASS_SOFTWARE.' AND s.customer_id='.$customer_id;
		}
		
		$data = DB::db_fetch_array ($q);
		foreach ($data as $d)
		{
			$found = false;
			foreach ($notif_licenses_ids as $ids)
			{
				if (in_array($d->object_id, $ids)) {$found = true; break;}
			}
			
			if (!$found)
			{
				$notification = new Notification($d->id);
				$notification->delete ();
			}
		}
	}

    function verify_access() {
        $uid = get_uid();
        class_load('User');
        $user = new User($uid);
        if($user->type == USER_TYPE_CUSTOMER) {
            if($this->customer_id != $user->customer_id) {
                $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
                header("Location: $url\n\n");
                exit;
            }
        }
    }
	
}

?>
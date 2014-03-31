<?php

class_load ('Computer');
class_load ('DiscoverySettingDetail');

/**
* Class for representing network discoveries settings for customers.
*
* These objects will store per-customer settings regarding to network discoveries.
* Even if a customer has an associated DiscoverySetting object created, actual
* discoveries will be performed only if one or more DiscoverySettingDetail objects
* are created as well.
*
* Note that when getting a DiscoverySetting object for a customer with the 
* get_by_customer() method, if an object was not created before for that customer 
* it will be automatically created now - but only if the customer is an active
* one.
*
* There can be only one DiscoverySetting object per customer. However, we're not
* using the customer ID as primary key in the database table so we don't have to
* worry about making the class inheritance from Base more complicated.
*
* Further discoveries settings are stored in the associated DiscoverySettingDetail
* object, which allows to specify which computers should do the discoveries and
* what IP ranges to use.
*
* When doing discoveries, unless the settings are prohibiting them, the Kawacs Agent
* will attempt to collect additional information about the found devices using
* WMI and SNMP.
*
*/

class DiscoverySetting extends Base
{
	/** The unique numeric ID
	* @var int */
	var $id = null;
	
	/** The customer to which this setting belongs to
	* @var int */
	var $customer_id = null;
	
	/** If True, all discoveries will be disabled for this customer
	* @var bool */
	var $disable_discoveries = false;
	
	/** Comments
	* @var text */
	var $comments = '';
	
	
	/** Array with the associated DiscoverySettingDetail objects for
	* this customer, specifying what computers should do the discovery
	* and which IP addresses to use.
	* @var array(DiscoverySettingDetail) */
	var $details = array ();
	
	
	/** The database table storing objects data 
	* @var string */
	var $table = TBL_DISCOVERIES_SETTINGS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'customer_id', 'disable_discoveries', 'comments');
	
	
	/** Constructor, also loads an object data if an ID is specified */
	function DiscoverySetting ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	/** [Class Method] Returns the DiscoverySetting associated with a customer.
	* If one didn't exist before, one will be created now - but only if the customer
	* is active and it is not otherwise prohibited by the parameters.
	* @param	int				$customer_id	The ID of the customer for which to get the setting
	* @param	bool				$allow_create	If False, then a new object will NOT be created if one doesn't exist already
	* @return	DiscoverySetting				The DiscoverySetting object for that customer, or null if one doesn't exist 
	*								and the customer is not active.
	*/
	function get_by_customer ($customer_id, $allow_create = true)
	{
		$ret = null;
		
		if ($customer_id)
		{
			$id = DB::db_fetch_field ('SELECT id FROM '.TBL_DISCOVERIES_SETTINGS.' WHERE customer_id='.$customer_id, 'id');
			if ($id) $ret = new DiscoverySetting ($id);
			else
			{
				if ($allow_create)
				{
					// An object doesn't exist for this customer - so create one if the customer is active
					$is_active = DB::db_fetch_field ('SELECT active FROM '.TBL_CUSTOMERS.' WHERE id='.$customer_id, 'active');
					
					if ($is_active)
					{
						$ret = new DiscoverySetting ();
						$ret->customer_id = $customer_id;
						$ret->save_data ();
						$ret->load_data (); // Just in case
					}
				}
			}
		}
		
		return $ret;
	}
	
	
	/** Loads the object's data, as well the associated DiscoverySettingDetail objects. */
	function load_data ()
	{
		parent::load_data ();
		if ($this->customer_id)
		{
			$this->details = DiscoverySettingDetail::get_settings_details ($this->customer_id);
		}
	}
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		if (!$this->customer_id) {error_msg ($this->get_string('NEED_CUSTOMER')); $ret = false;}
		else
		{
			$q = 'SELECT id FROM '.TBL_DISCOVERIES_SETTINGS.' WHERE customer_id='.$this->customer_id.' ';
			if ($this->id) $q.= 'AND id<>'.$this->id;
			if ($this->db_fetch_field ($q, 'id')) {error_msg ($this->get_string('NEED_CUSTOMER_UNIQUE')); $ret = false;}
		}
		
		return $ret;
	}
	
	/** Deletes the object and the associated details */
	function delete ()
	{
		if ($this->id)
		{
			foreach ($this->details as $detail) $detail->delete ();
			parent::delete ();
		}
	}
	
	
	
	/** Returns True or False if network discoveries are enabled for this customer, meaning if discoveries have not
	* been specifically disabled and there is at least one DiscoverySettingDetail object set.
	* @return	bool
	*/
	function is_enabled ()
	{
		return ($this->id and $this->customer_id and !$this->disable_discoveries and count($this->details)>0);
	}
	
	/** [Class Method] Returns the network discoveries settings for all active customers 
	* @param	int				$customer_id	(Optional) If set, will return the settings only for the specified customer
	* @return	array(DiscoverySetting)				Associative array with the discovery settings for all customers,
	*								the keys being customer IDs and the values being the associated
	*								DiscoverySetting objects.
	*/
	function get_customers_settings ($customer_id = null)
	{
		$ret = array ();
		
		if ($customer_id) $ids = array ($customer_id);
		else
		{
			// Fetch the list of active customers and load the discovery settings for each of them
			$ids = DB::db_fetch_vector('SELECT id FROM '.TBL_CUSTOMERS.' WHERE active=1 ORDER BY name');
		}
		
		foreach ($ids as $id) $ret[$id] = DiscoverySetting::get_by_customer ($id);
		
		return $ret;
	}
	
}


/*
drop table if exists discoveries_settings_computers;
create table discoveries_settings_computers (id int not null auto_increment, primary key(id), customer_id int not null, computer_id int not null, ip_start varchar(20), ip_end varchar(20), disable_wmi tinyint not null, disable_snmp tinyint not null, wmi_password varchar(50), wmi_login varchar(50), key(customer_id), key(computer_id), key(ip_start), key(ip_end));

*/
?>
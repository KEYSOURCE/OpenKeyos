<?php
class_load ('PeripheralClass');
class_load ('Customer');
class_load ('Computer');

/**
* Stores data about remote access phones for customers
* 
*/
class AccessPhone extends Base
{
	/** The access phone ID
	* @var int */
	var $id = null;
	
	/** The customer ID
	* @var int */
	var $customer_id = null;
	
	/** The phone number
	* @var string */
	var $phone = '';
	
	/** The type of device connected to the phone line - see $GLOBALS['PHONE_ACCESS_DEVICES']
	* @var int */
	var $device_type = 0;

	/** The ID of the object (Computer or Peripheral) connected to the phone line 
	* @var int */
	var $object_id = 0;
	
	/** The login name for remote access 
	* @var string */
	var $login = '';
	
	/** The password for remote access 
	* @var string */
	var $password = '';
	
	/** Comments about this access number
	* @var text */
	var $text = '';
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_ACCESS_PHONES;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'customer_id', 'phone', 'device_type', 'object_id', 'login', 'password', 'comments');
	
	
	/** Contructor. Loads an object's values if an ID is specified */
	function AccessPhone ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the phone number definition is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->phone) {error_msg ('Please specify the phone number.'); $ret = false;}
		if (!$this->device_type) {error_msg ('Please specify the device type.'); $ret = false;}
		
		return $ret;
	}

	
	/** 
	* [Class Method] Returns access phone numbers according to a filtering criteria
	* @param	array	$filter				Associative array with filtering criteria. Can contain:
	*							- customer_id: Return access phones for this customer only
	* @return	array(AccessPhone)			Array with the matched AccessPhone objects
	*/
    public static function get_access_phones($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_ACCESS_PHONES.' a WHERE ';
		
		if ($filter['customer_id']) $q.= 'a.customer_id='.$filter['customer_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY phone ';
		
		$ids = DB::db_fetch_vector ($q);
		
		foreach ($ids as $id) $ret[] = new AccessPhone ($id);
		
		return $ret;
	}
}

?>
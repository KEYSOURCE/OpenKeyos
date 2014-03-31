<?php

/**
* Stores and manages phone numbers for Internet provider contacts
* 
*/

class ProviderContactPhone extends Base
{
	/** The ID of the phone number
	* @var int */
	var $id = null;
	
	/** The ID of the provider contact
	* @var int */
	var $contact_id = null;
	
	/** The phone number
	* @var string */
	var $phone = '';
	
	/** The type of phone - see $GLOBALS['PHONE_TYPES']
	* @var int */
	var $type = PHONE_TYPE_MOBILE;
	
	/** Commnets about this phone number
	* @var string */
	var $comments = '';
	
	
	/** The database table storing provider contacts phones data
	* @var string */
	var $table = TBL_PROVIDERS_CONTACTS_PHONES;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'contact_id', 'phone', 'type', 'comments');
	
	
	/** 
	* Contructor. Loads an object's values if an object ID is specified 
	* @param	mixed		$id		An object ID
	*/
	function ProviderContactPhone ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}

	
	/** Checks if the information is valid for the phone number */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->phone) {error_msg ('Please specify the phone number.'); $ret = false;}

		return $ret;
	}
	
	
	
	/**
	* [Class Method] Return the provider contacts phones defined in the system according to a specified criteria
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- contact_id: Get the phones for the specified provider contact
	* @return	array(ProviderContractPhone)		Array of ProviderContractPhone objects
	*/
	public static function get_phones ($filter = array ())
	{
		$ret = array();
		
		$q = 'SELECT p.id FROM '.TBL_PROVIDERS_CONTACTS_PHONES.' p WHERE ';
		if ($filter['contact_id']) $q.= 'p.contact_id = '.$filter['contact_id'].' AND ';
		
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		
		$q.= 'ORDER BY p.id';
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new ProviderContactPhone($id);
		
		return $ret;
	}

}

?>
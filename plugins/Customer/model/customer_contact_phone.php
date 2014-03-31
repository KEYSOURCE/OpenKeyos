<?php
/**
* Class for storing phone numbers for customer contacts
*
*/

class_load ('Customer');
class_load ('CustomerContact');

class CustomerContactPhone extends Base
{
	/** Phone ID
	* @var int */
	var $id = null;
	
	/** Contact ID
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
	
	
	/** The database table storing customer contacts phones data
	* @var string */
	var $table = TBL_CUSTOMERS_CONTACTS_PHONES;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'contact_id', 'phone', 'type', 'comments');

	
	/**
	* Constructor, also loads the phone data from the database if an ID is specified
	* @param	int $id		The contact phone ID
	*/
	function CustomerContactPhone ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->contact_id) {error_msg ('Please specify the contact to which this phone belongs to.'); $ret = false;}
		if (!$this->phone) {error_msg ('Please specify the phone number.'); $ret = false;}
		
		return $ret;
	}
	
}

?>
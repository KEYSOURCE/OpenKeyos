<?php

/**
* Class for storing and manipulating user phone numbers
*
*/

class UserPhone extends Base
{
	/** Phone number ID
	* @var int */
	var $id = null;

	/** User ID
	* @var int */
	var $user_id = null;
	
	/** Phone number
	* @var string */
	var $phone = '';
	
	/** Phone type - see $GLOBALS['PHONE_TYPES']
	* @var int */
	var $type = 0;
	
	/** Additional comments
	* @var string */
	var $comment = '';
	
	
	/** The database table storing phone numbers data 
	* @var string */
	var $table = TBL_USERS_PHONES;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'user_id', 'phone', 'type', 'comment');
	
	/**
	* Constructor
	* @param	int $id		The user's id
	*/
	function UserPhone($id = null, $customer_id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
		if ($customer_id) $this->customer_id = $customer_id;
	}

	
	/**
	* Checks if the object data is valid 
	* @todo	Handling error messages through external files
	*/
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->user_id) {error_msg($this->get_string('NEED_USER_PHONE')); $ret = false;} 
		if (!$this->phone) {error_msg($this->get_string('NEED_USER_PHONE')); $ret = false;}
		
		return $ret;
	}
}

?>
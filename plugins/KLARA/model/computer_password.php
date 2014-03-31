<?php

class_load ('Customer');
class_load ('Computer');

/**
* Stores computers or network passwords.
*
* A login/password can be associated with a specific computer. If a computer is not set, then
* it is considered to be a login/password for the entire network, e.g. for the domain admin.
*
* These passwords are also used in networks discoveries settings, for specifying the WMI login/password.
* 
*/
class ComputerPassword extends Base
{
	/** The ID of the object
	* @var int */
	var $id = null;
	
	/** The computer ID - if not specified, then it a global network password for this customer
	* @var int */
	var $computer_id = 0;
	
	/** The customer ID - needed especially when the password is a global one and there is no computer ID
	* @var int */
	var $customer_id = 0;
	
	/** The login name
	* @var string */
	var $login = '';
	
	/** The password
	* @var string */
	var $password = '';
	
	/** Date removed - a non-zero value means the password is no longer in use
	* @var time */
	var $date_removed = 0;
	
	/** Comments about the password
	* @var text */
	var $comments = '';
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_COMPUTERS_PASSWORDS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'computer_id', 'customer_id', 'login', 'password', 'date_removed', 'comments');
	
	
	/** 
	* Contructor. Loads an object's values if an ID is specified 
	* @param	int		$id		An object ID 
	*/
	function ComputerPassword ($id = null)
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
		
		if (!$this->computer_id and !$this->customer_id) {error_msg ('Please specify the customer and/or computer to which this password belongs.'); $ret = false;}
		if (!$this->login) {error_msg ('Please specify the login name.'); $ret = false;}
		
		return $ret;
	}
	
	/** Saves the object data, also copies the customer ID from the computer */
	function save_data ()
	{
		if ($this->computer_id and !$this->customer_id)
		{
			$comp = new Computer ($this->computer_id);
			$this->customer_id = $comp->customer_id;
		}
		parent::save_data ();
	}
	
	
	/** Deletes this passwords, and also removes any reference to it from the discoveries settings */
	function delete ()
	{
		if ($this->id)
		{
			// Remove the references to this password
			$q = 'UPDATE '.TBL_DISCOVERIES_SETTINGS_DETAILS.' SET wmi_login_id=0, disable_wmi=1 WHERE wmi_login_id='.$this->id;
			db::db_query ($q);
			
			// Delete the object itself
			parent::delete ();
		}
	}
	
	/**
	* [Class Method] Returns computers password information based on some criteria
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- customer_id: Return information about passwords on computers for this customer
	*							- computer_id: Return passwords for this computer
	*							- by_customer: The results array will be grouped by customer ID
	*							- by_computer: The results array will be grouped by computer ID
	*							- include_expired: If true, expired passwords will be included in result (default 
	*							  is not to include them)
	* @return	array					Array with the matching password information. If 'by_customer'
	*							is specified, it will be an associative array, with the keys being customer IDs
	*							and the values being arrays of ComputerPassword objects for that customer. 
	*							If 'by_computer' is specified, it will be an associative array, with the keys
	*							being computer IDs and the values being arrays of ComputerPassword objects
	*							for those computers.
	*/
    public static function get_passwords ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT p.id, p.computer_id, p.customer_id ';
		//if ($filter['customer_id']) $q.= ', c.customer_id ';
		$q.= 'FROM '.TBL_COMPUTERS_PASSWORDS.' p ';
		//if ($filter['customer_id']) $q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON p.computer_id=c.id ';
		$q.= 'WHERE ';
		
		if ($filter['customer_id']) $q.= 'p.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['computer_id'])
		{
			if (!$filter['customer_id'])
			{
				// Include also the customer's network passwords
				$customer_id = db::db_fetch_field ('SELECT customer_id FROM '.TBL_COMPUTERS.' WHERE id='.$filter['computer_id'], 'customer_id');
				$q.= '(p.computer_id='.$filter['computer_id'].' OR (p.computer_id=0 AND p.customer_id='.$customer_id.')) AND ';
			}
			else
			{
				$q.= 'p.computer_id='.$filter['computer_id'].' AND ';
			}
		}
		if (!$filter['include_expired']) $q.= 'p.date_removed=0 AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY p.customer_id, p.computer_id, p.login, p.date_removed DESC ';
		
		$ids = db::db_fetch_array ($q);
		foreach ($ids as $id)
		{
			if ($filter['by_customer'])
				$ret[$id->customer_id][] = new ComputerPassword ($id->id);
			elseif ($filter['by_computer'])
				$ret[$id->computer_id][] = new ComputerPassword ($id->id);
			else
				$ret[] = new ComputerPassword ($id->id);
		}

		return $ret;
	}
	
	
	/** 
	* [Class Method] Returns a list of computers that have expired passwords
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- customer_id: Check for this customer only
	* @return	array					Array with the matched computer IDs that have expired passwords
	*/
    public static function get_computers_expired_passwords ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT distinct p.computer_id FROM '.TBL_COMPUTERS_PASSWORDS.' p ';
		if ($filter['customer_id']) $q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON p.computer_id=c.id ';
		$q.= 'WHERE p.date_removed<>0 ';
		
		if ($filter['customer_id']) $q.= 'AND c.customer_id='.$filter['customer_id'].' ';
		
		$ret = db::db_fetch_vector ($q);
		
		return $ret;
	}
}

?>
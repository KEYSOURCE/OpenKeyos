<?php

class_load ('ExchangeInterface');

/**
* Class for storing Exchange connection information for Keyos users
*
* The login name and e-mail are stored in clear. The password is not stored
* in clear, instead we store the MD5 hash needed by HTTP Digest authentication.
*
*/
class UserExchange extends Base
{
	/** User ID
	* @var int */
	var $id = null;
	
	/** The login name for the Exchange system
	* @var string */
	var $exch_login = '';
	
	/** The e-mail address in the Exchange system
	* @var string */
	var $exch_email = '';
	
	/** The hash for Digest authentication (H(A1) = md5(login:realm:password)), generated when the 
	* Exchange login object is created/validated
	* @var string */
	var $exch_ha1 = '';
	
	/** The Base64 encoding of username and password needed for Basic authentication
	* @var string */
	var $exch_basic = '';
	
	/** The exchange password - used only in memory during account validation, never saved to database
	* @var string */
	var $password = '';
	
	
	/** The database table storing user data 
	* @var string */
	var $table = TBL_USERS_EXCHANGE;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'exch_login', 'exch_email', 'exch_ha1', 'exch_basic');
	
	
	/**
	* Constructor, also loads the object data from the database if a user ID is specified
	* @param	int $id		The user's id
	*/
	function UserExchange ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Load object data from an array, optionally including a password - which is never saved to database */
	function load_from_array ($data = array())
	{
		parent::load_from_array ($data);
		
		// This is used only during account info creation or modification
		if ($data['password']) $this->password = $data['password'];
	}
	
	
	/** Checks if the object data is valid. This includes performing a test login on the server */
	function is_valid_data ()
	{
		$ret = true;
		if (!$this->exch_login) {error_msg('Please specify the login name.'); $ret = false;}
		if (!$this->exch_email) {error_msg('Please specify the Exchange e-mail.'); $ret = false;}
		if (!$this->exch_ha1 and !$this->password) {error_msg('Please specify the password'); $ret = false;}
		
		if ($ret)
		{
			// If all is OK so far, test the connection with Exchange
			class_load ('ExchangeInterface');
			if ($this->password)
			{
				// This is a new account or an account for which the password is being modified
				$ex_iface = new ExchangeInterface ($this->exch_login, $this->exch_email, '', '', $this->password);
				if (!$ex_iface->do_authentication())
				{
					$ret = false;
					error_msg ('Login failed, please try again.');
					if ($ex_iface->last_error) error_msg ('Exchange error: '.$ex_iface->last_error);
				}
				else
				{
					// We have a valid login, save the hash
					$this->exch_ha1 = $ex_iface->conn->digest_ha1;
					$this->exch_basic = $ex_iface->conn->basic_ident;
					
				}
			}
			else
			{
				// If we don't have a password, then we have a hash.
				$ex_iface = new ExchangeInterface ($this->exch_login, $this->exch_email, $this->exch_ha1, $this->exch_basic);
				if (!$ex_iface->do_authentication())
				{
					$ret = false;
					error_msg ('Login failed, please try again.');
					if ($ex_iface->last_error) error_msg ('Exchange error: '.$ex_iface->last_error);
				}
				else
				{
					// We have a valid login, save the hash
					$this->exch_ha1 = $ex_iface->conn->digest_ha1;
					$this->exch_basic = $ex_iface->conn->basic_ident;
				}
			}
		}
		
		return $ret;
	}
	
	
	/** Returns an ExchangeInterface object with this user's credentials (Basic or Digest Authentication) */
	function getExchangeInterface ($do_authentication = true)
	{
		$ret = new ExchangeInterface ($this->exch_login, $this->exch_email, $this->exch_ha1, $this->exch_basic);
		if ($do_authentication) $ret->do_authentication ();
		
		return $ret;
		
	}
	
}

?>
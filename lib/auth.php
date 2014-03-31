<?php

/**
* Authentication class
*
* This class allows easy handling of the authentication. 
* 
*/
class Auth extends Base
{

	/** The ID of the current logged in user (if any)
	* @var int */
	var $id = null;
	
	/** The type of the current logged in user (if any)
	* @var int */
	var $type = null;
	
	
	/** Constructor */
	function Auth ()
	{
		$this->load_data();
	}
	

	/**
	* Validates a login request
	*
	* Validates a login request. If valid, it also sets the authentication cookie
	*/
	function validate_login($username = '', $password = '')
	{
		$ret = false;
		if ($username and $password)
		{
			$q = 'SELECT id, type, active FROM '.TBL_USERS.' WHERE ';
			$pass = md5($password);
			$q.= 'login="'. $username . '" and password="'.$pass.'" ';

			$row = DB::db_fetch_row($q);
            if ($row['id'])
			{
				if ($row['active'])
				{
					$this->id = $row['id'];
					$this->type = $row['type'];
					$this->set_login_cookie();
					$ret = $this->id;
				}
				else error_msg ($this->get_sring('SORRY_ACCOUNT_DISABLED'));
			}
			else error_msg ($this->get_string('INVALID_LOGIN'));
		}
		return $ret;
	}
	
	
	/**
	* Checks if there is any logged in user user and sets the object data accordingly
	*/
	function load_data ()
	{
		$this->id = $this->get_uid();
		if ($this->id)
		{
			$q = 'SELECT type FROM '.TBL_USERS.' WHERE id='.$this->id;
			$this->type = $this->db_fetch_field($q, 'type');
		}
		else
		{
			$this->type = null;
		}
	}
	
	
	/**
	* Sets the login cookie. As a precaution, only the user ID is stored in the cookie, not the user type
	*/
	function set_login_cookie ()
	{
		$ret = false;
		if ($this->id)
		{
                        //debug(COOKIE_DOMAIN); die;
			setcookie(COOKIE_USER, $this->id,  (time()+COOKIE_DURATION), '/', '', false);
			//setcookie(COOKIE_USER, $this->id,  (time()+COOKIE_DURATION), '/', 'keyos.local', false);
			$ret = true;
		}
		return $ret;
	}
	
	
	/**
	* Returns (from $_COOKIE) the ID of the current logged in user
	*
	* @return	int the ID of the user or NULL if there is no logged in user
	*/
	function get_uid ()
	{
		$ret = null;
		if (isset($_COOKIE[COOKIE_USER]))
		{
			$ret = $_COOKIE[COOKIE_USER];
			if (!headers_sent())
			{
				// This trick is needed due some strange behaviour in some IE installations and Opera 6.5
				// Without it, sometimes the logout doesn't work
				if ($_REQUEST['op']!='login' and $_REQUEST['op']!='logout')
				{
					setcookie(COOKIE_USER, $this->id,  time()+COOKIE_DURATION, '/', COOKIE_DOMAIN, false);
				}
			}
		}
		return $ret;
	}
	
	
	/**
	* Performs a logout operation, which means un-setting the authentication cookie
	*/
	function logout ()
	{
		setcookie (COOKIE_USER, '', null, '/', COOKIE_DOMAIN, false);
		setcookie (COOKIE_USER, '');
		unset($_COOKIE[COOKIE_USER]);
		$_COOKIE[COOKIE_USER] = 2;
		session_write_close ();
	}
	
	
	/**
	* Retrieves a user's password, sending it by e-mail
	* @param	string	$login			User's login name
	* @param	string	$email			User's e-mail address
	* @return	bool				True or False if the password was found or not
	*/
	function get_password ($login, $email)
	{
		$ret = false;
		
		$q = 'SELECT id FROM '.TBL_USERS.' WHERE login="'.mysql_escape_string ($login).'" AND email="'.mysql_escape_string($email).'"';
		$id = DB::db_fetch_field ($q, 'id');
		
		if ($id)
		{
			$user = new User ($id);
			$ret = true;
			
			$lang_ext = ($user->language != LANG_EN ? '.'.$GLOBALS['LANGUAGE_CODES'][$user->language] : '');
			$tpl = '_classes_templates/user/lost_password.tpl'.$lang_ext;
			$tpl_subject = '_classes_templates/user/lost_password_subject.tpl'.$lang_ext;
				
			$parser = new BaseDisplay ();
			$parser->assign ('user', $user);

			$msg = $parser->fetch ($tpl);
			$subject = $parser->fetch ($tpl_subject);

			$headers = 'From: '.SENDER_NAME.' <'.SENDER_EMAIL.'>'."\n";
			$headers.= 'Date: '.date("D, j M Y G:i:s O")."\n";

			@mail ($user->email, $subject, $msg, $headers);
		}
		
		return $ret;
	}
}

?>
<?php
class_load('User');

/**
* Class for storing and manipulating group information.
*
* A group can be of type USER_TYPE_KEYSOURCE_GROUP, containing only Keysource users, or USER_TYPE_GROUP, 
* which can contain any user.
*
* For convenience and simplified access, the group information are stored in the same table as the users
* details.
*
*/
class Group extends User
{
	/** Group ID
	* @var int */
	var $id = null;

	/** Group name 
	* @var string */
	var $fname = '';
	
	/** The group type - $GLOBALS['USER_TYPES']
	* @var int */
	var $type = USER_TYPE_KEYSOURCE_GROUP;
	
	/** Specifies if the group is active or not 
	* @var boolean */
	var $active = true;
	
	
	/** The list of member user IDs 
	* @var array */
	var $members_list = array ();
	
	/** The list of member user objects 
	* @var array(User) */
	var $members = array ();
	
	
	/** The database table storing user data 
	* @var string */
	var $table = TBL_USERS;
	
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'fname', 'type', 'active', 'login', 'login_password');
	
	
	/**
	* Constructor, also loads the group data from the database if a group ID is specified
	* @param	int $id		The group's id
	*/
	function __construct($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/**
	* Loads the group data, as well as the members details
	*/
	function load_data()
	{
		$ret = false;
		if ($this->id)
		{
			parent::load_data();
			$ret = (!empty($this->id));
			
			if ($this->id)
			{
				// Load group members
				$q = 'SELECT user_id FROM '.TBL_USERS_GROUPS.' g LEFT JOIN '.TBL_USERS.' u ';
				$q.= 'ON g.user_id = u.id WHERE g.group_id='.$this->id.' ';
				$q.= 'ORDER BY u.fname, u.lname ';
				
				$ids = $this->db_fetch_array ($q);

				foreach ($ids as $id)
				{
					$this->members_list[] = $id->user_id;
					$this->members[] = new User ($id->user_id);
				}
			}
		}
		return $ret;
	}
	
	
	/** Saves the group data, including the list of members */
	function save_data ()
	{
		parent::save_data ();
	
		// Ensure consistency of login and login_password fields
		$this->login_password = $this->login.$this->password;
		$this->login = $this->fname;
	
		// Save the group members list
		if ($this->id)
		{
			// Delete the old members list 
			$this->db_query ('DELETE FROM '.TBL_USERS_GROUPS.' WHERE group_id='.$this->id);
			
			// Save the new list
			if (is_array($this->members_list) and count($this->members_list)>0)
			{
				$q = 'INSERT INTO '.TBL_USERS_GROUPS.' (group_id, user_id) VALUES ';
				foreach ($this->members_list as $member_id)
				{
					$q.= '('.$this->id.', '.$member_id.'), ';
				}
				$q = preg_replace ('/\,\s*$/', '', $q);
				$this->db_query ($q);
			}
		}
	}
	
	
	/** Loads the group data from an array, including members (if specified) */
	function load_from_array ($data = array())
	{
		parent::load_from_array ($data);
		
		if (isset($data['members_list']))
		{
			// Load the members list
			$this->members_list = $data['members_list'];
			$this->members = array ();
			
			foreach ($this->members_list as $member_id)
			{
				$this->members[] = new User ($member_id);
			}
		}
	}
	
	
	/** Checks if the group data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		// Ensure consistency of login and login_password fields
		$this->login_password = $this->login.$this->password;
		$this->login = $this->fname;
		
		if (!$this->fname) {error_msg ('Please specify the group name.'); $ret = false;}

		// Check uniqueness of group name
		if ($this->login or $this->fname)
		{
			$q = 'SELECT count(*) as cnt FROM '.TBL_USERS.' WHERE (login="'.mysql_escape_string($this->fname).'" OR fname="'.mysql_escape_string($this->fname).'") ';
			if ($this->id) $q.= 'AND id<>'.$this->id;
			$cnt = $this->db_fetch_field ($q, 'cnt');

			if ($cnt > 0)
			{
				error_msg ('The group name already exists, please choose a different one');
				$ret = false;
			}
		}
		
		// For Keysource groups, make sure they only contain keysource members
		if ($this->type == USER_TYPE_KEYSOURCE_GROUP and is_array ($this->members))
		{
			$found = false;
			for ($i=0; ($i<count($this->members) and !$found); $i++)
			{
				$found = ($this->members[$i]->type != USER_TYPE_KEYSOURCE);
			}
			
			if ($found)
			{
				error_msg ('A Keysource group can only contain Keysource users');
				$ret = false;
			}
		}
		
		return $ret;
	}
	
	
	/** Checks if the group can be deleted */
	function can_delete ()
	{
		$ret = true;
		
		if ($this->id)
		{
			// Check if there are no tickets assigned to this user
			$q = 'SELECT count(*) AS cnt FROM '.TBL_TICKETS.' WHERE owner_id='.$this->id.' OR assigned_id='.$this->id.' OR user_id='.$this->id;
			$cnt_tickets = $this->db_fetch_field ($q);
			$q = 'SELECT count(*) AS cnt FROM '.TBL_TICKETS_DETAILS.' WHERE user_id='.$this->id.' OR assigned_id='.$this->id;
			$cnt_tickets+= $this->db_fetch_field ($q);
			
			if ($cnt_tickets > 0)
			{
				error_msg ('The group can\'t be deleted because there are tickets in which it is involved. Try disabling the group instead.');
				$ret = false;
			}
			
			// Check if the user is not a default notifications recipient
			$q = 'SELECT count(*) as cnt FROM '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' WHERE user_id='.$this->id;
			$cnt = $this->db_fetch_field ($q, 'cnt');
			
			if ($cnt > 0)
			{
				error_msg ('The group can\'t be deleted because it was designated as default recipient for one or more classes of notifications. Try disabling the group instead.');
				$ret = false;
			}
		}
		
		return $ret;
	}

	
	/** Delete the group */
	function delete ()
	{
		if ($this->id)
		{
			// Delete the list of members
			$this->db_query ('DELETE FROM '.TBL_USERS_GROUPS.' WHERE group_id='.$this->id);
			
			parent::delete ();
		}
	}

	
	/**
	* Returns an array with the IDs of the member users.
	* Can be called as object method or as class method - in which case a group ID must be specified
	*/
	function get_member_ids ($group_id = null)
	{
		if (!$group_id)
		{
			$ret = $this->members_list;
		}
		else
		{
			$q = 'SELECT user_id FROM '.TBL_USERS_GROUPS.' WHERE group_id='.$group_id;
			$ret = db::db_fetch_vector ($q);
		}
		return $ret;
	}

	
	/** Returns an associative array with the group members. Keys are user IDs, values are their names */
	function get_members_list ()
	{
		$ret = array ();
		
		if (is_array($this->members))
		{
			for ($i=0; $i<count($this->members); $i++)
			{
				$ret[$this->members[$i]->id] = $this->members[$i]->get_name ();
			}
		}
		
		return $ret;
	}
	
	
	/** Returns the full name for the group */
	function get_name ($prefix = '[G] ')
	{
		$ret = $prefix.trim ($this->fname);
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list with the group IDs and their names
	* @param	array		$filter			Array with the filtering criteria. Can contain:
	*							- active: 1-return only active, 0-return only disable, -1-return all groups
	*							- type: a group type to return (if not specified, returns both types)
	*							- user_id: return only groups where this user is memeber
	* @param	string		$prefix			An optional prefix to add to the group names
	* @return	array					Associative array, the keys are group IDs and the values are group names
	*/
	public static function get_usergroups_list ($filter = array(), $prefix = '[G] ')
	{
		$ret = array ();
		
		// Unless specified expressly, return only active users
		if (!isset($filter['active'])) $filter['active'] = 1;
		elseif ($filter['active'] == -1) unset ($filter['active']);
		elseif (!$filter['active']) $filter['active'] = 0;
		
		$q = 'SELECT u.id, u.fname FROM '.TBL_USERS.' u ';
		if ($filter['user_id']) $q.= 'INNER JOIN '.TBL_USERS_GROUPS.' g ON u.id=g.group_id ';

        $current_user = $GLOBALS['CURRENT_USER'];
		if($current_user)
		{
			if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
			{
				$cc = $current_user->get_assigned_customers_list();
				$q.= ' INNER JOIN '.TBL_CUSTOMERS.' c on u.customer_id=c.id where c.id in (';
				$i=0;
				foreach($cc as $k=>$name)
				{
					if($i!=count($cc)-1) $q.=$k.", ";
					else $q.=$k;
				}
				$q = trim (preg_replace ('/,\s*$/', '', $q));
				$q.=") AND ";
			}
			else {
				$q.= 'WHERE ';
			}
		}
		else {		
			$q.= 'WHERE ';
		}
		
		// If no type is set, make sure we are returning only groups
		if (!isset ($filter['type'])) $filter['type'] = USER_TYPE_KEYSOURCE_GROUP + USER_TYPE_GROUP;
		
		if (isset($filter['type'])) $q.= 'u.type&'.$filter['type'].'=u.type AND ';
		if (isset($filter['active'])) $q.= 'u.active='.$filter['active'].' AND ';
		if (isset($filter['user_id'])) $q.= 'g.user_id='.$filter['user_id'].' AND ';
		
		$q = preg_replace ('/\s*AND\s*$/', ' ', $q);
		$q = preg_replace ('/\s*WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY u.fname ';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns an array of Groups 
	* @param	array		$filter			Array with the filtering criteria
	* @param	integer		$count			(By reference) If set, will store the total number of groups matching the criteria
	* @return	array					Array with the found Group objects
	*/
	function get_groups ($filter = array(), &$count)
	{
		$ret = array ();
		
		// Unless specified expressly, return only active users
		if (!isset($filter['active'])) $filter['active'] = 1;
		elseif ($filter['active'] == -1) unset ($filter['active']);
		elseif (!$filter['active']) $filter['active'] = 0;
		
		$q = 'SELECT id FROM '.TBL_USERS.' WHERE ';
		
		// If no type is set, make sure we are returning only groups
		if (!isset ($filter['type'])) $filter['type'] = USER_TYPE_KEYSOURCE_GROUP + USER_TYPE_GROUP;
		
		if (isset($filter['type'])) $q.= 'type&'.$filter['type'].'=type AND ';
		if (isset($filter['active'])) $q.= 'active='.$filter['active'].' AND ';
		
		$q = preg_replace ('/\s*AND\s*$/', ' ', $q);
		$q = preg_replace ('/\s*WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY fname ';
		
		$groups = db::db_fetch_array ($q);
		
		foreach ($groups as $group)
		{
			$ret[] = new Group ($group->id);
		}
		
		return $ret;
	}

}

?>

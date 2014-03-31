<?php

/**
* Class for managing the various types of activities (tasks) which can be performed in a ticket or project
*
*/

class_load ('ActivityCategory');

class Activity extends Base
{
	/** Activity ID
	* @var int */
	var $id = null;
	
	/** The name of the activity
	* @var string */
	var $name = '';
	
	/** The ERP ID of the activity
	* @var string */
	var $erp_id = '';
	
	/** The ERP name of the activity
	* @var string */
	var $erp_name = '';
	
	/** The ID of the category to which the activity belongs to
	* @var int */
	var $category_id = 0;
	
	/** Specifies if this activity is of type "travel"
	* @var bool */
	var $is_travel = false;
	
	
	/** Associative array with the user-specific ERP codes for this activity.
	* The keys are user IDs and the values are the ERP codes for this activity and this user.
	* These values are stored in TBL_ACTIVITIES_USERS. Note that this array is only loaded
	* on request, with load_users_codes() method.
	* @var array */
	var $users_codes = array ();
	
	
	var $table = TBL_ACTIVITIES;
	var $fields = array ('id', 'name', 'erp_id', 'erp_name', 'category_id', 'is_travel');


	/**
	* Constructor. Also loads a activity information if an ID is provided
	* @param	int	$id		The ID of the activity to load
	*/
	function Activity ($id = null)
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
		if (!$this->name) {error_msg ('Please specify the activity name.'); $ret = false;}
		return $ret;
	}
	
	/** Checks if the activity can be deleted */
	function can_delete ()
	{
		$ret = false;
		if ($this->id)
		{
			$ret = true;
			// Check if there aren't tickes with this activity type
			$q = 'SELECT id FROM '.TBL_TICKETS_DETAILS.' WHERE activity_id='.$this->id.' LIMIT 1';
			if (db::db_fetch_field ($q, 'id'))
			{
				error_msg ('This activity can\'t be deleted, it is already in use.');
				$ret = false;
			}
		}
		return $ret;
	}
	
	
	/** Load the ERP user-specific codes for this activity */
	function load_users_codes ()
	{
		if ($this->id)
		{
			$q = 'SELECT user_id, erp_activity_id FROM '.TBL_ACTIVITIES_USERS.' WHERE activity_id='.$this->id;
			$this->users_codes = db::db_fetch_list ($q);
		}
	}
	
	
	/** Set the set of users codes for this activity */
	function set_users_codes ($users_codes = array ())
	{
		if ($this->id and is_array($users_codes))
		{
			db::db_query ('DELETE FROM '.TBL_ACTIVITIES_USERS.' WHERE activity_id='.$this->id);
			foreach ($users_codes as $user_id => $code)
			{
				$q = 'INSERT INTO '.TBL_ACTIVITIES_USERS.' (activity_id, user_id, erp_activity_id) VALUES (';
				$q.= $this->id.','.$user_id.',"'.mysql_escape_string($code).'")';
				db::db_query ($q);
			}
		}
	}
	
	/**
	* [Class Method] Returns a list of activities
	* @param	array			Associative array with filtering criteria. Can contain:
	*					- category_id: return only activities in this category
	* @return	array			Associative array, they keys are activity IDs and the values are their names
	*/
	public static function get_activities_list ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT a.id, a.name FROM '.TBL_ACTIVITIES.' a ';
		if ($filter['category_id']) $q.= 'WHERE a.category_id='.$filter['category_id'].' ';
		
		$q.= 'ORDER BY name, id';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/** 
	* [Class Method] Returns the activities currently defined in the system
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- category_id: return only activities in this category
	* @return	array(Activity)				Array of Activity objects
	*/
    public static function get_activities ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_ACTIVITIES.' ';
		if ($filter['category_id']) $q.= 'WHERE a.category_id='.$filter['category_id'].' ';
		
		$q.= 'ORDER BY name, id ';
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Activity ($id);
		
		return $ret;
	}

}
?>
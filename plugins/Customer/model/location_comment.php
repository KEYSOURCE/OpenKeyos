<?php

/**
* Class for representing comments for customer locations.
*/

class LocationComment extends Base
{
	/** Comment ID
	* @var int */
	var $id = null;
	
	/** The location to which this comment refers to
	* @var int */
	var $location_id = null;
	
	/** The ID of the user who last modified the location
	* @var int */
	var $user_id = null;
	
	/** The time when the comment was last modified
	* @var timestamp */
	var $updated = 0;
	
	/** The text of the comment
	* @var text */
	var $comments = '';
	
	
	var $table = TBL_LOCATIONS_COMMENTS;
	var $fields = array ('id', 'location_id', 'user_id', 'updated', 'comments');
	
	
	/**
	* Constructor. Also loads the object data if an object ID is provided
	* @param	int	$id		The ID of the comment
	*/
	function LocationComment ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->comments) {error_msg('Please enter some text for the comments.'); $ret = false;}
		if (!$this->user_id) {error_msg('Please specify the author.'); $ret = false;}
		
		return $ret;
	}
	
	/** Save object data, also updating the timestamp */
	function save_data ()
	{
		$this->updated = time ();
		parent::save_data ();
	}
	
	
	/** [Class Method] Get locations comments according to some critera
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- location_id: Return comments for the specified location
	* @return	array(LocationComment)			Array  with the matched location comments.
	*/
	function get_comments ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_LOCATIONS_COMMENTS.' ';
		if ($filter['location_id']) $q.= 'WHERE location_id='.$filter['location_id'].' ';
		$q.= 'ORDER BY updated DESC ';
	
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new LocationComment ($id);
		
		return $ret;
	}
}
?>
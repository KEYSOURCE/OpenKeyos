<?php
class_load ('Ticket');

/**
* Class for managing saved searches for tickets.
*
*/

class KrifsSavedSearch extends Base
{
	/** Search ID
	* @var int */
	var $id = null;
	
	/** The name of this search
	* @var string */
	var $name = '';
	
	/** The ID of the user who created this search
	* @var int */
	var $user_id = null;
	
	/** Array with the search criteria in this saved search. In the
	* database is stored as a serialized array.
	* @var array */
	var $filter = array ();
	
	/** Tells if this is a private search - visible only to its creator
	* @var boolean */
	var $private = true;
	
	
	/** The User object who created this saved search
	* @var User */
	var $user = null;
	
	var $table = TBL_KRIFS_SAVED_SEARCHES;
	var $fields = array ('id', 'name', 'user_id', 'filter', 'private');


	/**
	* Constructor. Also loads the saved search data.
	* @param	int	$id		The ID of the saved search to load
	*/
	function KrifsSavedSearch ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	
	/** Loads the object data */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				// Unpack the filtering criteria array 
				$this->filter = unserialize ($this->filter);
				
				// Load the User object who created this ticket
				if ($this->user_id)
				{
					$this->user = new User ($this->user_id);
				}
			}
		}
	}
	
	
	/** Checks if the saved search data has valid data */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify a name'); $ret = false;}
		
		return $ret;
	}
	
	
	/** Saves the data for the saved search */
	function save_data ()
	{
		// Pack the data for the filtering criteria
		if (!is_array ($this->filter)) $this->filter = array ();
		$this->filter = serialize ($this->filter);
		
		parent::save_data ();
		
		// Un-pack the filtering criteria, so the object can be used normally
		$this->filter = unserialize ($this->filter);
	}
	
	
	/**
	* [Class Method] Returns an associative array of saved searches IDs and names for a user
	* @param	int	$uid			The user for whom to get the saved searches
	* @param	boolean	$favorites		If True, will return only the favorite searches for the specified user.
	*						If False, it will return all the searches available to that user.
	* @param	boolean	$non_favorites_only	(Only if $favorites is False) If True, it will return only searches
	*						which are not on the user's favorites list.
	* @return
	*/
	public static function get_saved_searches_list ($uid, $favorites = true, $non_favorites_only = false)
	{
		$ret = array ();
		
		if ($uid)
		{
			if ($favorites)
			{
				$q = 'SELECT s.id, s.name FROM '.TBL_KRIFS_SAVED_SEARCHES_FAVORITES.' f ';
				$q.= 'INNER JOIN '.TBL_KRIFS_SAVED_SEARCHES.' s ON f.search_id = s.id ';
				$q.= 'WHERE f.user_id='.$uid.' ';
				$q.= 'ORDER BY s.name ';
			}
			else
			{
				$q = 'SELECT DISTINCT s.id, s.name FROM '.TBL_KRIFS_SAVED_SEARCHES.' s ';
				if ($non_favorites_only) $q.= 'LEFT OUTER JOIN '.TBL_KRIFS_SAVED_SEARCHES_FAVORITES.' f ON s.id=f.search_id AND f.user_id = '.$uid.' ';
				$q.= 'WHERE (s.private=0 or (s.private=1 AND s.user_id='.$uid.')) ';
				if ($non_favorites_only) $q.= 'AND f.search_id IS NULL ';
			}
			$ret = db::db_fetch_list ($q);
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns an array of saved searches objects for a user
	* @param	int	$uid			The user for whom to get the saved searches
	* @param	boolean	$favorites		If True, will return only the favorite searches for the specified user.
	*						If False, it will return all the searches available to that user.
	* @param	boolean	$non_favorites_only	(Only if $favorites is False) If True, it will return only searches
	*						which are not on the user's favorites list.
	* @return
	*/
	public static function get_saved_searches ($uid, $favorites = true, $non_favorites_only = false)
	{
		$ret = array ();
		
		if ($uid)
		{
			$list = KrifsSavedSearch::get_saved_searches_list ($uid, $favorites, $non_favorites_only);
			
			foreach ($list as $id => $name)
			{
				$ret[] = new KrifsSavedSearch ($id);
			}
		}
		
		return $ret;
	}
	
	
	/** 
	* [Class Method] Adds a saved search to a user's Favorites. Can be called as object method too.
	* @param	int	$uid			The ID of the user to whos Favorites the search will be added
	* @param	int	$search_id		The ID of the search to add. It is ignored if this is called
	*						as object method, using the object's id instead.
	*/
	function add_to_favorites ($uid, $search_id = null)
	{
		if ($this->id) $search_id = $this->id;
		
		if ($uid and $search_id)
		{
			$q = 'INSERT INTO '.TBL_KRIFS_SAVED_SEARCHES_FAVORITES.' (user_id, search_id) VALUES ';
			$q.= '('.$uid.', '.$search_id.')';
			
			if ($this->id) $this->db_query ($q);
			else db::db_query ($q);
		}
	}
	
	
	/** 
	* [Class Method] Removes a saved search to a user's Favorites. Can be called as object method too.
	* @param	int	$uid			The ID of the user from whos Favorites the search will be removed
	* @param	int	$search_id		The ID of the search to remove. It is ignored if this is called
	*						as object method, using the object's id instead.
	*/
	function remove_from_favorites ($uid, $search_id = null)
	{
		if ($this->id) $search_id = $this->id;
		
		if ($uid and $search_id)
		{
			$q = 'DELETE FROM '.TBL_KRIFS_SAVED_SEARCHES_FAVORITES.' WHERE ';
			$q.= 'user_id='.$uid.' AND search_id='.$search_id.' ';
			
			if ($this->id) $this->db_query ($q);
			else db::db_query ($q);
		}
	}
}
?>
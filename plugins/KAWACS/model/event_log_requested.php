<?php

class_load ('Profile');

ini_set('max_execution_time', '350'); // 0 = no limit.
ini_set('max_input_time', '350'); // 0 = no limit.
ini_set('memory_limit', '500M');

/** Class for representing what computer events log are requested from computers,
* either at the profile or at computer level. Note that the settings at computer
* level override the ones at profile level.
*/

class EventLogRequested extends Base
{
	/** The object ID
	* @var int */
	var $id = null;
	
	/** The profile ID for which this is set, if this is defined at profile level.
	* Either this or computer_id must be set.
	* @var int */
	var $profile_id = 0;
	
	/** The computer ID for which this is set, if this is defined at profile level
	* Either this or profile_id must be set.
	* @var int */
	var $computer_id = 0;
	
	/** The category of events - see $GLOBALS['EVENTS_CATS']
	* @var int */
	var $category_id = 0;
	
	/** The ID of the source for which this is set. If it is not set, then it is
	* considered as being the default level for the specified category.
	* There must be one and only one default level for a category and a profile or computer
	* @var int */
	var $source_id = 0;
	
	/** The types of events to log, stored as sum of EVENTLOG_* constants
	* @var int */
	var $types = EVENTLOG_NO_REPORT;
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_EVENTS_LOG_REQUESTED;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'profile_id', 'computer_id', 'category_id', 'source_id', 'types');
	
	
	/** Class constructor, also loads object data if an ID was specified */
	function EventLogRequested ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	function __destruct()
	{
		if($this->profile_id) unset($this->profile_id);
		if($this->category_id) unset($this->category_id);
		if($this->source_id) unset($this->source_id);
	}
	
	/** Loads the object data from an array. The types can also be passed as values in an array. */
	function load_from_array ($data = array ())
	{
		parent::load_from_array ($data);
		if (isset($data['types']) and is_array($data['types']))
		{
			$this->types = 0;
			foreach ($data['types'] as $k=>$v) $this->types+= $v;
		}
		if ($this->types == 0) $this->types = EVENTLOG_NO_REPORT;
	}
	
	/** Saves the object data */
	function save_data ()
	{
		if ($this->types == 0) $this->types = EVENTLOG_NO_REPORT;
		parent::save_data ();
	}
	
	
	/** Checks if the object data is valid or not
	* @param	bool						$is_default		Specifies if this object should be a default setting or not
	*/
	function is_valid_data ($is_default = false)
	{
		$ret = true;
		
		if (!$this->profile_id and !$this->computer_id) {error_msg('Either the profile or the computer ID must be specified.'); $ret = false;}
		elseif ($this->profile_id and $this->computer_id) {error_msg('Can\'t specify both a profile and a computer ID.'); $ret = false;}
		if (!$this->category_id) {error_msg('Please specify the category.'); $ret = false;}
		
		if (!$is_default and !$this->source_id) {error_msg('Please specify a source.'); $ret = false;}
		elseif ($is_default and $this->source_id) {error_msg('You can\'t specify a source for a default setting.'); $ret = false;}
		
		// Profiles: If this is not a default setting, make sure the types are not the same as the default one for the profile
		if (!$is_default and $this->profile_id and $this->category_id)
		{
			$q = 'SELECT types FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE source_id=0 AND category_id='.$this->category_id.' AND profile_id='.$this->profile_id;
			$def_types = db::db_fetch_field ($q, 'types');
			if ($def_types == 0) $def_types = EVENTLOG_NO_REPORT;
			if ($def_types == $this->types) {error_msg('The selected event types are the same as the default ones, no saving was done.'); $ret = false;}
		}
		
		// Profiles: Check for unicity
		if (!$is_default and $this->profile_id and $this->category_id and $this->source_id)
		{
			$q = 'SELECT id FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE source_id='.$this->source_id.' AND profile_id='.$this->profile_id.' ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			$q.= 'LIMIT 1';
			if (db::db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('You have already defined a setting for the same event source.');
			}
		}
		
		// Computers: If this is not a default setting, make sure the types are not the same as the default ones for the computer
		if (!$is_default and $this->computer_id and $this->category_id)
		{
			$computer_default_types = EventLogRequested::get_computer_default_types ($this->computer_id);
			if ($computer_default_types[$this->category_id]==$this->types)
			{
				$ret = false;
				error_msg('The selected event types are the same as the default ones for this computer, no saving was done.');
			}
		}
		
		// Computers: Check unicity
		if (!$is_default and $this->computer_id and $this->category_id and $this->source_id)
		{
			$q = 'SELECT id FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE source_id='.$this->source_id.' AND computer_id='.$this->computer_id.' ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			$q.= 'LIMIT 1';
			if (db::db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('You have already defined a setting for the same event source.');
			}
		}
		
		return $ret;
	}
	
	/** Returns a string with the events types to report for this object */
	function get_events_types_str ()
	{
		$ret = '';
		if ($this->id)
		{
			foreach ($GLOBALS['EVENTLOG_TYPES'] as $type_id => $type_name)
			{
				if (($this->types & $type_id) == $type_id) $ret.= $type_name.', ';
			}
			$ret = preg_replace ('/\,\s*$/', '', $ret);
		}
		return $ret;
	}
	
	/** [Class Method] Checks if two sets of default settings are the same
	* @param	array							$settings1	Associative array with default settings, keys being 
	*											category IDs and the values being the types of
	*											events to log, specified either as arrays of values
	*											or as sum of EVENTLOG_* constants
	* @param	array							$settings2	Same format as $settings1
	* @return	bool									True or False if the settings are the same or not
	*/
	public static function are_defaults_identical ($settings1, $settings2)
	{
		$ret = false;
		if (is_array($settings1) and is_array($settings2))
		{
			// First do some cleanup
			$settings1 = EventLogRequested::cleanup_default_settings ($settings1);
			$settings2 = EventLogRequested::cleanup_default_settings ($settings2);
			
			// Now compare the settings for the known categories
			$ret = true;
			foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $cat_name)
			{
				if ($settings1[$cat_id] != $settings2[$cat_id]) $ret = false;
			}
			
		}
		return $ret;
	}
	
	/** [Class Method] Given a set of default settings in which the types are specified as arrays,
	* returns an array where the types are sum of EVENTLOG_* constants. Also makes sure that all the 
	* category IDs are set in the keys of the return array.
	* @param	array							$settings	Associative array of types settings, keys being 
	*											category IDs and the values being the types of
	*											events to log, specified either as arrays.
	* @return	array									Associative array of types settings, keys being 
	*											category IDs and the values being sum of 
	*											EVENTLOG_* constants
	*/
	public static function cleanup_default_settings ($types)
	{
		$ret = array ();
		foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $cat_name) $ret[$cat_id] = EVENTLOG_NO_REPORT;
		
		if (is_array($types))
		{
			foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $cat_name)
			{
				if (is_array($types[$cat_id]))
				{
					$tmp = 0;
					foreach ($types[$cat_id] as $k=>$v) $tmp+= $v;
					$ret[$cat_id] = $tmp; 
				}
				else $ret[$cat_id] = $types[$cat_id];
			}
		}
		
		return $ret;
	}
	
	
	
	/** [Class Method] Returns the default event types to record for a profile.
	* @param	int							$profile_id	The ID of the profile
	* @return 	array									Associative array with events categories IDs as 
	*											keys and the values being the types of events
	*											to record (as sum of EVENTLOG_* constants)
	*/
	public static function get_profile_default_types ($profile_id)
	{
		$ret = array ();
		// Set the default values to none if nothing is specified in the database
		foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $name) $ret[$cat_id] = EVENTLOG_NO_REPORT;
		
		if ($profile_id)
		{
			$q = 'SELECT category_id, types FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE profile_id='.$profile_id.' AND source_id=0';
			$data = db::db_fetch_list ($q);
			foreach ($data as $cat_id=>$types) $ret[$cat_id] = $types;
            if($data) $data = null;
		}
		return $ret;
	}
	
	
	/** [Class Method] Returns the profile events types, other than the default ones, if any are set */
	public static function get_profile_events_types ($profile_id)
	{
		$ret = array ();
		if ($profile_id)
		{
			$q = 'SELECT e.id FROM '.TBL_EVENTS_LOG_REQUESTED.' e INNER JOIN '.TBL_EVENTS_SOURCES.' s ON e.source_id=s.id ';
			$q.= 'WHERE e.profile_id='.$profile_id.' AND e.source_id<>0 ORDER BY s.category_id, s.name';
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new EventLogRequested ($id);
            if($ids) $ids = null;
		}
		return $ret;
	}
	
	
	
	/** [Class Method] Sets the default types of events to record for a profile
	* @param	int					$profile_id	The profile ID
	* @param	array					$types		Associative array with the types to record. The keys
	*									are categories IDs and the values are the types of events,
	*									specified either as array with type codes or as sum of
	*									type codes (EVENTLOG_* constants).
	*									NOTE: If a category is not specified in the keys, then
	*									that category will be set to "no reporting"
	*/
	public static function set_profile_default_types ($profile_id, $profile_types)
	{
		if ($profile_id and is_array($profile_types))
		{
			// Cleanup the received types
			$profile_types = EventLogRequested::cleanup_default_settings ($profile_types);
		
			// First, create empty records for the types which don't exist yet in database
			// Also double-check that there is a single default setting for each category for this profile
			foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $cat_name)
			{
				$q = 'SELECT id FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE profile_id='.$profile_id.' AND category_id='.$cat_id.' AND source_id=0';
				$ids = db::db_fetch_vector ($q);
				if (count($ids)==0)
				{
					// No setting previously existed, create a blank record
					$q = 'INSERT INTO '.TBL_EVENTS_LOG_REQUESTED.' (profile_id, category_id, source_id, types) VALUES ';
					$q.= '('.$profile_id.','.$cat_id.',0,'.EVENTLOG_NO_REPORT.')';
					db::db_query ($q);
				}
				elseif (count($ids)>1)
				{
					// There is more than 1 default settings. Delete all but one
					for ($i=1; $i<count($ids); $i++) db::db_query('DELETE FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE id='.$ids[$i]);
				}
                if($ids) $ids = null;
			}
			
			// Now save the settings
			foreach ($profile_types as $cat_id => $types)
			{
				$q = 'UPDATE '.TBL_EVENTS_LOG_REQUESTED.' SET types='.$types.' WHERE profile_id='.$profile_id.' AND category_id='.$cat_id.' ';
				$q.= 'AND source_id=0';
				db::db_query ($q);
			}
			
			// Finally, check if there are any specifically defined sources which have the same types as the profile's default, and delete them
			foreach ($profile_types as $cat_id => $types)
			{
				$q = 'SELECT id FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE profile_id='.$profile_id.' AND source_id<>0 AND category_id='.$cat_id.' ';
				$q.= 'AND types='.$types;
				$ids = db::db_fetch_vector ($q);
				if (count($ids) > 0)
				{
					$msg = 'Note: Some of the existing defined sources ('.count($ids).') had the same events types set, and therefore they were removed for being redundant.';
					foreach ($ids as $id)
					{
						$src = new EventLogRequested ($id);
						$src->delete ();
                        if($src) $src = null;
					}
					error_msg ($msg);
				}
                if($ids) $ids = null;
			}         
            
            if($profile_types) $profile_types = null;   
		}
	}
	
	
	/** [Class Method] Checks if the given computer has specific settings for events log or not (in which case
	* it means the computer is using the profile's settings) */
	public static function computer_has_default_settings ($computer_id)
	{
		$ret = false;
		if ($computer_id)
		{
			$q = 'SELECT id FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE computer_id='.$computer_id.' AND source_id=0 LIMIT 1';
			if (db::db_fetch_field($q,'id')) $ret = true;
		}
		return $ret;
	}
	
	/** [Class Method] Returns the default event types for a computer. If they are not set specifically for 
	* the computer, the ones from the profile are returned. See also the method 
	* @param	int							$computer_id	The ID of the computer
	* @return 	array									Associative array with events categories IDs as 
	*											keys and the values being the types of events
	*											to record (as sum of EVENTLOG_* constants)
	*/
	public static function get_computer_default_types ($computer_id)
	{
		$ret = array ();
		// Set the default values to none if nothing is specified in the database
		foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $name) $ret[$cat_id] = EVENTLOG_NO_REPORT;
		
		if ($computer_id)
		{
			if (EventLogRequested::computer_has_default_settings($computer_id))
			{
				// There are settings specifically for this computer
				$q = 'SELECT category_id, types FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE computer_id='.$computer_id.' AND source_id=0';
				$data = db::db_fetch_list ($q);
				foreach ($data as $cat_id=>$types) $ret[$cat_id] = $types;
                if($data) $data = null;
			}
			else
			{
				// Nothing set specifically for this computer, user the profile's settings
				$profile_id = db::db_fetch_field ('SELECT profile_id FROM '.TBL_COMPUTERS.' WHERE id='.$computer_id, 'profile_id');
				$ret = EventLogRequested::get_profile_default_types ($profile_id);
			}
		}
		return $ret;
	}
	
	
	/** [Class Method] Sets the default types of events to record for a computer
	* @param	int					$computer_id	The computer ID
	* @param	array					$types		Associative array with the types to record. The keys
	*									are categories IDs and the values are the types of events,
	*									specified either as array with type codes or as sum of
	*									type codes (EVENTLOG_* constants).
	*									NOTE: If a category is not specified in the keys, then
	*									that category will be set to "no reporting"
	*/
	public static function set_computer_default_types ($computer_id, $computer_types)
	{
		if ($computer_id and is_array($computer_types))
		{
			// Cleanup the received types
			$computer_types = EventLogRequested::cleanup_default_settings ($computer_types);
			
			// First, create empty records for the types which don't exist yet in database
			// Also double-check that there is a single default setting for each category for this profile
			foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $cat_name)
			{
				$q = 'SELECT id FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE computer_id='.$computer_id.' AND category_id='.$cat_id.' AND source_id=0';
				$ids = db::db_fetch_vector ($q);
				if (count($ids)==0)
				{
					// No setting previously existed, create a blank record
					$q = 'INSERT INTO '.TBL_EVENTS_LOG_REQUESTED.' (computer_id, category_id, source_id, types) VALUES ';
					$q.= '('.$computer_id.','.$cat_id.',0,'.EVENTLOG_NO_REPORT.')';
					db::db_query ($q);
				}
				elseif (count($ids)>1)
				{
					// There is more than 1 default settings. Delete all but one
					for ($i=1; $i<count($ids); $i++) db::db_query('DELETE FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE id='.$ids[$i]);
				}
                if($ids)  $ids = null;
			}
			
			// Now save the settings
			foreach ($computer_types as $cat_id => $types)
			{
				$q = 'UPDATE '.TBL_EVENTS_LOG_REQUESTED.' SET types='.$types.' WHERE computer_id='.$computer_id.' AND category_id='.$cat_id.' ';
				$q.= 'AND source_id=0';
				db::db_query ($q);
			}
			
			// Finally, check if there are any specifically defined sources which have the same types as the computer's default, and delete them
			foreach ($computer_types as $cat_id => $types)
			{
				$q = 'SELECT id FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE computer_id='.$computer_id.' AND source_id<>0 AND category_id='.$cat_id.' ';
				$q.= 'AND types='.$types;
				$ids = db::db_fetch_vector ($q);
				if (count($ids) > 0)
				{
					$msg = 'Note: Some of the existing defined sources ('.count($ids).') had the same events types set, and therefore they were removed for being redundant.';
					foreach ($ids as $id)
					{
						$src = new EventLogRequested ($id);
						$src->delete ();
						if($src) $src=null;
					}
					error_msg ($msg);
				}
                if($ids) $ids = null;
			}
            if($computer_types) $computer_types = null;
		}
	}
	
	/** [Class Method] Returns the computer events types, other than the default ones, if any are set. Note that this does NOT return the
	* sources defined through profile */
	public static function get_computer_events_types ($computer_id)
	{
		$ret = array ();
		if ($computer_id)
		{
			$q = 'SELECT e.id FROM '.TBL_EVENTS_LOG_REQUESTED.' e INNER JOIN '.TBL_EVENTS_SOURCES.' s ON e.source_id=s.id ';
			$q.= 'WHERE e.computer_id='.$computer_id.' AND e.source_id<>0 ORDER BY s.category_id, s.name';
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new EventLogRequested ($id);
            if($ids) $ids = null;
		}
		return $ret;
	}
	
	/** [Class Method] Removes all computer-specific settings, leaving it to use the profile's settings */
	public static function remove_computer_settings ($computer_id)
	{
		if ($computer_id)
		{
			db::db_query ('DELETE FROM '.TBL_EVENTS_LOG_REQUESTED.' WHERE computer_id='.$computer_id);
		}
	}
	
	
	
	/********************************************************/
	/* Sources management					*/
	/********************************************************/
	
	/** [Class Method] Checks if the specified event source exists in database and, if not, adds it 
	* @param	string						$name		The name of the event source
	* @param	int						$category_id	The ID of the category for the event source - see $GLOBALS['EVENTS_CATS']
	* @param	int						$computer_id	The ID of the computer reporting the source
	* @return	boolean								True or False if the source existed or not
	*/
	public static function check_events_source ($name, $category_id, $computer_id)
	{
		$ret = true;
		if ($name and $category_id and $computer_id)
		{
			$name = db::db_escape(trim($name));
			$q = 'SELECT id FROM '.TBL_EVENTS_SOURCES.' WHERE category_id='.$category_id.' AND name="'.$name.'" LIMIT 1';
			if (!db::db_fetch_field($q, 'id'))
			{
				// The source does not exist, add it
				$q = 'INSERT INTO '.TBL_EVENTS_SOURCES.' (category_id, name, reported_first, reported_first_computer_id) VALUES ';
				$q.= '('.$category_id.',"'.$name.'",'.time().','.$computer_id.')';
				db::db_query ($q);
				$ret = false;
			}
		}
		return $ret;
	}
	
	/** [Class Method] Checks if a specified source name exists in the specified list. Search is done case-insensitive,
	* that's way it should be used instead of array_search()
	* @param	array						$sources_list	(By Reference) The list with the sources, in extended format,
	*										as returned by a call to get_events_sources_list_extended() method
	* @param	int						$cat_id		The ID of the category in which to search
	* @param	string						$source		The name of the source to locate
	* @return	int								The ID of the found source, or -1 if it was not found
	*/
	public static function get_source_id ($sources_list, $cat_id, $source)
	{
		$ret = -1;
		
		if(!is_numeric($source))
		{
			$source = strtolower($source);
		}
		//$source = db::db_fetch_tolower($source);
		if (is_array($sources_list[$cat_id]))		
		{
			/*
			foreach ($sources_list[$cat_id] as $id => $name)
			{
				if(!is_numeric($name))
				{
					if(isset($name) and isset($source) and (strlen($name) == strlen($source)))
					{
						$name = strtolower($name);
						//$name = db::db_fetch_tolower($name);
						if ($name===$source)
						{
							$ret = $id;						
							break;
						}
					}
				}
				else
				{
					if($name===$source)
					{
						$ret=$id;
						break;
					}
				}
			}
			*/			
			//$key = array_search($source, $sources_list[$cat_id]);
			//if($key) $ret=$key;
            $source = db::db_escape($source);
            $query = "select id from ".TBL_EVENTS_SOURCES." where category_id=".$cat_id." AND MATCH(name) against ('".$source."' in boolean mode)";
            $key = db::db_fetch_field($query, 'id');
            if($key) $ret = $key;

		}
		//if(isset($sources_list)) unset($sources_list);
        if($sources_list) $sources_list = null;
		return $ret;
	}
	
	/** [Class Method] Returns a list with all the events sources defined in the system
	* @param	int						$cat_id		(Optional) Return only sources for this category
	* @return	array								Associative array, keys being source IDs and values being sources names 
	*/
	public static function get_events_sources_list ($cat_id=null)
	{
		$ret = array();
        /*
		if ($cat_id) 
		{
			$q = "select count(id) as cnt_no from ".TBL_EVENTS_SOURCES." WHERE category_id=".$cat_id;
			$cnt_no = db::db_fetch_field($q, 'cnt_no');
			$last_cnt = 0;
			$exact = (int)($cnt_no/1000);
			$rest = (int)($cnt_no%1000);
			for($i=0; $i<$exact;$i++){
				$start = $i*1000;
				$limit = ($i+1)*1000;
				$q = 'SELECT id, lower(name) as name FROM '.TBL_EVENTS_SOURCES.' WHERE category_id='.$cat_id.' ORDER BY name LIMIT '.$start.','.$limit;
				$rl = db::db_fetch_list($q);
				$ret = array_merge($ret, $rl);
			}
			$start = $i*1000;
			$limit = $start+$rest;
			$q = 'SELECT id, lower(name) as name FROM '.TBL_EVENTS_SOURCES.' WHERE category_id='.$cat_id.' ORDER BY name LIMIT '.$start.','.$limit;
			$rl = db::db_fetch_list($q);
			$ret = array_merge($ret, $rl);
			
									
			return $ret;

			//return db::db_fetch_list ('SELECT id, lower(name) as name FROM '.TBL_EVENTS_SOURCES.' WHERE category_id='.$cat_id.' ORDER BY name ');
		}
		else{
			$q = "select count(id) as cnt_no from ".TBL_EVENTS_SOURCES;
			$cnt_no = db::db_fetch_field($q, 'cnt_no');
			$last_cnt = 0;
			$exact = (int)($cnt_no/1000);
			$rest = (int)($cnt_no%1000);
			for($i=0; $i<$exact;$i++){
				$start = $i*1000;
				$limit = ($i+1)*1000;
				$q = 'SELECT id, lower(name) as name FROM '.TBL_EVENTS_SOURCES.' ORDER BY name LIMIT '.$start.','.$limit;
				$rl = db::db_fetch_list($q);
				$ret = array_merge($ret, $rl);
			}
			$start = $i*1000;
			$limit = $start+$rest;
			$q = 'SELECT id, lower(name) as name FROM '.TBL_EVENTS_SOURCES.' ORDER BY name LIMIT '.$start.','.$limit;
			$rl = db::db_fetch_list($q);
			$ret = array_merge($ret, $rl);
			
									
			return $ret;

			//return db::db_fetch_list ('SELECT id, lower(name) as name FROM '.TBL_EVENTS_SOURCES.' ORDER BY name ');
		}
        */
        return $ret;
	}
	
	/** [Class Method] Return a list with all the events sources defined in the system, grouped by category
	* @return	array								Associative array, the keys being category IDs and 
	*										the values being associative arrays with source IDs as keys and
	*										sources names as values.
	*/
	public static function get_events_sources_list_extended ()
	{
		$ret = array ();
		foreach ($GLOBALS['EVENTS_CATS'] as $cat_id => $cat_name)
		{
			$ret[$cat_id] = EventLogRequested::get_events_sources_list ($cat_id);
		}
		return $ret;
	}
}

?>
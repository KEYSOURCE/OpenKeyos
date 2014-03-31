<?php

/**
* Class for representing the items included in a monitor profile.
*
* These objects specify what items should be collected for a profile, how
* often should they be collected and what data should be logged.
* 
*/
class MonitorProfileItem extends Base
{
	/** The profile ID
	* @var int */
	var $profile_id = null;
	
	/** The item ID 
	* @var int */
	var $item_id = null;
	
	/** The frequency of updates for this item in this profile (in minutes)
	* @var int */
	var $update_interval = 0;
	
	/** What kind of logging to be made for this item in this profile
	* @var int */
	var $log_type = MONITOR_LOG_NONE;
	
	

	/** The fields that compose the primary key 
	* @var array */
	var $primary_key = array ('profile_id', 'item_id');
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_MONITOR_PROFILES_ITEMS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('profile_id', 'item_id', 'update_interval', 'log_type');
	
	
	
	/** Contructor. Loads the profile item definition */
	function MonitorProfileItem ($profile_id = null, $item_id = null)
	{
		if ($profile_id) $this->profile_id = $profile_id;
		if ($item_id) $this->item_id = $item_id;
		
		if ($this->profile_id and $this->item_id)
		{
			$this->load_data();
		}
	}
	
	
	/** Loads the object data, if the profile and item IDs are specified. Also loads the monitor item definition */
	function load_data ()
	{
		if ($this->profile_id and $this->item_id)
		{
			parent::load_data();
			$this->itemdef = new MonitorItem($this->item_id);
		}
		
	}
	
	function __destruct()
	{
	        if(isset($this->itemdef)) unset($this->itemdef);
	}
	
	/** 
	* Saves the data about this monitor profile item. In case the logging is set to "No logging",
	* it will also delete any previously logged data.
	*/
	function save_data ()
	{
		parent::save_data ();
		
		if ($this->profile_id and $this->item_id)
		{
			if ($this->log_type == MONITOR_LOG_NONE)
			{
				// Search for computers using this monitoring profile, so we can delete
				// any previously logged data that there might be.
				$q_comps = 'SELECT id FROM '.TBL_COMPUTERS.' WHERE profile_id='.$this->profile_id;
				$comps = $this->db_fetch_array ($q_comps);
				
				foreach ($comps as $comp)
				{
					$q_del = 'DELETE FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE ';
					$q_del.= 'computer_id='.$comp->id.' AND item_id='.$this->item_id;
					$this->db_query ($q_del);
				}
			}
		}
	}
	
	
	/**
	* [Class Method] Deletes all the items associated with the specified profile 
	* @param	int	$profile_id		The ID of the profile for which to delete the data
	*/
	function delete_profile_items ($profile_id = null)
	{
		if ($profile_id)
		{
			db::db_query('DELETE FROM '.TBL_MONITOR_PROFILES_ITEMS.' WHERE profile_id='.$profile_id);
		}
	}
	
}

?>
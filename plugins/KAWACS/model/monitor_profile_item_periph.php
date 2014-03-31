<?php

/**
* Class for representing the items included in a peripherals monitor profile
* 
* This class is similar with MonitorProfileItem, except that it refers to peripherals
* monitor profiles instead of computers monitor profiles.
*
*/

class_load ('MonitorProfileItem');

class MonitorProfileItemPeriph extends MonitorProfileItem
{
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_MONITOR_PROFILES_ITEMS_PERIPH;
	
	
	/** Contructor. Loads the profile item definition */
	function MonitorProfileItemPeriph ($profile_id = null, $item_id = null)
	{
		if ($profile_id) $this->profile_id = $profile_id;
		if ($item_id) $this->item_id = $item_id;
		
		if ($this->profile_id and $this->item_id)
		{
			$this->load_data();
		}
	}
	
	
	/** 
	* Saves the data about this monitor profile item. In case the logging is set to "No logging",
	* it will also delete any previously logged data.
	*/
	function save_data ()
	{
		Base::save_data ();
		
		if ($this->profile_id and $this->item_id)
		{
			if ($this->log_type == MONITOR_LOG_NONE)
			{
				// Search for peripherals using this monitoring profile, so we can delete
				// any previously logged data that there might be
				$q_periphs = 'SELECT id FROM '.TBL_PERIPHERALS.' WHERE profile_id='.$this->profile_id;
				$ids = $this->db_fetch_vector ($q_periphs);
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
			DB::db_query('DELETE FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$profile_id);
		}
	}
	
}

?>
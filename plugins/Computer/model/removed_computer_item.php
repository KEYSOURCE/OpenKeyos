<?php

class_load ('MonitorItem');

/**
* Class for storing and manipulating information about computer monitor items - for removed computers
*
*/

class RemovedComputerItem extends ComputerItem
{
	/** Computer ID
	* @var int */
	var $computer_id = null;
	
	/** Monitor item ID
	* @var int */
	var $item_id = null;
	
	/** True or false if to sort or not the values. Default is True
	* @var bool */
	var $sort_vals = true;
	
	
	/** Stores the reported values */
	var $val = array();
	
	var $fld_short_names = array();
	var $fld_names = array();
	
	/** Item Definition
	* @var MonitorItem */
	var $itemdef = null;
	
	
	/** The databas table storing customer data 
	* @var string */
	var $table = TBL_REMOVED_COMPUTERS_ITEMS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('computer_id', 'item_id');

	
	/**
	* Constructor, also loads the computer monitor item data from the database if IDs are specified
	* @param	int $computer_id		The computer id
	* @param	int $item_id			The monitor item id
	*/
	function RemovedComputerItem ($computer_id = null, $item_id = null, $sort_vals = true)
	{
		if ($computer_id) $this->computer_id = $computer_id;
		if ($item_id) $this->item_id = $item_id;
		$this->sort_vals = $sort_vals;
		if ($this->computer_id and $this->item_id)
		{
			$this->load_data();
		}
	}
	
		
	/** Loads the computer item data from the database */
	function load_data () {return parent::load_data ();}
	
	
	/** Sorts the values, if a "main field" was specified for the monitoring item */
	// XXXX Need to sort descending for dates
	function sort_values () {return parent::sort_values ();}
	
	
	/** Not needed for this class */
	function load_from_log () {return false;}
	
	/** Saves the data about this item into the database */
	function save_data () {return parent::save_data ();}
	
	
	/** For items with multiple values, saves a single value, specified by its nrc. It is assumed that the nrc already exists in database.
	Note that this updates only the "value" field in the database */
	function save_single_value ($nrc) {return parent::save_single_value ($nrc);}
	
	
	/**
	* Returns a formatted value according to its specified type
	* @param	int	$idx		The index from $this->val for which the display value should be composed
	* @param	int	$item_id	If this item is a structure, represents the sub-item ID for which the value should be returned
	* @return	string			The formatted value
	*/
	function get_formatted_value ($idx, $item_id = null) {return parent::get_formatted_value ($idx, $item_id);}
	
	
	/** Returns the index in $this->val for the value with the specified nrc. This is needed if the values have 
	* been sorted and the indexes in the array do not match anymore the nrc field from the database */
	function get_idx_for_nrc ($nrc) {return parent::get_idx_for_nrc ($nrc);}
}

?>
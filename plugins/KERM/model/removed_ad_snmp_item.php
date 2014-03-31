<?php

class_load ('MonitorItem');
class_load ('AD_SNMP_Item');

/**
* Class for storing and manipulating information about monitor items collected via SNMP for removed AD Printers
*
*/

class RemovedAD_SNMP_Item extends AD_SNMP_Item
{
	/** The databas table storing objects data 
	* @var string */
	var $table = TBL_REMOVED_PERIPHERALS_ITEMS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array();

	
	/**
	* Constructor, also loads the object data if the IDs are specified
	* @param	int $obj_id		The object ID
	* @param	int $item_id		The monitor item ID
	*/
	function RemovedAD_SNMP_Item ($obj_id = null, $item_id = null)
	{
		if ($obj_id) $this->obj_id = $obj_id;
		if ($item_id) $this->item_id = $item_id;
		if ($this->obj_id and $this->item_id)
		{
			$this->load_data();
		}
	}
	
	/** Loads the object data from the database */
	function load_data () {return parent::load_data();}
	
	/**
	* Returns a formatted value according to its specified type
	* @param	int	$idx		The index from $this->val for which the display value should be composed
	* @param	int	$item_id	If this item is a structure, represents the sub-item ID for which the value should be returned
	* @return	string			The formatted value
	*/
	function get_formatted_value ($idx, $item_id = null) {return parent::get_formatted_value ($idx, $item_id);}
	
	/** Returns the index in $this->val for the value with the specified nrc. This is needed if the values have 
	* been sorted and the indexes in the array do not match anymore the nrc field from the database */
	function get_idx_for_nrc ($nrc) {return parent::get_idx_for_nrc($nrc);}
}

?>
<?php
class_load ('MonitorItemAbstraction');

/**
* Class for representing AD groups
*/

class AD_Group extends MonitorItemAbstraction
{
	var $item_id = 1032;
	var $computer_id = null;
	var $nrc = 0;

	
	/** Constructor, also loads the object data if the computer ID and $nrc are passed */
	function AD_Group ($computer_id = null, $nrc = 0)
	{
		if ($computer_id)
		{
			$this->computer_id = $computer_id;
			$this->nrc = $nrc;
			$this->load_data ();
		}
	}
	
	
	/** [Class Method] Returns an associative array of AD groups */
	function get_ad_groups_list ($filter = array())
	{
		return self::get_list (138, $filter);
	}
	
	
	/** [Class Method] Returns and array of AD_Group objects according to the specified criteria */
	public static function get_ad_groups ($filter = array())
	{
		$ret = array ();
		
		$ids = self::get_list(138, $filter);

		foreach ($ids as $key => $val)
		{
			list ($computer_id, $item_id, $nrc) = split ('_', $key);
			$ret[] = new AD_Group ($computer_id, $nrc);
		}
		
		return $ret;
	}
}

?>
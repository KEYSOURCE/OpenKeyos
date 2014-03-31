<?php

class_load ('MonitorItem');

/**
* Class for storing and manipulating information about monitor items collected via SNMP for AD Printers
*
*/

class AD_SNMP_Item extends Base
{
	/** AD object numeric ID
	* @var int */
	var $obj_id = null;
	
	/** Monitor item ID
	* @var int */
	var $item_id = null;
	
	/** Stores the reported values */
	var $val = array();
	
	var $fld_short_names = array();
	var $fld_names = array();
	
	/** Item Definition
	* @var MonitorItem */
	var $itemdef = null;
	
	
	/** The databas table storing objects data 
	* @var string */
	var $table = TBL_PERIPHERALS_ITEMS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array();

	
	/**
	* Constructor, also loads the object data if the IDs are specified
	* @param	int $obj_id		The object ID
	* @param	int $item_id		The monitor item ID
	*/
	function AD_SNMP_Item ($obj_id = null, $item_id = null)
	{
		if ($obj_id) $this->obj_id = $obj_id;
		if ($item_id) $this->item_id = $item_id;
		if ($this->obj_id and $this->item_id)
		{
			$this->load_data();
		}
	}
	
		
	/** Loads the object data from the database */
	function load_data ()
	{
		$ret = false;
		if ($this->obj_id and $this->item_id)
		{
			$this->itemdef = new MonitorItem($this->item_id);
			
			if (!empty($this->itemdef->struct_fields))
			{
				foreach ($this->itemdef->struct_fields as $fld)
				{
					$this->fld_names[$fld->id] = $fld->name;
					$this->fld_short_names[$fld->id] = $fld->short_name;
				}
			}
			else
			{
				$this->fld_names[] = $this->itemdef->name;
				$this->fld_short_names[] = $this->itemdef->short_name;
			}
			
			$q = 'SELECT * FROM '.$this->table.' WHERE obj_id='.$this->obj_id.' AND obj_class='.SNMP_OBJ_CLASS_AD_PRINTER.' ';
			$q.= 'AND item_id = '.$this->item_id.' ORDER BY nrc, field_id';
			$vals = db::db_fetch_array($q);
			
			foreach ($vals as $val)
			{
				$this->reported = $val->reported;
				if (!$val->field_id)
				{
                    if(! $this->val[$val->nrc]) $this->val[$val->nrc] = new StdClass;
					$this->val[$val->nrc]->value = $val->value;
					$this->val[$val->nrc]->updated = $val->reported;
					$this->val[$val->nrc]->nrc = $val->nrc;
				}
				else
				{
					// Structure
                    if(! $this->val[$val->nrc]) $this->val[$val->nrc] = new StdClass;
					$this->val[$val->nrc]->value[$val->field_id] = $val->value;
					$this->val[$val->nrc]->updated = $val->reported;
					$this->val[$val->nrc]->nrc = $val->nrc;
				}
			}
		}
		return $ret;
	}
	
	/**
	* Returns a formatted value according to its specified type
	* @param	int	$idx		The index from $this->val for which the display value should be composed
	* @param	int	$item_id	If this item is a structure, represents the sub-item ID for which the value should be returned
	* @return	string			The formatted value
	*/
	function get_formatted_value ($idx, $item_id = null)
	{
		if (isset($this->val[$idx]))
		{
			$date_show_hour = false;
			$date_show_second = false;
			$snmp_vals_map = array ();
            $list_type = null;
			
			if ($item_id)
			{
				// This is part of a structure. Check first if this is a SNMP item which has a values map attached
				
				$type = 0;
				for ($i=0,$i_max=count($this->itemdef->struct_fields); $i<$i_max; $i++)
				{
					if ($this->itemdef->struct_fields[$i]->id == $item_id)
					{
						$type = $this->itemdef->struct_fields[$i]->type;
						$list_type = $this->itemdef->struct_fields[$i]->list_type;
						$date_show_hour = $this->itemdef->struct_fields[$i]->date_show_hour;
						$date_show_second = $this->itemdef->struct_fields[$i]->date_show_second;
						
						if ($this->itemdef->is_snmp) $snmp_vals_map = $this->itemdef->struct_fields[$i]->snmp_oid_vals;
					}
				}
				$value = $this->val[$idx]->value[$item_id];
			}
			else
			{
				// This is a single item
				$type = $this->itemdef->type;
				$value = $this->val[$idx]->value;
				$list_type = $this->itemdef->list_type;
				$date_show_hour = $this->itemdef->date_show_hour;
				$date_show_second = $this->itemdef->date_show_second;
				
				if ($this->itemdef->is_snmp) $snmp_vals_map = $this->itemdef->snmp_oid_vals;
			}
			
			if (count($snmp_vals_map) == 0)
			{
				// There are no SNMP mappings for this
				switch ($type)
				{
					case MONITOR_TYPE_INT:
						$ret = number_format ($value, 0);
						break;
					case MONITOR_TYPE_LIST:
						$ret = $GLOBALS['AVAILABLE_ITEMS_LISTS'][$list_type][$value];
						break;
					case MONITOR_TYPE_FLOAT:
						$ret = number_format ($value, 2);
						break;
					case MONITOR_TYPE_MEMORY:
						$ret = get_memory_string ($value * 1024); // SNMP items report the memory in KB, not B
						break;
					case MONITOR_TYPE_TEXT;
						$ret = nl2br(htmlentities($value));
						break;
					case MONITOR_TYPE_DATE:
						if (is_numeric($value) and $value>0)
						{
							if ($date_show_second) $ret = date (DATE_TIME_FORMAT_SECOND, $value);
							elseif ($date_show_hour) $ret = date (DATE_TIME_FORMAT, $value);
							else $ret = date (DATE_FORMAT, $value);
						}
	
						break;
					case MONITOR_TYPE_FILE:
                        $lnk = get_link('kawacs', 'open_item_file', array('computer_id'=>$this->computer_id, 'item_id'=>$this->item_id, 'field_id'=>$item_id));
						$ret = '<a href="' . $lnk . '">Open</a>';
						break;
					
					default:
						$ret = $value;
				}
			}
			else
			{
				// Use the SNMP mappings
				if (isset($snmp_vals_map[$value])) $ret = $snmp_vals_map[$value];
				else $ret = $value;
			}
		}
		return $ret;
	}
	
	
	/** Returns the index in $this->val for the value with the specified nrc. This is needed if the values have 
	* been sorted and the indexes in the array do not match anymore the nrc field from the database */
	function get_idx_for_nrc ($nrc)
	{
		$ret = -1;
		foreach ($this->val as $idx => $val)
        {
            if ($val->nrc==$nrc)
            {
                $ret = $idx; break;
            }
        }
		return $ret;
	}
}

?>
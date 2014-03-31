<?php

class_load ('MonitorItem');

/**
* Class for storing and manipulating information about computer monitor items
*
*/

class ComputerItem extends Base
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
	var $table = TBL_COMPUTERS_ITEMS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('computer_id', 'item_id');

	
	/**
	* Constructor, also loads the computer monitor item data from the database if IDs are specified
	* @param	int $computer_id		The computer id
	* @param	int $item_id		The monitor item id
	*/
	function ComputerItem ($computer_id = null, $item_id = null, $sort_vals = true)
	{
		if ($computer_id) $this->computer_id = $computer_id;
		if ($item_id) $this->item_id = $item_id;
		$this->sort_vals = $sort_vals;
		if ($this->computer_id and $this->item_id)
		{
			$this->load_data();
		}
	}
	
	function __destruct()
	{
		if($this->itemdef) unset($this->itemdef);
		if(isset($this->val)) unset($this->val);
		if(isset($this->fld_short_names)) unset($this->fld_short_names);
		if(isset($this->fld_names)) unset($this->fld_names);
	}
		
	/** Loads the computer item data from the database */
	function load_data ()
	{
		$ret = false;
		if ($this->computer_id and $this->item_id)
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
			
			$q = 'SELECT * FROM '.$this->table.' WHERE computer_id='.$this->computer_id.' AND item_id = '.$this->item_id.' ORDER BY ';
			if ($this->item_id==EVENTS_ITEM_ID) $q.= 'nrc DESC, field_id '; // For events log, we need the reverse order of nrc, to get them ordered by event date
			else $q.= 'nrc, field_id ';
			
			///XXX Victor need to remove - debugging purposes only
			//debug($q);
			
			$vals = $this->db_fetch_array($q); 


			foreach ($vals as $val)
			{
				$this->reported = $val->reported;
				if (!$val->field_id)
				{
                    if(!$this->val[$val->nrc]) $this->val[$val->nrc] = new StdClass;
					$this->val[$val->nrc]->value = $val->value;
					$this->val[$val->nrc]->updated = $val->reported;
					$this->val[$val->nrc]->nrc = $val->nrc;
				}
				else
				{
					// Structure
					//if (mb_detect_encoding($val->value) == 'UTF-8') $val->value = utf8_decode($val->value);
                    if(!$this->val[$val->nrc]) $this->val[$val->nrc] = new StdClass;
                    $this->val[$val->nrc]->value[$val->field_id] = $val->value;
					$this->val[$val->nrc]->updated = $val->reported;
					$this->val[$val->nrc]->nrc = $val->nrc;
				}
			}
            
            $vals = null;
			
			if ($this->sort_vals and is_array($this->val) and count($this->val)>1)
			{
				$this->sort_values ();
			}
			
		}
		return $ret;
	}
	
	
	/** Sorts the values, if a "main field" was specified for the monitoring item */
	// XXXX Need to sort descending for dates
	function sort_values ()
	{
		if ($this->itemdef->main_field_id)
		{
		
			// Determine the type of field used for sorting
			if ($this->itemdef->type == MONITOR_TYPE_STRUCT)
			{
				$sort_type = -1;
				for ($i=0; $i<count($this->itemdef) and $sort_type<0; $i++)
				{
					if ($this->itemdef->struct_fields[$i]->id==$this->itemdef->main_field_id) $sort_type = $this->itemdef->struct_fields[$i]->type;
				}
			}
			else $sort_type = $this->itemdef->type;
			
			switch ($sort_type)
			{
				case MONITOR_TYPE_INT:
				case MONITOR_TYPE_FLOAT:
				case MONITOR_TYPE_MEMORY;
				case MONITOR_TYPE_LIST: $sort_func = 'cmp_vals_numeric'; break;
				case MONITOR_TYPE_DATE: $sort_func = 'cmp_vals_date'; break;
				default: $sort_func = 'cmp_vals'; break;
			}
			if ($this->itemdef->type == MONITOR_TYPE_STRUCT) $sort_func.= '_struct';
			
			usort ($this->val, array($this, $sort_func));
		}
	}
	
	function cmp_vals_numeric_struct ($a, $b)
	{
		$ret = 0;
		$val_a = $a->value[$this->itemdef->main_field_id];
		$val_b = $b->value[$this->itemdef->main_field_id];
		if ($val_a > $val_b) $ret = 1;
		elseif ($val_a < $val_b) $ret = -1;
		return $ret;
	}
	
	function cmp_vals_numeric ($a, $b)
	{
		$ret = 0;
		$val_a = $a->value;
		$val_b = $b->value;
		if ($val_a > $val_b) $ret = 1;
		elseif ($val_a < $val_b) $ret = -1;
		return $ret;
	}
	
	function cmp_vals_date_struct ($a, $b)
	{
		$ret = 0;
		$val_a = $a->value[$this->itemdef->main_field_id];
		$val_b = $b->value[$this->itemdef->main_field_id];
		if ($val_a < $val_b) $ret = 1;		// Dates are sorted in reverse order
		elseif ($val_a > $val_b) $ret = -1;
		return $ret;
	}
	
	function cmp_vals_date ($a, $b)
	{
		$ret = 0;
		$val_a = $a->value;
		$val_b = $b->value;
		if ($val_a < $val_b) $ret = 1;		// Dates are sorted in reverse order
		elseif ($val_a > $val_b) $ret = -1;
		return $ret;
	}
	
	function cmp_vals_struct ($a, $b)
	{
		$ret = 0;
		$val_a = $a->value[$this->itemdef->main_field_id];
		$val_b = $b->value[$this->itemdef->main_field_id];
		if (is_string($val_a)) $val_a = strtolower($val_a);
		if (is_string($val_b)) $val_b = strtolower($val_b);
	
		if ($val_a > $val_b) $ret = 1;
		elseif ($val_a < $val_b) $ret = -1;
		
		return $ret;
	}
	
	function cmp_vals ($a, $b)
	{
		$ret = 0;
		$val_a = $a->value;
		$val_b = $b->value;
		if (is_string($val_a)) $val_a = strtolower($val_a);
		if (is_string($val_b)) $val_b = strtolower($val_b);
	
		if ($val_a > $val_b) $ret = 1;
		elseif ($val_a < $val_b) $ret = -1;
		return $ret;
	}
	
	
	/**
	* Loads the object with data from a log item
	* @param	int	$reported		The time when the data was reported
	* @param	string	$log_table_suffix	The suffix of the log table to be used, in the
	*						form YYYY_DD. If not specified, the current log
	*						table is used.
	*/
	function load_from_log ($reported, $log_table_suffix = '')
	{
		$this->val = array();
		if ($reported and $this->computer_id and $this->item_id)
		{
			$tbl = ($log_table_suffix ? TBL_COMPUTERS_ITEMS_LOG.'_'.$log_table_suffix : TBL_COMPUTERS_ITEMS_LOG);
		
			$q = 'SELECT * FROM '.$tbl.' WHERE ';
			$q.= 'computer_id='.$this->computer_id.' AND item_id='.$this->item_id.' ';
			$q.= 'AND reported='.$reported.' ';
			$q.= 'ORDER BY nrc, field_id';
		
			$vals = $this->db_fetch_array($q);
			
			foreach ($vals as $val)
			{
				$this->reported = $val->reported;
				if (!$val->field_id)
				{
					$this->val[$val->nrc]->value = $val->value;
					$this->val[$val->nrc]->updated = $val->reported;
				}
				else
				{
					// Structure
					$this->val[$val->nrc]->value[$val->field_id] = $val->value;
					$this->val[$val->nrc]->updated = $val->value;
				}
			}
            $vals = null;
		}
	}
	
	
	/** Saves the data about this item into the database */
	function save_data ()
	{
		if ($this->computer_id and $this->item_id)
		{
			// Purge old values, in case one of the values has been deleted
			$q = 'DELETE FROM '.$this->table.' WHERE computer_id='.$this->computer_id.' AND item_id='.$this->item_id.' ';
			$this->db_query($q);
			
			$q = 'REPLACE INTO '.$this->table.' SET ';
			$q.= 'computer_id='.$this->computer_id.', ';
			$q.= 'item_id='.$this->item_id.', ';
			
			$cnt = 0;
			foreach ($this->val as $nrc => $val)
			{
				$q_fld = $q . 'nrc='.($cnt++).', ';
				if ($this->itemdef->type == MONITOR_TYPE_STRUCT)
				{
					for ($i=0; $i<count($this->itemdef->struct_fields); $i++)
					{
						$q_fld2 = $q_fld;
						$q_fld2.= 'field_id='.$this->itemdef->struct_fields[$i]->id.', ';
						$q_fld2.= 'value="'.mysql_escape_string($this->val[$nrc]->value[$this->itemdef->struct_fields[$i]->id]).'", ';
						$q_fld2.= 'reported="'.$this->val[$nrc]->updated.'" ';
						
						$this->db_query ($q_fld2);
					}
				}
				else
				{
					$q_fld.= 'field_id=0, ';
					$q_fld.= 'value="'.mysql_escape_string($this->val[$nrc]->value).'", ';
					$q_fld.= 'reported="'.$this->val[$nrc]->updated.'" ';

					$this->db_query($q_fld);
				}
			}
		}
	}
	
	/** For items with multiple values, saves a single value, specified by its nrc. It is assumed that the nrc already exists in database.
	Note that this updates only the "value" field in the database */
	function save_single_value ($nrc)
	{
		$idx = $this->get_idx_for_nrc ($nrc);
		if ($this->computer_id and $this->item_id and isset($this->val[$idx]))
		{
			if ($this->itemdef->type == MONITOR_TYPE_STRUCT)
			{
				for ($i=0; $i<count($this->itemdef->struct_fields); $i++)
				{
					$q = 'UPDATE '.$this->table.' SET value=';
					$q.= '"'.mysql_escape_string($this->val[$idx]->value[$this->itemdef->struct_fields[$i]->id]).'" WHERE ';
					$q.= 'computer_id='.$this->computer_id.' AND item_id='.$this->item_id.' AND nrc='.$nrc.' ';
					$q.= 'AND field_id='.$this->itemdef->struct_fields[$i]->id;
					$this->db_query ($q);
				}
			}
			else
			{
				$q = 'UPDATE '.$this->table.' SET value=';
				$q.= '"'.mysql_escape_string($this->val[$idx]->value).'" WHERE ';
				$q.= 'computer_id='.$this->computer_id.' AND item_id='.$this->item_id.' AND nrc='.$nrc.' AND field_id=0';
				$this->db_query ($q);
			}
		}
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
						$ret = get_memory_string ($value);
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
						$ret = '<a href="./?cl=kawacs&op=open_item_file&computer_id='.$this->computer_id.'&item_id='.$this->item_id.'&field_id='.$item_id.'">Open</a>';
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
		foreach ($this->val as $idx => $val) if ($val->nrc==$nrc) { $ret = $idx; break;}
		return $ret;
	}
	
	function get_specific_value($fld_name)
	{
		//check if the supplied field name exists in this object
		$ret = "";
		if(in_array($fld_name, $this->fld_names))
		{
			foreach($this->itemdef->struct_fields as $flds)
			{
				if($flds->name == $fld_name)
				{
					$cc = $this->val[0];
					$ret = $cc->value[$flds->id];
					
				}
			}
		}
		return $ret;
	}
}

?>
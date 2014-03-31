<?php

class_load ('MonitorItem');
class_load ('ComputerItem');

/**
* Abstract class for representing Kawacs computer items.
*
* This class and the inheriting classes act as "interfaces" for the monitoring
* data collected by Kawacs. The object fields will be created from the list
* of fields defined for the monitoring items.
*/

class MonitorItemAbstraction extends Base
{
	/** Computer ID - the computer from which the information was collected
	* @var int */
	var $computer_id = null;
	
	/** Monitoring item ID. 
	* @var int */
	var $item_id = null;
	
	/** The "object" number, in case the monitoring item is of type "array"
	* @var int */
	var $nrc = 0;
	
	
	/** Associative array with the data type for each field 
	* @var array */
	var $field_types = array ();
	
	
	/** The primary key fields for an object */
	var $primary_key = array ('computer_id', 'item_id', 'nrc');
	
	function MonitorItemAbstraction ($computer_id = null, $nrc = 0)
	{
		if ($computer_id)
		{
			$this->computer_id = $computer_id;
			$this->nrc = $nrc;
		}
	}

	
	/** Loads the object data from the Kawacs database. The object fields are created from the monitoring item definition */
	function load_data ()
	{
		if ($this->computer_id and $this->item_id)
		{
			$q = 'SELECT * FROM '.TBL_COMPUTERS_ITEMS.' ci LEFT JOIN '.TBL_MONITOR_ITEMS.' i ';
			$q.= 'ON ci.field_id = i.id ';
			$q.= 'WHERE ci.computer_id='.$this->computer_id.' AND ci.item_id='.$this->item_id.' AND nrc='.$this->nrc.' ';
			$q.= 'ORDER BY ci.field_id ';
			
			$vals = $this->db_fetch_array ($q);
			
			if (count($vals) > 0)
			{
				for ($i=0; $i<count($vals); $i++)
				{
					$field = $vals[$i]->short_name;
					$this->$field = $vals[$i]->value;
					$this->field_types[$field] = $vals[$i]->type;
					$this->list_types[$field] = $vals[$i]->list_type;
				}
			}
			else
			{
				// There is no "object" with such ID, so cleare the ID fields
				$this->computer_id = null;
				$this->nrc = null;
			}
		}
	}
	
	
	/** Empty function overloading the base save_data() method - since item abstractions don't actually store data in database */
	function save_data ()
	{
		return true;
	}
	
	
	/** Empty function overloading the base delete() method - since item abstractions don't actually store data in database */
	function delete ()
	{
		return true;
	}
	
	
	function get_formatted_value ($field_name = '')
	{
		$ret = '';
		if ($field_name)
		{
			switch ($this->field_types[$field_name])
			{
				case MONITOR_TYPE_INT:
					$ret = number_format ($this->$field_name, 0);
					break;
				case MONITOR_TYPE_LIST:
					$ret = $GLOBALS['AVAILABLE_ITEMS_LISTS'][$this->list_types[$field_name]][$this->$field_name];
					break;
				case MONITOR_TYPE_FLOAT:
					$ret = number_format ($this->$field_name, 2);
					break;
				case MONITOR_TYPE_MEMORY:
					$ret = get_memory_string ($this->$field_name);
					break;
				case MONITOR_TYPE_DATE:
					if (is_numeric($this->$field_name) and $this->$field_name>0)
						$ret = date (DATE_TIME_FORMAT, $this->$field_name);
					break;
				
				default:
					$ret = $this->$field_name;
			}
			
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns an associative array of items, according to a filtering criteria. 
	* @param	int	$name_field_id		The id of the field which value will be used for displaying.
	* @param	array	$filter			Array with the filtering criteria
	* @return	array				Associative array, the keys being composed of the computer_id,
	*						item id and nrc (concatenated with '_') and the values being
	*						the values of the field passed by $name_field_id
	*/
	public static function get_list($name_field_id, $filter = array ())
	{
		$ret = array ();
		
		if ($name_field_id)
		{
			$q = 'SELECT ci.computer_id, ci.item_id, ci.nrc, ci.value ';
			$q.= 'FROM '.TBL_COMPUTERS_ITEMS.' ci ';
			if ($filter['customer_id'])
			{
				$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS.' comp ON ci.computer_id=comp.id ';
				//$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS.' cust ON comp.customer_id=cust.id ';
			}
			$q.= 'WHERE ci.field_id = '.$name_field_id.' AND ';
			
			if ($filter['computer_id']) $q.= 'ci.customer_id='.$filter['computer_id'].' AND ';
			if ($filter['customer_id']) $q.= 'comp.customer_id='.$filter['customer_id'].' AND ';
			
			$q = preg_replace ('/AND\s*$/', ' ', $q);
			
			$q.= 'ORDER BY value ';
			
			$items = db::db_fetch_array ($q);
			
			for ($i = 0; $i < count($items); $i++)
			{
				$ret[$items[$i]->computer_id.'_'.$items[$i]->item_id.'_'.$items[$i]->nrc] = $items[$i]->value;
			}
		}
		
		return $ret;
	}
}

?>

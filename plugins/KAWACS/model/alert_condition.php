<?php
class_load ('Alert');

/**
* Class for managing conditions for Alert objects. 
*
* Each Alert object can have one or more such AlertCondition objects
* associated with it.
* 
*/

class AlertCondition extends Base
{
	/** The condition ID
	* @var int */
	var $id = null;
	
	/** The alert object ID to which this condition belongs
	* @var int */
	var $alert_id = null;
	
	/** The field ID to which this condition applies (for "struct" monitoring items)
	* @var int */
	var $field_id = null;
	
	/** The criteria/condition applied by this AlertCondition object
	* @var int */
	var $criteria = null;
	
	/** The value used in the comparison (if needed)
	* @var mixed */
	var $value = null;
	
	/** The type of value (date, text, number etc.), if a value is specified - see $GLOBALS['CRIT_TYPES_NAMES'] */
	var $value_type = null;
	
	
	/** Item field definition if the parent alert is linked to a 'struct' type monitor item 
	* @var MonitorItem */
	var $fielddef = null;
	
	/** Array with the values set for alerts defined on fields of type list 
	* @var array */
	var $list_values = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_ALERTS_CONDITIONS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'alert_id', 'field_id', 'criteria', 'value', 'value_type');
	
	
	/** Contructor. Loads an object's data if an ID is specified */
	function AlertCondition ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Loads the condition data, as well as the field definition (if a monitor item field is involved) */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			// Load the related field definition
			if ($this->field_id)
			{
				$this->fielddef = new MonitorItem ($this->field_id);
				if ($this->fielddef->type == MONITOR_TYPE_LIST)
				{
					// Load also the values
					$q = 'SELECT list_value FROM '.TBL_ALERTS_LISTS_VALUES.' WHERE alert_id='.$this->id;
					$this->list_values = $this->db_fetch_vector ($q);
				}
			}
		}
	}
	
	function load_from_array ($data = array())
	{
		parent::load_from_array ($data);
		if (isset($data['list_values']) and is_array($data['list_values'])) $this->list_values = $data['list_values'];
	}
	
	/** Checks if the data for this monitor alert condition is valid */
	function is_valid_data ()
	{
		$valid = true;
		if (!$this->alert_id) {error_msg ('This alert condition does\'t have an associated alert.'); $valid = false; }
		if (!$this->criteria) {error_msg ('Please specify the condition to use'); $valid = false; }
		if ($this->fielddef->type == MONITOR_TYPE_LIST)
		{
			if (count($this->list_values)==0)
			{
				error_msg ('Please specify the value(s) to use for comparison');
				$valid = false;
			}
		}
		else
		{
			if (!$this->value and !is_numeric($this->value) and $this->value==0)
			{
				error_msg ('Please specify the value to use for comparison'); 
				$valid = false;
			}
		}
		return $valid;
	}
	
	function save_data ()
	{
		parent::save_data ();
		if ($this->id and $this->fielddef->type == MONITOR_TYPE_LIST)
		{
			$this->db_query ('DELETE FROM '.TBL_ALERTS_LISTS_VALUES.' WHERE alert_id='.$this->id);
			if (count($this->list_values) > 0)
			{
				$q = 'INSERT INTO '.TBL_ALERTS_LISTS_VALUES.' VALUES ';
				foreach ($this->list_values as $val)
				{
					$q.= '('.$this->id.','.$val.'), ';
				}
				$this->db_query (preg_replace('/,\s*$/', '', $q));
			}
		}
	}
	
	function delete ()
	{
		if ($this->id) $this->db_query ('DELETE FROM '.TBL_ALERTS_LISTS_VALUES.' WHERE alert_id='.$this->id);
		parent::delete ();
	}
}

?>
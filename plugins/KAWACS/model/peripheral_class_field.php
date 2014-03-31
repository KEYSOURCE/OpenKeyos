<?php
/**
* Represent definitions of peripherals fields
* 
*/
class PeripheralClassField extends Base
{
	/** The field ID
	* @var int */
	var $id = null;
	
	/** The ID of the peripheral class to which this field belongs
	* @var id */
	var $class_id = null;
	
	/** The field name
	* @var string */
	var $name = '';
	
	/** The type of data stored by this field - see $GLOBALS['PERIPHERALS_FIELDS_TYPES']
	* @var int */
	var $type = null;
	
	/** Specifies if the field should be included in listings 
	* @var bool */
	var $in_listings = false;
	
	/** Specifies if the field should be included in reports
	* @var bool */
	var $in_repots = false;
	
	/** Specifies a relative display width for this field, on a scale of 1-9
	* @var int */
	var $display_width = 1;
	
	
	/** Array with the possible values for the relative display width
	* @var array */
	var $width_options = array (1, 2, 3, 4, 5, 6, 7, 8, 9);
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PERIPHERALS_CLASSES_FIELDS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'class_id', 'name', 'type', 'in_listings', 'in_reports', 'display_width', 'ord');
	
	
	/** Contructor. Loads an object's values if an ID is specified */
	function PeripheralClassField ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the field definition is correct */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->class_id) {error_msg ('This peripheral field has not been assigned to any peripheral class.'); $ret = false;}
		if (!$this->name) {error_msg ('Please specify the field name.'); $ret = false;}
		else
		{
			// Check name uniqueness
			if ($this->class_id)
			{
				$q = 'SELECT id FROM '.TBL_PERIPHERALS_CLASSES_FIELDS.' WHERE name="'.mysql_escape_string($this->name).'" ';
				$q.= 'AND class_id='.$this->class_id.' ';
				if ($this->id) $q.= 'AND id<>'.$this->id.' ';
				$exists_id = $this->db_fetch_field ($q, 'id');
				
				if ($exists_id)
				{
					error_msg ('There is already a field with the same name, please choose another one.');
					$ret = false;
				}
			}
		}
		if (!$this->type or ($this->type and !isset($GLOBALS['PERIPHERALS_FIELDS_TYPES'][$this->type])))
		{
			error_msg ('Please specify a valid type for this field.');
			$ret = false;
		}
		
		return $ret;
	}
	
	/** Deletes a peripheral field definition, also deleting it from all peripherals using it */
	function delete ()
	{
		if ($this->id)
		{
			// Delete the field from all peripherals
			$this->db_query ('DELETE FROM '.TBL_PERIPHERALS_FIELDS.' WHERE field_id='.$this->id);
			
			// Delete all associations for this field with monitoring profiles
			$this->db_query ('DELETE FROM '.TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS.' WHERE class_field_id='.$this->id);
			
			parent::delete ();
		}
	}

}
?>
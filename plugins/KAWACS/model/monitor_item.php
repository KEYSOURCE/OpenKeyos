<?php

/**
* Represent definitions of items to be monitored by the KAWACS (and KALM system)
* 
*/

class MonitorItem extends Base
{
	/** The item ID
	* @var int */
	var $id = null;
	
	/** The ID of the parent item. If set, then this is part of the definition for a 'struct' type item
	* @var int */
	var $parent_id = null;
	
	/** The item's short name, to be used in object properties and database field names
	* @var string */
	var $short_name = '';
	
	/** The item's descriptive name 
	* @var string */
	var $name = '';
	
	/** The type of values stored by this item - see $GLOBALS['MONITOR_TYPES']
	* @var int */
	var $type = MONITOR_TYPE_STRING;
	
	/** If this item will store a single value per computer or an array of values - see $GLOBALS['MONITOR_MULTI']
	* @var int */
	var $multi_values = MONITOR_MULTI_NO;
	
	/** The category of this monitoring item - see $GLOBALS['MONITOR_CAT']
	* @var int */
	var $category_id = 0;
	
	/** The default type of logging for this item - see $GLOBALS['MONITOR_LOG']
	* @var int */
	var $default_log = MONITOR_LOG_NONE;
	
	/** The default monitoring interval for this item (in minutes)
	* @var float */
	var $default_update = DEFAULT_MONITOR_INTERVAL;
	
	/** For struct items, the item ID of the field to use as "main field" (e.g. for sorting)
	* @var int */
	var $main_field_id = 0;
	
	/** For "memory" type items (e.g. free disk space) define the minimum difference from the
	* previous reported value in order to consider that an item value has indeed changed
	* (e.g. for free disk space you might want to log only changes over 1 MB 
	* @var int */
	var $treshold = 0;
	
	/** The size multiplier for treshold - see $GLOBALS['CRIT_TYPES_NAMES'] 
	* @var int */
	var $treshold_type = CRIT_VAL_TYPE_MEM_MB;
	
	/** For items of type MONITOR_TYPE_LIST, it specifies the ID (type) of list - see $GLOBALS['AVAILABLE_ITEMS_LISTS_NAMES']
	* @var int */
	var $list_type = 0;
	
	/** For items of type MONITOR_TYPE_DATE, specifies if to show or not the hour
	* @var bool */
	var $date_show_hour = 1;
	
	/** For items of type MONITOR_TYPE_DATE, specifies if to show or not the seconds (if the hour is shown too)
	* @var bool */
	var $date_show_second = 1;
	
	/** True or False if this is a SNMP-collected item 
	* @var bool */
	var $is_snmp = false;
	
	/** For SNMP items, the OID whos value is collected
	* @var string */
	var $snmp_oid = '';
	
	/** For SNMP items, the ID of an MibOid object with the OID specified in the snmp_oid field.
	* It is not mandatory to set this for SNMP items, but doing so allows obtaining additional
	* details about the SNMP items (description, name etc.)
	* @var int */
	var $snmp_oid_id = '';
	
	
	/** For items of type "struct" this stores the children items definitions 
	@var array(MonitorItem) */
	var $struct_fields = array();
	
	/** The index in the struct_fields array of the "main field"
	* @var int */
	var $main_field_idx = null;
	
	/** For SNMP items linked to an actual OID object, this will store the values mappings defined in the MIB - if they exist.
	* @var array */
	var $snmp_oid_vals = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_MONITOR_ITEMS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'parent_id', 'short_name', 'name', 'type', 'multi_values', 'category_id', 'default_log', 'default_update', 'main_field_id', 'treshold', 'treshold_type', 'list_type', 'date_show_hour', 'date_show_second', 'is_snmp', 'snmp_oid', 'snmp_oid_id');
	
	
	/** Contructor. Loads an item's values if an ID is specified */
	function MonitorItem ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	function __destruct()
	{
		if(isset($this->struct_fields)) unset($this->struct_fields);
		if(isset($this->snmp_oid_vals)) unset($this->snmp_oid_vals);
	}
	
	/**
	* Loads the monitoring item definition, including the definition of the struct fields
	*/
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data();
			
			// Load also the fields definitions if this is a 'struct' type
			if ($this->type == MONITOR_TYPE_STRUCT)
			{
				$q = 'SELECT id FROM '.$this->table.' WHERE parent_id='.$this->id.' ORDER BY id ';
				$children = db::db_fetch_array($q);
				foreach ($children as $child)
				{
					$this->struct_fields[] = new MonitorItem($child->id);
					if ($child->id == $this->main_field_id) $this->main_field_idx = count($this->struct_fields)-1;
				}
			}
			
			// If defined, load the SNMP values mappings as well
			$this->snmp_oid_vals = array ();
			if ($this->snmp_oid_id)
			{
				$q = 'SELECT val, name FROM '.TBL_MIBS_OIDS_VALS.' WHERE oid_id='.$this->snmp_oid_id.' ORDER BY val';
				$this->snmp_oid_vals = db::db_fetch_list ($q);
			}
		}
	}
	
	/**
	* Validates the object data
	*
	* @todo	Use error codes and external files for storing the messages text
	*/
	function is_valid_data()
	{
		$ret = true;
		
		$this->id = intval($this->id);
		if (!$this->parent_id and !$this->id) {error_msg('Please specify the numeric ID'); $ret=false;}
		if (!$this->short_name) {error_msg('Please specify the short name'); $ret=false;}
		if (!$this->name) {error_msg('Please specify the descriptive name'); $ret=false;}
		if (!$this->type) {error_msg('Please specify the item type'); $ret=false;}
		if (!$this->parent_id)
		{
			if (!$this->multi_values) {error_msg('Please specify if using single or multiple values'); $ret=false;}
			if (!$this->category_id and !$this->is_peripheral_snmp_item()) {error_msg('Please specify the item category'); $ret=false;}
			if (!$this->default_log) {error_msg('Please specify the default logging mode'); $ret=false;}
			
			// Update interval is requested only for automatic items
			if ($this->id >= ITEM_ID_COLLECTED_MIN and $this->id <= ITEM_ID_COLLECTED_MAX)
			{
				if (!$this->default_update) {error_msg('Please specify the default update interval'); $ret=false;}
			}
		}
		else
		{
			// For fields of structure items make sure they are not structures as well
			if ($this->type == MONITOR_TYPE_STRUCT) {error_msg('An item field can\'t be of type "Structure"'); $ret=false;}
		}
		
		// For lists, make sure that the type of list is specified
		if ($this->type==MONITOR_TYPE_LIST and (!$this->list_type or !isset($GLOBALS['AVAILABLE_ITEMS_LISTS'][$this->list_type])))
		{
			$ret = false;
			error_msg ('Please specify the type of list');
		}
		
		// For SNMP items, make sure an OID is specified and that it is valid
		if ($this->is_snmp and !$this->snmp_oid) {error_msg ('Please specify the SNMP OID.'); $ret=false;}
		elseif ($this->is_snmp and $this->snmp_oid)
		{
			if (!preg_match('/^\.[0-9]+[0-9\.]+[0-9]$/', $this->snmp_oid)) {error_msg ('Please specify a valid numeric SNMP OID.'); $ret=false;}
			elseif ($this->parent_id)
			{
				// For SNMP structures with multiple values, all the fields OIDs must be descendants of the parent OID
				// If they are not multi-values, then there is no restriction on the OIDs, since they will be connected separately
				$parent_item = new MonitorItem ($this->parent_id);
				if ($parent_item->multi_values == MONITOR_MULTI_YES)
				{
					if ($parent_item->snmp_oid == $this->snmp_oid) {error_msg ('The SNMP OID can\'t be the same as the parent item OID'); $ret=false;}
					elseif (strpos($this->snmp_oid,$parent_item->snmp_oid)!==0) {error_msg ('The OID must be a descendent of the parent\'s OID'); $ret=false;}
				}
			}
		}
		
		// Check the name uniqueness for top-level items, or for fields belonging to same item
		if (($this->id or $this->parent_id) and $this->name and $this->short_name)
		{
			$q = 'SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE ';
			if ($this->parent_id) $q.= 'parent_id='.$this->parent_id.' AND '.($this->id ? ' id<>'.$this->id.' AND ' : '');
			elseif ($this->id) $q.= 'id<>'.$this->id.' AND ';
			$q.= '(name="'.db::db_escape($this->name).'" OR short_name="'.db::db_escape($this->short_name).'") LIMIT 1';
			$exists_id = db::db_fetch_field ($q, 'id');
			
			if ($exists_id)
			{
				$ret = false;
				if ($this->parent_id) error_msg ('Two fields of the same item can\'t have the same name or short name.');
				else error_msg ('There is already a monitoring item with the same name or short name.');
			}
		}
		
		return $ret;
	}
	
	
	/** 
	* Saves the monitoring item data
	*
	* If this is a field definition, it tries to uses the minimum availale ID, to avoid overlapping with the IDs for the main items
	* @todo	Add additional checkings in generating ID for struct fields
	*/
	function save_data ()
	{
		if (!$this->id and $this->parent_id)
		{
			// This is a new field definition
			$max = db::db_fetch_field('SELECT MAX(id) as max FROM '.$this->table.' WHERE parent_id<>0 ', 'max');
			$this->id = $max + 1;
		}
		if (!$this->snmp_oid) $this->snmp_oid_id = 0; // Just to be sure
		return parent::save_data();
	}
	
	
	/**
	* For each field containing codes or IDs, add a field (with suffix "_display") which contains the translated value for that field
	*/
	function set_display_fields ()
	{
		if ($this->id)
		{
			$this->type_display = $GLOBALS['MONITOR_TYPES'][$this->type];
			$this->multi_values_display = $GLOBALS['MONITOR_MULTI'][$this->multi_values];
			$this->category_id_display = $GLOBALS['MONITOR_CAT'][$this->category_id];
			$this->default_log_display = $GLOBALS['MONITOR_LOG'][$this->default_log];
			
			// Set also the display fields for struct fields, if any
			for ($i=0; $i<count($this->struct_fields); $i++) $this->struct_fields[$i]->set_display_fields();
		}
	}
	
	/** Tells if an item is a computer automatically collected item */
	function is_computer_automatic_item ()
	{
		return ($this->id and ($this->id >= ITEM_ID_COLLECTED_MIN and $this->id <= ITEM_ID_COLLECTED_MAX));
	}
	
	/** Tells if an item is manually editable */
	function is_editable ()
	{
		return ($this->id and ($this->id >= ITEM_ID_MANUAL_MIN and $this->id <= ITEM_ID_MANUAL_MAX));
	}
	
	/** Tells if an item refers to computer events */
	function is_computer_events_item ()
	{
		return ($this->id and ($this->id >= ITEM_ID_EVENTS_MIN and $this->id <= ITEM_ID_EVENTS_MAX));
	}
	
	/** Tells if this is a peripherals item (for now this is the same as is_peripheral_snmp_item()) */
	function is_peripheral_item ()
	{
		return ($this->id and ($this->id >= ITEM_ID_PERIPHERAL_SNMP_MIN and $this->id <= ITEM_ID_PERIPHERAL_SNMP_MAX));
	}
	
	/** Tells if an item refers to a SNMP peripheral item */
	function is_peripheral_snmp_item ()
	{
		return ($this->id and ($this->id >= ITEM_ID_PERIPHERAL_SNMP_MIN and $this->id <= ITEM_ID_PERIPHERAL_SNMP_MAX));
	}
	
	/**
	* Returns an array with the short names of the fields. The array keys are the structure fields IDs
	* If this is not a "struct" item, it adds the item's short name.
	*/
	function get_short_names ()
	{
		$ret = array();
		if ($this->id)
		{
			if (empty($this->struct_fields)) $ret[0] = $this->short_name;
			else
			{
				for ($i=0; $i<count($this->struct_fields); $i++)
					$ret[$this->struct_fields[$i]->id] = $this->struct_fields[$i]->short_name;
			}
		}
		return $ret;
	}
	
	
	
	/**
	* Returns the formatted value specifie
	*/
	function get_formatted_value ($value)
	{
		if ($this->snmp_oid_id and isset($this->snmp_oid_vals[$value]))
		{
			// If available, use the SNMP values mappings
			$ret = $this->snmp_oid_vals[$value];
		}
		else
		{
			switch ($this->type)
			{
				case MONITOR_TYPE_INT:
					$ret = number_format ($value, 0);
					break;
				case MONITOR_TYPE_LIST:
					$ret = $GLOBALS['AVAILABLE_ITEMS_LISTS'][$this->list_type][$value];
					break;
				case MONITOR_TYPE_FLOAT:
					$ret = number_format ($value, 2);
					break;
				case MONITOR_TYPE_MEMORY:
					if ($this->is_snmp) $ret = get_memory_string ($value * 1024); // SNMP agents report the memory in KB, not B
					else $ret = get_memory_string ($value);
					break;
				case MONITOR_TYPE_TEXT;
					$ret = nl2br(htmlentities($value));
					break;
				case MONITOR_TYPE_DATE:
					if (is_numeric($value) and $value>0)
					{
						if ($this->date_show_second) $ret = date (DATE_TIME_FORMAT_SECOND, $value);
						elseif ($this->date_show_hour) $ret = date (DATE_TIME_FORMAT, $value);
						else $ret = date (DATE_FORMAT, $value);
					}
	
					break;
				/*
				case MONITOR_TYPE_FILE:
					$ret = '<a href="./?cl=kawacs&op=open_item_file&computer_id='.$this->computer_id.'&item_id='.$this->item_id.'&field_id='.$item_id.'">Open</a>';
					break;
				*/
				default:
					$ret = $value;
			}
		}
		
		return $ret;
	}

	
	/**
	* [Class Method] Returns a list with monitor items (computer items ONLY)
	* @return	array				Associative array, they keys being monitor item IDs and the 
	*						values being their names, sorted by ID.
	*/
	public static function get_monitor_items_list ()
	{
		$ret = array ();
		$q = 'SELECT id, name FROM '.TBL_MONITOR_ITEMS.' WHERE parent_id=0 AND id<'.ITEM_ID_PERIPHERAL_SNMP_MIN.' ORDER BY id';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	public static function get_monitor_items_list_from_ids_array($ids = array())
	{
		$ret = array();
		$q = 'SELECT id, name FROM '.TBL_MONITOR_ITEMS.' WHERE parent_id=0 AND id<'.ITEM_ID_PERIPHERAL_SNMP_MIN;
		if(count($ids)!=0)
		{
			$q_add = " (";
			foreach ($ids as $id)
			{
				$q_add .= $id.",";
			}
			$q_add = substr($q_add, 0, strlen($q_add)-1);
			$q_add .= ")";
			$q .= ' and id in '.$q_add;	
		}
		$q .= ' order by id';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns a list with monitor items (peripherals items ONLY)
	* @return	array				Associative array, they keys being monitor item IDs and the 
	*						values being their names, sorted by ID.
	*/
	public static function get_peripherals_monitor_items_list ()
	{
		$ret = array ();
		$q = 'SELECT id, name FROM '.TBL_MONITOR_ITEMS.' WHERE parent_id=0 AND id>='.ITEM_ID_PERIPHERAL_SNMP_MIN.' ORDER BY id';
		$ret = db::db_fetch_list ($q);
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns an array with monitor items, according to some criteria.
	* IMPORTANT NOTE: This only returns computers items
	* @param	array		$filter		The fields and values by which to filter the items
	* @return	array(MonitorItem)		Array with the matched monitor items.
	*/
	public static function get_monitor_items ($filter = array())
	{
		$ret = array();
		$q = 'SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE parent_id=0 AND id<'.ITEM_ID_PERIPHERAL_SNMP_MIN.' ';
		if ($filter['category_id']) $q.= 'AND category_id='.$filter['category_id'].' ';
		$q.= 'ORDER BY id ';
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new MonitorItem ($id);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns an array with monitor items, according to some criteria.
	* IMPORTANT NOTE: This only returns peripherals items
	* @param	array		$filter		The fields and values by which to filter the items
	* @return	array(MonitorItem)		Array with the matched monitor items.
	*/
	function get_peripherals_monitor_items ($filter = array())
	{
		$ret = array();
		$q = 'SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE parent_id=0 AND id>='.ITEM_ID_PERIPHERAL_SNMP_MIN.' ';
		if ($filter['category_id']) $q.= 'AND category_id='.$filter['category_id'].' ';
		$q.= 'ORDER BY id ';
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new MonitorItem ($id);
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns a list with the current defined monitor items - with the display fields set (computer items ONLY)
	*/
    public static function get_monitor_items_display ()
	{
		$ret = MonitorItem::get_monitor_items();
		for ($i = 0; $i<count($ret); $i++) $ret[$i]->set_display_fields();
		return $ret;
	}
	
	/**
	* [Class Method] Returns a list with the current defined monitor items - with the display fields set (peripheral items ONLY)
	*/
    public static function get_peripherals_monitor_items_display ()
	{
		$ret = MonitorItem::get_peripherals_monitor_items();
		for ($i = 0; $i<count($ret); $i++) $ret[$i]->set_display_fields();
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list with the current defined monitor items, grouped by category (computer items ONLY)
	*/
    public static function get_categories_items ()
	{
		$ret = array();
		
		foreach (array_keys($GLOBALS['MONITOR_CAT']) as $category_id)
		{
			$ret[$category_id] = MonitorItem::get_monitor_items(array('category_id' => $category_id));
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns the numeric ID for a monitor item short name
	*/
    public static function get_item_id ($item_name = '')
	{
		$ret = null;
		
		// XXX: Temp only, while transitioning from AD Computers to AD Computers + AD Computers Monitoring
		if ($item_name == 'ad_computers') $ret = 1046;
		if ($item_name)
		{
			$ret = db::db_fetch_field ('SELECT id FROM '.TBL_MONITOR_ITEMS.' WHERE short_name = "'.$item_name.'"', 'id');
		}
		return $ret;
	}
}

?>
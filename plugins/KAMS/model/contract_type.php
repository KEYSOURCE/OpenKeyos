<?php
/**
 * Base class for defining a contract type
 */

class ContractType extends Base 
{
	/**
	 * contract type id
	 *
	 * @var int
	 */
	var $id = null;
	
	/**
	 * The name of the contract type
	 *
	 * @var string
	 */
	var $name = "";
	
	/**
	 * brief description of this contract type
	 *
	 * @var unknown_type
	 */
	var $description = "";
	
	/**
	 * [Flag]
	 * Specifies if this contract type has a quantity / amount
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $quantity = 0;
	
	/**
	 * [Flag]
	 * Specifies if this contract type has a total price
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $total_price = 0;
	
	/**
	 * [Flag]
	 * Specifies if this contract type has recurring payments or not
	 * 
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $recurring_payments = 0;
	
	/**
	 * [Flag]
	 * Specifies if this contract type has end / expiration date
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $end_date = 0;
	
	/**
	 * [Flag]
	 * Specifies if this contract type has vendor
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $vendor = 0;
	
	/**
	 * [Flag]
	 * Specifies if this contract type has a supplier / manufacturer
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $supplier = 0;
	
	/**
	 * [Flag]
	 * Specifies if this type of contract should be treated as a warranty contract or not
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $is_warranty_contract = 0;
	
	/**
	 * [Flag]
	 * Specifies if this contract type has a notification period
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $send_period_notifs = 0;
	
	/**
	 * [Flag]
	 * Specifies if this contract type should send notification at the expiration of the contract
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $send_expiration_notifs = 0;
	
	/**
	 * [Flag]
	 * Specifies if this contract type supports renewals
	 *
	 * @var int (0 - disabled; 1 - supports)
	 */
	var $supports_renewals = 0;
	
	/**
	 * table storing contract type informations
	 *
	 * @var string
	 */
	var $table = TBL_CONTRACT_TYPES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'name', 'description', 'quantity', 'total_price', 'recurring_payments', 'end_date', 'vendor', 'supplier', 'is_warranty_contract', 'send_period_notifs', 'send_expiration_notifs', 'supports_renewals');
	
	
	
	function ContractType($id = null)
	{
		if($id)
		{
			$this->id = $id;
			$this->load_data($id);
		}
	}
	
	/**
	 * [Class method]
	 * Gets a list of existing contract types
	 *
	 * @return array(ContractType)
	 */
	function get_types_list()
	{
		$ret = array();
		$query = "select id from ".TBL_CONTRACT_TYPES;
		$ids = db::db_fetch_array($query);
		foreach($ids as $id)
		{
			$contract = new ContractType($id->id);
			$ret[] = $contract;
		}
		return $ret;
	}
	
	/**
	 * [Class Method]
	 * Returns an array with all the contract types where the key is the id of the type and the value is it's name
	 *
	 * @return array
	 */
	function get_types_array()
	{
		$ret = array();
		$query = "select id, name from ".TBL_CONTRACT_TYPES;
		$ret = db::db_fetch_list($query);
		return $ret;
	}
	
	/**
	 * Check's if this object's data is valid
	 *
	 * @return bool
	 */
	function is_valid_data()
	{
		$valid = true;
		if(!$this->name)
		{ 
			error_msg($this->get_string('NEED_CTYPE_NAME'));
			$valid = false;
		}
		if(!$this->description)
		{
			error_msg($this->get_string('WARNING_NO_CTYPE_DESC'));
			$valid &= true;
		}
		return $valid;
	}
	
	/**
	 * Adds a new type of contract into the database
	 * @return bool
	 */
	function add_new()
	{	
		$ret = true;
		if (!empty($this->table))
		{
			$q = 'INSERT INTO '.$this->table.' ( ';
			foreach ($this->fields as $field)
			{
				if($field!="id")
				{
					$q.=$field.', ';
				}
			}
			$q = preg_replace('/,\s*$/', '', $q);
			$q.=") VALUES (";
			foreach ($this->fields as $field)
			{
				if($field!="id")
				{
					if (is_string($this->$field) and $this->$field == "NULL") $q.= 'NULL, ';
					else $q.= '"'.mysql_escape_string($this->$field).'", ';
				}
			}
			$q = preg_replace('/,\s*$/', '', $q);
			$q.=");";

			$this->db_query($q);
			
			$ret = (!$this->db_error());
		}
		return $ret;
	}
}

?>
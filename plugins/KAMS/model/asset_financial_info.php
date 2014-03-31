<?php
/**
 * This class delas with the financial information for an asset
 */

class AssetFinancialInfo extends Base 
{
	/**
	 * the id associated with this financial infos
	 *
	 * @var int
	 */
	var $id = null;
	
	/**
	 * the id of the asset to whom this info is associated to
	 *
	 * @var int
	 */
	var $asset_id = null;
	
	/**
	 * the id of the supplier id 
	 *
	 * @var int
	 */
	var $supplier_id = null;
	
	/**
	 * the purchase value of this asset
	 *
	 * @var float
	 */
	var $purchase_value = 0;
	
	/**
	 * the invoice number 
	 *
	 * @var string
	 */
	var $invoice_number = '';
	
	/**
	 * the timestamp of the invoice
	 *
	 * @var int
	 */
	var $invoice_date = 0;
	
	/**
	 * the writeoff_value of this asset
	 *
	 * @var float
	 */
	var $writeoff_value = 0;
	
	/**
	 * the amortization period in days
	 * the default period is 30 days
	 *
	 * @var int
	 */
	var $amortization_period = 30;
	
	/**
	 * the currency for the money values
	 *
	 * @var int
	 */
	var $currency = 1;
	/**
	 * the associated table
	 *
	 * @var unknown_type
	 */
	var $table = TBL_ASSET_FINANCIAL_INFOS;
	var $fields = array('id', 'asset_id',  'supplier_id',  'purchase_value',  'invoice_number',  'invoice_date',  'writeoff_value',  'amortization_period', 'currency');
	
	/**
	 * [Constructor]
	 *
	 * @param int $id
	 * @return AssetFinancialInfo
	 */
	function AssetFinancialInfo($id = null)
	{
		if($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	/**
	 * loads the associated data from the database
	 *
	 */
	function load_data()
	{
		if($this->id)
		{
			parent::load_data();
		}
	}
	
	/**
	 * Gets the supplier name associated with this invoice 
	 *
	 * @return string
	 */
	function get_supplier_name()
	{
		$query = "select name from ".TBL_SUPPLIERS." where id = ".$this->supplier_id;
		
		$supplier_name = db::db_fetch_row($query);
		
		return $supplier_name['name'];
	}
	
	/**
	 * Returns a supplier object associated with this invoice
	 *
	 * @return Supplier
	 */
	function get_supplier()
	{
		class_load('Supplier');
		return new Supplier($this->supplier_id);
	}
	
	/**
	 * get currency informations for this invoice
	 *
	 * @return array(string)
	 */
	function get_currency()
	{
		$ret = array();
		$query = "select name, symbol from ".TBL_CURRENCY." where id=".$this->currency;
		$ret = db::db_fe($query);
		return $ret;
	}
	
	/**
	 * gets the symbol for this currency
	 *
	 * @return string
	 */
	function get_currency_symbol($currency_id = null)
	{
		$ret = array();
		if(!$currency_id) $currency_id = $this->currency;
		$query = "select symbol from ".TBL_CURRENCY." where id=".$currency_id;
		$ret = db::db_fetch_field($query, 'symbol');
		return $ret;	
	}
	
	/**
	 * [Class Method]
	 * gets all the currencies
	 * returns an associative array where the key is the id and the value is the 
	 * corresponding value of the $field from the database
	 * @param field
	 * Can take one of the values "name", "symbol". If other field is specified, the function will return an empty field
	 * @return array
	 */
	function get_currecies($field)
	{
		$ret = array();
		$field = trim($field);
		$file = strtolower($field);
		
		if($field == "name" || $field == "symbol")
		{
			$query = "select id, ".$field." from ".TBL_CURRENCY;
			$ret  = db::db_fetch_list($query);
		}
		return $ret;
	}
	
	
	function is_valid_data()
	{
		$valid = true;
		if(!$this->invoice_number) { error_msg($this->get_string('NEED_INVOICE_NUMBER')); $valid = false; }
		if(!$this->invoice_date || $this->invoice_date == -1){ error_msg($this->get_string('NEED_INVOICE_DATE')); $valid = false; }
		if(!$this->purchase_value) { error_msg($this->get_string('NEED_PURCHASE_VALUE')); $valid=false; }
		if(!$this->supplier_id) {error_msg($this->get_string('NEED_SUPPLIER')); $valid = false;}
		if(!$this->amortization_period) {error_msg($this->get_string('NEED_PERIOD')); $valid = false;}
		return $valid;
	}
	/**
	 * Inserts the current object into the database
	 *
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

			debug($q);
			$this->db_query($q);
			
			
			$ret = (!$this->db_error());
		}
		return $ret;	
	}
	
	/**
	 * overloaded function for saving the form data
	 *
	 * @return bool
	 */
	function save_data()
	{
		$ret = true;
		if (!empty($this->table))
		{
			$q = 'UPDATE '.$this->table.' SET ';
			foreach ($this->fields as $field)
			{
				if($field != "id")
				{
					if (is_string($this->$field) and $this->$field == "NULL") $q.= $field.'=NULL, ';
					else $q.= $field.'="'.mysql_escape_string($this->$field).'", ';
				}
			}
			$q = preg_replace('/,\s*$/', '', $q);
			$q.=" where id=".$this->id.";";
			
			$this->db_query($q);
			
			$ret = (!$this->db_error());
		}
		return $ret;
	}
}

?>
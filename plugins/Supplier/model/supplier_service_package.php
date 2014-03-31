<?php

/**
* Class for storing and manipulating information about service packages offered by suppliers
*
*/

class_load ('Supplier');

class SupplierServicePackage extends Base
{
	/** Service package ID
	* @var int */
	var $id = null;
	
	/** Supplier ID
	* @var int */
	var $supplier_id = null;
	
	/** Service package name
	* @var string */
	var $name = '';
	
	/** Comments about the service package
	* @var text */
	var $description = '';
	

	/** The database table storing suppliers data 
	* @var string */
	var $table = TBL_SUPPLIERS_SERVICE_PACKAGES;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'supplier_id', 'name', 'description');

	
	/**
	* Constructor, also loads the object data from the database if an object ID is specified
	* @param	int $id		The object ID
	*/
	function SupplierServicePackage ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the customer data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg('Please specify the service package name.'); $ret = false;}
		if (!$this->supplier_id) {error_msg('Please specify the supplier.'); $ret = false;}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list with the available service packages
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- prefix_supplier : Prefix the packages with the supplier names
	* @param	array					Associative array with the packages, the keys being packages IDs and the values being their names
	*/
	public static function get_service_packages_list ($filter = array ())
	{
		$ret = array ();
		
		if (!$filter['prefix_supplier'])
		{
			$q = 'SELECT id, name FROM '.TBL_SUPPLIERS_SERVICE_PACKAGES.' ORDER BY name ';
		}
		else
		{
			$q = 'SELECT p.id, concat(s.name, ": ", p.name) as name FROM ';
			$q.= TBL_SUPPLIERS_SERVICE_PACKAGES.' p INNER JOIN '.TBL_SUPPLIERS.' s ON p.supplier_id=s.id ';
			$q.= 'ORDER BY s.name, p.name ';
		}
		
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
	
	/** 
	* [Class Method] Returns service packages according to a specified criteria.
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- supplier_id : Return packages for the specified supplier
	* @return	array(SupplierServicePackage)		Array with the matched service packages
	*/
	function get_service_packages ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT p.id FROM '.TBL_SUPPLIERS_SERVICE_PACKAGES.' p WHERE ';
		if ($filter['supplier_id']) $q.= 'p.supplier_id = '.$filter['supplier_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY p.name ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new SupplierServicePackage ($id);
		
		return $ret;
	}
	
}
?>
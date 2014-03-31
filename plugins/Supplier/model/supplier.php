<?php
/**
* Class for storing and manipulating suppliers information
*
*/

class_load ('SupplierServicePackage');
class_load ('ServiceLevel');

class Supplier extends Base
{
	/** Supplier ID
	* @var int */
	var $id = null;
	
	/** Supplier name
	* @var string */
	var $name = '';
	

	/** The service packages offered by this supplier. Note that they are only loaded on request, with load_service_packages() method
	* @var array */
	var $service_packages = array ();
	
	
	/** The database table storing suppliers data 
	* @var string */
	var $table = TBL_SUPPLIERS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'name');

	
	/**
	* Constructor, also loads the supplier data from the database if a supplier ID is specified
	* @param	int $id		The supplier ID
	*/
	function Supplier ($id = null)
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
		
		if (!$this->name) {error_msg('Please specify the supplier name.'); $ret = false;}
		
		return $ret;
	}
	
	
	/** Deletes a supplier and the associated service packages */
	function delete ()
	{
		if ($this->id)
		{
			// Delete the service packages
			$this->load_service_packages ();
			for ($i=0; $i<count($this->service_packages); $i++) $this->service_packages[$i]->delete ();
			
			// Delete the supplier
			parent::delete ();
		}
	}
	
	
	/** Load the service packages for this supplier */
	function load_service_packages ()
	{
		if ($this->id)
		{
			$this->service_packages = SupplierServicePackage::get_service_packages (array('supplier_id' => $this->id));
		}
	}
	
	
	/**
	* [Class Method] Return supplier according to a specified criteria.
	* @param	array		$filter			Associative array with the filtering criteria. Can contain:
	*							- load_service_packages: If True, load the service packages for each supplier
	*/
	function get_suppliers ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT s.id FROM '.TBL_SUPPLIERS.' s ORDER BY s.name ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Supplier ($id);
		
		if ($filter['load_service_packages'])
		{
			for ($i=0; $i<count($ret); $i++) $ret[$i]->load_service_packages ();
		}
		
		return $ret;
	}
	
	/**
	 * [Class Method] Retun an associative array containing supplier names and IDs
	 * @return array
	 */
	function get_supplier_names()
	{
		
		$ret = array();
		$query = 'SELECT id, name from '.TBL_SUPPLIERS.' order by name';
		$ret = DB::db_fetch_list($query);
		return $ret;
	}
}
?>
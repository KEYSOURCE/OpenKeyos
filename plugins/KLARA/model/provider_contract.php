<?php

/**
* Stores and manages information about contracts offered by Internet service providers 
* 
*/

class ProviderContract extends Base
{
	/** The ID of the contract
	* @var int */
	var $id = null;
	
	/** The ID of the provider
	* @var int */
	var $provider_id = null;
	
	/** The name of the contract
	* @var string */
	var $name = '';
	
	/** Comments about the contract
	* @var text */
	var $comments = '';
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PROVIDERS_CONTRACTS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'provider_id', 'name', 'comments');
	
	
	/** 
	* Contructor. Loads an object's values if an ID or IP address is specified 
	* @param	mixed		$id		An object ID
	*/
	function ProviderContract ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}

	
	/** Checks if the information is valid for the provider */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify the contract name.'); $ret = false;}
		if (!$this->provider_id) {error_msg ('Please specify the provider for this contract.'); $ret = false;}

		return $ret;
	}
	
	
	/** Checks if the contract can be deleted */
	function can_delete ()
	{
		$ret = false;
		
		if ($this->id)
		{
			$ret = true;
			// Check if there are any customers using this contract
			$q = 'SELECT contract_id FROM '.TBL_CUSTOMERS_INTERNET_CONTRACTS.' WHERE contract_id='.$this->id.' LIMIT 1';
			$in_use = db::db_fetch_field ($q, 'contract_id');
			
			if ($in_use)
			{
				$ret = false;
				error_msg ('There are customers with this contract assigned, it can\'t be deleted.');
			}
		}
		return $ret;
	}
	
		
	/**
	* [Class Method] Returns a list of available contracts
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- prefix_providers: If True, the name of the contracts will be prefixed with the provider names
	* @return	array					Associative array, the keys being contract IDs and the values being contract names
	*/
	public static function get_contracts_list($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT c.id, '.($filter['prefix_providers'] ? 'concat(p.name, " : ", c.name) as name' : 'c.name').' FROM ';
		$q.= TBL_PROVIDERS_CONTRACTS.' c ';
		if ($filter['prefix_providers']) $q.= 'INNER JOIN '.TBL_PROVIDERS.' p ON c.provider_id=p.id ';
		
		$q.= 'ORDER BY 2';
		
		$ret = db::db_fetch_list ($q);
		return $ret;
	}
	
	
	/**
	* [Class Method] Return the provider contracts defined in the system according to a specified criteria
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- provider_id: Get the contracts from the speciefied provider
	* @return	array(ProviderContract)			Array of ProviderContract objects
	*/
	public static function get_contracts($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT c.id FROM '.TBL_PROVIDERS_CONTRACTS.' c WHERE ';
		if ($filter['provider_id']) $q.= 'c.provider_id = '.$filter['provider_id'].' AND ';
		
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		
		$q.= 'ORDER BY c.name';
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new ProviderContract($id);
		
		return $ret;
	}

}

?>
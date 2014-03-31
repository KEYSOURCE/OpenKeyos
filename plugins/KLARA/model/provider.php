<?php

/**
* Stores and manages information about Internet service providers 
* 
*/

class_load ('ProviderContract');
class_load ('ProviderContact');

class Provider extends Base
{
	/** The ID of provider
	* @var int */
	var $id = null;
	
	/** The provider name
	* @var string */
	var $name = '';
	
	/** The provider address
	* @var text */
	var $address = '';
	
	/** The provider website
	* @var string */
	var $website = '';
	
	
	/** Array with the contracts offered by this provider
	* @var array(ProviderContract) */
	var $contracts = array ();
	
	/** Array with the contacts for this provider
	* @var array(ProviderContact) */
	var $contacts = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PROVIDERS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'name', 'address', 'website');
	
	
	/** 
	* Contructor. Loads an object's values if an object ID is specified 
	* @param	mixed		$id		An object ID
	*/
	function Provider ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}

	
	/** Loads the data for the provider, including contracts and contacts */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			
			if ($this->id)
			{
				$this->contracts = ProviderContract::get_contracts (array ('provider_id' => $this->id));
				$this->contacts = ProviderContact::get_contacts (array ('provider_id' => $this->id));
			}
		}
	}
	
	
	/** Checks if the information is valid for the provider */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg ('Please specify the provider name.'); $ret = false;}

		return $ret;
	}
	
	
	/** Saves the object data, making sure the website url has an HTTP in front */
	function save_data ()
	{
		if ($this->website and !preg_match('/^http(s)*\:\/\//',$this->website))
		{
			$this->website = 'http://'.$this->website;
		}
		parent::save_data ();
	}
	
	
	/** Checks if the provider can be deleted */
	function can_delete ()
	{
		$ret = false;
		
		if ($this->id)
		{
			$ret = true;
			if ($this->get_customers_count() > 0)
			{
				$ret = false;
				error_msg ('This provider can\'t be deleted, because there are customers assigned to it');
			}
		}
		return $ret;
	}
	
	
	/** Deletes a provider and its associated contracts and contacts */
	function delete ()
	{
		if ($this->id)
		{
			for ($i=0; $i<count($this->contracts); $i++)
			{
				$this->contracts[$i]->delete ();
			}
			for ($i=0; $i<count($this->contacts); $i++)
			{
				$this->contacts[$i]->delete ();
			}
			
			parent::delete ();
		}
	}
	
	
	/**
	* Returns the number of customers having contracts from this provider
	* @return	int					The number of customers with contracts with this provider
	*/
	function get_customers_count ()
	{
		$ret = 0;
		
		if ($this->id)
		{
			$q = 'SELECT count(DISTINCT c.customer_id) as cnt FROM '.TBL_CUSTOMERS_INTERNET_CONTRACTS.' c ';
			$q.= 'INNER JOIN '.TBL_PROVIDERS_CONTRACTS.' pc ON c.contract_id=pc.id WHERE ';
			$q.= 'pc.provider_id='.$this->id;
			
			$ret = db::db_fetch_field ($q, 'cnt');
		}
		
		return $ret;
	}
	
	
	/**
	* Returns the list of customers assigned to this provider
	* @return	array					Associative array with the customers for this provider,
	*							keys being customer IDs and the values being arrays with IDs
	*							of provider contracts (since a customer might have multiple contracts)
	*/
	function get_customers_list($current_user = null)
	{
		$ret = array ();
		
		if ($this->id)
		{
			$q = 'SELECT DISTINCT cust.id as customer_id, c.id as contract_id, ic.id as customer_internet_contract_id ';
			$q.= 'FROM '.TBL_PROVIDERS_CONTRACTS.' c ';
			$q.= 'INNER JOIN '.TBL_CUSTOMERS_INTERNET_CONTRACTS.' ic ON c.id=ic.contract_id ';
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON ic.customer_id=cust.id ';
			
			$q.= 'WHERE c.provider_id='.$this->id;
			
			if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
			{
				$cc = $current_user->get_assigned_customers_list();
				$q.= ' and  cust.id in (';
				$i=0;
				foreach($cc as $k=>$name)
				{
					if($i!=count($cc)-1) $q.=$k.", ";
					else $q.=$k;
				}
				$q = trim (preg_replace ('/,\s*$/', '', $q));
				$q.=") ";
			}
			
			$q.= ' ORDER BY cust.name ';
			
			$custs = db::db_fetch_array ($q);
			foreach ($custs as $cust)
			{
				$ret[$cust->customer_id][$cust->customer_internet_contract_id] = $cust->contract_id;
			}
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of available Internet providers defined in the system
	* @return	array					Associative array, keys being provider IDs and the values being provider names
	*/
    public static function get_providers_list ()
	{
		$ret = array ();
		$q = 'SELECT id, name FROM '.TBL_PROVIDERS.' ORDER BY name ';
		$ret = DB::db_fetch_list ($q);
		return $ret;
	}
	
	
	/**
	* [Class Method] Return the Internet Providers defined in the system
	* @return	array(Provider)				Array of Provider objects
	*/
    public static function get_providers ()
	{
		$ret = array ();

        $current_user = $GLOBALS['CURRENT_USER'];

		$q = 'SELECT p.id FROM '.TBL_PROVIDERS.' p ';
		if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			$q.=" inner join ".TBL_PROVIDERS_CONTRACTS." pc on p.id=pc.provider_id ";
			$q.=" INNER JOIN ".TBL_CUSTOMERS_INTERNET_CONTRACTS." cic on cic.contract_id=pc.id ";
			$cc = $current_user->get_assigned_customers_list();
			$q.= 'where cic.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$q.=") ";
		}
		$q.= ' ORDER BY p.name';
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Provider ($id);
		
		return $ret;
	}

}

?>
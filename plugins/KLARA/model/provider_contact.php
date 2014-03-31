<?php

/**
* Stores and manages information about provider contacts
* 
*/

class_load ('Provider');
class_load ('ProviderContactPhone');

class ProviderContact extends Base
{
	/** The ID of the contact
	* @var int */
	var $id = null;
	
	/** The ID of the provider
	* @var int */
	var $provider_id = null;
	
	/** Contact first name 
	* @var string */
	var $fname = '';
	
	/** Contact last name 
	* @var string */
	var $lname = '';
	
	/** Contact e-mail address
	* @var string */
	var $email = '';
	
	/** Comments about this contact
	* @var text */
	var $comments = '';
	
	
	/** Phone numbers for this contact
	* @var array(ProviderContactPhone) */
	var $phones = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_PROVIDERS_CONTACTS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'provider_id', 'fname', 'lname', 'email', 'comments');
	
	
	/** 
	* Contructor. Loads an object's values if an object ID is specified 
	* @param	mixed		$id		An object ID
	*/
	function ProviderContact ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}

	
	/** Loads a provider contact and its phone numbers */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				$this->phones = ProviderContactPhone::get_phones (array('contact_id' => $this->id));
			}
		}
	}
	
	
	/** Checks if the information is valid for the contact */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->fname) {error_msg ('Please specify the contact first name.'); $ret = false;}
		if (!$this->provider_id) {error_msg ('Please specify the provider for this contact.'); $ret = false;}

		return $ret;
	}
	
	
	/** Deletes a contact and its associated phone numbers */
	function delete ()
	{
		if ($this->id)
		{
			for ($i=0; $i<count($this->phones); $i++)
			{
				$this->phones[$i]->delete ();
			}
			
			parent::delete ();
		}
	}
	
	
	/**
	* [Class Method] Return the provider contacts defined in the system according to a specified criteria
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- provider_id: Get the contacts from the speciefied provider
	* @return	array(ProviderContact)			Array of ProviderContact objects
	*/
	public static function get_contacts ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT c.id FROM '.TBL_PROVIDERS_CONTACTS.' c WHERE ';
		if ($filter['provider_id']) $q.= 'c.provider_id = '.$filter['provider_id'].' AND ';
		
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		
		$q.= 'ORDER BY c.fname';
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new ProviderContact($id);
		
		return $ret;
	}

}

?>
<?php

class_load ('SoftwareLicense');

/**
* Class for storing and managing serial numbers for software licenses
*
*/

class SoftwareLicenseSN extends Base
{
	/** Object ID
	* @var int */
	var $id = null;
	
	/** The license ID 
	* @var int */
	var $license_id = null;
	
	/** The serial number
	* @var string */
	var $sn = '';
	
	/** Comments about this serial number
	* @var text */
	var $comments = '';
	
	
	/** The database table for storing the licenses information
	* @var string */
	var $table = TBL_SOFTWARE_LICENSES_SN;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'license_id', 'sn', 'comments');
	
	
	/** Class constructor, also loads the object data if an ID is provided */
	function SoftwareLicenseSN ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}

	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$valid = true;
		
		if (!$this->sn) {error_msg ('Please enter a serial number.'); $valid = false;}
		
		return $valid;
	}
	
	
	/** [Class Method] Return serial numbers according to a criteria
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- license_id : Return serial numbers for the specified license
	* @return	array(SoftwareLicenseSN)		Array with the matched serial numbers
	*/
	public static function get_serial_numbers ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT DISTINCT s.id FROM '.TBL_SOFTWARE_LICENSES_SN.' s WHERE ';
		
		if ($filter['license_id']) $q.= 's.license_id='.$filter['license_id'].' AND ';
		
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q.= 'ORDER BY s.sn ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new SoftwareLicenseSN ($id);
		
		return $ret;
	}
}

?>
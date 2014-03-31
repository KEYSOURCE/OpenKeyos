<?php
/**
* Class for storing and manipulating information about service levels
*
*/

class_load ('Supplier');

class ServiceLevel extends Base
{
	/** Service level ID
	* @var int */
	var $id = null;
	
	/** Service level name
	* @var string */
	var $name = '';
	
	/** Service level description
	* @var text */
	var $description = '';
		

	/** The database table storing service levels data
	* @var string */
	var $table = TBL_SERVICE_LEVELS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'name', 'description');

	
	/**
	* Constructor, also loads the object data from the database if an object ID is specified
	* @param	int $id		The object ID
	*/
	function ServiceLevel ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->name) {error_msg('Please specify the service level name.'); $ret = false;}
		if (!$this->description) {error_msg('Please specify the service level description.'); $ret = false;}
		
		return $ret;
	}
	
	
	/** [Class Method] Returns a list of the service levels defined in the system */
	public static function get_service_levels_list ()
	{
		$q = 'SELECT id, name FROM '.TBL_SERVICE_LEVELS.' ORDER BY name ';
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
	
	/**
	* [Class Method] Return the service levels defined in the system
	*/
	function get_service_levels ()
	{
		$ret = array ();
		
		$q = 'SELECT l.id FROM '.TBL_SERVICE_LEVELS.' l ORDER by l.name ';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new ServiceLevel ($id);
		
		return $ret;
	}
	
}
?>
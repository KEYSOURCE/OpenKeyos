<?php
class_load ('KawacsAgent');

/**
* Class for storing and managing the computers which should get pre-release versions of the
* KawacsAgent.
*
*/

class KawacsAgentUpdatePreview extends Base
{
	/** Object ID
	* @var int */
	var $id = null;
	
	/** The ID of the update object to which this relates
	* @var int */
	var $update_id = null;
	
	/** The ID of the designated computer
	* @var string */
	var $computer_id = null;
	
	
	/** The current Kawacs Agent version information. Loaded on request with load_computer_data()
	* @var array */
	var $current_version = array ();
	
	/** The associated Computer object. Loaded on request with load_computer_data ()
	* @var Computer */
	var $computer = null;
	
	/** The associated Customer object for the computer. Loaded on request with load_computer_data ()
	* @var Customer */
	var $customer = null;
	
	
	/** The database table storing object data 
	* @var string */
	var $table = TBL_KAWACS_AGENT_UPDATES_PREVIEWS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'update_id', 'computer_id');

	
	/**
	* Constructor, also loads the object data from the database if an ID is specified
	* @param	int $id		The object ID
	*/
	function KawacsAgentUpdatePreview ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	/** Loads the associated computer and customer data */
	function load_computer_data ()
	{
		if ($this->computer_id)
		{
			$this->computer = new Computer ($this->computer_id);
			$this->customer = new Customer ($this->computer->customer_id);
			
			$current_version = KawacsAgentUpdate::get_computers_versions ($this->computer_id);
			foreach ($current_version as $file_id => $versions)
			{
				$version = array_keys($versions);
				$version = $version[0];
				$this->current_version[$file_id] = $version;
			}
		}
	}
}

?>

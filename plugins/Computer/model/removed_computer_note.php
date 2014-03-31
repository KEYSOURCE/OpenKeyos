<?php
class_load ('RemovedComputer');
class_load ('ComputerNote');

/**
* Classes for storing and managing free-text notes about removed computers
*
*/


class RemovedComputerNote extends ComputerNote
{
	/** The database table storing notes data 
	* @var string */
	var $table = TBL_REMOVED_COMPUTERS_NOTES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'computer_id', 'user_id', 'created', 'note');

	
	/**
	* Constructor, also loads the data from the database if an ID is specified
	* @param	int	$id			The ID of the note.
	*/
	function RemovedComputerNote ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data () {return parent::is_valid_data();}
	
	/** Save the object data and sets the creation time if it was empty */
	function save_data () {return parent::save_data();}
	
	/** Get the notes for a certain removed computer 
	* @param	int			$computer_id		The ID of the computer
	* @return	array(ComputerNote)				Array with the matched ComputerNote object, sorted by date
	*/
	function get_computer_notes ($computer_id)
	{
		$ret = array ();
		
		if ($computer_id)
		{
			$q = 'SELECT id FROM '.TBL_REMOVED_COMPUTERS_NOTES.' WHERE computer_id='.$computer_id.' ORDER BY created DESC ';
			$ids = DB::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new RemovedComputerNote ($id);
		}
		
		return $ret;
	}
}

?>
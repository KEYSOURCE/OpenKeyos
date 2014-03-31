<?php
class_load ('Computer');

/**
* Classes for storing and managing free-text notes about computers
*
*/


class ComputerNote extends Base
{
	/** The note ID
	* @var int */
	var $id = null;
	
	
	/** The compute to which this note refers to
	* @var int */
	var $computer_id = null;
	
	/** The ID of the user who created this note
	* @var int */
	var $user_id = null;
	
	/** When was the note created
	* @var timestamp */
	var $created = 0;
	
	/** The text of the note
	* @var text */
	var $note = '';
	
	
	/** The database table storing notes data 
	* @var string */
	var $table = TBL_COMPUTERS_NOTES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'computer_id', 'user_id', 'created', 'note');

	
	/**
	* Constructor, also loads the data from the database if an ID is specified
	* @param	int	$id			The ID of the note.
	*/
	function ComputerNote ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->note) {error_msg($this->get_string('NEED_NOTE_TEXT')); $ret = false;}
		if (!$this->computer_id) {error_msg($this->get_string('NEED_NOTE_COMPUTER')); $ret = false;}
		
		return $ret;
	}
	
	
	/** Save the object data and sets the creation time if it was empty */
	function save_data ()
	{
		if (!$this->created) $this->created = time ();
		parent::save_data ();
	}
	
	
	/** Get the notes for a certain computer 
	* @param	int		$computer_id		The ID of the computer
	* @return	array(ComputerNote)			Array with the matched ComputerNote object, sorted by date
	*/
	public static function get_computer_notes($computer_id)
	{
		$ret = array ();
		
		if ($computer_id)
		{
			$q = 'SELECT id FROM '.TBL_COMPUTERS_NOTES.' WHERE computer_id='.$computer_id.' ORDER BY created DESC ';
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new ComputerNote($id);
		}
		
		return $ret;
	}
}

?>
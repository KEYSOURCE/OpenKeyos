<?php
class_load ('Ticket');

/**
* Class for managing KRIFS (technical support) ticket attachments.
*
* The uploaded file is created in the uploads directory upon invoking load_from_array(),
* NOT upon calling save_data (). The save_data() method only takes care of saving the
* data into the database.
*
* Accordingly, the delete() method will check for the file to be deleted even if the
* object has not been saved to database (doesn't have an ID).
*/

class TicketAttachment extends Base
{
	/** Attachment ID
	* @var int */
	var $id = null;
	
	/** The ID of the ticket to which this attachment belongs to
	* @var int */
	var $ticket_id = null;
	
	/** The time when the attachment was uploaded
	* @var time */
	var $uploaded = 0;
	
	/** The original name of the file that was uploaded
	* @var string */
	var $original_filename = '';
	
	/** The local stored file name 
	* @var string */
	var $local_filename = '';
	
	/** The ID of the user who uploaded the file 
	* @var integer */
	var $user_id = null;
	
	
	/** The User object who uploaded this attachment
	* @var User */
	var $user = null;
	
	
	var $table = TBL_TICKETS_ATTACHMENTS;
	var $fields = array ('id', 'ticket_id', 'uploaded', 'original_filename', 'local_filename', 'user_id');


	/**
	* Constructor. Also loads a ticket attachment data if an ID is provided
	* @param	int	$id		The ID of the ticket attachment to load
	*/
	function TicketAttachment ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	
	/** Loads the object data */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				// Load the User object who created this attachment
				if ($this->user_id) $this->user = new User ($this->user_id);
			}
		}
	}
	
	
	/**
	* Loads the attachment data from an array and creates the local copy of the uploaded file
	* uploaded file.
	* @param	array		$data		Associative array with the details of an uploaded file.
	*						It must contain the following keys:
	*						- name : the name of the file
	*						- tmp_name : the temporary name where the uploaded file was saved
	*						- ticket_id : the ID of the ticket to which this attachment belong
	*						- user_id : the ID of the user who uploaded this file
	*/
	function load_from_array ($data = array(), $upl = true)
	{
		if ($data['name'] and $data['tmp_name'] and file_exists($data['tmp_name']) and $data['ticket_id'] and $data['user_id'])
		{
			$this->original_filename = $data['name'];
			$this->ticket_id = $data['ticket_id'];
			$this->user_id = $data['user_id'];
			$this->uploaded = time();
			$this->local_filename = $this->generate_name ();
			
			if($upl)
			{
				move_uploaded_file ($data['tmp_name'], $this->local_filename);
			}
			else {
				rename($data['tmp_name'], $this->local_filename);
			}

			if (!file_exists($this->local_filename)) $this->local_filename = '';
		}
	}
	
	
	/** Generates a unique file name for storing an uploaded ticket attachment */
	function generate_name ()
	{
		$name = 'ticket_'.$this->ticket_id.'_';
		$name = tempnam (DIR_UPLOAD_KRIFS, $name);
		
		return $name;
	}
	
	
	/** Checks if the attachment data is valid - meaning if the local file has been properly created */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->local_filename or ($this->local_filename and !file_exists($this->local_filename)))
		{
			error_msg ($this->get_string('UPLOADING_FAILED'));
			$ret = false;
		}
		
		return $ret;
	}
	
	
	/** Deletes an object from database and the attachment file from disk */
	function delete ()
	{
		// Delete the attachment file from disk, if it exists
		if ($this->local_filename)
		{
			if (file_exists($this->local_filename)) @unlink ($this->local_filename);
		}
		
		// Delete the object from database, if exists
		if ($this->id)
		{
			parent::delete ();
		}
	}
	
	
	/** Returns a "friendly" string with the size of the file */
	function get_size_str ()
	{
		$ret = '?';
		if ($this->local_filename and file_exists($this->local_filename))
		{
			$ret = get_memory_string (filesize($this->local_filename), true);
		}
		return $ret;
	}
}
?>
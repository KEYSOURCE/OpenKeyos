<?php

class_load ('SoftwareLicense');

/**
* Class for storing and managing files attached to licenses
*
*/

class SoftwareLicenseFile extends Base
{
	/** Object ID
	* @var int */
	var $id = null;
	
	/** The license ID 
	* @var int */
	var $license_id = null;
	
	/** The time when the file was uploaded
	* @var time */
	var $uploaded = 0;
	
	/** The original name of the uploaded file
	* @var string */
	var $original_filename = '';
	
	/** The name of the file stored on the local disk
	* @var string */
	var $local_filename = '';
	
	/** Comments about this file
	* @var text */
	var $comments = '';
	
	
	/** The database table for storing the object information
	* @var string */
	var $table = TBL_SOFTWARE_LICENSES_FILES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'license_id', 'uploaded', 'original_filename', 'local_filename', 'comments');
	
	
	/** Class constructor, also loads the object data if an ID is provided */
	function SoftwareLicenseFile ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}

	/**
	* Loads the file data from an array and creates the local copy of the uploaded file
	* @param	array		$data		Associative array with the details of an uploaded file.
	*						It must contain the following keys:
	*						- name : the name of the file
	*						- tmp_name : the temporary name where the uploaded file was saved
	*						- license_id : the ID of the software licenses to which this attachment belongs
	*/
	function load_from_array ($data = array())
	{
	
		if ($data['name'] and $data['tmp_name'] and file_exists($data['tmp_name']))
		{
			// An uploaded file was provided
			
			// Delete previous file if it exists
			if ($this->local_filename and file_exists(DIR_UPLOAD_KALM.'/'.$this->local_filename))
			{
				@unlink(DIR_UPLOAD_KALM.'/'.$this->local_filename);
			}
			
			$this->original_filename = $data['name'];
			$this->uploaded = time();
			$this->local_filename = $this->generate_name ();
			
			move_uploaded_file ($data['tmp_name'], DIR_UPLOAD_KALM.'/'.$this->local_filename);
			if (!file_exists(DIR_UPLOAD_KALM.'/'.$this->local_filename)) $this->local_filename = '';
		}
		
		// Load additional fields, if provided
		parent::load_from_array ($data);
	}
	
	
	/** Generates a unique file name for storing an uploaded license file */
	function generate_name ()
	{
		$name = FILE_PREFIX_LICENSE_FILE.$this->license_id.'_';
		$name = basename(tempnam (DIR_UPLOAD_KALM, $name));
		
		return $name;
	}
	
	
	/** Checks if the attachment data is valid - meaning if the local file has been properly created */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->local_filename or ($this->local_filename and !file_exists(DIR_UPLOAD_KALM.'/'.$this->local_filename)))
		{
			error_msg ('The uploading of the attachment has failed. Please try again.');
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
			if (file_exists(DIR_UPLOAD_KALM.'/'.$this->local_filename)) @unlink (DIR_UPLOAD_KALM.'/'.$this->local_filename);
		}
		
		// Delete the object from database, if exists
		if ($this->id)
		{
			parent::delete ();
		}
	}
	
	
	/** [Class Method] Return license files according to a criteria
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- license_id : Return files for the specified license
	* @return	array(SoftwareLicenseFile)		Array with the matched files
	*/
	public static function get_files ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT DISTINCT f.id FROM '.TBL_SOFTWARE_LICENSES_FILES.' f WHERE ';
		
		if ($filter['license_id']) $q.= 'f.license_id='.$filter['license_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY f.uploaded ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new SoftwareLicenseFile($id);
		
		return $ret;
	}
}

?>
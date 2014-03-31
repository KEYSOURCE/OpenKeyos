<?php
class_load ('CustomerInternetContract');

/**
* Class for managing attachments for customer Internet contracts
*
* The uploaded file is created in the uploads directory upon invoking load_from_array(),
* NOT upon calling save_data (). The save_data() method only takes care of saving the
* data into the database.
*
* Accordingly, the delete() method will check for the file to be deleted even if the
* object has not been saved to database (doesn't have an ID).
*/

class CustomerInternetContractAttachment extends Base
{
	/** Attachment ID
	* @var int */
	var $id = null;
	
	/** The ID of the customer internet contract to which this attachment belongs
	* @var int */
	var $customer_internet_contract_id = null;
	
	/** The time when the attachment was uploaded
	* @var time */
	var $uploaded = 0;
	
	/** The original name of the file that was uploaded
	* @var string */
	var $original_filename = '';
	
	/** The local stored file name (without the path)
	* @var string */
	var $local_filename = '';	
	
	var $table = TBL_CUSTOMERS_INTERNET_CONTRACTS_ATTACHMENTS;
	var $fields = array ('id', 'customer_internet_contract_id', 'uploaded', 'original_filename', 'local_filename');


	/**
	* Constructor. Also loads the object data if an object ID is provided
	* @param	int	$id		The ID of the contract attachment to load
	*/
	function CustomerInternetContractAttachment ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	
	/**
	* Loads the attachment data from an array and creates the local copy of the uploaded file
	* uploaded file.
	* @param	array		$data		Associative array with the details of an uploaded file.
	*						It must contain the following keys:
	*						- name : the name of the file
	*						- tmp_name : the temporary name where the uploaded file was saved
	*						- contract_id : the ID of the customer internet contract to which this attachment belongs
	*/
	function load_from_array ($data = array())
	{
		if ($data['name'] and $data['tmp_name'] and file_exists($data['tmp_name']) and $data['contract_id'])
		{
			$this->original_filename = $data['name'];
			$this->customer_internet_contract_id = $data['contract_id'];
			$this->uploaded = time();
			$this->local_filename = $this->generate_name ();
		
			move_uploaded_file ($data['tmp_name'], DIR_UPLOAD_KLARA.'/'.$this->local_filename);
			if (!file_exists(DIR_UPLOAD_KLARA.'/'.$this->local_filename)) $this->local_filename = '';
		}
	}
	
	
	/** Generates a unique file name for storing an uploaded ticket attachment */
	function generate_name ()
	{
		$name = FILE_PREFIX_INTERNET_CONTRACTS.$this->customer_internet_contract_id.'_';
		$name = basename(tempnam (DIR_UPLOAD_KLARA, $name));
		
		return $name;
	}
	
	
	/** Checks if the attachment data is valid - meaning if the local file has been properly created */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->local_filename or ($this->local_filename and !file_exists(DIR_UPLOAD_KLARA.'/'.$this->local_filename)))
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
			if (file_exists(DIR_UPLOAD_KLARA.'/'.$this->local_filename)) @unlink (DIR_UPLOAD_KLARA.'/'.$this->local_filename);
		}
		
		// Delete the object from database, if exists
		if ($this->id)
		{
			parent::delete ();
		}
	}
	
	
	/**
	* [Class Method] Returns attachments according to a specified criteria
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	* 							- customer_internet_contract_id: The ID of a customer contract
	* @return	array(CustomerInternetContractAttachment)
	*/
	public static function get_attachments ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT a.id FROM '.TBL_CUSTOMERS_INTERNET_CONTRACTS_ATTACHMENTS.' a WHERE ';
		
		if ($filter['customer_internet_contract_id']) $q.= 'a.customer_internet_contract_id='.$filter['customer_internet_contract_id'].' AND ';
		
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q.= 'ORDER BY a.uploaded ';
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new CustomerInternetContractAttachment($id);
		
		return $ret;
	}
	
}
?>
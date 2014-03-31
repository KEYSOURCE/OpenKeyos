<?php
class_load ('KawacsAgentUpdate');

/**
* Class for storing and manipulating information about the files
* included in Kawacs Agent releases.
*
*/

class KawacsAgentUpdateFile extends Base
{
	/** Version ID
	* @var int */
	var $version_id = null;
	
	/** File ID - see $GLOBALS['KAWACS_AGENT_FILES']
	* @var int */
	var $file_id = null;
	
	/** The .exe/.dll file version
	* @var string */
	var $version = '';
	
	/** MD5 cheksum for the file
	* @var string */
	var $md5 = null;
		
		
	/** The databas table storing customer data 
	* @var string */
	var $table = TBL_KAWACS_AGENT_UPDATES_FILES;
	
	/** The database primary keys for this object */
	var $primary_key = array ('version_id', 'file_id');
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('version_id', 'file_id', 'version', 'md5');

	
	/**
	* Constructor, also loads the file details if the IDs are specified
	* @param	int $version_id		The ID of the release to which this file belongs
	* @param	int $file_id		The id of the file 
	*/
	function KawacsAgentUpdateFile ($version_id = null, $file_id = null)
	{
		if ($version_id) $this->version_id = $version_id;
		if ($file_id) $this->file_id = $file_id;
		if ($this->version_id and $this->file_id)
		{
			$this->load_data();
		}
	}
	

	/**
	* Recreates the Zip archive for the file and recalculates the MD5 checksum.
	* This method must be invoked every time a new file is uploaded for this object
	*/
	function remake_zip ()
	{
		if ($this->version_id and $this->file_id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->version_id.'/';
			$file = $GLOBALS['KAWACS_AGENT_FILES'][$this->file_id];

			if (file_exists($dir.$file))
			{
				$zip_file = $file.'.zip';
				if (file_exists($zip_file)) @unlink ($zip_file);
				
				chdir ($dir);
				$zip_command = PATH_TO_ZIP.' -0 '.$zip_file.' '.$file;
				$md5_command = PATH_TO_MD5SUM.' '.$dir.$zip_file;
				
				exec ($zip_command);
				$md5 = exec ($md5_command);
				
				$this->md5 = trim(preg_replace('/ .*$/', '', $md5));
			}
		}
	}

	
	/** Recalculates the MD5 checksum for the file */
	function remake_md5 ()
	{
		if ($this->version_id and $this->file_id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->version_id.'/';
			$file = $GLOBALS['KAWACS_AGENT_FILES'][$this->file_id];

			if (file_exists($dir.$file))
			{
				$zip_file = $file.'.zip';
				$md5_command = PATH_TO_MD5SUM.' '.$dir.$zip_file;
				
				$md5 = exec ($md5_command);
				$md5 = trim(preg_replace('/ .*$/', '', $md5));
				
				if ($this->md5 <> $md5)
				{
					$this->md5 = $md5;
					$this->save_data ();
				}
			}
		}
	}
	
	
	/**
	* Returns the URL for downloading the file 
	*/
	function get_download_url ()
	{
		$ret = '';
		if ($this->version_id and $this->file_id)
		{                        
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->version_id.'/';
			$file = $GLOBALS['KAWACS_AGENT_FILES'][$this->file_id];
			$zip_file = $file.'.zip';                        
			if (file_exists($dir.$zip_file))
			{
				$ret = get_base_url().'/'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->version_id.'/'.$zip_file;
			}
		}
		
		return $ret;
	}
	
	/**
	* Deletes a file from the release
	*/
	function delete ()
	{
		if ($this->version_id and $this->file_id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->version_id.'/';
			$file = $GLOBALS['KAWACS_AGENT_FILES'][$this->file_id];
			$zip_file = $file.'.zip';

			if (file_exists($dir.$file)) @unlink ($dir.$file);
			if (file_exists($dir.$zip_file)) @unlink ($dir.$zip_file);
		}
		parent::delete();
	}
	
}

?>

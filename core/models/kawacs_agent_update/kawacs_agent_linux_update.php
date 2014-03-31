<?

/**
* Class for storing and manipulating details of Kawacs Agent Linux updates
*
* Unlike the Kawacs Agent for Windows, the Linux Agent is distributed
* as a single file, therefore there is no need for a second class for
* storing details of multiple files.
*
* The files with the "installation kits" will be stored in the directory
* specified by the UPDATES_DIR_KAWACS_AGENT_LINUX constant, in sub-directories
* using as names the IDs of the corresponding KawacsAgentLinuxUpdate
* objects.
*/

class KawacsAgentLinuxUpdate extends Base
{
	/** Version ID
	* @var int */
	var $id = null;
	
	/** The global version string for this release
	* @var string */
	var $version = '';
	
	/** Comments about this release
	* @var string */
	var $comments = null;
	
	/** If this release has been published
	* @var boolean */
	var $published = false;
		
	/** The date when this release was created
	* @var timestamp */
	var $date_created = 0;
	
	/** The date when this release was published
	* @var timestamp */
	var $date_published = 0;
	
	/** The MD5 checksum of the package
	* @var string */
	var $md5 = NULL;
	
		
	/** The databas table storing release data 
	* @var string */
	var $table = TBL_KAWACS_AGENT_LINUX_UPDATES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'version', 'comments', 'published', 'date_created', 'date_published', 'md5');

	
	/**
	* Constructor, also loads the release data from the database if an ID is specified
	* @param	int $id		The release ID
	*/
	function KawacsAgentLinuxUpdate ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
		
	/** Loads the version data from the database */
	function load_data ()
	{
		$ret = false;
		if ($this->id)
		{
			parent::load_data();
			$this->comments = stripslashes ($this->comments);
		}
		return $ret;
	}

	
	/** Checks if the data for this object is valid */
	function is_valid_data ()
	{
		$ret = true;
		if (empty($this->version)) {error_msg('Please specify the version for this release'); $ret = false;}
		return $ret;
	}
	
	
	/**
	* Processes the upload of the installation kit (provided as tar.gz file)
	* @param	string	$fld_name	The name of the form field containing the upload file info
	*/
	function process_installer_upload ($fld_name = '')
	{
		if ($this->id and $fld_name and isset($_FILES[$fld_name]))
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT_LINUX.'/'.$this->id;
			if ($_FILES[$fld_name]['size'] > 0)
			{
				$tmp_name = $_FILES[$fld_name]['tmp_name'];
				copy ($tmp_name, $dir.'/'.FILE_NAME_KAWACS_INSTALLER_LINUX);
				@unlink($tmp_name);
				
				$this->remake_md5 ();
			}
		}
	}
	
	
	/**
	* Returns the URL for downloading the installation kit
	*/
	function get_installer_url ()
	{
		$ret = '';
		if ($this->id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT_LINUX.'/'.$this->id.'/';
			if (file_exists($dir.FILE_NAME_KAWACS_INSTALLER_LINUX))
			{
				$ret = get_base_url().'/'.UPDATES_DIR_KAWACS_AGENT_LINUX.'/'.$this->id.'/'.FILE_NAME_KAWACS_INSTALLER_LINUX;
			}
		}
		return $ret;
	}
	
	
	/**
	* Saves the data for this Kawacs Agent update. It creates the needed directories 
	* if they don't exist and also save files information if they are defined
	*/
	function save_data ()
	{
		if (!$this->date_created) $this->date_created = time();
		parent::save_data();
		
		if ($this->id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT_LINUX.'/'.$this->id;
			if (!file_exists($dir)) mkdir ($dir,  0775);
		}
	}
	
	
	/**
	* Checks if this release meets the criteria for being published
	*/
	function is_valid_for_publishing ()
	{
		$ret = false;
		if ($this->id)
		{
			$ret = true;
			if ($this->get_installer_url () == '')
			{
				$ret = false;
				error_msg ('The installer file has not been uploaded, this release can\'t be published');
			}
		}
		return $ret;
	}
	
	
	/**
	* Marks this release as published. When that happends, the Kawacs Agents clients will
	* start auto-updating themselves
	*/
	function publish ()
	{
		if ($this->id)
		{
			// Make sure that the MD5 checksums are OK
			$this->remake_md5();
			$this->published = true;
			$this->date_published = time ();
			
			$this->save_data ();
		}
	}
	
	
	/** 
	* Generates the MD5 checksum for the installer file
	*
	* Note that the MD5 is updated only in the object file, it is not saved in the 
	* database here. The save_data() method should be called to save it.
	*/
	function remake_md5 ()
	{
		if ($this->id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT_LINUX.'/'.$this->id.'/';
			$file = FILE_NAME_KAWACS_INSTALLER_LINUX;

			if (file_exists($dir.$file))
			{
				$md5_command = PATH_TO_MD5SUM.' '.$dir.$file;
				
				$md5 = exec ($md5_command);
				$md5 = trim(preg_replace('/ .*$/', '', $md5));
				
				$this->md5 = $md5;
			}
			else
			{
				$this->md5 = '';
			}
		}
	}
	
	
	/**
	* Deletes the information about a Kawacs Agent release
	*/
	function delete ()
	{
		if ($this->id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT_LINUX.'/'.$this->id;
			
			// Delete the directory and all the included files
			if (file_exists($dir))
			{
				$dirh = opendir($dir);
				while (false !== ($file = readdir($dirh)))
				{ 
					if ($file != '..' and $file != '.') unlink($dir.'/'.$file);
				}
				closedir ($dirh);
				rmdir ($dir);
			}
			
			parent::delete();
		}
	}
	
	
	/**
	* Compares a given version with the version of the corresponding file from this release.
	* @param	string	$version		The version to compare
	* @result	boolean				True if the given version is older than the current one
	*/
	function is_lower_version ($version)
	{
		$ret = false;
			
		$c_version = $this->version;
		$c_version_arr = preg_split ('/\.\s*/', $c_version);
		
		$version_arr = preg_split ('/\.\s*/', $version);
		
		for ($i=0; ($i<count($version_arr) and !$ret); $i++)
		{
			if (intval($version_arr[$i]) < intval($c_version_arr[$i])) $ret = true; 
			elseif (intval($version_arr[$i]) > intval($c_version_arr[$i])) $i = count($version_arr);
		}
		
		return $ret;
	}
	
	
	/**
	* Returns the URL for downloading the installer file 
	*/
	function get_download_url ()
	{
		$ret = '';
		if ($this->id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT_LINUX.'/'.$this->id.'/';
			$file = FILE_NAME_KAWACS_INSTALLER_LINUX;
		
			if (file_exists($dir.$file))
			{
				$ret = 'http://'.get_base_url().'/'.UPDATES_DIR_KAWACS_AGENT_LINUX.'/'.$this->id.'/'.$file;
			}
		}
		
		return $ret;
	}

	
	/** [Class Method] Returns a list of existing versions in the database */
	function get_updates_list ()
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_KAWACS_AGENT_LINUX_UPDATES.' ';
		$q.= 'ORDER BY date_created DESC ';
		$ids = db::db_fetch_array ($q);
		
		foreach ($ids as $id)
		{
			$ret[] = new KawacsAgentLinuxUpdate ($id->id);
		}
		return $ret;
	}
	
	
	/** [Class Method] Returns the latest published release */
	function get_current_release ()
	{
		$ret = null;
		
		$q = 'SELECT id FROM '.TBL_KAWACS_AGENT_LINUX_UPDATES.' WHERE published=1 ORDER BY date_published DESC LIMIT 1';
		$id = db::db_fetch_field ($q, 'id');
		
		if ($id) $ret = new KawacsAgentLinuxUpdate ($id);
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns statistics about what agent versions are the clients reporting 
	*
	* @return	array				Associative array, witht the indexes being Kawacs Linux versions,
	*						and the values being the number of computers using that version.
	*/
	function get_computers_versions ()
	{
		$ret = array ();

		// Just in case, delete records for computers that don't exist anymore
		$q = 'SELECT distinct a.computer_id FROM '.TBL_COMPUTERS_AGENT_LINUX_VERSIONS.' a LEFT JOIN '.TBL_COMPUTERS .' c  ON ';
		$q.= 'a.computer_id=c.id WHERE c.id IS NULL ';
		$to_delete = db::db_fetch_array ($q);
		foreach ($to_delete as $del) db::db_query ('DELETE FROM '.TBL_COMPUTERS_AGENT_LINUX_VERSIONS.' WHERE computer_id='.$del->computer_id);
		
		$q = 'SELECT version, count(*) as cnt FROM '.TBL_COMPUTERS_AGENT_LINUX_VERSIONS.' ';
		$q.= 'GROUP BY version ORDER BY version ';
		$stats = db::db_fetch_array ($q);
		
		foreach ($stats as $stat) $ret[$stat->version] = $stat->cnt;
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of computers using a specified version of the Linux Agent
	*
	* @param	string		$version	The version for which computers will be fetched
	* @return	array				Array of generic objects with computer information, fields:
	*						id (computer ID), name (computer name), customer_name and customer_id
	*/
	function get_computers_versions_details ($version)
	{
		class_load ('Computer');
		$ret = array ();
		
		$q = 'SELECT c.id, cust.id as customer_id, cust.name as customer_name, c.netbios_name as name ';
		$q.= 'FROM '.TBL_COMPUTERS .' c LEFT OUTER JOIN '.TBL_COMPUTERS_AGENT_LINUX_VERSIONS.' a  ON ';
		$q.= 'c.id=a.computer_id LEFT OUTER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
		$q.= 'WHERE a.version="'.$version.'" ';
		$q.= 'ORDER BY cust.name ';
		$ret = db::db_fetch_array ($q);
		
		return $ret;
	}
	
}

?>
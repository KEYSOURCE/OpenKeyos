<?php

class_load ('KawacsAgentUpdateFile');
class_load ('KawacsAgentUpdatePreview');

/**
* Class for storing and manipulating details of Kawacs Agent updates
*
*/

class KawacsAgentUpdate extends Base
{
	/** Version ID
	* @var int */
	var $id = null;
	
	/** The global version string for this release
	* @var string */
	var $gen_version = '';
	
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
	
	
	/** An array with all the file details for this release
	* @var array(KawacsAgentUpdateFile) */
	var $files = array();
	
	/** An array with the all the computers that should get pre-release installs
	* @var array(KawacsAgentUpdatePreview) */
	var $previews = array();
	
	
	/** The databas table storing release data 
	* @var string */
	var $table = TBL_KAWACS_AGENT_UPDATES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'gen_version', 'comments', 'published', 'date_created', 'date_published');

	
	/**
	* Constructor, also loads the release data from the database if an ID is specified
	* @param	int $id		The release ID
	*/
	function KawacsAgentUpdate ($id = null)
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
			if ($this->id)
			{
				$ret = true;
				// Load the files
				$q = 'SELECT version_id, file_id FROM '.TBL_KAWACS_AGENT_UPDATES_FILES.' WHERE version_id='.$this->id.' ';
				$q.= 'ORDER BY file_id ';
				$file_ids = $this->db_fetch_array ($q);
				foreach ($file_ids as $file_id)
				{
					$this->files[$file_id->file_id] = new KawacsAgentUpdateFile ($this->id, $file_id->file_id);
				}
				
				// Load the previews
				$q = 'SELECT p.id FROM '.TBL_KAWACS_AGENT_UPDATES_PREVIEWS.' p INNER JOIN '.TBL_COMPUTERS.' c ';
				$q.= 'ON p.computer_id=c.id INNER JOIN '.TBL_CUSTOMERS.' cust on c.customer_id=cust.id ';
				$q.= 'WHERE p.update_id='.$this->id.' ';
				$q.= 'ORDER BY cust.name, c.netbios_name ';
				$ids = $this->db_fetch_vector ($q);
				foreach ($ids as $id) $this->previews[] = new KawacsAgentUpdatePreview ($id);
			}
			$this->comments = stripslashes ($this->comments);
		}
		return $ret;
	}

	
	/** Checks if the data for this object is valid */
	function is_valid_data ()
	{
		$ret = true;
		if (empty($this->gen_version)) {error_msg('Please specify the general version for this release'); $ret = false;}
		return $ret;
	}
	
	
	/**
	* Processes the upload of the installation kit (provided as Zip file)
	* @param	string	$fld_name	The name of the form field containing the upload file info
	*/
	function process_installer_upload ($fld_name = '')
	{
		if ($this->id and $fld_name and isset($_FILES[$fld_name]))
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->id;
			if ($_FILES[$fld_name]['size'] > 0)
			{
				$tmp_name = $_FILES[$fld_name]['tmp_name'];
				copy ($tmp_name, $dir.'/'.FILE_NAME_KAWACS_INSTALLER);
				@unlink($tmp_name);
			}
		}
	}
	
	
	/**
	* Processes the uploads for release files 
	* @param	string	$fld_name	The name of the form field containing the upload files info
	* @param	array	$file_versions	Array with the versions of the updloaded file
	*/
	function process_files_uploads ($fld_name = '', $file_versions = array())
	{
		if ($this->id and $fld_name and isset($_FILES[$fld_name]))
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->id;
			foreach ($_FILES[$fld_name]['name'] as $file_id => $file_name)
			{
				$update_file = new KawacsAgentUpdateFile ($this->id, $file_id);
				
				if (!$update_file->version_id)
				{
					// This is a new file upload
					$update_file->version_id = $this->id;
					$update_file->file_id = $file_id;
					$update_file->save_data();
				}
				
				if ($_FILES[$fld_name]['size'][$file_id] > 0)
				{
					
					$tmp_name = $_FILES[$fld_name]['tmp_name'][$file_id];
					$expected_file_name = $GLOBALS['KAWACS_AGENT_FILES'][$file_id];
					
					if ($expected_file_name == $file_name)
					{
						copy ($tmp_name, $dir.'/'.$file_name);
						$update_file->remake_zip ();
					}
					else
					{
						error_msg ('Wrong file uploaded. Expected '.$expected_file_name.', got '.$file_name);
					}
					@unlink($tmp_name);
				}
				
				$update_file->version = $file_versions[$file_id];
				$update_file->save_data ();
				$this->files[$file_id] = $update_file;
			}
		}
	}
	
	
	/**
	* Returns the URL for downloading the installation kit
	*/
	function get_installer_url ()
	{
		if ($this->id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->id.'/';
			if (file_exists($dir.FILE_NAME_KAWACS_INSTALLER))
			{
				$ret = get_base_url().'/'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->id.'/'.FILE_NAME_KAWACS_INSTALLER;
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
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->id;
			if (!file_exists($dir)) mkdir ($dir,  0775);
			
			foreach ($this->files as $file)
			{
				$file->version_id = $this->id;
				$file->save_data();
			}
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
			foreach ($GLOBALS['KAWACS_AGENT_FILES'] as $file_id => $file_name)
			{
				if (isset($this->files[$file_id]))
				{
					$url = $this->files[$file_id]->get_download_url();
					if (!$url)
					{
						$ret = false;
						error_msg ('The file '.$file_name.' is missing from the download set.');
					}
					if (!$this->files[$file_id]->version)
					{
						$ret = false;
						error_msg ('The file '.$file_name.' has no version specified.');
					}
				}
				else
				{
					$ret = false;
					error_msg ('The file '.$file_name.' has not been uploaded yet');
				}
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
			foreach ($this->files as $file_id => $file) $file->remake_md5();
			
			$q = 'UPDATE '.$this->table.' SET published=1, date_published='.time().' ';
			$q.= 'WHERE id='.$this->id;
			$this->db_query ($q);
		}
	}
	
	/**
	* Deletes the information about a Kawacs Agent release
	*/
	function delete ()
	{
		if ($this->id)
		{
			$dir = dirname(__FILE__).'/../../'.UPDATES_DIR_KAWACS_AGENT.'/'.$this->id;
			
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
			
			foreach ($this->files as $file) $file->delete();
			
			// Delete the list of computers used for pre-release
			$q = 'DELETE FROM '.TBL_KAWACS_AGENT_UPDATES_PREVIEWS.' WHERE update_id='.$this->id;
			$this->db_query ($q);
			
			parent::delete();
		}
	}
	
	
	/**
	* Compares a given version with the version of the corresponding file from this release.
	* @param	string	$version		The version to compare
	* @param	int	$file_id		The ID of the file for which the check is done
	* @result	boolean				True if the given version is older than the current one
	*/
	function is_lower_version ($version, $file_id)
	{
		$ret = false;
		if (isset ($this->files[$file_id]))
		{
			$c_version = $this->files[$file_id]->version;
			$c_version_arr = preg_split ('/\.\s*/', $c_version);
			
			$version_arr = preg_split ('/\.\s*/', $version);
			
			for ($i=0; ($i<count($version_arr) and !$ret); $i++)
			{
				if (intval($version_arr[$i]) < intval($c_version_arr[$i])) $ret = true; 
				elseif (intval($version_arr[$i]) > intval($c_version_arr[$i])) $i = count($version_arr);
			}
		}
		
		return $ret;
	}

	/** Adds a computer to the list of pre-releases */
	function add_pre_release ($computer_id)
	{
		if ($this->id and $computer_id)
		{
			// Check if the computer is not already in the preview list
			$q = 'SELECT id FROM '.TBL_KAWACS_AGENT_UPDATES_PREVIEWS.' WHERE update_id='.$this->id.' AND computer_id='.$computer_id;
			if (!$this->db_fetch_field ($q, 'id'))
			{
				$p = new KawacsAgentUpdatePreview ();
				$p->update_id = $this->id;
				$p->computer_id = $computer_id;
				$p->save_data ();
				
				$this->load_data ();
			}
		}
	}
	
	/** [Class Method] Returns a list of existing versions in the database */
	function get_updates_list ()
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_KAWACS_AGENT_UPDATES.' ';
		$q.= 'ORDER BY date_created DESC ';
		$ids = db::db_fetch_array ($q);
		
		foreach ($ids as $id)
		{
			$ret[] = new KawacsAgentUpdate ($id->id);
		}
		return $ret;
	}
	
	
	/** [Class Method] Returns the latest published release */
	function get_current_release ($computer_id = null)
	{
		$ret = null;
		
		if ($computer_id)
		{
			// Check also if the specified computer was selected for pre-releas
			$q = 'SELECT u.id FROM '.TBL_KAWACS_AGENT_UPDATES.' u LEFT OUTER JOIN '.TBL_KAWACS_AGENT_UPDATES_PREVIEWS.' p ';
			$q.= 'ON u.id=p.update_id ';
			$q.= 'WHERE (u.published=1 or (u.published=0 and p.computer_id='.$computer_id.')) ORDER BY u.date_created DESC LIMIT 1';
		}
		else
		{
			$q = 'SELECT id FROM '.TBL_KAWACS_AGENT_UPDATES.' WHERE published=1 ORDER BY date_created DESC LIMIT 1';
		}
		
		//$q = 'SELECT id FROM '.TBL_KAWACS_AGENT_UPDATES.' WHERE published=1 ORDER BY date_published DESC ';
		$id = db::db_fetch_field ($q, 'id');
		
		if ($id) $ret = new KawacsAgentUpdate ($id);
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns statistics about what agent versions are the clients reporting 
	*
	* @return	array				Associative array, witht the indexes being file IDs and the elements
	*						being associative arrays, with files version numbers as indexes and counts as values.
	*/
	function get_computers_versions ($computer_id = null, $active_cust_only = false)
	{
		$ret = array ();
		
		// Just in case, delete records for computers that don't exist anymore
		$q = 'SELECT distinct a.computer_id FROM '.TBL_COMPUTERS_AGENT_VERSIONS.' a LEFT JOIN '.TBL_COMPUTERS .' c  ON ';
		$q.= 'a.computer_id=c.id WHERE c.id IS NULL ';
		$to_delete = db::db_fetch_array ($q);
		foreach ($to_delete as $del) db::db_query ('DELETE FROM '.TBL_COMPUTERS_AGENT_VERSIONS.' WHERE computer_id='.$del->computer_id);
		
		$q = 'SELECT v.file_id, v.version, count(distinct v.computer_id) as cnt FROM '.TBL_COMPUTERS_AGENT_VERSIONS.' v ';
		
		if ($active_cust_only) 
		{
			$q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON v.computer_id=c.id ';
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND cust.active=1 AND cust.onhold=0 ';
		}
		
		if ($computer_id) $q.= 'WHERE v.computer_id='.$computer_id.' ';
		$q.= 'GROUP BY v.file_id, v.version ORDER BY v.file_id, v.version ';
		$stats = db::db_fetch_array ($q);
		
		foreach ($stats as $stat) $ret[$stat->file_id][$stat->version] = $stat->cnt;
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns a list of computers using a specified version of an Agent file
	*
	* @return	array				
	*/
	function get_computers_versions_details ($file_id, $version, $active_cust_only = false)
	{
		class_load ('Computer');
		$ret = array ();
		
		// Just in case, delete records for computers that don't exist anymore
		$q = 'SELECT distinct a.computer_id FROM '.TBL_COMPUTERS_AGENT_VERSIONS.' a LEFT JOIN '.TBL_COMPUTERS .' c  ON ';
		$q.= 'a.computer_id=c.id WHERE c.id IS NULL ';
		$to_delete = db::db_fetch_array ($q);
		foreach ($to_delete as $del) db::db_query ('DELETE FROM '.TBL_COMPUTERS_AGENT_VERSIONS.' WHERE computer_id='.$del->computer_id);
		
		$q = 'SELECT c.id, cust.id as customer_id, cust.name as customer_name, c.netbios_name as name, c.last_contact ';
		$q.= 'FROM '.TBL_COMPUTERS .' c LEFT OUTER JOIN '.TBL_COMPUTERS_AGENT_VERSIONS.' a  ON ';
		$q.= 'c.id=a.computer_id LEFT OUTER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
		$q.= 'WHERE a.file_id='.$file_id.' AND a.version="'.$version.'" ';
		if ($active_cust_only) $q.= 'AND cust.active=1 AND cust.onhold=0 ';
		$q.= 'ORDER BY cust.name, c.netbios_name ';
		$ret = db::db_fetch_array ($q);
		
		return $ret;
	}

        /**
         * [Class Method] gets a list of deployers created for a customer
         * @param int $customer_id
         */
        static function get_deployers($customer_id){
            $ret = array();
            $target_directory = DIR_AGENT_DEPLOYER . "/" . $customer_id;
            if(is_dir($target_directory)){
                if(($handle = opendir($target_directory))){
                    while(false !== ($file = readdir($handle))) {
                        if($file!="." and $file!=".."){
                            $components = explode("_", $file);
                            if(count($components) == 5){
                                //the filename must respect the following notation
                                //kawacs_agent_$customer_id_$profile_id_$type_id
                                list($type, $ext) = explode(".", $components[4]);
                                $ret[] = array(
                                    'name' => $file,
                                    'link'=>'./' . DIR_AGENT_DEPLOYER_LINK . '/' . $customer_id . '/' . $file,
                                    'customer' => $components[2],
                                    'profile' => $components[3],
                                    'type' => $type
                                );
                            }
                        }
                    }
                    closedir($handle);
                }
            }
            return $ret;
        }
	
}

?>
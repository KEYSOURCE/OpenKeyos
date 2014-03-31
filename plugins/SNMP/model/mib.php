<?php

class_load ('MibOid');

/**
* Class for representing MIBs
*
**/

class Mib extends Base
{
	/** The unique ID of the MIB
	* @var int */
	var $id = null;
	
	/** The name of the MIB
	* @var string */
	var $name = '';
	
	/** Description of the MIB
	* @var string */
	var $comments = '';
	
	/** The date when the MIB was imported. It is updated every time a new file is uploaded for this MIB
	* @var timestamp */
	var $date_imported = '';
	
	/** The original file name of the uploaded file
	* @var string */
	var $orig_fname = '';
	
	/** True or false if the MIB file was parsed OK and all the OIDs were loaded
	* @var bool */
	var $loaded_ok = false;
	
	/** The ID of the file name from TBL_MIBS_FILES which should be considered as "main file"
	* See the 'fname' attribute for more details.
	* @var int */
	var $main_file_id = 0;
	
	
	/** The name of the file to be considered as the "main" one (using the original name from the upload). If the upload
	* contained a single file, it will be set automatically. If it contained multiple files the user will decide which one 
	* to be used as "main".
	* @var string */
	var $fname = '';
	
	/** The full path and name to the directory on local disk storing all the files for this MIB
	* @var string */
	var $work_dir = '';
	
	
	/** Table storing MIBs data
	* @var string */
	var $table = TBL_MIBS;
	
	/** The fields to load/save to database 
	* @var array */
	var $fields = array ('id', 'name', 'comments', 'date_imported', 'orig_fname', 'loaded_ok', 'main_file_id');
	
	
	/** Constructor. Also loads object data if an ID is specified */
	function Mib ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	/** Loads object data and set the local file name, if it exists */
	function load_data ()
	{
		parent::load_data ();
		$this->fname = '';
		$this->work_dir = $this->get_dir_name ();
		
		if ($this->id) $this->load_files ();
	}
	
	/** Loads into the object the list of files belonging to this MIB object */
	function load_files ()
	{
		$this->files_list = array ();
		if ($this->id)
		{
			// Load the list of files
			$q = 'SELECT id, fname FROM '.TBL_MIBS_FILES.' WHERE mib_id='.$this->id.' ORDER BY fname';
			$this->files_list = $this->db_fetch_list ($q);
			
			if ($this->main_file_id and isset($this->files_list[$this->main_file_id])) $this->fname = $this->files_list[$this->main_file_id];
			else
			{
				$this->fname = '';
				$this->main_file_id = 0;
			}
		}
	}
	
	
	/** Returns the path for the directory storing all the files for this MIB */
	function get_dir_name ()
	{
		if ($this->id) return DIR_UPLOAD_MIBS.'/'.FILE_PREFIX_MIBS.$this->id;
		else return '';
	}
	
	/** Saves object data to the database */
	function save_data ()
	{
		if (!$this->date_imported) $this->date_imported = time ();
		parent::save_data ();
	}
	
	/** Deletes the MIB and all related information */
	function delete ()
	{
		if ($this->id)
		{
			// Delete OID vals
			$this->delete_oids ();
			
			
			// Delete the associated files
			if (file_exists($this->work_dir)) remove_directory ($this->work_dir);
			
			// Delete the object itself
			parent::delete ();
		}
	}
	
	/** Delete all OIDs asociated with this MIB */
	function delete_oids ()
	{
		if ($this->id)
		{
			$ids = $this->db_fetch_vector ('SELECT id FROM '.TBL_MIBS_OIDS.' WHERE mib_id='.$this->id);
			foreach ($ids as $id) {$oid = new MibOid ($id); $oid->delete ();}
		}
	}
	
	/** Returns all MibOid objects for this Mib, sorted by OID */
	function get_oids ()
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_MIBS_OIDS.' WHERE mib_id='.$this->id.' ORDER BY ord';
		$ids = $this->db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new MibOid ($id);
		
		return $ret;
	}
	
	/** [Class Method] Returns the list of MIBs defined in the system */
	function get_mibs_list ()
	{
		$q = 'SELECT id, name FROM '.TBL_MIBS.' ORDER BY name, date_imported DESC ';
		return DB::db_fetch_list ($q);
	}
	
	/** [Class Method] Returns the MIBs defined in the system */
	function get_mibs ()
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_MIBS.' ORDER BY name, date_imported DESC ';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Mib ($id);
		return $ret;
	}
	
	/** [Class Method] Returns the SNMP-enabled devices defined in the system according to some criteria 
	* @param	array			$filter		Associative array with the filtering criteria. The keys can be:
	*							- customer_id: only search devices for this customer ID
	*							- obj_class: only search devices of this class (SNMP_OBJ_CLASS_COMPUTER, 
	*							  SNMP_OBJ_CLASS_PERIPHERAL, SNMP_OBJ_CLASS_AD_PRINTER)
	*							- snmp_ip: only search devices monitored on this IP address (is NOT used for computers)
	* @return	array					Associative array with the keys being customer IDs and the values being
	*							associative arrays with the keys being objects classes and the values being
	*							arrays of associative arrays, each having the following fields:
	*							- obj_id: The object ID
	*							- obj_class: The type of object ($GLOBALS['SNMP_OBJ_CLASSES'])
	*							- obj_name: The name of the object
	*							- snmp_ip: The IP address used for SNMP collection (empty for computers)
	*							- computer_id: The ID of the computer doing the SNMP monitoring (empty for computers)
	*							- computer_name: The name of the computer doing the SNMP monitoring (empty for computers)
	*							- profile_id: The ID of the monitoring profile used
	*							- profile_name: The name of the monitoring profile used
	*/
	public static function get_snmp_devices ($filter = array ())
	{
		$ret = array ();
		
		if ($filter['obj_class'] === '') unset ($filter['obj_class']);
		
		// First, fetch all the computers which have profiles with SNMP items enabled
		if (!isset($filter['obj_class']) or $filter['obj_class']==SNMP_OBJ_CLASS_COMPUTER)
		{
			$q = 'SELECT DISTINCT c.customer_id, c.id, c.netbios_name, prof.id as profile_id, prof.name as profile_name ';
			$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND cust.has_kawacs AND cust.active=1 AND cust.onhold=0 ';
			$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES_ITEMS.' pi ON c.profile_id=pi.profile_id ';
			$q.= 'INNER JOIN '.TBL_MONITOR_ITEMS.' i ON pi.item_id=i.id AND i.is_snmp=1 ';
			$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES.' prof ON c.profile_id=prof.id ';
			if ($filter['customer_id']) $q.= 'WHERE c.customer_id='.$filter['customer_id'].' ';
			
			$q.= ' ORDER BY cust.name, c.netbios_name, c.id ';
			$data = DB::db_fetch_array ($q);
			
			foreach ($data as $d) $ret[$d->customer_id][SNMP_OBJ_CLASS_COMPUTER][] = array (
					'obj_id'=>$d->id, 'obj_class'=>SNMP_OBJ_CLASS_COMPUTER, 'obj_name'=>$d->netbios_name, 'snmp_ip'=>'', 
					'computer_id'=>0, 'computer_name'=>'', 'profile_id'=>$d->profile_id, 'profile_name'=>$d->profile_name
				);
		}
		
		// Fetch the SNMP-enabled peripherals
		if (!isset($filter['obj_class']) or $filter['obj_class']==SNMP_OBJ_CLASS_PERIPHERAL)
		{
			$q = 'SELECT p.id, p.name, p.customer_id, p.snmp_ip, p.snmp_computer_id, prof.id as profile_id, prof.name as profile_name, c.netbios_name ';
			$q.= 'FROM '.TBL_PERIPHERALS.' p INNER JOIN '.TBL_CUSTOMERS.' cust ON p.customer_id=cust.id AND cust.has_kawacs AND cust.active=1 AND cust.onhold=0 ';
			$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES_PERIPH.' prof ON p.profile_id=prof.id ';
			$q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON p.snmp_computer_id=c.id ';
			$q.= 'WHERE p.snmp_enabled=1 ';
			if ($filter['customer_id']) $q.= 'AND c.customer_id='.$filter['customer_id'].' ';
			if ($filter['snmp_ip']) $q.= 'AND p.snmp_ip="'.$filter['snmp_ip'].'" ';
			$q.= 'ORDER BY cust.name, p.name, p.id ';
			$data = DB::db_fetch_array ($q);
			foreach ($data as $d) $ret[$d->customer_id][SNMP_OBJ_CLASS_PERIPHERAL][] = array (
				'obj_id'=>$d->id, 'obj_class'=>SNMP_OBJ_CLASS_PERIPHERAL, 'obj_name'=>$d->name, 'snmp_ip'=>$d->snmp_ip, 
				'computer_id'=>$d->snmp_computer_id, 'computer_name'=>$d->netbios_name, 'profile_id'=>$d->profile_id, 'profile_name'=>$d->profile_name
			);
		}
		
		// Fetch the SNMP-enabled AD printers
		if (!isset($filter['obj_class']) or $filter['obj_class']==SNMP_OBJ_CLASS_AD_PRINTER)
		{
			$q = 'SELECT a.*, aw.customer_id, prof.name as profile_name, c.netbios_name ';
			$q.= 'FROM '.TBL_AD_PRINTERS_EXTRAS.' a INNER JOIN '.TBL_AD_PRINTERS_WARRANTIES.' aw ON a.canonical_name=aw.canonical_name ';
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON aw.customer_id=cust.id AND cust.has_kawacs AND cust.active=1 AND cust.onhold=0 ';
			$q.= 'INNER JOIN '.TBL_MONITOR_PROFILES_PERIPH.' prof ON a.profile_id=prof.id ';
			$q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON a.snmp_computer_id=c.id ';
			$q.= 'WHERE a.snmp_enabled=1 ';
			if ($filter['customer_id']) $q.= 'AND aw.customer_id='.$filter['customer_id'].' ';
			if ($filter['snmp_ip']) $q.= 'AND a.snmp_ip="'.$filter['snmp_ip'].'" ';
			$q.= 'ORDER BY cust.name, a.canonical_name ';
			
			$data = DB::db_fetch_array ($q);
			foreach ($data as $d) $ret[$d->customer_id][SNMP_OBJ_CLASS_AD_PRINTER][] = array (
				'obj_id'=>$d->id, 'obj_class'=>SNMP_OBJ_CLASS_AD_PRINTER, 'obj_name'=>preg_replace ('/^.*\//','',$d->canonical_name), 
				'snmp_ip'=>$d->snmp_ip, 'computer_id'=>$d->snmp_computer_id, 'computer_name'=>$d->netbios_name, 
				'profile_id'=>$d->profile_id, 'profile_name'=>$d->profile_name
			);
		}
		
		return $ret;
	}
	
	
	/********************************************************/
	/* Processing uploaded MIBs				*/
	/********************************************************/
	
	/** Processes and stores on the server the uploaded MIB file(s). If the file is a zip archive it
	* will also unpack to the storage directory. 
	* @param	string			$upl_fname		The path and name of the uploaded file
	* @param	string			$orig_fname		The original file name of the uploaded file
	* @return	int						The number of obtained files, or 0 if the operation 
	*								failed.
	*/
	function set_uploaded_file ($upl_fname, $orig_fname)
	{
		$ret = 0;
		if ($this->id)
		{
			if (!$upl_fname) error_msg ($this->get_string('NEED_FILE'));
			elseif (!file_exists($upl_fname)) error_msg ($this->get_string('CANT_FIND_FILE'));
			else
			{
				$this->orig_fname = $orig_fname;
				$this->work_dir = $this->get_dir_name ();
				$this->loaded_ok = false;
				$this->db_query ('DELETE FROM '.TBL_MIBS_FILES.' WHERE mib_id='.$this->id);
				
				if (file_exists($this->work_dir)) 
				{
					if (!remove_directory ($this->work_dir))
					{
						error_msg ($this->get_string('FAILED_REMOVING_DIR', $this->work_dir));
						return 0;
					}
				}
				
				if (!@mkdir ($this->work_dir, 0775))
				{
					error_msg ($this->get_string('FAILED_CREATING_DIR', $this->work_dir));
					return 0;
				}
				
				move_uploaded_file ($upl_fname, $this->work_dir.'/'.$this->orig_fname);
				
				// Check if the uploaded file is an archive
				$out = array ();
				$retval = -1;
				$command = PATH_TO_UNZIP.' -t "'.$this->work_dir.'/'.$this->orig_fname.'"';
				exec($command, $out, $retval);
				
				if ($retval == 0)
				{
					// This is a zip archive
					$out = array ();
					$command = PATH_TO_UNZIP.' "'.$this->work_dir.'/'.$this->orig_fname.'" -d "'.$this->work_dir.'"';
					exec ($command, $out, $retval);
					if ($retval != 0)
					{
						// Unzipping failed
						error_msg ($this->get_string('FAILED_UNZIP'));
						return 0;
					}
					// Count the files
					flattern_directory ($this->work_dir);
					$handle = opendir ($this->work_dir);
					$cnt = 0;
					while (false !== ($item = readdir($handle)))
					{
						if ($item != "." and $item != ".." and $item != $this->orig_fname and !is_dir($this->work_dir.'/'.$item))
						{
							$this->db_query ('INSERT INTO '.TBL_MIBS_FILES.'(mib_id,fname) VALUES ('.$this->id.',"'.mysql_escape_string($item).'")');
							$cnt++;
						}
					}
					if ($cnt == 0)
					{
						error_msg ($this->get_string('NO_FILES_IN_ARCHIVE'));
					}
				}
				else
				{
					// This is a not a zip archive, treating as a single file
					$this->db_query ('INSERT INTO '.TBL_MIBS_FILES.'(mib_id,fname) VALUES ('.$this->id.',"'.mysql_escape_string($this->orig_fname).'")');
				}
				$this->load_files ();
				if (count($this->files_list) == 1)
				{
					$keys = array_keys($this->files_list);
					$this->main_file_id = $keys[0];
				}
				
				$ret = count ($this->files_list);
			}
		}
		return $ret;
	}
	
	
	/** Processes the file that was specified as the main file for this MIB object. The processing is
	* done in two phases: first an intermediate XML file is generated with the Java tools and then
	* the XML file is parsed and loaded into the database for this MIB object.
	* @return	bool						True or false if the processing was ok or not. In case
	*								of failure, the errors are raised with error_msg ()
	*/
	function process_uploaded_file ()
	{
		$ret = false;
		if ($this->id and $this->main_file_id and isset($this->files_list[$this->main_file_id]))
		{
			if (!$this->fname) error_msg ($this->get_string('NEED_FILE'));
			elseif (!file_exists($this->work_dir.'/'.$this->fname)) error_msg ($this->get_string('CANT_FIND_FILE').$this->work_dir.'/'.$this->fname);
			elseif (!is_readable($this->work_dir.'/'.$this->fname)) error_msg ($this->get_string('CANT_OPEN_FILE'));
			else
			{
				// Use the Java tool for converting the MIBs to the parsable XML file
				$tmp_xml_fname = $this->work_dir.'/'.$this->fname.'.xml';
				@unlink ($tmp_xml_fname); // Just to be sure
				
				$cmd_output = array ();
				$cmd_retval = -1;
				$mibble_path = realpath(dirname(__FILE__).'/../../_external/mibble');
				$classpath = $mibble_path.':'.$mibble_path.'/grammatica-bin-1.4.jar:'.$mibble_path.'/mibble-mibs-2.7.jar:';
				$classpath.= $mibble_path.'/mibble-parser-2.7.jar:'.$mibble_path.'/snmp4_13.jar';
				
				$cmd = 'java -classpath $CLASSPATH:'.$classpath.' MibParser '.$this->work_dir.'/'.$this->fname.' '.$tmp_xml_fname.' 2>&1 ';
				$res = exec ($cmd, $cmd_output, $cmd_retval);
				
				if ($cmd_retval!=0 or !file_exists($tmp_xml_fname))
				{
					// There was an error in processing the MIB file
					error_msg ($this->get_string('FAILED_PROCESSING_MIB'));
					error_msg ("Command: ".$cmd);
					foreach ($cmd_output as $s) error_msg (" > ".$s);
				}
				else
				{
					// The Java part of the processing was OK, now load the MIB data from the XML file
					// First, make a copy of all OIDs references from monitor items
					$q = 'SELECT o.oid, i.id FROM '.TBL_MIBS_OIDS.' o INNER JOIN '.TBL_MONITOR_ITEMS.' i ON o.id=i.snmp_oid_id ';
					$q.= 'WHERE o.mib_id='.$this->id;
					$data = $this->db_fetch_array ($q);
					$bk_oids_items = array ();
					foreach ($data as $d) $bk_oids_items[$d->oid][] = $d->id;
					
					$this->delete_oids (); // Delete the previous existing OIDs
					
					$this->parents_ids = array ();
					$ret = $this->process_mib_xml ($tmp_xml_fname);
					if ($ret)
					{
						// The XML file was processed OK. Mark the time and remove all OIDs which don't have any useful leafs
						$this->date_imported = time ();
						$this->loaded_ok = true;
						$parents_oids_ids = DB::db_fetch_vector ('SELECT id FROM '.TBL_MIBS_OIDS.' WHERE mib_id='.$this->id.' AND parent_id=0');
						foreach ($parents_oids_ids as $parent_oid_id) $this->prune_imported_oids ($parent_oid_id);
						
						// Now restore the associations with the monitoring items
						foreach ($bk_oids_items as $oid => $items_ids)
						{
							$q = 'SELECT id FROM '.TBL_MIBS_OIDS.' WHERE oid="'.$oid.'" AND mib_id='.$this->id;
							$new_id = $this->db_fetch_field ($q, 'id');
							if ($new_id)
							{
								foreach ($items_ids as $item_id)
								{
									$this->db_query ('UPDATE '.TBL_MONITOR_ITEMS.' SET snmp_oid_id='.$new_id.' WHERE id='.$item_id);
								}
							}
						}
					}
				}
				
				@unlink ($tmp_xml_fname);
			}
		}
		return $ret;
	}
	
	/*
	* @return	bool					Returns True if any of the children have a type, access or syntax set
	*/
	function prune_imported_oids ($oid_id)
	{
		$ret = false;
		
		$d = DB::db_fetch_array ('SELECT * FROM '.TBL_MIBS_OIDS.' WHERE id='.$oid_id);
		$d = $d[0];
		if ($d->data_type or $d->node_type or $d->access or $d->syntax) $ret = true;
		else
		{
			$oids_ids = DB::db_fetch_vector ('SELECT id FROM '.TBL_MIBS_OIDS.' WHERE mib_id='.$this->id.' AND parent_id='.$oid_id);
			foreach ($oids_ids as $id)
			{
				if ($this->prune_imported_oids($id)) $ret = true; // We don't use 'break' here so we check all the children
			}
		}
		
		if (!$ret) DB::db_query ('DELETE FROM '.TBL_MIBS_OIDS.' WHERE id='.$oid_id);
		return $ret;
	}
	
	
	/** Initiate the processing of the intermediate XML file for generating all the Mib and MibOid objects */
	function process_mib_xml ($xml_fname)
	{
		$ret = false;
		if ($this->id and file_exists($xml_fname) and is_readable($xml_fname))
		{
			$this->parser = xml_parser_create ();
			xml_set_object ($this->parser, $this);
			xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, 0);
			xml_set_element_handler ($this->parser, 'xml_start_element', 'xml_end_element');
			xml_set_character_data_handler ($this->parser, 'xml_character_data');
			
			$ret = true;
			$fp_xml = fopen ($xml_fname, 'r');
			$this->xml_processing_ok = true;
			
			while ($data = fread($fp_xml, 1024))
			{
				if (!xml_parse($this->parser, $data, feof($fp_xml)))
				{
					$ret = false;
					error_msg ($this->get_string('XML_PARSING_ERROR',
						xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser)));
					break;
				}
				elseif (!$this->xml_processing_ok)
				{
					$ret = false;
					error_msg ($this->get_string('XML_PARSING_ERROR_INTERNAL'));
					break;
				}
			}
			
			xml_parser_free ($this->parser);
			fclose ($fp_xml);
			unset ($this->parser);
		}
		return $ret;
	}
	
	/** XML handler for when new caracter data is encountered.*/
	function xml_character_data ($parser, $data)
	{
		$this->c_data.= $data;
	}
	
	/** XML handler to be called when a new tag is encountered. Just update the tags counter. */
	function xml_start_element ($parser, $name, $attribs)
	{
		switch ($name)
		{
			case 'mib': 	$this->name = $attribs['name']; break;
			case 'node':	
				$this->c_oid = new MibOid (); 
				$this->c_oid->mib_id = $this->id;
				$this->c_oid->oid = $attribs['oid'];
				$this->c_oid->name = $attribs['name'];
				$this->c_oid->ord = $this->cnt_nodes++; // Temporary now, just to ensure the unqueness required by the database index
				if ($attribs['node_type'])
				{
					$this->c_oid->node_type = $attribs['node_type'];
					if ($this->c_oid->node_type == SNMP_NODE_SCALAR) $this->c_oid->oid.= '.0';
				}
				if ($attribs['parent'])
				{
					if (isset($this->parents_ids[$attribs['parent']]))
					{
						$this->c_oid->parent_id = $this->parents_ids[$attribs['parent']]['id'];
						$this->c_oid->level = $this->parents_ids[$attribs['parent']]['level']+1;
					}
				}
				break;
		}
	}
	
	/** XML handler to be called when a tag is closed. */
	function xml_end_element ($parser, $name)
	{
		switch ($name)
		{
			case 'comments':	$this->comments = trim($this->c_data); break;
			case 'access':		
				$this->c_data = trim($this->c_data);
				if ($this->c_data == 'read-only') $this->c_oid->access = SNMP_ACCESS_READ_ONLY;
				elseif ($this->c_data == 'read-write') $this->c_oid->access = SNMP_ACCESS_READ_WRITE;
				break;
			case 'status':		
				if (trim($this->c_data) == 'mandatory') $this->c_oid->status = SNMP_OID_STAT_MANDATORY; 
				elseif (trim($this->c_data) == 'deprecated') $this->c_oid->status = SNMP_OID_STAT_DEPRECATED;
				elseif (trim($this->c_data) == 'obsolete') $this->c_oid->status = SNMP_OID_STAT_OBSOLETE;
				elseif (trim($this->c_data) == 'optional') $this->c_oid->status = SNMP_OID_STAT_OPTIONAL;
				break;
			case 'syntax':
				$this->c_oid->syntax = trim($this->c_data); 
				break;
			case 'data_type':
				$this->c_data = trim($this->c_data);
				if (isset($GLOBALS['SNMP_TYPES_TRANSLATION'][$this->c_data])) $this->c_oid->data_type = $GLOBALS['SNMP_TYPES_TRANSLATION'][$this->c_data];
				else
				{
					error_msg ($this->get_string('XML_UNKNOWN_TYPE', $this->c_data));
					$this->xml_processing_ok = false;
				}
				break;
			case 'description':	$this->c_oid->description = trim($this->c_data); break;
			case 'node':
				$this->c_oid->save_data ();
				$this->parents_ids[$this->c_oid->oid] = array ('id'=>$this->c_oid->id, 'level'=>$this->c_oid->level, 'parent_id'=>$this->c_oid->parent_id);
				
				// For OIDs of type INTEGER check if there are any details of values lists
				if ($this->c_oid->data_type==SNMP_TYPE_INTEGER and preg_match('/\{.+\}/',$this->c_oid->syntax))
				{
					$syntax = preg_replace ('/^.*\{\s*|\s*\}.*$/', '', $this->c_oid->syntax);
					$syntax = preg_split ('/,\s*/', $syntax);
					$vals = array ();
					foreach ($syntax as $s)
					{
						if (preg_match('/(.*)\(([0-9]+)\)/', $s, $m)) $vals[$m[2]] = $m[1];
					}
					$this->c_oid->set_vals ($vals);
				}
				
				$this->c_oid = null;
				break;
		}
		
		$this->c_data = '';
	}	
}

?>
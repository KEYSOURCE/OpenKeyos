<?php

class_load ('SoftwareMatch');

/**
* Class for storing and managing information about software packages. 
*
*/

class Software extends Base
{
	/** Software ID
	* @var int */
	var $id = null;
	
	/** The name of the software package 
	* @var string */
	var $name = '';
	
	/** The name of the software manufacturer 
	* @var string */
	var $manufacturer = '';
	
	/** The possible licensing types - see $GLOBALS['LIC_TYPES_NAMES'] (binary OR)
	* @var int */
	var $license_types = null;
	
	/** If this is considered as 'main' software - which is included in software reports
	* @var bool */
	var $in_reports = true;
	
	
	/** The list of regexp for matching with computer data from KAWACS info 
	* @var array (SoftwareMatch) */
	var $match_rules = array();
	
	
	/** The database table storing software packages data 
	* @var string */
	var $table = TBL_SOFTWARE;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'name', 'manufacturer', 'license_types', 'in_reports');
	
	
	/** Class constructor, also loads a product information if an ID is specified */
	function Software ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	function __destruct()
	{
		if($this->match_rules) unset($this->match_rules);
	}
	
	/** Loads the object data, as well as the name regexps, if available */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				$q = 'SELECT id FROM '.TBL_SOFTWARE_MATCHES.' WHERE software_id='.$this->id;
				$ids = $this->db_fetch_array ($q);
				
				foreach ($ids as $id)
				{
					$this->match_rules[] = new SoftwareMatch ($id->id);
				}
			}
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$valid = true;
		
		if (!$this->name) {error_msg('Please enter the name of the software package.'); $valid = false;}
		if (!$this->manufacturer) {error_msg('Please enter the name of the manufacturer.'); $valid = false;}
		if (!$this->license_types) {error_msg('Please specify the possible licensing types.'); $valid = false;}
		
		return $valid;
	}
	
	
	/** Saves the object data, as well as the data for the corresponding name matches - if applicable */
	function save_data ()
	{
		parent::save_data ();
		
		if ($this->id)
		{
			if (($this->license_types & LIC_TYPE_SEAT) == LIC_TYPE_SEAT)
			{
				// This package can have per/seat licensing, so save the data
				foreach ($this->match_rules as $rule) $rule->save_data ();
			}
			else
			{
				// This package doesn't have per/seat licensing, so remove old data that might have been before
				foreach ($this->match_rules as $rule) $rule->delete ();
			}
		}
	}
	
	
	/** Deletes the object from the database. Also deletes the related name matching rules */
	function delete ()
	{
		if ($this->id)
		{
			foreach ($this->match_rules as $rule) $rule->delete ();
			
			parent::delete ();
		}
	}

	
	/**
	* Returns the names matched by all the rules defined for this software.
	* This is NOT the number of licenses used, it is only a list of name matches.
	*/
	function get_matching_names ()
	{
		$ret = array ();
		
		if ($this->id and (count($this->match_rules) > 0))
		{
			foreach ($this->match_rules as $rule)
			{
				$names = array_diff ($rule->get_matching_names (), $ret);
				$ret = array_merge ($ret, $names);
			}
			
			asort ($ret);
		}
		
		return $ret;
	}
	
	
	/** Returns the number of licenses for this type of software package used by a specific customer */
	function get_used_licenses ($customer_id = null)
	{
		$ret = 0;
		if ($this->id and $customer_id and (count($this->match_rules) > 0))
		{
			class_load ('Computer');
			
			$q = 'SELECT count(DISTINCT i.computer_id) as cnt FROM '.TBL_COMPUTERS_ITEMS.' i INNER JOIN '.TBL_COMPUTERS.' c ';
			$q.= 'ON i.computer_id=c.id WHERE c.customer_id='.$customer_id.' AND ';
			$q.= '(i.item_id='.SOFTWARE_ITEM_ID.' OR i.item_id='.OS_NAME_ITEM_ID.') AND (';
			
			foreach ($this->match_rules as $rule)
			{
				$q.= '(i.value '.$rule->get_query_condition().') OR ';
			}
			$q = preg_replace ('/OR\s*$/', '', $q).') ';
			
			$ret = $this->db_fetch_field ($q, 'cnt');
		}
		
		return $ret;
	}
	
	
	/** Returns a list with the customers which are using this type of software - based on the defined licenses 
	* @return	array				Associative array with the matched customers, the keys being
	*						customer IDs and the values being customer names.
	*/
	function get_customers ()
	{
		$ret = array ();
		
		if ($this->id)
		{
			$q = 'SELECT DISTINCT c.id, c.name FROM '.TBL_SOFTWARE_LICENSES.' l INNER JOIN '.TBL_CUSTOMERS.' c ';
			$q.= 'ON l.customer_id=c.id WHERE l.software_id='.$this->id.' ORDER BY c.name';
			$ret = $this->db_fetch_list ($q);
		}
		return $ret;
	}
	
	
	/** Returns a list with the customers using this type of software - based on the info in Kawacs,
	* regardless if they have or not licenses defined for it
	*/
	function get_all_customers ()
	{
		$ret = array ();
		
		if ($this->id and count($this->match_rules)>0)
		{
			class_load ('Computer');
		
			$q = 'SELECT DISTINCT cust.id, cust.name FROM '.TBL_COMPUTERS_ITEMS.' ci ';
			$q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON ci.computer_id=c.id ';
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
			$q.= 'WHERE (item_id='.SOFTWARE_ITEM_ID.' OR item_id='.OS_NAME_ITEM_ID.') AND (';
			
			for ($i=0; $i<count($this->match_rules); $i++)
			{
				$q.= '(value '.$this->match_rules[$i]->get_query_condition().') OR ';
			}
			$q = preg_replace ('/OR\s*$/', ' ', $q).') ';
			$q.= 'ORDER BY cust.name ';

			$ret = $this->db_fetch_list ($q);
		}
		return $ret;
	}
	
	
	/** Returns an array with the computers from the specified customer using this type of software */
	function get_computers ($customer_id = null)
	{
		$ret = array ();
		if ($this->id and $customer_id and (count($this->match_rules) > 0))
		{
			class_load ('Computer');
			
			// First, fetch the list of all installed software on all computers for this customer.
			// It is faster to parse the list for software name matches, instead of having MySQL
			// doing comparision on unindexed text fields
			$q = 'SELECT DISTINCT c.id, i.value FROM '.TBL_COMPUTERS_ITEMS.' i INNER JOIN '.TBL_COMPUTERS.' c ';
			$q.= 'ON i.computer_id=c.id WHERE c.customer_id='.$customer_id.' AND ';
			$q.= '(i.item_id='.SOFTWARE_ITEM_ID.' OR i.item_id='.OS_NAME_ITEM_ID.') ';

			$computers = $this->db_fetch_array ($q);
			
			$computer_ids = array ();
			for ($i=0; $i<count($computers); $i++)
			{
				$matched = false;
				for ($j=0; $j<count($this->match_rules) and !$matched; $j++)
				{
					$matched = $this->match_rules[$j]->matches_name ($computers[$i]->value);
				}
				if ($matched and !in_array($computers[$i]->id, $computer_ids)) $computer_ids[] = $computers[$i]->id;
			}
		
			$computer_ids = array_values ($computer_ids);

			if (count ($computer_ids) > 0)
			{
				// There were some matches found
				foreach ($computer_ids as $id) $ret[] = new Computer ($id);
			}
		}

		return $ret;
	}

	
	/** Returns the list of computers (from KAWACS) on which this software is installed
	* @param	int		$customer_id		Search only computers for this customer. If not
	*							specified, then it will search all computers for all customers.
	* @param	bool		$by_asset_no		If True, then the return array will use asset numbers as keys instead of IDs.
	* @return	array					Array with results. If a customer ID was specified, it is an associative
	*							array with computer IDs as keys and their names as values. If a customer
	*							was not specified, it is an associative array with customer IDs as keys
	*							and the values are associative arrays with computer IDs as keys and computer
	*							names as values.
	*/
	function get_computers_list ($customer_id = null, $by_asset_no = false)
	{
		$ret = array ();
		
		if ($this->id and (count($this->match_rules) > 0))
		{
			// Fetch the list of all installed software on all computers for this customer.
			// It is faster to parse the list for software name matches, instead of having MySQL
			// doing comparision on unindexed text fields
			$q = 'SELECT c.id, c.customer_id, c.type, c.netbios_name, i.value ';
			if ($by_asset_no) $q.= ', concat(if(type='.COMP_TYPE_SERVER.',"'.ASSET_PREFIX_SERVER.'","'.ASSET_PREFIX_WORKSTATION.'"),lpad(id,'.ASSET_NUM_LENGTH.',"0")) as asset_no ';
			$q.= 'FROM '.TBL_COMPUTERS_ITEMS.' i INNER JOIN '.TBL_COMPUTERS.' c ON i.computer_id=c.id WHERE ';
			if ($customer_id) $q.= 'c.customer_id='.$customer_id.' AND ';
			$q.= '(i.item_id='.SOFTWARE_ITEM_ID.' OR i.item_id='.OS_NAME_ITEM_ID.') ';
			$q.= 'ORDER BY c.netbios_name';
			
			$computers = $this->db_fetch_array ($q);
			for ($i=0; $i<count($computers); $i++) $computers[$i]->asset_no = get_asset_no_comp ($computers[$i]->id, $computers[$i]->type);
			
			$computer_ids = array ();
			for ($i=0; $i<count($computers); $i++)
			{
				foreach ($this->match_rules as $rule)
				{
					if ($rule->matches_name ($computers[$i]->value))
					{
						$idx = ($by_asset_no ? $computers[$i]->asset_no : $computers[$i]->id);
						$ret[$computers[$i]->customer_id][$idx] = $computers[$i]->netbios_name;
						break;
					}
				}
			}
			
			if ($customer_id) $ret = $ret[$customer_id];
		}
		
		return $ret;
	}
	
	/** Returns true or false if the provided name matches any of the name rules */
	function matches_name ($name = '')
	{
		$ret = false;
		if ($name and $this->id and (count($this->match_rules) > 0))
		{
			foreach ($this->match_rules as $rule)
			{
				if ($rule->matches_name($name)) $ret = true;
			}
		}
		return $ret;
	}
	
		
	/**
	* [Class Method] Returns the list of defined software packages 
	*/
	function get_software_list ($filter = array(), &$sw_count)
	{
		$ret = array ();
		
		if (!$filter['orderby']) $filter['orderby'] = 'name';
		if (!$filter['orderdir']) $filter['orderid'] = 'ASC';
		
		$q = 'FROM '.TBL_SOFTWARE.' ';
		if($filter['search_text'] != "")
		{		
			$q.="WHERE MATCH (name, manufacturer) AGAINST ('".$filter['search_text']."' in boolean mode) ";
		}
		if(isset($sw_count))		
			$sw_count = db::db_fetch_field("SELECT count(id) as cnt ".$q, 'cnt');		
		
		$q = 'SELECT id FROM '.TBL_SOFTWARE.' ';
		if($filter['search_text'] != "")
		{			
			$q.="WHERE MATCH (name, manufacturer) AGAINST ('".$filter['search_text']."' in boolean mode) ";
		}
		$q.= 'ORDER BY '.$filter['orderby'].' '.$filter['orderdir'].' ';
		
		if (isset($filter['start']) and isset($filter['limit'])) $q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
		
		$ids = db::db_fetch_array ($q);
		
		foreach ($ids as $id)
		{
			$ret[] = new Software($id->id);
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns an array of available software, with the software ID as index and the name as array value.
	* Suitable to be used in SELECT elements.
	*/
	function get_software_names ()
	{
		$ret = array ();
		
		$q = 'SELECT id, name FROM '.TBL_SOFTWARE.' ORDER BY NAME ';
		$list = db::db_fetch_array ($q);
		
		foreach ($list as $software) $ret[$software->id] = $software->name;
		
		return $ret;
	}
	
	function get_formated_name()
	{
		$ret = iconv(db::get_client_encoding(), "ISO-8859-1//IGNORE", $this->name);
		debug($ret);
		return $ret;
	}
	
	/**
	 * [Class method] gets all sofware reported as installed per each machine
	 * @return unknown_type
	 */
	function get_permachine_sofware($filter=array())
	{
		if(!$filter['computer_id']) return array();
                //first test to see if for this computer we report the extended version of the installed software - the 1055
                $query = "select mpi.item_id
                          from ".TBL_MONITOR_PROFILES_ITEMS." mpi, ".TBL_MONITOR_PROFILES." mp, ".TBL_COMPUTERS." c
                          WHERE mpi.profile_id=mp.id and mp.id=c.profile_id and c.id=".$filter['computer_id'];

                $items = db::db_fetch_vector($query);
                $normal_select = true;
                //debug($filter['computer_id']);
                //        debug($items);
                if(in_array('1055', $items)){
                    //ok... now we know we report the extended version
                    $query = "select short_name, id from ".TBL_MONITOR_ITEMS." where parent_id=1055";
                    $mi = db::db_fetch_list($query);
                    //now get the names:
                    $query = "select nrc, value from ".TBL_COMPUTERS_ITEMS." where computer_id=".$filter['computer_id']." and field_id=".$mi['name'];
                    $names = db::db_fetch_list($query);

                    $query = "select nrc, value from ".TBL_COMPUTERS_ITEMS." where computer_id=".$filter['computer_id']." and field_id=".$mi['install_date'];
                    $dates = db::db_fetch_list($query);

                    if(empty($names) || count($names) == 0) $normal_select = false;
                    $ret = array();
                    foreach($names as $nrc=>$name){
                        $ret[]=array('name'=>$name, 'install_date'=>$dates[$nrc]);
                    }
                }
                else{
                    $normal_select = false;
                }
                if(!$normal_select)
                {
                    $query = "select ci.value from ".TBL_COMPUTERS_ITEMS." ci where ci.item_id=1019 and ci.computer_id=".$filter['computer_id'];
                    $ret = array();
                    $s = db::db_fetch_vector($query);
                    foreach($s as $sf)
                    {
                            $ret[] = array('name'=>$sf, 'install_date'=>'');
                    }
                }
                return $ret;
	}
}

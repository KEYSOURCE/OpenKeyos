<?php

class_load ('Software');

/**
* Class for storing and managing information about the name matching rules,
* used for matching the defined software packages with the softwar names
* from the Kawacs collected information.
*
*/

class SoftwareMatch extends Base
{
	/** Object ID
	* @var int */
	var $id = null;
	
	/** The ID of the software package for which the rule applies
	* @var int */
	var $software_id = null;
	
	/** The type of matching to be used - see $GLOBALS['NAMES_MATCH_TYPES']
	* @var int */
	var $match_type = null;
	
	/** The string used in comparisons
	* @var string */
	var $expression = '';
	
	
	/** The database table for storing the rules definitions
	* @var string */
	var $table = TBL_SOFTWARE_MATCHES;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'software_id', 'match_type', 'expression');
	
	
	
	/** Class constructor, also loads the object data if an ID is provided */
	function SoftwareMatch ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$valid = true;
		
		if (!$this->software_id) {error_msg ('A name matching rule must be associated with a software package.'); $valid = false;}
		if (!$this->match_type) {error_msg ('Please specify the comparison criteria'); $valid = false;}
		if (!$this->expression) {error_msg ('Please specify the expression to be used for comparison'); $valid = false;}
		
		return $valid;
	}
	
	
	/** 
	* Returns an array with all the distinct matching software names from the database for this rule.
	* This is NOT a list of used licenses. The role of this method is to test the name matching rules.
	*/
	function get_matching_names ()
	{
		$ret = array();
		class_load ('Computer');
		
		if ($this->id and $this->match_type and $this->expression)
		{
			$software_id = Computer::get_item_id ('software');
			$os_id = Computer::get_item_id ('os_name');
		
			$q = 'SELECT DISTINCT value FROM '.TBL_COMPUTERS_ITEMS.' ';
			$q.= 'WHERE (item_id='.$software_id.' OR item_id='.$os_id.') AND ';
			
			$q.= 'value '.$this->get_query_condition();

			$names = $this->db_fetch_array ($q);
			foreach ($names as $name) $ret[] = $name->value;
			
			asort ($ret);
		}
		return $ret;
	}
	
	
	/**
	* Returns a list with the customers for whom Kawacs contain matching software
	* @return	array				Associative array with the matched customers,
	*						the keys being customer IDs and the valued being customer names
	*/
	function get_matching_customers ()
	{
		$ret = array ();
		class_load ('Computer');
		
		if ($this->id and $this->match_type and $this->expression)
		{
			$software_id = Computer::get_item_id ('software');
			$os_id = Computer::get_item_id ('os_name');
		
			$q = 'SELECT DISTINCT cust.id, cust.name FROM '.TBL_COMPUTERS_ITEMS.' ci ';
			$q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON ci.computer_id=c.id ';
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
			$q.= 'WHERE (item_id='.$software_id.' OR item_id='.$os_id.') AND ';
			$q.= 'value '.$this->get_query_condition();
			$q.= 'ORDER BY cust.name ';

			$ret = $this->db_fetch_list ($q);
		}
		return $ret;
	}
	
	
	/**
	* Returns a string suitable to be used in a query, based on the object's criteria
	*/
	function get_query_condition ()
	{
		$ret = '';
		
		if ($this->id and $this->match_type and $this->expression)
		{
			$expr = mysql_escape_string ($this->expression);
			switch ($this->match_type)
			{
				case CRIT_STRING_MATCHES: 	$ret.= ' = "'.$expr.'" '; break;
				case CRIT_STRING_STARTS:	$ret.= ' like "'.$expr.'%" '; break;
				case CRIT_STRING_ENDS:		$ret.= ' like "%'.$expr.'" '; break;
				case CRIT_STRING_CONTAINS:	$ret.= ' like "%'.$expr.'%" '; break;
			}
		}
		
		return $ret;
	}

	
	/** Returns true or false if the provided name matches any of the name rules */
	function matches_name ($name = '')
	{
		$ret = false;
		if ($this->id and $name and $this->match_type and $this->expression)
		{
			// This was wrong. The expression must be escaped, not the name passed as argument
			/*
			$name = preg_replace("/\|/", "\\|", quotemeta($name));
			$name = preg_replace("/\//", "\/", $name);
			*/
			$expression = preg_replace("/\|/", "\\|", quotemeta($this->expression));
			$expression = preg_replace("/\//", "\/", $expression);
			$expression = trim ($expression);
			
			switch ($this->match_type)
			{
				case CRIT_STRING_MATCHES: 	$ret = ($this->expression == $name); break;
				case CRIT_STRING_STARTS:	$ret = preg_match ('/^'.$expression.'.*/i', $name); break;
				case CRIT_STRING_ENDS:		$ret = preg_match ('/.*'.$expression.'$/i', $name); break;
				case CRIT_STRING_CONTAINS:	$ret = preg_match ('/.*'.$expression.'.*/i', $name); break;
			}
		}
		return $ret;
	}
}
?>

<?php

/**
* Class for managing the various types of action types which can be assigned to
* ticket details.
* An action type represents a type of work that a technician can do, and can be
* further reflected in different pricing methods which will be then applied.
*
*/

class_load ('ActionTypeCategory');

class ActionType extends Base
{
	/** Action type ID
	* @var int */
	var $id = null;

	/** The ID used in the ERP system for this action type
	* @var string */
	var $erp_id = '';

	/** The alphanumeric code used in the ERP system
	* @var string */
	var $erp_code = '';

	/** The name of the action type
	* @var string */
	var $name = '';

	/** The name of the action type as it is defined in the ERP system
	* @var string */
	var $erp_nam = '';

	/** Specifies if this action type is active for using in new tickets
	* @var bool */
	var $active = true;

	/** The category ID of this action type (formerly stored in $GLOBALS['ACTYPES'])
	* @var int */
	var $category = null;

	/** The type of pricing used for this action type - see $GLOBALS['PRICE_TYPES']
	* @var int */
	var $price_type = null;

	/** The contract type for which this action type applies - see $GLOBALS['CONTRACT_TYPES']
	* NOTE: In the future it is possible to convert this field to store multiple contract types,
	* using bitwise sums - if it will be needed.
	* @var $int */
	var $contract_types = 0;

	/** The sub-type of contracts for which this action type applied
	* @var int */
	var $contract_sub_type = 0;

	/** Specifies if this action type is normally billable or not
	* @var bool */
	var $billable = true;

	/** Some comments about this action type
	* @var text */
	var $comments = '';

	/** Specifies if this is a special action type (e.g. travel costs). If non-zero, the field
	* stores the ID of the special type. These special action types can't be selected by engineers
	* in ticket details - see $GLOBALS ['ACTYPE_SPECIALS']
	* @var int */
	var $special_type = 0;

	/** If this is a special type and an user is required for it, this field stores that user ID
	* @var int */
	var $user_id = 0;

	/** The "Family" name from the ERP system
	* @var string */
	var $family = '';

	/** Specifies if this action type is for helpdesk only
	* @var bool */
	var $helpdesk = false;

	/** The billing unit (in minutes), if this is an hourly-priced activity. By
	* default it is 60 minutes, but there are activities which are billed on e.g. 15 minutes intervals
	* @var int */
	var $billing_unit = 60;

	/** If this is a special type and has a user linked to it, this field stores the associated User object
	* @var User */
	var $user = null;


	var $table = TBL_ACTION_TYPES;
	var $fields = array ('id', 'erp_id', 'erp_code', 'name', 'category', 'price_type', 'contract_types', 'billable', 'comments', 'special_type', 'user_id', 'erp_name', 'contract_sub_type', 'active', 'family', 'helpdesk', 'billing_unit');


	/**
	* Constructor. Also loads an action type information if an ID is provided
	* @param	int	$id		The ID of the action type to load
	*/
	function ActionType ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}


	function load_data ()
	{
		parent::load_data ();
		if ($this->user_id)
		{
			$this->user = new User ($this->user_id);
		}
		//if (mb_detect_encoding($this->family) == 'UTF-8') $this->family = utf8_decode ($this->family);
	}

	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		if (!$this->name) {error_msg ('Please specify the name.'); $ret = false;}
		if (!$this->erp_id) {error_msg ('Please specify the ERP id.'); $ret = false;}
		if (!$this->erp_code) {error_msg ('Please specify the ERP code.'); $ret = false;}

		if (!$this->special_type)
		{
			if (!$this->category) {error_msg ('Please specify the category.'); $ret = false;}
			if (!$this->price_type) {error_msg ('Please specify the pricing type.'); $ret = false;}
			if (!$this->contract_types) {error_msg ('Please specify the contract type.'); $ret = false;}
		}

		return $ret;
	}

	/** Checks if the action type can be deleted */
	function can_delete ()
	{
		$ret = false;
		if ($this->id)
		{
			$ret = true;
			// Check if there aren't tickes with this action type
			$q = 'SELECT id FROM '.TBL_TICKETS_DETAILS.' WHERE activity_id='.$this->id.' LIMIT 1';
			if ($this->db_fetch_field ($q, 'id'))
			{
				error_msg ('This action type can\'t be deleted, it is already in use.');
				$ret = false;
			}
		}
		return $ret;
	}


	/** [Class Method] Return the travel cost special action for an user
	* @param	int		$user_id	The user ID
	* @return	ActionType			The special action type representing the travel cost for that user
	*/
	public static function get_user_travel_cost ($user_id)
	{
		$ret = null;
		if ($user_id)
		{
			$q = 'SELECT id FROM '.TBL_ACTION_TYPES.' WHERE special_type='.ACTYPE_SPECIAL_TRAVEL.' AND user_id='.$user_id;
			$ret = new ActionType (DB::db_fetch_field($q, 'id'));
			if (!$ret->id) $ret = null;
		}

		return $ret;
	}


	/**
	* [Class Method] Returns a list of action types
	* @param	array		$filter		Associative array with filtering criteria. Can contain:
	*						- show_specials: If true, include special action types too. Default is not
	*						  to include them in the results - unless $filter['special_type'] is specified.
	*						- special_type: If specified, return only special action types of that type.
	*						- price_type: If specified, return only action types with this price type.
	* @return	array				Associative array, they keys are action IDs and the values are their names
	*/
	public static function get_list ($filter = array ())
	{
		$ret = array ();

		$q = 'SELECT a.id, concat("[",a.erp_code,"] ",a.name) FROM '.TBL_ACTION_TYPES.' a WHERE ';
		if ($filter['special_type']) $q.= 'a.special_type='.$filter['special_type'].' AND ';
		elseif (!$filter['show_specials']) $q.= 'a.special_type=0 AND ';
		if ($filter['price_type']) $q.= 'a.price_type='.$filter['price_type'].' AND ';

		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);

		$q.= 'ORDER BY a.name, a.id';
		$ret = DB::db_fetch_list ($q);

		return $ret;
	}


	/**
	* [Class Method] Returns a list of the ERP codes for the action types
	* @return	array			Associative array, the keys being action type IDs and the values being the
	*					corresponding ERP codes
	*/
	public static function get_erp_codes_list ()
	{
		$ret = array ();
		$q = 'SELECT id, erp_code FROM '.TBL_ACTION_TYPES.' ORDER BY name, erp_code';
		$ret = DB::db_fetch_list ($q);
		return $ret;
	}

	public static function get_actions()
	{
		$ret = array ();
		$q = 'SELECT id FROM '.TBL_ACTION_TYPES.' where active=1 ORDER BY name, erp_code';
		$ids = DB::db_fetch_vector ($q);
		foreach($ids as $id){
			$ret[] = new ActionType($id);
		}
		return $ret;
	}

	/**
	* [Class Method] Returns the action types currently defined in the system
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- group_by: 'category' or 'contract'. If this is specified,
	*							  then the results will be grouped by category or contract type
	*							  respectively.
	*							- show_empty_groups: if True, then the results will include empty
	*							  categorys or contracts (if $filter['group_by'] was specified)
	*							- contract_type: return only action types for this kind of contract
	*							- contract_type_cust: return only action types for this kind or contract,
	*							  as well any other action types that might be valid for this type of customer,
	*							  for example CONTRACT_ALL.
	*							- contract_sub_type: if set, return only actions for customers of this sub-type
	*							- active: if set, specified what action types to get. True=Active, False=Active
	*							- helpdesk: if set and True, return only helpdesk-related action types
	*							- order_by, order_dir: how to order the results
	*							- show_specials: If true, include special action types too. Default is not
	*							  to include them in the results - unless $filter['special_type'] is specified.
	*							- special_type: If specified, return only special action types of that type.
	* @return	array					If $filter['group_by'] is no specified, the result is an array of ActionType
	*							objects. If it is specified, then the result is an associative array,
	*							the keys being category or contract IDs and the values being array of ActionType
	*							objects.
	*/
	public static function get_action_types ($filter = array ())
	{
		$ret = array ();

                //debug($filter);

		if (!$filter['order_by']) $filter['order_by'] = 'name';
		if (!$filter['order_dir']) $filter['order_dir'] = 'ASC';
		$filter['order_by'] = 'a.'.$filter['order_by'];

		$q = 'SELECT id FROM '.TBL_ACTION_TYPES.' a WHERE ';

		if ($filter['contract_type']) $q.= 'a.contract_types='.$filter['contract_type'].' AND ';
		if ($filter['price_type']) $q.= 'a.price_type='.$filter['price_type'].' AND ';
		if ($filter['contract_type_cust'])
		{
			$q.= '(a.contract_types='.$filter['contract_type_cust'].' OR a.contract_types='.CONTRACT_ALL.') AND ';
			if ($filter['contract_type_cust']==CONTRACT_KEYPRO and $filter['contract_sub_type'])
				$q.= 'a.contract_sub_type='.$filter['contract_sub_type'].' AND ';
		}
		if ($filter['helpdesk']) $q.= 'a.helpdesk=1 AND ';

		if ($filter['special_type']) $q.= 'a.special_type='.$filter['special_type'].' AND ';
		elseif (!$filter['show_specials']) $q.= 'a.special_type=0 AND ';

		if (isset($filter['active'])) $q.= 'a.active='.($filter['active'] ? 1 : 0).' AND ';

		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY '.$filter['order_by'].' '.$filter['order_dir'];
                //debug($q);
		$ids = DB::db_fetch_vector ($q);

		foreach ($ids as $id) $ret[] = new ActionType ($id);

		if ($filter['group_by'] == 'category')
		{
			$tmp = array ();
			$actypes_categories_list = ActionTypeCategory::get_categories_list ();
			// Let's make sure the groups are ordered by the name of categories
			foreach ($actypes_categories_list as $category_id => $category_name) $tmp[$category_id] = array ();

			for ($i=0,$i_max=count($ret); $i<$i_max; $i++) $tmp[$ret[$i]->category][] = $ret[$i];

			if (!$filter['show_empty_groups'])
			{
				// Eliminate the categories for which there are no activities
				foreach ($actypes_categories_list as $category_id => $category_name)
				{
					if (count($tmp[$category_id]) == 0) unset ($tmp[$category_id]);
				}
			}
			$ret = $tmp;

		}
		elseif ($filter['group_by'] == 'contract')
		{
			$tmp = array ();
			// Let's make sure the groups are ordered by the name of contract types
			foreach ($GLOBALS['CONTRACT_TYPES'] as $contract_id => $contract_name) $tmp[$contract_id] = array ();

			for ($i=0,$i_max=count($ret); $i<$i_max; $i++) $tmp[$ret[$i]->contract_types][] = $ret[$i];

			if (!$filter['show_empty_groups'])
			{
				// Eliminate the categories for which there are no activities
				foreach ($GLOBALS['CONTRACT_TYPES'] as $contract_id => $contract_name)
				{
					if (count($tmp[$contract_id]) == 0) unset ($tmp[$contract_id]);
				}
			}
			$ret = $tmp;
		}

		return $ret;
	}
}
?>
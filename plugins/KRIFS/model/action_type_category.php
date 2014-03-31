<?php

/**
* Class for managing the categories of action types.
*
* These categories are defined in the ERP system and they are
* synchronized from there upon request.
*
*/

class ActionTypeCategory extends Base
{
	/** Category ID
	* @var int */
	var $id = null;

	/** ERP ID
	* @var string */
	var $erp_id = '';

	/** Category name
	* @var string */
	var $name = '';

	var $table = TBL_ACTION_TYPES_CATEGORIES;
	var $fields = array ('id', 'erp_id', 'name');


	/**
	* Constructor. Also loads a category information if an ID is provided
	* @param	int	$id		The ID of the category to load
	*/
	function ActionTypeCategory ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}


	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		if (!$this->name) {error_msg ('Please specify the name.'); $ret = false;}
		if (!$this->erp_id) {error_msg ('Please specify the ERP id.'); $ret = false;}

		return $ret;
	}


	/** [Class Method] Returns the list of defined action types categories
	* @return	array			Associative array, the keys being category IDs and
	*					the values being category names.
	*/
	public static function get_categories_list ()
	{
		$ret = array ();
		$q = 'SELECT id, name FROM '.TBL_ACTION_TYPES_CATEGORIES.' ORDER BY name ';
		$ret = DB::db_fetch_list ($q);
		return $ret;
	}

	public static function get_categories()
	{
		$ret = array();
		$q = 'SELECT id FROM '.TBL_ACTION_TYPES_CATEGORIES.' ORDER BY name ';
		$ids = DB::db_fetch_vector ($q);
		foreach($ids as $id){
			$ret[] = new ActionTypeCategory($id);
		}
		return $ret;
	}

	/** [Class Method] Returns the list of defined action types categories, based on the ERP IDs
	* @return	array			Associative array, the keys being category ERP IDs and
	*					the values being category names.
	*/
	public static function get_erp_categories_list ()
	{
		$ret = array ();
		$q = 'SELECT id, name FROM '.TBL_ACTION_TYPES_CATEGORIES.' ORDER BY name ';
		$ret = DB::db_fetch_list ($q);
		return $ret;
	}

	/** [Class Method] Returns an array for translating ERP categories IDs into Keyos categories IDs
	* @return	array			Associative array, the keys being ERP IDs and the values
	*					being Keyos categories IDs
	*/
	public static function get_erp_categories_translation ()
	{
		$ret = array ();
		$q = 'SELECT erp_id, id FROM '.TBL_ACTION_TYPES_CATEGORIES.' ORDER BY erp_id';
		$ret = DB::db_fetch_list ($q);
		return $ret;
	}

	/**
	 * [Class Method] this gets the ActionTypeCategory id from the erp_id
	 *
	 * @return int
	 **/
	public static function get_categ_by_erp_code($code){
		$q = "select id from ".TBL_ACTION_TYPES_CATEGORIES." where erp_id='".$code."'";
		$category = db::db_fetch_field($q, 'id');
		return $category;
	}
}
?>

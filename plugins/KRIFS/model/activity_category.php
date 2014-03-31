<?php

/**
* Class for managing activities categories
*
*/

class_load ('Activity');

class ActivityCategory extends Base
{
	/** Category ID
	* @var int */
	var $id = null;

	/** The name of the category
	* @var string */
	var $name = '';

	/** The code registered in the erp for this category - this is used for the apisoft erp*/
	var $erp_code = "";

	var $table = TBL_ACTIVITIES_CATEGORIES;
	var $fields = array ('id', 'name', 'erp_code');


	/**
	* Constructor. Also loads a activity information if an ID is provided
	* @param	int	$id		The ID of the activity to load
	*/
	function ActivityCategory ($id = null)
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
		if (!$this->name) {error_msg ('Please specify the category name.'); $ret = false;}
		return $ret;
	}

	/** Checks if the category can be deleted */
	function can_delete ()
	{
		$ret = false;
		if ($this->id)
		{
			$ret = true;
			// Check if there aren't activities in this group
			$q = 'SELECT id FROM '.TBL_ACTIVITIES.' WHERE category_id='.$this->id.' LIMIT 1';
			if (db::db_fetch_field ($q, 'id'))
			{
				error_msg ('This category can\'t be deleted, it contains activities.');
				$ret = false;
			}
		}
		return $ret;
	}


	/**
	* [Class Method] Returns a list of categories
	* @return	array			Associative array, they keys being category IDs and the values are categories names
	*/
    public static function get_categories_list ()
	{
		$ret = array ();
		$ret = db::db_fetch_list ('SELECT id, name FROM '.TBL_ACTIVITIES_CATEGORIES.' ORDER BY name, id');
		return $ret;
	}


	/**
	* [Class Method] Returns the categories currently defined in the system
	* @return	array(ActivityCategory)		Array of Activity objects
	*/
    public static function get_categories ($filter = array())
	{
		$ret = array ();

		$q = 'SELECT id FROM '.TBL_ACTIVITIES_CATEGORIES.' ORDER BY name, id ';
		//check if the filter contains a list of codes
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new ActivityCategory ($id);

		return $ret;
	}

	/**
	 * [Class Method]
	 * gets the activity category id associated to the erp_code
	 * @return int
	 */
    public static function get_categ_by_erp_code($code)
	{
		$q = "select id from ".TBL_ACTIVITIES_CATEGORIES." where erp_code='".$code."'";
		$id = db::db_fetch_field($q, "id");
		return $id;
	}
}
?>
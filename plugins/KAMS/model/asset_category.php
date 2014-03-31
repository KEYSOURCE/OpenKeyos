<?php

/**
 * This class manages all the types of assets
 * If an asset is managed by keyos, this class can identify it and linkn to it's specific class
 * For all the assets not managed by keyos that don't have a specialised class the generic category will be used
 */

class AssetCategory extends Base 
{
	/**
	 * The id of the asset category
	 *
	 * @var int
	 */
	var $id = null;
	
	/**
	 * The name of the category
	 *
	 * @var string
	 */
	var $name = '';
	
	/**
	 * Boolean value specifying if this category of objects is currently managed or not by keyos
	 *
	 * @var bool
	 */
	var $is_managed  = false;
	
	/**
	 * The class of the object. 
	 * This integer value will identify the class of the boject from the KAMS_OBJ_CLASSES
	 * 
	 *
	 * @var int
	 */
	var $obj_class = null;
	
	var $table = TBL_ASSET_CATEGORIES;
	
	/** 
	 * List of fields to be used when fetching or saving data to the database
	 * 
	 * @var array 
	 */
	var $fields = array('id', 'name', 'is_managed', 'obj_class');

	/**
	 * [Constructor]
	 * If an id is specified, it loads the data from the database for that  specific object
	 * 
	 * @param int $id
	 * @return AssetCategory
	 */
	function AssetCategory($id = null)
	{
		if($id)
		{	
			$this->id = $id;
			$this->load_data();	
		}
	}
	
	function load_data()
	{
		$ret = false;
		if($this->id)
		{
			parent::load_data();
			if($this->id)
			{
				$ret = true;
				$this->is_managed == 'y' ? $this->is_managed = true : $this->is_managed = false; 
			}
		}
		
		return $ret;
	}
	
	/**
	 * [Class function]
	 * gets an array with all the categories names
	 * 
	 * @return array(string)
	 *
	 */
	function get_categories_names()
	{
		$ret = array();
		$query  = "select id, name from ".TBL_ASSET_CATEGORIES;
		$ret = db::db_fetch_list($query);
		return $ret;
	}
	
	/**
	 * gets the class name of the associated item, managed by keyos
	 * 
	 * 
	 * @param int
	 * A category id, if none is supplied, the current id will be used
	 * @return string;
	 *
	 */
	function get_category_class($categ_id = null)
	{
		if($categ_id == null) $categ_id = $this->id;
		$query = "select obj_class from ".TBL_ASSET_CATEGORIES." where id=".$categ_id;
		$cls_id = Db::db_fetch_field($query, 'obj_class');
		return $GLOBALS["KAMS_OBJ_CLASSES"][$cls_id];
	}
}
?>
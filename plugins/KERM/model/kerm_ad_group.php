<?php

class KermADGroup extends Base 
{
	var $id = null;
	var $name = "";
	var $distinguishedname = "";
	var $description = "";
	var $customer_id = null;
	
	var $table = TBL_KERM_AD_GROUPS;
	var $fields = array('id', 'name', 'distinguished_name', 'description', 'customer_id');
	
	function KermADGroup($id = null)
	{
		if($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	/**
	 * [Class Method]
	 * Gets all the AD groups for the specified customer
	 *
	 * @param unknown_type $customer_id
	 */
	function get_groups_list($customer_id = null)
	{
		$result = array();
		$query = "select id, name from ".TBL_KERM_AD_GROUPS;
		if($customer_id!=null) $query.=" where customer_id=".$customer_id;
		$result = db::db_fetch_list($query);
		return $result;
	}
	function save_data()
	{
		$cnt = db::db_fetch_field("select count(id) as cnt from ".TBL_KERM_AD_GROUPS." where name='".$this->name."' and customer_id=".$this->customer_id, 'cnt');
		if($cnt > 0)
		{
			$query = "update ".TBL_KERM_AD_GROUPS." set distinguishedname='".$this->distinguishedname."', description = '".$this->description."' where name='".$this->name."' and customer_id=".$this->customer_id;
			db::db_query($query);
		}
		else {
			$query = "insert into ".TBL_KERM_AD_GROUPS." (name, distinguishedname, description, customer_id) values ('".$this->name."', '".$this->distinguishedname."', '".$this->description."', ".$this->customer_id.")";
			db::db_query($query);
		}
	}	
}

?>
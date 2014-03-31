<?php
/**
 * Base class for defining an asset
 * An asset can be a Computer, a Pheriperal (including AD Printer), a software package,
 * or any kind of asset that is not currently managed by Keyos (car, xerox machine, coffee maker, etc....)
 */

class_load('AssetCategory');
class_load('AssetFinancialInfo');

class Asset extends Base 
{
	/**
	 * Asset ID
	 *
	 * @var int
	 */
	var $id = null;
	
	/**
	 * Customer's ID
	 *
	 * @var int
	 */
	var $customer_id = null; 
	
	/**
	 * The ID of the Asset category. 
	 * There are some special categories that are managed by keyos
	 *
	 * @var int
	 */
	var $category_id = null;
	
	/**
	 * Flag that signals if this typep of asset is managed by keyos or not
	 * If this flag is set to true, the associated id must be set
	 *
	 * @var bool
	 */
	var $is_managed = false;
	
	/**
	 * If this asset is managed by keyos, this id links this asset to the specified item managed by keyos
	 * e.g. if this asset is a computer, the associated_id is the id from TBL_COMPUTERS
	 *
	 * @var unknown_type
	 */
	var $associated_id = '0';
	
	/**
	 * A descriptive name for the asset
	 *
	 * @var string
	 */
	var $name = '';
	
	/**
	 * Various comments for this asset
	 *
	 * @var unknown_type
	 */
	var $comments = '';
	
	/**
	 * Table storing assets data
	 *
	 * @var string
	 */
	var $table = TBL_ASSETS;
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id','name', 'customer_id', 'category_id', 'is_managed', 'associated_id', 'comments');

	
	/**
	 * The associated category object
	 *
	 * @var AssetCategory
	 */
	var $category = null;
	
	/**
	 * a list of id's of financial information associated with this asset
	 *
	 * @var array(int)
	 */
	var $financial_infos = array();
	
	/**
	 * [Constructor] 
	 * Also loads the asset data from the database if an ID is specified
	 *
	 * @param int $id
	 * @return Asset
	 */
	function Asset($id = null)
	{
		if($id)
		{
			$this->id = $id;
			$this->load_data();
                        //$this->verify_access();
		}
	}
	
	/**
	 * Loads asset data from the database
	 *
	 */
	function load_data()
	{
		$ret = false;
		if($this->id)
		{	
			parent::load_data();
			if($this->id)
			{
				$ret = true;
				//convert the is_managed from enum to bool
				$this->is_managed == 'y' ? $this->is_managed = true : $this->is_managed = false;
			}
			
			//load the list of financial infos id's
			$this->financial_infos = $this->db_fetch_vector('SELECT id from '.TBL_ASSET_FINANCIAL_INFOS.' where asset_id = '.$this->id);
			$this->category = new AssetCategory($this->category_id);
		}
		return $ret;
	}
	
	/**
	 * If this asset is unmanaged it'll return this object
	 * if this asset is managed by keyos it'll return an object of it's specific class
	 *
	 */
	function get_specific_object()
	{
		if(!$this->is_managed) return $this;
		
		//this object is managed by keyos this means that we have an associated_id
		$class_name = $GLOBALS['KAMS_OBJ_CLASSES'][$this->category->obj_class];
		
		if($class_name) return new $class_name($this->associated_id);
		return $this;
	}
	
	/**
	 * get a financial info for this asset based on a ID
	 * this id sould be one of the values in the $this->financial_infos
	 * 
	 * @param inancial_info_id
	 * @return AssetFinancialinfo
	 */
	function get_financial_info($financial_info_id)
	{
		return new AssetFinancialInfo($financial_info_id);
	}
	
	/**
	 * gets the list of financial infos associated with this asset
	 *
	 * @return array(AssetFinancialInfo)
	 */
	function get_asset_financial_infos()
	{
		$ret = array();
		if($this->financial_infos)
		{
			foreach ($this->financial_infos as $financial_info_id)
			{
				$financial_info = $this->get_financial_info($financial_info_id);
				$ret[] = $financial_info;
			}
		}
		return $ret;
	}
	
	/**
	 * get all the invoices numbers associated with this asset
	 *
	 * @return array(string)
	 */
	function get_asset_invoices()
	{
		$ret = array();
		$query = "select invoice_number from ".TBL_ASSET_FINANCIAL_INFOS." where asset_id = ".$this->id." order by id desc";
		
		$invoices = db::db_fetch_vector($query);
		
		foreach ($invoices as $inv)
			$ret[] = $inv;
		return $ret;
	}
	
	/** 
	* [Class Method] Returns the list of assets purchased by a customer.
	* @param	int		$customer_id			The ID of the customer
	* @return	array (Asset)
	*/
	function get_customer_assets($customer_id = null)
	{
		$ret = array ();
		
		if ( $customer_id and is_numeric($customer_id) )
		{
			$q = "select id from ".TBL_ASSETS." where customer_id = ".$customer_id." order by id desc";
			
			$ids = db::db_fetch_array ($q);
			
			foreach ($ids as $id)
			{
				$asset = new Asset($id->id);
				$ret[] = $asset;
			}
		}
		
		return $ret;
	}
	
	/**
	 * Based on the category gets a list of all the possilbe ids
	 *
	 * @param int 
	 * the id of the selected category, if no parameter is supplied, the current category_id will be assumed
	 * @param int 
	 * the client id, if no parameter is supplied the client asset of the current object will be used
	 * @return array(int)
	 * returns a list of all the id's from that category. If the Generic category is supplied function return FALSE;
	 */
	function get_assoc_list($categ_id = null, $client_id = null)
	{
		$ret = array();
		
		if($categ_id == null) $categ_id = $this->category_id;
		if($client_id == null) $client_id = $this->customer_id;
		//if(!$this->is_managed) return false;
		//$obj_cls = $this->category->get_category_class($categ_id); 
		$obj_cls = AssetCategory::get_category_class($categ_id);
		switch ($obj_cls)
		{
			case 'Computer':
				$query = "select id, netbios_name from ".TBL_COMPUTERS." where customer_id = ".$client_id;
				$ret = Db::db_fetch_list($query);				
				break;
			case "Peripheral":
				$query = "select id, name from ".TBL_PERIPHERALS." where customer_id = ".$client_id;
				$ret = Db::db_fetch_list($query);
				break;
			case "AD_Printer":
				//$query = "select id, name from ".TBL_PERIPHERALS." where customer_id = ".$this->customer_id;
				//$ret = Db::db_fetch_list($query);
				class_load('AD_Printer');
				$ret = AD_Printer::get_ad_printers_list(array('customer_id' => $client_id));
				break;
				
		}
		return $ret;
	}
	
	/**
	 * Checks if the object's data is valid
	 *
	 * @return bool
	 */
	function is_valid_data()
	{
		$valid = true;
		if(!$this->name) {error_msg($this->get_string('NEED_NAME')); $valid = false;}
		if(!$this->category_id) {error_msg($this->get_string('NEED_CATEGORY')); $valid = false;}
		return $valid;
	}
	
	/**
	 * overloaded function
	 * saves the object's data
	 *
	 */
	function save_data()
	{
		$managed = $this->is_managed;
		if(!$managed) $this->associated_id = 0;
		$managed ? $this->is_managed = 'y' : $this->is_managed='n';
		parent::save_data();
		$this->is_managed = $managed;
	}
	
	/**
	 * inserts new asset into the database
	 *
	 * @return bool
	 */
	function add_new()
	{
		$managed = $this->is_managed;
		if(!$managed) $this->associated_id = 0;
		$managed ? $this->is_managed = 'y' : $this->is_managed='n';
		
		$ret = true;
		if (!empty($this->table))
		{
			$q = 'INSERT INTO '.$this->table.' ( ';
			foreach ($this->fields as $field)
			{
				if($field!="id")
				{
					$q.=$field.', ';
				}
			}
			$q = preg_replace('/,\s*$/', '', $q);
			$q.=") VALUES (";
			foreach ($this->fields as $field)
			{
				if($field!="id")
				{
					if (is_string($this->$field) and $this->$field == "NULL") $q.= 'NULL, ';
					else $q.= '"'.mysql_escape_string($this->$field).'", ';
				}
			}
			$q = preg_replace('/,\s*$/', '', $q);
			$q.=");";

			$this->db_query($q);
			
			$ret = (!$this->db_error());
		}
		$this->is_managed = $managed;
		return $ret;	
	}
	
	/**
	 * Overloaded delete function
	 *
	 */
	function delete()
	{
		//1. delete all the financial informations associated with this asset 
		//2. delete the asset from the database
		//3. delete the aassociated managed item
		
		//1. delete all the financial informations
		foreach($this->financial_infos as $financial)
		{
			$finfo = new AssetFinancialInfo($financial);			
			$finfo->delete();
		}
		//2. delete the asset
		parent::delete();
		
		//3. XXX here I should delete the associated item
	}
	
	/**
	 * [Class method]
	 * syncronize the manage assets with the assets by category
	 *
	 * @param int $cat_id The category id to be syncronized
	 * @param int $customer_id The id of the customer
	 */
	function syncronize($cat_id, $customer_id)
	{
		switch ($cat_id)
		{
			case KAMS_OBJ_CLASS_COMPUTER+1:
				$query = "SELECT id, netbios_name, comments from ".TBL_COMPUTERS." where customer_id = ".$customer_id;
				$comp_list = Db::db_fetch_array($query);
				foreach($comp_list as $comp)
				{
					$query = "select count(id) as cnt from ".TBL_ASSETS." where is_managed='y' and associated_id='".$comp->id."'";
					$res = Db::db_fetch_field($query, 'cnt');
					if($res == 0)
					{
						//now we can begin the syncronize process
						$asset = new Asset();
						$asset->name = 'A_'.$comp->netbios_name.'_'.$comp->id;
						$asset->customer_id = $customer_id;
						$asset->category_id = $cat_id;
						$asset->is_managed = 'y';
						$asset->associated_id = $comp->id;
						$asset->comments = ''.$comp->comments;
						$asset->add_new();
					}
				}
				break;
			case KAMS_OBJ_CLASS_PERIPHERAL+1:
				$query = "SELECT id, name from ".TBL_PERIPHERALS." where customer_id = ".$customer_id;
				$per_list = Db::db_fetch_array($query);
				foreach($per_list as $per)
				{
					$query = "select count(id) as cnt from ".TBL_ASSETS." where is_managed='y' and associated_id='".$per->id."'";
					$res = Db::db_fetch_field($query, 'cnt');
					if($res == 0)
					{
						//now we can begin the syncronize process
						$asset = new Asset();
						$asset->name = 'A_'.$per->name.'_'.$per->id;
						$asset->customer_id = $customer_id;
						$asset->category_id = $cat_id;
						$asset->is_managed = 'y';
						$asset->associated_id = $per->id;
						$asset->comments = '';
						$asset->add_new();
					}
				}
				break;
			case KAMS_OBJ_CLASS_AD_PRINTER+1:
				class_load('AD_Printer');
				$printers = AD_Printer::get_ad_printers_list(array('customer_id'=>$customer_id));
				foreach ($printers as $printer)
				{
					$printer_keys = array_keys($printers, $printer);
					$query = "select count(id) as cnt from ".TBL_ASSETS." where is_managed='y' and associated_id='".$printer_keys[0]."'";
					$res = Db::db_fetch_field($query, 'cnt');
					if($res == 0)
					{
						$asset = new Asset();
						$asset->name = 'A_'.$printer;
						$asset->customer_id = $customer_id;
						$asset->category_id = $cat_id;
						$asset->is_managed = 'y';
						$asset->associated_id = $printer_keys[0];
						$asset->comments = '';
						$asset->add_new();
					}
					
				}
				break;	
		}
	}

        function verify_access() {
            $uid = get_uid();
            class_load('User');
            $user = new User($uid);
            if($user->type == USER_TYPE_CUSTOMER) {
                if($this->customer_id != $user->customer_id) {
                    $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
                    header("Location: $url\n\n");
                    exit;
                }
            }
        }
}
?>
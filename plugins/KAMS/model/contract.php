<?php
	/**
	 * Base class for defining a contract
	 * based on the flags set in the contract type, only some fields on the contracts table will be mandatory
	 */
	class_load("ContractType");
	class Contract extends Base 
	{
		/**
		 * Contract ID
		 *
		 * @var int
		 */
		var $id = null;
		/**
		 * The name of the contract
		 *
		 * @var string
		 */
		var $name = "";
		/**
		 * The contract number
		 *
		 * @var string
		 */
		var $contract_number = "";
		/**
		 * Notes on this contract
		 *
		 * @var string
		 */
		var $notes = "";
		/**
		 * The contract type
		 * it's a foreign key on the contract_types table
		 * based on the contract type some of the fileds will be mandatory
		 *
		 * @var int
		 */
		var $contract_type = null;
		/**
		 * The customer id to whom this contract is assigned
		 *
		 * @var int
		 */
		var $customer_id = null;
		/**
		 * If the contract type has the "quantity" flag set, this filed will be mandatory
		 * specifies the quantity/amount of this contract
		 *
		 * @var float;
		 */
		var $quantity = null;
		/**
		 * if the contract type has the "total_price" flag set
		 * the total price of this contract 
		 *
		 * @var float
		 */
		var $total_price = null;
		/**
		 * if the contract type has the "recurring_payments" flag set
		 * specifies the id of the corresponding payment_period in the
		 * TBL_PAYMENT_PERIODS
		 *
		 * @var int
		 */
		var $payment_periods = null;
		/**
		 * if the contract type has the "recurring_payments" flag set
		 * the price per payment period
		 *
		 * @var float
		 */
		var $price_per_period = null;
		/**
		 * the start date of the contract
		 *
		 * @var int
		 */
		var $start_date = null;
		/**
		 * if the contract type has the "end_date" flag set
		 * the expiration date of the contract
		 *
		 * @var int
		 */
		var $end_date = null;
		/**
		 * if the contract type has the "vendor" flag set
		 * the vendor id
		 *	
		 * @var int
		 */
		var $vendor = null;
		/**
		 * if the contract type has the "supplier" flag set
		 * the supplier id
		 *
		 * @var int
		 */
		var $supplier = null;
		/**
		 * if the contract type has the "send_period_notifs" flag set
		 * the notice period
		 *
		 * @var int
		 */
		var $notice_period = null;
		/**
		 * if the contract type has the "send_period_notifs" or the "send_expiration_notifs" flag set
		 * an additional customer id who should receive notifications
		 *
		 * @var int
		 */
		var $additional_notifiable_customer_id = null;
		/**
		 * if the contract type has the "supports_renewals" flag set
		 *
		 * @var int
		 */
		var $renewal_period = null;
		
		/**
		 * the currency that applies for this contract
		 * can exist if the contract type has the "total_price" or the "recurring_payments" flags set
		 *
		 * @var int
		 */
		var $currency = 1;
		
		/**
		 * Table storing contracts
		 *
		 * @var string
		 */
		var $table = TBL_CONTRACTS; 
		
		/**
		 * list of fields used when storing or fetching data into the contracts table
		 *
		 * @var array(string)
		 */
		var $fields = array('id', 'name', 'contract_number', 'notes', 'contract_type', 'customer_id', 'quantity', 'total_price', 'payment_periods', 'price_per_period', 'start_date', 'end_date', 'vendor', 'supplier', 'notice_period', 'additional_notifiable_customer_id', 'renewal_period', 'currency');
		
		/**
		 * the ContractType of this contract
		 *
		 * @var ContractType
		 */
		var $type = null;
		
		/**
		 * [Constructor]
		 * Creates and Contract object. 
		 * If an id is specified loads the objects data from the database
		 *
		 * @param int $id
		 * @return Contract
		 */
		function Contract($id = null)
		{
			if($id)
			{	
				$this->id = $id;
				$this->load_data();
                                //$this->verify_access();
			}
		}
		
		/**
		 * loads contract's data from the database
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
					//additional load here...
					$this->type = new ContractType($this->contract_type);
				}
			}
			return $ret;
		}
		
		/**
		 * returns an array with all the contracts of a specific user
		 *
		 * @param int $customer_id
		 * @return array		  
		 */
		function get_customer_contracts($customer_id)
		{
			$ret = array();
			$query = "select id from contracts where customer_id = ".$customer_id;
			$ret = db::db_fetch_array($query);
			return $ret;
		}

		/**
		 * [Class Method]
		 * Returns an array with all possible payment periods
		 *
		 * @return array
		 */
		function get_payment_periods()
		{
			$ret = array();
			$query = "select id, period_name from ".TBL_CONTRACTS_PAYMENT_PERIODS;
			$ret = db::db_fetch_list($query);
			return $ret;
		}
		
		/**
		 * checks if the input data is validd
		 *
		 * @return bool
		 */
		function is_valid_data()
		{
			$valid = true;
			if(!$this->name) { error_msg($this->get_string('NEED_CONTRACT_NAME')); $valid = false; }
			if(!$this->contract_number) { error_msg($this->get_string('NEED_CONTRACT_NUMBER')); $valid = false; }
			if(!$this->contract_type) {	error_msg($this->get_string('NEED_CONTRACT_TYPE')); $valid = false;}
			if(!$this->start_date) { error_msg($this->get_string('NEED_CONTRACT_STARTDATE')); $valid = false; }
			
			$contract_type = new ContractType($this->contract_type);
			//if the contract type requires a quantity/amount force the user to apecify it
			if($contract_type->quantity && !$this->quantity)
			{
				error_msg($this->get_string('NEED_CONTRACT_QUANTITY'));
				$valid = false;
			}
			
			//if the contract type requires a total price
			if($contract_type->total_price && !$this->total_price)
			{
				error_msg($this->get_string('NEED_CONTRACT_TOTALPRICE'));
				$valid = false;
			}
			
			//if the contract type supports recurring payments
			if($contract_type->recurring_payments && !$this->payment_periods)
			{
				error_msg($this->get_string('NEED_CONTRACT_PAYMENTPERIODS'));
				$valid = false;
			}
			if($contract_type->recurring_payments && !$this->price_per_period)
			{
				error_msg($this->get_string('NEED_CONTRACT_PRICEPERPERIOD'));
				$valid = false;
			}
			
			//expiration date
			if($contract_type->end_date && !$this->end_date)
			{
				error_msg($this->get_string('NEED_CONTRACT_ENDDATE'));
				$valid = false;
			}
			//vendor
			if($contract_type->vendor && !$this->vendor)
			{
				error_msg($this->get_string('NEED_CONTRACT_VENDOR'));
				$valid = false;
			}
			//supplier
			if($contract_type->supplier && !$this->supplier)
			{
				error_msg($this->get_string('NEED_CONTRACT_SUPPLIER'));
				$valid = false;
			}
			
			//periodical notifications
			if($contract_type->send_period_notifs && !$this->notice_period)
			{
				error_msg($this->get_string('NEED_CONTRACT_NOTICEPERIOD'));
				$valid = false;
			}
			
			//renewal period
			if($contract_type->supports_renewals && !$this->renewal_period)
			{
				error_msg($this->get_string('NEED_CONTRACT_RENEWAL'));
				$valid = false;
			}
			
			return $valid;	
		}
				
		/**
		 * Adds a new contract into the database
		 * @return bool
		 */
		function add_new()
		{	
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
						if($field == "quantity" && !$this->$field) $q.= 'NULL, ';
						elseif($field == "total_price" && !$this->$field) $q.= 'NULL, ';
						elseif($field == "payment_periods" && !$this->$field) $q.= 'NULL, ';
						elseif($field == "price_per_period" && !$this->$field) $q.= 'NULL, ';
						elseif($field == "end_date" && !$this->$field) $q.= 'NULL, '; 
						elseif($field == "vendor" && !$this->$field) $q.= 'NULL, ';
						elseif($field == "supplier" && !$this->$field) $q.= 'NULL, ';
						elseif($field == "notice_period" && !$this->$field) $q.= 'NULL, ';
						elseif($field == "additional_notifiable_customer_id" && !$this->$field) $q.= 'NULL, ';
						elseif($field == "renewal_period" && !$this->$field) $q.= 'NULL, ';
						else {
							if (is_string($this->$field) and $this->$field == "NULL") $q.= 'NULL, ';
							else $q.= '"'.mysql_escape_string($this->$field).'", ';
						}
						//if(!is_string($this->$field) and !$this->$field) $q .= 'NULL, ';
					}
				}
				$q = preg_replace('/,\s*$/', '', $q);
				$q.=");";
	
				//debug($q);
				$this->db_query($q);
				
				$ret = (!$this->db_error());
			}
			return $ret;
		}
		
		function save_data()
		{
			$ret = true;
			if (!empty($this->table))
			{
				$q = 'REPLACE INTO '.$this->table.' SET ';
				$this->type = new ContractType($this->contract_type);
				foreach ($this->fields as $field)
				{
					if($field == "quantity" && (!$this->$field || !$this->type->quantity)) $q.= $field.'=NULL, ';
					elseif($field == "total_price" && (!$this->$field || !$this->type->total_price)) $q.= $field.'=NULL, ';
					elseif($field == "payment_periods" && (!$this->$field || !$this->type->recurring_payments)) $q.= $field.'=NULL, ';
					elseif($field == "price_per_period" && (!$this->$field || !$this->type->recurring_payments)) $q.= $field.'=NULL, ';
					elseif($field == "end_date" && (!$this->$field || !$this->type->end_date)) $q.= $field.'=NULL, '; 
					elseif($field == "vendor" && (!$this->$field || !$this->type->vendor)) $q.= $field.'=NULL, ';
					elseif($field == "supplier" && (!$this->$field || !$this->type->supplier)) $q.= $field.'=NULL, ';
					elseif($field == "notice_period" && (!$this->$field || !$this->type->send_period_notifs)) $q.= $field.'=NULL, ';
					elseif($field == "additional_notifiable_customer_id" && (!$this->$field || !($this->type->send_period_notifs || $this->type->send_expiration_notifs))) $q.= $field.'=NULL, ';
					elseif($field == "renewal_period" && (!$this->$field || !$this->type->supports_renewals)) $q.= $field.'=NULL, ';
					else {
						if (is_string($this->$field) and $this->$field == "NULL") $q.= $field.'=NULL, ';
						else $q.= $field.'="'.mysql_escape_string($this->$field).'", ';
					}
				}
				$q = preg_replace('/,\s*$/', '', $q);
				
				//debug($q);
				$this->db_query($q);
				
				$ret = (!$this->db_error());
	
				if (empty($this->id))
				{
					$this->id = $this->db_insert_id();
					$ret = ($ret and !empty($this->id));
				}
			}
			return $ret;
		}
		
		/**
		 * [Class Method]
		 * gets all the currencies
		 * returns an associative array where the key is the id and the value is the 
		 * corresponding value of the $field from the database
		 * @param field
		 * Can take one of the values "name", "symbol". If other field is specified, the function will return an empty field
		 * @return array
		 */
		function get_currecies($field)
		{
			$ret = array();
			$field = trim($field);
			$file = strtolower($field);
			
			if($field == "name" || $field == "symbol")
			{
				$query = "select id, ".$field." from ".TBL_CURRENCY;
				$ret  = db::db_fetch_list($query);
			}
			return $ret;
		}
		
		/**
		 * links this contract to 1 or more assets
		 *
		 * @param int $asset_id
		 * @return bool
		 */
		function link_to_asset($asset_id)
		{
			$ret = false;
			$query = "REPLACE INTO ".TBL_CONTRACTS_ASSETS." SET ";
			foreach($assets as $asset_id)
			{
				$query .= "asset_id = ".$asset_id.", contract_id = ".$this->id.""; 
			}
			return $ret;
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
	
?>
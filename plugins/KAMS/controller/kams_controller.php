<?php
	class_load('Asset');
	class_load('AssetFinancialInfo');
	/**
	 * Class for handling the display of different KeyosAssetsManagemntSystem (KAMS)
	 */


	class KamsController extends PluginController
	{
		/**
		 * [Constructor]
		 *
		 * @return KamsDisplay
		 */
        protected $plugin_name = "KAMS";
        function __construct() {
            $this->base_plugin_dir = dirname(__FILE__).'/../';
            parent::__construct();
        }
		
		
		/********************************************************************************************************
		*	Customer assets management page
		********************************************************************************************************/
		
		/**
		 * Shows the list of the assets registered in the KAMS
		 *
		 */
		function manage_assets()
		{
			check_auth(array('customer_id' => $this->vars['customer_id']));
			class_load('Customer');
			
			$tpl = "manage_assets.tpl";
			
			//Extract the list of customers, eventually  restricting to the list assigned to this Customer
			$customers_filter = array('favourites_list' => $this->current_user->id);
			if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
			$customers = Customer::get_customers_list ($customers_filter);
			
			//If an Asset ID was passed, extract the customer id from it
			if($this->vars['asset_id'])
			{
				$asset = new Asset($this->vars['asset_id']);
				$this->vars['customer_id'] = $asset->customer_id;
			}
			
			if($this->vars['customer_id'])
			{
				$customer = new Customer($this->vars['customer_id']);
			}
			elseif ($this->locked_customer->id)
			{
				$customer = new Customer($this->locked_customer->id);
			}
			
			
			if($customer->id)
			{
				//a valid customer was loaded, so load the needed informations
				$assets = Asset::get_customer_assets($customer->id);
				//mark the potential client for locking
				$_SESSION['potential_lock_customer_id'] = $customer->id;
			}
			$filter = $_SESSION['assets']['filter'];
			if(!isset($filter['order_by'])) $filter['order_by'] = 'id';
			
			//debug($this->vars['customer_id']);
			$this->assign("filter", $filter);
			$this->assign("customers", $customers);
			$this->assign("customer", $customer);
			$this->assign("assets", $assets);
			$this->assign("KAMS_OBJ_CLASSES", $GLOBALS['KAMS_OBJ_CLASSES']);

			$this->assign("error_msg", error_msg());
			$this->set_form_redir ('manage_assets');

			$this->display($tpl);
		}
		
		
		/**
		 * Shows a page for editing the data for an asset
		 * If this asset is managed have a link to it's associated KeyOS managed item
		 *
		 */
		function asset_edit()
		{
			$tpl = "asset_edit.tpl";
			
			
			$asset = new Asset($this->vars['id']);
			if(!$asset->id) $this->mk_redir('manage_assets');
			$customer = new Customer($asset->customer_id);
			
			check_auth(array( 'customer_id' => $asset->customer_id));
			
			//mark potential customer for locking
			$_SESSION['potential_lock_customer_id'] = $customer->id;
			
			$asset_types = array(
				 0 => "Not managed by KeyOS",
				 1 => "Managed by KeyOS"
			);
			
			$categories = $asset->category->get_categories_names();
			//if($this->vars['cat_id'])
			$associated_list = $asset->get_assoc_list($this->vars['cat_id']);
			
			//get financial informations for this asset
			$finInfos = array();
			foreach ($asset->financial_infos as $id)
			{
				$finInfo = $asset->get_financial_info($id);
				$finInfos[$id] = $finInfo;
			}
			if($asset->category_id == KAMS_OBJ_CLASS_AD_PRINTER+1)
			{
				list($comp_id, $adp_id, $nrc) = split("_", $asset->associated_id);
			}
			//template assign
			$this->assign('customer', $customer);
			$this->assign('asset', $asset);
			$this->assign('asset_types', $asset_types);
			$this->assign('error_msg', error_msg());
			$this->assign('categories', $categories);
			$this->assign('associated_list', $associated_list);
			$this->assign('finInfos', $finInfos);
			if($comp_id)
			{
				$this->assign('comp_id', $comp_id);
				$this->assign('nrc', $nrc);
			}
			
			$this->set_form_redir('asset_edit_submit', array('id' => $asset->id, 'customer_id'=>$customer->id));
			
			$this->display($tpl);
			
		}
		
		/**
		 * Submit the edited  asset
		 *
		 */
		function asset_edit_submit()
		{
			check_auth();
			$ret = $this->mk_redir("manage_assets", array('customer_id'=>$this->vars['customer_id']));
			$asset = new Asset($this->vars['id']);
			if($this->vars['delete'] && $asset->id)
			{
				$ret = $this->mk_redir("delete_asset", array('asset_id' => $asset->id, 'customer_id'=>$this->vars['customer_id']));
			}
			elseif($this->vars['save'] && $asset->id)
			{
				$asset_data = $this->vars['asset'];
				$asset->load_from_array($asset_data);
				//debug($asset_data);
				if($asset->is_valid_data())
				{
					//debug($asset);
					$asset->save_data();
				}
				$ret = $this->mk_redir ('asset_edit', array ('id'=>$asset->id));
			}
			return $ret;
		}
		
		/**
		 * Shows a page for adding an asset
		 *
		 */
		function asset_add()
		{
			$tpl = "asset_add.tpl";
			
			check_auth(array( 'customer_id' => $this->vars['customer_id']));
			class_load("Customer");
			$customer = new Customer($this->vars['customer_id']);
			if(!$customer) $this->mk_redir('manage_assets');
			
			//mark potential customer for locking
			$_SESSION['potential_lock_customer_id'] = $customer->id;
			
			$asset = new Asset();
			
			if(!empty_error_msg()) 
			{
				$asset->load_from_array(restore_form_data('asset_data',true, $asset_data));
			}
			$categories = AssetCategory::get_categories_names();
			
			$asset_types = array(
				 0 => "Not managed by KeyOS",
				 1 => "Managed by KeyOS"
			);
			
			$this->assign('customer', $customer);
			$this->assign('categories', $categories);
			$this->assign('asset_types', $asset_types);
			$this->assign('error_msg', error_msg());
			$this->set_form_redir('asset_add_submit', array('customer_id'=>$this->vars['customer_id']));
			
			$this->display($tpl);
			
		}
		
		/**
		 * Submits the newly added asset
		 *
		 */
		function asset_add_submit()
		{
			check_auth();
			if(!$this->vars['customer_id']) 
				$ret = $this->mk_redir('manage_assets');
			else
				$ret = $this->mk_redir('manage_assets', array('customer_id' =>  $this->vars['customer_id']));
			$asset = new Asset();
			if($this->vars['save'])
			{
				$asset_data = $this->vars['asset'];
				$asset->load_from_array($asset_data);
				$asset->customer_id = $this->vars['customer_id'];
				if(!$asset->associated_id) $asset->associated_id = 0;
				if($asset->is_valid_data())
				{
					//debug($asset);
					$asset->add_new();
				}
				else 
				{
					save_form_data($asset_data, 'asset_data');
					$ret = $this->mk_redir('asset_add', array('customer_id' => $this->vars['customer_id']));
				}
			}
			return $ret;
			
		}
		
		/**
		 * add a new financial information for the specified asset
		 *
		 */
		function financial_infos_add()
		{
			$tpl = "financial_infos_add.tpl";
			$asset = new Asset($this->vars['asset_id']);
			if(!$asset->id) $this->mk_redir('manage_assets');
			check_auth(array('customer_id' => $asset->customer_id));
			class_load("Customer");
			$customer = new Customer($asset->customer_id);
			if(!$customer) $this->mk_redir('asset_edit', array('id'=>$asset->id));
			
			$_SESSION['potential_lock_customer_id'] = $customer->id;
			
			class_load("Supplier");
			$suppliers = Supplier::get_supplier_names();
			
			$currencies = AssetFinancialInfo::get_currecies('name');
			$currency_symbols = AssetFinancialInfo::get_currecies('symbol');
			
			$this->assign('suppliers', $suppliers);
			$this->assign('currency_symbols', $currency_symbols);
			$this->assign('currencies', $currencies);
			$this->assign('customer', $customer);
			$this->assign('asset', $asset);
			$this->assign('error_msg', error_msg());
			
			$this->set_form_redir('financial_infos_add_submit', array('asset_id' => $asset->id));
			$this->display($tpl);
		}
		
		/**
		 * submits the newly added financial infos
		 *
		 */
		function financial_infos_add_submit()
		{
			check_auth();
			$ret = $this->mk_redir("asset_edit", array('id'=>$this->vars['asset_id']));
			$finfo = new AssetFinancialInfo();
			if($this->vars['save'])
			{
				$fin_data = $this->vars['fin_infos'];
				$fin_data['invoice_date'] = js_strtotime($fin_data['invoice_date']); 
				$finfo->load_from_array($fin_data);
				$finfo->asset_id = $this->vars['asset_id'];
				//debug($finfo);
				if($finfo->is_valid_data())
				{
					$finfo->add_new();
				}
				$ret = $this->mk_redir ('financial_infos_add', array ('asset_id'=>$this->vars['asset_id']));
			}
			return $ret;
		}
		
		/**
		 * edit the specified financial informations for
		 *
		 */
		function financial_infos_edit()
		{
			check_auth();
			$tpl = "financial_infos_edit.tpl";
			$asset = new Asset($this->vars['asset_id']);
			if(!$asset->id) $this->mk_redir('manage_assets');
			
			$financial_info = new AssetFinancialInfo($this->vars['fin_info_id']);
			if(!$financial_info->id) $this->mk_redir('asset_edit', array('id'=>$asset->id));
			
			class_load("Customer");
			$customer = new Customer($asset->customer_id);
			if(!$customer) $this->mk_redir('asset_edit', array('id'=>$asset->id));
			
			$_SESSION['potential_lock_customer_id'] = $customer->id;
			
			class_load("Supplier");
			$suppliers = Supplier::get_supplier_names();
			
			$currencies = AssetFinancialInfo::get_currecies('name');
			$currency_symbol = $financial_info->get_currency_symbol();
			
			$this->assign('suppliers', $suppliers);
			$this->assign('currency_symbol', $currency_symbol);
			$this->assign('financial_info', $financial_info);
			$this->assign('currencies', $currencies);
			$this->assign('customer', $customer);
			$this->assign('asset', $asset);
			$this->assign('error_msg', error_msg());
			
			$this->set_form_redir('financial_infos_edit_submit', array('asset_id' => $asset->id, 'fin_info_id' => $financial_info->id));
			$this->display($tpl);
		}
		/**
		 * submits the data for the edited finacial info
		 *
		 * @return unknown
		 */
		function financial_infos_edit_submit()
		{
			check_auth();
			$ret = $this->mk_redir("asset_edit", array('id'=>$this->vars['asset_id']));
			$finfo = new AssetFinancialInfo($this->vars['fin_info_id']);
			if($this->vars['save'])
			{
				$fin_data = $this->vars['fin_infos'];
				$fin_data['invoice_date'] = js_strtotime($fin_data['invoice_date']); 
				$finfo->load_from_array($fin_data);
				$finfo->asset_id = $this->vars['asset_id'];
				if($finfo->is_valid_data())
				{
					$finfo->save_data();
				}
				$ret = $this->mk_redir ('financial_infos_edit', array ('asset_id'=>$this->vars['asset_id'], 'fin_info_id'=>$finfo->id));
			}
			return $ret;
		}
		
		/**
		 * deletes a financial information
		 *
		 */
		function financial_infos_delete()
		{
			check_auth(array('asset_id'=>$this->vars['asset_id']));
			$asset = new Asset($this->vars['asset_id']);
			if(!$asset->id) return $this->mk_redir('manage_assets', array('customer_id'=>$asset->customer_id));
			
			$fin_info = new AssetFinancialInfo($this->vars['fin_info_id']);
			if($fin_info->id) $fin_info->delete();
			$ret = $this->mk_redir('asset_edit', array('id'=>$asset->id));
			return $ret;
		}
		
		/**
		 * deletes an asset
		 *
		 * @return unknown
		 */
		function delete_asset()
		{
			$tpl = "delete_asset.tpl";
			check_auth(array('asset_id' => $this->vars['asset_id'], 'customer_id' => $this->vars['customer_id']));
			class_load("Customer");
			$customer  = new Customer($this->vars['customer_id']);
			if(!$customer->id) return $this->mk_redir('manage_assets');
			$asset = new Asset($this->vars['asset_id']);
			if(!$asset->id) return $this->mk_redir('manage_assets', array('customer_id' => $customer->id));
			$this->assign('customer', $customer);
			$this->assign('asset', $asset);
			$this->assign('error_msg', error_msg());
			
			$this->set_form_redir('delete_asset_submit', array('asset_id'=>$asset->id, 'customer_id'=>$customer->id));
			$this->display($tpl);
			
		}
		
		/**
		 * submits the deletion of an asset
		 */
		function delete_asset_submit()
		{
			check_auth(array('asset_id'=>$this->vars['asset_id'], 'customer_id' => $this->vars['customer_id']));
			$ret = $this->mk_redir('asset_edit', array('id' => $this->vars['asset_id']));
			if($this->vars['delete'])
			{
				$asset  = new Asset($this->vars['asset_id']);
				if(!$asset) return $this->mk_redir('asset_edit', array('id'=>$this->vars['asset_id']));
				$asset->delete();	
				$ret = $this->mk_redir('manage_assets', array('customer_id'=>$this->vars['customer_id']));	
			}
			return $ret;
		}
		
		/************************************************************************************
		* Asset syncronizing functions
		*************************************************************************************/
		
		/**
		 * add a syncronize options page
		 *
		 */
		function syncronize()
		{
			$tpl = "asset_syncronize.tpl";
			class_load('Customer');
			check_auth(array('customer_id' => $this->vars['customer_id']));
			
			$customer_filter = array('favourites_list' => $this->current_user->id);
			if($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
			
			$customers = Customer::get_customers_list($customers_filter);
			if($this->vars['customer_id'])
			{
				$customer = new Customer($this->vars['customer_id']);
			}
			elseif ($this->locked_customer->id)
			{
				$customer = new Customer($this->locked_customer->id);
			}
			$categories['sync'] = array();
			if($customer->id)
			{
				$_SESSION['potential_lock_customer_id'] = $customer->id;
				class_load('AssetCategory');
				$categs = AssetCategory::get_categories_names(); 
				foreach ($categs as $cat)
				{
					if($cat != 'Generic')
					{
						$keys = array_keys($categs, $cat);
						$categories['available'][$keys[0]] = $cat;
					}
				}
			}
			$this->assign('customers' , $customers);
			$this->assign('categories', $categories);
			$this->assign('customer' , $customer);
			//$this->assign('available', $available);
			$this->assign('error_msg' , error_msg());
			
			$this->set_form_redir('syncronize_submit', array('customer_id' => $this->vars['customer_id']));
			$this->display($tpl);
		}
		
		function syncronize_submit()
		{
			check_auth(array('customer_id' => $this->vars['customer_id']));
			$ret = $this->mk_redir('syncronize', array('customer_id'=>$this->vars['customer_id']));
			if($this->vars['cancel']) 
			{
				$ret = $this->mk_redir('manage_assets', array('customer_id' => $this->vars['customer_id']));
			}
		
			if($this->vars['syncronize'])
			{
				$sync_cat = $this->vars['categories']['sync'];
				foreach ($sync_cat as $cat_id)
				{
					Asset::syncronize($cat_id, $this->vars['customer_id']);
					$ret = $this->mk_redir('manage_assets', array('customer_id'=>$this->vars['customer_id']));
				}
			}
			return $ret;
		}
		
		/************************************************************************************
		* Contracts management functions
		*************************************************************************************/
		
		function manage_contracts()
		{
			check_auth(array('customer_id' => $this->vars['customer_id']));
			class_load('Customer');
			class_load('Contract');
			$tpl = "manage_contracts.tpl";
			
			//Extract the list of customers, eventually  restricting to the list assigned to this Customer
			$customers_filter = array('favourites_list' => $this->current_user->id);
			if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
			
			$customers = Customer::get_customers_list ($customers_filter);
			
			if($this->vars['customer_id'])
			{
				$customer = new Customer($this->vars['customer_id']);
			}
			elseif ($this->locked_customer->id)
			{
				$customer = new Customer($this->locked_customer->id);
			}
			
			if($customer->id)
			{
				//a valid customer was loaded, so load the needed informations
				$contracts_array = Contract::get_customer_contracts($customer->id);
				$contracts = array();
				foreach($contracts_array as $contr)
				{
					$contracts[] = new Contract($contr->id);
				}
				//mark the potential client for locking
				$_SESSION['potential_lock_customer_id'] = $customer->id;
			}
			$this->assign('customers', $customers);
			$this->assign('contracts', $contracts);
			$this->assign('customer', $customer);
			$this->assign('error_msg', error_msg()); 
			
			$this->set_form_redir('manage_contracts');
			$this->display($tpl);
		}
		
		/**
		 * displays a page for adding a new contract to the database
		 *
		 */
		function contract_add()
		{
			check_auth(array('customer_id'=>$this->vars['customer_id']));
			class_load('Customer');
			class_load('Contract');
			class_load('ContractType');
			class_load('Supplier');
			$tpl = "contract_add.tpl";
			$customer = new Customer($this->vars['customer_id']);
			if(!$customer->id) $this->mk_redir('manage_contracts');
			
			$customers_filter = array('favourites_list' => $this->current_user->id);
			if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
			$customers = Customer::get_customers_list ($customers_filter);
			$suppliers = Supplier::get_supplier_names();
			
			$contract_types = ContractType::get_types_array();
			$currencies = Contract::get_currecies('name');
			$currencies_symbols = Contract::get_currecies('symbol');
			
			
			$contract = new Contract();
			if(!empty_error_msg())
			{
				$contract->load_from_array(restore_form_data('contract_data', true, $contract_data));
				$type_contract = new ContractType($contract->contract_type);
			}
			
			//contract payment periods
			$payment_periods = $contract->get_payment_periods();
			
			/**
			 * variables to send to the template
			 */
			$this->assign('contract', $contract);
			$this->assign('currencies', $currencies);
			$this->assign('currencies_symbols', $currencies_symbols);
			$this->assign('contract_types', $contract_types);
			$this->assign('type_contract', $type_contract);
			$this->assign('customer', $customer);
			$this->assign('customers', $customers);
			$this->assign('suppliers', $suppliers);
			$this->assign('payment_periods', $payment_periods);
			$this->assign('error_msg', error_msg());
			
			$this->set_form_redir('contract_add_submit', array('customer_id'=>$this->vars['customer_id']));
			$this->display($tpl);
		}
		
		/**
		 * handles the form action from the contract add page
		 *
		 */
		function contract_add_submit()
		{
			check_auth(array('customer_id'=>$this->vars['customer_id']));
			class_load('Customer');
			class_load('Contract');
			$customer = new Customer($this->vars['customer_id']);
			if($customer->id) $ret = $this->mk_redir('manage_contracts');
			$ret = $this->mk_redir('manage_contracts', array('customer_id'=>$customer->id));
			if($this->vars['save'] && $customer->id)
			{
				$contract_data = $this->vars['contract'];
				$contract_data['start_date'] = js_strtotime($contract_data['start_date']); 
				$contract_data['end_date'] = js_strtotime($contract_data['end_date']);
				$contract = new Contract();
				$contract->load_from_array($contract_data);
				if($contract->is_valid_data())
				{
					//if we have valid data in the form, we can save this contract into the database
					$contract->add_new();	
				}
				else 
				{
					//we don't have valid data in the database, reload the form data and ask for valid data
					save_form_data($contract_data, 'contract_data');
					$ret = $this->mk_redir('contract_add', array('customer_id'=>$customer->id));
				}
			}
			return $ret;
		}
		
		/**
		 * displays the properites of the selected contract
		 *
		 */
		function contract_view()
		{
			check_auth(array('customer_id'=>$this->vars['customer_id'], 'contract_id'=>$this->vars['contract_id']));
			class_load('Customer');
			class_load('Contract');
			class_load('ContractType');
			class_load('Supplier');
			$tpl = "contract_view.tpl";
			$customer = new Customer($this->vars['customer_id']);
			if(!$customer->id) $this->mk_redir('manage_contracts');
			
			$contract = new Contract($this->vars['contract_id']);
			if(!$contract->id) $this->mk_redir('manage_contracts', array('customer_id'=>$customer->id));
			
			$customers_filter = array('favourites_list' => $this->current_user->id);
			if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
			
			$customers = Customer::get_customers_list ($customers_filter);
			
			$suppliers = Supplier::get_supplier_names();
			
			$contract_types = ContractType::get_types_array();
			
			
			//contract payment periods
			$payment_periods = $contract->get_payment_periods();
			
			//currency
			$currencies = Contract::get_currecies('name');
			$currencies_symbols = Contract::get_currecies('symbol');
			
			/**
			 * variables to send to the template
			 */
			$this->assign('contract', $contract);
			$this->assign('currencies', $currencies);
			$this->assign('currencies_symbols', $currencies_symbols);
			$this->assign('contract_types', $contract_types);
			$this->assign('type_contract', $contract->type);
			$this->assign('customer', $customer);
			$this->assign('customers', $customers);
			$this->assign('suppliers', $suppliers);
			$this->assign('payment_periods', $payment_periods);
			$this->assign('error_msg', error_msg());
			
			$this->set_form_redir('contract_view_submit', array('customer_id'=>$this->vars['customer_id'], 'contract_id'=>$contract->id));
			$this->display($tpl);	
		}
		
		/**
		 * handles the output of the contract view form
		 *
		 */
		function contract_view_submit()
		{
			check_auth(array('customer_id'=>$this->vars['customer_id'], 'contract_id'=>$this->vars['contract_id']));
			$ret = $this->mk_redir('manage_contracts');
			class_load('Customer');
			class_load('Contract');
			class_load('ContractType');
			$customer = new Customer($this->vars['customer_id']);
			if(!$customer->id) $ret = $this->mk_redir('manage_contracts');
			$contract = new Contract($this->vars['contract_id']);
			if(!$contract_id) $ret = $this->mk_redir('manage_contracts', array('customer_id'=>$customer->id));
			if($this->vars['save'])
			{
				$contract_data = $this->vars['contract'];
				$contract_data['start_date'] = js_strtotime($contract_data['start_date']); 
				$contract_data['end_date'] = js_strtotime($contract_data['end_date']);
				$contract = new Contract();
				$contract->load_from_array($contract_data);
				//debug($contract);
				if($contract->is_valid_data())
				{
					$contract->save_data();
				}
				$ret = $this->mk_redir('contract_view', array('customer_id'=>$customer->id, 'contract_id'=>$contract->id));
			}
			return $ret;
			
		}
		
		/********************************************************************************
		 * Contract types management functions
		 ********************************************************************************/
		
		/**
		 * Manage the contract types
		 * Displays a list with all the contract types, and gives the possibility to add a new type of contract
		 * also for an existing type the user has the possibility to edit the properties
		 *
		 */
		function manage_contract_types()
		{
			check_auth();
			class_load('ContractType');
			$tpl = 'kams/manage_contract_types.tpl';
			
			$contract_types = ContractType::get_types_list();
			//debug($contract_types);
			
			$this->assign('contract_types', $contract_types);
			$this->assign(error_msg, error_msg());
			$this->set_form_redir('manage_contract_types');
			$this->display($tpl);
		}
		
		function contract_type_add()
		{
			check_auth();
			$tpl = "contract_type_add.tpl";
			class_load('ContractType');
			$contract_type = new ContractType();
			if(!empty_error_msg()) 
			{
				$contract_type->load_from_array(restore_form_data('ctype_data', true, $ctype_data)); 
			}
			
			$this->assign('contract_type', $contract_type);
			$this->assign('error_msg', error_msg());
			
			$this->set_form_redir('contract_type_add_submit');
			$this->display($tpl);
			
		}
		function contract_type_add_submit()
		{
			check_auth();
			$ret = $this->mk_redir('manage_contract_types');
			class_load('ContractType');
			$contract_type = new ContractType();
			if($this->vars['save'])
			{
				$ctype_data = $this->vars['contract_type'];
				$ctype_data['quantity'] = ($ctype_data['quantity'] ? 1 : 0);
				$ctype_data['total_price'] = ($ctype_data['total_price'] ? 1 : 0);
				$ctype_data['recurring_payments'] = ($ctype_data['recurring_payments'] ? 1 : 0);
				$ctype_data['end_date'] = ($ctype_data['end_date'] ? 1 : 0);
				$ctype_data['vendor'] = ($ctype_data['vendor'] ? 1 : 0);
				$ctype_data['supplier'] = ($ctype_data['supplier'] ? 1 : 0);
				$ctype_data['is_warranty_contract'] = ($ctype_data['is_warranty_contract'] ? 1 : 0);
				$ctype_data['send_period_notifs'] = ($ctype_data['send_period_notifs'] ? 1 : 0);
				$ctype_data['send_expiration_notifs'] = ($ctype_data['send_expiration_notifs'] ? 1 : 0);
				$ctype_data['supports_renewals'] = ($ctype_data['supports_renewals'] ? 1 : 0);

				$contract_type->load_from_array($ctype_data);
				
				if($contract_type->is_valid_data())
				{
					$contract_type->add_new();
				}
				else 
				{
					save_form_data($ctype_data, 'ctype_data');	
				}
				
				$ret = $this->mk_redir('contract_type_add');
			}
			return $ret;
		}
		
		/**
		 * Displays a page that gives the user the possibility to edit the properites 
		 * of a contract type
		 *
		 */
		function contract_type_edit()
		{
			check_auth(array('type_id'=>$this->vars['type_id']));
			class_load('ContractType');
			$contract_type = new ContractType($this->vars['type_id']);
			if(!$contract_type->id) $this->mk_redir('manage_contract_types');
			
			$tpl = "contract_type_edit.tpl";
			$this->assign('contract_type', $contract_type);
			$this->assign('error_msg', error_msg());
			
			$this->set_form_redir('contract_type_edit_submit', array('type_id' => $contract_type->id));
			$this->display($tpl);
			
		}
		/**
		 * handles the form submit for the contract type edit
		 * possible outcome:
		 * 	checks if the data entered is valid and operates the modifications into the database
		 * 	or deletes a contract type from the database
		 *
		 * @return redirects to a page based on the action chosen by the user
		 */
		function contract_type_edit_submit()
		{
			check_auth();
			$ret = $this->mk_redir('manage_contract_types');
			class_load('ContractType');
			$contract_type = new ContractType($this->vars['type_id']);
			if($this->vars['delete'] && $contract_type->id)
			{
				$ret = $this->mk_redir('delete_contract_type', array('type_id' => $contract_type->id));
			}
			if($this->vars['save'] && $contract_type->id)
			{
				$ctype_data = $this->vars['contract_type'];
				$ctype_data['quantity'] = ($ctype_data['quantity'] ? 1 : 0);
				$ctype_data['total_price'] = ($ctype_data['total_price'] ? 1 : 0);
				$ctype_data['recurring_payments'] = ($ctype_data['recurring_payments'] ? 1 : 0);
				$ctype_data['end_date'] = ($ctype_data['end_date'] ? 1 : 0);
				$ctype_data['vendor'] = ($ctype_data['vendor'] ? 1 : 0);
				$ctype_data['supplier'] = ($ctype_data['supplier'] ? 1 : 0);
				$ctype_data['is_warranty_contract'] = ($ctype_data['is_warranty_contract'] ? 1 : 0);
				$ctype_data['send_period_notifs'] = ($ctype_data['send_period_notifs'] ? 1 : 0);
				$ctype_data['send_expiration_notifs'] = ($ctype_data['send_expiration_notifs'] ? 1 : 0);
				$ctype_data['supports_renewals'] = ($ctype_data['supports_renewals'] ? 1 : 0);

				$contract_type->load_from_array($ctype_data);
				
				if($contract_type->is_valid_data())
				{
					$contract_type->save_data();
				}
				$ret = $this->mk_redir('contract_type_edit', array('type_id'=>$contract_type->id));
			}
			return $ret;
		}
		
		/**
		 * display a page for delete confirmation
		 *
		 */
		function delete_contract_type()
		{
			check_auth(array('type_id'=> $this->vars['type_id']));
			class_load('ContractType');
			$contract_type = new ContractType($this->vars['type_id']);
			$tpl = "delete_contract_type.tpl";
			if(!$contract_type->id) $this->mk_redir('manage_contract_types');
			$this->assign('contract_type', $contract_type);
			$this->assign('error_msg', error_msg());
			
			$this->set_form_redir('delete_contract_type_submit', array('type_id'=>$contract_type->id));
			$this->display($tpl);
		}
		
		/**
		 * submits the deletion of a contract type
		 */
		function delete_contract_type_submit()
		{
			check_auth(array('type_id'=>$this->vars['type_id']));
			$ret = $this->mk_redir('contract_type_edit', array('type_id'=>$this->vars['type_id']));
			class_load('ContractType');
			$contract_type = new ContractType($this->vars['type_id']);
			if($this->vars['delete'] && $contract_type->id)
			{
				$contract_type->delete();
				$ret = $this->mk_redir('manage_contract_types');
			}
			return $ret;
		}
		
		
		
	}
?>
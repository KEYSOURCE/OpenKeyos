<?php

class_load('Customer');
class_load('MonitorItemAbstraction');
class_load('AD_Computer');
class_load('AD_User');
class_load('AD_Group');
class_load('AD_Printer');

class KermController extends PluginController{
    protected $plugin_name = "KERM";
    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    function manage_ad_computers ()
	{
		$tpl = 'manage_ad_computers.tpl';
		
		$extra_params = array ();
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;
		
		if (isset($this->vars['customer_id']))
		{
			$_SESSION['ad_customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['ad_customer_id'] = $this->locked_customer->id;
		}
		
		$filter = $_SESSION['manage_ad_computers']['filter'];
		// A single session var is used across AD pages for storing the customer
		$filter['customer_id'] = $_SESSION['ad_customer_id'];
		
		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			unset ($_SESSION['manage_computers']['customer_id']);		// Remove selected customer, in case user has no access to it
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_computers']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kerm customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('has_kerm' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		if ($filter['customer_id'])
		{
			$ad_computers = AD_Computer::get_ad_computers ($filter);
		}
		
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
		
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('ad_computers', $ad_computers);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('manage_ad_computers_submit', $extra_params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the filtering crtiteria */
	function manage_ad_computers_submit ()
	{
		check_auth ();
		$_SESSION['manage_ad_computers']['filter'] = $this->vars['filter'];
		// Use a single session var for storing the customer ID across all AD pages
		$_SESSION['ad_customer_id'] = $_SESSION['manage_ad_computers']['filter']['customer_id']; 
		return $this->mk_redir ('manage_ad_computers', array('do_filter' => 1));
	}
	
	
	/** Displays the details about an AD computer */
	function ad_computer_view ()
	{
		check_auth (array('computer_id' => $this->vars['computer_id']));
		class_load ('Computer');
		$tpl = 'ad_computer_view.tpl';
		
		$ad_computer = new AD_Computer ($this->vars['computer_id'], $this->vars['nrc']);
		if (!$ad_computer->computer_id) return $this->mk_redir ('manage_ad_computers');
		
		$computer = new Computer ($ad_computer->computer_id);
		$customer = new Customer ($computer->customer_id);
		$item = new ComputerItem ($computer->id, $ad_computer->item_id);
		$item_monitoring = new ComputerItem ($computer->id, $ad_computer->item_id_monitoring);
		for ($i=0; $i<count($item->val); $i++) if ($item->val[$i]->nrc == $ad_computer->nrc) $index_nrc = $i;

		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		$this->assign ('ad_computer', $ad_computer);
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('item', $item);
		$this->assign ('item_monitoring', $item_monitoring);
		$this->assign ('index_nrc', $index_nrc);
		$this->assign ('error_msg', $error_msg);
		$this->set_form_redir ('manage_ad_computers');
		
		$this->display ($tpl);
	}

		
	/** Displays the page with the AD users and groups */
	function manage_ad_users ()
	{
		$tpl = 'manage_ad_users.tpl';
		
		$extra_params = array ();
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;
		
		if (isset($this->vars['customer_id']))
		{
			$_SESSION['ad_customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['ad_customer_id'] = $this->locked_customer->id;
		}
		
		$filter = $_SESSION['manage_ad_users']['filter'];
		// A single session var is used across AD pages for storing the customer
		$filter['customer_id'] = $_SESSION['ad_customer_id'];
		
		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			unset ($_SESSION['manage_computers']['customer_id']);		// Remove selected customer, in case user has no access to it
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_computers']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kerm customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('has_kerm' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		if ($filter['customer_id'])
		{
			$ad_users = AD_User::get_ad_users ($filter);
			$ad_groups = AD_Group::get_ad_groups ($filter);
		}
		
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
		
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('ad_users', $ad_users);
		$this->assign ('ad_groups', $ad_groups);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('manage_ad_users_submit', $extra_params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the filtering crtiteria */
	function manage_ad_users_submit ()
	{
		$_SESSION['manage_ad_users']['filter'] = $this->vars['filter'];
		// Use a single session var for storing the customer ID across all AD pages
		$_SESSION['ad_customer_id'] = $_SESSION['manage_ad_users']['filter']['customer_id']; 
		
		return $this->mk_redir ('manage_ad_users', array('do_filter' => 1));
	}
	
	
	/** Displays the details about an AD user */
	function ad_user_view ()
	{
		check_auth (array('computer_id' => $this->vars['computer_id']));
		class_load ('Computer');
		$tpl = 'ad_user_view.tpl';
		
		$ad_user = new AD_User ($this->vars['computer_id'], $this->vars['nrc']);
		if (!$ad_user->computer_id) return $this->mk_redir ('manage_ad_users');
		
		$itemdef = new MonitorItem ($ad_user->item_id);
		$computer = new Computer ($ad_user->computer_id);
		$customer = new Customer ($computer->customer_id);
		$item = new ComputerItem ($computer->id, $ad_user->item_id);
		$item_info = new ComputerItem ($computer->id, $ad_user->item_id_info);
		$item_monitoring = new ComputerItem ($computer->id, $ad_user->item_id_monitoring);
		
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		$index_nrc = 0;  // The index in the ComputerItem->val where our ad_user is actually stored
		$index_nrc_monitoring; 
		for ($i=0; $i<count($item->val); $i++) if ($item->val[$i]->nrc == $ad_user->nrc) $index_nrc = $i;
		// 214: The field_id for item 1045 storing the account name
		for ($i=0; $i<count($item_info->val); $i++) if ($item_info->val[$i]->value[214] == $ad_user->sam_account_name) $index_nrc_info= $i;
		for ($i=0; $i<count($item_monitoring->val); $i++) if ($item_monitoring->val[$i]->nrc == $ad_user->nrc) $index_nrc_monitoring = $i;
		
		$this->assign ('ad_user', $ad_user);
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('item', $item);
		$this->assign ('item_info', $item_info);
		$this->assign ('item_monitoring', $item_monitoring);
		$this->assign ('index_nrc', $index_nrc);
		$this->assign ('index_nrc_info', $index_nrc_info);
		$this->assign ('index_nrc_monitoring', $index_nrc_monitoring);
		$this->assign ('itemdef', $itemdef);
		$this->assign ('returl', $this->vars['returl']);
		$this->assign ('error_msg', $error_msg);
		$this->set_form_redir ('manage_ad_users');
	
		$this->display ($tpl);
	}

	
	/** Displays the details about an AD group */
	function ad_group_view ()
	{
		check_auth (array('computer_id' => $this->vars['computer_id']));
		class_load ('Computer');
		$tpl = 'ad_group_view.tpl';
		
		$ad_group = new AD_Group ($this->vars['computer_id'], $this->vars['nrc']);
		if (!$ad_group->computer_id) return $this->mk_redir ('manage_ad_users');
		
		$itemdef = new MonitorItem ($ad_group->item_id);
		$computer = new Computer ($ad_group->computer_id);
		$customer = new Customer ($computer->customer_id);
		$item = new ComputerItem ($computer->id, $ad_group->item_id);
		for ($i=0; $i<count($item->val); $i++) if ($item->val[$i]->nrc == $ad_group->nrc) $index_nrc = $i;

		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		$this->assign ('ad_group', $ad_group);
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('item', $item);
		$this->assign ('index_nrc', $index_nrc);
		$this->assign ('itemdef', $itemdef);
		$this->assign ('error_msg', $error_msg);
		$this->set_form_redir ('manage_ad_users');
	
		$this->display ($tpl);
	}
	
	
	/** Displays the page with the AD printers */
	function manage_ad_printers ()
	{
		class_load ('Computer');
		$tpl = 'manage_ad_printers.tpl';
		
		$extra_params = array ();
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;
		
		if (isset($this->vars['customer_id']))
		{
			$_SESSION['ad_customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['ad_customer_id'] = $this->locked_customer->id;
		}
		
		$filter = $_SESSION['manage_ad_printers']['filter'];
		// A single session var is used across AD pages for storing the customer
		$filter['customer_id'] = $_SESSION['ad_customer_id'];
		
		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			unset ($_SESSION['manage_computers']['customer_id']);		// Remove selected customer, in case user has no access to it
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_computers']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kerm customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('has_kerm' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		if ($filter['customer_id'])
		{
			$ad_printers = AD_Printer::get_ad_printers ($filter);
			
			// Load also the orphan AD printers, which are candidates for deletion or removal
			$orphan_printers = AD_Printer::get_orphan_ad_printers ($filter['customer_id']);
		}
		
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
		
		// Build a list with all the AD servers involved. Also load the associated customer locations
		$ad_servers_list = array ();
		for ($i=0; $i<count($ad_printers); $i++)
		{
			$computer_id = $ad_printers[$i]->computer_id;
			if (!$ad_servers_list[$computer_id])
			{
				$server = new Computer ($computer_id);
				$ad_servers_list[$computer_id] = $server->netbios_name;
			}
			$ad_printers[$i]->load_location ();
		}
		$computers_list = Computer::get_computers_list (array('customer_id' => $filter['customer_id']));
		
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('ad_printers', $ad_printers);
		$this->assign ('orphan_printers', $orphan_printers);
		$this->assign ('ad_servers_list', $ad_servers_list);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('manage_ad_printers_submit', $extra_params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the filtering crtiteria */
	function manage_ad_printers_submit ()
	{
		$_SESSION['manage_ad_printers']['filter'] = $this->vars['filter'];
		// Use a single session var for storing the customer ID across all AD pages
		$_SESSION['ad_customer_id'] = $_SESSION['manage_ad_printers']['filter']['customer_id']; 
		
		return $this->mk_redir ('manage_ad_printers', array('do_filter' => 1));
	}
	
	
	/** Displays the details about an AD printer */
	function ad_printer_view ()
	{
		check_auth (array('computer_id' => $this->vars['computer_id']));
		class_load ('Computer');
		class_load ('Supplier');
		class_load ('MonitorProfilePeriph');
		class_load ('Discovery');
		class_load ('DiscoverySettingDetail');
		$tpl = 'ad_printer_view.tpl';
		
		if (isset($this->vars['computer_id']) and isset($this->vars['nrc'])) $ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
		elseif ($this->vars['id']) $ad_printer = AD_Printer::get_by_id ($this->vars['id']);
		
		if (!$ad_printer->computer_id) return $this->mk_redir ('manage_ad_printers');
		
		$itemdef = new MonitorItem ($ad_printer->item_id);
		$computer = new Computer ($ad_printer->computer_id);
		$customer = new Customer ($computer->customer_id);
		$item = new ComputerItem ($computer->id, $ad_printer->item_id);
		for ($i=0; $i<count($item->val); $i++) if ($item->val[$i]->nrc == $ad_printer->nrc) $index_nrc = $i;
		
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		$service_packages_list = SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true));
		$service_levels_list = ServiceLevel::get_service_levels_list ();
		$computers_list = Computer::get_computers_list (array('customer_id' => $computer->customer->id));
		$ad_printer->load_location ();
		
		// Load the monitoring profile, if any is set
		if ($ad_printer->profile_id) $monitor_profile = new MonitorProfilePeriph ($ad_printer->profile_id);
		
		// Load any related notifications
		$notifications = Notification::get_notifications (array('object_class' => NOTIF_OBJ_CLASS_AD_PRINTER, 'object_id'=>$ad_printer->id));
		
		// Load any matched devices from networks discoveries
		$discoveries = Discovery::get_matches_for_ad_printer ($ad_printer->id);
		$disc_details = array ();
		foreach ($discoveries as $discovery)
		{
			if (!isset($disc_details[$discovery->detail_id])) $disc_details[$discovery->detail_id] = new DiscoverySettingDetail ($discovery->detail_id);
		}
		
		$this->assign ('ad_printer', $ad_printer);
		$this->assign ('monitor_profile', $monitor_profile);
		$this->assign ('notifications', $notifications);
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('item', $item);
		$this->assign ('index_nrc', $index_nrc);
		$this->assign ('itemdef', $itemdef);
		$this->assign ('service_packages_list', $service_packages_list);
		$this->assign ('service_levels_list', $service_levels_list);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('discoveries', $discoveries);
		$this->assign ('disc_details', $disc_details);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('manage_ad_printers');
	
		$this->display ($tpl);
	}
	
	
	/** Deletes permanently an orphan AD Printer */
	function ad_printer_delete ()
	{
		check_auth ();
		if ($this->vars['id']) AD_Printer::delete_orphan_printer ($this->vars['id']);
		return $this->mk_redir ('manage_ad_printers', array('do_filter' => 1));
	}
	
	/** Displays the page for defining the SNMP monitoring settings for this AD Printer */
	function ad_printer_edit_snmp ()
	{
		check_auth ();
		class_load ('Computer');
		class_load ('MonitorProfilePeriph');
		$tpl = 'ad_printer_edit_snmp.tpl';
		
		$ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
		if (!$ad_printer->asset_no) return $this->mk_redir ('manage_ad_printers');
		if (!empty_error_msg()) $ad_printer->load_from_array(restore_form_data ('ad_printer_edit_snmp', false, $data));
		
		$customer = new Customer ($ad_printer->customer_id);
		$profiles_list = MonitorProfilePeriph::get_profiles_list ();
		
		// Get the list of available computers for this customer
		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id));
		// Get the list of computers already doing SNMP monitoring
		$computers_list_snmp = Computer::get_list_monitored_peripherals (array('customer_id' => $customer->id));
		
		$params = $this->set_carry_fields (array('computer_id','nrc', 'returl'));
		$this->assign ('ad_printer', $ad_printer);
		$this->assign ('customer', $customer);
		$this->assign ('profiles_list', $profiles_list);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('computers_list_snmp', $computers_list_snmp);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('ad_printer_edit_snmp_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Saves the SNMP monitoring settings for a peripheral */
	function ad_printer_edit_snmp_submit ()
	{
		check_auth ();
		$params = $this->set_carry_fields (array('computer_id', 'nrc', 'retur'));
		$ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
		$ret = $this->mk_redir ('ad_printer_view', $params);
		
		if ($this->vars['save'] and $ad_printer->computer_id)
		{
			$data = $this->vars['ad_printer'];
			$ad_printer->load_from_array ($data);
			
			if ($ad_printer->is_valid_data ()) $ad_printer->save_data ();
			else save_form_data ($data, 'ad_printer_edit_snmp');
			$ret = $this->mk_redir ('ad_printer_edit_snmp', $params);
		}
		
		return $ret;
	}
	
	/** Set the location for an AD printer */
	function ad_printer_location ()
	{
		class_load ('Location');
		$tpl = 'ad_printer_location.tpl';
		$params = array ();
		if ($this->vars['computer_id'] and isset($this->vars['nrc']))
		{
			// The AD printer is identified by the reporting computer
			$ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
		}
		elseif ($this->vars['canonical_name'])
		{
			// The AD printer is identified by the canonical name
			$ad_printer = new AD_Printer ();
			$warranty = AD_Printer::get_warranty_by_canonical_name ($this->vars['canonical_name']);
			$ad_printer->set_warranty_data ($warranty);
		}
		check_auth (array('customer_id' => $ad_printer->customer_id));
		
		$locations_list = Location::get_locations_list (array('customer_id'=>$ad_printer->customer_id, 'indent'=>true));
		$location = $ad_printer->get_location ();
		
		$params = $this->set_carry_fields (array('computer_id', 'nrc', 'canonical_name', 'ret', 'returl'));
		
		$this->assign ('ad_printer', $ad_printer);
		$this->assign ('location', $location);
		$this->assign ('locations_list', $locations_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('ad_printer_location_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the location for an AD Printer */
	function ad_printer_location_submit ()
	{
		if ($this->vars['computer_id'] and isset($this->vars['nrc']))
		{
			// The AD printer is identified by the reporting computer
			$ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
		}
		elseif ($this->vars['canonical_name'])
		{
			// The AD printer is identified by the canonical name
			$ad_printer = new AD_Printer ();
			$warranty = AD_Printer::get_warranty_by_canonical_name ($this->vars['canonical_name']);
			$ad_printer->set_warranty_data ($warranty);
		}
		
		$params = $this->set_carry_fields (array('computer_id', 'nrc', 'canonical_name'));
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('ad_printer_view', $params));
		
		if ($this->vars['save'] and $ad_printer->computer_id)
		{
			$ad_printer->set_location ($this->vars['location_id']);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing the "Managing since" date for an AD Printer */
	function ad_printer_date ()
	{
		$tpl = 'ad_printer_date.tpl';
		$params = array ();
		if ($this->vars['computer_id'] and isset($this->vars['nrc']))
		{
			// The AD printer is identified by the reporting computer
			$ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
		} 
		check_auth (array('customer_id' => $ad_printer->customer_id));
		
		$params = $this->set_carry_fields (array('computer_id', 'nrc', 'canonical_name', 'ret', 'returl'));
		
		$this->assign ('ad_printer', $ad_printer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('ad_printer_date_submit', $params);
		
		$this->display ($tpl);
	}
	
	function ad_printer_date_submit ()
	{
		$ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
		$params = $this->set_carry_fields (array('computer_id', 'nrc', 'canonical_name', 'ret', 'returl'));
		
		if ($this->vars['save'] and $ad_printer->computer_id)
		{
			$ad_printer->set_date_created (js_strtotime($this->vars['date_created']));
		}
		
		return $this->mk_redir ('ad_printer_view', $params);
	}
	
	/** Displays the page for editing the warranty information for an AD Printer */
	function ad_printer_warranty_edit ()
	{
		class_load ('Supplier');
		$tpl = 'ad_printer_warranty_edit.tpl';
		$params = array ();
		if ($this->vars['computer_id'] and isset($this->vars['nrc']))
		{
			// The AD printer is identified by the reporting computer
			$ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
			$params = array ('computer_id' => $ad_printer->computer_id, 'nrc' => $ad_printer->nrc);
		}
		elseif ($this->vars['canonical_name'])
		{
			// The AD printer is identified by the canonical name
			$ad_printer = new AD_Printer ();
			$warranty = AD_Printer::get_warranty_by_canonical_name ($this->vars['canonical_name']);
			$ad_printer->set_warranty_data ($warranty);
			$params = array ('canonical_name' => $ad_printer->canonical_name);
		}
		if (isset($this->vars['ret'])) $params['ret'] = $this->vars['ret'];
		
		check_auth (array ('customer_id' => $ad_printer->customer_id));
		
		$this->assign ('ad_printer', $ad_printer);
		$this->assign ('service_packages_list', SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true)));
		$this->assign ('service_levels_list', ServiceLevel::get_service_levels_list ());
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('ad_printer_warranty_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the warranty information for the AD Printer */
	function ad_printer_warranty_edit_submit ()
	{
		if ($this->vars['computer_id'] and isset($this->vars['nrc']))
		{
			// The AD printer is identified by the reporting computer
			$ad_printer = new AD_Printer ($this->vars['computer_id'], $this->vars['nrc']);
			$params = array ('computer_id' => $ad_printer->computer_id, 'nrc' => $ad_printer->nrc);
		}
		elseif ($this->vars['canonical_name'])
		{
			// The AD printer is identified by the canonical name
			$ad_printer = new AD_Printer ();
			$warranty = AD_Printer::get_warranty_by_canonical_name ($this->vars['canonical_name']);
			$ad_printer->set_warranty_data ($warranty);
			$params = array ('canonical_name' => $ad_printer->canonical_name);
		}
		if (isset($this->vars['ret'])) $params['ret'] = $this->vars['ret'];
		
		check_auth (array ('customer_id' => $ad_printer->customer_id));
		
		if ($this->vars['save'])
		{
			$warranty = $this->vars;
			$warranty['warranty_starts'] = date_fields_to_time ($warranty['warranty_starts']);
			$warranty['warranty_ends'] = date_fields_to_time ($warranty['warranty_ends']);
			$ad_printer->set_warranty_data ($warranty);
			
			$ad_printer->save_warranty ();
			
			$ret = $this->mk_redir ('ad_printer_warranty_edit', $params);
		}
		else
		{
			if ($this->vars['ret'] == 'manage_warranties')
				$ret = $this->mk_redir ('manage_warranties', array ('customer_id' => $ad_printer->customer_id), 'warranties');
			else
				$ret = $this->mk_redir ('ad_printer_view', array ('computer_id' => $ad_printer->computer_id, 'nrc' => $ad_printer->nrc));
		}
		
		return $ret;
	}
	
	
	/** What computers have been used for login */
	
	
	/** Displays the page showing on what computers each AD user has logged on*/
	function logon_computers ()
	{
		class_load ('Computer');
		$tpl = 'logon_computers.tpl';
		
		if (isset($this->vars['customer_id']))
		{
			$_SESSION['ad_customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['ad_customer_id'] = $this->locked_customer->id;
		}
		
		$filter = $_SESSION['logon_computers']['filter'];
		// A single session var is used across AD pages for storing the customer
		$filter['customer_id'] = $_SESSION['ad_customer_id'];
		if (!$filter['months']) $filter['months'] = 1;
		if (!$filter['view_by']) $filter['view_by'] = 'user';
		
		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			unset ($_SESSION['logon_computers']['customer_id']);		// Remove selected customer, in case user has no access to it
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['logon_computers']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kerm customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('has_kerm' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		$computers_list = array ();
		$used_computers = array ();
		$used_computers_count = array ();
		$computers_users = array ();
		$computers_users_count = array ();
		if (intval($filter['customer_id']))
		{
			// Get the existing AD users for this customer
			$ad_users = AD_User::get_ad_users ($filter);
			
			// Load the list of computers for this customer
			$computers_list = Computer::get_computers_list (array('customer_id' => $filter['customer_id']));
			
			// Load the computers used by each user
			foreach ($ad_users as $idx => $ad_user)
			{
				$used_computers[$idx] = $ad_user->get_used_computers ($filter['months']);
				// To help with displaying the page, store the number of computers used by each user
				$used_computers_count[$idx] = count($used_computers[$idx]);
			}
			
			if ($filter['view_by'] != 'user')
			{
				// The results are shown by computers, not by users.
				// These are stored in $computers_users, which is an associative array, with the
				// keys being computer IDs and the values being another associative array, the
				// keys being corresponding users from $ad_users and the values being the last
				// timestamp when the user was detected as logged on to that computer.
				foreach ($computers_list as $computer_id => $computer_name)
				{
					$computers_users[$computer_id] = array ();
					foreach ($used_computers as $idx=>$user_computers)
					{
						foreach ($user_computers as $comp_id=>$last_access)
						{
							if (!isset($computers_users[$comp_id][$idx]) or $computers_users[$comp_id][$idx] < $used_computers[$idx][$comp_id])
							{
								$computers_users[$comp_id][$idx] = $used_computers[$idx][$comp_id];
							}
						}
					}
				}
				
				// At this point, the users for each computer are sorted by name. Resort them by last recorded access
				foreach ($computers_users as $comp_id => $comp_users) arsort ($computers_users[$comp_id]);
				
				// To help with displaying the page, store the number of users for each computer
				foreach ($computers_users as $computer_id => $comp_users)
				{
					$computers_users_count[$computer_id] = count($comp_users);
				}
			}
		}
		
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
		
		$params = $this->set_carry_fields (array('do_filter'));
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('ad_users', $ad_users);
		$this->assign ('used_computers', $used_computers);
		$this->assign ('computers_users', $computers_users);
		$this->assign ('used_computers_count', $used_computers_count);
		$this->assign ('computers_users_count', $computers_users_count);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('logon_computers_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the filtering crtiteria */
	function logon_computers_submit ()
	{
		$_SESSION['logon_computers']['filter'] = $this->vars['filter'];
		// Use a single session var for storing the customer ID across all AD pages
		$_SESSION['ad_customer_id'] = $_SESSION['logon_computers']['filter']['customer_id']; 
		
		return $this->mk_redir ('logon_computers', array('do_filter' => 1));
	}
	
	function customer_added_users()
	{
		class_load("KermADUser");
		class_load("KermADGroup");
		check_auth();
		$tpl = "customer_added_users.tpl";
		$filter = $_SESSION['customer_added_users']['filter'];
		if($filter['customers'] == -1) unset($filter['customers']);
		else if(isset($filter['customers'])) $filter['customers'] = array($filter['customers']);
		
		if (!isset ($filter['order_by'])) $filter['order_by'] = 'status';
		if (!isset ($filter['order_dir'])) $filter['order_dir'] = 'DESC';
		if (!isset ($filter['start'])) $filter['start'] = 0;
		if (!isset ($filter['limit'])) $filter['limit'] = 10;
		if (!isset ($filter['status'])) $filter['status'] = -1;
		
		$customers = Customer::get_customers_list();
		$users = KermADUser::get_users_list($filter);
		
		if (count($users) < $filter['start'])
		{
			$filter['start'] = 0;
			$_SESSION['customer_added_users']['filter']['start'] = 0;
			$users = KermADUser::get_users_list($filter);
		}
		$pages = make_paging ($filter['limit'], count($users));
		
		$this->assign('users', $users);
		$this->assign('pages', $pages);
		$this->assign('tot_users', count($users));
		$this->assign('USERS_STATUSES', $GLOBALS['CUSTOMER_KERM_USERS_STATUSES']);
		$this->assign('filter', $filter);
		$this->assign('customers', $customers);
		$this->assign("error_msg", error_msg());
		$this->assign("sort_url", $this->mk_redir("customer_added_users_submit"));
		$this->set_form_redir("customer_added_users_submit");
		$this->display($tpl);
		
		
	}
	function customer_added_users_submit()
	{
		check_auth();
		class_load("KermADUser");
		if ($this->vars['order_by'] and $this->vars['order_dir'])
		{
			$_SESSION['customer_added_users']['filter']['order_by'] = $this->vars['order_by'];
			$_SESSION['customer_added_users']['filter']['order_dir'] = $this->vars['order_dir'];
		}
		else
		{
			if ($this->vars['go'] == 'prev' or $this->vars['go'] == 'next')
			{
				$this->vars['filter']['start']+= $this->vars['filter']['limit'] * ($this->vars['go'] == 'prev' ? -1 : 1);
			}
			
			if (is_array($_SESSION['customer_added_users']['filter']))
			{
				$_SESSION['customer_added_users']['filter'] = array_merge($_SESSION['customer_added_users']['filter'], $this->vars['filter']);
			}
			else
			{
				$_SESSION['customer_added_users']['filter'] = $this->vars['filter'];
			}
		}
		if($this->vars['approve'])
		{
			$sel_users = $this->vars['chk_approve'];
			foreach ($sel_users as $usr)
			{
				$ad_usr = new KermADUser($usr);
				$ad_usr->status = CKERM_STATUS_APPROVED;
				$ad_usr->save_data();
			}
		}
		
		return $this->mk_redir('customer_added_users');
	}
	function modify_user()
	{
		class_load('KermADUser');
		class_load('KermADGroup');
		check_auth(array('id'=>$this->vars['id']));
		$tpl="modify_user.tpl";
		
		$aduser = new KermADUser($this->vars['id']);
		if(!$aduser->id) return $this->mk_redir('customer_added_users');

		if($_SESSION['kerm_modify_user']['aduser'])
			$aduser->load_from_array($_SESSION['kerm_modify_user']['aduser']);
		$customers = Customer::get_customers_list();
		$groups_list = KermADGroup::get_groups_list($aduser->customer_id);
		
		$domains = $aduser->get_available_domains();
		
		$this->assign('domains', $domains);
		$this->assign('customers', $customers);
		$this->assign('groups_list', $groups_list);
		$this->assign('aduser', $aduser);
		$this->assign('error_msg', error_msg());
		$this->assign('USERS_STATUSES', $GLOBALS['CUSTOMER_KERM_USERS_STATUSES']);
		$this->set_form_redir('modify_user_submit');
		$this->display($tpl);
		
	}
	function modify_user_submit()
	{
		class_load('KermADUser');
		class_load('KermADGroup');
		unset($_SESSION['kerm_modify_user']);
		$_SESSION['kerm_modify_user']['aduser'] = $this->vars['aduser'];
		if($this->vars['cancel'])
		{			
			unset($_SESSION['kerm_modify_user']);
			return $this->mk_redir('customer_added_users');
		}
		if($this->vars['save'])
		{
			$ad_usr = new KermADUser($this->vars['id']);
			if($ad_usr->id)
			{
				$aduser = $this->vars['aduser'];
				$ad_usr->load_from_array($aduser);
				$ad_grp = new KermADGroup($aduser['GroupName']);
				$ad_usr->GroupName = $ad_grp->name;
				$ad_usr->Email.=$aduser['Domain'];
				if($ad_usr->is_valid_data())
				{
					$ad_usr->save_data();
					unset($_SESSION['kerm_modify_user']);
				}
			}
			else 
			{
				unset($_SESSION['kerm_modify_user']);
				return $this->mk_redir('customer_added_users');
			}
		}
		return $this->mk_redir('modify_user', array('id'=>$this->vars['id']));
	}
	
	function add_ad_user()
	{
		class_load("KermADUser");
		class_load("KermADGroup");
		check_auth();
		$tpl = "add_ad_user.tpl";
		
		$customers = Customer::get_customers_list();
		$aduser = array();
		if($_SESSION['add_ad_user']['aduser']) $aduser = $_SESSION['add_ad_user']['aduser'];
		if(!isset($aduser['customer_id'])) $aduser['customer_id']=0;
		$groups_list = KermADGroup::get_groups_list($aduser['customer_id']);
		$domains = KermADUser::get_available_domains($aduser['customer_id']);
		
		$this->assign('domains', $domains);
		$this->assign('aduser', $aduser);
		$this->assign('groups_list', $groups_list);
		$this->assign('customers', $customers);
		$this->assign('error_msg', error_msg());
		$this->assign('USERS_STATUSES', $GLOBALS['CUSTOMER_KERM_USERS_STATUSES']);
		$this->set_form_redir('add_ad_user_submit');
		$this->display($tpl);
	}
	
	function add_ad_user_submit()
	{
		class_load("KermADUser");
		class_load("KermADGroup");
		check_auth();
		if(isset($_SESSION['add_ad_user']['aduser'])) unset($_SESSION['add_ad_user']['aduser']);
		$ret = $this->mk_redir('add_ad_user');
		if($this->vars['cancel'])
		{
			$ret = $this->mk_redir('customer_added_users');
		}
		$_SESSION['add_ad_user']['aduser'] = $this->vars['aduser'];
		if($this->vars['save'])
		{
			$ad_usr = new KermADUser();

			$aduser = $this->vars['aduser'];
			$ad_usr->load_from_array($aduser);
			$ad_grp = new KermADGroup($aduser['GroupName']);
			$ad_usr->GroupName = $ad_grp->name;
			$ad_usr->Email.=$aduser['Domain'];
			//debug($ad_usr);
			if($ad_usr->is_valid_data())
			{
				$ad_usr->save_data();
				unset($_SESSION['add_ad_user']);
			}							
			$ret = $this->mk_redir('add_ad_user');
		}
		return $ret;
	}
}
?>

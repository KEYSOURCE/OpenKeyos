<?php

class_load ('Computer');
class_load ('Peripheral');
class_load ('AD_Printer');
class_load ('Customer');
class_load ('Discovery');
class_load ('DiscoverySetting');
class_load ('ComputerPassword');
class_load ('SnmpSysobjid');

/**
* Class for handling the display of the pages related to network discoveries
*
*/
class DiscoveryDisplay extends BaseDisplay
{
	function DiscoveryDisplay ()
	{
		parent::BaseDisplay ();
	}
	
	/****************************************************************/
	/* Management of network discoveries settings			*/
	/****************************************************************/
	
	/** Displays the page with the current discoveries settings for customers */
	function manage_discoveries_settings ()
	{
		check_auth ();
		$tpl = 'discovery/manage_discoveries_settings.html';
		
		if (isset($this->vars['customer_id'])) $_SESSION['manage_discoveries_settings']['customer_id'] = $this->vars['customer_id'];
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_discoveries_settings']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_discoveries_settings'];
		
		$settings = DiscoverySetting::get_customers_settings ($filter['customer_id']);
		
		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		$params = $this->set_carry_fields (array('do_filter'));
		$this->assign ('settings', $settings);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('manage_discoveries_settings_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the filtering criteria for the discoveries management page */
	function manage_discoveries_settings_submit ()
	{
		check_auth ();
		$_SESSION['manage_discoveries_settings'] = $this->vars['filter'];
		return $this->mk_redir ('manage_discoveries_settings', array('do_filter' => 1));
	}
	
	/** Displays the page for editing the discovery settings for a customer */
	function discovery_edit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_discoveries_settings');
		check_auth (array('customer_id' => $customer->id));
		$tpl = 'discovery/discovery_edit.html';
		
		$setting = DiscoverySetting::get_by_customer ($customer->id);
		$networks = ComputerReporting::get_customer_networks ($customer->id);
		
		$params = $this->set_carry_fields (array('customer_id'));
		$this->assign ('setting', $setting);
		$this->assign ('networks', $networks);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('discovery_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Saves a discovery setting for a customer */
	function discovery_edit_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		$params = $this->set_carry_fields (array('customer_id'));
		$ret = $this->mk_redir ('manage_discoveries_settings');
		
		if ($this->vars['save'] and $customer->id)
		{
			$setting = DiscoverySetting::get_by_customer ($customer->id);
			$setting->load_from_array ($this->vars['setting']);
			
			if ($setting->is_valid_data ()) $setting->save_data ();
			$ret = $this->mk_redir ('discovery_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for adding a new discovery detail for a customer */
	function discovery_detail_add ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_discoveries_settings');
		check_auth (array('customer_id' => $customer->id));
		$tpl = 'discovery/discovery_detail_add.html';
		
		$setting = DiscoverySetting::get_by_customer ($customer->id);
		$detail = new DiscoverySettingDetail ();
		if (!empty_error_msg()) $detail->load_from_array(restore_form_data ('discovery_detail_add', false, $data));
		$detail->customer_id = $customer->id;
		
		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'order_by' => 'type', 'append_id' => true));
		$networks = ComputerReporting::get_customer_networks ($customer->id, $computers_list);
		$passwords = ComputerPassword::get_passwords (array('customer_id' => $customer->id));
		
		$param = $this->set_carry_fields (array('customer_id'));
		$this->assign ('setting', $setting);
		$this->assign ('detail', $detail);
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('networks', $networks);
		$this->assign ('passwords', $passwords);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('discovery_detail_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Adds a new discovery detail to discovery settings */
	function discovery_detail_add_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array('customer_id' => $customer->id));
		$params = $this->set_carry_fields (array('customer_id'));
		$ret = $this->mk_redir ('discovery_edit', $params);
		
		if ($this->vars['save'] and $customer->id)
		{
			$data = $this->vars['detail'];
			$setting = DiscoverySetting::get_by_customer ($customer->id);
			$detail = new DiscoverySettingDetail ();
			$detail->load_from_array ($data);
			$detail->customer_id = $customer->id;
			
			if ($detail->is_valid_data ())
			{
				$detail->save_data ();
				$params['id'] = $detail->id;
				$ret = $this->mk_redir ('discovery_detail_edit', $params);
			}
			else
			{
				save_form_data ($data, 'discovery_detail_add');
				$ret = $this->mk_redir ('discovery_detail_add', $params);
			}
			
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a discovery detail for a customer */
	function discovery_detail_edit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		$detail = new DiscoverySettingDetail ($this->vars['id']);
		if (!$customer->id or !$detail->id) return $this->mk_redir ('manage_discoveries_settings');
		check_auth (array('customer_id' => $customer->id));
		$tpl = 'discovery/discovery_detail_edit.html';
		
		$setting = DiscoverySetting::get_by_customer ($customer->id);
		
		if (!empty_error_msg()) $detail->load_from_array(restore_form_data ('discovery_detail_edit', false, $data));
		$detail->customer_id = $customer->id;
		
		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'order_by' => 'type', 'append_id' => true));
		$networks = ComputerReporting::get_customer_networks ($customer->id, $computers_list);
		$passwords = ComputerPassword::get_passwords (array('customer_id' => $customer->id));
		
		$param = $this->set_carry_fields (array('id', 'customer_id'));
		$this->assign ('setting', $setting);
		$this->assign ('detail', $detail);
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('networks', $networks);
		$this->assign ('passwords', $passwords);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('discovery_detail_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Adds a new discovery detail to discovery settings */
	function discovery_detail_edit_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array('customer_id' => $customer->id));
		$detail = new DiscoverySettingDetail ($this->vars['id']);
		$params = $this->set_carry_fields (array('id', 'customer_id'));
		$ret = $this->mk_redir ('discovery_edit', $params);
		
		if ($this->vars['save'] and $customer->id and $detail->id)
		{
			$data = $this->vars['detail'];
			$setting = DiscoverySetting::get_by_customer ($customer->id);
			$detail->load_from_array ($data);
			$detail->customer_id = $customer->id;
			
			if ($detail->is_valid_data ()) $detail->save_data ();
			else save_form_data ($data, 'discovery_detail_edit');
			$ret = $this->mk_redir ('discovery_detail_edit', $params);
			
		}
		
		return $ret;
	}
	
	
	/** Deletes a discovery detail */
	function discovery_detail_delete ()
	{
		$detail = new DiscoverySettingDetail ($this->vars['id']);
		check_auth (array('customer_id' => $detail->customer_id));
		$ret = $this->mk_redir ('discovery_edit', array('customer_id' => $detail->customer_id));
		
		if ($detail->id) $detail->delete ();
		
		return $ret;
	}
	
	/** Initiates a manual request for a discovery */
	function discovery_request_make ()
	{
		check_auth ();
		$detail = new DiscoverySettingDetail ($this->vars['detail_id']);
		$ret = $this->mk_redir ('discovery_edit', array('customer_id' => $detail->customer_id));
		
		if ($detail->id) $detail->request_make ();
		
		return $ret;
	}
	
	/** Cancels a manual request for a discovery */
	function discovery_request_cancel ()
	{
		check_auth ();
		$detail = new DiscoverySettingDetail ($this->vars['detail_id']);
		$ret = $this->mk_redir ('discovery_edit', array('customer_id' => $detail->customer_id));
		
		if ($detail->id) $detail->request_cancel ();
		
		return $ret;
	}
	
	/****************************************************************/
	/* Management of SNMP system objects IDS			*/
	/****************************************************************/
	
	/** Displays the page for managing SNMP system objects IDs */
	function manage_snmp_sysobjids ()
	{
		check_auth ();
		$tpl = 'discovery/manage_snmp_sysobjids.html';
		
		$objects = SnmpSysobjid::get_objects ();
		
		$this->assign ('objects', $objects);
		$this->assign ('error_msg', error_msg());
		
		$this->display ($tpl);
	}
	
	/** Displays the page for adding a new SNMP system object ID */
	function snmp_sysobjid_add ()
	{
		check_auth ();
		$tpl = 'discovery/snmp_sysobjid_add.html';
		
		$obj = new SnmpSysobjid ();
		if (!empty_error_msg()) $obj->load_from_array(restore_form_data ('snmp_sysobjid_add', false, $data));
		if ($this->vars['snmp_sys_object_id']) $obj->snmp_sys_object_id = $this->vars['snmp_sys_object_id'];
		
		$params = $this->set_carry_fields (array('returl'));
		$this->assign ('obj', $obj);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('snmp_sysobjid_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Saves a new SNMP system object id */
	function snmp_sysobjid_add_submit ()
	{
		check_auth ();
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_snmp_sysobjids'));
		$params = $this->set_carry_fields (array('returl'));
		
		if ($this->vars['save'])
		{
			$data = $this->vars['obj'];
			$obj = new SnmpSysobjid ();
			$obj->load_from_array ($data);
			
			if ($obj->is_valid_data())
			{
				$obj->save_data ();
				$params['id'] = $obj->id;
				$ret = $this->mk_redir ('snmp_sysobjid_edit', $params);
			}
			else
			{
				save_form_data ($data, 'snmp_sysobjid_add');
				$ret = $this->mk_redir ('snmp_sysobjid_add', $params);
			}
		}
		
		return $ret;
	}
	
	/** Displays the name for editing a new SNMP system object ID */
	function snmp_sysobjid_edit ()
	{
		check_auth ();
		$tpl = 'discovery/snmp_sysobjid_edit.html';
		
		$obj = new SnmpSysobjid ($this->vars['id']);
		if (!$obj->id) return $this->mk_redir ('manage_snmpsysobjids');
		if (!empty_error_msg()) $obj->load_from_array(restore_form_data ('snmp_sysobjid_edit', false, $data));
		
		$params = $this->set_carry_fields (array('returl', 'id'));
		$this->assign ('obj', $obj);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('snmp_sysobjid_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Saves a SNMP system object id */
	function snmp_sysobjid_edit_submit ()
	{
		check_auth ();
		$obj = new SnmpSysobjid ($this->vars['id']);
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_snmp_sysobjids'));
		$params = $this->set_carry_fields (array('returl', 'id'));
		
		if ($this->vars['save'] and $obj->id)
		{
			$data = $this->vars['obj'];
			$obj->load_from_array ($data);
			
			if ($obj->is_valid_data()) $obj->save_data ();
			else save_form_data ($data, 'snmp_sysobjid_edit');
			$ret = $this->mk_redir ('snmp_sysobjid_edit', $params);
		}
		
		return $ret;
	}
	
	/** Deletes a SNMP system object id */
	function snmp_sysobjid_delete ()
	{
		check_auth ();
		$obj = new SnmpSysobjid ($this->vars['id']);
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_snmp_sysobjids'));
		
		if ($obj->id) $obj->delete ();
		
		return $ret;
	}
	
	
	
	/****************************************************************/
	/* Management of network discoveries 				*/
	/****************************************************************/
	
	/** Displays the page for viewing and managing discoveries */
	function manage_discoveries ()
	{
		$tpl = 'discovery/manage_discoveries.html';
		
		if (isset($this->vars['customer_id'])) $_SESSION['manage_discoveries_settings']['customer_id'] = $this->vars['customer_id'];
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_discoveries_settings']['customer_id'] = $this->locked_customer->id;
		}
		if (isset($this->vars['detail_id'])) $_SESSION['manage_discoveries']['detail_id'] = $this->vars['detail_id'];
		$filter = $_SESSION['manage_discoveries_settings'];
		$customer_id = $filter['customer_id'];
		
		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		if (!$customer_id) check_auth ();
		else
		{
			check_auth (array('customer_id' => $customer_id));
			$setting = DiscoverySetting::get_by_customer ($customer_id);
			
			// If any specific range was present in the filter, make sure it is valid for the current customer
			$found = false;
			if ($filter['detail_id'])
			{
				for ($i=0; $i<count($setting->details); $i++) if ($found = ($setting->details[$i]->id == $filter['detail_id'])) break;
				if (!$found) unset($filter['detail_id']);
			}
			$discoveries = Discovery::get_discoveries ($customer_id, $filter);
			$has_unmatched_discoveries = Discovery::has_unmatched_discoveries ($customer_id);
			$tot_devices_cnt = Discovery::get_customer_devices_count ($customer_id);
		}
		
		$params = $this->set_carry_fields (array('do_filter'));
		$this->assign ('filter', $filter);
		$this->assign ('discoveries', $discoveries);
		$this->assign ('has_unmatched_discoveries', $has_unmatched_discoveries);
		$this->assign ('tot_devices_cnt', $tot_devices_cnt);
		$this->assign ('setting', $setting);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('WMI_DOMAIN_ROLES', $GLOBALS['WMI_DOMAIN_ROLES']);
		$this->assign ('SNMP_OBJ_CLASSES', $GLOBALS['SNMP_OBJ_CLASSES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('manage_discoveries_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Saves the filtering settings for the discoveries page */
	function manage_discoveries_submit ()
	{
		check_auth ();
		
		$filter = $this->vars['filter'];
		if (!$filter['show_details']) $filter['show_details'] = false;
		$_SESSION['manage_discoveries_settings'] = $filter;
		
		return $this->mk_redir ('manage_discoveries', array('do_filter' => 1));
	}
	
	
	
	/** Displays the page with the details about a specific discovered device */
	function discovery_details ()
	{
		check_auth ();
		$tpl = 'discovery/discovery_details.html';
		$discovery = new Discovery ($this->vars['id']);
		
		if (!$discovery->id) return $this->mk_redir ('manage_discoveries');
		$detail = new DiscoverySettingDetail ($discovery->detail_id);
		
		$params = $this->set_carry_fields (array('id'));
		$this->assign ('discovery', $discovery);
		$this->assign ('detail', $detail);
		$this->assign ('WMI_DOMAIN_ROLES', $GLOBALS['WMI_DOMAIN_ROLES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('discovery_details_submit', $params);
		
		$this->display ($tpl);
	}
	
	function discovery_details_submit ()
	{
		check_auth ();
		$discovery = new Discovery ($this->vars['id']);
		$detail = new DiscoverySettingDetail ($discovery->detail_id);
		$ret = $this->mk_redir ('manage_discoveries', array('customer_id' => $detail->customer_id));
		
		if ($this->vars['save'] and $discovery->id)
		{
			$data = $this->vars['discovery'];
			$discovery->load_from_array ($data);
			
			if ($discovery->is_valid_data()) $discovery->save_data ();
			$ret = $this->mk_redir ('discovery_details', array('id'=>$discovery->id));
		}
		
		return $ret;
	}
	
	/** Displays the page for editing the matching of discovered devices with Keyos devices */
	function discovery_match ()
	{
		check_auth ();
		$tpl = 'discovery/discovery_match.html';
		$discovery = new Discovery ($this->vars['id']);
		if (!$discovery->id) return $this->mk_redir ('manage_discoveries');
		
		$detail = new DiscoverySettingDetail ($discovery->detail_id);
		$matches = $discovery->get_keyos_matches (false, $discovery->customer_id);
		
		$objects_lists = array ();
		$objects_lists[SNMP_OBJ_CLASS_COMPUTER] = Computer::get_computers_list (array('customer_id' => $detail->customer_id, 'order_by'=>'name', 'append_id' => true));
		$objects_lists[SNMP_OBJ_CLASS_PERIPHERAL] = Peripheral::get_peripherals_list (array('customer_id' => $detail->customer_id));
		$objects_lists[SNMP_OBJ_CLASS_AD_PRINTER] = AD_Printer::get_ad_printers_list (array('customer_id' => $detail->customer_id), true);
		
		$params = $this->set_carry_fields (array('id'));
		$this->assign ('discovery', $discovery);
		$this->assign ('detail', $detail);
		$this->assign ('matches', $matches);
		$this->assign ('objects_lists', $objects_lists);
		$this->assign ('SNMP_OBJ_CLASSES', $GLOBALS['SNMP_OBJ_CLASSES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('discovery_match_submit', $params);
		
		$this->display ($tpl); 
	}
	
	/** Saves the matching for a discovered device */
	function discovery_match_submit ()
	{
		check_auth ();
		$discovery = new Discovery ($this->vars['id']);
		$params = $this->set_carry_fields (array('id'));
		$ret = $this->mk_redir ('discovery_details', $params);
		
		if ($this->vars['save'] and $discovery->id)
		{
			$data = $this->vars['discovery'];
			if ($data['matched_obj_id'][$data['matched_obj_class']]) $data['matched_obj_id'] = $data['matched_obj_id'][$data['matched_obj_class']];
			else $data['matched_obj_id'] = 0;
			
			$discovery->set_matched_object ($data['matched_obj_class'], $data['matched_obj_id']);
			$discovery->load_from_array ($data);
			if ($discovery->is_valid_data ()) $discovery->save_data ();
			$ret = $this->mk_redir ('discovery_match', $params);
		}
		
		return $ret;
	}
	
	/** Deletes a discovered device */
	function discovery_delete ()
	{
		check_auth ();
		$discovery = new Discovery ($this->vars['id']);
		$detail = new DiscoverySettingDetail ($discovery->detail_id);
		if ($discovery->id) $discovery->delete ();
		
		return $this->mk_redir ('manage_discoveries', array('customer_id' => $detail->customer_id));
	}
}

?>
<?php
ini_set('display_errors', 1);
class KalmController extends PluginController{
    protected $plugin_name = "KALM";
    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    /****************************************************************/
	/* Software packages management					*/
	/****************************************************************/
	
	
	/** Displays the page for managing the software packages definitions */
	function manage_software ()
	{
		check_auth ();
		class_load ('Software');
		$tpl = 'manage_software.tpl';
		
		$filter = $_SESSION['manage_software']['filter'];
	 	
		if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
		if (!isset($filter['limit'])) $filter['limit'] = 10;
		if (!isset($filter['search_text'])) $fiter['search_text'] = "";
		else{
			$filter['search_text'] = preg_replace ("/'/", '"', $filter['search_text']);
			
		}
		if (!isset($filter['order_by'])) $filter['order_by'] = 'name';
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'ASC';
		
		$sw_count = 0;
		$softwares = Software::get_software_list ($filter, $sw_count);
				
		if ($sw_count < $filter['start'])
		{
			$filter['start'] = 0;
			$_SESSION['manage_software']['filter']['start'] = 0;
			$softwares = Software::get_software_list($filter, $sw_count);
		}
		
		$pages = make_paging ($filter['limit'], $sw_count);
		
		for ($i=0; $i<count($softwares); $i++)
		{
			$softwares[$i]->customers = $softwares[$i]->get_customers ();
			$softwares[$i]->all_customers = $softwares[$i]->get_all_customers ();
			//debug ($softwares[$i]->all_customers);
		}
			
		$this->assign ('softwares', $softwares);
		$this->assign ('filter', $filter);
		$this->assign ('pages', $pages);
		$this->assign ('sw_count', $sw_count);
		$this->assign ('start_prev', $filter['start'] - $filter['limit']);
		$this->assign ('start_next', $filter['start'] + $filter['limit']);
		$this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
		$this->assign ('LIC_TYPES_NAMES', $GLOBALS['LIC_TYPES_NAMES']);
		$this->assign ('NAMES_MATCH_TYPES', $GLOBALS['NAMES_MATCH_TYPES']);
		$this->set_form_redir('manage_software_submit');
		$this->assign ('error_msg', error_msg());
		
		$this->display ($tpl);
	}
	
	function manage_software_submit()
	{
		check_auth();
		$ret = $this->mk_redir('manage_software');
		
		$_SESSION['manage_software']['filter'] = $this->vars['filter'];
		
		return $ret;
	}
	
	
	/** Displays the page for adding a software package definition */
	function software_add ()
	{
		check_auth ();
		class_load ('Software');
		$tpl = 'software_add.tpl';
		
		$software = new Software ();
		// Restore form data, in case there was some error
		if (!empty_error_msg()) $software->load_from_array (restore_form_data ('software', false, $data));
		
		$this->assign ('software', $software);
		$this->assign ('LIC_TYPES_NAMES', $GLOBALS['LIC_TYPES_NAMES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('software_add_submit');
		
		$this->display ($tpl);
	}
	
	/** Saves the information about a new software package */
	function software_add_submit ()
	{
		check_auth ();
		class_load ('Software');
		class_load ('SoftwareMatch');
		
		$ret = $this->mk_redir ('manage_software');
		
		if ($this->vars['save'])
		{
			$software_data = $this->vars['software'];
			$software_data['license_types'] = 0;
			if (is_array ($this->vars['licensing_types']))
			{
				foreach ($this->vars['licensing_types'] as $type_id) $software_data['license_types']+= $type_id;
			}
			
			$software = new Software();
			$software->load_from_array ($software_data);
			
			if ($software->is_valid_data())
			{
				$software->save_data ();
				
				// Create also an initial rule using the package's name
				$rule = new SoftwareMatch ();
				$rule->software_id = $software->id;
				$rule->expression = $software->name;
				$rule->match_type = CRIT_STRING_STARTS;
				$rule->save_data ();
				
				$ret = $this->mk_redir ('software_edit', array ('id'=>$software->id));
			}
			else
			{
				$ret = $this->mk_redir ('software_add');
				save_form_data ($software_data, 'software');
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a software package definition */
	function software_edit ()
	{
		check_auth ();
		class_load ('Software');
		$tpl = 'software_edit.tpl';
		
		$software = new Software ($this->vars['id']);
		if (!$software->id) return $this->mk_redir ('manage_software');
		
		// Restore form data, in case there was some error
		if (!empty_error_msg()) $software->load_from_array (restore_form_data ('software', false, $data));
		
		$matching_names = $software->get_matching_names ();
		
		$this->assign ('software', $software);
		$this->assign ('matching_names', $matching_names);
		$this->assign ('LIC_TYPES_NAMES', $GLOBALS['LIC_TYPES_NAMES']);
		$this->assign ('NAMES_MATCH_TYPES', $GLOBALS['NAMES_MATCH_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('software_edit_submit', array ('id'=>$software->id));

		$this->display ($tpl);
	}
	
	
	/** Saves the information about an existing software package definition */
	function software_edit_submit ()
	{
		check_auth ();
		class_load ('Software');
		
		$ret = $this->mk_redir ('manage_software');
		$software = new Software ($this->vars['id']);
		
		if ($this->vars['save'] and $software->id)
		{
			$software_data = $this->vars['software'];
			$software_data['license_types'] = 0;
			if (is_array ($this->vars['licensing_types']))
			{
				foreach ($this->vars['licensing_types'] as $type_id) $software_data['license_types']+= $type_id;
			}
			
			$software->load_from_array ($software_data);
			if ($software->is_valid_data())
			{
				$software->save_data();
			}
			else
			{
				save_form_data ($software_data, 'software');
			}
			
			$ret = $this->mk_redir ('software_edit', array ('id'=>$software->id));
		}
		
		return $ret;
	}
	
	
	/** Deletes information about a software package */
	function software_delete ()
	{
		check_auth ();
		class_load ('Software');
		
		$ret = $this->mk_redir ('manage_software');
		$software = new Software ($this->vars['id']);
		
		if ($software->id) $software->delete();
		
		return $ret;
	}
	
	
	/** Displays the customers using a specific type of software */
	function software_customers ()
	{
		check_auth ();
		class_load ('Software');
		$tpl = 'software_customers.tpl';
		
		$software = new Software ($this->vars['id']);
		if (!$software->id) return $this->mk_redir ('manage_software');
		
		// Customers with licenses
		$customers = $software->get_customers ();
		
		// Use the merging just in case some customers have licenses defined without
		// actually having matching software in Kawacs
		$all_customers = $software->get_all_customers () + $customers;
		asort ($all_customers);
		
		// Fetch the list of computers for each customer on which the software is installed
		$all_computers = $software->get_computers_list ();
		
		$this->assign ('software', $software);
		$this->assign ('customers', $customers);
		$this->assign ('all_customers', $all_customers);
		$this->assign ('all_computers', $all_computers);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}

	
	/** Displays the page for adding a name matching rule for a software package */
	function software_rule_add ()
	{
		check_auth ();
		class_load ('Software');
		$tpl = 'software_rule_add.tpl';
		
		$software = new Software ($this->vars['software_id']);
		if (!$software->id) return $this->mk_redir ('manage_software');
		
		$this->assign ('software', $software);
		$this->assign ('NAMES_MATCH_TYPES', $GLOBALS['NAMES_MATCH_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('software_rule_add_submit', array ('software_id'=>$software->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about a new name matching rule */
	function software_rule_add_submit ()
	{
		check_auth ();
		class_load ('Software');
		
		$software = new Software ($this->vars['software_id']);
		if (!$software->id) return $this->mk_redir ('manage_software');
		
		$ret = $this->mk_redir ('software_edit', array ('id'=>$software->id));
		
		if ($this->vars['save'] and $software->id)
		{
			$rule_data = $this->vars['rule'];
			$rule = new SoftwareMatch ();
			$rule->load_from_array ($rule_data);
			$rule->software_id = $software->id;
			
			if ($rule->is_valid_data())
			{
				$rule->save_data ();
				$ret = $this->mk_redir ('software_rule_edit', array ('id'=>$rule->id));
			}
			else
			{
				save_form_data ('software_match', $rule_data);
				$ret = $this->mk_redir ('software_rule_add', array ('software_id'=>$software->id));
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing an existing rule. If the licensing type is 'Per seat', show also current matching names. */
	function software_rule_edit ()
	{
		check_auth ();
		class_load ('Software');
		$tpl = 'software_rule_edit.tpl';
		
		$rule = new SoftwareMatch ($this->vars['id']);
		if (!$rule->id) return $this->mk_redir ('manage_software');
		$software = new Software ($rule->software_id);
		
		$matching_names = $rule->get_matching_names();
		
		// If there was an error, load the saved form data
		if (!empty_error_msg()) $rule->load_from_array (restore_form_data('software_match', false, $rule_data));
		
		$this->assign ('software', $software);
		$this->assign ('rule', $rule);
		$this->assign ('matching_names', $matching_names);
		$this->assign ('NAMES_MATCH_TYPES', $GLOBALS['NAMES_MATCH_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('software_rule_edit_submit', array ('id'=>$rule->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the data about an existing software rule */
	function software_rule_edit_submit ()
	{
		check_auth ();
		class_load ('Software');
		
		$rule = new SoftwareMatch ($this->vars['id']);
		if (!$rule->id) $this->mk_redir ('manage_software');
		
		$ret = $this->mk_redir ('software_edit', array ('id'=>$rule->software_id));
		
		if ($this->vars['save'])
		{
			$rule_data = $this->vars['rule'];
			$rule->load_from_array ($rule_data);
			
			if ($rule->is_valid_data())
			{
				$rule->save_data ();
			}
			else
			{
				save_form_data ($rule_data, 'software_match');
			}
			$ret = $this->mk_redir ('software_rule_edit', array ('id'=>$rule->id));
		}
		
		return $ret;
	}
	
	
	/** Deletes a name matching rule */
	function software_rule_delete ()
	{
		check_auth ();
		class_load ('Software');
		
		$rule = new SoftwareMatch ($this->vars['id']);
		$ret = $this->mk_redir ('software_edit', array ('id'=>$rule->software_id));
		
		if ($rule->id) $rule->delete ();
		
		return $ret;
	}

	
	
	/****************************************************************/
	/* Customer software licenses management			*/
	/****************************************************************/
	
	/** Displays the page for managing customer software licenses */
	function manage_licenses ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$tpl = 'manage_licenses.tpl';
		class_load ('Customer');
		class_load ('SoftwareLicense');
		
		// Extract the list of Customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers = Customer::get_customers_list ($customers_filter);
		
		// If a license ID was passed, extract the customer ID from it
		if ($this->vars['license_id'])
		{
			$license = new SoftwareLicense ($this->vars['license_id']);
			$this->vars['customer_id'] = $license->customer_id;
		}
		
		if ($this->vars['customer_id'])
		{
			$customer = new Customer ($this->vars['customer_id']);
		}
		elseif ($this->locked_customer->id)
		{
			$customer = new Customer ($this->locked_customer->id);
		}
		
		if ($customer->id)
		{
			// A valid customer has been specified, so load the needed information
			$licenses = SoftwareLicense::get_customer_licenses ($customer->id, true);
			
			// Mark the potential customer for locking
			$_SESSION['potential_lock_customer_id'] = $customer->id;
			
			// For all licenses that have linke notifications, mark the notifications as read
			for ($i=0;  $i<count($licenses); $i++)
			{
				$licenses[$i]->load_notification ();
				if ($licenses[$i]->notification->id) $licenses[$i]->notification->mark_read ($this->current_user->id);
			}
			$this->update_unread_notifs ();
		}
		
		$this->assign ('customers', $customers);
		$this->assign ('customer', $customer);
		$this->assign ('licenses', $licenses);
		$this->assign ('LIC_TYPES_NAMES', $GLOBALS['LIC_TYPES_NAMES']);

		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('manage_licenses');
		
		$this->display ($tpl);
	}
	
	
	/** Displays a page with all the customers that have exceeded their licenses */
	function exceeded_licenses ()
	{
		check_auth ();
		class_load ('SoftwareLicense');
		class_load ('Customer');
		$tpl = 'exceeded_licenses.tpl';
		
		$exceeded_licenseses = SoftwareLicense::get_exceeded_licenses ();
		$customers_list = Customer::get_customers_list ();
		
		// For all licenses that have linke notifications, mark the notifications as read
		foreach ($exceeded_licenseses as $customer_id => $licenses)
		{
			for ($i=0;  $i<count($licenses); $i++)
			{
				$licenses[$i]->load_notification ();
				if ($licenses[$i]->notification->id) $licenses[$i]->notification->mark_read ($this->current_user->id);
			}
		}
		$this->update_unread_notifs ();
		
		$this->assign ('exceeded_licenseses', $exceeded_licenseses);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('LIC_TYPES_NAMES', $GLOBALS['LIC_TYPES_NAMES']);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	
	/** Shows the page for adding a license for a customer */
	function license_add ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$tpl = 'license_add.tpl';
		class_load ('Customer');
		class_load ('SoftwareLicense');
		
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_licenses');
		
		$license = new SoftwareLicense ();
		// Load the form data if there was any error
		if (!empty_error_msg()) $license->load_from_array (restore_form_data ('license', false, $license_data));
		
		$softwares = Software::get_software_names ();
		
		$this->assign ('customer', $customer);
		$this->assign ('license', $license);
		$this->assign ('softwares', $softwares);
		$this->assign ('LIC_TYPES_NAMES', $GLOBALS['LIC_TYPES_NAMES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('license_add_submit', array ('customer_id' => $customer->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about a new license */
	function license_add_submit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		class_load ('Customer');
		class_load ('SoftwareLicense');
		
		$customer = new Customer ($this->vars['customer_id']);
		$ret = $this->mk_redir ('manage_licenses', array ('customer_id' => $customer->id));
		
		if ($this->vars['save'] and $customer->id)
		{
			$license_data = $this->vars['license'];
			$license_data['comments'] = stripslashes ($license_data['comments']);
			$license_data['issue_date'] = date_fields_to_time ($license_data['issue_date']);
			$license_data['exp_date'] = date_fields_to_time ($license_data['exp_date']);
			
			$license = new SoftwareLicense ();
			$license->customer_id = $customer->id;
			$license->load_from_array ($license_data);
			
			if ($license->is_valid_data ())
			{
				$license->save_data ();
				$ret = $this->mk_redir ('license_edit', array ('id'=>$license->id));
				// Also run the checks for exceeded licenses for this customer
				SoftwareLicense::check_licenses_notifications ($license->customer_id);
			}
			else
			{
				save_form_data ($license_data, 'license');
				$ret = $this->mk_redir ('license_add', array ('customer_id' => $customer->id));
			}
		}
		
		return $ret;
	}
	
	
	/** Shows the page for editing an existing license for a customer */
	function license_edit ()
	{
		$tpl = 'license_edit.tpl';
		class_load ('Customer');
		class_load ('SoftwareLicense');
		
		$license = new SoftwareLicense ($this->vars['id']);
		if (!$license->id) return $this->mk_redir ('manage_licenses');
		$customer = new Customer ($license->customer_id);
		$license->load_serials ();
		$license->load_files ();
		
		check_auth (array('customer_id' => $license->customer_id));
		
		// Load the form data if there was any error
		if (!empty_error_msg()) $license->load_from_array (restore_form_data ('license', false, $license_data));
		
		$softwares = Software::get_software_names ();
		
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		// If there is a notification linked to this, mark it as read
		$license->load_notification ();
		if ($license->notification->id) $license->notification->mark_read ($this->current_user->id);
		$this->update_unread_notifs ();
		
		$this->assign ('customer', $customer);
		$this->assign ('license', $license);
		$this->assign ('softwares', $softwares);
		$this->assign ('LIC_TYPES_NAMES', $GLOBALS['LIC_TYPES_NAMES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('license_edit_submit', array ('id' => $license->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about an existing license */
	function license_edit_submit ()
	{
		class_load ('SoftwareLicense');
		
		$license = new SoftwareLicense ($this->vars['id']);
		check_auth (array('customer_id' => $license->customer_id));
		
		if ($this->vars['save'] and $license->id)
		{
			$license_data = $this->vars['license'];
			$license_data['comments'] = stripslashes ($license_data['comments']);
			$license_data['issue_date'] = date_fields_to_time ($license_data['issue_date']);
			$license_data['exp_date'] = date_fields_to_time ($license_data['exp_date']);
			$license_data['no_notifications'] = ($license_data['no_notifications'] ? 1 : 0);
			
			$license->load_from_array ($license_data);
			
			if ($license->is_valid_data ())
			{
				$license->save_data ();
				// Also run the checks for exceeded licenses for this customer
				SoftwareLicense::check_licenses_notifications ($license->customer_id);
			}
			else
			{
				save_form_data ($license_data, 'license');
			}
			$ret = $this->mk_redir ('license_edit', array ('id'=>$license->id));
		}
		else
		{
			$ret = $this->mk_redir ('manage_licenses', array ('customer_id'=>$license->customer_id));
		}
		
		return $ret;
	}
	
	
	/** Displays the page for adding a new serial number to a license */
	function license_sn_add ()
	{
		class_load ('SoftwareLicenseSN');
		$license = new SoftwareLicense ($this->vars['license_id']);
		check_auth (array('customer_id' => $license->customer_id));
		$tpl = 'license_sn_add.tpl';
		
		$sn = new SoftwareLicenseSN ();
		// Restore form data, in case there was some error
		if (!empty_error_msg()) $sn->load_from_array (restore_form_data ('license_sn', false, $data));
		
		$params = $this->set_carry_fields (array('license_id'));
		
		$this->assign ('license', $license);
		$this->assign ('sn', $sn);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('license_sn_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves a new serial number */
	function license_sn_add_submit ()
	{
		class_load ('SoftwareLicenseSN');
		$license = new SoftwareLicense ($this->vars['license_id']);
		check_auth (array('customer_id' => $license->customer_id));
		
		$ret = $this->mk_redir ('license_edit', array ('id' => $license->id));
		
		if ($this->vars['save'] and $license->id)
		{
			$data = $this->vars['sn'];
			$sn = new SoftwareLicenseSN ();
			$sn->load_from_array ($data);
			$sn->license_id = $license->id;
			
			if ($sn->is_valid_data ())
			{
				$sn->save_data ();
				$ret = $this->mk_redir ('license_sn_edit', array ('id' => $sn->id));
			}
			else
			{
				save_form_data ($data, 'license_sn');
				$ret = $this->mk_redir ('license_sn_add', array ('license_id' => $license->id));
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a serial number */
	function license_sn_edit ()
	{
		class_load ('SoftwareLicenseSN');
		$sn = new SoftwareLicenseSN ($this->vars['id']);
		$license = new SoftwareLicense ($sn->license_id);
		check_auth (array('customer_id' => $license->customer_id));
		$tpl = 'license_sn_edit.tpl';
		
		if (!$sn->id or !$license->id) return $this->mk_redir ('manage_licenses');
		
		// Restore form data, in case there was some error
		if (!empty_error_msg()) $sn->load_from_array (restore_form_data ('license_sn', false, $data));
		
		$params = $this->set_carry_fields (array('id'));
		
		$this->assign ('license', $license);
		$this->assign ('sn', $sn);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('license_sn_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves a license serial number */
	function license_sn_edit_submit ()
	{
		class_load ('SoftwareLicenseSN');
		$sn = new SoftwareLicenseSN ($this->vars['id']);
		$license = new SoftwareLicense ($sn->license_id);
		check_auth (array('customer_id' => $license->customer_id));
		
		$ret = $this->mk_redir ('license_edit', array ('id' => $license->id));
		
		if ($this->vars['save'] and $sn->id)
		{
			$data = $this->vars['sn'];
			$sn->load_from_array ($data);
			
			if ($sn->is_valid_data ())
			{
				$sn->save_data ();
			}
			else
			{
				save_form_data ($data, 'license_sn');
			}
			$ret = $this->mk_redir ('license_sn_edit', array ('id' => $sn->id));
		}
		
		return $ret;
	}
	
	
	/** Deletes a license serial number */
	function license_sn_delete ()
	{
		class_load ('SoftwareLicenseSN');
		$sn = new SoftwareLicenseSN ($this->vars['id']);
		$license = new SoftwareLicense ($sn->license_id);
		check_auth (array('customer_id' => $license->customer_id));
		
		$ret = $this->mk_redir ('license_edit', array ('id' => $license->id));
		
		if ($sn->id)
		{
			$sn->delete ();
		}
		
		return $ret;
	}
	
	
	/** Displays the page for uploading a file */
	function license_file_add ()
	{
		class_load ('SoftwareLicenseFile');
		$license = new SoftwareLicense ($this->vars['license_id']);
		check_auth (array('customer_id' => $license->customer_id));
		$tpl = 'license_file_add.tpl';
		
		$file = new SoftwareLicenseFile ();
		// Restore form data, in case there was some error
		if (!empty_error_msg()) $file->load_from_array (restore_form_data ('license_file', false, $data));
		
		$params = $this->set_carry_fields (array('license_id'));
		
		$this->assign ('license', $license);
		$this->assign ('file', $file);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('license_file_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the file added to a licensee */
	function license_file_add_submit ()
	{
		class_load ('SoftwareLicenseFile');
		$license = new SoftwareLicense ($this->vars['license_id']);
		check_auth (array('customer_id' => $license->customer_id));
		
		$ret = $this->mk_redir ('license_edit', array ('id' => $license->id));
		
		if ($this->vars['save'] and $license->id)
		{
			$data = $this->vars['file'];
			$data['name'] = $_FILES['upload']['name'];
			$data['tmp_name'] = $_FILES['upload']['tmp_name'];
			
			$file = new SoftwareLicenseFile ();
			$file->license_id = $license->id;
			$file->load_from_array ($data);
			
			if ($file->is_valid_data ())
			{
				$file->save_data ();
				$ret = $this->mk_redir ('license_file_edit', array ('id' => $file->id));
			}
			else
			{
				save_form_data ($data, 'license_file');
				$ret = $this->mk_redir ('license_file_add', array ('license_id' => $license->id));
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a license file */
	function license_file_edit ()
	{
		class_load ('SoftwareLicenseFile');
		$file = new SoftwareLicenseFile ($this->vars['id']);
		$license = new SoftwareLicense ($file->license_id);
		check_auth (array('customer_id' => $license->customer_id));
		$tpl = 'license_file_edit.tpl';
		
		if (!$file->id or !$license->id) return $this->mk_redir ('manage_licenses');
		
		// Restore form data, in case there was some error
		if (!empty_error_msg()) $file->load_from_array (restore_form_data ('license_file', false, $data));
		
		$params = $this->set_carry_fields (array('id'));
		
		$this->assign ('license', $license);
		$this->assign ('file', $file);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('license_file_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the file for a license */
	function license_file_edit_submit ()
	{
		class_load ('SoftwareLicenseFile');
		$file = new SoftwareLicenseFile ($this->vars['id']);
		$license = new SoftwareLicense ($file->license_id);
		check_auth (array('customer_id' => $license->customer_id));
		
		$ret = $this->mk_redir ('license_edit', array ('id' => $license->id));
		
		if ($this->vars['save'] and $file->id)
		{
			$data = $this->vars['file'];
			if ($_FILES['upload']['name'])
			{
				$data['name'] = $_FILES['upload']['name'];
				$data['tmp_name'] = $_FILES['upload']['tmp_name'];
			}
			
			$file->load_from_array ($data);
			
			if ($file->is_valid_data ())
			{
				$file->save_data ();
			}
			else
			{
				save_form_data ($data, 'license_file');
			}
			$ret = $this->mk_redir ('license_file_edit', array ('id' => $file->id));
		}
		
		return $ret;
	}
	
	
	
	/** Serves an attachment from a software license */
	function license_file_open ()
	{
		class_load ('SoftwareLicenseFile');
		$file = new SoftwareLicenseFile ($this->vars['id']);
		$license = new SoftwareLicense ($file->license_id);
		check_auth (array('customer_id' => $license->customer_id));
		
		if (!$file->local_filename or !file_exists(DIR_UPLOAD_KALM.'/'.$file->local_filename))
		{
			error_msg ('Sorry, the attachment file is missing');
			if ($this->vars['returl']) $ret = $this->vars['returl'];
			else $ret = $this->mk_redir ('license_edit', array ('id' => $license->id));
		}
		else
		{
			//header ("Pragma: no-cache");
			header ("Pragma: private");
			header ("Expires: 0");
			header ("Content-type: application/force-download;");
			header ("Content-Transfer-Encoding: none");
			//header ("Cache-Control: public");
			//header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header ("Cache-Control: private");
			header ('Content-Length: '.filesize(DIR_UPLOAD_KALM.'/'.$file->local_filename));
			header ('Content-Disposition: attachment; filename="'.$file->original_filename.'"');
			header ("Connection: close");
			
			readfile(DIR_UPLOAD_KALM.'/'.$file->local_filename);
			exit;
		}
	}
	
	
	/** Deletes a file from a license */
	function license_file_delete ()
	{
		class_load ('SoftwareLicenseFile');
		$file = new SoftwareLicenseFile ($this->vars['id']);
		$license = new SoftwareLicense ($file->license_id);
		check_auth (array('customer_id' => $license->customer_id));
		
		$ret = $this->mk_redir ('license_edit', array ('id' => $license->id));
		
		if ($file->id)
		{
			$file->delete ();
		}
		
		return $ret;
	}
	
	
	
	/** Deletes a license from the customer's list */
	function license_delete ()
	{
		class_load ('SoftwareLicense');
		$license = new SoftwareLicense ($this->vars['id']);
		
		check_auth (array('customer_id' => $license->customer_id));
		
		$ret = $this->mk_redir ('manage_licenses', array ('customer_id' => $license->customer_id));
		
		if ($license->id) $license->delete();
		
		return $ret;
	}
	
	
	/** Shows what computers are using this license */
	function license_computers ()
	{
		$tpl = 'license_computers.tpl';
		class_load ('Customer');
		class_load ('SoftwareLicense');
		
		$license = new SoftwareLicense ($this->vars['id']);
		if (!$license->id) return $this->mk_redir ('manage_licenses');
		
		check_auth (array('customer_id' => $license->customer_id));
		
		$computers = $license->get_computers ();
		$customer = new Customer ($license->customer_id);
	
		$this->assign ('license', $license);
		$this->assign ('computers', $computers);
		$this->assign ('customer', $customer);
		
		$this->display ($tpl);
	}
}
?>

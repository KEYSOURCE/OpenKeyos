<?php
class_load('Customer');
class CustomerController extends PluginController{
    protected $plugin_name = "Customer";
    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    /** Shows the page for managing customers */
	function manage_customers ()
	{
		check_auth ();
		$tpl = 'manage_customers.tpl';
		$filter = $_SESSION['manage_customers']['filter'];

		if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
		if (!isset($filter['limit'])) $filter['limit'] = 20;
		if (!isset($filter['order_by'])) $filter['order_by'] = 'name';
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'ASC';
		if (!isset($filter['active'])) $filter['active'] = 1;
		if (!isset($filter['contract_type'])) $filter['contract_type'] = CONTRACT_ALL;

		$customers_count = 0;
		if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
		$customers = Customer::get_customers ($filter, $customers_count);
		if ($customers_count < $filter['start'])
		{
			$filter['start'] = 0;
			$_SESSION['tickets']['filter']['start'] = 0;
			$customers = Customer::get_customers ($filter, $customers_count);
		}

		$pages = make_paging ($filter['limit'], $customers_count);


		// Extract the list of customers, eventually restricting only to the customers assigned to
		// the current user, if he has restricted customer access.
		$customers_filter = array ('favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		$all_customers_list = Customer::get_customers_list (array('active'=>-1, 'show_ids'=>true, 'favorites_first' => $this->current_user->id));

		$this->assign ('customers', $customers);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('all_customers_list', $all_customers_list);
		$this->assign ('customers_count', $customers_count);
		$this->assign ('filter', $filter);
		$this->assign ('pages', $pages);
		$this->assign ('start_prev', $filter['start'] - $filter['limit']);
		$this->assign ('start_next', $filter['start'] + $filter['limit']);
		$this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
		$this->assign ('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
		$this->assign ('CUST_SUBTYPES', $GLOBALS['CUST_SUBTYPES']);
		$this->assign ('CUST_PRICETYPES', $GLOBALS['CUST_PRICETYPES']);
		$this->set_form_redir ('manage_customers_submit');
		$this->assign ('error_msg', error_msg ());

		$this->display ($tpl);
	}


	/** Saves customers filtering criteria */
	function manage_customers_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_customers');

		if ($this->vars['filter']['customer_id'])
			$ret = $this->mk_redir ('customer_edit', array ('id' => $this->vars['filter']['customer_id']));
		else
			$_SESSION['manage_customers']['filter'] = $this->vars['filter'];

		return $ret;
	}
        
        /** Displays the quick search page for customers and performs the search */
	function search_customer ()
	{
		check_auth ();
		$tpl = 'search_customer.tpl';

        $customers = array();

		if ($this->vars['search_text'] and is_numeric($this->vars['search_text']))
		{
			// If a valid customer ID has been specified, go to that customer
			$customer = new Customer($this->vars['search_text']);
			if ($customer->id) return $this->mk_redir ('customer_edit', array('id'=>$customer->id));
			else error_msg ('There is no customer with the specified ID');
		}
		elseif ($this->vars['search_text'])
		{
			// Perform a search by customers name, and if a single one is found go directly to it
			$customers = Customer::get_customers (array('search_text' => $this->vars['search_text']), $no_count);
			if (count($customers) == 1) return $this->mk_redir ('customer_edit', array('id' => $customers[0]->id));
		}

		$this->assign ('search_text', $this->vars['search_text']);
		$this->assign ('customers', $customers);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('search_customer');

		$this->display ($tpl);
	}

	/**
	 * Displays a search page for customers by assigned users phone number
	 *
	 */
	function sbpn()
	{
		check_auth(array('number'=>$this->vars['number']));
		$tpl = 'sbpn.tpl';
		$customer = Customer::get_customers_by_phone_numbers($this->vars['number']);
		if($customer!=null and $customer->id)
		{
			$_SESSION['customer_edit']['active_tab'] = 'tickets';
			return $this->mk_redir ('customer_edit', array('id' => $customer->id));
		}
		else {
			return $this->mk_redir ('manage_customers');
		}
	}
        
        /** Displays the customers for which the alert e-mails have been suspended */
	function customers_suspended_alerts()
	{
		check_auth ();
		$tpl = 'customers_suspended_alerts.tpl';

		$suspended_customers = Customer::get_suspended_customers_alerts ();

		$this->assign ('suspended_customers', $suspended_customers);
		$this->assign ('error_msg', error_msg ());

		$this->display ($tpl);
	}


	/** Displays the page for adding a customer */
	function customer_add ()
	{
		check_auth ();
		$tpl = 'customer_add.tpl';

		$customer = new Customer ();

		if (!empty_error_msg()) $customer->load_from_array (restore_form_data ('customer_data', false, $customer_data));

		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg());
		$this->assign ('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
		$this->assign ('DEFAULT_ACCOUNT_MANAGER', DEFAULT_ACCOUNT_MANAGER);
		$this->set_form_redir ('customer_add_submit');

		$this->display ($tpl);
	}


	/** Saves the information about the new customer */
	function customer_add_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_customers');

		if ($this->vars['save'])
		{
			$customer_data = $this->vars['customer'];
			$customer = new Customer ();
			$customer->load_from_array ($customer_data);

			if ($customer->is_valid_data ())
			{
				$customer->save_data ();
				$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id));
			}
			else
			{
				save_form_data ($customer_data, 'customer_data');
				$ret = $this->mk_redir ('customer_add');
			}
		}

		return $ret;
	}


	/** Displays a page for viewing a customer details */
	function customer_view ()
	{
		$params = $this->set_carry_fields (array('id'));
		$params['view_only'] = 1;
		return $this->mk_redir ('customer_edit', $params);
	}
        
        /** Displays the page for editing a customer */
	function customer_edit ()
	{
		// If the user can't access Edit page, check
		// if he can access at least the view page
		//check_auth (array('customer_id' => $this->vars['id']));
		check_auth (array('customer_id' => $this->vars['id']));
		if (!$this->current_user->can_access ('customer', 'customer_edit'))
		{
			if ($this->current_user->can_access ('customer', 'customer_view')) $this->vars['view_only'] = 1;
			else return $this->mk_redir ('manage_customers');
		}

		class_load ('Notification');
		class_load ('Computer');
		class_load ('Peripheral');
		class_load ('CustomerInternetContract');
		class_load ('CustomerPhoto');
		class_load ('AD_User');
		class_load ('InfoRecipients');
		class_load ('Ticket');
		class_load ('LocationFixed');
		class_load ('CustomerCCRecipient');
		class_load ('MonitorProfile');
		class_load ('CustomerTemplateStyle');
		class_load ('InterventionReport');



		$tpl = 'customer_edit.tpl';

		$active_tab = $_SESSION['customer_edit']['active_tab'];
		if(!$active_tab or !isset($active_tab)) $active_tab='computers';
		//debug($active_tab);

		$customer = new Customer ($this->vars['id']);
		if (!$customer->id) return $this->mk_redir ('manage_customers');

		if (!empty_error_msg()) $customer->load_from_array (restore_form_data ('customer_data', false, $customer_data));

		if($active_tab == 'recipients')
		{
			// Get the list of Keysource recipients for this customer's notifications
			$customer_notif_recips = InfoRecipients::get_customer_recipients (array('customer_id' => $customer->id), $no_total);
			$customer_notif_recips = $customer_notif_recips[$customer->id];

			// Get the list of customer recipients for this customer's notifications
			$recipients_customers = InfoRecipients::get_customer_recipients_customers (array('customer_id' => $customer->id), $no_total);
			$default_recipients_customers = InfoRecipients::get_customer_default_recipients_customers($customer->id);
			$customers_users_list = User::get_customers_users_list ();
			$default_recipients = InfoRecipients::get_customer_default_recipients ($customer->id);
			$cc_recipients = CustomerCCRecipient::get_cc_recipients($customer->id);

			$users_list = User::get_users_list (array('type' => (USER_TYPE_KEYSOURCE+USER_TYPE_KEYSOURCE_GROUP)));
			$users_list_customer = User::get_users_list (array('type' => (USER_TYPE_CUSTOMER+USER_TYPE_GROUP)));
			$assigned_users = $customer->get_assigned_users_list ();
		}
		if($active_tab == "users")
		{
			$customer_users = User::get_users (array('customer_id' => $customer->id), $nocount);
			$customer_contacts = CustomerContact::get_contacts(array('customer_id' => $customer->id));
		}
		if($active_tab == 'infos')
		{
			$customer_comments = CustomerComment::get_comments(array('customer_id' => $customer->id));
		}
		if($active_tab == 'internet')
		{
			$customer_internet_contracts = CustomerInternetContract::get_contracts (array('customer_id' => $customer->id, 'load_details' => true));
		}
		if($active_tab == 'photos')
		{
			$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true));
			$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $customer->id, 'append_id' => true));
			$customer_photos = CustomerPhoto::get_photos (array('customer_id' => $customer->id));
		}
		if($active_tab == 'peripherals')
		{
			$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $customer->id, 'append_id' => true));
		}

		// Load the notifications for each computer
		if($active_tab == 'computers')
		{
			$customer_computers = Computer::get_computers (array('customer_id' => $customer->id, 'load_roles' => true), $no_count);
			//$customer_ad_users = AD_User::get_ad_users (array('customer_id' => $customer->id));
			$computers = array ();
			for ($i=0; $i<count($customer_computers); $i++)
			{
				$customer_computers[$i]->notifications = $customer_computers[$i]->get_notifications ();
			}
			class_load('CustomerComputerGroup');
			$computer_groups = CustomerComputerGroup::get_groups(array('customer_id'=>$customer->id));

            //now we need stats informations...
            $brands = Computer::get_customer_computers_brands($customer->id);

            $brands_names = array();
            $brands_values = array();
            foreach($brands as $k=>$v){
                if(trim($k) == "") $k="Unknown";
                $brands_names[] = "%%.%% - ".$k." (".$v.")";
                $brands_values[] = array($k, intval($v));
            }

            $this->assign("bcount", count($brands));
            $this->assign('brands_names', json_encode($brands_names));
            $this->assign('brands_values', json_encode($brands_values));

            $oses = Computer::get_customer_computers_oses($customer->id);

            $oses_names = array();
            $oses_values = array();
            foreach($oses as $k=>$v){
                if(trim($k) == "") $k="Unknown";
                $oses_names[] = "%%.%% - ".$k." (".$v.")";
                $oses_values[] = array($k, intval($v));
            }
            $this->assign("oscount", count($oses));
            $this->assign('oses_names', json_encode($oses_names));
            $this->assign('oses_values', json_encode($oses_values));


            $comps_evo = Computer::get_computers_evolution($customer->id);
            //debug($comps_evo);
            $this->assign('comps_evo_months', json_encode($comps_evo['months']));
            $this->assign('comps_evo_servers', json_encode($comps_evo['servers']));
            $this->assign('comps_evo_workstations', json_encode($comps_evo['workstations']));
            $this->assign('comps_evo_other', json_encode($comps_evo['other']));

            $nn = getdate(time());
            $one_year_ago =mktime(0,0,0,1,$nn['mon'], $nn['year']-1);
            //$rep_evo = Computer::get_computers_reporting_evo($one_year_ago, time(), $customer->id);
            //debug($rep_evo);
		}
		if($active_tab == 'adusers')
		{
			$computers_users = AD_User::get_logged_users($customer->id);
            $ad_user_evo = AD_User::get_adusers_stats($customer->id);
            $adevo_months = array();
            $adevo_users = array();
            $last_tot = 0;
            foreach($ad_user_evo['list_by_date'] as $kk=>$lst){
                $kdl = getdate($kk);
                $adevo_months[] = $kdl['month']." ".$kdl['year'];
                $adevo_users[] = count($lst);
                $last_tot += count($lst);
                $adevo_tot_users[] = $last_tot;
            }
            //debug($adevo_months);
            //debug($adevo_users);

            $this->assign('tot_users', $ad_user_evo['total_users']);
            $this->assign('adevo_months', json_encode($adevo_months));
            $this->assign('adevo_users', json_encode($adevo_users));
            $this->assign('adevo_totusers', json_encode($adevo_tot_users));
		}

		if($active_tab == 'backupconsole')
		{
			$profiles = MonitorProfile::get_profiles_with_backup();

			$filter = array('customer_id'=>$customer->id);
			$computers_green = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_SUCCESS, $count_g);
			$computers_red = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_ERROR, $count_r);
			$computers_orange = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_TAPE_ERROR, $count_o);
			$computers_grey = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_NOT_REPORTING, $count_gr);

			$backup_item_id = Computer::get_item_id('backup_status');
			$av_item_id = Computer::get_item_id ('anti_virus');
			$months_backups = array();//Computer::get_all_log_months (array('customer_id' => $customer->id, 'item_id' => $backup_item_id));
			$months_av = array();//Computer::get_all_log_months (array('customer_id' => $customer->id, 'item_id' => $av_item_id));
			$months_interval = array_unique(array_merge ($months_backups, $months_av));
			rsort($months_interval);

			$aprofiles = MonitorProfile::get_profiles_with_antivirus();
			$acomputers_red = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_ONE_WEEK, $acount_r);
			$acomputers_orange = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_ONE_DAY, $acount_o);
			$acomputers_green = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_SUCCESS, $acount_g);
			$acomputers_gray = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_NOT_REPORTING, $acount_gr);

			$total_cb = count($computers_red) + count($computers_orange) + count($computers_green) + count($computers_grey);
			if($total_cb!=0)
			{
				$perc_red = (count($computers_red) / $total_cb) * 100;
				$perc_orange = (count($computers_orange) / $total_cb) *100;
				$perc_green = (count($computers_green) / $total_cb) * 100;
				$perc_grey = (count($computers_grey)/ $total_cb) * 100;
			}
			$total_av = count($acomputers_red) + count($acomputers_orange) + count($acomputers_green) + count($acomputers_gray);
			if($total_av!=0)
			{
				$aperc_red = (count($acomputers_red) / $total_av) * 100;
				$aperc_orange = (count($acomputers_orange) / $total_av) *100;
				$aperc_green = (count($acomputers_green) / $total_av) * 100;
				$aperc_grey = (count($acomputers_gray)/ $total_av) * 100;
			}
		}

		//// XXX interventions related variables
		if($active_tab == 'tickets')
		{
			$customer_tickets = Ticket::get_tickets (array('customer_id' => $customer->id, 'customer_ids'=>$customer->id, 'status'=>-1), $no_count);
			$tot_interventions = 0;
			if($this->vars['ir_start']) $filter['start'] = $this->vars['ir_start'];
			else $filter['start'] = 0;
			$filter['limit'] = 50;
			$filter['customer_ids'] = array($customer->id);
			$interventions = InterventionReport::get_interventions($customer->account_manager, $filter, $tot_interventions);
			$pages = make_paging($filter['limit'], $tot_interventions);
			if($filter['start'] > $tot_interventions)
			{
				$filter['start'] = 0;
				$interventions = InterventionReport::get_interventions($customer->account_manager, $filter, $tot_interventions);
			}

			for($i=0; $i<count($interventions); $i++) $interventions[$i]->load_tickets();
			//now load the statuses
			$totals = InterventionReport::get_totals ();

            $tickes_lm_evo = Ticket::get_lm_tickets_evo($customer->id);
            $this->assign('tickets_lmevo_days', json_encode($tickes_lm_evo['days']));
            $this->assign('tickets_lmevo_closed', json_encode($tickes_lm_evo['closed']));
            $this->assign('tickets_lmevo_notclosed', json_encode($tickes_lm_evo['not_closed']));
            $this->assign('tickets_lmevo_new', json_encode($tickes_lm_evo['new']));

            $monthly_ir_stat = InterventionReport::get_monthly_intervention_stats($customer->id);
            $this->assign('ir_evo_months', json_encode($monthly_ir_stat['months']));
            $this->assign('ir_evo_closed', json_encode($monthly_ir_stat['closed']));
            $this->assign('ir_evo_open', json_encode($monthly_ir_stat['open']));
            $this->assign('ir_evo_centralized', json_encode($monthly_ir_stat['centralized']));
            $this->assign('ir_evo_hours_billed', json_encode($monthly_ir_stat['billed_hours']));
		}

		//debug($customer_computers);
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;

		$params = $this->set_carry_fields (array('id', 'view_only'));
		if ($this->vars['view_only']) $this->assign ('view_only', true);

		$customer_style = CustomerTemplateStyle::getByCustomerId($customer->id);

		//Load the nagvis account
		class_load('Nagvis');
		$nagvis = new Nagvis();
		$nagvis = $nagvis->get_item($customer->id);
		$this->assign('nagvis', $nagvis);

		$this->assign ('active_tab', $active_tab);
		$this->assign ('customer_style', $customer_style);
		$this->assign ('computers_users', $computers_users);
		$this->assign ('customer', $customer);
		$this->assign ('customer_notif_recips', $customer_notif_recips);
		$this->assign ('default_recipients', $default_recipients);
		$this->assign ('cc_recipients', $cc_recipients);
		$this->assign ('customer_users', $customer_users);
		$this->assign ('customer_contacts', $customer_contacts);
		$this->assign ('customer_comments', $customer_comments);
		$this->assign ('customer_internet_contracts', $customer_internet_contracts);
		$this->assign ('customer_photos', $customer_photos);

		$this->assign ('computers_list', $computers_list);
		$this->assign ('peripherals_list', $peripherals_list);

		$this->assign ('recipients_customers', $recipients_customers);
		$this->assign ('default_recipients_customers', $default_recipients_customers);
		$this->assign ('customers_users_list', $customers_users_list);

		$this->assign ('assigned_users', $assigned_users);
		$this->assign ('customer_computers', $customer_computers);
		$this->assign ('computer_groups', $computer_groups);
		$this->assign ('customer_ad_users', $customer_ad_users);
		$this->assign ('customer_tickets', $customer_tickets);

		$this->assign('count_r', count($computers_red));
		$this->assign('count_o', count($computers_orange));
		$this->assign('count_g', count($computers_green));
		$this->assign('count_gr', count($computers_grey));
		$this->assign('computers_red', $computers_red);
		$this->assign('computers_orange', $computers_orange);
		$this->assign('computers_green', $computers_green);
		$this->assign('computers_grey', $computers_grey);
		$this->assign('perc_red', $perc_red);
		$this->assign('perc_orange', $perc_orange);
		$this->assign('perc_green', $perc_green);
		$this->assign('perc_grey', $perc_grey);
		$this->assign('aperc_red', $aperc_red);
		$this->assign('aperc_orange', $aperc_orange);
		$this->assign('aperc_green', $aperc_green);
		$this->assign('aperc_grey', $aperc_grey);
		$this->assign('KEYOS_BASE_URL', KEYOS_BASE_URL);
		$this->assign('profiles', $profiles);
		$this->assign('months_interval', $months_backups);

		$this->assign('acount_r', count($acomputers_red));
		$this->assign('acount_o', count($acomputers_orange));
		$this->assign('acount_g', count($acomputers_green));
		$this->assign('acount_gr', count($acomputers_gray));
		$this->assign('acomputers_red', $acomputers_red);
		$this->assign('acomputers_orange', $acomputers_orange);
		$this->assign('acomputers_green', $acomputers_green);
		$this->assign('acomputers_gray', $acomputers_gray);
		$this->assign('aprofiles', $aprofiles);

		$this->assign ('interventions', $interventions);
		$this->assign ('filter', $filter);
		$this->assign ('totals', $totals);
		$this->assign ('tot_interventions', $tot_interventions);
		$this->assign ('pages', $pages);
		$this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);

		$this->assign ('users_list', $users_list);
		$this->assign ('users_list_customer', $users_list_customer);
		$this->assign ('returl', urlencode($this->mk_redir ('customer_edit', array ('id' => $customer->id))));
		$this->assign ('countries', LocationFixed::get_locations_list (array('type' => LOCATION_FIXED_TYPE_COUNTRY)));
		$this->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
		$this->assign ('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
		$this->assign ('CUST_SUBTYPES', $GLOBALS['CUST_SUBTYPES']);
		$this->assign ('CUST_PRICETYPES', $GLOBALS['CUST_PRICETYPES']);
		$this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
		$this->assign ('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('customer_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the customer information */
	function customer_edit_submit ()
	{
		check_auth (array('customer_id' => $this->vars['id']));
		$customer = new Customer ($this->vars['id']);
		if(!$customer->id)
		{
			$ret = $this->mk_redir ('manage_customers');
			return $ret;
		}
		$_SESSION['customer_edit']['active_tab'] = $this->vars['active_tab'];
		$ret = $this->mk_redir('customer_edit', array('id'=>$customer->id));
		$filter = $this->vars['filter'];
		if($this->vars['go'])
		{
			if ($this->vars['go'] == 'prev') $filter['start'] = $filter['start'] - $filter['limit'];
			elseif ($this->vars['go'] == 'next') $filter['start'] = $filter['start'] + $filter['limit'];
			$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id, 'ir_start'=>$filter['start']));
		}

		if ($this->vars['save'] and $customer->id)
		{
			$customer_data = $this->vars['customer'];
			$customer->load_from_array ($customer_data);

			if ($customer->is_valid_data ())
			{
				$customer->save_data ();
			}
			else
			{
				save_form_data ($customer_data, 'customer_data');
			}

			$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id, 'ir_start'=>$filter['start']));
		}
                if($this->vars['do_merge']){
                $cid = $this->vars['merge_with'];
                $merge_cust = new Customer($cid);
                if($merge_cust->id){
                    $customer->merge_with($merge_cust);
                }
            }
            return $ret;
	}
        
        function set_nagvis_data() {
                check_auth(array('customer_id' => $this->vars['customer_id']));
                class_load('Nagvis');
                $nagvis = new Nagvis();
                $nagvis = $nagvis->get_item($this->vars['customer_id']);
                if (!empty_error_msg()) restore_form_data('nagvis_data', false, $nagvis);

                $tpl = 'set_nagvis_data.tpl';

                $this->assign('nagvis', $nagvis);
                $this->set_form_redir('set_nagvis_data_submit', array('customer_id' => $this->vars['customer_id']));
                $this->assign('error_msg', error_msg());

                $this->display($tpl);
        }


        /** Adds or edit an nagvis account */
        function set_nagvis_data_submit() {
            check_auth(array('customer_id' => $this->vars['customer_id']));
            if(empty($this->vars['customer_id'])) {
            	return $this->mk_redir('manage_customers');
            }
            if(!empty($this->vars['save'])) {
                    $nagvis_data = $this->vars['nagvis'];
                    $nagvis_data['customer_id'] = $this->vars['customer_id'];
                    if(strpos($nagvis_data['url'], 'http://') !== FALSE) {
                        $nagvis_data['protocol'] = 'http://';
                        $nagvis_data['url'] = str_replace('http://', '', $nagvis_data['url']);
                    }
                    if(strpos($nagvis_data['url'], 'https://') !== FALSE) {
                        $nagvis_data['protocol'] = 'https://';
                        $nagvis_data['url'] = str_replace('https://', '', $nagvis_data['url']);
                    }

                    class_load('Nagvis');

	            if (!empty($nagvis_data['id'])) {
	            	$nagvis = new Nagvis($nagvis_data['id']);
	            	unset($nagvis_data['id']);
	            } else {
	            	$nagvis = new Nagvis();
	            }

	            $nagvis->load_from_array($nagvis_data);
	            if($nagvis->is_valid_data()) {
	            	$nagvis->save_data();
	            	save_form_data($nagvis_data, 'nagvis_data');
	            	return $this->mk_redir('set_nagvis_data', array('customer_id' => $this->vars['customer_id']));
	            } else {
	            	save_form_data ($data, 'customer_contact');
	            	return $this->mk_redir('set_nagvis_data', array('customer_id' => $this->vars['customer_id']));
	            }
            } else {
            	if(!empty($this->vars['returl'])) {
                        return $this->vars['returl'];
                } else {
                        return $this->mk_redir('customer_edit', array('id' => $this->vars['customer_id']));
                }
            }
        }


	/** Deletes a customer */
	function customer_delete ()
	{
		check_auth ();
		$customer = new Customer ($this->vars['id']);
		$ret = $this->mk_redir ('manage_customers');
		if ($customer->id)
		{
			if ($customer->can_delete ())
			{
				$customer->delete ();
			}
		}

		return $ret;
	}
        
        /****************************************************************/
	/* Customer contacts						*/
	/****************************************************************/

	/** Displays the page for managing customers contacts */
	function manage_customers_contacts ()
	{
		class_load ('CustomerContact');
		$tpl = 'manage_customers_contacts.tpl';

		$extra_params = array();	// Extra parameters to be carried in navigation
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

		if (isset($this->vars['customer_id']))
		{
			$_SESSION['manage_customers_contacts']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_customers_contacts']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_customers_contacts'];


		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			unset ($_SESSION['manage_customers_contacts']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_customers_contacts']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();

		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);

		// If a customer was selected, fetch the data for that customer
		if ($filter['customer_id'] > 0)
		{
			$customer = new Customer ($filter['customer_id']);
			$contacts = CustomerContact::get_contacts (array('customer_id' => $customer->id));

			$this->assign ('customer', $customer);
			$this->assign ('contacts', $contacts);
		}

		$this->assign ('customers_list', $customers_list);
		$this->assign ('filter', $filter);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->assign ('returl', urlencode($this->mk_redir ('manage_customers_contacts', $extra_params)));
		$this->set_form_redir ('manage_customers_contacts_submit', $extra_params);

		$this->display ($tpl);
	}


	function manage_customers_contacts_submit ()
	{
		check_auth ();
		$extra_params = array();
		$_SESSION['manage_customers_contacts'] = $this->vars['filter'];
		$_SESSION['manage_customers_contacts']['customer_id'] = trim ($_SESSION['manage_customers_contacts']['customer_id']);

		if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
		{
			$extra_params['do_filter'] = 1;
		}

		return $this->mk_redir('manage_customers_contacts', $extra_params);
	}
        
        /** Displays the page for adding a new customer contact */
	function customer_contact_add ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_customers');

		check_auth (array('customer_id' => $customer->id));
		class_load ('CustomerContact');
		$tpl = 'customer_contact_add.tpl';

		$contact = new CustomerContact ();
		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_contact', false, $data);
		$contact->load_from_array ($data, true);

		$params = array ('customer_id' => $customer->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

		$this->assign ('contact', $contact);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_contact_add_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a new contact */
	function customer_contact_add_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array('customer_id' => $customer->id));
		class_load ('CustomerContact');

		if ($this->vars['returl'])
			$ret = urldecode ($this->vars['returl']);
		else
			$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id));

		if ($this->vars['save'] and $customer->id)
		{
			$data = $this->vars['contact'];
			$contact = new CustomerContact ();
			$contact->load_from_array ($data);
			$contact->customer_id = $customer->id;

			if ($contact->is_valid_data ())
			{
				$contact->save_data ();
				$params = array ('id' => $contact->id);
				if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

				$ret = $this->mk_redir ('customer_contact_edit', $params);
			}
			else
			{
				save_form_data ($data, 'customer_contact');
				$params = array ('customer_id' => $customer->id);
				if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

				$ret = $this->mk_redir ('customer_contact_add', $params);
			}
		}

		return $ret;
	}


	/** Displays the page for adding a customer contact */
	function customer_contact_edit ()
	{
		class_load ('CustomerContact');
		$contact = new CustomerContact ($this->vars['id']);
		if (!$contact->id) return $this->mk_redir ('manage_customers');

		check_auth (array('customer_id' => $contact->customer_id));
		$customer = new Customer ($contact->customer_id);
		$tpl = 'customer_contact_edit.tpl';

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_contact', false, $data);
		$contact->load_from_array ($data, true);

		$params = array ('id' => $contact->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

		$this->assign ('contact', $contact);
		$this->assign ('customer', $customer);
		$this->assign ('returl', urlencode($this->vars['returl']));
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_contact_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the information about the customer contact */
	function customer_contact_edit_submit ()
	{
		class_load ('CustomerContact');
		$contact = new CustomerContact ($this->vars['id']);
		check_auth (array('customer_id' => $contact->customer_id));

		if ($this->vars['returl'])
			$ret = urldecode ($this->vars['returl']);
		else
			$ret = $this->mk_redir ('customer_edit', array ('id' => $contact->customer_id));

		if ($this->vars['save'] and $contact->id)
		{
			$data = $this->vars['contact'];
			$contact->load_from_array ($data);

			if ($contact->is_valid_data ())
			{
				$contact->save_data ();
			}
			else
			{
				save_form_data ($data, 'customer_contact');
			}
			$params = array ('id' => $contact->id);
			if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];
			$ret = $this->mk_redir ('customer_contact_edit', $params);
		}

		return $ret;
	}


	/** Deletes a customer contact */
	function customer_contact_delete ()
	{
		class_load ('CustomerContact');
		$contact = new CustomerContact ($this->vars['id']);

		if ($this->vars['returl'])
			$ret = urldecode ($this->vars['returl']);
		else
			$ret = $this->mk_redir ('customer_edit', array ('id' => $contact->customer_id));

		$contact->delete ();

		return $ret;
	}
        
        /****************************************************************/
	/* Customer contacts phones					*/
	/****************************************************************/

	/** Displays the page for adding a new phone number for a customer contact */
	function customer_contact_phone_add ()
	{
		class_load ('CustomerContact');
		$contact = new CustomerContact ($this->vars['contact_id']);
		$customer = new Customer ($contact->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_customers');

		check_auth (array('customer_id' => $customer->id));
		$tpl = 'customer_contact_phone_add.tpl';

		$phone = new CustomerContactPhone ();
		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_contact_phone', false, $data);
		$phone->load_from_array ($data, true);

		$params = array ('contact_id' => $contact->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

		$this->assign ('phone', $phone);
		$this->assign ('contact', $contact);
		$this->assign ('customer', $customer);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_contact_phone_add_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a new phone number for a contact */
	function customer_contact_phone_add_submit ()
	{
		class_load ('CustomerContact');
		$contact = new CustomerContact ($this->vars['contact_id']);
		check_auth (array('customer_id' => $contact->customer_id));

		$params = array ('id' => $contact->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];
		$ret = $this->mk_redir ('customer_contact_edit', $params);

		if ($this->vars['save'] and $contact->id)
		{
			$data = $this->vars['phone'];
			$phone = new CustomerContactPhone ();
			$phone->load_from_array ($data);
			$phone->contact_id = $contact->id;

			if ($phone->is_valid_data ())
			{
				$phone->save_data ();
				$params = array ('id' => $phone->id);
				if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

				$ret = $this->mk_redir ('customer_contact_phone_edit', $params);
			}
			else
			{
				save_form_data ($data, 'customer_contact_phone');
				$params = array ('contact_id' => $contact->id);
				if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

				$ret = $this->mk_redir ('customer_contact_phone_add', $params);
			}
		}

		return $ret;
	}


	/** Displays the page for editing the phone number for a contact */
	function customer_contact_phone_edit ()
	{
		class_load ('CustomerContact');
		$tpl = 'customer_contact_phone_edit.tpl';
		$phone = new CustomerContactPhone ($this->vars['id']);
		$contact = new CustomerContact ($phone->contact_id);
		$customer = new Customer ($contact->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_customers');
		check_auth (array('customer_id' => $customer->id));

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_contact_phone', false, $data);
		$phone->load_from_array ($data, true);

		$params = array ('id' => $phone->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

		$this->assign ('phone', $phone);
		$this->assign ('contact', $contact);
		$this->assign ('customer', $customer);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_contact_phone_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the modifications to the phone number for a customer contact */
	function customer_contact_phone_edit_submit ()
	{
		class_load ('CustomerContact');
		$phone = new CustomerContactPhone ($this->vars['id']);
		$contact = new CustomerContact ($phone->contact_id);
		check_auth (array('customer_id' => $contact->customer_id));

		$params = array ('id' => $contact->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];
		$ret = $this->mk_redir ('customer_contact_edit', $params);

		if ($this->vars['save'] and $phone->id)
		{
			$data = $this->vars['phone'];
			$phone->load_from_array ($data);

			if ($phone->is_valid_data ())
			{
				$phone->save_data ();
			}
			else
			{
				save_form_data ($data, 'customer_contact_phone');
			}
			$params = array ('id' => $phone->id);
			if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];
			$ret = $this->mk_redir ('customer_contact_phone_edit', $params);
		}

		return $ret;
	}


	/** Deletes a phone number from a contact */
	function customer_contact_phone_delete ()
	{
		class_load ('CustomerContact');
		$phone = new CustomerContactPhone ($this->vars['id']);
		$contact = new CustomerContact ($phone->contact_id);
		check_auth (array('customer_id' => $contact->customer_id));

		$params = array ('id' => $contact->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];
		$ret = $this->mk_redir ('customer_contact_edit', $params);

		if ($phone->id) $phone->delete ();

		return $ret;
	}
        
        /****************************************************************/
	/* Customer comments						*/
	/****************************************************************/

	/** Displays the page for managing customers contacts */
	function manage_customers_comments ()
	{
		class_load ('CustomerComment');
		$tpl = 'manage_customers_comments.tpl';

		$extra_params = array();	// Extra parameters to be carried in navigation
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

		if (isset($this->vars['customer_id']))
		{
			$_SESSION['manage_customers_comments']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_customers_comments']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_customers_comments'];


		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			unset ($_SESSION['manage_customers_comments']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_customers_comments']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();

		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);

		// If a customer was selected, fetch the data for that customer
		if ($filter['customer_id'] > 0)
		{
			$customer = new Customer ($filter['customer_id']);
			$comments = CustomerComment::get_comments (array('customer_id' => $customer->id));

			// Populate the user information
			for ($i=0; $i<count($comments); $i++)
			{
				$comments[$i]->user = new User ($comments[$i]->user_id);
			}

			$this->assign ('customer', $customer);
			$this->assign ('comments', $comments);
		}

		$this->assign ('customers_list', $customers_list);
		$this->assign ('filter', $filter);
		$this->assign ('error_msg', error_msg ());
		$this->assign ('returl', urlencode($this->mk_redir ('manage_customers_comments', $extra_params)));
		$this->set_form_redir ('manage_customers_comments_submit', $extra_params);

		$this->display ($tpl);
	}


	function manage_customers_comments_submit ()
	{
		check_auth ();
		$extra_params = array();
		$_SESSION['manage_customers_comments'] = $this->vars['filter'];
		$_SESSION['manage_customers_comments']['customer_id'] = trim ($_SESSION['manage_customers_comments']['customer_id']);

		if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
		{
			$extra_params['do_filter'] = 1;
		}

		return $this->mk_redir('manage_customers_comments', $extra_params);
	}
        
        function customer_comment_add ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_customers');

		check_auth (array('customer_id' => $customer->id));
		class_load ('CustomerComment');
		$tpl = 'customer_comment_add.tpl';

		$comment = new CustomerComment ();
		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_comment', false, $data);
		$comment->load_from_array ($data, true);

		$params = array ('customer_id' => $customer->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

		$this->assign ('comment', $comment);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_comment_add_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a new customer comment */
	function customer_comment_add_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array('customer_id' => $customer->id));
		class_load ('CustomerComment');

		if ($this->vars['returl'])
			$ret = urldecode ($this->vars['returl']);
		else
			$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id));

		if ($this->vars['save'] and $customer->id)
		{
			$data = $this->vars['comment'];
			$comment = new CustomerComment ();
			$comment->load_from_array ($data);
			$comment->customer_id = $customer->id;
			$comment->created = time ();
			$comment->user_id = get_uid ();

			if ($comment->is_valid_data ())
			{
				$comment->save_data ();
				$params = array ('id' => $comment->id);
				if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

				$ret = $this->mk_redir ('customer_comment_edit', $params);
			}
			else
			{
				save_form_data ($data, 'customer_comment');
				$params = array ('customer_id' => $customer->id);
				if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

				$ret = $this->mk_redir ('customer_comment_add', $params);
			}
		}

		return $ret;
	}


	/** Displays the page for adding a customer comment */
	function customer_comment_edit ()
	{
		class_load ('CustomerComment');
		$comment = new CustomerComment ($this->vars['id']);
		if (!$comment->id) return $this->mk_redir ('manage_customers');

		check_auth (array('customer_id' => $comment->customer_id));
		$customer = new Customer ($comment->customer_id);
		$tpl = 'customer_comment_edit.tpl';

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_comment', false, $data);
		$comment->load_from_array ($data, true);

		$params = array ('id' => $comment->id);
		if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];

		$this->assign ('comment', $comment);
		$this->assign ('customer', $customer);
		$this->assign ('returl', urlencode($this->vars['returl']));
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_comment_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the information about the customer comment */
	function customer_comment_edit_submit ()
	{
		class_load ('CustomerComment');
		$comment = new CustomerComment ($this->vars['id']);
		check_auth (array('customer_id' => $comment->customer_id));

		if ($this->vars['returl'])
			$ret = urldecode ($this->vars['returl']);
		else
			$ret = $this->mk_redir ('customer_edit', array ('id' => $comment->customer_id));

		if ($this->vars['save'] and $comment->id)
		{
			$data = $this->vars['comment'];
			$comment->load_from_array ($data);

			if ($comment->is_valid_data ())
			{
				$comment->save_data ();
			}
			else
			{
				save_form_data ($data, 'customer_comment');
			}
			$params = array ('id' => $comment->id);
			if ($this->vars['returl']) $params['returl'] = $this->vars['returl'];
			$ret = $this->mk_redir ('customer_comment_edit', $params);
		}

		return $ret;
	}


	/** Deletes a customer comment */
	function customer_comment_delete ()
	{
		class_load ('CustomerComment');
		$comment = new CustomerComment ($this->vars['id']);

		if ($this->vars['returl'])
			$ret = urldecode ($this->vars['returl']);
		else
			$ret = $this->mk_redir ('customer_edit', array ('id' => $comment->customer_id));

		$comment->delete ();

		return $ret;
	}
        
        /****************************************************************/
	/* Customers photos						*/
	/****************************************************************/

	/** Displays the page for managing customers photos */
	function manage_customers_photos ()
	{
		class_load ('CustomerPhoto');
		class_load ('Computer');
		class_load ('Peripheral');
		class_load ('Location');
		$tpl = 'manage_customers_photos.tpl';

		$extra_params = array();	// Extra parameters to be carried in navigation
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

		if (isset($this->vars['customer_id']))
		{
			$_SESSION['manage_customers_photos']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_customers_photos']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_customers_photos'];

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			unset ($_SESSION['manage_customers_photos']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_customers_photos']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();

		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);

		// If a customer was selected, fetch the data for that customer
		if ($filter['customer_id'] > 0)
		{
			$customer = new Customer ($filter['customer_id']);
			$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true));
			$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $customer->id, 'append_id' => true));
			$locations_list = Location::get_locations_list (array('customer_id' => $customer_id));

			$photos = CustomerPhoto::get_photos (array('customer_id' => $customer->id));

			$this->assign ('customer', $customer);
			$this->assign ('photos', $photos);
			$this->assign ('computers_list', $computers_list);
			$this->assign ('peripherals_list', $peripherals_list);
			$this->assign ('locations_list', $locations_list);
		}

		$this->assign ('customers_list', $customers_list);
		$this->assign ('filter', $filter);
		$this->assign ('PHOTO_OBJECT_CLASSES', $GLOBALS['PHOTO_OBJECT_CLASSES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('manage_customers_photos_submit', $extra_params);

		$this->display ($tpl);
	}


	function manage_customers_photos_submit ()
	{
		check_auth ();
		$extra_params = array();
		$_SESSION['manage_customers_photos'] = $this->vars['filter'];
		$_SESSION['manage_customers_photos']['customer_id'] = trim ($_SESSION['manage_customers_photos']['customer_id']);

		if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
		{
			$extra_params['do_filter'] = 1;
		}

		return $this->mk_redir('manage_customers_photos', $extra_params);
	}
        
        /** Re-sizes all images to the max allowed size (if they're bigger) and regenerates all the thumbnails */
	function customer_photos_regenerate_all ()
	{
		class_load ('CustomerPhoto');
		check_auth ();
		$photos = CustomerPhoto::get_photos ();

		$objects = 0;
		$missing_pics = 0;
		for ($i=0; $i<count ($photos); $i++)
		{
		echo $photos[$i]->id.' '.$photos[$i]->local_filename;



			if ($photos[$i]->get_full_path ())
			{
				$objects++;
				$photos[$i]->resize_image ();
				$photos[$i]->generate_thumbnail ();
				echo " <b>OK</b> ";
			}
			else $missing_pics++;

			echo '<br>';
		}

		echo "<b>Objects: $objects ; Missing: $missing_pics ";
	}

	/** Displays the page for uploading a new customer photo */
	function customer_photo_add ()
	{
		class_load ('CustomerPhoto');
		class_load ('Computer');
		class_load ('Peripheral');
		class_load ('Location');

		$photo = new CustomerPhoto ();
		if ($this->vars['customer_id'])
		{
			$customer = new Customer ($this->vars['customer_id']);
		}
		elseif ($this->vars['computer_id'])
		{
			$computer = new Computer ($this->vars['computer_id']);
			$photo->object_class = PHOTO_OBJECT_CLASS_COMPUTER;
			$photo->object_id = $computer->id;
			$customer = new Customer ($computer->customer_id);
		}
		elseif ($this->vars['peripheral_id'])
		{
			$peripheral = new Peripheral ($this->vars['peripheral_id']);
			$photo->object_class = PHOTO_OBJECT_CLASS_PERIPHERAL;
			$photo->object_id = $peripheral->id;
			$customer = new Customer ($peripheral->customer_id);
		}
		elseif ($this->vars['location_id'])
		{
			$location = new Location ($this->vars['location_id']);
			$photo->object_class = PHOTO_OBJECT_CLASS_LOCATION;
			$photo->object_id = $location->id;
			$customer = new Customer ($location->customer_id);
		}

		if (!$customer->id) return $this->mk_redir ('manage_customers_photos');
		check_auth (array('customer_id' => $customer->id));
		$tpl = 'customer_photo_add.tpl';


		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_photo', false, $data);
		if ($this->vars['photo']['original_filename']) $data['name'] = $this->vars['photo']['original_filename'];
		if ($this->vars['photo']['local_filename']) $data['local_filename'] = $this->vars['photo']['local_filename'];
		$photo->load_from_array ($data, true);

		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true));
		$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $customer->id, 'append_id' => true));
		$locations_list = Location::get_locations_list (array('customer_id' => $customer_id, 'indent' => true));

		$params = $this->set_carry_fields (array('customer_id', 'computer_id', 'peripheral_id', 'returl'));
		if ($data['local_filename']) $params['photo[local_filename]'] = $data['local_filename'];
		if ($data['name']) $params['photo[name]'] = $data['name'];

		$this->assign ('customer', $customer);
		$this->assign ('photo', $photo);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('peripherals_list', $peripherals_list);
		$this->assign ('locations_list', $locations_list);
		$this->assign ('PHOTO_OBJECT_CLASSES', $GLOBALS['PHOTO_OBJECT_CLASSES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_photo_add_submit', $params);

		$this->display ($tpl);
	}


	/** Adds the new photo */
	function customer_photo_add_submit ()
	{
		class_load ('CustomerPhoto');
		class_load ('Computer');
		class_load ('Peripheral');
		class_load ('Location');

		$photo = new CustomerPhoto ();
		if ($this->vars['customer_id'])
		{
			$customer = new Customer ($this->vars['customer_id']);
		}
		elseif ($this->vars['computer_id'])
		{
			$computer = new Computer ($this->vars['computer_id']);
			$photo->object_class = PHOTO_OBJECT_CLASS_COMPUTER;
			$photo->object_id = $computer->id;
			$customer = new Customer ($computer->customer_id);
		}
		elseif ($this->vars['peripheral_id'])
		{
			$peripheral = new Peripheral ($this->vars['peripheral_id']);
			$photo->object_class = PHOTO_OBJECT_CLASS_PERIPHERAL;
			$photo->object_id = $peripheral->id;
			$customer = new Customer ($peripheral->customer_id);
		}
		elseif ($this->vars['location_id'])
		{
			$location = new Location ($this->vars['location_id']);
			$photo->object_class = PHOTO_OBJECT_CLASS_LOCATION;
			$photo->object_id = $location->id;
			$customer = new Customer ($location->customer_id);
		}
		check_auth (array('customer_id' => $customer->id));

		$params = $this->set_carry_fields (array('returl'));
		$data = $this->vars['photo'];

		if ($this->vars['save'] and $customer->id)
		{
			$data['customer_id'] = $customer->id;
			if ($data['object_class'] == PHOTO_OBJECT_CLASS_COMPUTER) $data['object_id'] = $data['computer_id'];
			elseif ($data['object_class'] == PHOTO_OBJECT_CLASS_PERIPHERAL) $data['object_id'] = $data['peripheral_id'];
			elseif ($data['object_class'] == PHOTO_OBJECT_CLASS_LOCATION) $data['object_id'] = $data['location_id'];
			else $data['object_class'] = $data['object_id'] = 0;

			if ($_FILES['photo_file']['name'])
			{
				$data['name'] = $_FILES['photo_file']['name'];
				$data['tmp_name'] = $_FILES['photo_file']['tmp_name'];
			}

			$photo = new CustomerPhoto ();
			$photo->load_from_array ($data);

			if ($photo->is_valid_data ())
			{
				$photo->save_data ();
				$params['id'] = $photo->id;
				$ret = $this->mk_redir ('customer_photo_edit', $params);
			}
			else
			{
				$params = $this->set_carry_fields (array('returl', 'customer_id', 'computer_id', 'peripheral_id', 'location_id'));
				// If a file has been uploaded, make sure to keep track of the file
				if ($photo->local_filename)
				{
					$data['local_filename'] = $photo->local_filename;
					$params['photo[original_filename]'] = $photo->original_filename;
					$params['photo[local_filename]'] = $photo->local_filename;
				}

				$ret = $this->mk_redir ('customer_photo_add', $params);
				save_form_data ($data, 'customer_photo');
			}
		}
		else
		{
			// Make sure to delete any uploaded files
			if ($data['local_filename'] and file_exists(DIR_UPLOAD_CUSTOMER.'/'.$data['local_filename']))
			{
				@unlink (DIR_UPLOAD_CUSTOMER.'/'.$data['local_filename']);
			}
			if ($data['local_filename'] and file_exists(DIR_UPLOAD_CUSTOMER.'/'.$data['local_filename'].THUMBNAIL_SUFFIX))
			{
				@unlink (DIR_UPLOAD_CUSTOMER.'/'.$data['local_filename'].THUMBNAIL_SUFFIX);
			}

			if ($this->vars['returl']) $ret = $this->vars['returl'];
			else $ret = $this->mk_redir ('manage_customers_photots');
		}

		return $ret;
	}


	/** Displays the page for editing a customer photo */
	function customer_photo_edit ()
	{
		class_load ('CustomerPhoto');
		class_load ('Computer');
		class_load ('Peripheral');
		class_load ('Location');

		$photo = new CustomerPhoto ($this->vars['id']);
		$customer = new Customer ($photo->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_customers_photos');

		check_auth (array('customer_id' => $customer->id));
		$tpl = 'customer_photo_edit.tpl';

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_photo', false, $data);
		$photo->load_from_array ($data, true);

		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true));
		$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $customer->id, 'append_id' => true));
		$locations_list = Location::get_locations_list (array('customer_id' => $customer_id, 'indent' => true));

		$params = $this->set_carry_fields (array('id', 'returl'));

		$this->assign ('customer', $customer);
		$this->assign ('photo', $photo);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('peripherals_list', $peripherals_list);
		$this->assign ('locations_list', $locations_list);
		$this->assign ('PHOTO_OBJECT_CLASSES', $GLOBALS['PHOTO_OBJECT_CLASSES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_photo_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a customer photo */
	function customer_photo_edit_submit ()
	{
		class_load ('CustomerPhoto');
		$photo = new CustomerPhoto ($this->vars['id']);
		check_auth (array('customer_id' => $photo->customer_id));

		$params = $this->set_carry_fields (array('returl'));

		if ($this->vars['save'] and $photo->id)
		{
			$data = $this->vars['photo'];
			if ($data['object_class'] == PHOTO_OBJECT_CLASS_COMPUTER) $data['object_id'] = $data['computer_id'];
			elseif ($data['object_class'] == PHOTO_OBJECT_CLASS_PERIPHERAL) $data['object_id'] = $data['peripheral_id'];
			elseif ($data['object_class'] == PHOTO_OBJECT_CLASS_LOCATION) $data['object_id'] = $data['location_id'];
			else $data['object_class'] = $data['object_id'] = 0;

			if ($_FILES['photo_file']['name'])
			{
				// A new picture has been uploaded
				$data['name'] = $_FILES['photo_file']['name'];
				$data['tmp_name'] = $_FILES['photo_file']['tmp_name'];
			}

			$photo->load_from_array ($data);
			if ($photo->is_valid_data ())
			{
				$photo->save_data ();
			}
			else
			{
				save_form_data ($data, 'customer_photo');
			}
			$params['id'] = $photo->id;
			$ret = $this->mk_redir ('customer_photo_edit', $params);
		}
		else
		{
			if ($this->vars['returl']) $ret = $this->vars['returl'];
			else $ret = $this->mk_redir ('manage_customers_photos');
		}

		return $ret;
	}


	/** Deletes a customer image */
	function customer_photo_delete ()
	{
		class_load ('CustomerPhoto');
		$photo = new CustomerPhoto ($this->vars['id']);

		if ($this->vars['returl']) $ret = $this->vars['returl'];
		else $ret = $this->mk_redir ('manage_customers_photos');

		if ($photo->id)
		{
			$photo->delete ();
		}

		return $ret;
	}

	/** Displays a customer photo in a stand-alone page. */
	function customer_photo_view ()
	{
		class_load ('CustomerPhoto');
		class_load ('Computer');
		class_load ('Peripheral');
		class_load ('Location');
		$photo = new CustomerPhoto ($this->vars['id']);
		$tpl = 'customer_photo_view.tpl';
		check_auth (array('customer_id' => $photo->customer_id));

		if (!$photo->id) return $this->mk_redir ('manage_customers_photos');

		$customer = new Customer ($photo->customer_id);

		$ret_url = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_customers_photos'));

		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true));
		$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $customer->id, 'append_id' => true));
		$locations_list = Location::get_locations_list (array('customer_id' => $customer->id));

		$this->assign ('photo', $photo);
		$this->assign ('customer', $customer);
		$this->assign ('ret_url', urlencode($ret_url));
		$this->assign ('computers_list', $computers_list);
		$this->assign ('peripherals_list', $peripherals_list);
		$this->assign ('locations_list', $locations_list);
		$this->assign ('PHOTO_OBJECT_CLASSES', $GLOBALS['PHOTO_OBJECT_CLASSES']);
		$this->assign ('error_msg', error_msg());

		$this->display ($tpl);
	}


	/** Displays a customer photo. IMPORTANT NOTE: this should be called from the SRC attribute of an IMG tag */
	function customer_photo_show ()
	{
		check_auth ();
		class_load ('CustomerPhoto');

		$photo = null;
		if ($this->vars['id'])
		{
			$photo = new CustomerPhoto ($this->vars['id']);
			check_auth (array('customer_id' => $photo->customer_id));
			if ($this->vars['thumb']) $path = $photo->get_thumb_full_path ();
			else $path = $photo->get_full_path ();
		}
		elseif ($this->vars['tmp_name'])
		{
			$photo = new CustomerPhoto ();
			$photo->local_filename = $this->vars['tmp_name'];
			if ($this->vars['thumb']) $path = $photo->get_thumb_full_path ();
			else $path = $photo->get_full_path ();
			if (!$photo->original_filename) $photo->original_filename = $this->vars['orig_name'];
		}

		if ($path)
		{
			header ("Pragma: public");
			header ("Cache-Control: no-store, no-cache, must_revalidate");
			header ("Content-Transfer-Encoding: none");
			header ("Connection: close");

			if (!$photo->is_image ())
			{
				// We are serving a non-image file, we should instruct a download
				header("Pragma: public");
				header("Expires: 0"); // set expiration time
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: ".filesize($path));
				header("Content-Disposition: attachment; filename=\"".strip_tags($photo->original_filename)."\"");
				header("Content-Transfer-Encoding: binary");
			}

			readfile($path);

		}

		die;
	}
        
        /****************************************************************/
	/* Locking customers						*/
	/****************************************************************/

	/** Displays the page for locking a customer */
	function customer_lock ()
	{
		check_auth ();
		$tpl = 'customer_lock.tpl';

		$filter = array('favorites_first' => true, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($filter);

		if ($this->vars['ret'])
			$ret = $this->vars['ret'];
		else
			$ret = $_SERVER['HTTP_REFERER'];
                /*
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
                */
		$preselect_id = ($_SESSION['potential_lock_customer_id'] ? $_SESSION['potential_lock_customer_id'] : $_SESSION['locked_customer_id']);

		$this->assign ('customers_list', $customers_list);
		$this->assign ('preselect_id', $preselect_id);
		$this->set_form_redir ('customer_lock_submit', array ('ret' => $ret));
		$this->assign ('error_msg', error_msg());

		$this->display ($tpl);
	}


	/** Locks the customer */
	function customer_lock_submit ()
	{
		check_auth ();

		if ($this->vars['ret'])
			$ret = $this->vars['ret'];
		else
			$ret = $this->mk_redir ('customer_lock');
		// Just in case
		if (!$ret) $ret = $this->mk_redir ('customer_lock');

		if ($this->vars['lock'] and $this->vars['customer_id'])
		{
			$_SESSION['locked_customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->vars['lock'] and !$this->vars['customer_id'])
		{
			error_msg ('Please select a customer');
			$ret = $this->mk_redir ('customer_lock', array ('ret' => $ret));
		}
		elseif ($this->vars['cancel'])
		{
			$ret = $this->mk_redir ('customer_unlock', array ('ret' => $ret));
		}

		return $ret;
	}


	function customer_unlock ()
	{
		check_auth ();

		if ($this->vars['ret'])
			$ret = $this->vars['ret'];
		else
			$ret = $_SERVER['HTTP_REFERER'];

		// Just in case
		if (!$ret) $ret = $this->mk_redir ('customer_lock');

		unset ($_SESSION['locked_customer_id']);

		return $ret;

	}
        
        /****************************************************************/
	/* Notifications logs						*/
	/****************************************************************/

	/** Displays the page for consulting the notifications logs */
	function manage_notifications_logs ()
	{
		class_load ('Notification');
		class_load ('Computer');
		class_load ('Customer');
		class_load ('MonitoredIP');
		class_load ('CustomerInternetContract');
		class_load ('SoftwareLicense');
		$tpl = 'manage_notifications_logs.tpl';

		$extra_params = array();	// Extra parameters to be carried in navigation
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

		if (isset($this->vars['customer_id']))
		{
			$_SESSION['manage_notifications_logs']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_notifications_logs']['customer_id'] = $this->locked_customer->id;
		}
		if ($this->vars['computer_id'])
		{
			$_SESSION['manage_notifications_logs']['computer_id'] = $this->vars['computer_id'];
			$computer = new Computer ($this->vars['computer_id']);
			$_SESSION['manage_notifications_logs']['customer_id'] = $computer->customer_id;
		}

		$filter = $_SESSION['manage_notifications_logs'];
		if (!isset($filter['month_start'])) $filter['month_start'] = date('Y_m');
		if (!isset($filter['month_end'])) $filter['month_end'] = date('Y_m');

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			unset ($_SESSION['manage_notifications_logs']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_notifications_logs']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();

		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);

		if (!$filter['show']) $filter['show'] = 1;
		if (!$filter['customer_id']) $filter['show'] = 1;

		// If a customer was selected, fetch the data for that customer
		if ($filter['customer_id'] > 0)
		{
			$customer = new Customer ($filter['customer_id']);
			$notifications = Notification::get_notifications_log ($filter);
			$log_months = Notification::get_log_months ();

			$computers_list = $computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true));
			$tickets_list = Ticket::get_tickets_list (array('customer_id' => $customer->id));
			$monitored_ips_list = MonitoredIP::get_monitored_ips_list (array('customer_id' => $customer->id));
			$internet_contracts_lists = CustomerInternetContract::get_contracts_list (array('customer_id' => $customer->id));
			$software_list = SoftwareLicense::get_customer_software_list ($customer->id);

			$this->assign ('customer', $customer);
			$this->assign ('notifications', $notifications);
			$this->assign ('log_months', $log_months);
			$this->assign ('computers_list', $computers_list);
			$this->assign ('tickets_list', $tickets_list);
			$this->assign ('monitored_ips_list', $monitored_ips_list);
			$this->assign ('internet_contracts_lists', $internet_contracts_lists);
			$this->assign ('software_list', $software_list);
		}

		$this->assign ('customers_list', $customers_list);
		$this->assign ('filter', $filter);
		$this->assign ('error_msg', error_msg ());
		$this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
		//$this->assign ('returl', urlencode($this->mk_redir ('manage_customers_contacts', $extra_params)));
		$this->set_form_redir ('manage_notifications_logs_submit', $extra_params);

		$this->display ($tpl);
	}


	function manage_notifications_logs_submit ()
	{
		check_auth ();

		$extra_params = array();
		$_SESSION['manage_notifications_logs'] = $this->vars['filter'];

		if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
		{
			$extra_params['do_filter'] = 1;
		}

		return $this->mk_redir('manage_notifications_logs', $extra_params);
	}
        
        /****************************************************************/
	/* Messages log							*/
	/****************************************************************/
	/** Displays the page for consulting the notifications logs */
	function manage_messages_logs ()
	{
		class_load ('Notification');
		class_load ('MessageLog');

		$tpl = 'manage_messages_logs.tpl';

		$extra_params = array();	// Extra parameters to be carried in navigation
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

		if (isset($this->vars['customer_id']))
		{
			$_SESSION['manage_messages_logs']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_messages_logs']['customer_id'] = $this->locked_customer->id;
		}

		$filter = $_SESSION['manage_messages_logs'];
		if (!isset($filter['month'])) $filter['month'] = date('Y_m');
		if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
		if (!isset($filter['limit'])) $filter['limit'] = 50;

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			unset ($_SESSION['manage_messages_logs']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_messages_logs']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();

		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);

		// Get the list of months for which we have messages logs
		$log_months = MessageLog::get_messages_log_months ();

		// Fetch the messages according to current filtering
		$messages_count = 0;
		$messages = MessageLog::get_messages_log ($filter, $messages_count);
		if ($messages_count < $filter['start'])
		{
			$filter['start'] = 0;
			$messages = MessageLog::get_messages_log ($filter, $messages_count);
		}
		$pages = make_paging ($filter['limit'], $messages_count);

		$users_list = User::get_users_list ();

		$this->assign ('messages', $messages);
		$this->assign ('filter', $filter);
		$this->assign ('messages_count', $messages_count);
		$this->assign ('pages', $pages);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('users_list', $users_list);
		$this->assign ('log_months', $log_months);
		$this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
		$this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('manage_messages_logs_submit', $extra_params);

		$this->display ($tpl);
	}


	/** Save the filtering criteria for the messages logs */
	function manage_messages_logs_submit ()
	{
		check_auth ();
		$extra_params = array();

		$filter = $this->vars['filter'];
		if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
		{
			$extra_params['do_filter'] = 1;
		}
		if ($this->vars['go'] == 'prev') $filter['start']-= $filter['limit'];
		elseif ($this->vars['go'] == 'next') $filter['start']+= $filter['limit'];

		$_SESSION['manage_messages_logs'] = $filter;
		return $this->mk_redir('manage_messages_logs', $extra_params);
	}

	/** Shows the pop-up window with a specific message sent to a customer */
	function popup_log_message ()
	{
		check_auth ();
		class_load ('MessageLog');
		$tpl = 'popup_log_message.tpl';

		$message = new MessageLog ($this->vars['id'], $this->vars['month']);
		if (!$message->id) error_msg ('There is no such message in the logs');
		{
			if ($message->user_id) $user = new User ($message->user_id);
			if ($message->customer_id) $customer = new Customer ($message->customer_id);
		}

		$this->assign ('message', $message);
		$this->assign ('customer', $customer);
		$this->assign ('user', $user);
		$this->assign ('error_msg', error_msg ());
		$this->display_template_limited ($tpl);
	}
        
        /****************************************************************/
	/* Suppliers management						*/
	/****************************************************************/

	/** Displays the page for managing suppliers */
	function manage_suppliers ()
	{
		check_auth ();
		class_load ('Supplier');
		$tpl = 'manage_suppliers.tpl';

		$suppliers = Supplier::get_suppliers (array('load_service_packages' => true));
		$service_levels = ServiceLevel::get_service_levels ();

		$this->assign ('suppliers', $suppliers);
		$this->assign ('service_levels', $service_levels);
		$this->assign ('error_msg', error_msg ());

		$this->display ($tpl);
	}
        
        /** Displays the page for defining a new supplier */
	function supplier_add ()
	{
		check_auth ();
		class_load ('Supplier');
		$tpl = 'supplier_add.tpl';

		$supplier = new Supplier ();

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('supplier', false, $data);
		$supplier->load_from_array ($data, true);

		$this->assign ('supplier', $supplier);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('supplier_add_submit');

		$this->display ($tpl);
	}


	/** Saves the new supplier */
	function supplier_add_submit ()
	{
		check_auth ();
		class_load ('Supplier');

		$ret = $this->mk_redir ('manage_suppliers');

		if ($this->vars['save'])
		{
			$data = $this->vars['supplier'];
			$supplier = new Supplier ();
			$supplier->load_from_array ($data);

			if ($supplier->is_valid_data ())
			{
				$supplier->save_data ();
				$ret = $this->mk_redir ('supplier_edit', array ('id' => $supplier->id));
			}
			else
			{
				save_form_data ($data, 'supplier');
				$ret = $this->mk_redir ('supplier_add');
			}
		}

		return $ret;
	}


	/** Displays the page for editing a supplier */
	function supplier_edit ()
	{
		check_auth ();
		class_load ('Supplier');
		$tpl = 'supplier_edit.tpl';

		$supplier = new Supplier ($this->vars['id']);
		if (!$supplier->id) return $this->mk_redir ('manage_suppliers');

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('supplier', false, $data);
		$supplier->load_from_array ($data, true);

		$supplier->load_service_packages ();

		$params = $this->set_carry_fields (array('id'));

		$this->assign ('supplier', $supplier);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('supplier_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a supplier */
	function supplier_edit_submit ()
	{
		check_auth ();
		class_load ('Supplier');
		$supplier = new Supplier ($this->vars['id']);
		$ret = $this->mk_redir ('manage_suppliers');

		$params = $this->set_carry_fields (array('id'));

		if ($this->vars['save'] and $supplier->id)
		{
			$data = $this->vars['supplier'];
			$supplier->load_from_array ($data);

			if ($supplier->is_valid_data ())
			{
				$supplier->save_data ();
			}
			else
			{
				save_form_data ($data, 'supplier');
			}
			$ret = $this->mk_redir ('supplier_edit', $params);
		}

		return $ret;
	}


	/** Deletes a supplier */
	function supplier_delete ()
	{
		check_auth ();
		class_load ('Supplier');
		$supplier = new Supplier ($this->vars['id']);
		$ret = $this->mk_redir ('manage_suppliers');

		if ($supplier->id and $supplier->can_delete ())
		{
			$supplier->delete ();
		}
		return $ret;
	}
        
        /** Displays the page for adding a new supplier service package */
	function service_package_add ()
	{
		check_auth ();
		class_load ('Supplier');
		$tpl = 'service_package_add.tpl';

		$supplier = new Supplier ($this->vars['supplier_id']);
		if (!$supplier->id) return $this->mk_redir ('manage_suppliers');
		$package = new SupplierServicePackage ();

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('package', false, $data);
		$package->load_from_array ($data, true);
		$package->supplier_id = $supplier->id;

		$params = $this->set_carry_fields (array('supplier_id'));

		$this->assign ('supplier', $supplier);
		$this->assign ('package', $package);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('service_package_add_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a new supplier service package */
	function service_package_add_submit ()
	{
		check_auth ();
		class_load ('Supplier');
		$supplier = new Supplier ($this->vars['supplier_id']);

		$params = $this->set_carry_fields (array('supplier_id'));
		$ret = $this->mk_redir ('supplier_edit', array ('id' => $supplier->id));

		if ($this->vars['save'] and $supplier->id)
		{
			$data = $this->vars['package'];
			$package = new SupplierServicePackage ();
			$package->load_from_array ($data);
			$package->supplier_id = $supplier->id;

			if ($package->is_valid_data ())
			{
				$package->save_data ();
				unset ($params['supplier_id']);
				$params['id'] = $package->id;
				$ret = $this->mk_redir ('service_package_edit', $params);
			}
			else
			{
				save_form_data ($data, 'package');
				$ret = $this->mk_redir ('service_package_add', $params);
			}
		}
		return $ret;
	}


	/** Displays the page for editing a supplier service package */
	function service_package_edit ()
	{
		check_auth ();
		class_load ('Supplier');
		$tpl = 'service_package_edit.tpl';

		$package = new SupplierServicePackage ($this->vars['id']);
		$supplier = new Supplier ($package->supplier_id);
		if (!$package->id) return $this->mk_redir ('supplier_edit', array('id' => $supplier_id));


		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('package', false, $data);
		$package->load_from_array ($data, true);

		$params = $this->set_carry_fields (array('id'));

		$this->assign ('supplier', $supplier);
		$this->assign ('package', $package);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('service_package_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a supplier service package */
	function service_package_edit_submit ()
	{
		check_auth ();
		class_load ('Supplier');
		$package = new SupplierServicePackage ($this->vars['id']);

		$params = $this->set_carry_fields (array('id'));
		$ret = $this->mk_redir ('supplier_edit', array ('id' => $package->supplier_id));

		if ($this->vars['save'] and $package->id)
		{
			$data = $this->vars['package'];
			$package->load_from_array ($data);

			if ($package->is_valid_data ())
			{
				$package->save_data ();
			}
			else
			{
				save_form_data ($data, 'package');
			}
			$ret = $this->mk_redir ('service_package_edit', $params);
		}
		return $ret;
	}


	/** Deletes a service package */
	function service_package_delete ()
	{
		check_auth ();
		class_load ('Supplier');
		$package = new SupplierServicePackage ($this->vars['id']);

		$ret = $this->mk_redir ('supplier_edit', array ('id' => $package->supplier_id));

		if ($package->id and $package->can_delete ())
		{
			$package->delete ();
		}
		return $ret;
	}
        
        /** Displays the page for defining a new service level */
	function service_level_add ()
	{
		check_auth ();
		class_load ('Supplier');
		$tpl = 'service_level_add.tpl';

		$level = new ServiceLevel ();

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('service_level', false, $data);
		$level->load_from_array ($data, true);

		$this->assign ('level', $level);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('service_level_add_submit');

		$this->display ($tpl);
	}


	/** Saves a new service level definition */
	function service_level_add_submit ()
	{
		check_auth ();
		class_load ('Supplier');

		$ret = $this->mk_redir ('manage_suppliers');

		if ($this->vars['save'])
		{
			$data = $this->vars['level'];
			$level = new ServiceLevel ();
			$level->load_from_array ($data);

			if ($level->is_valid_data ())
			{
				$level->save_data ();
				$ret = $this->mk_redir ('service_level_edit', array ('id' => $level->id));
			}
			else
			{
				save_form_data ($data, 'service_level');
				$ret = $this->mk_redir ('service_level_add');
			}
		}
		return $ret;
	}


	/** Displays the page for editing a service level */
	function service_level_edit ()
	{
		check_auth ();
		class_load ('Supplier');
		$tpl = 'service_level_edit.tpl';

		$level = new ServiceLevel ($this->vars['id']);
		if (!$level->id) return $this->mk_redir ('manage_suppliers');

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('service_level', false, $data);
		$level->load_from_array ($data, true);

		$params = $this->set_carry_fields (array('id'));

		$this->assign ('level', $level);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('service_level_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a service level definition */
	function service_level_edit_submit ()
	{
		check_auth ();
		class_load ('Supplier');
		$level = new ServiceLevel ($this->vars['id']);

		$params = $this->set_carry_fields (array('id'));
		$ret = $this->mk_redir ('manage_suppliers');

		if ($this->vars['save'] and $level->id)
		{
			$data = $this->vars['level'];
			$level->load_from_array ($data);

			if ($level->is_valid_data ())
			{
				$level->save_data ();
			}
			else
			{
				save_form_data ($data, 'service_level');
			}
			$ret = $this->mk_redir ('service_level_edit', $params);
		}
		return $ret;
	}


	/** Deletes a service level */
	function service_level_delete ()
	{
		check_auth ();
		class_load ('Supplier');
		$level = new ServiceLevel ($this->vars['id']);

		$ret = $this->mk_redir ('manage_suppliers');

		if ($level->id and $level->can_delete ())
		{
			$level->delete ();
		}
		return $ret;
	}
        
        /****************************************************************/
	/* Locations management						*/
	/****************************************************************/

	/** Displays the page for managing customers locations */
	function manage_locations ()
	{
		check_auth ();
		class_load ('Location');
		class_load ('AD_Printer');
		$tpl = 'manage_locations.tpl';

		$filter = array ('top_only'=>true, 'load_children'=>true, 'load_objects'=>true, 'order_by'=>'town');

		if ($this->vars['customer_id'])
		{
			$_SESSION['manage_locations']['customer_id'] = $this->vars['customer_id'];
			$filter['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->vars['do_filter'])
		{
			$filter['customer_id'] = $_SESSION['manage_locations']['filter']['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_locations']['customer_id'] = $this->locked_customer->id;
			$filter['customer_id'] = $this->locked_customer->id;
		}

		$locations = Location::get_locations ($filter);

		// Extract the list of customers, eventually restricting only to the customers assigned to
		// the current user, if he has restricted customer access.
		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);

		// Get the array with the associations between AD Printers canonical names and the numeric IDs (computer_id, nrc)
		$printers_cn_ids = AD_Printer::get_cn_ids(array('customer_id' => $location->customer_id));

		$towns_list = LocationFixed::get_towns_list ();

		$start_locations = Location::get_locations(array('customer_id'=>MANAGER_CUSTOMER_ID, 'top_only'=>true));
		$start_location = nl2br($start_locations[0]->street_address);
		$start_location = str_replace('<br />', ', ', $start_location);
		$end_locations = Location::get_locations(array('customer_id'=>$filter['customer_id'], 'top_only'=>true));
		$end_locations_strings = array();
		foreach($end_locations as $end_loc)
		{
			$end_locations_strings[] = str_replace('<br />', ', ', nl2br($end_loc->street_address));
		}

		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

		$this->assign ('locations', $locations);
		$this->assign ('start_location', $start_location);
		$this->assign ('destinations_count', count($end_locations));
		$this->assign ('end_locations', $end_locations_strings);
		$this->assign ('primary_destination', $end_locations_strings[0]);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('printers_cn_ids', $printers_cn_ids);
		$this->assign ('towns_list', $towns_list);
		$this->assign ('BASE_URL', KEYOS_BASE_URL);
		$this->assign ('LOCATION_TYPES', $GLOBALS['LOCATION_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('manage_locations_submit');

		$this->display ($tpl);
	}

	function manage_locations_submit ()
	{
		$ret = $this->mk_redir ('manage_locations', array('do_filter'=>1));
		$_SESSION['manage_locations']['filter'] = $this->vars['filter'];
		return $ret;
	}

	/** Displays the page for adding a new customer location */
	function location_add ()
	{
		check_auth ();
		class_load ('Location');
		$tpl = 'location_add.tpl';

		$location = new Location ();
		if (!empty_error_msg()) $location->load_from_array (restore_form_data ('location_data', false, $location_data));
		if (!$location->customer_id and $this->vars['customer_id']) $location->customer_id = $this->vars['customer_id'];
		if (!$location->type and $this->vars['type']) $location->type = $this->vars['type'];
		if ($this->vars['parent_id']) $location->parent_id = $this->vars['parent_id'];

		// Extract the list of customers, eventually restricting only to the customers assigned to
		// the current user, if he has restricted customer access.
		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);

		$towns_list = LocationFixed::get_towns_list ();
		$location->load_parents ();

		if ($location->parent_id)
		{
			$parent = new Location ($location->parent_id);
			$location->customer_id = $parent->customer_id;
			$location->town_id = $parent->town_id;
			$location->street_address = $parent->street_address;

			$this->assign ('parent', $parent);
		}

		$params = $this->set_carry_fields (array('parent_id', 'customer_id', 'type', 'returl', 'do_filter'));

		$this->assign ('location', $location);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('towns_list', $towns_list);
		$this->assign ('LOCATION_TYPES_TOP', $GLOBALS['LOCATION_TYPES_TOP']);
		$this->assign ('LOCATION_TYPES', $GLOBALS['LOCATION_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('location_add_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the new customer location */
	function location_add_submit ()
	{
		check_auth ();
		class_load ('Location');

		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_locations', array('do_filter'=>1)));
		$params = $this->set_carry_fields (array('parent_id', 'customer_id', 'type', 'returl', 'do_filter'));

		if ($this->vars['save'])
		{
			$data = $this->vars['location'];
			$location = new Location ();
			$location->load_from_array ($data);
			if ($this->vars['parent_id'])
			{
				$parent = new Location ($this->vars['parent_id']);
				$location->parent_id = $parent->id;
				$location->customer_id = $parent->customer_id;
				$location->town_id = $parent->town_id;
				$location->street_address = $parent->street_address;
			}

			if ($location->is_valid_data())
			{
				$location->save_data ();
				unset ($params['parent_id']);
				unset ($params['customer_id']);
				unset ($params['type']);
				$params['id'] = $location->id;
				$ret = $this->mk_redir ('location_edit', $params);
			}
			else
			{
				save_form_data ($data, 'location_data');
				$ret = $this->mk_redir ('location_add', $params);
			}
		}

		return $ret;
	}


	/** Displays the page for editing a location */
	function location_edit ()
	{
		check_auth ();
		class_load ('Location');
		class_load ('AD_Printer');
		class_load ('CustomerPhoto');
		$tpl = 'location_edit.tpl';

		$location = new Location ($this->vars['id']);
		if (!$location->id) return $this->mk_redir ('manage_locations');
		if (!empty_error_msg()) $location->load_from_array (restore_form_data ('location_data', false, $location_data));
		$location->load_children ();
		$location->load_parents ();
		$location->load_computers_list ();
		$location->load_peripherals_list ();
		$location->load_ad_printers_list ();
		$location->load_photos ();

		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
		$customers_list = Customer::get_customers_list ($customers_filter);
		$users_list = User::get_users_list (array('type' => (USER_TYPE_KEYSOURCE)));
		$towns_list = LocationFixed::get_towns_list ();

		// Get the array with the associations between AD Printers canonical names and the numeric IDs (computer_id, nrc)
		$printers_cn_ids = AD_Printer::get_cn_ids(array('customer_id' => $location->customer_id));

		$params = $this->set_carry_fields (array('id', 'returl', 'do_filter'));

		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $location->customer_id;

		$this->assign ('location', $location);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('users_list', $users_list);
		$this->assign ('towns_list', $towns_list);
		$this->assign ('printers_cn_ids', $printers_cn_ids);
		$this->assign ('LOCATION_TYPES_TOP', $GLOBALS['LOCATION_TYPES_TOP']);
		$this->assign ('LOCATION_TYPES', $GLOBALS['LOCATION_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('location_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a customer location */
	function location_edit_submit ()
	{
		check_auth ();
		class_load ('Location');
		$location = new Location ($this->vars['id']);
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_locations', array('do_filter'=>1)));
		$params = $this->set_carry_fields (array('id', 'returl', 'do_filter'));

		if ($this->vars['save'] and $location->id)
		{
			$data = $this->vars['location'];
			$location->load_from_array ($data);

			if ($location->is_valid_data ()) $location->save_data ();
			else save_form_data ($data, 'location_data');
			$ret = $this->mk_redir ('location_edit', $params);
		}

		return $ret;
	}
        
        /** Displays the page for assigning computers to a customer location */
	function location_computers ()
	{
		check_auth ();
		class_load ('Location');
		class_load ('Computer');
		$tpl = 'location_computers.tpl';

		$location = new Location ($this->vars['id']);
		if (!$location->id) return $this->mk_redir ('manage_locations');

		$location->load_parents ();
		$location->load_computers_list ();

		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
		$customers_list = Customer::get_customers_list ($customers_filter);
		$computers_list = Computer::get_computers_list (array('customer_id' => $location->customer_id, 'append_id' => true));

		// Remove from the list the computers already assigned to this location
		foreach ($computers_list as $id=>$name) if (isset($location->computers_list[$id])) unset ($computers_list[$id]);

		$params = $this->set_carry_fields (array('id', 'returl', 'do_filter'));

		$this->assign ('location', $location);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('location_computers_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the list of computers associated with a location */
	function location_computers_submit ()
	{
		check_auth ();
		class_load ('Location');

		$location = new Location ($this->vars['id']);
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('location_edit', array('id'=>$location->id)));
		$params = $this->set_carry_fields (array('id', 'returl'));

		if ($this->vars['save'] and $location->id)
		{
			$location->set_computers ($this->vars['assigned_computers']);
			$ret = $this->mk_redir ('location_computers', $params);
		}

		return $ret;
	}


	/** Displays the page for assigning peripherals to a customer location */
	function location_peripherals ()
	{
		check_auth ();
		class_load ('Location');
		class_load ('Peripheral');
		$tpl = 'location_peripherals.tpl';

		$location = new Location ($this->vars['id']);
		if (!$location->id) return $this->mk_redir ('manage_locations', array('do_filter'=>1));

		$location->load_parents ();
		$location->load_peripherals_list ();

		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
		$customers_list = Customer::get_customers_list ($customers_filter);
		$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $location->customer_id, 'append_id' => true));

		// Remove from the list the peripherals already assigned to this location
		foreach ($peripherals_list as $id=>$name) if (isset($location->peripherals_list[$id])) unset ($peripherals_list[$id]);

		$params = $this->set_carry_fields (array('id', 'returl'));

		$this->assign ('location', $location);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('peripherals_list', $peripherals_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('location_peripherals_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the list of computers associated with a location */
	function location_peripherals_submit ()
	{
		check_auth ();
		class_load ('Location');

		$location = new Location ($this->vars['id']);
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('location_edit', array('id'=>$location->id)));
		$params = $this->set_carry_fields (array('id', 'returl'));

		if ($this->vars['save'] and $location->id)
		{
			$location->set_peripherals ($this->vars['assigned_peripherals']);
			$ret = $this->mk_redir ('location_peripherals', $params);
		}

		return $ret;
	}


	/** Displays the page for assigning AD PRinters to a customer location */
	function location_ad_printers ()
	{
		check_auth ();
		class_load ('Location');
		class_load ('AD_Printer');
		$tpl = 'location_ad_printers.tpl';

		$location = new Location ($this->vars['id']);
		if (!$location->id) return $this->mk_redir ('manage_locations', array('do_filter'=>1));

		$location->load_parents ();
		$location->load_ad_printers_list  ();

		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
		$customers_list = Customer::get_customers_list ($customers_filter);
		$ad_printers_list = AD_Printer::get_ad_printers_list_canonical (array('customer_id' => $location->customer_id));

		// Remove from the list the AD Printers already assigned to this location
		foreach ($ad_printers_list as $cn=>$name) if (isset($location->ad_printers_list[$cn])) unset ($ad_printers_list[$cn]);

		$params = $this->set_carry_fields (array('id', 'returl'));

		$this->assign ('location', $location);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('ad_printers_list', $ad_printers_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('location_ad_printers_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the list of AD Printers associated with a location */
	function location_ad_printers_submit ()
	{
		check_auth ();
		class_load ('Location');

		$location = new Location ($this->vars['id']);
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('location_edit', array('id'=>$location->id)));
		$params = $this->set_carry_fields (array('id', 'returl'));

		if ($this->vars['save'] and $location->id)
		{
			$location->set_ad_printers ($this->vars['assigned_ad_printers']);
			$ret = $this->mk_redir ('location_ad_printers', $params);
		}

		return $ret;
	}
        
        /** Displays the page for adding a comment to a location */
	function location_comment_add ()
	{
		check_auth ();
		class_load ('Location');
		$location = new Location($this->vars['location_id']);
		if (!$location->id) return $this->mk_redir ('manage_locations', array('do_filter'=>1));
		check_auth (array('customer_id' => $location->customer_id));
		$tpl = 'location_comment_add.tpl';

		$location->load_parents ();

		$params = $this->set_carry_fields (array('location_id', 'returl'));

		$this->assign ('location', $location);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('location_comment_add_submit', $params);

		$this->display ($tpl);
	}


	/** Save the new location comment */
	function location_comment_add_submit ()
	{
		class_load ('Location');
		$location = new Location($this->vars['location_id']);
		check_auth (array('customer_id' => $location->customer_id));

		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('location_edit', array('id' => $location->id)));
		$params = $this->set_carry_fields (array('location_id', 'returl'));

		if ($this->vars['save'] and $location->id)
		{
			$data = $this->vars['comment'];
			$data['location_id'] = $location->id;
			$data['user_id'] = $this->current_user->id;
			$data['comments'] = trim ($data['comments']);
			$comment = new LocationComment ();
			$comment->load_from_array ($data);

			if ($comment->is_valid_data ()) $comment->save_data ();
			else $ret = $this->mk_redir ('location_comment_add', $params);
		}

		return $ret;
	}


	/** Displays the page for editing a comment for a location */
	function location_comment_edit ()
	{
		check_auth ();
		class_load ('Location');
		$comment = new LocationComment ($this->vars['id']);
		$location = new Location($comment->location_id);
		if (!$location->id) return $this->mk_redir ('manage_locations', array('do_filter'=>1));
		check_auth (array('customer_id' => $location->customer_id));
		$tpl = 'location_comment_edit.tpl';

		$location->load_parents ();

		$params = $this->set_carry_fields (array('id', 'returl'));

		$this->assign ('comment', $comment);
		$this->assign ('location', $location);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('location_comment_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves a location comment */
	function location_comment_edit_submit ()
	{
		class_load ('Location');
		$comment = new LocationComment ($this->vars['id']);
		$location = new Location($comment->location_id);
		check_auth (array('customer_id' => $location->customer_id));

		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('location_edit', array('id' => $location->id)));
		$params = $this->set_carry_fields (array('id', 'returl'));

		if ($this->vars['save'] and $location->id)
		{
			$data = $this->vars['comment'];
			$data['user_id'] = $this->current_user->id;
			$data['comments'] = trim ($data['comments']);
			$comment->load_from_array ($data);

			if ($comment->is_valid_data ()) $comment->save_data ();
			else $ret = $this->mk_redir ('location_comment_edit', $params);
		}

		return $ret;
	}


	/** Deletes a location comment */
	function location_comment_delete ()
	{
		class_load ('Location');
		$comment = new LocationComment ($this->vars['id']);
		$location = new Location ($comment->location_id);
		check_auth (array('customer_id' => $location->customer_id));

		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('location_edit', array('id' => $location->id)));

		if ($comment->id) $comment->delete ();

		return $ret;
	}


	/** Deletes a location */
	function location_delete ()
	{
		class_load ('Location');
		$location = new Location ($this->vars['id']);
		check_auth (array('customer_id' => $location->customer_id));
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_locations', array('do_filter'=>1)));

		$location->delete ();

		return $ret;
	}
        
        /** Displays the page for managing fixed locations */
	function manage_locations_fixed ()
	{
		check_auth ();
		class_load ('LocationFixed');
		$tpl = 'manage_locations_fixed.tpl';

		$countries = LocationFixed::get_locations (array('type' => LOCATION_FIXED_TYPE_COUNTRY, 'load_children' => true));

		$this->assign ('countries', $countries);
		$this->assign ('error_msg', error_msg ());

		$this->display ($tpl);
	}


	/** Display the page showing the customer locations defined in a fixed location */
	function location_fixed_customers ()
	{
		check_auth ();
		class_load ('Location');
		class_load ('LocationFixed');
		$tpl = 'location_fixed_customers.tpl';

		$town = new LocationFixed ($this->vars['id']);
		$customers_locations = Location::get_locations (array('top_only'=>true, 'town_id'=>$town->id, 'order_by'=>'customer'));
		$customers_list = Customer::get_customers_list ();

		$this->assign ('town', $town);
		$this->assign ('customers_locations', $customers_locations);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());

		$this->display ($tpl);
	}


	/** Displays the page for adding a new fixed location */
	function location_fixed_add ()
	{
		check_auth ();
		class_load ('LocationFixed');
		$tpl = 'location_fixed_add.tpl';

		$location = new LocationFixed ();
		if (!empty_error_msg()) $location->load_from_array (restore_form_data ('location_data', false, $location_data));
		if ($this->vars['type']) $location->type = $this->vars['type'];
		if ($this->vars['parent_id']) $location->parent_id = $this->vars['parent_id'];

		if ($location->parent_id)
		{
			$parent = new LocationFixed ($location->parent_id);
			$this->assign ('parent', $parent);
		}

		$params = $this->set_carry_fields (array('parent_id', 'type', 'returl'));

		$this->assign ('location', $location);
		$this->assign ('LOCATION_FIXED_TYPES', $GLOBALS['LOCATION_FIXED_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('location_fixed_add_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the newly defined fixed location */
	function location_fixed_add_submit ()
	{
		check_auth ();
		class_load ('LocationFixed');

		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_locations_fixed'));
		$params = $this->set_carry_fields (array ('parent_id', 'type', 'returl'));

		if ($this->vars['save'])
		{
			$data = $this->vars['location'];
			$location = new LocationFixed ();
			$location->load_from_array ($data);
			if ($this->vars['parent_id']) $location->parent_id = $this->vars['parent_id'];
			if ($this->vars['type']) $location->type = $this->vars['type'];

			if ($location->is_valid_data())
			{
				$location->save_data ();
				unset ($params['parent_id']);
				unset ($params['type']);
				$params['id'] = $location->id;
				$ret = $this->mk_redir ('location_fixed_edit', $params);
			}
			else
			{
				save_form_data ($data, 'location_data');
				$ret = $this->mk_redir ('location_fixed_add', $params);
			}
		}

		return $ret;
	}


	/** Displays the page for editng a fixed location */
	function location_fixed_edit ()
	{
		check_auth ();
		class_load ('LocationFixed');
		$tpl = 'location_fixed_edit.tpl';

		$location = new LocationFixed ($this->vars['id']);
		if (!$location->id) return $this->mk_redir ('manage_locations_fixed');

		if (!empty_error_msg()) $location->load_from_array (restore_form_data ('location_data', false, $location_data));
		if ($location->parent_id)
		{
			$parent = new LocationFixed ($location->parent_id);
			$this->assign ('parent', $parent);
		}

		$params = $this->set_carry_fields (array('id', 'returl'));

		$this->assign ('location', $location);
		$this->assign ('LOCATION_FIXED_TYPES', $GLOBALS['LOCATION_FIXED_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('location_fixed_edit_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the fixed location */
	function location_fixed_edit_submit ()
	{
		check_auth ();
		class_load ('LocationFixed');

		$location = new LocationFixed ($this->vars['id']);
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_locations_fixed'));
		$params = $this->set_carry_fields (array ('id', 'returl'));

		if ($this->vars['save'] and $location->id)
		{
			$data = $this->vars['location'];
			$location->load_from_array ($data);

			if ($location->is_valid_data()) $location->save_data ();
			else save_form_data ($data, 'location_data');

			$ret = $this->mk_redir ('location_fixed_edit', $params);
		}

		return $ret;
	}
        
        /****************************************************************/
	/* Management of default tickets CC recipients			*/
	/****************************************************************/

	/** Displays the page for managing the CC recipients */
	function manage_cc_recipients ()
	{
		check_auth ();
		class_load ('CustomerCCRecipient');
		$tpl = 'manage_cc_recipients.tpl';

		$all_recipients = CustomerCCRecipient::get_all_cc_recipients ();

		$customers_filter = array ('has_krifs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => 1);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);

		$this->assign ('all_recipients', $all_recipients);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg());

		$this->display ($tpl);
	}

	/**
	 * Display a page with all the recipients that don't have at least one default recipient set
	 *
	 */
	function manage_cc_not_recipients()
	{
		check_auth();
		class_load('CustomerCCRecipient');
		$tpl = 'manage_cc_not_recipients.tpl';


		//debug(all_not_recipients);

		$customers_filter = array ('has_krifs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => 1);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;

		$customers_list = Customer::get_customers_list ($customers_filter);

		$all_not_recipients = CustomerCCRecipient::get_all_customers_without_cc($customers_list);
		$this->assign ('all_not_recipients', $all_not_recipients);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg());

		$this->display ($tpl);
	}

	/** Displays the page for editing the CC recipients of a customer */
	function cc_recipients_edit ()
	{
		check_auth ();
		class_load ('CustomerCCRecipient');
		$tpl = 'cc_recipients_edit.tpl';
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_cc_recipients');

		$recipients = CustomerCCRecipient::get_cc_recipients ($customer->id);
		$recipients_ids = array ();
		foreach ($recipients as $user) $recipients_ids[] = $user->id;

		$users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
		$groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
		$customer_users = User::get_users_list (array('customer_id' => $customer->id, 'type' => USER_TYPE_CUSTOMER));
		$all_users = $customer_users+$users+$groups;

		$params = $this->set_carry_fields (array('customer_id'));
		$this->assign ('customer', $customer);
		$this->assign ('recipients', $recipients);
		$this->assign ('recipients_ids', $recipients_ids);
		$this->assign ('all_users', $all_users);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('cc_recipients_edit_submit', $params);

		$this->display ($tpl);
	}

	/** Saves the list of CC recipients for a customer */
	function cc_recipients_edit_submit ()
	{
		check_auth ();
		class_load ('CustomerCCRecipient');
		$ret = $this->mk_redir ('manage_cc_recipients');
		$customer = new Customer ($this->vars['customer_id']);

		if ($this->vars['save'] and $customer->id)
		{
			if (!is_array($this->vars['cc_recipients'])) $recipients = array ();
			else $recipients = $this->vars['cc_recipients'];

			CustomerCCRecipient::set_cc_recipients ($customer->id, $recipients);
		}
		return $ret;
	}
        
        /****************************************************************/
	/* Reporting							*/
	/****************************************************************/

	/** Displays the page for generating a customer report */
	function customer_report ()
	{
		check_auth ();
		class_load ('Computer');
		$tpl = 'customer_report.tpl';

		$filter = array('favorites_first' => $this->current_user->id, 'show_ids' => true);
		if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($filter);

		if ($this->vars['customer_id'])
		{
			// The customer ID was passed in the URL
			$_SESSION['customer_report']['filter']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->vars['change_customer'])
		{
			// A change of customer was requested
			unset ($_SESSION['customer_report']['filter']['customer_id']);
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['customer_report']['filter']['customer_id'] = $this->locked_customer->id;
		}

		$filter = $_SESSION['customer_report']['filter'];
		$params = array ();

		if ($filter['customer_id'])
		{
			// There is a valid selected customer
			$customer = new Customer ($filter['customer_id']);
			$params['filter']['customer_id'] = $customer->id;


			// By default, select all reports if no previous selection was made
			if (empty ($filter['selected_report']))
			{
				$filter['selected_report'] = array (
					'report_computers' => true,
					'report_peripherals' => true,
					'report_warranties' => true,
					'report_software' => true,
					'report_all_software' => true,
					'report_licenses' => true,
					'report_users' => true,
					'report_free_space' => true,
					'report_backups' => true,
					'report_av_status' => true,
					'report_av_hist' => true,
				);
				$filter['report_peripherals'] = array (
					'summary' =>  true,
					'details' => true
				);
				$filter['report_warranties'] = array (
					'charts' =>  true,
					'details' => true,
					'computers' => true,
					'ad_printers' => true,
					'peripherals' => true
				);
				$filter['report_backups'] = array (
					'rep_age' =>  true,
					'rep_size' => true,
					'show_charts' => true,
					'show_numbers' => true
				);
				$filter['report_free_space'] = array (
					'show_charts' => true,
					'show_numbers' => true
				);
			}

			// Get computers lists, in the order servers, workstations, unspecified
			$servers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_SERVER, 'append_id' => true, 'exclude_blackouts' => true));
			$workstations_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_WORKSTATION, 'append_id' => true, 'exclude_blackouts' => true));
			$unspecifieds_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_UNSPECIFIED, 'append_id' => true, 'exclude_blackouts' => true));
			$computers_list = $servers_list + $workstations_list + $unspecifieds_list;

			// Get the list of partitions for the customer's computers,
			// including only those for which the disk space is logged
			$servers_disks = Computer::get_disks_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_SERVER, 'append_id' => true, 'with_logs' => true));
			$workstations_disks = Computer::get_disks_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_WORKSTATION, 'append_id' => true, 'with_logs' => true));
			$unspecifieds_disks = Computer::get_disks_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_UNSPECIFIED, 'append_id' => true, 'with_logs' => true));
			$disks_list = $servers_disks + $workstations_disks + $unspecifieds_disks;

			// Get the list of computers having backups and the months with logged backups
			$backup_item_id = Computer::get_item_id ('backup_status');
			$av_item_id = Computer::get_item_id ('anti_virus');
			$backup_computers = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true, 'logging_item' => $backup_item_id));
			$av_computers = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true, 'logging_item' => $av_item_id));

			// Get the months for which we have logs
			//$months_backups = Computer::get_all_log_months (array('customer_id' => $customer->id, 'item_id' => $backup_item_id));
			$months_partitions = Computer::get_all_log_months (array('customer_id' => $customer->id, 'item_id' => Computer::get_item_id ('partitions')));
			//$months_av = Computer::get_all_log_months (array('customer_id' => $customer->id, 'item_id' => $av_item_id));
			//$months_interval = array_unique(array_merge ($months_backups, $months_partitions, $months_av));
			//rsort($months_interval);
			$months_interval = array_unique($months_partitions);
			rsort($months_interval);

			// Pre-populate the list of selected partitions and make sure it contains only valid computers
			$available_disks_list = $disks_list;
			$selected_disks_list = array ();
			if (is_array($filter['report_free_space']['partitions']))
			{
				foreach ($filter['report_free_space']['partitions'] as $idx => $id)
				{
					$id = stripslashes ($id);
					if (!isset($disks_list[$id])) unset ($filter['report_free_space']['partitions'][$idx]);
				}

				foreach ($filter['report_free_space']['partitions'] as $id)
				{
					$id = stripslashes ($id);
					$selected_disks_list[$id] = $disks_list[$id];
					unset ($available_disks_list[$id]);
				}
			}

			// Pre-populate the list of computers of backups age report
			$available_backup_computers = $backup_computers;
			$selected_backup_computers = array ();
			if (is_array($filter['report_backups']['computers']))
			{
				foreach ($filter['report_backups']['computers'] as $idx => $id)
				{
					$id = stripslashes ($id);
					if (!isset($backup_computers[$id])) unset ($filter['report_backups']['computers'][$idx]);
				}

				foreach ($filter['report_backups']['computers'] as $id)
				{
					$id = stripslashes ($id);
					$selected_backup_computers[$id] = $backup_computers[$id];
					unset ($available_backup_computers[$id]);
				}
			}

			// Pre-populate the list of computers with AV monitoring
			$available_av_computers = $av_computers;
			$selected_av_computers = array ();

			if (is_array($filter['report_av_hist']['computers']))
			{
				foreach ($filter['report_av_hist']['computers'] as $idx => $id)
				{
					$id = stripslashes ($id);
					if (!isset($av_computers[$id])) unset ($filter['report_av_hist']['computers'][$idx]);
				}

				foreach ($filter['report_av_hist']['computers'] as $id)
				{
					$id = stripslashes ($id);
					$selected_av_computers[$id] = $av_computers[$id];
					unset ($available_av_computers[$id]);
				}
			}

			$this->assign ('servers_list', $servers_list);
			$this->assign ('workstations_list', $workstations_list);
			$this->assign ('unspecifieds_list', $unspecifieds_list);
			$this->assign ('computers_list', $computers_list);
			$this->assign ('servers_disks', $servers_disks);
			$this->assign ('workstations_disks', $workstations_disks);
			$this->assign ('unspecifieds_disks', $unspecifieds_disks);
			$this->assign ('disks_list', $disks_list);
			$this->assign ('selected_disks_list', $selected_disks_list);
			$this->assign ('available_disks_list', $available_disks_list);
			$this->assign ('backup_computers', $backup_computers);
			$this->assign ('selected_backup_computers', $selected_backup_computers);
			$this->assign ('available_backup_computers', $available_backup_computers);
			$this->assign ('av_computers', $av_computers);
			$this->assign ('selected_av_computers', $selected_av_computers);
			$this->assign ('available_av_computers', $available_av_computers);
			$this->assign ('months_partitions', $months_partitions);
			$this->assign ('months_backups', $months_backups);
			$this->assign ('months_av', $months_av);
			$this->assign ('months_interval', $months_interval);
		}

		$this->assign ('customer', $customer);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('customer_report_submit', $params);

		$this->display ($tpl);
	}


	/** Saves the criteria for generating a customer report or generates a report */
	function customer_report_submit ()
	{
		$ret = $this->mk_redir ('customer_report');

		if ($this->vars['select'] and $this->vars['filter']['customer_id'])
		{
			// This was a request to select a customer for reporting
			$_SESSION['customer_report']['filter']['customer_id'] = $this->vars['filter']['customer_id'];
			$ret = $this->mk_redir ('customer_report', array('do_filter' => 1));
		}
		elseif ($this->vars['generate'])
		{
			// This is a request to generate the report
			class_load ('Computer');
			class_load ('SoftwareLicense');
			class_load ('Software');
			class_load ('AD_User');
			class_load ('AD_Printer');
			class_load ('Peripheral');

			if (empty($this->vars['filter']['selected_report']))
			{
				error_msg ('Please select at least one report to include');
				$ret = $this->mk_redir ('customer_report', array('do_filter' => 1));
			}
			else
			{
				// Save the last used criteria
				$filter = $this->vars['filter'];
				if ($filter['report_av_status']['date'])
					$filter['report_av_status']['date'] = js_strtotime ($filter['report_av_status']['date']);
				else
					unset ($filter['report_av_status']['date']);
				$filter['order_by'] = ($filter['order_by'] ? $filter['order_by'] : 'name');
				$_SESSION['customer_report']['filter'] = $filter;

				if ($filter['interval']['month_start'] == -1) $month_start = date ('Y_m');
				else $month_start = $filter['interval']['month_start'];
				if ($filter['interval']['month_end'] == -1) $month_end = date ('Y_m');
				else $month_end = $filter['interval']['month_end'];

				// Validate the data
				$valid = true;
				if ($month_start > $month_end)
				{
					error_msg ('Please enter a valid time interval.');
					$valid = false;
				}
				if ($filter['selected_report']['report_peripherals'])
				{
					if (!$filter['report_peripherals']['summary'] and !$filter['report_peripherals']['details'])
						{error_msg ('For peripherals report you need to specify if you want summary and/or details report.'); $valid = false;}
				}
				if ($filter['selected_report']['report_warranties'])
				{
					if (!$filter['report_warranties']['charts'] and !$filter['report_warranties']['details'])
						{error_msg ('For warranties report you need to specify if you want charts and/or details report.'); $valid = false;}
					if (!$filter['report_warranties']['computers'] and !$filter['report_warranties']['ad_printers'] and !$filter['report_warranties']['peripherals'])
						{error_msg ('For warranties report you need to specify if you want computers and/or AD printers and/or peripherals.'); $valid = false;}
				}
				if ($filter['selected_report']['report_free_space'])
				{
					if (empty($filter['report_free_space']['partitions']))
						{error_msg ('Please specify the partitions to include in the free disk space report.'); $valid = false;}
				}
				if ($filter['selected_report']['report_backups'])
				{
					if (empty($filter['report_backups']['computers']))
						{error_msg ('Please specify the computers to include in the backups report.'); $valid = false;}
					if (!$filter['report_backups']['rep_age'] and !$filter['report_backups']['rep_size'])
						{error_msg ('For backups report you need to specify if you and want ages and/or sizes report.'); $valid = false;}
				}
				if ($filter['selected_report']['report_av_hist'])
				{
					if (empty($filter['report_av_hist']['computers']))
						{error_msg ('Please specify the computers to include in the AV updates history.'); $valid = false;}
				}

				// If data is not valid, sent the user back to the report generator page
				if (!$valid)
				{
					return $ret;
				}

				// Determine which sections have been selected
				$reports_sections = array (
					'report_computers' => 'technical_information',
					'report_peripherals' => 'technical_information',
					'report_warranties' => 'technical_information',
					'report_software' => 'technical_information',
					'report_all_software' => 'technical_information',
					'report_licenses' => 'technical_information',
					'report_users' => 'technical_information',
					'report_free_space' => 'statistics',
					'report_backups' => 'statistics',
					'report_av_status' => 'statistics',
					'report_av_hist' => 'statistics'
				);
				$selected_sections = array ('technical_information'=>false, 'statistics'=>false, 'support'=>false);
				if (is_array($filter['selected_report']))
				{
					foreach ($filter['selected_report'] as $report=>$on) $selected_sections[$reports_sections[$report]] = true;
				}
				$this->assign ('selected_sections', $selected_sections);

				$xml_tpl = 'customer/customer_report.xml';
				if ($filter['format'] == 'wordml') $xsl_tpl = 'customer_report_wordml.xslt';
				else $xsl_tpl = 'customer_report.xsl_fo';
				$customer_id = $this->vars['filter']['customer_id'];
				$reports = $this->vars['filter']['selected_report'];

				$customer = new Customer ($customer_id);
				$this->assign ('customer', $customer);
				$this->assign ('filter', $filter);
				$this->assign ('reports', $reports);

				// Set the params (size) for charts generation
				//$width = 600; $height = 400;
				switch ($filter['charts_size'])
				{
					case 1:	$width = 600; $height = 300; break;
					case 2:	$width = 600; $height = 400; break;
					case 3:	$width = 480; $height = 280; break;
				}
				$pdf_img_scale = 0.64;//0.64;
				$pdf_res = 100;

				// Generate computers report
				if ($reports['report_computers'])
				{
					$filter_computers = array ('customer_id' => $customer_id, 'order_by' => 'netbios_name', 'load_roles' => true, 'exclude_blackouts' => true);
					if ($filter['order_by'] == 'asset_no') $filter_computers['order_by'] = 'asset_no';

					$filter_computers['type'] = COMP_TYPE_SERVER;
					$servers = Computer::get_computers ($filter_computers, $no_count);
					$filter_computers['type'] = COMP_TYPE_WORKSTATION;
					$workstations = Computer::get_computers ($filter_computers, $no_count);
					$filter_computers['type'] = COMP_TYPE_UNSPECIFIED;
					$unspecified = Computer::get_computers ($filter_computers, $no_count);

					$this->assign ('servers', $servers);
					$this->assign ('workstations', $workstations);
					$this->assign ('unspecified', $unspecified);
				}

				// Generate peripherals  report
				if ($reports['report_peripherals'])
				{
					$ad_printers = AD_Printer::get_ad_printers (array('customer_id' => $customer_id, 'order_by' => $filter['order_by']));
					$all_peripherals = Peripheral::get_peripherals (array('customer_id' => $customer_id, 'order_by' => $filter['order_by']));
					$classes_list = PeripheralClass::get_classes_list ();
					if (!isset($computers_list)) $computers_list = Computer::get_computers_list (array('customer_id' => $customer_id, 'exclude_blackouts' => true));

					$ad_printers_monitor_item = new MonitorItem (Computer::get_item_id('ad_printers'));
					$ad_printers_fields = array ();
					for ($i=0; $i<count($ad_printers_monitor_item->struct_fields); $i++)
					{
						$ad_printers_fields[$ad_printers_monitor_item->struct_fields[$i]->short_name] = $ad_printers_monitor_item->struct_fields[$i]->name;
					}
					$ad_printers_fields_summary = array (
						'cn' => $ad_printers_fields['cn'],
						'location' => $ad_printers_fields['location']
					);

					// Set the display widths
					$display_widths = array ();
					$word_display_widths = array ();
					$name_widths = array ();
					$word_name_widths = array ();
					foreach ($all_peripherals as $class_id => $peripherals)
					{
						// For PDF documents, where we can use proportional widths
						$max_width = 1;
						$display_widths[$class_id] = $peripherals[0]->class_def->get_display_widths ($max_width, $name_width);
						$name_widths[$class_id] = $name_width;
						// For Word documents, where we can't use proportional widths
						$max_width = 9000;
						$word_display_widths[$class_id] = $peripherals[0]->class_def->get_display_widths ($max_width, $name_width);
						$word_name_widths[$class_id] = $name_width;
					}

					$this->assign ('ad_printers', $ad_printers);
					$this->assign ('ad_printers_fields', $ad_printers_fields);
					$this->assign ('ad_printers_fields_summary', $ad_printers_fields_summary);
					$this->assign ('all_peripherals', $all_peripherals);
					$this->assign ('classes_list', $classes_list);
					$this->assign ('computers_list', $computers_list);
					$this->assign ('name_widths', $name_widths);
					$this->assign ('display_widths', $display_widths);
					$this->assign ('word_name_widths', $word_name_widths);
					$this->assign ('word_display_widths', $word_display_widths);
					$this->assign ('peripherals_summary', ($filter['report_peripherals']['summary'] ? 'yes' : 'no'));
					$this->assign ('peripherals_details', ($filter['report_peripherals']['details'] ? 'yes' : 'no'));
				}

				// Generate warranties report
				if ($reports['report_warranties'])
				{
					class_load ('Supplier');
					class_load ('Warranty');
					// Fetch warranty information
					if($filter['report_warranties']['computers'])
					{
						$computers_warranties = Computer::get_warranties (array ('customer_id' => $customer_id, 'order_by' => $filter['order_by']));
						$computers_warranties_months = Warranty::get_warranties_months ($computers_warranties);
						$computers_warranties_head = Warranty::get_warranties_months_header ($computers_warranties_months, 4);
						$computers_warranties_months_grouped = Warranty::get_warranties_months_grouped ($computers_warranties_months, 40);
					}

					if($filter['report_warranties']['ad_printers'])
					{
						$ad_printers_warranties = AD_Printer::get_warranties (array ('customer_id' => $customer_id, 'order_by' => $filter['order_by']));
						$ad_printers_warranties_months = Warranty::get_warranties_months ($ad_printers_warranties);
						$ad_printers_warranties_head = Warranty::get_warranties_months_header ($ad_printers_warranties_months, 4);
						$ad_printers_warranties_months_grouped = Warranty::get_warranties_months_grouped ($ad_printers_warranties_months, 40);
					}

					if($filter['report_warranties']['peripherals'])
					{
						$peripherals_warranties = Peripheral::get_warranties (array ('customer_id' => $customer_id, 'order_by' => $filter['order_by']));
						$peripherals_warranties_months = Warranty::get_warranties_months ($peripherals_warranties);
						$peripherals_warranties_head = Warranty::get_warranties_months_header ($peripherals_warranties_months, 4);
						$peripherals_warranties_months_grouped = Warranty::get_warranties_months_grouped ($peripherals_warranties_months, 40);
					}

					if (!isset($computers_list)) $computers_list = Computer::get_computers_list (array('customer_id' => $customer_id));
					$peripherals_list = Peripheral::get_peripherals_list (array ('customer_id' => $customer_id));
					$peripherals_classes_list = PeripheralClass::get_classes_list ();
					$ad_printers_list = AD_Printer::get_ad_printers_list_canonical (array ('customer_id' => $customer_id));

					$this->assign ('computers_list', $computers_list);
					$this->assign ('computers_warranties', $computers_warranties);
					$this->assign ('peripherals_warranties', $peripherals_warranties);
					$this->assign ('ad_printers_warranties', $ad_printers_warranties);

					$this->assign ('computers_warranties_months', $computers_warranties_months);
					$this->assign ('computers_warranties_head', $computers_warranties_head);
					$this->assign ('computers_warranties_months_grouped', $computers_warranties_months_grouped);
					$this->assign ('ad_printers_warranties_months', $ad_printers_warranties_months);
					$this->assign ('ad_printers_warranties_head', $ad_printers_warranties_head);
					$this->assign ('ad_printers_warranties_months_grouped', $ad_printers_warranties_months_grouped);
					$this->assign ('peripherals_warranties_months', $peripherals_warranties_months);
					$this->assign ('peripherals_warranties_head', $peripherals_warranties_head);
					$this->assign ('peripherals_warranties_months_grouped', $peripherals_warranties_months_grouped);

					$this->assign ('peripherals_list', $peripherals_list);
					$this->assign ('peripherals_classes_list', $peripherals_classes_list);
					$this->assign ('ad_printers_list', $ad_printers_list);
					$this->assign ('service_packages_list', SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true)));
					$this->assign ('service_levels_list', ServiceLevel::get_service_levels_list ());
					$this->assign ('warranties_charts', ($filter['report_warranties']['charts'] ? 'yes' : 'no'));
					$this->assign ('warranties_details', ($filter['report_warranties']['details'] ? 'yes' : 'no'));
					$this->assign ('show_warranties_computers', ($filter['report_warranties']['computers'] ? 'yes' : 'no'));
					$this->assign ('show_warranties_ad_printers', ($filter['report_warranties']['ad_printers'] ? 'yes' : 'no'));
					$this->assign ('show_warranties_peripherals', ($filter['report_warranties']['peripherals'] ? 'yes' : 'no'));
				}


				if ($reports['report_software'] or $reports['report_licenses'])
				{
					$softwares = SoftwareLicense::get_customer_licenses ($customer_id, true);
					$this->assign ('softwares', $softwares);
				}

				// Generate installed software report
				if ($reports['report_software'])
				{
					// Load the list of computers using this software
					for ($i=0; $i<count($softwares); $i++)
					{
						if ($softwares[$i]->software->in_reports)
						{
							$softwares[$i]->computers_list = $softwares[$i]->get_computers_list (true); // Fetch the computers with asset numbers, not IDs
						}
					}

					$this->assign ('softwares', $softwares);
				}

				if($reports['report_all_software'])
				{
					$clist = Computer::get_computers_list (array('customer_id' => $customer_id));
					$installed_sft = array();
					foreach($clist as $id=>$name)
					{
						$sft = Software::get_permachine_sofware(array('computer_id'=>$id));
						$installed_sft[$name] = $sft;
					}
					//debug($installed_sft);
					$this->assign('installed_sft', $installed_sft);
				}

				// Generate users report
				if ($reports['report_users'])
				{
					$ad_users = AD_User::get_ad_users (array('customer_id' => $customer_id));
					$this->assign ('ad_users', $ad_users);
				}

				// Generate partitions report
				if ($reports['report_free_space'])
				{
					if (!isset($computers_list)) $computers_list = Computer::get_computers_list (array('customer_id' => $customer_id));

					$partitions = array ();

					$partitions_item_id = Computer::get_item_id ('partitions');
					$size_field_id = Computer::get_item_id ('size', $partitions_item_id);
					$path_field_id = Computer::get_item_id ('unc', $partitions_item_id);

					// Gather all partitions info
					$partitions_info = array ();
					foreach ($computers_list as $id => $name)
					{
						$partitions_info[$id] = Computer::get_item ('partitions', $id);
					}


					// Compose the list of partitions required for report
					foreach ($filter['report_free_space']['partitions'] as $partition)
					{
						list ($computer_id, $partition) = split ('_', $partition, 2);
						//$partition = trim(stripslashes ($partition));
                                                $partition = trim($partition);

						$partition_size = 0;
						for ($i=0; $i<count($partitions_info[$computer_id]); $i++)
						{
							if ($partitions_info[$computer_id][$i][$path_field_id] == $partition)
							{
								$partition_size = $partitions_info[$computer_id][$i][$size_field_id];
							}
						}

						$graph_param = array (
							'computer_id' => $computer_id,
							'computer_name' => $computers_list[$computer_id],
							'partition' => $partition,
							'start' => $month_start,
							'end' => $month_end,
							'width' => $width,
							'height' => $height,
							'unique' => time (),
							'no_title' => 1
						);

						$part = array (
							'computer_id' => $computer_id,
							'computer_name' => $computers_list[$computer_id],
							'month_start' => $month_start,
							'month_end' => $filter['report_free_space']['month_end'],
							'partition_name' => $partition,
							'partition_size' => get_memory_string ($partition_size),
							'graph_url' => $this->mk_redir ('plot_free_disk', $graph_param, 'kawacs'),
							'graph_width' => $width,
							'graph_height' => $height,
							'graph_width_pdf' => ($width / $pdf_res),
							'graph_height_pdf' => ($height / $pdf_res),
						);

						// If requested, load the actual numbers from which the graph is plotted
						$columns_bk_numbers = 0;
						if ($filter['report_free_space']['show_numbers'])
						{
							$filter_numbers = array (
								'computer_id' => $computer_id,
								'partition' => $partition,
								'month_start' => $month_start,
								'month_end' => $month_end,
								'interval' => 'day',
								'sort_dir' => 'DESC'
							);
							// Group the values in rows
							$columns_free_space_numbers = 3;
							$numbers = Computer::get_partitions_history ($filter_numbers);
							$per_col = ceil (count($numbers[$partition]->log)/$columns_free_space_numbers);
							$rows = array (); $cnt = 0;
							foreach ($numbers[$partition]->log as $k=>$v) $rows[($cnt++ % $per_col)][$k] = $v;
							$part['free_space_numbers'] = $rows;

							// Prepare an empty array with the number of columns, to make easier generation in XML/XSL
							$columns_free_space = array ();
							for ($i=0; $i<$columns_free_space_numbers; $i++) $columns_free_space[] = $i;
						}
						$partitions[] = $part;

					}
					$this->assign ('partitions', $partitions);
					$this->assign ('columns_free_space', $columns_free_space);
				}

				// Generate backups age report
				if ($reports['report_backups'])
				{
					if (!isset($computers_list)) $computers_list = Computer::get_computers_list (array('customer_id' => $customer_id));

					$partitions = array ();

					foreach ($filter['report_backups']['computers'] as $computer_id)
					{
						// Set the params for charts generation
						$graph_param = array (
							'computer_id' => $computer_id,
							'computer_name' => $computers_list[$computer_id],
							'start' => $month_start,
							'end' => $month_end,
							'graph_width' => $width,
							'graph_height' => $height,
							'unique' => time (),
							'no_title' => 1
						);

						$backup = array (
							'computer_id' => $computer_id,
							'computer_name' => $computers_list[$computer_id],
							'month_start' => $month_start,
							'month_end' => $month_end,
							'age_graph_url' => $this->mk_redir ('plot_backup_age', $graph_param, 'kawacs'),
							'size_graph_url' => $this->mk_redir ('plot_backup_size', $graph_param, 'kawacs'),
							'graph_width' => $width,
							'graph_height' => $height,
							'graph_width_pdf' => ($width / $pdf_res),
							'graph_height_pdf' => ($height / $pdf_res),
						);

						// If requested, load the actual numbers from which the graph is plotted
						$columns_bk_numbers = 0;
						if ($filter['report_backups']['show_numbers'])
						{
							$filter_numbers = array (
								'computer_id' => $computer_id,
								'month_start' => $month_start,
								'month_end' => $month_end,
								'sort_dir' => 'DESC'
							);
							// Group the values in rows
							$columns_bk_numbers = 3;
							if ($filter['report_backups']['rep_age'])
							{
								$numbers = Computer::get_backups_history ($filter_numbers);
								$per_col = ceil (count($numbers)/$columns_bk_numbers);
								$rows = array (); $cnt = 0;
								foreach ($numbers as $k=>$v) $rows[($cnt++ % $per_col)][$k] = $v;
								$backup['age_numbers'] = $rows;

							}
							if ($filter['report_backups']['rep_size'])
							{
								$numbers = Computer::get_backups_sizes ($filter_numbers);
								$per_col = ceil (count($numbers)/$columns_bk_numbers);
								$rows = array (); $cnt = 0;
								foreach ($numbers as $k=>$v) $rows[($cnt++ % $per_col)][$k] = $v;
								$backup['size_numbers'] = $rows;
							}
							// Prepare an empty array with the number of columns, to make easier generation in XML/XSL
							$columns_bk = array ();
							for ($i=0; $i<$columns_bk_numbers; $i++) $columns_bk[] = $i;
						}
						$backups[] = $backup;
					}
					$this->assign ('backups', $backups);
					$this->assign ('columns_bk', $columns_bk);
				}


				// Generate the AV updates status
				if ($reports['report_av_status'])
				{
					if (!isset($computers_list)) $computers_list = Computer::get_computers_list (array('customer_id' => $customer_id));

					$av_status_date = ($filter['report_av_status']['date'] ? $filter['report_av_status']['date'] : time());
					$graph_param = array (
						'customer_id' => $customer_id,
						'date' => $av_status_date,
						'graph_width' => $width,
						'graph_height' => $height,
						'unique' => time (),
						'no_title' => 1
					);
					$av_status_graph_url = $this->mk_redir ('plot_av_status', $graph_param, 'kawacs');

					$this->assign ('av_status_date', $av_status_date);
					$this->assign ('av_status_graph_url', $av_status_graph_url);
					$this->assign ('av_status_graph_width', $width);
					$this->assign ('av_status_graph_height', $height);
					$this->assign ('av_status_graph_width_pdf', ($width / $pdf_res));
					$this->assign ('av_status_graph_height_pdf', ($height / $pdf_res));
				}

				// Generate the AV updates status
				if ($reports['report_av_hist'])
				{
					if (!isset($computers_list)) $computers_list = Computer::get_computers_list (array('customer_id' => $customer_id));

					$updates_age = array ();
					foreach ($filter['report_av_hist']['computers'] as $computer_id)
					{
						$graph_param = array (
							'computer_id' => $computer_id,
							'computer_name' => $computers_list[$computer_id],
							'start' => $month_start,
							'end' => $month_end,
							'graph_width' => $width,
							'graph_height' => $height,
							'unique' => time (),
							'no_title' => 1
						);

						$updates_age[] = array (
							'computer_id' => $computer_id,
							'computer_name' => $computers_list[$computer_id],
							'month_start' => $month_start,
							'month_end' => $month_end,
							'graph_url' => $this->mk_redir ('plot_av_update_age', $graph_param, 'kawacs'),
							'graph_width' => $width,
							'graph_height' => $height,
							'graph_width_pdf' => ($width / $pdf_res),
							'graph_height_pdf' => ($height / $pdf_res),
						);
					}
					$this->assign ('updates_age', $updates_age);
				}

				if ($filter['format']=='pdf')
				{
					// Save the report in PDF format
					$xml = $this->fetch ($xml_tpl);
					//debug($xml);
					make_pdf_xml ($xml, $xsl_tpl);
					die;
				}
				elseif ($filter['format']=='wordml')
				{
					// Save the report in WordprocessingML format
					$xml = $this->fetch ($xml_tpl);
					$generated = make_word_ml ($xml, $xsl_tpl);
				}
				else
				{
					header('Content-Type: text/xml');
					$this->display_template_only ($xml_tpl);
					die;
				}
			}
		}

		return $ret;
	}
        
        function customer_template_style_edit()
	{
		check_auth(array('customer_id' => $this->vars['customer_id']));
		$customer = new Customer($this->vars['customer_id']);
		if(!$customer->id) return $this->mk_redir('manage_customers');

		class_load("CustomerTemplateStyle");
		$template = 'customer_template_style_edit.tpl';


		$customer_template = CustomerTemplateStyle::getByCustomerId($this->vars["customer_id"]);
		if($customer_template == null)
		{
			$customer_template = new CustomerTemplateStyle();
			$customer_template->load_defaults();
		}
		$photo_file = "images/logos/logo2.gif";
		$photo_search = "images/logos/logo2_".$customer_template->customer_id.".gif";
		if(file_exists($photo_search)) $photo_file = $photo_search;

		$this->assign('customer', $customer);
		$this->assign('photo_file', $photo_file);
		$this->assign('customer_template', $customer_template);
		$this->assign('error_msg', error_msg());
		$this->set_form_redir("customer_template_style_edit_submit", array('customer_id'=>$customer->id));
		$this->display($template);

	}
	function customer_template_style_edit_submit()
	{
		check_auth(array('customer_id' => $this->vars['customer_id']));
		class_load("CustomerTemplateStyle");
		$ret = $this->mk_redir('customer_edit', array('id'=>$this->vars['customer_id']));
		if($this->vars['save'])
		{
			$customer_template = CustomerTemplateStyle::getByCustomerId($this->vars["customer_id"]);
			if($customer_template == null)
			{
				$customer_template = new CustomerTemplateStyle();
			}
			$customer_template->load_from_array($this->vars['customer_template']);
			$customer_template->customer_id = $this->vars['customer_id'];
			$customer_template->save_data();
			//now upload the new file
			$ret = $this->mk_redir('customer_template_style_edit',array('customer_id'=>$this->vars['customer_id']));
			if($_FILES['photo_file']['name'])
			{
				$tp = basename($_FILES['photo_file']['name']);
				$file_ext = strtolower(substr($tp,strrpos($tp,".")));
				if(!empty_error_msg()) error_msg();
				if($file_ext != '.gif' and $file_ext!='.GIF')
				{
					error_msg("The only extension allowed is gif");
					return $ret;
				}
				$new_dest = 'images/logos/logo2_'.$this->vars["customer_id"].".gif";
				//debug($new_dest);
				if(file_exists($new_dest)) @unlink($new_dest);
				if(!move_uploaded_file($_FILES['photo_file']['tmp_name'], $new_dest))
				{
					error_msg("There was an error while uploading the file");
					return $ret;
				}
			}

		}
		return $ret;
	}

	//computer groups functions
	function create_computer_group()
	{
		class_load('CustomerComputerGroup');
		check_auth(array('customer_id'=>$this->vars['customer_id']));
		$customer = new Customer($this->vars['customer_id']);
		if(!$customer->id) return $this->mk_redir('manage_customers');

		$template = 'create_computer_group.tpl';

		$countries_list = array();
		$countries_list = CustomerComputerGroup::get_countries();


		$this->assign('error_msg', error_msg());
		$this->assign("countries_list", $countries_list);
		$this->assign('customer',$customer);
		$this->set_form_redir('create_computer_group_submit', array('customer_id'=>$customer->id));
		$this->display($template);
	}
	function create_computer_group_submit()
	{
		check_auth(array('customer_id' => $this->vars['customer_id']));
		class_load("CustomerComputerGroup");
		$ret = $this->mk_redir('customer_edit', array('id'=>$this->vars['customer_id']));
		if($this->vars['save'])
		{
			//save the new group here
			$computer_group = new CustomerComputerGroup();
			$computer_group->load_from_array($this->vars['computer_group']);
			$computer_group->customer_id = $this->vars['customer_id'];
			$computer_group->save_data();
			$ret = $this->mk_redir('create_computer_group',array('customer_id'=>$this->vars['customer_id']));
		}
		return $ret;
	}
        
        function view_computer_groups()
	{
		class_load('CustomerComputerGroup');
		class_load('Computer');
		class_load('Location');
		check_auth(array('customer_id'=>$this->vars['customer_id']));
		$customer = new Customer($this->vars['customer_id']);

		if(!$customer->id) return $this->mk_redir('manage_customers');

		$filter = array(
				'customer_id' => $customer->id
		);

		$template = 'view_computer_groups.tpl';

		if(isset($this->vars['gid'])) $grp = new CustomerComputerGroup($this->vars['gid']);

		if(!$grp->id)
		{
			$search_results = array();
			if(isset($_SESSION['view_computer_groups']['search_results']) and is_array($_SESSION['view_computer_groups']['search_results']))
			{
				$search_results = $_SESSION['view_computer_groups']['search_results'];
			}
			if(empty($search_results))
				$groups = CustomerComputerGroup::get_groups($filter);
			else
			{	foreach($search_results as $sr)
					$groups[] = new CustomerComputerGroup($sr);
			}
			unset($_SESSION['view_computer_groups']['search_results']);
		}
		else
		{
			$groups[] = $grp;
			unset($_SESSION['view_computer_groups']['search_results']);
		}
		$countries_list = CustomerComputerGroup::get_countries();

		//get the customer locations to be able to display a map from the customer to it's customer

		$end_locations = Location::get_locations(array('customer_id'=>$filter['customer_id'], 'top_only'=>true));
		$end_locations_strings = array();
		foreach($end_locations as $end_loc)
		{
			$end_locations_strings[] = str_replace('<br />', ', ', nl2br($end_loc->street_address));
		}

		$customer_computers = Computer::get_computers_list(array('customer_id' => $customer->id));

		//debug($groups);

		$this->assign('error_msg', error_msg());
		$this->assign('customer_computers', $customer_computers);
		$this->assign('customer', $customer);
		$this->assign('groups', $groups);
		$this->assign('end_locations_strings', $end_locations_strings);
		$this->assign('BASE_URL', BASE_URL);
		$this->assign('LOCATION_TYPES', $GLOBALS['LOCATION_TYPES']);
		$this->assign('countries_list', $countries_list);
		$this->set_form_redir('view_computer_groups_submit', array('customer_id'=>$customer->id));
		$this->display($template);


	}
	function view_computer_groups_submit()
	{
		//debug($this->vars);
		check_auth(array('customer_id'=>$this->vars['customer_id']));
		$ret = $this->mk_redir('view_computer_groups', array('customer_id'=>$this->vars['customer_id']));

		if($this->vars["searchBox"] != "")
		{
			class_load("CustomerComputerGroup");
			$results = CustomerComputerGroup::search_computer_group(array('customer_id'=>$this->vars['customer_id'], 'search_text'=>$this->vars['searchBox']));
			//debug($results);
			$_SESSION['view_computer_groups']['search_results'] = $results;
		}
		return $ret;
	}

	function edit_computer_group()
	{
		class_load('CustomerComputerGroup');
		class_load('Computer');
		check_auth(array('id'=>$this->vars['id']));

		$group = new CustomerComputerGroup($this->vars['id']);
		if(!$group->id) return $this->mk_redir('manage_customers');

		$template = 'edit_computer_group.tpl';

		$countries_list = array();
		$countries_list = CustomerComputerGroup::get_countries();

		$customer_computers = Computer::get_computers_list(array('customer_id' => $group->customer_id));

		$this->assign('error_msg', error_msg());
		$this->assign('group', $group);
		$this->assign('customer_computers', $customer_computers);
		$this->assign('countries_list', $countries_list);
		$this->set_form_redir('edit_computer_group_submit', array('id'=>$group->id));
		$this->display($template);
	}
	function edit_computer_group_submit()
	{
		check_auth(array('id' => $this->vars['id']));
		class_load("CustomerComputerGroup");
		$group = new CustomerComputerGroup($this->vars['id']);
		if(!$group->id) return $this->mk_redir('manage_customers');
		$ret = $this->mk_redir('view_computer_groups', array('customer_id'=>$group->customer_id));
		if($this->vars['save'])
		{
			//save the group data here
			$group->load_from_array($this->vars['computer_group']);
			$group->computers_list = $this->vars['computer_group']['computers_list'];
			//debug($group);
			$group->save_data();
			$ret = $this->mk_redir('edit_computer_group',array('id'=>$group->id));
		}
		return $ret;
	}
        
        function customer_computers_report()
	{
		check_auth();
		class_load('Computer');
		class_load('Warranty');
		class_load('MonitorItem');

		$tpl = 'customer_computers_report.tpl';


		$filter = array('favorites_first' => $this->current_user->id, 'show_ids' => true);
		if($this->current_user->restrict_customes)
			$filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list($filter);

		if($this->vars['customer_id'])
		{
			$_SESSION['customer_computers_report']['filter']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->vars['change_customer'])
		{
			//change the customer, if there is one set unset it
			unset($_SESSION['customer_computers_report']['filter']['customer_id']);
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			$_SESSION['customer_computers_report']['filter']['customer_id'] = $this->locked_customer->id;
		}

		$filter = $_SESSION['customer_computers_report']['filter'];
		$params = array();

		if($filter['customer_id'])
		{

			//we have a customer set... fetch data for it
			$customer = new Customer($filter['customer_id']);
			$params['filter']['customer_id'] = $customer->id;

			$filter['report_items'] = array();
			//now get list of the all the available monitor items
			$monitor_items_cat = MonitorItem::get_categories_items();
			$i=0;
			if(!$filter['select_items'])
				$filter['select_items'] = array(1001, 1002, 1003, 1006, 1008, 1009, 1010, 1011, 1012, 1021, 1022, 1023, 1024);
			foreach($monitor_items_cat as $mik => $micat)
			{
				$filter['report_items'][$i]['name'] = $GLOBALS['MONITOR_CAT'][$mik];
				foreach($micat as $mitem)
				{
					$select = false;
					if(in_array($mitem->id, $filter['select_items'])) $select = true;
					$filter['report_items'][$i]['items'][] = array('id'=>$mitem->id, 'short_name' => $mitem->short_name, 'name' => $mitem->name, 'select'=>$select);
				}
				$i++;
			}

			//we assign customer specifics to the template;
		}
		$this->assign('customer',$customer);
		$this->assign('error_msg', error_msg());
		$this->assign('filter', $filter);
		$this->assign('customers_list', $customers_list);
		$this->set_form_redir('customer_computers_report_submit', $params);
		$this->display($tpl);
	}
	function customer_computers_report_submit()
	{
		class_load('Warranty');
		class_load('MonitorItem');
		class_load('ComputerItem');
		$ret = $this->mk_redir('customer_computers_report');
		if($this->vars['select'] and $this->vars['filter']['customer_id'])
		{
			$_SESSION['customer_computers_report']['filter'] = $this->vars['filter'];
			$ret = $this->mk_redir ('customer_computers_report', array('do_filter' => 1));
		}
		elseif($this->vars['generate'])
		{
			//now we generate the XML file
			//first get the computers by type
			$customer = new Customer($this->vars['filter']['customer_id']);
			$servers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_SERVER, 'append_id' => true));
			$workstations_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_WORKSTATION, 'append_id' => true));
			$unspecifieds_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'type_id' => COMP_TYPE_UNSPECIFIED, 'append_id' => true));

			//debug($this->vars);
			$selected_items = $this->vars['filter']['select_items'];
			$servers=array();
			foreach($servers_list as $srv_id=>$srv_name)
			{
				//get the warranty start and end
				$server = new Computer($srv_id);
				if($server->id)
				{
					$wnty = new Warranty(WAR_OBJ_COMPUTER, $srv_id);
					$servers[$srv_id]['name'] = $srv_name;
					$servers[$srv_id]['id'] = $srv_id;
					$servers[$srv_id]['warranty_start'] = $wnty->warranty_starts;
					$servers[$srv_id]['warranty_end'] = $wnty->warranty_ends;
					foreach($selected_items as $selmi)
					{
						$mi = new MonitorItem($selmi);

						$servers[$srv_id]['items'][$mi->id]['name'] = $mi->name;
						$mival = $server->get_formatted_item($mi->short_name, $mi->id);
						//debug($mival);
						if(is_array($mival))
						{
							foreach($mival as $mik=>$miv)
							{
								if(is_array($miv))
								{
									foreach($miv as $k=>$v)
									{
										$milist = array();
										foreach($mi->struct_fields as $sf)
										{
											$milist[$sf->id] = $sf->short_name;
										}
										$servers[$srv_id]['items'][$mi->id]['values'][]= array('name'=>$milist[$k], 'val'=>$v);
									}
								}
							}
						}
						else
						{
							$servers[$srv_id]['items'][$mi->id]['values'][] = array('name'=>$mi->short_name,'val'=>$mival);
						}
					}
				}
			}

			$workstations=array();
			foreach($workstations_list as $wks_id=>$wks_name)
			{
				//get the warranty start and end
				$workstation = new Computer($wks_id);
				if($workstation->id)
				{
					$wnty = new Warranty(WAR_OBJ_COMPUTER, $wks_id);
					$workstations[$wks_id]['name'] = $wks_name;
					$workstations[$wks_id]['id'] = $wks_id;
					$workstations[$wks_id]['warranty_start'] = $wnty->warranty_starts;
					$workstations[$wks_id]['warranty_end'] = $wnty->warranty_ends;
					foreach($selected_items as $selmi)
					{
						$mi = new MonitorItem($selmi);

						$workstations[$wks_id]['items'][$mi->id]['name'] = $mi->name;
						$mival = $workstation->get_formatted_item($mi->short_name, $mi->id);
						//debug($mival);
						if(is_array($mival))
						{
							foreach($mival as $mik=>$miv)
							{
								if(is_array($miv))
								{
									$milist = array();
									foreach($mi->struct_fields as $sf)
									{
										$milist[$sf->id] = $sf->short_name;
									}
									foreach($miv as $k=>$v)
									{

										$workstations[$wks_id]['items'][$mi->id]['values'][]= array('name'=>$milist[$k], 'val'=>$v);
									}
								}
							}
						}
						else
						{
							$workstations[$wks_id]['items'][$mi->id]['values'][] = array('name'=>$mi->short_name,'val'=>$mival);
						}
					}
				}
			}

			$unspec=array();
			foreach($unspecifieds_list as $wks_id=>$wks_name)
			{
				//get the warranty start and end
				$workstation = new Computer($wks_id);
				if($workstation->id)
				{
					$wnty = new Warranty(WAR_OBJ_COMPUTER, $wks_id);
					$unspec[$wks_id]['name'] = $wks_name;
					$unspec[$wks_id]['id'] = $wks_id;
					$unspec[$wks_id]['warranty_start'] = $wnty->warranty_starts;
					$unspec[$wks_id]['warranty_end'] = $wnty->warranty_ends;
					foreach($selected_items as $selmi)
					{
						$mi = new MonitorItem($selmi);

						$unspec[$wks_id]['items'][$mi->id]['name'] = $mi->name;
						$mival = $workstation->get_formatted_item($mi->short_name, $mi->id);
						//debug($mival);
						if(is_array($mival))
						{
							foreach($mival as $mik=>$miv)
							{
								if(is_array($miv))
								{
									foreach($miv as $k=>$v)
									{
										$milist = array();
										foreach($mi->struct_fields as $sf)
										{
											$milist[$sf->id] = $sf->short_name;
										}
										$unspec[$wks_id]['items'][$mi->id]['values'][]= array('name'=>$milist[$k], 'val'=>$v);
									}
								}
							}
						}
						else
						{
							$unspec[$wks_id]['items'][$mi->id]['values'][] = array('name'=>$mi->short_name,'val'=>$mival);
						}
					}
				}
			}


			$this->assign('servers',$servers);
			$this->assign('workstations', $workstations);
			$this->assign('unspec', $unspec);
			$xml_tpl='customer_computers_report.xml';
			header('Content-Type: text/xml');
			$this->display_template_only($xml_tpl);
			die;
		}
		return $ret;
	}
        
        /**
	 * Transfer of assets between customers: tickets, IR's, users, computers, peripherals etc...
	 *
	 * */

	function transfer_assets()
	{
		check_auth();
		class_load("Ticket");
		class_load("InterventionReport");
		class_load("Computer");
		class_load("User");
		$tpl = "transfer_assets.tpl";

		$customers_filter = array ('favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers = Customer::get_customers_list ($customers_filter);

		if(isset($_SESSION["customer_transfer_assets"]["src_customer"])) $src_customer = $_SESSION["customer_transfer_assets"]["src_customer"];
		if(isset($_SESSION["customer_transfer_assets"]["dest_customer"])) $dest_customer = $_SESSION["customer_transfer_assets"]["dest_customer"];

		//if(isset($_SESSION["customer_transfer_assets"]["go_source"])) $act_source = $_SESSION["customer_transfer_assets"]["go_source"];
		//if(isset($_SESSION["customer_transfer_assets"]["go_dest"])) $act_dest = $_SESSION["customer_transfer_assets"]["go_dest"];

		$tickets_statuses = Ticket::get_statuses_list();

		if(isset($src_customer))
		{
			$source = new Customer($src_customer);
			if($source->id)
			{
				$src_tickets = Ticket::get_tickets_list_by_status(array('customer_id' => $src_customer));
				$src_irs = InterventionReport::get_interventions_list_by_status(array('customer_id' => $src_customer));
				$src_comp = Computer::get_computers_list_by_type(array('customer_id' => $src_customer));
				$src_users = User::get_users_list(array('customer_id' => $src_customer));
				$this->assign('src_tickets', $src_tickets);
				$this->assign('src_irs', $src_irs);
				$this->assign('src_comp', $src_comp);
				$this->assign('src_users', $src_users);
				$this->assign('src_users_count', count($src_users));
				//debug($src_users);
			}
		}
		if(isset($dest_customer))
		{
			$dest = new Customer($dest_customer);
			if($dest->id)
			{
				$dest_tickets = Ticket::get_tickets_list_by_status(array('customer_id' => $dest_customer));
				$dest_irs = InterventionReport::get_interventions_list_by_status(array('customer_id' => $dest_customer));
				$dest_comp = Computer::get_computers_list_by_type(array('customer_id' => $dest_customer));
				$dest_users = User::get_users_list(array('customer_id' => $dest_customer));
				$this->assign('dest_tickets', $dest_tickets);
				$this->assign('dest_irs', $dest_irs);
				$this->assign('dest_comp', $dest_comp);
				$this->assign('dest_users', $dest_users);
				$this->assign('dest_users_count', count($dest_users));

			}
		}

		$this->assign("ACCOUNT_MANAGERS", $GLOBALS['ACCOUNT_MANAGERS']);
		$this->assign("CONTRACT_TYPES", $GLOBALS['CONTRACT_TYPES']);
		$this->assign("INTERVENTION_STATS", $GLOBALS["INTERVENTION_STATS"]);
		$this->assign("COMP_TYPES", $GLOBALS['COMP_TYPE_NAMES']);
		$this->assign('tickets_statuses', $tickets_statuses);
		$this->assign("source", $source);
		$this->assign("dest", $dest);
		$this->assign("src_cust", $src_customer);
		$this->assign("dest_cust", $dest_customer);
		$this->assign("customers", $customers);
		$this->assign("error_msg", error_msg());
		$this->set_form_redir("transfer_assets_submit");
		$this->display($tpl);
	}
	function transfer_assets_submit()
	{
		check_auth();
		$ret = $this->mk_redir("transfer_assets");
		//if(isset($_SESSION["customer_transfer_assets"])) unset($_SESSION["customer_transfer_assets"]);
		$_SESSION["customer_transfer_assets"] = $this->vars;

		//debug($this->vars);

		return $ret;
	}

        function customer_stats(){
            check_auth();
            $tpl = 'customer_stats.tpl';

            $contract_types_cnt = Customer::get_active_customer_contract_types();

            $contract_types = $GLOBALS['CONTRACT_TYPES']  ;
            $contract_types[0] = "Other";

            $ax = array();
            foreach($contract_types_cnt as $K=>$cc){
            $ax[]  = array($contract_types[$K], intval($cc));
            }


            $this->assign("ax", json_encode($ax));
            $this->assign('error_msg', error_msg());
            $this->set_form_redir('customer_stats_submit');
            $this->display($tpl);
        }
        function customer_stats_submit(){
            check_auth();
        }

}
?>

<?php

class_load ('AccessPhone');
class_load ('RemoteAccess');
class_load ('Customer');
class_load ('Computer');
class_load ('Peripheral');
class_load ('ComputerRemoteService');
class_load ('ComputerPassword');
class_load('WebAccess');

class KlaraController extends PluginController{
    protected $plugin_name = "KLARA";
    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    /****************************************************************/
	/* Remote access - public IPs					*/
	/****************************************************************/
	
	/** Displays the page for managing remote access information for customers */
	function manage_access ()
	{
		$tpl = 'manage_access.tpl';
		class_load ('Peripheral');
		
		$extra_params = array();	// Extra parameters to be carried in navigation
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;
		
		if ($this->vars['set_filter'])
		{
			$_SESSION['manage_access']['customer_id'] = $this->vars['set_filter'];
		}
		elseif (isset($this->vars['customer_id']))
		{
			$_SESSION['manage_access']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_access']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_access'];

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			
			unset ($_SESSION['manage_access']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_access']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kawacs customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		// If a customer was selected, fetch the data for that customer
		if ($filter['customer_id'] > 0)
		{                                                            
			$customer = new Customer ($filter['customer_id']);
			// Mark the potential customer for locking
			$_SESSION['potential_lock_customer_id'] = $customer->id;
			
			$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
			$peripherals_list = Peripheral::get_peripherals_list (array ('customer_id'=>$customer->id));
			
			// Fetch all public IPs reported by computers for this customer,
			// then load for each one the related RemoteAcces object (if exists);
			$customer_ips_list = RemoteAccess::get_customer_computers_ips ($customer->id);
			$remote_ips = RemoteAccess::get_ips (array('customer_id' => $customer->id));
			
			// Fetch the list of all computers that have remote services defined
			$computers_services = ComputerRemoteService::get_services (array('customer_id' => $customer->id, 'by_computer' => true));
			// Fetch the list of all computers that have passwords defined
			$computers_passwords = ComputerPassword::get_passwords (array('customer_id' => $customer->id, 'by_computer' => true));
			// Fetch the list of computers that have expired passwords
			$computers_with_expired_passwords = ComputerPassword::get_computers_expired_passwords (array('customer_id' => $customer_id));
			
			// Fetch the list of computers with network remote access
			$peripherals = Peripheral::get_peripherals (array('customer_id' => $customer->id, 'no_group' => true));
                                                            $web_access = WebAccess::get_webaccess_list(array('customer_id' => $customer->id));
                                                            
                                                            if(!empty($web_access)){
                                                                class_load('User');
                                                                $users_list = User::get_users_list();                                                                
                                                                $this->assign('users_list', $users_list);
                                                            }
                                                            
			$this->assign ('customer', $customer);
			$this->assign ('computers_list', $computers_list);
			$this->assign ('peripherals_list', $peripherals_list);
			
			$this->assign ('remote_ips', $remote_ips);
			$this->assign ('customer_ips_list', $customer_ips_list);
			$this->assign ('add_url', $this->mk_redir ('remote_access_add', array ('customer_id' => $customer->id)));
			
			$this->assign ('computers_services', $computers_services);
			$this->assign ('computers_passwords', $computers_passwords);
			$this->assign ('computers_with_expired_passwords', $computers_with_expired_passwords);
			
			$this->assign ('peripherals', $peripherals);
                                                            $this->assign('web_access', $web_access);
		}
		
		if ($this->vars['do_filter']) $this->assign ('do_filter_url', '&do_filter=1');
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('REMOTE_SERVICE_NAMES', $GLOBALS['REMOTE_SERVICE_NAMES']);
		$this->assign ('REMOTE_SERVICES_PORTS', $GLOBALS['REMOTE_SERVICES_PORTS']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('manage_access_submit');
	
		$this->display ($tpl);
	}
	
	
	function manage_access_submit ()
	{
		check_auth ();
		
		$extra_params = array();
		$_SESSION['manage_access'] = $this->vars['filter'];
		
		if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
		{
			$extra_params['do_filter'] = 1;
		}
		
		return $this->mk_redir('manage_access', $extra_params);
	}
        
        /** Displays the page for defining remote access information for a public IP */
	function remote_access_add ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$tpl = 'remote_access_add.tpl';
		
		if ($this->vars['public_ip'] != '-1' and $this->vars['customer_id'])
		{
			$customer = new Customer ($this->vars['customer_id']);
			if ($customer->id)
			{
				// Check remote access information hasn't been defined already for this IP
				$remote_access = new RemoteAccess ($this->vars['public_ip'], $customer->id);
				if ($remote_access->id) return $this->mk_redir ('remote_access_edit', array ('id' => $remote_access->id));
			}
			else return $this->mk_redir ('manage_access');
		}
		else return $this->mk_redir ('manage_access');
		
		// Load the previous form data if there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('remote_access', false, $data);
		$remote_access->load_from_array ($data, true);
		
		$params = array ('public_ip'=>$this->vars['public_ip'], 'customer_id' => $customer->id);
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		
		$this->assign ('remote_access', $remote_access);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('remote_access_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the remote access definition */
	function remote_access_add_submit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		
		if ($this->vars['public_ip']!='-1' and $this->vars['customer_id'])
		{
			$customer = new Customer ($this->vars['customer_id']);
			if ($customer->id)
			{
				// Check remote access information hasn't been defined already for this IP
				$remote_access = new RemoteAccess ($this->vars['public_ip'], $customer->id);
				if ($remote_access->id) return $this->mk_redir ('remote_access_edit', array ('id' => $remote_access->id));
			}
			else return $this->mk_redir ('manage_access');
		}
		else return $this->mk_redir ('manage_access');
		
		$params = array ();
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		$ret = $this->mk_redir ('manage_access', $params);
		
		if ($this->vars['save'] and $customer->id)
		{
			$data = $this->vars['remote_access'];
			$remote_access->load_from_array ($data);
			
			if ($remote_access->is_valid_data ())
			{
				$remote_access->save_data ();
				$params['id'] = $remote_access->id;
				$ret = $this->mk_redir ('remote_access_edit', $params);
			}
			else
			{
				save_form_data ($data, 'remote_access');
				$params['customer_id'] = $customer->id;
				$params['public_ip'] = $this->vars['public_ip'];
				$ret = $this->mk_redir ('remote_access_add', $params);
			}
		}
		return $ret;
	}
	
	
	/** Edits remote access information */
	function remote_access_edit ()
	{
		$remote_access = new RemoteAccess ($this->vars['id']);
		if (!$remote_access->id) return $this->mk_redir ('manage_access');
		check_auth (array('customer_id' => $remote_access->customer_id));
		$tpl = 'remote_access_edit.tpl';
		
		$customer = new Customer ($remote_access->customer_id);
		
		// Load the previous form data if there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('remote_access', false, $data);
		$remote_access->load_from_array ($data, true);
		
		$params = array ('id' => $remote_access->id);
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		
		$this->assign ('remote_access', $remote_access);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('remote_access_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the details for a remote access IP */
	function remote_access_edit_submit ()
	{
		$remote_access = new RemoteAccess ($this->vars['id']);
		check_auth (array ('customer_id' => $remote_access->customer_id));
		
		$params = array ();
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		$ret = $this->mk_redir ('manage_access', $params);
		
		if ($this->vars['save'] and $remote_access->id)
		{
			$data = $this->vars['remote_access'];
			$remote_access->load_from_array ($data);
		
			if ($remote_access->is_valid_data ())
			{
				$remote_access->save_data ();
			}
			else
			{
				save_form_data ($data, 'remote_access');
			}
			$params['id'] = $remote_access->id;
			$ret = $this->mk_redir ('remote_access_edit', $params);
		}

		return $ret;
	}
	
	
	/** Deletes remote access data for a customer's public IP */
	function remote_access_delete ()
	{
		$remote_access = new RemoteAccess ($this->vars['id']);
		check_auth (array('customer_id' => $remote_access->customer_id));
		$ret = $this->mk_redir ('manage_access', array ('customer_id' => $remote_access->customer_id));
		
		if ($remote_access->id)
		{
			if ($remote_access->can_delete ()) $remote_access->delete ();
		}
		
		return $ret;
	}
	
        /****************************************************************/
	/* Computers remote services					*/
	/****************************************************************/
	
	/** Displays the page for quickly defining which remote services are available for a specific computer */
	function computer_remote_services ()
	{
		$tpl = 'computer_remote_services.tpl';
		$computer = new Computer ($this->vars['id']);
		if (!$computer->id) return $this->mk_redir ('manage_access');
		$customer = new Customer ($computer->customer_id);
		check_auth (array('customer_id' => $computer->customer_id));
		
		// Load the previous form data if there was an error
		if (!empty_error_msg()) 
		{
			$data = array ();
			restore_form_data ('computer_remote_services', false, $data);
			//$computer_remote_service->load_from_array ($data, true);
		}
		
		
		// Load the services already defined for this service
		$def_services = ComputerRemoteService::get_services (array('computer_id' => $computer->id));
		// Group the services by service type (the same as the keys from $GLOBALS['REMOTE_SERVICE_NAMES'])
		$services = array ();
		for ($i=0; $i<count($def_services); $i++) $services[$def_services[$i]->service_id][] = $def_services[$i];
		
		$params = $this->set_carry_fields (array('id', 'returl'));
		
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('services', $services);
		$this->assign ('REMOTE_SERVICE_NAMES', $GLOBALS['REMOTE_SERVICE_NAMES']);
		$this->assign ('REMOTE_SERVICES_PORTS', $GLOBALS['REMOTE_SERVICES_PORTS']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('computer_remote_services_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the remote access settings for a computer */
	function computer_remote_services_submit ()
	{
		$tpl = 'computer_remote_services.tpl';
		$computer = new Computer ($this->vars['id']);
		if (!$computer->id) return $this->mk_redir ('manage_access');
		$customer = new Customer ($computer->customer_id);
		check_auth (array('customer_id' => $computer->customer_id));
		
		$params = $this->set_carry_fields (array('id', 'returl'));
		if ($this->vars['returl']) $ret = $this->vars['returl'];
		else $ret = $this->mk_redir ('computer_remote_access', array('id' => $computer->id), 'kawacs');
		
		if ($this->vars['save'])
		{
			for ($i=0; $i<count($this->vars['services']); $i++)
			{
				$srv = &$this->vars['services'][$i];
				if ($srv['selected'])
				{
					if (!$srv['id'])
					{
						// This service was not defined before for this computer
						$service = new ComputerRemoteService ();
						$service->computer_id = $computer->id;
						$service->service_id = $srv['service_id'];
						$service->port = $srv['port'];
						if ($service->is_valid_data()) $service->save_data ();
					}
					else
					{
						// The service already exists, update the port number, in case it was changed
						$service = new ComputerRemoteService ($srv['id']);
						$service->port = $srv['port'];
						if ($service->is_valid_data()) $service->save_data ();
					}
				}
				elseif ($srv['id'])
				{
					// This is a service that existed, but was deselected now, so it needs to be deleted
					$service = new ComputerRemoteService ($srv['id']);
					$service->delete();
				}
			}
			$ret = $this->mk_redir ('computer_remote_services', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for defining a new computer remote access service */
	function computer_remote_service_add ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$tpl = 'computer_remote_service_add.tpl';
		
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_access');
		
		$computer_remote_service = new ComputerRemoteService ();
		// Load the previous form data if there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('computer_remote_service', false, $data);
		$computer_remote_service->load_from_array ($data, true);
		
		if (!$computer_remote_service->computer_id and $this->vars['computer_id'])
		{
			$computer_remote_service->computer_id = $this->vars['computer_id'];
		}
		
		$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
		
		$this->assign ('computer_remote_service', $computer_remote_service);
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('REMOTE_SERVICE_NAMES', $GLOBALS['REMOTE_SERVICE_NAMES']);
		$this->assign ('REMOTE_SERVICES_PORTS', $GLOBALS['REMOTE_SERVICES_PORTS']);
		$this->assign ('error_msg', error_msg ());
		
		$params = array ('customer_id' => $this->vars['customer_id']);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$this->set_form_redir ('computer_remote_service_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about the new service */
	function computer_remote_service_add_submit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$customer = new Customer ($this->vars['customer_id']);
		
		$params = array ('customer_id' => $this->vars['customer_id']);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$ret = $this->mk_redir ('manage_access', $params);
		
		if ($this->vars['save'] and $customer->id)
		{
			$data = $this->vars['computer_remote_service'];
			$data['is_web'] = ($data['is_web'] ? 1 : 0);
			$data['use_https'] = ($data['use_https'] ? 1 : 0);
			$computer_remote_service = new ComputerRemoteService ();
			$computer_remote_service->load_from_array ($data);
			
			if ($computer_remote_service->is_valid_data ())
			{
				$computer_remote_service->save_data ();
				$params['id'] = $computer_remote_service->id;
				$ret = $this->mk_redir ('computer_remote_service_edit', $params);
			}
			else
			{
				save_form_data ($data, 'computer_remote_service');
				$ret = $this->mk_redir ('computer_remote_service_add', $params);
			}
		}

		return $ret;
	}
	
	
	
	/** Displays the page for editing a computer remote service */
	function computer_remote_service_edit ()
	{
		$computer_remote_service = new ComputerRemoteService ($this->vars['id']);
		check_auth (array('computer_id' => $computer_remote_service->computer_id));
		$tpl = 'computer_remote_service_edit.tpl';
		
		$computer = new Computer ($computer_remote_service->computer_id);
		$customer = new Customer ($computer->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_access');
		
		// Load the previous form data if there was an error
		if (!empty_error_msg()) 
		{
			$data = array ();
			restore_form_data ('computer_remote_service', false, $data);
			$computer_remote_service->load_from_array ($data, true);
		}
		
		$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
		
		$this->assign ('computer_remote_service', $computer_remote_service);
		$this->assign ('customer', $customer);
		$this->assign ('computer', $computer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('REMOTE_SERVICE_NAMES', $GLOBALS['REMOTE_SERVICE_NAMES']);
		$this->assign ('REMOTE_SERVICES_PORTS', $GLOBALS['REMOTE_SERVICES_PORTS']);
		$this->assign ('error_msg', error_msg ());
		
		$params = array ('id');
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$this->set_form_redir ('computer_remote_service_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	
	/** Saves the information about the service */
	function computer_remote_service_edit_submit ()
	{
		$computer_remote_service = new ComputerRemoteService ($this->vars['id']);
		check_auth (array('computer_id' => $this->vars['computer_id']));
		
		$params = array ('id' => $computer_remote_service->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$ret = $this->mk_redir ('manage_access', $params);
		
		if ($this->vars['save'] and $computer_remote_service->id)
		{
			$data = $this->vars['computer_remote_service'];
			$data['is_web'] = ($data['is_web'] ? 1 : 0);
			$data['use_https'] = ($data['use_https'] ? 1 : 0);
			$computer_remote_service->load_from_array ($data);
			
			if ($computer_remote_service->is_valid_data ())
			{
				$computer_remote_service->save_data ();
			}
			else
			{
				save_form_data ($data, 'computer_remote_service');
			}
			$ret = $this->mk_redir ('computer_remote_service_edit', $params);
		}

		return $ret;
	}
	
	
	/** Deletes a computer remote service */
	function computer_remote_service_delete ()
	{
		$computer_remote_service = new ComputerRemoteService ($this->vars['id']);
		check_auth (array ('computer_id' => $computer_remote_service->computer_id));
		
		$params = array ();
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$ret = $this->mk_redir ('manage_access', $params);
		
		if ($computer_remote_service->id)
		{
			$computer_remote_service->delete ();
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Computers password					*/
	/****************************************************************/
	
	/** Displays the page for defining a new computer password */
	function computer_password_add ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$tpl = 'computer_password_add.tpl';
		
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_access');
		
		$computer_password = new ComputerPassword ();
		// Load the previous form data if there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('computer_password', false, $data);
		$computer_password->load_from_array ($data, true);
		
		if (!$computer_password->computer_id and $this->vars['computer_id'])
		{
			$computer_password->computer_id = $this->vars['computer_id'];
		}
		
		$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
		
		$this->assign ('computer_password', $computer_password);
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg ());
		
		$params = array ('customer_id' => $this->vars['customer_id']);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$this->set_form_redir ('computer_password_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about the new computer password */
	function computer_password_add_submit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$customer = new Customer ($this->vars['customer_id']);
		
		$params = array ('customer_id' => $this->vars['customer_id']);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		
		if ($this->vars['ret'] == 'remote_access')
			$ret = $this->mk_redir ('computer_remote_access', array ('id' => $this->vars['computer_id']), 'kawacs');
		else
			$ret = $this->mk_redir ('manage_access', $params);
		
		if ($this->vars['save'] and $customer->id)
		{
			$data = $this->vars['computer_password'];
			$computer_password = new ComputerPassword ();
			$computer_password->load_from_array ($data);
			$computer_password->customer_id = $customer->id;
			
			if ($computer_password->is_valid_data ())
			{
				$computer_password->save_data ();
				$params['id'] = $computer_password->id;
				$ret = $this->mk_redir ('computer_password_edit', $params);
			}
			else
			{
				save_form_data ($data, 'computer_password');
				$ret = $this->mk_redir ('computer_password_add', $params);
			}
		}

		return $ret;
	}
	
	
	
	/** Displays the page for editing a computer password */
	function computer_password_edit ()
	{
		$computer_password = new ComputerPassword ($this->vars['id']);
		check_auth (array('customer_id' => $computer_password->customer_id));
		$tpl = 'computer_password_edit.tpl';
		
		if ($computer_password->computer_id) $computer = new Computer ($computer_password->computer_id);
		$customer = new Customer ($computer_password->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_access');
		
		// Load the previous form data if there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('computer_password', false, $data);
		$computer_password->load_from_array ($data, true);
		
		$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
		
		$this->assign ('computer_password', $computer_password);
		$this->assign ('customer', $customer);
		$this->assign ('computer', $computer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg ());
		
		$params = array ('id'=>$computer_password->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$this->set_form_redir ('computer_password_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about the service */
	function computer_password_edit_submit ()
	{
		$computer_password = new ComputerPassword ($this->vars['id']);
		check_auth (array('customer_id' => $computer_password->customer_id));
		
		$params = array ('id' => $computer_password->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		
		if ($this->vars['ret'] == 'remote_access')
			$ret = $this->mk_redir ('computer_remote_access', array ('id' => $computer_password->computer_id), 'kawacs');
		else
			$ret = $this->mk_redir ('manage_access', $params);
		
		if ($this->vars['save'] and $computer_password->id)
		{
			$data = $this->vars['computer_password'];
			$computer_password->load_from_array ($data);
			
			if ($computer_password->is_valid_data ())
			{
				$computer_password->save_data ();
			}
			else
			{
				save_form_data ($data, 'computer_password');
			}
			$ret = $this->mk_redir ('computer_password_edit', $params);
		}

		return $ret;
	}
	
	
	/** Displays the page for expiring a computer password */
	function computer_password_expire ()
	{
		$computer_password = new ComputerPassword ($this->vars['id']);
		check_auth (array('customer_id' => $computer_password->customer_id));
		$tpl = 'computer_password_expire.tpl';
		
		if ($computer_password->computer_id) $computer = new Computer ($computer_password->computer_id);
		$customer = new Customer ($computer_password->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_access');
		
		
		$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
		
		$this->assign ('computer_password', $computer_password);
		$this->assign ('customer', $customer);
		$this->assign ('computer', $computer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg ());
		
		$params = array ('id' => $computer_password->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$this->set_form_redir ('computer_password_expire_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Expires a password */
	function computer_password_expire_submit ()
	{
		$computer_password = new ComputerPassword ($this->vars['id']);
		check_auth (array('customer_id' => $computer_password->customer_id));
		
		$params = array ('id' => $computer_password->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		
		if ($this->vars['ret'] == 'remote_access')
			$ret = $this->mk_redir ('computer_remote_access', array ('id' => $computer_password->computer_id), 'kawacs');
		else
			$ret = $this->mk_redir ('manage_access', $params);
		
		if ($this->vars['save'] and $computer_password->id)
		{
			$computer_password->date_removed = time ();
			$computer_password->save_data ();
			
			if ($this->vars['new_password'])
			{
				// Create the new replacement password
				$computer_password->id = null;
				$computer_password->password = $this->vars['new_password'];
				$computer_password->date_removed = 0;
				$computer_password->save_data ();
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page with the expired passwords for a computer */
	function computer_passwords_expired ()
	{
		$tpl = 'computer_passwords_expired.tpl';
		if ($this->vars['computer_id'])
		{
			check_auth (array('computer_id' => $this->vars['computer_id']));
			$computer = new Computer ($this->vars['computer_id']);
			$customer = new Customer ($computer->customer_id);
			if (!$computer->id) return $this->mk_redir ('manage_access');
			
			$passwords = ComputerPassword::get_passwords (array('computer_id' => $computer->id, 'include_expired' => true));
			$computers_list = Computer::get_computers_list (array ('customer_id'=>$computer->customer_id, 'append_id' => true));
		}
		elseif ($this->vars['customer_id'])
		{
			check_auth (array('customer_id' => $this->vars['customer_id']));
			$customer = new Customer ($this->vars['customer_id']);
			if (!$customer->id) return $this->mk_redir ('manage_access');
			
			$passwords = ComputerPassword::get_passwords (array('customer_id' => $customer->id, 'computer_id' => 0, 'include_expired' => true));
			$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
		}
		else return $this->mk_redir ('manage_access');
		
		$params = array ('id' => $computer_password->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['customer_id']) $params['customer_id'] = $this->vars['customer_id'];
		if ($this->vars['computer_id']) $params['computer_id'] = $this->vars['computer_id'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$return_url = $this->mk_redir ('manage_access', $params);
		
		$this->assign ('passwords', $passwords);
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('return_url', $return_url);
		
		$this->display ($tpl);
	}
	
	
	/** Deletes a computer remote service */
	function computer_password_delete ()
	{
		$computer_password = new ComputerPassword ($this->vars['id']);
		check_auth (array ('customer_id' => $computer_password->customer_id));
		
		$params = $this->set_carry_fields (array ('do_filter', 'computer_id', 'customer_id'));
		
		if ($this->vars['ret'] == 'expired')
			$ret = $this->mk_redir ('computer_passwords_expired', $params);
		elseif ($this->vars['ret'] == 'remote_access')
			$ret = $this->mk_redir ('computer_remote_access', array ('id' => $computer_password->computer_id), 'kawacs');
		else
			$ret = $this->mk_redir ('manage_access', $params);
	
		if ($computer_password->id)
		{
			$computer_password->delete ();
		}
		
		return $ret;
	}
	
	
	
	/****************************************************************/
	/* Access phone numbers						*/
	/****************************************************************/
	
	/** Shows the page for managing access phone numbers */
	function manage_access_phones ()
	{
		$tpl = 'manage_access_phones.tpl';
		
		$extra_params = array();	// Extra parameters to be carried in navigation
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;
		
		if ($this->vars['set_filter'])
		{
			$_SESSION['manage_access_phones']['customer_id'] = $this->vars['set_filter'];
		}
		elseif (isset($this->vars['customer_id']))
		{
			$_SESSION['manage_access_phones']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_access_phones']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_access_phones'];

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			
			unset ($_SESSION['manage_access_phones']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_access_phones']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kawacs customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		// If a customer was selected, fetch the data for that customer
		if ($filter['customer_id'] > 0)
		{
			$customer = new Customer ($filter['customer_id']);
			// Mark the potential customer for locking
			$_SESSION['potential_lock_customer_id'] = $customer->id;
			
			$access_phones = AccessPhone::get_access_phones (array('customer_id' => $customer->id));
			$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
			$peripherals_list = Peripheral::get_peripherals_list (array ('customer_id'=>$customer->id));
			
			$this->assign ('customer', $customer);
			$this->assign ('access_phones', $access_phones);
			$this->assign ('computers_list', $computers_list);
			$this->assign ('peripherals_list', $peripherals_list);
			$this->assign ('PHONE_ACCESS_DEVICES', $GLOBALS['PHONE_ACCESS_DEVICES']);
		}

		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg());
		$this->set_form_redir ('manage_access_phones_submit');
		$this->display($tpl);
	}
	
	
	function manage_access_phones_submit ()
	{
		check_auth ();
		
		$extra_params = array();
		$_SESSION['manage_access_phones'] = $this->vars['filter'];
		
		if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
		{
			$extra_params['do_filter'] = 1;
		}
		
		return $this->mk_redir('manage_access_phones', $extra_params);
	}
	
	
	/** Displays the page for defining a new access number */
	function access_phone_add ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$tpl = 'access_phone_add.tpl';
		
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_access_phones');
		$access_phone = new AccessPhone ();
		
		// Load the previously submitted data, in case there was an error
		$access_phone_data = array ();
		if (!empty_error_msg()) restore_form_data ('access_phone', false, $access_phone_data);
		$access_phone->load_from_array ($access_phone_data, true);
		
		$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
		$peripherals_list = Peripheral::get_peripherals_list (array ('customer_id'=>$customer->id));
		
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		$this->assign ('customer', $customer);
		$this->assign ('access_phone', $access_phone);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('peripherals_list', $peripherals_list);
		$this->assign ('PHONE_ACCESS_DEVICES', $GLOBALS['PHONE_ACCESS_DEVICES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('access_phone_add_submit', array ('customer_id' => $customer->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves a new access phone number */
	function access_phone_add_submit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		$customer = new Customer ($this->vars['customer_id']);
		$ret = $this->mk_redir ('manage_access_phones', array ('customer_id', $customer_id));
		
		if ($this->vars['save'] and $customer->id)
		{
			$access_phone = new AccessPhone ();
			$data = $this->vars['access_phone'];
			if ($data['device_type'] == PHONE_ACCESS_DEV_COMPUTER)
				$data['object_id'] = $this->vars['computer_id'];
			elseif ($data['device_type'] == PHONE_ACCESS_DEV_PERIPHERAL)
				$data['object_id'] = $this->vars['peripheral_id'];
			else
				$data['object_id'] = 0;
			$data['customer_id'] = $customer->id;
				
			$access_phone->load_from_array ($data);
			
			if ($access_phone->is_valid_data ())
			{
				$access_phone->save_data ();
				$ret = $this->mk_redir ('access_phone_edit', array ('id' => $access_phone->id));
			}
			else
			{
				save_form_data ($data, 'access_phone');
				$ret = $this->mk_redir ('access_phone_add', array ('customer_id' => $customer->id));
			}
		}
		return $ret;
	}
	
	
	/** Displays the page for editing an access number */
	function access_phone_edit ()
	{
		$tpl = 'access_phone_edit.tpl';
		
		$access_phone = new AccessPhone ($this->vars['id']);
		if (!$access_phone->id) return $this->mk_redir ('manage_access_phones');
		check_auth (array('customer_id' => $access_phone->customer_id));
		
		// Load the previously submitted data, in case there was an error
		$access_phone_data = array ();
		if (!empty_error_msg()) restore_form_data ('access_phone', false, $access_phone_data);
		$access_phone->load_from_array ($access_phone_data, true);
		
		$customer = new Customer ($access_phone->customer_id);
		$computers_list = Computer::get_computers_list (array ('customer_id'=>$customer->id, 'append_id' => true));
		$peripherals_list = Peripheral::get_peripherals_list (array ('customer_id'=>$customer->id));
		
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		$this->assign ('customer', $customer);
		$this->assign ('access_phone', $access_phone);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('peripherals_list', $peripherals_list);
		$this->assign ('PHONE_ACCESS_DEVICES', $GLOBALS['PHONE_ACCESS_DEVICES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('access_phone_edit_submit', array ('id' => $access_phone->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves an access phone number */
	function access_phone_edit_submit ()
	{
		$access_phone = new AccessPhone ($this->vars['id']);
		check_auth (array('customer_id' => $access_phone->customer_id));
		$ret = $this->mk_redir ('manage_access_phones', array ('customer_id', $access_phone->customer_id));
		
		if ($this->vars['save'] and $access_phone->id)
		{
			$data = $this->vars['access_phone'];
			if ($data['device_type'] == PHONE_ACCESS_DEV_COMPUTER)
				$data['object_id'] = $this->vars['computer_id'];
			elseif ($data['device_type'] == PHONE_ACCESS_DEV_PERIPHERAL)
				$data['object_id'] = $this->vars['peripheral_id'];
			else
				$data['object_id'] = 0;
			
			$access_phone->load_from_array ($data);
			
			if ($access_phone->is_valid_data ())
			{
				$access_phone->save_data ();
			}
			else
			{
				save_form_data ($data, 'access_phone');
			}
			$ret = $this->mk_redir ('access_phone_edit', array ('id' => $access_phone->id));
		}
		return $ret;
	}
	
	
	/** Deletes an access phone */
	function access_phone_delete ()
	{
		$access_phone = new AccessPhone ($this->vars['id']);
		check_auth (array ('customer_id' => $access_phone->customer_id));
		$ret = $this->mk_redir ('manage_access_phones', array ('customer_id' => $access_phone->customer_id));
		
		if ($access_phone->id)
		{
			$access_phone->delete ();
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Internet Service Providers					*/
	/****************************************************************/
	
	/** Manage Internet Providers */
	function manage_providers ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'manage_providers.tpl';
		
		$providers = Provider::get_providers ();
		
		$this->assign ('providers', $providers);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	
	/** Displays the page for adding a new Internet Provider */
	function provider_add ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'provider_add.tpl';
		
		$provider = new Provider ();
		
		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('provider', false, $data);
		$provider->load_from_array ($data, true);
		
		$this->assign ('provider', $provider);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('provider_add_submit');
		
		$this->display ($tpl);
	}
	
	
	/** Saves the new Internet Provider */
	function provider_add_submit ()
	{
		check_auth ();
		class_load ('Provider');
		$ret = $this->mk_redir ('manage_providers');
		
		if ($this->vars['save'])
		{
			$data = $this->vars['provider'];
			$provider = new Provider ();
			$provider->load_from_array ($data);
			
			if ($provider->is_valid_data ())
			{
				$provider->save_data ();
				$ret = $this->mk_redir ('provider_edit', array ('id' => $provider->id));
			}
			else
			{
				save_form_data ($data, 'provider');
				$ret = $this->mk_redir ('provider_add');
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing an Internet Provider */
	function provider_edit ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'provider_edit.tpl';
		
		$provider = new Provider ($this->vars['id']);
		if (!$provider->id) return ($this->mk_redir ('manage_providers'));
		
		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('provider', false, $data);
		$provider->load_from_array ($data, true);
		
		$params = array ('id' => $provider->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
		
		$this->assign ('provider', $provider);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('returl', urlencode($this->vars['returl'])); 
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('provider_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about service providers */
	function provider_edit_submit ()
	{
		check_auth ();
		class_load ('Provider');
		$provider = new Provider ($this->vars['id']);
		
		$params = array ('id' => $provider->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
		
		if ($this->vars['returl']) $ret = $this->vars['returl'];
		else $ret = $this->mk_redir ('manage_providers');
		
		if ($this->vars['save'] and $provider->id)
		{
			$data = $this->vars['provider'];
			$provider->load_from_array ($data);
			
			if ($provider->is_valid_data ())
			{
				$provider->save_data ();
			}
			else
			{
				save_form_data ($data, 'provider');
			}
			$ret = $this->mk_redir ('provider_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page showing the list of customers for a certain provider */
	function provider_customers ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'provider_customers.tpl';
		$provider = new Provider ($this->vars['id']);
		
		if (!$provider->id) return $this->mk_redir ('manage_providers');
		
		$provider_customers_list = $provider->get_customers_list ($this->current_user);
		$customers_list = Customer::get_customers_list ();
		$contracts_list = ProviderContract::get_contracts_list ();
		
		$this->assign ('provider', $provider);
		$this->assign ('provider_customers_list', $provider_customers_list);
		$this->assign ('contracts_list', $contracts_list);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	
	/** Deletes an Internet provider */
	function provider_delete ()
	{
		check_auth ();
		class_load ('Provider');
		$provider = new Provider ($this->vars['id']);
		$ret = $this->mk_redir ('manage_providers');
		
		if ($provider->can_delete ())
		{
			$provider->delete ();
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Internet Service Providers Contracts				*/
	/****************************************************************/
	
	/** Displays the page for adding a new contract */
	function provider_contract_add ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'provider_contract_add.tpl';
		$provider = new Provider ($this->vars['provider_id']);
		$contract = new ProviderContract ();
		
		if (!$provider->id) return $this->mk_redir ('manage_providers');

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('provider_contract', false, $data);
		$contract->load_from_array ($data, true);
		
		$params = array ('id' => $provider->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
		
		$this->assign ('provider', $provider);
		$this->assign ('contract', $contract);
		$this->assign ('returl', urlencode($this->vars['returl'])); 
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('provider_contract_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the new provider contract */
	function provider_contract_add_submit ()
	{
		check_auth ();
		class_load ('Provider');
		$provider = new Provider ($this->vars['provider_id']);
		
		$params = array ();
		$carry_fields = array ('returl', 'provider_id');
		foreach ($carry_fields as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
		
		if ($this->vars['save'] and $provider->id)
		{
			$data = $this->vars['contract'];
			$contract = new ProviderContract ();
			$contract->load_from_array ($data);
			$contract->provider_id = $provider->id;
			
			if ($contract->is_valid_data ())
			{
				$contract->save_data ();
				$params['id'] = $contract->id;
				$ret = $this->mk_redir ('provider_contract_edit', $params);
			}
			else
			{
				save_form_data ($data, 'provider_contract');
				$ret = $this->mk_redir ('provider_contract_add', $params);
			}
		}
		else
		{
			$params['id'] = $provider->id;
			$ret = $this->mk_redir ('provider_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a provider contract */
	function provider_contract_edit ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'provider_contract_edit.tpl';
		$contract = new ProviderContract ($this->vars['id']);
		$provider = new Provider ($contract->provider_id);
		
		if (!$contract->id or !$provider->id) return $this->mk_redir ('manage_providers');

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('provider_contract', false, $data);
		$contract->load_from_array ($data, true);
		
		$params = array ('id', $contract->id);
		$carry_fields = array ('provider_id', 'returl');
		foreach ($carry_fields as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
		
		$this->assign ('provider', $provider);
		$this->assign ('contract', $contract);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('provider_contract_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about the provider contract */
	function provider_contract_edit_submit ()
	{
		check_auth ();
		class_load ('Provider');
		$contract = new ProviderContract ($this->vars['id']);
		
		$params = array ();
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
		
		if ($this->vars['save'] and $contract->id)
		{
			$data = $this->vars['contract'];
			$contract->load_from_array ($data);
			
			if ($contract->is_valid_data ())
			{
				$contract->save_data ();
			}
			else
			{
				save_form_data ($data, 'provider_contract');
			}
			$params['id'] = $contract->id;
			$ret = $this->mk_redir ('provider_contract_edit', $params);
		}
		else
		{
			$params['id'] = $contract->provider_id;
			$ret = $this->mk_redir ('provider_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Deletes a contract from a Internet provider */
	function provider_contract_delete ()
	{
		check_auth ();
		class_load ('Provider');
		$contract = new ProviderContract ($this->vars['id']);
		
		$params = array ('id' => $contract->provider_id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
		
		$ret = $this->mk_redir ('provider_edit', $params);
		
		if ($contract->can_delete ())
		{
			$contract->delete ();
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Internet Service Providers Contacts				*/
	/****************************************************************/
	
	/** Displays the page for adding a new provider contact */
	function provider_contact_add ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'provider_contact_add.tpl';
		$provider = new Provider ($this->vars['provider_id']);
		$contact = new ProviderContact ();
		
		if (!$provider->id) return $this->mk_redir ('manage_providers');

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('provider_contact', false, $data);
		$contact->load_from_array ($data, true);
		
		$params = array ('provider_id', $provider->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
		
		$this->assign ('provider', $provider);
		$this->assign ('contact', $contact);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('provider_contact_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the new provider contact */
	function provider_contact_add_submit ()
	{
		check_auth ();
		class_load ('Provider');
		$provider = new Provider ($this->vars['provider_id']);
		
		$params = $this->set_carry_fields (array('returl'));
		
		if ($this->vars['save'] and $provider->id)
		{
			$data = $this->vars['contact'];
			$contact = new ProviderContact ();
			$contact->load_from_array ($data);
			$contact->provider_id = $provider->id;
			
			if ($contact->is_valid_data ())
			{
				$contact->save_data ();
				$params['id'] = $contact->id;
				$ret = $this->mk_redir ('provider_contact_edit', $params);
			}
			else
			{
				save_form_data ($data, 'provider_contact');
				$params['provider_id'] = $this->vars['provider_id'];
				$ret = $this->mk_redir ('provider_contact_add', $params);
			}
		}
		else
		{
			$params['id'] = $provider->id;
			$ret = $this->mk_redir ('provider_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a provider contact */
	function provider_contact_edit ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'provider_contact_edit.tpl';
		$contact = new ProviderContact ($this->vars['id']);
		$provider = new Provider ($contact->provider_id);
		
		if (!$contact->id or !$provider->id) return $this->mk_redir ('manage_providers');

		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('provider_contact', false, $data);
		$contact->load_from_array ($data, true);
		
		$params = $this->set_carry_fields (array('returl'), array ('id' => $contact->id));
		
		$this->assign ('provider', $provider);
		$this->assign ('contact', $contact);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('ret_url', urlencode($this->vars['returl']));
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('provider_contact_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about the provider contact */
	function provider_contact_edit_submit ()
	{
		check_auth ();
		class_load ('Provider');
		$contact = new ProviderContact ($this->vars['id']);
		
		$params = $this->set_carry_fields (array('returl'));
		
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
				save_form_data ($data, 'provider_contact');
			}
			$params['id'] = $contact->id;
			$ret = $this->mk_redir ('provider_contact_edit', $params);
		}
		else
		{
			$params['id'] = $contact->provider_id;
			$ret = $this->mk_redir ('provider_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Deletes a provider contact */
	function provider_contact_delete ()
	{
		check_auth ();
		class_load ('Provider');
		$contact = new ProviderContact ($this->vars['id']);
		
		$params = $this->set_carry_fields (array('returl'), array ('id'=>$contact->provider_id));
		$ret = $this->mk_redir ('provider_edit', $params);
		
		if ($contact->id) $contact->delete ();
		return $ret;
	}
	
	
	/****************************************************************/
	/* Internet Service Providers Contacts				*/
	/****************************************************************/
	
	/** Displays the page for adding a new phone number for a provider contact */
	function provider_contact_phone_add ()
	{
		check_auth ();
		class_load ('Provider');
		$contact = new ProviderContact ($this->vars['contact_id']);
		$provider = new Provider ($contact->provider_id);
		if (!$provider->id) return $this->mk_redir ('manage_providers');
		
		$tpl = 'provider_contact_phone_add.tpl';

		$phone = new ProviderContactPhone ();
		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('provider_contact_phone', false, $data);
		$phone->load_from_array ($data, true);
		
		$params = $this->set_carry_fields (array('contact_id', 'returl'));
	
		$this->assign ('phone', $phone);
		$this->assign ('contact', $contact);
		$this->assign ('provider', $provider);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('provider_contact_phone_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Saves a new phone number for a contact */
	function provider_contact_phone_add_submit ()
	{ 
		check_auth ();
		class_load ('Provider');
		$contact = new ProviderContact ($this->vars['contact_id']);
		
		$params = $this->set_carry_fields (array('returl'));
		
		if ($this->vars['save'] and $contact->id)
		{
			$data = $this->vars['phone'];
			$phone = new ProviderContactPhone ();
			$phone->load_from_array ($data);
			$phone->contact_id = $contact->id;
			
			if ($phone->is_valid_data ())
			{
				$phone->save_data ();
				$params['id'] = $phone->id;
				$ret = $this->mk_redir ('provider_contact_phone_edit', $params);
			}
			else
			{
				save_form_data ($data, 'provider_contact_phone');
				$params['contact_id'] = $contact->id;
				$ret = $this->mk_redir ('provider_contact_phone_add', $params);
			}
		}
		else
		{
			$params['id'] = $contact->id;
			$ret = $this->mk_redir ('provider_contact_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a provider contact phone number */
	function provider_contact_phone_edit ()
	{
		check_auth ();
		class_load ('Provider');
		$tpl = 'provider_contact_phone_edit.tpl';
		
		$phone = new ProviderContactPhone ($this->vars['id']);
		$contact = new ProviderContact ($phone->contact_id);
		$provider = new Provider ($contact->provider_id);
		if (!$provider->id) return $this->mk_redir ('manage_providers');
		
		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('provider_contact_phone', false, $data);
		$phone->load_from_array ($data, true);
		
		$params = $this->set_carry_fields (array('returl', 'id'));
	
		$this->assign ('phone', $phone);
		$this->assign ('contact', $contact);
		$this->assign ('provider', $provider);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('provider_contact_phone_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves a phone number for a provider contact */
	function provider_contact_phone_edit_submit ()
	{ 
		check_auth ();
		class_load ('Provider');
		$phone = new ProviderContactPhone ($this->vars['id']);
		$contact = new ProviderContact ($phone->contact_id);
		
		$params = $this->set_carry_fields (array('returl'));
		
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
				save_form_data ($data, 'provider_contact_phone');
			}
			$params['id'] = $phone->id;
			$ret = $this->mk_redir ('provider_contact_phone_edit', $params);
		}
		else
		{
			$params['id'] = $contact->id;
			$ret = $this->mk_redir ('provider_contact_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Deletes a phone number for a provider contact */
	function provider_contact_phone_delete ()
	{
		check_auth ();
		class_load ('Provider');
		$phone = new ProviderContactPhone ($this->vars['id']);
		
		$params = $this->set_carry_fields (array('returl'), array ('id' => $phone->contact_id));
		$ret = $this->mk_redir ('provider_contact_edit', $params);
		
		if ($phone->id) $phone->delete ();
		
		return $ret;
	}
	
	
	
	/****************************************************************/
	/* Customers Internet Contracts					*/
	/****************************************************************/
	
	/** Displays the page for managing customer Internet contracts */
	function manage_customer_internet_contracts () 
	{
		class_load ('CustomerInternetContract');
		$tpl = 'manage_customer_internet_contracts.tpl';
		
		$extra_params = array();	// Extra parameters to be carried in navigation
		if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;
		
		if ($this->vars['set_filter'])
		{
			$_SESSION['manage_customer_internet_contracts']['customer_id'] = $this->vars['set_filter'];
		}
		elseif (isset($this->vars['customer_id']))
		{
			$_SESSION['manage_customer_internet_contracts']['customer_id'] = $this->vars['customer_id'];
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_customer_internet_contracts']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_customer_internet_contracts'];

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			
			unset ($_SESSION['manage_customer_internet_contracts']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_customer_internet_contracts']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kawacs customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('show_ids' => 1, 'favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		// If a customer was selected, fetch the data for that customer
		if ($filter['customer_id'] > 0)
		{
			$customer = new Customer ($filter['customer_id']);
			// Mark the potential customer for locking
			$_SESSION['potential_lock_customer_id'] = $customer->id;
			$this->assign ('customer', $customer);
		}
		$contracts = CustomerInternetContract::get_contracts (array('customer_id' => $filter['customer_id'], 'load_details' => true));
		
		if ($this->vars['do_filter']) $this->assign ('do_filter_url', '&do_filter=1');
		$this->assign ('contracts', $contracts);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('LINE_TYPES', $GLOBALS['LINE_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('manage_customer_internet_contracts_submit');
		
		$this->display ($tpl);
	}
	
	
	function manage_customer_internet_contracts_submit ()
	{
		check_auth ();
		
		$extra_params = array();
		$_SESSION['manage_customer_internet_contracts'] = $this->vars['filter'];
		
		if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
		{
			$extra_params['do_filter'] = 1;
		}
		
		return $this->mk_redir('manage_customer_internet_contracts', $extra_params);
	}
	
	
	/** Displays the page for adding a new Internet contract for a customer */
	function customer_internet_contract_add ()
	{
		class_load ('CustomerInternetContract');
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ($this->vars['returl']);
		check_auth (array('customer_id' => $customer->id));
		$tpl = 'customer_internet_contract_add.tpl';
		
		// Load the previously submitted data, in case there was an error
		$contract = new CustomerInternetContract ();
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_internet_contract', false, $data);
		$contract->load_from_array ($data, true);
		
		// Get the list of available provider contracts
		$contracts_list = ProviderContract::get_contracts_list (array('prefix_providers' => true));
		
		// Set the parameters that need to be carried in navigation
		$params = array ('customer_id' => $customer->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars['returl'])) $params[$field] = $this->vars[$field];
		
		$this->assign ('customer', $customer);
		$this->assign ('contract', $contract);
		$this->assign ('contracts_list', $contracts_list);
		$this->assign ('LINE_TYPES', $GLOBALS['LINE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('customer_internet_contract_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves a newly defined customer contract */
	function customer_internet_contract_add_submit ()
	{
		class_load ('CustomerInternetContract');
		$customer = new Customer ($this->vars['customer_id']);
		
		$params = array ('customer_id' => $customer->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars['returl'])) $params[$field] = $this->vars[$field];
		
		if ($this->vars['returl']) $ret = $this->vars['returl'];
		else $ret = $this->mk_redir ('manage_customer_internet_contracts');
		
		if ($this->vars['save'] and $customer->id)
		{
			$data = $this->vars['contract'];
			if ($data['start_date']) $data['start_date'] = js_strtotime ($data['start_date']);
			if ($data['end_date']) $data['end_date'] = js_strtotime ($data['end_date']);
			
			$contract = new CustomerInternetContract ();
			$contract->load_from_array ($data);
			$contract->customer_id = $customer->id;
			
			if ($contract->is_valid_data ())
			{
				$contract->save_data ();
				$params['id'] = $contract->id;
				unset ($params['customer_id']);
				$ret = $this->mk_redir ('customer_internet_contract_edit', $params);
			}
			else
			{
				save_form_data ($data, 'customer_internet_contract');
				$ret = $this->mk_redir ('customer_internet_contract_add', $params);
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing an Internet contract for a customer */
	function customer_internet_contract_edit ()
	{
		class_load ('CustomerInternetContract');
		$contract = new CustomerInternetContract ($this->vars['id']);
		$customer = new Customer ($contract->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_customer_internet_contracts');
		check_auth (array('customer_id' => $customer->id));
		
		if ($this->current_user->is_customer_user()) $tpl = 'customer_internet_contract_view.tpl';
		else $tpl = 'customer_internet_contract_edit.tpl';
		
		// Load the previously submitted data, in case there was an error
		$data = array ();
		if (!empty_error_msg()) restore_form_data ('customer_internet_contract', false, $data);
		$contract->load_from_array ($data, true);
		$contract->load_details ();
		 
		// Get the list of available provider contracts
		$contracts_list = ProviderContract::get_contracts_list (array('prefix_providers' => true));
		
		if ($contract->notification->id)
		{
			// Mark the associated notification as being read and update the counter
			$contract->notification->mark_read ($this->current_user->id);
			$this->update_unread_notifs ();
		}
		
		// Set the parameters that need to be carried in navigation
		$params = array ('id' => $contract->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars['returl'])) $params[$field] = $this->vars[$field];
		
		$this->assign ('customer', $customer);
		$this->assign ('contract', $contract);
		$this->assign ('contracts_list', $contracts_list);
		$this->assign ('LINE_TYPES', $GLOBALS['LINE_TYPES']);
		$this->assign ('error_msg', error_msg ());
		$this->assign ('ret_url', urlencode($this->mk_redir('customer_internet_contract_edit', $params)));
		$this->set_form_redir ('customer_internet_contract_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves a customer Internet contract */
	function customer_internet_contract_edit_submit ()
	{
		class_load ('CustomerInternetContract');
		$contract = new CustomerInternetContract ($this->vars['id'], true);
		$customer = new Customer ($contract->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_customer_internet_contracts');
		check_auth (array('customer_id' => $customer->id));
		
		$params = array ('id' => $contract->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars['returl'])) $params[$field] = $this->vars[$field];
		
		if ($this->vars['returl']) $ret = $this->vars['returl'];
		else $ret = $this->mk_redir ('manage_customer_internet_contracts');
		
		if ($this->vars['save'] and $contract->id)
		{
			$data = $this->vars['contract'];
			if ($data['start_date']) $data['start_date'] = js_strtotime ($data['start_date']);
			if ($data['end_date']) $data['end_date'] = js_strtotime ($data['end_date']);
			
			$contract->load_from_array ($data);
			
			if ($contract->is_valid_data ())
			{
				$contract->save_data ();
				// Runs the checks for expiration
				CustomerInternetContract::check_expirations ();
			}
			else
			{
				save_form_data ($data, 'customer_internet_contract');
			}
			$ret = $this->mk_redir ('customer_internet_contract_edit', $params);
		}
		
		return $ret;
	}
	
	/** Sets or removes the 'suspend_notifs' flag for a contract */
	function customer_internet_contract_set_notifs ()
	{
		class_load ('CustomerInternetContract');
		$contract = new CustomerInternetContract ($this->vars['id']);
		check_auth (array('customer_id' => $contract->customer_id));
		
		if ($contract->id)
		{
			if ($this->vars['suspend_notifs']) $contract->suspend_notifications ();
			else $contract->unsuspend_notifications ();
		}
		
		return $this->mk_redir ('customer_internet_contract_edit', array('id'=>$contract->id));
	}
	
	/** Clears the 'date_notified' flag for a contract */
	function customer_internet_contract_remove_mark ()
	{
		class_load ('CustomerInternetContract');
		$contract = new CustomerInternetContract ($this->vars['id']);
		check_auth (array('customer_id' => $contract->customer_id));
		
		if ($contract->id) $contract->remove_notified_mark ();
		
		return $this->mk_redir ('customer_internet_contract_edit', array('id'=>$contract->id));
	}
	
	/** Clears the 'notice_again_sent' flag for a contract */
	function customer_internet_contract_remove_again_mark ()
	{
		class_load ('CustomerInternetContract');
		$contract = new CustomerInternetContract ($this->vars['id']);
		check_auth (array('customer_id' => $contract->customer_id));
		
		if ($contract->id) $contract->remove_notified_again_mark ();
		
		return $this->mk_redir ('customer_internet_contract_edit', array('id'=>$contract->id));
	}
	
	/** Deletes a customer internet contract */
	function customer_internet_contract_delete ()
	{
		class_load ('CustomerInternetContract');
		$contract = new CustomerInternetContract($this->vars['id']);
		check_auth (array('customer_id' => $contract->customer_id));
		
		if ($this->vars['returl']) $ret = $this->vars['returl'];
		else $ret = $this->mk_redir ('manage_customer_internet_contracts');
		
		if ($contract->id) $contract->delete ();
		
		return $ret;
	}
	
	
	/** Displays the page for adding an attachment to a contract */
	function customer_internet_contract_attachment_add ()
	{
		class_load ('CustomerInternetContract');
		$tpl = 'customer_internet_contract_attachment_add.tpl';
		$contract = new CustomerInternetContract ($this->vars['id'], true);
		$customer = new Customer ($contract->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_customer_internet_contracts');
		check_auth (array('customer_id' => $customer->id));
		
		$params = array ('id' => $contract->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars['returl'])) $params[$field] = $this->vars[$field];
	
		$this->assign ('contract', $contract);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('customer_internet_contract_attachment_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Adds an attachment to a customer internet contract */
	function customer_internet_contract_attachment_add_submit ()
	{
		class_load ('CustomerInternetContract');
		$contract = new CustomerInternetContract ($this->vars['id']);
		$customer = new Customer ($contract->customer_id);
		if (!$customer->id) return $this->mk_redir ('manage_customer_internet_contracts');
		check_auth (array('customer_id' => $customer->id));
	
		$params = array ('id' => $contract->id);
		$carry_fields = array ('returl');
		foreach ($carry_fields as $field) if (isset($this->vars['returl'])) $params[$field] = $this->vars[$field];
	
		if ($this->vars['returl']) $ret = $this->vars['returl'];
		else $ret = $this->mk_redir ('customer_internet_contract_edit', $params);
	
		if ($this->vars['save'] and $contract->id)
		{
			if ($_FILES['attachment']['name'])
			{
				$data = array (
					'name' =>  $_FILES['attachment']['name'],
					'tmp_name' => $_FILES['attachment']['tmp_name'],
					'contract_id' => $contract->id
				);
				$attachment = new CustomerInternetContractAttachment ();
				$attachment->load_from_array ($data);

				if ($attachment->is_valid_data ())
				{
					$attachment->save_data ();
				}
				else
				{
					$ret = $this->mk_redir ('customer_internet_contract_attachment_add', $params);
				}
			}
			else
			{
				error_msg ('Please specify an attachment to upload');
				$ret = $this->mk_redir ('customer_internet_contract_attachment_add', $params);
			}
		}
		
		return $ret;
	}
	
	
	/** Serves an attachment from a customer internet contract */
	function customer_internet_contract_attachment_open ()
	{
		class_load ('CustomerInternetContract');
		$attachment = new CustomerInternetContractAttachment ($this->vars['attachment_id']);
		$contract = new CustomerInternetContract ($attachment->customer_internet_contract_id);
		check_auth (array('customer_id' => $contract->customer_id));
		
		if (!$attachment->local_filename or !file_exists(DIR_UPLOAD_KLARA.'/'.$attachment->local_filename))
		{
			error_msg ('Sorry, the attachment file is missing');
			if ($this->vars['returl']) $ret = $this->vars['returl'];
			$ret = $this->mk_redir ('customer_internet_contract_edit', array ('id' => $contract_id));
		}
		else
		{
			header ("Pragma: public");
			header ("Cache-Control: private");
			header ("Content-Transfer-Encoding: none");
			header ("Content-type: application/octet-stream;");
			header ("Content-Disposition: attachment; filename=".$attachment->original_filename.";");
			header ('Content-Length: '.filesize(DIR_UPLOAD_KLARA.'/'.$attachment->local_filename));
			header ("Connection: close");
			
			readfile(DIR_UPLOAD_KLARA.'/'.$attachment->local_filename);
			exit;
		}
	}
	
	
	/** Deletes an attachment from a customer internet contract */
	function customer_internet_contract_attachment_delete ()
	{
		class_load ('CustomerInternetContract');
		$attachment = new CustomerInternetContractAttachment ($this->vars['attachment_id']);
		$contract = new CustomerInternetContract ($attachment->customer_internet_contract_id);
		check_auth (array('customer_id' => $contract->customer_id));
		$ret = $this->vars['returl'];
		
		if ($attachment->id) $attachment->delete ();
		
		return $ret;
	}
        
        
        /**************************** WEB ACCESS ********************************************************/
        function webaccess_add(){
            check_auth(array('customer_id' => $this->vars['customer_id']));
            class_load('WebAccess');
            class_load('WebAccessResource');
            class_load('Customer');
            
            $tpl = 'webaccess_add.tpl';
            $customer = new Customer($this->vars['customer_id']);
            if(!$customer->id) {
                return $this->mk_redir('manage_access');
            }
           
            $webaccess = array();
            if(isset($_SESSION['klara']['webaccess_add'])){
                $webaccess = $_SESSION['klara']['webaccess_add'];
                unset($_SESSION['klara']['webaccess_add']);
            }
            
            
            $this->assign('webaccess', $webaccess);
            $this->assign('error_msg', error_msg());           
            $this->set_form_redir('webaccess_add_submit', array('customer_id' => $customer->id));                         
            $this->display($tpl);
            
        }
        
        function webaccess_add_submit(){                 
            check_auth(array('customer_id' => $this->vars['customer_id']));
            class_load('WebAccess');
            class_load('WebAccessResource');
            class_load('Customer');
            $customer = new Customer($this->vars['customer_id']);
            if(!$customer->id){
                return $this->mk_redir('manage_access');
            }
            
            if($this->vars['save']){
                $webaccess_data = $this->vars['webaccess'];
                $webaccess = new WebAccess();
                $webaccess->load_from_array($webaccess_data);
                $webaccess->customer_id = $customer->id;
                $webaccess->date_added = time();
                $webaccess->date_modified = time();
                $webaccess->user_id = $this->current_user->id;
                $webaccess->load_additional_data($webaccess_data);
                
                if($webaccess->is_valid_data()){                 
                    if($webaccess->save_data()){
                        //success -> return to the edit page
                        return $this->mk_redir('webaccess_edit', array('waid' => $webaccess->id));
                    } else {
                        $_SESSION['klara']['webaccess_add'] = $this->vars['webaccess'];
                        return $this->mk_redir('webaccess_add', array('customer_id' => $customer->id));                        
                    }
                } else {
                    $_SESSION['klara']['webaccess_add'] = $this->vars['webaccess'];
                    return $this->mk_redir('webaccess_add', array('customer_id' => $customer->id));
                }             
            }  else {
                return $this->mk_redir('manage_access');
            }                        
        }
        
        function webaccess_edit(){
            check_auth(array('id' => $this->vars['waid']));
            class_load('WebAccess');
            class_load('WebAccessResource');
            $tpl = 'webaccess_edit.tpl';
            
            $webaccess = new WebAccess($this->vars['waid']);
            if(!$webaccess->id) {
                return $this->mk_redir('manage_access');
            }
            
            if(isset($_SESSION['klara']['webaccess_edit'])){
                $webaccess->load_from_array($_SESSION['klara']['webaccess']);
                unset($_SESSION['klara']['webaccess_edit']);
            }
            
            $this->assign('i', count($webaccess->credentials));
            $this->assign('webaccess', $webaccess);
            $this->set_form_redir('webaccess_edit_submit', array('id' => $webaccess->id));
            $this->assign('error_msg', error_msg());
            $this->display($tpl);
        }
        function webaccess_edit_submit(){
            check_auth(array('id' => $this->vars['id']));
            class_load('WebAccess');
            class_load('WebAccessResource');
            $webaccess = new WebAccess($this->vars['id']);
            if(!$webaccess->id){
                return $this->mk_redir('manage_access');
            }
            if($this->vars['save']){
                $webaccess_data = $this->vars['webaccess'];               
                $webaccess->last_modified = time();
                $webaccess->user_id = $this->current_user->id;
                $webaccess->load_from_array($webaccess_data);
                $webaccess->load_additional_data($webaccess_data);
                //debug($webaccess_data);
                //debug($webaccess); die;
                if($webaccess->is_valid_data()){
                    if($webaccess->save_data()){                     
                        return $this->mk_redir('webaccess_edit', array('waid' => $webaccess->id));
                    } else {
                        $_SESSION['klara']['webaccess_add'] = $this->vars['webaccess'];
                        return $this->mk_redir('webaccess_edit', array('customer_id' => $customer->id));                        
                    }                    
                } else {
                    $_SESSION['klara']['webaccess_edit'] = $this->vars['webaccess'];
                    return $this->mk_redir('webaccess_edit', array('waid'=>$webaccess->id));
                }
            } else {
                return $this->mk_redir('manage_access');
            }
            
        }
        
        function webaccess_delete(){
            check_auth(array('waid' =>$this->vars['waid']));
            $wa = new WebAccess($this->vars['waid']);
            if($wa->id){
                    if($wa->can_delete()){
                        $wa->delete();
                    }
            }
            return $this->mk_redir('manage_access');
        }
        /************************************************************************************************/
        
}
?>

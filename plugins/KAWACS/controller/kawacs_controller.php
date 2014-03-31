<?php

class_load('MonitorProfile');
class_load('MonitorProfilePeriph');
class_load('MonitorItem');

class KawacsController extends PluginController{
    protected $plugin_name = "KAWACS";
    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
//        $this->do_not_cache_ops = array(
//            'computer_view',
//        );
    }
    
    /** Shows the page with the list of current computers */
    function customers_computer_count()
    {
        class_load('Computer');
        class_load('Customer');
        class_load('AD_User');
        $tpl = 'customers_computer_count.tpl';
        if(!isset($_SESSION['customers_computer_count']))
        {
            $_SESSION['customers_computer_count'] = array(
                'group_by_type' => 1,
                'show_brand' => 0,
                'show_model' => 0,
                'show_os' => 1,
                'show_user' => 1,
                'show_contact' => 1,
                'order_by' => 'alert',
                'order_dir' => 'DESC'
            );
        }
        $extra_params = array();
        if($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        if($this->vars['set_filter'])
        {
                $_SESSION['customers_computer_count']['customer_id'] = $this->vars['set_filter'];
        }
        elseif (isset($this->vars['customer_id']))
        {
                $_SESSION['customers_computer_count']['customer_id'] = $this->vars['customer_id'];
        }
        elseif ($this->locked_customer->id and !$this->vars['do_filter'])
        {
                // If 'do_filter' is present in request, the locked customer is ignored
                $_SESSION['customers_computer_count']['customer_id'] = $this->locked_customer->id;
        }
        $filter = $_SESSION['customers_computer_count'];

        //check auth
        if ($filter['customer_id'] > 0)
        {
            // Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
            // This way he can return to this page, without getting again "Permission Denied".

            unset ($_SESSION['customers_computer_count']['customer_id']);
            check_auth (array('customer_id' => $filter['customer_id']));
            $_SESSION['customers_computer_count']['customer_id'] = $filter['customer_id'];
        }
        else check_auth ();

        //debug($_SESSION['customers_computer_count']);
        //debug($filter);

        //if (!isset($filter['start'])) $filter['start'] = 0;
        //if (!isset($filter['limit'])) $filter['limit'] = 100;
        //if (!isset($filter['customer_id'])) $filter['customer_id'] = COMPUTERS_FILTER_ALL;
        //elseif ($filter['customer_id'] == '') $filter['customer_id'] = COMPUTERS_FILTER_ALL;

        $tot_computers = 0;

        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers = Customer::get_customers_list ($customers_filter);
        $profiles = MonitorProfile::get_profiles_list ();

        // Shows the list of computers for a specific customer
        if (!$filter['customer_id'])
        {
                $ids = array_keys($customers);
                $filter['customer_id'] = $ids[0];
        }

        // Check if the user has restricted access to customers
        if ($this->current_user->restrict_customers and $filter['customer_id'] < 0) $filter['assigned_user_id'] = $this->current_user->id;

        // Mark the potential customer for locking
        if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
        $computers_all = array();
        if(isset($filter['customer_id']))
        {
                $computers_all = Computer::get_computers_ex($filter);
        }
        $computers_current = array();
        if(isset($computers_all['current'])) $computers_current = $computers_all['current'];
        $computers_old = array();
        if(isset($computers_all['old'])) $computers_old = $computers_all['old'];
        $computers_blackout = array();
        if(isset($computers_all['blackout'])) $computers_blackout = $computers_all['blackout'];

        $tot_comps_current = sizeof($computers_current);
        $tot_comps_old = sizeof($computers_old);
        $tot_comps_blackout = sizeof($computers_blackout);


        $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
        $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
        $users_list = $users + $groups;
        $users_logins_list = User::get_logins_list (array('type' => USER_TYPE_KEYSOURCE));

        // Get the AD info about the logged in users
        $ad_users = array ();
        for ($i=0; $i<count($computers_current); $i++)
        {
                $login = $computers_current[$i]->get_item('current_user');
                $computers_current[$i]->current_user = $login;
                if ($login and !isset($ad_users[$login]))
                {
                        $ad_users[$login] = AD_User::get_by_login ($login, $computers_current[$i]->customer_id);
                }
        }
        for ($j=0; $j<count($computers_old); $j++)
        {
                $login = $computers_old[$j]->get_item('current_user');
                $computers_old[$j]->current_user = $login;
                if ($login and !isset($ad_users[$login]))
                {
                        $ad_users[$login] = AD_User::get_by_login ($login, $computers_old[$j]->customer_id);
                }
        }

        //debug($computers_old);

        $this->assign ('users_list', $users_list);
        $this->assign ('users_logins_list', $users_logins_list);
        $this->assign ('ad_users', $ad_users);
        $this->assign('profiles', $profiles);
        $this->assign('customers', $customers);
        $this->assign ('sort_url', $this->mk_redir('customers_computer_count_submit', $extra_params));
        $this->assign ('filter', $filter);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        //$this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
        $this->assign ('COMPUTERS_FILTER', $GLOBALS['COMPUTERS_FILTER']);
        $this->assign('computers_current', $computers_current);
        $this->assign('computers_old', $computers_old);
        $this->assign('computers_blackout',$computers_blackout);
        $this->assign('tot_comps_current', $tot_comps_current);
        $this->assign('tot_comps_old', $tot_comps_old);
        $this->assign('tot_comps_blackout', $tot_comps_blackout);

        $this->assign('error_msg', error_msg());
        $this->set_form_redir('customers_computer_count_submit', $extra_params);
        $this->display($tpl);

    }
    function customers_computer_count_submit()
    {
            check_auth ();

            $extra_params = array();

            if ($this->vars['order_by'] and $this->vars['order_dir'])
            {
                // This is a request to change the sorting order
                $_SESSION['customers_computer_count']['order_by'] = $this->vars['order_by'];
                $_SESSION['customers_computer_count' ]['order_dir'] = $this->vars['order_dir'];
            }
            else
            {
                $_SESSION['customers_computer_count'] = $this->vars['filter'];
            }

            if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
            {
                $extra_params['do_filter'] = 1;
            }

            return $this->mk_redir('customers_computer_count', $extra_params);
    }
    function manage_computers ()
    {
    show_elapsed ('Start');
        // check_auth (); 		// Authorization is checked lower, in case there was a specific customer requested
        class_load ('Computer');
        class_load ('Customer');
        class_load ('Ticket');
        class_load ('AD_User');
        $tpl = 'manage_computers.tpl';

        if (!isset($_SESSION['manage_computers']))
        {
            $_SESSION['manage_computers'] = array (
                'group_by_type' => 1,
                'show_brand' => 0,
                'show_model' => 0,
                'show_os' => 1,
                'show_user' => 1,
                'show_contact' => 1,
                'order_by' => 'alert',
                'order_dir' => 'DESC'
            );
        }

        $extra_params = array();	// Extra parameters to be carried in navigation
        if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        if ($this->vars['set_filter'])
        {
                $_SESSION['manage_computers']['customer_id'] = $this->vars['set_filter'];
        }
        elseif (isset($this->vars['customer_id']))
        {
                $_SESSION['manage_computers']['customer_id'] = $this->vars['customer_id'];
        }
        elseif ($this->locked_customer->id and !$this->vars['do_filter'])
        {
                // If 'do_filter' is present in request, the locked customer is ignored
                $_SESSION['manage_computers']['customer_id'] = $this->locked_customer->id;
        }
        $filter = $_SESSION['manage_computers'];

        // Check authorization
        if ($filter['customer_id'] > 0)
        {
                // Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
                // This way he can return to this page, without getting again "Permission Denied".

                unset ($_SESSION['manage_computers']['customer_id']);
                check_auth (array('customer_id' => $filter['customer_id']));
                $_SESSION['manage_computers']['customer_id'] = $filter['customer_id'];
        }
        else check_auth ();

        if (!isset($filter['start'])) $filter['start'] = 0;
        if (!isset($filter['limit'])) $filter['limit'] = 100;
        if (!isset($filter['customer_id'])) $filter['customer_id'] = COMPUTERS_FILTER_ALERTS;
        elseif ($filter['customer_id'] == '') $filter['customer_id'] = COMPUTERS_FILTER_ALL;

        $tot_computers = 0;

        // Extract the list of Kawacs customers, eventually restricting only to the customers assigned to
        // the current user, if he has restricted customer access.
        $customers_filter = array (
                                   'has_kawacs' => 1,
                                   'favorites_first' => $this->current_user->id,
                                   'account_manager' => $filter['account_manager']
                                   );
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers = Customer::get_customers_list ($customers_filter);

        // Shows the list of computers for a specific customer
        if (!$filter['customer_id'])
        {
                $ids = array_keys($customers);
                $filter['customer_id'] = $ids[0];
        }

        // Check if the user has restricted access to customers
        if ($this->current_user->restrict_customers and $filter['customer_id'] < 0) $filter['assigned_user_id'] = $this->current_user->id;

        // Mark the potential customer for locking
        if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
    show_elapsed ('Get computers');
        $computers = Computer::get_computers($filter, $tot_computers);

        if ($filter['start'] > $tot_computers)
        {
                $filter['start'] = 0;
                $computers = Computer::get_computers($filter, $tot_computers);
        }
    show_elapsed ('Get notifs');

        // Load the notifications for each computers, as well as the computer roles
        $computers_tickets = array ();
        for ($i=0; $i<count($computers); $i++)
        {

                $computers[$i]->notifications = $computers[$i]->get_notifications ();

                // This will store the IDs of the tickets associated with notifications for this computer
                $tickets_ids = array ();

                // This will store the tickets associated with this computer - except those that are assigned through notifications
                $computers[$i]->tickets = array ();

                // For each notification, check if there is an associated ticket and load it
                for ($j=0; $j<count($computers[$i]->notifications); $j++)
                {
                        if ($computers[$i]->notifications[$j]->ticket_id)
                        {
                                $tickets_ids[] = $computers[$i]->notifications[$j]->ticket_id;
                                $computers[$i]->notifications[$j]->ticket = new Ticket ($computers[$i]->notifications[$j]->ticket_id);
                        }
                }


                // Get the list of tickets related to each computer and eliminate from list the tickets from notifications
                $all_tickets = Ticket::get_computer_tickets ($computers[$i]->id);
                for ($j = 0; $j<count($all_tickets); $j++)
                {
                        $ticket_id = $all_tickets[$j]->id;
                        if (!in_array ($ticket_id, $tickets_ids))
                        {
                                $computers[$i]->tickets[] = $all_tickets[$j];
                        }
                }
                unset ($all_tickets);

                // Load the roles
                $computers[$i]->load_roles ();
        }
    show_elapsed ('Get notifs end');
        // Check if there are also any relevant internet alerts to be shown
        $filter_internet = array ();
        if ($filter['customer_id']>0 or $filter['customer_id']==-1 or $filter['customer_id']==-2)
        {
                class_load ('MonitoredIP');
                $filter_internet = array ('status' => MONITOR_STAT_ERROR);
                if ($filter['customer_id']>0) $filter_internet['customer_id'] = $filter['customer_id'];

                $connections_down = MonitoredIP::get_monitored_ips ($filter_internet);
                for ($i=0; $i<count($connections_down); $i++)
                {
                        $connections_down[$i]->load_customer ();
                        $connections_down[$i]->load_notification ();
                        if ($connections_down[$i]->notification->ticket_id)
                        {
                                // Load the ticket too
                                $connections_down[$i]->ticket = new Ticket ($connections_down[$i]->notification->ticket_id);
                        }
                }
                $this->assign ('connections_down', $connections_down);
        }


    show_elapsed ('Done');
        $profiles = MonitorProfile::get_profiles_list ();

        // Get the users list
        $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
        $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
        $users_list = $users + $groups;
        $users_logins_list = User::get_logins_list (array('type' => USER_TYPE_KEYSOURCE));

        // Get the AD info about the logged in users
        $ad_users = array ();
        for ($i=0; $i<count($computers); $i++)
        {
                $login = $computers[$i]->get_item('current_user');
                $computers[$i]->current_user = $login;
                if ($login and !isset($ad_users[$login]))
                {
                        $ad_users[$login] = AD_User::get_by_login ($login, $computers[$i]->customer_id);
                }
        }

        $margin_days = 2;
        $repo_stats = Computer::get_repo_stats($margin_days, $filter['customer_id']>0 ? $filter['customer_id'] : 0);
        $servers_repo[] = array('Today', intval($repo_stats['today_servers'])); 
        $servers_repo[] = array("In last $margin_days days", intval($repo_stats['margin_servers']));
        $servers_repo[] = array("Not reporting",intval($repo_stats['notreporting_servers']));
        $servers_repo[] = array("Not reporting for more than 1 year", intval($repo_stats['moreyearnr_servers']));
        $workstations_repo[] = array('Today', intval($repo_stats['today_workstations'])); 
        $workstations_repo[] = array("In last $margin_days days", intval($repo_stats['margin_workstations']));
        $workstations_repo[] = array("Not reporting", intval($repo_stats['notreporting_workstations']));
        $workstations_repo[] = array("Not reporting for more than 1 year", intval($repo_stats['moreyearnr_workstations'])); 

        $srvrepo_vals = array(intval($repo_stats['today_servers']), intval($repo_stats['margin_servers']), intval($repo_stats['notreporting_servers']), intval($repo_stats['moreyearnr_servers']));
        $wksrepo_vals = array(intval($repo_stats['today_workstations']), intval($repo_stats['margin_workstations']), intval($repo_stats['notreporting_workstations']), intval($repo_stats['moreyearnr_workstations']));
        //debug($workstations_repo);
        $this->assign('servers_repo', json_encode($servers_repo));
        $this->assign('workstations_repo', json_encode($workstations_repo));
        $this->assign('repo_servers_today', json_encode($repo_stats['today_servers']));
        $this->assign('repo_wks_today', json_encode($repo_stats['today_workstations']));
        $this->assign('repo_servers_margin', json_encode($repo_stats['margin_servers']));
        $this->assign('repo_wks_margin', json_encode($repo_stats['margin_workstations']));
        $this->assign('repo_servers_notreporting', json_encode($repo_stats['notreporting_servers']));
        $this->assign('repo_wks_notreporting', json_encode($repo_stats['notreporting_workstations']));


        //debug($computers);
        // Compose paging
        $pages = make_paging ($filter['limit'], $tot_computers);
    show_elapsed ('Done processing');
        $this->assign ('computers', $computers);
        $this->assign ('tot_computers', $tot_computers);
        $this->assign ('pages', $pages);
        $this->assign ('customers', $customers);
        $this->assign ('profiles', $profiles);
        $this->assign ('filter', $filter);
        $this->assign ('tickets', $tickets);
        $this->assign ('users_list', $users_list);
        $this->assign ('users_logins_list', $users_logins_list);
        $this->assign ('ad_users', $ad_users);
        $this->assign ('sort_url', get_link('kawacs', 'manage_computers_submit', $extra_params));//$this->mk_redir ('manage_computers_submit', $extra_params));
        $this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
        $this->assign ('COMPUTERS_FILTER_SPECIAL', $GLOBALS['COMPUTERS_FILTER_SPECIAL']);
        $this->assign ('TICKET_STATUSES', $GLOBALS['TICKET_STATUSES']);
        $this->assign ('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
        $this->set_form_redir ('manage_computers_submit', $extra_params);
        $this->assign ('error_msg', error_msg());
    show_elapsed ('Display');
        $this->display ($tpl);

    show_elapsed ('Done display');
        if($computers)
        {
            foreach ($computers as $comp)
            {
                if($comp)
                {
                    //$comp->destruct();
                    unset($comp);
                }
            }
            unset($computers);
        }
    }


    /** Sets the filtering criteria for the list of computers */
    function manage_computers_submit ()
    {
        check_auth ();

        $extra_params = array();

        if ($this->vars['order_by'] and $this->vars['order_dir'])
        {
            // This is a request to change the sorting order
            $_SESSION['manage_computers']['order_by'] = $this->vars['order_by'];
            $_SESSION['manage_computers']['order_dir'] = $this->vars['order_dir'];
        }
        else
        {
            if ($this->vars['go'] == 'prev' or $this->vars['go'] == 'next')
            {
                    $this->vars['filter']['start']+= $this->vars['filter']['limit'] * ($this->vars['go'] == 'prev' ? -1 : 1);
            }
            $this->vars['filter']['order_by'] = $this->vars['order_by_bk'];
            $this->vars['filter']['order_dir'] = $this->vars['order_dir_bk'];

            $_SESSION['manage_computers'] = $this->vars['filter'];
        }

        if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
        {
            $extra_params['do_filter'] = 1;
        }

        return $this->mk_redir('manage_computers', $extra_params);
    }

    /** Shows the page with the list of current computers */
    function kawacs_console ()
    {
        check_auth ();
        class_load ('Computer');
        class_load ('Customer');
        class_load ('Ticket');
        class_load ('Peripheral');
        class_load ('AD_Printer');
        class_load ('AD_User');
        class_load ('CustomerInternetContract');
        class_load ('SoftwareLicense');
        $tpl = 'kawacs_console.tpl';

        $extra_params = array();	// Extra parameters to be carried in navigation
        if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        $filter = $_SESSION['kawacs_console'];
        $filter['group_by_type'] = 1;
        $filter['customer_id'] = COMPUTERS_FILTER_ALERTS;
        if (!$filter['order_by']) $filter['order_by'] = 'alert_raised';
        if (!$filter['order_dir']) $filter['order_dir'] = 'DESC';
        if (!isset($filter['start'])) $filter['start'] = 0;
        if (!isset($filter['limit'])) $filter['limit'] = 100;
        if (!isset($filter['reload_seconds'])) $filter['reload_seconds'] = 300;
        if (!isset($filter['show_in_console'])) $filter['show_in_console'] = 1;

        //debug($filter);

        // Extract the list of customers
        $customers = Customer::get_customers_list ($customers_filter);
        $customers_filter_all['active'] = -1;
        $customers_all = Customer::get_customers_list ($customers_filter_all);

        $tot_computers = 0;
        $computers = Computer::get_computers($filter, $tot_computers);
        if ($filter['start'] > $tot_computers)
        {
            $filter['start'] = 0;
            $computers = Computer::get_computers($filter, $tot_computers);
        }

        // Load the notifications, roles and tickets for all computers
        $all_notifs_brief = Notification::get_computers_notifs_brief ($filter['show_in_console']); //all that should show in console
        for ($i=0; $i<count($computers); $i++)
        {
            $computers[$i]->notifications = $all_notifs_brief[$computers[$i]->id];
            $computers[$i]->tickets = Ticket::get_computer_tickets ($computers[$i]->id);
            $computers[$i]->load_roles ();
        }

        // Check if there are also any relevant internet alerts to be shown
        $filter_internet = array ();
        if ($filter['customer_id']>0 or $filter['customer_id']==-1 or $filter['customer_id']==-2)
        {
            class_load ('MonitoredIP');
            $filter_internet = array ('status' => MONITOR_STAT_ERROR);
            if ($filter['customer_id']>0) $filter_internet['customer_id'] = $filter['customer_id'];

            $connections_down = MonitoredIP::get_monitored_ips ($filter_internet);
            for ($i=0; $i<count($connections_down); $i++)
            {
                    $connections_down[$i]->load_customer ();
                    $connections_down[$i]->load_notification ();
                    if ($connections_down[$i]->notification->ticket_id)
                    {
                            // Load the ticket too
                            $connections_down[$i]->ticket = new Ticket ($connections_down[$i]->notification->ticket_id);
                    }
            }
        }

        // Check for customer internet contracts within the notification period
        $expiring_internet_contracts = CustomerInternetContract::get_contracts (array('expiring_contracts'=>true, 'load_details'=>true, 'load_customers'=>true));

        // Check for notifications linked to exceeded software licenses
        $exceeded_lic_notifs = SoftwareLicense::get_exceeded_notifications (true);

        // Check for notifications linked to peripherals and AD Printers
        $peripherals_notifs = Peripheral::get_peripherals_notifications ();

        // Check for notifications about unassigned discoveries
        $unassigned_disc_notifs = Notification::get_notifications (array('event_code' => NOTIF_CODE_UNMATCHED_DISCOVERIES));

        // Get the users list
        $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
        $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
        $users_list = $users + $groups;

        // Get the AD info about the logged in users
        $ad_users = array ();
        for ($i=0; $i<count($computers); $i++)
        {
            $login = $computers[$i]->get_item('current_user');
            $computers[$i]->current_user = $login;
            if ($login and !isset($ad_users[$login]))
            {
                    $ad_users[$login] = AD_User::get_by_login ($login, $computers[$i]->customer_id);
            }
        }

        // Load the list of computers profiles
        $profiles = MonitorProfile::get_profiles_list ();

        // Compose paging
        $pages = make_paging ($filter['limit'], $tot_computers);

        // Check how many extra info DIVs we have to show
        $cnt_extra_divs = 0;
        if (count($connections_down) > 0) {$has_connections_down = true; $cnt_extra_divs++;}
        if (count($expiring_internet_contracts) > 0) {$has_expired_contracts = true; $cnt_extra_divs++;}
        if (count($exceeded_lic_notifs) > 0) {$has_exceeded_licenses = true; $cnt_extra_divs++;}
        if (count($peripherals_notifs) > 0) {$has_peripherals_notifs = true; $cnt_extra_divs++;}
        if (count($unassigned_disc_notifs) > 0) {$has_unassigned_disc_notifs = true; $cnt_extra_divs++;}
        if ($cnt_extra_divs > 0) $extra_divs_width = round (100 / $cnt_extra_divs, 2);

        // Get the list of unread notification IDs for the current user
        $unread_notifs_ids = Notification::get_unread_notifs_ids ($this->current_user->id);

        $clist=array();
        foreach($computers as $c) $clist[]= $c->id;

        $this->assign ('computers', $computers);
        $this->assign ('clist', json_encode($clist));
        $this->assign ('tot_computers', $tot_computers);
        $this->assign ('pages', $pages);
        $this->assign ('customers', $customers);
        $this->assign ('customers_all', $customers_all);
        $this->assign ('profiles', $profiles);
        $this->assign ('filter', $filter);
        $this->assign ('tickets', $tickets);
        $this->assign ('users_list', $users_list);
        $this->assign ('ad_users', $ad_users);
        $this->assign ('sort_url', get_link('kawacs', 'kawacs_console_submit', $extra_params));
        $this->assign ('unread_notifs_ids', $unread_notifs_ids);

        $this->assign ('connections_down', $connections_down);
        $this->assign ('expiring_internet_contracts', $expiring_internet_contracts);
        $this->assign ('exceeded_lic_notifs', $exceeded_lic_notifs);
        $this->assign ('peripherals_notifs', $peripherals_notifs);
        $this->assign ('unassigned_disc_notifs', $unassigned_disc_notifs);

        $this->assign ('has_connections_down', $has_connections_down);
        $this->assign ('has_exceeded_licenses', $has_exceeded_licenses);
        $this->assign ('has_expired_contracts', $has_expired_contracts);
        $this->assign ('has_expired_contracts', $has_expired_contracts);
        $this->assign ('has_peripherals_notifs', $has_peripherals_notifs);
        $this->assign ('has_unassigned_disc_notifs', $has_unassigned_disc_notifs);
        $this->assign ('cnt_extra_divs', $cnt_extra_divs);
        $this->assign ('extra_divs_width', $extra_divs_width);

        $this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
        $this->assign ('COMPUTERS_FILTER_SPECIAL', $GLOBALS['COMPUTERS_FILTER_SPECIAL']);
        $this->assign ('TICKET_STATUSES', $GLOBALS['TICKET_STATUSES']);
        $this->assign ('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
        $this->set_form_redir ('kawacs_console_submit', $extra_params);
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);

        if($computers)
        {
            foreach ($computers as $comp)
            {
                if($comp)
                {
                    //$comp->destruct();
                    unset($comp);
                }
            }
            unset($computers);
        }
    }

    /** Sets the filtering criteria for the list of computers */
    function kawacs_console_submit ()
    {
        check_auth ();		
        $extra_params = array();

        if ($this->vars['order_by'] and $this->vars['order_dir'])
        {
            // This is a request to change the sorting order
            $_SESSION['kawacs_console']['order_by'] = $this->vars['order_by'];
            $_SESSION['kawacs_console']['order_dir'] = $this->vars['order_dir'];
        }
        else
        {
            if ($this->vars['go'] == 'prev' or $this->vars['go'] == 'next')
            {
                $this->vars['filter']['start']+= $this->vars['filter']['limit'] * ($this->vars['go'] == 'prev' ? -1 : 1);
            }
            $this->vars['filter']['order_by'] = $this->vars['order_by_bk'];
            $this->vars['filter']['order_dir'] = $this->vars['order_dir_bk'];

            $_SESSION['kawacs_console'] = $this->vars['filter'];
        }

        if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
        {
            $extra_params['do_filter'] = 1;
        }

        if ($this->vars['clear']=="Acknowledge")
        {
            //now we have to browsae through all the notifications
            class_load('Notification');
            $notif_ids = $this->vars['notif_ids'];
            foreach($notif_ids as $nid)
            {
                $notification = new Notification($nid);
                $notification->suspend_email = 1;
                $notification->show_in_console = 0;
                $notification->mark_read($this->current_user->id);
                $notification->save_data();
                $notification->load_ticket();
                if($notification->ticket != null)
                {
                    $notification->ticket->mark_closed();
                }				
            }
        }		

        return $this->mk_redir('kawacs_console', $extra_params);
    }

    function create_notif_ticket_submit()
    {
        check_auth(array('id' => $this->vars['id']));
        class_load ('Notification');
        class_load ('Computer');
        class_load ('Ticket');
        class_load ('User');
        class_load ('Group');
        //class_load('NotificationRecipient');

        $notif = new Notification($this->vars['id']);
        if(!$notif->id) return $this->mk_redir('manage_tickets', array(), 'krifs');
        //now we are gonna create the ticket

        /*
        $has_ks_recips = false;


        foreach ($notif->recipients as $recip)
                if (!User::is_customer_user($recip->user_id))
                        $has_ks_recips = true;

        if(!$has_ks_recips)
        {
                $dummy_notif_recip = new NotificationRecipient();
                $dummy_notif_recip->user_id = $this->current_user->id;
                $dummy_notif_recip->load_user();
                $notif->recipients[] = $dummy_notif_recip;
        }
        */
        $ticket = new Ticket ();
        $comp = new Computer ($notif->object_id);

        if ($notif->text) $ticket->subject = $notif->text;
        else $ticket->subject = $GLOBALS['NOTIF_CODES_TEXTS'][$notif->event_code];
        $ticket->subject.= ' : #'.$comp->id.': '.$comp->netbios_name;

        $default_owner_id = null;
        if ($notif->object_event_code)
        {
                // This notification is linked to a specific kind of alert. See if there is a default recipient for this alert type
                $q_alert_recip = 'SELECT user_id FROM '.TBL_ALERTS_RECIPIENTS.' WHERE is_default=1 and alert_id='.$notif->object_event_code;
                $default_owner_id = DB::db_fetch_field ($q_alert_recip, 'user_id');
        }

        $ticket->customer_id = $comp->customer_id;
        if (!$default_owner_id)
        {
                // We don't need to know here if the default owner is "Away" and the function returned an alternate recipient,
                // because get_default_cc_list() will add it anyway to the CC list.
                $default_owner_id = $ticket->get_default_owner ($none);
        }
        $ticket->owner_id = $default_owner_id;
        $ticket->assigned_id = $default_owner_id;
        $ticket->cc_list = $ticket->get_default_cc_list ();
        $ticket->private = true;

        $ticket->status = TICKET_STATUS_NEW;
        $ticket->source = TICKET_SOURCE_KAWACS;
        $ticket->type = $ticket_type;
        $ticket->created = time ();
        $ticket->last_modified = time ();

        foreach ($notif->recipients as $recip)
        {
                if (!User::is_customer_user($recip->user_id) and $default_owner_id!=$recip->user_id and !in_array($recip->user_id,$ticket->cc_list))
                {
                        $ticket->cc_list[] = $recip->user_id;
                }
        }

        $ticket->save_data ();
        $ticket->load_data ();
        //$ticket->escalate (0, 'Escalated from notification.');
        $ticket->add_objects (TICKET_OBJ_CLASS_COMPUTER, array ($comp->id));
        $ticket->save_data ();

        $ticket->dispatch_notifications (TICKET_NOTIF_TYPE_NEW);
        $ticket->save_data ();

        // Mark on the notification that it is linked to a ticket
        $notif->mark_ticket_created ($ticket->id);

        return $this->mk_redir('ticket_edit', array('id'=>$ticket->id), 'krifs');

    }

    /** Displays the page for viewing if there are any issues with the computers reporting */
    function reporting_issues ()
    {
        check_auth ();
        class_load ('ComputerReporting');
        class_load ('CustomerAllowedIP');
        class_load ('Discovery');
        $tpl = 'reporting_issues.tpl';

        $conflicting_macs = ComputerReporting::get_conflicting_macs ();
        $conflicting_names = ComputerReporting::get_conflicting_names ();
        $name_swingers = ComputerReporting::get_name_swingers ();
        $conflicting_ips = ComputerReporting::get_conflicting_reporting_ips ();
        $conflicting_discoveries = Discovery::get_non_reporting_computers ();

        $allowed_ips_list = CustomerAllowedIP::get_allowed_ips_list ();
        $customers_list = Customer::get_customers_list (array('active'=>-1));

        $this->assign ('conflicting_macs', $conflicting_macs);
        $this->assign ('conflicting_names', $conflicting_names);
        $this->assign ('name_swingers', $name_swingers);
        $this->assign ('conflicting_ips', $conflicting_ips);
        $this->assign ('conflicting_discoveries', $conflicting_discoveries);
        $this->assign ('allowed_ips_list', $allowed_ips_list);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }


    /** Displays the page allowing to define which remote IP addresses are allowed for receiving KAWACS Agent reports from each customer */
    function customers_allowed_ips ()
    {
        check_auth ();
        class_load ('CustomerAllowedIP');
        $tpl = 'customers_allowed_ips.tpl';

        $filter = $_SESSION['customers_allowed_ips']['filter'];
        if (isset($this->vars['customer_id'])) $filter['customer_id'] = $this->vars['customer_id'];

        $allowed_ips = CustomerAllowedIP::get_allowed_ips ($filter);
        $ips_computers_list = CustomerAllowedIP::get_ips_computers_list ();
        $allowed_ips_list = CustomerAllowedIP::get_allowed_ips_list ();

        $customers_list = Customer::get_customers_list (array('active' => 1, 'show_ids' => 1));
        $customers_list_all = Customer::get_customers_list (array('active' => -1, 'show_ids' => 1));
        $users_list = User::get_users_list ();

        $this->assign ('allowed_ips', $allowed_ips);
        $this->assign ('allowed_ips_list', $allowed_ips_list);
        $this->assign ('filter', $filter);
        $this->assign ('ips_computers_list', $ips_computers_list);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('customers_list_all', $customers_list_all);
        $this->assign ('users_list', $users_list);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('customers_allowed_ips_submit');

        $this->display ($tpl);
    }

    /** Saves the filtering criteria for the allowed IPs page */
    function customers_allowed_ips_submit ()
    {
        check_auth ();
        $_SESSION['customers_allowed_ips']['filter'] = $this->vars['filter'];
        return $this->mk_redir ('customers_allowed_ips');
    }


    /** Displays the page for adding a new allowed IP for a customer */
    function customer_allowed_ip_add ()
    {
        check_auth ();
        class_load ('CustomerAllowedIP');
        $tpl = 'customer_allowed_ip_add.tpl';

        $allowed_ip = new CustomerAllowedIP ();
        if ($this->vars['customer_id']) $allowed_ip->customer_id = $this->vars['customer_id'];
        if ($this->vars['remote_ip']) $allowed_ip->remote_ip = $this->vars['remote_ip'];
        $data = array ();
        if (!empty_error_msg()) $allowed_ip->load_from_array(restore_form_data ('customer_allowed_ip_add', false, $data));

        $customers_list = Customer::get_customers_list (array('active' => 1, 'show_ids' => 1));

        $existing_customers = ComputerReporting::get_customers_using_ip ($allowed_ip->remote_ip);

        $this->assign ('allowed_ip', $allowed_ip);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('existing_customers', $existing_customers);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('customer_allowed_ip_add_submit');

        $this->display ($tpl);
    }

    /** Adds a new allowed IP address for a customer */
    function customer_allowed_ip_add_submit ()
    {
        check_auth ();
        class_load ('CustomerAllowedIP');
        $ret = $this->mk_redir ('customers_allowed_ips', array ('customer_id'=>$this->vars['allowed_ip']['customer_id']));

        if ($this->vars['save'])
        {
            $data = $this->vars['allowed_ip'];
            $data['updated_by_id'] = $this->current_user->id;
            $data['update_date'] = time ();
            $data['remote_ip'] = trim($data['remote_ip']);
            $allowed_ip = new CustomerAllowedIP ();
            $allowed_ip->load_from_array ($data);

            if ($allowed_ip->is_valid_data ()) $allowed_ip->save_data ();
            else
            {
                save_form_data ($data, 'customer_allowed_ip_add');
                $ret = $this->mk_redir ('customer_allowed_ip_add');
            }
        }

        return $ret;
    }

    /** Deletes a customer allowed remote IP */
    function customer_allowed_ip_delete ()
    {
        check_auth ();
        class_load ('CustomerAllowedIP');
        $allowed_ip = new CustomerAllowedIP ($this->vars['id']);
        if ($allowed_ip->id) $allowed_ip->delete ();
        return $this->mk_redir ('customers_allowed_ips');
    }

    /** Displays the page with the valid name duplicates */
    function valid_dup_names ()
    {
        check_auth ();
        class_load ('ValidDupName');
        $tpl = 'valid_dup_names.tpl';

        $valid_dup_names = ValidDupName::get_valid_dup_names ();
        $customers_list = Customer::get_customers_list (array('active'=>-1));

        $this->assign ('valid_dup_names', $valid_dup_names);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }

    /** Displays the page for adding a new valid duplicate name */
    function valid_dup_name_add ()
    {
        check_auth ();
        class_load ('ValidDupName');
        class_load ('Computer');
        $tpl = 'valid_dup_name_add.tpl';

        $dup_name = $this->vars['dup_name'];
        $valid_dup_names = ValidDupName::get_valid_dup_names ($dup_name);
        $computers = ValidDupName::get_computers_by_name ($dup_name);
        $customers_list = Customer::get_customers_list (array('active'=>-1));

        // Make a list with the computer IDs already allowed for this names
        $selected_ids = array ();
        if ($valid_dup_names[$dup_name])
        {
                foreach ($valid_dup_names[$dup_name] as $valid_dup) $selected_ids[] = $valid_dup->computer_id;
        }

        $this->assign ('dup_name', $dup_name);
        $this->assign ('valid_dup_names', $valid_dup_names);
        $this->assign ('computers', $computers);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('selected_ids', $selected_ids);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('valid_dup_name_add_submit');

        $this->display ($tpl);
    }

    /** Adds one or more valid duplicate names */
    function valid_dup_name_add_submit ()
    {
        check_auth ();
        class_load ('ValidDupName');
        $ret = $this->mk_redir ('valid_dup_names');

        if ($this->vars['save'])
        {
            $dup_name = trim($this->vars['dup_name']);
            if (!$dup_name)
            {
                error_msg ($this->get_string('NEED_NAME'));
                $ret = $this->mk_redir ('valid_dup_name_add');
            }
            else
            {
                if ($this->vars['computers_ids']) $computers_ids = $this->vars['computers_ids'];
                else $computers_ids = array ();
                ValidDupName::set_computers ($dup_name, $computers_ids);
            }
        }

        return $ret;
    }

    /** Removes a valid duplicate name */
    function valid_dup_name_delete ()
    {
        check_auth ();
        class_load ('ValidDupName');
        $ret = $this->mk_redir ('valid_dup_names');

        $valid_dup = new ValidDupName ($this->vars['id']);
        if ($valid_dup->id) $valid_dup->delete ();

        return $ret;
    }

    /** Displays the page from which invalid names can be cleaned from the logs in case on name swingers */
    function computer_name_swings_clean ()
    {
        class_load ('Computer');
        class_load ('ComputerReporting');
        $tpl = 'computer_name_swings_clean.tpl';
        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('reporting_issues');
        check_auth (array('computer_id' => $computer->id));

        $swingers = ComputerReporting::get_name_swingers ();
        $names = $swingers[$computer->id];
        if (count($names)<=1) return $this->mk_redir ('reporting_issues');

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('computer', $computer);
        $this->assign ('names', $names);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_name_swings_clean_submit', $params);

        $this->display ($tpl);
    }

    /** Cleans the name logs for a name swinger computer */
    function computer_name_swings_clean_submit ()
    {
        class_load ('Computer');
        class_load ('ComputerReporting');
        $computer = new Computer($this->vars['id']);
        check_auth (array('computer_id' => $computer->id));
        $ret = $this->mk_redir ('reporting_issues');

        if ($this->vars['save'] and $computer->id)
        {
            $keep_name = $this->vars['keep_name'];
            if ($keep_name) ComputerReporting::clean_name_swinger_logs ($computer->id, $keep_name);
            else
            {
                error_msg ($this->get_string('NEED_NAME_TO_KEEP'));
                $ret = $this->mk_redir ('computer_name_swings_clean', array('id' => $computer->id));
            }
        }

        return $ret;
    }


    /** Displays the page for viewing notifications history or current notifications */
    function manage_notifications ()
    {
        check_auth ();
        class_load ('Notification');
        class_load ('Computer');
        class_load ('Customer');
        class_load ('MonitoredIP');
        $tpl = 'manage_notifications.tpl';

        $filter = $_SESSION['manage_notifications']['filter'];
        if (!isset($filter['object_class'])) $filter['object_class'] = NOTIF_OBJ_CLASS_COMPUTER;
        if (!isset($filter['order_dir'])) $filter['order_dir'] = 'DESC';
        $filter['order_by'] = 'raised';

        $log_months = Notification::get_log_months ();

        $types = $GLOBALS['NOTIF_OBJ_CLASSES'];
        unset ($types[NOTIF_OBJ_CLASS_CUSTOMER]);

        if (!$filter['interval'])
        {
            // Fetch the current notifications
            $notifications = Notification::get_notifications($filter);
        }
        else
        {
            // Fetch notifications log
            $filter_log = $filter;
            $filter_log['month_start'] = $filter['interval'];
            $filter_log['month_end'] = $filter['interval'];
            $filter_log['show'] = 3;

            $notifications = Notification::get_notifications_log ($filter_log);
            $computers = array ();
            $ips = array ();
            $tickets = array ();

            for ($i=0; $i<count($notifications); $i++)
            {
                $obj_id = $notifications[$i]->object_id;
                if ($notifications[$i]->object_class == NOTIF_OBJ_CLASS_COMPUTER)
                {
                        if (!isset($computers[$obj_id])) $computers[$obj_id] = new Computer ($obj_id);
                }
                elseif ($notifications[$i]->object_class == NOTIF_OBJ_CLASS_INTERNET)
                {
                        if (!isset($ips[$obj_id])) $ips[$obj_id] = new MonitoredIP ($obj_id);
                }
                elseif ($notifications[$i]->object_class == NOTIF_OBJ_CLASS_KRIFS)
                {
                        if (!isset($tickets[$obj_id])) $tickets[$obj_id] = new Ticket ($obj_id);
                }
            }


            $this->assign ('customers_list', Customer::get_customers_list ());
            if ($filter['object_class'] == NOTIF_OBJ_CLASS_COMPUTER) $this->assign ('computers_list', Computer::get_computers_list ());

            $this->assign ('computers', $computers);
            $this->assign ('ips', $ips);
            $this->assign ('tickets', $tickets);
        }

        $this->assign ('filter', $filter);
        $this->assign ('notifications', $notifications);
        $this->assign ('types', $types);
        $this->assign ('log_months', $log_months);
        $this->assign ('error_msg', error_msg ());
        $this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
        $this->set_form_redir ('manage_notifications_submit');

        $this->display ($tpl);
    }

    /** Save the filtering criteria for the notifications status page */
    function manage_notifications_submit ()
    {
        $_SESSION['manage_notifications']['filter'] = $this->vars['filter'];
        return $this->mk_redir ('manage_notifications');
    }


    /** XXXX: Maintenance of logs: remove from monthly logs the duplicate items for disk space */
    function cklogs_free_space ()
    {
        check_auth ();
        class_load ('KawacsLogs');
        class_load ('Computer');
        $tpl = 'cklogs_free_space.tpl';

        $months = Computer::get_all_log_months ();

        $stats = array ();
        if ($this->vars['month'] and $this->vars['checked_stat'] and $_SESSION['cklogs_free_space'])
        {
                $stats = $_SESSION['cklogs_free_space'];
                $this->assign ('checked_stat', true);
        }
        elseif ($this->vars['month'] and $this->vars['updated_stat'] and $_SESSION['cklogs_free_space'])
        {
                $stats = $_SESSION['cklogs_free_space'];
                $this->assign ('updated_stat', true);
        }

        $params = $this->set_carry_fields (array('month'));

        $this->assign ('months', $months);
        $this->assign ('month', $this->vars['month']);
        $this->assign ('stats', $stats);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('cklogs_free_space_submit', $params);

        $this->display ($tpl);
    }


    function cklogs_free_space_submit ()
    {
        check_auth ();
        class_load ('KawacsLogs');
        class_load ('Computer');

        $params = $this->set_carry_fields (array('month'));

        if ($this->vars['month'] and $this->vars['do_check'])
        {
            $_SESSION['cklogs_free_space'] = KawacsLogs::get_disk_space_stats ($this->vars['month']);
            $params['checked_stat'] = 1;
        }
        elseif ($this->vars['month'] and $this->vars['do_update'])
        {
            $_SESSION['cklogs_free_space'] = KawacsLogs::update_disk_space_stats($this->vars['month']);
            $params['updated_stat'] = 1;
        }

        $ret = $this->mk_redir ('cklogs_free_space', $params);
        return $ret;
    }


    function cklogs_ad_computers ()
    {
        check_auth ();
        class_load ('KawacsLogs');
        class_load ('Computer');
        $tpl = 'cklogs_ad_computers.tpl';

        $months = Computer::get_all_log_months ();

        $stats = array ();
        if ($this->vars['month'] and $this->vars['checked_stat'] and $_SESSION['cklogs_ad_computers'])
        {
            $stats = $_SESSION['cklogs_ad_computers'];
            $this->assign ('checked_stat', true);
        }
        elseif ($this->vars['month'] and $this->vars['updated_stat'] and $_SESSION['cklogs_ad_computers'])
        {
            $stats = $_SESSION['cklogs_ad_computers'];
            $this->assign ('updated_stat', true);
        }

        $params = $this->set_carry_fields (array('month'));

        $this->assign ('months', $months);
        $this->assign ('month', $this->vars['month']);
        $this->assign ('stats', $stats);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('cklogs_ad_computers_submit', $params);

        $this->display ($tpl);
    }


    function cklogs_ad_computers_submit ()
    {
        check_auth ();
        class_load ('KawacsLogs');
        class_load ('Computer');

        $params = $this->set_carry_fields (array('month'));

        if ($this->vars['month'] and $this->vars['do_check'])
        {
            $_SESSION['cklogs_ad_computers'] = KawacsLogs::old_get_ad_computers_stats ($this->vars['month']);
            $params['checked_stat'] = 1;
        }
        elseif ($this->vars['month'] and $this->vars['do_update'])
        {
            $_SESSION['cklogs_ad_computers'] = KawacsLogs::update_ad_computers_stats($this->vars['month']);
            $params['updated_stat'] = 1;
        }

        $ret = $this->mk_redir ('cklogs_ad_computers', $params);
        return $ret;
    }

    function cklogs_1030_1046 ()
    {
        check_auth ();
        class_load ('KawacsLogs');
        class_load ('Computer');
        $tpl = 'cklogs_1030_1046.tpl';

        $months = Computer::get_all_log_months ();

        $stats = array ();
        if ($this->vars['month'] and $this->vars['updated_stat'] and $_SESSION['cklogs_1030_1046'])
        {
            $stats = $_SESSION['cklogs_1030_1046'];
            $this->assign ('updated_stat', true);
        }

        $params = $this->set_carry_fields (array('month'));

        $this->assign ('months', $months);
        $this->assign ('month', $this->vars['month']);
        $this->assign ('stats', $stats);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('cklogs_1030_1046_submit', $params);

        $this->display ($tpl);
    }

    function cklogs_1030_1046_submit ()
    {
        check_auth ();
        class_load ('KawacsLogs');
        class_load ('Computer');

        $params = $this->set_carry_fields (array('month'));

        if ($this->vars['month'] and $this->vars['do_update'])
        {
                $_SESSION['cklogs_1030_1046'] = KawacsLogs::update_1030_1046($this->vars['month']);
                $params['updated_stat'] = 1;
        }

        $ret = $this->mk_redir ('cklogs_1030_1046', $params);
        return $ret;
    }

    
    /** Shows the page with the details reported for this computer */
    function computer_view ()
    {
        class_load ('Customer');
        class_load ('Ticket');
        class_load ('Peripheral');
        class_load ('ComputerBlackout');
        class_load ('ComputerNote');
        class_load ('MonitoredIP');
        class_load ('Discovery');
        class_load ('DiscoverySettingDetail');
        class_load ('ComputerLogmein');

        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('ComputerItem');
        class_load ('MonitorProfile');
        class_load ('CustomerPhoto');
        class_load ('Supplier');
        $tpl = 'computer_view.tpl';

        $computer = new Computer ($this->vars['id']);
        //debug($computer);
        if (!$computer->id) return $this->mk_redir('manage_computers');

        // Load the associated MonitoredIP object, if any
        $monitored_ip = MonitoredIP::get_by_remote_ip ($computer->remote_ip);
        if ($monitored_ip->id)
        {
            $monitored_ip->load_customer ();
            $monitored_ip->load_notification ();
        }

        // Check alerts
        $computer->check_monitor_alerts ();
        $notifications = $computer->get_notifications ();
        $notifications_tickets = array();
        if ($monitored_ip->notification->id) $notifications_tickets[$monitored_ip->notification->ticket_id] = new Ticket($monitored_ip->notification->ticket_id);

        // Mark all associated notifications as being read and update the counter
        if (count($notifications) > 0)
        {
            foreach ($notifications as $notification) $notification->mark_read ($this->current_user->id);
            $this->update_unread_notifs ();
        }

        // Check notifications that have associated tickets
        for ($i=0; $i<count($notifications); $i++)
        {
            $notifications_tickets[$notifications[$i]->ticket_id] = new Ticket ($notifications[$i]->ticket_id);
        }

        // Get the list of tickets related to this computer and eliminate from list the tickets from notifications
        $all_tickets = Ticket::get_computer_tickets ($computer->id);
        $tickets = array ();
        for ($i = 0; $i<count($all_tickets); $i++)
        {
            if (!isset($notifications_tickets[$all_tickets[$i]->id])) $tickets[] = $all_tickets[$i];
        }
        unset ($all_tickets);

        // Get the tickets history for this computer
        $tickets_history = Ticket::get_computer_tickets_history ($computer->id);

        // Check what is being logged (determined by profile settings)
        if ($computer->profile_id)
        {
            $profile_items = MonitorProfile::get_profile_items_list ($computer->profile_id);
            $is_logging_partitions = ($profile_items[PARTITIONS_ITEM_ID] > MONITOR_LOG_NONE);
            $is_logging_backup = ($profile_items[BACKUP_STATUS_ITEM_ID] > MONITOR_LOG_NONE);
            $is_logging_av = ($profile_items[AV_STATUS_ITEM_ID] > MONITOR_LOG_NONE);

            $is_requesting_events = isset($profile_items[EVENTS_ITEM_ID]);
        }



        $computer_peripherals = Peripheral::get_peripherals (array('computer_id' => $computer->id));
        $peripherals_classes_list = PeripheralClass::get_classes_list ();

        // Get the list of notes for this computer
        $notes = ComputerNote::get_computer_notes ($computer->id);

        // Get the users list
        $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
        $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
        $users_list = $users + $groups;
        $users_logins_list = User::get_logins_list (array('type' => USER_TYPE_KEYSOURCE));

        // Mark the potential customer for locking
        $_SESSION['potential_lock_customer_id'] = $computer->customer_id;

        // Check to see if the computer is blacked out
        $blackout = new ComputerBlackout ($computer->id);
        if ($blackout->computer_id) $this->assign ('blackout', $blackout);

        // See if there are any photos, roles and location for this computer
        $computer->load_photos ();
        $computer->load_roles ();
        $computer->load_location ();

        // Load local IP addresses for this computer
        $ips = array ();
        $adapters = $computer->get_item('net_adapters');
        $net_adapters_field_id = $computer->get_item_id ('net_adapters');
        $ip_field_id = $computer->get_item_id('ip_address', $net_adapters_field_id);
        $adapter_name_id = $computer->get_item_id('name', $net_adapters_field_id);
        for ($i=0; $i<count($adapters); $i++)
        {
            if ($adapters[$i][$ip_field_id] and $adapters[$i][$ip_field_id] != '0.0.0.0')
            {
                    $ips[] = array('ip'=>trim($adapters[$i][$ip_field_id]), 'adapter'=>trim($adapters[$i][$adapter_name_id]));
            }
        }


        // See if this computer is matched by any of the networks discoveries
        $discoveries = Discovery::get_matches_for_computer ($computer->id);
        $disc_details = array ();
        foreach ($discoveries as $discovery)
        {
            if (!isset($disc_details[$discovery->detail_id])) $disc_details[$discovery->detail_id] = new DiscoverySettingDetail ($discovery->detail_id);
        }

        $stolen_computer = Computer::is_computer_stolen($computer->id);
        // Load the reported data
        $items = $computer->get_reported_items();

        //Load the LogMeIn id if one is set
        $logmein = new ComputerLogmein();
        $logmein = $logmein->get_item($computer->id);
        $this->assign('logmein', $logmein);


        $this->assign ('computer', $computer);
        $this->assign ('customer', new Customer($computer->customer_id));
        $this->assign ('tickets', $tickets);
        $this->assign ('tickets_history', $tickets_history);
        $this->assign ('notifications', $notifications);
        $this->assign ('notifications_tickets', $notifications_tickets);
        $this->assign ('notes', $notes);
        $this->assign ('users_list', $users_list);
        $this->assign ('users_logins_list', $users_logins_list);

        $this->assign ('items', $items);
        $this->assign ('is_logging_partitions', $is_logging_partitions);
        $this->assign ('is_logging_backup', $is_logging_backup);
        $this->assign ('is_logging_av', $is_logging_av);
        $this->assign ('is_requesting_events', $is_requesting_events);
        $this->assign ('computer_peripherals', $computer_peripherals);
        $this->assign ('peripherals_classes_list', $peripherals_classes_list);
        $this->assign ('monitored_ip', $monitored_ip);
        $this->assign ('ips', $ips);

        $this->assign ('discoveries', $discoveries);
        $this->assign ('disc_details', $disc_details);

        $this->assign ('stolen_computer', $stolen_computer);

        $this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
        $this->assign ('ALERT_NAMES', $GLOBALS['ALERT_NAMES']);
        $this->assign ('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
        $this->assign ('MONITOR_STATS', $GLOBALS['MONITOR_STATS']);
        $this->assign ('profiles_list', MonitorProfile::get_profiles_list());
        $this->set_form_redir ('computer_view_submit', array ('id', $computer->id));


        $this->display ($tpl);
    }


    /** Processes requests from the computer_view page */
    function computer_view_submit ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        $ret = $this->mk_redir ('computer_view', array ('id' => $this->vars['id']));

        if ($this->vars['request_full_update'])
        {
            // This is a request to place a request for a full update for this computer
            class_load ('Computer');
            $computer = new Computer ($this->vars['id']);
            if ($computer->id)
            {
                    $computer->request_full_update = true;
                    $computer->save_data ();
            }
        }
        elseif ($this->vars['cancel_full_update'])
        {
            // This is a request to cancel a request for a full update for this computer
            class_load ('Computer');
            $computer = new Computer ($this->vars['id']);
            if ($computer->id)
            {
                    $computer->request_full_update = false;
                    $computer->save_data ();
            }
        }
        if($this->vars['stolen_computer']){
            class_load('Computer');
            $computer = new Computer($this->vars['id']);
            if($computer->id){
                    $computer->mark_stolen();
            }
        }
        else
        {
            class_load('Computer');
            Computer::unmark_stolen($this->vars['id']);
        }

        return $ret;
    }

    function set_logmein() {
        check_auth();
        if(!empty($this->vars['computer_id'])) {
            class_load('ComputerLogmein');
            $logmein = new ComputerLogmein();
            $logmein = $logmein->get_item($this->vars['computer_id']);

            $this->assign('logmein', $logmein);
            $this->assign('error_msg', error_msg());

            $this->set_form_redir('set_logmein_submit', array('computer_id' => $this->vars['computer_id']));
            $this->display('set_logmein.tpl');
        } else {
            return $this->mk_redir('manage_computer');
        }
    }

    function set_logmein_submit() {
        check_auth();
        if(!empty($this->vars['computer_id'])) {
            if(isset($this->vars['save'])) {
                class_load('ComputerLogmein');
                $logmein_data = $this->vars['logmein'];
                $logmein_data['computer_id'] = $this->vars['computer_id'];
                $logmein = new ComputerLogmein($logmein_data['id']);

                $logmein->load_from_array($logmein_data);
                if($logmein->is_valid_data()) {
                    $logmein->save_data();
                    return $this->mk_redir('set_logmein', array('computer_id' => $this->vars['computer_id']));
                } else {
                    return $this->mk_redir('set_logmein', array('computer_id' => $this->vars['computer_id']));
                }
            } else {
                return $this->mk_redir('computer_view', array('id' => $this->vars['computer_id']));
            }
        } else {
            return $this->mk_redir('manage_computer');
        }
    }

    /**
     * displays a page for searching a serial number of an asset and performs a search
     *
     */
    function search_serial()
    {
        check_auth();
        class_load('Computer');
        class_load('Peripheral');
        class_load('AD_Printer');
        $tpl = "search_serial.tpl";
        //$this->vars['search_text'] = strtoupper(trim($this->vars['search_text']));
        $data_computers = array();
        $data_peripherals = array();
        $data_adprinters = array();
        if($this->vars['search_text'] and $this->vars['search_text']!="")
        {
            $data_computers = Computer::get_serials_numbers($this->vars['search_text']);
            $data_peripherals['sn'] = Peripheral::get_serials_numbers($this->vars['search_text'], "sn");
            $data_peripherals['pn'] = Peripheral::get_serials_numbers($this->vars['search_text'], "pn");
            $data_adprinters = AD_Printer::get_serials_numbers($this->vars['search_text']);
        }
        else {
            error_msg("You must specify a search query!");
        }

        $this->assign("data_computers", $data_computers);
        $this->assign("data_peripherals", $data_peripherals);
        $this->assign("data_adprinters", $data_adprinters);
        $this->assign("error_msg", error_msg());
        $this->set_form_redir("search_serial");
        $this->display($tpl);
    }


    /** Displays the page for searching for computers and performs a search. */
    function search_computer ()
    {
        check_auth ();
        class_load ('Computer');
        $tpl = 'search_computer.tpl';

        $this->vars['search_text'] = strtoupper(trim($this->vars['search_text']));
        if ($this->vars['search_text'] and is_numeric ($this->vars['search_text']))
        {
            // A numeric ID was provided, go directly to that computer if it is a valid one
            $computer = new Computer ($this->vars['search_text']);
            if ($computer->id) return $this->mk_redir ('computer_view', array('id' => $computer->id));
            else error_msg ($this->get_string('INVALID_COMPUTER_ID', $this->vars['search_text']));
        }
        elseif ($this->vars['search_text'] and preg_match('/^['.ASSET_PREFIX_WORKSTATION.'|'.ASSET_PREFIX_SERVER.'][0-9]{'.ASSET_NUM_LENGTH.'}$/', $this->vars['search_text']))
        {
            // An asset number was specified
            $id = intval(substr($this->vars['search_text'],1));
            $computer = new computer ($id);
            if ($computer->id) return $this->mk_redir ('computer_view', array('id' => $computer->id));
            else error_msg ($this->get_string('INVALID_ASSET_NUMBER', $this->vars['search_text']));
        }
        elseif ($this->vars['search_text'])
        {
            // A string was specified, try a search by computer name
            $computers = Computer::get_computers (array('search_text' => $this->vars['search_text'], 'order_by' => 'netbios_name'), $no_count);
            if (count($computers) > 0)
            {
                // If a single match is find, then go directly to that computer
                if (count($computers) == 1) return $this->mk_redir ('computer_view', array('id' => $computers[0]->id));
                else $customers_list = Customer::get_customers_list ();
            }
        }

        $this->assign ('search_text', $this->vars['search_text']);
        $this->assign ('computers', $computers);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('search_computer');

        $this->display ($tpl);
    }


    /** Displays the page for manually creating a computer */
    function computer_add ()
    {
        class_load ('Computer');
        class_load ('MonitorProfile');
        class_load ('Customer');
        check_auth ();
        $tpl = 'computer_add.tpl';

        $computer = new Computer ();
        if (!empty_error_msg()) $computer->load_from_array(restore_form_data ('computer_add', false, $data));
        $computer->netbios_name = $data['netbios_name'];
        $computer->is_manual = true;

        // Extract the list of Kawacs customers, eventually restricting only to the customers assigned to
        // the current user, if he has restricted customer access.
        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);
        $profiles_list = MonitorProfile::get_profiles_list ();

        $this->assign ('computer', $computer);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('profiles_list', $profiles_list);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_add_submit');

        $this->display ($tpl);
    }


    /** Saves a new manually created computer */
    function computer_add_submit ()
    {
        class_load ('Computer');

        if ($this->vars['save'])
        {
            $data = $this->vars['computer'];
            $data['is_manual'] = 1;
            $computer = new Computer ();
            $computer->netbios_name = $data['netbios_name'];
            $computer->load_from_array ($data);

            if ($computer->is_valid_data ())
            {
                $computer->save_data ();
                $computer->load_data ();

                // Create the name computer item
                $name = new ComputerItem ($computer->id, 1001, false);
                $name->val[0]->value = $computer->netbios_name;
                $name->val[0]->updated = time ();
                $name->val[0]->nrc = 0;
                $name->save_data ();

                $ret = $this->mk_redir ('computer_view', array ('id' => $computer->id));
            }
            else
            {
                save_form_data ($data, 'computer_add');
                $ret = $this->mk_redir ('computer_add');
            }
        }
            return $ret;
    }

    /** Displays the page for manually editing a manually created computer */
    function computer_edit ()
    {
        class_load ('Computer');
        class_load ('MonitorProfile');
        class_load ('Customer');
        check_auth ();
        $tpl = 'computer_edit.tpl';

        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('kawacs');
        if (!empty_error_msg()) $computer->load_from_array(restore_form_data ('computer_edit', false, $data));

        // Extract the list of Kawacs customers, eventually restricting only to the customers assigned to
        // the current user, if he has restricted customer access.
        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);
        $profiles_list = MonitorProfile::get_profiles_list ();

        $params = $this->set_carry_fields (array('id', 'returl'));
        $this->assign ('computer', $computer);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('profiles_list', $profiles_list);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_edit_submit', $params);

        $this->display ($tpl);
    }

    /** Saves changes to the manually created computer */
    function computer_edit_submit ()
    {
        class_load ('Computer');
        $computer = new Computer ($this->vars['id']);
        $params = $this->set_carry_fields (array('id', 'returl'));
        $ret = $this->mk_redir ('computer_view', $params);

        if ($this->vars['save'] and $computer->id and $computer->is_manual)
        {
            $data = $this->vars['computer'];
            $computer->load_from_array ($data);

            if ($computer->is_valid_data ()) $computer->save_data ();
            else save_form_data ($data, 'computer_edit');
            $ret = $this->mk_redir ('computer_edit', $params);
        }
        return $ret;
    }


    /** Displays the page for adding a new note for a computer */
    function computer_note_add ()
    {
        class_load ('ComputerNote');
        check_auth (array('computer_id' => $this->vars['computer_id']));
        $tpl = 'computer_note_add.tpl';

        $computer = new Computer ($this->vars['computer_id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');

        $params = $this->set_carry_fields (array('computer_id'));

        $this->assign ('computer', $computer);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_note_add_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the newly created note */
    function computer_note_add_submit ()
    {
        class_load ('ComputerNote');
        $computer = new Computer ($this->vars['computer_id']);
        check_auth (array('computer_id' => $computer->id));
        $ret = $this->mk_redir ('computer_view', array ('id' => $computer->id));
        $params = $this->set_carry_fields (array('computer_id'));

        if ($this->vars['save'] and $computer->id)
        {
            $data = $this->vars['note'];
            $note = new ComputerNote ();
            $note->load_from_array ($data);
            $note->computer_id = $computer->id;
            $note->user_id = $this->current_user->id;

            if ($note->is_valid_data ())
            {
                $note->save_data ();
                unset ($params['computer_id']);
                $params['id'] = $note->id;
                $ret = $this->mk_redir ('computer_note_edit', $params);
            }
            else $ret = $this->mk_redir ('computer_note_add', $params);
        }

        return $ret;
    }


    /** Displays the page for editing a computer note */
    function computer_note_edit ()
    {
        class_load ('ComputerNote');
        $tpl = 'computer_note_edit.tpl';

        $note = new ComputerNote ($this->vars['id']);
        $computer = new Computer ($note->computer_id);
        $user = new User ($note->user_id);

        if (!$note->id or !$computer->id) return $this->mk_redir ('manage_computers');
        check_auth (array('computer_id' => $computer->id));

        $params = $this->set_carry_fields (array('id'));

        $this->assign ('note', $note);
        $this->assign ('computer', $computer);
        $this->assign ('user', $user);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_note_edit_submit', $params);

        $this->display ($tpl);
    }


    /** Saves a modified computer note */
    function computer_note_edit_submit ()
    {
        class_load ('ComputerNote');
        $note = new ComputerNote ($this->vars['id']);
        check_auth (array('computer_id' => $note->computer_id));

        $ret = $this->mk_redir ('computer_view', array('id' => $note->computer_id));
        $params = $this->set_carry_fields (array('id'));

        if ($this->vars['save'] and $note->id)
        {
            $note->load_from_array ($this->vars['note']);
            if ($note->is_valid_data ()) $note->save_data ();
            $ret = $this->mk_redir ('computer_note_edit', $params);
        }

        return $ret;
    }


    /** Deletes a computer note */
    function computer_note_delete ()
    {
        class_load ('ComputerNote');
        check_auth (array('computer_id' => $note->computer_id));
        $note = new ComputerNote ($this->vars['id']);
        $ret = $this->mk_redir ('computer_view', array ('id' => $note->computer_id));

        if ($note->id and $note->can_delete ()) $note->delete ();

        return $ret;
    }

    /** Displays a specific monitor item for a computer */
    function computer_view_item ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('ComputerItem');
        class_load ('MonitorProfile');
        class_load ('Supplier');
        $tpl = 'computer_view_item.tpl';

        $filter = $_SESSION['computer_view_item']['filter'];
        if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
        if (!isset($filter['limit'])) $filter['limit'] = 50;

        $computer = new Computer ($this->vars['id']);
        $item_id = $this->vars['item_id'];
        if (!$computer->id or !$item_id) return $this->mk_redir('manage_computers');
        $item = $computer->get_item_by_id ($item_id);

        // Build the paging, if needed
        $pages = array ();
        if (count ($item->val) > 0)
        {
            $cnt = 0;
            $tot_items = count($item->val);
            $pages = make_paging ($filter['limit'], $tot_items);
            if ($filter['start'] > $tot_items) $filter['start'] = 0;
            foreach ($item->val as $idx => $value)
            {
                    if ($cnt<$filter['start'] or $cnt>$filter['start']+$filter['limit']) unset ($item->val[$idx]);
                    $cnt++;
            }
        }

        $params = $this->set_carry_fields (array ('id', 'item_id'));
        $this->assign ('computer', $computer);
        $this->assign ('item', $item);
        $this->assign ('filter', $filter);
        $this->assign ('pages', $pages);
        $this->assign ('tot_items', $tot_items);
        $this->assign ('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        $this->assign ('EVENTLOG_TYPES_ICONS', $GLOBALS['EVENTLOG_TYPES_ICONS']);
        $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
        $this->set_form_redir ('computer_view_item_submit', $parms);
        $this->display ($tpl);
    }

    function computer_view_item_submit ()
    {
        $filter = $this->vars['filter'];
        if ($this->vars['go'] == 'prev') $filter['start']-= $filter['limit'];
        elseif ($this->vars['go'] == 'next') $filter['start']+= $filter['limit'];
        $_SESSION['computer_view_item']['filter'] = $filter;
        $params = $this->set_carry_fields (array ('id', 'item_id'));
        return $this->mk_redir ('computer_view_item', $params);
    }

    /** Displays the page with a detailed view of a single reported item */
    function computer_view_item_detail ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('ComputerItem');
        class_load ('MonitorProfile');
        class_load ('Supplier');
        $tpl = 'computer_view_item_detail.tpl';

        $computer = new Computer ($this->vars['id']);
        $item_id = $this->vars['item_id'];
        if (!$computer->id or !$item_id) return $this->mk_redir('manage_computers');
        $item = $computer->get_item_by_id ($item_id);

        $nrc = $this->vars['nrc'];
        $idx = $item->get_idx_for_nrc ($nrc);

        $this->assign ('computer', $computer);
        $this->assign ('item', $item);
        $this->assign ('nrc', $nrc);
        $this->assign ('idx', $idx);
        $this->assign ('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        $this->assign ('EVENTLOG_TYPES_ICONS', $GLOBALS['EVENTLOG_TYPES_ICONS']);

        $this->display_template_limited ($tpl);
    }

    /** Marks an event from the event log as being ignored */
    function computer_event_ignore ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('ComputerReporting');
        $computer = new Computer ($this->vars['id']);
        $ret = $this->mk_redir ('computer_view_item', array ('id'=>$computer->id, 'item_id'=>EVENTS_ITEM_ID));

        ComputerReporting::set_event_ignored ($computer, $this->vars['nrc'], 1);

        return $ret;
    }

    /** Marks an event from the event log as not being ignored */
    function computer_event_unignore ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('ComputerReporting');
        $computer = new Computer ($this->vars['id']);
        $ret = $this->mk_redir ('computer_view_item', array ('id'=>$computer->id, 'item_id'=>EVENTS_ITEM_ID));

        ComputerReporting::set_event_ignored ($computer, $this->vars['nrc'], 0);

        return $ret;
    }

    /** Displays the page for deleting a computer */
    function computer_delete ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('RemovedComputer');
        $tpl = 'computer_delete.tpl';

        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');

        $removal = new RemovedComputer ();
        $data = array ();
        if (!empty_error_msg()) $removal->load_from_array(restore_form_data ('computer_delete', false, $data));
        if (!$removal->date_removed) $removal->date_removed = time ();

        $this->assign ('computer', $computer);
        $this->assign ('removal', $removal);
        $this->set_form_redir ('computer_delete_submit', array ('id' => $computer->id));
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Processes a request to delete a computer */
    function computer_delete_submit ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('RemovedComputer');
        $computer = new Computer ($this->vars['id']);
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('computer_view', $params);

        if ($this->vars['save'] and $computer->id)
        {
            $data = $this->vars['removal'];
            $data['date_removed'] = js_strtotime ($data['date_removed']);

            if ($this->vars['delete_action'] == 'do_delete')
            {
                // Delete the computer permanently
                $computer->delete ();
                $ret = $this->mk_redir ('manage_computers');
            }
            elseif ($this->vars['delete_action'] == 'do_remove')
            {
                    // Move the computer to the removed table

                $removal = new RemovedComputer ();
                $removal->load_from_array ($data);

                if ($removal->is_valid_data())
                {
                        $removal = RemovedComputer::remove_computer ($computer, $this->current_user->id, $data['reason_removed'], $data['date_removed']);
                        $ret = $this->mk_redir ('computer_view', $params, 'kawacs_removed');
                }
                else
                {
                        save_form_data ($data, 'computer_delete');
                        $ret = $this->mk_redir ('computer_delete', $params);
                }
            }
            else
            {
                error_msg ($this->get_string('NEED_CHOICE_DELETE'));
                save_form_data ($data, 'computer_delete');
                $ret = $this->mk_redir ('computer_delete', $params);
            }
        }

        return $ret;
    }


    /** Displays the logged data for a computer item */
    function computer_view_log ()
    {
        check_auth (array('computer_id' => $this->vars['computer_id']));
        class_load ('Computer');
        $tpl = 'computer_view_log.tpl';

        $computer_id = $this->vars['computer_id'];
        $item_id = $this->vars['item_id'];

        $computer = new Computer ($computer_id);
        $item = new ComputerItem ($computer_id, $item_id);
        $log_items_count = 0;

        // Get list of available months for this computer
        $months = $computer->get_log_months ($item_id);

        if (isset($_SESSION['computer_view_log']['filter'])) $filter = $_SESSION['computer_view_log']['filter'];

        if (!$filter['page']) $filter['page'] = 0;
        if (!$filter['per_page']) $filter['per_page'] = 30;

        // Make sure the page is a multipe of 'per_page'
        $filter['page'] = intval($filter['page']/$filter['per_page']) * $filter['per_page'];

        if (!in_array($filter['month'], $months)) unset ($filter['month']);

        $items_log = $computer->get_logged_data ($item_id, $filter, $log_items_count);
        if ($log_items_count < $filter['page'])
        {
            $filter['page'] = 0;
            $items_log = $computer->get_logged_data ($item_id, $filter, $log_items_count);
        }

        $pages = make_paging ($filter['per_page'], $log_items_count);
        $per_page_options = array ('10'=>10, '30'=>30, '40'=>40, '50'=>50, '100'=>100);

        // Compose the Next and Previous URLs
        $next_url = '';
        $prev_url = '';
        $params = array ('computer_id'=>$computer->id, 'item_id'=>$item_id, 'filter[per_page]'=>$filter['per_page']);
        if ($filter['month']) $params['filter[month]'] = $filter['month'];
        if (($filter['page'] - $filter['per_page']) >= 0)
        {
            $prev_page = $filter['page'] - $filter['per_page'];
            $params['filter[page]'] = $prev_page;
            $prev_url = $this->mk_redir ('computer_view_log_submit', $params);
        }
        if (($filter['page'] + $filter['per_page']) <= $log_items_count)
        {
            $next_page = $filter['page'] + $filter['per_page'];

            $params['filter[page]'] = $next_page;
            $next_url = $this->mk_redir ('computer_view_log_submit', $params);
        }

        $this->assign ('computer', $computer);
        $this->assign ('item', $item);
        $this->assign ('items_log', $items_log);
        $this->assign ('months', $months);
        $this->assign ('log_items_count', $log_items_count);
        $this->assign ('prev_url', $prev_url);
        $this->assign ('next_url', $next_url);
        $this->assign ('pages', $pages);
        $this->assign ('per_page_options', $per_page_options);
        $this->assign ('error_msg', error_msg ());
        $this->assign ('filter', $filter);

        $this->set_form_redir ('computer_view_log_submit', array('computer_id'=>$computer_id, 'item_id'=>$item_id));

        $this->display ($tpl);
    }


    /** Processes requests from the "View Item Log" page */
    function computer_view_log_submit ()
    {
        check_auth (array('computer_id' => $this->vars['computer_id']));

        if ($this->vars['clear_log'])
        {
            class_load ('Computer');
            // This is a request to delete all logged data for this item
            $computer = new Computer ($this->vars['computer_id']);
            if ($computer->id)
            {
                    $computer->clear_logged_data ($this->vars['item_id']);
            }
        }
        else
        {
                // This is a request to change filtering criteria
                $_SESSION['computer_view_log']['filter'] = $this->vars['filter'];
        }
        return $this->mk_redir ('computer_view_log', array ('computer_id'=>$this->vars['computer_id'], 'item_id'=>$this->vars['item_id']));
    }

    
    /** Displays the content of a monitoring item of type 'File' */
    function open_item_file ()
    {
        check_auth (array('computer_id' => $this->vars['computer_id']));
        class_load ('Computer');
        $tpl = 'open_item_file.tpl';

        $item = new ComputerItem ($this->vars['computer_id'], $this->vars['item_id']);
        $file = DIR_MONITOR_ITEMS_FILE . '/' . $item->val[0]->value[$this->vars['field_id']];

        $file_contents = '';
        $fp = @fopen ($file, 'r');
        if ($fp)
        {
                while ($s = fread($fp, 1024)) $file_contents.= $s;
                fclose ($fp);
        }

        $this->assign ('file', $file);
        $this->assign ('file_contents', $file_contents);
        $this->assign ('item', $item);

        $this->display ($tpl);
    }


    /** Displays the page for changing the monitoring profile of a customer */
    function computer_profile ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('MonitorProfile');
        $tpl = 'computer_profile.tpl';

        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');

        $profiles = MonitorProfile::get_profiles();
        $this->assign ('computer', $computer);
        $this->assign ('profiles', $profiles);
        $this->set_form_redir ('computer_profile_submit', array ('id'=>$computer->id));
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Processes the request for assigning a profile to a computer */
    function computer_profile_submit ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('MonitorProfile');

        if ($this->vars['cancel']) return $this->mk_redir ('computer_view', array('id'=>$this->vars['id']));

        $profile = new MonitorProfile($this->vars['profile']);
        $computer = new Computer($this->vars['id']);
        if ($profile->id and $computer->id)
        {
                $computer->profile_id = $profile->id;
                $computer->save_data();
                $ret = $this->mk_redir ('computer_profile', array('id' => $this->vars['id']));
        }
        elseif (!$profile->id)
        {
                error_msg ($this->get_string('NEED_PROFILE'));
                $ret = $this->mk_redir ('computer_profile', array('id' => $this->vars['id']));
        }
        else
        {
                error_msg ($this->get_string('NEED_COMPUTER'));
                $ret = $this->mk_redir ('manage_computers');
        }

        return $ret;
    }


    /** Displays the page for changing a computer's customer */
    function computer_customer ()
    {
        class_load ('Computer');
        class_load ('Ticket');
        $computer = new Computer ($this->vars['id']);
        check_auth (array('computer_id' => $computer->id));
        $tpl = 'computer_customer.tpl';

        if (!$computer->id) return $this->mk_redir ('manage_computers');
        $current_customer = new Customer ($computer->customer_id);

        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true, 'active' => -1);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);

        // Get the tickets history for this computer
        $tickets_history = Ticket::get_computer_tickets_history ($computer->id);

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('computer', $computer);
        $this->assign ('current_customer', $current_customer);
        $this->assign ('tickets_history', $tickets_history);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_customer_submit', $params);

        $this->display ($tpl);
    }

    /** Displays the page for changing the MAC address used for identifying a computer */
    function computer_mac_edit ()
    {
        class_load ('Computer');
        $tpl = 'computer_mac_edit.tpl';
        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');
        $customer = new Customer ($computer->customer_id);
        check_auth (array('customer_id' => $customer->id));


        $this->set_form_redir (array('id'));
        $this->assign ('computer', $computer);
        $this->assign ('customer', $customer);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_mac_edit_submit', $params);

        $this->display ($tpl);
    }

    /** Saves the modifications for a computer's MAC */
    function computer_mac_edit_submit ()
    {
        class_load ('Computer');
        $computer = new Computer ($this->vars['id']);
        $ret = $this->mk_redir ('computer_view', array('id' => $computer->id));

        if ($this->vars['save'] and $computer->id and $this->vars['mac_address'])
        {
                $computer->mac_address = $this->vars['mac_address'];
                $computer->save_data ();
        }

        return $ret;
    }

    /** Sets the new customer for a computer */
    function computer_customer_submit ()
    {
        class_load ('Computer');
        $computer = new Computer ($this->vars['id']);
        check_auth (array('computer_id' => $computer->id));
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('computer_view', $params);

        if ($this->vars['save'] and $computer->id)
        {
            if (!$computer->set_customer ($this->vars['customer_id'])) $ret = $this->mk_redir ('computer_customer', $params);
        }

        return $ret;
    }


    /** Displays the page for setting the date since the computer is monitored */
    function computer_date_created ()
    {
        class_load ('Computer');
        $tpl = 'computer_date_created.tpl';
        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');
        check_auth (array('computer_id' => $computer->id));

        $customer = new Customer ($computer->customer_id);

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('computer', $computer);
        $this->assign ('customer', $customer);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_date_created_submit', $params);

        $this->display ($tpl);
    }

    /** Saves the start monitoring date for a computer */
    function computer_date_created_submit ()
    {
        class_load ('Computer');
        $computer = new Computer ($this->vars['id']);
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('computer_view', $params);

        if ($this->vars['save'] and $computer->id)
        {
            $ret = $this->mk_redir ('computer_date_created', $params);

            if ($this->vars['date_created'])
            {
                $start_date = js_strtotime ($this->vars['date_created']);
                if ($start_date <= 0) error_msg ($this->get_string('NEED_VALID_DATE'));
            }
            else $start_date = 0;

            if ($start_date > 0)
            {
                $computer->date_created = $start_date;
                $computer->save_data ();
            }
            $ret = $this->mk_redir ('computer_date_created', $params);
        }

        return $ret;
    }

    /** Displays the page for changing a computer's location */
    function computer_location ()
    {
        class_load ('Computer');
        class_load ('Location');
        $tpl = 'computer_location.tpl';
        $computer = new Computer ($this->vars['id']);
        check_auth (array('computer_id' => $computer->id));
        $computer->load_location ();

        $locations_list = Location::get_locations_list (array('customer_id'=>$computer->customer_id, 'indent'=>true));
        $params = $this->set_carry_fields (array('id', 'returl'));

        $this->assign ('computer', $computer);
        $this->assign ('locations_list', $locations_list);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_location_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the location assignment for a computer */
    function computer_location_submit ()
    {
        class_load ('Computer');
        class_load ('Location');
        $computer = new Computer ($this->vars['id']);

        $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('computer_view', array('id' => $computer->id)));
        $params = $this->set_carry_fields (array('id', 'returl'));

        if ($this->vars['save'] and $computer->id)
        {
            if ($this->vars['location_id'] >= 0)
            {
                $computer->location_id = $this->vars['location_id'];
                $computer->save_data ();
            }
            else
            {
                error_msg ($this->get_string('NEED_VALID_LOCATION'));
                $ret = $this->mk_redir ('computer_location', $params);
            }
        }
        return $ret;
    }

    /** Displays the page for specifying the type of computer */
    function computer_type ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        $tpl = 'computer_type.tpl';

        $computer = new Computer ($this->vars['id']);

        if (!$computer->id) return $this->mk_redir ('manage_computers');

        $this->assign ('computer', $computer);
        $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_type_submit', array ('id', $computer->id));

        $this->display ($tpl);
    }


    /** Saves the computer type */
    function computer_type_submit ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        $ret = $this->mk_redir ('computer_view', array ('id' => $this->vars['id']));

        $computer = new Computer ($this->vars['id']);

        if ($this->vars['save'] and $computer->id)
        {
            $computer->type = $this->vars['type'];
            $computer->save_data ();
            $ret = $this->mk_redir ('computer_type', array ('id' => $this->vars['id']));
        }

        return $ret;
    }


    /** Edit computer comments */
    function computer_comments ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        $tpl = 'computer_comments.tpl';

        $computer = new Computer ($this->vars['id']);

        if (!$computer->id) return $this->mk_redir ('manage_computers');

        $this->assign ('computer', $computer);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_comments_submit', array ('id', $computer->id));

        $this->display ($tpl);
    }


    /** Save the computer comments */
    function computer_comments_submit ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        $ret = $this->mk_redir ('computer_view', array ('id' => $this->vars['id']));

        $computer = new Computer ($this->vars['id']);

        if ($this->vars['save'] and $computer->id)
        {
            $computer->comments = $this->vars['comments'];
            $computer->save_data ();
            $ret = $this->mk_redir ('computer_comments', array ('id' => $this->vars['id']));
        }

        return $ret;
    }


    /** Displays the page with the events settings particular for a computer */
    function computer_events_settings ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('MonitorProfile');
        class_load ('ComputerReporting');
        $tpl = 'computer_events_settings.tpl';

        $computer = new Computer ($this->vars['id']);
        $profile = new MonitorProfile ($computer->profile_id);
        if (!$computer->id and !$profile->id) return $this->mk_redir ('manage_computers');

        $computer->load_events_settings ();
        $profile->load_events_settings ();
        $sources = EventLogRequested::get_events_sources_list_extended ();
        $computer_has_default_settings = EventLogRequested::computer_has_default_settings ($computer->id);

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('computer', $computer);
        $this->assign ('profile', $profile);
        $this->assign ('sources', $sources);
        $this->assign ('computer_has_default_settings', $computer_has_default_settings);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_events_settings_submit', $params);

        $this->display ($tpl);
    }

    /** Displays the page for editing the default events log settings for a computer */
    function computer_events_settings_edit ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('MonitorProfile');
        class_load ('ComputerReporting');
        $tpl = 'computer_events_settings_edit.tpl';

        $computer = new Computer ($this->vars['id']);
        $profile = new MonitorProfile ($computer->profile_id);
        if (!$computer->id and !$profile->id) return $this->mk_redir ('manage_computers');

        $profile->load_events_settings ();
        $computer->load_events_settings ();
        $sources = EventLogRequested::get_events_sources_list_extended ();

        $computer_has_default_settings = EventLogRequested::computer_has_default_settings ($computer->id);
        $profile_types = EventLogRequested::get_profile_default_types ($computer->profile_id);

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('computer', $computer);
        $this->assign ('profile', $profile);
        $this->assign ('sources', $sources);
        $this->assign ('computer_has_default_settings', $computer_has_default_settings);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_events_settings_edit_submit', $params);

        $this->display ($tpl);
    }

    /** Saves the default settings for events log for a computer */
    function computer_events_settings_edit_submit ()
    {
        check_auth ();
        class_load ('Computer');
        $computer = new Computer ($this->vars['id']);
        $profile = new MonitorProfile ($computer->profile_id);
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('computer_events_settings', $params);

        if ($this->vars['save'] and $computer->id and $profile->id)
        {
            $data = $this->vars['default_report'];
            //debug ($data);
            $computer->set_default_events_reporting ($data);
            $ret = $this->mk_redir ('computer_events_settings_edit', $params);
        }

        return $ret;
    }


    /** Displays the page for defining additional events sources for a computer */
    function computer_events_src_add ()
    {
        check_auth ();
        class_load ('Computer');
        class_load ('ComputerReporting');
        $tpl = 'computer_events_src_add.tpl';
        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');

        $computer->load_events_settings ();
        $sources = EventLogRequested::get_events_sources_list_extended ();

        $src = new EventLogRequested ();
        if (!empty_error_msg()) $src->load_from_array(restore_form_data ('computer_events_src_add', false, $data));

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('computer', $computer);
        $this->assign ('src', $src);
        $this->assign ('sources', $sources);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_events_src_add_submit', $params);

        $this->display ($tpl);
    }

    /** Adds the new events reporting source for the profile */
    function computer_events_src_add_submit ()
    {
        check_auth ();
        class_load ('Computer');
        class_load ('ComputerReporting');
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('computer_events_settings', $params);
        $computer = new Computer ($this->vars['id']);

        if ($this->vars['save'] and $computer->id)
        {
            $sources = $this->vars['sources'];
            $data = $this->vars['src'];
            if ($data['category_id'] and isset($sources[$data['category_id']])) $data['source_id'] = $sources[$data['category_id']];
            $data['category_id'] = (isset($data['category_id']) ? $data['category_id'] : EVENTLOG_NO_REPORT);
            if (!isset($data['types'])) $data['types'] = array ();

            $src = new EventLogRequested ();
            $src->computer_id = $computer->id;
            $src->load_from_array ($data);

            if ($src->is_valid_data ())
            {
                $src->save_data ();
                $params['src_id'] = $src->id;
                $ret = $this->mk_redir ('computer_events_src_edit', $params);
            }
            else
            {
                save_form_data ($data, 'computer_events_src_add');
                $ret = $this->mk_redir ('computer_events_src_add', $params);
            }
        }

        return $ret;
    }


    /** Displays the page for defining additional events sources for the profile */
    function computer_events_src_edit ()
    {
        check_auth ();
        class_load ('Computer');
        class_load ('ComputerReporting');
        $tpl = 'computer_events_src_edit.tpl';
        $computer = new Computer ($this->vars['id']);
        $src = new EventLogRequested ($this->vars['src_id']);
        if (!$computer->id or !$src->id) return $this->mk_redir ('manage_computers');

        $computer->load_events_settings ();
        $sources = EventLogRequested::get_events_sources_list_extended ();
        if (!empty_error_msg()) $src->load_from_array(restore_form_data ('computer_events_src_edit', false, $data));

        $params = $this->set_carry_fields (array('id', 'src_id'));
        $this->assign ('computer', $computer);
        $this->assign ('src', $src);
        $this->assign ('sources', $sources);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_events_src_edit_submit', $params);

        $this->display ($tpl);
    }

    /** Saves the events reporting source for the profile */
    function computer_events_src_edit_submit ()
    {
        check_auth ();
        class_load ('Computer');
        class_load ('ComputerReporting');
        $params = $this->set_carry_fields (array('id', 'src_id'));
        $computer = new Computer ($this->vars['id']);
        $src = new EventLogRequested ($this->vars['src_id']);
        $ret = $this->mk_redir ('computer_events_settings', array('id'=>$computer->id));

        if ($this->vars['save'] and $computer->id and $src->id)
        {
            $sources = $this->vars['sources'];
            $data = $this->vars['src'];
            if (!isset($data['types'])) $data['types'] = array ();
            $src->load_from_array ($data);

            if ($src->is_valid_data ()) $src->save_data ();
            else save_form_data ($data, 'computer_events_src_edit');

            $ret = $this->mk_redir ('computer_events_src_edit', $params);
        }

        return $ret;
    }


    /** Displays the page for defining additional events sources for the profile */
    function computer_events_src_delete ()
    {
        check_auth ();
        class_load ('ComputerReporting');
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('computer_events_settings', $params);
        $src = new EventLogRequested ($this->vars['src_id']);

        if ($src->id) $src->delete ();

        return $ret;
    }

    /** Deletes all computer-specific settings for events log, leaving it to use the profile's settings */
    function computer_events_revert_to_profile ()
    {
        check_auth ();
        class_load ('EventLogRequested');
        class_load ('Computer');
        $params = $this->set_carry_fields (array('id'));
        $computer = new Computer ($this->vars['id']);
        $ret = $this->mk_redir ('computer_events_settings', $params);

        if ($computer->id) EventLogRequested::remove_computer_settings ($computer->id);

        return $ret;
    }


    /** Shows the page for editing a manual computer item */
    function computer_edit_item ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('MonitorProfile');
        $tpl = 'computer_edit_item.tpl';

        $computer = new Computer($this->vars['computer_id']);
        $computer_item = new ComputerItem($computer->id, $this->vars['item_id']);


        if ($computer_item->itemdef->multi_values == MONITOR_MULTI_YES)
        {
            // Add an extra item as placeholder for multi-value items
            $computer_item->val[] = array();
        }
        elseif (empty ($computer_item->val))
        {
            // Add a placeholder for single-value items if no value is present
            $computer_item->val[] = array();
        }

        // Special case for warranties
        $warranty_product_field_id = $computer->get_item_id ('product', WARRANTY_ITEM_ID);
        $warranty_sn_field_id = $computer->get_item_id ('sn', WARRANTY_ITEM_ID);
        $sn_item_id = $computer->get_item_id ('computer_sn');
        $brand_item_id = $computer->get_item_id ('computer_brand');
        $model_item_id = $computer->get_item_id ('computer_model');
        if ($computer_item->item_id == WARRANTY_ITEM_ID )
        {
            $sn_item = new ComputerItem ($computer->id, $sn_item_id);
            $brand_item = new ComputerItem ($computer->id, $brand_item_id);
            $model_item = new ComputerItem ($computer->id, $model_item_id);
            $computer_sn = $sn_item->val[0]->value;
            $computer_product_name = trim (trim($model_item->val[0]->value).' - '.trim($brand_item->val[0]->value));

            $warranty_service_package_field_id = $computer->get_item_id ('service_package_id', WARRANTY_ITEM_ID);
            $warranty_service_level_field_id = $computer->get_item_id ('service_level_id', WARRANTY_ITEM_ID);

            $this->assign ('is_warranty', true);
            $this->assign ('computer_sn', $computer_sn);
            $this->assign ('computer_product_name', $computer_product_name);
        }

        $this->assign ('computer', $computer);
        $this->assign ('computer_item', $computer_item);
        $this->assign ('warranty_product_field_id', $warranty_product_field_id);
        $this->assign ('warranty_sn_field_id', $warranty_sn_field_id);
        $this->assign ('AVAILABLE_ITEMS_LISTS_NAMES', $GLOBALS['AVAILABLE_ITEMS_LISTS_NAMES']);
        $this->assign ('AVAILABLE_ITEMS_LISTS', $GLOBALS['AVAILABLE_ITEMS_LISTS']);

        $params = array ('computer_id'=>$computer->id, 'item_id'=>$computer_item->itemdef->id);
        if (isset($this->vars['ret'])) $params['ret'] = $this->vars['ret'];
        $this->set_form_redir ('computer_edit_item_submit', $params);

        $this->assign ('delete_url', $this->mk_redir ('computer_edit_item_del', $params));

        $this->display ($tpl);
    }


    /** Saves the value of a manually editable item */
    function computer_edit_item_submit ()
    {
        check_auth (array('computer_id' => $this->vars['computer_id']));
        class_load ('Computer');
        class_load ('MonitorProfile');
        $computer = new Computer($this->vars['computer_id']);

        if ($this->vars['save'])
        {
            // Cleanup the entered data
            foreach ($this->vars['item']['value'] as $idx=>$vals)
            {
                if (is_array($vals))
                {
                    foreach ($vals as $k=>$v)
                    {
                        if ($v and !is_array($v)) $this->vars['item']['value'][$idx][$k] = stripslashes ($v);
                    }
                }
                else $this->vars['item']['value'][$idx] = stripslashes ($vals);
            }

            $computer_item = new ComputerItem($computer->id, $this->vars['item_id']);

            // Convert date fields, if any are present
            if ($computer_item->itemdef->type == MONITOR_TYPE_STRUCT)
            {
                foreach ($this->vars['item']['value'] as $key=>$val)
                {
                    foreach ($val as $id=>$val2)
                    {
                        if (is_array($val2) and isset($val2['Date_Month']))
                        {
                            $date = mktime(0,0,0,$val2['Date_Month'],$val2['Date_Day'],$val2['Date_Year']);
                            if ($date>0) $this->vars['item']['value'][$key][$id] = $date;
                            else $this->vars['item']['value'][$key][$id] = 0;
                        }
                    }
                }
            }
            else
            {
                foreach ($this->vars['item']['value'] as $key=>$val)
                {
                    if (is_array($val) and isset($val['Date_Month']))
                    {
                        $date = mktime(0,0,0,$val['Date_Month'],$val['Date_Day'],$val['Date_Year']);
                        if ($date>0) $this->vars['item']['value'][$key] = $date;
                        else $this->vars['item']['value'][$key] = 0;
                    }
                }
            }

            // Check if the last value in list is still empty or something has been added - for multi-value items
            if ($computer_item->itemdef->multi_values == MONITOR_MULTI_YES)
            {
                $added = false;
                $tot_values = count($this->vars['item']['value']);

                if ($tot_values > 0)
                {
                    if (is_array($this->vars['item']['value'][$tot_values-1]))
                    {
                        foreach ($this->vars['item']['value'][$tot_values-1] as $key=>$val)
                        {
                            if (!empty($this->vars['item']['value'][$tot_values-1][$key])) $added=true;
                        }
                    }
                    else
                    {
                        $added = !empty($this->vars['item']['value'][$tot_values-1]);
                    }
                    if (!$added) unset($this->vars['item']['value'][$tot_values-1]);
                }
            }

            // Load new values and mark changed items
            foreach ($this->vars['item']['value'] as $key=>$val)
            {
                if ($val != $computer_item->val[$key]->value)
                {
                    $computer_item->val[$key]->updated = time();
                }
                $computer_item->val[$key]->value = $val;
            }

            $computer_item->save_data();

            $params = array ('computer_id'=>$computer->id, 'item_id'=>$computer_item->itemdef->id);
            if (isset($this->vars['ret'])) $params['ret'] = $this->vars['ret'];
            $ret = $this->mk_redir ('computer_edit_item', $params);
        }
        else
        {
            if ($this->vars['ret'] == 'manage_warranties')
                $ret = $this->mk_redir ('manage_warranties', array ('customer_id' => $computer->customer_id));
            else
                $ret = $this->mk_redir ('computer_view', array ('id' => $this->vars['computer_id']));
        }
        return $ret;
    }

    /**
    * Deletes a specified value from a multi-value computer item
    */
    function computer_edit_item_del()
    {
        check_auth (array('computer_id' => $this->vars['computer_id']));
        class_load ('ComputerItem');

        $computer_item = new ComputerItem($this->vars['computer_id'], $this->vars['item_id']);

        if (isset($computer_item->val[$this->vars['val']]))
        {
            unset ($computer_item->val[$this->vars['val']]);
            $computer_item->save_data();
        }

        $params = array ('computer_id'=>$this->vars['computer_id'], 'item_id'=>$this->vars['item_id']);
        if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
        return $this->mk_redir ('computer_edit_item', $params);
    }

    /****************************************************************/
    /* Merging computers						*/
    /****************************************************************/

    /** Displays the page for merging computers */
    function computers_merge ()
    {
        class_load ('Computer');
        class_load ('Customer');
        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');
        $tpl = 'computers_merge.tpl';

        $customer = new Customer ($computer->customer_id);
        $computers_list = Computer::get_computers_list (array('customer_id' => $customer->id));
        $computers = Computer::get_computers (array('customer_id' => $customer->id, 'order_by' => 'netbios_name' ), $no_count);

        $identical_macs = $computer->get_identical_macs ();
        $identical_names = $computer->get_identical_names ();

        // Remove from the computers array the ones from identical_macs and identical_names
        unset ($computers_list[$computer->id]);
        for ($i=0; $i<count($identical_macs); $i++) unset ($computers_list[$identical_macs[$i]->id]);
        for ($i=0; $i<count($identical_names); $i++) unset ($computers_list[$identical_names[$i]->id]);

        for ($i=count($computers)-1; $i>=0; $i--)
        {
            if (!isset($computers_list[$computers[$i]->id])) unset ($computers[$i]);
        }


        $this->assign ('computer', $computer);
        $this->assign ('customer', $customer);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('computers', $computers);
        $this->assign ('identical_macs', $identical_macs);
        $this->assign ('identical_names', $identical_names);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computers_merge_submit', array ('id' => $computer->id));

        $this->display ($tpl);
    }


    /** Processes the request to merge computers and, if everything is ok, redirect to the confirmation page */
    function computers_merge_submit ()
    {
        class_load ('Computer');
        class_load ('Customer');
        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');
        check_auth (array ('computer_id' => $computer->id));

        $ret = $this->mk_redir ('computer_view', array ('id' => $computer->id));

        if ($this->vars['do_merge'] and $computer->id)
        {
            if ($this->vars['selected_id']) $selected_computer = new Computer ($this->vars['selected_id']);

            if (!$selected_computer->id)
            {
                error_msg ($this->get_string('NEED_COMPUTER'));
                $ret = $this->mk_redir ('computers_merge', array ('id' => $computer->id));
            }
            else
            {
                $ret = $this->mk_redir ('computers_merge_confirm', array ('id' => $computer->id, 'selected_id' => $selected_computer->id));
            }
        }

        return $ret;
    }


    /** Displays the page asking for the confirmation for merging two computers */
    function computers_merge_confirm ()
    {
        class_load ('Computer');
        class_load ('Customer');
        $computer = new Computer ($this->vars['id']);
        $selected_computer = new Computer ($this->vars['selected_id']);
        $tpl = 'computers_merge_confirm.tpl';

        check_auth (array('computer_id' => $computer->id));
        check_auth (array('computer_id' => $selected_computer->id));

        if ($computer->last_contact < $selected_computer->last_contact)
        {
            // Making sure that the most recently reporting computer is preserved
            $tmp = $computer;
            $computer = $selected_computer;
            $selected_computer = $tmp;
        }

        $this->assign ('computer', $computer);
        $this->assign ('selected_computer', $selected_computer);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computers_merge_confirm_submit', array ('id' => $computer->id, 'selected_id' => $selected_computer->id));

        $this->display ($tpl);
    }


    /** Performs the merging of the two computers */
    function computers_merge_confirm_submit ()
    {
        class_load ('Computer');
        class_load ('Customer');
        $computer = new Computer ($this->vars['id']);
        $selected_computer = new Computer ($this->vars['selected_id']);

        check_auth (array('computer_id' => $computer->id));
        check_auth (array('computer_id' => $selected_computer->id));

        $ret = $this->mk_redir ('computer_view', array ('id' => $computer->id));

        if ($this->vars['do_merge'] and $computer->id and $selected_computer->id and ($computer->id!=$selected_computer->id))
        {
            $computer->merge_with_computer ($selected_computer->id);
            $ret = $this->mk_redir ('computers_merge_finished', array ('id'=>$computer->id));
        }

        return $ret;
    }


    /** Displays the page confirming that the merging has finished */
    function computers_merge_finished ()
    {
        class_load ('Computer');
        class_load ('Customer');
        $computer = new Computer ($this->vars['id']);
        check_auth (array('computer_id' => $computer->id));
        $tpl = 'computers_merge_finished.tpl';

        $this->assign ('computer', $computer);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }
    
    
    /****************************************************************/
    /* Remote access						*/
    /****************************************************************/

    /** Shows the page for getting remote access (VNC or Terminal Services) */
    function computer_remote_access ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('Customer');
        class_load ('RemoteAccess');
        class_load ('ComputerRemoteService');
        class_load ('ComputerPassword');
        class_load ('Plink');

        $tpl = 'computer_remote_access.tpl';

        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');
        $customer = new Customer ($computer->customer_id);

        // Specifies if this is a simple view mode (Plink) or the complete one
        $view = ($this->vars['view'] ? $this->vars['view'] : 'simple');

        $adapters = $computer->get_item('net_adapters');
        $ips = array ();

        $net_adapters_field_id = $computer->get_item_id ('net_adapters');
        $ip_field_id = $computer->get_item_id('ip_address', $net_adapters_field_id);
        $adapter_name_id = $computer->get_item_id('name', $net_adapters_field_id);

        // Fetch VNC information and the IDs of the item fields storing VNC password and port
        $vnc_info = $computer->get_item('vnc');
        $vnc_id = $computer->get_item_id ('vnc');
        $vnc_pwd_id = $computer->get_item_id ('pwd_hash', $vnc_id);
        $vnc_port_id = $computer->get_item_id ('port', $vnc_id);

        for ($i=0; $i<count($adapters); $i++)
        {
            if ($adapters[$i][$ip_field_id] and $adapters[$i][$ip_field_id] != '0.0.0.0')
            {
                $ips[] = array('ip'=>trim($adapters[$i][$ip_field_id]), 'adapter'=>trim($adapters[$i][$adapter_name_id]));
            }
        }

        // Get port forwarding gateways and computer services
        $remote_ips = RemoteAccess::get_ips (array('customer_id' => $customer->id));
        $computer_services = ComputerRemoteService::get_services (array('computer_id' => $computer->id));

        $other_plink = array (
            'public_ip' => $computer->remote_ip,
            'public_port' => '22',
            'computer_ip' => $ips[0]['ip']
        );
        $selected_plink = 0;
        $plink_local_port = str_pad ('1'.$computer->id, 5, '0', STR_PAD_RIGHT);

        // Get passwords for this computer
        $computer_passwords = ComputerPassword::get_passwords (array('computer_id' => $computer->id));
        $expired_computer_passwords = ComputerPassword::get_passwords (array('computer_id' => $computer->id, 'include_expired' => true));

        // See if there are any previous saved Plink settings for this user and computer
        $saved_plink = new Plink ($this->current_user->id, $computer->id);
        $this->assign ('saved_plink', $saved_plink);

        $this->assign ('computer', $computer);
        $this->assign ('view', $view);
        $this->assign ('customer', $customer);
        $this->assign ('ips', $ips);
        $this->assign ('vnc_info', $vnc_info);
        $this->assign ('vnc_pwd_id', $vnc_pwd_id);
        $this->assign ('vnc_port_id', $vnc_port_id);

        $this->assign ('remote_ips', $remote_ips);
        $this->assign ('computer_services', $computer_services);
        $this->assign ('other_plink', $other_plink);
        $this->assign ('selected_plink', $selected_plink);
        $this->assign ('plink_local_port', $plink_local_port);
        $this->assign ('REMOTE_SERVICE_NAMES', $GLOBALS['REMOTE_SERVICE_NAMES']);
        $this->assign ('computer_passwords', $computer_passwords);
        $this->assign ('expired_computer_passwords', $expired_computer_passwords);

        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('computer_remote_access_submit', array ('id'=>$computer->id));

        return $this->display ($tpl);
    }

    function computer_remote_access_submit ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        class_load ('Plink');

        if ($this->vars['cancel']) return $this->mk_redir('computer_view', array ('id'=>$this->vars['id']));

        $computer = new Computer($this->vars['id']);
        $fname = $computer->netbios_name.'_'.$computer->id;

        $can_connect = true;
        if ($this->vars['connect_vnc'])
        {

            // Do a VNC connection
            $tpl = 'computer_remote_access_vnc.tpl';
            $fname.= '.vnc';

            if (!$this->vars['vnc_port'])
            {
                error_msg ($this->get_string('NEED_VNC_PORT'));
                $can_connect = false;
            }

            //Allow empty hash
            //if (!$this->vars['vnc_hash']) {error_msg ('Please specify the VNC password hash.'); $can_connect = false;}

            @ob_end_clean();
            @ini_set('zlib.output_compression', 'Off');

            $connect_ip = '';
            if ($this->vars['connect_using']=='other') $connect_ip = $this->vars['other_ip'];
            else $connect_ip = $this->vars['connect_using'];

            if (!$connect_ip) {error_msg ($this->get_string('NEED_IP_ADDRESS')); $can_connect = false;}

            if ($can_connect)
            {
                $this->assign ('host', $connect_ip);
                $this->assign ('port', $this->vars['vnc_port']);
                $this->assign ('password_hash', $this->vars['vnc_hash']);

                $conf_file = $this->fetch ($tpl);

                //header ("Pragma: no-cache");
                header ("Pragma: private");
                header ("Expires: 0");
                header ("Content-type: application/vnc");
                header ("Content-Transfer-Encoding: none");
                header ("Cache-Control: private");
                header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header ("Content-length: ".strlen($conf_file));
                header ("Content-Disposition: inline; filename=\"computer.vnc\"");
                header ("Connection: close");

                echo $conf_file;
                die;
            }
        }

        if ($this->vars['connect_rdp'])
        {
                $tpl = 'computer_remote_access_rdp.tpl';
                $fname.= '.rdp';

                $connect_ip = '';
                if ($this->vars['connect_using']=='other') $connect_ip = $this->vars['other_ip'];
                else $connect_ip = $this->vars['connect_using'];

                if (!$connect_ip) {error_msg ($this->get_string('NEED_IP_ADDRESS')); $can_connect = false;}

                if ($can_connect)
                {
                    $addr = $connect_ip . ($this->vars['rdp_port'] ? ':'.$this->vars['rdp_port'] : '');
                    $addr_new = '';
                    for ($i=0;$i<strlen($addr); $i++) $addr_new.= $addr[$i].chr(0);

                    @ob_end_clean();
                    @ini_set('zlib.output_compression', 'Off');

                    // Use direct file reading instead of fetch() method, because fetch() has a problem with chr(0) characters in the file
                    $fp = fopen (dirname(__FILE__).'/../../templates/'.$tpl, 'r');
                    $conf_file = '';
                    while ($s = fread ($fp, 1024)) $conf_file.= $s;
                    fclose ($fp);
                    $conf_file = preg_replace ('/_host_/', $addr_new, $conf_file);

                    session_write_close ();

                    /*
                    header ("Pragma: no-cache");
                    */
                    header ("Pragma: private");
                    header ("Expires: 0");
                    header ("Content-type: application/rdp");
                    header ("Content-Transfer-Encoding: none");
                    header ("Cache-Control: private");
                    header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header ("Content-length: ".strlen($conf_file));
                    header ("Content-Disposition: inline; filename=\"computer.rdp\"");
                    header ("Connection: close");

                    echo $conf_file;
                    die;
                }
        }

        if ($this->vars['connect_plink'] or $this->vars['save_plink'])
        {
                // Save the current Plink settings
                $plink = new Plink ($this->current_user->id, $computer->id);
                if (!$plink->user_id or !$plink->computer_id)
                {
                        $plink->user_id = $this->current_user->id;
                        $plink->computer_id = $computer->id;
                }
                $plink->load_from_array ($this->vars['plink']);

                $plink->save_data ();
                $plink->set_services ($this->vars['plink']);


                // If requested, make the actual connection
                if ($this->vars['connect_plink'])
                {
                        $command = '"'.$this->vars['plink']['command_base'].'" '.$this->vars['plink']['command']."\n\n";

                        @ob_end_clean();
                        @ini_set('zlib.output_compression', 'Off');
                        session_write_close ();

                        //header ("Pragma: no-cache");
                        header ("Pragma: private");
                        header ("Expires: 0");
                        header ("Content-type: application/cmd");
                        header ("Content-Transfer-Encoding: none");
                        header ("Cache-Control: private");
                        header ("Content-length: ".strlen($command));
                        header ("Content-Disposition: inline; filename=\"plink.cmd\"");
                        header ("Connection: close");

                        echo $command;
                        die;
                }
        }

        $params = $this->set_carry_fields (array('id', 'view'));
        return $this->mk_redir ('computer_remote_access', $params);
    }


    /** Displays the page with computer quick contacts */
    function manage_quick_contacts ()
    {
        check_auth ();
        class_load ('ComputerQuickContact');
        $tpl = 'manage_quick_contacts.tpl';

        $contacts = ComputerQuickContact::get_quick_contacts ();

        $this->assign ('contacts', $contacts);

        $this->display ($tpl);
    }


    /** Deletes a quick contact */
    function quick_contact_delete ()
    {
        check_auth ();
        class_load ('ComputerQuickContact');

        $contact = new ComputerQuickContact ($this->vars['id']);
        if ($contact->id) $contact->delete();

        return $this->mk_redir ('manage_quick_contacts');
    }


    /** Displays the page showing the computers who have not reported in a long time */
    function manage_oldest_contacts ()
    {
        check_auth ();
        class_load ('Computer');
        class_load ('Customer');
        $tpl = 'manage_oldest_contacts.tpl';

        $contacts = Computer::get_oldest_contacts ();
        $customers_list = Customer::get_customers_list ();

        for ($i=0; $i<count($contacts); $i++)
        {
            $contacts[$i]->days_missed = round ((time() - $contacts[$i]->last_contact)/(3600*24));
        }

        $this->assign ('contacts', $contacts);
        $this->assign ('customers_list', $customers_list);

        $this->display ($tpl);
    }

    /****************************************************************/
    /* Computers Roles Management					*/
    /****************************************************************/

    /** Displays the page for managing the computers roles */
    function manage_roles ()
    {
        check_auth ();
        class_load ('Role');
        $tpl = 'manage_roles.tpl';

        $roles = Role::get_roles ();

        $this->assign ('roles', $roles);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }

    /** Displays the page for defining a new role */
    function role_add ()
    {
        check_auth ();
        class_load ('Role');
        $tpl = 'role_add.tpl';

        $role = new Role ();

        $this->assign ('role', $role);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('role_add_submit');

        $this->display ($tpl);
    }


    /** Saves a new computer role */
    function role_add_submit ()
    {
        check_auth ();
        class_load ('Role');
        $ret = $this->mk_redir ('manage_roles');

        if ($this->vars['save'])
        {
            $data = $this->vars['role'];
            $role = new Role ();
            $role->load_from_array ($data);

            if ($role->is_valid_data ())
            {
                $role->save_data ();
                $ret = $this->mk_redir ('role_edit', array ('id' => $role->id));
            }
            else
            {
                $ret = $this->mk_redir ('role_add');
            }
        }

        return $ret;
    }


    /** Displays the page for editing a computer role */
    function role_edit ()
    {
        check_auth ();
        class_load ('Role');
        $tpl = 'role_edit.tpl';

        $role = new Role ($this->vars['id']);
        if (!$role->id) return $this->mk_redir ('manage_roles');

        $this->assign ('role', $role);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('role_edit_submit', array ('id' => $role->id));

        $this->display ($tpl);
    }


    /** Saves a computer role */
    function role_edit_submit ()
    {
        check_auth ();
        class_load ('Role');
        $role = new Role ($this->vars['id']);
        $ret = $this->mk_redir ('manage_roles');

        if ($this->vars['save'] and $role->id)
        {
            $data = $this->vars['role'];
            $role->load_from_array ($data);

            if ($role->is_valid_data ())
            {
                $role->save_data ();
            }
            $ret = $this->mk_redir ('role_edit', array ('id' => $role->id));
        }

        return $ret;
    }


    /** Deletes a computer role */
    function role_delete ()
    {
        check_auth ();
        class_load ('Role');
        $role = new Role ($this->vars['id']);
        $ret = $this->mk_redir ('manage_roles');

        if ($role->id and $role->can_delete ())
        {
            $role->delete ();
        }

        return $ret;
    }


    /** Displays the page for setting the roles of a computer */
    function computer_roles ()
    {
        class_load ('Role');
        class_load ('Computer');
        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');
        check_auth (array('computer_id' => $computer->id));
        $tpl = 'computer_roles.tpl';

        $computer->load_roles ();
        $roles_list = Role::get_roles_list ();

        $this->assign ('computer', $computer);
        $this->assign ('roles_list', $roles_list);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('computer_roles_submit', array ('id' => $computer->id));

        $this->display ($tpl);
    }


    /** Saves the computer's roles list */
    function computer_roles_submit ()
    {
        class_load ('Computer');
        class_load ('Role');
        $computer = new Computer ($this->vars['id']);
        $ret = $this->mk_redir ('computer_view', array ('id' => $computer->id));

        if ($this->vars['save'] and $computer->id)
        {
            $computer->set_roles ($this->vars['roles']);
            $ret = $this->mk_redir ('computer_roles', array ('id' => $computer->id));
        }

        return $ret;
    }


    /****************************************************************/
    /* Peripherals Management					*/
    /****************************************************************/

    function manage_peripherals ()
    {
        class_load ('Computer');
        class_load ('Customer');
        class_load ('AD_Printer');
        class_load ('Peripheral');
        class_load ('Supplier');
        $tpl = 'manage_peripherals.tpl';

        $extra_params = array();	// Extra parameters to be carried in navigation
        if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        if (isset($this->vars['customer_id']))
        {
            $_SESSION['manage_peripherals']['customer_id'] = $this->vars['customer_id'];
        }
        elseif ($this->locked_customer->id and !$this->vars['do_filter'])
        {
                // If 'do_filter' is present in request, the locked customer is ignored
            $_SESSION['manage_peripherals']['customer_id'] = $this->locked_customer->id;
        }
        $filter = $_SESSION['manage_peripherals'];


        // Check authorization
        if ($filter['customer_id'] > 0)
        {
            // Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
            // This way he can return to this page, without getting again "Permission Denied".
            unset ($_SESSION['manage_peripherals']['customer_id']);
            check_auth (array('customer_id' => $filter['customer_id']));
            $_SESSION['manage_peripherals']['customer_id'] = $filter['customer_id'];
        }
        else check_auth ();


        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);

        // If a customer was selected, fetch the data for that customer
        $peripherals_all = array ();
        if ($filter['customer_id'] > 0)
        {
            $customer_id = $filter['customer_id'];
            $customer = new Customer ($customer_id);
            $add_url = $this->mk_redir ('peripheral_add', array ('customer_id' => $customer->id));
            $classes_list = PeripheralClass::get_classes_list ();
            $computers_list = Computer::get_computers_list (array('customer_id' => $customer->id));

            // AD Printers
            $ad_printers = AD_Printer::get_ad_printers (array('customer_id' => $customer->id));
            for ($i=0; $i<count($ad_printers); $i++) $ad_printers[$i]->load_location ();

            $peripherals_all = Peripheral::get_peripherals (array('customer_id' => $customer->id));

            // Set the display widths
            $display_widths = array ();
            $name_widths = array ();
            foreach ($peripherals_all as $class_id => $peripherals)
            {
                $max_width = ($peripherals[0]->class_def->link_computers ? 80 : 90);
                $display_widths[$class_id] = $peripherals[0]->class_def->get_display_widths ($max_width, $name_width);
                $name_widths[$class_id] = $name_width;
            }

            // Mark the potential customer for locking
            if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

            $service_packages_list = SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true));
            $service_levels_list = ServiceLevel::get_service_levels_list ();
        }


        // Load the photos, if any exist
        foreach ($peripherals_all as $class_id => $peripherals)
        {
            for ($i=0; $i<count($peripherals); $i++)
            {
                $peripherals_all[$class_id][$i]->load_photos ();
            }
        }

        $this->assign ('peripherals_all', $peripherals_all);
        $this->assign ('ad_printers', $ad_printers);
        $this->assign ('add_url', $add_url);
        $this->assign ('display_widths', $display_widths);
        $this->assign ('name_widths', $name_widths);
        $this->assign ('filter', $filter);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('classes_list', $classes_list);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('service_packages_list', $service_packages_list);
        $this->assign ('service_levels_list', $service_levels_list);
        $this->assign ('customer', $customer);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('manage_peripherals_submit', $extra_params);

        $this->display ($tpl);
    }


    function manage_peripherals_submit ()
    {
        check_auth ();

        $extra_params = array();
        $_SESSION['manage_peripherals'] = $this->vars['filter'];

        if ($this->vars['do_filter'] or $this->vars['do_filter_hidden'])
        {
            $extra_params['do_filter'] = 1;
        }

        return $this->mk_redir('manage_peripherals', $extra_params);
    }

    
    /** Displays the page for adding a new peripheral */
    function peripheral_add ()
    {
        class_load ('Peripheral');
        class_load ('Customer');
        class_load ('Supplier');
        $tpl = 'peripheral_add.tpl';

        $peripheral_class = new PeripheralClass ($this->vars['class_id']);
        $customer = new Customer ($this->vars['customer_id']);
        if (!$peripheral_class->id or !$customer->id) return $this->mk_redir ('manage_peripherals');

        check_auth (array('customer_id' => $customer->id));

        $peripheral = new Peripheral ();
        $peripheral->customer_id = $customer->id;
        $peripheral->class_id = $peripheral_class->id;
        $peripheral->class_def = $peripheral_class;

        // Load the previously submitted data, in case there was an error
        $peripheral_data = array ();
        if (!empty_error_msg()) restore_form_data ('peripheral', false, $peripheral_data);
        $peripheral->load_from_array ($peripheral_data, true);
        if (!$peripheral->date_created) $peripheral->date_created = time ();

        // Mark the potential customer for locking
        $_SESSION['potential_lock_customer_id'] = $customer->id;

        $this->assign ('peripheral', $peripheral);
        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('customer', $customer);
        $this->assign ('CRIT_MEMORY_MULTIPLIERS_NAMES', $GLOBALS['CRIT_MEMORY_MULTIPLIERS_NAMES']);
        $this->assign ('service_packages_list', SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true)));
        $this->assign ('service_levels_list', ServiceLevel::get_service_levels_list ());
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_add_submit', array ('class_id' => $peripheral_class->id, 'customer_id' => $customer->id));

        $this->display ($tpl);
    }


    /** Saves the new peripheral data */
    function peripheral_add_submit ()
    {
        class_load ('Peripheral');
        class_load ('Supplier');
        $peripheral_class = new PeripheralClass ($this->vars['class_id']);
        $customer = new Customer ($this->vars['customer_id']);
        $ret = $this->mk_redir ('manage_peripherals', array ('customer_id' => $customer->id));

        check_auth (array('customer_id' => $customer->id));

        if ($this->vars['save'] and $peripheral_class->id and $customer->id)
        {
            $peripheral = new Peripheral ();
            $peripheral->customer_id = $customer->id;
            $peripheral->class_id = $peripheral_class->id;
            $peripheral->class_def = $peripheral_class;

            $peripheral_data = $this->vars['peripheral'];
            $peripheral_data['date_created'] = js_strtotime ($peripheral_data['date_created']);
            $values = $this->vars['values'];
            // Set the date and memory fields, if any
            for ($i=0; $i<count($peripheral_class->field_defs); $i++)
            {
                $id = $peripheral_class->field_defs[$i]->id;
                if ($peripheral_class->field_defs[$i]->type == MONITOR_TYPE_DATE)
                {
                    $values[$id] = mktime (0,0,0, $values[$id]['Date_Month'], $values[$id]['Date_Day'], $values[$id]['Date_Year']);
                }
                elseif ($peripheral_class->field_defs[$i]->type == MONITOR_TYPE_MEMORY)
                {
                    $values[$id] = pow (1024, $values[$id]['multiplier']-1) * $values[$id]['size'];
                }
            }
            $peripheral_data ['values'] = $values;

            $peripheral->load_from_array ($peripheral_data, true);
            if ($peripheral->is_valid_data ())
            {
                $peripheral->save_data (true);
                $ret = $this->mk_redir ('peripheral_edit', array ('id' => $peripheral->id));
            }
            else
            {
                save_form_data ($peripheral_data, 'peripheral');
                $ret = $this->mk_redir ('peripheral_add', array ('customer_id' => $customer->id, 'class_id' => $peripheral_class->id));
            }
        }

        return $ret;
    }


    /** Displays the page for editing a peripheral */
    function peripheral_edit ()
    {
        class_load ('Peripheral');
        class_load ('Customer');
        class_load ('Supplier');
        class_load ('Discovery');
        class_load ('DiscoverySettingDetail');

        $tpl = 'peripheral_edit.tpl';

        $peripheral = new Peripheral ($this->vars['id']);
        $customer = new Customer ($peripheral->customer_id);
        $peripheral_class = new PeripheralClass ($peripheral->class_id);

        check_auth (array('customer_id' => $customer->id));

        // Load the previously submitted data, in case there was an error
        $peripheral_data = array ();
        if (!empty_error_msg()) restore_form_data ('peripheral', false, $peripheral_data);
        $peripheral->load_from_array ($peripheral_data, true);

        $computers_list = Computer::get_computers_list (array ('customer_id' => $peripheral->customer_id));
        $available_computers_list = $computers_list;
        foreach ($available_computers_list as $id => $name)
        {
            if (in_array ($id, $peripheral->computers)) unset ($available_computers_list[$id]);
        }

        // Mark the potential customer for locking
        $_SESSION['potential_lock_customer_id'] = $customer->id;

        $peripheral->load_photos ();
        $peripheral->load_location ();

        // SNMP-related information
        $snmp_computer = $peripheral->get_snmp_computer ();
        $profile = $peripheral->get_monitoring_profile ();

        $params = $this->set_carry_fields (array('id', 'ret', 'returl'));

        // Load any related notifications
        $notifications = Notification::get_notifications (array('object_class' => NOTIF_OBJ_CLASS_PERIPHERAL, 'object_id'=>$peripheral->id));

        // Load any matched devices from networks discoveries
        $discoveries = Discovery::get_matches_for_peripheral ($peripheral->id);
        $disc_details = array ();
        foreach ($discoveries as $discovery)
        {
            if (!isset($disc_details[$discovery->detail_id])) $disc_details[$discovery->detail_id] = new DiscoverySettingDetail ($discovery->detail_id);
        }

        $this->assign ('peripheral', $peripheral);
        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('notifications', $notifications);
        $this->assign ('customer', $customer);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('available_computers_list', $available_computers_list);
        $this->assign ('service_packages_list', SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true)));
        $this->assign ('service_levels_list', ServiceLevel::get_service_levels_list ());
        $this->assign ('snmp_computer', $snmp_computer);
        $this->assign ('profile', $profile);
        $this->assign ('discoveries', $discoveries);
        $this->assign ('disc_details', $disc_details);
        $this->assign ('CRIT_MEMORY_MULTIPLIERS_NAMES', $GLOBALS['CRIT_MEMORY_MULTIPLIERS_NAMES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_edit_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the peripheral data */
    function peripheral_edit_submit ()
    {
        class_load ('Peripheral');
        class_load ('Customer');
        $peripheral = new Peripheral ($this->vars['id']);

        $params = $this->set_carry_fields (array('ret', 'returl'));
        check_auth (array('customer_id' => $peripheral->customer_id));

        if ($this->vars['save'] and $peripheral->id)
        {
            $peripheral_data = $this->vars['peripheral'];
            $peripheral_data['date_created'] = js_strtotime ($peripheral_data['date_created']);

            $values = $this->vars['values'];
            // Set the date and memory fields, if any
            for ($i=0; $i<count($peripheral->class_def->field_defs); $i++)
            {
                $id = $peripheral->class_def->field_defs[$i]->id;
                if ($peripheral->class_def->field_defs[$i]->type == MONITOR_TYPE_DATE)
                {
                    $values[$id] = mktime (0,0,0, $values[$id]['Date_Month'], $values[$id]['Date_Day'], $values[$id]['Date_Year']);
                }
                elseif ($peripheral->class_def->field_defs[$i]->type == MONITOR_TYPE_MEMORY)
                {
                    $values[$id] = pow (1024, $values[$id]['multiplier']-1) * $values[$id]['size'];
                }
            }
            $peripheral_data ['values'] = $values;
            $peripheral_data ['computers'] = (isset($this->vars['computers']) ? $this->vars['computers'] : array());

            $peripheral->load_from_array ($peripheral_data, true);
            if ($peripheral->is_valid_data ())
            {
                $peripheral->save_data (true);
            }
            else
            {
                save_form_data ($peripheral_data, 'peripheral');
            }

            $params['id'] = $peripheral->id;
            $ret = $this->mk_redir ('peripheral_edit', $params);
        }
        else
        {
            if ($this->vars['ret'] == 'manage_warranties')
                $ret = $this->mk_redir ('manage_warranties', array ('customer_id' => $peripheral->customer_id));
            elseif ($this->vars['returl'])
                $ret = $this->vars['returl'];
            else
                $ret = $this->mk_redir ('manage_peripherals', array ('customer_id' => $peripheral->customer_id));
        }

        return $ret;
    }

    /** Displays the page for defining the settings for the SNMP monitoring of a peripheral */
    function peripheral_edit_snmp ()
    {
        check_auth ();
        class_load ('Peripheral');
        class_load ('Customer');
        $tpl = 'peripheral_edit_snmp.tpl';

        $peripheral = new Peripheral ($this->vars['id']);
        if (!$peripheral->id) return $this->mk_redir ('manage_peripherals');
        $customer = new Customer ($peripheral->customer_id);
        $class = new PeripheralClass ($peripheral->class_id);
        $class->load_profiles ();

        if (!empty_error_msg()) $peripheral->load_from_array(restore_form_data ('peripheral_snmp_edit', false, $data));

        // Get the list of available computers for this customer
        $computers_list = Computer::get_computers_list (array('customer_id' => $customer->id));
        // Get the list of computers already doing SNMP monitoring
        $computers_list_snmp = Computer::get_list_monitored_peripherals (array('customer_id' => $customer->id));

        $params = $this->set_carry_fields (array('id', 'returl'));
        $this->assign ('peripheral', $peripheral);
        $this->assign ('customer', $customer);
        $this->assign ('class', $class);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('computers_list_snmp', $computers_list_snmp);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_edit_snmp_submit', $params);

        $this->display ($tpl);
    }

    /** Save the SNMP monitoring settings for a peripheral */
    function peripheral_edit_snmp_submit ()
    {
        check_auth ();
        class_load ('Peripheral');
        $params = $this->set_carry_fields (array('id', 'returl'));
        $peripheral = new Peripheral ($this->vars['id']);
        $ret = $this->mk_redir ('peripheral_edit', $params);

        if ($this->vars['save'] and $peripheral->id)
        {
            $data = $this->vars['peripheral'];
            $peripheral->load_from_array ($data);

            if ($peripheral->is_valid_data()) $peripheral->save_data ();
            else save_form_data ($data, 'peripheral_snmp_edit');
            $ret = $this->mk_redir ('peripheral_edit_snmp', $params);
        }

        return $ret;
    }


    /** Displays the page for deleting or removing a peripheral */
    function peripheral_delete ()
    {
        $tpl = 'peripheral_delete.tpl';
        class_load ('Peripheral');
        class_load ('RemovedPeripheral');
        $peripheral = new Peripheral ($this->vars['id']);
        if (!$peripheral->id) return $this->mk_redir ('manage_peripherals');
        check_auth (array('customer_id' => $peripheral->customer_id));

        $removal = new RemovedPeripheral ();
        $data = array ();
        if (!empty_error_msg()) $removal->load_from_array(restore_form_data ('peripheral_delete', false, $data));
        if (!$removal->date_removed) $removal->date_removed = time ();

        $this->assign ('peripheral', $peripheral);
        $this->assign ('removal', $removal);
        $this->set_form_redir ('peripheral_delete_submit', array ('id' => $peripheral->id));
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Processes a request to delete a computer */
    function peripheral_delete_submit ()
    {
        class_load ('Peripheral');
        class_load ('RemovedPeripheral');
        $peripheral = new Peripheral ($this->vars['id']);
        check_auth (array('customer_id' => $peripheral->customer_id));
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('manage_peripherals');

        if ($this->vars['save'] and $peripheral->id)
        {
            $data = $this->vars['removal'];
            $data['date_removed'] = js_strtotime ($data['date_removed']);

            if ($this->vars['delete_action'] == 'do_delete')
            {
                // Delete the peripheral permanently
                if ($peripheral->can_delete()) $peripheral->delete ();
                $ret = $this->mk_redir ('manage_peripherals');
            }
            elseif ($this->vars['delete_action'] == 'do_remove')
            {
                // Move the peripheral to the removed table
                $removal = new RemovedPeripheral ();
                $removal->load_from_array ($data);

                if ($removal->is_valid_data())
                {
                    $removal = RemovedPeripheral::remove_peripheral ($peripheral, $this->current_user->id, $data['reason_removed'], $data['date_removed']);
                    $ret = $this->mk_redir ('peripheral_view', $params, 'kawacs_removed');
                }
                else
                {
                    save_form_data ($data, 'peripheral_delete');
                    $ret = $this->mk_redir ('peripheral_delete', $params);
                }
            }
            else
            {
                error_msg ($this->get_string('NEED_CHOICE_DELETE'));
                save_form_data ($data, 'peripheral_delete');
                $ret = $this->mk_redir ('peripheral_delete', $params);
            }
        }

        return $ret;
    }


    /** Displays the page for changing a peripherals's location */
    function peripheral_location ()
    {
        class_load ('Peripheral');
        class_load ('Location');
        $tpl = 'peripheral_location.tpl';
        $peripheral = new Peripheral ($this->vars['id']);
        check_auth (array('customer_id' => $peripheral->customer_id));
        $peripheral->load_location ();

        $locations_list = Location::get_locations_list (array('customer_id'=>$peripheral->customer_id, 'indent'=>true));
        $params = $this->set_carry_fields (array('id', 'returl'));

        $this->assign ('peripheral', $peripheral);
        $this->assign ('locations_list', $locations_list);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_location_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the location assignment for a computer */
    function peripheral_location_submit ()
    {
        class_load ('Peripheral');
        class_load ('Location');
        $peripheral = new Peripheral ($this->vars['id']);
        check_auth (array('customer_id' => $peripheral->customer_id));

        $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('peripheral_edit', array('id' => $peripheral->id)));
        $params = $this->set_carry_fields (array('id', 'returl'));

        if ($this->vars['save'] and $peripheral->id)
        {
            if ($this->vars['location_id'] >= 0)
            {
                $peripheral->location_id = $this->vars['location_id'];
                $peripheral->save_data ();
            }
            else
            {
                error_msg ($this->get_string('NEED_VALID_LOCATION'));
                $ret = $this->mk_redir ('peripheral_location', $params);
            }
        }
        return $ret;
    }

    /** Displays the page for making Plink tunnels to peripherals */
    function peripheral_plink ()
    {
        //xxxxxxxxxxxxxxxxxxxxxxx
        class_load ('Peripheral');
        class_load ('Customer');
        class_load ('RemoteAccess');
        class_load ('PeripheralPlink');
        $tpl = 'peripheral_plink.tpl';

        $peripheral = new Peripheral ($this->vars['id']);
        check_auth (array('customer_id' => $peripheral->customer_id));
        $plink_local_port = str_pad ('1'.$peripheral->id, 5, '0', STR_PAD_RIGHT);

        // Get port forwarding gateways
        $remote_ips = RemoteAccess::get_ips (array('customer_id' => $customer->id));
        // See if there are any previous saved Plink settings for this user and peripheral
        $saved_plink = new PeripheralPlink ($this->current_user->id, $peripheral->id);

        // Mark the potential customer for locking
        $_SESSION['potential_lock_customer_id'] = $customer->id;


        $params = $this->set_carry_fields (array('id', 'ret', 'returl'));

        $this->assign ('peripheral', $peripheral);
        $this->assign ('remote_ips', $remote_ips);
        $this->assign ('saved_plink', $saved_plink);
        $this->assign ('plink_local_port', $plink_local_port);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_plink_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the peripheral Plink settings and creates the tunnel if requested */
    function peripheral_plink_submit ()
    {
        class_load ('Peripheral');
        class_load ('Customer');
        class_load ('RemoteAccess');
        class_load ('PeripheralPlink');

        $peripheral = new Peripheral ($this->vars['id']);
        check_auth (array('customer_id' => $peripheral->customer_id));

        $ret = $this->mk_redir ('peripheral_edit', array ('id' => $peripheral->id));

        if ($this->vars['connect_plink'] or $this->vars['save_plink'])
        {
            // Save the current Plink settings
            $plink = new PeripheralPlink ($this->current_user->id, $peripheral->id);
            if (!$plink->user_id or !$plink->peripheral_id)
            {
                $plink->user_id = $this->current_user->id;
                $plink->peripheral_id = $peripheral->id;
            }
            $plink->load_from_array ($this->vars['plink']);

            $plink->save_data ();
            $plink->set_services ($this->vars['services']);

            // If requested, make the actual connection
            if ($this->vars['connect_plink'])
            {
                $command = '"'.$this->vars['plink']['command_base'].'" '.$this->vars['plink']['command']."\n\n";

                @ob_end_clean();
                @ini_set('zlib.output_compression', 'Off');
                session_write_close ();

                //header ("Pragma: no-cache");
                header ("Pragma: private");
                header ("Expires: 0");
                header ("Content-type: application/cmd");
                header ("Content-Transfer-Encoding: none");
                header ("Cache-Control: private");
                header ("Content-length: ".strlen($command));
                header ("Content-Disposition: inline; filename=\"plink.cmd\"");
                header ("Connection: close");

                echo $command;
                die;
            }

            $ret = $this->mk_redir ('peripheral_plink', array ('id' => $peripheral->id));
        }

        return $ret;
    }


    /****************************************************************/
    /* Peripherals Definitions Management				*/
    /****************************************************************/

    /** Displays the page for managing peripherals classes */
    function manage_peripherals_classes ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'manage_peripherals_classes.tpl';

        $peripherals_classes = PeripheralClass::get_classes ();

        // Check which customers are using these peripherals
        $customer_peripherals_count = array ();
        foreach ($peripherals_classes as $peripheral_class)
        {
            $customer_peripherals_count[$peripheral_class->id] = $peripheral_class->get_customers_count ();
            $peripherals_count[$peripheral_class->id] = $peripheral_class->get_peripherals_count ();
        }

        $this->assign ('peripherals_classes', $peripherals_classes);
        $this->assign ('customer_peripherals_count', $customer_peripherals_count);
        $this->assign ('peripherals_count', $peripherals_count);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('manage_peripherals_classes_submit');

        $this->display ($tpl);
    }


    /** Saves the ordering for peripherals classes */
    function manage_peripherals_classes_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $ret = $this->mk_redir ('manage_peripherals_classes');

        if ($this->vars['reorder'])
        {
                PeripheralClass::set_positions ($this->vars['positions']);
        }

        return $ret;
    }


    /** Displays the page for defining a new peripheral class */
    function peripheral_class_add ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'peripheral_class_add.tpl';

        $peripheral_class = new PeripheralClass ();
        $peripheral_class_data = array ();
        if (!empty_error_msg()) restore_form_data ('peripheral_class', false, $peripheral_class_data);
        $peripheral_class->load_from_array ($peripheral_class_data);

        $this->assign ('peripheral_clas', $peripheral_class);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_class_add_submit');

        $this->display ($tpl);
    }


    /** Saves the definition of a new peripheral class */
    function peripheral_class_add_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $ret = $this->mk_redir ('manage_peripherals_classes');

        if ($this->vars['save'])
        {
            $peripheral_class_data = $this->vars['peripheral_class'];
            $peripheral_class = new PeripheralClass ();
            $peripheral_class->load_from_array ($peripheral_class_data);

            if ($peripheral_class->is_valid_data ())
            {
                $peripheral_class->save_data ();
                $ret = $this->mk_redir ('peripheral_class_edit', array ('id' => $peripheral_class->id));
            }
            else
            {
                $ret = $this->mk_redir ('peripheral_class_add');
                save_form_data ($peripheral_class_data, 'peripheral_class');
            }
        }

        return $ret;
    }


    /** Displays the page for editing a peripheral class */
    function peripheral_class_edit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'peripheral_class_edit.tpl';

        $peripheral_class = new PeripheralClass ($this->vars['id']);
        if (!$peripheral_class->id) return $this->mk_redir ('manage_peripherals_classes');

        $peripheral_class_data = array ();
        if (!empty_error_msg()) restore_form_data ('peripheral_class', false, $peripheral_class_data);
        $peripheral_class->load_from_array ($peripheral_class_data);

        $string_fields = array ();
        $date_fields = array ();
        $string_fields_idx = $peripheral_class->get_string_fields();
        $date_fields_idx = $peripheral_class->get_date_fields();
        $int_fields_idx = $peripheral_class->get_int_fields ();

        foreach ($string_fields_idx as $i) $string_fields[$peripheral_class->field_defs[$i]->id] = $peripheral_class->field_defs[$i]->name;
        foreach ($date_fields_idx as $i) $date_fields[$peripheral_class->field_defs[$i]->id] = $peripheral_class->field_defs[$i]->name;
        foreach ($int_fields_idx as $i) $int_fields[$peripheral_class->field_defs[$i]->id] = $peripheral_class->field_defs[$i]->name;

        $profiles_list = MonitorProfilePeriph::get_profiles_list ();
        $peripheral_class->load_profiles ();

        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('fields_names', $fields_names);
        $this->assign ('date_fields', $date_fields);
        $this->assign ('string_fields', $string_fields);
        $this->assign ('int_fields', $int_fields);
        $this->assign ('profiles_list', $profiles_list);
        $this->assign ('FIELDS_TYPES', $GLOBALS['PERIPHERALS_FIELDS_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_class_edit_submit', array ('id' => $peripheral_class->id));


        $this->display ($tpl);
    }


    /** Saves the definition of a peripheral class */
    function peripheral_class_edit_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $ret = $this->mk_redir ('manage_peripherals_classes');
        $peripheral_class = new PeripheralClass ($this->vars['id']);

        if ($this->vars['save'] and $peripheral_class->id)
        {
            $peripheral_class_data = $this->vars['peripheral_class'];

            // Clean the data
            if ($peripheral_class_data['use_warranty']) $peripheral_class_data['use_warranty'] = 1;
            else
            {
                $peripheral_class_data['use_warranty'] = 0;
                $peripheral_class_data['warranty_start_field'] = 0;
                $peripheral_class_data['warranty_end_field'] = 0;
            }
            if ($peripheral_class_data['use_sn']) $peripheral_class_data['use_sn'] = 1;
            else
            {
                $peripheral_class_data['use_sn'] = 0;
                $peripheral_class_data['sn_field'] = 0;
            }
            if ($peripheral_class_data['use_web_access']) $peripheral_class_data['use_web_access'] = 1;
            else
            {
                $peripheral_class_data['use_web_access'] = 0;
                $peripheral_class_data['web_access_field'] = 0;
            }
            if ($peripheral_class_data['use_net_access']) $peripheral_class_data['use_net_access'] = 1;
            else
            {
                $peripheral_class_data['use_net_access'] = 0;
                $peripheral_class_data['net_access_ip_field'] = 0;
                $peripheral_class_data['net_access_port_field'] = 0;
                $peripheral_class_data['net_access_login_field'] = 0;
                $peripheral_class_data['net_access_password_field'] = 0;
            }
            $peripheral_class_data['link_computers'] = ($peripheral_class_data['link_computers'] ? 1 : 0);
            $peripheral_class_data['use_snmp'] = ($peripheral_class_data['use_snmp'] ? 1 : 0);

            $peripheral_class->load_from_array ($peripheral_class_data);
            if ($peripheral_class->is_valid_data ())
            {
                $peripheral_class->save_data ();
                $ret = $this->mk_redir ('peripheral_class_edit', array ('id' => $peripheral_class->id));
            }
            else
            {
                save_form_data ($peripheral_class_data, 'peripheral_class');
            }
            $ret = $this->mk_redir ('peripheral_class_edit', array ('id' => $peripheral_class->id));
        }

        return $ret;
    }


    /** Displays the page for confirming the deletion of a peripheral class*/
    function peripheral_class_delete ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'peripheral_class_delete.tpl';

        $peripheral_class = new PeripheralClass ($this->vars['id']);
        if (!$peripheral_class->id) return $this->mk_redir ('manage_peripherals_classes');

        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('peripheral_class_delete_submit', array ('id' => $peripheral_class->id));

        $this->display ($tpl);
    }


    /** Deletes a peripheral class */
    function peripheral_class_delete_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $ret = $this->mk_redir ('manage_peripherals_classes');
        $peripheral_class = new PeripheralClass ($this->vars['id']);

        if ($this->vars['delete'] and $peripheral_class->id)
        {
            $peripheral_class->delete ();
        }

        return $ret;
    }


    /** Displays the list with the customers using a certain class of peripherals */
    function peripheral_class_customers ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        class_load ('Customer');
        $tpl = 'peripheral_class_customers.tpl';

        $peripheral_class = new PeripheralClass ($this->vars['id']);
        if (!$peripheral_class->id) return $this->mk_redir ('manage_peripherals_classes');

        $customers_peripheral = $peripheral_class->get_customers_list ();
        $customers_list = Customer::get_customers_list ();

        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('customers_peripheral', $customers_peripheral);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }

    /** Displays the list with all the peripherals of a certain class */
    function peripheral_class_peripherals ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        class_load ('Customer');
        $tpl = 'peripheral_class_peripherals.tpl';

        $peripheral_class = new PeripheralClass ($this->vars['id']);
        if (!$peripheral_class->id) return $this->mk_redir ('manage_peripherals_classes');

        $peripherals_list = $peripheral_class->get_peripherals_list ();
        $customers_list = Customer::get_customers_list (array('active'=>-1));

        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('peripherals_list', $peripherals_list);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }


    /** Displays the page for defining a peripheral class field */
    function peripheral_field_add ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'peripheral_field_add.tpl';

        $peripheral_class = new PeripheralClass ($this->vars['class_id']);
        $peripheral_class_field = new PeripheralClassField ();
        if (!$peripheral_class->id) return $this->mk_redir ('manage_peripherals_classes');

        $field_data = array ();
        if (!empty_error_msg()) restore_form_data ('peripheral_field', false, $field_data);
        $peripheral_class_field->load_from_array ($field_data);
        $peripheral_class_field->class_id = $peripheral_class->id;

        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('peripheral_class_field', $peripheral_class_field);
        $this->assign ('FIELDS_TYPES', $GLOBALS['PERIPHERALS_FIELDS_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_field_add_submit', array ('class_id' => $peripheral_class->id));

        $this->display ($tpl);
    }


    /** Saves the definition of a peripheral class field */
    function peripheral_field_add_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $peripheral_class = new PeripheralClass ($this->vars['class_id']);
        $ret = $this->mk_redir ('peripheral_class_edit', array ('id' => $peripheral_class->id));

        if ($this->vars['save'] and $peripheral_class->id)
        {
            $field_data = $this->vars['peripheral_class_field'];
            $field_data['in_listings'] = ($field_data['in_listings'] ? 1 : 0);
            $field_data['in_reports'] = ($field_data['in_reports'] ? 1 : 0);
            $peripheral_class_field = new PeripheralClassField ();
            $peripheral_class_field->class_id = $peripheral_class->id;
            $peripheral_class_field->load_from_array ($field_data);

            if ($peripheral_class_field->is_valid_data ())
            {
                $peripheral_class_field->save_data ();
                $ret = $this->mk_redir ('peripheral_field_edit', array ('id' => $peripheral_class_field->id));
            }
            else
            {
                save_form_data ($field_data, 'peripheral_field');
                $ret = $this->mk_redir ('peripheral_field_add', array ('class_id' => $peripheral_class->id));
            }
        }

        return $ret;
    }


    /** Displays the page for editing a peripheral field */
    function peripheral_field_edit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'peripheral_field_edit.tpl';

        $peripheral_class_field = new PeripheralClassField ($this->vars['id']);
        $peripheral_class = new PeripheralClass ($peripheral_class_field->class_id);

        $field_data = array ();
        if (!empty_error_msg()) restore_form_data ('peripheral_field', false, $field_data);
        $peripheral_class_field->load_from_array ($field_data);

        $this->assign ('peripheral_class_field', $peripheral_class_field);
        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('FIELDS_TYPES', $GLOBALS['PERIPHERALS_FIELDS_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_field_edit_submit', array ('id' => $peripheral_class_field->id));

        $this->display ($tpl);
    }


    /** Saves a peripheral class field */
    function peripheral_field_edit_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $peripheral_class_field = new PeripheralClassField ($this->vars['id']);
        $peripheral_class = new PeripheralClass ($peripheral_class_field->class_id);
        $ret = $this->mk_redir ('peripheral_class_edit', array ('id' => $peripheral_class->id));

        if ($this->vars['save'] and $peripheral_class_field->id)
        {
            $field_data = $this->vars['peripheral_class_field'];
            $field_data['in_listings'] = ($field_data['in_listings'] ? 1 : 0);
            $field_data['in_reports'] = ($field_data['in_reports'] ? 1 : 0);

            $peripheral_class_field->load_from_array ($field_data);
            if ($peripheral_class_field->is_valid_data ())
            {
                $peripheral_class_field->save_data ();
            }
            else
            {
                save_form_data ($field_data, 'peripheral_field');
            }
            $ret = $this->mk_redir ('peripheral_field_edit', array ('id' => $peripheral_class_field->id));
        }

        return $ret;
    }


    /** Displays the page for defining the order of fields in a peripheral class */
    function peripheral_field_order ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'peripheral_field_order.tpl';

        $peripheral_class = new PeripheralClass ($this->vars['id']);

        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_field_order_submit', array ('id' => $peripheral_class->id));

        $this->display ($tpl);
    }


    /** Saves the orders of the field */
    function peripheral_field_order_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $peripheral_class = new PeripheralClass ($this->vars['id']);
        $ret = $this->mk_redir ('peripheral_class_edit', array ('id' => $peripheral_class->id));

        if ($this->vars['save'] and $peripheral_class->id)
        {
            $peripheral_class->set_fields_order ($this->vars['fields']);
            $ret = $this->mk_redir ('peripheral_field_order', array ('id' => $peripheral_class->id));
        }

        return $ret;
    }


    /** Displays the page for confirming the deletion of a peripheral class field */
    function peripheral_field_delete ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'peripheral_field_delete.tpl';

        $peripheral_class_field = new PeripheralClassField ($this->vars['id']);
        if (!$peripheral_class_field->id) return $this->mk_redir ('manage_peripherals_classes');
        $peripheral_class = new PeripheralClass ($peripheral_class_field->class_id);

        $this->assign ('peripheral_class', $peripheral_class);
        $this->assign ('peripheral_class_field', $peripheral_class_field);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('peripheral_field_delete_submit', array ('id' => $peripheral_class_field->id));

        $this->display ($tpl);
    }


    /** Deletes a peripheral class field */
    function peripheral_field_delete_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $peripheral_class_field = new PeripheralClassField ($this->vars['id']);
        $ret = $this->mk_redir ('peripheral_class_edit', array ('id' => $peripheral_class_field->class_id));

        if ($this->vars['delete'] and $peripheral_class_field->id)
        {
            $peripheral_class_field->delete ();
        }

        return $ret;
    }
    
    
    /****************************************************************/
    /* Blackouts							*/
    /****************************************************************/

    /** Displays the page for managing blackouts */
    function manage_blackouts ()
    {
        // check_auth (); 		// Authorization is checked lower, in case there was a specific customer requested
        class_load ('ComputerBlackout');
        $tpl = 'manage_blackouts.tpl';

        if (isset($this->vars['customer_id']))
        {
            $_SESSION['manage_blackouts']['filter']['customer_id'] = $this->vars['customer_id'];
        }
        elseif ($this->locked_customer->id and !$this->vars['do_filter'])
        {
            // If 'do_filter' is present in request, the locked customer is ignored
            $_SESSION['manage_blackouts']['filter']['customer_id'] = $this->locked_customer->id;
        }
        $filter = $_SESSION['manage_blackouts']['filter'];

        // Check authorization
        if ($filter['customer_id'] > 0)
        {
            // Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
            // This way he can return to this page, without getting again "Permission Denied".

            unset ($_SESSION['manage_blackouts']['filter']['customer_id']);
            check_auth (array('customer_id' => $filter['customer_id']));
            $_SESSION['manage_blackouts']['filter']['customer_id'] = $filter['customer_id'];
        }
        else check_auth ();

        // Extract the list of Kawacs customers, eventually restricting only to the customers assigned to
        // the current user, if he has restricted customer access.
        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);
        $all_customers_list = Customer::get_customers_list (array('show_ids'=>true, 'active'=>-1));

        // Mark the potential customer for locking
        if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

        $blackouts = ComputerBlackout::get_computers ($filter);
        $computers_list = Computer::get_computers_list ();
        $customers_computers_id = Computer::get_computers_customer_ids ();

        $this->assign ('blackouts', $blackouts);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('all_customers_list', $all_customers_list);
        $this->assign ('customers_computers_id', $customers_computers_id);
        $this->assign ('filter', $filter);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('manage_blackouts_submit');

        $this->display ($tpl);
    }


    /** Saves criteria for the manage blackouts page */
    function manage_blackouts_submit ()
    {
        check_auth ();
        $ret = $this->mk_redir ('manage_blackouts', array ('do_filter' => 1));

        $_SESSION['manage_blackouts']['filter'] = $this->vars['filter'];

        return $ret;
    }


    /** Displays the page for editing the blackouts for a specific customer */
    function blackouts_edit ()
    {
        class_load ('ComputerBlackout');
        $tpl = 'blackouts_edit.tpl';
        $customer = new Customer ($this->vars['customer_id']);

        if (!$customer->id) return $this->mk_redir ('manage_blackouts');
        check_auth (array ('customer_id' => $customer->id));

        $computers_list = Computer::get_computers_list (array('customer_id' => $customer->id, 'append_id' => true));
        ksort ($computers_list);
        $blackouts = ComputerBlackout::get_computers (array('customer_id' => $customer->id, 'load_computers' => true));

        // Make a list of computers that are blacked out
        $computers_blackouts = array ();
        for ($i=0; $i<count($blackouts); $i++) $computers_blackouts[$blackouts[$i]->computer_id] = $i;

        $this->assign ('blackouts', $blackouts);
        $this->assign ('customer', $customer);
        $this->assign ('computers_blackouts', $computers_blackouts);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('default_date', time());
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('blackouts_edit_submit', array ('customer_id' => $customer->id));

        $this->display ($tpl);
    }


    /** Saves the defined blackouts */
    function blackouts_edit_submit ()
    {
        class_load ('ComputerBlackout');
        $customer = new Customer ($this->vars['customer_id']);
        if (!$customer->id) return $this->mk_redir ('manage_blackouts');
        check_auth (array('customer_id' => $customer->id));
        $ret = $this->mk_redir ('manage_blackouts', array ('customer_id' => $customer->id));

        if ($this->vars['save'])
        {
            $blacked_out_computers = $this->vars['blacked_out'];

            if (is_array ($blacked_out_computers))
            {
                foreach ($blacked_out_computers as $computer_id => $is_out)
                {
                    if (!$is_out)
                    {
                        // This computer is should not be blacked out. Check, to make sure
                        $blackout = new ComputerBlackout ($computer_id);
                        if ($blackout->computer_id)
                        {
                            $blackout->delete ();
                        }
                    }
                    else
                    {
                        // This computer is blacked out
                        if ($this->vars['start_date'][$computer_id])
                        {
                            $start_date = js_strtotime ($this->vars['start_date'][$computer_id]);
                        }
                        else $start_date = 0;

                        if ($this->vars['end_date'][$computer_id])
                        {
                            $end_date = js_strtotime ($this->vars['end_date'][$computer_id]);
                        }
                        else $end_date = 0;

                        $blackout = new ComputerBlackout ();
                        $blackout->computer_id = $computer_id;
                        $blackout->start_date = $start_date;
                        $blackout->end_date = $end_date;
                        $blackout->comments = $this->vars['comments'][$computer_id];
                        $blackout->save_data ();
                    }
                }
            }

            $ret = $this->mk_redir ('blackouts_edit', array ('customer_id' => $customer->id));
        }

        return $ret;
    }


    /** Blacks out a specific computer */
    function blackout_computer ()
    {
        class_load ('ComputerBlackout');
        check_auth ();

        $computer = new Computer ($this->vars['id']);
        $ret = $this->mk_redir ('computer_view', array ('id' => $computer->id));
        if ($computer->id)
        {
            // Check to see if the computer is blacked out
            $blackout = new ComputerBlackout ($computer->id);

            if (!$blackout->computer_id)
            {
                // The computer is not blacked out already
                $blackout->computer_id = $computer->id;
                $blackout->start_date = time ();
                $blackout->save_data ();
            }
        }

        return $ret;
    }


    /** Removes the blackout for a specific computer */
    function blackout_computer_remove ()
    {
        class_load ('ComputerBlackout');
        check_auth ();

        $computer = new Computer ($this->vars['id']);
        $ret = $this->mk_redir ('computer_view', array ('id' => $computer->id));
        if ($computer->id)
        {
            // Check to see if the computer is blacked out
            $blackout = new ComputerBlackout ($computer->id);
            if ($blackout->computer_id)
            {
                $blackout->delete ();
            }
        }

        return $ret;
    }


    /** Removes a blackout */
    function blackouts_remove ()
    {
        class_load ('ComputerBlackout');
        check_auth ();
        $blackout = new ComputerBlackout ($this->vars['computer_id'], true);
        $ret = $this->mk_redir ('manage_blackouts', array ('customer_id' => $blackout->computer->customer_id));

        if ($blackout->computer_id)
        {
            $blackout->delete ();
        }
        return $ret;
    }


    /****************************************************************/
    /* Reports							*/
    /****************************************************************/

    /** Displays the page with the evolution of free disk space */
    function computer_report_partitions ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        $tpl = 'computer_report_partitions.tpl';

        $filter = $_SESSION['filter']['computer_report_partitions'];
        if (!isset($filter['unit'])) $filter['unit'] = 1024*1024;
        if (!isset($filter['interval'])) $filter['interval'] = 'day';
        if (!isset($filter['sort_dir'])) $filter['sort_dir'] = 'DESC';
        if (!isset($filter['month_start'])) $filter['month_start'] = date ('Y_m');
        if (!isset($filter['month_end'])) $filter['month_end'] = date ('Y_m');

        $computer = new Computer ($this->vars['id']);
        if (!$computer->id) return $this->mk_redir ('manage_computers');

        $units = array (1024 => 'KB', (1024*1024) => 'MB', (1024*1024*1024) => 'GB');
        $intervals = array ('day'=>'Day', 'hour'=>'Hour');

        $filter['computer_id'] = $computer->id;
        $history = Computer::get_partitions_history($filter);

        $partitions = array_keys ($history);
        if (count($partitions)>0)
        {
            $dates = array_keys($history[$partitions[0]]->log);

            // Convert the sizes
            foreach ($partitions as $partition)
            {
                $history[$partition]->size = round($history[$partition]->size/$filter['unit'], 2);
                foreach ($history[$partition]->log as $time=>$size)
                {
                    $history[$partition]->log[$time] = round($size/$filter['unit'], 2);
                }
            }
        }
        $months = $computer->get_log_months(1013);

        $this->assign ('computer', $computer);
        $this->assign ('filter', $filter);
        $this->assign ('history', $history);
        $this->assign ('dates', $dates);
        $this->assign ('months', $months);
        $this->assign ('partitions', $partitions);
        $this->assign ('units', $units);
        $this->assign ('intervals', $intervals);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('computer_report_partitions_submit', array ('id' => $computer->id));

        $this->display ($tpl);
    }


    /** Save the filtering criteria for the free disk space page */
    function computer_report_partitions_submit ()
    {
        $ret = $this->mk_redir ('computer_report_partitions', array ('id' => $this->vars['id']));
        $_SESSION['filter']['computer_report_partitions'] = $this->vars['filter'];
        return $ret;
    }


    /** Displays the page with the missed backups report */
    function computer_report_backups ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        $tpl = 'computer_report_backups.tpl';

        $filter = $_SESSION['filter']['computer_report_backups'];
        if (!isset($filter['sort_dir'])) $filter['sort_dir'] = 'DESC';
        if (!isset($filter['month_start'])) $filter['month_start'] = date ('Y_m');
        if (!isset($filter['month_end'])) $filter['month_end'] = date ('Y_m');

        $computer = new Computer ($this->vars['id']);
        $history = $computer->get_backups_history ($filter);

        $months = $computer->get_log_months (1044);

        $this->assign ('computer', $computer);
        $this->assign ('filter', $filter);
        $this->assign ('history', $history);
        $this->assign ('months', $months);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('computer_report_backups_submit', array ('id' => $computer->id));

        $this->display ($tpl);
    }

    /** Saves filtering criteria for the missed backups report */
    function computer_report_backups_submit ()
    {
        $ret = $this->mk_redir ('computer_report_backups', array ('id' => $this->vars['id']));
        $_SESSION['filter']['computer_report_backups'] = $this->vars['filter'];
        return $ret;
    }

    /** Displays the page with the backups size report */
    function computer_report_backup_sizes ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        $tpl = 'computer_report_backup_sizes.tpl';

        $filter = $_SESSION['filter']['computer_report_backup_sizes'];
        if (!isset($filter['sort_dir'])) $filter['sort_dir'] = 'DESC';
        if (!isset($filter['month_start'])) $filter['month_start'] = date ('Y_m');
        if (!isset($filter['month_end'])) $filter['month_end'] = date ('Y_m');

        $computer = new Computer ($this->vars['id']);

        $history = $computer->get_backups_sizes ($filter);
        $months = $computer->get_log_months (1044);

        $this->assign ('computer', $computer);
        $this->assign ('filter', $filter);
        $this->assign ('history', $history);
        $this->assign ('months', $months);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('computer_report_backup_sizes_submit', array ('id' => $computer->id));

        $this->display ($tpl);
    }

    /** Saves filtering criteria for the backups size report */
    function computer_report_backup_sizes_submit ()
    {
        $ret = $this->mk_redir ('computer_report_backup_sizes', array ('id' => $this->vars['id']));
        $_SESSION['filter']['computer_report_backup_sizes'] = $this->vars['filter'];
        return $ret;
    }


    /** Displays the page with the AV updates age */
    function computer_report_av ()
    {
        check_auth (array('computer_id' => $this->vars['id']));
        class_load ('Computer');
        $tpl = 'computer_report_av.tpl';

        $filter = $_SESSION['filter']['computer_report_av'];
        if (!isset($filter['sort_dir'])) $filter['sort_dir'] = 'DESC';
        if (!isset($filter['month_start'])) $filter['month_start'] = date ('Y_m');
        if (!isset($filter['month_end'])) $filter['month_end'] = date ('Y_m');

        $computer = new Computer ($this->vars['id']);
        $history = $computer->get_av_history ($filter);

        $months = $computer->get_log_months (1025);

        $this->assign ('computer', $computer);
        $this->assign ('filter', $filter);
        $this->assign ('history', $history);
        $this->assign ('months', $months);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('computer_report_av_submit', array ('id' => $computer->id));

        $x = Computer::get_av_status (array('customer_id' => $computer->customer_id));

        $this->display ($tpl);
    }


    /** Saves filtering criteria for the AV updates age report */
    function computer_report_av_submit ()
    {
        $ret = $this->mk_redir ('computer_report_av', array ('id' => $this->vars['id']));
        $_SESSION['filter']['computer_report_av'] = $this->vars['filter'];
        return $ret;
    }

    /****************************************************************/
    /* Monitor Profiles management					*/
    /****************************************************************/

    /** Shows the page for showing the currently defined profiles */
    function manage_profiles ()
    {
        check_auth ();
        class_load ('Computer');
        $tpl = 'manage_profiles.tpl';

        $profiles = MonitorProfile::get_profiles();
        $computers_count = MonitorProfile::get_computers_count();

        $default_profile = MonitorProfile::get_default_profile ();
        $this->assign ('profiles', $profiles);
        $this->assign ('computers_count', $computers_count);
        $this->assign ('default_profile', $default_profile);

        $this->display ($tpl);
    }

    /** Shows the page for defining a new monitor profile */
    function monitor_profile_add ()
    {
        check_auth ();
        $tpl = 'monitor_profile_add.tpl';

        $this->set_form_redir ('monitor_profile_add_submit');
        $this->display ($tpl);
    }


    /** Processes and saves data for a new monitoring profile */
    function monitor_profile_add_submit ()
    {
        check_auth ();

        if ($this->vars['save'])
        {
            $profile_data = $this->vars['profile'];

            $profile = new MonitorProfile();
            $profile->load_from_array($profile_data);
            $profile->save_data();
            $ret = $this->mk_redir('monitor_profile_edit', array('id' => $profile->id));
        }
        else
        {
            $ret = $this->mk_redir('manage_profiles');
        }
        return $ret;
    }


    /** Shows the page for editing a monitor profile */
    function monitor_profile_edit ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl =  'monitor_profile_edit.tpl';

        $profile = new MonitorProfile ($this->vars['id']);
        if (!$profile->id) return $this->mk_redir('manage_profiles');

        $profile->load_events_settings ();
        $available_categories = MonitorItem::get_categories_items();
        $sources = EventLogRequested::get_events_sources_list_extended ();

        $this->assign ('profile', $profile);
        $this->assign ('available_categories', $available_categories);
        $this->assign ('sources', $sources);

        $this->assign ('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
        $this->assign ('MONITOR_LOG', $GLOBALS['MONITOR_LOG']);
        $this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
        $this->assign ('ALERT_NAMES', $GLOBALS['ALERT_NAMES']);
        $this->assign ('CRIT_NAMES', $GLOBALS['CRIT_NAMES']);
        $this->assign ('DAY_NAMES', $GLOBALS ['DAY_NAMES']);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->assign ('error_msg', error_msg());

        $this->set_form_redir ('monitor_profile_edit_submit', array('id' => $profile->id));


        $this->display ($tpl);
    }


    /** Processes and saves the data for the monitor profile */
    function monitor_profile_edit_submit ()
    {
        check_auth ();
        $ret = $this->mk_redir('manage_profiles');

        $profile = new MonitorProfile ($this->vars['id']);
        if ($profile->id and $this->vars['save'])
        {
            // Save the data for a profile
            $profile_data = $this->vars['profile'];
            $profile->load_from_array ($this->vars['profile'], $this->vars['items']);

            if ($profile->is_valid_data())
            {
                $profile->save_data();
            }

            $ret = $this->mk_redir ('monitor_profile_edit', array ('id' => $profile->id));
        }
        elseif ($profile->id and $this->vars['copy'])
        {
            // Copy the existing profile into a new profile
            $ret = $this->mk_redir ('monitor_profile_copy', array ('old_id' => $profile->id));
        }

        return $ret;
    }


    /** Shows the page for copying the monitor profile into a new one */
    function monitor_profile_copy ()
    {
        check_auth ();
        $tpl = 'monitor_profile_copy.tpl';
        $profile = new MonitorProfile ($this->vars['old_id']);

        if (!$profile->id) return ($this->mk_redir('manage_profiles'));

        $this->set_form_redir ('monitor_profile_copy_submit', array('old_id'=>$profile->id));
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Processes the request to copy an exiting pofile into a new one */
    function monitor_profile_copy_submit ()
    {
        check_auth ();
        if ($this->vars['cancel'])
        {
            $ret = $this->mk_redir ('monitor_profile_edit', array ('id'=>$this->vars['old_id']));
        }
        else
        {
            $ret = $this->mk_redir ('manage_profiles');

            if ($this->vars['copy'])
            {
                if (!$this->vars['new_name'])
                {
                    error_msg ($this->get_string('NEED_PROFILE_NAME'));
                    $ret = $this->mk_redir ('monitor_profile_copy', array('old_id'=>$this->vars['old_id']));
                }
                else
                {
                    $old_profile = new MonitorProfile ($this->vars['old_id']);
                    if ($old_profile->id)
                    {
                        $new_profile = $old_profile->copy_to ($this->vars['new_name']);
                        $ret = $this->mk_redir ('monitor_profile_edit', array ('id' => $new_profile->id));
                    }
                }
            }
        }

        return $ret;
    }


    /** Shows the page for selecting the default profile */
    function monitor_profile_default ()
    {
        check_auth ();
        $tpl = 'monitor_profile_default.tpl';
        $this->set_form_redir ('monitor_profile_default_submit');

        $default_profile = MonitorProfile::get_default_profile ();
        $profiles = MonitorProfile::get_profiles();
        $this->assign('default_profile', $default_profile);

        $this->assign('profiles', $profiles);
        $this->assign('error_msg', error_msg());

        $this->display ($tpl);

    }


    /** Saves the default monitoring profile */
    function monitor_profile_default_submit ()
    {
        check_auth ();

        $id = $this->vars['default_profile'];
        $profile = new MonitorProfile ($id);
        $ret = $this->mk_redir ('manage_profiles');

        if ($this->vars['save'])
        {
            if ($profile->id)
            {
                $profile->set_as_default ();
            }
            else
            {
                error_msg ($this->get_string('NEED_DEFAULT_PROFILE'));
                $ret = $this->mk_redir('monitor_profile_default');
            }
        }

        return $ret;
    }


    /** Displays the page for editing the alerts assigned to this profile */
    function monitor_profile_alerts_edit ()
    {
        check_auth ();
        $tpl = 'monitor_profile_alerts_edit.tpl';

        $profile = new MonitorProfile ($this->vars['id']);
        if (!$profile->id) return $this->mk_redir ('manage_profiles');
        $alerts = Alert::get_alerts (array('computers_only' => true));

        // Compose an array with the IDs of the already assigned alerts
        $assigned_alerts_ids = array ();
        for ($i=0; $i<count($profile->alerts); $i++) $assigned_alerts_ids[] = $profile->alerts[$i]->id;

        $params = $this->set_carry_fields (array('id'));

        $this->assign ('profile', $profile);
        $this->assign ('alerts', $alerts);
        $this->assign ('assigned_alerts_ids', $assigned_alerts_ids);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('monitor_profile_alerts_edit_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the list of alerts assigned to a profile */
    function monitor_profile_alerts_edit_submit ()
    {
        check_auth ();
        $profile = new MonitorProfile ($this->vars['id']);

        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('monitor_profile_edit', $params);

        if ($this->vars['save'] and $profile->id)
        {
            $alerts = ($this->vars['assigned_alerts'] ? $this->vars['assigned_alerts'] : array ());
            $profile->set_alerts ($alerts);
            $ret = $this->mk_redir ('monitor_profile_alerts_edit', $params);
        }

        return $ret;
    }


    /** Displays the page for editing the computer events which should be reported by computers with this profile */
    function monitor_profile_events_edit ()
    {
        check_auth ();
        $tpl = 'monitor_profile_events_edit.tpl';
        $profile = new MonitorProfile ($this->vars['id']);
        if (!$profile->id) return $this->mkRedir ('manage_profiles');

        $profile->load_events_settings ();
        $sources = EventLogRequested::get_events_sources_list_extended ();

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('profile', $profile);
        $this->assign ('sources', $sources);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('monitor_profile_events_edit_submit');

        $this->display ($tpl);
    }


    /** Saves the settings for events logs reporting for a profile */
    function monitor_profile_events_edit_submit ()
    {
        check_auth ();
        $profile = new MonitorProfile ($this->vars['id']);
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('monitor_profile_edit', $params);

        if ($this->vars['save'] and $profile->id)
        {
            $data = $this->vars['default_report'];
            $profile->set_default_events_reporting ($data);
            $ret = $this->mk_redir ('monitor_profile_events_edit', $params);
        }

        return $ret;
    }


    /** Displays the page for defining additional events sources for the profile */
    function monitor_profile_events_src_add ()
    {
        check_auth ();
        class_load ('ComputerReporting');
        $tpl = 'monitor_profile_events_src_add.tpl';
        $profile = new MonitorProfile ($this->vars['id']);
        if (!$profile->id) return $this->mk_redir ('manage_profiles');

        $profile->load_events_settings ();
        $sources = EventLogRequested::get_events_sources_list_extended ();

        $src = new EventLogRequested ();
        if (!empty_error_msg()) $src->load_from_array(restore_form_data ('monitor_profile_events_src_add', false, $data));

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('profile', $profile);
        $this->assign ('src', $src);
        $this->assign ('sources', $sources);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('monitor_profile_events_src_add_submit', $params);

        $this->display ($tpl);
    }

    /** Adds the new events reporting source for the profile */
    function monitor_profile_events_src_add_submit ()
    {
        check_auth ();
        class_load ('ComputerReporting');
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('monitor_profile_events_edit', $params);
        $profile = new MonitorProfile ($this->vars['id']);

        if ($this->vars['save'] and $profile->id)
        {
            $sources = $this->vars['sources'];
            $data = $this->vars['src'];
            if ($data['category_id'] and isset($sources[$data['category_id']])) $data['source_id'] = $sources[$data['category_id']];
            $data['category_id'] = (isset($data['category_id']) ? $data['category_id'] : EVENTLOG_NO_REPORT);
            if (!isset($data['types'])) $data['types'] = array ();

            $src = new EventLogRequested ();
            $src->profile_id = $profile->id;
            $src->load_from_array ($data);

            if ($src->is_valid_data ())
            {
                $src->save_data ();
                $params['src_id'] = $src->id;
                $ret = $this->mk_redir ('monitor_profile_events_src_edit', $params);
            }
            else
            {
                save_form_data ($data, 'monitor_profile_events_src_add');
                $ret = $this->mk_redir ('monitor_profile_events_src_add', $params);
            }
        }

        return $ret;
    }


    /** Displays the page for defining additional events sources for the profile */
    function monitor_profile_events_src_edit ()
    {
        check_auth ();
        class_load ('ComputerReporting');
        $tpl = 'monitor_profile_events_src_edit.tpl';
        $profile = new MonitorProfile ($this->vars['id']);
        $src = new EventLogRequested ($this->vars['src_id']);
        if (!$profile->id or !$src->id) return $this->mk_redir ('manage_profiles');

        $profile->load_events_settings ();
        $sources = EventLogRequested::get_events_sources_list_extended ();
        if (!empty_error_msg()) $src->load_from_array(restore_form_data ('monitor_profile_events_src_edit', false, $data));

        $params = $this->set_carry_fields (array('id', 'src_id'));
        $this->assign ('profile', $profile);
        $this->assign ('src', $src);
        $this->assign ('sources', $sources);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('monitor_profile_events_src_edit_submit', $params);

        $this->display ($tpl);
    }

    /** Saves the events reporting source for the profile */
    function monitor_profile_events_src_edit_submit ()
    {
        check_auth ();
        class_load ('ComputerReporting');
        $params = $this->set_carry_fields (array('id', 'src_id'));
        $profile = new MonitorProfile ($this->vars['id']);
        $src = new EventLogRequested ($this->vars['src_id']);
        $ret = $this->mk_redir ('monitor_profile_events_edit', array('id'=>$profile->id));

        if ($this->vars['save'] and $profile->id and $src->id)
        {
            $sources = $this->vars['sources'];
            $data = $this->vars['src'];
            if (!isset($data['types'])) $data['types'] = array ();
            $src->load_from_array ($data);

            if ($src->is_valid_data ()) $src->save_data ();
            else save_form_data ($data, 'monitor_profile_events_src_edit');

            $ret = $this->mk_redir ('monitor_profile_events_src_edit', $params);
        }

        return $ret;
    }


    /** Displays the page for defining additional events sources for the profile */
    function monitor_profile_events_src_delete ()
    {
        check_auth ();
        class_load ('ComputerReporting');
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('monitor_profile_events_edit', $params);
        $src = new EventLogRequested ($this->vars['src_id']);

        if ($src->id) $src->delete ();

        return $ret;
    }

    /** Displays the page showing the computers which use a certain profile */
    function monitor_profile_computers ()
    {
        check_auth ();
        class_load ('Computer');
        class_load ('Customer');
        $tpl = 'monitor_profile_computers.tpl';

        $profile = new MonitorProfile ($this->vars['id']);
        if (!$profile->id) return $this->mk_redir ('manage_profiles');

        $computers_list = Computer::get_computers_list (array('profile_id' => $profile->id, 'order_by' => 'customer'));
        $customers_list = Customer::get_customers_list ();
        $computers_customer_ids = Computer::get_computers_customer_ids (array('profile_id' => $profile->id));

        $this->assign ('profile', $profile);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('computers_customer_ids', $computers_customer_ids);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }


    /** Performs the deletion of a monitor profile */
    function monitor_profile_delete ()
    {
        check_auth ();
        class_load ('MonitorProfile');

        $profile = new MonitorProfile ($this->vars['id']);

        if ($profile->id and $profile->can_delete ())
        {
                $profile->delete ();
        }

        return $this->mk_redir ('manage_profiles');

    }


    /****************************************************************/
    /* Peripherals Monitor Profiles Management			*/
    /****************************************************************/

    /** Shows the page for manage peripherals monitor profiles */
    function manage_profiles_periph ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $tpl = 'manage_profiles_periph.tpl';

        $profiles = MonitorProfilePeriph::get_profiles ();
        $peripherals_count = MonitorProfilePeriph::get_peripherals_count ();

        $this->assign ('profiles', $profiles);
        $this->assign ('peripherals_count', $peripherals_count);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }

    /** Shows the page for defining a new peripherals monitor profile */
    function monitor_profile_periph_add ()
    {
        check_auth ();
        $tpl = 'monitor_profile_periph_add.tpl';

        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('monitor_profile_periph_add_submit');
        $this->display ($tpl);
    }

    /** Processes and saves data for a new peripherals monitoring profile */
    function monitor_profile_periph_add_submit ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $ret = $this->mk_redir ('manage_profiles_periph');

        if ($this->vars['save'])
        {
            $profile_data = $this->vars['profile'];

            $profile = new MonitorProfilePeriph();
            $profile->load_from_array($profile_data);
            if ($profile->is_valid_data ())
            {
                $profile->save_data ();
                $ret = $this->mk_redir ('monitor_profile_periph_edit', array('id'=>$profile->id));
            }
            else $ret = $this->mk_redir ('monitor_profile_periph_add');
        }

        return $ret;
    }

    /** Displays the page for editing a peripherals monitor profile */
    function monitor_profile_periph_edit ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $tpl = 'monitor_profile_periph_edit.tpl';

        $profile = new MonitorProfilePeriph ($this->vars['id']);
        if (!$profile->id) return $this->mk_redir ('manage_profiles_periph');

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('profile', $profile);
        $this->assign ('error_msg', error_msg ());
        $this->assign ('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
        $this->assign ('MONITOR_LOG', $GLOBALS['MONITOR_LOG']);
        $this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
        $this->assign ('ALERT_NAMES', $GLOBALS['ALERT_NAMES']);
        $this->assign ('CRIT_NAMES', $GLOBALS['CRIT_NAMES']);
        $this->assign ('DAY_NAMES', $GLOBALS ['DAY_NAMES']);
        $this->assign ('EVENTS_CATS', $GLOBALS['EVENTS_CATS']);
        $this->assign ('EVENTLOG_TYPES', $GLOBALS['EVENTLOG_TYPES']);
        $this->set_form_redir ('monitor_profile_periph_edit_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the changes to the peripheral monitor profile */
    function monitor_profile_periph_edit_submit ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $profile = new MonitorProfilePeriph($this->vars['id']);
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('manage_profiles_periph');

        if ($this->vars['save'] and $profile->id)
        {
            $data = $this->vars['profile'];
            $data_items = $this->vars['items'];
            $profile->load_from_array ($data, $data_items);

            if ($profile->is_valid_data ())
            {
                $profile->save_data ();
            }

            $ret = $this->mk_redir ('monitor_profile_periph_edit', $params);
        }
        elseif ($profile->id and $this->vars['copy'])
        {
            // Copy the existing profile into a new profile
            $ret = $this->mk_redir ('monitor_profile_periph_copy', array ('old_id' => $profile->id));
        }

        return $ret;
    }

    /** Displays the page for editing the alerts assigned to this peripherals profile */
    function monitor_profile_periph_alerts_edit ()
    {
        check_auth ();
        $tpl = 'monitor_profile_alerts_edit.tpl';

        $profile = new MonitorProfilePeriph ($this->vars['id']);
        if (!$profile->id) return $this->mk_redir ('manage_profiles_periph');
        $alerts = Alert::get_alerts (array('peripherals_only'=>true));

        // Compose an array with the IDs of the already assigned alerts
        $assigned_alerts_ids = array ();
        for ($i=0; $i<count($profile->alerts); $i++) $assigned_alerts_ids[] = $profile->alerts[$i]->id;

        $params = $this->set_carry_fields (array('id'));

        $this->assign ('profile', $profile);
        $this->assign ('alerts', $alerts);
        $this->assign ('assigned_alerts_ids', $assigned_alerts_ids);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('monitor_profile_periph_alerts_edit_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the list of alerts assigned to a peripherals profile */
    function monitor_profile_periph_alerts_edit_submit ()
    {
        check_auth ();
        $profile = new MonitorProfilePeriph ($this->vars['id']);

        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('monitor_profile_periph_edit', $params);

        if ($this->vars['save'] and $profile->id)
        {
            $alerts = ($this->vars['assigned_alerts'] ? $this->vars['assigned_alerts'] : array ());
            $profile->set_alerts ($alerts);
            $ret = $this->mk_redir ('monitor_profile_periph_alerts_edit', $params);
        }

        return $ret;
    }


    /** Shows the page for copying the peripherals monitor profile into a new one */
    function monitor_profile_periph_copy ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $tpl = 'monitor_profile_periph_copy.html';
        $profile = new MonitorProfilePeriph ($this->vars['old_id']);

        if (!$profile->id) return ($this->mk_redir('manage_profiles_periph'));

        $this->set_form_redir ('monitor_profile_periph_copy_submit', array('old_id'=>$profile->id));
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Processes the request to copy an exiting peripherals pofile into a new one */
    function monitor_profile_periph_copy_submit ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $old_profile = new MonitorProfilePeriph ($this->vars['old_id']);
        $ret = $this->mk_redir ('monitor_profile_periph_edit', array ('id' => $old_profile->id));

        if ($this->vars['copy'] and $old_profile->id)
        {
            if (!$this->vars['new_name'])
            {
                error_msg ($this->get_string('NEED_PROFILE_NAME'));
                $ret = $this->mk_redir ('monitor_profile_periph_copy', array('old_id'=>$this->vars['old_id']));
            }
            else
            {
                $new_profile = $old_profile->copy_to ($this->vars['new_name']);
                $ret = $this->mk_redir ('monitor_profile_periph_edit', array ('id' => $new_profile->id));
            }
        }

        return $ret;
    }

    /** Displays the page with the peripherals which are using a certain monitor profile */
    function monitor_profile_peripherals ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        class_load ('Peripheral');
        class_load ('AD_Printer');
        $tpl = 'monitor_profile_peripherals.tpl';

        $profile = new MonitorProfilePeriph ($this->vars['id']);
        if (!$profile->id) return $this->mk_redir ('manage_profiles_periph');

        $peripherals_list = Peripheral::get_peripherals_list (array('profile_id' => $profile->id, 'order_by' => 'customer'));
        $ad_printers_list = AD_Printer::get_ad_printers_list_profile ($profile->id);

        $customers_list = Customer::get_customers_list ();
        $peripherals_customer_ids = Peripheral::get_peripherals_customer_ids (array('profile_id' => $profile->id));
        $ad_printers_customer_ids = AD_Printer::get_ad_printers_customer_ids ();

        $this->assign ('profile', $profile);
        $this->assign ('peripherals_list', $peripherals_list);
        $this->assign ('ad_printers_list', $ad_printers_list);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('peripherals_customer_ids', $peripherals_customer_ids);
        $this->assign ('ad_printers_customer_ids', $ad_printers_customer_ids);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }

    /** Displays the page for selecting the monitoring items to be used by a peripherals profile */
    function monitor_profile_periph_items ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $tpl = 'monitor_profile_periph_items.tpl';

        $profile = new MonitorProfilePeriph ($this->vars['id']);
        if (!$profile->id) return $this->mk_redir ('manage_profiles_periph');

        $items = MonitorItem::get_peripherals_monitor_items ();

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('profile', $profile);
        $this->assign ('items', $items);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('monitor_profile_periph_items_submit', $params);

        $this->display ($tpl);
    }

    function monitor_profile_periph_items_submit ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $profile = new MonitorProfilePeriph ($this->vars['id']);
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('monitor_profile_periph_edit', $params);

        if ($this->vars['save'] and $profile->id)
        {
            if (!is_array($this->vars['profile_items'])) $this->vars['profile_items'] = array ();
            $profile->set_items_ids ($this->vars['profile_items']);
            $profile->save_data ();
        }

        return $ret;
    }

    /** Deletes a peripherals monitor profile */
    function monitor_profile_periph_delete ()
    {
        check_auth ();
        class_load ('MonitorProfilePeriph');
        $profile = new MonitorProfilePeriph ($this->vars['id']);

        if ($profile->can_delete ()) $profile->delete ();

        return $this->mk_redir ('manage_profiles_periph');
    }


    /** Displays the page for associating the monitoring items from a profile with the fields from a peripheral class */
    function peripheral_class_profile ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $tpl = 'peripheral_class_profile.tpl';

        $profile = new MonitorProfilePeriph ($this->vars['profile_id']);
        $class = new PeripheralClass ($this->vars['class_id']);
        if (!$profile->id or !$class->id) return $this->mk_redir ('manage_profiles_periph');

        // Prepare the list of fields from all preofile's items that can be associated to profile class fields
        $profile_fields = array ();
        $profile_fields_noselect = array (); // These will contain the IDs that can't be selected, e.g. ID of struct items
        foreach ($profile->items as $item)
        {
            $profile_fields[$item->itemdef->id] = $item->itemdef->id.': '.$item->itemdef->name.' ['.$item->itemdef->snmp_oid.']';
            if ($item->itemdef->type == MONITOR_TYPE_STRUCT)
            {
                $profile_fields_noselect[] = $item->itemdef->id;
                foreach ($item->itemdef->struct_fields as $field)
                {
                    $profile_fields[$item->itemdef->id.'_'.$field->id] = ' - '.$field->name.' ['.$field->snmp_oid.']';
                }
            }
        }

        $params = $this->set_carry_fields (array('class_id', 'profile_id', 'returl'));
        $this->assign ('profile', $profile);
        $this->assign ('class', $class);
        $this->assign ('profile_fields', $profile_fields);
        $this->assign ('profile_fields_noselect', $profile_fields_noselect);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('peripheral_class_profile_submit', $params);

        $this->display ($tpl);
    }

    /** Saves the associations between peripherals classes and profiles monitor items fields */
    function peripheral_class_profile_submit ()
    {
        check_auth ();
        class_load ('PeripheralClass');
        $profile = new MonitorProfilePeriph ($this->vars['profile_id']);
        $class = new PeripheralClass ($this->vars['class_id']);
        $params = $this->set_carry_fields (array('profile_id', 'class_id', 'returl'));
        $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('peripheral_class_edit', array('id' => $class->id)));

        if ($this->vars['save'] and $profile->id and $class->id)
        {
            if (!is_array($this->vars['item_ids'])) $item_ids = array ();
            else $item_ids = $this->vars['item_ids'];
            foreach ($item_ids as $field_id => $item_id) if (!$item_id) unset($item_ids[$field_id]);

            $class->set_profile_items_fields ($profile->id, $item_ids);

            $ret = $this->mk_redir ('peripheral_class_profile', $params);
        }

        return $ret;
    }


    /** Removes all associations between a peripherals class and a monitoring profile */
    function peripheral_class_profile_remove ()
    {
        check_auth ();
        class_load ('PeripheralClass');

        $class = new PeripheralClass ($this->vars['class_id']);
        $profile = new MonitorProfilePeriph ($this->vars['profile_id']);
        $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('peripheral_class_edit', array('id' => $class->id)));

        if ($class->id and $profile->id)
        {
            $class->remove_profile_references ($profile->id);
        }

        return $ret;
    }


    /****************************************************************/
    /* Monitor Items management					*/
    /****************************************************************/


    /** Shows the page with the currently defined monitoring items for computers */
    function manage_monitor_items ()
    {
        check_auth ();
        $tpl = 'manage_monitor_items.tpl';

        $items = MonitorItem::get_monitor_items_display();
        $this->assign ('items', $items);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }

    /** Shows the page with the currently defined monitoring items for peripherals */
    function manage_monitor_items_peripherals ()
    {
        check_auth ();
        $tpl = 'manage_monitor_items_peripherals.tpl';

        $items = MonitorItem::get_peripherals_monitor_items_display();
        $this->assign ('items', $items);
        $this->assign ('error_msg', error_msg ());

        $this->display ($tpl);
    }

    /** Shows the page for adding a new monitoring item */
    function monitor_item_add ()
    {
        check_auth();
        class_load ('MibOid');

        $data = array ();
        if (!empty_error_msg()) $data = restore_form_data ('monitor_item_add', false, $data);
        $item = new MonitorItem ();
        $item->load_from_array ($data);

        if (!$this->vars['parent_id'])
        {
            // Adding a main monitor item
            $params = $this->set_carry_fields (array('peripheral_item'));
            $tpl = 'monitor_item_add.tpl';
            $this->assign('MONITOR_MULTI', $GLOBALS['MONITOR_MULTI']);
            $this->assign('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
            $this->assign('MONITOR_LOG', $GLOBALS['MONITOR_LOG']);
            $this->set_form_redir('monitor_item_submit', $params);
        }
        else
        {
            // Adding a field for a monitor item of type 'struct'
            $tpl = 'monitor_item_field_add.tpl';
            $parent_item = new MonitorItem($this->vars['parent_id']);
            $parent_item->set_display_fields();
            $this->assign('parent_item', $parent_item);
            $this->set_form_redir('monitor_item_submit', array('item[parent_id]' => $this->vars['parent_id']));

            if ($parent_item->is_snmp)
            {
                $item->is_snmp = true;
                if (!$item->snmp_oid) $item->snmp_oid = $parent_item->snmp_oid;
                if ($item->snmp_oid_id) $oid = new MibOid ($item->snmp_oid_id);
                elseif ($parent_item->snmp_oid_id)
                {
                    $oid = new MibOid ($parent_item->snmp_oid_id);
                    $item->snmp_oid_id = $parent_item->snmp_oid_id;
                }
            }
        }

        $this->assign ('item', $item);
        $this->assign ('oid', $oid);
        $this->assign ('MONITOR_TYPES', $GLOBALS['MONITOR_TYPES']);
        $this->assign ('CRIT_TYPES_NAMES', $GLOBALS['CRIT_TYPES_NAMES']);
        $this->assign ('AVAILABLE_ITEMS_LISTS_NAMES', $GLOBALS['AVAILABLE_ITEMS_LISTS_NAMES']);
        $this->assign ('AVAILABLE_ITEMS_LISTS', $GLOBALS['AVAILABLE_ITEMS_LISTS']);
        $this->assign ('error_msg', error_msg());

        $this->display($tpl);
    }


    /** Shows the page for editing a monitoring item */
    function monitor_item_edit ()
    {
        check_auth();
        class_load ('MibOid');

        $id = $this->vars['id'];
        $item = new MonitorItem($id);
        if (!$item->id) return ($this->mk_redir('manage_monitor_items'));

        if (!$item->parent_id)
        {
            // Editing a main monitoring item
            $tpl = 'monitor_item_edit.tpl';
            $this->assign('MONITOR_MULTI', $GLOBALS['MONITOR_MULTI']);
            $this->assign('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
            $this->assign('MONITOR_LOG', $GLOBALS['MONITOR_LOG']);
            $this->set_form_redir('monitor_item_submit', array('item[id]' => $item->id));

        }
        else
        {
            // Editing a field for a monitor item of type 'struct'
            $tpl = 'monitor_item_field_edit.tpl';
            $parent_item = new MonitorItem($item->parent_id);
            $parent_item->set_display_fields();
            $this->assign('parent_item', $parent_item);
            $this->set_form_redir('monitor_item_submit', array('item[id]' => $item->id, 'item[parent_id]' => $item->parent_id));
        }

        if ($item->snmp_oid_id) $this->assign ('oid', new MibOid ($item->snmp_oid_id));

        $item->set_display_fields();
        $this->assign ('MONITOR_TYPES', $GLOBALS['MONITOR_TYPES']);
        $this->assign ('CRIT_TYPES_NAMES', $GLOBALS['CRIT_TYPES_NAMES']);
        $this->assign ('AVAILABLE_ITEMS_LISTS_NAMES', $GLOBALS['AVAILABLE_ITEMS_LISTS_NAMES']);
        $this->assign ('AVAILABLE_ITEMS_LISTS', $GLOBALS['AVAILABLE_ITEMS_LISTS']);
        $this->assign ('item', $item);
        $this->assign ('error_msg', error_msg());

        $this->display($tpl);
    }


    /** Processes and saves the data about a new or existing monitoring item */
    function monitor_item_submit ()
    {
        check_auth();
        $ret = $this->mk_redir('manage_monitor_items');
        $params = $this->set_carry_fields (array('id', 'parent_id', 'peripheral_item'));

        if ($this->vars['save'])
        {
            if ($this->vars['id']) $item = new MonitorItem($this->vars['id']);
            else $item = new MonitorItem ();

            $data_ok = true;
            $this->vars['item']['date_show_hour'] = ($this->vars['item']['date_show_hour'] ? 1 : 0);
            $this->vars['item']['date_show_second'] = ($this->vars['item']['date_show_second'] ? 1 : 0);

            // Check duplicate IDs on new items
            if ($this->vars['save'] == 'Add')
            {
                $existing = new MonitorItem ($this->vars['item']['id']);
                if ($existing->id or $this->vars['item']['id']==DISCOVERY_ITEM_ID)
                {
                    error_msg ($this->get_string('NEED_UNIQUE_ITEM_ID'));
                    $data_ok = false;
                }
            }

            // Inherit the SNMP fields, if needed
            if ($this->vars['parent_id'])
            {
                $parent_item = new MonitorItem ($this->vars['parent_id']);
                $item->is_snmp = $parent_item->is_snmp;
            }

            if ($data_ok)
            {
                $item->load_from_array($this->vars['item']);
                if ($item->is_valid_data()) $item->save_data ();
                else $data_ok = false;
            }

            if ($data_ok) $ret = $this->mk_redir ('monitor_item_edit', array('id' => $item->id));
            else
            {
                if ($this->vars['save'] == 'Add')
                {
                    $ret = $this->mk_redir ('monitor_item_add', $params);
                    save_form_data ($this->vars['item'], 'monitor_item_add');
                }
                else
                {
                    $ret = $this->mk_redir('monitor_item_edit', $params);
                    save_form_data ($this->vars['item'], 'monitor_item_edit');
                }
            }
        }
        else
        {
            if ($this->vars['item']['parent_id']) $ret = $this->mk_redir('monitor_item_edit', array('id'=>$this->vars['item']['parent_id']));
            else
            {
                if ($this->vars['id'])
                {
                    $item = new MonitorItem ($this->vars['id']);
                    if ($item->is_peripheral_item()) $ret = $this->mk_redir ('manage_monitor_items_peripherals');
                    else $ret = $this->mk_redir('manage_monitor_items');
                }
                else
                {
                    if ($this->vars['peripheral_item']) $ret = $this->mk_redir ('manage_monitor_items_peripherals');
                    else $ret = $this->mk_redir('manage_monitor_items');
                }
            }
        }

        return $ret;
    }


    /** Processes a deletion request of a monitoring item */
    function monitor_item_delete ()
    {
        check_auth();
        $id = $this->vars['id'];
        $item = new MonitorItem($id);
        if ($item->id) $item->delete();

        if ($item->parent_id) $ret = $this->mk_redir('monitor_item_edit', array('id' => $item->parent_id));
        else
        {
            if ($item->is_peripheral_item()) $ret = $this->mk_redir('manage_monitor_items_peripherals');
            else $ret = $this->mk_redir('manage_monitor_items');
        }

        return $ret;
    }


    /****************************************************************/
    /* Monitor Alerts management					*/
    /****************************************************************/

    /** Displays the page for managing KAWACS alerts */
    function manage_alerts ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl = 'manage_alerts.tpl';

        $filter = $_SESSION['manage_alerts']['filter'];
        if (!isset($filter['details'])) $filter['details'] = 0;

        $alerts = Alert::get_alerts ($filter);
        $used_item_ids = Alert::get_used_item_ids ();
        $items_list = MonitorItem::get_monitor_items_list ();
        $items_list_periph = MonitorItem::get_peripherals_monitor_items_list ();
        $items_list = $items_list + $items_list_periph;

        $profiles_list = MonitorProfile::get_profiles_list ();
        $users_list = User::get_users_list (array('type'=>USER_TYPE_KEYSOURCE, 'active'=>USER_FILTER_ACTIVE_AWAY));

        // Load the list of profiles for each alert
        for ($i=0; $i<count($alerts); $i++) $alerts[$i]->load_profiles_list ();

        $this->assign ('alerts', $alerts);
        $this->assign ('filter', $filter);
        $this->assign ('items_list', $items_list);
        $this->assign ('profiles_list', $profiles_list);
        $this->assign ('used_item_ids', $used_item_ids);
        $this->assign ('users_list', $users_list);
        $this->assign ('CRIT_TYPES_NAMES', $GLOBALS['CRIT_TYPES_NAMES']);
        $this->assign ('CRIT_NAMES', $GLOBALS['CRIT_NAMES']);
        $this->assign ('AVAILABLE_ITEMS_LISTS', $GLOBALS['AVAILABLE_ITEMS_LISTS']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('manage_alerts_submit');

        $this->display ($tpl);
    }


    /** Saves filtering info or redirects the user to the page for creating a new alert */
    function manage_alerts_submit ()
    {
        check_auth ();
        $ret = $this->mk_redir ('manage_alerts');

        if ($this->vars['add_alert'])
        {
            // This is a request to create a new alert
            if ($this->vars['item_id']) $ret = $this->mk_redir ('alert_add', array ('item_id' => $this->vars['item_id']));
            else
            {
                error_msg ($this->get_string('NEED_ALERT_MONITOR_ITEM'));
                $ret = $this->mk_redir ('manage_alerts');
            }
        }
        else
        {
            // This is a request to save filtering information
            $_SESSION['manage_alerts']['filter'] = $this->vars['filter'];
        }

        return $ret;
    }


    /** Displays the page for creating a new alert */
    function alert_add ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl = 'alert_add.tpl';

        $item_id = $this->vars['item_id'];
        if (!$item_id)
        {
            error_msg ($this->get_string('NEED_ALERT_MONITOR_ITEM'));
            return $this->mk_redir ('manage_alerts');
        }

        $alert = new Alert ();
        $alert->item_id = $item_id;
        $item = new MonitorItem ($item_id);

        $params = $this->set_carry_fields (array('item_id'));

        $this->assign ('alert', $alert);
        $this->assign ('item', $item);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('alert_add_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the newly created alert */
    function alert_add_submit ()
    {
        check_auth ();
        class_load ('Alert');

        $ret = $this->mk_redir ('manage_alerts');
        $params = $this->set_carry_fields (array('item_id'));

        if ($this->vars['save'])
        {
            $alert = new Alert ();
            $data = $this->vars['alert'];
            $alert->load_from_array ($data);
            $alert->item_id = $this->vars['item_id'];



            if ($alert->is_valid_data ())
            {
                $alert->save_data ();
                unset ($params['item_id']);
                $params['id'] = $alert->id;
                $ret = $this->mk_redir ('alert_edit', $params);
            }
            else
            {
                save_form_data ($data, 'alert_add');
                $ret = $this->mk_redir ('alert_add', $params);
            }
        }

        return $ret;
    }


    /** Displays the page for editing an alert */
    function alert_edit ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl = 'alert_edit.tpl';

        $alert = new Alert ($this->vars['id']);
        if (!$alert->id) return $this->mk_redir ('manage_alerts');
        $item = new MonitorItem ($alert->item_id);

        $users_list = User::get_users_list (array('type'=>USER_TYPE_KEYSOURCE, 'active'=>USER_FILTER_ACTIVE_AWAY));

        $alert->load_profiles_list ();
        $params = $this->set_carry_fields (array('id'));

        $this->assign ('alert', $alert);
        $this->assign ('item', $item);
        $this->assign ('users_list', $users_list);
        $this->assign ('DAY_NAMES', $GLOBALS['DAY_NAMES']);
        $this->assign ('JOIN_CONDITION_NAMES', $GLOBALS['JOIN_CONDITION_NAMES']);
        $this->assign ('ALERT_SEND_TO', $GLOBALS['ALERT_SEND_TO']);
        $this->assign ('CRIT_NAMES', $GLOBALS['CRIT_NAMES']);
        $this->assign ('CRIT_TYPES_NAMES', $GLOBALS['CRIT_TYPES_NAMES']);
        $this->assign ('AVAILABLE_ITEMS_LISTS', $GLOBALS['AVAILABLE_ITEMS_LISTS']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('alert_edit_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the modifications to the alert or opens the page for adding a new condition */
    function alert_edit_submit ()
    {
        check_auth ();
        class_load ('Alert');
        $alert = new Alert ($this->vars['id']);

        $ret = $this->mk_redir ('manage_alerts');
        $params = $this->set_carry_fields (array('id'));

        if ($this->vars['save'] and $alert->id)
        {
            // This is a requeste to save the alert definition
            $data = $this->vars['alert'];
            $data ['on_contact_only'] = ($data['on_contact_only'] ? 1 : 0);
            $ignore_days = 0;
            if (is_array($data['ignore_days'])) foreach ($data['ignore_days'] as $day) $ignore_days+= $day;
            $data['ignore_days'] = $ignore_days;
            $send_to=0;
            if (is_array($data['send_to'])) foreach ($data['send_to'] as $send) $send_to+= $send;
            $data['send_to'] = $send_to;

            $alert->load_from_array ($data);

            if ($alert->is_valid_data ()) $alert->save_data ();
            else save_form_data ($data, 'alert_edit');

            $ret = $this->mk_redir ('alert_edit', $params);
        }
        elseif ($this->vars['add_condition'] and $alert->id)
        {
            // This is a request to add a new condition
            $field_id = $this->vars['cond']['field_id'];
            if ($field_id or $alert->itemdef->type != MONITOR_TYPE_STRUCT)
            {
                if ($alert->itemdef->type != MONITOR_TYPE_STRUCT) $field_id = $alert->item_id;
                $ret = $this->mk_redir ('alert_condition_add', array ('alert_id' => $alert->id, 'field_id' => $field_id));
            }
            else
            {
                error_msg ($this->get_string('NEED_CONDITION_FIELD'));
                $ret = $this->mk_redir ('alert_edit', $params);
            }
        }
        elseif ($this->vars['edit_profiles'] and $alert->id)
        {
            // This is a request to edit the profiles to which the alert is assigned
            $ret = $this->mk_redir ('alert_profiles_edit', $params);
        }


        return $ret;
    }


    /** Displays the page for editing the alert-specific recpients */
    function alert_edit_recips ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl = 'alert_edit_recips.tpl';

        $alert = new Alert ($this->vars['id']);

        $params = $this->set_carry_fields (array('id', 'returl'));

        $users_list = User::get_users_list (array('type'=>USER_TYPE_KEYSOURCE, 'active'=>USER_FILTER_ACTIVE_AWAY));

        $this->assign ('alert', $alert);
        $this->assign ('users_list', $users_list);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('alert_edit_recips_submit', $params);

        $this->display ($tpl);
    }


    /** Save the list of recipients for an alert */
    function alert_edit_recips_submit ()
    {
        check_auth ();
        class_load ('Alert');

        $alert = new Alert ($this->vars['id']);
        $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('alert_edit', array('id'=>$alert->id)));
        $params = $this->set_carry_fields (array('id', 'returl'));

        if ($this->vars['save'] and $alert->id)
        {
            $recipients = ($this->vars['recipients'] ? $this->vars['recipients'] : array());
            $default_recipient = $this->vars['default_recipient'];

            if (count($recipients) > 0)
            {
                    if (!$default_recipient) error_msg($this->get_string('NEED_DEFAULT_RECIPIENT'));
                    elseif (!in_array($default_recipient, $recipients)) error_msg ($this->get_string('NEED_DEFAULT_RECIPIENT_SELECTED'));
                    else $alert->set_recipients ($recipients, $default_recipient);
            }
            else $alert->set_recipients ($recipients);

            $ret = $this->mk_redir ('alert_edit_recips', $params);
        }

        return $ret;
    }


    /** Displays the page for selecting computer values to include in notifications subjects when alerts are raised */
    function alert_edit_fields_send ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl = 'alert_edit_fields_send.tpl';
        $alert = new Alert ($this->vars['id']);


        $params = $this->set_carry_fields (array('id'));
        $this->assign ('alert', $alert);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('alert_edit_fields_send_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the list of field IDs for an alert */
    function alert_edit_fields_send_submit ()
    {
        check_auth ();
        class_load ('Alert');
        $alert = new Alert ($this->vars['id']);
        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('alert_edit', $params);

        if ($this->vars['save'] and $alert->id)
        {
            $alert->set_send_fields ($this->vars['fields']);
        }

        return $ret;
    }



    /** Deletes an alert definition */
    function alert_delete ()
    {
        check_auth ();
        class_load ('Alert');
        $ret = $this->mk_redir ('manage_alerts');
        $alert = new Alert ($this->vars['id']);

        if ($alert->id and $alert->can_delete ())
        {
                $alert->delete ();
        }

        return $ret;
    }


    /** Displays the page for editing the profiles to which an alert is assigned */
    function alert_profiles_edit ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl = 'alert_profiles_edit.tpl';

        $alert = new Alert ($this->vars['id']);
        if (!$alert->id) return $this->mk_redir ('manage_alerts');
        $alert->load_profiles_list ();

        if ($alert->itemdef->is_peripheral_item ()) $profiles_list = MonitorProfilePeriph::get_profiles_list ();
        else $profiles_list = MonitorProfile::get_profiles_list ();

        $params = $this->set_carry_fields (array('id'));

        $this->assign ('alert', $alert);
        $this->assign ('profiles_list', $profiles_list);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('alert_profiles_edit_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the profiles assignment for the alert */
    function alert_profiles_edit_submit ()
    {
        check_auth ();
        class_load ('Alert');
        $alert = new Alert ($this->vars['id']);

        $params = $this->set_carry_fields (array('id'));
        $ret = $this->mk_redir ('alert_edit', $params);

        if ($this->vars['save'] and $alert->id)
        {
                $profiles = ($this->vars['assigned_profiles'] ? $this->vars['assigned_profiles'] : array ());
                $alert->set_profiles ($profiles);
                $ret = $this->mk_redir ('alert_profiles_edit', $params);
        }

        return $ret;
    }

    /** Displays the page for adding a new alert condition */
    function alert_condition_add ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl = 'alert_condition_add.tpl';

        $alert = new Alert ($this->vars['alert_id']);
        if (!$alert->id) return $this->mk_redir ('manage_alerts');

        $condition = new AlertCondition ();
        if (!empty_error_msg()) $condition->load_from_array (restore_form_data ('alert_condition_add', false, $data));
        $condition->field_id = $this->vars['field_id'];
        $condition->fielddef = new MonitorItem ($this->vars['field_id']);

        if ($condition->fielddef->type == MONITOR_TYPE_LIST)
        {
                $this->assign ('list_values', $GLOBALS['AVAILABLE_ITEMS_LISTS'][$condition->fielddef->list_type]);
        }

        $params = $this->set_carry_fields (array('alert_id', 'field_id'));
        $this->assign ('alert', $alert);
        $this->assign ('condition', $condition);
        $this->assign ('CRIT_NAMES', $GLOBALS['CRIT_NAMES']);
        $this->assign ('CRIT_NAMES_DATE', $GLOBALS['CRIT_NAMES_DATE']);
        $this->assign ('CRIT_NAMES_STRING', $GLOBALS['CRIT_NAMES_STRING']);
        $this->assign ('CRIT_NAMES_NUMBER', $GLOBALS['CRIT_NAMES_NUMBER']);
        $this->assign ('CRIT_MEMORY_MULTIPLIERS_NAMES', $GLOBALS['CRIT_MEMORY_MULTIPLIERS_NAMES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('alert_condition_add_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the alert condition */
    function alert_condition_add_submit ()
    {
        check_auth ();
        $alert = new Alert ($this->vars['alert_id']);

        $ret = $this->mk_redir ('alert_edit', array ('id'=>$alert->id));
        $params = $this->set_carry_fields (array('alert_id', 'field_id'));

        if ($this->vars['save'] and $alert->id)
        {
            $data = $this->vars['cond'];
            $cond = new AlertCondition ();
            $cond->load_from_array ($data);
            $cond->alert_id = $this->vars['alert_id'];
            $cond->field_id = $this->vars['field_id'];
            $cond->fielddef = new MonitorItem ($cond->field_id);

            if ($cond->is_valid_data ())
            {
                $cond->save_data ();
                unset ($params['alert_id']);
                unset ($params['field_id']);
                $params['id'] = $cond->id;
                $ret = $this->mk_redir ('alert_condition_edit', $params);
            }
            else
            {
                save_form_data ($data, 'alert_condition_add');
                $ret = $this->mk_redir ('alert_condition_add', $params);
            }
        }

        return $ret;
    }


    /** Displays the page for editing an alert condition */
    function alert_condition_edit ()
    {
        check_auth ();
        class_load ('Alert');
        $tpl = 'alert_condition_edit.tpl';

        $condition = new AlertCondition ($this->vars['id']);
        $alert = new Alert ($condition->alert_id);
        if (!$condition->id) return $this->mk_redir ('manage_alerts');

        if (!empty_error_msg()) $condition->load_from_array (restore_form_data ('alert_condition_edit', false, $data));

        if ($condition->fielddef->type == MONITOR_TYPE_LIST)
        {
                $this->assign ('list_values', $GLOBALS['AVAILABLE_ITEMS_LISTS'][$condition->fielddef->list_type]);
        }

        $params = $this->set_carry_fields (array('id'));
        $this->assign ('condition', $condition);
        $this->assign ('alert', $alert);
        $this->assign ('CRIT_NAMES', $GLOBALS['CRIT_NAMES']);
        $this->assign ('CRIT_NAMES_DATE', $GLOBALS['CRIT_NAMES_DATE']);
        $this->assign ('CRIT_NAMES_STRING', $GLOBALS['CRIT_NAMES_STRING']);
        $this->assign ('CRIT_NAMES_NUMBER', $GLOBALS['CRIT_NAMES_NUMBER']);
        $this->assign ('CRIT_MEMORY_MULTIPLIERS_NAMES', $GLOBALS['CRIT_MEMORY_MULTIPLIERS_NAMES']);
        $this->assign ('CRIT_NAMES_LIST', $GLOBALS['CRIT_NAMES_LIST']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('alert_condition_edit_submit', $params);

        $this->display ($tpl);
    }


    /** Saves the alert condition */
    function alert_condition_edit_submit ()
    {
        check_auth ();
        class_load ('Alert');
        $condition = new AlertCondition ($this->vars['id']);

        $ret = $this->mk_redir ('alert_edit', array ('id' => $condition->alert_id));
        $params = $this->set_carry_fields (array('id'));

        if ($this->vars['save'] and $condition->id)
        {
                $data = $this->vars['cond'];
                if ($condition->fielddef->type==MONITOR_TYPE_LIST)
                {
                        if (!isset($data['list_values'])) $data['list_values'] = array();
                }
                $condition->load_from_array ($data);

                if ($condition->is_valid_data ()) $condition->save_data ();
                else save_form_data ($data, 'alert_condition_edit');

                $ret = $this->mk_redir ('alert_condition_edit', $params);
        }

        return $ret;
    }

    /**Deletes an alert condition */
    function alert_condition_delete ()
    {
        check_auth ();
        class_load ('Alert');
        $condition = new AlertCondition ($this->vars['id']);
        $ret = $this->mk_redir ('alert_edit', array ('id' => $condition->alert_id));

        if ($condition->id) $condition->delete ();

        return $ret;
    }


    /****************************************************************/
    /* Kawacs Agent Linux Updates management			*/
    /****************************************************************/

    /** Shows the page with the list of Kawacs Linux Agent releases */
    function manage_kawacs_linux_updates ()
    {
        check_auth ();
        class_load ('KawacsAgentLinuxUpdate');

        $tpl = 'manage_kawacs_linux_updates.tpl';

        $updates = KawacsAgentLinuxUpdate::get_updates_list();

        $this->assign ('updates', $updates);
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Shows the page for defining a new release of Kawacs Linux Agent */
    function kawacs_linux_update_add ()
    {
        check_auth ();
        class_load ('KawacsAgentLinuxUpdate');

        $tpl = 'kawacs_linux_update_add.tpl';
        $this->set_form_redir ('kawacs_linux_update_add_submit');

        $update = new KawacsAgentLinuxUpdate ();
        $update_data = array();
        if (!empty_error_msg()) restore_form_data ('update', false, $update_data);

        $update->load_from_array ($update_data);

        $this->assign ('update', $update);
        $this->assign ('error_msg', error_msg());
        $this->display ($tpl);
    }


    /** Saves the details about a new Kawacs Linux Agent release */
    function kawacs_linux_update_add_submit ()
    {
        check_auth ();
        class_load ('KawacsAgentLinuxUpdate');

        if ($this->vars['save'])
        {
            $update_data = $this->vars['update'];

            $update = new KawacsAgentLinuxUpdate();
            $update->load_from_array ($update_data);

            if ($update->is_valid_data())
            {
                $update->save_data ();
                $ret = $this->mk_redir('kawacs_linux_update_edit', array ('id'=>$update->id));
            }
            else
            {
                save_form_data ($update_data, 'update');
                $ret = $this->mk_redir('kawacs_linux_update_add');
            }
        }
        else
        {
            $ret = $this->mk_redir ('manage_kawacs_updates');
        }
        return $ret;
    }


    /** Shows the page for editing the details of a Kawacs Linux Agent release */
    function kawacs_linux_update_edit ()
    {
            check_auth ();
            class_load ('KawacsAgentLinuxUpdate');

            $tpl = 'kawacs_linux_update_edit.tpl';

            $update = new KawacsAgentLinuxUpdate ($this->vars['id']);
            if (!$update->id) return $this->mk_redir ('manage_linux_updates');

            $this->set_form_redir ('kawacs_linux_update_edit_submit', array ('id'=>$update->id));
            $this->assign ('update', $update);
            $this->assign ('error_msg', error_msg());

            $this->display ($tpl);
    }


    /** Saves the information about a Kawacs Linux release */
    function kawacs_linux_update_edit_submit ()
    {
        check_auth ();
        class_load ('KawacsAgentLinuxUpdate');

        if ($this->vars['save'])
        {
                $update_data = $this->vars['update'];
                $update = new KawacsAgentLinuxUpdate ($this->vars['id']);
                $update->load_from_array ($update_data);

                $update->process_installer_upload ('installer');
                $update->save_data();
                $ret = $this->mk_redir ('kawacs_linux_update_edit', array('id'=>$this->vars['id']));
        }
        else
        {
                $ret = $this->mk_redir ('manage_kawacs_linux_updates');
        }

        return $ret;
    }


    /** Publishes a Kawacs Linux Agent release */
    function kawacs_linux_update_publish ()
    {
        check_auth ();
        class_load ('KawacsAgentLinuxUpdate');

        $update = new KawacsAgentLinuxUpdate ($this->vars['id']);
        if ($update->id)
        {
                if ($update->is_valid_for_publishing())
                {
                        $update->publish();
                }
        }

        return $this->mk_redir ('manage_kawacs_linux_updates');
    }


    /** Deletes a Kawacs Linux Agent release */
    function kawacs_linux_update_delete ()
    {
        check_auth ();
        class_load ('KawacsAgentLinuxUpdate');

        $update = new KawacsAgentLinuxUpdate ($this->vars['id']);
        if ($update->id)
        {
                $update->delete();
        }

        return $this->mk_redir ('manage_kawacs_linux_updates');
    }


    /****************************************************************/
    /* Kawacs Agent Updates management				*/
    /****************************************************************/

    /** Shows the page with the list of Kawacs Agent releases */
    function manage_kawacs_updates ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');

        $tpl = 'manage_kawacs_updates.tpl';

        $updates = KawacsAgentUpdate::get_updates_list();

        $this->assign ('updates', $updates);
        $this->assign ('KAWACS_AGENT_FILES', $GLOBALS['KAWACS_AGENT_FILES']);
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Shows the page for defining a new release of Kawacs Agent */
    function kawacs_update_add ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');

        $tpl = 'kawacs_update_add.tpl';
        $this->set_form_redir ('kawacs_update_add_submit');

        $update = new KawacsAgentUpdate ();
        $update_data = array();
        if (!empty_error_msg()) restore_form_data ('update', false, $update_data);

        $update->load_from_array ($update_data);

        $this->assign ('update', $update);
        $this->assign ('error_msg', error_msg());
        $this->display ($tpl);
    }


    /** Saves the details about a new Kawacs Agent release */
    function kawacs_update_add_submit ()
    {
            check_auth ();
            class_load ('KawacsAgentUpdate');

            if ($this->vars['save'])
            {
                    $update_data = $this->vars['update'];

                    $update = new KawacsAgentUpdate();
                    $update->load_from_array ($update_data);

                    if ($update->is_valid_data())
                    {
                            $update->save_data ();
                            $ret = $this->mk_redir('kawacs_update_edit', array ('id'=>$update->id));
                    }
                    else
                    {
                            save_form_data ($update_data, 'update');
                            $ret = $this->mk_redir('kawacs_update_add');
                    }
            }
            else
            {
                    $ret = $this->mk_redir ('manage_kawacs_updates');
            }
            return $ret;
    }


    /** Shows the page for editing the details of a Kawacs Agent release */
    function kawacs_update_edit ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');
        class_load ('Computer');

        $tpl = 'kawacs_update_edit.tpl';

        $update = new KawacsAgentUpdate ($this->vars['id']);
        if (!$update->id) return $this->mk_redir ('manage_updates');

        // If there are preview computers, load their associated data
        for ($i=0; $i<count($update->previews); $i++)
        {
                $update->previews[$i]->load_computer_data ();
        }

        $this->set_form_redir ('kawacs_update_edit_submit', array ('id'=>$update->id));
        $this->assign ('update', $update);
        $this->assign ('KAWACS_AGENT_FILES', $GLOBALS['KAWACS_AGENT_FILES']);
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Saves the information about a Kawacs release */
    function kawacs_update_edit_submit ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');

        if ($this->vars['save'])
        {
                $update_data = $this->vars['update'];
                $update = new KawacsAgentUpdate ($this->vars['id']);
                $update->load_from_array ($update_data);


                $update->process_installer_upload ('installer');
                $update->process_files_uploads ('uploads', $this->vars['file_versions']);
                $update->save_data();
                $ret = $this->mk_redir ('kawacs_update_edit', array('id'=>$this->vars['id']));
        }
        else
        {
                $ret = $this->mk_redir ('manage_kawacs_updates');
        }

        return $ret;
    }


    /** Publishes a Kawacs Agent release */
    function kawacs_update_publish ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');

        $update = new KawacsAgentUpdate ($this->vars['id']);
        if ($update->id)
        {
                if ($update->is_valid_for_publishing())
                {
                        $update->publish();
                }
        }

        return $this->mk_redir ('manage_kawacs_updates');
    }


    /** Removes a file from this release */
    function kawacs_update_remove_file ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');

        $update_file = new KawacsAgentUpdateFile ($this->vars['version_id'], $this->vars['file_id']);
        if ($update_file->version_id and $update_file->file_id)
        {
                $update_file->delete();
        }

        return $this->mk_redir ('kawacs_update_edit', array('id' => $this->vars['version_id']));
    }


    /** Deletes a Kawacs Agent release */
    function kawacs_update_delete ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');

        $update = new KawacsAgentUpdate ($this->vars['id']);
        if ($update->id)
        {
                $update->delete();
        }

        return $this->mk_redir ('manage_kawacs_updates');
    }

    /** Displays the page for adding a new computer for pre-release */
    function kawacs_update_preview_add ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');
        class_load ('Computer');
        $tpl = 'kawacs_udpate_preview_add.tpl';

        $update = new KawacsAgentUpdate ($this->vars['udpate_id']);
        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);

        if ($this->vars['customer_id'])
        {
                $computers_list = Computer::get_computers_list (array('customer_id' => $this->vars['customer_id'], 'append_id' => true));
        }

        $params = $this->set_carry_fields (array('update_id', 'customer_id'));
        $this->assign ('udpate', $update);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('customer_id', $this->vars['customer_id']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('kawacs_update_preview_add_submit', $params);

        $this->display ($tpl);
    }

    function kawacs_update_preview_add_submit ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');
        $params = $this->set_carry_fields (array('update_id', 'customer_id'));
        $ret = $this->mk_redir ('kawacs_update_preview_add', $params);
        $update = new KawacsAgentUpdate ($this->vars['update_id']);

        if ($this->vars['save'])
        {
            if (!$this->vars['computer_id']) error_msg ($this->get_string('NEED_COMPUTER_FROM_LIST'));
            else
            {
                $update->add_pre_release ($this->vars['computer_id']);
                $ret = $this->mk_redir ('kawacs_update_edit', array('id' => $update->id));
            }
        }
        elseif ($this->vars['cancel'])
        {
            $ret = $this->mk_redir ('kawacs_update_edit', array('id' => $update->id));
        }

        return $ret;
    }

    /** Removes a computer from the pre-release list */
    function kawacs_update_preview_delete ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdatePreview');
        $ret = $this->mk_redir ('kawacs_update_edit', array('id' => $this->vars['update_id']));

        $preview = new KawacsAgentUpdatePreview ($this->vars['id']);
        $preview->delete ();

        return $ret;
    }

    /** Displays what versions of Kawacs Agent are currently reported by clients */
    function computers_agent_versions ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');
        $tpl = 'computers_agent_versions.tpl';

        $versions_stats = KawacsAgentUpdate::get_computers_versions ();
        $versions_stats_active = KawacsAgentUpdate::get_computers_versions (false, true);

        $this->assign ('versions_stats', $versions_stats);
        $this->assign ('versions_stats_active', $versions_stats_active);
        $this->assign ('KAWACS_AGENT_FILES', $GLOBALS['KAWACS_AGENT_FILES']);
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Shows which computers are using a specific version of a file */
    function computers_agent_versions_details ()
    {
        check_auth ();
        class_load ('KawacsAgentUpdate');
        $tpl = 'computers_agent_versions_details.tpl';

        $computers_version = KawacsAgentUpdate::get_computers_versions_details ($this->vars['file_id'], $this->vars['version'], $this->vars['active_only']);

        $this->assign ('computers_version', $computers_version);
        $this->assign ('version', $this->vars['version']);
        $this->assign ('file_id', $this->vars['file_id']);
        $this->assign ('active_only', $this->vars['active_only']);
        $this->assign ('KAWACS_AGENT_FILES', $GLOBALS['KAWACS_AGENT_FILES']);
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Displays what versions of Kawacs Linux Agent are currently reported by clients */
    function computers_linux_agent_versions ()
    {
            check_auth ();
            class_load ('KawacsAgentLinuxUpdate');
            $tpl = 'computers_linux_agent_versions.tpl';

            $versions_stats = KawacsAgentLinuxUpdate::get_computers_versions ();

            $this->assign ('versions_stats', $versions_stats);
            $this->assign ('error_msg', error_msg());

            $this->display ($tpl);
    }


    /** Shows which computers are using a specific version of a file */
    function computers_linux_agent_versions_details ()
    {
        check_auth ();
        class_load ('KawacsAgentLinuxUpdate');
        $tpl = 'computers_linux_agent_versions_details.tpl';

        $computers_version = KawacsAgentLinuxUpdate::get_computers_versions_details ($this->vars['version']);

        $this->assign ('computers_version', $computers_version);
        $this->assign ('version', $this->vars['version']);
        $this->assign ('file_id', $this->vars['file_id']);
        $this->assign ('KAWACS_AGENT_FILES', $GLOBALS['KAWACS_AGENT_FILES']);
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /****************************************************************/
    /* Internet connections monitoring				*/
    /****************************************************************/

    /** Displays the page for managing monitored IP addresses */
    function manage_monitored_ips ()
    {
        check_auth ();
        class_load ('MonitoredIP');
        class_load ('Customer');
        $tpl = 'manage_monitored_ips.tpl';

        $filter = $_SESSION['manage_monitored_ips']['filter'];

        // Extract the list of Kawacs customers, eventually restricting only to the customers assigned to
        // the current user, if he has restricted customer access.
        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);

        $monitored_ips = MonitoredIP::get_monitored_ips ($filter);
        for ($i=0; $i<count($monitored_ips); $i++)
        {
                $monitored_ips[$i]->load_customer ();
                $monitored_ips[$i]->load_contract ();
        }

        $this->assign ('monitored_ips', $monitored_ips);
        $this->assign ('filter', $filter);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('MONITOR_STATS', $GLOBALS['MONITOR_STATS']);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('manage_monitored_ips_submit');

        $this->display ($tpl);
    }


    function manage_monitored_ips_submit ()
    {
        $_SESSION['manage_monitored_ips']['filter'] = $this->vars['filter'];
        return $this->mk_redir ('manage_monitored_ips');
    }


    /** Displays the page for creating a new monitoring IP */
    function monitored_ip_add ()
    {
        check_auth ();
        class_load ('MonitoredIP');
        class_load ('Customer');
        class_load ('CustomerInternetContract');
        $tpl = 'monitored_ip_add.tpl';

        $customer = new Customer ($this->vars['customer_id']);
        if (!$customer->id) return $this->mk_redir ('manage_monitored_ips');
        $customers_ips = MonitoredIP::get_customers_remote_ips ($customer->id);

        $monitored_ip = new MonitoredIP ();
        if (!empty_error_msg()) $monitored_ip->load_from_array (restore_form_data('monitored_ip_add', false, $data));
        $monitored_ip->customer_id = $customer->id;

        $internet_contracts = CustomerInternetContract::get_contracts (array('customer_id'=>$customer->id, 'load_details' => true));

        $params = $this->set_carry_fields (array('customer_id'));

        $this->assign ('monitored_ip', $monitored_ip);
        $this->assign ('customer', $customer);
        $this->assign ('customers_ips', $customers_ips);
        $this->assign ('internet_contracts', $internet_contracts);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('monitored_ip_add_submit', $params);

        $this->display ($tpl);
    }


    function monitored_ip_add_submit ()
    {
        class_load ('MonitoredIP');
        class_load ('Customer');
        check_auth (array('customer_id' => $this->vars['customer_id']));

        $ret = $this->mk_redir ('manage_monitored_ips');
        $params = $this->set_carry_fields (array('customer_id'));

        if ($this->vars['save'])
        {
                $data = $this->vars['monitored_ip'];
                $monitored_ip = new MonitoredIP ();
                $monitored_ip->load_from_array ($data);
                $monitored_ip->customer_id = $this->vars['customer_id'];

                if ($monitored_ip->is_valid_data())
                {
                        $monitored_ip->created = time ();
                        $monitored_ip->user_id = $this->current_user->id;
                        $monitored_ip->save_data ();
                        unset($params['customer_id']);
                        $params['id'] = $monitored_ip->id;
                        $ret = $this->mk_redir ('monitored_ip_edit', $params);
                }
                else
                {
                        save_form_data ($data, 'monitored_ip_add');
                        $ret = $this->mk_redir ('monitored_ip_add', $params);
                }
        }

        return $ret;
    }


    function monitored_ip_edit ()
    {
        class_load ('Customer');
        class_load ('MonitoredIP');
        class_load ('CustomerInternetContract');
        $monitored_ip = new MonitoredIP ($this->vars['id']);
        $customer = new Customer ($monitored_ip->customer_id);
        check_auth (array('customer_id' => $monitored_ip->customer_id));
        $tpl = 'monitored_ip_edit.tpl';

        if (!$monitored_ip->id) return $this->mk_redir ('manage_monitored_ips');
        if (!empty_error_msg()) $monitored_ip->load_from_array (restore_form_data('monitored_ip_edit', false, $data));

        $monitored_ip->load_customer ();
        $monitored_ip->load_user ();
        $monitored_ip->load_notification ();
        $computers_list = $monitored_ip->get_computers ();

        $internet_contracts = CustomerInternetContract::get_contracts (array('customer_id'=>$customer->id, 'load_details' => true));

        if ($monitored_ip->notification->id)
        {
                // Mark the associated notification as being read and update the counter
                $monitored_ip->notification->mark_read ($this->current_user->id);
                $this->update_unread_notifs ();
        }

        $params = $this->set_carry_fields (array('id', 'returl'));

        $this->assign ('monitored_ip', $monitored_ip);
        $this->assign ('computers_list', $computers_list);
        $this->assign ('internet_contracts', $internet_contracts);
        $this->assign ('MONITOR_STATS', $GLOBALS['MONITOR_STATS']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('monitored_ip_edit_submit', $params);

        $this->display ($tpl);
    }


    function monitored_ip_edit_submit ()
    {
        class_load ('Customer');
        class_load ('MonitoredIP');
        $monitored_ip = new MonitoredIP ($this->vars['id']);
        check_auth (array('customer_id' => $monitored_ip->customer_id));

        if ($this->vars['returl']) $ret = $this->vars['returl'];
        else $ret = $this->mk_redir ('manage_monitored_ips');
        $params = $this->set_carry_fields (array('id', 'returl'));

        if ($this->vars['save'] and $monitored_ip->id)
        {
                $last_target = $monitored_ip->target_ip;
                $data = $this->vars['monitored_ip'];
                $monitored_ip->load_from_array ($data);

                if ($monitored_ip->is_valid_data())
                {
                        if ($last_target != $monitored_ip->target_ip) $monitored_ip->last_traceroute_test = 0;
                        $monitored_ip->save_data ();
                }
                else save_form_data ($data, 'monitored_ip_edit');

                $ret = $this->mk_redir ('monitored_ip_edit', $params);
        }

        return $ret;
    }


    /** Deletes a monitored IP */
    function monitored_ip_delete ()
    {
        class_load ('Customer');
        class_load ('MonitoredIP');
        $monitored_ip = new MonitoredIP ($this->vars['id']);
        $ret = $this->mk_redir ('manage_monitored_ips');

        if ($monitored_ip->id and $monitored_ip->can_delete ()) $monitored_ip->delete ();

        return $ret;
    }

    /****************************************************************/
    /* Graphs generation						                    */
    /****************************************************************/

    function plot_free_disk ()
    {
        class_load ('Computer');

        // XXX: Temporary solution, until the final graph library is decided
        require_once ('./_external/phplot/phplot.php');


        $computer_id = $this->vars['computer_id'];
        $partition = trim(urldecode($this->vars['partition']));
        if (preg_match('/^[A-Za-z]\:$/', $partition)) $partition.= '\\';  // For windows disks make sure we have the trailing slash
        $computer_name = ($this->vars['computer_name'] ? $this->vars['computer_name'] : Computer::get_item_ex('netbios_name', $computer_id));
        $month_start = (isset($this->vars['start']) ? $this->vars['start'] : date ('Y_m'));
        $month_end = ($this->vars['end'] ? $this->vars['end'] : date ('Y_m'));
        $format = ($this->vars['format'] ? strtolower ($this->vars['format']) : 'png');
        $width = ($this->vars['width'] ? $this->vars['width'] : 600);
        $height = ($this->vars['height'] ? $this->vars['height'] : 400);
        $limit_range = ($this->vars['limit_range'] ? true : false);
        $no_title = ($this->vars['no_title'] ? true : false);

        // Fetch the data
        $filter_partitions = array (
                'computer_id' => $computer_id,
                'partition' => $partition,
                'month_start' => $month_start,
                'month_end' => $month_end,
                'interval' => 'day',
                'sort_dir' => 'ASC'
        );

        $history = Computer::get_partitions_history($filter_partitions);
        $data = array ();

        if ($history[$partition]->size > (1024*1024*1024))
        {
                $size_divisor = 1024*1024*1024;
                $size_divisor_unit = 'GB';
        }
        else
        {
                $size_divisor = 1024*1024;
                $size_divisor_unit = 'MB';
        }

        $total_size = round($history[$partition]->size / $size_divisor, 2);
        if(!empty($history[$partition]->log))
            foreach($history[$partition]->log as $time => $size) $data[] = array(date(DATE_TIME_FORMAT,$time), $size/$size_divisor);

        $date_min = min (array_keys($history[$partition]->log));
        $date_max = max (array_keys($history[$partition]->log));
        $size_min = min ($history[$partition]->log)/$size_divisor;
        $size_max = max ($history[$partition]->log)/$size_divisor;

        // Create the image
        $graph = new PHPlot ($width, $height);
        $graph->setDataValues ($data);

        $graph->SetPlotType ('lines');
        if (!$no_title)
        {
                $graph->SetTitle ($computer_name.' : Free Disk Space - '.$partition.' ('.get_memory_string($history[$partition]->size).')');
        }
        $graph->SetXLabel ('Date ('.date (DATE_FORMAT,$date_min).' - '.date (DATE_FORMAT,$date_max).')');
        $graph->SetYLabel ('Free Space ('.$size_divisor_unit.')');

        $graph->SetNumVertTicks (10);
        $graph->SetPrecisionY(2);
        $graph->SetNumHorizTicks (min(32, count($history[$partition]->log)));
        $graph->SetXLabelAngle(90);
        if ($limit_range) $graph->SetPlotAreaWorld ("", $size_min , "", $size_max);
        else $graph->SetPlotAreaWorld ("", 0 , "", $total_size);
        $graph->SetDataColors (array('red'));

        $graph->SetXGridLabelType("title");
        $graph->SetXDatalabelPos('none');

        $graph->SetFileFormat ($format);
        $graph->DrawGraph ();
        die;
    }


    function plot_backup_age ()
    {
        class_load ('Computer');

        // XXX: Temporary solution, until the final graph library is decided
        require_once ('./_external/phplot/phplot.php');

        $computer_id = $this->vars['computer_id'];
        $partition = trim(urldecode($this->vars['partition']));
        $computer_name = ($this->vars['computer_name'] ? $this->vars['computer_name'] : Computer::get_item_ex('netbios_name', $computer_id));
        $month_start = (isset($this->vars['start']) ? $this->vars['start'] : date ('Y_m'));
        $month_end = ($this->vars['end'] ? $this->vars['end'] : date ('Y_m'));
        $format = ($this->vars['format'] ? strtolower ($this->vars['format']) : 'png');
        $width = ($this->vars['width'] ? $this->vars['width'] : 600);
        $height = ($this->vars['height'] ? $this->vars['height'] : 400);
        $limit_range = ($this->vars['limit_range'] ? true : false);
        $no_title = ($this->vars['no_title'] ? true : false);

        // Fetch the data
        $filter_partitions = array (
                'computer_id' => $computer_id,
                'month_start' => $month_start,
                'month_end' => $month_end,
                'sort_dir' => 'ASC'
        );

        $history = Computer::get_backups_history($filter_partitions);

        $data = array ();
        if(!empty($history[$partition]->log))
            foreach($history as $time => $age) $data[] = array(date('D, '.DATE_FORMAT,$time), $age);

        $date_min = min (array_keys($history));
        $date_max = max (array_keys($history));
        $age_max = max ($history)+3;
        $age_max = max ($age_max, 7);

        // Create the image
        $graph = new PHPlot ($width, $height);
        $graph->setDataValues ($data);


        $graph->SetPlotType ('bars');
        if (!$no_title)
        {
                $graph->SetTitle ($computer_name.' : Backup Age');
        }
        $graph->SetXLabel ('Date ('.date (DATE_FORMAT,$date_min).' - '.date (DATE_FORMAT,$date_max).')');
        $graph->SetYLabel ('Age (days)');

        $graph->SetPrecisionY (0);
        //$graph->SetNumHorizTicks (count($history));
        $graph->SetHorizTickIncrement (1);
        $graph->SetVertTickIncrement (1);
        $graph->SetXLabelAngle (90);
        $graph->SetPlotAreaWorld ('', 0 , '', $age_max+3);

        $graph->SetXDatalabelPos ('none');
        $graph->SetXGridLabelType ('title');


        $graph->SetFileFormat ($format);
        $graph->DrawGraph ();
        die;
    }


    //xxxxxxxxxxxxxxxxxxxxxxxxxxxx
    function plot_backup_size ()
    {
        class_load ('Computer');
        require_once ('./_external/phplot/phplot.php');

        $computer_id = $this->vars['computer_id'];
        $computer_name = ($this->vars['computer_name'] ? $this->vars['computer_name'] : Computer::get_item_ex('netbios_name', $computer_id));
        $month_start = (isset($this->vars['start']) ? $this->vars['start'] : date ('Y_m'));
        $month_end = ($this->vars['end'] ? $this->vars['end'] : date ('Y_m'));
        $format = ($this->vars['format'] ? strtolower ($this->vars['format']) : 'png');
        $width = ($this->vars['width'] ? $this->vars['width'] : 600);
        $height = ($this->vars['height'] ? $this->vars['height'] : 400);
        $limit_range = ($this->vars['limit_range'] ? true : false);
        $no_title = ($this->vars['no_title'] ? true : false);

        // Fetch the data
        $filter_data = array (
                'computer_id' => $computer_id,
                'month_start' => $month_start,
                'month_end' => $month_end,
                'sort_dir' => 'ASC'
        );

        $history = Computer::get_backups_sizes($filter_data);
        $size_min = min ($history);
        $size_max = max ($history);
        $date_min = min (array_keys($history));
        $date_max = max (array_keys($history));
        $size_divisor = 1024*1024;
        $size_divisor_unit = 'MB';
        if ($size_max > (1024*1024*1024))
        {
                $size_divisor*= 1024;
                $size_divisor_unit = 'GB';
        }

        $total_size = round($history[$partition]->size / $size_divisor, 2);
        $data = array ();
        foreach ($history as $time => $size) $data[] = array (date (DATE_FORMAT,$time), $size/$size_divisor);
        $size_min = $size_min / $size_divisor;
        $size_max = $size_max / $size_divisor;

        // Create the image
        $graph = new PHPlot ($width, $height);
        $graph->setDataValues ($data);


        $size_min = round ($size_min, 2);
        $size_max = round ($size_max, 2);
        $graph->SetPlotType ('lines');
        if (!$no_title)
        {
                $graph->SetTitle ($computer_name.' : Backups Sizes ');
        }
        $graph->SetXLabel ('Date ('.date (DATE_FORMAT,$date_min).' - '.date (DATE_FORMAT,$date_max).')');
        $graph->SetYLabel ("Size ($size_min $size_divisor_unit - $size_max $size_divisor_unit)");

        $graph->SetNumVertTicks (10);
        $graph->SetPrecisionY(2);
        //$graph->SetNumHorizTicks (30);
        $graph->SetNumHorizTicks (min(32, count($history)));
        $graph->SetXLabelAngle(90);

        $size_min = floor ($size_min);
        $size_max = ceil ($size_max);
        $graph->SetPlotAreaWorld ("", $size_min , "", $size_max);
        $graph->SetDataColors (array('red'));

        $graph->SetXGridLabelType("title");
        $graph->SetXDatalabelPos('none');

        $graph->SetFileFormat ($format);
        $graph->DrawGraph ();
        die;
    }


    /** Creates and serve to the browser a graph with the evolution of AV updates ages for a computer */
    function plot_av_update_age ()
    {
        class_load ('Computer');

        // XXX: Temporary solution, until the final graph library is decided
        require_once ('./_external/phplot/phplot.php');

        $computer_id = $this->vars['computer_id'];
        $computer_name = ($this->vars['computer_name'] ? $this->vars['computer_name'] : Computer::get_item_ex('netbios_name', $computer_id));
        $month_start = (isset($this->vars['start']) ? $this->vars['start'] : date ('Y_m'));
        $month_end = ($this->vars['end'] ? $this->vars['end'] : date ('Y_m'));
        $format = ($this->vars['format'] ? strtolower ($this->vars['format']) : 'png');
        $width = ($this->vars['width'] ? $this->vars['width'] : 600);
        $height = ($this->vars['height'] ? $this->vars['height'] : 400);
        $no_title = ($this->vars['no_title'] ? true : false);

        // Fetch the data
        $filter_av = array (
                'computer_id' => $computer_id,
                'month_start' => $month_start,
                'month_end' => $month_end,
                'sort_dir' => 'ASC'
        );

        $history = Computer::get_av_history ($filter_av);

        $data = array ();
        foreach ($history as $time => $age) $data[] = array (date ('D, '.DATE_FORMAT,$time), $age);

        if(empty($history)){
           $history[time()] = 0;
        }
        $date_min = min(array_keys($history));
        $date_max = max(array_keys($history));
        $age_max = max($history)+3;
        $age_max = max($age_max, 7);


        // Create the image
        $graph = new PHPlot ($width, $height);
        $graph->setDataValues ($data);


        $graph->SetPlotType ('bars');
        if (!$no_title)
        {
                $graph->SetTitle ($computer_name.' : Antivirus update age');
        }
        $graph->SetXLabel ('Date ('.date (DATE_FORMAT,$date_min).' - '.date (DATE_FORMAT,$date_max).')');
        $graph->SetYLabel ('Age (days)');

        $graph->SetPrecisionY (0);
        //$graph->SetNumHorizTicks (count($history));

        $graph->SetHorizTickIncrement (intval(count($history)/30) + 1);
        $graph->SetVertTickIncrement (1);
        $graph->SetXLabelAngle (90);
        $graph->SetPlotAreaWorld ('', 0 , '', $age_max+3);

        $graph->SetXDatalabelPos ('none');
        $graph->SetXGridLabelType ('title');


        $graph->SetFileFormat ($format);
        $graph->DrawGraph ();
        die;
    }


    /** Creates and serves to the browser a graph with the last AV updates for a customer at a given date */
    function plot_av_status ()
    {
        class_load ('Computer');
        class_load ('Customer');

        // XXX: Temporary solution, until the final graph library is decided
        require_once ('./_external/phplot/phplot.php');

        $customer_id = $this->vars['customer_id'];
        $customer = new Customer ($customer_id);
        $date = (isset($this->vars['date']) ? $this->vars['date'] : time());

        $format = ($this->vars['format'] ? strtolower ($this->vars['format']) : 'png');
        $width = ($this->vars['width'] ? $this->vars['width'] : 600);
        $height = ($this->vars['height'] ? $this->vars['height'] : 400);
        $no_title = ($this->vars['no_title'] ? true : false);

        // Fetch the data
        $filter_av = array (
                'customer_id' => $customer_id,
                'date' => $date,
        );
        $status = Computer::get_av_status ($filter_av);

        $date_min = time (); $date_max = 0;
        foreach ($status as $id => $t)
        {
                if (!is_null($t) and $t<$date_min) $date_min = $t;
                if (!is_null($t) and $t>$date_max) $date_max = $t;
        }
        $data = array ();
        $computers_list = Computer::get_computers_list (array('customer_id' => $customer_id));
        // Make the data order by computer name
        foreach ($computers_list as $id => $name)
        {
                if (isset($status[$id])) $data[] = array ($name.' ('.$id.')', $status[$id]);
        }

        // Create the image
        $graph = new PHPlot ($width, $height);
        $graph->setDataValues ($data);

        $graph->SetPlotType ('bars');
        if (!$no_title) $graph->SetTitle ($customer->name.' : Latest AV Updates'."\n".date(DATE_TIME_FORMAT, $date));
        $graph->SetXLabel ('Computers');
        $graph->SetYLabel ('Latest updates');

        //$graph->SetNumHorizTicks (count($status));
        $graph->SetNumHorizTicks (count($data));

        $date_min_plot = $date_min - (2*24*3600);
        $date_max_plot = $date_max + (2*24*3600);
        $days = ($date_max_plot - $date_min) / (24*3600);
        if ($days < 5)
        {
                $date_max_plot = $date_min_plot + (5*24*3600);
                $days = ($date_max_plot - $date_min_plot) / (24*3600);
        }

        $graph->SetNumVertTicks (min ($days, 20)); // More than 20 horizontal lines look ugly
        $graph->y_time_format = '%d %b %Y';
        $graph->SetXLabelAngle (90);
        $graph->SetPlotAreaWorld ('', $date_min_plot , '', $date_max_plot);
        $graph->SetXDatalabelPos ('none');
        $graph->SetXGridLabelType ('title');
        $graph->SetYGridLabelType ('time');

        $graph->SetFileFormat ($format);
        $graph->DrawGraph ();
        die;
    }



    /** Displays the pop-up for running traceroute */
    function popup_traceroute ()
    {
        class_load ('Customer');
        class_load ('Computer');
        class_load ('MonitoredIP');
        //check_auth (array('computer_id' => $this->vars['computer_id']));
        $tpl = 'popup_traceroute.tpl';

        if ($this->vars['computer_id'])
        {
                $computer = new Computer ($this->vars['computer_id']);
                // Load the computer's local IPs
                $ips = array ();
                $adapters = $computer->get_item('net_adapters');
                $net_adapters_field_id = $computer->get_item_id ('net_adapters');
                $ip_field_id = $computer->get_item_id('ip_address', $net_adapters_field_id);
                $adapter_name_id = $computer->get_item_id('name', $net_adapters_field_id);
                for ($i=0; $i<count($adapters); $i++)
                {
                        if ($adapters[$i][$ip_field_id] and $adapters[$i][$ip_field_id] != '0.0.0.0')
                        {
                                $ips[] = array('ip'=>trim($adapters[$i][$ip_field_id]), 'adapter'=>trim($adapters[$i][$adapter_name_id]));
                        }
                }
        }
        elseif ($this->vars['customer_id'])
        {
                $customer = new Customer ($this->vars['customer_id']);
                $customer_ips = MonitoredIP::get_customers_remote_ips ($customer->id);
        }

        $params = $this->set_carry_fields (array('computer_id', 'customer_id'));

        $target_ip = '';
        if ($this->vars['run_traceroute'])
        {
                // This is a request to actually run traceroute
                $target_ip = $this->vars['target_ip'];
                $this->assign ('run_traceroute', 1);
        }

        $this->assign ('computer', $computer);
        $this->assign ('customer', $customer);
        $this->assign ('target_ip', $target_ip);
        $this->assign ('ips', $ips);
        $this->assign ('customer_ips', $customer_ips);
        $this->assign ('max_hops', ($this->vars['max_hops'] ? $this->vars['max_hops'] : 20));
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('popup_traceroute', $params);

        $this->display_template_limited ($tpl);

        // Start running traceroute and display progress
        if ($target_ip)
        {
                session_write_close ();
                $target_reached = false;
                $max_hops = ($this->vars['max_hops'] ? $this->vars['max_hops'] : 20);
                $command = '/bin/traceroute -m '.$max_hops.' '.$target_ip.' 2>&1';
                $h = popen ($command, 'r');
                if ($h)
                {
                        while ($s = fread($h, 1024))
                        {
                                $s = str_replace ("\n", '\n', $s);
                                echo '<script language="JavaScript">updateStat("'.$s.'");</script>';
                                flush();
                                if (!preg_match('/^traceroute/',$s) and strpos($s,$target_ip)) $target_reached = true;
                        }
                        pclose ($h);
                }
                else
                {
                        echo '<script language="JavaScript">updateStat("Failed running traceroute.");</script>';
                }

                echo '<script language="JavaScript">runFinished('.($target_reached?'true':'false').');</script>';
                flush();
        }
    }

    /****************************************************************/
    /* KAWACS Dashboard											*/
    /****************************************************************/

    function customer_computers_dashboard()
    {
        check_auth();
        $tpl = "customer_computers_dashboard.tpl";

        $filter = array('favorites_first' => $this->current_user->id, 'show_ids' => true);
        if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($filter);

        if ($this->vars['customer_id'])
        {
                // The customer ID was passed in the URL
                $_SESSION['customer_computers_dashboard']['filter']['customer_id'] = $this->vars['customer_id'];
        }
        elseif ($this->vars['change_customer'])
        {
                // A change of customer was requested
                unset ($_SESSION['customer_computers_dashboard']['filter']['customer_id']);
        }
        elseif ($this->locked_customer->id and !$this->vars['do_filter'])
        {
                // If 'do_filter' is present in request, the locked customer is ignored
                $_SESSION['customer_computers_dashboard']['filter']['customer_id'] = $this->locked_customer->id;
        }

        $filter = $_SESSION['customer_computers_dashboard']['filter'];
        $params = array();
        if ($filter['customer_id'])
        {
                // There is a valid selected customer
                $customer = new Customer ($filter['customer_id']);
                $params['filter']['customer_id'] = $customer->id;
        }


        $this->assign('customer', $customer);
        $this->assign('filter', $filter);
        $this->assign('customers_list', $customers_list);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('customer_computers_dashboard_submit');
        $this->display($tpl);
    }
    function customer_computers_dashboard_submit()
    {
        check_auth();
        $ret = $this->mk_redir('customer_computers_dashboard');
        /*
        if ($this->vars['select'] and $this->vars['filter']['customer_id'])
        {
                // This was a request to select a customer for reporting
                $_SESSION['customer_computers_dashboard']['filter']['customer_id'] = $this->vars['filter']['customer_id'];
                $ret = $this->mk_redir ('customer_computers_dashboard', array('do_filter' => 1));
        }
        */
        $_SESSION['customer_computers_dashboard'] = $this->vars;
        return $ret;
    }

    function customer_computers_dashboard_data()
    {
        check_auth(array('customer_id'=>$this->vars['customer_id']));
        $tpl = 'customer_computers_dashboard_data.xml';

        $customer = new Customer($this->vars['customer_id']);
        if(!$customer->id) return $this->mk_redir('customer_computers_dashboard');

        $log_months = Computer::get_all_log_months (array('customer_id' => $customer->id));

        $c_start_month = 0;
        $c_end_month = 0;

        if($this->vars['c_start_month'])
                $c_start_month = $this->vars['c_start_month'];
        if($this->vars['c_end_month'])
                $c_end_month = $this->vars['c_end_month'];

        $computers_all = Computer::get_computers_ex(array('customer_id'=>$customer->id));

        $computers_current = array();
        if(isset($computers_all['current'])) $computers_current = $computers_all['current'];
        $computers_old = array();
        if(isset($computers_all['old'])) $computers_old = $computers_all['old'];
        $computers_blackout = array();
        if(isset($computers_all['blackout'])) $computers_blackout = $computers_all['blackout'];


        $tot_comps_current = sizeof($computers_current);
        $tot_comps_old = sizeof($computers_old);
        $tot_comps_blackout = sizeof($computers_blackout);


        //now we have to get the data for each month we are plotting
        //first we create a filter with the required log months
        if($c_start_month < $c_end_month)
        {
                //swap this
                $a = $c_start_month;
                $c_start_month = $c_end_month;
                $c_end_month = $a;
        }

        //now generate the protable months array;
        $plottable_months = array();
        for($i=$c_end_month; $i<=$c_start_month; $i++)
        {
                $plottable_months[] = $log_months[$i];
        }

        $plottable_months = array_reverse($plottable_months);

        $evo = array();
        $months_names = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
        foreach($plottable_months as $pm)
        {
                $s = array();
                $m = split("_", $pm);
                $ppm = $months_names[intval($m[1])-1]." ".$m[0];
                $s['month'] = $ppm;
                $s['available'] = rand(50, 60);
                $s['reporting'] = rand(45, 55);
                $s['blackout'] = rand(1,4);
                $evo[] = $s;
        }
        //now we have to generate the data for this months



        $this->assign('evo', $evo);
        $this->assign('tot_comps_current', $tot_comps_current);
        $this->assign('tot_comps_old', $tot_comps_old);
        $this->assign('tot_comps_blackout', $tot_comps_blackout);
        $this->assign('c_start_month', $c_start_month);
        $this->assign('c_end_month', $c_end_month);
        $this->assign('log_months', $log_months);
        $this->assign('customer', $customer);
        header('Content-Type: text/xml');
        $this->display_template_only ($tpl);
        die;
    }

    /**
     * displays a page with backup statuses of all computers managed by keyos that have backup reporting in their profile
     *
     */
    function kawacs_backup_dashboard()
    {
        check_auth();
        $tpl = "kawacs_backup_dashboard.tpl";
        $extra_params = array(); //array with additional parameters used in navigation
        if($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        class_load('Computer');
        class_load('MonitorProfile');
        $filter['customer_id'] = COMPUTERS_FILTER_ALL;
        $filter = $_SESSION['kawacs_backup_dashboard']['filter'];

        //$filter['group_by_type'] = 1;

        if (!$filter['order_by']) $filter['order_by'] = 'customer';
        if (!$filter['order_dir']) $filter['order_dir'] = 'ASC';
        //if (!isset($filter['start'])) $filter['start'] = 0;
        //if (!isset($filter['limit'])) $filter['limit'] = 100;
        //if (!isset($filter['reload_seconds'])) $filter['reload_seconds'] = 300;

        if(isset($filter['customer_id']) && $filter['customer_id'] != COMPUTERS_FILTER_ALL)
        {
                $_SESSION['kawacs_backup_dashboard']['filter']['customer_id'] = $filter['customer_id'];
        }
        $filter['customer_id'] = $_SESSION['kawacs_backup_dashboard']['filter']['customer_id'];

        $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
        // Extract the list of customers
        $customers = Customer::get_customers_list ($customers_filter);
        $customers_filter_all['active'] = -1;
        $customers_all = Customer::get_customers_list ($customers_filter_all);
        $profiles = MonitorProfile::get_profiles_with_backup();

        $computers_green = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_SUCCESS, $count_g);
        $computers_red = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_ERROR, $count_r);
        $computers_orange = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_TAPE_ERROR, $count_o);
        $computers_grey = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_NOT_REPORTING, $count_gr);
        $this->assign('error_msg', error_msg());
        $this->assign('customers_all', $customers_all);
        //$this->assign('computers', $computers_green);
        $this->assign('count_r', count($computers_red));
        $this->assign('count_o', count($computers_orange));
        $this->assign('count_g', count($computers_green));
        $this->assign('count_gr', count($computers_grey));
        $this->assign('computers_red', $computers_red);
        $this->assign('computers_orange', $computers_orange);
        $this->assign('computers_green', $computers_green);
        $this->assign('computers_grey', $computers_grey);
        $this->assign('profiles', $profiles);
        $this->assign('filter', $filter);
        $this->assign('sort_url', $this->mk_redir('kawacs_backup_dashboard_submit', $extra_params));
        $this->set_form_redir('kawacs_backup_dashboard_submit', $extra_params);
        $this->display($tpl);
    }

    function kawacs_backup_dashboard_submit()
    {
        check_auth();
        //$extra_params = array();

        //debug($this->vars);
        if($this->vars['order_by'] and $this->vars['order_dir'])
        {
                $_SESSION['kawacs_backup_dashboard']['filter']['order_by'] = $this->vars['order_by'];
                $_SESSION['kawacs_backup_dashboard']['filter']['order_dir'] = $this->vars['order_dir'];
        }
        if($this->vars['filter'])
        {
                if(is_array($_SESSION['kawacs_backup_dashboard']['filter']))
                {
                        $_SESSION['kawacs_backup_dashboard']['filter'] = array_merge($_SESSION['kawacs_backup_dashboard']['filter'], $this->vars['filter']);
                }
                else
                {
                        $_SESSION['kawacs_backup_dashboard']['filter'] = $this->vars['filter'];
                }
        }

        return $this->mk_redir('kawacs_backup_dashboard');
    }


    function kawacs_antivirus_dashboard()
    {
        check_auth();
        $tpl = "kawacs_antivirus_dashboard.tpl";
        $extra_params = array(); //array with additional parameters used in navigation
        if($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        class_load('Computer');
        class_load('MonitorProfile');

        $filter['customer_id'] = COMPUTERS_FILTER_ALL;
        $filter = $_SESSION['kawacs_antivirus_dashboard']['filter'];

        //$filter['group_by_type'] = 1;
        if (!$filter['order_by']) $filter['order_by'] = 'customer';
        if (!$filter['order_dir']) $filter['order_dir'] = 'ASC';
        if (!isset($filter['start'])) $filter['start'] = 0;
        if (!isset($filter['limit'])) $filter['limit'] = 100;
        if (!isset($filter['reload_seconds'])) $filter['reload_seconds'] = 300;

        if(isset($filter['customer_id']) && $filter['customer_id'] != COMPUTERS_FILTER_ALL)
        {
                $_SESSION['kawacs_antivirus_dashboard']['filter']['customer_id'] = $filter['customer_id'];
        }
        $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

        // Extract the list of customers
        $customers = Customer::get_customers_list ($customers_filter);
        $customers_filter_all['active'] = -1;
        $customers_all = Customer::get_customers_list ($customers_filter_all);

        $profiles = MonitorProfile::get_profiles_with_antivirus();
        $computers_red = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_ONE_WEEK, $count_r);
        $computers_orange = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_ONE_DAY, $count_o);
        $computers_green = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_SUCCESS, $count_g);
        $computers_gray = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_NOT_REPORTING, $count_gr);


        $this->assign('customers_all', $customers_all);
        $this->assign('error_msg', error_msg());
        $this->assign('profiles', $profiles);
        $this->assign('computers_red', $computers_red);
        $this->assign('computers_orange', $computers_orange);
        $this->assign('computers_green', $computers_green);
        $this->assign('computers_gray', $computers_gray);
        $this->assign('count_r', count($computers_red));
        $this->assign('count_o', count($computers_orange));
        $this->assign('count_g', count($computers_green));
        $this->assign('count_gr', count($computers_gray));
        $this->assign('sort_url', $this->mk_redir('kawacs_antivirus_dashboard_submit', $extra_params));
        $this->assign('filter', $filter);


        $this->set_form_redir('kawacs_antivirus_dashboard_submit', $extra_params);
        $this->display($tpl);

    }
    function kawacs_antivirus_dashboard_submit()
    {
        check_auth();
        $extra_params = array();

        if($this->vars['order_by'] and $this->vars['order_dir'])
        {
                $_SESSION['kawacs_antivirus_dashboard']['filter']['order_by'] = $this->vars['order_by'];
                $_SESSION['kawacs_antivirus_dashboard']['filter']['order_dir'] = $this->vars['order_dir'];
        }
        if($this->vars['filter'])
        {
                if(is_array($_SESSION['kawacs_antivirus_dashboard']['filter']))
                {
                        $_SESSION['kawacs_antivirus_dashboard']['filter'] = array_merge($_SESSION['kawacs_antivirus_dashboard']['filter'], $this->vars['filter']);
                }
                else
                {
                        $_SESSION['kawacs_antivirus_dashboard']['filter'] = $this->vars['filter'];
                }
        }

        //$_SESSION['kawacs_antivirus_dashboard']['filter'] = $this->vars['filter'];

        return $this->mk_redir('kawacs_antivirus_dashboard', $extra_params);
    }

    function kawacs_inventory_dashboard()
    {
        check_auth();
        $tpl = "kawacs_inventory_dashboard.tpl";
        $extra_params = array(); //array with additional parameters used in navigation
        if($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        class_load('Computer');
        class_load('Supplier');
        class_load('ComputerItem');


        if(!isset($_SESSION['kawacs_inventory_dashboard']['customer_id']))
                $_SESSION['kawacs_inventory_dashboard']['customer_id'] = COMPUTERS_FILTER_ALL;
        //$filter['customer_id'] = COMPUTERS_FILTER_ALL;
        $filter = $_SESSION['kawacs_inventory_dashboard'];

        //sdebug($filter);

        //$filter['group_by_type'] = 1;
        //if (!$filter['order_by']) $filter['order_by'] = 'alert_raised';
        if (!$filter['order_dir']) $filter['order_dir'] = 'DESC';
        if (!isset($filter['start'])) $filter['start'] = 0;
        if (!isset($filter['limit'])) $filter['limit'] = 100;
        if (!isset($filter['current_computer_items'])) $filter['current_computer_items'] = array(1002);
        if (!isset($filter['current_peripheral_class'])) $filter['current_peripheral_class'] = array(13);
        if (!isset($filter['reload_seconds'])) $filter['reload_seconds'] = 300;
        if (!isset($filter['itype'])) $filter['itype'] = 0;

        //debug($filter);

        //debug($_SESSION['kawacs_inventory_dashboard']);
        //debug($filter);

        if(isset($filter['customer_id']) && $filter['customer_id'] != COMPUTERS_FILTER_ALL)
        {
                $_SESSION['kawacs_inventory_dashboard']['customer_id'] = $filter['customer_id'];
        }
        $filter['customer_id'] = $_SESSION['kawacs_inventory_dashboard']['customer_id'];
        $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

        // Extract the list of customers
        $customers = Customer::get_customers_list ($customers_filter);
        $customers_filter_all['active'] = -1;
        $customers_all = Customer::get_customers_list ($customers_filter_all);

        $suppliers = Supplier::get_supplier_names();
        if(isset($filter['supplier_id']))
                $filter['search'] = $suppliers[$filter['supplier_id']];
        if($filter['itype'] == 0)
        {
                $computers = Computer::search_by_condition($filter);
                $computers_array = array();
                $items = array();
                foreach($computers as $id)
                {
                        $computers_array[] = new Computer($id);
                        if(isset($filter['current_computer_items']) && count($filter['current_computer_items']) > 0)
                        {
                                foreach ($filter['current_computer_items'] as $cid)
                                {
                                        $rgood = array();
                                        $citem = new ComputerItem($id, $cid);
                                        foreach ($citem->val as $valobj)
                                        {
                                                if(strstr($valobj->value, $filter['search']) || strstr($valobj->value, strtoupper($filter['search'])))
                                                {
                                                        $rgood = array('item_id'=>$cid, 'item_name'=>$citem->itemdef->name, 'value'=>$valobj->value);
                                                }
                                        }
                                        if(count($rgood) != 0)
                                                $items[$id][] = $rgood;
                                }
                        }

                }
                $count = count($computers_array);
        }
        else if($filter['itype'] == 1)
        {
                class_load('Peripheral');
                $peripherals = Peripheral::search_by_condition($filter);
                //debug($computers);
                $peripherals_array = array();
                foreach($peripherals as $id)
                {
                        $peripherals_array[] = new Peripheral($id);
                }
                $count = count($peripherals_array);
        }
        else if($filter['itype'] == 2)
        {
                class_load('AD_Printer');
                $ad_printers = AD_Printer::get_ad_printers_by_condition($filter);
                //debug($computers);
                $count = count($ad_printers);
        }

        //debug($ad_printers);
        //debug($peripherals_array);

        $this->assign('customers_all', $customers_all);
        $this->assign('error_msg', error_msg());
        $this->assign('suppliers', $suppliers);
        $this->assign('computers_array', $computers_array);
        $this->assign('peripherals_array', $peripherals_array);
        $this->assign('ad_printers', $ad_printers);
        $this->assign('items', $items);

        $this->assign('count',$count);
        $this->assign('filter', $filter);


        $this->set_form_redir('kawacs_inventory_dashboard_submit', $extra_params);
        $this->display($tpl);
    }
    function kawacs_inventory_dashboard_submit()
    {
        check_auth();
        $extra_params = array();

        $_SESSION['kawacs_inventory_dashboard'] = $this->vars['filter'];
        if($this->vars['advanced'])
                return $this->mk_redir('inventory_search_advanced', $extra_params);


        return $this->mk_redir('kawacs_inventory_dashboard', $extra_params);
    }

    function inventory_search_advanced()
    {
        check_auth();
        $tpl = "inventory_search_advanced.tpl";
        $extra_params = array(); //array with additional parameters used in navigation
        if($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        class_load('MonitorItem');
        $filter = $_SESSION['kawacs_inventory_dashboard'];
        $filter['customer_id'] = $_SESSION['kawacs_inventory_dashboard']['customer_id'];

        if($filter['itype'] == 0)
        {
                $mitems['available'] = MonitorItem::get_monitor_items_list();
                if($filter['current_computer_items'])
                        $mitems['current'] = MonitorItem::get_monitor_items_list_from_ids_array($filter['current_computer_items']);
        }
        else if($filter['itype']==1)
        {
                class_load('PeripheralClass');
                $mitems['available'] = PeripheralClass::get_classes_list();
                if($filter['current_peripheral_class'])
                        $mitems['current'] = PeripheralClass::get_classes_list_from_ids_array($filter['current_peripheral_class']);
        }
        //else if($filter['itype'] == 2){
                //$mitems['available'] = array();
                //$mitems['current'] = array();
        //}
        $this->assign("mitems",$mitems);
        $this->assign("filter", $filter);
        $this->assign("error_msg", error_msg());
        $this->set_form_redir('kawacs_inventory_dashboard_submit', $extra_params);
        $this->display($tpl);
    }
    function check_rbl_listed_servers()
    {
        check_auth();
        $tpl = 'check_rbl_listed_servers.tpl';
        class_load("Customer");
        class_load("MonitorProfile");
        class_load("Rbl");

        $filter = array('customer_id' =>-1);

        if($_SESSION['chceck_rbl_listed_servers']['filter'])
        {
          $filter = $_SESSION['chceck_rbl_listed_servers']['filter'];
        }

        if($this->vars['id']) $filter['customer_id'] = $this->vars['id'];
        if(!isset($filter['customer_id'])) $filter['customer_id'] = -1;

        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);

        //debug($customers_list);

        $stats = array();
        $rbl = null;
        if($filter[$customer_id] != -1)
        {
          if($filter['customer_id'])
                  $rbl = new Rbl($filter['customer_id']);
          else
                  $rbl = new Rbl();

          $stats = $rbl->get_statuses();
        }

        //debug($stats);

        //$profiles = MonitorProfile::get_profiles_list ();

        $this->assign('customers_list', $customers_list);
        $this->assign('stats', $stats);
        $this->assign('filter', $filter);
        //$this->assign('profiles', $profiles);
        $this->assign('error_msg', $error_msg);
        $this->set_form_redir('check_rbl_listed_servers_submit');
        $this->display($tpl);

    }
    function check_rbl_listed_servers_submit()
    {
        check_auth();
        $_SESSION['chceck_rbl_listed_servers']['filter'] = $this->vars['filter'];
        //debug($_SESSION['chceck_rbl_listed_servers']);
        return $this->mk_redir('check_rbl_listed_servers');

    }

    function manage_mremote_connections()
    {
        class_load('mRemoteConnection');
        check_auth();
        $tpl = 'manage_mremote_connections.tpl';

        $filter = array('customer_id' =>-1);

        if(isset($_SESSION['manage_mremote_connections']['filter']))
        {
          $filter = $_SESSION['manage_mremote_connections']['filter'];
        }

        if($this->vars['id']) $filter['customer_id'] = $this->vars['id'];
        if(!isset($filter['customer_id'])) $filter['customer_id'] = -1;

        $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);

        $nodes = array();
        $roots = array();
        if($filter['customer_id'] != -1)
        {
          if(!$filter['customer_id'])
          {
              $roots = mRemoteConnection::getRootNodes();
          }
          else
          {
              $roots = mRemoteConnection::getRootNodes($filter['customer_id']);
          }
        }
        if($_SESSION['manage_mremote_connections']['xml'])
        {
                $xml=$_SESSION['manage_mremote_connections']['xml'];
                $_SESSION['manage_mremote_connections']['xml'] = '';
        }
        else $xml = '';
        foreach($roots as $r)
        {
                $childs = $r->getAllChildren();
                $nodes = array_merge($nodes, array($r));
                $nodes = array_merge($nodes, $childs);
        }

        //debug($nodes);

        $this->assign('customers_list', $customers_list);
        $this->assign('nodes', $nodes);
        $this->assign('error_msg', $error_msg);
        $this->assign('xml_config',$xml);
        $this->assign('filter', $filter);
        $this->assign('MREMOTE_PROTOCOLS', $GLOBALS['MREMOTE_PROTOCOLS']);
        $this->assign('MREMOTE_CONNECTION_TYPES', $GLOBALS['MREMOTE_CONNECTION_TYPES']);

        $this->set_form_redir('manage_mremote_connections_submit');
        $this->display($tpl);
    }
    function manage_mremote_connections_submit()
    {
        check_auth();
        if($this->vars['generate'])
        {
              class_load('mRemoteConnection');
              $xml = mRemoteConnection::getXMLHeader();
              $nodes = array();
              $customer_id = $this->vars['filter']['customer_id'];
              if($customer_id != -1)
              {
                if(!$customer_id)
                {
                    $roots = mRemoteConnection::getRootNodes();
                }
                else
                {
                    $roots = mRemoteConnection::getRootNodes($customer_id);
                }
          }

          foreach($roots as $r)
          {
                  $xml.=$r->getConnectionXML();
          }
          $xml.= mRemoteConnection::getXMLFooter();
          $output_file = tempnam(DIR_EXPORT_XML_MREMOTE, "confCons");
          $output_file.=".xml";
          $oname =  substr(strrchr($output_file, '/'), 1);
          if(file_put_contents($output_file, $xml))
            $_SESSION['manage_mremote_connections']['xml'] = BASE_URL."/files/mremote/".$oname;
        }
        $_SESSION['manage_mremote_connections']['filter'] = $this->vars['filter'];
        $ret = $this->mk_redir('manage_mremote_connections');
        //debug($xml);
        return $ret;
    }
    
    /******************************* AUTOMATIC DEPLOYER RELATED *************************************/
    
    function create_kawacs_agent_deployer(){
        check_auth();
        class_load('User');
        class_load('Customer');
        class_load('MonitorProfile');
        class_load('KawacsAgentUpdate');
        $tpl = "create_kawacs_agent_deployer.tpl";
        $current_user = new User();
        $current_user = $this->current_user;
        if( ! $current_user or ! $current_user->id ) $this->mk_redir('manage_computers');                
        if( ! $current_user->is_customer_user())
        {
            if(isset($_SESSION['create_kawacs_agent_deployer'])){
                $sel_customer = $_SESSION['create_kawacs_agent_deployer']['script_customer'];
                $sel_profile = $_SESSION['create_kawacs_agent_deployer']['script_monitor_profile'];
                $sel_type = $_SESSION['create_kawacs_agent_deployer']['script_computer_type'];
                $sel_repint = $_SESSION['create_kawacs_agent_deployer']['script_report_interval'];
                unset($_SESSION['create_kawacs_agent_deployer']);
            }
            if( ! isset($sel_profile)) $sel_profile = MONITOR_PROFILE_WORKSTATION;
            if( ! isset($sel_type)) $sel_type = COMP_TYPE_WORKSTATION;
            if( ! isset($sel_repint)) $sel_repint = 120;

            $existing_deployers = array();
            if($sel_customer > 0){
                $existing_deployers = KawacsAgentUpdate::get_deployers($sel_customer);
                $this->assign('depl_count', count($existing_deployers));
                $this->assign('existing_deployers', $existing_deployers);
            }

            $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list($customers_filter);
            $monitor_profiles = MonitorProfile::get_profiles_list();
            $prf = MonitorProfile::get_profiles();
            $profiles_times = array();
            foreach ($prf as $p){
                $profiles_times[$p->id] = $p->report_interval;
            }
            $computer_types = $GLOBALS['COMP_TYPE_NAMES'];
            
            //get the installer for the last stable version of the KawacsAgent
            
            $current_release = KawacsAgentUpdate::get_current_release();
            $installer_url = 'http://'.$current_release->get_installer_url();
            $this->assign('sel_customer', $sel_customer);
            $this->assign('sel_profile', $sel_profile);
            $this->assign('sel_type', $sel_type);
            $this->assign('sel_repint', $sel_repint);
            $this->assign('installer_url', $installer_url);
            $this->assign('computer_types', $computer_types);
            $this->assign("monitor_profiles", $monitor_profiles);
            $this->assign("lenpt", count($profiles_times));
            $this->assign("profiles_times", $profiles_times);
            $this->assign('customer_template', false);
            $this->assign('customers_list', $customers_list);
        }


        $this->assign("error_msg", error_msg());
        $this->set_form_redir('create_kawacs_agent_deployer_submit');
        $this->display($tpl);
    }
    function create_kawacs_agent_deployer_submit(){
            check_auth();
            
            if($this->vars['cancel']){
                return $this->mk_redir('user_area', array(), 'home');
            }
            if(isset($_SESSION['create_kawacs_agent_deployer'])) unset($_SESSION['create_kawacs_agent_deployer']);
            if($this->vars['script_customer'] < 0){
                $_SESSION['create_kawacs_agent_deployer'] = $this->vars;
                error_msg("You must select the customer for which this script is generated");
                return $this->mk_redir('create_kawacs_agent_deployer');
            }
            else{
                if(isset($this->vars['chgcust']) and $this->vars['chgcust'] != ""){
                        // debug($this->vars);
                        $_SESSION['create_kawacs_agent_deployer'] = $this->vars;
                        $_SESSION['create_kawacs_agent_deployer']['script_customer'] = $this->vars['chgcust'];
                        return $this->mk_redir('create_kawacs_agent_deployer');
                }
                if($this->vars['save']){
                    //here we generate the vbs installer
                    $vbs_tpl = "kawacs/kawacs_agent_deploy_script.tpl";
                    $this->assign('script_customer_id', $this->vars['script_customer']);
                    $this->assign('script_server_url', $this->vars['script_server_url']);
                    $this->assign('script_monitor_profile', $this->vars['script_monitor_profile']);
                    $this->assign('script_computer_type', $this->vars['script_computer_type']);
                    $this->assign('script_report_interval', $this->vars['script_report_interval']);
                    $this->assign('installer_url', $this->vars['script_installer_url']);
                    
                    $script = $this->fetch($vbs_tpl);
                    
                    $dest_dir = DIR_AGENT_DEPLOYER . "/" . $this->vars['script_customer'];
                    if( ! is_dir($dest_dir)){
                        $dircreated = mkdir($dest_dir, 0777);
                        if( ! $dircreated ) {
                            error_msg("There was a problem during the script generation, please try again in a few minutes....");
                            $_SESSION['create_kawacs_agent_deployer'] = $this->vars;
                            return $this->mk_redir('create_kawacs_agent_deployer');
                        }
                    }
                    $script_name = $dest_dir . "/" . "kawacs_deployer_" . $this->vars['script_customer'] . "_" . $this->vars['script_monitor_profile'] . "_" . $this->vars['script_computer_type'] . ".vbs";
                    if (file_exists($script_name)) @unlink($script_name);
                    file_put_contents($script_name, $script);
                    $_SESSION['create_kawacs_agent_deployer']['script_customer'] = $this->vars['script_customer'];
                    //return $this->mk_redir('create_kawacs_agent_deployer');
                    if (file_exists($script_name)){
                        //return $this->mk_redir('get_deployer', array('admrd'=>1,'customer_id'=>$this->vars['script_customer'], 'profile'=>$this->vars['script_monitor_profile'], 'type'=>$this->vars['script_computer_type']));
                        return $this->mk_redir('create_kawacs_agent_deployer');
                    }
                    else{
                        error_msg("There was a problem during the script generation, please try again in a few minutes....");
                        $_SESSION['create_kawacs_agent_deployer'] = $this->vars;
                        return $this->mk_redir('create_kawacs_agent_deployer');
                    }
                }
            }
    }

    function get_deployer(){              
        if(isset($this->vars['customer_id']) and !isset($this->vars['profile']) and !isset($this->vars['type'])){                
            $this->vars['profile'] = MONITOR_PROFILE_WORKSTATION;
            $this->vars['type'] = COMP_TYPE_WORKSTATION; 
        }                 
                    
        if(isset($this->vars['customer_id']) and isset($this->vars['profile']) and isset($this->vars['type'])){        
            $_SESSION['create_kawacs_agent_deployer']['script_customer'] = $this->vars['customer_id'];
            
            $dest_path = DIR_AGENT_DEPLOYER . '/' . $this->vars['customer_id'] . '/kawacs_deployer_' . $this->vars['customer_id'] . '_' . $this->vars['profile'] . '_' . $this->vars['type'] . '.vbs';
            if( ! file_exists($dest_path) )
            {
                
                class_load('KawacsAgentUpdate');
                //this file was not created yet so we should generate it
                $vbs_tpl = "kawacs_agent_deploy_script.tpl";
                $this->assign('script_customer_id', $this->vars['customer_id']);
                $this->assign('script_server_url', KEYOS_KAWACS_SERVER);
                $this->assign('script_monitor_profile', $this->vars['profile']);
                $this->assign('script_computer_type', $this->vars['type']);
                $this->assign('script_report_interval', 120);
                $current_release = KawacsAgentUpdate::get_current_release();
                $this->assign('installer_url', 'http://'.$current_release->get_installer_url());
                
                $script = $this->fetch($vbs_tpl);
                
                $dest_dir = DIR_AGENT_DEPLOYER . "/" . $this->vars['customer_id'];
                if( ! is_dir($dest_dir)){
                    $dircreated = mkdir($dest_dir, 0777);
                    if( ! $dircreated ) {
                        error_msg("There was a problem during the script generation, please try again in a few minutes....");                            
                        return $this->mk_redir('create_kawacs_agent_deployer');
                    }
                }
                $script_name = $dest_dir . "/" . "kawacs_deployer_" . $this->vars['customer_id'] . "_" . $this->vars['profile'] . "_" . $this->vars['type'] . ".vbs";
                if (file_exists($script_name)) @unlink($script_name);
                file_put_contents($script_name, $script);
            }
            if(file_exists($dest_path)){
                $title =  'kawacs_deployer_' . $this->vars['customer_id'] . '_' . $this->vars['profile'] . '_' . $this->vars['type'];
                $filesize=filesize($dest_path);
                header("Pragma: public");
                header("Expires: 0"); // set expiration time
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/vbs");
                header("Content-Length: ".$filesize);
                header("Content-Disposition: attachment; filename=\"".strip_tags($title).".vbs\"");
                header("Content-Transfer-Encoding: binary");
                readfile($dest_path);
            }
        }     
        
        if($this->vars['admrd']){
            return $this->mk_redir('create_kawacs_agent_deployer');
        }
        die;
    }
    
    /************************************************************************************************/

    /*****************************CENTREON INTERCOMMUNICATION****************************************/

    function hostcentreon_lim_view(){
        class_load('Computer');
        class_load('Peripheral');

        check_auth(array('id' => $this->vars['id']));

        //get the first character out and see if it's a computer or a peripheral
        $host_type = substr($this->vars['id'], 0, 1);
        $host_id = substr($this->vars['id'], 1);
        if((strtolower($host_type) != 'c') and (strtolower($host_type) != 'p')){
            error_msg('Undefined Host Type!');
        }
        switch(strtolower($host_type)){
            case 'c':
                //we need a computer
                $tpl = 'hostcentreon_computer_limited.tpl';
                $computer = new Computer($host_id);
                if(!$computer->id){
                    //no such machine in keyos -> we should redirect somewhere
                    error_msg('A computer with this ID was not found!');
                    die;
                }
                class_load ('Customer');
		class_load ('Ticket');
		class_load ('Peripheral');
		class_load ('ComputerBlackout');
		class_load ('ComputerNote');
		class_load ('MonitoredIP');
		class_load ('Discovery');
		class_load ('DiscoverySettingDetail');
                class_load ('ComputerItem');
                class_load ('MonitorProfile');
		class_load ('CustomerPhoto');
		class_load ('Supplier');

                $monitored_ip = MonitoredIP::get_by_remote_ip ($computer->remote_ip);
		if ($monitored_ip->id)
		{
			$monitored_ip->load_customer ();
			$monitored_ip->load_notification ();
		}

		// Check alerts
		$computer->check_monitor_alerts ();
		$notifications = $computer->get_notifications ();
		$notifications_tickets = array();
		if ($monitored_ip->notification->id) $notifications_tickets[$monitored_ip->notification->ticket_id] = new Ticket($monitored_ip->notification->ticket_id);

		// Mark all associated notifications as being read and update the counter
		if (count($notifications) > 0)
		{
			foreach ($notifications as $notification) $notification->mark_read ($this->current_user->id);
			$this->update_unread_notifs ();
		}

		// Check notifications that have associated tickets
		for ($i=0; $i<count($notifications); $i++)
		{
			$notifications_tickets[$notifications[$i]->ticket_id] = new Ticket ($notifications[$i]->ticket_id);
		}

		// Get the list of tickets related to this computer and eliminate from list the tickets from notifications
		$all_tickets = Ticket::get_computer_tickets ($computer->id);
		$tickets = array ();
		for ($i = 0; $i<count($all_tickets); $i++)
		{
			if (!isset($notifications_tickets[$all_tickets[$i]->id])) $tickets[] = $all_tickets[$i];
		}
		unset ($all_tickets);

		// Get the tickets history for this computer
		$tickets_history = Ticket::get_computer_tickets_history ($computer->id);

		// Check what is being logged (determined by profile settings)
		if ($computer->profile_id)
		{
			$profile_items = MonitorProfile::get_profile_items_list ($computer->profile_id);
			$is_logging_partitions = ($profile_items[PARTITIONS_ITEM_ID] > MONITOR_LOG_NONE);
			$is_logging_backup = ($profile_items[BACKUP_STATUS_ITEM_ID] > MONITOR_LOG_NONE);
			$is_logging_av = ($profile_items[AV_STATUS_ITEM_ID] > MONITOR_LOG_NONE);

			$is_requesting_events = isset($profile_items[EVENTS_ITEM_ID]);
		}

		$computer_peripherals = Peripheral::get_peripherals (array('computer_id' => $computer->id));
		$peripherals_classes_list = PeripheralClass::get_classes_list ();

		// Get the list of notes for this computer
		$notes = ComputerNote::get_computer_notes ($computer->id);

		// Get the users list
		$users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
		$groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
		$users_list = $users + $groups;
		$users_logins_list = User::get_logins_list (array('type' => USER_TYPE_KEYSOURCE));

		// Check to see if the computer is blacked out
		$blackout = new ComputerBlackout ($computer->id);
		if ($blackout->computer_id) $this->assign ('blackout', $blackout);

		// See if there are any photos, roles and location for this computer
		$computer->load_photos ();
		$computer->load_roles ();
		$computer->load_location ();

		// Load local IP addresses for this computer
		$ips = array ();
		$adapters = $computer->get_item('net_adapters');
		$net_adapters_field_id = $computer->get_item_id ('net_adapters');
		$ip_field_id = $computer->get_item_id('ip_address', $net_adapters_field_id);
		$adapter_name_id = $computer->get_item_id('name', $net_adapters_field_id);
		for ($i=0; $i<count($adapters); $i++)
		{
			if ($adapters[$i][$ip_field_id] and $adapters[$i][$ip_field_id] != '0.0.0.0')
			{
				$ips[] = array('ip'=>trim($adapters[$i][$ip_field_id]), 'adapter'=>trim($adapters[$i][$adapter_name_id]));
			}
		}

		// See if this computer is matched by any of the networks discoveries
		$discoveries = Discovery::get_matches_for_computer ($computer->id);
		$disc_details = array ();
		foreach ($discoveries as $discovery)
		{
			if (!isset($disc_details[$discovery->detail_id])) $disc_details[$discovery->detail_id] = new DiscoverySettingDetail ($discovery->detail_id);
		}

		$stolen_computer = Computer::is_computer_stolen($computer->id);
		// Load the reported data
		$items = $computer->get_reported_items();

		$this->assign ('computer', $computer);
		$this->assign ('customer', new Customer($computer->customer_id));
		$this->assign ('tickets', $tickets);
		$this->assign ('tickets_history', $tickets_history);
		$this->assign ('notifications', $notifications);
		$this->assign ('notifications_tickets', $notifications_tickets);
		$this->assign ('notes', $notes);
		$this->assign ('users_list', $users_list);
		$this->assign ('users_logins_list', $users_logins_list);

		$this->assign ('items', $items);
		$this->assign ('is_logging_partitions', $is_logging_partitions);
		$this->assign ('is_logging_backup', $is_logging_backup);
		$this->assign ('is_logging_av', $is_logging_av);
		$this->assign ('is_requesting_events', $is_requesting_events);
		$this->assign ('computer_peripherals', $computer_peripherals);
		$this->assign ('peripherals_classes_list', $peripherals_classes_list);
		$this->assign ('monitored_ip', $monitored_ip);
		$this->assign ('ips', $ips);

		$this->assign ('discoveries', $discoveries);
		$this->assign ('disc_details', $disc_details);

		$this->assign ('stolen_computer', $stolen_computer);

		$this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
		$this->assign ('ALERT_NAMES', $GLOBALS['ALERT_NAMES']);
		$this->assign ('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
		$this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
		$this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
		$this->assign ('MONITOR_STATS', $GLOBALS['MONITOR_STATS']);
		$this->assign ('profiles_list', MonitorProfile::get_profiles_list());
		$this->set_form_redir ('hostcentreon_lim_view_submit', array ('id' => $computer->id, 'host_type' => 'computer'));

		$this->display_template_only($tpl);

                break;
            case 'p':
                $tpl = 'hostcentreon_peripheral_limited.tpl';
                
		class_load ('Customer');
		class_load ('Supplier');
		class_load ('Discovery');
		class_load ('DiscoverySettingDetail');


		$peripheral = new Peripheral($host_id);
		$customer = new Customer ($peripheral->customer_id);
		$peripheral_class = new PeripheralClass ($peripheral->class_id);

		check_auth (array('customer_id' => $customer->id));

		// Load the previously submitted data, in case there was an error
		$peripheral_data = array ();
		if (!empty_error_msg()) restore_form_data ('peripheral', false, $peripheral_data);
		$peripheral->load_from_array ($peripheral_data, true);

		$computers_list = Computer::get_computers_list (array ('customer_id' => $peripheral->customer_id));
		$available_computers_list = $computers_list;
		foreach ($available_computers_list as $id => $name)
		{
			if (in_array ($id, $peripheral->computers)) unset ($available_computers_list[$id]);
		}

		$peripheral->load_photos ();
		$peripheral->load_location ();

		// SNMP-related information
		$snmp_computer = $peripheral->get_snmp_computer ();
		$profile = $peripheral->get_monitoring_profile ();

		$params = $this->set_carry_fields (array('id', 'ret', 'returl'));

		// Load any related notifications
		$notifications = Notification::get_notifications (array('object_class' => NOTIF_OBJ_CLASS_PERIPHERAL, 'object_id'=>$peripheral->id));

		// Load any matched devices from networks discoveries
		$discoveries = Discovery::get_matches_for_peripheral ($peripheral->id);
		$disc_details = array ();
		foreach ($discoveries as $discovery)
		{
			if (!isset($disc_details[$discovery->detail_id])) $disc_details[$discovery->detail_id] = new DiscoverySettingDetail ($discovery->detail_id);
		}

		$this->assign ('peripheral', $peripheral);
		$this->assign ('peripheral_class', $peripheral_class);
		$this->assign ('notifications', $notifications);
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('available_computers_list', $available_computers_list);
		$this->assign ('service_packages_list', SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true)));
		$this->assign ('service_levels_list', ServiceLevel::get_service_levels_list ());
		$this->assign ('snmp_computer', $snmp_computer);
		$this->assign ('profile', $profile);
		$this->assign ('discoveries', $discoveries);
		$this->assign ('disc_details', $disc_details);
		$this->assign ('CRIT_MEMORY_MULTIPLIERS_NAMES', $GLOBALS['CRIT_MEMORY_MULTIPLIERS_NAMES']);
		$this->assign ('error_msg', error_msg ());
                $params['host_type'] = 'peripheral';
		$this->set_form_redir ('hostcentreon_lim_view_submit', $params);

		$this->display_template_only($tpl);
                break;
            default:
                //host is not one of the characters that we expected, try to determine it
                //if a computer with that id is not available, try to see if a peripheral exists -> in this order

                break;
        }

    }
    function hostcentreon_lim_view_submit(){

    }

    /************************************************************************************************/

}
?>

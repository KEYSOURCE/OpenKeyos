<?php
class_load ('Computer');
class_load ('Customer');
class_load ('User');
class_load ('Group');

class CustomerKawacsController extends PluginController{
    protected $plugin_name = 'CustomerKAWACS';
    public function __contstruct(){
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    /**
     * Show a page with a all the back-up statuses of the computers this users is allowed to view
     *
     */
    function kawacs_backup_dashboard()
    {
        $uid = get_uid();
        if($uid)
        {
            $user = New User($uid);
            if(!$user->customer_id)
            {
                    return $this->mk_redir('kawacs_backup_dashboard', array(), 'kawacs');
            }
            if(!$user->allow_dashboard)
            {
                    return $this->mk_redir('user_area', array(), $home);
            }
        }
        check_auth();
        $tpl = 'kawacs_backup_dashboard.tpl';	
        $filter = $_SESSION['backup_dashboard_customer']['filter'];
        $current_user = new User (get_uid());
        $filter['customer_id'] = $current_user->customer_id;

        if (!$current_user->allow_private) $filter['private'] = 0;

        $extra_params = array(); //array with additional parameters used in navigation
        if($this->vars['do_filter']) $extra_params['do_filter'] = 1;

        class_load('Computer');
        class_load('MonitorProfile');

        //$filter['group_by_type'] = 1;

        if (!$filter['order_by']) $filter['order_by'] = 'alert_raised';
        if (!$filter['order_dir']) $filter['order_dir'] = 'DESC';
        if (!isset($filter['start'])) $filter['start'] = 0;
        if (!isset($filter['limit'])) $filter['limit'] = 100;

        $profiles = MonitorProfile::get_profiles_with_backup();


        $computers_green = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_SUCCESS, $count_g);
        $computers_red = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_ERROR, $count_r);
        $computers_orange = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_TAPE_ERROR, $count_o);
        $computers_grey = Computer::get_computers_backup_statuses($filter, BACKUP_STATUS_NOT_REPORTING, $count_gr);

        $total_cb = count($computers_red) + count($computers_orange) + count($computers_green) + count($computers_grey);
        if($total_cb)
        {
            $perc_red = (count($computers_red) / $total_cb) * 100;
            $perc_orange = (count($computers_orange) / $total_cb) *100;
            $perc_green = (count($computers_green) / $total_cb) * 100;
            $perc_grey = (count($computers_grey)/ $total_cb) * 100;
        }

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
        $this->assign('profiles', $profiles);
        $this->assign('filter', $filter);
        $this->set_form_redir('kawacs_backup_dashboard_submit', $extra_params);



        $this->assign('error_msg', error_msg());

        $this->display($tpl);
    }
    function kawacs_backup_dashboard_submit()
    {
        check_auth();
        $extra_params = array();

        $_SESSION['backup_dashboard_customer']['filter'] = $this->vars['filter'];
        return $this->mk_redir('kawacs_backup_dashboard', $extra_params);
    }
    
    
    /**
     * display a page with the antivirus statuses of all the computers belonging to the firm where this customer works
     * which have antivirus reporting in the profile
     * @return unknown
     */
    function kawacs_antivirus_dashboard()
    {
        $uid = get_uid();
        if($uid)
        {
            $user = New User($uid);
            if(!$user->customer_id)
            {
                return $this->mk_redir('kawacs_antivirus_dashboard', array(), 'kawacs');
            }
            if(!$user->allow_dashboard)
            {
                return $this->mk_redir('user_area', array(), $home);
            }
        }
        check_auth();
        $tpl = 'kawacs_antivirus_dashboard.tpl';	
        class_load('Computer');
        class_load('MonitorProfile');

        $filter = $_SESSION['antivirus_dashboard_customer']['filter'];
        $filter['customer_id'] = $user->customer_id;

        if (!$filter['order_by']) $filter['order_by'] = 'alert_raised';
        if (!$filter['order_dir']) $filter['order_dir'] = 'DESC';
        if (!isset($filter['start'])) $filter['start'] = 0;
        if (!isset($filter['limit'])) $filter['limit'] = 100;
        if (!isset($filter['reload_seconds'])) $filter['reload_seconds'] = 300;

        $profiles = MonitorProfile::get_profiles_with_antivirus();
        $computers_red = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_ONE_WEEK, $count_r);
        $computers_orange = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_ONE_DAY, $count_o);
        $computers_green = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_SUCCESS, $count_g);
        $computers_gray = Computer::get_computers_antivirus_statuses($filter, ANTIVIRUS_UPD_NOT_REPORTING, $count_gr);

        $total_av = count($computers_red) + count($computers_orange) + count($computers_green) + count($computers_gray);
        if($total_av)
        {
            $perc_red = (count($computers_red) / $total_av) * 100;
            $perc_orange = (count($computers_orange) / $total_av) *100;
            $perc_green = (count($computers_green) / $total_av) * 100;
            $perc_grey = (count($computers_gray)/ $total_av) * 100;	
        }

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
        $this->assign('perc_red', $perc_red);
        $this->assign('perc_orange', $perc_orange);
        $this->assign('perc_green', $perc_green);
        $this->assign('perc_grey', $perc_grey);
        $this->assign('filter', $filter);

        $this->set_form_redir('kawacs_antivirus_dashboard_submit');


        $this->display($tpl);

    }
    function kawacs_antivirus_dashboard_submit()
    {
        check_auth();
        $_SESSION['antivirus_dashboard_customer']['filter'] = $this->vars['filter'];
        return $this->mk_redir('kawacs_antivirus_dashboard');

    }

}

?>

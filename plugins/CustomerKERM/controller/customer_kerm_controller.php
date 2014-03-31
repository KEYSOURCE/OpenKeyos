<?php

class_load('KermADUser');
class_load('KermADGroup');
class_load('User');
class_load('Customer');

class CustomerKermController extends PluginController{
    protected $plugin_name = "CustomerKERM";
    function __construct(){
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    function manage_users(){
        $uid = get_uid();
        if($uid)
        {
            $user = new User($uid);
            if(!$user->customer_id)
            {
                return $this->mk_redir('customer_added_users', array(), "kerm");
            }
        }
        check_auth();
        $tpl = "manage_users.tpl";

        $filter = $_SESSION['customer_kerm_manage_users']['filter'];

        if (!isset ($filter['order_by'])) $filter['order_by'] = 'status';
        if (!isset ($filter['order_dir'])) $filter['order_dir'] = 'DESC';
        if (!isset ($filter['start'])) $filter['start'] = 0;
        if (!isset ($filter['limit'])) $filter['limit'] = 10;
        if (!isset ($filter['status'])) $filter['status'] = -1;

        $current_user = new User($uid);
        $customers = Customer::get_customers_list();
        $assigned_customers = $current_user->get_users_customer_list();
        $assigned_customers_count = count($assigned_customers);
        $filter['customers'] = $assigned_customers;
        $users = KermADUser::get_users_list($filter);

        $customers_list = array();
        foreach ($assigned_customers as $ac)
        {
            $customers_list[$ac] = $customers[$ac];
        }

        if (count($users) < $filter['start'])
        {
            $filter['start'] = 0;
            $_SESSION['customer_kerm_manage_users']['filter']['start'] = 0;
            $users = KermADUser::get_users_list($filter);
        }
        $pages = make_paging ($filter['limit'], count($users));

        $this->assign('pages', $pages);
        $this->assign('filter', $filter);
        $this->assign('tot_users', count($users));
        $this->assign('users', $users);
        $this->assign('customers_list', $customers_list);
        $this->assign('sort_url', $this->mk_redir('manage_users_submit'));
        $this->assign('assigned_customers_count', $assigned_customers_count);	
        $this->assign("USERS_STATUSES", $GLOBALS['CUSTOMER_KERM_USERS_STATUSES']);	
        $this->assign("error_msg", error_msg());
        $this->set_form_redir('manage_users_submit');
        $this->display($tpl);
    }
    
    function manage_users_submit(){
        check_auth();
        if ($this->vars['order_by'] and $this->vars['order_dir'])
        {
            $_SESSION['customer_kerm_manage_users']['filter']['order_by'] = $this->vars['order_by'];
            $_SESSION['customer_kerm_manage_users']['filter']['order_dir'] = $this->vars['order_dir'];
        }
        else
        {
            if ($this->vars['go'] == 'prev' or $this->vars['go'] == 'next')
            {
                $this->vars['filter']['start']+= $this->vars['filter']['limit'] * ($this->vars['go'] == 'prev' ? -1 : 1);
            }

            if (is_array($_SESSION['customer_kerm_manage_users']['filter']))
            {
                $_SESSION['customer_kerm_manage_users']['filter'] = array_merge($_SESSION['customer_kerm_manage_users']['filter'], $this->vars['filter']);
            }
            else
            {
                $_SESSION['customer_kerm_manage_users']['filter'] = $this->vars['filter'];
            }
        }

        return $this->mk_redir('manage_users');
    }
    
    function add_user()
    {
        $uid = get_uid();
        if($uid)
        {
            $user = new User($uid);
            if(!$user->customer_id)
            {
                    return $this->mk_redir('manage_ad_users', array(), 'kerm');
            }
        }
        check_auth();
        $aduser = array();
        if($_SESSION['customer_kerm_add_user']['aduser'])
                $aduser = $_SESSION['customer_kerm_add_user']['aduser'];
        $tpl = "add_user.tpl";
       
        $current_user = new User($uid);
        $customers = Customer::get_customers_list();
        $assigned_customers = $current_user->get_users_customer_list();
        $assigned_customers_count = count($assigned_customers);
        $customers_list = array();
        $groups_list = array();

        $fc = $assigned_customers[0];
        if($aduser['customer_id'])	$fc = $aduser['customer_id'];
        $domains = KermADUser::get_available_domains($fc);
        foreach ($assigned_customers as $ac)
        {
            $customers_list[$ac] = $customers[$ac];
            $groups_list[$ac] = KermADGroup::get_groups_list($ac);
        }

        $this->assign('fc', $fc);
        $this->assign('domains', $domains);
        $this->assign('aduser', $aduser);
        $this->assign('groups_list', $groups_list);
        $this->assign('assigned_customers', $assigned_customers);
        $this->assign('assigned_customers_count', $assigned_customers_count);
        $this->assign('customers_list', $customers_list);
        $this->assign('tot_cust', $tot_cust);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('add_user_submit');
        $this->display($tpl);

    }
    function add_user_submit()
    {
        unset($_SESSION['customer_kerm_add_user']);
        $_SESSION['customer_kerm_add_user']['aduser'] = $this->vars['aduser'];
        if($this->vars['cancel']) 
        {
            unset($_SESSION['customer_kerm_add_user']);
            return $this->mk_redir('manage_users');
        }
        if($this->vars['save'])
        {
            $aduser = $this->vars['aduser'];
            $ad_usr = new KermADUser();
            $ad_usr->load_from_array($aduser);
            $ad_grp = new KermADGroup($aduser['GroupName']);
            $ad_usr->status = CKERM_STATUS_NEW;
            $ad_usr->GroupName = $ad_grp->name;
            $ad_usr->Email .= $aduser['Domain']; 
            if($ad_usr->is_valid_data())
            {
                $ad_usr->save_data();
                unset($_SESSION['customer_kerm_add_user']);
            }
        }
        return $this->mk_redir('add_user');

    }

    function modify_user()
    {
        $uid = get_uid();
        if($uid)
        {
            $user = new User($uid);
            if(!$user->customer_id)
            {
                    return $this->mk_redir('customer_added_users', array(), 'kerm');
            }
        }
        check_auth(array('id'=>$this->vars['id']));

        $aduser = new KermADUser($this->vars['id']);
        if(!$aduser->id) return $this->mk_redir('manage_users');


        if($_SESSION['customer_kerm_modify_user']['aduser'])
                $aduser->load_from_array($_SESSION['customer_kerm_modify_user']['aduser']);
        $tpl = "modify_user.tpl";

        $current_user = new User($uid);
        $customers = Customer::get_customers_list();
        $assigned_customers = $current_user->get_users_customer_list();
        $assigned_customers_count = count($assigned_customers);
        $customers_list = array();
        $groups_list = array();

        $fc = $assigned_customers[0];
        $domains = KermADUser::get_available_domains($fc);
        if($aduser->customer_id)	$fc = $aduser->customer_id;
        foreach ($assigned_customers as $ac)
        {
            $customers_list[$ac] = $customers[$ac];
            $groups_list[$ac] = KermADGroup::get_groups_list($ac);
        }
        $this->assign('domains', $domains);
        $this->assign('fc', $fc);
        $this->assign('aduser', $aduser);
        $this->assign('groups_list', $groups_list);
        $this->assign('assigned_customers', $assigned_customers);
        $this->assign('assigned_customers_count', $assigned_customers_count);
        $this->assign("USERS_STATUSES", $GLOBALS['CUSTOMER_KERM_USERS_STATUSES']);
        $this->assign('customers_list', $customers_list);
        $this->assign('tot_cust', $tot_cust);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('modify_user_submit');
        $this->display($tpl);
    }
    function modify_user_submit()
    {
        unset($_SESSION['customer_kerm_modify_user']);

        $_SESSION['customer_kerm_modify_user']['aduser'] = $this->vars['aduser'];

        if($this->vars['cancel']) 
        {
            unset($_SESSION['customer_kerm_modify_user']);
            return $this->mk_redir('manage_users');
        }
        if($this->vars['save'])
        {
            $ad_usr = new KermADUser($this->vars['id']);
            if($ad_usr->id)
            {
                $aduser = $this->vars['aduser'];
                $ad_usr->load_from_array($aduser);
                $ad_grp = new KermADGroup($aduser['GroupName']);
                $ad_usr->status = CKERM_STATUS_MODI;
                $ad_usr->GroupName = $ad_grp->name;
                $ad_usr->Email .= $aduser['Domain'];
                if($ad_usr->is_valid_data())
                {
                        $ad_usr->save_data();
                        unset($_SESSION['customer_kerm_modify_user']);
                }
            }
            else 
            {
                unset($_SESSION['customer_kerm_modify_user']);
                return $this->mk_redir('manage_users');
            }
        }
        if($this->vars['delete'])
        {
            $ad_usr = new KermADUser($this->vars['id']);
            if($ad_usr->id)
            {
                    $ad_usr->delete();
                    unset($_SESSION['customer_kerm_modify_user']);
                    return $this->mk_redir('manage_users');
            }
        }

        return $this->mk_redir('modify_user', array('id'=>$this->vars['id']));
    }

}

?>

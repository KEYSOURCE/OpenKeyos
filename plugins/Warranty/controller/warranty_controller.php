<?php
class_load ('Computer');
class_load ('Peripheral');
class_load ('Customer');
class_load ('MonitorProfile');
class_load ('MonitorItem');
class_load ('AD_Printer');
class_load ('Supplier');
class_load ('Warranty');

class WarrantyController extends PluginController{
    protected $plugin_name = 'Warranty';
    
    function __construct(){ 
        $this->base_plugin_dir = dirname(__FILE__)."/../";
        parent::__construct();        
    }
    
    /** Displays the page with the warranties information for customers */
    function manage_warranties ()
    {
            $tpl = 'manage_warranties.tpl';

            $extra_params = array();	// Extra parameters to be carried in navigation
            if ($this->vars['do_filter']) $extra_params['do_filter'] = 1;

            if (isset($this->vars['customer_id']))
            {
                    $_SESSION['manage_warranties']['customer_id'] = $this->vars['customer_id'];
            }
            elseif ($this->locked_customer->id and !$this->vars['do_filter'])
            {
                    // If 'do_filter' is present in request, the locked customer is ignored
                    $_SESSION['manage_warranties']['customer_id'] = $this->locked_customer->id;
            }
            $filter = $_SESSION['manage_warranties'];

            // Check authorization
            if ($filter['customer_id'] > 0)
            {
                    // Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
                    // This way he can return to this page, without getting again "Permission Denied".
                    unset ($_SESSION['manage_warranties']['customer_id']);
                    check_auth (array('customer_id' => $filter['customer_id']));
                    $_SESSION['manage_warranties']['customer_id'] = $filter['customer_id'];
            }
            else check_auth ();

            $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            if ($filter['customer_id'] > 0)
            {
                    $customer = new Customer ($filter['customer_id']);

                    // Load the computers warranties
                    $computers_warranties = Computer::get_warranties (array ('customer_id' => $customer->id));
                    //debug($computers_warranties);
                    $computers_warranties_months = Warranty::get_warranties_months ($computers_warranties);
                    $computers_types = Computer::get_computers_types ($customer->id);

                    // Load the AD Printers warranties
                    $ad_printers_warranties = AD_Printer::get_warranties (array ('customer_id' => $customer->id));
                    $ad_printers_warranties_months = Warranty::get_warranties_months ($ad_printers_warranties);

                    // Load the Peripherals warranties
                    $peripherals_warranties = Peripheral::get_warranties (array ('customer_id' => $customer->id));
                    $peripherals_warranties_months = Warranty::get_warranties_months ($peripherals_warranties);

                    // Load the lists with the names for computers, peripherals and AD printers
                    $computers_list = Computer::get_computers_list (array ('customer_id' => $customer->id));
                    //debug($computers_list);
                    $peripherals_list = Peripheral::get_peripherals_list (array ('customer_id' => $customer->id));
                    $peripherals_classes_list = PeripheralClass::get_classes_list ();
                    $ad_printers_list = AD_Printer::get_ad_printers_list_canonical (array ('customer_id' => $customer->id));

                    // Make the list with all months
                    $month_min = time (); $month_max = 0;
                    if (count($computers_warranties_months) > 0)
                    {
                            $month_min = min($month_min, $computers_warranties_months[0]->month_start);
                            $month_max = max($month_max, $computers_warranties_months[count($computers_warranties_months)-1]->month_end);
                    }
                    if (count($peripherals_warranties_months) > 0)
                    {
                            $month_min = min($month_min, $peripherals_warranties_months[0]->month_start);
                            $month_max = max($month_max, $peripherals_warranties_months[count($peripherals_warranties_months)-1]->month_end);
                    }
                    if (count($ad_printers_warranties_months) > 0)
                    {
                            $month_min = min($month_min, $ad_printers_warranties_months[0]->month_start);
                            $month_max = max($month_max, $ad_printers_warranties_months[count($ad_printers_warranties_months)-1]->month_end);
                    }
                    $all_months = array ();
                    // Make sure we have found months
                    if ($month_max > 0)
                    {
                            while ($month_min <= $month_max)
                            {
                                    $all_months[$month_min] = date ('M Y', $month_min);
                                    $month_min = strtotime ('+1 month', $month_min);
                            }
                    }
                    // If any filtering on months was present, make sure it is on the available list of months
                    if ($filter['month_start'] or $filter['month_end'])
                    {
                            if (count($all_months)==0 or 
                            ($filter['month_start'] and !isset($all_months[$filter['month_start']])) or 
                            ($filter['month_end'] and !isset($all_months[$filter['month_end']])))
                            {
                                    unset($filter['month_start']);
                                    unset($filter['month_end']);
                            }
                    }

                    // Build the arrays for warranties table headers
                    $computers_warranties_head = Warranty::get_warranties_months_header ($computers_warranties_months, 4, $filter['month_start'], $filter['month_end']);
                    $ad_printers_warranties_head = Warranty::get_warranties_months_header ($ad_printers_warranties_months, 4, $filter['month_start'], $filter['month_end']);
                    $peripherals_warranties_head = Warranty::get_warranties_months_header ($peripherals_warranties_months, 4, $filter['month_start'], $filter['month_end']);

                    // Remove all warranties months not in the selected interval
                    if ($filter['month_start'])
                    {
                            foreach ($computers_warranties_months as $idx => $month)
                            {
                                    if ($month->month_start<$filter['month_start']) unset ($computers_warranties_months[$idx]);
                            }
                            foreach ($ad_printers_warranties_months as $idx => $month)
                            {
                                    if ($month->month_start<$filter['month_start']) unset ($ad_printers_warranties_months[$idx]);
                            }
                            foreach ($peripherals_warranties_months as $idx => $month)
                            {
                                    if ($month->month_start<$filter['month_start']) unset ($peripherals_warranties_months[$idx]);
                            }
                    }
                    if ($filter['month_end'])
                    {
                            foreach ($computers_warranties_months as $idx => $month)
                            {
                                    if ($month->month_start>$filter['month_end']) unset ($computers_warranties_months[$idx]);
                            }
                            foreach ($ad_printers_warranties_months as $idx => $month)
                            {
                                    if ($month->month_start>$filter['month_end']) unset ($ad_printers_warranties_months[$idx]);
                            }
                            foreach ($peripherals_warranties_months as $idx => $month)
                            {
                                    if ($month->month_start>$filter['month_end']) unset ($peripherals_warranties_months[$idx]);
                            }
                    }

                    // Special fields for warranties
                    $warranty_item_id = Computer::get_item_id ('warranties'); 
                    $service_packages_list = SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true));
                    $service_levels_list = ServiceLevel::get_service_levels_list ();
                    $this->assign ('warranty_item_id', $warranty_item_id);
                    $this->assign ('service_packages_list', $service_packages_list);
                    $this->assign ('service_levels_list', $service_levels_list);
                    $this->assign ('all_months', $all_months);

                    $this->assign ('customer', $customer);
                    $this->assign ('computers_warranties', $computers_warranties);
                    $this->assign ('computers_warranties_months', $computers_warranties_months);
                    $this->assign ('computers_warranties_head', $computers_warranties_head);
                    $this->assign ('computers_types', $computers_types);

                    $this->assign ('ad_printers_warranties', $ad_printers_warranties);
                    $this->assign ('ad_printers_warranties_months', $ad_printers_warranties_months);
                    $this->assign ('ad_printers_warranties_head', $ad_printers_warranties_head);

                    $this->assign ('peripherals_warranties', $peripherals_warranties);
                    $this->assign ('peripherals_warranties_months', $peripherals_warranties_months);
                    $this->assign ('peripherals_warranties_head', $peripherals_warranties_head);

                    $this->assign ('computers_list', $computers_list);
                    $this->assign ('peripherals_list', $peripherals_list);
                    $this->assign ('peripherals_classes_list', $peripherals_classes_list);
                    $this->assign ('ad_printers_list', $ad_printers_list);

                    // Count the number of unique servers, workstations and peripherals
                    $cnt_computers = array ();	// The count of computers by type
                    $cnt_computers_all = 0;		// Total number of unique computers
                    $counted = array ();
                    foreach ($computers_warranties as $w)
                    {
                            if (!in_array($w->id, $counted))
                            {
                                    $cnt_computers[$computers_types[$w->id]]++;
                                    $cnt_computers_all++;
                                    $counted[] = $w->id;
                            }
                    }

                    $this->assign ('cnt_computers', $cnt_computers);
                    $this->assign ('cnt_computers_all', $cnt_computers_all);
            }
            else
            {
                    // Special case, fetch servers or workstations without warranties dates or SN
                    if ($filter['customer_id'] == -1) $missing_warranties_sn = Warranty::get_servers_missing_warranties_sn ();
                    elseif ($filter['customer_id'] == -2) $missing_warranties_sn = Warranty::get_workstations_missing_warranties_sn ();
                    $this->assign ('missing_warranties_sn', $missing_warranties_sn);
            }

            // Mark the potential customer for locking
            if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

            $this->assign ('filter', $filter);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('warranty_item_id', Computer::get_item_id ('warranties'));
            $this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('manage_warranties_submit', $extra_params);

            $this->display ($tpl);
    }
    
    /** Saves filtering criteria for the Manage Warranties page */
    function manage_warranties_submit ()
    {
            check_auth ();

            $extra_params = array();
            $_SESSION['manage_warranties'] = $this->vars['filter'];

            if ($this->vars['do_filter'] or $this->vars['do_filter_hidden']) $extra_params['do_filter'] = 1;

            return $this->mk_redir('manage_warranties', $extra_params);
    }

    /** Displays the page with the EOW (end of warranties) */
    function warranties_eow ()
    {
            check_auth ();
            $tpl = 'warranties_eow.tpl';

            if (isset($this->vars['customer_id']))
            {
                    $_SESSION['warranties_eow']['customer_id'] = $this->vars['customer_id'];
            }
            elseif ($this->locked_customer->id and !$this->vars['do_filter'])
            {
                    // If 'do_filter' is present in request, the locked customer is ignored
                    $_SESSION['warranties_eow']['customer_id'] = $this->locked_customer->id;
            }
            $filter = $_SESSION['warranties_eow'];
            if (!isset($filter['expired_only'])) $filter['expired_only'] = true;
            if (!isset($filter['computers_only'])) $filter['computers_only'] = false;
            if ($filter['customer_id']==-1) $filter['expired_only'] = true;

            $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);
            unset ($customers_filter['favorites_first']);
            $customers_list_sort = Customer::get_customers_list ($customers_filter);

            if ($filter['customer_id'])
            {
                    $customer_id = $filter['customer_id'];
                    if ($customer_id > 0) $computers_list = array($customer_id => Computer::get_computers_list (array ('customer_id' => $customer_id)));
                    else
                    {
                            $computers_list = array ();
                            foreach ($customers_list_sort as $cust_id => $customer_name)
                            {
                                    $computers_list[$cust_id] = Computer::get_computers_list (array ('customer_id' => $cust_id));
                            }
                    }

                    $comp_warranties_active = array ();
                    $comp_warranties_eow = array ();
                    $comp_warranties_unknown = array ();
                    foreach ($computers_list as $cust_id => $comp_list)
                    {
                            // Get computer warranties and separate them in the list of active, expired and missing info lists
                            // First separately them by warranty status, then go over the lists so they are sorted by
                            // computer name, and also to make sure a computer is in a single list (computers can have multiple warranties)
                            $comp_warranties = Computer::get_warranties (array ('customer_id' => $cust_id));
                            $comp_warranties_active_tmp = array ();
                            $comp_warranties_eow_tmp = array ();
                            $comp_warranties_unknown_tmp = array ();
                            foreach ($comp_warranties as $warranty)
                            {
                                    $id = $warranty->id;
                                    if (!$warranty->warranty_ends) $comp_warranties_unknown_tmp[$id][] = $warranty;
                                    elseif ($warranty->warranty_ends > time()) $comp_warranties_active_tmp[$id][] = $warranty;
                                    else $comp_warranties_eow_tmp[$id][] = $warranty;
                            }

                            foreach ($comp_list as $id => $computer_name)
                            {
                                    if (isset($comp_warranties_active_tmp[$id])) $comp_warranties_active[$cust_id][$id] = $comp_warranties_active_tmp[$id];
                                    elseif (isset($comp_warranties_eow_tmp[$id])) $comp_warranties_eow[$cust_id][$id] = $comp_warranties_eow_tmp[$id];
                                    else $comp_warranties_unknown[$cust_id][$id] = $comp_warranties_unknown_tmp[$id];
                            }
                    }

                    $this->assign ('customer_id', $customer_id);
                    $this->assign ('computers_list', $computers_list);
                    $this->assign ('service_packages_list', SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true)));
                    $this->assign ('service_levels_list', ServiceLevel::get_service_levels_list ());

                    if ($filter['expired_only']) $shown_warranties = array ('eow' => $comp_warranties_eow);
                    else
                    {
                            $shown_warranties = array (
                                    'eow' => $comp_warranties_eow,
                                    'active' => $comp_warranties_active,
                                    'unknown' => $comp_warranties_unknown
                            );
                    }

                    $this->assign ('shown_warranties', $shown_warranties);
            }

            $params = array ('do_filter' => 1);
            $this->assign ('filter', $filter);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('warranties_eow_submit', $params);

            $this->display ($tpl);
    }

    function warranties_eow_submit ()
    {
            $params = array ();
            if ($this->vars['do_filter'] or $this->vars['do_filter_hidden']) $params['do_filter'] = 1;
            $filter = $this->vars['filter'];
            $filter['expired_only'] = ($filter['expired_only'] ? true : false);
            $filter['computers_only'] = ($filter['computers_only'] ? true : false);

            $_SESSION['warranties_eow'] = $filter;

            return $this->mk_redir ('warranties_eow', $params);
    }
    
    function online_warranty_check(){
            check_auth();
            $tpl = 'online_warranty_check.tpl';

            if (isset($this->vars['customer_id']))
            {
                    $_SESSION['online_warranty_check']['customer_id'] = $this->vars['customer_id'];
            }
            elseif ($this->locked_customer->id and !$this->vars['do_filter'])
            {
                    // If 'do_filter' is present in request, the locked customer is ignored
                    $_SESSION['online_warranty_check']['customer_id'] = $this->locked_customer->id;
            }
            $filter = $_SESSION['online_warranty_check'];

            //if(isset($_SESSION['online_warranty_check'])) unset($_SESSION['online_warranty_check']);


            $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => true);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            if ($filter['customer_id'])
            {
                    $customer_id = $filter['customer_id'];
                    if ($customer_id > 0) $computers_list = array($customer_id => Computer::get_computers_list (array ('customer_id' => $customer_id)));
                    
                    $comp_warranties_active = array ();
                    $comp_warranties_eow = array ();
                    $comp_warranties_unknown = array ();
                    $comp_additional_infos = array();
                    foreach ($computers_list as $cust_id => $comp_list)
                    {
                            // Get computer warranties and separate them in the list of active, expired and missing info lists
                            // First separately them by warranty status, then go over the lists so they are sorted by
                            // computer name, and also to make sure a computer is in a single list (computers can have multiple warranties)
                            $comp_warranties = Computer::get_warranties (array ('customer_id' => $cust_id));
                            $comp_warranties_active = array ();
                            $comp_warranties_eow = array ();
                            $comp_warranties_unknown = array ();
                            foreach ($comp_warranties as $warranty)
                            {
                                    $id = $warranty->id;
                                    if (!$warranty->warranty_ends) $comp_warranties_unknown[$id] = $warranty;
                                    elseif ($warranty->warranty_ends > time()) $comp_warranties_active[$id] = $warranty;
                                    else $comp_warranties_eow[$id] = $warranty;
                            }
                            foreach($comp_list as $comp_id=>$comp_name){
                                $c = new Computer();
                                $comp_additional_infos[$comp_id] = $c->get_additional_info($comp_id);
                            }
                    }

                    $service_levels = ServiceLevel::get_service_levels_list();                    
                    $service_packages =  SupplierServicePackage::get_service_packages_list(array('prefix_supplier'=>true));
                    
                    //debug($comp_warranties_unknown);

                    $this->assign('service_levels_list', $service_levels);
                    $this->assign('service_packages_list', $service_packages);
                    $this->assign('comp_warranties_active', $comp_warranties_active);
                    $this->assign('comp_warranties_eow', $comp_warranties_eow);
                    $this->assign('comp_warranties_unknown', $comp_warranties_unknown);
                    $this->assign('comp_additional_infos', $comp_additional_infos);
                    $this->assign('computers_list', $computers_list[$filter['customer_id']]);

            }          
            $this->assign('filter', $filter);
            $this->assign('customers_list', $customers_list);
            $this->assign('error_msg', error_msg());
            $this->set_form_redir('online_warranty_check_submit');
            $this->display($tpl);
        }
        function online_warranty_check_submit(){
            check_auth();
            class_load('ComputerItem');
            $_SESSION['online_warranty_check'] = $this->vars['filter'];
            //debug($this->vars);
            $warr_to_save = $this->vars['warranties_to_save'];           
            if(isset($warr_to_save) and !empty($warr_to_save)){
                $warranties = $this->vars['warranty'];
                foreach($warr_to_save as $warr){                    
                    $wr = $warranties[$warr];
                    //debug($wr);
                    if($wr['start_date']!='' and $wr['end_date'] != ''){
                        
                        $ws = strtotime($wr['start_date']);
                        $we = strtotime($wr['end_date']);
                        $warranty = new ComputerItem($warr, 2001);

                        $val = array(
                            207 => $wr['sn'],
                            208 => $ws,
                            209 => $we,
                            211 => $wr['service_level_id'],
                            210 => $wr['service_package_id'],
                            27 => $wr['product'],
                            247 => $wr['raise_alert'] ? 1 : 0
                        );
                        if($val != $warranty->val[0]->value){
                            $warranty->val[0]->updated = time();
                        }
                        $warranty->val[0]->value = $val;
                        $warranty->save_data();
                    }
                }
            }
            return $this->mk_redir('online_warranty_check');
        }    
}
?>

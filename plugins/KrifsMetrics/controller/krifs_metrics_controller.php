<?php
class_load('KrifsMetrics');
class KrifsMetricsController extends PluginController{
    protected $plugin_name = "KrifsMetrics";
    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    /********************************************************/
    /* Tickets activities					*/
    /********************************************************/

    function metrics ()
    {
            check_auth ();
            $tpl = 'metrics.tpl';

            $filter = $_SESSION['metrics']['filter'];

            if (!$filter['date_start']) $filter['date_start'] = get_first_hour(get_first_monday ());
            if (!$filter['date_end']) $filter['date_end'] = get_last_hour();
            if (!$filter['view_by']) $filter['view_by'] = 'users';
            if ($this->vars['date_start']) $filter['date_start'] = $this->vars['date_start'];
            if ($this->vars['date_end']) $filter['date_end'] = $this->vars['date_end'];

            //debug($filter);

            if ($filter['date_start'] >= $filter['date_end']) $filter['date_start'] = get_first_hour(strtotime ('-1 day', $filter['date_end']));

            $customer_admin = false;
            if($this->current_user->is_customer_user() and $this->current_user->administrator and $this->current_user->type==USER_TYPE_CUSTOMER)
            {
                    $customer_admin = true;
                    $filter['view_by'] = 'customers';
            }


            if ($filter['view_by'] == 'users')
            {
                    // View users metrics
                    $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE + USER_TYPE_KEYSOURCE_GROUP, 'active'=>-1));
                    $users = User::get_users (array('type' => USER_TYPE_KEYSOURCE + USER_TYPE_KEYSOURCE_GROUP, 'active'=>-1), $no_count);

                    $assigned_tickets = KrifsMetrics::get_assigned_tickets_count (true);
                    $assigned_tickets_ignored = KrifsMetrics::get_assigned_tickets_count (false);
                    $created_tds = KrifsMetrics::get_created_tickets_details ($filter['date_start'], $filter['date_end']);
                    $closed_tickets = KrifsMetrics::get_closed_tickets ($filter['date_start'], $filter['date_end'], $tot_closed_tickets);
                    $work_times = KrifsMetrics::get_work_times ($filter['date_start'], $filter['date_end']);
                    $total_work_times = KrifsMetrics::get_total_work_times($filter['date_start'], $filter['date_end']);
                    $oldest_tickets_dates = KrifsMetrics::get_oldest_tickets_dates ();
                    $avg_closing_times = KrifsMetrics::get_average_closing_time ($filter['date_start'], $filter['date_end']);

                    // Append the customer users too
                    foreach (array_keys($assigned_tickets) as $user_id)
                    {
                            if (!isset($users_list[$user_id])) 
                            {
                                    $user = new User($user_id);
                                    $user->customer = new Customer ($user->customer_id);
                                    $users_list[$user->id] = $user->get_name();
                                    $users[] = $user;
                            }
                    }

                    $tot_assigned_tickets = 0;
                    $tot_assigned_tickets_ignored = 0;
                    $tot_created_tds = 0;
                    $tot_work_times = 0;
                    $tt_wt = 0;
                    $oldest_ticket_date = time () + 1000;
                    $avg_closing_time = 0;
                    $cnt_closing_time = 0;
                    foreach ($users_list as $user_id => $user_name)
                    {
                            $tot_assigned_tickets+= $assigned_tickets[$user_id];
                            $tot_assigned_tickets_ignored+= $assigned_tickets_ignored[$user_id];
                            $tot_created_tds+= $created_tds[$user_id];
                            $tot_work_times+= $work_times[$user_id];
                            $tt_wt+=$total_work_times[$user_id];
                            if ($oldest_tickets_dates[$user_id]) $oldest_ticket_date = min ($oldest_ticket_date, $oldest_tickets_dates[$user_id]);
                            if ($avg_closing_times[$user_id])
                            {
                                    $cnt_closing_time++;
                                    $avg_closing_time+= $avg_closing_times[$user_id];
                            }
                            if(!empty($created_tds[$user_id]) OR !empty($closed_tickets[$user_id])) {
                                $user = new User($user_id);
                                if($user->is_active() OR (!$user->is_active() AND isset($assigned_tickets[$user_id]))) {
                                    $chart_categories .= "'" .$user_name . "',";
                                    $tmp = (!empty($created_tds[$user_id])) ? $created_tds[$user_id] : 0;
                                    $chart_created_tds .= "{y: {$tmp}, unit: 'created ticket detail(s)'},";

                                    $tmp = (!empty($closed_tickets[$user_id])) ? $closed_tickets[$user_id] : 0;
                                    $chart_closed_tickets .= "{y: {$tmp}, unit: 'closed ticket(s)'},";

                                    if(!empty($total_work_times[$user_id]) or !empty($work_times[$user_id])) {
                                        $tmp = (!empty($work_times[$user_id])) ? minutes2hours($work_times[$user_id]) : 0;
                                        $tmp2 = (!empty($work_times[$user_id])) ? format_interval_minutes(($work_times[$user_id])) : '00:00';
                                        $color = random_color();
                                        $chart_pie_data1 .= "{name: '{$user_name}', y: {$tmp}, hours: '{$tmp2}', color: '#{$color}'},";

                                        $tmp = (!empty($total_work_times[$user_id])) ? minutes2hours($total_work_times[$user_id]) : 0;
                                        $tmp2 = (!empty($total_work_times[$user_id])) ? format_interval_minutes(($total_work_times[$user_id])) : '00:00';

                                        $chart_pie_data2 .= "{name: '{$user_name}', y: {$tmp}, hours: '{$tmp2}', color: '#{$color}'},";
                                    }

                                    $tmp = (!empty($work_times[$user_id])) ? $work_times[$user_id] : 0;
                                    $chart_work_times .= "{y: {$tmp}, unit: 'minute(s)'},";

                                    $tmp = (!empty($total_work_times[$user_id])) ? $total_work_times[$user_id] : 0;
                                    $chart_tt_work_times .= "{y: {$tmp}, unit: 'minute(s)'},";
                                }
                            }
                    }
                    //Chart data
                    $chart_created_tds = rtrim($chart_created_tds, ',');
                    $this->assign('chart_created_tds', $chart_created_tds);
                    $chart_categories = rtrim($chart_categories, ',');
                    $this->assign('chart_categories', $chart_categories);
                    $chart_closed_tickets = rtrim($chart_closed_tickets, ',');
                    $this->assign('chart_closed_tickets', $chart_closed_tickets);
                    $chart_work_times = rtrim($chart_work_times, ',');
                    $this->assign('chart_work_times', $chart_work_times);
                    $chart_tt_work_times = rtrim($chart_tt_work_times, ',');
                    $this->assign('chart_tt_work_times', $chart_tt_work_times);
                    $chart_pie_data1 = rtrim($chart_pie_data1, ',');
                    $this->assign('chart_pie_data1', $chart_pie_data1);
                    $chart_pie_data2 = rtrim($chart_pie_data2, ',');
                    $this->assign('chart_pie_data2', $chart_pie_data2);

                    if ($cnt_closing_time>0) $avg_closing_time = intval($avg_closing_time / $cnt_closing_time);


                    $this->assign ('users_list', $users_list);
                    $this->assign ('users', $users);
                    $this->assign ('assigned_tickets', $assigned_tickets);
                    $this->assign ('assigned_tickets_ignored', $assigned_tickets_ignored);
                    $this->assign ('created_tds', $created_tds);
                    $this->assign ('closed_tickets', $closed_tickets);
                    $this->assign ('work_times', $work_times);
                    $this->assign ('total_work_times', $total_work_times);
                    $this->assign ('oldest_tickets_dates', $oldest_tickets_dates);
                    $this->assign ('avg_closing_times', $avg_closing_times);

                    $this->assign ('tot_assigned_tickets', $tot_assigned_tickets);
                    $this->assign ('tot_assigned_tickets_ignored', $tot_assigned_tickets_ignored);
                    $this->assign ('tot_created_tds', $tot_created_tds);
                    $this->assign ('tot_closed_tickets', $tot_closed_tickets);
                    $this->assign ('tot_work_times', $tot_work_times);
                    $this->assign ('tt_wt', $tt_wt);
                    $this->assign ('oldest_ticket_date', $oldest_ticket_date);
                    $this->assign ('avg_closing_time', $avg_closing_time);
            }
            else
            {
                    // View customers metrics
                    $customers_list = Customer::get_customers_list ();
                    $full_customers_list = $customers_list;
                    if(!empty($filter['customer_id'])) {
                        unset($customers_list);
                        $customers_list[$filter['customer_id']] = $full_customers_list[$filter['customer_id']];
                    }

                    $open_tickets = KrifsMetrics::get_cust_open_tickets_count (true);
                    $open_tickets_ignored = KrifsMetrics::get_cust_open_tickets_count (false);
                    $created_tickets = KrifsMetrics::get_cust_created_tickets ($filter['date_start'], $filter['date_end']);
                    $closed_tickets = KrifsMetrics::get_cust_closed_tickets ($filter['date_start'], $filter['date_end'], $tot_closed_tickets);
                    $oldest_tickets_dates = KrifsMetrics::get_cust_oldest_tickets_dates ();
                    $avg_closing_times = KrifsMetrics::get_cust_average_closing_time ($filter['date_start'], $filter['date_end']);
                    $tickets_by_status = KrifsMetrics::get_cust_tickets_stats ();

                    $tot_open_tickets = 0;
                    $tot_open_tickets_ignored = 0;
                    $tot_created_tickets = 0;
                    $oldest_ticket_date = time () + 1000;
                    $avg_closing_time = 0;
                    $cnt_closing_time = 0;

                    $tot_stats = array ();
                    foreach ($GLOBALS ['TICKET_STATUSES'] as $stat_code => $stat_name) $tot_stats[$stat_code] = 0;


                    $values = array();
                    $values_irs = array();

                    foreach($customers_list as $customer_id => $customer_name)
                    {
                            $flt['customer_id'] = $customer_id;
                            $flt['d_start'] = $filter['date_start'];
                            $flt['d_end'] = $filter['date_end'];

                            $values[$customer_id] = KrifsMetrics::get_tot_tickets_times($flt);	
                            $values_irs[$customer_id] = KrifsMetrics::get_tot_irs_times($flt);
                    }

                    foreach ($customers_list as $customer_id => $customer_name)
                    if (isset($open_tickets[$customer_id]))
                    {
                            $tot_open_tickets+= $open_tickets[$customer_id];
                            $tot_open_tickets_ignored+= $open_tickets_ignored[$customer_id];
                            $tot_created_tickets+= $created_tickets[$customer_id];
                            if ($oldest_tickets_dates[$customer_id]) $oldest_ticket_date = min ($oldest_ticket_date, $oldest_tickets_dates[$customer_id]);
                            if ($avg_closing_times[$customer_id])
                            {
                                    $cnt_closing_time++;
                                    $avg_closing_time+= $avg_closing_times[$customer_id];
                            }
                            if (is_array($tickets_by_status[$customer_id]))
                            {
                                    foreach ($tickets_by_status[$customer_id] as $stat=>$cnt) $tot_stats[$stat]+= $cnt;
                            }
                    }
                    if ($cnt_closing_time>0) $avg_closing_time = intval($avg_closing_time / $cnt_closing_time);
                    //debug($values[361]);
                    $this->assign ('values', $values);
                    $this->assign ('values_irs', $values_irs);
                    $this->assign ('customers_list', $customers_list);
                    $this->assign('full_customers_list', $full_customers_list);
                    $this->assign ('open_tickets', $open_tickets);
                    $this->assign ('open_tickets_ignored', $open_tickets_ignored);
                    $this->assign ('created_tickets', $created_tickets);
                    $this->assign ('closed_tickets', $closed_tickets);
                    $this->assign ('oldest_tickets_dates', $oldest_tickets_dates);
                    $this->assign ('avg_closing_times', $avg_closing_times);
                    $this->assign ('tickets_by_status', $tickets_by_status);



                    $this->assign ('tot_closed_tickets', $tot_closed_tickets);
                    $this->assign ('tot_open_tickets', $tot_open_tickets);
                    $this->assign ('tot_open_tickets_ignored', $tot_open_tickets_ignored);
                    $this->assign ('tot_created_tickets', $tot_created_tickets);
                    $this->assign ('oldest_ticket_date', $oldest_ticket_date);
                    $this->assign ('avg_closing_time', $avg_closing_time);
                    $this->assign ('tot_stats', $tot_stats);
                    $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            }

            $last_monday = get_first_monday ();
            $this_month_start = get_month_start ();
            $last_month_end = get_last_hour (strtotime('-1 day', $this_month_start));
            $last_month_start = get_month_start ($last_month_end);
            $oldest_date = KrifsMetrics::get_oldest_date ();
            $predefined_intervals = array (
                    1 => array (
                            'name' => 'This week',
                            'date_start' => get_first_hour($last_monday),
                            'date_end' => get_last_hour(),
                    ),
                    2 => array (
                            'name' => 'Last week',
                            'date_start' => get_first_hour(strtotime('-1 week', $last_monday)),
                            'date_end' => get_last_hour(strtotime('-1 day', $last_monday)),
                    ),
                    3 => array (
                            'name' => 'This month',
                            'date_start' => get_first_hour($this_month_start),
                            'date_end' => get_last_hour(),
                    ),
                    4 => array (
                            'name' => 'Last month',
                            'date_start' => get_first_hour($last_month_start),
                            'date_end' => get_last_hour($last_month_end),
                    ),
            );
            for ($year = date('Y'); $year>=date('Y',$oldest_date); $year--)
            {
                    $predefined_intervals[$year] = array (
                            'name' => 'Year '.$year,
                            'date_start' => strtotime('1 Jan '.$year.' 00:00:00'),
                            'date_end' => strtotime('31 Dec '.$year.' 23:59:59'),
                    );
            }

            foreach ($predefined_intervals as $idx => $interval)
            {
                    if ($interval['date_start']==$filter['date_start'] and $interval['date_end']==$filter['date_end'])
                    {
                            $predefined_intervals[$idx]['selected'] = true;
                    }
                    $predefined_intervals[$idx]['date_start_str'] = date('d M Y H:i:s',$predefined_intervals[$idx]['date_start']);
                    $predefined_intervals[$idx]['date_end_str'] = date('d M Y H:i:s',$predefined_intervals[$idx]['date_end']);
            }

            $this->assign ('filter', $filter);
            $this->assign ('customer_admin', $customer_admin);
            $this->assign ('predefined_intervals', $predefined_intervals);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('metrics_submit');

            $this->display ($tpl);
    }

    function metrics_submit ()
    {
            check_auth ();
            $filter = $this->vars['filter'];
            if(isset($filter['customer_id']) and empty($filter['customer_id'])) {
                unset($filter['customer_id']);
            }
            //debug($filter);
            $filter['date_start'] = get_first_hour (js_strtotime ($filter['date_start']));
            $filter['date_end'] = get_last_hour (js_strtotime ($filter['date_end']));
            //debug($filter);
            $_SESSION['metrics']['filter'] = $filter;
            return $this->mk_redir ('metrics');
    }

    /********************************************************/
    /* Tickets activities					*/
    /********************************************************/

    function metrics_compare ()
    {
            check_auth ();
            $tpl = 'metrics_compare.tpl';

            $filter = $_SESSION['metrics']['filter'];

            if (!$filter['date_start']) $filter['date_start'] = get_first_hour(get_first_monday ());
            if (!$filter['date_end']) $filter['date_end'] = get_last_hour();
            if (!$filter['view_by']) $filter['view_by'] = 'users';
            if ($this->vars['date_start']) $filter['date_start'] = $this->vars['date_start'];
            if ($this->vars['date_end']) $filter['date_end'] = $this->vars['date_end'];

            if (!$filter['compare_date_start']) {
                $filter['compare_date_start'] = strtotime(date('Y-m-d', $filter['date_start']) . '-1 year');
            }
            if (!$filter['compare_date_end']) {
                $filter['compare_date_end'] = strtotime(date('Y-m-d', $filter['date_end']) . '-1 year');
            }
            if ($this->vars['compare_date_start']) $filter['compare_date_start'] = $this->vars['compare_date_start'];
            if ($this->vars['compare_date_end']) $filter['compare_date_end'] = $this->vars['compare_date_end'];


            if ($filter['date_start'] >= $filter['date_end']) $filter['date_start'] = get_first_hour(strtotime ('-1 day', $filter['date_end']));
            if ($filter['compare_date_start'] >= $filter['compare_date_end']) $filter['compare_date_start'] = get_first_hour(strtotime ('-1 day', $filter['compare_date_end']));

            $customer_admin = false;
            if($this->current_user->is_customer_user() and $this->current_user->administrator and $this->current_user->type==USER_TYPE_CUSTOMER)
            {
                    $customer_admin = true;
                    $filter['view_by'] = 'customers';
            }


            if ($filter['view_by'] == 'users')
            {
                    // View users metrics
                    $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE + USER_TYPE_KEYSOURCE_GROUP, 'active'=>-1));
                    $users = User::get_users (array('type' => USER_TYPE_KEYSOURCE + USER_TYPE_KEYSOURCE_GROUP, 'active'=>-1), $no_count);

                    $created_tds = KrifsMetrics::get_created_tickets_details ($filter['date_start'], $filter['date_end']);
                    $closed_tickets = KrifsMetrics::get_closed_tickets ($filter['date_start'], $filter['date_end'], $tot_closed_tickets);
                    $work_times = KrifsMetrics::get_work_times ($filter['date_start'], $filter['date_end']);
                    $total_work_times = KrifsMetrics::get_total_work_times($filter['date_start'], $filter['date_end']);

                    $compare_created_tds = KrifsMetrics::get_created_tickets_details ($filter['compare_date_start'], $filter['compare_date_end']);
                    $compare_closed_tickets = KrifsMetrics::get_closed_tickets ($filter['compare_date_start'], $filter['compare_date_end'], $compare_tot_closed_tickets);
                    $compare_work_times = KrifsMetrics::get_work_times ($filter['compare_date_start'], $filter['compare_date_end']);
                    $compare_total_work_times = KrifsMetrics::get_total_work_times($filter['compare_date_start'], $filter['compare_date_end']);

                    $assigned_tickets = KrifsMetrics::get_assigned_tickets_count (true);
                    $this->assign('assigned_tickets', $assigned_tickets);
                    foreach (array_keys($assigned_tickets) as $user_id)
                    {
                            if (!isset($users_list[$user_id]))
                            {
                                    $user = new User($user_id);
                                    $user->customer = new Customer ($user->customer_id);
                                    $users_list[$user->id] = $user->get_name();
                                    $users[] = $user;
                            }
                    }

                    $tot_created_tds = 0;
                    $tot_work_times = 0;
                    $tt_wt = 0;

                    $compare_tot_created_tds = 0;
                    $compare_tot_work_times = 0;
                    $compare_tt_wt = 0;

                    $chart_created_tds = '';
                    $chart_compare_created_tds = '';
                    $chart_categories = '';
                    $chart_closed_tickets = '';
                    $chart_compare_closed_tickets = '';
                    $chart_pie_data1 = '';
                    $chart_pie_data2 = '';
                    $chart_work_times = '';
                    $chart_tt_work_times = '';
                    foreach ($users_list as $user_id => $user_name)
                    {
                            $tot_created_tds+= $created_tds[$user_id];
                            if(!empty($created_tds[$user_id]) OR !empty($closed_tickets[$user_id])) {
                                $user = new User($user_id);
                                if($user->is_active() OR (!$user->is_active() AND isset($assigned_tickets[$user_id]))) {
                                    $chart_categories .= "'" .$user_name . "',";

                                    $tmp = (!empty($created_tds[$user_id])) ? $created_tds[$user_id] : 0;
                                    $chart_created_tds .= "{$tmp},";
                                    $tmp = (!empty($compare_created_tds[$user_id])) ? $compare_created_tds[$user_id] : 0;
                                    $chart_compare_created_tds .= "{$tmp},";

                                    $tmp = (!empty($closed_tickets[$user_id])) ? $closed_tickets[$user_id] : 0;
                                    $chart_closed_tickets .= "{$tmp},";
                                    $tmp = (!empty($compare_closed_tickets[$user_id])) ? $compare_closed_tickets[$user_id] : 0;
                                    $chart_compare_closed_tickets .= "{$tmp},";

                                    $tmp = (!empty($work_times[$user_id])) ? $work_times[$user_id] : 0;
                                    $chart_work_times .= "{$tmp},";
                                    $tmp = (!empty($compare_work_times[$user_id])) ? $compare_work_times[$user_id] : 0;
                                    $chart_compare_work_times .= "{$tmp},";

                                    $tmp = (!empty($total_work_times[$user_id])) ? $total_work_times[$user_id] : 0;
                                    $chart_tt_work_times .= "{$tmp},";
                                    $tmp = (!empty($compare_total_work_times[$user_id])) ? $compare_total_work_times[$user_id] : 0;
                                    $chart_compare_tt_work_times .= "{$tmp},";
                                }
                            }
                            $tot_work_times+= $work_times[$user_id];
                            $tt_wt+=$total_work_times[$user_id];

                            $compare_tot_created_tds+= $compare_created_tds[$user_id];
                            $compare_tot_work_times+= $compare_work_times[$user_id];
                            $compare_tt_wt+=$compare_total_work_times[$user_id];

                    }
                    //Chart data
                    $chart_categories = rtrim($chart_categories, ',');
                    $this->assign('chart_categories', $chart_categories);

                    $chart_created_tds = rtrim($chart_created_tds, ',');
                    $this->assign('chart_created_tds', $chart_created_tds);
                    $chart_compare_created_tds = rtrim($chart_compare_created_tds, ',');
                    $this->assign('chart_compare_created_tds', $chart_compare_created_tds);

                    $chart_closed_tickets = rtrim($chart_closed_tickets, ',');
                    $this->assign('chart_closed_tickets', $chart_closed_tickets);
                    $chart_compare_closed_tickets = rtrim($chart_compare_closed_tickets, ',');
                    $this->assign('chart_compare_closed_tickets', $chart_compare_closed_tickets);

                    $chart_work_times = rtrim($chart_work_times, ',');
                    $this->assign('chart_work_times', $chart_work_times);
                    $chart_compare_work_times = rtrim($chart_compare_work_times, ',');
                    $this->assign('chart_compare_work_times', $chart_compare_work_times);

                    $chart_tt_work_times = rtrim($chart_tt_work_times, ',');
                    $this->assign('chart_tt_work_times', $chart_tt_work_times);
                    $chart_compare_tt_work_times = rtrim($chart_compare_tt_work_times, ',');
                    $this->assign('chart_compare_tt_work_times', $chart_compare_tt_work_times);


                    $this->assign ('users_list', $users_list);
                    $this->assign ('users', $users);

                    $this->assign ('created_tds', $created_tds);
                    $this->assign ('closed_tickets', $closed_tickets);
                    $this->assign ('work_times', $work_times);
                    $this->assign ('total_work_times', $total_work_times);

                    $this->assign ('compare_created_tds', $compare_created_tds);
                    $this->assign ('compare_closed_tickets', $compare_closed_tickets);
                    $this->assign ('compare_work_times', $compare_work_times);
                    $this->assign ('compare_total_work_times', $compare_total_work_times);

                    $this->assign ('tot_created_tds', $tot_created_tds);
                    $this->assign ('tot_closed_tickets', $tot_closed_tickets);
                    $this->assign ('tot_work_times', $tot_work_times);
                    $this->assign ('tt_wt', $tt_wt);

                    $this->assign ('compare_tot_created_tds', $compare_tot_created_tds);
                    $this->assign ('compare_tot_closed_tickets', $compare_tot_closed_tickets);
                    $this->assign ('compare_tot_work_times', $compare_tot_work_times);
                    $this->assign ('compare_tt_wt', $compare_tt_wt);
            }
            else
            {
                    // View customers metrics
                    $customers_list = Customer::get_customers_list ();
                    $full_customers_list = $customers_list;
                    $compare = false;
                    if(!empty($filter['customer_id'])) {
                        unset($customers_list);
                        $customers_list[$filter['customer_id']] = $full_customers_list[$filter['customer_id']];
                        if(empty($filter['compare_customer_id'])) {
                            $filter['compare_customer_id'] = $filter['customer_id'];
                        }
                        $compare_customers_list[$filter['compare_customer_id']] = $full_customers_list[$filter['compare_customer_id']];
                        $compare = true;

                        if($filter['compare_customer_id'] != $filter['customer_id']) {
                            $chart_categories = "'Opened ticket(s)', 'Created ticket(s)', 'Closed ticket(s)', 'TWT tickets', 'TWT IRs', 'Bill time tickets', 'Bill time IRs'";
                        } else {
                            $chart_categories = "'Created ticket(s)', 'Closed ticket(s)', 'TWT tickets', 'TWT IRs', 'Bill time tickets', 'Bill time IRs'";
                        }


                        $chart_customer = trim($full_customers_list[$filter['customer_id']]);
                        $chart_compare_customer = trim($full_customers_list[$filter['compare_customer_id']]);

                        $this->assign('chart_categories', $chart_categories);
                        $this->assign('chart_compare_customer', $chart_compare_customer);
                        $this->assign('chart_customer', $chart_customer);
                    }

                    $open_tickets = KrifsMetrics::get_cust_open_tickets_count (true);
                    $open_tickets_ignored = KrifsMetrics::get_cust_open_tickets_count (false);
                    $created_tickets = KrifsMetrics::get_cust_created_tickets ($filter['date_start'], $filter['date_end']);
                    $closed_tickets = KrifsMetrics::get_cust_closed_tickets ($filter['date_start'], $filter['date_end'], $tot_closed_tickets);
                    $oldest_tickets_dates = KrifsMetrics::get_cust_oldest_tickets_dates ();
                    $avg_closing_times = KrifsMetrics::get_cust_average_closing_time ($filter['date_start'], $filter['date_end']);
                    $tickets_by_status = KrifsMetrics::get_cust_tickets_stats ();

                    if($compare) {
                        $compare_created_tickets = KrifsMetrics::get_cust_created_tickets ($filter['compare_date_start'], $filter['compare_date_end']);
                        $compare_closed_tickets = KrifsMetrics::get_cust_closed_tickets ($filter['compare_date_start'], $filter['compare_date_end'], $compare_tot_closed_tickets);
                        $compare_avg_closing_times = KrifsMetrics::get_cust_average_closing_time ($filter['compare_date_start'], $filter['compare_date_end']);
                    }
                    $tot_open_tickets = 0;
                    $tot_open_tickets_ignored = 0;
                    $tot_created_tickets = 0;
                    $oldest_ticket_date = time () + 1000;
                    $avg_closing_time = 0;
                    $cnt_closing_time = 0;

                    if($compare) {
                        $compare_tot_open_tickets = 0;
                        $compare_tot_open_tickets_ignored = 0;
                        $compare_tot_created_tickets = 0;
                        $compare_oldest_ticket_date = time () + 1000;
                        $compare_avg_closing_time = 0;
                        $compare_cnt_closing_time = 0;
                    }

                    $tot_stats = array ();
                    foreach ($GLOBALS ['TICKET_STATUSES'] as $stat_code => $stat_name) $tot_stats[$stat_code] = 0;

                    if($compare) {
                        $compare_tot_stats = array ();
                        foreach ($GLOBALS ['TICKET_STATUSES'] as $stat_code => $stat_name) $compare_tot_stats[$stat_code] = 0;
                    }

                    $values = array();
                    $values_irs = array();

                    foreach($customers_list as $customer_id => $customer_name)
                    {
                            $flt['customer_id'] = $customer_id;
                            $flt['d_start'] = $filter['date_start'];
                            $flt['d_end'] = $filter['date_end'];

                            $values[$customer_id] = KrifsMetrics::get_tot_tickets_times($flt);
                            $values_irs[$customer_id] = KrifsMetrics::get_tot_irs_times($flt);
                    }

                    if($compare) {
                        foreach($compare_customers_list as $customer_id => $customer_name)
                        {
                                $flt['customer_id'] = $customer_id;
                                $flt['d_start'] = $filter['compare_date_start'];
                                $flt['d_end'] = $filter['compare_date_end'];

                                $compare_values[$customer_id] = KrifsMetrics::get_tot_tickets_times($flt);
                                $compare_values_irs[$customer_id] = KrifsMetrics::get_tot_irs_times($flt);
                        }
                    }

                    foreach ($customers_list as $customer_id => $customer_name)
                    if (isset($open_tickets[$customer_id]))
                    {
                            $tot_open_tickets+= $open_tickets[$customer_id];
                            $tot_open_tickets_ignored+= $open_tickets_ignored[$customer_id];
                            $tot_created_tickets+= $created_tickets[$customer_id];
                            if ($oldest_tickets_dates[$customer_id]) $oldest_ticket_date = min ($oldest_ticket_date, $oldest_tickets_dates[$customer_id]);
                            if ($avg_closing_times[$customer_id])
                            {
                                    $cnt_closing_time++;
                                    $avg_closing_time+= $avg_closing_times[$customer_id];
                            }
                            if (is_array($tickets_by_status[$customer_id]))
                            {
                                    $total = 0;
                                    foreach ($tickets_by_status[$customer_id] as $stat=>$cnt)  {
                                        $tot_stats[$stat]+= $cnt;
                                        $total += $cnt;
                                    }
                            }

                            $pie_data .= '';
                            foreach($tot_stats as $key => $value) {
                                $p = round((100*$value)/$total, 2);
                                if($value == 0)
                                    continue;
                                $pie_data .= "{y : {$p}, val: {$value}, name: '{$GLOBALS ['TICKET_STATUSES'][$key]}'},";
                            }
                            $this->assign('pie_data', trim($pie_data, ','));

                            if($filter['compare_customer_id'] != $filter['customer_id']) {
                                $customer_data = "{y: {$tot_open_tickets}, name: '{$chart_customer}'}";
                                $customer_data .= ",{y: {$tot_created_tickets}, name: '{$chart_customer}'}";
                            } else {
                                $customer_data = "{y: {$tot_created_tickets}, name: '{$chart_customer}'}";
                            }

                            $tmp = (!empty($closed_tickets[$filter['customer_id']])) ? $closed_tickets[$filter['customer_id']] : 0;
                            $customer_data .= ",{y: {$tmp}, name: '{$chart_customer}'}";

                            $tmp = minutes2hours($values[$filter['customer_id']]['work_time']);
                            $tmp2 = format_interval_minutes($values[$filter['customer_id']]['work_time']);
                            $customer_data .= ",{y: {$tmp}, name: '{$chart_customer}', time: '{$tmp2}'}";

                            $tmp = minutes2hours($values_irs[$filter['customer_id']]['work_time']);
                            $tmp2 = format_interval_minutes($values_irs[$filter['customer_id']]['work_time']);
                            $customer_data .= ",{y: {$tmp}, name: '{$chart_customer}', time: '{$tmp2}'}";

                            $tmp = minutes2hours($values[$filter['customer_id']]['bill_time']);
                            $tmp2 = format_interval_minutes($values[$filter['customer_id']]['bill_time']);
                            $customer_data .= ",{y: {$tmp}, name: '{$chart_customer}', time: '{$tmp2}'}";

                            $tmp = minutes2hours($values_irs[$filter['customer_id']]['bill_amount']);
                            $tmp2 = format_interval_minutes($values_irs[$filter['customer_id']]['bill_amount']);
                            $customer_data .= ",{y: {$tmp}, name: '{$chart_customer}', time: '{$tmp2}'}";

                            $this->assign('customer_data', $customer_data);
                    }

                    if($compare) {
                        foreach ($compare_customers_list as $customer_id => $customer_name)
                        if (isset($open_tickets[$customer_id]))
                        {
                                $compare_tot_open_tickets+= $open_tickets[$customer_id];
                                $compare_tot_open_tickets_ignored+= $open_tickets_ignored[$customer_id];
                                $compare_tot_created_tickets+= $compare_created_tickets[$customer_id];
                                if ($oldest_tickets_dates[$customer_id]) $compare_oldest_ticket_date = min ($oldest_ticket_date, $oldest_tickets_dates[$customer_id]);
                                if ($avg_closing_times[$customer_id])
                                {
                                        $compare_cnt_closing_time++;
                                        $compare_avg_closing_time+= $compare_avg_closing_times[$customer_id];
                                }
                                if (is_array($tickets_by_status[$customer_id]))
                                {
                                        $total = 0;
                                        foreach ($tickets_by_status[$customer_id] as $stat=>$cnt) {
                                            $compare_tot_stats[$stat]+= $cnt;
                                            $total += $cnt;
                                        }
                                }

                                $compare_pie_data .= '';
                                foreach($compare_tot_stats as $key => $value) {
                                    $p = round((100*$value)/$total, 2);
                                    if($value == 0)
                                        continue;
                                    $compare_pie_data .= "{y : {$p}, val: {$value}, name: '{$GLOBALS ['TICKET_STATUSES'][$key]}'},";
                                }
                                $this->assign('compare_pie_data', trim($compare_pie_data, ','));
                                if($filter['compare_customer_id'] != $filter['customer_id']) {
                                    $compare_customer_data = "{y: {$compare_tot_open_tickets}, name: '{$chart_compare_customer}'}";
                                    $compare_customer_data .= ",{y: {$compare_tot_created_tickets}, name: '{$chart_compare_customer}'}";
                                } else {
                                    $compare_customer_data = "{y: {$compare_tot_created_tickets}, name: '{$chart_compare_customer}'}";
                                }

                                $tmp = (!empty($compare_closed_tickets[$filter['compare_customer_id']])) ? $compare_closed_tickets[$filter['compare_customer_id']] : 0;
                                $compare_customer_data .= ",{y: {$tmp}, name: '{$chart_compare_customer}'}";

                                $tmp = minutes2hours($compare_values[$filter['compare_customer_id']]['work_time']);
                                $tmp2 = format_interval_minutes($compare_values[$filter['compare_customer_id']]['work_time']);
                                $compare_customer_data .= ",{y: {$tmp}, name: '{$chart_compare_customer}', time: '{$tmp2}'}";

                                $tmp = minutes2hours($compare_values_irs[$filter['compare_customer_id']]['work_time']);
                                $tmp2 = format_interval_minutes($compare_values_irs[$filter['compare_customer_id']]['work_time']);
                                $compare_customer_data .= ",{y: {$tmp}, name: '{$chart_compare_customer}', time: '{$tmp2}'}";

                                $tmp = minutes2hours($compare_values[$filter['compare_customer_id']]['bill_time']);
                                $tmp2 = format_interval_minutes($compare_values[$filter['compare_customer_id']]['bill_time']);
                                $compare_customer_data .= ",{y: {$tmp}, name: '{$chart_compare_customer}', time: '{$tmp2}'}";

                                $tmp = minutes2hours($compare_values_irs[$filter['compare_customer_id']]['bill_amount']);
                                $tmp2 = format_interval_minutes($compare_values_irs[$filter['compare_customer_id']]['bill_amount']);
                                $compare_customer_data .= ",{y: {$tmp}, name: '{$chart_compare_customer}', time: '{$tmp2}'}";

                                $this->assign('compare_customer_data', $compare_customer_data);
                        }

                        if ($compare_cnt_closing_time>0) $compare_avg_closing_time = intval($compare_avg_closing_time / $compare_cnt_closing_time);
                        $this->assign ('compare_values', $compare_values);
                        $this->assign ('compare_values_irs', $compare_values_irs);
                        $this->assign ('compare_customers_list', $compare_customers_list);
                        $this->assign ('compare_open_tickets', $compare_open_tickets);
                        $this->assign ('compare_open_tickets_ignored', $compare_open_tickets_ignored);
                        $this->assign ('compare_created_tickets', $compare_created_tickets);
                        $this->assign ('compare_closed_tickets', $compare_closed_tickets);
                        $this->assign ('compare_avg_closing_times', $compare_avg_closing_times);


                        $this->assign ('compare_tot_open_tickets', $compare_tot_open_tickets);
                        $this->assign ('compare_tot_open_tickets_ignored', $compare_tot_open_tickets_ignored);
                        $this->assign ('compare_tot_created_tickets', $compare_tot_created_tickets);
                        $this->assign ('compare_tot_closed_tickets', $compare_tot_closed_tickets);
                        $this->assign ('compare_oldest_ticket_date', $compare_oldest_ticket_date);
                        $this->assign ('compare_avg_closing_time', $compare_avg_closing_time);
                        $this->assign ('compare_tot_stats', $compare_tot_stats);
                        $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
                    }
                    //debug($values[361]);
                    $this->assign ('values', $values);
                    $this->assign ('values_irs', $values_irs);
                    $this->assign ('customers_list', $customers_list);
                    $this->assign('full_customers_list', $full_customers_list);
                    $this->assign ('open_tickets', $open_tickets);
                    $this->assign ('open_tickets_ignored', $open_tickets_ignored);
                    $this->assign ('created_tickets', $created_tickets);
                    $this->assign ('closed_tickets', $closed_tickets);
                    $this->assign ('oldest_tickets_dates', $oldest_tickets_dates);
                    $this->assign ('avg_closing_times', $avg_closing_times);
                    $this->assign ('tickets_by_status', $tickets_by_status);



                    $this->assign ('tot_closed_tickets', $tot_closed_tickets);
                    $this->assign ('tot_open_tickets', $tot_open_tickets);
                    $this->assign ('tot_open_tickets_ignored', $tot_open_tickets_ignored);
                    $this->assign ('tot_created_tickets', $tot_created_tickets);
                    $this->assign ('oldest_ticket_date', $oldest_ticket_date);
                    $this->assign ('avg_closing_time', $avg_closing_time);
                    $this->assign ('tot_stats', $tot_stats);
                    $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            }

            $last_monday = get_first_monday ();
            $this_month_start = get_month_start ();
            $last_month_end = get_last_hour (strtotime('-1 day', $this_month_start));
            $last_month_start = get_month_start ($last_month_end);
            $oldest_date = KrifsMetrics::get_oldest_date ();
            $predefined_intervals = array (
                    1 => array (
                            'name' => 'This week',
                            'date_start' => get_first_hour($last_monday),
                            'date_end' => get_last_hour(),
                    ),
                    2 => array (
                            'name' => 'Last week',
                            'date_start' => get_first_hour(strtotime('-1 week', $last_monday)),
                            'date_end' => get_last_hour(strtotime('-1 day', $last_monday)),
                    ),
                    3 => array (
                            'name' => 'This month',
                            'date_start' => get_first_hour($this_month_start),
                            'date_end' => get_last_hour(),
                    ),
                    4 => array (
                            'name' => 'Last month',
                            'date_start' => get_first_hour($last_month_start),
                            'date_end' => get_last_hour($last_month_end),
                    ),
            );
            for ($year = date('Y'); $year>=date('Y',$oldest_date); $year--)
            {
                    $predefined_intervals[$year] = array (
                            'name' => 'Year '.$year,
                            'date_start' => strtotime('1 Jan '.$year.' 00:00:00'),
                            'date_end' => strtotime('31 Dec '.$year.' 23:59:59'),
                    );
            }

            foreach ($predefined_intervals as $idx => $interval)
            {
                    if ($interval['date_start']==$filter['date_start'] and $interval['date_end']==$filter['date_end'])
                    {
                            $predefined_intervals[$idx]['selected'] = true;
                    }
                    $predefined_intervals[$idx]['date_start_str'] = date('d M Y H:i:s',$predefined_intervals[$idx]['date_start']);
                    $predefined_intervals[$idx]['date_end_str'] = date('d M Y H:i:s',$predefined_intervals[$idx]['date_end']);
            }

            //Compare
            $compare_predefined_intervals = array (
                    1 => array (
                            'name' => 'This week',
                            'date_start' => get_first_hour($last_monday),
                            'date_end' => get_last_hour(),
                    ),
                    2 => array (
                            'name' => 'Last week',
                            'date_start' => get_first_hour(strtotime('-1 week', $last_monday)),
                            'date_end' => get_last_hour(strtotime('-1 day', $last_monday)),
                    ),
                    3 => array (
                            'name' => 'This month',
                            'date_start' => get_first_hour($this_month_start),
                            'date_end' => get_last_hour(),
                    ),
                    4 => array (
                            'name' => 'Last month',
                            'date_start' => get_first_hour($last_month_start),
                            'date_end' => get_last_hour($last_month_end),
                    ),
            );
            for ($year = date('Y'); $year>=date('Y',$oldest_date); $year--)
            {
                    $compare_predefined_intervals[$year] = array (
                            'name' => 'Year '.$year,
                            'date_start' => strtotime('1 Jan '.$year.' 00:00:00'),
                            'date_end' => strtotime('31 Dec '.$year.' 23:59:59'),
                    );
            }

            foreach ($compare_predefined_intervals as $idx => $interval)
            {
                    if ($interval['date_start']==$filter['compare_date_start'] and $interval['date_end']==$filter['compare_date_end'])
                    {
                            $compare_predefined_intervals[$idx]['selected'] = true;
                    }
                    $compare_predefined_intervals[$idx]['date_start_str'] = date('d M Y H:i:s',$compare_predefined_intervals[$idx]['date_start']);
                    $compare_predefined_intervals[$idx]['date_end_str'] = date('d M Y H:i:s',$compare_predefined_intervals[$idx]['date_end']);
            }

            $this->assign ('filter', $filter);
            $this->assign ('customer_admin', $customer_admin);
            $this->assign ('predefined_intervals', $predefined_intervals);
            $this->assign ('compare_predefined_intervals', $compare_predefined_intervals);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('metrics_compare_submit');

            $this->display ($tpl);
    }

    function metrics_compare_submit ()
    {
            check_auth ();
            $filter = $this->vars['filter'];

            if(!empty($this->vars['filter'])) {
                $filter = $this->vars['filter'];
                if(isset($_SESSION['metrics']['filter']['view_by']) AND isset($filter['view_by']) and ($filter['view_by'] != $_SESSION['metrics']['filter']['view_by'])) {
                    unset($_SESSION['metrics']['filter']);
                    $_SESSION['metrics']['filter']['view_by'] = $filter['view_by'];
                    return $this->mk_redir('metrics_compare');
                }

                if(!empty($filter['date_start']) AND !empty($filter['date_end'])) {
                    $_SESSION['metrics']['filter']['date_start'] = get_first_hour (js_strtotime ($filter['date_start']));
                    $_SESSION['metrics']['filter']['date_end'] = get_last_hour (js_strtotime ($filter['date_end']));
                }

                if(!empty($filter['compare_date_start']) AND !empty($filter['compare_date_end'])) {
                    $_SESSION['metrics']['filter']['compare_date_start'] = get_first_hour (js_strtotime ($filter['compare_date_start']));
                    $_SESSION['metrics']['filter']['compare_date_end'] = get_last_hour (js_strtotime ($filter['compare_date_end']));
                }
                if(!empty($filter['view_by'])) {
                    $_SESSION['metrics']['filter']['view_by'] = $filter['view_by'];
                }
                if(empty($filter['customer_id'])) {
                    unset($_SESSION['metrics']['filter']['customer_id']);
                } else {
                    $_SESSION['metrics']['filter']['customer_id'] = $filter['customer_id'];
                }
                if(isset($filter['compare_customer_id'])) {
                    $_SESSION['metrics']['filter']['compare_customer_id'] = $filter['compare_customer_id'];
                }
            }

            return $this->mk_redir ('metrics_compare');
    }

    function metrics_user ()
    {
            check_auth ();
            $tpl = 'metrics_user.tpl';
            class_load("Timesheet");

            $user = new User ($this->vars['user_id']);
            if (!$user->id) return $this->mk_redir ('metrics');

            $filter = $_SESSION['metrics']['filter'];
            if (!$filter['date_start']) $filter['date_start'] = strtotime ('-1 week');
            if (!$filter['date_end']) $filter['date_end'] = time ();
            if ($this->vars['date_start']) $filter['date_start'] = $this->vars['date_start'];
            if ($this->vars['date_end']) $filter['date_end'] = $this->vars['date_end'];

            if ($filter['date_start'] >= $filter['date_end']) $filter['date_start'] = strtotime ('-1 day', $filter['date_end']);

            $user_metrics = KrifsMetrics::get_user_metrics ($user->id, $filter['date_start'], $filter['date_end']);
            $timesheets = array();
            foreach($user_metrics as $metric)
            {
                    $timesheets[$metric->date] = Timesheet::get_timesheet ($user->id, $metric->date);
                    if (!$timesheets[$metric->date]->id) {
                            $timesheets[$metric->date]->load_unassigned_details ();
                    }
            }

            $params = $this->set_carry_fields (array('user_id'));
            $this->assign ('user', $user);
            $this->assign ('user_metrics', $user_metrics);
            $this->assign ('timesheets', $timesheets);
            $this->assign ('filter', $filter);
            $this->set_form_redir ('metrics_user_submit', $params);

            $this->display ($tpl);
    }

    function metrics_user_submit ()
    {
            check_auth ();
            $filter = $this->vars['filter'];
            $filter['date_start'] = get_first_hour (js_strtotime ($filter['date_start']));
            $filter['date_end'] = get_last_hour (js_strtotime ($filter['date_end']));
            $_SESSION['metrics']['filter'] = $filter;

            $params = $this->set_carry_fields (array('user_id'));
            return $this->mk_redir ('metrics_user', $params);
    }
}
?>

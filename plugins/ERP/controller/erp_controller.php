<?php
ini_set('display_errors', 1);
class ErpController extends PluginController{
    protected $plugin_name = "ERP";
    
    function __construct() {
            $this->base_plugin_dir = dirname(__FILE__).'/../';
            parent::__construct();
    }
    
    /** Displays the page for checking the ERP syncronisation of customers */
    function erp_sync_customers ()
    {
            check_auth ();

            $tpl = 'erp_sync_customers.tpl';

            $sync = new ErpSync ();

            $erp_customers = $sync->get_erp_customers ();

            $this->assign ('erp_customers', $erp_customers);
            $this->assign ('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
            $this->assign ('ERP_CUST_SUBTYPES', $GLOBALS['ERP_CUST_SUBTYPES']);
            $this->assign ('CUST_SUBTYPES', $GLOBALS['CUST_SUBTYPES']);
            $this->assign ('CUST_PRICETYPES', $GLOBALS['CUST_PRICETYPES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('erp_sync_customers_submit');

            $this->display ($tpl);
    }

    /** Performs the synchronization of the action types between the ERP and Keyos databases */
    function erp_sync_customers_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('erp_sync_customers');

            if ($this->vars['do_synchronize'])
            {
                    $sync = new ErpSync ();
                    $sync->sync_erp_customers ();
            }
            return $ret;
    }


    /** Displays the page for checking the ERP syncronisation of action types categories */
    function erp_sync_actypes_categories ()
    {
            check_auth ();
            $tpl = 'erp_sync_actypes_categories.tpl';

            $sync = new ErpSync ();
            $erp_actypes_categories = $sync->get_erp_actypes_categories ();

            $this->assign ('erp_actypes_categories', $erp_actypes_categories);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('erp_sync_actypes_categories_submit');

            $this->display ($tpl);
    }


    /** Performs the synchronization of the action types categories between the ERP and Keyos databases */
    function erp_sync_actypes_categories_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('erp_sync_actypes_categories');

            if ($this->vars['do_synchronize'])
            {
                    $sync = new ErpSync ();
                    $sync->sync_erp_actypes_categories ();
            }
            return $ret;
    }


    /** Displays the page for checking the ERP syncronisation of action types */
    function erp_sync_actypes ()
    {
            check_auth ();
            $tpl = 'erp_sync_actypes.tpl';

            $sync = new ErpSync ();
            $erp_actypes = $sync->get_erp_actypes ();
            $actypes_categories_list = ActionTypeCategory::get_categories_list ();

            $this->assign ('erp_actypes', $erp_actypes);
            $this->assign ('actypes_categories_list', $actypes_categories_list);
            $this->assign ('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
            $this->assign ('CUST_SUBTYPES', $GLOBALS['CUST_SUBTYPES']);
            $this->assign ('PRICE_TYPES', $GLOBALS['PRICE_TYPES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('erp_sync_actypes_submit');

            $this->display ($tpl);
    }


    /** Performs the synchronization of the action types between the ERP and Keyos databases */
    function erp_sync_actypes_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('erp_sync_actypes');

            if ($this->vars['do_synchronize'])
            {
                    $sync = new ErpSync ();
                    $sync->sync_erp_actypes ();
            }
            return $ret;
    }


    /** Displays the page for checking the ERP syncronisation of activities (timesheets) */
    function erp_sync_activities ()
    {
            check_auth ();
            $tpl = 'erp_sync_activities.tpl';

            $sync = new ErpSync ();
            $erp_activities = $sync->get_erp_activities ();

            $this->assign ('erp_activities', $erp_activities);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('erp_sync_activities_submit');

            $this->display ($tpl);
    }

    /** Performs the synchronization of the activities (for Timesheets) between the ERP and Keyos databases */
    function erp_sync_activities_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('erp_sync_activities');

            if ($this->vars['do_synchronize'])
            {
                    $sync = new ErpSync ();
                    $sync->sync_erp_activities ();
            }
            return $ret;
    }

    /** Displays the page for checking the ERP syncronisation of engineers */
    function erp_sync_engineers ()
    {
            check_auth ();
            $tpl = 'erp_sync_engineers.tpl';

            $sync = new ErpSync ();
            $erp_engineers = $sync->get_erp_engineers ();

            $this->assign ('erp_engineers', $erp_engineers);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('erp_sync_engineers_submit');

            $this->display ($tpl);
    }


    /** Performs the synchronization of the engineers between the ERP and Keyos databases */
    function erp_sync_engineers_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('erp_sync_engineers');

            if ($this->vars['do_synchronize'])
            {
                    $sync = new ErpSync ();
                    $sync->sync_erp_engineers ();
            }
            return $ret;
    }


    //XXXXXXXXX
    function mssql ()
    {
            $conn = mssql_connect (ERP_DB_HOST, ERP_DB_USER, ERP_DB_PWD);
            mssql_select_db (ERP_DB_NAME);


            // Articles
            $q = 'SELECT stock.s_id, stock.s_modele, stock.s_id_rayon, rayons.nom, familles.nom, ';
            $q.= 'familles.id,stock.s_cat2,stock.s_cat3,stock.s_cat4,stock.s_id_famil,rayons.id ';
            $q.= 'FROM rayons, familles, stock ';
            $q.= 'WHERE stock.s_id_rayon="R000000004" AND ';
            $q.= '(stock.s_id_famil="Q1J50PXW11" OR stock.s_id_famil="S1J50PYRYC") AND ';
            $q.= 'rayons.id=stock.s_id_rayon AND familles.id=s_id_famil ';
            /*
            stock.s_cat2 is the categorie2 (Basic/TC/Keypro)
            stock.s_cat3 is the categorie3 (Basic/TC level1/ TC level2 / TC level3/Keypro /GlobalPro)
            stock.s_cat4 is the categorie3 (HourlyBased/FixedBased)

            stock.s_id_rayon='R000000004' (service ks)
            stock.s_id_famil='Q1J50PXW11' (regie tc)
            stock.s_id_famil='S1J50PYRYC' (regie autre)
            */

            // Customers
            $q = 'SELECT c_nom,c_id,c_adresse,c_adresse2,c_codep,c_ville,c_pays,c_tarif,c_cle2 FROM cli ';
            // c_cle2 is the Keyos reference

            // Engineers articles
            $q = 'SELECT stock.s_id, stock.s_modele, stock.s_id_rayon, rayons.nom, familles.nom, ';
            $q.= 'familles.id,stock.s_id_famil,rayons.id ';
            $q.= 'FROM rayons, familles, stock ';
            $q.= 'WHERE stock.s_id_rayon="R000000004" AND ';
            $q.= 'stock.s_id_famil="Q1KR12NDTT" AND rayons.id=stock.s_id_rayon AND familles.id=s_id_famil ';
            // stock.s_id_famil='Q1KR12NDTT' (autre)


            $h = mssql_query ($q);

            echo "<table border=1>";
            $cnt=1; $header = false;
            while ($c = mssql_fetch_assoc ($h))
            {
                    //debug ($c);

                    if (!$header)
                    {
                            echo '<tr><td></td>';
                            foreach ($c as $key => $val) echo "<td>$key";
                            echo "</tr>";
                            $header = true;
                    }
                    echo "<tr><td>$cnt ";
                    foreach ($c as $key => $val) echo "<td>$val";
                    echo "</tr>";

                    $cnt++;
            }
            mssql_close ();
            debug ($conn);
            die;
    }


    /****************************************************************/
    /* Management of intervention report exports			*/
    /****************************************************************/

    /** Displays the page for managing intervention exports */
    function manage_interventions_exports ()
    {          
            class_load ('InterventionsExport');
            $tpl = 'manage_interventions_exports.tpl';
            if($this->vars['am']) $am=$this->vars['am'];
            else $am= DEFAULT_ACCOUNT_MANAGER;

            $exports = InterventionsExport::get_exports ($am);

            //debug($exports);

            $this->assign ('exports', $exports);
            $this->assign ('error_msg', error_msg ());
            $this->assign ('INTERVENTIONS_EXPORTS_STATS', $GLOBALS['INTERVENTIONS_EXPORTS_STATS']);

            $this->display ($tpl);
    }

    function interventions_exports_showirs()
    {
        class_load ('InterventionReport');
        class_load ('Customer');
        //check_auth(array('export_id' => $this->vars['id']));

        $tpl = 'erp/interventions_exports_showirs.html';
        $q = "select intervention_id from ".TBL_INTERVENTIONS_EXPORTS_IDS." where export_id=".$this->vars['id'];
        $ids = db::db_fetch_vector($q);
        $customers_list = Customer::get_customers_list ();
        $interventions = array();
        foreach($ids as $id)
        {
            $interventions[] = new InterventionReport($id);
        }
        $customers_list = Customer::get_customers_list ($customers_filter);
        $this->assign('interventions', $interventions);
        $this->assign('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
        $this->assign('customers_list', $customers_list);
        $this->assign ('error_msg', error_msg());
        $this->display($tpl);
    }



    /** Used as entry point by the ERP system to fetch intervention reports */
    // XXXXXX: TO BE REMOVED AFTER IMPLEMENTATION
    // we should add here the exporting by account manager...
    // the default manager is Keysource, so Mercator connector code should not change
    // This should export to mercator the IR's for the customers managed by KeySource,
    // and and to GestExp the IR's for the customers managed by MPI

    function interventions_fetch ()
    {
            class_load ('InterventionsExport');

            if(isset($this->vars['am']) and $this->vars['am']!='') $manager = $this->vars['am'];
            else $manager = DEFAULT_ACCOUNT_MANAGER; //this should be keysource

            if (InterventionsExport::has_export($manager))
            {
                    $export = new InterventionsExport ();
                    $export->save_data ();

                    $export->make_export ($manager, $_SERVER['REMOTE_ADDR']);
                    header('Content-Type: text/xml');
                    $export->serve_file ();
            }
            else
            {
                    // There is nothing to export, serve an empty XML file
                    $tpl = 'interventions_export.xml';

                    $export = new InterventionsExport ();
                    $this->assign ('export', $export);
                    $this->assign ('base_url', 'http://'.get_base_url());
                    //$this->assign ('base_url', $KEYOS_BASE_URL);
                    header('Content-Type: text/xml');
                    $this->display_template_only ($tpl);
            }

            die;
    }

    /** Used for serving the XSD schema definition for XML intervention reports exports */
    function interventions_export_schema ()
    {
            $tpl = 'erp/interventions_export.xsd';
            header('Content-Type: text/xml');
            $this->display_template_only ($tpl);
    }

    /** Used as entry point by the ERP system to fetch the list of pending exports */
    // XXXXXX: TO BE REMOVED AFTER IMPLEMENTATION
    // the same mod as for interventions_fetch. Must implement retrieval based on the account manager
    function pending_interventions_export ()
    {
            class_load ('InterventionsExport');

            if(isset($this->vars['am']) and $this->vars['am']!='') $manager = $this->vars['am'];
            else $manager = DEFAULT_ACCOUNT_MANAGER; //this should be keysource

            $tpl = 'pending_interventions_export.xml';

            $exports = InterventionsExport::get_exports ($manager, array('get_peding' => true));

            $this->assign ('exports', $exports);
            header('Content-Type: text/xml');
            $this->display_template_only ($tpl);

            die;
    }

    /** Used for serving the XSD schema definition for XML with the pending interventions exports */
    function pending_interventions_export_schema ()
    {
            $tpl = 'pending_interventions_export.xsd';
            header('Content-Type: text/xml');
            $this->display_template_only ($tpl);
    }


    /** Receives a request from the ERP system with the MD5 checksum of the fetched file */
    function interventions_confirm_file ()
    {
            if ($this->vars['id'])
            {
                    class_load ('InterventionsExport');
                    $export = new InterventionsExport ($this->vars['id']);
                    $checksum_ok = $export->is_file_confirmation_ok ($this->vars['md5'], $_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
                    if ($checksum_ok) echo "ok";
                    else echo "incorrect \nexpected md5: ".$export->md5_file;
                    die;
            }
    }

    /** Receives a request from the ERP system with the control sums for the import */
    function interventions_confirm_import ()
    {
            if ($this->vars['id'])
            {
                    class_load ('InterventionsExport');
                    $export = new InterventionsExport ($this->vars['id']);
                    $checksum_ok = $export->is_import_confirmation_ok ($this->vars['cnt_interventions'], $this->vars['tbb_sum'],$_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
                    if ($checksum_ok) echo "ok";
                    else echo "incorrect \nexpected cnt_interventions: ".$export->cnt_interventions." \nexpected tbb_sum: ".$export->tbb_sum;
                    die;
            }
    }


    /** Receives a request from the ERP system to re-transfer the XML file */
    function interventions_retransfer ()
    {
            if ($this->vars['id'])
            {
                    class_load ('InterventionsExport');
                    $export = new InterventionsExport ($this->vars['id']);
                    header('Content-Type: text/xml');
                    $export->serve_file ();
            }
    }

    /** Displays the exported XML content for an intervention report, without making any actions on the export */
    function intervention_export_show ()
    {
            if ($this->vars['id'])
            {
                    class_load ('InterventionsExport');
                    $export = new InterventionsExport ($this->vars['id']);
                    header('Content-Type: text/xml');
                    $export->serve_file ();
            }
    }

    /****************************************************************/
    /* Management of timesheets exports				*/
    /****************************************************************/

    /** Displays the page for managing timesheets exports */
    function manage_timesheets_exports ()
    {
            class_load ('TimesheetsExport');
            $tpl = 'manage_timesheets_exports.tpl';

            //xxxxxxxxxxx
            $exports = TimesheetsExport::get_exports ();

            $this->assign ('exports', $exports);
            $this->assign ('error_msg', error_msg ());
            $this->assign ('INTERVENTIONS_EXPORTS_STATS', $GLOBALS['INTERVENTIONS_EXPORTS_STATS']);

            $this->display ($tpl);
    }

    /** Used as entry point by the ERP system to fetch timesheets */
    function timesheets_fetch ()
    {
            class_load ('TimesheetsExport');

            if (TimesheetsExport::has_export())
            {
                    $export = new TimesheetsExport ();
                    $export->save_data ();

                    $export->make_export ($_SERVER['REMOTE_ADDR']);
                    header('Content-Type: text/xml');

                    $export->serve_file ();
            }
            else
            {
                    // There is nothing to export, serve an empty XML file
                    $tpl = 'timesheets_export.xml';

                    $export = new TimesheetsExport ();
                    $this->assign ('export', $export);
                    $this->assign ('base_url', 'http://'.get_base_url());
                    header('Content-Type: text/xml');
                    $this->display_template_only ($tpl);
            }

            die;
    }

    /** Used for serving the XSD schema definition for XML timesheets exports */
    function timesheets_export_schema ()
    {
            $tpl = 'erp/timesheets_export.xsd';
            header('Content-Type: text/xml');
            $this->display_template_only ($tpl);
    }


    /** Used as entry point by the ERP system to fetch the list of pending timesheets exports */
    function pending_timesheets_export ()
    {
            class_load ('TimesheetsExport');
            $tpl = 'pending_interventions_export.xml';

            $exports = TimesheetsExport::get_exports (array('get_peding' => true));

            $this->assign ('exports', $exports);
            header('Content-Type: text/xml');
            $this->display_template_only ($tpl);

            die;
    }

    /** Used for serving the XSD schema definition for XML with the pending interventions exports */
    function pending_timesheets_export_schema ()
    {
            $tpl = 'pending_timesheets_export.xsd';
            header('Content-Type: text/xml');
            $this->display_template_only ($tpl);
    }


    /** Receives a request from the ERP system with the MD5 checksum of a fetched timesheets file */
    function timesheets_confirm_file ()
    {
            if ($this->vars['id'])
            {
                    class_load ('TimesheetsExport');
                    $export = new TimesheetsExport ($this->vars['id']);
                    $checksum_ok = $export->is_file_confirmation_ok ($this->vars['md5'], $_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
                    if ($checksum_ok) echo "ok";
                    else echo "incorrect \nexpected md5: ".$export->md5_file;
                    die;
            }
    }

    /** Receives a request from the ERP system with the control sums for the import of a timesheets file */
    function timesheets_confirm_import ()
    {
            if ($this->vars['id'])
            {
                    class_load ('TimesheetsExport');
                    $export = new TimesheetsExport ($this->vars['id']);
                    $checksum_ok = $export->is_import_confirmation_ok ($this->vars['cnt_timesheets'], $this->vars['work_time_sum'],$_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
                    if ($checksum_ok) echo "ok";
                    else echo "incorrect \nexpected cnt_timesheets: ".$export->cnt_timesheets." \nexpected work_time_sum: ".$export->work_time_sum;
                    die;
            }
    }


    /** Receives a request from the ERP system to re-transfer the XML file for a timesheets export */
    function timesheets_retransfer ()
    {
            if ($this->vars['id'])
            {
                    class_load ('TimesheetsExport');
                    $export = new TimesheetsExport ($this->vars['id']);
                    header('Content-Type: text/xml');
                    $export->serve_file ();
            }
    }

    /** Displays the exported XML content for a timesheet, without making any actions on the export */
    function timesheet_export_show ()
    {
            if ($this->vars['id'])
            {
                    class_load ('TimesheetsExport');
                    $export = new TimesheetsExport ($this->vars['id']);
                    header('Content-Type: text/xml');
                    $export->serve_file ();
            }
    }




    /****************************************************************/
    /* Customer orders management					*/
    /****************************************************************/

    /** Displays the page for managing customer orders */
    function manage_customer_orders ()
    {
            check_auth ();
            class_load ('CustomerOrder');
            $tpl = 'manage_customer_orders.tpl';


            if (isset($this->vars['customer_id'])) $_SESSION['manage_customer_orders']['customer_id'] = $this->vars['customer_id'];
            elseif ($this->locked_customer->id and !$this->vars['do_filter'])
            {
                    // If 'do_filter' is present in request or if we are in advanced search,
                    // the locked customer is ignored
                    $_SESSION['manage_customer_orders']['customer_id'] = $this->locked_customer->id;
            }

            $filter = $_SESSION['manage_customer_orders'];

            if (!isset($filter['status'])) $filter['status'] = ORDER_STAT_OPEN;
            if (!$filter['start']) $filter['start'] = 0;
            if (!$filter['limit']) $filter['limit'] = 50;

            $tot_customer_orders = 0;
            $customer_orders = CustomerOrder::get_orders ($filter, $tot_customer_orders);
            $pages = make_paging ($filter['limit'], $tot_customer_orders);
            if ($filter['start'] > $tot_customer_orders)
            {
                    $filter['start'] = 0;
                    $customer_orders = CustomerOrder::get_orders ($filter, $tot_customer_orders);
            }

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            $params = $this->set_carry_fields (array('do_filter'));

            // Load the tickets for each order
            for ($i=0; $i<count($customer_orders); $i++) $customer_orders[$i]->load_tickets ();

            // Mark the potential customer for locking
            if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];


            $this->assign ('customer_orders', $customer_orders);
            $this->assign ('tot_customer_orders', $tot_customer_orders);
            $this->assign ('filter', $filter);
            $this->assign ('pages', $pages);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('do_filter', $this->vars['do_filter']);
            $this->assign ('ORDER_STATS', $GLOBALS['ORDER_STATS']);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('manage_customer_orders_submit', $params);

            $this->display ($tpl);
    }


    /** Saves the filtering criteria for managing customer orders */
    function manage_customer_orders_submit ()
    {
            check_auth ();
            $params = array ('do_filter'=>1);
            $ret = $this->mk_redir ('manage_customer_orders', $params);
            $filter = $this->vars['filter'];

            if ($this->vars['go'] == 'prev') $filter['start'] = $filter['start'] - $filter['limit'];
            elseif ($this->vars['go'] == 'next') $filter['start'] = $filter['start'] + $filter['limit'];

            $_SESSION['manage_customer_orders'] = $filter;

            return $ret;
    }


    /** Displays the page for creating a new customer order */
    function customer_order_add ()
    {
            check_auth ();
            class_load ('CustomerOrder');
            $tpl = 'customer_order_add.tpl';

            $customer_order = new CustomerOrder ();
            if (!empty_error_msg())
            {
                    $data = array();
                    $customer_order->load_from_array (restore_form_data ('customer_order', false, $data));
            }
            if ($this->vars['customer_id'] and !$customer_order->customer_id) $customer_order->customer_id = $this->vars['customer_id'];

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            // Mark the potential customer for locking
            if ($customer_order->customer_id) $_SESSION['potential_lock_customer_id'] = $customer_order->customer_id;

            // Load the ticket, if it was specified
            if ($this->vars['ticket_id']) $this->assign ('ticket', new Ticket($this->vars['ticket_id']));

            $params = $this->set_carry_fields (array('do_filter', 'ret', 'returl', 'customer_id', 'ticket_id'));

            $this->assign ('customer_order', $customer_order);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('ORDER_STATS', $GLOBALS['ORDER_STATS']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('customer_order_add_submit', $params);

            $this->display ($tpl);
    }


    /** Saves a new customer order */
    function customer_order_add_submit ()
    {
            check_auth ();
            class_load ('CustomerOrder');

            $params = $this->set_carry_fields (array('do_filter'));
            if ($this->vars['ticket_id']) $ret = $this->mk_redir ('ticket_edit', array ('id' => $this->vars['ticket_id']), 'krifs');
            else $ret = $this->mk_redir ('manage_customer_orders', $params);
            $params = $this->set_carry_fields (array('do_filter', 'ret', 'returl', 'customer_id', 'ticket_id'));


            if ($this->vars['save'])
            {
                    $data = $this->vars['customer_order'];
                    $data['date'] = js_strtotime ($data['date']);

                    $customer_order = new CustomerOrder ();
                    $customer_order->load_from_array ($data);
                    //debug($customer_order);
                    if ($customer_order->is_valid_data ())
                    {
                            //debug("data seems valid");
                            $customer_order->save_data ();
                            $params['id'] = $customer_order->id;
                            unset ($params['customer_id']);
                            $ret = $this->mk_redir ('customer_order_edit', $params);

                            // If a ticket id was specified, mark the ticket as belonging to this order
                            if ($this->vars['ticket_id'])
                            {
                                    $ticket = new Ticket ($this->vars['ticket_id']);
                                    $ticket->customer_order_id = $customer_order->id;
                                    $ticket->billable = $customer_order->billable;
                                    $ticket->save_data ();
                            }
                    }
                    else
                    {
                            save_form_data ($data, 'customer_order');
                            $ret = $this->mk_redir ('customer_order_add', $params);
                    }
            }

            return $ret;
    }


    /** Displays the page for editing a customer order */
    function customer_order_edit ()
    {
            class_load ('CustomerOrder');
            $customer_order = new CustomerOrder ($this->vars['id']);
            if (!$customer_order->id) return $this->mk_redir ('manage_customer_orders');
            check_auth (array('customer_id' => $customer_order->customer_id));
            $tpl = 'customer_order_edit.tpl';

            if (!empty_error_msg())
            {
                    $data = array();
                    $customer_order->load_from_array (restore_form_data ('customer_order', false, $data));
            }

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            // Mark the potential customer for locking
            if ($customer_order->customer_id) $_SESSION['potential_lock_customer_id'] = $customer_order->customer_id;

            $tickets = $customer_order->get_tickets ();

            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'returl'));

            $this->assign ('customer_order', $customer_order);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('tickets', $tickets);
            $this->assign ('ORDER_STATS', $GLOBALS['ORDER_STATS']);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            if ($this->vars['returl']) $this->assign ('ret_url', urlencode($this->vars['returl']));
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('customer_order_edit_submit', $params);

            $this->display ($tpl);
    }


    /** Saves a customer order */
    function customer_order_edit_submit ()
    {
            class_load ('CustomerOrder');
            $customer_order = new CustomerOrder ($this->vars['id']);
            check_auth (array('customer_id' => $customer_order->customer_id));

            $params = $this->set_carry_fields (array('do_filter'));
            if ($this->vars['returl']) $ret = $this->vars['returl'];
            elseif ($this->vars['ticket_id']) $ret = $this->mk_redir ('ticket_edit', array ('id' => $this->vars['ticket_id']), 'krifs');
            else $ret = $this->mk_redir ('manage_customer_orders', $params);
            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'returl'));

            if ($this->vars['save'] and $customer_order->id)
            {
                    $data = $this->vars['customer_order'];
                    $data['billable'] = ($data['billable'] ? 1 : 0);
                    $data['date'] = js_strtotime ($data['date']);
                    $customer_order->load_from_array ($data);

                    if ($customer_order->is_valid_data ())
                    {
                            $customer_order->save_data ();
                    }
                    else
                    {
                            save_form_data ($data, 'customer_order');
                    }
                    $ret = $this->mk_redir ('customer_order_edit', $params);
            }

            return $ret;
    }


    /** Deletes a customer order */
    function customer_order_delete ()
    {
            class_load ('CustomerOrder');
            $customer_order = new CustomerOrder ($this->vars['id']);
            check_auth (array('customer_id' => $customer_order->customer_id));
            $params = $this->set_carry_fields (array('do_filter'));
            $ret = $this->mk_redir ('manage_customer_orders', $params);

            if ($customer_order->id and $customer_order->can_delete ())
            {
                    $customer_order->delete ();
            }

            return $ret;
    }


    /** Displays the page for adding existing tickets to orders */
    function customer_order_tickets ()
    {
            class_load ('CustomerOrder');
            $order = new CustomerOrder ($this->vars['id']);
            if (!$order->id) return $this->mk_redir ('manage_customer_orders');
            check_auth (array('customer_id' => $order->customer_id));
            $tpl = 'customer_order_tickets.tpl';

            $tickets = Ticket::get_tickets (array(
                    'customer_id' => $order->customer_id,
                    'order_by' => 'id',
                    'order_dir' => 'desc',
                    'status' => -1,
                    'customer_order_id' => -1
            ), $no_count);

            $params = $this->set_carry_fields (array('id', 'returl'));

            $this->assign ('order', $order);
            $this->assign ('tickets', $tickets);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('customer_order_tickets_submit', $params);
            $this->display ($tpl);
    }


    /** Add the selected tickets to the customer order */
    function customer_order_tickets_submit ()
    {
            class_load ('CustomerOrder');
            $order = new CustomerOrder ($this->vars['id']);
            check_auth (array('customer_id' => $order->customer_id));

            $params = $this->set_carry_fields (array('id', 'returl'));
            $ret = $this->mk_redir ('customer_order_edit', $params);

            if ($this->vars['save'] and $order->id and is_array($this->vars['tickets']))
            {
                    foreach ($this->vars['tickets'] as $id)
                    {
                            $ticket = new Ticket ($id);
                            $ticket->customer_order_id = $order->id;
                            $ticket->save_data ();

                            for ($i=0; $i<count($ticket->details); $i++)
                            {
                                    $ticket->details[$i]->customer_order_id = $order->id;
                                    $ticket->details[$i]->save_data ();
                            }
                    }
            }

            return $ret;
    }


    /** Removes a ticket from and order */
    function customer_order_ticket_remove ()
    {
            class_load ('CustomerOrder');
            $order = new CustomerOrder ($this->vars['id']);
            check_auth (array('customer_id' => $order->customer_id));

            $ticket = new Ticket ($this->vars['ticket_id']);
            $params = $this->set_carry_fields (array('id', 'returl'));
            $ret = $this->mk_redir ('customer_order_edit', $params);

            if ($ticket->id and $order->id)
            {
                    if (($ticket->customer_order_id = $order->id))
                    {
                            $ticket->customer_order_id = 0;
                            $ticket->save_data ();
                    }

                    for ($i=0; $i<count($ticket->details); $i++)
                    {
                            $ticket->details[$i]->customer_order_id = 0;
                            $ticket->details[$i]->save_data ();
                    }
            }

            return $ret;
    }
}
?>

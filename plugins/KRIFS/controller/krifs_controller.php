<?php
class_load ('Ticket');
class_load ('TicketDetail');
class_load ('TicketAttachment');
class_load ('Computer');
class_load ('Customer');
class_load ('User');
class_load ('Group');
class_load ('Activity');
class_load ('ActionType');


class KrifsController extends PluginController{

    protected $plugin_name = "KRIFS";

    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
//        $this->do_not_cache_ops = array(
//            'intervention_approval_console',
//        );
    }
    
    function manage_tickets ()
    {
            // If a customer user has arrived here, send it to the customer-specific ticket
            $uid = get_uid ();
            if ($uid)
            {
                    $user = New User ($uid);
                    if ($user->customer_id)
                    {
                            return $this->mk_redir ('manage_tickets', array(), 'customer_krifs');
                    }
            }

            class_load ('KrifsSavedSearch');
            $tpl = 'manage_tickets.tpl';

            $advanced = $this->vars['advanced'];
            $do_search = $this->vars['do_search'];
            $search_id = $this->vars['search_id'];

            if (isset($this->vars['customer_id']))
            {
                    $_SESSION['tickets']['filter']['customer_id'] = $this->vars['customer_id'];
            }
            elseif ($this->locked_customer->id and !$this->vars['do_filter'] and !$this->vars['advanced'])
            {
                    // If 'do_filter' is present in request or if we are in advanced search,
                    // the locked customer is ignored
                    $_SESSION['tickets']['filter']['customer_id'] = $this->locked_customer->id;
            }

            if (isset($this->vars['user_id'])) $_SESSION['tickets']['filter']['user_id'] = $this->vars['user_id'];
            if (isset($this->vars['view'])) $_SESSION['tickets']['filter']['view'] = $this->vars['view'];
            if (isset($this->vars['escalated_only'])) $_SESSION['tickets']['filter']['escalated_only'] = $this->vars['escalated_only'];
            if (isset($this->vars['status'])) $_SESSION['tickets']['filter']['status'] = $this->vars['status'];
            if (isset($this->vars['type'])) $_SESSION['tickets']['filter']['type'] = $this->vars['type'];
            if (isset($this->vars['types_main_only'])) $_SESSION['tickets']['filter']['types_main_only'] = $this->vars['types_main_only'];

            $filter = $_SESSION['tickets']['filter'];
            if($filter['customer_id']) $filter['customer_ids'] = $filter['customer_id'];

            // Check authorization
            if ($filter['customer_id'] > 0 and !is_array($filter['customer_id']))
            {
                    // Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
                    // This way he can return to this page, without getting again "Permission Denied".

                    unset ($_SESSION['tickets']['filter']['customer_id']);
                    check_auth (array('customer_id' => $filter['customer_id']));
                    $_SESSION['tickets']['filter']['customer_id'] = $filter['customer_id'];
            }
            else check_auth ();

            if (!isset ($filter['order_by'])) $filter['order_by'] = 'last_modified';
            if (!isset ($filter['order_dir'])) $filter['order_dir'] = 'DESC';
            if (!isset ($filter['user_id'])) $filter['user_id'] = get_uid ();
            if (!isset ($filter['view'])) $filter['view'] = 1;
            if (!isset ($filter['status'])) $filter['status'] = -1;
            if (!isset ($filter['start'])) $filter['start'] = 0;
            if (!isset ($filter['limit'])) $filter['limit'] = 50;
            if ($filter['types_main_only']) $filter['type'] = 0;

            // Check for request to select all users
            if ($filter['user_id'] == -1) unset ($filter['user_id']);
            // Check for request to select all customers
            if ($filter['customer_id'] == -1) unset ($filter['customer_id']);

            // Extract the list of Krifs customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('has_krifs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => 1, 'account_manager'=>$filter['account_manager']);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);
            $all_customers_list = Customer::get_customers_list (array('show_ids'=>1, 'active'=>-1)); // In case there are ticket linked to disabled users

            if (!$advanced)
            {
                    // For simple filtering, make sure the values are not in arrays
                    if (is_array ($filter['user_id'])) $filter['user_id'] = get_uid();
                    if (is_array ($filter['customer_id'])) $filter['customer_id'] = 0;
                    if (is_array ($filter['type'])) $filter['type'] = 0;
                    if (is_array ($filter['status'])) $filter['status'] = 0;
                    unset ($filter['keywords']);
            }
            else
            {
                    // If switching to advanced search, put the values in arrays where needed
                    if (!is_array ($filter['user_id']))
                    {
                            if ($filter['user_id']) array ($filter['user_id']);
                            $filter['user_id'] = array ();
                    }
                    if (!is_array ($filter['customer_id']))
                    {
                            if ($filter['customer_id']) $filter['customer_id'] = array ($filter['customer_id']);
                            else $filter['customer_id'] = array ();
                    }
                    if (!is_array ($filter['status']))
                    {
                            if ($filter['status']) $filter['status'] = array ($filter['status']);
                            else $filter['status'] = array ();
                    }
                    if (!is_array ($filter['type']))
                    {
                            if ($filter['type']) $filter['type'] = array ($filter['type']);
                            else $filter['type'] = array ();
                    }

                    // Make sure that the selected customers are allowed for this user
                    if ($this->current_user->restrict_customers)
                    {
                            for ($i = count($filter['customer_id'])-1; $i>=0; $i--)
                            {
                                    if (!isset($customers_list[$filter['customer_id'][$i]])) array_splice($filter['customer_id'], $i, 1);
                            }
                    }
            }

            if (!$advanced or ($advanced and $do_search))
            {
                    // Results are shown only for simple filtering or
                    // for advanced searching if the search command was given
                    $tot_tickets = 0;

                    // Check if the user has restricted access to customers
                    if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;

                    if ($filter['show_scheduled']) $filter['load_schedule'] = true;

                    $tickets = Ticket::get_tickets ($filter, $tot_tickets);
                    if ($tot_tickets < $filter['start'])
                    {
                            $filter['start'] = 0;
                            $_SESSION['tickets']['filter']['start'] = 0;
                            $tickets = Ticket::get_tickets ($filter, $tot_tickets);
                    }
            }

            $ks_users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE + USER_TYPE_KEYSOURCE_GROUP));
            $cust_users_list = User::get_users_list (array('type' => USER_TYPE_CUSTOMER + USER_TYPE_GROUP));
            $users_list = $ks_users_list + array('-10' => ' ', '-11' => '----------', '-12' => ' ') + $cust_users_list;

            $favorites_searches = KrifsSavedSearch::get_saved_searches_list (get_uid(), true);
            $other_searches = KrifsSavedSearch::get_saved_searches_list (get_uid(), false, true);

            $pages = make_paging ($filter['limit'], $tot_tickets);

            $order_by_options = array (
                    'id' => 'ID',
                    'subject' => 'Subject',
                    'type' => 'Type',
                    'priority' => 'Priority',
                    'customer' => 'Customer',
                    'private' => 'Private',
                    'assigned_id' => 'Assigned to',
                    'owner' => 'Owner',
                    'status' => 'Status',
                    'created' => 'Created',
                    'updated' => 'Updated',
                    'escalated' => 'Escalated'
            );

            // Mark the potential customer for locking
            if (!is_array($filter['customer_id']) and $filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

            //get the total number billable minutes that where allready included in IR's
            $tickets_tbb = array();
            if(is_array($tickets) and !empty($tickets))
            {
              foreach ($tickets as $t)
              {
                      $ir_tbb = $t->get_ir_tbbtime();
                      $tot_tbb = $t->get_tot_tbbtime();
                      $diff = $tot_tbb - $ir_tbb;
                      $color = $diff>0 ? 'red' : 'green';
                      $tickets_tbb[$t->id]['ir'] = format_interval_minutes($ir_tbb);
                      $tickets_tbb[$t->id]['tot'] = format_interval_minutes($tot_tbb);
                      $tickets_tbb[$t->id]['dif'] = $diff > 0 ? format_interval_minutes($diff) : $diff;
                      $tickets_tbb[$t->id]['color'] = $color;
              }
            }
            $extra_params = array ();
            if ($advanced) $extra_params['advanced'] = 1;
            if ($do_search) $extra_params['do_search'] = 1;
            $extra_params['search_id'] = $search_id;
            //if ($show_created) $extra_params['show_created'] = 1;
            $extra_params['show_created'] = 1;

            $this->assign ('tickets', $tickets);
            $this->assign ('tickets_tbb', $tickets_tbb);
            $this->assign ('self_uid', get_uid());
            $this->assign ('filter', $filter);
            $this->assign ('advanced', $advanced);
            $this->assign ('do_search', $do_search);
            $this->assign ('favorites_searches', $favorites_searches);
            $this->assign ('other_searches', $other_searches);
            $this->assign ('search_id', $search_id);
            $this->assign ('pages', $pages);
            $this->assign ('tot_tickets', $tot_tickets);
            $this->assign ('sort_url', get_link('krifs','manage_tickets_submit', $extra_params)); //$this->mk_redir ('manage_tickets_submit', $extra_params));
            $this->assign ('customers_list', $customers_list);
            $this->assign ('all_customers_list', $all_customers_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('order_by_options', $order_by_options);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('TICKETS_PRIORITIES_COLORS', $GLOBALS ['TICKETS_PRIORITIES_COLORS']);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
            $this->set_form_redir ('manage_tickets_submit', $extra_params);
            $this->assign ('error_msg', error_msg());

            $this->display ($tpl);
    }

    /** Sets the filtering criteria for the list of computers */
    function manage_tickets_submit ()
    {
            check_auth ();

            if ($this->vars['filter']['customer_id'] == ' ') $this->vars['filter']['customer_id'] = '';

            // Always mark we've passed through the submit function,
            // in case a different customer was selected from the locked one
            $extra_params = array ('do_filter' => 1);

            $advanced = $this->vars['advanced'];
            $do_search = $this->vars['do_search'];
            $search_id = $this->vars['search_id'];
            if ($this->vars['cancel']) {$advanced = false; $do_search = false;}

            if ($advanced) $extra_params['advanced'] = 1;
            if ($do_search) $extra_params['do_search'] = 1;
            if ($search_id) $extra_params['search_id'] = $search_id;

            if ($this->vars['do_search_button'])
            {
                    if (!$this->vars['filter']['user_id']) $this->vars['filter']['user_id'] = array ();
                    if (!$this->vars['filter']['customer_id']) $this->vars['filter']['customer_id'] = array ();
                    if (!$this->vars['filter']['status']) $this->vars['filter']['status'] = array ();
                    if (!$this->vars['filter']['type']) $this->vars['filter']['type'] = array ();
                    if (!$this->vars['filter']['in_subject']) $this->vars['filter']['in_subject'] = false;
                    if (!$this->vars['filter']['in_comments']) $this->vars['filter']['in_comments'] = false;
                    if (!$this->vars['filter']['escalated_only']) $this->vars['filter']['escalated_only'] = false;
                    if (!$this->vars['filter']['unscheduled_only']) $this->vars['filter']['unscheduled_only'] = false;
                    if (!$this->vars['filter']['not_linked_ir']) $this->vars['filter']['not_linked_ir'] = false;
                    if (!$this->vars['filter']['not_seen_manager']) $this->vars['filter']['not_seen_manager'] = false;
                    if (!$this->vars['filter']['not_seen_manager_or_not_ir']) $this->vars['filter']['not_seen_manager_or_not_ir'] = false;
                    $this->vars['do_search'] = 1;
                    $do_search = 1;
                    $extra_params['do_search'] = 1;
            }

            if($this->vars['mark_seen'])
            {
                if(isset($this->current_user) and $this->current_user->is_manager)
                {
                    $seen_tickets = $this->vars['man_sel'];                        
                    foreach($seen_tickets as $ticket_id)
                    {
                        $tik = new Ticket($ticket_id);
                        if($tik->id)
                        {
                            $tik->seen_manager_id = $this->current_user->id;
                            $tik->seen_manager_date = time();
                            if($tik->is_valid_data()) $tik->save_data();
                        }
                    }
                }
                else
                {
                    error_msg("You must be a manager to perform this action");
                }
            }

            if ($this->vars['order_by'] and $this->vars['order_dir'])
            {
                    // This is a request to change the sorting order
                    $_SESSION['tickets']['filter']['order_by'] = $this->vars['order_by'];
                    $_SESSION['tickets']['filter']['order_dir'] = $this->vars['order_dir'];
            }
            elseif ($this->vars['load_search'] or $this->vars['edit_search'])
            {
                    // This is a request to load and run or edit a saved search
                    class_load ('KrifsSavedSearch');
                    $search = new KrifsSavedSearch ($search_id);
                    $_SESSION['tickets']['filter'] = $search->filter;
                    $extra_params = array ('advanced'=>1, 'search_id'=>$search_id);
                    if ($this->vars['load_search']) $extra_params['do_search'] = 1;
            }
            else
            {
                    if ($this->vars['go'] == 'prev' or $this->vars['go'] == 'next')
                    {
                            $this->vars['filter']['start']+= $this->vars['filter']['limit'] * ($this->vars['go'] == 'prev' ? -1 : 1);
                    }
                    if (!$this->vars['filter']['show_owner']) $this->vars['filter']['show_owner'] = false;
                    if (!$this->vars['filter']['show_created']) $this->vars['filter']['show_created'] = false;
                    if (!$this->vars['filter']['show_escalated']) $this->vars['filter']['show_escalated'] = false;
                    if (!$this->vars['filter']['show_scheduled']) $this->vars['filter']['show_scheduled'] = false;
                    if (!$this->vars['filter']['escalated_only']) $this->vars['filter']['escalated_only'] = false;
                    if (!$this->vars['filter']['unscheduled_only']) $this->vars['filter']['unscheduled_only'] = false;
                    if (!$this->vars['filter']['not_linked_ir']) $this->vars['filter']['not_linked_ir'] = false;
                    if (!$this->vars['filter']['not_seen_manager']) $this->vars['filter']['not_seen_manager'] = false;
                    if (!$this->vars['filter']['not_seen_manager_or_not_ir']) $this->vars['filter']['not_seen_manager_or_not_ir'] = false;
                    if (!$this->vars['filter']['types_main_only']) $this->vars['filter']['types_main_only'] = false;

                    if (is_array($_SESSION['tickets']['filter']))
                    {
                            $_SESSION['tickets']['filter'] = array_merge($_SESSION['tickets']['filter'], $this->vars['filter']);
                    }
                    else
                    {
                            $_SESSION['tickets']['filter'] = $this->vars['filter'];
                    }
            }

            return $this->mk_redir('manage_tickets', $extra_params);
    }

    /** Displays the page showing who is doing what */
    function now_working ()
    {
            check_auth ();
            $tpl = 'now_working.tpl';

            $now_working = Ticket::get_now_working ();

            $this->assign ('now_working', $now_working);
            $this->assign ('error_msg', error_msg ());

            $this->display ($tpl);
    }

    /** Displays the quick search page for tickets and performs a search */
    function search_ticket ()
    {
            check_auth ();
            $tpl = 'search_ticket.tpl';
            $show_limit = 50;

            if ($this->vars['search_text'] and is_numeric ($this->vars['search_text']))
            {
                    // A numeric ID was provided, go directly to that ticket if it is a valid one
                    $ticket = new Ticket ($this->vars['search_text']);
                    if ($ticket->id) return $this->mk_redir ('ticket_edit', array('id' => $ticket->id));
                    else error_msg ('There is no ticket with the specified ID');
            }
            elseif ($this->vars['search_text'])
            {
                    // A string was specified, try a search by computer name
                    $tot_tickets = 0;
                    $filter = array ('keywords'=>$this->vars['search_text'], 'in_comments'=>true, 'in_subject'=>true, 'start'=>0, 'limit'=>$show_limit);
                    $tickets = Ticket::get_tickets ($filter, $tot_tickets);
                    if (count($tickets) > 0)
                    {
                            // If a single match is find, then go directly to that ticket
                            if (count($tickets) == 1) return $this->mk_redir ('ticket_edit', array('id' => $tickets[0]->id));
                            else $customers_list = Customer::get_customers_list ();
                    }
            }

            $this->assign ('search_text', $this->vars['search_text']);
            $this->assign ('tickets', $tickets);
            $this->assign ('tot_tickets', $tot_tickets);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('show_limit', $show_limit);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('search_ticket');
            $this->display($tpl);
    }

    /**
     *	searches for the PO code in the tickets if a single ticket is found the page of that ticket is displayed
     *	otherwise it opens a page for searching the desired PO code
     **/
    function search_po()
    {
            check_auth();
            $tpl = "search_po.tpl";
            $show_limit = 50; //if this limit is reached we should ask the user to refine his search, meaning to add more infos

            if($this->vars["search_text"])
            {
                    //we search for a compact code so we can trim the extremities of the search_text
                    $search_code = trim($this->vars['search_text']);
                    if($search_code != "")
                    {
                            //all set now get the tickets by code
                            $tot_tickets = 0;
                            $filter = array('keyword'=>$search_code, 'start' =>0, 'limit'=>$show_limit);
                            $tickets = Ticket::get_tickets_by_PO($filter, $tot_tickets);
                            if($tot_tickets > 0)
                            {
                                    if($tot_tickets == 1)
                                    {
                                            return $this->mk_redir('ticket_edit', array('id' => $tickets[0]->id));
                                    }
                                    else
                                    {
                                            //debug("ar trerbui sa arat ".$tpl);
                                             $customers_list = Customer::get_customers_list ();
                                    }
                            }
                            else
                            {
                                    error_msg("There is no ticket found with the specified PO code.");
                            }
                    }
            }
            $this->assign('error_msg', error_msg());
            $this->assign('tickets', $tickets);
            $this->assign('search_code', $search_code);
            $this->assign("tickets_count", $tot_tickets);
            $this->assign('customers_list', $customers_list);
            $this->assign("show_limit", $show_limit);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->set_form_redir('search_po');
            $this->display($tpl);
    }

    /****************************************************************/
    /* Saved searches management					*/
    /****************************************************************/


    /** Displays the page for managing saved searches */
    function manage_saved_searches ()
    {
            check_auth ();
            class_load ('KrifsSavedSearch');
            $tpl = 'manage_saved_searches.tpl';

            $customers_list = Customer::get_customers_list ();
            $users_list = User::get_users_list (array());

            $searches = array (
                    'Favorites' => KrifsSavedSearch::get_saved_searches (get_uid(), true),
                    'Other searches' => KrifsSavedSearch::get_saved_searches (get_uid(), false, true)
            );

            $this->assign ('searches', $searches);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('TICKETS_PRIORITIES_COLORS', $GLOBALS ['TICKETS_PRIORITIES_COLORS']);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('error_msg', error_msg());

            $this->display ($tpl);
    }


    /** Removes or adds saved searches to favorites */
    function manage_saved_searches_submit ()
    {
            check_auth ();
            class_load ('KrifsSavedSearch');
            $ret = $this->mk_redir ('manage_saved_searches');

            if ($this->vars['remove_id'])
            {
                    // Removes a search from the favorites
                    KrifsSavedSearch::remove_from_favorites (get_uid(), $this->vars['remove_id']);
            }
            elseif ($this->vars['add_id'])
            {
                    // Add a search to favorites
                    KrifsSavedSearch::add_to_favorites (get_uid(), $this->vars['add_id']);
            }

            return $ret;
    }


    /** Displays the page for saving a search */
    function saved_search_add ()
    {
            check_auth ();
            class_load ('KrifsSavedSearch');
            $tpl = 'saved_search_add.tpl';

            $search_id = $this->vars['search_id'];

            $search = new KrifsSavedSearch ();
            $search->add_to_favorites = false;
            if (!empty_error_msg())
            {
                    $search_data = array();
                    $search->load_from_array (restore_form_data ('search_data', false, $search_data));
                    $search->add_to_favorites = $search_data['add_to_favorites'];
            }

            $filter = $_SESSION['tickets']['filter'];

            $customers_list = Customer::get_customers_list ();
            $users_list = User::get_users_list (array());

            $favorites_searches = KrifsSavedSearch::get_saved_searches_list (get_uid(), true);
            $other_searches = KrifsSavedSearch::get_saved_searches_list (get_uid(), false, true);

            $this->assign ('search', $search);
            $this->assign ('filter', $filter);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('search_id', $search_id);
            $this->assign ('favorites_searches', $favorites_searches);
            $this->assign ('other_searches', $other_searches);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('TICKETS_PRIORITIES_COLORS', $GLOBALS ['TICKETS_PRIORITIES_COLORS']);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('saved_search_add_submit');

            $this->display ($tpl);
    }


    /** Saves the new search */
    function saved_search_add_submit ()
    {
            check_auth ();
            class_load ('KrifsSavedSearch');
            $ret = $this->mk_redir ('manage_tickets', array ('advanced'=>1, 'do_search'=>1));

            if ($this->vars['save'])
            {
                    $filter = $_SESSION['tickets']['filter'];
                    $search_data = $this->vars['search'];

                    if (!$this->vars['save_new'] and $this->vars['save_as_search'])
                    {
                            $search = new KrifsSavedSearch ($this->vars['save_as_search']);
                            $search->filter = $filter;
                    }
                    else
                    {
                            $search = new KrifsSavedSearch ();
                            $search->load_from_array ($search_data);
                            $search->filter = $filter;
                            $search->user_id = get_uid ();
                    }

                    if ($search->is_valid_data ())
                    {
                            $search->save_data ();
                            if ($this->vars['save_as_new'] and $this->vars['search']['add_to_favorites']) $search->add_to_favorites (get_uid());
                            $ret = $this->mk_redir ('manage_tickets', array ('advanced'=>1, 'do_search'=>1, 'search_id'=>$search->id));
                    }
                    else
                    {
                            save_form_data ($search_data, 'search_data');
                            $ret = $this->mk_redir ('saved_search_add');
                    }

            }

            return $ret;
    }

    /** Displays the page for editing the details of a saved search */
    function saved_search_edit ()
    {
            check_auth ();
            class_load ('KrifsSavedSearch');
            $tpl = 'saved_search_edit.tpl';

            $search_id = $this->vars['search_id'];

            $search = new KrifsSavedSearch ($search_id);
            if (!empty_error_msg())
            {
                    $search->load_from_array (restore_form_data ('search_data', false, $search_data));
            }

            $filter = $search->filter;

            $customers_list = Customer::get_customers_list ();
            $users_list = User::get_users_list (array());

            $this->assign ('search', $search);
            $this->assign ('filter', $filter);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('search_id', $search_id);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('TICKETS_PRIORITIES_COLORS', $GLOBALS ['TICKETS_PRIORITIES_COLORS']);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('saved_search_edit_submit', array ('search_id'=>$search->id));

            $this->display ($tpl);
    }


    /** Saves the details for a saved search */
    function saved_search_edit_submit ()
    {
            check_auth ();
            class_load ('KrifsSavedSearch');
            $ret = $this->mk_redir ('manage_saved_searches');
            $search = new KrifsSavedSearch ($this->vars['search_id']);

            if ($this->vars['save'] and $search->id)
            {
                    $search_data = $this->vars['search'];
                    $search->load_from_array ($search_data);

                    if ($search->is_valid_data ())
                    {
                            $search->save_data ();
                    }
                    else
                    {
                            save_form_data ('search_data', $search_data);
                    }
                    $ret = $this->mk_redir ('saved_search_edit', array ('search_id' => $search->id));
            }

            return $ret;
    }


    /** Displays the page for generating reports of outstanding tickets for customers */
    function report_krifs_outstanding_tickets ()
    {
            check_auth ();
            $tpl = 'report_krifs_outstanding_tickets.tpl';
            $xml_tpl = 'report_krifs_outstanding_tickets.xml';
            $xsl_tpl = 'report_krifs_outstanding_tickets.xsl_fo';
    //unset($_SESSION['report_outstanding']['filter']);
            $filter = $_SESSION['report_outstanding']['filter'];

            if (!is_array($filter) or !isset($filter['status']))
            {
                    $filter = array (
                            'status' => -1,
                            'show_assigned' => false,
                            'show_private' => false,
                            'show_created' => true,
                            'show_updated' => true,
                            'date_from' => strtotime(date('Y-m-01')),
                            'date_to' => time (),
                            'order_by' => 'created',
                            'order_dir' => 'desc'
                    );
            }
            if ($this->locked_customer->id and !$this->vars['do_filter'])
            {
                    // If 'do_filter' is present in request, the locked customer is ignored
                    $_SESSION['report_outstanding']['filter']['customer_id'] = $this->locked_customer->id;
                    $filter['customer_id'] = $this->locked_customer->id;
            }

            // Check if the user has restricted access to customers
            if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
            if($filter['customer_id']) $filter['customer_ids'] = $filter['customer_id'];
            $tickets = Ticket::get_tickets ($filter, $no_count);


            // Extract the list of Krifs customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('has_krifs' => 1, 'favorites_first' => $this->current_user->id);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            $users_list = User::get_users_list (array());

            $this->assign ('mtd_from', strtotime(date('Y-m-01')));
            $this->assign ('mtd_to', time());
            $this->assign ('ytd_from', strtotime(date('Y-01-01')));
            $this->assign ('ytd_to', time());
            $this->assign ('last_month_from', strtotime(date('M').' 1 -1 month'));
            $this->assign ('last_month_to', strtotime('+1 month -1 day', strtotime(date('M').' 1 - month')));

            $this->assign ('tickets', $tickets);
            $this->assign ('show_details', $this->vars['show_details']);
            $this->assign ('do_filter', $this->vars['do_filter']);
            $filter['generated'] = time ();
            $this->assign ('filter', $filter);
            $this->assign ('sort_url', $this->mk_redir ('report_krifs_outstanding_tickets_submit'));
            $this->assign ('customers_list', $customers_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('TICKETS_PRIORITIES_COLORS', $GLOBALS ['TICKETS_PRIORITIES_COLORS']);
            $this->set_form_redir ('report_krifs_outstanding_tickets_submit');

            // Calculate the columns span for details in PDF
            $span = 3;
            if (!$filter['customer_id']) $span++;
            if ($filter['show_assigned']) $span++;
            if ($filter['show_private']) $span++;
            if ($filter['show_created']) $span++;
            if ($filter['show_updated']) $span++;
            $this->assign ('detail_span', $span);

            // Mark the potential customer for locking
            if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

            if (strtolower($this->vars['format']) == 'pdf')
            {
                    $xml = $this->fetch ($xml_tpl);
                    make_pdf_xml ($xml, $xsl_tpl);
                    die;
            }
            elseif (strtolower($this->vars['format']) == 'xml')
            {
                    header('Content-Type: text/xml');
                    $this->display_template_only ($xml_tpl);
                    die;
            }
            else
            {
                    $this->display ($tpl);
            }
    }


    /** Save the filtering criteria for the report page */
    function report_krifs_outstanding_tickets_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('report_krifs_outstanding_tickets', array('do_filter' =>1));

            if ($this->vars['order_by'] and $this->vars['order_dir'])
            {
                    // This is a request to change the sorting order
                    $_SESSION['report_outstanding']['filter']['order_by'] = $this->vars['order_by'];
                    $_SESSION['report_outstanding']['filter']['order_dir'] = $this->vars['order_dir'];
            }
            else
            {
                    $filter = $this->vars['filter'];
                    if ($filter['customer_id'] == ' ') unset ($filter['customer_id']);

                    $filter['order_by'] = $_SESSION['report_outstanding']['filter']['order_by'];
                    $filter['order_dir'] = $_SESSION['report_outstanding']['filter']['order_dir'];

                    if ($filter['date_from'])
                            $filter['date_from'] = js_strtotime ($filter['date_from'].' 00:00');
                    if ($filter['date_to'])
                            $filter['date_to'] = js_strtotime ($filter['date_to'].' 23:59');

                    $_SESSION['report_outstanding']['filter'] = $filter;
            }

            return $ret;
    }


    /** Displays the page where the user can specify which notification is to be associated with the newly created ticket */
    function ticket_add_check_notifs ()
    {
            class_load ('Notification');
            $computer = new Computer ($this->vars['object_id']);
            $tpl = 'ticket_add_check_notifs.tpl';
            check_auth (array('customer_id' => $computer->customer_id));

            // Get the list of notifications and remove from it those that don't have an associated ticket
            $notifs_all = $computer->get_notifications ();
            $notification = array ();
            for ($i=0; $i<count($notifs_all); $i++)
            {
                    if (!$notifs_all[$i]->ticket_id) $notifications[] = $notifs_all[$i];
            }

            $keep_params = array ('check_notifs', 'object_id', 'object_class', 'subject', 'mark_now_working');
            $params = array ();
            foreach ($keep_params as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];

            $this->assign ('computer', $computer);
            $this->assign ('notifications', $notifications);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_add_check_notifs_submit', $params);

            $this->display ($tpl);
    }

    function ticket_add_check_notifs_submit ()
    {
            class_load ('Notification');
            $computer = new Computer ($this->vars['object_id']);
            check_auth (array('customer_id' => $computer->customer_id));

            $ret = $this->mk_redir ('computer_view', array ('id' => $computer->id), 'kawacs');

            if ($this->vars['proceed'])
            {
                    $keep_params = array ('object_id', 'object_class', 'subject', 'mark_now_working');
                    $params = array ();
                    foreach ($keep_params as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];

                    if ($this->vars['notification_id']) $params['notification_id'] = $this->vars['notification_id'];
                    $ret = $this->mk_redir ('ticket_add', $params);
            }

            return $ret;
    }


    /** Displays the page for creating a new ticket */
    function ticket_add ()
    {
            class_load ('Notification');
            class_load ('InterventionReport');
            class_load ('MonitoredIP');
            class_load ('CustomerCCRecipient');
            class_load ('InterventionLocation');
            class_load ('ActionType');

            // If a customer user has arrived here, send it to the customer-specific ticket
            $uid = get_uid ();
            if ($uid)
            {
                    $user = New User ($uid);
                    if ($user->customer_id)
                    {
                            return $this->mk_redir ('ticket_add', array(), 'customer_krifs');
                    }
            }

            if ($this->vars['check_notifs'] and !$this->vars['notification_id'] and $this->vars['object_id'] and $this->vars['object_class']==TICKET_OBJ_CLASS_COMPUTER)
            {
                    // This is a request to create a ticket for a computer, with checking if there
                    // are notifications for that computer. If there are notifications, redirect the user to the
                    // page for selecting what notification should be associated with the newly created ticket.
                    $computer = new Computer ($this->vars['object_id']);
                    $notifs = $computer->get_notifications ();

                    // If notifications exist, check if there are any without an associated ticket
                    $has_free_notifs = false;
                    for ($i=0; $i<count($notifs) and !$has_free_notifs; $i++)
                    {
                            if (!$notifs[$i]->ticket_id) $has_free_notifs = true;
                    }

                    if ($has_free_notifs)
                    {
                            // There are notifications without associated tickets, send the user to the page
                            // for selecting the relevant notification
                            $keep_params = array ('check_notifs', 'object_id', 'object_class', 'subject');
                            $params = array ();
                            foreach ($keep_params as $field) if (isset($this->vars[$field])) $params[$field] = $this->vars[$field];
                            return $this->mk_redir ('ticket_add_check_notifs', $params);
                    }
            }

            check_auth ();               
            $tpl = 'ticket_add.tpl';

            // Assign a temporary ID for this ticket, if one doesn't exist already.
            // This ID will be used for keeping track of what other objects should be linked to the new ticket
            if (!$this->vars['temp_id']) {$this->vars['temp_id'] = uniqid('t'); $is_new_ticket = true;}
            $temp_id = $this->vars['temp_id'];

            $ticket = new Ticket ();
            $ticket->private = false;
            $ticket->type = DEFAULT_TICKET_TYPE;
            $ticket_detail = new TicketDetail ();
            $ticket_detail->private = false;
            $linked_objects_data = array ();
            $cc_users_data = array ();

            // Restore saved data, if any is available
            $ticket->load_from_array (restore_form_data ('ticket_data_'.$temp_id, false, $ticket_data));
            $ticket_detail->load_from_array (restore_form_data ('ticket_detail_data_'.$temp_id, false, $ticket_detail_data));
            // The saved linked objects and saved CC users will be handled later
            $linked_objects_data = restore_form_data ('linked_objects_data_'.$temp_id, true, $linked_objects_data);
            $cc_users_data = restore_form_data ('cc_users_data_'.$temp_id, true, $cc_users_data);
            $cc_emails_data = restore_form_data('cc_emails_data', true, $cc_emails_data);

            // Extract the list of Krifs customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('has_krifs' => 1, 'favorites_first' => $this->current_user->id);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers = Customer::get_customers_list ($customers_filter);

            $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
            $action_types = ActionType::get_list ();

            $users = $users + $groups;

            // If no user was specified, set to current user
            if (!$ticket->owner_id) $ticket->owner_id = get_uid ();
            if (!$ticket->assigned_id) $ticket->assigned_id = get_uid ();
            if (!$ticket_detail->user_id) $ticket_detail->user_id = get_uid ();

            $time_in = $ticket_detail->time_in;
            $time_out = $ticket_detail->time_out;

            if (!$time_in and $duration and $time_out) $time_in = $time_out - $duration*60;
            if ($time_in and !$duration and $time_out) $duration = intval(($time_out-$time_in)/60);
            if ($time_in and $duration and !$time_out) $time_out = $time_in + $duration*60;

            // If some values are still missing, start assigning defaults
            if (!$time_in) $time_in = time();
            if (!$duration) $duration = 10;
            $time_out = $time_in + $duration*60;

            $duration_hours = intval ($duration/60);
            $duration_minutes = abs(($duration % 60));
            $duration_minutes = str_pad ($duration_minutes, 2, '0', STR_PAD_LEFT);
            if ($duration < 0 and $duration_hours==0) $duration_hours = '-'.$duration_hours;

            $default_location_id = $ticket_detail->location_id;
            $default_activity_id = $ticket_detail->activity_id;
            if(!$default_location_id) $default_location_id = DEFAULT_LOCATION_ID;
            if(!$default_activity_id) $default_activity_id = DEFAULT_ACTIVITY_ID;


            $location = new InterventionLocation($default_location_id);
            $acttype = new ActionType($default_activity_id);



            if ($this->vars['customer_id']) $ticket->customer_id = $this->vars['customer_id'];
            elseif ($this->locked_customer->id) $ticket->customer_id = $this->locked_customer->id;

            if ($this->vars['customer_order_id']) $ticket->customer_order_id = $this->vars['customer_order_id'];
            if ($ticket->customer_order_id)
            {
                    $ticket->customer_order = new CustomerOrder ($ticket->customer_order_id);
                    $ticket->billable = $ticket->customer_order->billable;
            }

            // Check if the creation of the ticket has been invoked for a specific object
            $params = $this->set_carry_fields (array('temp_id', 'notification_id', 'mark_now_working', 'customer_order_id', 'subject', 'returl'));
            $obj_class = $this->vars['object_class'];
            $obj_id = $this->vars['object_id'];
            if ($obj_class and $obj_id)
            {
                    // An object to be linked has been passed in the URL
                    $this->assign ('object_class', $obj_class);
                    $this->assign ('object_id', $obj_id);

                    if ($obj_class == TICKET_OBJ_CLASS_COMPUTER)
                    {
                            $comp = new Computer ($obj_id);
                            $ticket->customer_id = $comp->customer_id;
                            $this->assign ('object_name', $comp->netbios_name);
                    }
                    elseif ($obj_class == TICKET_OBJ_CLASS_MONITORED_IP)
                    {
                            $monitored_ip = new MonitoredIP ($obj_id);
                            $ticket->customer_id = $monitored_ip->customer_id;
                            $this->assign ('object_name', $monitored_ip->remote_ip.'/'.$monitored_ip->target_ip);
                    }
                    elseif ($obj_class == TICKET_OBJ_CLASS_INTERNET_CONTRACT)
                    {
                            class_load ('CustomerInternetContract');
                            $contract = new CustomerInternetContract ($obj_id);
                            $contract->load_details ();
                            $ticket->customer_id = $contract->customer_id;
                            $this->assign ('object_name', $contract->provider->name.': '.$contract->provider_contract->name);
                    }
                    elseif ($obj_class == TICKET_OBJ_CLASS_PERIPHERAL)
                    {
                            class_load ('Peripheral');
                            $peripheral = new Peripheral ($obj_id);
                            $ticket->customer_id = $peripheral->customer_id;
                            $this->assign ('object_name', $peripheral->name);
                    }
                    elseif ($obj_class == TICKET_OBJ_CLASS_AD_PRINTER)
                    {
                            class_load ('AD_Printer');
                            list ($a_computer_id, $a_nrc) = split ('_', $obj_id);
                            $ad_printer = new AD_Printer ($a_computer_id, $a_nrc);
                            $ticket->customer_id = $ad_printer->customer_id;
                            $this->assign ('object_name', $ad_printer->name);
                    }
            }
            if (count($linked_objects_data) > 0)
            {
                    // One or more objects have been saved in the session, append them
                    $this->assign ('existing_linked_objects', $linked_objects_data);

            }

            // If this is a new ticket, add the default CC recipients for the customer, if any exists
            if ($is_new_ticket and $ticket->customer_id)
            {
                    $default_cc_recipients = CustomerCCRecipient::get_cc_recipients ($ticket->customer_id);
                    foreach ($default_cc_recipients as $cc_recipient)
                    {
                            $cc_users_data[] = array ('user_id'=>$cc_recipient->id, 'user_name'=>$cc_recipient->get_name(), 'is_customer_user'=>$cc_recipient->is_customer_user());
                    }
            }

            // Check if there are any specified CC users
            if (count($cc_users_data) > 0) $this->assign ('cc_users', $cc_users_data);
            if (count($cc_emails_data) > 0) $this->assign ('cc_emails', $cc_emails_data);

            // Set the default subject
            if (!$ticket->subject and $this->vars['subject']) $ticket->subject = $this->vars['subject'];
            if (!$ticket_detail->comments and $this->vars['body']) $ticket_detail->comments = $this->vars['body'];

            // Fetch the list of available customer orders and subscriptions, if a customer has been specified
            if ($ticket->customer_id)
            {
                    $available_orders_list = CustomerOrder::get_open_orders_list ($ticket->customer_id);
                    $this->assign ('available_orders_list', $available_orders_list);
            }

            // Get all available (open) intervention reports for this customer
            if ($ticket->customer_id)
            {
                    $filter_interventions = array (
                            'customer_id' => $ticket->customer_id,
                            'status' => INTERVENTION_STAT_OPEN,
                            'show_ids' => true
                    );
                    $available_interventions_list = InterventionReport::get_interventions_list ($filter_interventions);
                    $this->assign ('available_interventions_list', $available_interventions_list);
            }

            $this->assign ('ticket', $ticket);
            $this->assign ('ticket_detail', $ticket_detail);

            $this->assign ('time_in', $time_in);
            $this->assign ('time_out', $time_out);
            $this->assign ('duration', $duration);
            $this->assign ('duration_minutes', $duration_minutes);
            $this->assign ('duration_hours', $duration_hours);
            $this->assign ('location', $location);
            $this->assign ('acttype', $acttype);

            $this->assign ('customers', $customers);
            $this->assign ('users', $users);
            $this->assign ('action_types', $action_types);
            $this->assign ('TICKET_SOURCES', $GLOBALS ['TICKET_SOURCES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('TICKET_TYPES_BILLABLE', Ticket::get_ticket_types_list(array('is_billable' => 1)));
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('TICKET_OBJECT_CLASSES', $GLOBALS['TICKET_OBJECT_CLASSES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_add_submit', $params);

            $this->display ($tpl);
    }


    /** Processes the request to create a new ticket */
    function ticket_add_submit ()
    {
            class_load("WorkMarker");                
            check_auth (array('customer_id' => $this->vars['ticket']['customer_id']));
            $temp_id = $this->vars['temp_id'];
            if (!$temp_id)
            {
                    // We always need a temporary ID to exist
                    error_msg ('No creation temporary ID was specified for the ticket.');
                    return $this->mk_redir ('ticket_add');
            }

            $params = $this->set_carry_fields (array('temp_id', 'notification_id', 'mark_now_working', 'customer_order_id', 'subject', 'returl'));
            $ret = $this->mk_redir ('manage_tickets', $params);

            if ($this->vars['save'])
            {
                    $ticket_data = $this->vars['ticket'];
                    $ticket_detail_data = $this->vars['ticket_detail'];
                    $linked_objects_data = $this->vars['linked_objects'];
                    if (!is_array($this->vars['cc_users'])) $cc_users_data = array ();
                    else
                    {
                            $cc_users_data = $this->vars['cc_users'];
                            foreach ($cc_users_data as $idx => $usr) $cc_users_data[$idx]['is_customer_user'] = ($usr['is_customer_user']=='true' ? true : false);
                    }

                    if(!is_array($this->vars['cc_emails'])) $cc_emails_data = array();
                    else {
                            $cc_emails_data = $this->vars['cc_emails'];
                    }

                    if (!$ticket_data['private']) $ticket_data['private'] = false;
                    if (!$ticket_detail_data['private']) $ticket_detail_data['private'] = false;

                    $ticket_data['user_id'] = get_uid ();
                    $ticket_data['created'] = time ();
                    $ticket_data['last_modified'] = time ();
                    $ticket_data['status'] = TICKET_STATUS_NEW;
                    if ($ticket_data['deadline']) $ticket_data['deadline'] = js_strtotime ($ticket_data['deadline']);

                    $ticket_detail_data['user_id'] = get_uid ();
                    $ticket_detail_data['assigned_id'] = get_uid ();
                    $ticket_detail_data['created'] = time ();
                    if (!$ticket_detail_data['private']) $ticket_detail_data['private'] = false;

                    if ($ticket_data['assigned_id'] != get_uid()) $ticket_detail_data['assigned_id'] = $ticket_data['assigned_id'];

                    $ticket = new Ticket ();
                    $ticket->load_from_array ($ticket_data);

                    if ($ticket->is_valid_data())
                    {
                            if ($this->vars['customer_order_id'])
                            {
                                    $ticket->customer_order_id = $this->vars['customer_order_id'];
                            }
                            $ticket->save_data ();
                            $ticket->log_action ($this->current_user->id, TICKET_ACCESS_CREATE);

                            if ($this->vars['notification_id'])
                            {
                                    // This ticket was created from a notification, so mark the notification accordingly
                                    $notification = new Notification ($this->vars['notification_id']);
                                    $notification->mark_ticket_created ($ticket->id);
                            }
                            if ($this->vars['mark_now_working'])
                            {
                                    // There is a request to mark that the current user is working on this ticket
                                    $ticket->mark_now_working ($this->current_user->id);
                            }

                            $td_comments = $ticket_detail_data['comments'] ;

                            //$td_comments = str_replace("<br />", "<", $td_comments);
                            //$td_comments = str_replace("<p>&nbsp;</p>", "", $td_comments);
                            //$td_comments = str_replace("\r\n", "<br />", $td_comments);
                            $ticket_detail_data['comments'] = $td_comments;

                            $ticket_detail = new TicketDetail ();
                            $ticket_detail->load_from_array ($ticket_detail_data);
                            $ticket_detail->ticket_id = $ticket->id;
                            $ticket_detail->status = $ticket->status;
                            $ticket_detail->customer_order_id = $ticket->customer_order_id;

                            // If an intervention report has been assigned, make sure it belongs to the customer to which the ticket belongs.
                            if ($ticket_detail->intervention_report_id)
                            {
                                    class_load ('InterventionReport');
                                    $ir = new InterventionReport ($ticket_detail->intervention_report_id);
                                    if ($ir->customer_id != $ticket->customer_id) $ticket_detail->intervention_report_id = null;
                            }

                            $ticket_detail->save_data ();
                            $ticket_detail->log_action ($this->current_user->id, TICKET_ACCESS_DETAIL_CREATE);
                            $ticket_detail->load_data ();

                            if($this->vars['work_marker'])
                            {
                                    WorkMarker::mark_working($this->current_user->id, $ticket_detail->id);
                            }

                            // Reload the ticket data, to ensure consistency
                            $ticket->load_data ();

                            // Create the related objects, if it was requested
                            if (is_array($linked_objects_data) and count($linked_objects_data)>0)
                            {
                                    foreach ($linked_objects_data as $obj) $ticket->add_objects ($obj['class'], array ($obj['id']));
                            }

                            // Set the CC users, if any
                            if (is_array($cc_users_data) and count($cc_users_data)>0)
                            {
                                    $ticket->cc_list = array ();
                                    foreach ($cc_users_data as $usr) $ticket->cc_list[] = $usr['user_id'];
                                    $ticket->save_data ();
                            }

                            //set the cc_manual_list if any
                            if(is_array($cc_emails_data) and count($cc_emails_data)>0)
                            {
                                    $ticket->cc_manual_list = array();
                                    foreach($cc_emails_data as $eml) $ticket->cc_manual_list[] = $eml;
                                    $ticket->save_data();
                            }

                            // Finally, load again all tickets details and dispatch the notifications
                            $ticket->load_data ();
                            $ticket->dispatch_notifications (TICKET_NOTIF_TYPE_NEW, get_uid());

                            unset ($params['notification_id']);
                            unset ($params['mark_now_working']);
                            unset ($params['customer_order_id']);
                            unset ($params['temp_id']);
                            $params['id'] = $ticket->id;
                            $ret = $this->mk_redir ('ticket_edit', $params);

                            clear_form_data ('ticket_data_'.$temp_id);
                            clear_form_data ('ticket_detail_data_'.$temp_id);
                            clear_form_data ('linked_objects_data_'.$temp_id);
                            clear_form_data ('cc_users_data_'.$temp_id);
                            clear_form_data ('cc_emails_data');

                            // Mark the potential customer for locking
                            $_SESSION['potential_lock_customer_id'] = $ticket->customer_id;
                    }
                    else
                    {
                            save_form_data ($ticket_data, 'ticket_data_'.$temp_id);
                            save_form_data ($ticket_detail_data, 'ticket_detail_data_'.$temp_id);
                            save_form_data ($linked_objects_data, 'linked_objects_data_'.$temp_id);
                            save_form_data ($cc_users_data, 'cc_users_data_'.$temp_id);
                            save_form_data ($cc_emails_data, 'cc_emails_data');
                            if ($ticket->customer_id) $params['customer_id'] = $ticket->customer_id;

                            $ret = $this->mk_redir ('ticket_add', $params);
                    }
            }

            return $ret;
    }

    /** Displays the pop-up window for adding objects to a new ticket */
    function popup_ticket_add_objects ()
    {
            check_auth ();
            class_load ('AD_Computer');
            class_load ('AD_User');
            class_load ('AD_Group');
            class_load ('AD_Printer');
            class_load ('Peripheral');
            class_load ('PeripheralClass');
            class_load ('CustomerInternetContract');
            class_load ('MonitoredIP');
            class_load ('RemovedComputer');

            $tpl = 'popup_ticket_add_objects_ajax.tpl';
            $customer_id = $this->vars['customer_id'];
            $customer = new Customer ($customer_id);
            $all_objects = array ();

            // Fetch the list of all objects that could be linked for the specified customer
            $objects = Computer::get_computers_list (array('customer_id' => $customer_id));
            foreach ($objects as $object_id => $object_name) $all_objects[TICKET_OBJ_CLASS_COMPUTER][$object_id] = $object_name;

            $objects = RemovedComputer::get_removed_computers_list (array('customer_id' => $customer_id));
            foreach ($objects as $object_id => $object_name) $all_objects[TICKET_OBJ_CLASS_REMOVED_COMPUTER][$object_id] = $object_name;

            $objects = Peripheral::get_peripherals (array('customer_id' => $customer_id));
            $classes_list = PeripheralClass::get_classes_list ();
            foreach ($objects as $class_id => $peripherals)
            {
                    foreach ($peripherals as $peripheral)
                    {
                            $all_objects[TICKET_OBJ_CLASS_PERIPHERAL][$peripheral->id] = '['.$classes_list[$class_id].'] '.$peripheral->name;
                    }
            }

            // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
            $objects = AD_Printer::get_ad_printers (array('customer_id' => $customer_id));
            for ($i = 0; $i < count($objects); $i++)
            {
                    $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                    $all_objects[TICKET_OBJ_CLASS_AD_PRINTER][$id] = $objects[$i]->name;
            }

            $objects = MonitoredIP::get_monitored_ips (array('customer_id' => $customer_id));
            for ($i = 0; $i < count($objects); $i++)
            {
                    $all_objects[TICKET_OBJ_CLASS_MONITORED_IP][$objects[$i]->id] = $objects[$i]->remote_ip.'/'.trim($objects[$i]->target_ip);
            }

            $objects = User::get_users (array('customer_id' => $customer_id), $no_count);
            for ($i = 0; $i < count($objects); $i++)
            {
                    $all_objects[TICKET_OBJ_CLASS_USER][$objects[$i]->id] = $objects[$i]->get_name().' ('.$objects[$i]->login.')';
            }

            // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
            $objects = AD_Computer::get_ad_computers (array('customer_id' => $customer_id));
            for ($i = 0; $i < count($objects); $i++)
            {
                    $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                    $all_objects[TICKET_OBJ_CLASS_AD_COMPUTER][$id] = $objects[$i]->cn;
            }

            // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
            $objects = AD_User::get_ad_users (array('customer_id' => $customer_id));
            for ($i = 0; $i < count($objects); $i++)
            {
                    $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                    $all_objects[TICKET_OBJ_CLASS_AD_USER][$id] = $objects[$i]->sam_account_name.' ('.$objects[$i]->display_name.')';
            }

            $objects = CustomerInternetContract::get_contracts (array('customer_id'=>$customer_id));
            for ($i = 0; $i < count($objects); $i++)
            {
                    $all_objects[TICKET_OBJ_CLASS_INTERNET_CONTRACT][$objects[$i]->id] = $objects[$i]->get_name();
            }


            $this->assign ('customer', $customer);
            $this->assign ('all_objects', $all_objects);
            $this->assign ('TICKET_OBJECT_CLASSES', $GLOBALS['TICKET_OBJECT_CLASSES']);
            $this->assign ('error_msg', error_msg ());

            $this->display_template_limited ($tpl);
    }

    /** Displays the pop-up window for adding objects or AD users by doing users searching */
    function popup_ticket_search_by_user ()
    {
            check_auth ();
            class_load ('AD_User');
            $tpl = 'popup_ticket_search_by_user.tpl';
            $customer = new Customer ($this->vars['customer_id']);

            // Get the AD users for the customer
            $ad_users = AD_User::get_ad_users (array('customer_id' => $customer->id));
            $used_computers = array ();
            foreach ($ad_users as $idx => $ad_user) $used_computers[$idx] = $ad_user->get_used_computers (1);

            // Get the list of computers for this customer
            $computers_list = Computer::get_computers_list (array('customer_id'=>$customer_id));

            $this->assign ('customer', $customer);
            $this->assign ('ad_users', $ad_users);
            $this->assign ('used_computers', $used_computers);
            $this->assign ('computers_list', $computers_list);
            $this->assign ('obj_computer_class_id', TICKET_OBJ_CLASS_COMPUTER);
            $this->assign ('obj_computer_class_name', $GLOBALS['TICKET_OBJECT_CLASSES'][TICKET_OBJ_CLASS_COMPUTER]);
            $this->assign ('obj_user_class_id', TICKET_OBJ_CLASS_AD_USER);
            $this->assign ('obj_user_class_name', $GLOBALS['TICKET_OBJECT_CLASSES'][TICKET_OBJ_CLASS_AD_USER]);
            $this->assign ('error_msg', error_msg ());

            $this->display_template_limited ($tpl);
    }

    /** Displays the pop-up window for adding CC users to a new ticket */
    function popup_ticket_add_cc_users ()
    {
            check_auth ();
            $tpl = 'popup_ticket_add_cc_users.tpl';
            $customer_id = $this->vars['customer_id'];
            $customer = new Customer ($customer_id);
            $customer_users = array();

            $users = User::get_users (array('type' => USER_TYPE_KEYSOURCE), $no_count);
            $groups = Group::get_groups (array('type' => USER_TYPE_KEYSOURCE_GROUP), $no_count);
            if ($customer_id!=MANAGER_CUSTOMER_ID) $customer_users = User::get_users (array('customer_id' => $customer->id, 'type' => USER_TYPE_CUSTOMER), $no_count);

            $users = array_merge ($users, $groups);
            $all_users = array_merge($customer_users, $users);

            $this->assign ('customer', $customer);
            $this->assign ('users', $users);
            $this->assign ('customer_users', $customer_users);
            $this->assign ('all_users', $all_users);
            $this->assign ('error_msg', error_msg ());

            $this->display_template_limited ($tpl);
    }

    /** This is used for serving XML requests for getting the default CC recipients for a customer */
    function xml_get_customer_cc_recipients ()
    {
            check_auth ();
            class_load ('CustomerCCRecipient');
            $customer_id = $this->vars['customer_id'];

            $xml = '<?xml version="1.0" encoding="ISO-8859-1" ?><users type="cc_recipients" customer_id="'.$customer_id.'">';
            if ($customer_id)
            {
                    $recipients = CustomerCCRecipient::get_cc_recipients ($customer_id);
                    foreach ($recipients as $recipient)
                    {
                            $xml.= '<user id="'.$recipient->id.'" is_customer_user="'.($recipient->is_customer_user()?1:0).'">';
                            $xml.= htmlspecialchars($recipient->get_name()).'</user>'."\n";
                    }
            }
            $xml.= '</users>';
            header ('Content-Type: text/xml');
            header ('Content-length: '+strlen($xml));
            echo $xml;

            die;
    }


    /** Displays a page for viewing/editing a ticket */
    function ticket_edit ()
    {
            class_load ('InterventionReport');
            class_load ('Task');
            class_load ('InterventionLocation');
            class_load ('ActionType');
            class_load ("WorkMarker");

            // If a customerinterventions_list user has arrived here, send it to the customer-specific ticket
            $uid = get_uid ();

            if ($uid)
            {
                $user = New User ($uid);
                if ($user->customer_id)
                {
                        return $this->mk_redir ('ticket_edit', array('id' => $this->vars['id']), 'customer_krifs');
                }
            }

            check_auth (array('ticket_id' => $this->vars['id']));


            $tpl = 'ticket_edit.tpl';

            $ticket = new Ticket ($this->vars['id']);

            if (!isset($ticket) or !$ticket->id){ return $this->mk_redir ('manage_tickets');}
            $ticket->log_action ($this->current_user->id, TICKET_ACCESS_READ);
            $customer = new Customer ($ticket->customer_id);

            // Mark that the current user has read the ticket
            $ticket->mark_read (get_uid());

            $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));

            $customer_users = array();
            if (!$ticket->private and $ticket->customer_id and $ticket->customer_id!=MANAGER_CUSTOMER_ID)
            {
                    $customer_users = User::get_users_list (array('customer_id' => $ticket->customer_id, 'type' => USER_TYPE_CUSTOMER), $nocount);
            }

            // Fetch the action types - both the complet list and the actions currently available for this customer
            $action_types = ActionType::get_list ();
            $filter_customer_actions = array (
                    'group_by' => 'category',
                    'contract_type_cust' => $customer->contract_type,
                    'contract_sub_type' => $customer->contract_sub_type
            );
            $action_types_customer = ActionType::get_action_types ($filter_customer_actions);

            $ticket_detail = new TicketDetail ();
            // Initialize the ticket detail to display the same 'Reassign to' value as the parent ticket
            $ticket_detail->assigned_id = $ticket->assigned_id;
            $ticket_detail->customer_order_id = $ticket->customer_order_id;

            if (!empty_error_msg())
            {
                    $ticket_data = array ();
                    $ticket_detail_data = array ();
                    $ticket->load_from_array (restore_form_data ('ticket_data', false, $ticket_data));
                    $ticket_detail->load_from_array (restore_form_data ('ticket_detail_data', false, $ticket_detail_data));
            }

            $users = $users + $groups;
            $users_all = $users + $customer_users;

            $ticket->set_objects_display_additional_info ();

            // Closed tickets and tickets for on-hold customers are not editable
            if ($ticket->customer->onhold or $ticket->status==TICKET_STATUS_CLOSED)
            {
                    if ($ticket->customer->onhold)
                            error_msg ('WARNING: This customer is \'On hold\', therefore the ticket can\'t be modified');
                    $tpl = 'ticket_view.tpl';
            }

            // Fetch the list of intervention reports and the order associated with this ticket - if any
            $interventions_list = InterventionReport::get_interventions_list (array('ticket_id' => $ticket->id));

            // Fetch the list of available customer orders and subscriptions.
            // Append the currently assigned order if exists and it's not in the list already.
            $available_orders_list = CustomerOrder::get_open_orders_list ($ticket->customer_id);
            if ($ticket->customer_order_id and !isset($available_orders_list[$ticket->customer_order_id]))
            {
                    $available_orders_list[$ticket->customer_order_id] = $ticket->customer_order->get_list_name ();
            }
            // Make the list with the billable status for each order
            $orders_billable_list = array ();
            foreach ($available_orders_list as $order_id => $order_subject)
            {
                    $order = new CustomerOrder ($order_id);
                    $orders_billable_list[$order_id] = $order->billable;
            }

            // For each ticket detail that has an intervention report linked, load the intervention
            for ($i=0; $i<count($ticket->details); $i++)
            {
                    if ($ticket->details[$i]->id) {
                        $obj = new TicketDetail($ticket->details[$i]->id);
                        if($obj->can_modify(false)) {
                                $ticket->details[$i]->is_editable = true;
                        }
                        unset($obj);
                    }
                    if ($ticket->details[$i]->intervention_report_id)
                    {
                            $ticket->details[$i]->intervention = new InterventionReport ($ticket->details[$i]->intervention_report_id);
                    }
            }

            $pp = array();
            $query = "SELECT count(id) as prv from users where customer_id=".$customer->id." and allow_private=1";
            $pp = db::db_fetch_row($query);
            if($pp['prv']) $has_private = true;
            else $has_private = false;

            $locations_list = InterventionLocation::get_locations_list ();

            // Get all available (open) intervention reports for this customer
            $filter_interventions = array (
                    'customer_id' => $ticket->customer_id,
                    'status' => INTERVENTION_STAT_OPEN,
                    'show_ids' => true
            );
            $available_interventions_list = InterventionReport::get_interventions_list ($filter_interventions);

            // Get any scheduled tasks for this ticket
            $tasks = Task::get_tasks (array('ticket_id'=>$ticket->id));

            $time_in = $ticket_detail->time_in;
            $time_out = $ticket_detail->time_out;

            if (!$time_in and $duration and $time_out) $time_in = $time_out - $duration*60;
            if ($time_in and !$duration and $time_out) $duration = intval(($time_out-$time_in)/60);
            if ($time_in and $duration and !$time_out) $time_out = $time_in + $duration*60;

            // If some values are still missing, start assigning defaults
            if (!$time_in) $time_in = time();
            if (!$duration) $duration = 10;
            $time_out = $time_in + $duration*60;

            $duration_hours = intval ($duration/60);
            $duration_minutes = abs(($duration % 60));
            $duration_minutes = str_pad ($duration_minutes, 2, '0', STR_PAD_LEFT);
            if ($duration < 0 and $duration_hours==0) $duration_hours = '-'.$duration_hours;

            $default_location_id = $ticket_detail->location_id;
            $default_activity_id = $ticket_detail->activity_id;
            if(!$default_location_id) $default_location_id = DEFAULT_LOCATION_ID;
            if(!$default_activity_id) $default_activity_id = DEFAULT_ACTIVITY_ID;


            $location = new InterventionLocation($default_location_id);
            $acttype = new ActionType($default_activity_id);

            //get work_markers
            $markers = WorkMarker::get_working_detail($this->current_user->id, $ticket->id);

            // Mark the potential customer for locking
            $_SESSION['potential_lock_customer_id'] = $ticket->customer_id;


            $this->assign ('ticket', $ticket);
            $this->assign ('available_orders_list', $available_orders_list);
            $this->assign ('orders_billable_list', $orders_billable_list);
            $this->assign ('users', $users);
            $this->assign ('customer_users', $customer_users);
            $this->assign ('users_all', $users_all);
            $this->assign ('ticket_detail', $ticket_detail);
            $this->assign ('action_types', $action_types);
            $this->assign ('action_types_customer', $action_types_customer);
            $this->assign ('interventions_list', $interventions_list);
            $this->assign ('available_interventions_list', $available_interventions_list);
            $this->assign ('tasks', $tasks);
            $this->assign ('has_prv', $has_private);
            $this->assign ('markers', $markers);

            $this->assign ('time_in', $time_in);
            $this->assign ('time_out', $time_out);
            $this->assign ('duration', $duration);
            $this->assign ('duration_minutes', $duration_minutes);
            $this->assign ('duration_hours', $duration_hours);
            $this->assign ('location', $location);
            $this->assign ('acttype', $acttype);

            $this->assign ('TICKET_SOURCES', $GLOBALS ['TICKET_SOURCES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('TICKET_OBJECT_CLASSES', $GLOBALS['TICKET_OBJECT_CLASSES']);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir('ticket_edit_submit', array ('ticket[id]' => $ticket->id));

            $this->display ($tpl);
    }


    /** Processes the request to edit a ticket and/or add a new entry */
    function ticket_edit_submit ()
    {
            class_load("WorkMarker");
            $ticket = new Ticket ($this->vars['ticket']['id']);
            $old_status = $ticket->status;
            $old_customer_order_id = $ticket->customer_order_id;

            check_auth (array('customer_id' => $ticket->customer_id));

            $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir('manage_tickets'));
            $params = $this->set_carry_fields (array('id', 'returl'));
            if(empty($params['id'])) $params['id'] = $ticket->id;

            $no_notifs = false;
            if ($this->vars['mark_reopen_no_notifs']) {$this->vars['mark_reopen']=true; $no_notifs=true;}
            if ($this->vars['mark_closed_no_notifs']) {$this->vars['mark_closed']=true; $no_notifs=true;}
            if ($this->vars['save_no_notifs']) {$this->vars['save']=true; $no_notifs=true;}

             if($this->vars['print_report']){
                //debug('here');
                return $this->mk_redir('print_ticket', array('ticket_id'=>$ticket->id));
            }

            //if($this->cacheObj) $this->cacheObj->delete_key($this->vars['cache_key']);

            if ($ticket->customer->onhold and $this->vars['mark_closed'])
            {
                $ticket_data['status'] = TICKET_STATUS_CLOSED;
                $ticket->load_from_array ($ticket_data);

                if($ticket->is_valid_data())
                {
                    $ticket->save_data();
                    if ($ticket->status == TICKET_STATUS_CLOSED)
                    {
                            // Delete any associated tasks
                            class_load ('Task');
                            Task::delete_for_ticket ($ticket->id);
                    }
                    // Since the ticket has been modified, dispatch notifications
                    if (!$no_notifs)
                    {
                            $type = ($ticket->status == TICKET_STATUS_CLOSED ? TICKET_NOTIF_TYPE_CLOSED : TICKET_NOTIF_TYPE_UPDATED);
                            $ticket->dispatch_notifications ($type, get_uid());
                    }
                } else {
                    error_msg('Ticket could not be closed.');
                }

                return $this->mk_redir('ticket_edit', $params);
            }

            if (($this->vars['save'] or $this->vars['add_entry'] or $this->vars['mark_closed'] or $this->vars['mark_reopen'] or $this->vars['mark_unescalated'] or $this->vars['mark_escalated']) and $ticket->id and !$ticket->onhold)
            {
                    // ANY ticket modification will result in adding a new entry to the ticket

                    // The list of ticket fields to be checked for modifications
                    // If these fields have been modfied, a comment to that effect will be aded
                    // to the ticket, if no comment is provided by the user.
                    $check_fields = array ('type', 'priority', 'owner_id', 'assigned_id', 'private', 'deadline', 'status', 'escalated', 'billable', 'customer_order_id', 'intervention_report_id', 'work_time', 'subject', 'po');
                    $fields_names = array (
                            'type' => 'Type',
                            'priority' => 'Priority',
                            'owner_id' => 'Owner',
                            'assigned_id' => 'Assigned',
                            'private' => 'Private',
                            'deadline' => 'Deadline',
                            'status' => 'Status',
                            'escalated' => 'Escalated',
                            'billable' => 'Billable',
                            'customer_order_id' => 'Order/Subscription',
                            'intervention_report_id' => 'Intervention report',
                            'work_time' => 'Work time',
                            'subject' => 'Subject',
                            'po' => 'PO Code'
                    );
                    $ticket_modified = false;
                    $ticket_closed = false;
                    $ticket_reopened = false;
                    $ticket_escalated = false;
                    $ticket_unescalated = false;

                    // The list of fields which, if only these are changed and no comments are entered, then the new ticket
                    // detail will be marked as private.
                    $fields_changed_private = array ('owner_id', 'private', 'deadline', 'escalated', 'billable', 'assigned_id', 'customer_order_id');

                    $ticket_data = $this->vars['ticket'];
                    $ticket_detail_data = $this->vars['ticket_detail'];

                    $td_comments = $this->vars['ticket_detail']['comments'];
                    /*if(strstr($td_comments, "<p>") != FALSE)
                    {*/
                            //html code
                            //$td_comments = preg_replace("/<br \/><p>&nbsp;<\/p>/", "<br />", $td_comments);
                            // $td_comments = str_replace("<br /><", "<", $td_comments);
                            //$td_comments = preg_replace("/<p>&nbsp;<\/p>+/", "<p>&nbsp;</p>", $td_comments);
                            //$td_comments = preg_replace("/(\r\n)+/", "<br />", $td_comments);
                            //$td_comments = str_replace("<br /></td>", "</td>", $td_comments);
                            //$td_comments = str_replace("</td><br />", "</td>", $td_comments);
                    $ticket_detail_data['comments'] = $td_comments;
                    /*}*/
                    // Ensure proper formatting of the ticket data
                    $ticket_data['assigned_id'] = $ticket_detail_data['assigned_id'];
                    $ticket_data['last_modified'] = time();
                    $ticket_data['escalated'] = ($ticket_data['escalated'] ? $ticket_data['escalated'] : 0);
                    $ticket_data['customer_order_id'] = ($ticket_data['customer_order_id'] ? $ticket_data['customer_order_id'] : 0);
                    if ($ticket_data['deadline']) $ticket_data['deadline'] = js_strtotime ($ticket_data['deadline']);
                    else $ticket_data['deadline'] = 0;
                    if (!$ticket_data['private']) $ticket_data['private'] = 0;

                    if ($this->vars['mark_closed'])
                    {
                            $ticket_data['status'] = TICKET_STATUS_CLOSED;
                            $ticket_closed = true;
                    }
                    elseif ($this->vars['mark_reopen'])
                    {
                            $ticket_data['status'] = TICKET_STATUS_ASSIGNED;
                            $ticket_reopened = true;

                            // When reopening, use the last known ticket attributes
                            // This is needed because, when the form is closed and the "re-open" button is pressed,
                            // some data (which is not valid) still comes from the form
                            $ticket_data['assigned_id'] = $ticket->assigned_id;
                            $ticket_detail_data['assigned_id'] = $ticket->assigned_id;
                            $ticket_data['deadline'] = $ticket->deadline;
                            $ticket_data['private'] = $ticket->private;
                            $ticket_data['escalated'] = $ticket->escalated;
                            $ticket_data['customer_order_id'] = $ticket->customer_order_id;
                            $ticket_data['seen_manager_id'] = 0;
                            $ticket_data['seen_manager_date'] = 0;
                    }
                    elseif ($this->vars['mark_escalated'])
                    {
                            $ticket_data['escalated'] = time ();
                            $ticket_escalated = true;
                    }
                    elseif ($this->vars['mark_unescalated'])
                    {
                            $ticket_data['escalated'] = 0;
                            $ticket_unescalated = true;
                    }


                    // Check what fields, if any, have actually changed
                    $ticket_reassigned = ($ticket->assigned_id != $ticket_detail_data['assigned_id']);
                    $fields_changed = array ();
                    foreach ($check_fields as $field)
                    {
                            if ($ticket->$field != $ticket_data[$field])
                            {
                                    $ticket_modified = true;
                                    $fields_changed[] = $field;
                            }
                    }

                    if ($this->vars['add_entry'])
                    {
                            // Check_first if there is indeed something to save
                            $has_text = !(empty ($ticket_detail_data['comments']));
                            $action_type = (!empty ($ticket_detail_data['activity_id']));
                            $has_time = (!empty($ticket_detail_data['work_time']));

                            $ticket_modified = ($has_text or $reassigned or $action_type or $has_time or $status_changed);
                    }


                    // Save the ticket if any modifications were made
                    if ($ticket_modified or $ticket_closed or $ticket_reopened or $ticket_reassigned or $ticket_unescalated or $ticket_escalated)
                    {
                            // Some modifications have been made to the ticket
                            $ticket->load_from_array ($ticket_data);

                            // If no specific comments have been provided by user,
                            // add an explanatory text.
                            if (empty ($ticket_detail_data ['comments']))
                            {
                                    $diff = array_diff($fields_changed, $fields_changed_private);

                                    if (!empty($diff)) $ticket_detail_data ['private'] = 0;
                                    else $ticket_detail_data ['private'] = 1;

                                    if ($ticket_closed) $ticket_detail_data ['comments'] = 'Ticket closed';
                                    elseif ($ticket_reopened) $ticket_detail_data ['comments'] = 'Ticket re-opened';
                                    else
                                    {
                                            $ticket_detail_data ['comments'] = 'Ticket updated';
                                            if ($ticket_detail_data['private'] and !empty($fields_changed))
                                            {
                                                    $ticket_detail_data ['comments'].= ' (';
                                                    // Specify which fields have been changed
                                                    foreach ($fields_changed as $field)
                                                    {
                                                            $ticket_detail_data ['comments'].= $fields_names[$field].', ';
                                                    }
                                                    $ticket_detail_data ['comments'] = preg_replace ('/\, $/', '', $ticket_detail_data ['comments']).')';
                                            }
                                    }
                            }

                            // Create the ticket detail to be added
                            $ticket_detail_data ['created'] = time();
                            $ticket_detail_data ['user_id'] = get_uid ();
                            $ticket_detail_data ['status'] = $ticket->status;
                            $ticket_detail_data ['escalated'] = $ticket->escalated;
                            if (!$ticket_detail_data['private']) $ticket_detail_data['private'] = 0;

                            $ticket_detail = new TicketDetail ();
                            $ticket_detail->load_from_array ($ticket_detail_data);
                            $ticket_detail->ticket_id = $ticket->id;
                            $ticket_detail->customer_order_id = $ticket->customer_order_id;

                            if ($ticket->is_valid_data() and $ticket_detail->is_valid_data())
                            {
                                    // If any change was made but status is still "New", change status to "Opened"
                                    // (unless the change to "New" was specifically requested)
                                    if ($ticket->status == TICKET_STATUS_NEW and $old_status == TICKET_STATUS_NEW)
                                    {
                                            $ticket->status = TICKET_STATUS_ASSIGNED;
                                            $ticket_detail->status = $ticket->status;
                                    }

                                    $ticket->save_data ();
                                    $ticket_detail->save_data ();
                                    $ticket_detail->load_data ();

                                    if($this->vars['work_marker'])
                                    {
                                            WorkMarker::mark_working($this->current_user->id, $ticket_detail->id);
                                    }


                                    if ($ticket->status == TICKET_STATUS_CLOSED)
                                    {
                                            // Delete any associated tasks
                                            class_load ('Task');
                                            Task::delete_for_ticket ($ticket->id);
                                    }

                                    if ($ticket_closed) $ticket->log_action ($this->current_user->id, TICKET_ACCESS_CLOSE);
                                    elseif ($ticket_reopened) $ticket->log_action ($this->current_user->id, TICKET_ACCESS_REOPEN);
                                    elseif ($ticket_escalated) $ticket->log_action ($this->current_user->id, TICKET_ACCESS_ESCALATE);
                                    elseif ($ticket_unescalated) $ticket->log_action ($this->current_user->id, TICKET_ACCESS_UNESCALATE);
                                    else $ticket->log_action ($this->current_user->id, TICKET_ACCESS_SAVE);

                                    $ticket_detail->log_action ($this->current_user->id, TICKET_ACCESS_DETAIL_CREATE);

                                    // Since the ticket has been modified, dispatch notifications
                                    if (!$no_notifs)
                                    {
                                            $type = ($ticket->status == TICKET_STATUS_CLOSED ? TICKET_NOTIF_TYPE_CLOSED : TICKET_NOTIF_TYPE_UPDATED);
                                            $ticket->dispatch_notifications ($type, get_uid());
                                    }

                                    // Check if a different customer order has been assigned to the ticket,
                                    // and if yes, update all existing ticket details.
                                    if ($old_customer_order_id != $ticket->customer_order_id)
                                    {
                                            $ticket->reset_details_customer_orders ();
                                    }
                            }
                            else
                            {
                                    save_form_data ($ticket_data, 'ticket_data');
                                    save_form_data ($ticket_detail_data, 'ticket_detail_data');
                            }
                    }
                    else
                    {
                            error_msg ('No save was performed, because the ticket data has not been modified in any way.');
                            save_form_data ($ticket_data, 'ticket_data');
                            save_form_data ($ticket_detail_data, 'ticket_detail_data');
                    }

                    $ret = $this->mk_redir('ticket_edit', $params);
            }
            elseif ($this->vars['delete'] and $ticket->id)
            {
                    // Deleting tickets is not allowed anymore
                    //$ticket->delete ();
            }
            elseif ($this->vars['mark_now_working'])
            {
                    $ticket->mark_now_working ($this->current_user->id);
                    $ret = $this->mk_redir ('ticket_edit', $params);
            }
            elseif ($this->vars['unmark_now_working'])
            {
                    $ticket->unmark_now_working ($this->current_user->id);
                    $ret = $this->mk_redir ('ticket_edit', $params);
            }
            elseif ($this->vars['mark_seen_manager'])
            {
                    $ticket->seen_manager_id = $this->current_user->id;
                    $ticket->seen_manager_date = time ();
                    $ticket->save_data ();
                    $ret = $this->mk_redir('ticket_edit', $params);
            }
            elseif ($this->vars['unmark_seen_manager'])
            {
                    $ticket->seen_manager_id = 0;
                    $ticket->seen_manager_date = 0;
                    $ticket->save_data ();
                    $ret = $this->mk_redir ('ticket_edit', $params);
            }

            if($this->vars['work_marker_stop'])
            {
                    $dts = $this->vars['detail_to_stop'];
                    //debug($dts);
                    WorkMarker::close_marker($this->current_user->id, $dts);
                    $ret = $this->mk_redir ('ticket_edit', $params);
            }

            return $ret;
    }

    function print_ticket(){
        check_auth(array('ticket_id'=>$this->vars['ticket_id']));
        //debug($this->vars);
        class_load('Customer');
        $ticket = new Ticket($this->vars['ticket_id']);
        $customers_list = Customer::get_customers_list($customers_filter);

        if(!$ticket->id) return $this->mk_redir('manage_tickets');
        $xml_tpl = "ticket_print.xml";
        $xsl_tpl = "ticket_print.xsl_fo";

        $this->assign('ticket', $ticket);
        $this->assign('customers_list', $customers_list);
        $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
        $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
        $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
        $this->assign ('TICKETS_PRIORITIES_COLORS', $GLOBALS ['TICKETS_PRIORITIES_COLORS']);

        //$this->display_template_only ($xml_tpl);
        $xml = $this->fetch($xml_tpl);

        make_pdf_xml ($xml, $xsl_tpl);

        die;

       return $this->mk_redir('ticket_edit', array('ticket_id'=>$ticket->id));
    }

    /** Displays the page for reassigning a ticket to a customer */
    function ticket_edit_customer ()
    {
            $ticket = new Ticket ($this->vars['id']);
            check_auth (array('customer_id' => $ticket->customer_id));
            $params = $this->set_carry_fields (array('id', 'returl'));

            if ($ticket->can_change_customer ())
            {
                    $tpl = 'ticket_edit_customer.tpl';

                    $customers_filter = array ('has_krifs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => 1);
                    if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
                    $customers_list = Customer::get_customers_list ($customers_filter);

                    $this->assign ('ticket', $ticket);
                    $this->assign ('customers_list', $customers_list);
                    $this->assign ('error_msg', error_msg ());
                    $this->set_form_redir ('ticket_edit_customer_submit', $params);

                    $this->display ($tpl);
            }
            else
            {
                    return $this->mk_redir ('ticket_edit', $params);
            }
    }

    /** Reassign the customer ticket */
    function ticket_edit_customer_submit ()
    {
            $ticket = new Ticket ($this->vars['id']);
            check_auth (array('customer_id' => $ticket->customer_id));
            $params = $this->set_carry_fields (array('id', 'returl'));
            $ret = $this->mk_redir ('ticket_edit', $params);

            if ($this->vars['save'] and $this->vars['customer_id'] and $ticket->can_change_customer ())
            {
                    $ticket->customer_id = $this->vars['customer_id'];
                    $ticket->save_data ();
            }

            return $ret;
    }

    /** Displays the page for entering comments for "Seen by manager" */
    function ticket_edit_manager_comments ()
    {
            $tpl = 'ticket_edit_manager_comments.tpl';
            $ticket = new Ticket ($this->vars['id']);
            if (!$ticket->id or !$this->current_user->is_manager) return $this->mk_redir('manage_tickets');
            check_auth (array('ticket_id' => $ticket->id));

            $params = $this->set_carry_fields (array('id', 'returl'));
            $this->assign ('ticket', $ticket);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_edit_manager_comments_submit', $params);
            $this->display ($tpl);
    }

    /** Saves the manager comments */
    function ticket_edit_manager_comments_submit ()
    {
            $ticket = new Ticket ($this->vars['id']);
            if (!$ticket->id or !$this->current_user->is_manager) return $this->mk_redir('manage_tickets');
            check_auth (array('ticket_id' => $ticket->id));
            $ret = $this->mk_redir ('ticket_edit', array('id'=>$ticket->id));

            if ($this->vars['save'])
            {
                    $ticket->seen_manager_comments = $this->vars['comments'];
                    $ticket->save_data ();
            }
            return $ret;
    }


    /** Marks that the current logged in user is working on the specified ticket */
    function ticket_mark_working ()
    {
            $ticket = new Ticket ($this->vars['id']);
            check_auth (array('ticket_id' => $ticket->id));

            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));

            if ($ticket->id)
            {
                    $ticket->mark_now_working ($this->current_user->id);
            }

            return $ret;
    }

    /** Marks that the specified user is not working on anything anymore */
    function ticket_unmark_working ()
    {
            check_auth ();

            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));

            Ticket::unmark_now_working ($this->current_user->id);

            return $ret;
    }


    /** Displays a page for editing a ticket entry (detail) */
    function ticket_detail_edit ()
    {
            class_load ('InterventionReport');
            check_auth (array('ticket_id' => $this->vars['id']));
            $tpl = 'ticket_detail_edit.tpl';

            $ticket_detail = new TicketDetail ($this->vars['id']);
            $ticket = new Ticket ($ticket_detail->ticket_id);
            if (!$ticket_detail->id) return $this->mk_redir ('manage_tickets');

            if (!empty_error_msg())
            {
                    $data = array();
                    $ticket_detail->load_from_array (restore_form_data ('ticket_detail', false, $data));
            }

            $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
            $users = $users + $groups;

            // Fetch the action types - both the complete list and the actions currently available for this customer
            $action_types = ActionType::get_list ();
            $filter_customer_actions = array (
                    'group_by' => 'category',
                    'contract_type_cust' => $customer->contract_type,
                    'contract_sub_type' => $customer->contract_sub_type
            );

            $action_types_customer = ActionType::get_action_types ($filter_customer_actions);

            // Get all available (open) intervention reports for this customer
            $filter_interventions = array (
                    'customer_id' => $ticket->customer_id,
                    'status' => INTERVENTION_STAT_OPEN,
                    'show_ids' => true
            );
            $available_interventions_list = InterventionReport::get_interventions_list ($filter_interventions);

            $locations_list = InterventionLocation::get_locations_list ();

            $params = $this->set_carry_fields (array('id', 'ret', 'returl'));

            // Check if the ticket is editable
            $is_editable = $ticket_detail->can_modify (false);

            // Load the intervention report
            if ($ticket_detail->intervention_report_id)
            {
                    $intervention_report = new InterventionReport ($ticket_detail->intervention_report_id);
                    $this->assign ('intervention_report', $intervention_report);
            }

            $this->assign ('ticket_detail', $ticket_detail);
            $this->assign ('is_editable', $is_editable);
            $this->assign ('ticket', $ticket);
            $this->assign ('users', $users);
            $this->assign ('action_types', $action_types);
            $this->assign ('action_types_customer', $action_types_customer);
            $this->assign ('available_interventions_list', $available_interventions_list);
            $this->assign ('TICKET_SOURCES', $GLOBALS ['TICKET_SOURCES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('ticket_detail_edit_submit', $params);

            $this->display ($tpl);
    }


    /** Saves the changes made to the ticket detail, or deletes a ticket detail */
    function ticket_detail_edit_submit ()
    {
            check_auth (array('ticket_id' => $this->vars['id']));
            $ticket_detail = new TicketDetail ($this->vars['id']);

            $params = $this->set_carry_fields (array('id', 'ret', 'returl'));
            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else
            {
                    $ret = $this->mk_redir ('ticket_edit', array('id' => $ticket_detail->ticket_id));
            }

            $no_notifs = false;
            if ($this->vars['save_no_notifs']) {$this->vars['save']=true; $no_notifs=true;}

            if ($this->vars['save'] and $ticket_detail->id)
            {
                    $ticket_detail_data = $this->vars['ticket_detail'];
        $td_comments = $this->vars['ticket_detail']['comments'];
                    $ticket_detail_data['comments'] = $td_comments;
                    if (!$ticket_detail_data['private']) $ticket_detail_data['private'] = 0;

                    $ticket_detail->load_from_array ($ticket_detail_data);

                    if ($ticket_detail->is_valid_data ())
                    {
                            $ticket_detail->save_data ();
                            $ticket_detail->log_action ($this->current_user->id, TICKET_ACCESS_DETAIL_SAVE);

                            $ticket = new Ticket ($ticket_detail->ticket_id);

                            if ($ticket->is_last_entry ($ticket_detail->id))
                            {
                                    // This was the last made entry, so syncronize the assignment
                                    // and dispatch notifications.
                                    $ticket->assigned_id = $ticket_detail->assigned_id;
                                    $ticket->save_data ();
                                    $ticket->load_data ();

                                    if (!$no_notifs)
                                    {
                                            $type = ($ticket->status == TICKET_STATUS_CLOSED ? TICKET_NOTIF_TYPE_CLOSED : TICKET_NOTIF_TYPE_UPDATED);
                                            $ticket->dispatch_notifications ($type, get_uid());
                                    }
                            }
                    }
                    else
                    {
                            save_form_data ($ticket_detail_data, 'ticket_detail');
                    }

                    $ret = $this->mk_redir ('ticket_detail_edit', $params);
            }
            elseif ($this->vars['delete'] and $ticket_detail->id)
            {
                    $ticket = new Ticket ($ticket_detail->ticket_id);

                    if (count($ticket->details) <= 1)
                    {
                            error_msg ('This entry can\'t be deleted, it is the only one for this ticket.');
                    }
                    else
                    {
                            $ticket_detail->delete ();
                    }
            }
            return $ret;
    }

    /** Saves the changes made to the ticket detail, or deletes a ticket detail */
    function ticket_detail_edit_submit_ajax ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_detail_id']));
            $ticket_detail = new TicketDetail ($this->vars['ticket_detail_id']);

            $params = $this->set_carry_fields (array('ticket_detail_id', 'ret', 'returl'));
            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else
            {
                    $ret = $this->mk_redir ('ticket_edit', array('id' => $ticket_detail->ticket_id));
            }

            $no_notifs = false;
            if ($this->vars['save_no_notifs']) {$this->vars['save']=true; $no_notifs=true;}

            if ($this->vars['save'] and $ticket_detail->id)
            {
                    $this->vars['detail']['work_time'] = js_durationtomins($this->vars['detail']['work_time']);
                    $ticket_detail_data = $this->vars['detail'];

                    if (!$ticket_detail_data['private']) $ticket_detail_data['private'] = 0;

                    $ticket_detail->load_from_array ($ticket_detail_data);

                    if ($ticket_detail->is_valid_data ())
                    {
                            $ticket_detail->save_data ();
                            $ticket_detail->log_action ($this->current_user->id, TICKET_ACCESS_DETAIL_SAVE);

                            $ticket = new Ticket ($ticket_detail->ticket_id);

                            if ($ticket->is_last_entry ($ticket_detail->id))
                            {
                                    // This was the last made entry, so syncronize the assignment
                                    // and dispatch notifications.
                                    $ticket->assigned_id = $ticket_detail->assigned_id;
                                    $ticket->save_data ();
                                    $ticket->load_data ();

                                    if (!$no_notifs)
                                    {
                                            $type = ($ticket->status == TICKET_STATUS_CLOSED ? TICKET_NOTIF_TYPE_CLOSED : TICKET_NOTIF_TYPE_UPDATED);
                                            $ticket->dispatch_notifications ($type, get_uid());
                                    }
                            }
                    }
                    else
                    {
                            save_form_data ($ticket_detail_data, 'ticket_detail');
                    }

                    $ret = $this->mk_redir ('ticket_detail_edit', $params);
            }
            elseif ($this->vars['delete'] and $ticket_detail->id)
            {
                    $ticket = new Ticket ($ticket_detail->ticket_id);

                    if (count($ticket->details) <= 1)
                    {
                            error_msg ('This entry can\'t be deleted, it is the only one for this ticket.');
                    }
                    else
                    {
                            $ticket_detail->delete ();
                    }
            }
            //return $ret;
            echo error_msg();
            debug($this->vars['detail']);
    }


    /** Displays the page for changing the user who performed a ticket detail */
    function ticket_detail_edit_user ()
    {
            $ticket_detail = new TicketDetail($this->vars['id']);
            $ticket = new Ticket ($ticket_detail->ticket_id);
            check_auth (array('ticket_id' => $ticket->id));
            $tpl = 'ticket_detail_edit_user.tpl';

            if (!$ticket->id) return $this->mk_redir ('manage_tickets');

            $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $params = $this->set_carry_fields (array('id', 'returl'));

            $this->assign ('ticket', $ticket);
            $this->assign ('ticket_detail', $ticket_detail);
            $this->assign ('users', $users);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_detail_edit_user_submit', $params);

            $this->display ($tpl);
    }


    /** Saves the change of owner user to a ticket detail */
    function ticket_detail_edit_user_submit ()
    {
            $ticket_detail = new TicketDetail($this->vars['id']);
            $ticket = new Ticket ($ticket_detail->ticket_id);
            check_auth (array('ticket_id' => $ticket->id));

            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else $ret = $this->mk_redir ('ticket_detail_edit', array ('id' => $ticket_detail->id));

            if ($this->vars['save'] and $ticket_detail->id and $this->vars['ticket_detail']['user_id'])
            {
                    $ticket_detail->user_id = $this->vars['ticket_detail']['user_id'];
                    $ticket_detail->save_data ();
                    $ticket_detail->log_action ($this->current_user->id, TICKET_ACCESS_DETAIL_SAVE);
            }

            return $ret;
    }


    /** Displays the page for adding attachments to a ticket */
    function ticket_attachment_add ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_id']));
            $tpl = 'ticket_attachment_add.tpl';

            $ticket = new Ticket ($this->vars['ticket_id']);

            if (!$ticket->id) return $this->mk_redir ('manage_tickets');

            $this->assign ('ticket', $ticket);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('ticket_attachment_add_submit', array ('ticket_id' => $ticket->id));

            $this->display ($tpl);
    }


    /** Adds the new attachment to the ticket */
    function ticket_attachment_add_submit ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_id']));
            $ticket = new Ticket ($this->vars['ticket_id']);
            $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));

            if ($this->vars['save'] and $ticket->id)
            {
                    if ($_FILES['attachment']['name'])
                    {
                            $data = array (
                                    'name' =>  $_FILES['attachment']['name'],
                                    'tmp_name' => $_FILES['attachment']['tmp_name'],
                                    'ticket_id' => $ticket->id,
                                    'user_id' => get_uid ()
                            );

                            $attachment = new TicketAttachment ();
                            $attachment->load_from_array ($data);

                            if ($attachment->is_valid_data ())
                            {
                                    $attachment->save_data ();
                                    $ticket->log_action ($this->current_user->id, TICKET_ACCESS_ATTACH_ADD);
                                    $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));
                            }
                            else
                            {
                                    error_msg ('Uploading file has failed, please try again');
                                    $ret = $this->mk_redir ('ticket_attachment_add', array('ticket_id' => $ticket->id));
                            }
                    }
                    else
                    {
                            error_msg ('Please specify an attachment to upload');
                            $ret = $this->mk_redir ('ticket_attachment_add', array('ticket_id' => $ticket->id));
                    }
            }

            return $ret;
    }

    /** Displays the page for adding attachments to a ticket */
    function ticket_attachment_add_iframe ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_id']));
            $tpl = 'ticket_attachment_add_iframe.tpl';

            $ticket = new Ticket ($this->vars['ticket_id']);

            if (!$ticket->id) return $this->mk_redir ('manage_tickets');

            $this->assign ('ticket', $ticket);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('ticket_attachment_add_iframe_submit', array ('ticket_id' => $ticket->id));

            $this->display_template_limited ($tpl);
    }


    /** Adds the new attachment to the ticket */
    function ticket_attachment_add_iframe_submit ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_id']));
            $ticket = new Ticket ($this->vars['ticket_id']);
            $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));

            if ($this->vars['save'] and $ticket->id)
            {
                    if ($_FILES['attachment']['name'])
                    {
                            $data = array (
                                    'name' =>  $_FILES['attachment']['name'],
                                    'tmp_name' => $_FILES['attachment']['tmp_name'],
                                    'ticket_id' => $ticket->id,
                                    'user_id' => get_uid ()
                            );

                            $attachment = new TicketAttachment ();
                            $attachment->load_from_array ($data);

                            if ($attachment->is_valid_data ())
                            {
                                    $attachment->save_data ();
                                    $ticket->log_action ($this->current_user->id, TICKET_ACCESS_ATTACH_ADD);
                                    echo '<script type="text/javascript">';
                                    echo 'top.location.href = top.location.href;';
                                    echo 'top.$.fancybox.close();';
                                    echo '</script>';
                                    return;
                            }
                            else
                            {
                                    error_msg ('Uploading file has failed, please try again');
                                    $ret = $this->mk_redir ('ticket_attachment_add_iframe', array('ticket_id' => $ticket->id));
                            }
                    }
                    else
                    {
                            error_msg ('Please specify an attachment to upload');
                            $ret = $this->mk_redir ('ticket_attachment_add_iframe', array('ticket_id' => $ticket->id));
                    }
            }

            return $ret;
    }


    /** Opens a file from an attachment */
    function ticket_attachment_open ()
    {
            $attachment = new TicketAttachment ($this->vars['id']);

            check_auth (array('ticket_id' => $attachment->ticket_id));

            if($attachment->local_filename){
                $local_path = explode("/", $attachment->local_filename);
                $new_path="";
                if(count($local_path) > 2){
                    if($local_path[1] == "srv"){
                        $new_path_aray = array();
                        for($i=2;$i < count($local_path); $i++){
                            $new_path_aray[] = $local_path[$i];
                        }
                        $np = implode("/",$new_path_aray);
                        $new_path = "/var/".$np;

                    }
                }
                if($new_path != "") $attachment->local_filename = $new_path;
            }
            //debug($attachment->local_path);
            if (!$attachment->local_filename or !file_exists($attachment->local_filename))
            {
                    error_msg ('Sorry, the attachment file is missing');
                    return $this->mk_redir ('ticket_edit', array ('id' => $attachment->ticket_id));
            }
            else
            {
                    @ob_end_clean();
                    @ini_set('zlib.output_compression', 'Off');
                    session_write_close ();

                    header ("Pragma: public");
                    //header ("Cache-Control: no-store, no-cache, must_revalidate");
                    header ("Cache-Control: private");
                    header ("Content-Transfer-Encoding: none");
                    header ("Content-type: application/octet-stream;");
                    header ("Content-Disposition: attachment; filename=".$attachment->original_filename.";");
                    header ('Content-Length: '.filesize($attachment->local_filename));
                    header ("Connection: close");

                    readfile($attachment->local_filename);
                    exit;
            }
    }

    /** Deletes an attachment from a ticket */
    function ticket_attachment_delete ()
    {
            $attachment = new TicketAttachment ($this->vars['id']);

            check_auth (array('ticket_id' => $attachment->ticket_id));

            $ticket_id = $attachment->ticket_id;
            $ticket = new Ticket ($ticket_id);
            $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket_id));

            $attachment->delete ();
            $ticket->log_action ($this->current_user->id, TICKET_ACCESS_ATTACH_DELETE);

            return $ret;
    }


    /** Displays the page for selecting the objects to add to a ticket */
    function ticket_object_add ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_id']));
            class_load ('RemovedComputer');
            class_load ('AD_Computer');
            class_load ('AD_User');
            class_load ('AD_Group');
            class_load ('AD_Printer');
            class_load ('Peripheral');
            class_load ('PeripheralClass');
            class_load ('CustomerInternetContract');
            $tpl = 'ticket_object_add.tpl';

            $obj_class = $this->vars['object_class'];
            $ticket_id = $this->vars['ticket_id'];

            $ticket = new Ticket ($ticket_id);
            if (!$ticket->id) return $this->mk_redir ('manage_tickets');

            $headers_list = array ();	// Array with the table headers for displaying the list of objects
            $objects_list = array ();	// Array with the object values. Each element is an array, with the same number of
                                            // elements as $headers_list

            if ($obj_class == TICKET_OBJ_CLASS_COMPUTER)
            {
                    $headers_list = array ('ID', 'Computer name');
                    $objects = Computer::get_computers (array('customer_id' => $ticket->customer_id, 'order_by' => 'netbios_name'), $no_count);

                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $objects_list[$objects[$i]->id] = array ($objects[$i]->id, $objects[$i]->netbios_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_REMOVED_COMPUTER)
            {
                    $headers_list = array ('ID', 'Computer name');
                    $objects = RemovedComputer::get_removed_computers (array('customer_id' => $ticket->customer_id, 'order_by' => 'netbios_name'), $no_count);

                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $objects_list[$objects[$i]->id] = array ($objects[$i]->id, $objects[$i]->netbios_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_PERIPHERAL)
            {
                    $headers_list = array ('ID', 'Class', 'Peripheral name');
                    $objects = Peripheral::get_peripherals (array('customer_id' => $ticket->customer_id));

                    $classes_list = PeripheralClass::get_classes_list ();
                    foreach ($objects as $class_id => $peripherals)
                    {
                            foreach ($peripherals as $peripheral)
                            {
                                    $objects_list[$peripheral->id] = array ($peripheral->id, $classes_list[$class_id], $peripheral->name);
                            }
                    }

            }
            elseif ($obj_class == TICKET_OBJ_CLASS_MONITORED_IP)
            {
                    class_load ('MonitoredIP');
                    $headers_list = array ('Remote IP', 'Target IP', 'Status');
                    $objects = MonitoredIP::get_monitored_ips (array('customer_id' => $ticket->customer_id));
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $objects_list[$objects[$i]->id] = array ($objects[$i]->remote_ip, $objects[$i]->target_ip, $GLOBALS['MONITOR_STATS'][$objects[$i]->status]);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_USER)
            {
                    $headers_list = array ('ID', 'Name', 'Login');
                    $objects = User::get_users (array('customer_id' => $ticket->customer_id), $no_count);

                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $objects_list[$objects[$i]->id] = array ($objects[$i]->id, $objects[$i]->get_name(), $objects[$i]->login);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_AD_COMPUTER)
            {
                    $headers_list = array ('CN', 'Distinguished name');
                    $objects = AD_Computer::get_ad_computers (array('customer_id' => $ticket->customer_id));

                    // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                            $objects_list[$id] = array ($objects[$i]->cn, $objects[$i]->distinguished_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_AD_USER)
            {
                    $headers_list = array ('User name', 'Distinguished name');
                    $objects = AD_User::get_ad_users (array('customer_id' => $ticket->customer_id));

                    // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                            $objects_list[$id] = array ($objects[$i]->sam_account_name, $objects[$i]->distinguished_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_AD_GROUP)
            {
                    $headers_list = array ('Name', 'Distinguished name');
                    $objects = AD_Group::get_ad_groups (array('customer_id' => $ticket->customer_id));

                    // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                            $objects_list[$id] = array ($objects[$i]->name, $objects[$i]->distinguished_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_AD_PRINTER)
            {
                    $headers_list = array ('Name', 'Server name');
                    $objects = AD_Printer::get_ad_printers (array('customer_id' => $ticket->customer_id));

                    // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                            $objects_list[$id] = array ($objects[$i]->name, $objects[$i]->server_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_INTERNET_CONTRACT)
            {
                    $headers_list = array ('ID', 'Contract');
                    $objects = CustomerInternetContract::get_contracts (array('customer_id'=>$ticket->customer_id));
                    for ($i = 0; $i < count($objects); $i++) $objects_list[$objects[$i]->id] = array ($objects[$i]->id, $objects[$i]->get_name());
            }

            $this->assign ('ticket', $ticket);
            $this->assign ('objects_list', $objects_list);
            $this->assign ('headers_list', $headers_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_object_add_submit', array ('ticket_id'=>$ticket->id, 'object_class'=>$obj_class));

            $this->display ($tpl);
    }

    /** Displays the page for selecting the objects to add to a ticket */
    function ticket_object_add_iframe ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_id']));
            class_load ('RemovedComputer');
            class_load ('AD_Computer');
            class_load ('AD_User');
            class_load ('AD_Group');
            class_load ('AD_Printer');
            class_load ('Peripheral');
            class_load ('PeripheralClass');
            class_load ('CustomerInternetContract');
            $tpl = 'ticket_object_add_iframe.tpl';

            $obj_class = $this->vars['object_class'];
            $ticket_id = $this->vars['ticket_id'];

            $ticket = new Ticket ($ticket_id);
            if (!$ticket->id) return $this->mk_redir ('manage_tickets');

            $headers_list = array ();	// Array with the table headers for displaying the list of objects
            $objects_list = array ();	// Array with the object values. Each element is an array, with the same number of
                                            // elements as $headers_list

            if ($obj_class == TICKET_OBJ_CLASS_COMPUTER)
            {
                    $headers_list = array ('ID', 'Computer name');
                    $objects = Computer::get_computers (array('customer_id' => $ticket->customer_id, 'order_by' => 'netbios_name'), $no_count);

                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $objects_list[$objects[$i]->id] = array ($objects[$i]->id, $objects[$i]->netbios_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_REMOVED_COMPUTER)
            {
                    $headers_list = array ('ID', 'Computer name');
                    $objects = RemovedComputer::get_removed_computers (array('customer_id' => $ticket->customer_id, 'order_by' => 'netbios_name'), $no_count);

                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $objects_list[$objects[$i]->id] = array ($objects[$i]->id, $objects[$i]->netbios_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_PERIPHERAL)
            {
                    $headers_list = array ('ID', 'Class', 'Peripheral name');
                    $objects = Peripheral::get_peripherals (array('customer_id' => $ticket->customer_id));

                    $classes_list = PeripheralClass::get_classes_list ();
                    foreach ($objects as $class_id => $peripherals)
                    {
                            foreach ($peripherals as $peripheral)
                            {
                                    $objects_list[$peripheral->id] = array ($peripheral->id, $classes_list[$class_id], $peripheral->name);
                            }
                    }

            }
            elseif ($obj_class == TICKET_OBJ_CLASS_MONITORED_IP)
            {
                    class_load ('MonitoredIP');
                    $headers_list = array ('Remote IP', 'Target IP', 'Status');
                    $objects = MonitoredIP::get_monitored_ips (array('customer_id' => $ticket->customer_id));
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $objects_list[$objects[$i]->id] = array ($objects[$i]->remote_ip, $objects[$i]->target_ip, $GLOBALS['MONITOR_STATS'][$objects[$i]->status]);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_USER)
            {
                    $headers_list = array ('ID', 'Name', 'Login');
                    $objects = User::get_users (array('customer_id' => $ticket->customer_id), $no_count);

                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $objects_list[$objects[$i]->id] = array ($objects[$i]->id, $objects[$i]->get_name(), $objects[$i]->login);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_AD_COMPUTER)
            {
                    $headers_list = array ('CN', 'Distinguished name');
                    $objects = AD_Computer::get_ad_computers (array('customer_id' => $ticket->customer_id));

                    // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                            $objects_list[$id] = array ($objects[$i]->cn, $objects[$i]->distinguished_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_AD_USER)
            {
                    $headers_list = array ('User name', 'Distinguished name');
                    $objects = AD_User::get_ad_users (array('customer_id' => $ticket->customer_id));

                    // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                            $objects_list[$id] = array ($objects[$i]->sam_account_name, $objects[$i]->distinguished_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_AD_GROUP)
            {
                    $headers_list = array ('Name', 'Distinguished name');
                    $objects = AD_Group::get_ad_groups (array('customer_id' => $ticket->customer_id));

                    // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                            $objects_list[$id] = array ($objects[$i]->name, $objects[$i]->distinguished_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_AD_PRINTER)
            {
                    $headers_list = array ('Name', 'Server name');
                    $objects = AD_Printer::get_ad_printers (array('customer_id' => $ticket->customer_id));

                    // For AD objects, the "ID" is composed of the Kawacs server ID and the nrc (array index for the array of values)
                    for ($i = 0; $i < count($objects); $i++)
                    {
                            $id = $objects[$i]->computer_id.'_'.$objects[$i]->nrc;
                            $objects_list[$id] = array ($objects[$i]->name, $objects[$i]->server_name);
                    }
            }
            elseif ($obj_class == TICKET_OBJ_CLASS_INTERNET_CONTRACT)
            {
                    $headers_list = array ('ID', 'Contract');
                    $objects = CustomerInternetContract::get_contracts (array('customer_id'=>$ticket->customer_id));
                    for ($i = 0; $i < count($objects); $i++) $objects_list[$objects[$i]->id] = array ($objects[$i]->id, $objects[$i]->get_name());
            }

            $this->assign ('ticket', $ticket);
            $this->assign ('objects_list', $objects_list);
            $this->assign ('headers_list', $headers_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_object_add_submit', array ('ticket_id'=>$ticket->id, 'object_class'=>$obj_class));

            $this->display_template_limited($tpl);
    }


    /** Saves the objects to add to a ticket */
    function ticket_object_add_submit ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_id']));
            $ticket = new Ticket ($this->vars['ticket_id']);
            $obj_class = $this->vars['object_class'];
            $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));

            if ($this->vars['save'] and $ticket->id and $obj_class)
            {
                    $ticket->add_objects ($obj_class, $this->vars['object_ids']);
                    $ticket->log_action ($this->current_user->id, TICKET_ACCESS_OBJ_ADD);
            }

            return $ret;
    }


    /** Deletes an object reference from the ticket */
    function ticket_object_delete ()
    {
            check_auth (array('ticket_id' => $this->vars['ticket_id']));
            $ticket = new Ticket ($this->vars['ticket_id']);
            $obj_class = $this->vars['object_class'];
            $obj_id = $this->vars['object_id'];
            $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));

            if ($ticket->id and $obj_class and $obj_id)
            {
                    $ticket->delete_object ($obj_class, $obj_id);
                    $ticket->log_action ($this->current_user->id, TICKET_ACCESS_OBJ_DELETE);
            }

            return $ret;
    }


    /** Displays the list for editing the list of CC recipients for a ticket */
    function ticket_edit_cc ()
    {
            check_auth (array('ticket_id' => $this->vars['id']));
            $tpl = 'ticket_edit_cc.tpl';
            $ticket = new Ticket ($this->vars['id']);

            if (!$ticket->id) return $this->mk_redir ('manage_tickets');

            $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));

            $customer_users = array();
            if (!$ticket->private and $ticket->customer_id and $ticket->customer_id!=MANAGER_CUSTOMER_ID)
            {
                    $customer_users = User::get_users_list (array('customer_id' => $ticket->customer_id, 'type' => USER_TYPE_CUSTOMER));
            }

            $users = $users + $groups;
            $all_users = $users + $customer_users;

            $this->assign ('ticket', $ticket);
            $this->assign ('users', $users);
            $this->assign ('customer_users', $customer_users);
            $this->assign ('all_users', $all_users);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_edit_cc_submit', array ('id' => $ticket->id));

            $this->display ($tpl);
    }


    /** Saves the list of CC recipients */
    function ticket_edit_cc_submit ()
    {
            check_auth (array('ticket_id' => $this->vars['id']));
            $ticket = new Ticket ($this->vars['id']);
            $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));

            if ($this->vars['save'] and $ticket->id)
            {
                    $arr = split(";", $this->vars['ticket']['cc_manual_list']);
                    if(is_array($arr))
                    {
                            $ticket->cc_manual_list = array();
                            foreach ($arr as $eml)
                            {
                                    $ticket->cc_manual_list[]  = trim($eml);
                            }
                    }
                    $ticket->cc_list = $this->vars['ticket']['cc_list'];
                    if (!is_array($ticket->cc_list)) $ticket->cc_list = array ();
                    $ticket->save_data ();
                    $ticket->log_action ($this->current_user->id, TICKET_ACCESS_SAVE_CC);
                    $ret = $this->mk_redir ('ticket_edit_cc', array ('id' => $ticket->id));
            }

            return $ret;
    }


    /****************************************************************/
    /* Tasks scheduling 						*/
    /****************************************************************/

    /** Displays the page for viewing scheduled tasks */
    function manage_tasks ()
    {
            class_load ('Task');
            class_load ('InterventionLocation');
            check_auth ();
            $tpl = 'manage_tasks.tpl';

            // If as user was requested in URL, set filtering on that user and for current day
            if ($this->vars['user_id'])
            {
                    $filter['user_id'] = $this->vars['user_id'];
                    $filter['date'] = get_first_hour ();
                    $filter['days'] = 3;
            }
            else
            {
                    $filter = $_SESSION['manage_tasks']['filter'];
                    if (!isset($filter['user_id'])) $filter['user_id'] = $this->current_user->id;
                    if (!isset($filter['date'])) $filter['date'] = get_first_hour ();
                    if (!isset($filter['days'])) $filter['days'] = 3;
                    if (!isset($filter['order_by'])) $filter['order_by'] = 'date_start';
            }

            $customers_list = Customer::get_customers_list ();
            $locations_list = InterventionLocation::get_locations_list ();
            $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $logins_list = User::get_logins_list (array('type' => USER_TYPE_KEYSOURCE));

            $date_next = strtotime ('+1 day', $filter['date']);
            if ($filter['date'] > get_first_hour()) $date_prev = strtotime ('-1 day', $filter['date']);

            // Build the list of dates and users we have to show
            $date = $filter['date'];
            for ($i=1; $i<=$filter['days']; $i++) {$show_days[] = $date; $date = strtotime('+1 day', $date);}
            if ($filter['user_id']) $show_users = array ($filter['user_id']);
            else $show_users = array_keys ($users_list);

            $tasks = array ();
            // If a single user is shown, show all tasks on which he is involved (organizer or attendee)
            // If all users are shown, show only tasks for the organizer
            if ($filter['user_id']) $organizers_only = false;
            else $organizers_only = true;
            foreach ($show_days as $day)
            {
                    $tasks[$day] = array ();
                    foreach ($show_users as $user_id)
                    {
                            $t = Task::get_tasks (array('user_id'=>$user_id, 'date'=>$day, 'organizer_only'=>$organizers_only, 'order_by'=>$filter['order_by']));
                            if (count($t) > 0) $tasks[$day][$user_id] = $t;
                    }
            }

            $this->assign ('tasks', $tasks);
            $this->assign ('filter', $filter);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('logins_list', $logins_list);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('date_next', $date_next);
            $this->assign ('date_prev', $date_prev);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('manage_tasks_submit', $params);

            $this->display ($tpl);
    }

    /** Save filtering criteria for manage tasks page */
    function manage_tasks_submit ()
    {
            $filter = $this->vars['filter'];
            $filter['date'] = js_strtotime ($filter['date'].' 00:00');

            $_SESSION['manage_tasks']['filter'] = $filter;
            return $this->mk_redir ('manage_tasks');
    }

    /** Displays the TBS ticket */
    function tbs_tickets ()
    {
            check_auth ();
            class_load ('Task');
            class_load ('InterventionLocation');
            $tpl = 'tbs_tickets.tpl';

            if (isset($this->vars['customer_id']))
            {
                    $_SESSION['tbs_tickets']['filter']['customer_id'] = $this->vars['customer_id'];
            }
            elseif ($this->locked_customer->id and !$this->vars['do_filter'])
            {
                    // If 'do_filter' is present in request, the locked customer is ignored
                    $_SESSION['tbs_tickets']['filter']['customer_id'] = $this->locked_customer->id;
            }
            $filter = $_SESSION['tbs_tickets']['filter'];
            $filter['status'] = TICKET_STATUS_TBS;
            $filter['load_schedule'] = true;

            // Check authorization
            if ($filter['customer_id'] > 0)
            {
                    // Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
                    // This way he can return to this page, without getting again "Permission Denied".

                    unset ($_SESSION['tbs_tickets']['filter']['customer_id']);
                    check_auth (array('customer_id' => $filter['customer_id']));
                    $_SESSION['tbs_tickets']['filter']['customer_id'] = $filter['customer_id'];
            }
            else check_auth ();

            if (!isset ($filter['unscheduled_only'])) $filter['unscheduled_only'] = 1;
            if (!isset ($filter['order_by'])) $filter['order_by'] = 'last_modified';
            if (!isset ($filter['order_dir'])) $filter['order_dir'] = 'DESC';
            if (!isset ($filter['start']) or $filter['start']<0) $filter['start'] = 0;
            if (!isset ($filter['limit'])) $filter['limit'] = 50;

            // Check for request to select all customers
            if ($filter['customer_id'] == -1) unset ($filter['customer_id']);

            // Extract the list of Krifs customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('has_krifs' => 1, 'favorites_first' => $this->current_user->id, 'show_ids' => 1);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);
            $all_customers_list = Customer::get_customers_list (array('show_ids'=>1, 'active'=>-1)); // In case there are ticket linked to disabled users

            $tickets_count = 0;
            $tickets = Ticket::get_tickets ($filter, $tickets_count);
            if ($tickets_count < $filter['start'])
            {
                    $filter['start'] = 0;
                    $_SESSION['tickets']['filter']['start'] = 0;
                    $tickets = Ticket::get_tickets ($filter, $tickets_count);
            }
            $pages = make_paging ($filter['limit'], $tickets_count);

            $this->assign ('tickets', $tickets);
            $this->assign ('tickets_count', $tickets_count);
            $this->assign ('filter', $filter);
            $this->assign ('pages', $pages);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('all_customers_list', $all_customers_list);
            $this->assign ('sort_url', $this->mk_redir ('tbs_tickets_submit'));
            $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
            $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('TICKETS_PRIORITIES_COLORS', $GLOBALS ['TICKETS_PRIORITIES_COLORS']);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('tbs_tickets_submit');

            $this->display ($tpl);
    }

    /** Saves filtering criteria for the TBS Tickets page */
    function tbs_tickets_submit ()
    {
            check_auth ();

            if ($this->vars['filter']['customer_id'] == ' ') $this->vars['filter']['customer_id'] = '';

            // Always mark we've passed through the submit function,
            // in case a different customer was selected from the locked one
            $extra_params = array ('do_filter' => 1);

            if ($this->vars['order_by'] and $this->vars['order_dir'])
            {
                    // This is a request to change the sorting order
                    $_SESSION['tbs_tickets']['filter']['order_by'] = $this->vars['order_by'];
                    $_SESSION['tbs_tickets']['filter']['order_dir'] = $this->vars['order_dir'];
            }
            else
            {
                    if ($this->vars['go'] == 'prev' or $this->vars['go'] == 'next')
                    {
                            $this->vars['filter']['start']+= $this->vars['filter']['limit'] * ($this->vars['go'] == 'prev' ? -1 : 1);
                    }
                    if (is_array($_SESSION['tbs_tickets']['filter']))
                            $_SESSION['tbs_tickets']['filter'] = array_merge($_SESSION['tbs_tickets']['filter'], $this->vars['filter']);
                    else
                            $_SESSION['tbs_tickets']['filter'] = $this->vars['filter'];
            }

            return $this->mk_redir('tbs_tickets', $extra_params);
    }




    /** Displays the page for setting the tasks order for a user and a date */
    function manage_tasks_order ()
    {
            check_auth ();
            class_load ('Task');
            class_load ('InterventionLocation');
            $tpl = 'manage_tasks_order.tpl';

            if (!$this->vars['user_id'] and !$this->vars['date']) return $this->mk_redir ('manage_tasks');

            $tasks = Task::get_tasks (array('user_id'=>$this->vars['user_id'], 'date'=>$this->vars['date'], 'order_by'=>'ord'));

            $customers_list = Customer::get_customers_list ();
            $locations_list = InterventionLocation::get_locations_list ();
            $params = $this->set_carry_fields (array('user_id', 'date'));

            $this->assign ('tasks', $tasks);
            $this->assign ('user', new User ($this->vars['user_id']));
            $this->assign ('customers_list', $customers_list);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('date', $this->vars['date']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('manage_tasks_order_submit', $params);

            $this->display ($tpl);
    }

    /** Save the tasks order */
    function manage_tasks_order_submit ()
    {
            class_load ('Task');
            check_auth ();
            $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_tasks', array('user_id'=>$this->vars['user_id'])));

            if ($this->vars['save'] and $this->vars['user_id'] and $this->vars['date'] and is_array($this->vars['tasks_list']))
            {
                    Task::set_order ($this->vars['user_id'], $this->vars['date'], $this->vars['tasks_list']);
                    Task::resort ($this->vars['user_id'], $this->vars['date']);
            }

            return $ret;
    }

    /** Serves as XML the schedules for the specified users. Used in adding and editing task schedules */
    function xml_get_schedules ()
    {
            class_load ('Task');
            $ticket = new Ticket ($this->vars['ticket_id']);
            if ($ticket->id) check_auth (array('ticket_id'=>$ticket_id));
            else check_auth ();

            $show_days = ($this->vars['show_days'] ? $this->vars['show_days'] : 4); // The number of days to return
            $locations_list = InterventionLocation::get_locations_list ();
            $customers_list = Customer::get_customers_list (array('show_ids'=>0, 'active'=>-1)); // In case there are ticket linked to disabled users

            $xml = '<?xml version="1.0" encoding="ISO-8859-1" ?><schedules>';
            $users = array ();
            $users_ids = array ();
            $user = new User ($this->vars['user_id']);
            if ($user->id) {$users[] = $user; $users_ids[] = $user->id;}

            if ($this->vars['attendees_id'] and is_array($this->vars['attendees_id']))
            {
                    foreach ($this->vars['attendees_id'] as $attendee_id)
                    {
                            $attendee = new User ($attendee_id);
                            if ($attendee->id) {$users[] = $attendee; $users_ids[] = $attendee->id;}
                    }
            }

            // If no date is specified, use the current day
            $date = ($this->vars['date'] ? js_strtotime($this->vars['date']) : time());
            if (!$date) $date = time();

            if ($this->vars['move'] == 'next') $date+= 24*3600;
            elseif ($this->vars['move'] == 'prev') $date-= 24*3600;

            // Fetch the tasks for each day and each user
            for ($i=0; $i<$show_days; $i++)
            {

                    $xml.= '<day date_str="'.date(DATE_FORMAT_SHORT_2,$date).'" date_str_long="'.date(DATE_FORMAT_LONG,$date).'">';
                    foreach ($users_ids as $idx=>$user_id)
                    {
                            $tasks = Task::get_tasks (array('user_id'=>$user_id, 'date'=>$date));
                            if (count($tasks) > 0)
                            {
                                    $xml.='<user id="'.$user_id.'" name="'.htmlspecialchars($users[$idx]->get_name()).'" short_name="'.htmlentities($users[$idx]->get_short_name()).'">';

                                    foreach ($tasks as $task)
                                    {
                                            $xml.= '<task id="'.$task->id.'" ticket_id="'.$task->ticket_id.'">';
                                            $xml.= '<ticket_subject>'.htmlspecialchars($task->ticket_subject).'</ticket_subject>';
                                            $xml.= '<customer_id>'.$task->customer_id.'</customer_id>';
                                            $xml.= '<customer>'.htmlspecialchars($customers_list[$task->customer_id]).'</customer>';
                                            $xml.= '<date_start>'.$task->date_start.'</date_start><date_end>'.$task->date_end.'</date_end>';
                                            $xml.= '<hour_start_str>'.date('H:i', $task->date_start).'</hour_start_str>';
                                            $xml.= '<hour_end_str>'.date('H:i', $task->date_end).'</hour_end_str>';
                                            $xml.= '<location>'.htmlspecialchars($locations_list[$task->location_id]).'</location>';
                                            $xml.= '<customer_location>'.htmlspecialchars($task->customer_location_name).'</customer_location>';
                                            $xml.= '</task>';
                                    }
                                    $xml.= '</user>';
                            }
                    }
                    $xml.= '</day>';
                    $date = $date + (24 * 3600);
            }

            $xml.= '</schedules>';
            header ('Content-Type: text/xml');
            header ('Content-length: '+strlen($xml));
            echo $xml;

            die;
    }

    /** Displays the page for scheduling a ticket (creating or editing a task) */
    function task_add ()
    {
            class_load ('Task');
            class_load ('InterventionLocation');
            $ticket = new Ticket ($this->vars['ticket_id']);
            check_auth (array('ticket_id' => $ticket->id));
            $tpl = 'task_add.tpl';

            $task = new Task ();
            $data = restore_form_data ('task_add', false, $data);
            $task->load_from_array ($data);
            if (!$task->user_id) $task->user_id = $ticket->assigned_id;
            if (!$task->date_start and $data['date']) $last_selected_date = $data['date']; // In case a date without hours was specified

            $customer = new Customer ($ticket->customer_id);
            $locations_list = InterventionLocation::get_locations_list ();
            $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $customers_locations_list = Location::get_locations_list (array('customer_id' => $customer->id, 'indent'=>1));

            $date_today = time ();
            $date_tomorrow = strtotime ('+1 day');
            $date_day_after = strtotime ('+2 days');

            $params = $this->set_carry_fields (array('ticket_id', 'returl'));
            $this->assign ('task', $task);
            $this->assign ('last_selected_date', $last_selected_date);
            $this->assign ('ticket', $ticket);
            $this->assign ('customer', $customer);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('customers_locations_list', $customers_locations_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('date_today', $date_today);
            $this->assign ('date_tomorrow', $date_tomorrow);
            $this->assign ('date_day_after', $date_day_after);
            $this->assign ('TASK_COMPLETED_OPTS', $GLOBALS['TASK_COMPLETED_OPTS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('task_add_submit', $params);

            $this->display ($tpl);
    }

    /** Saves the newly created task */
    function task_add_submit ()
    {
            class_load ('Task');
            $ticket = new Ticket ($this->vars['ticket_id']);
            check_auth (array('ticket_id' => $ticket->id));
            $tpl = 'task_add.tpl';

            $params = $this->set_carry_fields (array('ticket_id', 'returl'));
            $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('ticket_edit', array('id'=>$ticket->id)));

            if ($this->vars['save'] and $ticket->id)
            {
                    $data = $this->vars['task'];
                    if ($data['date'])
                    {
                            if ($data['date_start']) $data['date_start'] = js_strtotime ($data['date'].' '.$data['date_start']);
                            if ($data['date_end']) $data['date_end'] = js_strtotime ($data['date'].' '.$data['date_end']);
                    }
                    else
                    {
                            $data['date_start'] = '';
                            $data['date_end'] = '';
                    }
                    $data['ticket_id'] = $ticket->id;
                    $data['created_by_id'] = $this->current_user->id;
                    $data['comments'] = trim ($data['comments']);

                    $task = new Task ();
                    $task->load_from_array ($data);

                    if ($task->is_valid_data ())
                    {
                            $task->save_data ();
                            $task->resort ();
                            $task->load_data ();

                            $task->send_notification (TASK_NOTIF_NEW, $this->current_user->id);
                    }
                    else
                    {
                            save_form_data ($data, 'task_add');
                            $ret = $this->mk_redir('task_add', $params);
                    }
            }

            return $ret;
    }


    /** Displays the page for editing a task */
    function task_edit ()
    {
            class_load ('Task');
            class_load ('InterventionLocation');

            $task = new Task ($this->vars['id']);
            $tpl = 'task_edit.tpl';
            if (!$task->id) return $this->mk_redir ('manage_tickets');
            check_auth (array('customer_id' => $task->customer_id));

            $customer = new Customer ($task->customer_id);
            $locations_list = InterventionLocation::get_locations_list ();
            $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $customers_locations_list = Location::get_locations_list (array('customer_id' => $customer->id, 'indent'=>1));

            $date_today = time ();
            $date_tomorrow = strtotime ('+1 day');
            $date_day_after = strtotime ('+2 days');

            $params = $this->set_carry_fields (array('id', 'returl'));
            $this->assign ('task', $task);
            $this->assign ('customer', $customer);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('customers_locations_list', $customers_locations_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('date_today', $date_today);
            $this->assign ('date_tomorrow', $date_tomorrow);
            $this->assign ('date_day_after', $date_day_after);
            $this->assign ('TASK_COMPLETED_OPTS', $GLOBALS['TASK_COMPLETED_OPTS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('task_edit_submit', $params);

            $this->display ($tpl);
    }

    /** Saves the task */
    function task_edit_submit ()
    {
            check_auth ();
            class_load ('Task');
            $task = new Task ($this->vars['id']);
            check_auth (array('customer_id' => $task->customer_id));

            $params = $this->set_carry_fields (array('id', 'returl'));
            $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('ticket_edit', array('id' => $task->ticket_id)));

            if ($this->vars['save'] and $task->id)
            {
                    $task_orig = $task;
                    $data = $this->vars['task'];
                    if ($data['date'])
                    {
                            $data['date_start'] = js_strtotime ($data['date'].' '.$data['date_start']);
                            $data['date_end'] = js_strtotime ($data['date'].' '.$data['date_end']);
                    }
                    $data['comments'] = trim ($data['comments']);
                    if (!$data['attendees_ids']) $data['attendees_ids'] = array ();
                    $task->load_from_array ($data);

                    if ($task->is_valid_data ())
                    {
                            $task->save_data ();
                            $task->resort();
                            $task->load_data ();

                            if ($task->need_notif ($task_orig))
                            {
                                    // Notify all present and previous involved users
                                    $recipients_ids = array_merge($task->attendees_ids, $task_orig->attendees_ids, $task->user_id, $task_orig->user_id);
                                    $recipients_ids = array_unique ($recipients_ids);

                                    $task->send_notification (TASK_NOTIF_MODIFIED, $this->current_user->id, $task_orig, $recipients_ids);
                            }

                            //XXXXXXXXXX TEST ONLY
                            $ret = $this->mk_redir ('task_edit', $params);
                    }
                    else
                    {
                            save_form_data ($data, 'task_edit');
                            $ret = $this->mk_redir ('task_edit', $params);
                    }
            }
            elseif ($this->vars['delete'] and $task->id and $task->can_delete())
            {
                    if ($task->user_id != $this->current_user->id)
                    {
                            $task->send_notification (TASK_NOTIF_DELETED, $this->current_user->id);
                    }
                    $task->delete ();
                    $task->resort ();
            }

            return $ret;
    }


    /** Delete a scheduled task */
    function task_delete ()
    {
            class_load ('Task');
            check_auth ();
            $task = new Task ($this->vars['id']);
            $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_tasks', array ('user_id' => $task->user_id)));

            if ($task->id and $task->can_delete())
            {
                    if ($task->user_id != $this->current_user->id)
                    {
                            $task->send_notification (TASK_NOTIF_DELETED, $this->current_user->id);
                    }
                    $task->delete ();
                    $task->resort ();
            }

            return $ret;
    }


    /****************************************************************/
    /* Statuses management						*/
    /****************************************************************/

    /** Displays the page for managing the statuses */
    function manage_statuses ()
    {
            check_auth ();
            $tpl = 'manage_statuses.tpl';

            $statuses_list = Ticket::get_statuses_list ();
            $escalate_intervals = Ticket::get_statuses_escalation_intervals ();
            $ticket_class = new Ticket();

            // Parse the intervals
            foreach ($escalate_intervals as $id => $seconds)
            {
                    if ($seconds)
                    {
                            if ($seconds>(24*3600)) $escalate_intervals[$id] = ($seconds/(24 * 3600)).' days';
                            else $escalate_intervals[$id] = ($seconds/3600).' hours';
                    }
            }

            $this->assign ('statuses_list', $statuses_list);
            $this->assign ('escalate_intervals', $escalate_intervals);
            $this->assign ('ticket_class', $ticket_class);
            $this->assign ('error_msg', error_msg());

            $this->display ($tpl);
    }


    /** Displays the page for adding a new status */
    function status_add ()
    {
            check_auth ();
            $tpl = 'status_add.tpl';

            $id = $this->vars['id'];
            $statuses_list = Ticket::get_statuses_list ();

            $this->assign ('statuses_list', $statuses_list);
            $this->assign ('id', $this->vars['id']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('status_add_submit');

            $this->display ($tpl);
    }


    /** Saves the new status */
    function status_add_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_statuses');

            if ($this->vars['save'])
            {
                    if ($this->vars['status_name'])
                    {
                            $id = Ticket::add_status ($this->vars['status_name']);
                            $ret = $this->mk_redir ('status_edit', array ('id' => $id));
                    }
                    else
                    {
                            error_msg ('Please enter the name of the status');
                            $ret = $this->mk_redir ('manage_statuses');
                    }
            }

            return $ret;
    }


    /** Displays the page for editing a ticket status */
    function status_edit ()
    {
            check_auth ();
            $tpl = 'status_edit.tpl';

            $id = $this->vars['id'];
            $statuses_list = Ticket::get_statuses_list ();

            if (!$statuses_list[$id]) return $this->mk_redir ('manage_statuses');

            $escalate_intervals = Ticket::get_statuses_escalation_intervals ();

            $interval = $escalate_intervals[$id];
            if ($interval > (24 * 3600))
            {
                    $interval = $interval / (24 * 3600);
                    $unit =  (24 * 3600);
            }
            else
            {
                    $interval = $interval / 3600;
                    $unit = 3600;
            }

            $ticket_class = new Ticket ();

            $this->assign ('statuses_list', $statuses_list);
            $this->assign ('id', $id);
            $this->assign ('interval', $interval);
            $this->assign ('unit', $unit);
            $this->assign ('ticket_class', $ticket_class);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('status_edit_submit', array ('id'=>$id));

            $this->display ($tpl);
    }


    /** Saves the new name of the ticket status */
    function status_edit_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_statuses');
            $id = $this->vars['id'];

            if ($this->vars['save'] and $id)
            {
                    if ($this->vars['status_name'])
                    {
                            $interval = $this->vars['interval'] * $this->vars['unit'];
                            Ticket::rename_status ($id, $this->vars['status_name'], $interval);
                    }
                    else
                    {
                            error_msg ('Please enter the status name');
                    }
                    $ret = $this->mk_redir ('status_edit', array ('id' => $id));
            }

            return $ret;
    }


    /** Deletes a ticket status */
    function status_delete ()
    {
            check_auth ();
            $id = $this->vars['id'];
            $ret = $this->mk_redir ('manage_statuses');

            if ($id)
            {
                    if (Ticket::can_delete_status ($id))
                    {
                            Ticket::delete_status ($id);
                    }
            }

            return $ret;
    }


    /****************************************************************/
    /* Types management						*/
    /****************************************************************/

    /** Displays the page for managing the types */
    function manage_types ()
    {
            check_auth ();
            $tpl = 'manage_types.tpl';

            $types = Ticket::get_types_defs ();
            $types_list = Ticket::get_types_list ();
            $ticket_class = new Ticket();

            $this->assign ('types', $types);
            $this->assign ('types_list', $types_list);
            $this->assign ('ticket_class', $ticket_class);
            $this->assign ('error_msg', error_msg());
            $this->assign ('default_customer_ticket_type', Ticket::get_default_customer_ticket_type ());
            $this->set_form_redir ('manage_types_submit');

            $this->display ($tpl);
    }


    /** Saves the default type for customer created tickets */
    function manage_types_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_tickets');

            if ($this->vars['save'])
            {
                    Ticket::set_default_customer_ticket_type ($this->vars['default_customer_type']);
                    $ret = $this->mk_redir ('manage_types');
            }

            return $ret;
    }


    /** Displays the page for adding a new types */
    function type_add ()
    {
            check_auth ();
            $tpl = 'type_add.tpl';

            $id = $this->vars['id'];
            $types_list = Ticket::get_types_list ();

            $this->assign ('types_list', $types_list);
            $this->assign ('id', $this->vars['id']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('type_add_submit');

            $this->display ($tpl);
    }


    /** Saves the new type */
    function type_add_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_types');

            if ($this->vars['save'])
            {
                    $data = $this->vars['type'];
                    if ($data['name'])
                    {
                            $data['ignore_count'] = ($data['ignore_count'] ? 1 : 0);
                            $data['is_billable'] = ($data['is_billable'] ? 1 : 0);
                            $id = Ticket::add_type ($data['name'], $data['ignore_count'], $data['is_billable']);
                            $ret = $this->mk_redir ('type_edit', array ('id' => $id));
                    }
                    else
                    {
                            error_msg ('Please enter the name of the type');
                            $ret = $this->mk_redir ('type_add');
                    }
            }

            return $ret;
    }


    /** Displays the page for editing a ticket status */
    function type_edit ()
    {
            check_auth ();
            $tpl = 'type_edit.tpl';

            $id = $this->vars['id'];
            $type = Ticket::get_type_def ($id);

            if (!$type->id) return $this->mk_redir ('manage_types');

            $this->assign ('type', $type);
            $this->assign ('id', $id);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('type_edit_submit', array ('id'=>$id));

            $this->display ($tpl);
    }


    /** Saves the new name of the ticket type */
    function type_edit_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_types');
            $id = $this->vars['id'];

            if ($this->vars['save'] and $id)
            {
                    $data = $this->vars['type'];
                    if ($data['name'])
                    {
                            $data['ignore_count'] = ($data['ignore_count'] ? 1 : 0);
                            $data['is_billable'] = ($data['is_billable'] ? 1 : 0);
                            $id = Ticket::rename_type ($id, $data['name'], $data['ignore_count'], $data['is_billable']);
                    }
                    else
                    {
                            error_msg ('Please enter the type name');
                    }
                    $ret = $this->mk_redir ('type_edit', array ('id' => $id));
            }

            return $ret;
    }


    /** Deletes a ticket type */
    function type_delete ()
    {
            check_auth ();
            $id = $this->vars['id'];
            $ret = $this->mk_redir ('manage_types');

            if ($id)
            {
                    if (Ticket::can_delete_type ($id))
                    {
                            Ticket::delete_type ($id);
                    }
            }

            return $ret;
    }


    /****************************************************************/
    /* Escalation recipients management				*/
    /****************************************************************/

    /** Displays the page for editing the list of escalation recipients */
    function manage_escalation_recipients ()
    {
            check_auth ();
            $tpl = 'manage_escalation_recipients.tpl';

            $escalation_recips_list = Ticket::get_escalation_recipients_list ();
            $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE+USER_TYPE_KEYSOURCE_GROUP));

            $this->assign ('escalation_recips_list', $escalation_recips_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('manage_escalation_recipients_submit');

            $this->display ($tpl);
    }


    /** Saves the list of escalation recipients */
    function manage_escalation_recipients_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_tickets');

            if ($this->vars['save'] and is_array ($this->vars['recipients']))
            {
                    Ticket::set_escalation_recipients ($this->vars['recipients']);
                    $ret = $this->mk_redir ('manage_escalation_recipients');
            }

            return $ret;
    }


    /****************************************************************/
    /* Intervention reports						*/
    /****************************************************************/


    /** Displays the page for managing intervention reports */
    function manage_interventions ()
    {
            class_load ('InterventionReport');
            check_auth ();
            $tpl = 'manage_interventions.tpl';

            if (isset($this->vars['customer_id']))
            {
                    $_SESSION['manage_interventions']['customer_id'] = $this->vars['customer_id'];
            }
            elseif ($this->locked_customer->id and !$this->vars['do_filter'])
            {
                    // If 'do_filter' is present in request or if we are in advanced search,
                    // the locked customer is ignored
                    $_SESSION['manage_interventions']['customer_id'] = $this->locked_customer->id;
            }

            $filter = $_SESSION['manage_interventions'];
            if($filter['customer_id']) $filter['customer_ids'] = $filter['customer_id'];

            if (!$filter['start']) $filter['start'] = 0;
            if (!$filter['limit']) $filter['limit'] = 50;

            if(!isset($filter['manager'])) $manager = 0; else $manager=$filter['manager'];

            $tot_interventions = 0;
            $interventions = InterventionReport::get_interventions ($manager, $filter, $tot_interventions);
            $pages = make_paging ($filter['limit'], $tot_interventions);
            if ($filter['start'] > $tot_interventions)
            {
                    $filter['start'] = 0;
                    $interventions = InterventionReport::get_interventions ($manager, $filter, $tot_interventions);
            }

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1, 'account_manager'=>$filter['manager']);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);
            $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));

            // For each intervention report, load also the associated tickets
            for ($i=0; $i<count($interventions); $i++) $interventions[$i]->load_tickets ();

            // Get the statuses of the intervention reports
            $totals = InterventionReport::get_totals ();

            $params = $this->set_carry_fields (array('do_filter'));

            // Mark the potential customer for locking
            if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

            $this->assign ('interventions', $interventions);
            $this->assign ('filter', $filter);
            $this->assign ('totals', $totals);
            $this->assign ('do_filter', $this->vars['do_filter']);
            $this->assign ('pages', $pages);
            $this->assign ('tot_interventions', $tot_interventions);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
            $this->assign ('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('manage_interventions_submit', $params);

            $this->display ($tpl);
    }

    /** Saves filtering criteria for the intervention reports management page */
    function manage_interventions_submit ()
    {
            check_auth ();
            class_load ('InterventionReport');
            $params = array ('do_filter'=>1);
            $ret = $this->mk_redir ('manage_interventions', $params);
            $filter = $this->vars['filter'];

            if ($this->vars['go'] == 'prev') $filter['start'] = $filter['start'] - $filter['limit'];
            elseif ($this->vars['go'] == 'next') $filter['start'] = $filter['start'] + $filter['limit'];

            if(isset($this->vars['bulk_approve']) and $this->vars['bulk_approve'])
            {
                $sel_ir_approve = $this->vars['appr_sel'];
                foreach($sel_ir_approve as $irid)
                {
                    $ir = new InterventionReport($irid);
                    if($ir->id)
                    {
                        $ir->approve_intervention_report (get_uid());
                    }
                }
            }

            $_SESSION['manage_interventions'] = $filter;

            return $ret;
    }

    /** Displays the quick search page for intervention reports */
    function search_intervention ()
    {
            check_auth ();
            class_load ('InterventionReport');
            $tpl = 'search_intervention.tpl';
            $show_limit = 50;

            if ($this->vars['search_text'] and is_numeric($this->vars['search_text']))
            {
                    $intervention = new InterventionReport ($this->vars['search_text']);
                    if ($intervention->id) return $this->mk_redir ('intervention_edit', array('id' => $intervention->id));
                    else error_msg ('There is no intervention report with the specified ID');
            }
            elseif ($this->vars['search_text'])
            {
                    $tot_interventions = 0;
                    $filter = array ('search_text'=>$this->vars['search_text'], 'start'=>0, 'limit'=>$show_limit);
                    $interventions = InterventionReport::get_interventions ($filter, $tot_interventions);
                    if (count($interventions)==1) return $this->mk_redir ('intervention_edit', array('id' => $interventions[0]->id));
                    elseif (count($interventions) > 0) $customers_list = Customer::get_customers_list ();
            }

            $this->assign ('search_text', $this->vars['search_text']);
            $this->assign ('interventions', $interventions);
            $this->assign ('tot_interventions', $tot_interventions);
            $this->assign ('show_limit', $show_limit);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('search_intervention');

            $this->display ($tpl);
    }


    /** Displays the page for creating new intervention reports */
    function intervention_add ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ();
            check_auth();
            $tpl = 'intervention_add.tpl';

            if (!empty_error_msg())
            {
                    $data = array();
                    $intervention->load_from_array (restore_form_data ('intervention', false, $data));
            }
            if ($this->vars['customer_id'] and !$intervention->customer_id) $intervention->customer_id = $this->vars['customer_id'];

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            $params = $this->set_carry_fields (array('customer_id', 'do_filter'));

            $this->assign ('intervention', $intervention);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('intervention_add_submit', $params);

            $this->display ($tpl);
    }


    /** Saves the newly created intervention report */
    function intervention_add_submit ()
    {
            class_load ('InterventionReport');
            check_auth();
            $params = $this->set_carry_fields (array('do_filter'));
            $ret = $this->mk_redir ('manage_interventions', $params);
            $params = $this->set_carry_fields (array('customer_id', 'do_filter'));

            if ($this->vars['save'])
            {
                    $data = $this->vars['intervention'];
                    $intervention = new InterventionReport ();
                    $intervention->load_from_array ($data);

                    if ($intervention->is_valid_data())
                    {
                            $intervention->save_data ();
                            unset ($params['customer_id']);
                            $params['id'] = $intervention->id;
                            $ret = $this->mk_redir ('intervention_edit', $params);
                    }
                    else
                    {
                            save_form_data ($data, 'intervention');
                            $ret = $this->mk_redir ('intervention_add', $params);
                    }
            }
            return $ret;
    }


    /** Displays the page for creating a new intervention report starting
    * from a ticket */
    function ticket_add_intervention ()
    {
            class_load ('InterventionReport');
            $ticket = new Ticket ($this->vars['ticket_id']);
            if (!$ticket->id) return $this->mk_redir ('manage_tickets');
            check_auth (array('customer_id' => $ticket->customer_id));
            $tpl = 'ticket_add_intervention.tpl';

            $customer = new Customer ($ticket->customer_id);

            $intervention = new InterventionReport ();
            if (!empty_error_msg())
            {
                    $data = array();
                    $intervention->load_from_array (restore_form_data ('intervention', false, $data));
            }
            if (!$intervention->subject) $intervention->subject = $ticket->subject;

            $action_types = ActionType::get_list ();
            $params = $this->set_carry_fields (array('ticket_id', 'do_filter'));

            $this->assign ('intervention', $intervention);
            $this->assign ('customer', $customer);
            $this->assign ('ticket', $ticket);
            $this->assign ('action_types', $action_types);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_add_intervention_submit', $params);

            $this->display ($tpl);
    }

    /** Creates a new intervention report from a ticket */
    function ticket_add_intervention_submit ()
    {
            class_load ('InterventionReport');
            $ticket = new Ticket ($this->vars['ticket_id']);
            if (!$ticket->id) return $this->mk_redir ('manage_tickets');
            check_auth (array('customer_id' => $ticket->customer_id));

            $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));
            $params = $this->set_carry_fields (array('ticket_id', 'do_filter'));

            if ($this->vars['save'] and $ticket->id)
            {
                    $data = $this->vars['intervention'];
                    $intervention = new InterventionReport ();
                    $intervention->load_from_array ($data);

                    $intervention->customer_id = $ticket->customer_id;
                    $intervention->user_id = get_uid ();

                    if ($intervention->is_valid_data ())
                    {
                            $intervention->save_data ();
                            if (is_array ($this->vars['include_details']))
                            {
                                    $intervention->set_details ($this->vars['include_details']);
                            }
                            unset ($params['ticket_id']);
                            $params['id'] = $intervention->id;
                            $ret = $this->mk_redir ('intervention_edit', $params);
                    }
                    else
                    {
                            save_form_data ($data, 'intervention');
                            $ret = $this->mk_redir ('ticket_add_intervention', $params);
                    }
            }

            return $ret;
    }

    /**
     * creates a black intervention
     * After the ticket is created, the operator can generate a blank intervention
     * in this intervention will be included the first detail of the ticket
     * After the return form the field operation he'll complete the detail with the field information
     * and then update automatically the intervention report.
     * @return unknown_type
     */
    function ticket_add_blank_intervention()
    {
            class_load ('InterventionReport');
            class_load ('Customer');
            $ticket = new Ticket ($this->vars['ticket_id']);
            if (!$ticket->id) return $this->mk_redir ('manage_tickets');
            check_auth (array('customer_id' => $ticket->customer_id));
            $tpl = 'ticket_add_blank_intervention.tpl';

            $customer = new Customer ($ticket->customer_id);

            $intervention = new InterventionReport ();
            if (!empty_error_msg())
            {
                    $data = array();
                    $intervention->load_from_array (restore_form_data ('intervention', false, $data));
            }
            if (!$intervention->subject) $intervention->subject = $ticket->subject;

            $action_types = ActionType::get_list ();
            $params = $this->set_carry_fields (array('ticket_id', 'do_filter'));

            $this->assign ('intervention', $intervention);
            $this->assign ('customer', $customer);
            $this->assign ('ticket', $ticket);
            $this->assign ('action_types', $action_types);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_add_blank_intervention_submit', $params);

            $this->display ($tpl);

    }
    function ticket_add_blank_intervention_submit()
    {
            class_load("InterventionReport");
            $ticket = new Ticket($this->vars['ticket_id']);
            if(!$ticket->id) return $this->mk_redir('manage_tickets');
            check_auth(array('customer_id'=>$ticket->customer_id));

            $ret = $this->mk_redir('ticket_edit', array('id'=>$ticket->id));
            $params = $this->set_carry_fields(array('ticket_id', 'do_filter'));

            if($this->vars['save'] and $ticket->id)
            {
                    $data = $this->vars['intervention'];
                    $intervention = new InterventionReport();
                    $intervention->load_from_array($data);

                    $intervention->customer_id = $ticket->customer_id;
                    $intervention->user_id = get_uid();

                    if($intervention->is_valid_data())
                    {
                            $intervention->save_data();
                            $intervention->set_details(array($ticket->details[0]->id));
                            //debug($intervention->details);
                            unset($params['ticket_id']);
                            $params['id'] = $intervention->id;
                            $ret = $this->mk_redir('intervention_edit', $params);
                    }
                    else
                    {
                            save_form_data($data,'intervention');
                            $ret = $this->mk_redir('ticket_add_intervetion', $params);
                    }
                    return $ret;
            }

    }


    /** Displays the page for editing an existing intervention report */
    function intervention_edit ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            if (!$intervention->id) return $this->mk_redir ('manage_interventions');
            check_auth (array ('customer_id' => $intervention->customer_id));
            $tpl = 'intervention_edit.tpl';

            $customer = new Customer ($intervention->customer_id);
            $intervention->load_tickets ();

            $action_types_list = ActionType::get_list ();
            //$action_types_erp_ids_list = ActionType::get_erp_codes_list ();
            $locations_list = InterventionLocation::get_locations_list ();

            // If the intervention was approved, load the user who has done the approval
            if ($intervention->approved_by_id)
            {
                    $approved_by = new User ($intervention->approved_by_id);
                    $this->assign ('approved_by', $approved_by);
            }

            // If the intervention has not been closed, run a simulation of the closure
            if ($intervention->status == INTERVENTION_STAT_OPEN) $intervention->close_intervention_report (get_uid(), true);

            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'ticket_id', 'returl'));

            $this->assign ('intervention', $intervention);
            $this->assign ('customer', $customer);
            $this->assign ('action_types_list', $action_types_list);
            //$this->assign ('action_types_erp_ids_list', $action_types_erp_ids_list);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
            $this->assign ('error_msg', error_msg ());
            if (!$intervention->has_complete_info(true))
            {
                    $this->assign ('warning_msg', error_msg ());
            }
            $this->set_form_redir ('intervention_edit_submit', $params);

            $this->display ($tpl);
    }


    /** Saves an intervention report */
    function intervention_edit_submit ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            check_auth (array('customer_id' => $intervention->customer_id));

            $params = $this->set_carry_fields (array('do_filter'));
            if ($this->vars['returl']) $ret = $this->vars['returl'];
            elseif ($this->vars['ret'] == 'ticket') $ret = $this->mk_redir ('ticket_edit', array ('id' => $this->vars['ticket_id']));
            else $ret = $this->mk_redir ('manage_interventions', $params);
            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'ticket_id', 'returl'));

            if($this->vars['make_non_billable'] and $intervention->id)
            {
                //debug($intervention->lines);
                foreach($intervention->lines as $line)
                {
                    if(!$line->action_type->special_type){
                        $line->tbb_amount = 0;
                        $line->save_data();
                    }
                }
                $ret = $this->mk_redir ('intervention_edit', $params);
            }

            if($this->vars['adjust_tbb'] and $intervention->id)
            {
                $work_time = 0;
                foreach($intervention->lines as $line)
                {
                    $work_time += $line->work_time;
                }

                $hours = intval($work_time/60);
                $minutes = $work_time%60;
                //first if the see if we are under the 15 minutes rule
                if($hours ==0 and $minutes<=15)
                {
                    foreach($intervention->lines as $line)
                    {
                        $line->tbb_amount = 0;
                        $line->save_data();
                    }
                }
                //otherwise let's round up till we get a correct number of hours
                else
                {
                    $target_tbb = ($minutes > 0) ? $hours+1 : $hours;
                    $tbb = 0;
                    foreach($intervention->lines as $line)
                    {
                        if(!$line->action_type->special_type){
                            $h = intval($line->work_time/60);
                            $m = $line->work_time%60;
                            if($tbb <= $target_tbb)
                            {
                                if($m>0) $h++;

                                $line->tbb_amount = $h*60;

                                $s_trg = $tbb;
                                $tbb += $h;

                                if($tbb > $target_tbb)
                                {
                                    $ltb  = $target_tbb - $s_trg;
                                    $line->tbb_amount = $ltb*60;
                                }
                                $line->save_data();

                            }
                            else
                            {
                                if($taget_tbb > $tbb)
                                {
                                    $ltb  =$target_tbb - $h;
                                    $line->tbb_amount = $ltb*60;
                                    $line->save_data();
                                }
                                else
                                {
                                    $line->tbb_amount = 0;
                                    $line->save_data();
                                }

                            }
                        }
                        else{
                            if($line->tbb_amount != $line->bill_amount){
                                $line->tbb_amount = $line->bill_amount;
                                $line->save_data();
                            }
                        }
                    }
                }
                $ret = $this->mk_redir ('intervention_edit', $params);

            }

            if ($this->vars['save'] and $intervention->id)
            {
                    // This is a request to save modifications to an intervention report
                    $data = $this->vars['intervention'];
                    if (isset($data['created'])) $data['created'] = js_strtotime ($data['created']);
                    $intervention->load_from_array ($data);

                    if ($intervention->is_valid_data ())
                    {
                            $intervention->save_data ();
                    }
                    else
                    {
                            save_form_data ($data, 'intervention');
                    }
                    $ret = $this->mk_redir ('intervention_edit', $params);
            }
            elseif ($this->vars['close'] and $intervention->id)
            {
                    // This is a request to change the intervention's status to "Closed"
                    // Check first if the intervention can be closed
                    if ($intervention->has_complete_info ())
                    {
                            $intervention->close_intervention_report (get_uid());
                    }
                    else
                    {
                            error_msg ('Sorry, this intervention can\'t be closed yet.');
                    }
                    $ret = $this->mk_redir ('intervention_edit', $params);
            }
            elseif ($this->vars['reopen'] and $intervention->id)
            {
                    $intervention->reopen_intervention_report (get_uid());
                    $ret = $this->mk_redir ('intervention_edit', $params);
            }
            elseif ($this->vars['approve'] and $intervention->id)
            {
                    $intervention->approve_intervention_report (get_uid());
                    $ret = $this->mk_redir ('intervention_edit', $params);
            }
            elseif ($this->vars['cancel_approval'] and $intervention->id)
            {
                    $intervention->cancel_approval (get_uid());
                    $ret = $this->mk_redir ('intervention_edit', $params);
            }
            elseif ($this->vars['cancel_centralization'])
            {
                    $ret = $this->mk_redir ('intervention_cancel_centralized', $params);
            }
            elseif ($this->vars['print'] and $intervention->id)
            {
                    $ret = $this->mk_redir ('intervention_print', $params);
            }
            elseif ($this->vars['print_blank'] and $intervention->id)
            {
                    $ret = $this->mk_redir('intervention_print_blank', $params);
            }

            return $ret;
    }


    /** Displays the page asking the user to confirm if he really wants to cancel the "Centralized" status */
    function intervention_cancel_centralized ()
    {
            class_load ('InterventionReport');
            $tpl = 'kriintervention_cancel_centralized.tpl';
            $intervention = new InterventionReport ($this->vars['id']);
            if (!$intervention->id) return $this->mk_redir ('manage_interventions');
            check_auth (array('customer_id' => $intervention->customer_id));

            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'ticket_id', 'returl'));
            $this->assign ('intervention', $intervention);
            $this->set_form_redir ('intervention_cancel_centralized_submit', $params);
            $this->display ($tpl);
    }


    /** Displays the page asking the user to confirm if he really wants to cancel the "Centralized" status */
    function intervention_cancel_centralized_submit ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            if (!$intervention->id) return $this->mk_redir ('manage_interventions');
            check_auth (array('customer_id' => $intervention->customer_id));

            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'ticket_id', 'returl'));
            $ret = $this->mk_redir ('intervention_edit', $params);

            if ($this->vars['save'] and $intervention->id)
            {
                    $intervention->cancel_centralization ();
            }

            return $ret;
    }


    /** Displays the function allowing to edit the to be billed amounts for an invoice */
    function intervention_lines_edit ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            if (!$intervention->id) return $this->mk_redir ('manage_interventions');
            check_auth (array('customer_id' => $intervention->customer_id));
            $tpl = 'intervention_lines_edit.tpl';


            $customer = new Customer ($intervention->customer_id);
            $intervention->load_tickets ();

            $action_types_list = ActionType::get_list ();
            //$action_types_erp_ids_list = ActionType::get_erp_codes_list ();
            $locations_list = InterventionLocation::get_locations_list ();

            $params = $this->set_carry_fields (array('id', 'returl'));

            // Get lists of action types for this customer, separately for helpdesk and non-helpdesk
            $filter_customer_actions = array (
                    'group_by' => 'category',
                    'contract_type_cust' => $customer->contract_type,
                    'contract_sub_type' => $customer->contract_sub_type,
                    'active' => 1
            );
            $filter_customer_actions['helpdesk'] = true;
            $action_types_helpdesk = ActionType::get_action_types ($filter_customer_actions);
            $filter_customer_actions['helpdesk'] = false;
            $action_types_nonhelpdesk = ActionType::get_action_types ($filter_customer_actions);
            $actypes_categories_list = ActionTypeCategory::get_categories_list ();

            $this->assign ('intervention', $intervention);
            $this->assign ('customer', $customer);
            $this->assign ('action_types_list', $action_types_list);
            $this->assign ('action_types_helpdesk', $action_types_helpdesk);
            $this->assign ('action_types_nonhelpdesk', $action_types_nonhelpdesk);
            $this->assign ('actypes_categories_list', $actypes_categories_list);
            //xxxxxxxxxxxxxxx
            //$this->assign ('action_types_erp_ids_list', $action_types_erp_ids_list);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('intervention_lines_edit_submit', $params);

            $this->display ($tpl);
    }


    /** Saves the changes made to the invoing lines for an intervention report */
    function intervention_lines_edit_submit ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            if (!$intervention->id) return $this->mk_redir ('manage_interventions');
            check_auth (array('customer_id' => $intervention->customer_id));

            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else $ret = $this->mk_redir ('intervention_edit', array ('id' => $intervention->id));
            $params = $this->set_carry_fields (array('id', 'returl'));

            if ($this->vars['save'] and $intervention->id)
            {
                    $lines = $this->vars['lines'];
                    // Will prepare each line to be saved, but will not actually save
                    // unless all data is OK
                    $all_valid = true;
                    $all_lines = array ();
                    // IDs of the lines for which the linked ticket details will need updating,
                    // in case the billable flag or action type is changed
                    $need_update_tickets = array ();
                    foreach ($lines as $id => $data)
                    {
                            $line = new InterventionReportDetail ($id);
                            if ($line->action_type->price_type == PRICE_TYPE_HOURLY)
                            {
                                    $data['tbb_amount'] = js_durationtomins ($data['tbb_amount']);
                            }

                            if (count($line->ticket_detail_ids)>0 and ($line->action_type_id!=$data['action_type_id'] or $line->billable!=$data['billable']))
                            {
                                    $need_update_tickets[] = $line->id;
                            }
                            $line->load_from_array ($data);
                            if (!$line->is_valid_data ()) $all_valid = false;
                            $all_lines[] = $line;
                    }

                    if ($all_valid)
                    {
                            for ($i=0; $i<count($all_lines); $i++)
                            {
                                    $all_lines[$i]->save_data ();
                                    if (in_array($all_lines[$i]->id, $need_update_tickets))
                                    {
                                            $all_lines[$i]->update_ticket_details ();
                                    }
                            }

                            // Make sure to update the travel lines
                            $intervention->recheck_travel_lines ();
                    }
                    $ret = $this->mk_redir ('intervention_lines_edit', $params);
            }

            return $ret;
    }

    function intervention_print_blank()
    {
            class_load('InterventionReport');
            $intervention = new InterventionReport($this->vars[id]);
            check_auth(array('customer_id' => $intervention->customer_id));

            $params = $this->set_carry_fields(array('id', 'ret', 'ticket_id'));
            $ret = $this->mk_redir('intervention_print_blank', $params);

            $xml_template = "intervention_blank.xml";
            $xsl_template = "intervention_blank.xslt";

            //get the customer
            $customer = new Customer($intervention->customer_id);
            $this->assign('customer', $customer);
            $this->assign('intervention', $intervention);
            $this->assign('user', new User($intervention->user_id));
            $this->assign('ACCOUNT_MANAGERS_LOGOS', $GLOBALS['ACCOUNT_MANAGERS_LOGOS']);
            $this->assign('ACCOUNT_MANAGERS_INFO', $GLOBALS['ACCOUNT_MANAGERS_INFO']);

            $xml = $this->fetch($xml_template);
            $pdf_name = 'IR_blank_'.$intervention->id;
            make_pdf_xml($xml, $xsl_template, $pdf_name);
            die;
    }

    /** Displays the page for printing or e-mailing an intervention report */
    function intervention_print ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            check_auth (array('customer_id' => $intervention->customer_id));
            $tpl = 'intervention_print.tpl';

            $filter = $_SESSION['intervention_print']['filter'];
            if (!$filter['show']) $filter['show'] = 'detailed';
            if (!$filter['view']) $filter['view'] = 'keysource';

            $intervention->load_tickets ();
            $customer = new Customer ($intervention->customer_id);
            $action_types = ActionType::get_list ();
            $locations_list = InterventionLocation::get_locations_list ();

            if ($filter['show'] == 'summary')
            {
                    // Calculate the times for each ticket
                    for ($i=0; $i<count($intervention->details); $i++)
                    {
                            $detail = &$intervention->details[$i];
                            $ticket = &$intervention->tickets[$detail->ticket_id];
                            $ticket->work_time += $detail->work_time;
                            $ticket->bill_time += $detail->bill_time;

                            if ($detail->time_in)
                            {
                                    if (!isset($ticket->time_in) or (isset($ticket->time_in) and $ticket->time_in > $detail->time_in))
                                            $ticket->time_in = $detail->time_in;
                            }
                            if ($detail->time_out)
                            {
                                    if (!isset($ticket->time_out) or (isset($ticket->time_out) and $ticket->time_out < $detail->time_out))
                                            $ticket->time_out = $detail->time_out;
                            }
                    }
            }

            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'ticket_id'));

            $this->assign ('intervention', $intervention);
            $this->assign ('filter', $filter);
            $this->assign ('customer', $customer);
            $this->assign ('action_types', $action_types);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);

            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('intervention_print_submit', $params);

            $this->display ($tpl);
    }


    function intervention_print_submit ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            check_auth (array('customer_id' => $intervention->customer_id));

            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'ticket_id'));
            $ret = $this->mk_redir ('intervention_print', $params);
            $filter = $this->vars['filter'];

            $_SESSION['intervention_print']['filter'] = $filter;

            if ($this->vars['do_pdf'] or $this->vars['do_email'])
            {
                    $all_ok = true;
                    if ($this->vars['do_email'])
                    {
                            // Email was requested, make sure everything is ok
                            if (!$filter['email_recipients']) {$all_ok=false; error_msg ('Please specify the e-mail recipient(s).');}
                            if (!$filter['email_subject']) {$all_ok=false; error_msg ('Please specify the e-mail subject.');}
                            if (!$filter['email_body']) {$all_ok=false; error_msg ('Please specify the e-mail text.');}
                    }

                    if ($all_ok)
                    {
                            // Generate the PDF document
                            $xml_tpl = 'intervention.xml';
                            $xsl_tpl = 'intervention.xslt';

                            $customer = new Customer ($intervention->customer_id);
                            $action_types = ActionType::get_list ();
                            $locations_list = InterventionLocation::get_locations_list ();
                            $intervention->load_tickets ();

                            // Calculate the times for each ticket
                            for ($i=0; $i<count($intervention->details); $i++)
                            {
                                    $detail = &$intervention->details[$i];
                                    $ticket = &$intervention->tickets[$detail->ticket_id];
                                    $ticket->work_time += $detail->work_time;
                                    $ticket->bill_time += $detail->bill_time;

                                    if ($detail->time_in)
                                    {
                                            if (!isset($ticket->time_in) or (isset($ticket->time_in) and $ticket->time_in > $detail->time_in))
                                                    $ticket->time_in = $detail->time_in;
                                    }
                                    if ($detail->time_out)
                                    {
                                            if (!isset($ticket->time_out) or (isset($ticket->time_out) and $ticket->time_out < $detail->time_out))
                                                    $ticket->time_out = $detail->time_out;
                                    }
                            }

                            $this->assign ('intervention', $intervention);
                            $this->assign ('filter', $filter);
                            $this->assign ('customer', $customer);
                            $this->assign ('action_types', $action_types);
                            $this->assign ('locations_list', $locations_list);
                            $this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
                            $this->assign('ACCOUNT_MANAGERS_LOGOS', $GLOBALS['ACCOUNT_MANAGERS_LOGOS']);
                            $this->assign('ACCOUNT_MANAGERS_INFO', $GLOBALS['ACCOUNT_MANAGERS_INFO']);

                            if (0) {header ('Content-type: text/xml');$this->display_template_only ($xml_tpl); die;}

                            $xml = $this->fetch ($xml_tpl);

                            //$pdf_name = 'report.pdf';
                            $pdf_name = 'intervention_report_'.$intervention->id;
                            if ($this->vars['do_pdf'])
                            {
                                    // Send the PDF to the browser
                                    make_pdf_xml ($xml, $xsl_tpl, $pdf_name);
                                    die;
                            }
                            else
                            {
                                    // Generate the e-mail with the PDF attachment
                                    $pdf_file_path = make_pdf_xml ($xml, $xsl_tpl, "", true);

                                    $boundary = '=_NextPart_001_0011_1234ABCD.4321FDAC';
                                    $headers = 'From: '.SENDER_NAME.' <'.SENDER_EMAIL.'>'."\n";
                                    $headers.= 'Date: '.date("D, j M Y G:i:s O")."\n";
                                    $headers.= "MIME-Version: 1.0\n";
                                    $headers.= "Content-Type: multipart/mixed; boundary=\"$boundary\"\n";
                                    $headers.= "Content-transfer-encoding: 7bit\n";

                                    $message = "\nThis is a multi-part message in MIME format.\n\n";
                                    $message.= "--$boundary\n";
                                    $message.= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
                                    $message.= "Content-Disposition: inline\n";
                                    $message.= "Content-Transfer-Encoding: 7bit\n\n";
                                    $message.= $filter['email_body']."\n\n";

                                    $message.= "--$boundary\n";
                                    $message.= "Content-Type: application/octet-stream; name=\"$pdf_name.pdf\"\n";
                                    $message.= "Content-Disposition: attachment; name=\"$pdf_name.pdf\"\n";
                                    $message.= "Content-Transfer-Encoding: base64\n\n";
                                    $fp = fopen ($pdf_file_path, 'rb');
                                    $message.= chunk_split (base64_encode( fread($fp, filesize($pdf_file_path))));
                                    $message.= "--$boundary--\n";

                                    $sent = mail ($filter['email_recipients'], $filter['email_subject'], $message, $headers);

                                    @unlink ($pdf_file_path);

                                    if ($sent)
                                    {
                                            $params['recipients'] = $filter['email_recipients'];
                                            $ret = $this->mk_redir ('intervention_print_sent', $params);
                                    }
                                    else
                                    {
                                            error_msg ('Sorry, there was an error sending the message. Please try again.');
                                            $ret = $this->mk_redir ('intervention_print', $params);
                                    }
                            }
                    }
                    else
                    {
                            $ret = $this->mk_redir ('intervention_print', $params);
                    }
            }
            elseif ($this->vars['cancel'])
            {
                    // Clear the e-mail settings from the session
                    unset ($filter['email_subject']);
                    unset ($filter['email_recipients']);
                    unset ($filter['email_body']);
                    unset ($filter['show_email']);
                    $_SESSION['intervention_print']['filter'] = $filter;

                    $ret = $this->mk_redir ('intervention_edit', $params);
            }

            return $ret;
    }


    /** Displays the page confirming the sending of the intervention report by e-mail */
    function intervention_print_sent ()
    {
            check_auth ();
            $tpl = 'intervention_print_sent.tpl';

            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'ticket_id'));

            $this->assign ('recipients', $this->vars['recipients']);
            $this->assign ('return_url', $this->mk_redir ('intervention_print', $params));
            $this->assign ('error_msg', error_msg ());

            $this->display ($tpl);
    }


    /** Deletes an intervention report */
    function intervention_delete ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            check_auth (array('customer_id' => $intervention->customer_id));

            $params = $this->set_carry_fields (array('do_filter', 'ret', 'ticket_id'));
            $ret = $this->mk_redir ('manage_interventions', $params);

            if ($intervention->can_delete ()) $intervention->delete ();

            return $ret;
    }


    /** Adds a ticket detail to a new intervention report */
    function intervention_add_detail ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['intervention_id']);
            check_auth (array ('customer_id' => $intervention->customer_id));
            $tpl = 'intervention_add_detail.tpl';

            // Check if the intervention report can be edited
            if (!$intervention->can_modify ())
            {
                    error_msg ('This intervention report can\'t be modified anymore.');
                    return $this->mk_redir ('intervention_edit', array ('id' => $intervention->id));
            }

            $customer = new Customer ($intervention->customer_id);
            // Fetch the list of open billable tickets
            $tickets = Ticket::get_tickets (array(
                    'customer_ids' => $customer->id,
                    'order_by' => 'id',
                    'order_dir' => 'DESC',
                    'status' => -1,
                    'billable_only' => 1
            ), $no_count);

            // Remove from list the tickets which don't have any details that can be added
            for ($i=count($tickets)-1; $i>=0; $i--)
            {
                    $has_details = false;
                    for ($j=0; $j<count($tickets[$i]->details) and !$has_details; $j++)
                    {
                            $has_details = $tickets[$i]->details[$j]->is_valid_for_intervention ();
                            //$detail = &$tickets[$i]->details[$j];
                            //$has_details = ($detail->user_id and !$detail->user->is_customer_user() and ($detail->comments or $detail->work_time) and !$detail->intervention_report_id);
                    }
                    if (!$has_details) unset ($tickets[$i]);
            }

            $action_types = ActionType::get_list ();
            $params = $this->set_carry_fields (array('intervention_id', 'do_filter', 'ret', 'ticket_id', 'returl'));

            $this->assign ('intervention', $intervention);
            $this->assign ('tickets', $tickets);
            $this->assign ('customer', $customer);
            $this->assign ('action_types', $action_types);
            $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('intervention_add_detail_submit', $params);

            $this->display ($tpl);
    }


    /** Adds one or more ticket details to an intervention report */
    function intervention_add_detail_submit ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['intervention_id']);
            check_auth (array ('customer_id' => $intervention->customer_id));

            $params = $this->set_carry_fields (array('do_filter', 'ret', 'ticket_id', 'returl'));
            $params['id'] = $intervention->id;
            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else $ret = $this->mk_redir ('intervention_edit', $params);

            if ($this->vars['save'] and $intervention->id and is_array($this->vars['detail_ids']))
            {
                    $intervention->set_details ($this->vars['detail_ids'], true);
            }

            return $ret;
    }


    /** Removes a detail from an intervention report */
    function intervention_remove_detail ()
    {
            class_load ('InterventionReport');
            $intervention = new InterventionReport ($this->vars['id']);
            check_auth (array ('customer_id' => $intervention->customer_id));

            $params = $this->set_carry_fields (array('id', 'do_filter', 'ret', 'ticket_id', 'returl'));
            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else $ret = $this->mk_redir ('intervention_edit', $params);

            if ($intervention->can_remove_detail($this->vars['detail_id']))
            {
                    $intervention->remove_detail ($this->vars['detail_id']);
            }


            return $ret;
    }


    /** Sets the intervention report for a ticket detail */
    function ticket_detail_intervention ()
    {
            class_load ('InterventionReport');
            $ticket_detail = new TicketDetail ($this->vars['detail_id']);
            $ticket = new Ticket ($ticket_detail->ticket_id);
            check_auth (array('customer_id' => $ticket->customer_id));
            $customer = new Customer ($ticket->customer_id);
            $tpl = 'ticket_detail_intervention.tpl';

            $interventions = InterventionReport::get_interventions (array('customer_id'=>$customer->id, 'status' => INTERVENTION_STAT_OPEN), $no_count);

            if ($ticket_detail->intervention_report_id)
            {
                    $this->assign ('current_intervention', new InterventionReport ($ticket_detail->intervention_report_id));
            }

            $params = $this->set_carry_fields (array('detail_id', 'ret', 'returl'));

            $this->assign ('interventions', $interventions);
            $this->assign ('ticket_detail', $ticket_detail);
            $this->assign ('ticket', $ticket);
            $this->assign ('customer', $customer);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('ticket_detail_intervention_submit', $params);

            $this->display ($tpl);
    }


    /** Saves the assignment of intervention reports to a ticket detail */
    function ticket_detail_intervention_submit ()
    {
            class_load ('InterventionReport');
            $ticket_detail = new TicketDetail ($this->vars['detail_id']);
            $ticket = new Ticket ($ticket_detail->ticket_id);
            check_auth (array('customer_id' => $ticket->customer_id));

            if ($this->vars['returl']) $ret = $this->vars['returl'];
            else $ret = $this->mk_redir ('ticket_detail_edit', array ('id' => $ticket_detail->id));

            if ($this->vars['save'] and $this->vars['intervention_id'])
            {
                    $ticket_detail->intervention_report_id = $this->vars['intervention_id'];
                    $ticket_detail->save_data ();
            }

            return $ret;
    }


    /****************************************************************/
    /* Timesheets managemet						*/
    /****************************************************************/

    /** Displays the page for managing timesheets */
    function manage_timesheets ()
    {
            class_load ('Timesheet');
            check_auth ();
            $tpl = 'manage_timesheets.tpl';

            $filter = $_SESSION['manage_timesheets'];
            if (!isset($filter['user_id']) or !$filter['user_id']) $filter['user_id'] = get_uid ();
            if (!isset($filter['date'])) $filter['date'] = get_first_hour ();

            if (isset($this->vars['date'])) $filter['date'] = $this->vars['date'];
            if (isset($this->vars['user_id'])) $filter['user_id'] = $this->vars['user_id'];
            $_SESSION['manage_timesheets']['date'] = $filter['date'];

            // Will display the timesheets for one week
            $day = get_first_monday ($filter['date']);
            $days = array ();
            $timesheets = array();
            for ($i=0; $i<7; $i++)
            {
                    $days[] = $day;
                    $timesheets[$day] = Timesheet::get_timesheet ($filter['user_id'], $day);
                    if (!$timesheets[$day]->id) {

                    // debug ($timesheets[$day]);
                     $timesheets[$day]->load_unassigned_details ();
                    }
                    $day = strtotime ('+1 day', $day);
                    //debug($day);
            }


            $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));

            $prev_date = strtotime ('-1 week', $filter['date']);
            $next_date = strtotime ('+1 week', $filter['date']);

            $this->assign ('timesheets', $timesheets);
            $this->assign ('filter', $filter);
            $this->assign ('days', $days);
            $this->assign ('prev_date', $prev_date);
            $this->assign ('next_date', $next_date);
            $this->assign ('users_list', $users_list);
            $this->assign ('TIMESHEET_STATS', $GLOBALS['TIMESHEET_STATS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('manage_timesheets_submit');

            $this->display ($tpl);
    }


    /** Saves the filtering criteria for the timesheets management page */
    function manage_timesheets_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_timesheets');

            $filter = $this->vars['filter'];
            $filter['date'] = js_strtotime ($filter['date']);
            if ($filter['date'] <= 0) $filter['date'] = time ();
            $_SESSION['manage_timesheets'] = $filter;

            return $ret;
    }

    /**
     * Manage the timesheets for all users, an superviser view for all
     *
     */
    function manage_timesheets_extended()
    {
            class_load('Timesheet');
            check_auth();
            $tpl = "manage_timesheets_extended.tpl";

            //get the date of the last monday
            if (date("w") == 0) {
                $adjuster = 6;
            }
            else {
                $adjuster = date("w") - 1;
            }
            $lowdate = date("Y-m-d", strtotime("-" .$adjuster. " days"));

            if(!isset($_SESSION['manage_timesheet_extended']))
            {
                    $_SESSION['manage_timesheet_extended'] = array(
                            'date_from' => strtotime($lowdate), //should be the begining of the week
                            'date_to' => time()
                    );
            }

            $filter = $_SESSION['manage_timesheet_extended'];

            if(!$filter['date_from']) $filter['date_from'] = strtotime($lowdate);
            if(!$filter['date_to']) $filter['date_to'] = time();

            $users_list = User::get_users_list(array('type'=>USER_TYPE_KEYSOURCE));

            $timesheets = array();
            $day = $filter['date_from'];
            $days = array();
            foreach ($users_list as $uid=>$name)
            {
                    $uid_timesheets = array();
                    while($day <= $filter['date_to'])
                    {
                            $days[] = $day;
                            $pp = new Timesheet();
                            $pp = Timesheet::get_timesheet($uid, $day);
                            //debug($pp);
                            if(!$pp->id)
                            {
                                    $pp->load_unassigned_details();
                                    $pp->load_hours();
                                    $pp->load_user();

                                    //load the tickets too for the ticket details
                                    for($i=0;$i<count($pp->details);$i++)
                                    {
                                            if($pp->details[i]->ticket_detail_id)
                                                    $pp->details[i]->load_ticket();
                                    }
                            }
                            else {
                                    if($pp->status == TIMESHEET_STAT_OPEN) $pp->save_data(true);
                                    $pp->load_hours();
                                    $pp->load_user();

                                    //load the tickets too for the ticket details
                                    for($i=0;$i<count($pp->details);$i++)
                                    {
                                            if($pp->details[i]->ticket_detail_id)
                                                    $pp->details[i]->load_ticket();
                                    }
                            }
                            $uid_timesheets[$day] = $pp;

                            $day = strtotime ('+1 day', $day);
                    }
                    $day = $filter['date_from'];
                    $timesheets[$uid] = $uid_timesheets;
            }
            $activities = Activity::get_activities_list(array("timesheet"=>true));
            $categories_list = ActivityCategory::get_categories_list();
            $action_types_list = ActionType::get_list();
            $locations_list = InterventionLocation::get_locations_list();
            $customers_list = Customer::get_customers_list();
            //debug($timesheets);
            $this->assign("categories_list", $categories_list);
            $this->assign("customers_list", $customers_list);
            $this->assign("activities", $activities);
            $this->assign("action_types_list", $action_types_list);
            $this->assign("locations_list", $locations_list);
            $this->assign('timesheets', $timesheets);
            $this->assign('users_list', $users_list);
            $this->assign('days', $days);
            $this->assign('filter', $filter);
            $this->assign("TIMESHEET_STATS", $GLOBALS['TIMESHEET_STATS']);
            $this->assign("error_msg", error_msg());
            $this->set_form_redir('manage_timesheets_extended_submit');

            $this->display($tpl);

    }
    function manage_timesheets_extended_submit()
    {
            class_load('Timesheet');
            $ret = $this->mk_redir('manage_timesheet_extended');
            $xml_tpl = "timesheets_report.xml";
            $xsl_tpl = 'timesheet_report.xsl_fo';

            $filter = array();
            $filter['date_from'] = js_strtotime($this->vars['date_from']);
            $filter['date_to'] = js_strtotime($this->vars['date_to']);
            $users_list = User::get_users_list(array('type'=>USER_TYPE_KEYSOURCE));
            //debug($filter);
            $timesheets = array();
            $day = $filter['date_from'];
            $days = array();
            foreach ($users_list as $uid=>$name)
            {
                    $uid_timesheets = array();
                    while($day <= $filter['date_to'])
                    {
                            $days[] = $day;
                            $pp = new Timesheet();
                            $pp = Timesheet::get_timesheet($uid, $day);
                            //debug($pp);
                            if(!$pp->id)
                            {
                                    $pp->load_unassigned_details();
                                    $pp->load_hours();
                                    $pp->load_user();

                                    //load the tickets too for the ticket details
                                    for($i=0;$i<count($pp->details);$i++)
                                    {
                                            if($pp->details[i]->ticket_detail_id)
                                                    $pp->details[i]->load_ticket();
                                    }
                            }
                            else {
                                    if($pp->status == TIMESHEET_STAT_OPEN) $pp->save_data(true);
                                    $pp->load_hours();
                                    $pp->load_user();

                                    //load the tickets too for the ticket details
                                    for($i=0;$i<count($pp->details);$i++)
                                    {
                                            if($pp->details[i]->ticket_detail_id)
                                                    $pp->details[i]->load_ticket();
                                    }
                            }
                            $uid_timesheets[$day] = $pp;

                            $day = strtotime ('+1 day', $day);
                    }
                    $day = $filter['date_from'];
                    $timesheets[$uid] = $uid_timesheets;
            }
            $activities = Activity::get_activities_list(array("timesheet"=>true));
            $categories_list = ActivityCategory::get_categories_list();
            $action_types_list = ActionType::get_list();
            $locations_list = InterventionLocation::get_locations_list();
            $customers_list = Customer::get_customers_list();
            //debug($timesheets);
            $this->assign("categories_list", $categories_list);
            $this->assign("customers_list", $customers_list);
            $this->assign("activities", $activities);
            $this->assign("action_types_list", $action_types_list);
            $this->assign("locations_list", $locations_list);
            $this->assign('timesheets', $timesheets);
            $this->assign('users_list', $users_list);
            $this->assign('days', $days);
            $this->assign('filter', $filter);
            $this->assign("TIMESHEET_STATS", $GLOBALS['TIMESHEET_STATS']);

            $xml = $this->fetch ($xml_tpl);
            make_pdf_xml ($xml, $xsl_tpl);
            die;

    }


    /** Displays the "Filtered view" page for timesheets, where timesheets can be filtered by status and user */
    function timesheets_filter ()
    {
            class_load ('Timesheet');
            check_auth ();
            $tpl = 'timesheets_filter.tpl';

            $filter = $_SESSION['timesheets_filter'];
            if (!isset($filter['user_id'])) $filter['user_id'] = 0;
            if (!isset($filter['status'])) $filter['status'] = TIMESHEET_STAT_CLOSED;
            if (!isset($filter['start'])) $filter['start'] = 0;
            if (!isset($filter['limit'])) $filter['limit'] = 50;

            $tot_timesheets = 0;
            $timesheets = Timesheet::get_timesheets ($filter, $tot_timesheets);
            $pages = make_paging ($filter['limit'], $tot_timesheets);
            if ($filter['start'] > $tot_timesheets)
            {
                    $filter['start'] = 0;
                    $timesheets = Timesheet::get_timesheets ($filter, $tot_timesheets);
            }

            $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
            $stats = $GLOBALS['TIMESHEET_STATS'];
            unset ($stats[TIMESHEET_STAT_NONE]);

            $this->assign ('timesheets', $timesheets);
            $this->assign ('filter', $filter);
            $this->assign ('pages', $pages);
            $this->assign ('tot_timesheets', $tot_timesheets);
            $this->assign ('users_list', $users_list);
            $this->assign ('TIMESHEET_STATS', $stats);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('timesheets_filter_submit');

            $this->display ($tpl);
    }


    /** Save the filtering criteria for the timesheets "Filtered view" page */
    function timesheets_filter_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('timesheets_filter');

            $filter = $this->vars['filter'];
            if ($this->vars['go'] == 'prev') $filter['start']-= $filter['limit'];
            elseif ($this->vars['go'] == 'next') $filter['start']+= $filter['limit'];
            if ($filter['start'] < 0) $filter['start'] = 0;

            $_SESSION['timesheets_filter'] = $filter;
            return $ret;
    }


    /** Displays the page for creating a new timesheet */
    function timesheet_add ()
    {
            class_load ('Timesheet');
            check_auth ();
            $tpl = 'timesheet_add.tpl';

            $user = new User ($this->vars['user_id']);
            $date = $this->vars['date'];
            if (!$user->id or $date<=0) return $this->mk_redir ('manage_timesheets');

            $timesheet = Timesheet::get_timesheet ($user->id, $date);
            if ($timesheet->id) return $this->mk_redir ('timesheet_edit', array ('id' => $timesheet->id));

            $timesheet->load_unassigned_details ();
            $timesheet->load_hours ();
            $timesheet->load_user ();

            // Load the tickets too for ticket details - where available
            for ($i=0; $i<count($timesheet->details); $i++)
            {
                    if ($timesheet->details[$i]->ticket_detail_id) $timesheet->details[$i]->load_ticket ();
            }

            $customers_list = Customer::get_customers_list ();
            $activities = Activity::get_activities_list (array('timesheet' => true));
            $action_types_list = ActionType::get_list ();
            $locations_list = InterventionLocation::get_locations_list ();
            $params = $this->set_carry_fields (array('user_id', 'date'));

            $this->assign ('timesheet', $timesheet);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('activities', $activities);
            $this->assign ('action_types_list', $action_types_list);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('timesheet_add_submit', $params);

            $this->display ($tpl);
    }


    /** Saves the newly created timesheet */
    function timesheet_add_submit ()
    {
            class_load ('Timesheet');
            check_auth ();

            $params = array ();
            $ret = $this->mk_redir ('manage_timesheets');

            if ($this->vars['save'] and $this->vars['user_id'] and $this->vars['date'])
            {
                    // No validations are done, save the timesheet and the details
                    $timesheet = Timesheet::get_timesheet ($this->vars['user_id'], $this->vars['date']);
                    $timesheet->load_unassigned_details ();
                    $timesheet->save_data (true);

                    $ret = $this->mk_redir ('timesheet_edit', array ('id' => $timesheet->id));
            }

            return $ret;
    }


    /** Displays the page for editing a timesheet */
    function timesheet_edit ()
    {
            class_load ('Timesheet');
            check_auth ();
            $tpl = 'timesheet_edit.tpl';

            $timesheet = new Timesheet ($this->vars['id']);
            if (!$timesheet->id) return $this->mk_redir ('manage_timesheets');

            // For OPEN timesheets, we perform a "save" each time we open one, just to
            // associate the timesheet with any new ticket detail which might have been added
            if ($timesheet->status == TIMESHEET_STAT_OPEN) $timesheet->save_data (true);

            $timesheet->load_hours ();
            $timesheet->load_user ();

            // Load the tickets too for ticket details - where available
            for ($i=0; $i<count($timesheet->details); $i++)
            {
                    if ($timesheet->details[$i]->ticket_detail_id) $timesheet->details[$i]->load_ticket ();
            }

            $activities = Activity::get_activities_list (array('timesheet' => true));
            $categories_list = ActivityCategory::get_categories_list ();
            $action_types_list = ActionType::get_list ();
            $customers_list = Customer::get_customers_list ();
            $locations_list = InterventionLocation::get_locations_list ();
            $params = $this->set_carry_fields (array('id', 'returl'));

            $this->assign ('timesheet', $timesheet);
            $this->assign ('activities', $activities);
            $this->assign ('categories_list', $categories_list);
            $this->assign ('action_types_list', $action_types_list);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('TIMESHEET_STATS', $GLOBALS['TIMESHEET_STATS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('timesheet_edit_submit', $params);

            $this->display ($tpl);
    }


    /** Saves a timesheet */
    function timesheet_edit_submit ()
    {
            class_load ('Timesheet');
            check_auth ();
            $timesheet = new Timesheet ($this->vars['id']);

            $params = $this->set_carry_fields (array('id', 'returl'));
            $ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_timesheets'));

            if ($this->vars['close_timesheet'] and $timesheet->id)
            {
                    // This is a request to close the timesheet.
                    if ($timesheet->can_close_timesheet ()) $timesheet->close_timesheet ($this->current_user->id);
                    $ret = $this->mk_redir ('timesheet_edit', $params);
            }
            elseif ($this->vars['reopen_timesheet'] and $timesheet->id)
            {
                    // This is a request to reopen the timesheet
                    if ($timesheet->can_reopen_timesheet ()) $timesheet->reopen_timesheet ();
                    $ret = $this->mk_redir ('timesheet_edit', $params);
            }
            elseif ($this->vars['approve_timesheet'] and $timesheet->id)
            {
                    // This is a request to approve the timesheet
                    if ($timesheet->can_approve_timesheet()) $timesheet->approve_timesheet ($this->current_user->id);
                    $ret = $this->mk_redir ('timesheet_edit', $params);
            }
            elseif ($this->vars['cancel_approval'] and $timesheet->id)
            {
                    // This is a request to cancel the approval of the timesheet
                    if ($timesheet->can_cancel_approval()) $timesheet->cancel_approval ();
                    $ret = $this->mk_redir ('timesheet_edit', $params);
            }
            elseif ($this->vars['cancel_centralization'] and $timesheet->id)
            {
                    // This is a request to cancel the "Centralized" status
                    $ret = $this->mk_redir ('timesheet_cancel_centralized', $params);
            }

            return $ret;
    }


    /** Displays the page asking the user to confirm if he really wants to cancel the "Centralized" status */
    function timesheet_cancel_centralized ()
    {
            class_load ('Timesheet');
            check_auth ();
            $tpl = 'timesheet_cancel_centralized.tpl';
            $timesheet = new Timesheet ($this->vars['id']);
            if (!$timesheet->id) return $this->mk_redir ('manage_timesheets');

            $params = $this->set_carry_fields (array('id', 'returl'));
            $this->assign ('timesheet', $timesheet);
            $this->set_form_redir ('timesheet_cancel_centralized_submit', $params);
            $this->display ($tpl);
    }


    /** Cancels the centralization */
    function timesheet_cancel_centralized_submit ()
    {
            class_load ('Timesheet');
            check_auth ();
            $timesheet = new Timesheet ($this->vars['id']);
            if (!$timesheet->id) return $this->mk_redir ('manage_timesheets');

            $params = $this->set_carry_fields (array('id', 'returl'));
            $ret = $this->mk_redir ('timesheet_edit', $params);

            if ($this->vars['save'] and $timesheet->id)
            {
                    $timesheet->cancel_centralization ();
            }

            return $ret;
    }


    /** Adds a new timesheet detail, unrelated to a ticket detail */
    function timesheet_detail_add ()
    {
            class_load ('Timesheet');
            check_auth ();
            $tpl = 'timesheet_detail_add.tpl';

            $timesheet = new Timesheet ($this->vars['timesheet_id']);
            if (!$timesheet->id) return $this->mk_redir ('manage_timesheets');

            $detail = new TimesheetDetail ();
            if (!empty_error_msg())
            {
                    $data = array();
                    $detail->load_from_array (restore_form_data ('timesheet_detail', false, $data));
            }
            if (!$detail->time_in) $detail->time_in = $this->vars['start'];
            if (!$detail->time_out) $detail->time_out = $this->vars['end'];

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            $timesheet->load_user ();
            $activities = Activity::get_activities_list (true);
            $locations_list = InterventionLocation::get_locations_list (array('helpdesk'=>0));
            $params = $this->set_carry_fields (array ('timesheet_id', 'start', 'end'));

            $this->assign ('timesheet', $timesheet);
            $this->assign ('detail', $detail);
            $this->assign ('start', $this->vars['start']);
            $this->assign ('end', $this->vars['end']);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('activities', $activities);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('timesheet_detail_add_submit', $params);

            $this->display ($tpl);
    }

    /** Saves a detail for a timesheet */
    function timesheet_detail_add_submit ()
    {
            class_load ('Timesheet');
            check_auth ();

            $timesheet = new Timesheet ($this->vars['timesheet_id']);
            $ret = $this->mk_redir ('timesheet_edit', array ('id' => $timesheet->id));

            if ($this->vars['save'] and $timesheet->id)
            {
                    $data = $this->vars['detail'];
                    $data['time_in'] = js_durationtomins ($data['time_in']); 	// minutes from day start
                    $data['time_out'] = js_durationtomins ($data['time_out']);	// minutes from day start

                    if ($data['time_in'] > 24*60) $data['time_in'] = 0;
                    if ($data['time_out'] > 24*60) $data['time_out'] = 0;
                    if ($data['time_in']) $data['time_in'] = $timesheet->date + $data['time_in']*60;
                    if ($data['time_out']) $data['time_out'] = $timesheet->date + $data['time_out']*60;

                    $detail = new TimesheetDetail ();
                    $detail->load_from_array ($data);
                    $detail->timesheet_id = $timesheet->id;

                    if ($detail->is_valid_data ())
                    {
                            $detail->save_data ();
                            $ret = $this->mk_redir ('timesheet_detail_edit', array ('id' => $detail->id));
                    }
                    else
                    {
                            save_form_data ($data, 'timesheet_detail');
                            $params = $this->set_carry_fields (array('timesheet_id', 'start', 'end'));
                            $ret = $this->mk_redir ('timesheet_detail_add', $params);
                    }
            }

            return $ret;
    }


    /** Display the page for editing a timesheet detail - one which is not linked to a ticket detail */
    function timesheet_detail_edit ()
    {
            class_load ('Timesheet');
            check_auth ();
            $tpl = 'ktimesheet_detail_edit.tpl';

            $detail = new TimesheetDetail ($this->vars['id']);
            $timesheet = new Timesheet ($detail->timesheet_id);
            if (!$detail->id or !$timesheet->id) return $this->mk_redir ('manage_timesheets');

            if (!empty_error_msg())
            {
                    $data = array();
                    $detail->load_from_array (restore_form_data ('timesheet_detail', false, $data));
            }

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            $timesheet->load_user ();
            $activities = Activity::get_activities_list (true);
            $locations_list = InterventionLocation::get_locations_list (array('helpdesk'=>0));
            $params = $this->set_carry_fields (array ('id'));

            $this->assign ('timesheet', $timesheet);
            $this->assign ('detail', $detail);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('activities', $activities);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('timesheet_detail_edit_submit', $params);

            $this->display ($tpl);
    }

    /** Saves a timesheet detail */
    function timesheet_detail_edit_submit ()
    {
            class_load ('Timesheet');
            check_auth ();

            $detail = new TimesheetDetail ($this->vars['id']);
            $timesheet = new Timesheet ($detail->timesheet_id);

            $ret = $this->mk_redir ('timesheet_edit', array ('id' => $timesheet->id));

            if ($this->vars['save'] and $detail->id)
            {
                    $data = $this->vars['detail'];
                    $data['time_in'] = js_durationtomins ($data['time_in']);	// minutes from day start
                    $data['time_out'] = js_durationtomins ($data['time_out']);	// minutes from day start

                    if ($data['time_in'] > 24*60) $data['time_in'] = 0;
                    if ($data['time_out'] > 24*60) $data['time_out'] = 0;
                    if ($data['time_in']) $data['time_in'] = $timesheet->date + $data['time_in']*60;
                    if ($data['time_out']) $data['time_out'] = $timesheet->date + $data['time_out']*60;

                    $detail->load_from_array ($data);
                    if ($detail->is_valid_data ())
                    {
                            $detail->save_data ();
                    }
                    else
                    {
                            save_form_data ($data, 'timesheet_detail');
                    }
                    $ret = $this->mk_redir ('timesheet_detail_edit', array ('id' => $detail->id));
            }

            return $ret;
    }


    /** Deletes a detail from a timesheet, one that is not linked to a ticket detail */
    function timesheet_detail_delete ()
    {
            class_load ('Timesheet');
            check_auth ();

            $detail = new TimesheetDetail ($this->vars['id']);
            $ret = $this->mk_redir ('timesheet_edit', array ('id' => $detail->timesheet_id));

            if ($detail->id and $detail->can_delete ())
            {
                    $detail->delete ();
            }

            return $ret;
    }


    /** Used for displaying popup window for "filling the gaps" in a timesheet. */
    function popup_fill_ts_gaps ()
    {
            class_load ('Timesheet');
            class_load ('InterventionLocation');
            check_auth ();
            $tpl = 'popup_fill_ts_gaps.tpl';

            $timesheet = new Timesheet ($this->vars['id']);
            $timesheet->load_hours ();
            $intervals = array ();

            // Use a generic TimesheetDetail for storing the selected activity, in case there was an error and
            // the page is reloaded
            $detail = new TimesheetDetail ();
            if (!empty_error_msg())
            {
                    $data = array();
                    $detail->load_from_array (restore_form_data ('fill_gaps_details', false, $data));
                    if (isset($data['intervals'])) $intervals = $data['intervals'];
            }

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            $timesheet->load_user ();
            $activities = Activity::get_activities_list (true);
            $locations_list = InterventionLocation::get_locations_list (array('helpdesk'=>0));

            $params = $this->set_carry_fields (array('id'));

            $this->assign ('timesheet', $timesheet);
            $this->assign ('detail', $detail);
            $this->assign ('intervals', $intervals);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('activities', $activities);
            $this->assign ('locations_list', $locations_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('popup_fill_ts_gaps_submit', $params);

            $this->display_template_limited ($tpl);
    }


    function popup_fill_ts_gaps_submit ()
    {
            class_load ('Timesheet');
            class_load ('InterventionLocation');
            check_auth ();
            $timesheet = new Timesheet ($this->vars['id']);

            $params = $this->set_carry_fields (array('id'));
            $ret = $this->mk_redir ('popup_fill_ts_gaps', $params);

            if ($this->vars['save'] and $timesheet->id)
            {
                    $data = $this->vars['detail'];
                    $intervals = $data['intervals'];
                    $details = array ();
                    $all_valid = true;

                    if (!is_array($intervals) or count($intervals)==0)
                    {
                            $all_valid = false;
                            error_msg ('You have not selected any intervals.');
                    }
                    else
                    {
                            $times_in = $this->vars['times_in'];
                            $times_out = $this->vars['times_out'];

                            foreach ($intervals as $idx)
                            {
                                    $detail = new TimesheetDetail ();
                                    $data_detail = $data;
                                    $data_detail['timesheet_id'] = $timesheet->id;
                                    $data_detail['time_in'] = $times_in[$idx];
                                    $data_detail['time_out'] = $times_out[$idx];
                                    $detail->load_from_array ($data_detail);
                                    $details[] = $detail;
                            }

                            // We only need to check the validity of the first detail, because the rest are similar
                            if ($details[0]->is_valid_data ())
                            {
                                    $all_valid = true;
                                    for ($i=0; $i<count($details); $i++) $details[$i]->save_data ();
                            }
                            else $all_valid = false;
                    }

                    if ($all_valid)
                    {
                            // Use JavaScript in the pop-up window to refresh the calling page and close the pop-up
                            echo '<script language="JavaScript"> window.opener.location = window.opener.location; ';
                            echo 'window.close (); </script>';
                            exit;
                    }
                    else
                    {
                            save_form_data ($data, 'fill_gaps_details');
                    }
            }

            return $ret;
    }


    /****************************************************************/
    /* Action types management					*/
    /****************************************************************/

    /** Displays the page for managing action types */
    function manage_action_types ()
    {
            check_auth ();
            $tpl = 'manage_action_types.tpl';

            $filter = $_SESSION['manage_action_types'];
            if (!$filter['group_by']) $filter['group_by'] = 'category';
            if (!$filter['order_by']) $filter['order_by'] = 'name';
            $filter['show_empty_groups'] = true;

            $action_types = ActionType::get_action_types ($filter);
            $actypes_categories_list = ActionTypeCategory::get_categories_list ();

            $this->assign ('action_types', $action_types);
            $this->assign ('filter', $filter);
            $this->assign ('PRICE_TYPES', $GLOBALS['PRICE_TYPES']);
            $this->assign ('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
            $this->assign ('CUST_SUBTYPES', $GLOBALS['CUST_SUBTYPES']);
            $this->assign ('actypes_categories_list', $actypes_categories_list);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('manage_action_types_submit');

            $this->display ($tpl);
    }


    /** Saves filtering criteria for manage action types page */
    function manage_action_types_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_action_types');

            $_SESSION['manage_action_types'] = $this->vars['filter'];
            return $ret;
    }


    /** NOT USED ANYMORE. Displays the page for defining a new action type */
    function action_type_add ()
    {
            error_msg ('New action types should be added only through ERP');
            return $this->mk_redir ('manage_action_types');

            check_auth ();
            $tpl = 'krifaction_type_add.tpl';

            $action_type = new ActionType ();
            if (!empty_error_msg())
            {
                    $data = array();
                    $action_type->load_from_array (restore_form_data ('action_type', false, $data));
            }

            $this->assign ('action_type', $action_type);
            $this->assign ('PRICE_TYPES', $GLOBALS['PRICE_TYPES']);
            $this->assign ('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('action_type_add_submit');

            $this->display ($tpl);
    }


    /** NOT USED ANYMORE. Saves a new action type */
    function action_type_add_submit ()
    {
            error_msg ('New action types should be added only through ERP');
            return $this->mk_redir ('manage_action_types');

            check_auth ();
            $ret = $this->mk_redir ('manage_action_types');

            if ($this->vars['save'])
            {
                    $data = $this->vars['action_type'];
                    $action_type = new ActionType ();
                    $action_type->load_from_array ($data);

                    if ($action_type->is_valid_data ())
                    {
                            $action_type->save_data ();
                            $ret = $this->mk_redir ('action_type_edit', array ('id' => $action_type->id));
                    }
                    else
                    {
                            save_form_data ($data, 'action_type');
                            $ret = $this->mk_redir ('action_type_add');
                    }
            }

            return $ret;
    }


    /** Displays the page for editing an action type */
    function action_type_edit ()
    {
            check_auth ();
            $tpl = 'action_type_edit.tpl';
            $action_type = new ActionType ($this->vars['id']);
            if (!$action_type->id) return $this->mk_redir ('manage_action_types');

            if (!empty_error_msg())
            {
                    $data = array();
                    $action_type->load_from_array (restore_form_data ('action_type', false, $data));
            }

            $params = $this->set_carry_fields (array('id'));
            $actypes_categories_list = ActionTypeCategory::get_categories_list ();

            $this->assign ('action_type', $action_type);
            $this->assign ('PRICE_TYPES', $GLOBALS['PRICE_TYPES']);
            $this->assign ('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
            $this->assign ('CUST_SUBTYPES', $GLOBALS['CUST_SUBTYPES']);
            $this->assign ('actypes_categories_list', $actypes_categories_list);
            $this->assign ('ACTYPE_SPECIALS', $GLOBALS['ACTYPE_SPECIALS']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('action_type_edit_submit', $params);

            $this->display ($tpl);
    }


    /** Saves an action type */
    function action_type_edit_submit ()
    {
            check_auth ();
            $action_type = new ActionType ($this->vars['id']);

            if ($action_type->special_type) $ret = $this->mk_redir ('manage_action_types_special');
            else $ret = $this->mk_redir ('manage_action_types');
            $params = $this->set_carry_fields (array('id'));

            if ($this->vars['save'] and $action_type->id)
            {
                    $data = $this->vars['action_type'];
                    $action_type->load_from_array ($data);

                    if ($action_type->is_valid_data ())
                    {
                            $action_type->save_data ();
                    }
                    else
                    {
                            save_form_data ($data, 'action_type');
                    }
                    $ret = $this->mk_redir ('action_type_edit', $params);
            }

            return $ret;
    }


    /** Delets an action type */
    function action_type_delete ()
    {
            check_auth ();
            $action_type = new ActionType ($this->vars['id']);
            $ret = $this->mk_redir ('manage_action_types');

            //if ($action
            //XXXXXXXXXXXXXXXXXXXXXx
            echo "Not done yet";
    }


    /****************************************************************/
    /* Management of special action types				*/
    /****************************************************************/

    /** Display the page for managing special action types */
    function manage_action_types_special ()
    {
            check_auth ();
            $tpl = 'manage_action_types_special.tpl';

            $action_types = array ();
            foreach ($GLOBALS ['ACTYPE_SPECIALS'] as $id => $name)
            {
                    $action_types[$id] = ActionType::get_action_types (array(
                            'special_type' => $id
                    ));
            }

            $this->assign ('action_types', $action_types);
            $this->assign ('filter', $filter);
            $this->assign ('ACTYPE_SPECIALS', $GLOBALS ['ACTYPE_SPECIALS']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('manage_action_types_submit');

            $this->display ($tpl);
    }


    /****************************************************************/
    /* Activities management					*/
    /****************************************************************/

    /** Displays the page for managing activities */
    function manage_activities ()
    {
            check_auth ();
            $tpl = 'manage_activities.tpl';

            $activities = Activity::get_activities ();
            $categories_list = ActivityCategory::get_categories_list ();

            $this->assign ('activities', $activities);
            $this->assign ('categories_list', $categories_list);
            $this->assign ('error_msg', error_msg ());

            $this->display ($tpl);
    }


    /** Displays the page for adding a new activity */
    function activity_add ()
    {
            check_auth ();
            $tpl = 'activity_add.tpl';

            $activity = new Activity ();
            if (!empty_error_msg())
            {
                    $data = array();
                    $activity->load_from_array (restore_form_data ('activity', false, $data));
            }

            $this->assign ('activity', $activity);
            $this->assign ('categories_list', ActivityCategory::get_categories_list ());
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('activity_add_submit');
            $this->display ($tpl);
    }

    /** Saves a new activity */
    function activity_add_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_activities');

            if ($this->vars['save'])
            {
                    $data = $this->vars['activity'];
                    $activity = new Activity ();
                    $activity->load_from_array ($data);

                    if ($activity->is_valid_data ())
                    {
                            $activity->save_data ();
                    }
                    else
                    {
                            save_form_data ($data, 'activity');
                            $ret = $this->mk_redir ('activity_add');
                    }
            }

            return $ret;
    }


    /** Displays the page for editing an activity */
    function activity_edit ()
    {
            check_auth ();
            $tpl = 'activity_edit.tpl';
            $activity = new Activity ($this->vars['id']);
            if (!$activity->id) return $this->mk_redir ('manage_activities');

            if (!empty_error_msg())
            {
                    $data = array();
                    $activity->load_from_array (restore_form_data ('activity', false, $data));
            }

            $params = $this->set_carry_fields (array('id'));

            $this->assign ('activity', $activity);
            $this->assign ('categories_list', ActivityCategory::get_categories_list ());
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('activity_edit_submit', $params);

            $this->display ($tpl);
    }


    /** Saves the data for an activity */
    function activity_edit_submit ()
    {
            check_auth ();
            $activity = new Activity ($this->vars['id']);

            $ret = $this->mk_redir ('manage_activities');

            if ($this->vars['save'] and $activity->id)
            {
                    $data = $this->vars['activity'];
                    $activity->load_from_array ($data);

                    if ($activity->is_valid_data ())
                    {
                            $activity->save_data ();
                    }
                    else
                    {
                            save_form_data ($data, 'activity');
                            $ret = $this->mk_redir ('activity_edit', array ('id' => $activity->id));
                    }
            }

            return $ret;
    }


    /** Deletes an activity */
    function activity_delete ()
    {
            check_auth ();
            $activity = new Activity ($this->vars['id']);

            $ret = $this->mk_redir ('manage_activities');
            if ($activity->can_delete ()) $activity->delete ();

            return $ret;
    }


    /** Displays the page for managing activities categories */
    function manage_activities_categories ()
    {
            check_auth ();
            $tpl = 'manage_activities_categories.tpl';

            $categories = ActivityCategory::get_categories ();
            // Populate the array with the activities for each category
            for ($i=0; $i<count($categories); $i++) $categories[$i]->activities = Activity::get_activities_list (array('category_id'=>$categories[$i]->id));

            $this->assign ('categories', $categories);
            $this->assign ('error_msg', error_msg ());

            $this->display ($tpl);
    }


    /** Displays the page for adding a new activities category */
    function activity_category_add ()
    {
            check_auth ();
            $tpl = 'activity_category_add.tpl';

            $category = new ActivityCategory ();
            $this->assign ('category', $category);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('activity_category_add_submit');

            $this->display ($tpl);
    }


    /** Saves the newly created activities category */
    function activity_category_add_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_activities_categories');

            if ($this->vars['save'])
            {
                    $data = $this->vars['category'];
                    $category = new ActivityCategory ();
                    $category->load_from_array ($data);

                    if ($category->is_valid_data ())
                    {
                            $category->save_data ();
                            $ret = $this->mk_redir ('activity_category_edit', array('id'=>$category->id));
                    }
                    else $ret = $this->mk_redir ('activity_category_add');
            }

            return $ret;
    }


    /** Displays the page for editing a category of activities */
    function activity_category_edit ()
    {
            check_auth ();
            $tpl = 'activity_category_edit.tpl';

            $category = new ActivityCategory ($this->vars['id']);
            if (!$category->id) return $this->mk_redir ('manage_activities_categories');

            $params = $this->set_carry_fields (array('id'));

            $this->assign ('category', $category);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('activity_category_edit_submit', $params);

            $this->display ($tpl);
    }

    /** Saves the details for the category of activities */
    function activity_category_edit_submit ()
    {
            check_auth ();
            $category = new ActivityCategory ($this->vars['id']);

            $ret = $this->mk_redir ('manage_activities_categories');
            $params = $this->set_carry_fields (array('id'));

            if ($this->vars['save'] and $category->id)
            {
                    $category->load_from_array ($this->vars['category']);
                    if ($category->is_valid_data ()) $category->save_data ();
                    $ret = $this->mk_redir ('activity_category_edit', $params);
            }

            return $ret;
    }

    /** Deletes a category of activities  */
    function activity_category_delete ()
    {
            check_auth ();
            $category = new ActivityCategory ($this->vars['id']);
            $ret = $this->mk_redir ('manage_activities_categories');

            if ($category->id and $category->can_delete ()) $category->delete ();

            return $ret;
    }


    /****************************************************************/
    /* Intervention locations management				*/
    /****************************************************************/

    /** Displays the page for managing intervention locations */
    function manage_intervention_locations ()
    {
            check_auth ();
            class_load ('InterventionLocation');
            $tpl = 'manage_intervention_locations.tpl';

            $locations = InterventionLocation::get_locations ();

            $this->assign ('locations', $locations);
            $this->assign ('error_msg', error_msg ());

            $this->display ($tpl);
    }


    /** Displays the page for defining a new intervention location */
    function intervention_location_add ()
    {
            check_auth ();
            class_load ('InterventionLocation');
            $tpl = 'intervention_location_add.tpl';

            $location = new InterventionLocation ();
            if (!empty_error_msg())
            {
                    $data = array();
                    $location->load_from_array (restore_form_data ('location', false, $data));
            }

            $this->assign ('location', $location);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('intervention_location_add_submit');

            $this->display ($tpl);
    }


    /** Saves the new intervention location */
    function intervention_location_add_submit ()
    {
            check_auth ();
            class_load ('InterventionLocation');
            $ret = $this->mk_redir ('manage_intervention_locations');

            if ($this->vars['save'])
            {
                    $data = $this->vars['location'];
                    $location = new InterventionLocation ();
                    $location->load_from_array ($data);

                    if ($location->is_valid_data ())
                    {
                            $location->save_data ();
                            $ret = $this->mk_redir ('intervention_location_edit', array ('id' => $location->id));
                    }
                    else
                    {
                            save_form_data ($data, 'location');
                            $ret = $this->mk_redir ('intervention_location_add');
                    }
            }

            return $ret;
    }


    /** Displays the page for editing an intervention location */
    function intervention_location_edit ()
    {
            check_auth ();
            class_load ('InterventionLocation');
            $tpl = 'intervention_location_edit.tpl';

            $location = new InterventionLocation ($this->vars['id']);
            if (!$location->id) return $this->mk_redir ('manage_intervention_locations');

            if (!empty_error_msg())
            {
                    $data = array();
                    $location->load_from_array (restore_form_data ('location', false, $data));
            }

            $this->assign ('location', $location);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('intervention_location_edit_submit', array ('id' => $location->id));

            $this->display ($tpl);
    }


    /** Saves an intervention location */
    function intervention_location_edit_submit ()
    {
            check_auth ();
            class_load ('InterventionLocation');
            $location = new InterventionLocation ($this->vars['id']);
            $ret = $this->mk_redir ('manage_intervention_locations');

            if ($this->vars['save'] and $location->id)
            {
                    $data = $this->vars['location'];
                    $location->load_from_array ($data);

                    if ($location->is_valid_data ())
                    {
                            $location->save_data ();
                    }
                    else
                    {
                            save_form_data ($data, 'location');
                    }
                    $ret = $this->mk_redir ('intervention_location_edit', array ('id' => $location->id));
            }

            return $ret;
    }


    /** Deletes an intervention location */
    function intervention_location_delete ()
    {
            check_auth ();
            class_load ('InterventionLocation');
            $location = new InterventionLocation ($this->vars['id']);
            $ret = $this->mk_redir ('manage_intervention_locations');

            if ($location->id and $location->can_delete ())
            {
                    $location->delete ();
            }

            return $ret;
    }


    /****************************************************************/
    /* Pop-ups for selecting durations and intervals		*/
    /****************************************************************/

    /** Used for displaying popup windows for selecting intervals. Note that the
    * data is passed back to the calling page through JavaScript */
    function popup_activity ()
    {
            check_auth ();
            class_load ('InterventionLocation');
            $tpl = 'popup_activity_ajax.tpl';

            // Check if the times have been passed as strings
            if ($this->vars['time_in_string']) $this->vars['time_in'] = js_strtotime ($this->vars['time_in_string']);
            if ($this->vars['duration_string']) $this->vars['duration'] = js_durationtomins ($this->vars['duration_string']);
            if ($this->vars['time_out_string']) $this->vars['time_out'] = js_strtotime ($this->vars['time_out_string']);

            $title = ($this->vars['title'] ? $this->vars['title'] : 'Duration');
            $show_location = $this->vars['show_location'];

            if ($this->vars['ticket_id'])
            {
                    $ticket = new Ticket ($this->vars['ticket_id']);
                    $customer = new Customer ($ticket->customer_id);
            }
            elseif ($this->vars['customer_id'])
            {
                    $customer = new Customer ($this->vars['customer_id']);
            }

            $activity_id = ($this->vars['activity_id'] ? $this->vars['activity_id'] : 0);
            $is_continuation = ($this->vars['is_continuation'] ? 1 : 0);
            $billable = ($this->vars['billable'] ? 1 : 0);
            $intervention_report_id = ($this->vars['intervention_report_id'] ? $this->vars['intervention_report_id'] : 0);
            $time_in = $this->vars['time_in'];
            $duration = $this->vars['duration'];
            $time_out = $this->vars['time_out'];
            $location_id = ($this->vars['location_id'] ? $this->vars['location_id'] : 0);
            if(!$location_id) $location_id = DEFAULT_LOCATION_ID;
            if(!$activity_id) $activity_id = DEFAULT_ACTIVITY_ID;

            $time_start_travel_to = $this->vars['time_start_travel_to'];
            $time_end_travel_to = $this->vars['time_end_travel_to'];
            $time_start_travel_from = $this->vars['time_start_travel_from'];
            $time_end_travel_from = $this->vars['time_end_travel_from'];

            $time_start_travel_to = ($time_start_travel_to ? $time_start_travel_to : 0);
            $time_end_travel_to = ($time_end_travel_to ? $time_end_travel_to : 0);
            $time_start_travel_from = ($time_start_travel_from ? $time_start_travel_from : 0);
            $time_end_travel_from = ($time_end_travel_from ? $time_end_travel_from : 0);

            // Check first if we have two of the parameters specified, so we can computer the other
            if (!$time_in and $duration and $time_out) $time_in = $time_out - $duration*60;
            if ($time_in and !$duration and $time_out) $duration = intval(($time_out-$time_in)/60);
            if ($time_in and $duration and !$time_out) $time_out = $time_in + $duration*60;

            // If some values are still missing, start assigning defaults
            if (!$time_in) $time_in = time();
            if (!$duration) $duration = 5;
            $time_out = $time_in + $duration*60;

            $duration_hours = intval ($duration/60);
            $duration_minutes = abs(($duration % 60));
            $duration_minutes = str_pad ($duration_minutes, 2, '0', STR_PAD_LEFT);
            if ($duration < 0 and $duration_hours==0) $duration_hours = '-'.$duration_hours;

            // Get the list of action types for this customer and for fixed-price items
            $filter_customer_actions = array (
                    'group_by' => 'category',
                    'contract_type_cust' => $customer->contract_type,
                    'contract_sub_type' => $customer->contract_sub_type,
                    'active' => 1
            );

            $action_types_customer = ActionType::get_action_types ($filter_customer_actions);
            $filter_customer_actions['helpdesk'] = 1;
            $action_types_customer_helpdesk = ActionType::get_action_types ($filter_customer_actions);
            $action_types_fixed_list = ActionType::get_list (array('price_type' => PRICE_TYPE_FIXED));
            $actypes_categories_list = ActionTypeCategory::get_categories_list ();

            // Get the list of locations
            $locations_list = InterventionLocation::get_locations_list ();
            $locations_list_helpdesk = InterventionLocation::get_locations_list (array('helpdesk' => 1));
            $locations_list_onsite = InterventionLocation::get_locations_list (array('on_site' => 1));

            $this->assign ('title', $title);
            $this->assign ('show_location', $show_location);
            $this->assign ('customer', $customer);
            $this->assign ('ticket', $ticket);
            $this->assign ('ticket_detail', $ticket_detail);

            $this->assign ('activity_id', $activity_id);
            $this->assign ('is_continuation', $is_continuation);
            $this->assign ('billable', $billable);
            $this->assign ('intervention_report_id', $intervention_report_id);
            $this->assign ('time_in', $time_in);
            $this->assign ('duration', $duration);
            $this->assign ('time_out', $time_out);
            $this->assign ('location_id', $location_id);

            $this->assign ('time_start_travel_to', $time_start_travel_to);
            $this->assign ('time_end_travel_to', $time_end_travel_to);
            $this->assign ('time_start_travel_from', $time_start_travel_from);
            $this->assign ('time_end_travel_from', $time_end_travel_from);

            $this->assign ('locations_list', $locations_list);
            $this->assign ('locations_list_helpdesk', $locations_list_helpdesk);
            $this->assign ('locations_list_onsite', $locations_list_onsite);
            $this->assign ('action_types_customer', $action_types_customer);
            $this->assign ('action_types_customer_helpdesk', $action_types_customer_helpdesk);
            $this->assign ('action_types_fixed_list', $action_types_fixed_list);
            $this->assign ('actypes_categories_list', $actypes_categories_list);

            $this->display_template_limited ($tpl);
    }

    /****************************************************************/
    /* Pop-ups for selecting durations and intervals		*/
    /****************************************************************/

    /** Used for displaying popup windows for selecting intervals. Note that the
    * data is passed back to the calling page through JavaScript */
    function popup_activity_ajax_home ()
    {
            check_auth ();
            class_load ('InterventionLocation');
            $tpl = 'popup_activity_ajax_home.tpl';

            // Check if the times have been passed as strings
            if ($this->vars['time_in_string']) $this->vars['time_in'] = js_strtotime ($this->vars['time_in_string']);
            if ($this->vars['duration_string']) $this->vars['duration'] = js_durationtomins ($this->vars['duration_string']);
            if ($this->vars['time_out_string']) $this->vars['time_out'] = js_strtotime ($this->vars['time_out_string']);

            $title = ($this->vars['title'] ? $this->vars['title'] : 'Duration');
            $show_location = $this->vars['show_location'];

            if ($this->vars['ticket_id'])
            {
                    $ticket = new Ticket ($this->vars['ticket_id']);
                    $customer = new Customer ($ticket->customer_id);
            }
            elseif ($this->vars['customer_id'])
            {
                    $customer = new Customer ($this->vars['customer_id']);
            }

            $activity_id = ($this->vars['activity_id'] ? $this->vars['activity_id'] : 0);
            $is_continuation = ($this->vars['is_continuation'] ? 1 : 0);
            $billable = ($this->vars['billable'] ? 1 : 0);
            $intervention_report_id = ($this->vars['intervention_report_id'] ? $this->vars['intervention_report_id'] : 0);
            $time_in = $this->vars['time_in'];
            $duration = $this->vars['duration'];
            $time_out = $this->vars['time_out'];
            $location_id = ($this->vars['location_id'] ? $this->vars['location_id'] : 0);
            if(!$location_id) $location_id = DEFAULT_LOCATION_ID;
            if(!$activity_id) $activity_id = DEFAULT_ACTIVITY_ID;

            $time_start_travel_to = $this->vars['time_start_travel_to'];
            $time_end_travel_to = $this->vars['time_end_travel_to'];
            $time_start_travel_from = $this->vars['time_start_travel_from'];
            $time_end_travel_from = $this->vars['time_end_travel_from'];

            $time_start_travel_to = ($time_start_travel_to ? $time_start_travel_to : 0);
            $time_end_travel_to = ($time_end_travel_to ? $time_end_travel_to : 0);
            $time_start_travel_from = ($time_start_travel_from ? $time_start_travel_from : 0);
            $time_end_travel_from = ($time_end_travel_from ? $time_end_travel_from : 0);

            // Check first if we have two of the parameters specified, so we can computer the other
            if (!$time_in and $duration and $time_out) $time_in = $time_out - $duration*60;
            if ($time_in and !$duration and $time_out) $duration = intval(($time_out-$time_in)/60);
            if ($time_in and $duration and !$time_out) $time_out = $time_in + $duration*60;

            // If some values are still missing, start assigning defaults
            if (!$time_in) $time_in = time();
            if (!$duration) $duration = 5;
            $time_out = $time_in + $duration*60;

            $duration_hours = intval ($duration/60);
            $duration_minutes = abs(($duration % 60));
            $duration_minutes = str_pad ($duration_minutes, 2, '0', STR_PAD_LEFT);
            if ($duration < 0 and $duration_hours==0) $duration_hours = '-'.$duration_hours;

            // Get the list of action types for this customer and for fixed-price items
            $filter_customer_actions = array (
                    'group_by' => 'category',
                    'contract_type_cust' => $customer->contract_type,
                    'contract_sub_type' => $customer->contract_sub_type,
                    'active' => 1
            );

            $action_types_customer = ActionType::get_action_types ($filter_customer_actions);
            $filter_customer_actions['helpdesk'] = 1;
            $action_types_customer_helpdesk = ActionType::get_action_types ($filter_customer_actions);
            $action_types_fixed_list = ActionType::get_list (array('price_type' => PRICE_TYPE_FIXED));
            $actypes_categories_list = ActionTypeCategory::get_categories_list ();

            // Get the list of locations
            $locations_list = InterventionLocation::get_locations_list ();
            $locations_list_helpdesk = InterventionLocation::get_locations_list (array('helpdesk' => 1));
            $locations_list_onsite = InterventionLocation::get_locations_list (array('on_site' => 1));

            $this->assign ('title', $title);
            $this->assign ('show_location', $show_location);
            $this->assign ('customer', $customer);
            $this->assign ('ticket', $ticket);
            $this->assign ('ticket_detail', $ticket_detail);

            $this->assign ('activity_id', $activity_id);
            $this->assign ('is_continuation', $is_continuation);
            $this->assign ('billable', $billable);
            $this->assign ('intervention_report_id', $intervention_report_id);
            $this->assign ('time_in', $time_in);
            $this->assign('ticket_id', $this->vars['ticket_id']);
            $this->assign('ticket_detail_id', $this->vars['ticket_detail_id']);
            $this->assign ('duration', $duration);
            $this->assign ('time_out', $time_out);
            $this->assign ('location_id', $location_id);

            $this->assign ('time_start_travel_to', $time_start_travel_to);
            $this->assign ('time_end_travel_to', $time_end_travel_to);
            $this->assign ('time_start_travel_from', $time_start_travel_from);
            $this->assign ('time_end_travel_from', $time_end_travel_from);

            $this->assign ('locations_list', $locations_list);
            $this->assign ('locations_list_helpdesk', $locations_list_helpdesk);
            $this->assign ('locations_list_onsite', $locations_list_onsite);
            $this->assign ('action_types_customer', $action_types_customer);
            $this->assign ('action_types_customer_helpdesk', $action_types_customer_helpdesk);
            $this->assign ('action_types_fixed_list', $action_types_fixed_list);
            $this->assign ('actypes_categories_list', $actypes_categories_list);

            $this->display_template_limited ($tpl);
    }

    /** Used for displaying a pop-up window for selecting hours and durations */
    function popup_hours ()
    {
            $tpl = 'popup_hours.tpl';

            // The durations to select, in minutes
            $intervals = array (15=>'15 min.', 30=>'30 min.', 60=>'1:00 h.', 90=>'1:30 h.', 120=>'2:00 h.');

            // The starting hours
            $hours = array();
            for ($h=6; $h<=22; $h++)
            {
                    $hours[$h] = array ();
                    foreach ($intervals as $mins => $duration)
                    {
                            $hours[$h][] = array (
                                    'hour_start' => str_pad($h, 2, '0', STR_PAD_LEFT).':00',
                                    'duration' => $duration,
                                    'minutes' => $minutes,
                                    'hour_end' => str_pad(($h+intval($mins/60)), 2, '0', STR_PAD_LEFT).':'.str_pad(($mins % 60), 2, '0', STR_PAD_LEFT),
                            );
                    }
            }

            $this->assign ('intervals', $intervals);
            $this->assign ('hours', $hours);

            $this->display_template_limited ($tpl);
    }

///XXX Added by Victor

    ///displays a conosle for printing the reports directly
    function interventions_print_console()
    {
            class_load ('InterventionReport');
            check_auth ();
            $tpl = 'interventions_print_console.tpl';

            if (isset($this->vars['customer_id'])) $_SESSION['interventions_print_console']['customer_id'] = $this->vars['customer_id'];
            elseif ($this->locked_customer->id and !$this->vars['do_filter'])
            {
                    // If 'do_filter' is present in request or if we are in advanced search,
                    // the locked customer is ignored
                    $_SESSION['interventions_print_console']['customer_id'] = $this->locked_customer->id;
            }

            $filter = $_SESSION['interventions_print_console'];
            if($filter['customer_id']) $filter['customer_ids'] = $filter['customer_id'];

            if (!$filter['start']) $filter['start'] = 0;
            if (!$filter['limit']) $filter['limit'] = 20;
            if (!$filter['show']) $filter['show'] = 'detailed';
            if (!$filter['view']) $filter['view'] = 'keysource';

            if(!isset($filter['manager'])) $manager=0; else $manager=$filter['manager'];
            $tot_interventions = 0;
            $interventions = InterventionReport::get_interventions ($manager, $filter, $tot_interventions);
            $pages = make_paging ($filter['limit'], $tot_interventions);
            if ($filter['start'] > $tot_interventions)
            {
                    $filter['start'] = 0;
                    $interventions = InterventionReport::get_interventions ($manager, $filter, $tot_interventions);
            }

            // Extract the list of customers, eventually restricting only to the customers assigned to
            // the current user, if he has restricted customer access.
            $customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1, 'account_manager'=>$filter['manager']);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);
            $users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));

            // For each intervention report, load also the associated tickets
            for ($i=0; $i<count($interventions); $i++) $interventions[$i]->load_tickets ();

            // Get the statuses of the intervention reports
            $totals = InterventionReport::get_totals ();

            $params = $this->set_carry_fields (array('do_filter'));

            // Mark the potential customer for locking
            if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

            $this->assign ('interventions', $interventions);
            $this->assign ('filter', $filter);
            $this->assign ('totals', $totals);
            $this->assign ('do_filter', $this->vars['do_filter']);
            $this->assign ('pages', $pages);
            $this->assign ('tot_interventions', $tot_interventions);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('users_list', $users_list);
            $this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
            $this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
            $this->assign ('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('interventions_print_console_submit', $params);

            $this->display ($tpl);
    }

    function interventions_print_console_submit()
    {
            check_auth ();
            $params = array ('do_filter'=>1);
            $ret = $this->mk_redir ('interventions_print_console', $params);
            $filter = $this->vars['filter'];

            if ($this->vars['go'] == 'prev') $filter['start'] = $filter['start'] - $filter['limit'];
            elseif ($this->vars['go'] == 'next') $filter['start'] = $filter['start'] + $filter['limit'];

            $_SESSION['interventions_print_console'] = $filter;

            return $ret;
    }


    function intervention_approval_console()
    {
        check_auth();
        class_load('InterventionReport');
        class_load('InterventionLocation');
        $tpl = "intervention_approval_console.tpl";

        if(isset($this->vars['customer_id'])) 
                $_SESSION['intervention_approval_console']['customer_id'] = $this->vars['customer_id'];
        elseif ($this->locked_customer->id and  !$this->vars['do_filter'])
        {
            $_SESSION['intervention_approval_console']['customer_id'] = $this->locked_customer->id;
        }

        $filter = $_SESSION['intervention_approval_console'];
        if($filter['customer_id']) $filter['customer_ids'] = $filter['customer_id'];

        if(!$filter['start']) $filter['start'] = 0;
        if(!$filter['limit']) $filter['limit'] = 20;
        if(!isset($filter['status'])) $filter['status'] = INTERVENTION_STAT_CLOSED;

        $ir_to_select = 0;
        if(isset($_SESSION['intervention_approval_console']['sel_ir'])) $ir_to_select = $_SESSION['intervention_approval_console']['sel_ir'];
        //debug($filter);

        if(!isset($filter['manager'])) $manager=0;
        else $manager=$filter['manager'];

        $tot_interventions = 0;

        $interventions = InterventionReport::get_interventions($manager, $filter, $tot_interventions);

        $pages = make_paging($filter['limit'], $tot_interventions);

        if($filter['start'] > $tot_interventions){
            $filter['start'] = 0;
            $interventions = InterventionReport::get_interventions($manager, $filter, $tot_interventions);
        }

        $customers_filter = array('favorites_first'=>$this->current_user->id, 'show_ids' => 1, 'account_manager'=>$filter['manager']);
        if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($customers_filter);
        $users_list = User::get_users_list(array('type' => USER_TYPE_KEYSOURCE));

        $interventions_ids_list = array();
        $tickets_tbb = array();
        //foreach intervention report load the associated tickets
        for($i=0; $i<count($interventions); $i++){
            $interventions_ids_list[] = $interventions[$i]->id;
            $interventions[$i]->load_tickets();
            foreach ($interventions[$i]->tickets as $t)
              {
                      $ir_tbb = $t->get_ir_tbbtime();
                      $tot_tbb = $t->get_tot_tbbtime();
                      $diff = $tot_tbb - $ir_tbb;
                      $color = $diff>0 ? 'red' : 'green';
                      $tickets_tbb[$t->id]['ir'] = format_interval_minutes($ir_tbb);
                      $tickets_tbb[$t->id]['tot'] = format_interval_minutes($tot_tbb);
                      $tickets_tbb[$t->id]['dif'] = $diff > 0 ? format_interval_minutes($diff) : $diff;
                      $tickets_tbb[$t->id]['color'] = $color;
              }
        }
        if($ir_to_select>0){
            if(!in_array($ir_to_select, $interventions_ids_list)) $ir_to_select = 0;
        }

        $totals = InterventionReport::get_totals();

        $locations_list = InterventionLocation::get_locations_list ();

        //mark the potentialcustomer for locking
        if($filter['customer_id'] > 0)
            $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

        if(isset($_SESSION['intervention_approval_console'])) unset($_SESSION['intervention_approval_console']);

        $this->assign('tickets_tbb', $tickets_tbb);
        $this->assign('ir_to_select', $ir_to_select);
        $this->assign('locations_list', $locations_list);
        $this->assign('interventions', $interventions);
        $this->assign('filter', $filter);
        $this->assign('totals', $totals);
        $this->assign('do_filter', $this->vars['do_filter']);
        $this->assign('pages', $pages);
        $this->assign('tot_interventions', $tot_interventions);
        $this->assign('customers_list', $customers_list);
        $this->assign('users_list', $users_list);
        $this->assign('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
        $this->assign('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
        $this->assign('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('intervention_approval_console_submit');
        $this->display($tpl);
    }

    function intervention_approval_console_submit()
    {
        check_auth ();
        class_load('InterventionReport');
        $params = array ('do_filter'=>1);
        $ret = $this->mk_redir ('intervention_approval_console', $params);
        $filter = $this->vars['filter'];

        if ($this->vars['go'] == 'prev') $filter['start'] = $filter['start'] - $filter['limit'];
        elseif ($this->vars['go'] == 'next') $filter['start'] = $filter['start'] + $filter['limit'];

        if(isset($_SESSION['intervention_approval_console'])) unset($_SESSION['intervention_approval_console']);
        $_SESSION['intervention_approval_console'] = $filter;
        $intervention_id = $this->vars['radio_irshow'];
        $_SESSION['intervention_approval_console']['sel_ir'] = $intervention_id;

        if($this->vars['make_non_billable'])
        {
            $intervention = new InterventionReport($intervention_id);
            if (!$intervention->id) {error_msg("Could not load intervention report"); return $ret;}
            foreach($intervention->lines as $line)
            {
                if(!$line->action_type->special_type){
                    $line->tbb_amount = 0;
                    $line->save_data();
                }
            }
            $ret = $this->mk_redir ('intervention_approval_console');
        }

        if($this->vars['adjust_tbb'])
        {
            $intervention = new InterventionReport($intervention_id);
            if (!$intervention->id) {error_msg("Could not load intervention report"); return $ret;}
            $work_time = 0;
            foreach($intervention->lines as $line)
            {
                $work_time += $line->work_time;
            }

            $hours = intval($work_time/60);
            $minutes = $work_time%60;
            //first if the see if we are under the 15 minutes rule
            if($hours ==0 and $minutes<=15)
            {
                foreach($intervention->lines as $line)
                {
                    $line->tbb_amount = 0;
                    $line->save_data();
                }
            }
            //otherwise let's round up till we get a correct number of hours
            else
            {
                $target_tbb = ($minutes > 0) ? $hours+1 : $hours;                    
                $tbb = 0;
                foreach($intervention->lines as $line)
                {
                    if(!$line->action_type->special_type){
                        $h = intval($line->work_time/60);
                        $m = $line->work_time%60;
                        if($tbb <= $target_tbb)
                        {
                            if($m>0) $h++;

                            $line->tbb_amount = $h*60;

                            $s_trg = $tbb;
                            $tbb += $h;

                            if($tbb > $target_tbb)
                            {
                                $ltb  = $target_tbb - $s_trg;
                                $line->tbb_amount = $ltb*60;
                            }
                            $line->save_data();

                        }
                        else
                        {
                            if($taget_tbb > $tbb)
                            {
                                $ltb  =$target_tbb - $h;
                                $line->tbb_amount = $ltb*60;
                                $line->save_data();
                            }
                            else
                            {
                                $line->tbb_amount = 0;
                                $line->save_data();
                            }

                        }
                    }
                    else{
                        if($line->tbb_amount != $line->bill_amount){
                            $line->tbb_amount = $line->bill_amount;
                            $line->save_data();
                        }
                    }
                }
            }
            $ret = $this->mk_redir ('intervention_approval_console');

        }

        if(isset($this->vars['bulk_approve']) and $this->vars['bulk_approve'])
        {
            $sel_ir_approve = $this->vars['appr_sel'];
            foreach($sel_ir_approve as $irid)
            {
                $ir = new InterventionReport($irid);
                if($ir->id)
                {
                    $ir->approve_intervention_report (get_uid());
                }
            }
        }

        return $ret;
    }

    function tickets_stats(){
        check_auth();
        $tpl = 'tickets_stats.tpl';

        class_load('Customer');
        class_load('User');
        $customers_list = Customer::get_customers_list(array('favorites_first' => $this->current_user->id, 'show_ids' => 1));
        $users_list = User::get_users_list(array('customer_id'=>0));
        $nodays = 15;      
        $sel_cust = 0;   
        $nodays1 = 15;      
        $sel_cust1 = 0;  
        $user_sel1=0;
        if(isset($_SESSION['tickets_stats']['nodays'])){
            $nodays = $_SESSION['tickets_stats']['nodays'];
            $sel_cust = $_SESSION['tickets_stats']['customer'];              
        }
        if(isset($_SESSION['tickets_stats']['nodays1'])){
            $nodays1 = $_SESSION['tickets_stats']['nodays1'];
            $sel_cust1 = $_SESSION['tickets_stats']['customer1'];  
            $user_sel1 = $_SESSION['tickets_stats']['user1'];               
        }
        if(isset($_SESSION['tickets_stats'])){
             unset($_SESSION['tickets_stats']);
        }

        $tickets_stats = Ticket::get_lm_tickets_evo($sel_cust, $nodays);

        $this->assign('tickets_lmevo_days', json_encode($tickets_stats['days']));
        $this->assign('tickets_lmevo_closed', json_encode($tickets_stats['closed']));
        $this->assign('tickets_lmevo_notclosed', json_encode($tickets_stats['not_closed']));
        $this->assign('tickets_lmevo_new', json_encode($tickets_stats['new']));

        $ut_activity = Ticket::get_user_tickets_activity($nodays1, $sel_cust1,$user_sel1);
        $this->assign('tickets_utevo_days', json_encode($ut_activity['days']));
        $this->assign('tickets_utevo_details', json_encode($ut_activity['details']));
        $this->assign('tickets_utevo_tdbill', json_encode($ut_activity['td_bill_time']));
        $this->assign('tickets_utevo_irbill', json_encode($ut_activity['ir_bill_time']));

        $this->assign('sel_cust', $sel_cust);
        $this->assign('sel_cust1', $sel_cust1); 
        $this->assign('customers_list', $customers_list);
        $this->assign('user_sel1', $user_sel1);
        $this->assign('users_list', $users_list);
        $this->assign('nodays', $nodays);     
        $this->assign('nodays1', $nodays1);       
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('tickets_stats_submit');
        $this->display($tpl);
    }
    function tickets_stats_submit(){
        check_auth();
        $ret = $this->mk_redir('tickets_stats');            
        if(isset($this->vars['nodays']) and $this->vars['nodays'] > 0){               
            $_SESSION['tickets_stats']['nodays'] = $this->vars['nodays'];
            $_SESSION['tickets_stats']['customer'] = $this->vars['customer'];
        }
        if(isset($this->vars['nodays1']) and $this->vars['nodays1'] > 0){               
            $_SESSION['tickets_stats']['nodays1'] = $this->vars['nodays1'];
            $_SESSION['tickets_stats']['customer1'] = $this->vars['customer1'];  
            $_SESSION['tickets_stats']['user1'] = $this->vars['user1'];
        }
        return $ret;
    }

    function work_time_stats(){
        check_auth();
        $tpl = "work_time_stats.tpl";

        class_load('Customer');
        class_load('User');
        $customers_list = Customer::get_customers_list(array('favorites_first' => $this->current_user->id, 'show_ids' => 1));
        $users_list = User::get_users_list(array('customer_id'=>0, 'type'=>USER_TYPE_KEYSOURCE));

        $end_date = time();
        $ed = getdate($end_date);
        $start_date = mktime(0,0,0,$ed['mon'], $ed['mday']-15, $ed['year']);
        $selected_customer = 0;
        $selected_user = 0;

        //debug($_SESSION['work_time_stats']);

        if(isset($_SESSION['work_time_stats'])){
            if(isset($_SESSION['work_time_stats']['repStartDate']) and
                is_numeric($_SESSION['work_time_stats']['repStartDate']) and
                $_SESSION['work_time_stats']['repStartDate'] > 0){
                    $start_date = $_SESSION['work_time_stats']['repStartDate'];
                }
            if(isset($_SESSION['work_time_stats']['repEndDate']) and
                is_numeric($_SESSION['work_time_stats']['repEndDate']) and
                $_SESSION['work_time_stats']['repEndDate'] > 0){
                    $end_date = $_SESSION['work_time_stats']['repEndDate'];
                }
            if(isset($_SESSION['work_time_stats']['customer']) and
                is_numeric($_SESSION['work_time_stats']['customer']) and
                $_SESSION['work_time_stats']['customer'] > 0){
                    $selected_customer = $_SESSION['work_time_stats']['customer'];
            }
            if(isset($_SESSION['work_time_stats']['user']) and
                is_numeric($_SESSION['work_time_stats']['user']) and
                $_SESSION['work_time_stats']['user'] > 0){
                    $selected_user = $_SESSION['work_time_stats']['user'];
            }
            unset($_SESSION['work_time_stats']);
        }


        $work_time_stats = Ticket::get_work_time_stats($start_date, $end_date, $selected_customer, $selected_user);

        $this->assign('wt_days', json_encode($work_time_stats['days']));
        $this->assign('wt_totbill', json_encode($work_time_stats['td_bill_time']));
        $this->assign('wt_irbill', json_encode($work_time_stats['td_bill_time_ir']));
        $this->assign('wt_nirbill', json_encode($work_time_stats['td_bill_time_nir']));


        $this->assign('start_date', date("d/m/Y", $start_date));
        $this->assign('end_date', date("d/m/Y", $end_date));                  
        $this->assign('selected_user', $selected_user);
        $this->assign('selected_customer', $selected_customer);
        $this->assign('customers_list', $customers_list);
        $this->assign('users_list', $users_list);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('work_time_stats_submit');
        $this->display($tpl);

    }
    function work_time_stats_submit(){
        check_auth();

        $ret = $this->mk_redir('work_time_stats');

        //debug($this->vars);
        $repStartDate = $this->vars['reportStartDate'];
        $repEndDate = $this->vars['reportEndDate'];
        list($day, $month, $year) = explode('/', $repStartDate);
        $_SESSION['work_time_stats']['repStartDate'] = mktime(0,0,0,$month, $day, $year);
        list($day, $month, $year) = explode('/', $repEndDate);
        $_SESSION['work_time_stats']['repEndDate'] = mktime(0,0,0,$month, $day, $year);

        $_SESSION['work_time_stats']['customer'] = $this->vars['customer'];
        $_SESSION['work_time_stats']['user'] = $this->vars['user'];
        return $ret;
    }

    function manage_support_emails() {
        check_auth();
        class_load('ImapSettings');

        $ims = new ImapSettings();
        $ims = $ims->get_all();

        $this->assign('ims', $ims);
        $tpl = 'manage_support_emails.tpl';
        $this->display($tpl);
    }

    function add_support_email() {
        check_auth();
        $tpl = 'add_support_email.tpl';

        $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
        $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
        $users = $users + $groups;

        $this->assign('users',$users);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('add_support_email_submit');
        $this->display($tpl);
    }

    function add_support_email_submit() {
        check_auth();

        if($this->vars['save']) {
            $imapset = $this->vars['imapsettings'];
            class_load('ImapSettings');
            $imapsettings = new ImapSettings();
            $imapsettings->load_from_array($imapset);

            if($imapsettings->is_valid_data()) {
                $imapsettings->save_data();

                return $this->mk_redir('edit_support_email', array('id' => $imapsettings->id));
            } else {
                return $this->mk_redir('add_support_email');
            }
        }
        return $this->mk_redir('manage_support_emails');

    }

    function delete_support_email() {
        check_auth();

        if (!empty($this->vars['id'])) {
            class_load('ImapSettings');
            $ims = new ImapSettings($this->vars['id']);
            $ims->delete();
        }

        return $this->mk_redir('manage_support_emails');
    }

    function edit_support_email() {
        check_auth();

        if (!empty($this->vars['id'])) {
            class_load('ImapSettings');
            $ims = new ImapSettings($this->vars['id']);
            if($ims->id) {
                $users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
                $groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
                $users = $users + $groups;

                $this->assign('users',$users);

                $this->assign('imapsettings', $ims);
                $this->assign('error_msg', error_msg());
                $this->set_form_redir('edit_support_email_submit');
                $this->display('edit_support_email.tpl');
            } else {
                return $this->mk_redir('manage_support_emails');
            }
        } else {
            return $this->mk_redir('manage_support_emails');
        }
    }

    function edit_support_email_submit() {
        check_auth();

        if($this->vars['save'] && !empty($this->vars['id'])) {
            $imapset = $this->vars['imapsettings'];
            class_load('ImapSettings');
            $imapsettings = new ImapSettings($this->vars['id']);
            $imapsettings->load_from_array($imapset);

            if($imapsettings->is_valid_data()) {
                $imapsettings->save_data();
            }

            return $this->mk_redir('edit_support_email', array('id' => $imapsettings->id));
        } else {
            return $this->mk_redir('manage_support_emails');
        }

    }

    /****************************************************************/
    /* Tickets management - create new ones from support emails     */
    /****************************************************************/

    /** Shows the page with the list of current tickets */
    function tickets_from_emails ()
    {
        check_auth();

        class_load('ImapSettings');
        class_load('ImapConnector');

        $ims = new ImapSettings();
        $ret = $ims->get_all();

        $i = 0;
        $confs = null;
        foreach($ret as $r) {
            if(is_object($r)) {
                foreach($r as $k => $v) {
                    $confs[$i][$k] = $v;
                }
                $i++;
            }
        }

        $i = 0;
        $tickets = null;

        $query = 'UNSEEN';
        $this->assign('tab', 'unseen');
        $this->assign('filter', 'last 2 days');
        if(isset($this->vars['filter']) and ($this->vars['filter'] == 'last_2_days')) {
            $this->assign('tab', 'last_2_days');
            $yesterday = date("d-M-Y", strtotime ("-2 days"));
            $query = 'SINCE "'.$yesterday.'"';
            if(!empty($this->vars['from_date'])) {
                $this->assign('from_date', $this->vars['from_date']);
                $this->assign('filter', $this->vars['from_date']);
                $this->vars['from_date'] = str_replace('/', '-', $this->vars['from_date']);
                $from_date = strtotime($this->vars['from_date']);
                $from_date = date('d-M-Y', $from_date);
                $query = 'SINCE "' . $from_date . '"';
            }
        }

        if(is_array($confs)) {
            foreach($confs as $conf) {
                if(is_array($conf)) {
                    $imap = new ImapConnector($conf);
                    if($imap->connected()) {
                        $imap->fetch_msg_numbers($query);
                        if($imap->has_mail()) {
                            while(($mail = $imap->fetch_mail())) {

                                $customerEmail = $mail->header->from[0]->mailbox . '@' . $mail->header->from[0]->host;

                                class_load('User');

                                $user = new User();
                                $user = $user->get_user_by_email($customerEmail);

                                $body = $mail->body;
                                if($mail->mimetype == 'TEXT/PLAIN') {
                                    $body = nl2br($body);
                                }

                                $ticket_data['customer_id'] = $user->customer_id;
                                $ticket_data['subject'] = $mail->header->subject;
                                $ticket_data['status'] = 1;
                                $ticket_data['source'] = 16;
                                $ticket_data['type'] = 2;
                                $ticket_data['priority'] = 20;
                                $ticket_data['assigned_id'] = $conf['assigned_user_id'];
                                $ticket_data['owner_id'] = $user->id;
                                $ticket_data['private'] = 0;
                                $ticket_data['billable'] = 1;
                                $ticket_data['deadline'] = '';
                                $ticket_data['user_id'] = $user->id;
                                $ticket_data['created'] = time();
                                $ticket_data['last_modified'] = time ();
                                $ticket_data['status'] = TICKET_STATUS_NEW;
                                $ticket_detail_data['user_id'] = $user->id;
                                $ticket_detail_data['assigned_id'] = $conf['assigned_user_id'];
                                $ticket_detail_data['created'] = time();

                                $ticket_detail_data['comments'] = $body;

                                $cc_emails = $customerEmail;
                                if (!empty($mail->header->cc) and is_array($mail->header->cc)) {
                                    foreach($mail->header->cc as $cc) {
                                        $cc_emails .=  ', ' . $cc->mailbox . '@' . $cc->host;
                                    }
                                }

                                class_load('Ticket');
                                $ticket = new Ticket ();
                                $ticket->load_from_array($ticket_data);

                                $u = new User($conf['assigned_user_id']);
                                if ($ticket->is_valid_data()) {
                                    $tickets[$i]['subject'] = $mail->header->subject;
                                    $c = new Customer($user->customer_id);
                                    $tickets[$i]['customer'] = $c->name . ' (' . $c->id . ')';
                                    $tickets[$i]['assigned'] = $u->get_short_name();
                                    $tickets[$i]['mail_date'] = date('d-M-Y H:i:s',strtotime($mail->header->date));
                                    $tickets[$i]['mail'] = $conf['username'];
                                    $tickets[$i]['cc_emails'] = $cc_emails;
                                    $tickets[$i]['msgno'] = $imap->get_msg_number();
                                    $tickets[$i]['customer_id'] = $c->id;
                                    $tickets[$i]['body'] = $body;
                                } else {
                                    $tickets[$i]['subject'] = $mail->header->subject;
                                    $tickets[$i]['customer'] = 'None';
                                    $tickets[$i]['mail_date'] = date('d-M-Y H:i:s',strtotime($mail->header->date));
                                    $tickets[$i]['assigned'] = $u->get_short_name();
                                    $tickets[$i]['mail'] = $conf['username'];
                                    $tickets[$i]['cc_emails'] = $cc_emails;
                                    $tickets[$i]['msgno'] = $imap->get_msg_number();
                                    $tickets[$i]['customer_id'] = 0;
                                    $tickets[$i]['body'] = $body;
                                }
                                $i++;
                                error_msg();
                            }
                        }

                        $imap->disconnect();
                    }
                }
            }
        }

        $this->assign('tickets', $tickets);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('tickets_from_emails_submit');
        $tpl = 'tickets_from_emails.tpl';
        $this->display ($tpl);
    }

    function tickets_from_emails_submit() {
        check_auth();

        if(!empty($this->vars['msgnos']) and is_array($this->vars['msgnos'])) {
            $msgnos = $this->vars['msgnos'];
        } else {
            return $this->mk_redir('tickets_from_emails');
        }

        class_load('ImapConnector');
        class_load('ImapSettings');

        $ims = new ImapSettings();
        $ret = $ims->get_all();

        $i = 0;
        $confs = null;
        foreach($ret as $r) {
            if(is_object($r)) {
                foreach($r as $k => $v) {
                    $confs[$i][$k] = $v;
                }
                $i++;
            }
        }

        if(is_array($confs)) {
            foreach($confs as $conf) {
                if(is_array($conf)) {
                    $imap = new ImapConnector($conf);
                    if($imap->connected()) {
                        foreach($msgnos as $msgno) {
                            if(($mail = $imap->fetch_mail($msgno))) {
                                if($this->vars['mark_as_read']) {
                                    $imap->set_mail_seen($msgno);
                                    continue;
                                }

                                $customerEmail = $mail->header->from[0]->mailbox . '@' . $mail->header->from[0]->host;

                                class_load('User');

                                $user = new User();
                                $user = $user->get_user_by_email($customerEmail);

                                if (!empty($user->customer_id)) {

                                    $body = $mail->body;
                                    if($mail->mimetype == 'TEXT/PLAIN') {
                                        $body = nl2br($body);
                                    }

                                    $ticket_data['customer_id'] = $user->customer_id;
                                    $ticket_data['subject'] = $mail->header->subject;
                                    $ticket_data['status'] = 1;
                                    $ticket_data['source'] = 16;
                                    $ticket_data['type'] = 2;
                                    $ticket_data['priority'] = 20;
                                    $ticket_data['assigned_id'] = $conf['assigned_user_id'];
                                    $ticket_data['owner_id'] = $user->id;
                                    $ticket_data['private'] = 0;
                                    $ticket_data['billable'] = 1;
                                    $ticket_data['deadline'] = '';
                                    $ticket_data['user_id'] = $user->id;
                                    $ticket_data['created'] = time();
                                    $ticket_data['last_modified'] = time ();
                                    $ticket_data['status'] = TICKET_STATUS_NEW;
                                    $ticket_detail_data['user_id'] = $user->id;
                                    $ticket_detail_data['assigned_id'] = $conf['assigned_user_id'];
                                    $ticket_detail_data['created'] = time();

                                    $ticket_detail_data['comments'] = $body;

                                    $cc_emails[0] = $customerEmail;

                                    class_load('Ticket');
                                    $ticket = new Ticket ();
                                    $ticket->load_from_array($ticket_data);

                                    if ($ticket->is_valid_data()) {
                                        $ticket->save_data(); 

                                        $td_comments = $ticket_detail_data['comments'];
                                        $ticket_detail_data['comments'] = $td_comments;

                                        class_load('TicketDetail');
                                        $ticket_detail = new TicketDetail ();
                                        $ticket_detail->load_from_array($ticket_detail_data);
                                        $ticket_detail->ticket_id = $ticket->id;
                                        $ticket_detail->status = $ticket->status;

                                        $ticket_detail->save_data(); //---TODO: Uncomment so save can take place

                                        if (is_array($cc_emails) and count($cc_emails) > 0) {
                                            $ticket->cc_manual_list = array();
                                            foreach ($cc_emails as $eml)
                                                $ticket->cc_manual_list[] = $eml;
                                            $ticket->save_data(); //---TODO: Uncomment so save can take place
                                        }

                                        $_SESSION['new_email_tickets'][] = $ticket->id;

                                        $imap->set_mail_seen($msgno);
                                    }
                                    error_msg();
                                }
                            }
                        }

                        $imap->disconnect();
                        unset($imap);
                    }
                }
            }
        }
        return $this->mk_redir('tickets_from_emails');
    }

    function mark_email_as_read() {
        check_auth();


        if (!empty($this->vars['msgno'])) {
            $msgno = $this->vars['msgno'];
            class_load('ImapConnector');
            class_load('ImapSettings');

            $ims = new ImapSettings();
            $ret = $ims->get_all();

            $i = 0;
            $confs = null;
            foreach($ret as $r) {
                if(is_object($r)) {
                    foreach($r as $k => $v) {
                        $confs[$i][$k] = $v;
                    }
                    $i++;
                }
            }

            if(is_array($confs)) {
                foreach($confs as $conf) {
                    if(is_array($conf)) {
                        $imap = new ImapConnector($conf);
                        if($imap->connected()) {
                            $imap->set_mail_seen($msgno);
                        }
                    }
                }
            }
        }
        return $this->mk_redir('tickets_from_emails');
    }
}
?>

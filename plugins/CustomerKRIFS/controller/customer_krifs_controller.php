<?php
class_load ('Ticket');
class_load ('TicketDetail');
class_load ('Computer');
class_load ('Customer');
class_load ('User');
class_load ('Group');
class_load ('Activity');
class_load ('ActionType');
class_load ('InterventionReport');

class CustomerKrifsController extends PluginController{
    protected $plugin_name = 'CustomerKRIFS';
    public function __contstruct(){
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    /****************************************************************/
    /* Tickets management						*/
    /****************************************************************/

    /** Shows the page with the list of current tickets */
    function manage_tickets ()
    {
        //// If a Keysource user has arrived here, send it to the keysourcer-specific ticket
        $uid = get_uid ();
        if ($uid)
        {
                $user = New User ($uid);
                if (!$user->customer_id)
                {
                        return $this->mk_redir ('manage_tickets', array(), 'krifs');
                }
        }

        check_auth ();
        $tpl = 'manage_tickets.tpl';
        $filter = $_SESSION['tickets_customer']['filter'];

        if (!isset ($filter['order_by'])) $filter['order_by'] = 'last_modified';
        if (!isset ($filter['order_dir'])) $filter['order_dir'] = 'DESC';
        //if (!isset ($filter['user_id'])) $filter['user_id'] = get_uid ();
        if (!isset ($filter['start'])) $filter['start'] = 0;
        if (!isset ($filter['limit'])) $filter['limit'] = 10;
        if (!isset ($filter['status'])) $filter['status'] = -1;
        if (!isset ($filter['customer'])) $filter['customer'] = -1;
        $filter['view'] = 1;

        // For customer tickets, force the displaying only of the public tickets for this customer
        $current_user = new User (get_uid());
        $filter['customer_id'] = $current_user->customer_id;
        $filter['customer_ids'] = $current_user->get_users_customer_list();
        $tot_cust = count($filter['customer_ids']);
        if($tot_cust == 0) $filter['customer_ids'] = $filter['customer_id'];

        //
        //XXXX I think that in this place we should enable more than one customer tickets for one user
        //

        if (!$current_user->allow_private) $filter['private'] = 0;

        $tot_tickets = 0;
        $tickets = Ticket::get_tickets ($filter, $tot_tickets);
        if ($tot_tickets < $filter['start'])
        {
                $filter['start'] = 0;
                $_SESSION['tickets_customer']['filter']['start'] = 0;
                $tickets = Ticket::get_tickets ($filter, $tot_tickets);
        }

        $customers_list = Customer::get_customers_list ();

        $pages = make_paging ($filter['limit'], $tot_tickets);

        $tc_uc = $user->get_users_customer_list();
        $tc_ulist = array();
        foreach($tc_uc as $cid){
            $tc_ulist[$cid] = $customers_list[$cid];
        }

        //debug($tickets);
        //debug($filter);   
        $filtered_tickets = array();
        if($filter['customer'] > 0){
            //debug($tickets);
            foreach($tickets as $t){
                if($t->customer_id == $filter['customer']){
                    $filtered_tickets[] = $t;
                }
            }
        } else {
            $filtered_tickets = $tickets;
        }
        $this->assign ('tc_ulist', $tc_ulist);
        //$this->assign ('tickets', $tickets);
        $this->assign ('tickets', $filtered_tickets);
        $this->assign ('tot_tickets', $tot_tickets);
        $this->assign ('tot_cust', $tot_cust);
        $this->assign ('pages', $pages);
        $this->assign ('self_uid', get_uid());
        $this->assign ('filter', $filter);
        $this->assign ('sort_url', $this->mk_redir ('manage_tickets_submit'));
        $this->assign ('customers_list', $customers_list);
        $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
        $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
        $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
        $this->assign ('TICKETS_PRIORITIES_COLORS', $GLOBALS ['TICKETS_PRIORITIES_COLORS']);
        $this->set_form_redir ('manage_tickets_submit');
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }


    /** Sets the filtering criteria for the list of computers */
    function manage_tickets_submit ()
    {
            check_auth ();

            if ($this->vars['order_by'] and $this->vars['order_dir'])
            {
                    $_SESSION['tickets_customer']['filter']['order_by'] = $this->vars['order_by'];
                    $_SESSION['tickets_customer']['filter']['order_dir'] = $this->vars['order_dir'];
            }
            else
            {
                    if ($this->vars['go'] == 'prev' or $this->vars['go'] == 'next')
                    {
                            $this->vars['filter']['start']+= $this->vars['filter']['limit'] * ($this->vars['go'] == 'prev' ? -1 : 1);
                    }

                    if (is_array($_SESSION['tickets_customer']['filter']))
                    {
                            $_SESSION['tickets_customer']['filter'] = array_merge($_SESSION['tickets_customer']['filter'], $this->vars['filter']);
                    }
                    else
                    {
                            $_SESSION['tickets_customer']['filter'] = $this->vars['filter'];
                    }
            }


            return $this->mk_redir('manage_tickets');
    }
    
    function manage_interventions()
    {
        // if a Keysource user is here, redirect it to the keysource view of the intermentions
        $uid = get_uid();
        if($uid)
        {
                $user = New User($uid);
                if(!$user->customer_id)
                {
                        return $this->mk_redir('manage_interventions', array(), 'krifs');
                }
        }
        check_auth();
        $tpl = 'manage_interventions.tpl';
        $filter = $_SESSION['interventions_customer']['filter'];

        if (!isset ($filter['order_by'])) $filter['order_by'] = 'last_modified';
        if (!isset ($filter['order_dir'])) $filter['order_dir'] = 'DESC';
        //if (!isset ($filter['user_id'])) $filter['user_id'] = get_uid ();
        if (!isset ($filter['start'])) $filter['start'] = 0;
        if (!isset ($filter['limit'])) $filter['limit'] = 10;
        //if (!isset ($filter['status'])) $filter['status'] = -1;
        $filter['view'] = 1;


        $current_user = new User (get_uid());
        $filter['customer_id'] = $current_user->customer_id;
        $filter['customer_ids'] = $current_user->get_users_customer_list();
        $tot_cust = count($filter['customer_ids']);
        if($tot_cust == 0) $filter['customer_ids'] = $filter['customer_id'];
        if (!$current_user->allow_private) $filter['private'] = 0;

        $cust = new Customer($current_user->customer_id);
        $tot_interventions = 0;
        $interventions = InterventionReport::get_interventions ($cust->account_manager,$filter, $tot_interventions);
        $pages = make_paging ($filter['limit'], $tot_interventions);
        if ($filter['start'] > $tot_interventions)
        {
                $filter['start'] = 0;
                $_SESSION['interventions_customer']['filter']['start'] = 0;
                $interventions = InterventionReport::get_interventions ($cust->account_manager, $filter, $tot_interventions);
        }

        // For each intervention report, load also the associated tickets
        for ($i=0; $i<count($interventions); $i++) $interventions[$i]->load_tickets ();

        // Get the statuses of the intervention reports
        $totals = InterventionReport::get_totals ();

        $customers_list = Customer::get_customers_list ();

        $pages = make_paging ($filter['limit'], $tot_interventions);

        $this->assign ('tot_cust', $tot_cust);
        $this->assign ('interventions', $interventions);
        $this->assign ('totals', $totals);
        $this->assign ('tot_interventions', $tot_interventions);
        $this->assign ('pages', $pages);
        $this->assign ('self_uid', get_uid());
        $this->assign ('filter', $filter);
        $this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
        $this->assign ('sort_url', $this->mk_redir ('manage_tickets_submit'));
        $this->assign ('customers_list', $customers_list);


        $this->set_form_redir ('manage_interventions_submit');
        $this->assign ('error_msg', error_msg());

        $this->display ($tpl);
    }

    function manage_interventions_submit()
    {
        check_auth();
        if($this->vars['order_by'] and $this->vars['order_dir'])
        {
                $_SESSION['interventions_customer']['filter']['order_by'] = $this->vars['order_by'];
                $_SESSION['interventions_customer']['filter']['order_dir'] = $this->vars['order_dir'];
        }
        else
        {
                if($this->vars['go'] == 'prev' || $this->vars['go'] == 'next')
                {
                        $this->vars['filter']['start'] += $this->vars['filter']['limit'] * ($this->vars['go']=='prev' ? -1 : 1);
                }
                if(is_array($_SESSION['interventions_customer']['filter']))
                {
                        $_SESSION['interventions_customer']['filter'] = array_merge($_SESSION['interventions_customer']['filter'], $this->vars['filter']);
                }
                else
                {
                        $_SESSION['interventions_customer']['filter'] = $this->vars['filter'];
                }
        }
        return $this->mk_redir('manage_interventions');
    }
	
    /** Displays the page for creating a new ticket */
    function ticket_add ()
    {        
        // If a Keysource user has arrived here, send it to the keysourcer-specific ticket
        $uid = get_uid ();
        if ($uid)
        {
            $user = New User ($uid);
            if (!$user->customer_id)
            {
                return $this->mk_redir ('ticket_add', array(), 'krifs');
            }
        }
        
        check_auth ();
        $tpl = 'ticket_add.tpl';

        $ticket = new Ticket ();
        $ticket_detail = new TicketDetail ();

        if (!empty_error_msg())
        {
            $ticket->load_from_array (restore_form_data ('ticket_data', false, $ticket_data));
            $ticket_detail->load_from_array (restore_form_data ('ticket_detail_data', false, $ticket_detail_data));
        }

        $customers = Customer::get_customers_list ();
        $assigned_customers = $user->get_users_customer_list();
        $assigned_customers_count = count($assigned_customers);
        $customers_list = array();
        foreach ($assigned_customers as $ac)
        {
            $customers_list[$ac] = $customers[$ac];
        }
        $action_types = ActionType::get_list ();        
        // If no user was specified, set to current user
        if (!$ticket->owner_id) $ticket->owner_id = get_uid ();
        if (!$ticket->assigned_id) $ticket->assigned_id = get_uid ();
        if (!$ticket_detail->user_id) $ticket_detail->user_id = get_uid ();

        $this->assign ('ticket', $ticket);
        $this->assign ('ticket_detail', $ticket_detail);
        $this->assign ('customers', $customers);
        $this->assign ('customers_list', $customers_list);
        $this->assign ('assigned_customers', $assigned_customers);
        $this->assign ('assigned_customers_count', $assigned_customers_count);
        $this->assign ('action_types', $action_types);
        $this->assign ('TICKET_SOURCES', $GLOBALS ['TICKET_SOURCES']);
        $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
        $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
        $this->assign ('error_msg', error_msg ());
        $this->set_form_redir ('ticket_add_submit');

        $this->display ($tpl);
    }


    /** Processes the request to create a new ticket */
    function ticket_add_submit ()
    {
        check_auth ();
        class_load ('CustomerCCRecipient');
        $ret = $this->mk_redir ('manage_tickets');

        if ($this->vars['save'])
        {
            $user = new User (get_uid());
            $customer = new Customer ($user->customer_id);

            $ticket_data = $this->vars['ticket'];
            $ticket_detail_data = $this->vars['ticket_detail'];

            $ticket_data['user_id'] = get_uid();
            if(!$ticket_data['customer_id']) $ticket_data['customer_id'] = $customer->id;
            $ticket_data['created'] = time ();
            $ticket_data['last_modified'] = time ();
            $ticket_data['status'] = TICKET_STATUS_NEW;
            $ticket_data['private'] = 0;
            if ($ticket_data['deadline']) $ticket_data['deadline'] = js_strtotime ($ticket_data['deadline']);
            $ticket_data['type'] = Ticket::get_default_customer_ticket_type ();

            $ticket_detail_data['user_id'] = get_uid ();
            $ticket_detail_data['created'] = time ();
            $ticket_detail_data['private'] = false;

            $ticket = new Ticket ();
            $ticket->load_from_array ($ticket_data);

            // The owner and assignee will be the default user specified as being the default recipient of Krifs notification
            // We don't care here if the intended owner was "Away", since get_default_cc_list() will take it into account anyway
            $ticket->owner_id = $ticket->get_default_owner ($none);
            $ticket->assigned_id = $ticket->owner_id;
            $ticket->cc_list = $ticket->get_default_cc_list ();

            $ticket_detail_data['assigned_id'] = $ticket->assigned_id;
            
            if ($ticket->is_valid_data())
            {
                $ticket->save_data ();
                $ticket->log_action ($this->current_user->id, TICKET_ACCESS_CREATE);

                $ticket_detail = new TicketDetail ();
                $ticket_detail->load_from_array ($ticket_detail_data);
                $ticket_detail->ticket_id = $ticket->id;
                $ticket_detail->status = $ticket->status;
                $ticket_detail->save_data ();

                // Set the default CC recipients, if any are defined for this customer
                $cc_recipients = CustomerCCRecipient::get_cc_recipients_ids ($ticket->customer_id);
                if (count($cc_recipients) > 0)
                {
                    foreach ($cc_recipients as $cc_recipient_id)
                    {
                        if (!in_array($cc_recipient_id, $ticket->cc_list)) $ticket->cc_list[] = $cc_recipient_id;
                    }
                    $ticket->save_data ();
                }

                // Reload the ticket data, to ensure consistency, and dispatch the notifications
                $ticket->load_data ();
                $ticket->dispatch_notifications (TICKET_NOTIF_TYPE_NEW, get_uid());

                $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));
            }
            else
            {
                save_form_data ($ticket_data, 'ticket_data');
                save_form_data ($ticket_detail_data, 'ticket_detail_data');

                $ret = $this->mk_redir ('ticket_add');
            }
        }

        return $ret;
    }
    
    /** Displays a pave for viewing/editing a ticket */
    function ticket_edit ()
    {
        // If a Keysource user has arrived here, send it to the keysourcer-specific ticket
        $uid = get_uid ();
        if ($uid)
        {
            $user = New User ($uid);
            if (!$user->customer_id)
            {
                return $this->mk_redir ('ticket_edit', array('id' => $this->vars['id']), 'krifs');
            }
        }

        check_auth ();
        $tpl = 'ticket_edit.tpl';

        $ticket = new Ticket ($this->vars['id']);
        if (!$ticket->id) return $this->mk_redir ('manage_tickets');
        $ticket->log_action ($this->current_user->id, TICKET_ACCESS_READ);

        // Mark that the current user has read the ticket
        $ticket->mark_read (get_uid());
        $action_types = ActionType::get_list ();
        $ticket_detail = new TicketDetail ();
        
        // Initialize the ticket detail to display the same 'Reassign to' value as the parent ticket
        $ticket_detail->assigned_id = $ticket->assigned_id;
        $c_list_all = $user->get_cusers_list();                
        $c_list[$ticket->get_last_ks_handler()] = "[Keysource Support]";
        
        foreach($c_list_all[$ticket->customer_id] as $k=>$v){
            $c_list[$k] = $v;                    
        }

        $this->assign ('c_list', $c_list);
        $this->assign ('user', $user);
        $this->assign ('ticket', $ticket);
        $this->assign ('ticket_detail', $ticket_detail);
        $this->assign ('action_types', $action_types);

        $this->assign ('TICKET_SOURCES', $GLOBALS ['TICKET_SOURCES']);
        $this->assign ('TICKET_PRIORITIES', $GLOBALS ['TICKET_PRIORITIES']);
        $this->assign ('TICKET_TYPES', $GLOBALS ['TICKET_TYPES']);
        $this->assign ('TICKET_STATUSES', $GLOBALS ['TICKET_STATUSES']);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('ticket_edit_submit', array ('ticket[id]' => $ticket->id));

        $this->display ($tpl);
    }


    /** Processes the request to edit a ticket and/or add a new entry */
    function ticket_edit_submit ()
    {
        check_auth ();
        $ret = $this->mk_redir ('manage_tickets');
        $ticket = new Ticket ($this->vars['ticket']['id']);
        
        // Make sure the ticket belongs to this user
        $user = new User (get_uid());
        $cust_list = array_keys($user->get_assigned_customers_list());
        
        $customer = new Customer ($user->customer_id);
        if (!in_array($ticket->customer_id, $cust_list)) {         
            return $this->mk_redir ('manage_tickets');
        }

        if ($this->vars['escalate'] and $ticket->id)
        {
            // This is an escalation request
            $ret = $this->mk_redir ('ticket_escalate', array ('id' => $ticket->id));
        }
        elseif (($this->vars['save'] or $this->vars['add_entry'] or $this->vars['mark_closed'] or $this->vars['mark_reopen']) and $ticket->id)
        {
            // Update ticket information
            $ticket_data = $this->vars['ticket'];
            $ticket_detail_data = $this->vars['ticket_detail'];

            $ticket_data['last_modified'] = time();
            if ($ticket_data['deadline']) $ticket_data['deadline'] = js_strtotime ($ticket_data['deadline']);

            if ($this->vars['mark_closed']) $ticket_data['status'] = TICKET_STATUS_CLOSED;
            elseif ($this->vars['mark_reopen'])
            {
                $ticket_data['status'] = TICKET_STATUS_ASSIGNED;
                // For re-opened tickets, add a text comment if no comments were entered by user,
                // for clarity. Also make sure such entry is public
                if (empty($ticket_detail_data['comments']))
                {
                    $ticket_detail_data['comments'] = 'Ticket re-opened';
                    $ticket_detail_data['private'] = 0;
                }
            }

            // If ticket deadline has changed, clear the "deadline_notified" flag
            if ($ticket_data['deadline'] != $ticket->deadline) $ticket_data['deadline_notified'] = false;

            $ticket->load_from_array ($ticket_data);

            $ticket_updated = false;

            if ($ticket->is_valid_data ())
            {
                $ticket->save_data ();
                $ticket->log_action ($this->current_user->id, TICKET_ACCESS_SAVE);
                $ticket_updated = true;

                // Re-syncronize the ticket information
                $ticket->load_data ();
            }

            // Force adding a detail in case of a status change
            if ($ticket->status != $old_status)
            {
                $this->vars['add_entry'] = true;
                if ($ticket_data['status'] == TICKET_STATUS_CLOSED and empty($ticket_detail_data['comments']))
                {
                    // When a status is changed to Closed, make sure
                    // there is a text comment stating that, for clarity.
                    // Also make sure such entry is public.
                    $ticket_detail_data['comments'] = 'Ticket closed';
                    $ticket_detail_data['private'] = 0;
                }
            }

            // Add a new entry if requested
            if ($this->vars['add_entry'])
            {
                $ticket_detail_data['private'] = 0;
                if(!$ticket_detail_data['assigned_id']){
                    $ticket_detail_data['assigned_id'] = $ticket->assigned_id;
                }

                // Check_first if there is indeed something to save
                $has_text = !(empty ($ticket_detail_data['comments']));

                if ($has_text)
                {
                    // The details are valid, save them
                    $ticket_detail_data ['created'] = time();
                    $ticket_detail_data ['user_id'] = get_uid ();
                    $ticket_detail_data ['status'] = $ticket->status;

                    $ticket_detail = new TicketDetail ();
                    $ticket_detail->load_from_array ($ticket_detail_data);
                    $ticket_detail->ticket_id = $ticket->id;
                    $ticket_detail->save_data ();
                    $ticket_detail->log_action ($this->current_user->id, TICKET_ACCESS_DETAIL_CREATE);

                                                                                    $ticket->assigned_id = $ticket_detail->assigned_id;
                    // Update the parent ticket too
                    $ticket->save_data ();

                    $ticket_updated = true;
                }
                else
                {
                    error_msg ($this->get_string('NEED_COMMENTS'));
                }
            }

            // Mark the ticket as 'Closed', if it was requested
            if ($this->vars['mark_closed'])
            {
                $ticket->mark_closed ();
                $ticket->save_data ();
                $ticket->log_action ($this->current_user->id, TICKET_ACCESS_CLOSE);
                $ticket_updated = true;
            }


            // Re-open a ticket
            if ($this->vars['mark_reopen'])
            {
                $ticket->reopen ();
                $ticket->save_data ();
                $ticket->log_action ($this->current_user->id, TICKET_ACCESS_REOPEN);
                $ticket_updated = true;
            }


            // If the ticket has been modified, dispatch notifications
            if ($ticket_updated)
            {
                $type = ($ticket->status == TICKET_STATUS_CLOSED ? TICKET_NOTIF_TYPE_CLOSED : TICKET_NOTIF_TYPE_UPDATED);
                $ticket->dispatch_notifications ($type, get_uid());
            }

            $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));
        }
        elseif ($this->vars['delete'] and $ticket->id)
        {
            // Ticket deletions is not allowed anymore
            //$ticket->delete ();
            $ret = $this->mk_redir ('manage_tickets');
        }

        return $ret;
    }
    
    /** Displays the page for adding attachments to a ticket */
    function ticket_attachment_add ()
    {
        check_auth ();
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
        check_auth ();
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
                $ticket->log_action ($this->current_user->id, TICKET_ACCESS_ATTACH_ADD);

                if ($attachment->is_valid_data ())
                {
                    $attachment->save_data ();
                    $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));
                }
                else
                {
                    error_msg ($this->get_string('UPLOADING_FAILED'));
                    $ret = $this->mk_redir ('ticket_attachment_add', array('ticket_id' => $ticket->id));
                }
            }
            else
            {
                error_msg ($this->get_string('NEED_ATTACHMENT'));
                $ret = $this->mk_redir ('ticket_attachment_add', array('ticket_id' => $ticket->id));
            }
        }

        return $ret;
    }
    
    /** Opens a file from an attachment */
    function ticket_attachment_open ()
    {
        check_auth ();
        $attachment = new TicketAttachment ($this->vars['id']);

        if (!$attachment->local_filename or !file_exists($attachment->local_filename))
        {
            error_msg ($this->get_string('ATTACHMENT_MISSING'));
            return $this->mk_redir ('ticket_edit', array ('id' => $attachment->ticket_id));
        }
        else
        {
            header ("Pragma: public");
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
        check_auth ();
        $attachment = new TicketAttachment ($this->vars['id']);
        $ticket_id = $attachment->ticket_id;
        $ticket = new Ticket ($ticket_id);
        $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket_id));

        $attachment->delete ();
        $ticket->log_action ($this->current_user->id, TICKET_ACCESS_ATTACH_DELETE);

        return $ret;
    }
    
    /** Displays the page for escalating a ticket */
    function ticket_escalate ()
    {
        check_auth ();
        $tpl = 'ticket_escalate.tpl';
        $ticket = new Ticket ($this->vars['id']);

        if (!$ticket->id) return ($this->mk_redir ('ticket_escalate_submit'));

        $this->assign ('ticket', $ticket);
        $this->assign ('error_msg', error_msg());
        $this->set_form_redir ('ticket_escalate_submit', array ('id' => $ticket->id));

        $this->display ($tpl);
    }


    /** Escalates a ticket */
    function ticket_escalate_submit ()
    {
        check_auth ();
        $ticket = new Ticket ($this->vars['id']);
        $ret = $this->mk_redir ('ticket_edit', array ('id' => $ticket->id));

        if ($this->vars['save'] and $ticket->id)
        {
            if ($this->vars['comments'])
            {
                $ticket->escalate (get_uid(), $this->vars['comments'], false);
                $ticket->save_data ();
                $ticket->dispatch_notifications (TICKET_NOTIF_TYPE_ESCALATED, get_uid());
                $ticket->save_data ();
                $ticket->log_action ($this->current_user->id, TICKET_ACCESS_ESCALATE);
            }
            else
            {
                error_msg ($this->get_string('NEED_ESCALATION_REASON'));
                $ret = $this->mk_redir ('ticket_escalate', array ('id' => $ticket->id));
            }
        }

        return $ret;
    }
    
    function customer_satisfaction(){
        check_auth(array('ticket_id'=>$this->vars['ticket_id']));
        $tpl = 'customer_satisfaction.tpl';
        
        $ticket = new Ticket($this->vars['ticket_id']);
        if(!$ticket->id) return $this->mk_redir('manage_tickets');
        
        $questions = array(
            'overall_satisfaction' => $this->get_string('CS_OVERALL_SATISFACTION'),
            'problem_solved' => $this->get_string('CS_PROBLEM_SOLVED'),
            'satisfaction_degree' => $this->get_string('CS_SATISFACTION_DEGREE'),
            'waiting_time' => $this->get_string('CS_WAITING_TIME'),
            'expertize' => $this->get_string('CS_EXPERTIZE'),
            'urgency_consideration' => $this->get_string('CS_URGENCY_CONSIDERATION'),
            'impact_consideration' => $this->get_string('CS_IMPACT_CONSIDERATION'),
            'technician_expertize' => $this->get_string('CS_TECHNICIAN_EXPERTIZE'),
            'technician_commitment' => $this->get_string('CS_TECHNICIAN_COMMITMENT'),
            'time_to_solve' => $this->get_string('CS_TIME_TO_SOLVE'),
            'occurence' => $this->get_string('CS_OCCURENCE'),
            'suggestions' => $this->get_string('CS_SUGGESTIONS'),
            'would_recommend' => $this->get_string('CS_WOULD_RECOMMEND')
        );
        
        $satisfaction_level_str = array(
            'little_satisfied' => $this->get_string('CS_LITTLE_SATISFIED'),
            'very_satisfied' => $this->get_string('CS_VERY_SATISFIED')
        );
        
        $responses = array(
            'yes' => $this->get_string('CS_YES'),
            'no' => $this->get_string('CS_NO')
        );
        
        $submit_texts = array(
            'send' => $this->get_string('CS_SEND'),
            'quit' => $this->get_string('CS_QUIT'),
        );
        
        $incident_occurence = $GLOBALS['INCIDENT_OCCURENCE'][$_SESSION['USER_LANG']];              
        $this->assign('thanks_note', $this->get_string('CS_THANKS_NOTE'));
        $this->assign('questions', $questions);
        $this->assign('satisfaction_level', $satisfaction_level_str);
        $this->assign('responses', $responses);
        $this->assign('incident_occurence', $incident_occurence);
        $this->assign('submit_texts', $submit_texts);
        $this->assign('ticket', $ticket);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('customer_satisfaction_submit');
        $this->display($tpl);
        
    }
    function customer_satisfaction_submit(){
        $ret = $this->mk_redir('manage_tickets');
        if(isset($this->vars['send'])){
            class_load('CustomerSatisfactionModel');
            $customer_satisfaction = new CustomerSatisfactionModel();
            $customer_satisfaction_data = $this->vars['customer_satisfaction'];
            $customer_satisfaction->load_from_array($customer_satisfaction_data);
            $customer_satisfaction->date_completed = time();
            $customer_satisfaction->user_id = $this->current_user->id;
            if($customer_satisfaction->is_valid_data()){
                $customer_satisfaction->save_data();
            } else {
                $ret = $this->mk_redir('customer_satisfaction');
            }
        }
        return $ret;
    }
}

?>

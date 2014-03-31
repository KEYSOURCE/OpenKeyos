<?php
//load the ticket comments
require_once(dirname(__FILE__).'/../../phplib/lib.php');
class_load('AsteriskModel');
check_auth();

$action = '';
if(isset($_REQUEST['action']) && trim($_REQUEST['action'])!=''){   
    $action = $_REQUEST['action'];
    if($action == 'load_comments'){
        class_load('Ticket');
        if(isset($_REQUEST['ticket_id']) and is_numeric($_REQUEST['ticket_id']) and $_REQUEST['ticket_id'] > 0){
            $ticket = new Ticket($_REQUEST['ticket_id']);

            $ret = array();
            if($ticket->id){    
                $ret['ticket_id'] = $ticket->id;
                $ret['ticket_status'] = $GLOBALS['TICKET_STATUSES'][$ticket->status];
                $ret['ticket_subject'] = AsteriskModel::json_escape($ticket->subject);
                $comment = array();
                foreach($ticket->details as  $det){        
                    $comment = array(
                        'id' => $det->id,
                        'comment' => AsteriskModel::json_escape(nl2br($det->comments)),
                        'created' => date('d/m/Y H:i', $det->created),            
                        'user_id' => $det->user->id,
                        'user_name' => AsteriskModel::json_escape($det->user->fname." ".$det->user->lname),
                        'assigned_id' => $det->assigned->id,
                        'assigned_name' => AsteriskModel::json_escape($det->assigned->fname." ".$det->assigned->lname)
                    );
                    //debug($comment);
                    $ret['comments'][] = $comment;
                }   
                $ret['last_comment'] = $comment;
            }
            echo json_encode($ret);
        }
    }
    if($action == 'load_tickets'){
        class_load('Ticket');
        if(isset($_REQUEST['customer_id']) and is_numeric($_REQUEST['customer_id']) and $_REQUEST['customer_id'] > 0){            
            $ret = array();
            
            $status = -1;            
            if(isset($_REQUEST['status']) and $_REQUEST['status'] < 0){
                $status = array(TICKET_STATUS_ASSIGNED, TICKET_STATUS_NEW, TICKET_STATUS_OVER_DEADLINE, TICKET_STATUS_TBS, TICKET_STATUS_WAITING_CUSTOMER);
            } else {
                $status = $_REQUEST['status'];
            }
            $tickets_filter = array(
                'customer_id' => $_REQUEST['customer_id'],                                       
                'order_by'=>'id', 
                'order_dir'=>'desc',
                'status' => $status
            );
            $tickets_list = Ticket::get_tickets_list($tickets_filter);
            foreach($tickets_list as $ticket_id=>$ticket_subject){
                $ret[] = array(
                    'id' => $ticket_id,
                    'subject' => AsteriskModel::json_escape($ticket_subject)
                );
            }
            echo json_encode($ret);
        }
    }
    if($action=='load_users'){
        class_load('User');
        if(isset($_REQUEST['customer_id']) and is_numeric($_REQUEST['customer_id']) and $_REQUEST['customer_id'] > 0){
            $users_list = User::get_users_list(array('customer_id' => $_REQUEST['customer_id']));
            foreach($users_list as $k=>$usr) $users_list[$k] = AsteriskModel::json_escape($usr);
            if($_REQUEST['customer_id'] == 6){
                $ul = User::get_users_list(array('customer_id' => '0'));
                foreach($ul as $k=>$u){
                    $users_list[$k] = AsteriskModel::json_escape($u);
                }
            }            
            echo json_encode($users_list);
        }
    }
    if($action=='load_customer_contacts'){
        class_load('CustomerContact');
        if(isset($_REQUEST['customer_id']) and is_numeric($_REQUEST['customer_id']) and $_REQUEST['customer_id'] > 0){
            $customer_contacts = CustomerContact::get_contacts_list(array('customer_id' => $_REQUEST['customer_id']));            
            foreach($customer_contacts as $k=>$cc) $customer_contacts[$k] = AsteriskModel::json_escape($cc);
            echo json_encode($customer_contacts);
        }
    }
    if($action=='load_user_phones'){
        class_load('User');       
        if(isset($_REQUEST['user_id']) and is_numeric($_REQUEST['user_id']) and $_REQUEST['user_id'] > 0){
            $user = new User($_REQUEST['user_id']);            
            if($user->id){
                $phones = array();
                foreach($user->phones as $phone){
                    $phones[] = array(
                      'number' => $phone->phone,
                      'type' => AsteriskModel::json_escape($GLOBALS['PHONE_TYPES'][$phone->type]),
                      'comment' => AsteriskModel::json_escape($phone->comment)
                    );
                }                
                echo json_encode($phones);
            }
                        
        }
    }
    if($action=='load_contact_phones'){
        class_load('CustomerContact');       
        if(isset($_REQUEST['contact_id']) and is_numeric($_REQUEST['contact_id']) and $_REQUEST['contact_id'] > 0){
            $contact = new CustomerContact($_REQUEST['contact_id']);  
            if($contact->id){
                $phones = array();
                foreach($contact->phones as $phone){
                    $phones[] = array(
                      'number' => $phone->phone,
                      'type' => AsteriskModel::json_escape($GLOBALS['PHONE_TYPES'][$phone->type]),
                      'comment' => AsteriskModel::json_escape($phone->comment)
                    );
                }                
                echo json_encode($phones);
            }
                        
        }
    }
    if($action=='check_username'){
        class_load('AsteriskModel');
        if(isset($_REQUEST['username']) && trim($_REQUEST['username']) != ''){
            $ret = array();
            $is_unique = AsteriskModel::check_unique_username($_REQUEST['username']);
            $ret['status'] = $is_unique ? 1: 0;
            echo json_encode($ret);
        }
    }
    if($action=='check_email'){
        class_load('AsteriskModel');
        if(isset($_REQUEST['email']) && trim($_REQUEST['email']) != ''){
            $ret = array();
            $is_unique = AsteriskModel::check_unique_username($_REQUEST['email']);
            $ret['status'] = $is_unique ? 1: 0;
            echo json_encode($ret);
        }
    }
    if($action == 'create_new_customer'){
        $ret=array();
        class_load('Customer');
        $customer_data = $_REQUEST['customer'];
        $customer = new Customer();
        $customer->load_from_array($customer_data);
        if($customer->is_valid_data()) {            
            $customer->save_data();
            if($customer->id) $ret['id'] = $customer->id;
        }
        $customers_list = Customer::get_customers_list(array('favorites_first' => 1));      
        foreach($customers_list as $key=>$cust){
            $customers_list[$key] = AsteriskModel::json_escape($cust);
        }
        $ret['customers'] = $customers_list;
        echo json_encode($ret);        
    }
}
die;
?>

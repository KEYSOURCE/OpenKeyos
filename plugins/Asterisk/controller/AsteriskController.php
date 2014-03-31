<?php
class_load('AsteriskModel');
class AsteriskController extends PluginController{
    protected $plugin_name = 'Asterisk';
    public function __contstruct(){
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    public function detect(){
        check_auth();
        class_load('Customer');
        class_load('User');
        class_load('UserPhone');
        $tpl = 'detect.tpl';
        if(!isset($this->vars['caller_id'])){
            //no phone number is set -> we should add a box to write a phone number
            $missing_caller_id = 1;
            $caller_detected = 0;
        } else {
            $missing_caller_id = 0;
            //now we have the phone number... search it
            $caller_info = AsteriskModel::search_user_phone_number($this->vars['caller_id']);
            if(!$caller_info){
                $caller_info = AsteriskModel::search_contact_phone_number($this->vars['caller_id']);
                if($caller_info) $caller_info['type'] = 'contact';
            } else {
                $caller_info['type'] = 'user';
            }
            if($caller_info){
                $this->assign('caller_info', $caller_info);
                $caller_detected = 1;
                //we have the caller... get the tickets for the customer
                class_load('Ticket');                
                class_load('Computer');
                class_load('User');
                class_load('CustomerContact');
                class_load('CustomerInternetContract');
                $tickets_filter = array(
                   'customer_id' => $caller_info['customer']->id,                                       
                   'order_by'=>'id', 
                   'order_dir'=>'desc',
                   'status' => array(TICKET_STATUS_ASSIGNED, TICKET_STATUS_NEW, TICKET_STATUS_OVER_DEADLINE, TICKET_STATUS_TBS, TICKET_STATUS_WAITING_CUSTOMER)
                    
                );
                if(isset($_SESSION['asterisk']['detect']['tickets_filter'])){
                    $tickets_filter = $_SESSION['asterisk']['detect']['tickets_filter'];
                    unset($_SESSION['asterisk']['detect']['tickets_filter']);
                }
                $customer_tickets = Ticket::get_tickets_list($tickets_filter);
                foreach ($customer_tickets as $k=>$ct){
                    $customer_tickets[$k] = "#".$k." ".$ct;
                }
                
                $customer_computers = Computer::get_computers_list($tickets_filter);
                foreach($customer_computers as $comp_id=>$comp_name){
                    $customer_computers[$comp_id] = "#".$comp_id." ".$comp_name;
                }
                
                $customer_users = User::get_users($tickets_filter, $no_users);
                
                $customer_contacts = CustomerContact::get_contacts($tickets_filter);
                $customer_contracts = CustomerInternetContract::get_contracts($tickets_filter);
                foreach($customer_contracts as $k=>$contract){
                    $contract->load_details();
                }
                //debug($customer_contracts);
                $this->assign('customer_contracts', $customer_contracts);
                $this->assign('customer_contacts', $customer_contacts);
                $this->assign('customer_users', $customer_users);
                $this->assign('customer_computers', $customer_computers);
                $this->assign('customer_tickets', $customer_tickets);
            } else { 
                $caller_detected = 0;
                $customers_list = Customer::get_customers_list(array('favorites_first' => 1));
                $this->assign('customers_list', $customers_list);               
            }
            
        }
        $this->assign('TICKET_STATUSES', $GLOBALS['TICKET_STATUSES']);
        $this->assign('LYNE_TYPES', $GLOBALS['LINE_TYPES']);
        $this->assign('USER_TYPE_KEYSOURCE', USER_TYPE_KEYSOURCE);
        $this->assign('USER_TYPE_CUSTOMER', USER_TYPE_CUSTOMER);
        $this->assign('ACCOUNT_MANAGERS', $GLOBALS['ACCOUNT_MANAGERS']);
        $this->assign('DEFAULT_ACCOUNT_MANAGER', DEFAULT_ACCOUNT_MANAGER);
        $this->assign('caller_detected', $caller_detected);
        $this->assign('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
        $this->assign('CONTRACT_SUBTYPES', $GLOBALS['CUST_SUBTYPES']);
        $this->assign('PRICE_TYPES', $GLOBALS['CUST_PRICETYPES']);
        $this->assign('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
        $this->assign('USER_TYPES', $GLOBALS['USER_TYPES']);
        $this->assign('caller_number', $this->vars['caller_id']);
        $this->assign('missing_caller_id', $missing_caller_id);
        $this->set_form_redir('detect_submit');
        $this->assign('error_msg', error_msg());
        //$this->display($tpl);        
        $this->display($tpl);
    }

    public function detect_submit(){
        $ret = $this->mk_redir('detect');
        
        if(isset($this->vars['auth_code_submit'])){
            class_load('CustomerComment');            
            $cust = new Customer($this->vars['customer_id']);
            if($cust->id){
                $cust->onhold = 0;
                if($cust->is_valid_data()){
                    $cust->save_data();
                }
                $ccoment = new CustomerComment();
                $ccoment->customer_id = $this->vars['customer_id'];
                $ccoment->subject = "Customer revive authorization code";
                $ccoment->comments = $this->vars['auth_code_txt'];
                $ccoment->user_id = $this->current_user->id;
                $ccoment->created = time();
                if(trim($ccoment->comments) != ""){
                    $ccoment->save_data();
                }            
            }
            $caller_id = preg_replace('/[^0-9\+]+/', '', $this->vars['caller_id_txt']);              
            $caller_id = preg_replace('/\++/','00', $caller_id);
            $ret = $this->mk_redir('detect', array('caller_id' => $caller_id));
            return $ret;
        }
        
        if(isset($this->vars['caller_id_txt'])){
            $caller_id = preg_replace('/[^0-9\+]+/', '', $this->vars['caller_id_txt']);              
            $caller_id = preg_replace('/\++/','00', $caller_id);
            $ret = $this->mk_redir('detect', array('caller_id' => $caller_id));
            return $ret;
        }             
        
        if(isset($this->vars['add_to_user_submit'])){
            class_load('UserPhone');
            $phone_data = $this->vars['phone'];
            $phone_data['user_id'] = $this->vars['cu_user_id'];
            $phone_data['customer_id'] = $this->vars['cu_customer_id'];
            $phone = new UserPhone();
            $phone->load_from_array($phone_data);            
            if($phone->is_valid_data()){
                $phone->save_data();
            }
            
            return $this->mk_redir('detect', array('caller_id' => $phone->phone));
        }
        if(isset($this->vars['create_new_user_submit'])){
            //here we create the user and the userPhone object
            class_load('User');
            class_load('UserPhone');
            $user = new User();
            $user_data = $this->vars['user'];
            $user_phone_data = $this->vars['user_phone'];
            $user->load_from_array($user_data);
            $user->customer_id=$this->vars['cu_cusr_customer_id'];            
            if($user->is_valid_data()){
                $user->save_data();
                if($user->id){
                    //save the phone
                    $userphone = new UserPhone();
                    $userphone->load_from_array($user_phone_data);
                    $userphone->cutomer_id = $user->customer_id;
                    $userphone->user_id = $user->id;
                    if($userphone->is_valid_data()){
                        $userphone->save_data();                                
                    }
                }
            }
            return $this->mk_redir('detect', array('caller_id' => $this->vars['user_phone']['phone']));
        }
        
        if(isset($this->vars['add_to_customer_contact_submit'])){
            class_load('CustomerContactPhone');
            $phone_data = $this->vars['phone'];
            $phone_data['contact_id'] = $this->vars['cu_contact_id'];
            $phone = new CustomerContactPhone();
            $phone->load_from_array($phone_data);
            if($phone->is_valid_data()) $phone->save_data();
            return $this->mk_redir('detect', array('caller_id' => $phone_data['phone']));
        }
        
        if(isset($this->vars['create_new_contact_submit'])){
            class_load('CustomerContact');
            class_load('CustomerContactPhone');
            $contact_data = $this->vars['contact'];
            $contact_phone_data = $this->vars['contact_phone'];
            $contact_data['customer_id'] = $this->vars['cu_ccont_customer_id'];
            $contact = new CustomerContact();
            $contact->load_from_array($contact_data);
            if($contact->is_valid_data()){
                $contact->save_data();
                if($contact->id){
                    $phone = new CustomerContactPhone();
                    $contact_phone_data['contact_id'] = $contact->id;
                    $phone->load_from_array($contact_phone_data);
                    if($phone->is_valid_data()){
                        $phone->save_data();
                    }
                }
            }
            return $this->mk_redir('detect', array('caller_id' => $this->vars['contact_phone']['phone']));
        }
            
        return $ret;
    }
}
?>

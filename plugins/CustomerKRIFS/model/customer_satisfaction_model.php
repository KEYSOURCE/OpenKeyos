<?php

class CustomerSatisfactionModel extends PluginModel{
    public $id = null;
    public $user_id = null;
    public $ticket_id = null;
    public $customer_id = null;
    public $overall_satisfaction = 3;
    public $problem_solved = TRUE;
    public $waiting_time = 3;
    public $expertize = 3;
    public $urgency_consideration = 3;
    public $impact_consideration = 3;
    public $technician_expertize = 3;
    public $technician_commitment = 3;
    public $time_to_solve = 3;
    public $ocurence = RARE_INCIDENT;
    public $suggestions = '';
    public $would_recommend = TRUE;
    public $date_completed = null;
    
    public $table = TBL_CUSTOMERS_SATISFACTION;
    
    public $fields = array(
        'id', 'user_id', 'ticket_id', 'customer_id', 'overall_satisfaction', 'problem_solved', 'waiting_time', 'expertize',
        'urgency_consideration', 'impact_consideration', 'technician_expertize', 'technician_commitment', 'time_to_solve',
        'occurence', 'suggestions', 'would_recommend', 'date_completed'
    );
    
    function __construct($id = null) {
        if($id!=null){
            $this->id = $id;
            $this->load_data();
        }
    }
    
    function is_valid_data() {
        $ret = TRUE;
        if(!$this->user_id) {
            error_msg($this->get_string(CS_NEED_USER));
            $ret = FALSE;
        }
        if(!$this->ticket_id){
            error_msg($this->get_string(CS_NEED_TICKET));
            $ret = FALSE;
        }
        return $ret;
    }
}
?>

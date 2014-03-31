<?php

class WebAccessResource extends Base{
    
    var $id = null;
    var $webaccess_id = null;
    var $username = "";
    var $password = "";    
    var $notes = "";
    var $not_working = false;
    var $date_added = null;
    var $date_modified = null;
    var $user_id = null;
    
    var $table = TBL_WEB_ACCESS_RESOURCES;
    var $fields = array(
        'id',
        'webaccess_id',
        'username',
        'password',
        'notes',
        'not_working',
        'date_added',
        'date_modified',
        'user_id'        
    );
    
    function __construct($id = null){
        if($id){
            $this->id = $id;
            $this->load_data();
        }
    }
    
    function is_valid_data() {
        $ret = true;
        if(!$this->username) {
            error_msg("You must specify a username!");
            $ret = false;
        }
        if(!$this->password){
            error_msg("You must specify the password!");
            $ret = false;
        }
        
        return $ret;
    }
    /**
     * Check if the data in the array is different from the one in the object
     * if even one bit is different should yeld true else false
     *
     * @param array $data 
     * @return bool
     */
    function data_changed($data = array()){
        $ret = false;
        foreach($data as $k => $v){
            if($this->$k !== $v){
                $ret = true;
            }
        }
        return $ret;
    }
}

?>

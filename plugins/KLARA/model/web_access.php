<?php

class_load("WebAccessResource");

class WebAccess extends Base{
    var $id;
    var $uri;
    var $customer_id;
    var $comments;
    var $date_added  = null;
    var $date_modified = null;    
    var $user_id = null;
    /* a list of WebAccessResource objects */
    var $credentials = array();
    
    var $table = TBL_WEB_ACCESS;
    var $fields = array(
        'id', 
        'customer_id', 
        'uri', 
        'comments',
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
    
    function load_data(){
        if($this->id){
            parent::load_data();
            if($this->id){
                //load the credentials for this website
                $this->credentials = array();
                $query = "SELECT id from ".TBL_WEB_ACCESS_RESOURCES." where webaccess_id = ".$this->id;
                $ids = $this->db_fetch_vector($query);
                foreach($ids as $rid){
                    $this->credentials[] = new WebAccessResource($rid);
                }
            }
        }
    }
    
    function delete(){
        if($this->id){
            foreach($this->credentials as $cred){
                $cred->delete();
            }
            parent::delete();
        }
    }
    
    function save_data(){
        parent::save_data();
        if($this->id){
            //now we delete all the credentials for this...
            $query = "delete from ".TBL_WEB_ACCESS_RESOURCES." where webaccess_id=".$this->id;
            $this->db_query($query);
            //we must save the credentials for this.
            foreach($this->credentials as $cred){                
                $cred->webaccess_id = $this->id;                
                if($cred->is_valid_data()){
                    $cred->save_data();
                }
            }
        }
        return true;
    }
    
    function load_additional_data($data = array()){       
        //now we must deal with the credentials
        //in the $data array we check if there is an array called credentials
        //and if there is see each credential
        $this->credentials = array();
        if(isset($data['credentials'])){
            if(count($data['credentials']) > 0){
                foreach($data['credentials'] as $cred){
                    if($cred['id']){
                        //this might just be an edit operation
                        $wac = new WebAccessResource($cred['id']);
                        if($wac->data_changed($cred)){
                            $wac->load_from_array();
                            $wac->date_modified = time();
                            $wac->user_id = $this->user_id;
                            $wac->webaccess_id = $this->id;
                            $this->credentials[] = $wac;
                        } else {
                            $this->credentials[] = $wac;
                        }
                    } else {
                        //this is an add operation
                        $wac = new WebAccessResource();
                        $wac->load_from_array($cred);
                        $wac->webaccess_id = $this->id;
                        $wac->user_id = $this->user_id;
                        $wac->date_added = time();
                        $wac->date_modified = time();
                        $this->credentials[] = $wac;
                    }
                }
            }
        }
    }
    
    function is_valid_data(){
        $ret = true;
        if(!$this->uri){
            error_msg('You need to specify the URL of the Web site!');
            $ret = false;
        }
        return $ret;
    }
    
    static function get_webaccess_list($filter = array()){
        $ret = array();
        
        $query = "select id from " . TBL_WEB_ACCESS . ' WHERE 1 ';
        if((isset($filter['customer_id'])) && ($filter['customer_id'] > 0)){
            $query .= " AND customer_id=".$filter['customer_id'] . ' ';
        }
        //add more filters if there is a need for it
        $ids  = DB::db_fetch_vector($query);
        if(is_array($ids) and !empty($ids)){
            foreach($ids as $id){
                $ret[] = new WebAccess($id);
            }
        }
        
        return $ret;
    }
}

?>

<?php

class Nagvis extends Base {

    var $id = null;
    var $cusmoter_id = null;
    var $username = null;
    var $password = null;
    var $url = null;
    var $protocol = null;

    var $fields = array('id', 'customer_id', 'username', 'password', 'url', 'protocol');
    var $table = TBL_CUSTOMER_NAGVIS_ACCOUNT;

    function Nagvis($id = null) {
        if ($id) {
            $this->id = $id;
            $this->load_data();
            $this->verify_access();
        }
    }
    
    function get_item($customer_id) {
    	$customer_id = (int) $customer_id;
    	$q = 'SELECT * FROM ' . $this->table . ' WHERE customer_id="' . $customer_id . '"';
    	$ret = $this->db_fetch_array($q);
    	if(!empty($ret)) {
    		return $ret[0];
    	} else {
    		return null;
    	}
    }

    function is_valid_data() {
    	$ret = true;	
        if(empty($this->customer_id)) {
            error_msg('Invalid customer');
            $ret = false;
        }
        if(empty($this->username)) {
            error_msg('Username field is required');
            $ret = false;
        }
        if(empty($this->password)) {
            error_msg('Password field is required');
            $ret = false;
        }
        if(empty($this->url)) {
            error_msg('Url is invalid');
            $ret = false;
        }
        return $ret;
    }

    function verify_access() {
        $uid = get_uid();
        class_load('User');
        $user = new User($uid);
        if($user->type == USER_TYPE_CUSTOMER) {
            if($this->customer_id != $user->customer_id) {
                $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
                header("Location: $url\n\n");
                exit;
            }
        }
    }

}

?>

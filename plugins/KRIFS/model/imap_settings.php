<?php

class ImapSettings extends Base {

    var $id = null;
    var $server = null;
    var $port = null;
    var $encrypt = null; //Default true
    var $mailbox = null;
    var $validate_cert = 0; //Default false
    var $username = null;
    var $password = null;
    var $assigned_user_id = null;
    var $table = TBL_IMAP_SETTINGS;
    var $fields = array('id', 'server', 'port', 'encrypt', 'mailbox', 'validate_cert', 'username', 'password', 'assigned_user_id');

    function __construct($id = null) {
        if ($id) {
            $this->id = $id;
            $this->load_data();
        }
    }

    function is_valid_data() {
        $ret = true;
        if (empty($this->server)) {
            $ret = false;
            error_msg('Server field cannot be empty.');
        }
        if (empty($this->port)) {
            $ret = false;
            error_msg('You must set an port.');
        }
        if (empty($this->username)) {
            $ret = false;
            error_msg('User name must be completed.');
        }
        if (empty($this->password)) {
            $ret = false;
            error_msg('Password must be completed.');
        }
        if (empty($this->mailbox)) {
            $ret = false;
            error_msg('Mailbox folder must be completed.');
        }
        if (empty($this->assigned_user_id)) {
            $ret = false;
            error_msg('Need user to assign created tickets');
        }
        return $ret;
    }

    function get_data() {
        $ret = null;
        if ($this->id) {
            foreach ($fields as $field) {
                $ret[$field] = $this->{$field};
            }
        }
        return $ret;
    }

    function get_all() {
        $q = 'SELECT * FROM ' . $this->table;
        return $this->db_fetch_array($q);
    }

}

?>

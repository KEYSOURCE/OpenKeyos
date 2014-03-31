<?php

class ComputerLogmein extends Base {

    var $id= null;
    var $computer_id = null;
    var $logmein_id = null;

    var $table = TBL_COMPUTER_LOGMEIN;
    var $fields = array('id', 'computer_id', 'logmein_id');

    function __construct($id = null) {
        if ($id) {
            $this->id = $id;
            $this->load_data();
        }
    }

    function is_valid_data() {
        $ret = true;
        if(!$this->logmein_id) {
            error_msg('Invalid ID');
            $ret = false;
        }
        if(!$this->computer_id) {
            error_msg('Invalid computer');
            $ret = false;
        }
        return $ret;
    }

    function get_item($computer_id = null) {
        if($computer_id) {
            $computer_id = intval($computer_id);
            $q = 'SELECT * FROM ' . $this->table . ' WHERE computer_id="' . $computer_id . '"';
            $ret = $this->db_fetch_array($q);
            if(!empty($ret)) {
                return $ret[0];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

}

?>

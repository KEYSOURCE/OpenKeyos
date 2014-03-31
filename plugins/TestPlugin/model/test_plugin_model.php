<?php
class_load('User');
class TestPluginModel extends PluginModel{
    public $id = null;
    public $value = '';
    public $name = '';
    public $date_added = null;
    public $date_last_modification = null;
    public $fk_user = null;
    /*load the user object for this instance*/
    public $usr_obj = null;
    
    public $fields = array(
                'id', 'name', 'value', 'date_added', 'date_last_modification', 'fk_user'
    );
    public $table = TBL_TEST_PLUGIN;
    function __construct($id = null){
        if($id){
            $this->id = $id;
            $this->load_data();
        }
    }
    
    public function load_data(){
        if($this->id){
            parent::load_data();
            if($this->id){
                $this->usr_obj = new User($this->fk_user);
            }
        }
    }
    
    public static function get_all_items($filter=array()){
        $ret = array();
        $query = "SELECT id FROM ".TBL_TEST_PLUGIN;
        if(isset($filter['order_by'])){
            $query .= ' ORDER BY '.$filter['order_by'].' ';
            $query .= isset($filter['order_dir']) ? $filter['order_dir'] : 'desc';                
        }
        if(isset($filter['max_records'])){
            if(isset($filter['start_record'])){
                $query .= ' LIMIT '.$filter['start_record'].", ".$filter['max_records'];
            } else {
                $query .= ' LIMIT '.$filter['max_records'];
            }            
        }
        $ids = Db::db_fetch_vector($query);
        foreach($ids as $id){
            $ret[] = new TestPluginModel($id);
        }
        return $ret;
    }
}
?>

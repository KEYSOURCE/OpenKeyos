<?php
class TestPluginController extends PluginController{
    
    protected $plugin_name = 'TestPlugin';
    function __construct(){        
        parent::__construct();        
    }
    
    public function list_all(){
        check_auth();        
        class_load('TestPluginModel');
        $tpl = 'list_all.tpl';
        //the body of the function here
        $filter=array(
            'order_by' => 'date_last_modification',
            'order_dir' => 'desc'
        );
        $items = TestPluginModel::get_all_items($filter);
        
        $this->assign('items', $items);
        $this->assign('error_msg', error_msg());
        $this->display($tpl);
    }
    public function list_all_submit(){
        return $this->mk_redir('list_all');
    }
    
    public function add(){
        check_auth();
        class_load('TestPluginModel');
        $tpl = 'add.tpl';        
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('add_submit');
        $this->display($tpl);
    }
    public function add_submit(){
        check_auth();
        class_load('TestPluginModel');
        $ret = $this->mk_redir('list_all');        
        if($this->vars['save']=="Save"){
            $item_data = $this->vars['item'];
            $item = new TestPluginModel();
            $item->load_from_array($item_data);
            $item->date_added = time();
            $item->date_last_modification = time();
            $item->fk_user = $this->current_user->id;           
            $item->save_data();
            return $this->mk_redir('add');
        }
        return $ret;
    }
}
?>

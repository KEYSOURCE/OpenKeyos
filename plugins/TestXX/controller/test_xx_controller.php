
<?php

class TestXXController extends PluginController{
    protected $plugin_name = 'TestXX';

    function __construct(){
        parent::__construct();
    }

    public function index(){
        $tpl = 'index.tpl';
        //the body of the function here
        $items = array();

        $this->assign('items', $items);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('index_submit');
        $this->display($tpl);
    }

    public function list_all_submit(){
        //process data here
        return $this->mk_redir('index');
    }
}

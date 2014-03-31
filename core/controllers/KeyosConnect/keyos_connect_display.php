<?php
class KeyosConnectDisplay extends BaseDisplay{
    public function __construct(){
        parent::BaseDisplay();
    }

    public function manage_plugins(){
        check_auth();
        $tpl = 'KeyosConnect/manage_plugins.tpl';

        $plugins = $GLOBALS['PLUGINS'];
        $plugin_statuses = array();
        foreach($plugins as $plugin_key=>$plugin){
            $stat = PluginBase::status($plugin_key, $plugin);
            $plugin_statuses[$plugin_key] = array(
                                                'status'=>$stat,
                                                'status_display'=>$GLOBALS['PLUGIN_STATUSES'][$stat],
                                            );
        }

        $this->assign('active_plugins', $GLOBALS['PLUGINS']);
        $this->assign('plugin_statuses', $plugin_statuses);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('manage_plugins_submit');
        $this->display($tpl);
    }

    public function manage_plugins_submit(){
        check_auth(array('plugin_key' => $this->vars['plugin_key'], 'plugin_status' => $this->vars['plugin_status'], ));
        if(($this->vars['plugin_status'] == PLUGIN_STATUS_DISABLED) or ($this->vars['plugin_status'] == PLUGIN_STATUS_INCONSISTENT)){
            //activate the plugin
            PluginBase::activate($this->vars['plugin_key'], $GLOBALS['PLUGINS'][$this->vars['plugin_key']]);
            return $this->mk_redir('manage_plugins');
        }
        else {
            //deactivate the plugin
            PluginBase::deactivate($this->vars['plugin_key'], $GLOBALS['PLUGINS'][$this->vars['plugin_key']]);
            return $this->mk_redir('manage_plugins');
        }
    }


}
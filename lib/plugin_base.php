<?php

function deps_sort($a, $b){
    if(empty($a['deps'])) return -1;
    if(empty($b['deps'])) return 1;
    if(!in_array($a['key'], $b['deps']) and !in_array($b['key'], $a['deps'])) return 0;
    if(in_array($a['key'], $b['deps']) and in_array($b['key'], $a['deps'])){ debug("Circular dependency: " . $a['key'] . " AND " . $b['key']); return 0;}
    if(in_array($a['key'], $b['deps']) and !in_array($b['key'], $a['deps'])) return 1;
    if(in_array($b['key'], $a['deps']) and !in_array($a['key'], $b['deps'])) return -1;
}

class PluginBase{
    static $preload_models = array();
    static $plugin_statuses = array();
    public static function check_plugin_consistency($plugin_reg, $plugin_init=null){
        if(!$plugin_init){
            $plugin_init_file = $plugin_reg['plugin_dir'].'/init.php';
            if(file_exists($plugin_init_file)){
                require_once $plugin_init_file;
            }
            else{
                return FALSE;
            }
        }
        if(!isset($plugin_reg['plugin_name']) || trim($plugin_reg['plugin_name']) == '' || preg_match('/\s+/', $plugin_reg['plugin_name'])){
            PluginBase::deactivate($plugin_reg);
            return FALSE;
        }
        if(!isset($plugin_reg['plugin_dir']) || trim($plugin_reg['plugin_dir']) == '' || !is_dir($plugin_reg['plugin_dir'])){
            PluginBase::deactivate($plugin_reg);
            return FALSE;
        }
        if(!file_exists($plugin_reg['plugin_dir']."/init.php")){
            PluginBase::deactivate($plugin_reg);
            return FALSE;
        }
        if(!is_dir($plugin_reg['plugin_dir']."/controller")){
            PluginBase::deactivate($plugin_reg);
            return FALSE;
        }
        if(!is_dir($plugin_reg['plugin_dir']."/model")){
            PluginBase::deactivate($plugin_reg);
            return FALSE;
        }
        if(!is_dir($plugin_reg['plugin_dir']."/views")){
            PluginBase::deactivate($plugin_reg);
            return FALSE;
        }
        if($plugin_init){
            if(isset($plugin_init['REQUIRED_MODULES']) and !empty($plugin_init['REQUIRED_MODULES'])){
                foreach($plugin_init['REQUIRED_MODULES'] as $req_mod){
                    if(!isset($GLOBALS['PLUGINS'][$req_mod])){
                        //debug("Plugin ".$plugin_reg['plugin_name']." required module ".$req_mod." that was not found");
                        error_msg("Plugin ".$plugin_reg['plugin_name']." required module ".$req_mod." that was not found");
                        PluginBase::deactivate($plugin_reg);
                        return FALSE;
                    }
                    else {
                        //debug("Prereq: ".$req_mod." was found for module ".$plugin_reg['plugin_name']);
                    }
                }
            }
        }
        return TRUE;
    }



    public static function plugins_load_order(){
        /*$plugin_load_order = array();
        self::get_preload_models();
        $dependencies = array();
        foreach($GLOBALS['PLUGINS'] as $plg_key => $plg){
            $dependencies[$plg_key] = array_diff(self::plugin_depends_on($plg['plugin_name']), array($plg_key));
        }
        foreach($dependencies as $plg_key => $dep) {
            $plugin_load_order[] = array('key'=>$plg_key, 'deps'=>$dep);
        }
        //uasort($plugin_load_order, deps_sort);
        //debug($plugin_load_order);
        return $plugin_load_order;*/

    }

    public static function get_preload_models(){
        //preload models
        self::$preload_models = array();
        $init_files = glob(__DIR__ . '/../plugins/*/init.php');

        $plugins = array();
        foreach($GLOBALS['PLUGINS'] as $k => $plg_reg){
            $plugins[$plg_reg['plugin_name']] = $k;
        }

        foreach($init_files as $if){
            require_once $if;
            $plugin_name = basename(dirname($if));
            foreach(array_keys($plugin_init['MODELS']) as $model){
                self::$preload_models[$model]['plugin'] = $plugins[$plugin_name];
            }
        }
    }

    public static function get_plugin_for_model($class){
        if(in_array($class, array_keys(self::$preload_models))){
            return self::$preload_models[$class]['plugin'];
        } else {
            //check if this is a core MODEL
            if(in_array($class, array_keys($GLOBALS['CLASSES']))){
                return "CORE";
            }
            else return FALSE;
        }
    }


    public static function plugin_depends_on($plugin_name){
        $cmd = "./search_plugin_class_loads.sh " . $plugin_name . " \"class_load(\"";
        $output = shell_exec($cmd);
        $class_loads_raw = preg_split("/\n/", $output);

        $class_loads = array();
        $pos = 0;
        $bC_style_comment = false;
        foreach($class_loads_raw as $cl){
            //$bC_style_comment = false;
            $normal_style_comment = false;
            if($cl == ""){ $normal_style_comment = true; }
            if(startsWith($cl, "//")){ $normal_style_comment = true; }
            if(startsWith($cl, "/*")){ $bC_style_comment = true; }
            if(startsWith($cl, "*/")){ $bC_style_comment = false; $cl = ltrim(substr($cl, 2)); }
            if(endsWith($cl, "*/")){ $bC_style_comment = false; $normal_style_comment = true;}
            if(($pp = strpos($cl, "*/")) > 0) { $bC_style_comment = false; $cl = ltrim(substr($cl, $pp+2)); }
            if(!$bC_style_comment && !$normal_style_comment && startsWith($cl, "class_load")){
                $class_loads[] = substr($cl, strlen("class_load")+2, -3);
            }
            $pos++;
        }
        $class_loads = array_values(array_unique($class_loads));
        $required_modules = array();

        foreach($class_loads as $cl){
            if(($plugin = self::get_plugin_for_model($cl))){
                if($plugin && $plugin != "CORE"){
                    $required_modules[] = $plugin;
                }
            }
        }
        $required_modules = array_values(array_unique($required_modules));

        return $required_modules;
    }

    public static function load($plugin_key, $plugin, $plugin_init=null){
        //load configurations
        if(!$plugin_init){
            $plugin_init_file = $plugin['plugin_dir'].'/init.php';
            if(file_exists($plugin_init_file)){
                require_once $plugin_init_file;
            }
        }
        if(PluginBase::status($plugin_key, $plugin) == PLUGIN_STATUS_ENABLED)
        {
            $GLOBALS['ENABLED_PLUGINS'][$plugin_key] = $plugin;
            $config_dir = $plugin['plugin_dir'] . "/config";
            if(is_dir($config_dir)){
                $module_config = $config_dir . "/module.config.php";
                $router = array();
                if(file_exists($module_config)){
                    //debug('loading config: ' . $module_config);
                    require_once($module_config);
                }
                if(!empty($router) && isset($router['routes'])){
                    $routerObj = Router::getInstance();
                    foreach($router['routes'] as $module_route_name => $module_route){
                        $route_name = $plugin['plugin_name'] . "_" . $module_route_name;
                        //debug("loading " . $route_name . " : " . $module_route['route']);
                        $routerObj->map(
                            $module_route['route'],
                            $module_route['target'],
                            array(
                                'methods' => $module_route['methods'],
                                'name' => $route_name ,
                                'filters' => $module_route['filters'],
                            )
                        );

                    }
                }

                //load module specific configurations
                $module_constants = $config_dir . "/local.php";
                $module_db_constants = $config_dir . "/local_db.php";
                if(file_exists($module_db_constants)){ require_once($module_db_constants); }
                if(file_exists($module_constants)){ require_once($module_constants); }
                foreach($GLOBALS['LANGUAGE_CODES'] as $lg){
                    if(file_exists($config_dir . "/local." . $lg . ".php")){ require_once($config_dir . "/local." . $lg . ".php"); }
                }
            }


            //everything else
            foreach($plugin_init['MODELS'] as $plugin_model_name => $plugin_model_file){
                $GLOBALS['MODELS'][$plugin_model_name] = array('file' => $plugin_model_file, 'plugin' => $plugin_key, );
                $GLOBALS['CLASSES'][$plugin_model_name] = $plugin_model_file;
            }
            foreach($plugin_init['CONTROLLERS'] as $plugin_controller_name => $plugin_controller){
                $GLOBALS['CLASSES_DISPLAY'][$plugin_controller_name] = $plugin_controller;
                if($plugin_controller['requires_acl']){
                    $GLOBALS['CLASSES_DISPLAY_ACL'][] = $plugin_controller_name;
                }
            }
            foreach($plugin_init['STRINGS'] as $plugin_class_name => $plugin_strings_file){
                $GLOBALS['CLASSES_STRINGS_FILES'][strtolower($plugin_class_name)] = $plugin_strings_file;
            }
            $GLOBALS['PLUGIN_TEMPLATES'][$plugin['plugin_name']] = $plugin_init['VIEWS'];

            /*if($plugin_init['IS_CUSTOMER']){
                $GLOBALS['CUSTOMER_PLUGINS'][] = $plugin['plugin_name'];
            }*/
            if($plugin_init['IS_MAIN_MODULE']){
                if(isset($plugin_init['MAIN_MENU_MODULE'])){
                    if(!isset($plugin_init['AVAILABLE_FOR_INTERFACE_MODE']) || empty($plugin_init['AVAILABLE_FOR_INTERFACE_MODE'])){
                        //if the interface mode is not set make it available only for administrators
                        $GLOBALS['MAIN_MODULES'][$plugin_init['MAIN_MENU_MODULE']['name']] = $plugin_init['MAIN_MENU_MODULE'];
                    } else {
                        if(in_array(INTERFACE_MODE_ADMINISTRATOR, $plugin_init['AVAILABLE_FOR_INTERFACE_MODE'])){
                            $GLOBALS['MAIN_MODULES'][$plugin_init['MAIN_MENU_MODULE']['name']] = $plugin_init['MAIN_MENU_MODULE'];
                        }
                        if(in_array(INTERFACE_MODE_CUSTOMER, $plugin_init['AVAILABLE_FOR_INTERFACE_MODE'])) {
                            $GLOBALS['MAIN_CUSTOMER_MODULES'][$plugin_init['MAIN_MENU_MODULE']['name']] = $plugin_init['MAIN_MENU_MODULE'];
                        }
                        if(in_array(INTERFACE_MODE_CUSTOMER_ADMINISTRATOR, $plugin_init['AVAILABLE_FOR_INTERFACE_MODE'])){
                            $GLOBALS['MAIN_CUSTOMER_ADMINISTRATOR_MODULES'][$plugin_init['MAIN_MENU_MODULE']['name']] = $plugin_init['MAIN_MENU_MODULE'];
                        }
                    }
                }
            }

            if(isset($plugin_init['MENU']) and is_array($plugin_init['MENU'])){
                if(!isset($plugin_init['AVAILABLE_FOR_INTERFACE_MODE']) || empty($plugin_init['AVAILABLE_FOR_INTERFACE_MODE'])){
                    foreach($plugin_init['MENU'] as $menu_item){
                        $GLOBALS['MENU'][] = $menu_item;
                    }
                } else {
                    if(in_array(INTERFACE_MODE_ADMINISTRATOR, $plugin_init['AVAILABLE_FOR_INTERFACE_MODE'])){
                        foreach($plugin_init['MENU'] as $menu_item){
                            $GLOBALS['MENU'][] = $menu_item;
                        }
                    }
                    if(in_array(INTERFACE_MODE_CUSTOMER, $plugin_init['AVAILABLE_FOR_INTERFACE_MODE'])){
                        foreach($plugin_init['MENU'] as $menu_item){
                            $GLOBALS['MENU_CUSTOMER'][] = $menu_item;
                        }
                    }
                    if(in_array(INTERFACE_MODE_CUSTOMER_ADMINISTRATOR, $plugin_init['AVAILABLE_FOR_INTERFACE_MODE'])){
                        foreach($plugin_init['MENU'] as $menu_item){
                            $GLOBALS['MENU_CUSTOMER_ADMINISTRATOR'][] = $menu_item;
                        }
                    }
                }
            }
        }
    }

    public static function activate($plugin_key, $plugin){
        //debug($plugin['plugin_dir'] . '/.disabled');
        Db::db_query("delete from disabled_plugins where plugin_key='" . $plugin_key . "'");


        if(file_exists($plugin['plugin_dir'] . '/.disabled')){
            error_msg("Disabled marker exists on : " . basename(dirname($plugin['plugin_dir'])) . "/" . basename($plugin['plugin_dir']) . '/.disabled' . ". Please delete this file in order to activate the module!");
        }


        $plugin_init_file = $plugin['plugin_dir'].'/init.php';
        if(file_exists($plugin_init_file)) require_once $plugin_init_file;
        PluginBase::load($plugin_key, $plugin, $plugin_init);
    }

    public static function deactivate($plugin_key, $plugin=NULL){

        try{
            if(file_exists($plugin['plugin_dir'] . '/.no_disable')){
                error_msg("This plugin cannot be disabled!");
                return;
            }else{
                db::db_query("replace into disabled_plugins values ('" . $plugin_key . "', " . PLUGIN_STATUS_DISABLED . ")");
            }
        } catch(Exception $e){
            error_msg("Cannot create the plugin marker: " . $plugin['plugin_dir'] . '/.disabled');
        }

    }

    public static function status($plugin_key, $plugin=NULL){

        if(!$plugin) $plugin=$GLOBALS['PLUGINS'][$plugin_key];
        if(file_exists($plugin['plugin_dir'] . "/.no_disable")){
            if(PluginBase::check_plugin_consistency($plugin)){ self::$plugin_statuses[$plugin_key] = PLUGIN_STATUS_ENABLED; return PLUGIN_STATUS_ENABLED;}
            else{ self::$plugin_statuses[$plugin_key] = PLUGIN_STATUS_INCONSISTENT; return PLUGIN_STATUS_INCONSISTENT;}
        }
        $deactivate_marker = $plugin['plugin_dir'] . "/.disabled";
        if(file_exists($deactivate_marker)){ self::$plugin_statuses[$plugin_key] = PLUGIN_STATUS_DISABLED; return PLUGIN_STATUS_DISABLED;}

        //if a plugin depends on another plugin that is not enabled - disable it

       $depends_on = PluginBase::plugin_depends_on($plugin['plugin_name']);
       if(!empty($depends_on)){

           //debug($plugin_key . " depends on ");
           //debug($depends_on);
           foreach($depends_on as $dep){
               if(in_array($plugin_key, array_keys(self::plugin_statuses))){
                   if(self::$plugin_statuses[$plugin_key] != PLUGIN_STATUS_ENABLED){
                       debug("Plugin depends on " . $dep . " which is in status not enabled");
                       self::$plugin_statuses[$plugin_key] = PLUGIN_STATUS_DISABLED;
                       return PLUGIN_STATUS_DISABLED;
                   }
               }
               elseif(PluginBase::status($dep) != PLUGIN_STATUS_ENABLED){
                   debug("Plugin brrum depends on " . $dep . " which is in status not enabled");
                   self::$plugin_statuses[$plugin_key] = PLUGIN_STATUS_DISABLED;
                   return PLUGIN_STATUS_DISABLED;
               }
           }
       }
        if(!PluginBase::check_plugin_consistency($plugin)){self::$plugin_statuses[$plugin_key] = PLUGIN_STATUS_INCONSISTENT; return PLUGIN_STATUS_INCONSISTENT;};
        $plugin_db_status = Db::db_fetch_field("select plugin_status from disabled_plugins where plugin_key='" . $plugin_key  . "'", 'plugin_status');
        if($plugin_db_status){ self::$plugin_statuses[$plugin_key] = $plugin_db_status; return $plugin_db_status;}

        self::$plugin_statuses[$plugin_key] = PLUGIN_STATUS_ENABLED;
        return PLUGIN_STATUS_ENABLED;
    }
}
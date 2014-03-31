<?php
/**
 * Created by IntelliJ IDEA.
 * User: victor
 * Date: 3/5/14
 * Time: 3:49 PM
 * To change this template use File | Settings | File Templates.
 */

function joinPaths(){
    $args = func_get_args();
    $paths = array();
    foreach($args as $arg) {
        $paths = array_merge( $paths, (array)$arg );
    }

    foreach($paths as &$path) {
        $path = trim( $path, '/' );
    }

    $paths = array_filter($paths);

    // make sure if the path was originally an absolute path that it is kept that way
    if(substr( $args[0], 0, 1 ) == '/' ) {
        $paths[0] = '/' . $paths[0];
    }

    return join('/', $paths);

}

function get_file_ext($filename){
    return substr(strrchr($filename, '.'), 1);
}

function get_filename_without_ext($filename){
    $pos = strripos($filename, '.');
    if($pos === false){
        return $filename;
    } else {
        return substr($filename, 0, $pos);
    }
}

function get_base_url_install(){
    return sprintf(
        "%s://%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['HTTP_HOST']
    );
}

function abs_path($path){
    if (substr($path, 0, 1) == "/"){
        return $path;
    } else {
        return joinPaths(dirname(__DIR__),$path);
    }
}

function write_ini_file($assoc_arr, $path, $has_sections=FALSE) {
    $content = "";
    if ($has_sections) {
        foreach ($assoc_arr as $key=>$elem) {
            $content .= "[".$key."]\n";
            foreach ($elem as $key2=>$elem2) {
                if(is_array($elem2))
                {
                    for($i=0;$i<count($elem2);$i++)
                    {
                        $content .= $key2."[] = \"".$elem2[$i]."\"\n";
                    }
                }
                else if($elem2=="") $content .= $key2." = \n";
                else $content .= $key2." = \"".$elem2."\"\n";
            }
        }
    }
    else {
        foreach ($assoc_arr as $key2=>$elem) {
            if(is_array($elem))
            {
                for($i=0;$i<count($elem);$i++)
                {
                    $content .= $key2."[] = \"".$elem[$i]."\"\n";
                }
            }
            else if($elem=="") $content .= $key2 . " = \n";
            else $content .= $key2 . " = " . $elem . "\n";
        }
    }
    if(!is_writable(dirname($path))) return false;
    if (!$handle = fopen($path, 'w')) {
        return false;
    }
    if (!fwrite($handle, $content)) {
        return false;
    }
    fclose($handle);

    return true;
}

function install_clean_database($db_file, $db_host, $db_user, $db_pass, $db_name){
    if( ! defined('DB_HOST')) define('DB_HOST', $db_host);
    if( ! defined('DB_USER')) define('DB_USER', $db_user);
    if( ! defined('DB_PWD')) define('DB_PWD', $db_pass);
    if( ! defined('DB_NAME')) define('DB_NAME', $db_name);
    require_once(abs_path('install/db.php'));
    require_once(abs_path('install/db_schema_update.php'));

    /*if(file_exists($db_file)){
        $query = "source '" . $db_file . "'";
        return db::db_query($query);
    }*/
    $bb=true;
    foreach($tables as $table_name=>$tsa){
        $create_stmt = $tsa['create_stmt'];
        $bb &= db::db_query('drop table if exists ' . $table_name);
        $bb &= db::db_query($create_stmt);
    }
    $q = "insert into users (id, login, fname, lname, password, email, administrator, `type`, is_manager, allow_private, customer_id) values  (1, 'admin', 'admin', '', md5('admin1234'), 'admin@yourdomain.com', 1, 2, 1, 1, 0)";
    db::db_query($q);
    db::db_query("insert into users_customers values (1,0)");
    db::db_query("REPLACE INTO `tickets_types` VALUES (1,'Support',1,0,1),(2,'Task',0,0,1),(3,'Labo',0,0,0),(4,'RFQ',0,0,0),(5,'Planning',0,0,0),(6,'SALES',0,0,0)");
    db::db_query("REPLACE INTO `tickets_statuses` VALUES (1,'New',0),(2,'Opened',0),(3,'Waiting',0),(10,'Closed',0),(5,'Overdue',0),(11,'Scheduled',0),(12,'WFC',0),(13,'Confirmed',0),(14,'TBS',0)");
    db::db_query("REPLACE INTO `acl_categories` VALUES (1,'Computers'),(2,'Monitor profiles'),(3,'Users'),(4,'Customer - Krifs'),(5,'Krifs'),(6,'Customers Informations'),(7,'Asterisk'),(8,'Customer - Kawacs'),(9,'Customer - Kerm'),(10,'Customer Contracts')");
    db::db_query("REPLACE INTO `acl_items` VALUES (1,'View computers',0,1),(2,'Edit computers',0,1),(3,'Delete computers',0,1),(4,'View profiles',0,2),(5,'Edit profiles',0,2),(6,'Delete profiles',0,2),(7,'Edit monitor items',0,2),(8,'View users',0,3),(9,'Edit users',0,3),(10,'Delete users',0,3),(11,'View tickets',0,4),(12,'Edit tickets',0,4),(13,'View interventions',0,4),(14,'Krif',0,5),(15,'Customer licenses',0,6),(16,'Peripherals',0,6),(17,'Active directory Informations',0,6),(18,'Contrats et informations customers',0,6),(19,'Phone operator',0,7),(21,'View Dashboards',0,8),(22,'Manage Users',0,9),(23,'View contracts',0,10)");
    db::db_query("REPLACE INTO `acl_items_operations` VALUES (113,2,'kawacs','computer_edit_item'),(114,2,'kawacs','computer_edit_item_del'),(115,2,'kawacs','computer_profile'),(116,2,'kawacs','computer_remote_access'),(117,2,'kawacs','computer_type'),(118,2,'kawacs','computer_view'),(119,2,'kawacs','computer_view_log'),(408,1,'kawacs','alert_add'),(121,7,'kawacs','manage_monitor_items'),(122,7,'kawacs','monitor_item_add'),(123,7,'kawacs','monitor_item_delete'),(110,1,'kawacs','computer_profile'),(111,1,'kawacs','computer_view_log'),(112,1,'kawacs','manage_computers'),(124,7,'kawacs','monitor_item_edit'),(125,7,'kawacs','monitor_item_submit'),(126,3,'customer_krifs','manage_tickets'),(130,12,'customer_krifs','ticket_edit'),(129,12,'customer_krifs','ticket_add'),(131,11,'customer_krifs','manage_tickets'),(132,12,'customer_krifs','manage_tickets'),(133,12,'customer_krifs','ticket_attachment_add'),(134,12,'customer_krifs','ticket_attachment_delete'),(135,12,'customer_krifs','ticket_attachment_open'),(136,12,'customer_krifs','ticket_escalate'),(137,13,'customer_krifs','manage_interventions'),(279,14,'krifs','ticket_add_intervention'),(278,14,'krifs','ticket_add_check_notifs'),(277,14,'krifs','ticket_add'),(272,14,'krifs','status_edit'),(271,14,'krifs','status_delete'),(270,14,'krifs','status_add'),(269,14,'krifs','search_ticket'),(268,14,'krifs','search_intervention'),(267,14,'krifs','saved_search_edit'),(266,14,'krifs','saved_search_add'),(265,14,'krifs','report_krifs_outstanding_tickets'),(264,14,'krifs','popup_ticket_search_by_user'),(263,14,'krifs','popup_ticket_add_objects'),(262,14,'krifs','popup_ticket_add_cc_users'),(261,14,'krifs','popup_hours'),(260,14,'krifs','popup_fill_ts_gaps'),(259,14,'krifs','popup_activity'),(255,14,'krifs','manage_tickets'),(422,1,'kawacs','cklogs_free_space'),(230,14,'krifs','activity_edit'),(229,14,'krifs','activity_delete'),(228,14,'krifs','activity_category_edit'),(227,14,'krifs','activity_category_delete'),(226,14,'krifs','activity_category_add'),(225,14,'krifs','activity_add'),(224,14,'krifs','action_type_edit'),(421,1,'kawacs','cklogs_ad_computers'),(222,14,'krifs','action_type_add'),(280,14,'krifs','ticket_attachment_add'),(281,14,'krifs','ticket_attachment_delete'),(282,14,'krifs','ticket_attachment_open'),(283,14,'krifs','ticket_detail_edit'),(284,14,'krifs','ticket_detail_edit_user'),(285,14,'krifs','ticket_detail_intervention'),(286,14,'krifs','ticket_edit'),(287,14,'krifs','ticket_edit_cc'),(288,14,'krifs','ticket_edit_customer'),(289,14,'krifs','ticket_edit_manager_comments'),(290,14,'krifs','ticket_mark_working'),(291,14,'krifs','ticket_object_add'),(293,14,'krifs','ticket_unmark_working'),(420,1,'kawacs','cklogs_1030_1046'),(419,1,'kawacs','blackouts_remove'),(418,1,'kawacs','blackouts_edit'),(417,1,'kawacs','blackout_computer'),(416,1,'kawacs','alert_profiles_edit'),(415,1,'kawacs','alert_edit_recips'),(301,14,'krifs','type_add'),(302,14,'krifs','type_delete'),(303,14,'krifs','type_edit'),(304,14,'krifs','xml_get_customer_cc_recipients'),(305,14,'krifs','xml_get_schedules'),(308,15,'kalm','license_computers'),(314,15,'kalm','license_file_open'),(551,15,'kalm','manage_licenses'),(321,15,'kalm','software_customers'),(327,16,'kawacs','peripheral_add'),(328,16,'kawacs','peripheral_class_add'),(329,16,'kawacs','peripheral_class_customers'),(330,16,'kawacs','peripheral_class_delete'),(331,16,'kawacs','peripheral_class_edit'),(332,16,'kawacs','peripheral_class_peripherals'),(333,16,'kawacs','peripheral_class_profile'),(334,16,'kawacs','peripheral_class_profile_remove'),(335,16,'kawacs','peripheral_delete'),(336,16,'kawacs','peripheral_edit'),(337,16,'kawacs','peripheral_edit_snmp'),(338,16,'kawacs','peripheral_field_add'),(339,16,'kawacs','peripheral_field_delete'),(366,18,'klara','access_phone_edit'),(365,18,'klara','access_phone_delete'),(363,17,'kerm','logon_computers'),(362,17,'kerm','ad_user_view'),(361,17,'kerm','ad_printer_warranty_edit'),(360,17,'kerm','ad_printer_view'),(359,17,'kerm','ad_printer_location'),(358,17,'kerm','ad_printer_edit_snmp'),(357,17,'kerm','ad_printer_date'),(356,17,'kerm','ad_group_view'),(364,18,'klara','access_phone_add'),(355,17,'kerm','ad_computer_view'),(367,18,'klara','computer_password_add'),(368,18,'klara','computer_password_delete'),(369,18,'klara','computer_password_edit'),(370,18,'klara','computer_password_expire'),(371,18,'klara','computer_passwords_expired'),(372,18,'klara','computer_remote_service_add'),(373,18,'klara','computer_remote_service_delete'),(374,18,'klara','computer_remote_service_edit'),(375,18,'klara','computer_remote_services'),(376,18,'klara','customer_internet_contract_add'),(377,18,'klara','customer_internet_contract_attachment_add'),(378,18,'klara','customer_internet_contract_attachment_delete'),(379,18,'klara','customer_internet_contract_attachment_open'),(380,18,'klara','customer_internet_contract_delete'),(381,18,'klara','customer_internet_contract_edit'),(382,18,'klara','customer_internet_contract_remove_again_mark'),(383,18,'klara','customer_internet_contract_remove_mark'),(384,18,'klara','customer_internet_contract_set_notifs'),(385,18,'klara','manage_access'),(386,18,'klara','manage_access_phones'),(387,18,'klara','manage_customer_internet_contracts'),(409,1,'kawacs','alert_condition_add'),(390,18,'klara','provider_contact_add'),(414,1,'kawacs','alert_edit_fields_send'),(413,1,'kawacs','alert_edit'),(412,1,'kawacs','alert_delete'),(395,18,'klara','provider_contact_phone_edit'),(396,18,'klara','provider_contract_add'),(411,1,'kawacs','alert_condition_edit'),(399,18,'klara','provider_customers'),(410,1,'kawacs','alert_condition_delete'),(401,18,'klara','provider_edit'),(402,18,'klara','remote_access_add'),(403,18,'klara','remote_access_delete'),(404,18,'klara','remote_access_edit'),(405,17,'kerm','manage_ad_computers'),(406,17,'kerm','manage_ad_printers'),(407,17,'kerm','manage_ad_users'),(423,1,'kawacs','computer_add'),(424,1,'kawacs','computer_comments'),(425,1,'kawacs','computer_customer'),(426,1,'kawacs','computer_date_created'),(427,1,'kawacs','computer_event_unignore'),(428,1,'kawacs','computer_events_revert_to_profile'),(429,1,'kawacs','computer_events_settings'),(430,1,'kawacs','computer_events_settings_edit'),(431,1,'kawacs','computer_events_src_add'),(432,1,'kawacs','computer_events_src_delete'),(433,1,'kawacs','computer_events_src_edit'),(434,1,'kawacs','computer_location'),(435,1,'kawacs','computer_mac_edit'),(436,1,'kawacs','computer_name_swings_clean'),(437,1,'kawacs','computer_note_add'),(438,1,'kawacs','computer_note_delete'),(439,1,'kawacs','computer_note_edit'),(440,1,'kawacs','computer_remote_access'),(441,1,'kawacs','computer_report_av'),(442,1,'kawacs','computer_report_backup_sizes'),(443,1,'kawacs','computer_report_backups'),(444,1,'kawacs','computer_report_partitions'),(445,1,'kawacs','computer_roles'),(446,1,'kawacs','computer_type'),(447,1,'kawacs','computer_view'),(448,1,'kawacs','computer_view_item'),(449,1,'kawacs','computer_view_item_detail'),(452,1,'kawacs','computers_linux_agent_versions'),(453,1,'kawacs','computers_linux_agent_versions_details'),(454,1,'kawacs','computers_merge'),(455,1,'kawacs','computers_merge_confirm'),(456,1,'kawacs','computers_merge_finished'),(457,1,'kawacs','customer_allowed_ip_add'),(458,1,'kawacs','customer_allowed_ip_delete'),(459,1,'kawacs','customers_allowed_ips'),(460,1,'kawacs','customers_computer_count'),(461,1,'kawacs','inventory_search_advanced'),(465,1,'kawacs','kawacs_inventory_dashboard'),(466,1,'kawacs','kawacs_linux_update_add'),(467,1,'kawacs','kawacs_linux_update_delete'),(468,1,'kawacs','kawacs_linux_update_edit'),(469,1,'kawacs','kawacs_linux_update_publish'),(470,1,'kawacs','kawacs_update_add'),(471,1,'kawacs','kawacs_update_delete'),(472,1,'kawacs','kawacs_update_edit'),(473,1,'kawacs','kawacs_update_preview_add'),(474,1,'kawacs','kawacs_update_preview_delete'),(475,1,'kawacs','kawacs_update_publish'),(476,1,'kawacs','kawacs_update_remove_file'),(479,1,'kawacs','manage_kawacs_linux_updates'),(480,1,'kawacs','manage_kawacs_updates'),(482,1,'kawacs','manage_monitor_items_peripherals'),(486,1,'kawacs','manage_peripherals'),(489,1,'kawacs','manage_profiles_periph'),(492,1,'kawacs','monitor_item_add'),(494,1,'kawacs','monitor_item_edit'),(495,1,'kawacs','monitor_item_submit'),(514,1,'kawacs','monitored_ip_add'),(515,1,'kawacs','monitored_ip_delete'),(516,1,'kawacs','monitored_ip_edit'),(517,1,'kawacs','open_item_file'),(518,1,'kawacs','peripheral_add'),(520,1,'kawacs','peripheral_class_customers'),(521,1,'kawacs','peripheral_class_delete'),(522,1,'kawacs','peripheral_class_edit'),(523,1,'kawacs','peripheral_class_peripherals'),(525,1,'kawacs','peripheral_class_profile_remove'),(526,1,'kawacs','peripheral_delete'),(527,1,'kawacs','peripheral_edit'),(528,1,'kawacs','peripheral_edit_snmp'),(530,1,'kawacs','peripheral_field_delete'),(531,1,'kawacs','peripheral_field_edit'),(532,1,'kawacs','peripheral_field_order'),(533,1,'kawacs','peripheral_location'),(540,1,'kawacs','popup_traceroute'),(546,1,'kawacs','search_computer'),(547,1,'kawacs','search_serial'),(548,1,'kawacs','valid_dup_name_add'),(549,1,'kawacs','valid_dup_name_delete'),(550,1,'kawacs','valid_dup_names'),(552,15,'kalm','manage_software'),(553,18,'customer','cc_recipients_edit'),(554,18,'customer','customer_add'),(555,18,'customer','customer_comment_add'),(556,18,'customer','customer_comment_delete'),(557,18,'customer','customer_contact_add'),(558,18,'customer','customer_contact_edit'),(559,18,'customer','customer_contact_phone_add'),(560,18,'customer','customer_contact_phone_edit'),(561,18,'customer','customer_delete'),(562,18,'customer','customer_edit'),(563,18,'customer','customer_lock'),(564,18,'customer','customer_photo_delete'),(565,18,'customer','customer_photo_edit'),(566,18,'customer','customer_photo_show'),(567,18,'customer','customer_photo_view'),(568,18,'customer','customer_report'),(569,18,'customer','customer_view'),(570,18,'customer','location_comment_add'),(571,18,'customer','location_comment_delete'),(572,18,'customer','location_computers'),(573,18,'customer','location_fixed_customers'),(574,18,'customer','location_fixed_edit'),(575,18,'customer','manage_customers'),(576,18,'customer','manage_customers_comments'),(577,18,'customer','manage_customers_contacts'),(578,19,'asterisk','detect'),(579,21,'customer_kawacs','kawacs_antivirus_dashboard'),(580,21,'customer_kawacs','kawacs_backup_dashboard'),(581,22,'customer_kerm','manage_users'),(582,22,'customer_kerm','add_user'),(583,22,'customer_kerm','modify_user'),(584,11,'customer_krifs','customer_satisfaction'),(585,23,'customer_contracts','customer_manage_contracts')");
    db::db_query("REPLACE INTO `acl_roles` VALUES (1,'Support assistant',1),(2,'Computers technician',1),(3,'Users manager',1),(4,'Secretary',1),(5,'Computers manager',1),(6,'Customer',2),(7,'KeyOS Opérateur',1),(8,'Customers Administrator',1)");
    db::db_query("REPLACE INTO `acl_roles_items` VALUES (1,1),(2,1),(3,1),(5,1),(6,1),(7,1),(8,1),(2,2),(5,2),(7,2),(2,3),(5,3),(2,4),(3,4),(5,4),(7,4),(8,4),(8,5),(2,8),(4,8),(5,8),(7,8),(8,8),(1,11),(2,11),(3,11),(5,11),(6,11),(7,11),(8,11),(1,12),(5,12),(6,12),(7,12),(8,12),(1,13),(5,13),(6,13),(7,13),(8,13),(1,14),(3,14),(5,14),(8,14),(2,15),(3,15),(5,15),(8,15),(2,16),(3,16),(5,16),(8,16),(2,17),(3,17),(5,17),(8,17),(2,18),(3,18),(5,18),(8,18),(6,21),(6,22),(6,23)");
    
    return $bb;
}

function set_customer($customer_name){
    if( ! defined('DB_HOST')) define('DB_HOST', $db_host);
    if( ! defined('DB_USER')) define('DB_USER', $db_user);
    if( ! defined('DB_PWD')) define('DB_PWD', $db_pass);
    if( ! defined('DB_NAME')) define('DB_NAME', $db_name);
    require_once(abs_path('install/db.php'));
    require_once(abs_path('install/db_schema_update.php'));
    $query = "REPLACE INTO `customers` VALUES (2,'" . db::db_escape($customer_name) . "','0',NULL,'','','','','','','',0,'','','','','',1,'','','','','12.00',0,1,1,0,1,0,0,8,'','',5,3,1)";
    $bb=true;
    db::db_query($query);
    return $bb;
}

function apply_database_update($db_host, $db_user, $db_pass, $db_name){
    if( ! defined('DB_HOST')) define('DB_HOST', $db_host);
    if( ! defined('DB_USER')) define('DB_USER', $db_user);
    if( ! defined('DB_PWD')) define('DB_PWD', $db_pass);
    if( ! defined('DB_NAME')) define('DB_NAME', $db_name);
    require_once(abs_path('install/db.php'));
    require_once(abs_path('install/db_schema_update.php'));

    $existing_tables = db::db_fetch_vector("show tables");

    foreach($tables as $table_name=>$tsa){
        $create_stmt = $tsa['create_stmt'];
        if( ! in_array($table_name, $existing_tables)){
           debug("execute creation script: " . $create_stmt);
           db::db_query($create_stmt);
        } else {
           //table  exists, see for each field if it exists
            $fields =  array();
            preg_match_all('/(`\w+`)+\s+(\w+\(*\d*\)*)+/', $create_stmt, $fields);
            foreach($fields[1] as $k => $field){
               $fld = trim($field, '`');
               $query = "select count(*) as cnt from information_schema.columns where table_name='" . $table_name . "' and column_name='" . $fld . "'";
               $bF = db::db_fetch_field($query, 'cnt');
               if($bF == 0){
                   $query = "ALTER TABLE " . $table_name . " add " . $fields[0][$k];
                   debug("executing stmt: " . $query);
                   db::db_query($query);
               }
            }
        }
    }
}


function debug($v){
    print("<pre>");
    print_r($v);
    print("</pre>");
}

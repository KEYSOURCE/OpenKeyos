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
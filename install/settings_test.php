<?php
/**
 * Created by IntelliJ IDEA.
 * User: victor
 * Date: 3/6/14
 * Time: 5:01 PM
 * To change this template use File | Settings | File Templates.
 */
require_once __DIR__ . '/install_lib.php';

function test_database_connection($db_host, $db_user, $db_password, $db_name, &$err=null)
{
    $link = mysqli_connect($db_host, $db_user, $db_password);
    if(!$link) {
        $err = $link->connect_errno . " - " . $link->connect_error;
        return false;
    }
    if(!$db_name) {
        $err .= "No database selected";
        return false;
    }
    if(! mysqli_select_db($link, $db_name)){
        $err .= $link->errno() . " - " . $link->error();
        return false;
    }
    $link->close();
    return true;
}

function test_smarty_settings($cache_dir, $config_dir, $compile_dir, &$err=null){
    $ret = true;
    if(!file_exists(abs_path($cache_dir))){
        mkdir(abs_path($cache_dir), 0777, true);
    }
    if(!file_exists(abs_path($config_dir))){
        mkdir(abs_path($config_dir), 0755, true);
    }
    if(!file_exists(abs_path($compile_dir))){
        mkdir(abs_path($compile_dir), 0777, true);
    }


    if(!file_exists(abs_path($cache_dir))){
        $err .= "Smarty cache directory doesn't exist!<br />";
        $ret = false;
    }
    if(!file_exists(abs_path($config_dir))){
        $err .= "Smarty config directory doesn't exist!<br />";
        $ret = false;
    }
    if(!file_exists(abs_path($compile_dir))){
        $err .= "Smarty template compilation directory doesn't exist!<br />";
        $ret = false;
    }

    if(file_exists(abs_path($cache_dir))) chmod(abs_path($cache_dir), 0777);
    if(file_exists(abs_path($compile_dir))) chmod(abs_path($compile_dir), 0777);
    //check directory permissions and ensure the cache and compile dirs are writable
    if(!is_writable(abs_path($cache_dir))){
        $err .= "The cache directory must be writable!<br />";
        $ret = false;
    }
    if(!is_writable(abs_path($compile_dir))){
        $err .= "The template compilation directory must be writable!<br />";
        $ret = false;
    }

    return $ret;
}

function test_cache_settings($use_caching, $cache_engine, $cache_default_ttl, $cache_default_server, $cache_default_port, &$err){
    $ret = true;
    $err = "";

    if(!$use_caching) return $ret;

    if($cache_engine == 'redis'){
        //check if the Redis class is avaiable
        if(!class_exists('Redis')){
            $ret = false;
            $err .= "It seems that the phpredis extension is not installed. Please install it and test again.";
        }
        //test the redis connection
        if(class_exists('Redis')){
            $r = new Redis();
            if(!$r->connect($cache_default_server, $cache_default_port)){
                $ret = false;
                $err .= "Could not connect to the redis server on " . $cache_default_server . " on port " . $cache_default_port;
            }
            if(!$r->ping()){
                $ret = false;
                $err .= "Redis server didn't respond to our ping";
            }
        } else {
            $ret = false;
            $err .= "It seems that the phpredis extension is not installed. Please install it from here: https://code.google.com/p/phpredis/ and test again.";
        }
    }

    if($cache_engine == "memcache"){
        if(!class_exists('Memcache')){
            $ret = false;
            $err .= "It seems that the php5-memcache is not installed. Please install it and test again";
        }
        if(class_exists('Memcache')){
            $mc = new Memcache();
            if(!$mc->connect($cache_default_server, $cache_default_port)){
                $ret = false;
                $err .= 'Could not connect to the memcache server on ' . $cache_default_server . " on port " . $cache_default_port;
            }
            else{
                $err .= "connected to " . $cache_default_server  . " on port " . $cache_default_port;
            }
        } else {
            $ret = false;
            $err .= "It seems that the memcache php extension is not installed. Please install php5-memcache and try again";
        }
    }

    return $ret;
}

switch($_REQUEST['test_type']){
    case "database":
        $err = "";
        $result = test_database_connection($_REQUEST['db_host'], $_REQUEST['db_user'], $_REQUEST['db_password'], $_REQUEST['db_name'], $err);
        $response = array(
            'result' => $result ? 'ok' : 'ko',
            'observations' => $err,
        );

        echo json_encode($response);
        break;
    case "smarty":
        $err = "";
        $result = test_smarty_settings($_REQUEST['smarty_cache_dir'], $_REQUEST['smarty_config_dir'], $_REQUEST['smarty_config_dir'], $err);
        $response = array(
            'result' => $result ? 'ok' : 'ko',
            'observations' => $err,
        );
        echo json_encode($response);
        break;
    case "cache":
        $err = "";
        $result = test_cache_settings($_REQUEST['use_caching'], $_REQUEST['cache_engine'], $_REQUEST['cache_default_ttl'], $_REQUEST['cache_default_server'], $_REQUEST['cache_default_port'], $err);
        $response = array(
            'result' => $result ? 'ok' : 'ko',
            'observations' => $err,
        );
        echo json_encode($response);
        break;
    default:
        echo json_encode(array('result'=> 'ko'));
        break;
}
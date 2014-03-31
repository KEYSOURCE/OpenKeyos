<?php
/**
 * CacheWrapper Factory singleton
 */
require_once __DIR__ . '/redis_wrapper.php';
require_once __DIR__ . '/memcache_wrapper.php';

abstract class CacheWrapper{
    private static $_instances = array();
    public static function getInstance($type=null, $options = array()){

        if(!USE_CACHING) return null;
        if(null == $type) $type="redis";
        $c = "RedisWrapper";
        if($type == "redis") $c = "RedisWrapper";
        elseif($type == "memcache") $c = "MemcacheWrapper";
        if(class_exists($c)){
            //debug('here: ' . $c);
            self::$_instances[$type] = new $c($options);
        }
        return self::$_instances[$type];
    }

    public abstract function get_cache($key);
    public abstract function set_cache($key, $value, $ttl);
    public abstract function get_status();
    public abstract function key_exists($key);
    public abstract function delete_key($key);
}
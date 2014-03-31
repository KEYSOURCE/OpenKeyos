<?php
/**
 * Created by
 * User: victor
 * Date: 2/20/14
 * Time: 12:21 PM
 * Redis server wrapper
 */
require_once __DIR__ . "/lib.php";
require_once __DIR__ . "/cache_wrapper.php";

class RedisWrapper extends CacheWrapper{

    const default_redis_server = "127.0.0.1";
    const default_redis_port = "6379";
    const default_redis_scheme = "tcp";
    const default_redis_ttl = "86400";

    private $server;
    private $port;
    private $scheme;
    private $ttl;
    private $obj_redis;
    private $is_running = FALSE;

    protected function __construct($options = array()){
        $this->server = ( ! empty($options['server'])) ? $options['server'] : self::default_redis_server;
        $this->port = ( ! empty($options['port'])) ? $options['port'] : self::default_redis_port;
        $this->scheme = ( ! empty($options['scheme'])) ? $options['scheme'] : self::default_redis_scheme;

        try{
            $this->obj_redis = new Redis();
            $this->obj_redis->connect($this->server, $this->port);
            //debug('REDIS CONNECTED');
            $this->is_running = TRUE;
        } catch (Exception $e){
            $this->is_running = FALSE;
            do_log('REDIS CONNECTION ERROR:' . $e->getMessage(), LOG_LEVEL_ERRORS);
        }

    }

    public function get_status(){
        return $this->is_running;
    }

    public function get_cache($key){
        return $this->obj_redis->get($key);
    }

    public function set_cache($key, $value, $ttl = null){
        $set_ttl = $ttl ? $ttl : self::default_redis_ttl;
        $this->obj_redis->setex($key, $set_ttl, $value);

        //$this->obj_redis->expire($key, $set_ttl);
    }

    public function key_exists($key){
        return $this->obj_redis->exists($key);
    }

    public function delete_key($key){
        $this->obj_redis->del($key);
    }
}
<?php
/**
 * Created by IntelliJ IDEA.
 * User: victor
 * Date: 3/18/14
 * Time: 11:01 AM
 * To change this template use File | Settings | File Templates.
 */

require_once __DIR__ . "/lib.php";
require_once __DIR__ . "/cache_wrapper.php";

class MemcacheWrapper extends CacheWrapper{

    const default_memcache_server = "127.0.0.1";
    const default_memcache_port = "11211";
    const default_memcache_scheme = "tcp";
    const default_memcache_ttl = "86400";

    private $server;
    private $port;
    private $scheme;
    private $ttl;
    private $obj_memcache;
    private $is_running = FALSE;

    public function __construct(){
        $this->server = ( ! empty($options['server'])) ? $options['server'] : self::default_memcache_server;
        $this->port = ( ! empty($options['port'])) ? $options['port'] : self::default_memcache_port;
        $this->scheme = ( ! empty($options['scheme'])) ? $options['scheme'] : self::default_memcache_scheme;

        try{
            $this->obj_memcache = new Memcache();
            $this->obj_memcache->connect($this->server, $this->port);
            //debug('MEMCACHE CONNECTED');
            $this->is_running = TRUE;
        } catch (Exception $e){
            $this->is_running = FALSE;
            //do_log('MEMCACHE CONNECTION ERROR:' . $e->getMessage(), LOG_LEVEL_ERRORS);
        }
    }

    public function get_cache($key){
        return $this->obj_memcache->get($key);
    }
    public function set_cache($key, $value, $ttl){
        $set_ttl = $ttl ? $ttl : self::default_memcache_ttl;
        $this->obj_memcache->set($key, $value, MEMCACHE_COMPRESSED, $set_ttl);
    }
    public function get_status(){
        return $this->is_running;

    }
    public function key_exists($key){
        return (bool)$this->obj_memcache->get($key);
    }
    public function delete_key($key){
        $this->obj_memcache->delete($key);
    }
}
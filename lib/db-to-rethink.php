<?php

interface Db{
    static function db_connect();
    static function db_query($q = '');
    static function db_fetch_row($q = '');
    static function db_fetch_array($q = '');
    static function db_fetch_list($q = '');
    static function db_fetch_vector($q = '');
    static function db_fetch_field($q = '', $field = '');
    static function db_get_next($h = null);
    static function db_insert_id();
    static function db_error();
    static function get_client_encoding();
}
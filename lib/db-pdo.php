<?php

class DbPDO
{
    public static function db_connect(){
        if (!defined('PDO_CONNECTION'))
        {
            try {
                $dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PWD);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $dbh->exec("set names ISO-8859-1");
                define('PDO_CONNECTION', true);
                $GLOBALS['PDO_CONN'] = $dbh;
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        return $GLOBALS['PDO_CONN'];
    }

    public static function db_query($query="", $params=array()){
        $conn = Db::db_connect();
        if($query){
            debug($query);
            $stmt = $conn->prepare($query);
            foreach($params as $k=>$val){
                $stmt->bindParam($k, $val);
            }
            $t1 = time();
            $result = $stmt->execute();
            if (!$result)
            {
                $msg = 'Query error: '.$conn->errorInfo() . " : " . $conn->errorCode().'; query: '.$query;
                if (function_exists('debug_backtrace'))
                {
                    $trace = debug_backtrace();
                    $msg.= "\n".'  CALL TRACE: ';
                    for ($i=count($trace)-1; $i>=0; $i--)
                    {
                        $msg.= "\n    ".$trace[$i]['class'].$trace[$i]['type'].$trace[$i]['function'].' ';
                        $msg.= '('.$trace[$i]['line'].': '.$trace[$i]['file'].')';
                    }
                }
                do_log ($msg, LOG_LEVEL_ERRORS);

                error_msg ('Query error, check error log: ' . $conn->errorCode());
                return false;
            }
            else
            {
                $t2 = time();
                if ($t2 - $t1 > 5) do_log ('Long query: '.($t2-$t1).' :: '.$query, LOG_LEVEL_ERRORS);
                return $stmt;
            }
        }
    }

    public static function  db_fetch_row($query=''){

        $ret = array();
        if ($query)
        {
            $conn = Db::db_connect();
            $stmt = Db::db_query($query);
            if($stmt){
                $result = $stmt->fetchAll();
                if (!$result)
                {
                    do_log ('Query error: '.$query.'<br/>:: '. $conn->errorInfo() , LOG_LEVEL_ERRORS);
                }

                if(count($result) > 0) $ret=$result[0];
            }

        }
        return $ret;
    }

    public static function db_fetch_array ($query = '')
    {
        $ret = array();
        if ($query)
        {
            $conn = Db::db_connect();
            $stmt = Db::db_query($query);
            if(!$stmt){
               do_log ('Query error: '.$query.'<br/>:: '. $conn->errorInfo() , LOG_LEVEL_ERRORS);
            }

            while (($row = $stmt->fetchObject()))
            {
                $ret[] = $row;
            }

            if ($conn->errorCode() != '00000')
            {
                do_log ('Query error: '.$query .'<br/>:: '.$conn->errorInfo(), LOG_LEVEL_ERRORS);
                echo ('Query error: '.$query.'<br/>:: '.$conn->errorInfo());
            }
        }
        return $ret;
    }

    public static function db_fetch_list ($query = '')
    {
        $ret = array();
        if ($query)
        {
            $conn = Db::db_connect();
            $stmt = Db::db_query($query);
            if (!$stmt)
            {
                do_log ('Query error: '.$query.'<br/>:: '.$stmt->errorInfo(), LOG_LEVEL_ERRORS);
            }

            while (($row = $stmt->fetch(PDO::FETCH_BOTH)))
            {
                $ret[$row[0]] = $row[1];
            }

            if ($conn->errorCode() != '00000')
            {
                do_log ('Query error: '.$query.'<br/>:: '.$conn->errorInfo(), LOG_LEVEL_ERRORS);
                echo ('Query error: '.$query.'<br/>:: '.$conn->errorInfo());
            }
        }
        return $ret;
    }

    public static function db_fetch_vector ($query = '')
    {
        $ret = array();
        if ($query)
        {
            $conn = Db::db_connect();
            $stmt = Db::db_query($query);
            if ($stmt)
            {
                do_log ('Query error: '.$query.'<br/>:: '.$stmt->errorInfo(), LOG_LEVEL_ERRORS);
            }

            while (($row = $stmt->fetch(PDO::FETCH_BOTH)))
            {
                $ret[] = $row[0];
            }

            if ($conn->errorCode() != '00000')
            {
                do_log ('Query error: '.$query.' :: '.$conn->errorInfo(), LOG_LEVEL_ERRORS);
                echo ('Query error: '.$query.' :: '.$conn->errorInfo());
            }
        }
        return $ret;
    }

    public static function db_fetch_field ($query = '', $field = '')
    {
        $ret = null;
        if ($query)
        {
            $conn = Db::db_connect();
            $stmt = Db::db_query($query);

            if(!$stmt){
                do_log ('Query error: '.$query.'<br/>:: '.$stmt->errorInfo(), LOG_LEVEL_ERRORS);
            }

            $ret = $stmt->fetchColumn();

            if ($conn->errorCode()!='00000')
            {
                do_log ('Query error: '.$query.' :: '.$conn->errorInfo(), LOG_LEVEL_ERRORS);
            }
        }
        return $ret;
    }


    public static function db_insert_id ()
    {
        $conn = Db::db_connect();
        return $conn->lastInsertId();
    }

    public static function db_error ()
    {
        $conn = Db::db_connect();
        $ret = $conn->errorInfo();
        $ret = (empty($ret));
        return $ret;
    }

    public static function get_client_encoding(){
        return 'ISO-8859-1';
    }

}
<?php
/*
 * Created on Oct 24, 2009
 *
 * apisoft sycronizer responder
 * this script is the entry-point for the data exchanged with the APISOFT keyos client
 */

 list($usec, $sec)  = explode(" ", microtime());
 require_once(dirname(__FILE__)."/lib/lib.php");
set_error_die_function("apisoftsync_die");
set_error_handler('ks_error_handler');

class_load('ApisoftSync');

//get a soap server instance
$server = new  ApisoftSync();
$HTTP_RAW_POST_DATA = (isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : "");
$server->service($HTTP_RAW_POST_DATA);

exit;

function apisoftsync_die()
{
	echo "ERROR\n";
	echo preg_replace('/<br (\/) *>/', "\n", error_msg());
	exit(1);
}

?>

<?php
	/**
	 * @package KERM
	 * @subpackage KERM_SOAP_SERVER
	 */
	
list($usec, $sec) = explode(" ", microtime());
$GLOBALS['t_start_time'] = ((float)$usec + (float)$sec);
$GLOBALS['kerm_request'] = true;

require_once(dirname(__FILE__).'/lib/lib.php');
set_error_die_function ('kerm_gracefully_die');
set_error_handler ('ks_error_handler');

class_load('KermServer');


$fp = fopen (dirname(__FILE__).'/logs/direct.log', 'a');
fwrite ($fp, date('d-m-y H:i:s').' '.$_SERVER['REQUEST_METHOD']." -------------\n".$HTTP_RAW_POST_DATA);
foreach ($_REQUEST as $k => $v) fwrite ($fp, "$k::$v\n");
fclose ($fp);

if (!$_REQUEST['data_form'])
{
	// This is a normal SOAP request
	
	$server = new KermServer();
	
	$HTTP_RAW_POST_DATA = (isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');
	
	
	$server->service($HTTP_RAW_POST_DATA);
}
elseif ($_REQUEST['data_form'] == 'plain')
{
	// This is a direct data sending
	
	$res =$_POST;
	
	if (empty_error_msg ()) echo "OK\n";
	else
	{
		echo "ERROR\n";
		$err_msg = preg_replace ('/<br(\/)*>/', "\n", error_msg ());
		echo $err_msg;
	}
}
exit;

function kerm_gracefully_die ()
{
	echo "ERROR\n";
	echo preg_replace ('/<br(\/)*>/', "\n", error_msg ());
	exit (1);
}
?>
<?
/**
* KAWACS server responder
*
* This script is the entry-point for data reported by Kawacs Agents or by 
* other reporting scripts.
*
* If the 'data_form' field is not present in the HTTP request, then it acts like a SOAP server.
* If the 'date_form' field has the value 'plain', then it interprets the request as a 
* direct report from a custom script.
*
* @package
* @subpackage KAWACS_SOAP_SERVER
*/

list($usec, $sec) = explode(" ", microtime());
$GLOBALS['start_time'] = ((float)$usec + (float)$sec);
$GLOBALS['kawacs_request'] = true;

require_once(dirname(__FILE__).'/lib/lib.php');
set_error_die_function ('kawacs_gracefully_die');
set_error_handler ('ks_error_handler');
class_load('KawacsServer');


if (!$_REQUEST['data_form'])
{
	// This is a normal SOAP request

	$server = new KawacsServer();
	
	$HTTP_RAW_POST_DATA = (isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');
	$server->service($HTTP_RAW_POST_DATA);
}
elseif ($_REQUEST['data_form'] == 'plain')
{
	// This is a direct data sending
	
	$res = process_direct_data ($_POST);
	if (empty_error_msg ()) echo "OK\n";
	else
	{
		echo "ERROR\n";
		$err_msg = preg_replace ('/<br(\/)*>/', "\n", error_msg ());
		echo $err_msg;
	}
}
if($server)
{
	unset($server);
}
exit;


/** Process the data received directly from a custom script
* @param	array		$direct_data		Associative array with reported data (normally it is $_POST).
*							It must contain the keys 'customer_id', 'mac_address' and 'items'.
*							The 'items' field is an associative array, the keys being reporting
*							item IDs and the values being arrays with the reported data. The
*							values of these arrays are associative arrays, they keys being
*							monitoring items field names, and the values being the values of
*							those fields.
* @return	string					True or False if processing was done Ok or not. In case of errors,
*							these are raised using error_msg().
*/
function process_direct_data ($data = array ())
{
	$ret = false;
	class_load ('Computer');
	if (0)
	{
		$fp = fopen (dirname(__FILE__).'/logs/direct.log', 'a');
		fwrite ($fp, date('d-m-y H:i:s').' '.$_SERVER['REQUEST_METHOD']." -------------\n".$HTTP_RAW_POST_DATA);
		foreach ($_REQUEST as $k => $v) fwrite ($fp, "$k::$v\n");
		fclose ($fp);
	}
	
	if (isset($data['customer_id']) and isset($data['mac_address']) and isset($data['items']) and is_array($data['items']))
	{
		$comp = Computer::get_by_mac ($data['mac_address']);
		if ($comp->id and $comp->customer_id == $data['customer_id'])
		{
			// Parse the data into a format suitable for Computer->add_reported_items
			$items = Computer::translate_direct_data ($data);
			
			$comp->add_reported_items ($items);
		}
		else
		{
			if (!$comp->id) error_msg ("4: There is no computer with the specified MAC address.");
			else error_msg ("5: The customer ID doesn't match the computer's customer.");
		}
	}
	else
	{
		if (!isset($data['customer_id'])) error_msg ("1: The customer ID was not specified.");
		if (!isset($data['mac_address'])) error_msg ("2: The MAC address was not specified.");
		if (!isset($data['items']) or !isset($data['items'])) error_msg ("3: No items were specified.");
	}
	
	if(isset($comp)) unset($comp);

	return $ret;
}


/** Custom "die" function. It ensures that an error notification is sent to the client before exiting */
function kawacs_gracefully_die ()
{
	echo "ERROR\n";
	echo preg_replace ('/<br(\/)*>/', "\n", error_msg ());
	exit (1);
}

?>
<?php

/**
* Crontab actions - Internet monitoring
* 
* @package
* @subpackage Crontab
*
*/
$t0 = time ();
$display_info = false;

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Notification');
class_load ('Computer');
class_load ('Customer');
class_load ('MonitoredIP');

if (count($argv) == 1)
{
	// This is a request from crontab to start dispatching checking requests
	$ck_ids = MonitoredIP::get_ids_to_check ();

	//debug($ck_ids);
	
	foreach ($ck_ids as $id)
	{
		$monitored_ip = new MonitoredIP ($id);
		if ($monitored_ip->id)
		{
			if ($display_info) echo "Checking ID $id, $monitored_ip->target_ip: ".(time()-$t0)."\n";
			$monitored_ip->mark_processing_start ();
			
			$command = '/usr/bin/php -q '.dirname(__FILE__).'/internet_monitor.php '.$monitored_ip->id.' > /dev/null 2>&1 & ';
			//$command = '/usr/local/bin/php '.dirname(__FILE__).'/internet_monitor.php '.$monitored_ip->id;
			exec ($command);
		}
		if($monitored_ip) $monitored_ip = null;
	}
}
else
{
	// This is a request to actually check a batch of remote IPs
	for ($i=1; $i<count($argv); $i++)
	{
		$id = $argv[$i];
		$monitored_ip = new MonitoredIP ($id);
		
		//debug($monitored_ip);
		if ($monitored_ip->id)
		{
			$monitored_ip->run_test ();
		}
		if($monitored_ip) $monitored_ip = null;
	}
}
if ($display_info) echo "Final: ".(time()-$t0)."\n";

?>

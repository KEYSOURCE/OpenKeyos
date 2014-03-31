<?php

/**
* Crontab actions - check computers
*
* This file will be run regularily from crontab and will check if there 
* are any new alerts from Kawacs.
* 
* @package
* @subpackage Crontab
*
*/

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Computer');

Computer::check_monitor_alerts ();

?>
<?php

/**
* Crontab actions - check tickets deadlines
*
* This file will be run regularily from crontab and will check if there 
* are any tickets that have exceeded their deadlines.
* 
* Note that the deadline field for tickets stores only a deadline date, not
* an hour. Controlling the hour on which the deadline alerts are raised is
* done by setting crontab to run on a specific hour of the day.
*
* @package
* @subpackage Crontab
*
*/

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Ticket');

Ticket::check_deadlines ();

?>
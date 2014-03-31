<?php

/**
* Crontab actions - daily tasks
*
* This file will be run at the end of each day (MUST BE after 12:00 PM) and will
* perform daily maintenance routines such as:
* - Delete the events logs which are older than the retention period and reassign the NRCs for those computers items
* - Re-schedule tasks that have not been completed yet to the next day
* - Moving the logs older than a month to their respective monthly logs and cleanup the logs
* - Running database optimizers
* - Make sure that all users that are assigned as notification recipients are actually active
* - Make sure that all needed tables for storing notifications logs are present
* - Make sure that all needed tables for storing tickets access logs are present
* 
* @package
* @subpackage Crontab
*
*/

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Computer');
class_load ('Peripheral');
class_load ('ComputerReporting');
class_load ('Notification');
class_load ('InfoRecipients');
class_load ('MessageLog');
class_load ('Task');
class_load ('CustomerInternetContract');

// Make sure that the tables for logs of messages sent to customer users are present
$log_table = TBL_MESSAGES_LOG.'_'.date ('Y_m');
MessageLog::check_exists_messages_log_table ($log_table);
$log_table = TBL_MESSAGES_LOG.'_'.date ('Y_m', strtotime('+1 month'));
MessageLog::check_exists_messages_log_table ($log_table);

// Delete the events logs items that have exceeded the retention period
ComputerReporting::cleanup_events_logs ();

// Move to the next day the tasks that have not been completed yet
Task::check_tasks ();

// Process and cleanup the computers and peripherals items logs
Computer::update_monthly_logs ();
Peripheral::update_monthly_logs ();

// Make sure that the tables for storing notifications logs are present (this and next month)
$log_table = TBL_NOTIFICATIONS.'_'.date ('Y_m');
Notification::check_exists_log_table ($log_table);
$log_table = TBL_NOTIFICATIONS.'_'.date ('Y_m', strtotime('+1 month'));
Notification::check_exists_log_table ($log_table);

// Make sure that all users that are assigned as recipients are actually active
InfoRecipients::check_active_users ();

// Make sure that the tables for storing tickets acess logs are present (this and next month)
$tables = db::db_fetch_vector ('SHOW TABLES');
$tables_check = array (
	TBL_TICKETS_ACCESS.'_'.date ('Y_m'),
	TBL_TICKETS_ACCESS.'_'.date ('Y_m', strtotime('+1 month'))
);
foreach ($tables_check as $tbl)
{
	if (!in_array($tbl, $tables))
	{
		$q = 'CREATE TABLE '.$tbl.' (ticket_id int not null, ticket_detail_id int not null, user_id int not null, date int not null, action_id int not null, ';
		$q.= 'key(ticket_id), key(ticket_detail_id), key(user_id), key(date), key(action_id))';
		DB::db_query($q);
	}
}

// Optimize all the tables in the database - except for older logs
$tables = db::db_fetch_vector ('SHOW TABLES');
$this_month = date ('Y_m');
foreach ($tables as $tbl)
{
	$is_log = preg_match ('/20[0-9]{2}+_[0-9]{2}$/', $tbl);
	if (!$is_log or ($is_log and preg_match('/'.$this_month.'$/',$tbl)))
	{
		db::db_query ('OPTIMIZE TABLE '.$tbl);
	}
}

?>
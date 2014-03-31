<?php

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Notification');
class_load ('Computer');

/*
+-------------------+--------------+------+-----+---------+----------------+
| Field             | Type         | Null | Key | Default | Extra          |
+-------------------+--------------+------+-----+---------+----------------+
| id                | int(11)      |      | PRI | NULL    | auto_increment |
| event_code        | int(11)      |      | MUL | 0       |                |
| level             | int(11)      |      | MUL | 0       |                |
| raised            | int(11)      |      | MUL | 0       |                |
| raised_last       | int(11)      |      | MUL | 0       |                |
| raised_count      | int(11)      |      |     | 0       |                |
| object_class      | int(11)      |      | MUL | 0       |                |
| object_id         | int(11)      |      | MUL | 0       |                |
| object_event_code | int(11)      |      | MUL | 0       |                |
| item_id           | int(11)      |      | MUL | 0       |                |
| user_id           | int(11)      |      | MUL | 0       |                |
| text              | text         |      |     |         |                |
| emailed_last      | int(11)      |      |     | 0       |                |
| suspend_email     | int(11)      |      |     | 0       |                |
| template          | varchar(255) |      |     |         |                |
| expires           | int(11)      |      | MUL | 0       |                |
| no_repeat         | tinyint(4)   |      | MUL | 0       |                |
| ticket_id         | int(11)      |      | MUL | 0       |                |
+-------------------+--------------+------+-----+---------+----------------+
*/
$tbl = 'notifications';
$tbl_bk = 'bk_notifications';

DB::db_query ('DELETE from '.$tbl);
//$unique_fields = array ('event_code', 'object_class', 'object_id', 'object_event_code', 'item_id');
$q = 'SELECT min(id) as id, max(level) as level, min(raised) as raised, max(raised_count) as raised_count, ';
$q.= 'max(raised_last) as raised_last, max(no_repeat) as no_repeat, max(ticket_id) as ticket_id, max(text) as text, ';
$q.= 'max(emailed_last) as emailed_last, max(suspend_email) as suspend_email, min(expires) as expires, ';
$q.= 'event_code, object_class, object_id, object_event_code, item_id, count(*) as cnt ';
$q.= 'FROM '.$tbl_bk.' GROUP BY event_code, object_class, object_id, object_event_code, item_id ';
$q.= 'ORDER BY raised, id ';
$bk_notifs = DB::db_fetch_array ($q);

foreach ($bk_notifs as $bk_notif)
{
	$notif = new Notification ();
	$cp_fields = array ('id', 'event_code', 'level', 'raised', 'raised_last', 'raised_count', 'object_class', 'object_id', 'object_event_code', 'item_id', 'text', 'emailed_last', 'suspend_email', 'template', 'expires', 'no_repeat', 'ticket_id');
	foreach ($cp_fields as $field) $notif->$field = $bk_notif->$field;
	$notif->save_data ();
	
	$q = 'SELECT user_id, text, template from '.$tbl_bk.' where ';
	$q.= 'event_code='.$notif->event_code.' AND object_class='.$notif->object_class.' AND object_id='.$notif->object_id.' AND ';
	$q.= 'object_event_code='.$notif->object_event_code.' AND item_id='.$notif->item_id;
	$recips = DB::db_fetch_array ($q);
	
	foreach ($recips as $recip)
	{
		$r = new NotificationRecipient ();
		$r->notification_id = $notif->id;
		$r->user_id = $recip->user_id;
		$r->emailed_last = $notif->emailed_last;
		$r->save_data ();
	}
	$notif->load_data ();
	foreach ($recips as $recip) $notif->set_notification_recipient_text ($recip->user_id, $recip->text, false, $recip->template);
	
}

$logs = array ('2005_09', '2005_08', '2005_07', '2005_06', '2005_05', '2005_04');
foreach ($logs as $log)
{
$tbl = 'notifications_'.$log;
$tbl_bk = 'bk_notifications_'.$log;

DB::db_query ('DELETE from '.$tbl);
$q = 'INSERT INTO '.$tbl.' ';
$q.= 'SELECT min(id) as id, event_code, level, raised, max(ended) as ended, ';
$q.= 'object_class, object_id, object_event_code, object_name, item_id, max(text) as text, ';
$q.= 'max(emailed_last) as emailed_last, max(ticket_id) as ticket_id, 0 as user_id ';
$q.= 'FROM '.$tbl_bk.' GROUP BY event_code, object_class, object_id, object_event_code, item_id, raised ';
DB::db_query ($q);

echo mysql_error();

}

/*
mysql> explain notifications_2005_09;
+-------------------+--------------+------+-----+---------+-------+
| Field             | Type         | Null | Key | Default | Extra |
+-------------------+--------------+------+-----+---------+-------+
| id                | int(11)      |      | PRI | 0       |       |
| event_code        | int(11)      |      | MUL | 0       |       |
| level             | int(11)      |      | MUL | 0       |       |
| raised            | int(11)      |      | PRI | 0       |       |
| ended             | int(11)      |      | MUL | 0       |       |
| object_class      | int(11)      |      | MUL | 0       |       |
| object_id         | int(11)      |      | MUL | 0       |       |
| object_event_code | int(11)      |      | MUL | 0       |       |
| object_name       | varchar(255) |      |     |         |       |
| item_id           | int(11)      |      | MUL | 0       |       |
| text              | text         |      |     |         |       |
| emailed_last      | int(11)      |      | MUL | 0       |       |
| ticket_id         | int(11)      |      | MUL | 0       |       |
| user_id           | int(11)      |      | MUL | 0       |       |
+-------------------+--------------+------+-----+---------+-------+

*/

?>
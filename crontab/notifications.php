<?php

/**
* Crontab actions - notifications
*
* This file will be run regularily from crontab and will check if there 
* are notifications for which e-mails need to be delivered.
* 
* The reason for sending the e-mails from here is that at a certain point
* there might be a lot of notifications from one or more objects, and 
* sending an e-mail for each might generate excessive amounts of e-mails.
* 
* @package
* @subpackage Crontab
*
*/
$t0 = time ();
$display_info = false; //XXXX Don't forget to set to false on production

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Notification');
class_load ('Computer');
class_load ('Peripheral');
class_load ('AD_Printer');
class_load ('ComputerBlackout');
class_load ('Ticket');
class_load ('User');
class_load ('Group');

// Make sure there is only one instance running
$my_pid = getmypid();
$out = array ();
$procs = exec ('ps awx | grep notifications\.php | grep -v grep | grep -v "'.$my_pid.' "', $out);
if (count($out) > 0)
{
	if ($display_info) echo "More than one instance running, exiting now.\n";
	exit (1);
}


set_time_limit (600);

// Check the blackouts status and delete existing notifications for blacked out computers
ComputerBlackout::check_blackouts ();

if ($display_info) echo "Check blackouts: ".(time()-$t0)."\n";

// Make sure that all reported AD printers are synced with warranties and extras infos tables
AD_Printer::sync_extras ();

if ($display_info) echo "Sync warranties: ".(time()-$t0)."\n";


// Check the status of computers
Computer::check_monitor_alerts ();

if ($display_info) echo "Check monitor alerts - computers: ".(time()-$t0)."\n";

// Check the status of peripherals and AD Printers
Peripheral::check_monitor_alerts ();

if ($display_info) echo "Check monitor alerts - peripherals: ".(time()-$t0)."\n";

// Clear the notifications table of all references to tickets that don't exist anymore
Notification::clear_missing_tickets ();

if ($display_info) echo "Clear missing tickets: ".(time()-$t0)."\n";

// Determine first the recipients list
$recipients = array ();
$recipients_classes = array ();

// The notifications with specified templates will be treated separately,
// as they are e-mailed individually
$template_notifications = array ();

// First, remove the notifications which have "expired"
$q = 'DELETE FROM '.TBL_NOTIFICATIONS.' WHERE expires<>0 AND expires<'.time ();
db::db_query ($q);


//-----------------------------------------------------------------------------
// For notifications over the specified number of repetations, generate the tickets
//-----------------------------------------------------------------------------

$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE ticket_id=0 AND object_class='.NOTIF_OBJ_CLASS_COMPUTER.' AND ';
$q_cond = '';

if (NOTIF_RAISE_TICKET_NONE > 0) $q_cond.= '(level='.ALERT_NONE.' AND raised<'.(time() - NOTIF_RAISE_TICKET_NONE * 24*60*60).') OR ';
if (NOTIF_RAISE_TICKET_NOTICE > 0) $q_cond.= '(level='.ALERT_NOTICE.' AND raised<'.(time() - NOTIF_RAISE_TICKET_NOTICE * 24*60*60).') OR ';
if (NOTIF_RAISE_TICKET_WARNING > 0) $q_cond.= '(level='.ALERT_WARNING.' AND raised<'.(time() - NOTIF_RAISE_TICKET_WARNING * 24*60*60).') OR ';
if (NOTIF_RAISE_TICKET_ERROR > 0) $q_cond.= '(level='.ALERT_ERROR.' AND raised<'.(time() - NOTIF_RAISE_TICKET_ERROR * 24*60*60).') OR ';
if (NOTIF_RAISE_TICKET_CRITICAL > 0) $q_cond.= '(level='.ALERT_CRITICAL.' AND raised<'.(time() - NOTIF_RAISE_TICKET_CRITICAL * 24*60*60).') OR ';

if ($q_cond)
{
	$q = $q . '('.preg_replace ('/OR\s*$/', '', $q_cond).')';
	$notifs_ids = DB::db_fetch_vector ($q);
	$ticket_type = db::db_fetch_field ('SELECT id FROM '.TBL_TICKETS_TYPES.' WHERE is_customer_default=1', 'id');
	
	foreach ($notifs_ids as $notif_id)
	{
		$notif = new Notification ($notif_id);
		
		// Make sure that there are Keysource recipients for this notification
		$has_ks_recips = false;
		foreach ($notif->recipients as $recip) if (!User::is_customer_user($recip->user_id)) $has_ks_recips = true; 
		
		if ($has_ks_recips)
		{
			$ticket = new Ticket ();
			$comp = new Computer ($notif->object_id);
		
			if ($notif->text) $ticket->subject = $notif->text;
			else $ticket->subject = $GLOBALS['NOTIF_CODES_TEXTS'][$notif->event_code];
			$ticket->subject.= ' : #'.$comp->id.': '.$comp->netbios_name;
			
			$default_owner_id = null;
			if ($notif->object_event_code)
			{
				// This notification is linked to a specific kind of alert. See if there is a default recipient for this alert type
				$q_alert_recip = 'SELECT user_id FROM '.TBL_ALERTS_RECIPIENTS.' WHERE is_default=1 and alert_id='.$notif->object_event_code;
				$default_owner_id = DB::db_fetch_field ($q_alert_recip, 'user_id');
			}
			
			$ticket->customer_id = $comp->customer_id;
			if (!$default_owner_id)
			{
				// We don't need to know here if the default owner is "Away" and the function returned an alternate recipient,
				// because get_default_cc_list() will add it anyway to the CC list.
				$default_owner_id = $ticket->get_default_owner ($none);
			}
			$ticket->owner_id = $default_owner_id;
			$ticket->assigned_id = $default_owner_id;
			$ticket->cc_list = $ticket->get_default_cc_list ();
			$ticket->private = true;
			
			$ticket->status = TICKET_STATUS_NEW;
			$ticket->source = TICKET_SOURCE_KAWACS;
			$ticket->type = $ticket_type;
			$ticket->created = time ();
			$ticket->last_modified = time ();
			
			foreach ($notif->recipients as $recip)
			{
				if (!User::is_customer_user($recip->user_id) and $default_owner_id!=$recip->user_id and !in_array($recip->user_id,$ticket->cc_list))
				{
					$ticket->cc_list[] = $recip->user_id;
				}
			}
			
			$ticket->save_data ();
			$ticket->load_data ();
			$ticket->escalate (0, 'Escalated from notification.');
			$ticket->add_objects (TICKET_OBJ_CLASS_COMPUTER, array ($comp->id));
			$ticket->save_data ();
		
			if ($display_info) echo "Ticket created, ID:: $ticket->id\n";
			
			$ticket->dispatch_notifications (TICKET_NOTIF_TYPE_ESCALATED);
			$ticket->save_data ();
			
			// Mark on the notification that it is linked to a ticket
			$notif->mark_ticket_created ($ticket->id);
		}
	}
}

//-----------------------------------------------------------------------------
// Generate the e-mails for the notifications without specified templates
//-----------------------------------------------------------------------------

// Fetch the users that have notifications which need to be delivered
// Note that the query might still include notifications which shouldn't actually be delivered,
// but extra checks will be done later.
// Also, the query includes notifications with templates set, but those will be handled separately.
$q = 'SELECT DISTINCT n.id, nr.user_id, n.template, nr.template as recip_template FROM '.TBL_NOTIFICATIONS.' n INNER JOIN '.TBL_NOTIFICATIONS_RECIPIENTS.' nr ';
$q.= 'ON n.id=nr.notification_id ';
$q.= 'WHERE n.suspend_email=0 AND ';
// Include only notifications which haven't been e-mailed already or which have been e-mailed but for which the repetitions are not prohibited
$q.= '(nr.emailed_last=0 OR (nr.emailed_last>0 AND (nr.no_repeat=0 AND n.no_repeat=0))) ';
$q.= 'ORDER BY n.level DESC, n.raised, n.id, nr.user_id ';
$notifs = DB::db_fetch_array ($q);

// Array with notifications which have the templates set. It is an associative array, the keys
// being user IDs and the values being array of Notification objects.
$template_notifications = array ();

// $recips_notifs: Associative array, keys being user IDs and values being arrays of Notification
// objects about which the user needs to be informed (e-mailed)
$recips_notifs = array ();

foreach ($notifs as $n)
{
	$notification = new Notification ($n->id);
	if (!$n->template and !$n->recip_template)
	{
		// This notification doesn't have a specific template set
		if ($notification->needs_emailing($n->user_id))
		{
		
			if ($notification->recipients[$n->user_id]->emailed_last > 0) $recips_notifs[$n->user_id]['old'][] = $notification;
			else $recips_notifs[$n->user_id]['new'][] = $notification;
		}
	}
	else $template_notifications[$n->user_id][] = $notification;
}

if ($display_info) echo "Pick recipients: ".(time()-$t0)."\n";

// Start composing the e-mails
$parser = new BaseDisplay ();
$tpl = '_classes_templates/notification/msg_notifications.tpl';
$tpl_subject = '_classes_templates/notification/msg_notifications_subject.tpl';

$parser->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
$parser->assign ('NOTIF_CODES_TEXTS', $GLOBALS['NOTIF_CODES_TEXTS']);
$parser->assign ('NOTIF_OBJ_URLS', $GLOBALS['NOTIF_OBJ_URLS']);
$parser->assign ('ALERT_NAMES', $GLOBALS['ALERT_NAMES']);
$parser->assign ('base_url', get_base_url());

foreach ($recips_notifs as $user_id => $notifs)
{
	$recipient = new User ($user_id);
	
	// Send e-mails only to users with defined addresses and only if
	// they are active (not disabled, not away)
	if ($recipient->email and $recipient->is_active_strict ())
	{
		// Group the notifications for same objects
		$notifs_objects = array ();
		$user_notifs = array ();
		
		foreach (array('new', 'old') as $is_new)
		{
			for ($i=0; $i<count ($notifs[$is_new]); $i++)
			{
				$class = $notifs[$is_new][$i]->object_class;
				$object_id = $notifs[$is_new][$i]->object_id;
				
				if (isset($notifs_objects[$is_new][$class][$object_id]))
				{
					$obj_index = $notifs_objects[$is_new][$class][$object_id];
					$user_notifs[$is_new][$obj_index]->others[] = $notifs[$is_new][$i];
				}
				else
				{
					$notifs_objects[$is_new][$class][$object_id] = count($user_notifs[$is_new]);
					$user_notifs[$is_new][] = $notifs[$is_new][$i];
				}
			}
		}
	
		$user_notifs_count = Notification::get_user_notifications_count($recipient->id);
		
		$parser->assign ('recipient', $recipient);
		$parser->assign ('new_notifications', $user_notifs['new']);
		$parser->assign ('old_notifications', $user_notifs['old']);
		$parser->assign ('new_notifications_count', count($user_notifs['new']));
		$parser->assign ('old_notifications_count', count($user_notifs['old']));
		$parser->assign ('notifications_count', $user_notifs_count['total']);

		$subject = $parser->fetch ($tpl_subject);
		$msg = $parser->fetch ($tpl);
		
		$headers = 'From: '.SENDER_NAME.' <'.SENDER_EMAIL.'>'."\n";
		$headers.= 'Date: '.date("D, j M Y G:i:s O")."\n";

		$mail_sent = @mail ($recipient->email, $subject, $msg, $headers);
		if ($mail_sent)
		{
			if (is_array($notifs['new']))
			{
				foreach ($notifs['new'] as $notification)
				{
					$notification->recipients[$user_id]->emailed_last = time();
					$notification->recipients[$user_id]->save_data ();
				}
			}
			if (is_array($notifs['old']))
			{
				foreach ($notifs['old'] as $notification)
				{
					$notification->recipients[$user_id]->emailed_last = time();
					$notification->recipients[$user_id]->save_data ();
				}
			}
		}
	}
}
if ($display_info) echo "Generate emails without templates: ".(time()-$t0)."\n";


//-----------------------------------------------------------------------------
// Now generate the e-mails for the notifications with specified templates
// Note that the e-mail for tickets are not generated here, but directly in
// Ticket->dispatch_notifications() (which, however, uses the same method)
//-----------------------------------------------------------------------------
foreach ($template_notifications as $user_id => $notifications)
{
	foreach ($notifications as $notification)
	{
		if ($notification->needs_emailing($user_id) and User::is_active_strict($user_id)) $notification->send_email ($user_id);
	}
}
if ($display_info) echo "Generate emails with templates: ".(time()-$t0)."\n";


// Finally, log all notifications and their modifications into the notifications log
Notification::log_notifications ();

if ($display_info) echo "Final: ".(time()-$t0)."\n";

?>
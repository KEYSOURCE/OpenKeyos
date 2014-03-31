<?php

/**
* Constants relative to notifications
* 
* @package
* @subpackage Constants_notification
*
*/

/** Alert level: notification */
define ('ALERT_NONE', 0);
/** Alert level: notification */
define ('ALERT_NOTICE', 1);
/** Alert level: warning */
define ('ALERT_WARNING', 10);
/** Alert level: ERROR */
define ('ALERT_ERROR', 20);
/** Alert level: ERROR */
define ('ALERT_CRITICAL', 30);


$GLOBALS['ALERT_COLORS'] = array(
	ALERT_NONE => 'green',
	ALERT_NOTICE => '#0A06BB', 
	ALERT_WARNING => '#FFD800',
	ALERT_ERROR => '#EE6622',
	ALERT_CRITICAL => '#FF0000'
);

$GLOBALS['ALERT_NAMES'] = array(
	ALERT_NONE => 'Information',
	ALERT_NOTICE => 'Notice',
	ALERT_WARNING => 'Warning',
	ALERT_ERROR => 'Error',
	ALERT_CRITICAL => 'Red Alert'
);

/** Classes of objects associtated with notifications. Where they denote the same object types, should be coordinated with TICKET_OBJ_CLASS_* constants */
/** Computer object class  */
define ('NOTIF_OBJ_CLASS_COMPUTER', 1);
/** Customer object class  */
define ('NOTIF_OBJ_CLASS_CUSTOMER', 2);
/** Support ticket object class  */
define ('NOTIF_OBJ_CLASS_KRIFS', 4);
/** Internet connection object class (MonitoredIP) */
define ('NOTIF_OBJ_CLASS_INTERNET', 5);
/** Internet contract object class (CustomerInternetContract) */
define ('NOTIF_OBJ_CLASS_INTERNET_CONTRACT', 6);
/** Software license object class (SoftwareLicense) */
define ('NOTIF_OBJ_CLASS_SOFTWARE', 7);
/** Peripheral object class */
define ('NOTIF_OBJ_CLASS_PERIPHERAL', 8);
/** AD Printer object class */
define ('NOTIF_OBJ_CLASS_AD_PRINTER', 9);
/** Rbl object class */
define ('NOTIF_OBJ_CLASS_RBL', 10);

$GLOBALS['NOTIF_OBJ_CLASSES'] = array (
	NOTIF_OBJ_CLASS_CUSTOMER => 'Account manager',
	NOTIF_OBJ_CLASS_KRIFS => 'Ticket',
	NOTIF_OBJ_CLASS_COMPUTER => 'Computer',
	NOTIF_OBJ_CLASS_INTERNET => 'Internet',
	NOTIF_OBJ_CLASS_INTERNET_CONTRACT => 'Internet Contract',
	NOTIF_OBJ_CLASS_SOFTWARE => 'Software License',
	NOTIF_OBJ_CLASS_PERIPHERAL => 'Peripheral',
	NOTIF_OBJ_CLASS_AD_PRINTER => 'AD Printer',
	NOTIF_OBJ_CLASS_RBL => 'Rbl'
);

$GLOBALS['NOTIF_OBJ_CLASSES_SHORT'] = array (
	NOTIF_OBJ_CLASS_CUSTOMER => 'Cust.',
	NOTIF_OBJ_CLASS_KRIFS => 'Ticket',
	NOTIF_OBJ_CLASS_COMPUTER => 'Comp.',
	NOTIF_OBJ_CLASS_INTERNET => 'Internet',
	NOTIF_OBJ_CLASS_INTERNET_CONTRACT => 'Internet Contr.',
	NOTIF_OBJ_CLASS_SOFTWARE => 'Software Lic.',
	NOTIF_OBJ_CLASS_PERIPHERAL => 'Periph.',
	NOTIF_OBJ_CLASS_AD_PRINTER => 'AD Printer',
	NOTIF_OBJ_CLASS_RBL => 'Rbl'
);

/** The intervals (in seconds) after which e-mails are sent again */
$GLOBALS['NOTIF_REPEAT_INTERVALS'] = array(
	ALERT_NONE => $conf['repeat_alert_none']*60*60,
	ALERT_NOTICE => $conf['repeat_alert_notice']*60*60,
	ALERT_WARNING => $conf['repeat_alert_warning']*60*60,
	ALERT_ERROR => $conf['repeat_alert_error']*60*60,
	ALERT_CRITICAL => $conf['repeat_alert_critical']*60*60
);

/** URLs for linking object notifications to actual objects */
$GLOBALS['NOTIF_OBJ_URLS'] = array (
	NOTIF_OBJ_CLASS_COMPUTER => '?cl=kawacs&op=computer_view&id=',
	NOTIF_OBJ_CLASS_CUSTOMER => '?cl=customer&op=customer_edit&id=',
	NOTIF_OBJ_CLASS_KRIFS => '?cl=krifs&op=ticket_edit&id=',
	NOTIF_OBJ_CLASS_INTERNET => '?cl=kawacs&op=monitored_ip_edit&id=',
	NOTIF_OBJ_CLASS_INTERNET_CONTRACT => '?cl=klara&op=customer_internet_contract_edit&id=',
	NOTIF_OBJ_CLASS_SOFTWARE => '?cl=kalm&op=manage_licenses&license_id=',
	NOTIF_OBJ_CLASS_PERIPHERAL => '?cl=kawacs&op=peripheral_edit&id=',
	NOTIF_OBJ_CLASS_AD_PRINTER => '?cl=kerm&op=ad_printer_view&id=',
	NOTIF_OBJ_CLASS_RBL => '?cl=kawacs&op=check_rbl_listed_servers&id='
);

/** Notification code - computer missed heartbeats */
define ('NOTIF_CODE_MISSED_HEARTBEATS', 1);
/** Notification code - new computer */
define ('NOTIF_CODE_NEW_COMPUTER', 2);
/** Notification code - new ticket */
define ('NOTIF_CODE_NEW_TICKET', 3);
/** Notification code - ticket updated */
define ('NOTIF_CODE_UPDATED_TICKET', 4);
/** Notification code - ticket escalated */
define ('NOTIF_CODE_ESCALATED_TICKET', 5);
/** Notification code - ticket over deadline */
define ('NOTIF_CODE_OVER_DEADLINE_TICKET', 6);
/** Notification code - ticket closed */
define ('NOTIF_CODE_CLOSED_TICKET', 7);
/** Notification code - Internet down */
define ('NOTIF_CODE_INTERNET_DOWN', 8);
/** Notification code - Internet contract expires */
define ('NOTIF_CODE_INTERNET_CONTRACT_EXPIRES', 9);
/** Notification code - Exceeded software licenses */
define ('NOTIF_CODE_LICENSES_EXCEEDED', 10);
/** Notification code - MAC reporting conflict */
define ('NOTIF_CODE_MAC_CONFLICT', 11);
/** Notification code - Computers names reporting conflicts */
define ('NOTIF_CODE_NAME_CONFLICT', 12);
/** Notification code - Computers name swingers */
define ('NOTIF_CODE_NAME_SWINGERS', 13);
/** Notification code - Computers remote IP reporting conflicts */
define ('NOTIF_CODE_REPORTING_IP_CONFLICT', 14);
/** Notification code - Customer has discoveries without matching Keyos object */
define ('NOTIF_CODE_UNMATCHED_DISCOVERIES', 15);
/** Notification code - A computer was found active in discovery but there is no recent Kawacs contact */
define ('NOTIF_CODE_LATE_DISCOVERY', 16);
/** Notification code - Customer has public ip rbl listed */
define ('NOTIF_CODE_RBL', 17);

/** The text for the various defined notification codes
* @global	array $GLOBALS['NOTIF_CODES_TEXTS'] */
$GLOBALS['NOTIF_CODES_TEXTS'] = array (
	NOTIF_CODE_MISSED_HEARTBEATS => 'Missed heartbeats',
	NOTIF_CODE_NEW_COMPUTER => 'New computer, no profile',
	NOTIF_CODE_NEW_TICKET => 'New ticket',
	NOTIF_CODE_UPDATED_TICKET => 'Ticket updated',
	NOTIF_CODE_ESCALATED_TICKET => 'Ticket escalated',
	NOTIF_CODE_OVER_DEADLINE_TICKET => 'Ticket exceeded deadline',
	NOTIF_CODE_CLOSED_TICKET => 'Ticket closed',
	NOTIF_CODE_INTERNET_DOWN => 'Internet down',
	NOTIF_CODE_INTERNET_CONTRACT_EXPIRES => 'Internet contract expires',
	NOTIF_CODE_LICENSES_EXCEEDED => 'Software licenses exceeded',
	NOTIF_CODE_MAC_CONFLICT => 'MAC address conflict',
	NOTIF_CODE_NAME_CONFLICT => 'Computer name conflict',
	NOTIF_CODE_NAME_SWINGERS => 'Computer name swinger',
	NOTIF_CODE_REPORTING_IP_CONFLICT => 'Reporting IP conflict',
	NOTIF_CODE_UNMATCHED_DISCOVERIES => 'Unmatched discoveries',
	NOTIF_CODE_LATE_DISCOVERY => 'Discovery without Keyos contact',
	NOTIF_CODE_RBL => "Customer with RBL listed IP's" 
);


/** The number of hours after which a ticket is created for a notification */
define ('NOTIF_RAISE_TICKET_NONE', $conf['notif_raise_ticket_none']);
define ('NOTIF_RAISE_TICKET_NOTICE', $conf['notif_raise_ticket_notice']);
define ('NOTIF_RAISE_TICKET_WARNING', $conf['notif_raise_ticket_warning']);
define ('NOTIF_RAISE_TICKET_ERROR', $conf['notif_raise_ticket_error']);
define ('NOTIF_RAISE_TICKET_CRITICAL', $conf['notif_raise_ticket_critical']);

?>
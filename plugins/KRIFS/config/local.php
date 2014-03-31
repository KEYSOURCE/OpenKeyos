<?php

/**
* KRIFS specific constants
*
* Various constants to be used across the ticket tracking system
*
* @package
* @subpackage Constants_KRIFS
*/


/** The names of KRIFS ticket types - loaded from database in lib.php */
$GLOBALS ['TICKET_TYPES'] = array ();

/** The number of hours after which notifications for tickets are automatically deleted */
define ('EXPIRE_NOTIF_TICKETS', ($conf['expire_notif_tickets'] ? $conf['expire_notif_tickets'] : 72));
/** The number of hours after which notifications of closed tickets are automatically deleted */
define ('EXPIRE_NOTIF_CLOSED_TICKETS', ($conf['expire_notif_closed_tickets'] ? $conf['expire_notif_closed_tickets'] : 48));

/** The default location for timeshseet details */
define ('DEFAULT_TS_LOCATION', 3); // Helpdesk
/** The default customer for timesheet details */
define ('DEFAULT_TS_CUSTOMER', 6);


/** Ticket source: Website (support form) */
define ('TICKET_SOURCE_SITE', 2);
/** Ticket source: Phone call */
define ('TICKET_SOURCE_PHONE', 4);
/** Ticket source: Fax */
define ('TICKET_SOURCE_FAX', 8);
/** Ticket source: E-mail from customer */
define ('TICKET_SOURCE_MAIL', 16);
/** Ticket source: Meeting */
define ('TICKET_SOURCE_MEETING', 32);
/** Ticket source: KAWACS system */
define ('TICKET_SOURCE_KAWACS', 64);
/** Ticket source: other */
define ('TICKET_SOURCE_OTHER', 128);
/** Ticket source: Krifs 1 */
define ('TICKET_SOURCE_KRIFS1', 256);


/** The names for the KRIFS ticket sources */
$GLOBALS ['TICKET_SOURCES'] = array (
	TICKET_SOURCE_SITE => 'Website',
	TICKET_SOURCE_PHONE => 'Phone call',
	TICKET_SOURCE_FAX => 'Fax',
	TICKET_SOURCE_MAIL => 'E-mail',
	TICKET_SOURCE_MEETING => 'Meeting',
	TICKET_SOURCE_KAWACS => 'KAWACS',
	TICKET_SOURCE_OTHER => 'Other',
	TICKET_SOURCE_KRIFS1 => 'Krifs 1'
);


/** Ticket priority: Low */
define ('TICKET_PRIORITY_LOW', 10);
/** Ticket priority: Normal */
define ('TICKET_PRIORITY_NORMAL', 20);
/** Ticket priority: High */
define ('TICKET_PRIORITY_HIGH', 30);

/** The names of the priority levels */
$GLOBALS ['TICKET_PRIORITIES'] = array (
	TICKET_PRIORITY_LOW => 'Low',
	TICKET_PRIORITY_NORMAL => 'Normal',
	TICKET_PRIORITY_HIGH => 'High'
);

/** The colors to use for tickets priorities */
$GLOBALS['TICKETS_PRIORITIES_COLORS'] = array(
	TICKET_PRIORITY_LOW => 'white',
	TICKET_PRIORITY_NORMAL => '#0A06BB',
	TICKET_PRIORITY_HIGH => '#FF0000'
);


/** Ticket status: New */
define ('TICKET_STATUS_NEW', 1);
/** Ticket status: Assigned */
define ('TICKET_STATUS_ASSIGNED', 2);
/** Ticket status: Waiting customer information */
define ('TICKET_STATUS_WAITING_CUSTOMER', 3);
/** Ticket status: Deadline exceeded */
define ('TICKET_STATUS_OVER_DEADLINE', 5);
/** Ticket status: Closed */
define ('TICKET_STATUS_CLOSED', 10);
/** Ticket status: TBS */
define ('TICKET_STATUS_TBS', 14);

/** The names of the tickets statuses - will be loaded from database */
$GLOBALS ['TICKET_STATUSES'] = array ();


/** Type of ticket notification: New ticket */
define ('TICKET_NOTIF_TYPE_NEW', 1);
/** Type of ticket notification: Ticket updated */
define ('TICKET_NOTIF_TYPE_UPDATED', 2);
/** Type of ticket notification: Ticket escalated */
define ('TICKET_NOTIF_TYPE_ESCALATED', 3);
/** Type of ticket notification: Ticket exceeded deadline */
define ('TICKET_NOTIF_TYPE_OVER_DEADLINE', 4);
/** Type of ticket notification: Ticket closed */
define ('TICKET_NOTIF_TYPE_CLOSED', 5);


/** Types of linked objects */
define ('TICKET_OBJ_CLASS_COMPUTER', 1);
define ('TICKET_OBJ_CLASS_USER', 2);
define ('TICKET_OBJ_CLASS_AD_COMPUTER', 3);
define ('TICKET_OBJ_CLASS_AD_USER', 4);
define ('TICKET_OBJ_CLASS_AD_GROUP', 5);
define ('TICKET_OBJ_CLASS_AD_PRINTER', 6);
define ('TICKET_OBJ_CLASS_MONITORED_IP', 7);
define ('TICKET_OBJ_CLASS_PERIPHERAL', 8);
define ('TICKET_OBJ_CLASS_INTERNET_CONTRACT', 9);
define ('TICKET_OBJ_CLASS_REMOVED_COMPUTER', 10);
define ('TICKET_OBJ_CLASS_REMOVED_PERIPHERAL', 11);

/** Translation table between notification object classes and tickets objects classes - where appropriate */
$GLOBALS['NOTIFS_TICKETS_OBJ_CLASS_TRANSLATE'] = array (
	NOTIF_OBJ_CLASS_COMPUTER => TICKET_OBJ_CLASS_COMPUTER,
	NOTIF_OBJ_CLASS_INTERNET => TICKET_OBJ_CLASS_MONITORED_IP,
	NOTIF_OBJ_CLASS_INTERNET_CONTRACT => TICKET_OBJ_CLASS_INTERNET_CONTRACT,
	NOTIF_OBJ_CLASS_PERIPHERAL => TICKET_OBJ_CLASS_PERIPHERAL,
	NOTIF_OBJ_CLASS_AD_PRINTER => TICKET_OBJ_CLASS_AD_PRINTER
);

$GLOBALS['TICKET_OBJECT_CLASSES'] = array (
	TICKET_OBJ_CLASS_COMPUTER => 'Computer',
	TICKET_OBJ_CLASS_PERIPHERAL => 'Peripheral',
	TICKET_OBJ_CLASS_MONITORED_IP => 'Monitored IP',
	TICKET_OBJ_CLASS_USER => 'User',
	TICKET_OBJ_CLASS_AD_COMPUTER => 'AD Computer',
	TICKET_OBJ_CLASS_AD_USER => 'AD User',
	TICKET_OBJ_CLASS_AD_GROUP => 'AD Group',
	TICKET_OBJ_CLASS_AD_PRINTER => 'AD Printer',
	TICKET_OBJ_CLASS_INTERNET_CONTRACT => 'Internet contract',
	TICKET_OBJ_CLASS_REMOVED_COMPUTER => 'Removed computer',
	TICKET_OBJ_CLASS_REMOVED_PERIPHERAL => 'Removed peripheral'
);

/** Statuses for intervention reports */
define ('INTERVENTION_STAT_OPEN', 1);
define ('INTERVENTION_STAT_CLOSED', 2);
define ('INTERVENTION_STAT_APPROVED', 3);
define ('INTERVENTION_STAT_PENDING_CENTRALIZE', 4); // The ERP system requested the XML file, but did not confirmed centralization
define ('INTERVENTION_STAT_CENTRALIZED', 5); // The ERP system confirmed the centralization

$GLOBALS['INTERVENTION_STATS'] = array (
	INTERVENTION_STAT_OPEN => 'Open',
	INTERVENTION_STAT_CLOSED => 'Closed',
	INTERVENTION_STAT_APPROVED => 'Approved',
	INTERVENTION_STAT_PENDING_CENTRALIZE => 'Pending centralize',
	INTERVENTION_STAT_CENTRALIZED => 'Centralized'
);

/** Statuses for the timesheets */
define ('TIMESHEET_STAT_NONE', 1);
define ('TIMESHEET_STAT_OPEN', 2);
define ('TIMESHEET_STAT_CLOSED', 3);
define ('TIMESHEET_STAT_APPROVED', 6);
define ('TIMESHEET_STAT_PENDING_CENTRALIZE', 4);
define ('TIMESHEET_STAT_CENTRALIZED', 5);

$GLOBALS['TIMESHEET_STATS'] = array (
	TIMESHEET_STAT_NONE => 'No timesheet',
	TIMESHEET_STAT_OPEN => 'Open',
	TIMESHEET_STAT_CLOSED => 'Closed',
	TIMESHEET_STAT_APPROVED => 'Approved',
	TIMESHEET_STAT_PENDING_CENTRALIZE => 'Pending centralize',
	TIMESHEET_STAT_CENTRALIZED => 'Centralized'
);

/** The first and last hour of the work day */
define ('DAY_HOUR_START', 9*60*60);
define ('DAY_HOUR_END', 18*60*60);

/** The minimum number of required hours in order to be able to close a timesheet */
define ('MIN_TIMESHEET_HOURS', $conf['min_timesheet_hours']);


/** Statuses for customer orders */
define ('ORDER_STAT_OPEN', 1);
define ('ORDER_STAT_CLOSED', 2);
define ('ORDER_STAT_COMPLETED', 3);

//$GLOBALS['PO_STATS']
$GLOBALS['ORDER_STATS'] = array (
	ORDER_STAT_OPEN => 'Open',
	ORDER_STAT_CLOSED => 'Closed',
	ORDER_STAT_COMPLETED => 'Completed'
);


/** Types of pricing for action types */
define ('PRICE_TYPE_HOURLY', 1);
define ('PRICE_TYPE_FIXED', 2);
define('PRICE_TYPE_QUARTERLY', 3);

$GLOBALS['PRICE_TYPES'] = array (
	PRICE_TYPE_HOURLY => 'Hourly',
	PRICE_TYPE_FIXED => 'Fixed',
        PRICE_TYPE_QUARTERLY => "1/4 Hours"
);

$GLOBALS['PRICE_TYPE_BILLING_UNIT'] = array(
        PRICE_TYPE_HOURLY => 60,
        PRICE_TYPE_QUARTERLY => 15
);

/** Customer types */
define ('CONTRACT_BASIC', 2);
define ('CONTRACT_KEYPRO', 4);
define ('CONTRACT_TOTAL_CARE', 8);
/** Special type - to be used for action types which apply to all types of customers */
define ('CONTRACT_ALL', 1024);

$GLOBALS['CONTRACT_TYPES'] = array (
	CONTRACT_BASIC => 'Basic',
	CONTRACT_KEYPRO => 'KeyPro',
	CONTRACT_TOTAL_CARE => 'Total Care',
	CONTRACT_ALL => '[All customers]'
);

/** Customer sub-types  - linked to ERP 'customer.cat_2' */
define ('CUST_SUBTYPE_BASIC', 1);
define ('CUST_SUBTYPE_KEYPRO', 2);
define ('CUST_SUBTYPE_GLOBALPRO', 3);
define ('CUST_SUBTYPE_REMOTEADMIN', 4);
define ('CUST_SUBTYPE_TC', 5);

$GLOBALS['CUST_SUBTYPES'] = array (
	CUST_SUBTYPE_BASIC => 'Basic',
	CUST_SUBTYPE_KEYPRO => 'Key Pro',
	CUST_SUBTYPE_GLOBALPRO => 'Global Pro',
	CUST_SUBTYPE_REMOTEADMIN => 'Remote Admin',
	CUST_SUBTYPE_TC => 'Total Care'
);


/** Customer price types - linked to ERP 'customer.c_tarif' */
define ('CUST_PRICETYPE_BASIC', 1);
define ('CUST_PRICETYPE_KEYPRO', 2);
define ('CUST_PRICETYPE_TC1', 3);
define ('CUST_PRICETYPE_TC2', 4);
define ('CUST_PRICETYPE_TC3', 5);

$GLOBALS['CUST_PRICETYPES'] = array (
	CUST_PRICETYPE_BASIC => 'Basic',
	CUST_PRICETYPE_KEYPRO => 'Key Pro',
	CUST_PRICETYPE_TC1 => 'TC 1',
	CUST_PRICETYPE_TC2 => 'TC 2',
	CUST_PRICETYPE_TC3 => 'TC 3'
);

/** The ID of the action type which represents travel costs */
define ('ACTYPE_SPECIAL_TRAVEL', 1);

/** Special action types */
$GLOBALS ['ACTYPE_SPECIALS'] = array (
	ACTYPE_SPECIAL_TRAVEL => 'Travel costs'
);

/** Special detail types for linking timesheets details to tickets details */
define ('TS_SPECIAL_TRAVEL_TO', 1);
define ('TS_SPECIAL_TRAVEL_FROM', 2);

$GLOBALS ['TS_SPECIALS'] = array (
	TS_SPECIAL_TRAVEL_TO => 'Travel to',
	TS_SPECIAL_TRAVEL_FROM => 'Travel from'
);

/** Codes for actions when logging tickets access */
define ('TICKET_ACCESS_CREATE', 1);
define ('TICKET_ACCESS_READ', 2);
define ('TICKET_ACCESS_SAVE', 3);
define ('TICKET_ACCESS_DELETE', 4);
define ('TICKET_ACCESS_ESCALATE', 5);
define ('TICKET_ACCESS_UNESCALATE', 6);
define ('TICKET_ACCESS_CLOSE', 7);
define ('TICKET_ACCESS_REOPEN', 8);

define ('TICKET_ACCESS_ATTACH_ADD', 9);
define ('TICKET_ACCESS_ATTACH_DELETE', 10);
define ('TICKET_ACCESS_OBJ_ADD', 11);
define ('TICKET_ACCESS_OBJ_DELETE', 12);
define ('TICKET_ACCESS_SAVE_CC', 13);

define ('TICKET_ACCESS_DETAIL_CREATE', 101);
define ('TICKET_ACCESS_DETAIL_READ', 102);
define ('TICKET_ACCESS_DETAIL_SAVE', 103);
define ('TICKET_ACCESS_DETAIL_DELETE', 104);

$GLOBALS['TASK_COMPLETED_OPTS'] = array (
	0 => '0%',
	10 => '10%',
	20 => '20%',
	30 => '30%',
	40 => '40%',
	50 => '50%',
	60 => '60%',
	70 => '70%',
	80 => '80%',
	90 => '90%',
	100 => '100%',
);

/** Types of notifications sent for tasks */
define ('TASK_NOTIF_NEW', 1);
define ('TASK_NOTIF_MODIFIED', 2);
define ('TASK_NOTIF_DELETED', 3);

define ('DEFAULT_LOCATION_ID', 3);
define ('DEFAULT_ACTIVITY_ID', 23);
define ('DEFAULT_TICKET_TYPE', 2);

?>
<?php

/**
* KAWACS specific constants
* 
* Various constants to be used across the projects
*
* @package
* @subpackage Constants_KAWACS
*/

//require_once(__DIR__ . "/../init.php");

/*class_load ('ServiceLevel');
class_load ('SupplierServicePackage');
class_load ('ComputerReporting');
class_load ('EventLogRequested');*/

/** Default update interval (cycle) for an item - in minutes */
define ('DEFAULT_MONITOR_INTERVAL', $conf['default_monitor_interval']);

/** The default number of cycles (DEFAULT_MONITOR_INTERVAL) after which a computer is considered to have lost contact */
define ('DEFAULT_CONTACT_LOST_INTERVAL', $conf['default_contact_lost_interval']);

/** The maximum number of items to show in boxes in the the View Computer page */
define ('MAX_COMPUTER_ITEMS_SHOWN', 50);


/** The minimum ID for an automatically collected item (e.g. MITEC or SNMP) */
define ('ITEM_ID_COLLECTED_MIN', 1000);
/** The maximum ID for an automatically collected item (e.g. MITEC or SNMP) */
define ('ITEM_ID_COLLECTED_MAX', 1999);
/** The minimum recommended ID for an SNMP automatically collected item */
define ('ITEM_ID_COLLECTED_SNMP_MIN', 1500);

/** The minimum ID for a manually collected item */
define ('ITEM_ID_MANUAL_MIN', 2000);
/** The maximum ID for a manually collected item */
define ('ITEM_ID_MANUAL_MAX', 2999);

/** The minimum ID for event-log items */
define ('ITEM_ID_EVENTS_MIN', 3000);
/** The macimum ID for event-log items */
define ('ITEM_ID_EVENTS_MAX', 3999);

/** The minimum ID for peripherals SNMP items */
define ('ITEM_ID_PERIPHERAL_SNMP_MIN', 5000);
/** The maximum ID for peripherals SNMP items */
define ('ITEM_ID_PERIPHERAL_SNMP_MAX', 5999);


/** Stores an integer type value */
define ('MONITOR_TYPE_INT', 1);
/** Stores a string type value */
define ('MONITOR_TYPE_STRING', 2);
/** Stores a long text type value */
define ('MONITOR_TYPE_TEXT', 3);
/** Stores a float type value */
define ('MONITOR_TYPE_FLOAT', 4);
/** Stores a struct type value */
define ('MONITOR_TYPE_STRUCT', 5);
/** Stores a memory or disk space value */
define ('MONITOR_TYPE_MEMORY', 6);
/** Stores a memory or disk space value */
define ('MONITOR_TYPE_DATE', 7);
/** Stores a file */
define ('MONITOR_TYPE_FILE', 8);
/** Stores a value with a meaning from a predefined list - internally they are stored as integers */
define ('MONITOR_TYPE_LIST', 9);


/** The names of the monitor value types
* @global	array $GLOBALS['MONITOR_TYPES'] */
$GLOBALS['MONITOR_TYPES'] = array(
	MONITOR_TYPE_INT => 'Integer',
	MONITOR_TYPE_STRING => 'String',
	MONITOR_TYPE_TEXT => 'Long text',
	MONITOR_TYPE_FLOAT => 'Float',
	MONITOR_TYPE_MEMORY => 'Memory',
	MONITOR_TYPE_DATE => 'Date',
	MONITOR_TYPE_STRUCT => 'Structure',
	MONITOR_TYPE_FILE => 'File',
	MONITOR_TYPE_LIST => 'Value from list'
);

/** The names of the monitor value types
* @global	array $GLOBALS['PERIPHERALS_FIELDS_TYPES'] */
$GLOBALS['PERIPHERALS_FIELDS_TYPES'] = array(
	MONITOR_TYPE_INT => 'Integer',
	MONITOR_TYPE_STRING => 'String',
	MONITOR_TYPE_TEXT => 'Long text',
	MONITOR_TYPE_FLOAT => 'Float',
	MONITOR_TYPE_MEMORY => 'Memory',
	MONITOR_TYPE_DATE => 'Date',
	//MONITOR_TYPE_LIST => 'Value from list'
);

/* Record a single value for the monitoring item */
define ('MONITOR_MULTI_NO', 1);
/* Record multiple values for the monitoring item */
define ('MONITOR_MULTI_YES', 2);

/** The names for multi/single monitoring options 
* @global	array $GLOBALS['MONITOR_MULTI'] */
$GLOBALS['MONITOR_MULTI'] = array(
	MONITOR_MULTI_NO => 'Single',
	MONITOR_MULTI_YES => 'Multiple values'
);

/** Special filtering condition: computers with alerts */
define ('COMPUTERS_FILTER_MISSED_BEATS', -1);
/** Special filtering condition: computers with missed heartbeats */
define ('COMPUTERS_FILTER_ALERTS', -2);
/** Special filtering condition: new computers (no profile) */
define ('COMPUTERS_FILTER_NEW', -3);
/** Special filtering condition: all computers */
define ('COMPUTERS_FILTER_ALL', -4);

$GLOBALS['COMPUTERS_FILTER_SPECIAL'] = array (
	COMPUTERS_FILTER_ALERTS => '[Alerts]',
	COMPUTERS_FILTER_MISSED_BEATS => '[Missed heartbeats]',
	COMPUTERS_FILTER_NEW => '[New computers]',
	COMPUTERS_FILTER_ALL => '[-- All --]'
);

/** Join conditions with AND */
define ('JOIN_CONDITION_AND', 0);
/** Join conditions with OR */
define ('JOIN_CONDITION_OR', 1);

/** The names of join conditions */
$GLOBALS['JOIN_CONDITION_NAMES'] = array (
	'0' => 'AND',
	'1' => 'OR'
);


/** No logging */
define ('MONITOR_LOG_NONE', 1);
/** Log only changes */
define ('MONITOR_LOG_CHANGES', 2);
/** Log all reported values */
define ('MONITOR_LOG_ALL', 3);

/** The names of the monitor logging options 
* @global	array $GLOBALS['MONITOR_LOG'] */
$GLOBALS['MONITOR_LOG'] = array(
	MONITOR_LOG_NONE => 'No logging',
	MONITOR_LOG_CHANGES => 'Changes only',
	MONITOR_LOG_ALL => 'All reported values'
);


/** @todo These could be defined in the database
/** Hardware monitoring items */
define ('MONITOR_CAT_SYSTEM', 1);
define ('MONITOR_CAT_HARDWARE', 2);
define ('MONITOR_CAT_SOFTWARE', 3);
define ('MONITOR_CAT_WARRANTY', 4);
define ('MONITOR_CAT_AD', 5);
define ('MONITOR_CAT_EVENTS', 6);
define ('MONITOR_CAT_NETSTAT', 7);

/** The names of the monitoring items categories 
* @global	array $GLOBALS['MONITOR_CAT'] */
$GLOBALS['MONITOR_CAT'] = array (
	MONITOR_CAT_SYSTEM => 'System',
	MONITOR_CAT_HARDWARE => 'Hardware',
	MONITOR_CAT_SOFTWARE => 'Software',
	MONITOR_CAT_WARRANTY => 'Warranty',
	MONITOR_CAT_AD => 'Active Directory',
	MONITOR_CAT_EVENTS => 'Events Log',
	MONITOR_CAT_NETSTAT => 'Network status'
);

/** The categories of events sources */
define ('EVENTS_CAT_APPLICATION', 1);
define ('EVENTS_CAT_AD', 2);
define ('EVENTS_CAT_DNS', 3);
define ('EVENTS_CAT_FILE_REPLICATION', 4);
define ('EVENTS_CAT_SECURITY', 5);
define ('EVENTS_CAT_SYSTEM', 6);

$GLOBALS['EVENTS_CATS'] = array (
	EVENTS_CAT_APPLICATION => 'Application',
	EVENTS_CAT_AD => 'Directory Service',
	EVENTS_CAT_DNS => 'DNS Server',
	EVENTS_CAT_FILE_REPLICATION => 'File Replication Service',
	EVENTS_CAT_SECURITY => 'Security',
	EVENTS_CAT_SYSTEM => 'System'
);

/** The types of events */
//define ('EVENTLOG_SUCCESS', 0);
define ('EVENTLOG_ERROR_TYPE', 1);
define ('EVENTLOG_WARNING_TYPE', 2);
define ('EVENTLOG_INFORMATION_TYPE', 4);
define ('EVENTLOG_AUDIT_SUCCESS', 8);
define ('EVENTLOG_AUDIT_FAILURE', 16);
define ('EVENTLOG_NO_REPORT', 32);	// We use 32 instead of 0 because the type 0 has a special meaning in Windows events log

$GLOBALS['EVENTLOG_TYPES'] = array (
	EVENTLOG_NO_REPORT => '[No reporting]',
	EVENTLOG_ERROR_TYPE => 'Error',
	EVENTLOG_WARNING_TYPE => 'Warning',
	EVENTLOG_INFORMATION_TYPE => 'Information',
	EVENTLOG_AUDIT_SUCCESS => 'Audit success',
	EVENTLOG_AUDIT_FAILURE => 'Audit failure'
);

$GLOBALS['EVENTLOG_TYPES_ICONS'] = array (
	EVENTLOG_NO_REPORT => '',
	EVENTLOG_ERROR_TYPE => 'error2_16.png',
	EVENTLOG_WARNING_TYPE => 'warning_16.png',
	EVENTLOG_INFORMATION_TYPE => 'information_16.png',
	EVENTLOG_AUDIT_SUCCESS => 'key_16.pn',
	EVENTLOG_AUDIT_FAILURE => 'key_del_16.png'
);

/** Criteria for comparing values of monitor alerts */
define ('CRIT_DATE_OLDER_THAN', 1);
define ('CRIT_DATE_EXPIRES', 2);
define ('CRIT_STRING_MATCHES', 51);
define ('CRIT_STRING_STARTS', 52);
define ('CRIT_STRING_ENDS', 53);
define ('CRIT_STRING_CONTAINS', 54);
define ('CRIT_STRING_EMPTY', 55);
define ('CRIT_STRING_NOT_EMPTY', 56);
define ('CRIT_STRING_NOT_CONTAINS', 57);
define ('CRIT_NUMBER_EQUALS', 101);
define ('CRIT_NUMBER_DIFFERENT', 102);
define ('CRIT_NUMBER_HIGHER', 103);
define ('CRIT_NUMBER_HIGHER_EQUAL', 104);
define ('CRIT_NUMBER_SMALLER', 105);
define ('CRIT_NUMBER_SMALLER_EQUAL', 106);
define ('CRIT_LIST_EQUALS', 151);
define ('CRIT_LIST_DIFFERS', 152);

$GLOBALS['CRIT_NAMES'] = array (
	CRIT_DATE_OLDER_THAN => 'Older than',
	CRIT_DATE_EXPIRES => 'Expires in',
	CRIT_STRING_MATCHES => 'Equals',
	CRIT_STRING_STARTS => 'Starts with',
	CRIT_STRING_ENDS => 'Ends with',
	CRIT_STRING_CONTAINS => 'Contains',
	CRIT_STRING_EMPTY => 'Is empty',
	CRIT_STRING_NOT_EMPTY => 'Is not empty',
	CRIT_STRING_NOT_CONTAINS => 'Does not contain',
	CRIT_NUMBER_EQUALS => 'Equals',
	CRIT_NUMBER_DIFFERENT => 'Different than',
	CRIT_NUMBER_HIGHER => 'Higher than',
	CRIT_NUMBER_HIGHER_EQUAL => 'Higher or equal',
	CRIT_NUMBER_SMALLER => 'Smaller than',
	CRIT_NUMBER_SMALLER_EQUAL => 'Smaller or equal',
	CRIT_LIST_EQUALS => 'Equals',
	CRIT_LIST_DIFFERS => 'Different than'
);
 
$GLOBALS['CRITERIAS_DATE'] = array (CRIT_DATE_OLDER_THAN, CRIT_DATE_EXPIRES);
$GLOBALS['CRITERIAS_STRING'] = array (CRIT_STRING_MATCHES, CRIT_STRING_STARTS, CRIT_STRING_ENDS, CRIT_STRING_CONTAINS, CRIT_STRING_EMPTY, CRIT_STRING_NOT_EMPTY, CRIT_STRING_NOT_CONTAINS);
$GLOBALS['CRITERIAS_NUMBER'] = array (CRIT_NUMBER_EQUALS, CRIT_NUMBER_DIFFERENT, CRIT_NUMBER_HIGHER, CRIT_NUMBER_HIGHER_EQUAL, CRIT_NUMBER_SMALLER, CRIT_NUMBER_SMALLER_EQUAL);
$GLOBALS['CRITERIAS_LIST'] = array (CRIT_LIST_EQUALS, CRIT_LIST_DIFFERS);

foreach ($GLOBALS['CRITERIAS_DATE'] as $crit) $GLOBALS['CRIT_NAMES_DATE'][$crit] = $GLOBALS['CRIT_NAMES'][$crit];
foreach ($GLOBALS['CRITERIAS_STRING'] as $crit) $GLOBALS['CRIT_NAMES_STRING'][$crit] = $GLOBALS['CRIT_NAMES'][$crit];
foreach ($GLOBALS['CRITERIAS_NUMBER'] as $crit) $GLOBALS['CRIT_NAMES_NUMBER'][$crit] = $GLOBALS['CRIT_NAMES'][$crit];
foreach ($GLOBALS['CRITERIAS_LIST'] as $crit) $GLOBALS['CRIT_NAMES_LIST'][$crit] = $GLOBALS['CRIT_NAMES'][$crit];

define ('CRIT_VAL_TYPE_MEM_B', 1);
define ('CRIT_VAL_TYPE_MEM_KB', 2);//1024);
define ('CRIT_VAL_TYPE_MEM_MB', 3);//(1024*1024));
define ('CRIT_VAL_TYPE_MEM_GB', 4);//(1024*1024*1024));
define ('CRIT_VAL_TYPE_MEM_TB', 5);//(1024*1024*1024*1024));

$GLOBALS['CRIT_TYPES_NAMES'] = array (
	CRIT_VAL_TYPE_MEM_B => 'Bytes',
	CRIT_VAL_TYPE_MEM_KB => 'KB',
	CRIT_VAL_TYPE_MEM_MB => 'MB',
	CRIT_VAL_TYPE_MEM_GB => 'GB',
	CRIT_VAL_TYPE_MEM_TB => 'TB'
);

$GLOBALS['CRIT_MEMORY_MULTIPLIERS'] = array (CRIT_VAL_TYPE_MEM_B, CRIT_VAL_TYPE_MEM_KB, CRIT_VAL_TYPE_MEM_MB, CRIT_VAL_TYPE_MEM_GB, CRIT_VAL_TYPE_MEM_TB);

foreach ($GLOBALS['CRIT_MEMORY_MULTIPLIERS'] as $crit) $GLOBALS['CRIT_MEMORY_MULTIPLIERS_NAMES'][$crit] = $GLOBALS['CRIT_TYPES_NAMES'][$crit];


/** How log to keep in the database logs for computer items */
define ('COMPUTER_ITEMS_LOG_LIFE', $conf['computer_items_log_life']);


/** Computer category: Not specified */
define ('COMP_TYPE_UNSPECIFIED', 0);
/** Computer category: Workstation */
define ('COMP_TYPE_WORKSTATION', 5);
/** Computer category: Server */
define ('COMP_TYPE_SERVER', 10);

$GLOBALS['COMP_TYPE_NAMES'] = array (
	COMP_TYPE_UNSPECIFIED => '[Not specified]',
	COMP_TYPE_SERVER => 'Server',
	COMP_TYPE_WORKSTATION => 'Workstation'
);

/** Whom to send alert to: Keysource user */
define ('ALERT_SEND_KEYSOURCE', 1);
/** Whom to send alert to: Customer */
define ('ALERT_SEND_CUSTOMER', 2);

$GLOBALS['ALERT_SEND_TO'] = array (
	ALERT_SEND_KEYSOURCE => 'Keysource',
	ALERT_SEND_CUSTOMER => 'Customer'
);


/** Object classes to be specfied in SOAP requests for SNMP data */
define ('SNMP_OBJ_CLASS_COMPUTER', 0);
define ('SNMP_OBJ_CLASS_PERIPHERAL', 2);
define ('SNMP_OBJ_CLASS_AD_PRINTER', 3);

/** Names of the SNMP objects classes - also used in networks discoveries */
$GLOBALS['SNMP_OBJ_CLASSES'] = array (
	SNMP_OBJ_CLASS_COMPUTER => 'Computer',
	SNMP_OBJ_CLASS_PERIPHERAL => 'Peripheral',
	SNMP_OBJ_CLASS_AD_PRINTER => 'AD Printer'
);

/** Statuses of monitored Internet connection */
define ('MONITOR_STAT_UNKNOWN', 0);
define ('MONITOR_STAT_OK', 1);
define ('MONITOR_STAT_ERROR', 2);

$GLOBALS['MONITOR_STATS'] = array (
	MONITOR_STAT_UNKNOWN => 'Unknown',
	MONITOR_STAT_OK => 'OK',
	MONITOR_STAT_ERROR => 'ERROR'
);

/** The intervals at which to run ping tests, in seconds */
define ('INTERVAL_PING_TESTS', 55);
/** The intervals at which to run traceroutes, in seconds */
define ('INTERVAL_TRACEROUTE_TESTS', 60*15);
/** The timeout interval for Internet connection checks. If the processing has not finished in this allowed time, is considered dead */
define ('INTERVAL_TESTS_TIMEOUT', 60*3);
/** The number of packets to send in ping tests */
define ('PING_TEST_PACKETS', 3);

/** SNMP data types */
define ('SNMP_TYPE_INTEGER', 1);
define ('SNMP_TYPE_STRING', 2);
define ('SNMP_TYPE_NULL', 3);
define ('SNMP_TYPE_OBJECT_ID', 4);
define ('SNMP_TYPE_SEQUENCE', 5);
define ('SNMP_TYPE_BITS', 6);

$GLOBALS['SNMP_TYPES'] = array (
	SNMP_TYPE_INTEGER => 'Integer',
	SNMP_TYPE_STRING => 'String',
	SNMP_TYPE_NULL => 'Null',
	SNMP_TYPE_OBJECT_ID => 'Object identifier',
	SNMP_TYPE_SEQUENCE => 'Sequence',
	SNMP_TYPE_BITS => 'Bits',
);

/** Mappings between the data types output by the Java tool (in the XML file) and the types used in Keyos */
$GLOBALS['SNMP_TYPES_TRANSLATION'] = array (
	'INTEGER' => SNMP_TYPE_INTEGER,
	'OBJECT IDENTIFIER' => SNMP_TYPE_OBJECT_ID,
	'OCTET STRING' => SNMP_TYPE_STRING,
	'CHOICE' => SNMP_TYPE_STRING,
	'SEQUENCE' => SNMP_TYPE_SEQUENCE,
	'BITS' => SNMP_TYPE_BITS
);

/** SNMP nodes (OIDs) types */
define ('SNMP_NODE_NONE', 0);
define ('SNMP_NODE_SCALAR', 1);
define ('SNMP_NODE_TABLE', 2);
define ('SNMP_NODE_TABLE_ROW', 4);
define ('SNMP_NODE_TABLE_COL', 8);


/** Snmp access codes for OIDs */
define ('SNMP_ACCESS_NONE', 0);
define ('SNMP_ACCESS_READ_ONLY', 1);
define ('SNMP_ACCESS_READ_WRITE', 2);
$GLOBALS['SNMP_ACCESSES'] = array (
	SNMP_ACCESS_NONE => '--',
	SNMP_ACCESS_READ_ONLY => 'Read-only',
	SNMP_ACCESS_READ_WRITE => 'Read-write'
);

/** SNMP "status" codes for OIDs */
define ('SNMP_OID_STAT_NONE', 0);
define ('SNMP_OID_STAT_MANDATORY', 1);
define ('SNMP_OID_STAT_DEPRECATED', 2);
define ('SNMP_OID_STAT_OBSOLETE', 3);
define ('SNMP_OID_STAT_OPTIONAL', 4);
$GLOBALS['SNMP_OID_STATS'] = array (
	SNMP_OID_STAT_NONE => '',
	SNMP_OID_STAT_MANDATORY => 'Mandatory',
	SNMP_OID_STAT_DEPRECATED => 'Deprecated',
	SNMP_OID_STAT_OBSOLETE => 'Obsolete',
	SNMP_OID_STAT_OPTIONAL => 'Optional'
);

/** The interval (in seconds) at which to perform networks discoveries */
define ('DISCOVERY_INTERVAL', ($conf['discovery_interval'] ? ($conf['discovery_interval'] * 3600) : (4 * 3600)));

/** The maximum number of allowed IPs in a range specified for network discovery */
define ('DISCOVERY_MAX_IPS_COUNT', 254);
/** The maximum number of parallel threads that Agent should launch in discoveries */
define ('DISCOVERY_MAX_THREADS', 25);
/** The default timeout (in milliseconds) for Agent discovery operations */
define ('DISCOVERY_DEFAULT_TIMEOUT', 500);
/** The default timeout (in milliseconds) for a batch of discovery threads when WMI is NOT used */
define ('DISCOVERY_BATCH_TIMEOUT_NO_WMI', 45000);
/** The default timeout (in milliseconds) for a batch of discovery threads when WMI IS used */
define ('DISCOVERY_BATCH_TIMEOUT_WMI', 60000);
/** The interval (in seconds) after which a discovered computered is considered to have a reporting problem - if 
* the difference between the last discovery and the last KawacsAgent report is higher than this interval */
define ('DISCOVERY_REPORTING_ISSUE_INTERVAL', 12 * 3600);

/** WMI code for computer domain role: standalone workstation */
define ('WMI_DOMAIN_ROLE_WKS_STANDALONE', 0);
/** WMI code for computer domain role: member workstation */
define ('WMI_DOMAIN_ROLE_WKS_MEMBER', 1);
/** WMI code for computer domain role: standalone workstation */
define ('WMI_DOMAIN_ROLE_SRV_STANDALONE', 2);
/** WMI code for computer domain role: member server */
define ('WMI_DOMAIN_ROLE_SRV_MEMBER', 3);
/** WMI code for computer domain role: backup domain controller */
define ('WMI_DOMAIN_ROLE_BDC', 4);
/** WMI code for computer domain role: primary domain controller */
define ('WMI_DOMAIN_ROLE_PDC', 5);

$GLOBALS['WMI_DOMAIN_ROLES'] = array (
	WMI_DOMAIN_ROLE_WKS_STANDALONE => 'Standalone workstation',
	WMI_DOMAIN_ROLE_WKS_MEMBER => 'Member workstation',
	WMI_DOMAIN_ROLE_SRV_STANDALONE => 'Standalone server',
	WMI_DOMAIN_ROLE_SRV_MEMBER => 'Member server',
	WMI_DOMAIN_ROLE_BDC => 'Backup domain controller',
	WMI_DOMAIN_ROLE_PDC => 'Primary domain controller'
);

/** Backup statuses alerts */
define ('BACKUP_STATUS_SUCCESS', 0);
define ('BACKUP_STATUS_ERROR', 1);
define ('BACKUP_STATUS_TAPE_ERROR', 2);
define ('BACKUP_STATUS_NOT_REPORTING', 3);

/** Antivirus statuses alerts */
define ('ANTIVIRUS_UPD_SUCCESS', 0);
define ('ANTIVIRUS_UPD_ONE_DAY', 1);
define ('ANTIVIRUS_UPD_ONE_WEEK', 2);
define ('ANTIVIRUS_UPD_NOT_REPORTING', 3);


/** MRemote connection types */
define('MREMOTE_CONNECTION_TYPE_CONNECTION', 0);
define('MREMOTE_CONNECTION_TYPE_CONTAINER', 1);

$GLOBALS['MREMOTE_CONNECTION_TYPES'] = array(
	MREMOTE_CONNECTION_TYPE_CONNECTION => 'Connection',
	MREMOTE_CONNECTION_TYPE_CONTAINER => 'Container'
);

/** MRemote protocols & default ports */
define('SSH2', 1);
define('RDP', 2);
define('VNC', 3);
define('RAW', 4); //vmaware console
define('Telnet', 5);
define('SSH1', 6);
define('Rlogin', 7);
define('HTTP', 8);
define('HTTPS', 9);


$GLOBALS['MREMOTE_PROTOCOLS'] = array(
	RDP => "RDP",
	VNC => "VNC",
	SSH1 => 'SSH1',
	SSH2 => 'SSH2',
	Rlogin => 'Rlogin',
	RAW => 'RAW',
	HTTP => 'HTTP',
	HTTPS => 'HTTPS',
	Telnet => 'Telnet'
);

$GLOBALS['MREMOTE_PROTOCOLS_PORTS'] = array(
	RDP => 3389,
	VNC => 5900,
	SSH1 => 22,
	SSH2 => 22,
	Rlogin => 513,
	RAW => 902,
	HTTP => 80,
	HTTPS => 443,
	Telnet => 23
);
define('MONITOR_PROFILE_WORKSTATION', 3);
//
///** IDs of the lists for MONITOR_TYPE_LIST */
//define ('ITEMS_LIST_BOOL', 1);				// Simple yes/no selector
//define ('ITEMS_LIST_SERVICES_STATS', 2);		// Status (running, stopped etc.) of system services on computers
//define ('ITEMS_LIST_SERVICE_LEVELS', 3);		// List of services levels - see ServiceLevel class
//define ('ITEMS_LIST_SERVICE_PACKAGES', 4);		// List of service packages offered by suppliers - see SupplierServicePackage class
//define ('ITEMS_LIST_EVENTS_CATEGORIES', 5);		// List of events log categories (Application, System etc.)
//define ('ITEMS_LIST_EVENTS_TYPES', 6);			// List of events log types (Error, Warning, Information etc.)
//define ('ITEMS_LIST_EVENTS_SOURCES', 7);			// List of events log sources
//
///** Array with the name of lists for MONITOR_TYPE_LIST items. MUST be in sync with $GLOBALS['AVAILABLE_ITEMS_LISTS'] */
//$GLOBALS['AVAILABLE_ITEMS_LISTS_NAMES'] = array (
//    ITEMS_LIST_BOOL => 'Yes/No',
//    ITEMS_LIST_SERVICES_STATS => 'Computer services status',
//    ITEMS_LIST_SERVICE_LEVELS => 'Support service levels',
//    ITEMS_LIST_SERVICE_PACKAGES => 'Suppliers service packages',
//    ITEMS_LIST_EVENTS_CATEGORIES => 'Events log categories',
//    ITEMS_LIST_EVENTS_TYPES => 'Events log types',
//    ITEMS_LIST_EVENTS_SOURCES => 'Events log sources',
//);
//
///** Array with the lists of available values list for MONITOR_TYPE_LIST items. MUST be in sync with $GLOBALS['AVAILABLE_ITEMS_LISTS_NAMES'] */
//$GLOBALS['AVAILABLE_ITEMS_LISTS'] = array (
//    ITEMS_LIST_BOOL => array (
//        0 => 'No',
//        1 => 'Yes'
//    ),
//    ITEMS_LIST_SERVICES_STATS => array (
//        1 => 'Stopped',
//        2 => 'Starting',
//        3 => 'Stopping',
//        4 => 'Running',
//        5 => 'Continue pending',
//        6 => 'Pause pending',
//        7 => 'Paused'
//    ),
//    ITEMS_LIST_SERVICE_LEVELS => ServiceLevel::get_service_levels_list (),
//    ITEMS_LIST_SERVICE_PACKAGES => SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true)),
//    ITEMS_LIST_EVENTS_CATEGORIES => $GLOBALS['EVENTS_CATS'],
//    ITEMS_LIST_EVENTS_TYPES => $GLOBALS['EVENTLOG_TYPES'],
//    ITEMS_LIST_EVENTS_SOURCES => EventLogRequested::get_events_sources_list(),
//);
?>
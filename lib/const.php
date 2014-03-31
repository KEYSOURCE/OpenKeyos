<?php

/**
* Constants settings
*
* Various constants to be used across the projects
*
* @package
* @subpackage Constants
*/

require_once(dirname(__FILE__) . '/const_db.php');
require_once(dirname(__FILE__) . '/smarty.php');
require_once(dirname(__FILE__) . '/const_notification.php');
require_once(dirname(__FILE__) . '/Route.php');
require_once(dirname(__FILE__) . '/Router.php');
require_once(dirname(__FILE__) . '/plugin_base.php');
define('PLUGINS_DIRECTORY', dirname(__FILE__)."/../".$conf['plugins_directory']);

$GLOBALS['PLUGINS'] = array();
if (PLUGINS_DIRECTORY.'/registerPlugins.php')
{
    //if we have a plugin folder and the registration for the plugins - load them
    require_once(PLUGINS_DIRECTORY.'/registerPlugins.php');
}

define('INTERFACE_MODE_ADMINISTRATOR', 1);
define('INTERFACE_MODE_CUSTOMER', 2);
define('INTERFACE_MODE_CUSTOMER_ADMINISTRATOR', 3);

define('PLUGIN_STATUS_ENABLED', 1);
define('PLUGIN_STATUS_DISABLED', 2);
define('PLUGIN_STATUS_INCONSISTENT', 3);

$GLOBALS['PLUGIN_STATUSES'] = array(
    PLUGIN_STATUS_ENABLED => 'Enabled',
    PLUGIN_STATUS_DISABLED => 'Disabled',
    PLUGIN_STATUS_INCONSISTENT => 'Inconsistent',
);


/** The default display class to use if none is specified */
define('DEFAULT_CLASS', 'base');
/** The default template to use if none is specified */
define('DEFAULT_TEMPLATE', 'index.html');
/** The templates directory used for showing static pages */
define('STATIC_PAGES_DIR', '_static_pages');

/** Sender information for outgoing mails - name */
define('SENDER_NAME', $conf['sender_name']);
/** Sender information for outgoing mails - email */
define('SENDER_EMAIL', $conf['sender_email']);

/** The customer ID of Keysource */
define('MANAGER_CUSTOMER_ID', $conf['manager_customer_id']);

/** The name of the cookie for storing the logged in user */
define('COOKIE_USER', 'keyosuser');
/** The name of the cookie for storing the type of the logged in user */
define('COOKIE_USER_TYPE', 'keyosusertype');
/** The duration of the authentication cookie */
define('COOKIE_DURATION', (60*60*6));
/** The domain name to be used for cookies, in the form .domain.com */
define('COOKIE_DOMAIN', preg_replace('/^[^.]*(\.[^:]*)(\:.*)*/', '\1', $_SERVER['HTTP_HOST']));

/** The path an name of the logfile */
define('LOGFILE', dirname(__FILE__).'/../logs/log.log');
/** The path an name of the errors logfile */
define('LOGFILE_ERR', dirname(__FILE__).'/../logs/errors.log');
/** Max size for a log file, in bytes */
define('LOGFILES_MAX_SIZE', 256*1024);
/** The maximum number of log files to keep */
define('LOGFILES_MAX_KEEP', 4);

/** The number of days of reported computers events to keep in database */
define ('EVENTS_LOG_KEEP_DAYS', ($conf['events_log_keep_days'] ? $conf['events_log_keep_days'] : 7));

/** Default datetime display format */
define('DATE_TIME_FORMAT', 'd.m.y H:i');
define('DATE_TIME_FORMAT_SECOND', 'd.m.y H:i:s');
define('DATE_FORMAT', 'd M Y');
define('DATE_FORMAT_SHORT', 'd.m.Y');
define('DATE_FORMAT_SHORT_2', 'd/m/y');
define('DATE_FORMAT_LONG', 'D, d M Y');

define('DATE_TIME_FORMAT_SMARTY', "%d/%m/%y %H:%M");
define('DATE_TIME_FORMAT_LONG_SMARTY', "%d %b %Y, %H:%M");
define('DATE_FORMAT_SMARTY', "%d/%m/%Y");
define('DATE_FORMAT_SHORT_SMARTY', "%d/%m/%y");
define('HOUR_FORMAT_SMARTY', "%H:%M");
define('DATE_FORMAT_LONG_SMARTY', "%d %b %Y");
define('DATE_FORMAT_LONG_DAY_SMARTY', "%a, %d %b %Y");

//define('DATE_FORMAT_JAVASCRIPT', "dd NNN yyyy");
define('DATE_FORMAT_SELECTOR', "%d/%m/%y");
define('HOUR_FORMAT_SELECTOR', "%H:%M");




/** Sets the HTTPS port number for the development server (it uses a non-standard port number */
//define('HTTPS_PORT', (ereg('www.proteusworld.com', $_SERVER['HTTP_HOST']) ? 8889 : 443));
define('HTTPS_PORT', 443);

if (isset($conf['skip_https']) and is_array($conf['skip_https'])) $GLOBALS['SKIP_HTTPS'] = $conf['skip_https'];
else $GLOBALS['SKIP_HTTPS'] = array ();

/** Directory where the contents of the 'File' type monitoring items are stored - make sure there is no trailing slash  */
define ('DIR_MONITOR_ITEMS_FILE', trim(preg_replace ('/\/+$/', '', $conf['dir_monitor_items_file'])));

/** Directory where to store Krifs uploads - make sure there is no trailing slash */
define ('DIR_UPLOAD_KRIFS', trim(preg_replace ('/\/+$/', '', $conf['dir_upload_krifs'])));
/** Directory where to store Klara uploads - make sure there is no trailing slash */
define ('DIR_UPLOAD_KLARA', trim(preg_replace ('/\/+$/', '', $conf['dir_upload_klara'])));
/** Directory where to store Kalm uploads - make sure there is no trailing slash */
define ('DIR_UPLOAD_KALM', trim(preg_replace ('/\/+$/', '', $conf['dir_upload_kalm'])));
/** Prefix for Klara uploads - customer internet contracts */
define ('FILE_PREFIX_INTERNET_CONTRACTS', 'customer_internet_contract_');
/** Directory where to store customer uploads, e.g. photos - make sure there is no trailing slash */
define ('DIR_UPLOAD_CUSTOMER', trim(preg_replace ('/\/+$/', '', $conf['dir_upload_customer'])));
/** Prefix for customers uploads - photos */
define ('FILE_PREFIX_CUSTOMER_PHOTO', 'customer_photo_');
/** Prefix for software licenses files */
define ('FILE_PREFIX_LICENSE_FILE', 'license_file_');

/** Directory where to store the uploaded MIB files */
define ('DIR_UPLOAD_MIBS', trim(preg_replace ('/\/+$/', '', $conf['dir_upload_mibs'])));
/** Prefix for uploaded MIB files */
define ('FILE_PREFIX_MIBS', 'mib_');

/** Directory where to store the exported XML files with intervention reports */
define ('DIR_EXPORT_XML_INTERVENTIONS', trim(preg_replace ('/\/+$/', '', $conf['dir_export_xml_interventions'])));

define ('DIR_EXPORT_XML_MREMOTE', trim(preg_replace ('/\/+$/', '', $conf['dir_export_xml_mremote'])));
/** Prefix for XML exported files with intervention reports */
define ('FILE_PREFIX_EXPORT_XML_INTERVENTIONS', 'interventions_export_');
/** Directory where to store the exported XML files with timesheets */
define ('DIR_EXPORT_XML_TIMESHEETS', trim(preg_replace ('/\/+$/', '', $conf['dir_export_xml_timesheets'])));
/** Prefix for XML exported files with timesheets */
define ('FILE_PREFIX_EXPORT_XML_TIMESHEETS', 'timesheets_export_');

/** Directory for holding the agent deployer scripts  */
define('DIR_AGENT_DEPLOYER', $conf['dir_agent_deployer']);
define('DIR_AGENT_DEPLOYER_LINK', $conf['dir_agent_deployer_link']);

/** Prefixes to use in asset numbers generation */
define ('ASSET_PREFIX_WORKSTATION', 'W');
define ('ASSET_PREFIX_SERVER', 'S');
define ('ASSET_PREFIX_PERIPHERAL', 'P');
define ('ASSET_PREFIX_AD_PRINTER', 'A');
define ('ASSET_NUM_LENGTH', 5); // The length of the numeric part of the generated asset numbers

/** Settings for Exchange interface */
define ('EXCHANGE_SERVER', $conf['exchange_server']);
define ('EXCHANGE_BASE_URI', $conf['exchange_base_uri']);
define ('EXCHANGE_PROTOCOL', ($conf['exchange_protocol'] ? strtoupper($conf['exchange_protocol']) : 'HTTP'));
define ('EXCHANGE_WEB_PORT', ($conf['exchange_web_port'] ? $conf['exchange_web_port'] : (EXCHANGE_PROTOCOL == 'HTTP' ? 80 : 443)));
//define ('EXCHANGE_CRT_CA', $conf['exchange_crt_ca']);

/**
* The paths and names of the display classes.
*
* The array keys represent the "short" names, as passed in URLs or used in classload_display().
* The keys for each of the main array items contain:
*  - class:		The real name of the class
*  - friendly_name:	A friendly name to be used in displays
*  - file:		The full path to he file containing the specified class
*  - default method:	The name of the method to be invoked if no method is specified in URL
*
* @global	array $GLOBALS['CLASSES_DISPLAY']
*/
$GLOBALS['CLASSES_DISPLAY'] = array(
	'base' => array (
		'class' => 'BaseDisplay',
		'friendly_name' => 'Base',
		'file'  => dirname(__FILE__).'/base_display.php',
		'default_method' => 'display'
	),
    'plugin_controller' => array(
        'class' => 'PluginController',
        'friendly_name' => 'PluginController',
        'file'  => dirname(__FILE__).'/plugin_controller.php',
        'default_method' => 'display'
     ),
	'home' => array (
		'class' => 'HomeDisplay',
		'friendly_name' => 'Home',
		'file' => dirname(__FILE__).'/../core/controllers/home/home_display.php',
		'default_method' => 'notifications'
	),
	'user' => array (
		'class' => 'UserDisplay',
		'friendly_name' => 'Users management',
		'file' => dirname(__FILE__).'/../core/controllers/user/user_display.php',
		'default_method' => 'manage_users'
	),
    'keyos_connect' => array(
        'class' => 'KeyosConnectDisplay',
        'friendly_name' => 'KeyosConnect',
        'file' => dirname(__FILE__).'/../core/controllers/KeyosConnect/keyos_connect_display.php',
        'default_method' => 'manage_plugins',
    ),
);


/**
* The display classes for which ACL will be enforced
* @global	array	$GLOBALS['CLASSES_DISPLAY_ACL']
*/
$GLOBALS['CLASSES_DISPLAY_ACL'] = array (
	'kawacs', 'kalm', 'kams', 'user', 'erp', 'customer_krifs', 'kerm', 'klara', 'customer', 'kb'
);


/**
* The paths of the data processing classes
*
* The array keys represent the name of the classes, while the values represent the full path to the file containing the class
*
* @global	array $GLOBALS['CLASSES']
*/
$GLOBALS['CLASSES'] = array(
	'Base' => dirname(__FILE__).'/base.php',
    'PluginModel' => dirname(__FILE__).'plugin_model.php',
	'Auth' => dirname(__FILE__).'/auth.php',
	'User' => dirname(__FILE__).'/../core/models/user/user.php',	
	'nusoap' => (dirname(__FILE__).'/../_external/nusoap/nusoap.php'),
    'RemovedUser' => dirname(__FILE__).'/../core/models/user/removed_user.php',
	'Group' => dirname(__FILE__).'/../core/models/user/group.php',
	'UserPhone' => dirname(__FILE__).'/../core/models/user/user_phone.php',
	'UserExchange' => dirname(__FILE__).'/../core/models/user/user_exchange.php',
	'DiscoverySetting' => dirname (__FILE__).'/../core/models/discovery/discovery_setting.php',
	'DiscoverySettingDetail' => dirname (__FILE__).'/../core/models/discovery/discovery_setting_detail.php',
	'Discovery' => dirname (__FILE__).'/../core/models/discovery/discovery.php',
	'SnmpSysobjid'  => dirname (__FILE__).'/../core/models/discovery/snmp_sysobjid.php',
	'Notification' => dirname (__FILE__).'/../core/models/notification/notification.php',
    'MessageLog' => dirname (__FILE__).'/../core/models/notification/message_log.php',
	'NotificationRecipient' => dirname (__FILE__).'/../core/models/notification/notification_recipient.php',
    'InfoRecipients' => dirname(__FILE__) . '/../core/models/notification/info_recipients.php',
	'KawacsAgentUpdate' => dirname (__FILE__).'/../core/models/kawacs_agent_update/kawacs_agent_update.php',
	'KawacsAgentUpdateFile' => dirname (__FILE__).'/../core/models/kawacs_agent_update/kawacs_agent_update_file.php',
	'KawacsAgentUpdatePreview' => dirname (__FILE__).'/../core/models/kawacs_agent_update/kawacs_agent_update_preview.php',
	'KawacsAgentLinuxUpdate' => dirname (__FILE__).'/../core/models/kawacs_agent_update/kawacs_agent_linux_update.php',
	'Acl' => dirname (__FILE__).'/../core/models/acl/acl.php',
	'AclRole' => dirname (__FILE__).'/../core/models/acl/acl_role.php',
	'AclCategory' => dirname (__FILE__).'/../core/models/acl/acl_category.php',
	'AclItem' => dirname (__FILE__).'/../core/models/acl/acl_item.php',
	'AclItemOperation' => dirname (__FILE__).'/../core/models/acl/acl_item_operation.php',
	'HttpConn' => dirname (__FILE__).'/../core/models/http/http_conn.php',
	'ExchangeInterface' => dirname (__FILE__).'/../core/models/exchange/exchange_interface.php',
	'Mib' => dirname (__FILE__).'/../core/models/snmp/mib.php',
	'MibOid' => dirname (__FILE__).'/../core/models/snmp/mib_oid.php',
	'ImapConnector' => dirname(__FILE__) . '/../_external/imap/imap_connector.php',
    'HTMLPurifier' => dirname(__FILE__) . '/../_external/htmlpurifier/library/HTMLPurifier.php',
);

/** Logging level - errors only */
define ('LOG_LEVEL_ERRORS', 1);
/** Logging level - errors only */
define ('LOG_LEVEL_TRACE', 2);
/** Logging level - errors only */
define ('LOG_LEVEL_DEBUG', 3);

/** User account associated with a KeySource employee */
define ('USER_TYPE_KEYSOURCE', 2);
/** User account associated with a KeySource customer */
define ('USER_TYPE_CUSTOMER', 4);
/** A group of KeySource users */
define ('USER_TYPE_KEYSOURCE_GROUP', 8);
/** A group of users (either KeySource or customer users) */
define ('USER_TYPE_GROUP', 16);
/** User account associated with a customer, but only for shop, not Keyos */
define ('USER_TYPE_CUSTOMER_SHOP', 32);

define ('KEYOS_TYPES', 2+4+8+16);

$GLOBALS['USER_TYPES'] = array(
	USER_TYPE_KEYSOURCE => 'Keysource user',
	USER_TYPE_CUSTOMER => 'Customer user',
	USER_TYPE_CUSTOMER_SHOP => 'Shop user',
	USER_TYPE_KEYSOURCE_GROUP => 'Keysource group',
	USER_TYPE_GROUP => 'Group'
);

$GLOBALS['USER_ONLY_TYPES'] = array(
	USER_TYPE_KEYSOURCE => $GLOBALS['USER_TYPES'][USER_TYPE_KEYSOURCE],
	USER_TYPE_CUSTOMER => $GLOBALS['USER_TYPES'][USER_TYPE_CUSTOMER],
);

$GLOBALS['GROUP_ONLY_TYPES'] = array(
	USER_TYPE_KEYSOURCE_GROUP => $GLOBALS['USER_TYPES'][USER_TYPE_KEYSOURCE_GROUP],
	USER_TYPE_GROUP => $GLOBALS['USER_TYPES'][USER_TYPE_GROUP],
);

/** Active status - not active */
define ('USER_STATUS_INACTIVE', 0);
/** Active status - active */
define ('USER_STATUS_ACTIVE', 1);
/** Active status - away on business */
define ('USER_STATUS_AWAY_BUSINESS', 2);
/** Active status - away on holiday */
define ('USER_STATUS_AWAY_HOLIDAY', 3);

$GLOBALS['USER_STATUSES'] = array (
	USER_STATUS_INACTIVE => 'Not active',
	USER_STATUS_ACTIVE => 'Active',
	USER_STATUS_AWAY_BUSINESS => 'Away on business',
	USER_STATUS_AWAY_HOLIDAY => 'Away on holiday'
);


/** Filtering condition for fetching users based on active status: get both Active and Away */
define ('USER_FILTER_ACTIVE_AWAY', -2);
/** Filtering condition for fetching users based on active status: all users (active, inactive, away) */
define ('USER_FILTER_ALL', -1);


/** ACL role type: for Keysource users */
define ('ACL_ROLE_TYPE_KEYSOURCE', 1);
/** ACL role type: for customers */
define ('ACL_ROLE_TYPE_CUSTOMER', 2);

$GLOBALS['ACL_ROLE_TYPES'] = array (
	ACL_ROLE_TYPE_KEYSOURCE => 'Keysource ACL role',
	ACL_ROLE_TYPE_CUSTOMER => 'Customer ACL role'
);

/** The default role that will be assigned to new customers */
define ('DEFAULT_CUSTOMER_ROLE', $conf['default_customer_role']);


define ('PHONE_TYPE_MOBILE', 2);
define ('PHONE_TYPE_OFFICE', 4);
define ('PHONE_TYPE_HOME', 6);

$GLOBALS['PHONE_TYPES'] = array(
	PHONE_TYPE_MOBILE => 'Mobile',
	PHONE_TYPE_OFFICE => 'Business fixed',
	PHONE_TYPE_HOME => 'Home fixed'
);


define ('FILE_NAME_AGENT', 1);
define ('FILE_NAME_LIB', 2);
define ('FILE_NAME_KAWACS', 3);
define ('FILE_NAME_MANAGER', 4);
define ('FILE_NAME_ZIPDLL', 5);

$GLOBALS['KAWACS_AGENT_FILES'] = array (
	FILE_NAME_KAWACS => 'Kawacs.exe',
	FILE_NAME_AGENT => 'KawacsAgent.exe',
	FILE_NAME_LIB => 'KawacsLib.dll',
	FILE_NAME_MANAGER => 'KawacsManager.exe',
	FILE_NAME_ZIPDLL => 'UnzDll.dll'
);

/** The default name for installer archive */
define ('FILE_NAME_KAWACS_INSTALLER', 'kawacs_agent.zip');

/** The default name for Linux installer archive */
define ('FILE_NAME_KAWACS_INSTALLER_LINUX', 'kawacs_agent_linux.tar.gz');

/** The directory where the Kawacs Agent updates files will be kept */
define ('UPDATES_DIR_KAWACS_AGENT', 'updates/kawacs_agent');

/** The directory where the Kawacs Agent Linux updates files will be kept */
define ('UPDATES_DIR_KAWACS_AGENT_LINUX', 'updates/kawacs_agent_linux');

/** Defines how long quick contacts will be kept in the database - in hours */
define ('QUICK_CONTACTS_KEEP', 1);


/** Array with the number of items per page options */
$GLOBALS['PER_PAGE_OPTIONS'] = array (
	'5' => '5',
	'10' => '10',
	'20' => '20',
	'30' => '30',
	'50' => '50',
	'75' => '75',
	'100' => '100',
	'500' => '500'
);

/** Day codes */
define ('DAY_SUN', 1);
define ('DAY_MON', 2);
define ('DAY_TUE', 4);
define ('DAY_WED', 8);
define ('DAY_THU', 16);
define ('DAY_FRI', 32);
define ('DAY_SAT', 64);


$GLOBALS ['DAY_NAMES'] = array (
	DAY_MON => 'Monday',
	DAY_TUE => 'Tuesday',
	DAY_WED => 'Wednesday',
	DAY_THU => 'Thursday',
	DAY_FRI => 'Friday',
	DAY_SAT => 'Saturday',
	DAY_SUN => 'Sunday'
);

/** Object class for customer photos: Computer */
define ('PHOTO_OBJECT_CLASS_COMPUTER', 1);
/** Object class for customer photos: Peripheral */
define ('PHOTO_OBJECT_CLASS_PERIPHERAL', 2);
/** Object class for customer photos: Location */
define ('PHOTO_OBJECT_CLASS_LOCATION', 3);

$GLOBALS['PHOTO_OBJECT_CLASSES'] = array (
	PHOTO_OBJECT_CLASS_COMPUTER => 'Computer',
	PHOTO_OBJECT_CLASS_PERIPHERAL => 'Peripheral',
	PHOTO_OBJECT_CLASS_LOCATION => 'Location',
);

/** Uploaded images max width */
define ('IMAGE_MAX_WIDTH', ($conf['image_max_width'] ? $conf['image_max_width'] : 800));
/** Uploaded images max height */
define ('IMAGE_MAX_HEIGHT', ($conf['image_max_height'] ? $conf['image_max_height'] : 600));
/** Thumbnails max width */
define ('THUMBNAIL_MAX_WIDTH', ($conf['thumbnail_max_width'] ? $conf['thumbnail_max_width'] : 300));
/** Thumbnails max height */
define ('THUMBNAIL_MAX_HEIGHT', ($conf['thumbnail_max_height'] ? $conf['thumbnail_max_height'] : 200));
/** Suffix for filename with image thumbnails */
define ('THUMBNAIL_SUFFIX', '_thumb');

/** Fixed location type: country */
define ('LOCATION_FIXED_TYPE_COUNTRY', 1);
/** Fixed location type: province */
define ('LOCATION_FIXED_TYPE_PROVINCE', 2);
/** Fixed location type: town */
define ('LOCATION_FIXED_TYPE_TOWN', 3);

$GLOBALS['LOCATION_FIXED_TYPES'] = array (
	LOCATION_FIXED_TYPE_COUNTRY => 'Country',
	LOCATION_FIXED_TYPE_PROVINCE => 'Province',
	LOCATION_FIXED_TYPE_TOWN => 'Town'
);

/** Location type: complex */
define ('LOCATION_TYPE_COMPLEX', 1);
/** Location type: building */
define ('LOCATION_TYPE_BUILDING', 2);
/** Location type: floor */
define ('LOCATION_TYPE_FLOOR', 3);
/** Location type: room */
define ('LOCATION_TYPE_ROOM', 4);

$GLOBALS['LOCATION_TYPES'] = array (
	LOCATION_TYPE_COMPLEX => 'Complex',
	LOCATION_TYPE_BUILDING => 'Building',
	LOCATION_TYPE_FLOOR => 'Floor',
	LOCATION_TYPE_ROOM => 'Room'
);

/** Array with location types which can be assigned to top-level locations */
$GLOBALS['LOCATION_TYPES_TOP'] = array (
	LOCATION_TYPE_COMPLEX => 'Complex',
	LOCATION_TYPE_BUILDING => 'Building',
);

/** Language options for Keyos interface and newsletters */
define ('LANG_FR', 1);
define ('LANG_EN', 2);

$GLOBALS['LANGUAGES'] = array (
	LANG_FR => 'Fran?ais',
	LANG_EN => 'English'
);

$GLOBALS['LANGUAGE_CODES'] = array (
	LANG_FR => 'fr',
	LANG_EN => 'en'
);

/** The ID of the computer item which stores the currently logged in user */
define ('CURRENT_USER_ITEM_ID', 1000);
/** The ID of the computer item which stores computer names */
define ('NAME_ITEM_ID', 1001);
/** The ID of the computer item storing computer brand */
define ('BRAND_ITEM_ID', 1002);
/** The ID of the computer item which stores the OS name */
define ('OS_NAME_ITEM_ID', 1008);
/** The ID of the computer item which stores the disk partitions */
define ('PARTITIONS_ITEM_ID', 1013);
/** The ID of the computer item which stores installed software */
define ('SOFTWARE_ITEM_ID', 1019);
/** The ID of the computer item which stores network interfaces */
define ('NET_ADAPTERS_ITEM_ID', 1021);
/** The ID of the computer item which stores antivirus information */
define ('AV_STATUS_ITEM_ID', 1025);
/** The ID of the computer item which stores AD Printers */
define ('ADPRINTERS_ITEM_ID', 1031);
/** The ID of the computer item which stores backup information */
define ('BACKUP_STATUS_ITEM_ID', 1044);
/** The ID of the computer item which stores warranties information */
define ('WARRANTY_ITEM_ID', 2001);
/** The ID of the computer item referring to Event Log */
define ('EVENTS_ITEM_ID', 3000);
/** The ID of the computer item for transmitting network discoveries - This is a special ID, which doesn't have an actual MonitorItem object in the database. */
define ('DISCOVERY_ITEM_ID', 3001);

/** The field ID of the computer item which stores common name for AD Printers */
define ('FIELD_ID_AD_PRINTER_CN', 91);
/** The field ID of the computer item which stores common name for AD Printers */
define ('FIELD_ID_AD_PRINTER_CANONICAL_NAME', 90);
/** The field ID of the computer item which stores the descriptive name for AD Printers */
define ('FIELD_ID_AD_PRINTER_NAME', 96);
/** The field ID of the computer item which stores network interface name */
define ('FIELD_ID_NET_NAME', 18);
/** The field ID of the computer item which stores network interface MAC */
define ('FIELD_ID_NET_MAC', 19);
/** The field ID of the computer item which stores network IP address */
define ('FIELD_ID_NET_IP', 20);
/** The field ID of the computer item which stores network mask */
define ('FIELD_ID_NET_MASK', 284);
/** The field ID of the computer item which stores SN for warranties */
define ('FIELD_ID_WARRANTY_SN', 207);
/** The field ID of the computer item which stores start date for warranties */
define ('FIELD_ID_WARRANTY_START', 208);
/** The field ID of the computer item which stores end date for warranties */
define ('FIELD_ID_WARRANTY_END', 209);
/** The field ID of the computer item which stores the service package */
define ('FIELD_ID_WARRANTY_SERVICE_PACKAGE', 210);
/** The field ID of the computer item which stores the service level */
define ('FIELD_ID_WARRANTY_SERVICE_LEVEL', 211);
/** The field ID of the computer item for events logs which stores the event date */
define ('FIELD_ID_EVENTS_LOG_DATE', 251);
/** The field ID of the computer item for events logs which stores the event category (Application, System etc.) */
define ('FIELD_ID_EVENTS_LOG_CATEGORY', 252);
/** The field ID of the computer item for events logs which stores the event type (Error, Warning etc.) */
define ('FIELD_ID_EVENTS_LOG_TYPE', 253);
/** The field ID of the computer item for events logs which stores the event source */
define ('FIELD_ID_EVENTS_LOG_SOURCE', 254);
/** The field ID of the computer item for events logs which stores the event ID */
define ('FIELD_ID_EVENTS_LOG_EVENT_ID', 255);
/** The field ID of the computer item for events logs which stores the event description */
define ('FIELD_ID_EVENTS_LOG_DESCRIPTION', 256);
/** The field ID of the computer item for events logs which says if the event should be ignored */
define ('FIELD_ID_EVENTS_LOG_IGNORED', 257);


/** The string which identifies VMWare machines (testing in item 1002 - computer brand) */
define ('VMWARE_BRAND_MARKER', 'VMware, Inc.');




//added by Victor
define ('KEYOS_TEMP_FILE', $conf['dir_keyos_temp']);
define ('KEYOS_EXTERNAL', $conf['dir_keyos_external']);
define ('KEYOS_BASE_URL', $conf['base_url']);
define('KEYOS_KAWACS_SERVER', KEYOS_BASE_URL."/kawacs.php");
//end added by Victor

define('MERGE_USERS_ACT', 101);
define('SET_CUSTOMER_ACT', 102);
define('RESTORE_USER_ACT', 103);
$GLOBALS['USER_SP_ACT'] = array(
	MERGE_USERS_ACT => "Merged users accounts",
	SET_CUSTOMER_ACT => "Add or remove users accounts",
	RESTORE_USER_ACT => "Restore user account"
);

define('CKERM_STATUS_NEW', 0);
define('CKERM_STATUS_MODI', 1);
define('CKERM_STATUS_APPROVED', 2);
define('CKERM_STATUS_FINALIZED', 3);
define('CKERM_STATUS_REJECTED', 4);

$GLOBALS['CUSTOMER_KERM_USERS_STATUSES'] = array(
	CKERM_STATUS_NEW => "New",
	CKERM_STATUS_MODI => "Modified",
	CKERM_STATUS_APPROVED => "Approved by operator",
	CKERM_STATUS_FINALIZED => "Finalized",
	CKERM_STATUS_REJECTED => "Rejected"
);

define('ACCOUNT_MANAGER_KS', 6);
define('ACCOUNT_MANAGER_MPI', 576);
define('DEFAULT_ACCOUNT_MANAGER', ACCOUNT_MANAGER_KS);
$GLOBALS['ACCOUNT_MANAGERS'] = array(
    ACCOUNT_MANAGER_KS => "Keysource",
    ACCOUNT_MANAGER_MPI => "MPI"
);
$GLOBALS['ACCOUNT_MANAGERS_LOGOS'] = array(
	ACCOUNT_MANAGER_KS => "logo_ksrc.gif",
	ACCOUNT_MANAGER_MPI => "logo_ksrcmpi.jpg"
);
$GLOBALS['ACCOUNT_MANAGERS_INFO']  = array(
	ACCOUNT_MANAGER_KS => array(
		"name" => "KeySource scrl",
		"address" => "Av. de la Couronne 480",
		"city" => "1050 Brussels",
		"country" => "Belgium",
		"phone" => "T +32-2-62.61.333",
		"fax" => "F +32-2-62.61.339",
		"email" => "info@keysource.be",
		"web" => "www.keysource.be",
		"tva" => "TVA: BE 435 019 363",
		"rcb" => "RCB: 508.360",
		"bbl" => "BBL: 310-0808309-94",
		"fortis" => "FORTIS: 210-0533549-04"
	),
	ACCOUNT_MANAGER_MPI => array(
		"name" => "MPI-KS Groupe Keysource",
		"address" => "11 route d'iss�",
		"city" => "44110 ch�teaubriant",
		"country" => "France",
		"phone" => "T : 02 40 81 03 80",
		"fax" => "FAX : 02 40 81 05 76",
		"email" => "info@keysource.eu",
		"web" => "www.mpi44.com",
		"tva" => "",
		"rcb" => "",
		"bbl" => "",
		"fortis" => ""
	)
);

$GLOBALS['MAIN_CUSTOMER_ADMINISTRATOR_MODULES'] = array();
$GLOBALS['CUSTOMER_PLUGINS'] = array();
$GLOBALS['MENU'] = array();
$GLOBALS['MENU_CUSTOMER'] = array();
$GLOBALS['MENU_CUSTOMER_ADMINISTRATOR'] = array();
$GLOBALS['ENABLED_PLUGINS'] = array();
$GLOBALS['DISABLED_PLUGINS'] = array();

//add the plugins information

$routerObj = Router::getInstance();
$routerObj->setBasePath(BASE_URL);

//PluginBAse::plugins_load_order();

foreach($GLOBALS['PLUGINS'] as $plugin_key => $plugin){
    PluginBase::load($plugin_key, $plugin);
}

$routerObj->map(
    '/:cl[/]*',
    array(),
    array(
        'methods' => array('GET', 'POST'),
        'name' => 'default' ,
        'filters' => array(
            'cl' =>  '([a-zA-Z][a-zA-Z0-9_-]*)',
        ),
    )
);

$routerObj->map(
    '/:cl[/]*:op[/]*',
    array(),
    array(
        'methods' => array('GET', 'POST'),
        'name' => 'default' ,
        'filters' => array(
            'cl' =>  '([a-zA-Z][a-zA-Z0-9_-]*)',
            'action' => '([a-zA-Z][a-zA-Z0-9_-]*)',
        ),
    )
);

$routeObj = $routerObj->matchCurrentRequest();

if($routeObj){
    $route_params = $routeObj->getParameters();
    $route_params = array_merge($route_params, $routeObj->getTarget());
    //debug($route_params);
    foreach($route_params as $k=>$v){
        if(strtoupper($_SERVER['REQUEST_METHOD']) == "POST"){
            if($k == 'op') if(!isset($_REQUEST['op'])) $_REQUEST[$k] = $v;
            if($k == 'cl') if(!isset($_REQUEST['cl'])) $_REQUEST[$k] = $v;
            else $_REQUEST[$k] = $v;
        }
        else{
            $_REQUEST[$k] = $v;
        }
    }
}
?>

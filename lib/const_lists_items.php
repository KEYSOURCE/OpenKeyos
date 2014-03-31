<?php

/**
* Constants with the list definitions for MONITOR_TYPE_LIST items.
*
* This must be loaded after all other constants and global variables are set,
* since the list definitions can contain various variables defined elsewhere.
*
* @package
* @subpackage Constants_KAWACS
*/

require_once 'const.php';


// IDs of the lists for MONITOR_TYPE_LIST
define ('ITEMS_LIST_BOOL', 1);				// Simple yes/no selector
define ('ITEMS_LIST_SERVICES_STATS', 2);		// Status (running, stopped etc.) of system services on computers
define ('ITEMS_LIST_SERVICE_LEVELS', 3);		// List of services levels - see ServiceLevel class
define ('ITEMS_LIST_SERVICE_PACKAGES', 4);		// List of service packages offered by suppliers - see SupplierServicePackage class
define ('ITEMS_LIST_EVENTS_CATEGORIES', 5);		// List of events log categories (Application, System etc.)
define ('ITEMS_LIST_EVENTS_TYPES', 6);			// List of events log types (Error, Warning, Information etc.)
define ('ITEMS_LIST_EVENTS_SOURCES', 7);			// List of events log sources

//Array with the name of lists for MONITOR_TYPE_LIST items. MUST be in sync with $GLOBALS['AVAILABLE_ITEMS_LISTS']
$GLOBALS['AVAILABLE_ITEMS_LISTS_NAMES'] = array (
	ITEMS_LIST_BOOL => 'Yes/No',
	ITEMS_LIST_SERVICES_STATS => 'Computer services status',
	ITEMS_LIST_SERVICE_LEVELS => 'Support service levels',
	ITEMS_LIST_SERVICE_PACKAGES => 'Suppliers service packages',
	ITEMS_LIST_EVENTS_CATEGORIES => 'Events log categories',
	ITEMS_LIST_EVENTS_TYPES => 'Events log types',
	ITEMS_LIST_EVENTS_SOURCES => 'Events log sources',
);

//Array with the lists of available values list for MONITOR_TYPE_LIST items. MUST be in sync with $GLOBALS['AVAILABLE_ITEMS_LISTS_NAMES']
$GLOBALS['AVAILABLE_ITEMS_LISTS'] = array (
	ITEMS_LIST_BOOL => array (
		0 => 'No',
		1 => 'Yes'
	),
	ITEMS_LIST_SERVICES_STATS => array (
		1 => 'Stopped',
		2 => 'Starting',
		3 => 'Stopping',
		4 => 'Running',
		5 => 'Continue pending',
		6 => 'Pause pending',
		7 => 'Paused'
	),
    ITEMS_LIST_SERVICE_LEVELS => class_load('ServiceLevel') ? ServiceLevel::get_service_levels_list() : array(),
	ITEMS_LIST_SERVICE_PACKAGES => class_load('SupplierServicePackage') ? SupplierServicePackage::get_service_packages_list(array('prefix_supplier'=>true)) : array(),
	ITEMS_LIST_EVENTS_CATEGORIES => $GLOBALS['EVENTS_CATS'],
	ITEMS_LIST_EVENTS_TYPES => $GLOBALS['EVENTLOG_TYPES'],
	ITEMS_LIST_EVENTS_SOURCES => class_load('EventLogRequested') ? EventLogRequested::get_events_sources_list() : array(),
);

?>
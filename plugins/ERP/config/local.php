<?php

/**
* Constants settings
*
* Constants used for ERP integration
*
* @package
* @subpackage Constants
*/

/** ERP Database server */
define ('ERP_DB_HOST', $conf['erp_db_host']);
/** ERP Database user name */
define ('ERP_DB_USER', $conf['erp_db_user']);
/** ERP Database password */
define ('ERP_DB_PWD', $conf['erp_db_password']);
/** ERP Database name */
define ('ERP_DB_NAME', $conf['erp_db_name']);


/** Statuses for exports of intervention reports */
define ('INTERVENTION_EXPORT_STAT_NEW', 1);
define ('INTERVENTION_EXPORT_STAT_REQUESTED', 2);
define ('INTERVENTION_EXPORT_STAT_SENT', 3);
define ('INTERVENTION_EXPORT_STAT_FILE_CONFIRMED', 4);
define ('INTERVENTION_EXPORT_STAT_IMPORT_CONFIRMED', 5);
define ('INTERVENTION_EXPORT_STAT_CENTRALIZED', 6);
define ('INTERVENTION_EXPORT_STAT_ERROR', 10);

/** Statuses for exports of intervention reports */
$GLOBALS['INTERVENTIONS_EXPORTS_STATS'] = array (
	INTERVENTION_EXPORT_STAT_NEW => 'New',
	INTERVENTION_EXPORT_STAT_REQUESTED => 'Requested',
	INTERVENTION_EXPORT_STAT_SENT => 'Sent',
	INTERVENTION_EXPORT_STAT_FILE_CONFIRMED => 'File OK confirmed',
	INTERVENTION_EXPORT_STAT_IMPORT_CONFIRMED => 'Import OK confirmed',
	INTERVENTION_EXPORT_STAT_CENTRALIZED => 'Centralized',
	INTERVENTION_EXPORT_STAT_ERROR => 'ERROR'
);

/** Codes for actions taken for exports of intervention reports */
define ('INTERVENTION_EXPORT_ACTION_FILE_CONFIRM', 2);
define ('INTERVENTION_EXPORT_ACTION_IMPORT_CONFIRM', 3);
define ('INTERVENTION_EXPORT_ACTION_RETRANSFER', 4);

/** Codes for actions taken for exports of intervention reports */
$GLOBALS['INTERVENTION_EXPORT_ACTIONS'] = array (
	INTERVENTION_EXPORT_ACTION_FILE_CONFIRM => 'File confirmed',
	INTERVENTION_EXPORT_ACTION_IMPORT_CONFIRM => 'Import confirmed',
	INTERVENTION_EXPORT_ACTION_RETRANSFER => 'Retransfer',
);

/** Syncronisation status for objects which have to be syncronized with ERP */
define ('ERP_SYNC_STAT_KS_NEW', 1);
define ('ERP_SYNC_STAT_ERP_NEW', 2);
define ('ERP_SYNC_STAT_MODIFIED', 3);
define ('ERP_SYNC_STAT_ERP_INCOMPLETE', 4);

$GLOBALS['ERP_SYNC_STATS'] = array (
	ERP_SYNC_STAT_KS_NEW => 'Only in Keyos',
	ERP_SYNC_STAT_ERP_NEW => 'Only in ERP',
	ERP_SYNC_STAT_MODIFIED => 'Modified',
	ERP_SYNC_STAT_ERP_INCOMPLETE => 'Incomplete ERP info'
);

/** Array for translating ERP customer types ('customer.cat_1' or 'stock.s_cat2') into Keyos codes */
$GLOBALS['ERP_CONTRACT_TYPES'] = array (
	'AllCustomers' => CONTRACT_ALL,
	'TotalCare' => CONTRACT_TOTAL_CARE,
	'KTC' => CONTRACT_TOTAL_CARE,
	'basic' => CONTRACT_BASIC,
	'Basic' => CONTRACT_BASIC,
	'Key Pro' => CONTRACT_KEYPRO,
	'KeyPro' => CONTRACT_KEYPRO,
	'GlobalPro' => CONTRACT_KEYPRO,
	'KeyPro Global' => CONTRACT_KEYPRO
);

/** Array for translating ERP customer sub-types ('customer.cat_2') into Keyos codes - see also $GLOBALS['CUST_SUBTYPES'] */
$GLOBALS['ERP_CUST_SUBTYPES'] = array (
	'Basic' => CUST_SUBTYPE_BASIC,
	'Key Pro' => CUST_SUBTYPE_KEYPRO,
	'KeyPro' => CUST_SUBTYPE_KEYPRO,
	'Global Pro' => CUST_SUBTYPE_GLOBALPRO,
	'KeyPro Global' => CUST_SUBTYPE_GLOBALPRO,
	'Total Care' => CUST_SUBTYPE_TC
);

/** Array for getting the sub-type for actions from ERP field 'stock.cat2' - see also $GLOBALS['CUST_SUBTYPES'] */
$GLOBALS['ERP_CONTRACT_SUBTYPES_ACTIONS'] = array (
	'TotalCare' => CUST_SUBTYPE_TC,
	'KeyPro' => CUST_SUBTYPE_KEYPRO,
	'Basic' => CUST_SUBTYPE_BASIC,
	'GlobalPro' => CUST_SUBTYPE_GLOBALPRO
);

/** Array for translating ERP customer price types ('customer.c_tarif') into Keyos codes */
$GLOBALS['ERP_CUST_PRICETYPES'] = array (
	'1' => CUST_PRICETYPE_BASIC,
	'2' => CUST_PRICETYPE_KEYPRO,
	'3' => CUST_PRICETYPE_TC1,
	'4' => CUST_PRICETYPE_TC2,
	'5' => CUST_PRICETYPE_TC3,
);


/** Array for translating ERP price types for actions into Keyos codes - see $GLOBALS['PRICE_TYPES'] in Krifs constans */
$GLOBALS['ERP_PRICE_TYPES'] = array (
	'HourlyBased' => PRICE_TYPE_HOURLY,
	'FixedBased' => PRICE_TYPE_FIXED
);

/** The ERP article ID for representing travel cost items in invoicing details */
define ('ERP_TRAVEL_ID', '136787');
define ('ERP_TRAVEL_CODE', 'DEPLAC');
define ('ERP_TRAVEL_NAME', 'Travel costs');

?>
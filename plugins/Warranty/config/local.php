<?php

/**
* Constants relative to warranties
*
* @package
* @subpackage Constants
*/


/** Warranties objects types */
define ('WAR_OBJ_COMPUTER', 1);
define ('WAR_OBJ_PERIPHERAL', 2);
define ('WAR_OBJ_AD_PRINTER', 3);
define ('WAR_OBJ_REMOVED_COMPUTER', 4);
define ('WAR_OBJ_REMOVED_PERIPHERAL', 5);
define ('WAR_OBJ_REMOVED_AD_PRINTER', 6);

/** The IDs of the warranty item fields for computers and the corresponding fields for Warranty object */
$GLOBALS['WARRANTY_ITEM_FIELDS'] = array(
    27 => 'product',
    207 => 'sn',
    208 => 'warranty_starts',
    209 => 'warranty_ends',
    210 => 'service_package_id',
    211 => 'service_level_id',
    212 => 'contract_number',
    213 => 'hw_product_id',
    247 => 'raise_alert',
    248 => 'replaced_ignored'
);

/** The mapping of warranty information field from AD Printers to Warranty object */
$GLOBALS['AD_PRINTERS_FIELDS'] = array (
	'sn' => 'sn',
	'warranty_starts' => 'warranty_starts',
	'warranty_ends' => 'warranty_ends',
	'service_package_id' => 'service_package_id',
	'service_level_id' => 'service_level_id',
	'contract_number' => 'contract_number',
	'hw_product_id' => 'hw_product_id',
	'product_number' => 'product_number'
);

/** Definitions of warranties fields for peripherals. Will be loaded in Warranty objects each time a new peripheral class is processed */
$GLOBALS['PRINTERS_WARRANTY_FIELDS'] = array ();

/** Warranties colors */
define ('WAR_COL_OK', '#00BB00');
define ('WAR_COL_EXPIRED', '#666666');
define ('WAR_COL_REPLACED', '#DDDDDD');
define ('WAR_COL_6_MONTHS', '#99CC00');
define ('WAR_COL_3_MONTHS', 'orange');
define ('WAR_COL_1_MONTH', 'red');

/** Warranties color codes - these relate to styles defined in main.css */
define ('WAR_COL_CODE_OK', 1);
define ('WAR_COL_CODE_EXPIRED', 2);
define ('WAR_COL_CODE_REPLACED', 3);
define ('WAR_COL_CODE_6_MONTHS', 4);
define ('WAR_COL_CODE_3_MONTHS', 5);
define ('WAR_COL_CODE_1_MONTH', 6);

?>
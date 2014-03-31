<?php

/**
* KALM specific constants
* 
* Various constants to be used across the projects
*
* @package
* @subpackage Constants_KALM
*/


/** Criteria for comparing names */
$GLOBALS['NAMES_MATCH_TYPES'] = array (
	CRIT_STRING_MATCHES => 'Equals',
	CRIT_STRING_STARTS => 'Starts with',
	CRIT_STRING_ENDS => 'Ends with',
	CRIT_STRING_CONTAINS => 'Contains'
);

/** Types of licensing software */
define ('LIC_TYPE_SEAT', 1);
define ('LIC_TYPE_SERVER', 2);
define ('LIC_TYPE_CLIENT', 4);
define ('LIC_TYPE_COMPANY', 8);
define ('LIC_TYPE_FREEWARE', 16);

$GLOBALS['LIC_TYPES_NAMES'] = array(
	LIC_TYPE_SEAT => 'Per seat',
	LIC_TYPE_SERVER => 'Per server', 
	LIC_TYPE_CLIENT => 'Per concurrent client',
	LIC_TYPE_COMPANY => 'Per company',
	LIC_TYPE_FREEWARE => 'Freeware'
);

?>

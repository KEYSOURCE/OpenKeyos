<?php

define ('KAMS_OBJ_CLASS_GENERIC', 0);
define ('KAMS_OBJ_CLASS_COMPUTER', 1);
define ('KAMS_OBJ_CLASS_PERIPHERAL', 2);
define ('KAMS_OBJ_CLASS_AD_PRINTER', 3);

/** Names of the KAMS objects classes */
$GLOBALS['KAMS_OBJ_CLASSES'] = array (
	KAMS_OBJ_CLASS_GENERIC => 'Asset',
	KAMS_OBJ_CLASS_COMPUTER => 'Computer',
	KAMS_OBJ_CLASS_PERIPHERAL => 'Peripheral',
	KAMS_OBJ_CLASS_AD_PRINTER => 'AD_Printer'
);
?>

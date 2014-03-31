<?php

/**
* Constants relative to notifications - French
* 
* @package
* @subpackage Constants_notification
*
*/

$GLOBALS['ALERT_NAMES'] = array(
	ALERT_NONE => 'Information',
	ALERT_NOTICE => 'Notice',
	ALERT_WARNING => 'Avertissement',
	ALERT_ERROR => 'Erreur',
	ALERT_CRITICAL => 'Alerte Rouge'
);

/** Classes of objects associtated with notifications */
$GLOBALS['NOTIF_OBJ_CLASSES'] = array (
	NOTIF_OBJ_CLASS_CUSTOMER => 'Account manager',
	NOTIF_OBJ_CLASS_KRIFS => 'Ticket',
	NOTIF_OBJ_CLASS_COMPUTER => 'Ordinateur',
	NOTIF_OBJ_CLASS_INTERNET => 'Internet',
	NOTIF_OBJ_CLASS_INTERNET_CONTRACT => 'Internet Contrat',
	NOTIF_OBJ_CLASS_SOFTWARE => 'Licence de Logiciel',
);

$GLOBALS['NOTIF_OBJ_CLASSES_SHORT'] = array (
	NOTIF_OBJ_CLASS_CUSTOMER => 'Client',
	NOTIF_OBJ_CLASS_KRIFS => 'Ticket',
	NOTIF_OBJ_CLASS_COMPUTER => 'Ordi.',
	NOTIF_OBJ_CLASS_INTERNET => 'Internet.',
	NOTIF_OBJ_CLASS_INTERNET_CONTRACT => 'Internet Contract',
	NOTIF_OBJ_CLASS_SOFTWARE => 'Logiciel lic.',
);

/** The text for the various defined notification codes */
$GLOBALS['NOTIF_CODES_TEXTS'] = array (
	NOTIF_CODE_MISSED_HEARTBEATS => 'Battements de coeur perdus',
	NOTIF_CODE_NEW_COMPUTER => 'Nouvel ordinateur, pas de profil',
	NOTIF_CODE_NEW_TICKET => 'Nouveau ticket',
	NOTIF_CODE_UPDATED_TICKET => 'Ticket actualisé',
	NOTIF_CODE_ESCALATED_TICKET => 'Ticket prioritaire',
	NOTIF_CODE_OVER_DEADLINE_TICKET => 'Le ticket a dépassé la limite de temps',
	NOTIF_CODE_CLOSED_TICKET => 'Ticket fermé',
	NOTIF_CODE_INTERNET_DOWN => 'Plus de connection Internet',
	NOTIF_CODE_INTERNET_CONTRACT_EXPIRES => 'Internet contrat expire',
	NOTIF_CODE_LICENSES_EXCEEDED => 'Licences de logiciel excédé',
	NOTIF_CODE_MAC_CONFLICT => 'MAC address conflict',
	NOTIF_CODE_NAME_CONFLICT => 'Computer name conflict',
	NOTIF_CODE_NAME_SWINGERS => 'Computer name swinger',
	NOTIF_CODE_REPORTING_IP_CONFLICT => 'Reporting IP conflict',
	NOTIF_CODE_UNMATCHED_DISCOVERIES => 'Unmatched discoveries',
	NOTIF_CODE_LATE_DISCOVERY => 'Discovery without Keyos contact'
);

?>
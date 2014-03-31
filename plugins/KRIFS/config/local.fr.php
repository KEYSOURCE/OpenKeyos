<?php
/**
* KRIFS specific constants - French
* 
* Various constants to be used across the ticket tracking system
*
* @package
* @subpackage Constants_KRIFS
*/

/** The names for the KRIFS ticket sources */
$GLOBALS ['TICKET_SOURCES'] = array (
	TICKET_SOURCE_SITE => 'Site Web',
	TICKET_SOURCE_PHONE => 'Téléphone',
	TICKET_SOURCE_FAX => 'Fax',
	TICKET_SOURCE_MAIL => 'E-mail',
	TICKET_SOURCE_MEETING => 'Réunion',
	TICKET_SOURCE_KAWACS => 'KAWACS',
	TICKET_SOURCE_OTHER => 'Autre',
	TICKET_SOURCE_KRIFS1 => 'Krifs 1'
);

/** The names of the priority levels */
$GLOBALS ['TICKET_PRIORITIES'] = array (
	TICKET_PRIORITY_LOW => 'Basse',
	TICKET_PRIORITY_NORMAL => 'Normale',
	TICKET_PRIORITY_HIGH => 'Haute'
);

$GLOBALS['TICKET_OBJECT_CLASSES'] = array (
	TICKET_OBJ_CLASS_COMPUTER => 'Ordinateur',
        TICKET_OBJ_CLASS_PERIPHERAL => 'Périphérique',
	TICKET_OBJ_CLASS_MONITORED_IP => 'Moniteur IP',
	TICKET_OBJ_CLASS_USER => 'Utilisateur',
	TICKET_OBJ_CLASS_AD_COMPUTER => 'Ordinateur AD',
	TICKET_OBJ_CLASS_AD_USER => 'Utilisateur AD',
	TICKET_OBJ_CLASS_AD_GROUP => 'Groupe AD',
	TICKET_OBJ_CLASS_AD_PRINTER => 'Printer AD',
	TICKET_OBJ_CLASS_INTERNET_CONTRACT => 'Internet contrat',
        TICKET_OBJ_CLASS_REMOVED_COMPUTER => 'Ordinateur supprimés',
	TICKET_OBJ_CLASS_REMOVED_PERIPHERAL => 'Périphériques supprimés'
);

$GLOBALS['INTERVENTION_STATS'] = array (
	INTERVENTION_STAT_OPEN => 'Ouvert', 
	INTERVENTION_STAT_CLOSED => 'Fermé',
	INTERVENTION_STAT_APPROVED => 'Approuvé',
	INTERVENTION_STAT_PENDING_CENTRALIZE => 'En attente de centralisation',
	INTERVENTION_STAT_CENTRALIZED => 'Centralisé'
);

$GLOBALS['TIMESHEET_STATS'] = array (
	TIMESHEET_STAT_NONE => 'Pas de timesheet',
	TIMESHEET_STAT_OPEN => 'Ouvert',
	TIMESHEET_STAT_CLOSED => 'Fermé',
	TIMESHEET_STAT_PENDING_CENTRALIZE => 'En attente de centralisation',
	TIMESHEET_STAT_CENTRALIZED => 'Centralisé'
);

$GLOBALS['ORDER_STATS'] = array (
	ORDER_STAT_OPEN => 'Ouvert',
	ORDER_STAT_CLOSED => 'Fermé', 
	ORDER_STAT_COMPLETED => 'Terminé'
);

$GLOBALS['PRICE_TYPES'] = array (
	PRICE_TYPE_HOURLY => 'Par heure',
	PRICE_TYPE_FIXED => 'Fixe'
);

$GLOBALS['CONTRACT_TYPES'] = array (
	CONTRACT_BASIC => 'Basic',
	CONTRACT_KEYPRO => 'KeyPro',
	CONTRACT_TOTAL_CARE => 'Total Care',
	CONTRACT_ALL => '[Tous les clients]'
);

$GLOBALS['CUST_SUBTYPES'] = array (
	CUST_SUBTYPE_BASIC => 'Basic',
	CUST_SUBTYPE_KEYPRO => 'Key Pro',
	CUST_SUBTYPE_GLOBALPRO => 'Global Pro',
	CUST_SUBTYPE_REMOTEADMIN => 'Remote Admin',
	CUST_SUBTYPE_TC => 'Total Care'
);

$GLOBALS['CUST_PRICETYPES'] = array (
	CUST_PRICETYPE_BASIC => 'Basic',
	CUST_PRICETYPE_KEYPRO => 'Key Pro',
	CUST_PRICETYPE_TC1 => 'TC 1',
	CUST_PRICETYPE_TC2 => 'TC 2',
	CUST_PRICETYPE_TC3 => 'TC 3'
);

/** Special action types */
$GLOBALS ['ACTYPE_SPECIALS'] = array (
	ACTYPE_SPECIAL_TRAVEL => 'Coûts de voyage'
);


/** Special detail types for linking timesheets details to tickets details */
$GLOBALS ['TS_SPECIALS'] = array (
	TS_SPECIAL_TRAVEL_TO => 'Voyager vers',
	TS_SPECIAL_TRAVEL_FROM => 'Voyager depuis'
);

?>
<?php

/**
* Constants settings - French

* Various constants to be used across the projects
*
* @package
* @subpackage Constants
*/

//require_once(dirname(__FILE__).'/local.php');
//require_once(dirname(__FILE__).'/local.php');
//require_once(dirname(__FILE__).'/const_notification.fr.php');
//require_once(dirname(__FILE__).'/const_krifs.fr.php');
//require_once(dirname(__FILE__).'/local.php');
//require_once(dirname(__FILE__).'/local.php');

$GLOBALS['USER_TYPES'] = array(
	USER_TYPE_KEYSOURCE => 'Utilisateur Keysource',
	USER_TYPE_CUSTOMER => 'Utilisateur Client',
	USER_TYPE_CUSTOMER_SHOP => 'Utilisateur Boutique',
	USER_TYPE_KEYSOURCE_GROUP => 'Groupe Keysource',
	USER_TYPE_GROUP => 'Groupe'
);

/** Users statuses **/
$GLOBALS['USER_STATUSES'] = array (
	USER_STATUS_INACTIVE => 'Pas actif',
	USER_STATUS_ACTIVE => 'Actif',
	USER_STATUS_AWAY_BUSINESS => 'Absent pour affaires',
	USER_STATUS_AWAY_HOLIDAY => 'Parti en vacances'
);

/** ACL role types */
$GLOBALS['ACL_ROLE_TYPES'] = array (
	ACL_ROLE_TYPE_KEYSOURCE => 'R�le Keysource ACL',
	ACL_ROLE_TYPE_CUSTOMER => 'R�le Client ACL'
);

/** Phone types */
$GLOBALS['PHONE_TYPES'] = array(
	PHONE_TYPE_MOBILE => 'Mobile',
	PHONE_TYPE_OFFICE => 'Bureau',
	PHONE_TYPE_HOME => 'Maison/Priv�'
);

$GLOBALS ['DAY_NAMES'] = array (
	DAY_MON => 'Lundi',
	DAY_TUE => 'Mardi',
	DAY_WED => 'Mercredi',
	DAY_THU => 'Jeudi',
	DAY_FRI => 'Vendredi',
	DAY_SAT => 'Samedi',
	DAY_SUN => 'Dimanche'
);

$GLOBALS['PHOTO_OBJECT_CLASSES'] = array (
	PHOTO_OBJECT_CLASS_COMPUTER => 'Ordinateur',
	PHOTO_OBJECT_CLASS_PERIPHERAL => 'P�riph�rique',
	PHOTO_OBJECT_CLASS_LOCATION => 'Emplacement',
);

$GLOBALS['LOCATION_FIXED_TYPES'] = array (
	LOCATION_FIXED_TYPE_COUNTRY => 'Pays',
	LOCATION_FIXED_TYPE_PROVINCE => 'Province',
	LOCATION_FIXED_TYPE_TOWN => 'Ville'
);

$GLOBALS['LOCATION_TYPES'] = array (
	LOCATION_TYPE_COMPLEX => 'Complexe',
	LOCATION_TYPE_BUILDING => 'Immeuble',
	LOCATION_TYPE_FLOOR => 'Etage',
	LOCATION_TYPE_ROOM => 'Chambre'
);

/** Array with location types which can be assigned to top-level locations */
$GLOBALS['LOCATION_TYPES_TOP'] = array (
	LOCATION_TYPE_COMPLEX => 'Complexe',
	LOCATION_TYPE_BUILDING => 'Immeuble',
);

?>
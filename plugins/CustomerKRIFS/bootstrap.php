<?php

//constants and stuff - anything that gets this going
define('RARE_INCIDENT', 1);
define('RECURRING_INCIDENT', 2);
define('REGULAR_INCIDENT', 3);

$GLOBALS['INCIDENT_OCCURENCE'] = array(
    LANG_FR => array(
        RARE_INCIDENT => 'Incident rare',
        RECURRING_INCIDENT => 'Incident r&eacute;current (2 &agrave; 5 fois)',
        REGULAR_INCIDENT => 'Incident r&eacute;gulier (plus de 5 fois)'
    ),
    LANG_EN => array(
        RARE_INCIDENT => "Rare incident",
        RECURRING_INCIDENT => "Recurrent incident (2 to 5 times)",
        REGULAR_INCIDENT => "Regular incident (more than 5 times)"
    )
);

define('TBL_CUSTOMERS_SATISFACTION', 'customers_satisfaction');

?>

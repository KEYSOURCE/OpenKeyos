<?php

/**
* Crontab actions - hourly tasks
*
* This file will be run hourly and will perform routines such as:
* - Doing a quick update of the computers items logs (note that a full update still needs to be run in the daily tasks)
* 
* @package
* @subpackage Crontab
*
*/

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Computer');
class_load ('Peripheral');
class_load ('SoftwareLicense');
class_load ('NotificationRecipient');
class_load ('CustomerInternetContract');


// Check for expiring Internet contracts
CustomerInternetContract::check_expirations ();

// Clear all notification recipients which are not linked to valid notifications
NotificationRecipient::clear_orphan_recipients ();

// Check for exceeded software licenses
SoftwareLicense::check_licenses_notifications ();

// Process and cleanup the computers items logs
Computer::update_monthly_logs (true);

// Process and cleanup the peripherals items logs
Peripheral::update_monthly_logs (true);

?>
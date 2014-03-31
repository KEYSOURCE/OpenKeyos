<?php

/**
* Tickets crontab actions, to be run every hour.
*
* Tasks performed:
*   - check tickets SLA times, meaning the customer-specific
*     times after which new tickets are automatically escalated.
*   - check escalation limits based on statuses
*
* @package
* @subpackage Crontab
*
*/

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Ticket');

Ticket::check_sla_times ();
Ticket::check_escalation_conditions ();

?>
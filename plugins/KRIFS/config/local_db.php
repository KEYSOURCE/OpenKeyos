<?php

/** Table for storing KRIFS tickets */
define ('TBL_TICKETS', 'tickets');
/** Table for storing KRIFS tickets details (comments) */
define ('TBL_TICKETS_DETAILS', 'tickets_details');
/** Table for storing KRIFS attachments */
define ('TBL_TICKETS_ATTACHMENTS', 'tickets_attachments');
/** Table for storing KRIFS tickets objects (e.g. computers related to a ticket) */
define ('TBL_TICKETS_OBJECTS', 'tickets_objects');
/** Table with the names of ticket statuses */
define ('TBL_TICKETS_STATUSES', 'tickets_statuses');
/** Table with the names of ticket types */
define ('TBL_TICKETS_TYPES', 'tickets_types');
/** Table with the CC recipients for tickets */
define ('TBL_TICKETS_CC', 'tickets_cc');
/** Table with the list of users who will receive escalation notifications */
define ('TBL_TICKETS_ESCALATION_RECIPIENTS', 'tickets_escalation_recipients');
/** Table with access logs for tickets - it will be suffixed with _Y_m */
define ('TBL_TICKETS_ACCESS', 'tickets_access');
/** Table with scheduled tasks for tickets */
define ('TBL_TASKS', 'tasks');
/** Table with attendees for the scheduled tasks */
define ('TBL_TASKS_ATTENDEES', 'tasks_attendees');


/** Table storing intervention report headers */
define ('TBL_INTERVENTION_REPORTS', 'intervention_reports');
/** Table storing intervention reports detail lines*/
define ('TBL_INTERVENTION_REPORTS_DETAILS', 'intervention_reports_details');
/** Table storing the ticket detail IDs from which each invoicing line in a intervention report comes from */
define ('TBL_INTERVENTION_REPORTS_DETAILS_IDS', 'intervention_reports_details_ids');
/** Table storing the history of intervention report exports to Mercator */
define ('TBL_INTERVENTIONS_EXPORTS', 'interventions_exports');
/** Table storing the list of actions (confirmation requests) received from the ERP system for intervention reports exports */
define ('TBL_INTERVENTIONS_EXPORTS_ACTIONS', 'interventions_exports_actions');
/** Table storing the list of intervention report IDs included in each export */
define ('TBL_INTERVENTIONS_EXPORTS_IDS', 'interventions_exports_ids');
/** Table storing the definitions of intervention locations */
define ('TBL_INTERVENTION_LOCATIONS', 'intervention_locations');

/** Table storing intervention timesheet headers */
define ('TBL_TIMESHEETS', 'timesheets');
/** Table storing intervention timesheet details */
define ('TBL_TIMESHEETS_DETAILS', 'timesheets_details');
/** Table storing the history of timesheets exports to ERP */
define ('TBL_TIMESHEETS_EXPORTS', 'timesheets_exports');
/** Table storing the list of actions (confirmation requests) received from the ERP system for timesheets exports */
define ('TBL_TIMESHEETS_EXPORTS_ACTIONS', 'timesheets_exports_actions');
/** Table storing the list of timesheets IDs included in each export */
define ('TBL_TIMESHEETS_EXPORTS_IDS', 'timesheets_exports_ids');

/** Table storing the list of users and tickets that they are working on now */
define ('TBL_NOW_WORKING', 'now_working');

/** Table with the saved searches */
define ('TBL_KRIFS_SAVED_SEARCHES', 'krifs_saved_searches');
/** Table with the 'Favourites' saved searches */
define ('TBL_KRIFS_SAVED_SEARCHES_FAVORITES', 'krifs_saved_searches_favorites');

/** Table for storing activities information */
define ('TBL_ACTIVITIES', 'activities');
/** Table for storing categories of activities */
define ('TBL_ACTIVITIES_CATEGORIES', 'activities_categories');
/** Table storing the user-specific activity code for each activity */
define ('TBL_ACTIVITIES_USERS', 'activities_users');
/** Table for storing action types information */
define ('TBL_ACTION_TYPES', 'action_types');
/** Table for storing action types categories */
define ('TBL_ACTION_TYPES_CATEGORIES', 'action_types_categories');

/** Table for storing customer orders */
define ('TBL_CUSTOMER_ORDERS', 'customer_orders');


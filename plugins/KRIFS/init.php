<?php

$plugin_init = array(
    'MODELS' => array(
        'Ticket' => dirname(__FILE__).'/model/ticket.php', 
        'TicketAttachment' => dirname(__FILE__).'/model/ticket_attachment.php',
        'TicketDetail' => dirname(__FILE__).'/model/ticket_detail.php',
        'ActionType' => dirname(__FILE__).'/model/action_type.php',
        'ActionTypeCategory' => dirname(__FILE__).'/model/action_type_category.php',
        'Activity' => dirname(__FILE__).'/model/activity.php',
        'ActivityCategory' => dirname(__FILE__).'/model/activity_category.php',
        'ImapSettings' => dirname(__FILE__).'/model/imap_settings.php',
        'InterventionLocation' => dirname(__FILE__).'/model/intervention_location.php',
        'InterventionReport' => dirname(__FILE__).'/model/intervention_report.php',
        'InterventionReportDetail' => dirname(__FILE__).'/model/intervention_report_detail.php',       
        'KrifsSavedSearch' => dirname(__FILE__).'/model/krifs_saved_search.php',
        'KrifsServer' => dirname(__FILE__).'/model/krifs_server.php',
        'Task' => dirname(__FILE__).'/model/task.php',
        'Timesheet' => dirname(__FILE__).'/model/timesheet.php',
        'TimesheetDetail' => dirname(__FILE__).'/model/timesheet_detail.php',
        'TimesheetsExport' => dirname(__FILE__).'/model/timesheets_export.php',
        'WorkMarker' => dirname(__FILE__).'/model/work_marker.php',
    ),
    'CONTROLLERS' => array(
        'krifs' => array(    
            'class' => 'KrifsController',
            'friendly_name' => 'KrifsController',
            'file'  => dirname(__FILE__).'/controller/krifs_controller.php',
            'default_method' => 'manage_tickets',
            'requires_acl' => True
        ),
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        'KrifsController' => dirname(__FILE__).'/strings/krifs.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'krifs',
        'display_name' => 'KRIFS',
        'uri' => '/krifs'
    ),
    'MENU' => array(      
        'manage_ticktes' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'manage_tickets',
            'display_name' => 'Tickets',
            'uri' => '/krifs/manage_tickets',
            'add_separator_before' => FALSE
        ),
        'tickets_stats' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'tickets_stats',
            'display_name' => 'Tickets Stats &#0187;',
            'uri' => '/krifs/tickets_stats',
            'add_separator_before' => FALSE
        ),
        'activity_stats' => array(
            'module' => 'krifs',
            'submenu_of' => 'tickets_stats',
            'name' => 'activity_stats',
            'display_name' => 'Activity Stats',
            'uri' => '/krifs/tickets_stats',
            'add_separator_before' => FALSE
        ),
        'work_time_stats' => array(        
            'module' => 'krifs',
            'submenu_of' => 'tickets_stats',
            'name' => 'work_time_stats',
            'display_name' => 'Work Time Stats',
            'uri' => '/krifs/work_time_stats',
            'add_separator_before' => FALSE
        ),
        'intervention_reports' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'intervention_reports',
            'display_name' => 'Intervention Reports &#0187;',
            'uri' => '/krifs/manage_interventions',
            'add_separator_before' => FALSE
        ),
        'manage_interventions' => array(        
            'module' => 'krifs',
            'submenu_of' => 'intervention_reports',
            'name' => 'manage_interventions',
            'display_name' => 'Intervention Reports',
            'uri' => '/krifs/manage_interventions',
            'add_separator_before' => FALSE
        ),
        'interventions_print_console' => array(        
            'module' => 'krifs',
            'submenu_of' => 'intervention_reports',
            'name' => 'interventions_print_console',
            'display_name' => 'IR print console',
            'uri' => '/krifs/interventions_print_console',
            'add_separator_before' => FALSE
        ),
        'interventions_approval_console' => array(        
            'module' => 'krifs',
            'submenu_of' => 'intervention_reports',
            'name' => 'interventions_approval_console',
            'display_name' => 'IR approval console',
            'uri' => '/krifs/intervention_approval_console',
            'add_separator_before' => FALSE
        ),
        'manage_timesheets' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'manage_timesheets',
            'display_name' => 'Timesheets',
            'uri' => '/krifs/manage_timesheets',
            'add_separator_before' => FALSE
        ),
        'manage_timesheets_extended' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'manage_timesheets_extended',
            'display_name' => 'Timesheets reports',
            'uri' => '/krifs/manage_timesheets_extended',
            'add_separator_before' => FALSE
        ),        
        'manage_tasks' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'manage_tasks',
            'display_name' => 'Tasks Scheduling',
            'uri' => '/krifs/manage_tasks',
            'add_separator_before' => TRUE
        ),
        'tbs_tickets' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'tbs_tickets',
            'display_name' => 'TBS tickets',
            'uri' => '/krifs/tbs_tickets',
            'add_separator_before' => FALSE
        ),
        'ticket_add' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'ticketadd',
            'display_name' => 'New Ticket',
            'uri' => '/krifs/ticket_add',
            'add_separator_before' => TRUE
        ),
        'intervention_add' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'intervention_add',
            'display_name' => 'New Intervention Report',
            'uri' => '/krifs/intervention_add',
            'add_separator_before' => FALSE
        ),
        'tickets_from_emails' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'tickets_from_emails',
            'display_name' => 'Tickets from support emails',
            'uri' => '/krifs/tickets_from_emails',
            'add_separator_before' => FALSE
        ),
        'manage_saved_searches' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'manage_saved_searches',
            'display_name' => 'Saved searched',
            'uri' => '/krifs/manage_save_searches',
            'add_separator_before' => TRUE
        ),
        'report_krifs_outstanding_tickets' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'report_krifs_outstanding_tickets',
            'display_name' => 'Tickets reports',
            'uri' => '/krifs/report_krifs_outstanding_tickets',
            'add_separator_before' => TRUE
        ),
        'now_working' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'now_working',
            'display_name' => 'Who is doing what',
            'uri' => '/krifs/now_working',
            'add_separator_before' => FALSE
        )
        
    )
);
return $plugin_init;

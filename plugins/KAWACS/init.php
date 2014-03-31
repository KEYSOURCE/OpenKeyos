<?php

$plugin_init = array(
    'MODELS' => array(
        'Alert' => dirname(__FILE__).'/model/alert.php',   
        'AlertCondition' => dirname(__FILE__).'/model/alert_condition.php',
        'CustomerAllowedIP' => dirname(__FILE__).'/model/customer_allowed_ip.php',
        'EventLogRequested' => dirname(__FILE__).'/model/event_log_requested.php',
        'KawacsLogs' => dirname(__FILE__).'/model/kawacs_logs.php',
        'KawacsServer' => dirname(__FILE__).'/model/kawacs_server.php',
        'mRemoteConnection' => dirname(__FILE__).'/model/mRemoteConnection.php',
        'mRemoteConnectionInfo' => dirname(__FILE__).'/model/mRemoteConnectionInfo.php',
        'MonitorItem' => dirname(__FILE__).'/model/monitor_item.php',
        'MonitorProfile' => dirname(__FILE__).'/model/monitor_profile.php',
        'MonitorProfileItem' => dirname (__FILE__).'/model/monitor_profile_item.php',
        'MonitorProfilePeriph' => dirname (__FILE__).'/model/monitor_profile_periph.php',
        'MonitorProfileItemPeriph' => dirname (__FILE__).'/model/monitor_profile_item_periph.php',
        'MonitoredIP' => dirname (__FILE__).'/model/monitored_ip.php',
        'Peripheral' => dirname (__FILE__).'/model/peripheral.php',
        'PeripheralClass' => dirname (__FILE__).'/model/peripheral_class.php',
        'PeripheralClassField' => dirname (__FILE__).'/model/peripheral_class_field.php',
        'RemovedPeripheral' => dirname (__FILE__).'/model/removed_peripheral.php',
        'Rbl' => dirname (__FILE__).'/model/rbl.php',
        'Role' => dirname (__FILE__).'/model/role.php',
        'ValidDupName' => dirname (__FILE__).'/model/valid_dup_name.php',
    ),
    'CONTROLLERS' => array(
        'kawacs' => array(    
            'class' => 'KawacsController',
            'friendly_name' => 'KawacsController',
            'file'  => dirname(__FILE__).'/controller/kawacs_controller.php',
            'default_method' => 'manage_computers',
            'requires_acl' => True
        )
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(        
        'KawacsController' => dirname(__FILE__).'/strings/kawacs.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'kawacs',
        'display_name' => 'KAWACS',
        'uri' => '/kawacs'
    ),
    'MENU' => array(      
        'manage_computers' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_computers',
            'display_name' => 'Computers',
            'uri' => '/kawacs/manage_computers',
            'add_separator_before' => FALSE
        ),
        'kawacs_console' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'kawacs_console',
            'display_name' => 'KAWACS Console',
            'uri' => '/kawacs/kawacs_console',
            'add_separator_before' => FALSE
        ),
        'customer_computers' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'customer_computers',
            'display_name' => 'Customer computers',
            'uri' => '/kawacs/customers_computer_count',
            'add_separator_before' => FALSE
        ),
        'menu_dashboards' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'menu_dashboards',
            'display_name' => 'KAWACS Dashboards &#0187;',
            'uri' => '/kawacs/kawacs_backup_dashboard',
            'add_separator_before' => FALSE
        ),
        'kawacs_backup_dashboard' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'menu_dashboards',
            'name' => 'kawacs_backup_dashboard',
            'display_name' => 'Backup statuses dashboard',
            'uri' => '/kawacs/kawacs_backup_dashboard',
            'add_separator_before' => FALSE
        ),
         'kawacs_antivirus_dashboard' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'menu_dashboards',
            'name' => 'kawacs_antivirus_dashboard',
            'display_name' => 'Antivirus statuses dashboard',
            'uri' => '/kawacs/kawacs_antivirus_dashboard',
            'add_separator_before' => FALSE
        ),
        'kawacs_inventory_dashboard' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'menu_dashboards',
            'name' => 'kawacs_inventory_dashboard',
            'display_name' => 'Inventory statuses dashboard',
            'uri' => '/kawacs/kawacs_inventory_dashboard',
            'add_separator_before' => FALSE
        ),
         'agent_deployer' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'agent_deployer',
            'display_name' => 'Generate KawacsAgent Deploy script',
            'uri' => '/kawacs/create_kawacs_agent_deployer',
            'add_separator_before' => TRUE
        ),
         'manage_notifications' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_notifications',
            'display_name' => 'Notification Status',
            'uri' => '/kawacs/manage_notifications',
            'add_separator_before' => TRUE
        ),
         'check_rbl' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'check_rbl',
            'display_name' => 'RBL Statuses',
            'uri' => '/kawacs/check_rbl_listed_servers',
            'add_separator_before' => FALSE
        ),
         'manage_mremote' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_mremote',
            'display_name' => 'Generate mRemote file',
            'uri' => '/kawacs/manage_mremote_connections',
            'add_separator_before' => FALSE
        ),
         'computer_add' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'computer_add',
            'display_name' => 'Add Computer Manually',
            'uri' => '/kawacs/computer_add',
            'add_separator_before' => FALSE
        ),
         'manage_peripherals' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_peripherals',
            'display_name' => 'Peripherals',
            'uri' => '/kawacs/manage_peripherals',
            'add_separator_before' => FALSE
        ),
         'manage_blackouts' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_blackouts',
            'display_name' => 'Blackouts &amp; Ignored Computers',
            'uri' => '/kawacs/manage_blackouts',
            'add_separator_before' => FALSE
        ),                 
        'manage_monitored_ips' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_monitored_ips',
            'display_name' => 'Internet Monitoring',
            'uri' => '/kawacs/manage_monitored_ips',
            'add_separator_before' => TRUE
        ),
         'manage_profiles' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_profiles',
            'display_name' => 'Monitor Profiles',
            'uri' => '/kawacs/manage_profiles',
            'add_separator_before' => FALSE
        ),
         'manage_alerts' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_alerts',
            'display_name' => 'Manage Alerts',
            'uri' => '/kawacs/manage_alerts',
            'add_separator_before' => FALSE
        ),
         'manage_monitor_items' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_monitor_items',
            'display_name' => 'Monitor Items',
            'uri' => '/kawacs/manage_monitor_items',
            'add_separator_before' => FALSE
        ),
        'manage_roles' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_roles',
            'display_name' => 'Computer Roles',
            'uri' => '/kawacs/manage_roles',
            'add_separator_before' => FALSE
        ),
        'manage_peripherals_classes' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_peripherals_classes',
            'display_name' => 'Peripherals Classes',
            'uri' => '/kawacs/manage_peripherals_classes',
            'add_separator_before' => FALSE
        ),        
         'manage_kawacs_updates' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_kawacs_updates',
            'display_name' => 'Kawacs Agent Updates',
            'uri' => '/kawacs/manage_kawacs_updates',
            'add_separator_before' => TRUE
        ),
         'manage_kawacs_linux_updates' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_kawacs_linux_updates',
            'display_name' => 'Kawacs Linux Updates',
            'uri' => '/kawacs/manage_kawacs_linux_updates',
            'add_separator_before' => FALSE
        ),
         'computers_agent_versions' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'computers_agent_versions',
            'display_name' => 'Agent Versions',
            'uri' => '/kawacs/computers_agent_versions',
            'add_separator_before' => FALSE
        ),
         'computers_linux_agent_versions' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'computers_linux_agent_versions',
            'display_name' => 'Linux Agent Versions',
            'uri' => '/kawacs/computers_linux_agent_versions',
            'add_separator_before' => FALSE
        ),
         'manage_quick_contacts' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_quick_contacts',
            'display_name' => 'Computer Quick Contacts',
            'uri' => '/kawacs/manage_quick_contacts',
            'add_separator_before' => FALSE
        ),
         'manage_oldest_contacts' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_oldest_contacts',
            'display_name' => 'Oldest Contacts',
            'uri' => '/kawacs/manage_oldest_contacts',
            'add_separator_before' => FALSE
        )        
    )
);

?>

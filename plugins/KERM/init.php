<?php

$plugin_init = array(
    'MODELS' => array(
        'AD_Computer' => dirname(__FILE__).'/model/ad_computer.php', 
        'AD_Group' => dirname(__FILE__).'/model/ad_group.php',
        'AD_Printer' => dirname(__FILE__).'/model/ad_printer.php',
        'AD_SNMP_Item' => dirname(__FILE__).'/model/ad_snmp_item.php',
        'AD_User' => dirname(__FILE__).'/model/ad_user.php',
        'KermADUser' => dirname(__FILE__).'/model/kerm_ad_user.php',
        'KermADGroup' => dirname(__FILE__).'/model/kerm_ad_group.php',
        'KermServer' => dirname(__FILE__).'/model/kerm_server.php',
        'MonitorItemAbstraction' => dirname(__FILE__).'/model/monitor_item_abstraction.php',
        'RemovedAD_Printer' => dirname(__FILE__).'/model/removed_ad_printer.php',
        'RemovedAD_SNMP_Item' => dirname(__FILE__).'/model/removed_ad_snmp_item.php'
    ),
    'CONTROLLERS' => array(
        'kerm' => array(    
            'class' => 'KermController',
            'friendly_name' => 'KermController',
            'file'  => dirname(__FILE__).'/controller/KermController.php',
            'default_method' => 'manage_ad_computers',
            'requires_acl' => True
        )
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(        
        'KermController' => dirname(__FILE__).'/strings/kerm.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'kerm',
        'display_name' => 'KERM',
        'uri' => '/kerm'
    ),
    'MENU' => array(      
        'manage_ad_computers' => array(        
            'module' => 'kerm',
            'submenu_of' => 'kerm',
            'name' => 'manage_ad_computers',
            'display_name' => 'AD Computers',
            'uri' => '/kerm/manage_ad_computers',
            'add_separator_before' => FALSE
        ),
        'manage_ad_users' => array(        
            'module' => 'kerm',
            'submenu_of' => 'kerm',
            'name' => 'manage_ad_users',
            'display_name' => 'AD Users',
            'uri' => '/kerm/manage_ad_users',
            'add_separator_before' => FALSE
        ),
        'manage_ad_printers' => array(        
            'module' => 'kerm',
            'submenu_of' => 'kerm',
            'name' => 'manage_ad_printers',
            'display_name' => 'AD Printers',
            'uri' => '/kerm/manage_ad_printers',
            'add_separator_before' => FALSE
        ),
        'logon_computers' => array(        
            'module' => 'kerm',
            'submenu_of' => 'kerm',
            'name' => 'logon_computers',
            'display_name' => 'Logon Computers',
            'uri' => '/kerm/logon_computers',
            'add_separator_before' => TRUE
        )
    )
);

?>

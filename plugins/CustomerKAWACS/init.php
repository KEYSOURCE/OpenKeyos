<?php

$plugin_init = array(  
    'MODELS' => array(        
    ),
    'CONTROLLERS' => array(
        'customer_kawacs' => array(    
            'class' => 'CustomerKawacsController',
            'friendly_name' => 'CustomerKawacs',
            'file'  => dirname(__FILE__).'/controller/customer_kawacs_controller.php',
            'default_method' => 'kawacs_backup_dashboard',
            'requires_acl' => True            
        )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(              
        'CustomerKawacsController' => dirname(__FILE__).'/strings/customer_kawacs.ini'
    ),
    'AVAILABLE_FOR_INTERFACE_MODE' => array(INTERFACE_MODE_CUSTOMER),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'customer_kawacs',
        'display_name' => 'KAWACS',
        'uri' => '/customer_kawacs',
        'requires_auth' => TRUE
    ),    
    'MENU' => array(      
        'backup_dashboard' => array(        
            'module' => 'customer_kawacs',
            'submenu_of' => 'customer_kawacs',
            'name' => 'customer_kawacs_backup_dashboard',
            'display_name' => 'Backup Dashboard',
            'uri' => '/customer_kawacs/kawacs_backup_dashboard',
            'add_separator_before' => FALSE
        ),
        'antivirus_dashboard' => array(        
            'module' => 'customer_kawacs',
            'submenu_of' => 'customer_kawacs',
            'name' => 'customer_kawacs_antivirus_dashboard',
            'display_name' => 'Antivirus Dashboard',
            'uri' => '/customer_kawacs/kawacs_antivirus_dashboard',
            'add_separator_before' => FALSE
        )        
    )
);

require_once(dirname(__FILE__).'/bootstrap.php');

?>

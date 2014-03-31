<?php

$plugin_init = array(  
    'MODELS' => array(        
    ),
    'CONTROLLERS' => array(
        'customer_kerm' => array(    
            'class' => 'CustomerKermController',
            'friendly_name' => 'CustomerKerm',
            'file'  => dirname(__FILE__).'/controller/customer_kerm_controller.php',
            'default_method' => 'manage_users',
            'requires_acl' => True            
        )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(              
        'CustomerKermController' => dirname(__FILE__).'/strings/customer_kerm.ini'
    ),
    'AVAILABLE_FOR_INTERFACE_MODE' => array(INTERFACE_MODE_CUSTOMER),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'customer_kerm',
        'display_name' => 'KERM',
        'uri' => '/customer_kerm',
        'requires_auth' => TRUE
    ),    
    'MENU' => array(      
        'manage_users' => array(        
            'module' => 'customer_kerm',
            'submenu_of' => 'customer_kerm',
            'name' => 'customer_kerm_manage_users',
            'display_name' => 'Manage Users',
            'uri' => '/customer_kerm/manage_users',
            'add_separator_before' => FALSE
        ),
        'add_user' => array(        
            'module' => 'customer_kerm',
            'submenu_of' => 'customer_kerm',
            'name' => 'customer_kerm_add_user',
            'display_name' => 'Add User',
            'uri' => '/customer_kerm/add_user',
            'add_separator_before' => FALSE
        )        
    )
);

require_once(dirname(__FILE__).'/bootstrap.php');

?>

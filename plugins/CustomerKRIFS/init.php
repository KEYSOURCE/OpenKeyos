<?php

$plugin_init = array(  
    'MODELS' => array( 
        'CustomerSatisfactionModel' => dirname(__FILE__).'/model/customer_satisfaction_model.php'
    ),
    'CONTROLLERS' => array(
        'customer_krifs' => array(    
            'class' => 'CustomerKrifsController',
            'friendly_name' => 'CustomerKrifs',
            'file'  => dirname(__FILE__).'/controller/customer_krifs_controller.php',
            'default_method' => 'manage_tickets',
            'requires_acl' => True            
        )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(              
        'CustomerKrifsController' => dirname(__FILE__).'/strings/customer_krifs.ini'
    ),
    'AVAILABLE_FOR_INTERFACE_MODE' => array(INTERFACE_MODE_CUSTOMER),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'customer_krifs',
        'display_name' => 'KRIFS',
        'uri' => '/customer_krifs',
        'requires_auth' => TRUE
    ),    
    'MENU' => array(      
        'manage_tickets' => array(        
            'module' => 'customer_krifs',
            'submenu_of' => 'customer_krifs',
            'name' => 'customer_krifs_manage_tickets',
            'display_name' => 'Tickets',
            'uri' => '/customer_krifs/manage_tickets',
            'add_separator_before' => FALSE
        ),
        'manage_interventions' => array(        
            'module' => 'customer_krifs',
            'submenu_of' => 'customer_krifs',
            'name' => 'customer_krifs_manage_interventions',
            'display_name' => 'Intervention Reports',
            'uri' => '/customer_krifs/manage_interventions',
            'add_separator_before' => FALSE
        )        
    )
);

require_once(dirname(__FILE__).'/bootstrap.php');

?>

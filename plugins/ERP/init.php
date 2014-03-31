<?php

$plugin_init = array(
    'REQUIRED_MODULES' => array('krifs'),
    'MODELS' => array(
        //add the models here
        'InterventionsExport' => dirname (__FILE__).'/model/interventions_export.php',
        'ErpSync' => dirname (__FILE__).'/model/erp_sync.php',
        'ApisoftSync' => dirname(__FILE__).'/model/apisoft_sync.php',
        'CustomerOrder' => dirname(__FILE__).'/model/customer_order.php',
    ),
    'CONTROLLERS' => array(
        'erp' => array(    
            'class' => 'ErpController',
            'friendly_name' => 'ErpController',
            'file'  => dirname(__FILE__).'/controller/erp_controller.php',
            'default_method' => 'manage_intervetions_exports',
            'requires_acl' => True
        )
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(        
        'ErpController' => dirname(__FILE__).'/strings/erp.ini'
    ),
    'IS_MAIN_MODULE' => FALSE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'erp',
        'display_name' => 'ERP',
        'uri' => '/erp'
    ),
    'MENU' => array(             
        'manage_customer_orders' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'manage_customer_orders',
            'display_name' => 'Customer Orders / Subscriptions',
            'uri' => '/erp/manage_customer_orders',
            'add_separator_before' => TRUE,
            'insert_after' => 'manage_timesheets_extended'
        ),
        'manage_interventions_exports' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'manage_interventions_exports',
            'display_name' => 'Interventions Exports',
            'uri' => '/erp/manage_interventions_exports',
            'add_separator_before' => FALSE,
            'insert_after' => 'manage_customer_orders'
        )        
    )
);

?>
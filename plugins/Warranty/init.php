<?php

$plugin_init = array(    
     'REQUIRED_MODULES' => array('kawacs', 'kerm', 'supplier'),
    'MODELS' => array(        
        'Warranty' => dirname(__FILE__).'/model/warranty.php',
        'WarrantyContractModel' => dirname(__FILE__).'/model/warranty_contract.php'
    ),    
    'CONTROLLERS' => array(
        'warranty' => array(    
            'class' => 'WarrantyController',
            'friendly_name' => 'Warranty',
            'file'  => dirname(__FILE__).'/controller/warranty_controller.php',
            'default_method' => 'manage_warranties',
            'requires_acl' => True
        )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        'WarrantyContractModel' => dirname(__FILE__).'/strings/warranty_contract.ini',
        'WarrantyController' => dirname(__FILE__).'/strings/warranty_contract.ini'
    ),
    'IS_MAIN_MODULE' => FALSE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'warranty_plugin',
        'display_name' => 'Warranty Plugin',
        'uri' => './?cl=warranty'
    ),
    'MENU' => array(      
         'manage_warranties' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'manage_warranties',
            'display_name' => 'Warranties',
            'uri' => get_link('warranty', 'manage_warranties'),//'warranty&op=manage_warranties',
            'add_separator_before' => TRUE,
             'insert_after' => 'manage_blackouts'   
        ),
         'warranties_eow' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'warranties_eow',
            'display_name' => 'Warranties - Out of Warranty',
            'uri' => get_link('warranty','warranties_eow'),
            'add_separator_before' => FALSE,
            'insert_after' => 'manage_warranties'     
        ),
        'check_online' => array(        
            'module' => 'kawacs',
            'submenu_of' => 'kawacs',
            'name' => 'warranty_check',
            'display_name' => 'Check online warranty',
            'uri' => get_link('warranty', 'online_warranty_check'),
            'add_separator_before' => FALSE,
            'insert_after' => 'warranties_eow'            
        )        
    )
);

//apend to menu


require_once(dirname(__FILE__).'/bootstrap.php');

?>

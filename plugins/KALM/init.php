<?php

$plugin_init = array(
    'MODELS' => array(
        'Software' => dirname(__FILE__).'/model/software.php',      
        'SoftwareLicense' => dirname(__FILE__).'/model/software_license.php',
        'SoftwareLicenseSN' => dirname(__FILE__).'/model/software_license_sn.php',
        'SoftwareLicenseFile' => dirname (__FILE__).'/model/software_license_file.php',
        'SoftwareMatch' => dirname(__FILE__).'/model/software_match.php'
    ),
    'CONTROLLERS' => array(
        'kalm' => array(    
            'class' => 'KalmController',
            'friendly_name' => 'KalmController',
            'file'  => dirname(__FILE__).'/controller/kalm_controller.php',
            'default_method' => 'manage_licenses',
            'requires_acl' => True
        )
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        //'AsteriskModel' => dirname(__FILE__).'/strings/strings.ini',
        'KlaraController' => dirname(__FILE__).'/strings/kalm.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'kalm',
        'display_name' => 'KALM',
        'uri' => '/kalm'
    ),
    'MENU' => array(      
        'manage_licenses' => array(        
            'module' => 'kalm',
            'submenu_of' => 'kalm',
            'name' => 'manage_licenses',
            'display_name' => 'Customer licenses',
            'uri' => '/kalm/manage_licenses',
            'add_separator_before' => FALSE
        ),
        'exceeded_licenses' => array(        
            'module' => 'kalm',
            'submenu_of' => 'kalm',
            'name' => 'exceeded_licenses',
            'display_name' => 'Exceeded licenses',
            'uri' => '/kalm/manage_licenses',
            'add_separator_before' => FALSE
        ),
        'manage_software' => array(        
            'module' => 'kalm',
            'submenu_of' => 'kalm',
            'name' => 'manage_software',
            'display_name' => 'Software packages',
            'uri' => '/kalm/manage_software',
            'add_separator_before' => TRUE
        ),
        'software_add' => array(        
            'module' => 'kalm',
            'submenu_of' => 'kalm',
            'name' => 'software_add',
            'display_name' => 'Add software package',
            'uri' => '/kalm/software_add',
            'add_separator_before' => FALSE
        )
    )
);

?>

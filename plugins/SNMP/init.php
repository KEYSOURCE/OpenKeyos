<?php
$plugin_init = array(    
    //'REQUIRED_MODULES' => array('config'),
    'MODELS' => array(        
        'Mib' => dirname(__FILE__).'/model/mib.php',
        'MibOid' => dirname(__FILE__).'/model/mib_oid.php',
    ),    
    'CONTROLLERS' => array(
        'snmp' => array(    
            'class' => 'SnmpController',
            'friendly_name' => 'Snmp',
            'file'  => dirname(__FILE__).'/controller/snmp_controller.php',
            'default_method' => 'manage_mibs',
            'requires_acl' => True
     )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        'SnmpController' => dirname(__FILE__).'/strings/snmp.ini',     
    ),
    'IS_MAIN_MODULE' => FALSE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'snmp',
        'display_name' => 'SNMP',
        'uri' => '/snmp'
    ),
    'MENU' => array(      
         'manage_mibs' => array(        
            'module' => 'config',
            'submenu_of' => 'config',
            'name' => 'manage_mibs',
            'display_name' => 'MIB\'s Management',
            'uri' => '/snmp/manage_mibs',
            'add_separator_before' => TRUE
             //'insert_after' => 'manage_saved_searches'   
        )
    )
);
?>

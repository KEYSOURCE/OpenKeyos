<?php
$plugin_init = array(        
    'MODELS' => array(        
        'Computer' => dirname(__FILE__) . '/model/computer.php',
        'ComputerBlackout' => dirname(__FILE__) . '/model/computer_blackout.php',
        'ComputerItem' => dirname(__FILE__) . '/model/computer_item.php',
        'ComputerNote' => dirname(__FILE__) . '/model/computer_note.php',
        'ComputerLogmein' => dirname(__FILE__) . '/model/computer_logmein.php',
        'ComputerReporting' => dirname(__FILE__) . '/model/computer_reporting.php',
    ),    
    'CONTROLLERS' => array(
        'computer' => array(    
            'class' => 'ComputerController',
            'friendly_name' => 'Computer',
            'file'  => dirname(__FILE__).'/controller/computer_controller.php',
            'default_method' => '',
            'requires_acl' => True
     )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        'SnmpController' => dirname(__FILE__).'/strings/computer.ini',     
    ),
    'IS_MAIN_MODULE' => FALSE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'computer',
        'display_name' => 'Computer',
        'uri' => '/computer'
    ),    
    'MENU' => array( )
 )
?>

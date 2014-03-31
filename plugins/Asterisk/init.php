<?php

$plugin_init = array(
    //register here any model classes needed by this plugin
    //model classes should all be in the model directory - 
    //each model class should extend PluginModel
    'MODELS' => array(
        'AsteriskModel' => dirname(__FILE__).'/model/AsteriskModel.php'
    ),
    //register here any controller classes - they all should be placed in the controllers dir
    //all controllers should extend PluginController
    //each method of the controller should have a pair '_submit' method, that will be used to handle the form redir from the view
    'CONTROLLERS' => array(
        'asterisk' => array(    
            'class' => 'AsteriskController',
            'friendly_name' => 'Asterisk',
            'file'  => dirname(__FILE__).'/controller/AsteriskController.php',
            'default_method' => 'detect',
            'requires_acl' => True
        )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        'AsteriskModel' => dirname(__FILE__).'/strings/strings.ini',
        'AsteriskController' => dirname(__FILE__).'/strings/strings.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'asterisk',
        'display_name' => 'Asterisk',
        'uri' => '/asterisk'
    ),
    'MENU' => array(      
        'detect' => array(        
            'module' => 'asterisk',
            'submenu_of' => 'asterisk',
            'name' => 'detect_caller',
            'display_name' => 'Detect Caller',
            'uri' => '/asterisk/detect',
            'add_separator_before' => FALSE
        )
    )
);

//apend to menu


//require_once(dirname(__FILE__).'/bootstrap.php');

?>
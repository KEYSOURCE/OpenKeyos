<?php

$plugin_init = array(
    //register here any model classes needed by this plugin
    //model classes should all be in the model directory - 
    //each model class should extend PluginModel
    'MODELS' => array(
        'TestPluginModel' => dirname(__FILE__).'/model/test_plugin_model.php'
    ),
    //register here any controller classes - they all should be placed in the controllers dir
    //all controllers should extend PluginController
    //each method of the controller should have a pair '_submit' method, that will be used to handle the form redir from the view
    'CONTROLLERS' => array(
        'test_plugin' => array(    
            'class' => 'TestPluginController',
            'friendly_name' => 'TestPlugin',
            'file'  => dirname(__FILE__).'/controller/test_plugin_controller.php',
            'default_method' => 'list_all',
            'requires_acl' => True            
        )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        'TestPluginModel' => dirname(__FILE__).'/strings/test_plugin_model.ini',
        'TestPluginController' => dirname(__FILE__).'/strings/test_plugin_model.ini'
    ),
    'AVAILABLE_FOR_INTERFACE_MODE' => array(INTERFACE_MODE_ADMINISTRATOR, INTERFACE_MODE_CUSTOMER, INTERFACE_MODE_CUSTOMER_ADMINISTRATOR),
    'IS_MAIN_MODULE' => FALSE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'test_plugin',
        'display_name' => 'Test Plugin',
        'uri' => './?cl=test_plugin',
        'requires_auth' => TRUE
    ),    
    'MENU' => array(      
        'list_all' => array(        
            'module' => 'test_plugin',
            'submenu_of' => 'test_plugin',
            'name' => 'test_plugin_list_all',
            'display_name' => 'Manage Test Plugin Items &#0187;',
            'uri' => './?cl=test_plugin&op=list_all',
            'add_separator_before' => FALSE
        ),
        'list_allx' => array(        
            'module' => 'test_plugin',
            'submenu_of' => 'test_plugin_list_all',
            'name' => 'test_plugin_list_allx',
            'display_name' => 'List all items',
            'uri' => './?cl=test_plugin&op=list_all',
            'add_separator_before' => FALSE
        ),
        'add_item' => array(        
            'module' => 'test_plugin',
            'submenu_of' => 'test_plugin_list_all',
            'name' => 'test_plugin_add_item',
            'display_name' => 'Add new item',
            'uri' => './?cl=test_plugin&op=add',
            'add_separator_before' => FALSE
        )
    )
);

//apend to menu


require_once(dirname(__FILE__).'/bootstrap.php');

?>

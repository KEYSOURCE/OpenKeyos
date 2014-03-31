<?php
$plugin_init = array(    
     'REQUIRED_MODULES' => array('krifs'),
    'MODELS' => array(        
        'KrifsMetrics' => dirname(__FILE__).'/model/krifs_metrics.php',     
    ),    
    'CONTROLLERS' => array(
        'krifs_metrics' => array(    
            'class' => 'KrifsMetricsController',
            'friendly_name' => 'KrifsMetrics',
            'file'  => dirname(__FILE__).'/controller/krifs_metrics_controller.php',
            'default_method' => 'metrics',
            'requires_acl' => True
        )
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        'KrifsMetricsController' => dirname(__FILE__).'/strings/krifs_metrics.ini',     
    ),
    'IS_MAIN_MODULE' => FALSE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'krifs_metrics',
        'display_name' => 'Krifs Metrics',
        'uri' => '/krifs_metrics'
    ),
    'MENU' => array(      
         'metrics' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'metrics',
            'display_name' => 'Krifs Metrics',
            'uri' => '/krifs_metrics/metrics',
            'add_separator_before' => TRUE,
             'insert_after' => 'manage_saved_searches'   
        ),
         'metrics_compare' => array(        
            'module' => 'krifs',
            'submenu_of' => 'krifs',
            'name' => 'metrics_compare',
            'display_name' => 'Krifs - Comparative metrics',
            'uri' => '/krifs_metrics/metrics_compare',
            'add_separator_before' => FALSE,
            'insert_after' => 'metrics'     
        )       
    )
);

?>
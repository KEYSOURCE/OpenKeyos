<?php

$plugin_init = array(
    'MODELS' => array(
        'PHPExcel' => KEYOS_EXTERNAL."/phpexcel/PHPExcel.php",
        'PHPWord' => KEYOS_EXTERNAL."/phpword/PHPWord.php",
        'Graph' => KEYOS_EXTERNAL.'/jpgraph/jpgraph.php',
        'GanttGraph' => KEYOS_EXTERNAL.'/jpgraph/jpgraph_gantt.php',
        'GanttBar' => KEYOS_EXTERNAL.'/jpgraph/jpgraph_gantt.php'
    ),
    'CONTROLLERS' => array(
        'customer_reports' => array(    
            'class' => 'CustomerReportsController',
            'friendly_name' => 'CustomerReports',
            'file'  => dirname(__FILE__).'/controller/customer_reports_controller.php',
            'default_method' => 'generate_report',
            'requires_acl' => True
        )
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        //'AsteriskModel' => dirname(__FILE__).'/strings/strings.ini',
        'CustomerReportsController' => dirname(__FILE__).'/strings/customer_reports.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'customer_reports',
        'display_name' => 'Customer Reports',
        'uri' => '/customer_reports'
    ),
    'MENU' => array(      
        'generate_report' => array(        
            'module' => 'customer_reports',
            'submenu_of' => 'customer_reports',
            'name' => 'generate_report',
            'display_name' => 'Generate Report',
            'uri' => '/customer_reports/generate_report',
            'add_separator_before' => FALSE
        )
    )
);

require_once(dirname(__FILE__).'/bootstrap.php');

?>
<?php
$plugin_init = array(        
    'MODELS' => array(        
        'Supplier' => dirname(__FILE__).'/model/supplier.php',
        'ServiceLevel' => dirname(__FILE__).'/model/service_level.php',
        'SupplierServicePackage' => dirname(__FILE__).'/model/supplier_service_package.php'
    ),    
    'CONTROLLERS' => array(
        
    ),

    //register here the views directory - smarty templates
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(       
    ),
    'IS_MAIN_MODULE' => FALSE,
    'MAIN_MENU_MODULE' => array(),
    'MENU' => array()
);
?>

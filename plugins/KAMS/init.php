<?php
$plugin_init = array(
    'MODELS' => array(
        'Asset' => __DIR__ . '/model/asset.php',
        'AssetCategory' => __DIR__ . '/model/asset_category.php',
        'AssetFinancialInfo' => __DIR__ . '/model/asset_financial_info.php',
        'Contract' => __DIR__ . '/model/contract.php',
        'ContractType' => __DIR__ . '/model/contract_type.php',
    ),
    'CONTROLLERS' => array(
        'kams' => array(
            'class' => 'KamsController',
            'friendly_name' => 'KAMS',
            'file' => __DIR__ . '/controller/kams_controller.php',
            'default_method' => 'manage_assets',
            'requries_acl' => True,
        ),
    ),
    'VIEWS' => __DIR__.'/views',
    'STRINGS' => array(
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'kams',
        'display_name' => 'KAMS',
        'uri' => '/kams',
    ),
    'MENU' => array(
        'manage_assets' => array(
            'module' => 'kams',
            'submenu_of' => 'kams',
            'name' => 'manage_assets',
            'display_name' => 'Assets',
            'uri' => '/kams/manage_assets',
            'add_separator_before' => FALSE
        ),
        'manage_contracts' => array(
            'module' => 'kams',
            'submenu_of' => 'kams',
            'name' => 'manage_contracts',
            'display_name' => 'Contracts',
            'uri' => '/kams/manage_contracts',
            'add_separator_before' => FALSE
        ),
    ),
);

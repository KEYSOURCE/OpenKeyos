<?php

$plugin_init = array(
    'MODELS' => array(
        'AccessPhone' => dirname(__FILE__).'/model/access_phone.php',
        'ComputerPassword' => dirname(__FILE__).'/model/computer_password.php',
        'ComputerRemoteService' => dirname(__FILE__).'/model/computer_remote_service.php',
        'CustomerInternetContract' => dirname(__FILE__).'/model/customer_internet_contract.php',
        'CustomerInternetContractAttachment' => dirname(__FILE__).'/model/customer_internet_contract_attachment.php',
        'PeripheralPlink' => dirname(__FILE__).'/model/peripheral_plink.php',
        'PeripheralPlinkService' => dirname(__FILE__).'/model/peripheral_plink_service.php',
        'Plink' => dirname(__FILE__).'/model/plink.php',
        'PlinkService' => dirname(__FILE__).'/model/plink_service.php',
        'Provider' => dirname(__FILE__).'/model/provider.php',
        'ProviderContact' => dirname(__FILE__).'/model/provider_contact.php',
        'ProviderContactPhone' => dirname(__FILE__).'/model/provider_contact_phone.php',
        'ProviderContract' => dirname(__FILE__).'/model/provider_contract.php',
        'RemoteAccess' => dirname(__FILE__).'/model/remote_access.php',
        'WebAccess' => dirname(__FILE__).'/model/web_access.php',
        'WebAccessResource' => dirname(__FILE__).'/model/web_access_resource.php'
    ),
    'CONTROLLERS' => array(
        'klara' => array(    
            'class' => 'KlaraController',
            'friendly_name' => 'KlaraController',
            'file'  => dirname(__FILE__).'/controller/klara_controller.php',
            'default_method' => 'manage_access',
            'requires_acl' => True
        )
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        //'AsteriskModel' => dirname(__FILE__).'/strings/strings.ini',
        'KlaraController' => dirname(__FILE__).'/strings/klara.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'klara',
        'display_name' => 'KLARA',
        'uri' => '/klara'
    ),
    'MENU' => array(      
        'manage_access' => array(        
            'module' => 'klara',
            'submenu_of' => 'klara',
            'name' => 'manage_access',
            'display_name' => 'Access information',
            'uri' => '/klara/manage_access',
            'add_separator_before' => FALSE
        ),
        'manage_access_phones' => array(        
            'module' => 'klara',
            'submenu_of' => 'klara',
            'name' => 'manage_access_phones',
            'display_name' => 'Access phones',
            'uri' => '/klara/manage_access_phones',
            'add_separator_before' => FALSE
        ),
        'manage_customer_internet_contracts' => array(        
            'module' => 'klara',
            'submenu_of' => 'klara',
            'name' => 'manage_customer_internet_contracts',
            'display_name' => 'Customer internet contracts',
            'uri' => '/klara/manage_customer_internet_contracts',
            'add_separator_before' => FALSE
        ),
        'manage_providers' => array(        
            'module' => 'klara',
            'submenu_of' => 'klara',
            'name' => 'manage_providers',
            'display_name' => 'Manage internet proviers',
            'uri' => '/klara/manage_providers',
            'add_separator_before' => TRUE
        )
    )
);

?>

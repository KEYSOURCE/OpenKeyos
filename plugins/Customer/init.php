<?php

$plugin_init = array(
    'MODELS' => array(
        'Customer' => dirname(__FILE__).'/model/customer.php',
        'CustomerComputerGroup' => dirname(__FILE__).'/model/computer_groups.php',
        'CustomerCCRecipient' => dirname(__FILE__).'/model/customer_cc_recipient.php',
        'CustomerComment' => dirname(__FILE__).'/model/customer_comment.php',
        'CustomerContact' => dirname(__FILE__).'/model/customer_contact.php',
        'CustomerContactPhone' => dirname(__FILE__).'/model/customer_contact_phone.php',
        'CustomerPhoto' => dirname(__FILE__).'/model/customer_photo.php',
        'CustomerTemplateStyle' => dirname(__FILE__).'/model/customer_template_style.php',
        'Location' => dirname(__FILE__).'/model/location.php',
        'LocationComment' => dirname(__FILE__).'/model/location_comment.php',
        'LocationFixed' => dirname(__FILE__).'/model/location_fixed.php',
        'Nagvis' => dirname(__FILE__).'/model/nagvis.php',
        'SupplierCustomer' => dirname(__FILE__).'/model/supplier_customer.php'
    ),
    'CONTROLLERS' => array(
        'customer' => array(    
            'class' => 'CustomerController',
            'friendly_name' => 'CustomerController',
            'file'  => dirname(__FILE__).'/controller/customer_controller.php',
            'default_method' => 'manage_customers',
            'requires_acl' => True
        )
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        //'AsteriskModel' => dirname(__FILE__).'/strings/strings.ini',
        'CustomerController' => dirname(__FILE__).'/strings/customer.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'customer',
        'display_name' => 'Customers',
        'uri' => '/customer'
    ),
    'MENU' => array(      
        'manage_customers' => array(        
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_customers',
            'display_name' => 'Customers',
            'uri' => '/customer/manage_customers',
            'add_separator_before' => FALSE
        ),
        'customers_suspended_alerts' => array(        
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'customers_suspended_alerts',
            'display_name' => 'Customers with suspended alerts',
            'uri' => '/customer/customers_suspended_alerts',
            'add_separator_before' => FALSE
        ),
        'manage_customers_contacts' => array(        
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_customers_contacts',
            'display_name' => 'Customers contacts',
            'uri' => '/customer/manage_customers_contacts',
            'add_separator_before' => FALSE
        ),
        'manage_customers_comments' => array(
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_customers_comments',
            'display_name' => 'Customers comments',
            'uri' => '/customer/manage_customers_comments',
            'add_separator_before' => FALSE
        ),
        'manage_customers_photos' => array(
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_customers_photos',
            'display_name' => 'Customers photos',
            'uri' => '/customer/manage_customers_photos',
            'add_separator_before' => FALSE
        ),
        'manage_locations' => array(
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_locations',
            'display_name' => 'Customers locations',
            'uri' => '/customer/manage_locations',
            'add_separator_before' => FALSE
        ),
        'manage_cc_recipients' => array(
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_cc_recipients',
            'display_name' => 'Tickets CC recipients',
            'uri' => '/customer/manage_cc_recipients',
            'add_separator_before' => TRUE
        ),
        'manage_suppliers' => array(
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_suppliers',
            'display_name' => 'Suppliers',
            'uri' => '/customer/manage_suppliers',
            'add_separator_before' => TRUE
        ),
        'customer_report' => array(
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'customer_report',
            'display_name' => 'Customer report',
            'uri' => '/customer/customer_report',
            'add_separator_before' => TRUE
        ),
        'manage_notifications_logs' => array(
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_notifications_logs',
            'display_name' => 'Notifications logs',
            'uri' => '/customer/manage_notifications_logs',
            'add_separator_before' => FALSE
        ),
        'manage_messages_logs' => array(
            'module' => 'customer',
            'submenu_of' => 'customer',
            'name' => 'manage_messages_logs',
            'display_name' => 'Messages logs',
            'uri' => '/customer/manage_messages_logs',
            'add_separator_before' => FALSE
        )
    )
);

?>

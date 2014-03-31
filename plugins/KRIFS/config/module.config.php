<?php
$router = array(
    'routes' => array(
        'krifs_ticket_add' => array(
            'route' => '/krifs/ticket_add[/]*:customer_id[/]*',
            'target' => array('cl' => 'krifs', 'op'=>'ticket_add'),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'customer_id' => '([0-9]*)',
            ),
        ),
        'krifs_intervention_add' => array(
            'route' => '/krifs/intervention_add[/]*:customer_id[/]*',
            'target' => array('cl' => 'krifs', 'op'=>'intervention_add'),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'customer_id' => '([0-9]*)',
            ),
        ),
        'krifs_ticket_object_add_iframe' => array(
            'route' => '/krifs/ticket_object_add_iframe[/]*:ticket_id[/]*:object_class[/]*',
            'target' => array('cl' => 'krifs', 'op'=>'ticket_object_add_iframe'),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'ticket_id' => '([0-9]*)',
                'object_class' => '([0-9]*)',
            ),
        ),
        'krifs_ticket_object_add' => array(
            'route' => '/krifs/ticket_object_add[/]*:ticket_id[/]*:object_class[/]*',
            'target' => array('cl' => 'krifs', 'op'=>'ticket_object_add'),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'ticket_id' => '([0-9]*)',
                'object_class' => '([0-9]*)',
            ),
        ),
        'krifs' => array(
            'route' => '/krifs[/]*:op[/]*:id[/]*',
            'target' => array('cl' => 'krifs'),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                    'op' => '([a-zA-Z][a-zA-Z0-9_-]*)',
                    'id' => '([0-9]*)',
            ),
        ),
    ),
);
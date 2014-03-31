<?php
$router = array(
    'routes' => array(
        'customer' => array(
            'route' => '/customer[/]*:op[/]*:id[/]*',
            'target' => array('cl' => 'customer', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'op' => '([a-zA-Z][a-zA-Z0-9_-]*)',
                'id' => '([0-9]*)',
            ),
        ),
    ),
);
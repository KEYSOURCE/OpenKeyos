<?php
$router = array(
    'routes' => array(
        'klara_computer_password_add' => array(
            'route' => '/klara/computer_password_add[/]*:computer_id[/]*:customer_id[/]*',
            'target' => array('cl' => 'klara', 'op' => 'computer_password_add', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'computer_id' => '([0-9]*)',
                'customer_id' => '([0-9]*)',
            ),
        ),
        'klara' => array(
            'route' => '/klara[/]*:op[/]*:id[/]*',
            'target' => array('cl' => 'klara', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'op' => '([a-zA-Z][a-zA-Z0-9_-]*)',
                'id' => '([0-9]*)',
            ),
        ),
    ),
);
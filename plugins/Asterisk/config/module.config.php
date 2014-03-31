<?php
$router = array(
    'routes' => array(
        'asterisk' => array(
            'route' => '/asterisk[/]*:op[/]*:id[/]*',
            'target' => array('cl' => 'asterisk', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'op' => '([a-zA-Z][a-zA-Z0-9_-]*)',
                'id' => '([0-9]*)',
            ),
        ),
    ),
);
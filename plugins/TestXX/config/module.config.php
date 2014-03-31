
<?php

$router = array(
    'routes' => array(
        'test_xx' => array(
            'route' => '/test_xx[/]*:op[/]*:id[/]*',
            'target' => array('cl' => 'test_xx'),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                    'op' => '([a-zA-Z][a-zA-Z0-9_-]*)',
                    'id' => '([0-9]*)',
            ),
        ),
    ),
);

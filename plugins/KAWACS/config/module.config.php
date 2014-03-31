<?php
$router = array(
    'routes' => array(
        'kawacs_monitored_ip_edit' => array(
            'route' => '/kawacs/monitored_ip_edit[/]*:id[/]*',
            'target' => array('cl' => 'kawacs', 'op' => 'monitored_ip_edit', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'id' => '([0-9]*)',
            ),
        ),
        'kawacs_computer_view_log' => array(
            'route' => '/kawacs/computer_view_log[/]*:computer_id[/]*:item_id[/]*',
            'target' => array('cl' => 'kawacs', 'op' => 'computer_view_log', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'computer_id' => '([0-9]*)',
                'item_id' => '([0-9]*)',
            ),
        ),
        'kawacs_computer_edit_item' => array(
            'route' => '/kawacs/computer_edit_item[/]*:computer_id[/]*:item_id[/]*',
            'target' => array('cl' => 'kawacs', 'op' => 'computer_edit_item', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'computer_id' => '([0-9]*)',
                'item_id' => '([0-9]*)',
            ),
        ),
        'kawacs_computer_view_item' => array(
            'route' => '/kawacs/computer_view_item[/]*:id[/]*:item_id[/]*',
            'target' => array('cl' => 'kawacs', 'op' => 'computer_view_item', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'id' => '([0-9]*)',
                'item_id' => '([0-9]*)',
            ),
        ),
        'kawacs' => array(
            'route' => '/kawacs[/]*:op[/]*:id[/]*',
            'target' => array('cl' => 'kawacs', ),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                'op' => '([a-zA-Z][a-zA-Z0-9_-]*)',
                'id' => '([0-9]*)',
            ),
        ),
    ),
);
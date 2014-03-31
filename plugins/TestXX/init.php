<?php
$plugin_init = array(
	'MODELS' => array(			
	),
	'CONTROLLERS' => array(
		'test_xx' => array(
			'class' => 'TestXXController', 
			'friendly_name' => 'TestXX', 
			'file' => '/var/www/openkeyos/plugins/TestXX/controller/test_xx_controller.php', 
			'default_method' => 'index', 
			'requries_acl' => False, 
		), 
	),
	'VIEWS' => __DIR__.'/views',
	'STRINGS' => array(
	),
	'IS_MAIN_MODULE' => FALSE,
	'MAIN_MENU_MODULE' => array(
		'name' => 'test_xx',
		'display_name' => 'TestXX',
		'uri' => '/test_xx',
	),
	'MENU' => array(
	),
);

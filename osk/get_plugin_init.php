<?php
$plg_reg =  $argv[1];
if(!isset($plg_reg)){
    echo "Please specify the plugin init file!";
    die;
}
require_once $plg_reg;
echo json_encode($plugin_init);
<?php
$plg_reg = ( ! isset($argv[1])) ? __DIR__ . "/../plugins/registerPlugins.php" : $argv[1];
require_once $plg_reg;
echo json_encode($GLOBALS['PLUGINS']);
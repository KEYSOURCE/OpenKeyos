<?php

/**
* Sets the environment for the Smarty engine and loads the Smarty class
* @package
* @subpackage Smarty_settings
*/

$path = dirname(__FILE__);

define ('SMARTY_DIR', $path.'/../_external/smarty/smartylibs/');

define('SMARTY_TEMPLATE_DIR', $path.'/../core/views/');
define('SMARTY_COMPILE_DIR', $path.'/../'.$conf['smarty_compile']);
define('SMARTY_CONFIG_DIR', $path.'/../'.$conf['smarty_configs']);
define('SMARTY_CACHE_DIR', $path.'/../'.$conf['smarty_cache']);

require_once(SMARTY_DIR.'Smarty.class.php');

?>

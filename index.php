<?php
/**
* KeyOS web server request dispatcher
*
* This script receives URL requests from browsers, instantiate the specified display class and 
* calls the specified display method.
*
* The classes must be children of the Smarty > base_display class. The methods should either:
* - Display a page (using the display() method inherited from Smarty > base_display)
* - Return an URL (e.g. after processing a form submission), in which case the browser
* is redirected to the newly specified URL.
*
* The class and method are specified through the "cl" and "op" URL parameters. Alternatively,
* for displaying static pages, the "sec" and "page" parameters can be used, which specify
* a directory and template file to be displayed using the base_display class.
*
* @todo:	Improve error handling
*/
ini_set('display_errors', 0);

if(!file_exists(__DIR__ . "/config.ini")){
    //not installed
    if(file_exists(__DIR__ . "/install/install.php")){
        header("Location: install/install.php");
        die;
    }
}
/*if(file_exists(__DIR__ . "/install/generated/.install")){
    //there is an active installation in progress
    header("Location: install/install.php");
    die;
}*/

require_once(dirname(__FILE__).'/lib/lib.php');
date_default_timezone_set('Europe/Paris');
$GLOBALS['start_time'] = microtime_float();

// Make sure the connection will be done over HTTP, if requested
//if (isset($_REQUEST['http'])) force_http();
force_https ();

// The class and method to call
$class = (isset($_REQUEST['cl']) ? $_REQUEST['cl'] : '');
$op = (isset($_REQUEST['op']) ? $_REQUEST['op'] : '');


if (empty($class)) $class = DEFAULT_CLASS;
if (empty($op))
{
	if ($class == DEFAULT_CLASS and get_uid())
	{
		$class = 'home';
		$op = 'user_area';
	}
	else $op = $GLOBALS['CLASSES_DISPLAY'][$class]['default_method'];
}

// Check for request of displaying a static page
$sec = (isset($_REQUEST['sec']) ? $_REQUEST['sec'] : '');
$page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : '');

session_start();
if (class_display_load($class))
{
	set_time_limit (120);
	$class = $GLOBALS['CLASSES_DISPLAY'][$class]['class'];
	
	// Instantiate the needed display class
	$obj = new $class();
	
	if (!empty($sec)) $obj->sec = $sec;
	if (!empty($page)) $obj->page = $page;


	// Call the needed display method
    $ret = "";
    if(!$obj->preDispatch()){
        //debug("call method uncached");
	    $ret = $obj->$op();
    }
    $obj->postDispatch();
	
	if (preg_match('/^http(s)*\:\/\//', $ret))
	{
		// If the method returned an URL, this means no display was performed
		// and the browser must be redirected to a new page.
		session_write_close();
		header("Location: $ret\n\n");
		exit;
	}
}
else
{
	echo "Can't load class $class <b>Class file: ".$GLOBALS['CLASSES_DISPLAY'][$class]['file'];
}
?>
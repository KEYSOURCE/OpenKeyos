<?php
/**
* This file contains main global functions
*
* @package
* @subpackage Global_funtions
*/

//error_reporting(E_ERROR);
error_reporting(E_ALL & ~E_NOTICE);

//this seems like a good place to check if keyos is installed

// Load configuration file settings
$conf = parse_ini_file(dirname(__FILE__).'/../config.ini', 1);

/** Database server */
define ('DB_HOST', $conf['db_host']);
/** Database user name */
define ('DB_USER', $conf['db_user']);
/** Database password */
define ('DB_PWD', $conf['db_password']);
/** Database name */
define ('DB_NAME', $conf['db_name']);

/** Path to Zip binary */
define ('PATH_TO_ZIP', $conf['path_to_zip']);
/** Path to Unzip binary */
define ('PATH_TO_UNZIP', $conf['path_to_unzip']);
/** Path to md5sum binary */
define ('PATH_TO_MD5SUM', $conf['path_to_md5sum']);

/** The log level */
define ('LOG_LEVEL', $conf['log_level']);

/** The base URL, when it can't be determined automatically */
define ('BASE_URL', $conf['base_url']);

/** For PDF generator */
define ('JAVA_HOME', $conf['java_home']);
define ('FOP_PARSER', $conf['fop_parser']);

/** For WordML generator */
define ('XSLTPROC', $conf['xsltproc']);

define('TIMEZONE_IDENTIFIER', $conf['timezone_identifier']);

define('CACHE_KEY_PREFIX', $conf['cache_key_prefix']);

define('USE_CACHING', $conf['use_caching']);
define('CACHE_ENGINE', $conf['cache_engine']);
define('CACHE_DEFAULT_SERVER', $conf['cache_default_server']);
define('CACHE_DEFAULT_PORT', $conf['cache_default_port']);
define('CACHE_DEFAULT_TTL', $conf['cache_default_ttl']);

define('FRONT_END_ERRORS', $conf['front_end_errors']);

/**
 * Checks if a plugin registration is valid or not
 * 
 * @param array $plugin_reg 
 * @return BOOLEAN
 */


// Do initialization based on the type of request: normal browsing or Kawacs Agent contact
if (!isset($GLOBALS['kawacs_reporting']) or !$GLOBALS['kawacs_request'])
{
         
	// Load the base classes
        
	require_once(dirname(__FILE__) . '/db.php');
	require_once(dirname(__FILE__) . '/base.php');
	require_once(dirname(__FILE__) . '/smarty.php');
    require_once(dirname(__FILE__) . '/const.php');
	require_once(dirname(__FILE__) . '/base_display.php');
    require_once(dirname(__FILE__) . '/plugin_model.php');
    require_once(dirname(__FILE__) . '/plugin_controller.php');

	// Load the constants with the lists definitions for MONITOR_TYPE_LIST items
	require_once(dirname(__FILE__).'/const_lists_items.php');
        

    if(class_load('Ticket')){
        $GLOBALS ['TICKET_STATUSES'] = Ticket::get_ticket_statuses_list();
        $GLOBALS ['TICKET_TYPES'] = Ticket::get_ticket_types_list();
    }
	// Will store the currently logged in user (if any). It will be initially set in BaseDisplay::BaseDisplay()
	$GLOBALS ['CURRENT_USER'] = null;

	// Load additional classes which are required in most or all other modules
	class_load('Auth');
	class_load('User');
	class_load('Group');
	class_load('Acl');
}
else
{
	// KAWACS Agent reporting
	require_once(dirname(__FILE__).'/const.php');

	// Load the base classes
	require_once(dirname(__FILE__) . '/db-mysql.php');
	require_once(dirname(__FILE__).'/base.php');
}

function get_class_plugin($class){
    if(in_array($class, array_keys($GLOBALS['MODELS']))){
        return $GLOBALS['MODELS'][$class]['plugin'];
    } else {
        //check if this is a core MODEL
        if(in_array($class, array_keys($GLOBALS['CLASSES']))){
            return "CORE";
        }
        else return FALSE;
    }

}

/**
* Shortcut function for loading a file containing a data manipulating object.
* @paraam	string $class	The name of the class to load. Must be a key from $GLOBALS['CLASSES']
* @return	bool		TRUE or FALSE if loading the class was succesfull or not
*/
function class_load($class = '')
{
    if(!$class) return TRUE;

	if($class_plugin = get_class_plugin($class))
	{
        if($class_plugin == "CORE"){
            $file = $GLOBALS['CLASSES'][$class];
            if (file_exists($file))
            {
                require_once($file);
                return TRUE;
            }
        }
        else if(PluginBase::status($class_plugin) == PLUGIN_STATUS_ENABLED){
            $file = $GLOBALS['CLASSES'][$class];
            if (file_exists($file))
            {
                require_once($file);
                return TRUE;
            }
        }
	}
	return FALSE;
}

/**
* Shortcut function for loading a class containing a displaying class.
* @paraam	string $class	The short name of the class to load (usually taken from the URL). Must be a key from $GLOBALS['CLASSES_DISPLAY']
* @return	bool		TRUE or FALSE if loading the class was succesfull or not
*/
function class_display_load ($class = '')
{
	$ret = false;
	if ($class and !empty($GLOBALS['CLASSES_DISPLAY'][$class]))
	{
		$file = $GLOBALS['CLASSES_DISPLAY'][$class]['file'];
		if (file_exists($file))
		{
			require_once($file);
			$ret = true;
		}
	}
	return $ret;
}


/**
* Returns the list of methods in a display class
* @param	string	$class		The short class name (as used in URLs) for which to return methods
* @return	array			Array with the method names - excluding inherited methods
*/
function get_display_class_methods ($class = '')
{
	$ret = array ();

	if ($class)
	{
		class_display_load ($class);
		$class_name = $GLOBALS['CLASSES_DISPLAY'][$class]['class'];
		$parent_methods = get_class_methods (get_parent_class($class_name));
		$parent_methods[] = strtolower ($class_name); // Eliminate the constructor from the list

		$ret = get_class_methods ($class_name);

		$ret = array_values (array_diff ($ret, $parent_methods));

		// Eliminate the "_submit" methods, where possible
		$ret_submits = array();
		foreach ($ret as $r) $ret_submits[] = $r.'_submit';

		$ret = array_diff ($ret, $ret_submits);
		sort($ret);
		reset($ret);
	}

	return $ret;
}


/**
* Returns a list of ACL-bout display classes
* @return	array		Associative array, the keys are the short class names and
*				the values are the friendly names
*/
function get_acl_display_classes_list ()
{
	$ret = array ();
	foreach ($GLOBALS['CLASSES_DISPLAY_ACL'] as $cl) $ret[$cl] = $GLOBALS['CLASSES_DISPLAY'][$cl]['friendly_name'].' ['.$cl.']';
	return $ret;
}


/** Returns the ID of the current logged in user */
function get_uid()
{
	$auth = new Auth();
	return $auth->get_uid();
}


/**
* Checks if there is a currently logged in user and, if needed, if he has enough access permissions.
*
* If not, redirects the browser to the login page, remembering the URL which the user tried to access,
* so he can be sent back to that page after login.
* This function should be called at the beginning of any display class method which is linked to a
* user-restricted page.
*
* @param	array		$extra_checks		Array with additional access checks to perform, e.g. check
*							permissions to access a specific customer. The array keys(s) will
*							indicate the type of object(s) to check and the value(s) will be
*							object ID(s). Possible keys are:
*							- customer_id : Checks if a Keysource user is allowed to access that customer
*							- computer_id : Checks if a Keysource user is allowed to access the coustomer owning that computer
*							- ticket_id : Checks if a Keysource user is allowed to access the coustomer owning that ticket
*
*/
function check_auth ($extra_checks = null)
{
	$uid = get_uid();
	if (empty($uid))
	{
		// User not logged in, redirect to the login screen
		$url = BaseDisplay::mk_redir('login', array('goto' => $_SERVER['REQUEST_URI']), 'user');
		header("Location: $url\n\n");
		exit;
	}
	else
	{
		// There is a user logged in, check if he has proper permissions
		$class = $_REQUEST['cl'];
		$function = $_REQUEST['op'];

		if ($GLOBALS['CURRENT_USER'] and $GLOBALS['CURRENT_USER']->id==$uid) $user = &$GLOBALS['CURRENT_USER'];
		else $user = new User ($uid);	// This will seldom be done, since check_auth() is mainly called from children of BaseDisplay

		// Check if the user is allowed to access the module and operation
		if (in_array($class, $GLOBALS['CLASSES_DISPLAY_ACL']))
		{
			// This is a module for which ACL permissions are required
			$has_permission = $user->can_access ($class, $function);
                      
			if (!$has_permission)
			{
				$url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
				header ("Location: $url\n\n");				
                                exit;
			}
		}

		// If the above was OK, do also additional checks if they were requested
		if (is_array ($extra_checks))
		{
			if (isset($extra_checks['customer_id'])) $extra_checks['customer_id'] = trim($extra_checks['customer_id']);

			if ($extra_checks['computer_id'])
			{
				// Will actually need to check access to the customer owning the computer
				$q = 'SELECT customer_id FROM '.TBL_COMPUTERS.' WHERE id='.db::db_escape($extra_checks['computer_id']);
				$extra_checks['customer_id'] = db::db_fetch_field ($q, 'customer_id');
			}

			if ($extra_checks['ticket_id'])
			{
				// Will actually need to check access to the customer for whom the ticket is
				$q = 'SELECT customer_id FROM '.TBL_TICKETS.' WHERE id='.db::db_escape($extra_checks['ticket_id']);
				$extra_checks['customer_id'] = db::db_fetch_field ($q, 'customer_id');
			}

			if ($extra_checks['customer_id'])
			{
				// Needs to check access permissions to a customer
				if (!$user->is_customer_user() and !$user->administrator and $user->restrict_customers)
				{
                                        
					// This user must have been explicetly granted access to this customer
					$has_permission = $user->has_assigned_customer ($extra_checks['customer_id']);
                                       
					if (!$has_permission)
					{
						error_msg ('This customer is not assigned to you');
						$url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
						header ("Location: $url\n\n");
						exit;
					}
				}
			}
		}
	}
}


/**
* Returns the base path (part of the URL) for the current installation.
*
* By using this function the application can be placed in any directory on
* the web server, without needing to re-define any setting.
* @return	string 		If the application entry point is at http://www.mydomain.com/myapplication, it returns "/myapplication"
*/
function get_base_location() {
    $ret = pathinfo($_SERVER['PHP_SELF']);
	$ret = $ret['dirname'].'/';

	$doc_root = ($_SERVER['DOCUMENT_ROOT']);
	while (!file_exists($doc_root.$ret.'phplib/lib.php') and $cnt++<100) $ret = preg_replace('/([^\/])*\/$/', '', $ret);
	$ret = preg_replace('/\/\//', '/', $ret);
	return $ret;
}


function get_base_url2() {
    $ret = $_SERVER['HTTP_HOST'] . get_base_location();
    $ret = preg_replace('/\/\//', '/', $ret);
    $ret = preg_replace('/\/$/', '', $ret);

    if (!$ret or $ret[0] == "/") {
        $ret = BASE_URL;
    }

    if (!$GLOBALS['conf']['rewrite_on'])
        $ret .= '/index.php';
    return $ret;
}

/**
* Returns the base URL for the current installation.
*
* By using this function the application can be placed in any directory on
* the web server, without needing to re-define any setting.
* @return	string 		The application entry point, e.g. http://www.mydomain.com/myapplication
*/
function get_base_url()
{
    if ($GLOBALS['conf']['nice_url'])
        return get_base_url2 ();
    $ret = $_SERVER['HTTP_HOST'] . get_base_location();
    $ret = preg_replace('/\/\//', '/', $ret);
    $ret = preg_replace('/\/$/', '', $ret);

    if (!$ret or $ret[0] == "/") {
        $ret = BASE_URL;
    }

    return $ret;
}

function add_extra_get_params($link, $params, $type="normal"){
    if($link){
        if($type=='json'){
            $params = json_decode($params);
        }
        if($type=="template"){
            $nv = array();
            foreach(explode(',', $params) as $ind){
                $spl = explode(':', $ind);
                if(is_array($spl) and count($spl) == 2){
                    $nv[$spl[0]] = $spl[1];
                }
            }
            $params = $nv;
        }

        $has_get_params = strpos($link, '?');
        if($has_get_params === FALSE){
            $first = true;
            foreach($params as $key=>$value){
                if($first){
                    $link .= "/?" . $key . "=" . $value;
                    $first = false;
                }
                else {
                    $link .= "&" . $key . "=" . $value;
                }
            }
        } else {
            foreach($params as $key=>$value){
                $link .= "&" . $key . "=" . $value;
            }
        }
        return $link;
    }

    return "http://" . get_base_url();
}

function get_link($cl, $op='', $params = array(), $type='normal'){

    global $routerObj;
    if($type=='json'){
        $params = json_decode($params);
    }
    if($type=="template"){
        $nv = array();
        foreach(explode(',', $params) as $ind){
            $spl = explode(':', $ind);
            if(is_array($spl) and count($spl) == 2){
                $nv[$spl[0]] = $spl[1];
            }
        }
        $params = $nv;
    }
    if($op==''){
        //put in the op the default method from this controller
        $plugin_name = $GLOBALS['PLUGINS'][$cl]['plugin_name'];
        $default_op = $GLOBALS['CLASSES_DISPLAY'][$plugin_name]['CONTROLLERS'][$cl]['default_method'];
        $op=$default_op;
    }
    if(empty($params)) return "http://" . get_base_url() . "/" . $cl . "/" . $op;
    //debug($routerObj); die;
    $route_name = $op != '' ? $cl . "_" . $op : $cl;
    $route_name = $GLOBALS['PLUGINS'][$cl]['plugin_name'] . '_' . $route_name;
    $rtObj = $routerObj->get_named_route($route_name);
    if(!$rtObj){
        //no such route -> this means that we can put all params in GET - except the $id
        $ret_link = get_base_url() . "/" . $cl . "/" . $op;
        if(isset($params['id'])){
            $ret_link .= "/" . $params['id'];
        }
        $first = true;
        foreach($params as $key=>$value){
            if($key!="id"){
                if($first){
                    $ret_link .= "/?" . $key . "=" . $value;
                    $first = false;
                }
                else {
                    $ret_link .= "&" . $key . "=" . $value;
                }

            }
        }
        return "http://" . $ret_link;
    }
    else{
        $params_keys = array_keys($rtObj->getFilters());
        $get_params = array_diff(array_keys($params), $params_keys);
        $ret_link = get_base_url() . "/" . $cl . "/" . $op;
        foreach($params_keys as $pk){
            $ret_link .= "/" . $params[$pk];
        }
        $first = true;
        foreach($get_params as $gp){
            if($first){
                $ret_link .= "/?" . $gp . "=" . $params[$gp];
                $first = false;
            }
            else {
                $ret_link .= "&" . $gp . "=" . $params[$gp];
            }
        }
        return "http://" . $ret_link;
    }



}

function xss_clean($data) {
    class_load('HTMLPurifier');
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = xss_clean($value);
            } else {
                $data[$key] = HTMLPurifier($value);//HTMLPurifier($value);
            }
        }
        return $data;
    } else {
        return HTMLPurifier($data);
    }
}

function get_date($format){
    date_default_timezone_set(TIMEZONE_IDENTIFIER);
    return date($format);
}

/**
* Appends an error message into $_SESSION, so it is preserved when moving between pages; or it returns the current messages and removes them from $_SESSION
* @param 	string 	$msg	The messages to store. If not specified, the function will instead
*				return the currently saved message(s) and (by default) removes
*				them from $_SESSION
* @param	bool	$keep	If no message was specified, passing $keep as TRUE will
*				instruct the method NOT to delete the current stored messages.
* @return	string		The current stored messages if $msg was empty, or an empty string otherwise.
*/
function error_msg($msg = '', $keep = false)
{
	$ret = '';
    if(!FRONT_END_ERRORS){
        if (!empty($msg))
        {
            $_SESSION['error_msg'].= $msg.'<br>';
        }
        else
        {
            $ret = $_SESSION['error_msg'];
            if (!$keep)
            {
                $_SESSION['error_msg'] = null;
                unset($_SESSION['error_msg']);
            }
        }
    }
	return $ret;
}


/** Returns true or false if there are any error messages */
function empty_error_msg ()
{
	return (empty($_SESSION['error_msg']));
}


/** Makes sure the current page is displayed over HTTP */
function force_http()
{
	if ($_SERVER['HTTPS'] == 'on')
	{
		$href = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$href = preg_replace('/(&)*http\=1/', '', $href);
		$href = preg_replace('/\/\?$/', '', $href);
		session_write_close();
		header("Location: $href\n\n");
		exit();
	}
}


/** Makes sure the current page is displayed over HTTPS */
function force_https()
{
        if(isset($_SERVER['HTTPS'])){
            if ($_SERVER['HTTPS'] != 'on')
            {
                    $skip_https = false;
                    foreach ($GLOBALS['SKIP_HTTPS'] as $skip_ip)
                    {
                            if (preg_match ('/^'.$skip_ip.'/', $_SERVER['REMOTE_ADDR'])) $skip_https = true;
                    }

                    if (!$skip_https)
                    {
                            $href = 'https://'.$_SERVER['HTTP_HOST'];
                            if (HTTPS_PORT and HTTPS_PORT != 443) $href.= ':'.HTTPS_PORT;
                            $href.= $_SERVER['REQUEST_URI'];

                            session_write_close();
                            header("Location: $href\n\n");
                            exit();
                    }
            }
        }
}


/**
* Given an array of values, it returns their sum - useful for composing values from bitwise operations
* @param	array	$data		Array with the values to add - which should be powers of 2
* @return	int			The sum of the values, or 0.
*/
function get_bitwise_sum ($data = array ())
{
	$ret = 0;
	if (is_array($data))
	{
		foreach ($data as $val) $ret+= $val;
	}
	return $ret;
}


/**
* Converts an integer value to a memory string
* @param	float	$mem		The memory size (in bytes) to convert
* @param	bool	$round		If True, don't add decimals
* @return	string			The user-friendly representation of the memory amount
*/
function get_memory_string ($mem = 0, $round = false)
{
	$ret = '';
	if (is_numeric($mem))
	{
		$c_val = 1;
		$c_val = (float) $c_val;	// Enforce the use of float, for very large numbers
		$idx = 1;
		while ($c_val>0 and $c_val*1024 <= abs($mem))
		{
			$c_val*= 1024;
			$idx++;
		}

		$decimals = ($round ? 0 : 2);
		$ret = number_format (($mem/$c_val), $decimals).' '.$GLOBALS['CRIT_MEMORY_MULTIPLIERS_NAMES'][$idx];
	}

	return $ret;
}


/**
* Complement to get_memory_string(), returns only the numeric value, without the multiplier symbol
* @param	float	$mem		The memory size (in bytes) to convert
* @return	string			The user-friendly representation of the memory amount
*/
function get_memory_string_num ($mem = 0)
{
	$ret = '';
	if (is_numeric($mem))
	{
		$c_val = 1;
		$idx = 1;
		while ($c_val*1024 <= $mem)
		{
			$c_val*= 1024;
			$idx++;
		}

		$ret = number_format (($mem/$c_val), 2);
	}
	return $ret;
}

/**
* Complement to get_memory_string(), returns only the multiplier index, without the value
* @param	float	$mem		The memory size (in bytes) to convert
* @return	string			The user-friendly representation of the memory amount
*/
function get_memory_string_multiplier ($mem = 0)
{
	$ret = '';
	if (is_numeric($mem))
	{
		$c_val = 1;
		$idx = 1;
		while ($c_val*1024 <= $mem)
		{
			$c_val*= 1024;
			$idx++;
		}

		$ret = $idx;
	}
	return $ret;
}


/** Saves an array (e.g. a form submission data) into $_SESSION */
function save_form_data ($data = array(), $field_name = '')
{
	if ($field_name)
	{
		$_SESSION['form_bk'][$field_name] = $data;
	}
	else
	{
		$_SESSION['form_bk'] = $data;
	}
}


/**
* Restores data saved to $_SESSION by a save_form_data() operation
* @param	string	$field_name	If specified, restores only this field instead of the entire array
* @param	boolean	$keep		If to keep in $_SESSION the saved data after its retrieval
* @param	array	$data		An array in which the saved values will be merged
* @return	array			The restored data
*/
function restore_form_data ($field_name = '', $keep = false, &$data)
{
	$ret = array();
	if ($field_name)
	{
		if (isset($_SESSION['form_bk'][$field_name]))
		{
			$ret = $_SESSION['form_bk'][$field_name];

			if (!$keep) unset ($_SESSION['form_bk'][$field_name]);
		}

	}
	else
	{
		$ret = $_SESSION['form_bk'];
		if (!$keep) unset ($_SESSION['form_bk']);
	}

	if (is_array($data))
	{
		if (is_array ($ret))
		{
			foreach ($ret as $k=>$v) $data[$k] = $ret[$k];

		}
		$data = $ret;
	}
	return $ret;
}

/** Deletes, by name, the data for a saved form */
function clear_form_data ($field_name)
{
	if (isset($_SESSION['form_bk'][$field_name])) unset ($_SESSION['form_bk'][$field_name]);
}

/** Composes an array with paging options */
function make_paging ($perpage, $total_pages)
{
	$ret = array();
	$perpage = ($perpage ? $perpage : 30);
	for ($i=1; $i<=$total_pages; $i+= $perpage) $ret[$i-1] = $i.' - '.(min(($i+$perpage-1), $total_pages)).' of '.$total_pages;
	return $ret;
}


/** Converts and MySQL timestamp to a PHP time */
function stamp_to_time ($ts)
{
	return mktime(substr($ts,8,2),substr($ts,10,2),substr($ts,12,2),substr($ts,4,2),substr($ts,6,2),substr($ts,0,4));
}

/**
* Converts the date values given by the Smarty form data edit fields into a time value
* @param	array	$date_fields		Associative array with the keys 'Date_Month', 'Date_Day' and 'Date_Year'.
* @return	time				The converted time value or 0 if the conversion doesn't give a proper time value.
*/
function date_fields_to_time ($date = array())
{
	$ret = 0;

	if (is_array($date))
	{
		$date = mktime(0,0,0, $date['Date_Month'], $date['Date_Day'], $date['Date_Year']);

		if ($date>0) $ret = $date;
		else $ret = 0;
	}

	return $ret;
}


/**
* Converts an interval (in seconds) into a string representation ([d days] h:m:s)
*/
function format_interval ($seconds)
{
	$days = intval($seconds / (24*60*60));
	$seconds = ($seconds % (24*60*60));
	$hours = str_pad (intval ($seconds / (60*60)), 2, '0', STR_PAD_LEFT);
	$seconds = ($seconds % (60*60));
	$minutes = str_pad (intval ($seconds / 60), 2, '0', STR_PAD_LEFT);
	$seconds = str_pad (intval ($seconds % 60), 2, '0', STR_PAD_LEFT);

	if ($days)
		$ret = "$days d $hours:$minutes:$seconds";
	else
		$ret = "$hours:$minutes:$seconds";
	return $ret;
}


/**
* Converts an interval (in minutes) into a string representation (hh:mm)
*/
function format_interval_minutes ($minutes)
{
	$hours = intval(abs($minutes)/60);
	$minutes = (abs($minutes) % 60);
	$minutes = str_pad ($minutes, 2, 0, STR_PAD_LEFT);
	$hours = str_pad ($hours, 2, 0, STR_PAD_LEFT);

	$ret = $hours.':'.$minutes;
	if ($minutes < 0) $ret = '-'.$ret;

	return $ret;
}


/** Converts a number of minutes into hours */
function minutes2hours ($minutes)
{
	return round($minutes/60, 2);
}

/**
* Takes a date from the format used by the javascript calendar (d/m/y) and converts it to a time value
*/
function js_strtotime ($date = '')
{
	$ret = 0;
	$date = trim($date);
	if ($date)
	{
		list ($day, $month, $year, $hour, $minute) = preg_split ('/\/|\.|\:| /', $date);

		$day = str_pad ($day, 2, '0', STR_PAD_LEFT);
		$month = str_pad ($month, 2, '0', STR_PAD_LEFT);

		if ($hour or $minute)
		{
			$ret = strtotime ($year . $month . $day.' '.$hour.':'.$minute);
		}
		else
		{

			$ret = strtotime ($year."-".$month."-".$day);
			//debug(date('d M Y',$ret));
		}
	}

	return $ret;
}

//function date_format($day, $format)
//{
//	return strftime($format,$day);
//}


/**
* Takes a duration string (hh:mm) and converts it to minutes
*/
function js_durationtomins ($duration = '')
{
	$ret = 0;

	if ($duration)
	{
		list ($hours, $minutes) = preg_split ('/\s*:\s*/', $duration);

		$ret = abs($hours)*60 + abs($minutes);
		if (preg_match('/^\s*\-/', $duration)) $ret = $ret * -1;
	}

	return $ret;
}

/** Given a date (as UNIX time), returns a time which represents the hour 00:00 for that day */
function get_first_hour ($date = null)
{
    date_default_timezone_set(TIMEZONE_IDENTIFIER);
	if (!$date) $date = time ();

	//debug($date);

	return strtotime (date('d M Y', $date).' 00:00:00');
}

/** Given a date (as UNIX time), returns a time which represents the hour 23:59 for that day */
function get_last_hour ($date = null)
{
	if (!$date) $date = time ();

	return strtotime (date('d M Y', $date).' 23:59:59');
}

/** Given a date (as UNIX time), returns a time which represents first Monday for the week to which the log belongs to */
function get_first_monday ($date = null)
{
	if (!$date) $date = time ();

	if (date ('w', $date) == 1)
	{
		// Today is Monday
		$ret = strtotime (date('d M Y', $date).' 00:00:00');
	}
	else
	{
		$ret = strtotime ('last Monday', $date);
	}

	return $ret;
}

function get_month_start ($date = null)
{
	if (!$date) $date = time();
	return strtotime (date('01 M Y', $date).' 00:00:00');
}


/** Given two dates, it returns an array with the months in that interval
* @return	array					Array of generic objects, each having the following attributes:
*							- month_start, month_end: timestamps for the start and end of the month
*							- month_str: string with the month and year
*							- is_current: specifies if this is the current month or not
*							- is_year_start: specified is this is the first month in the year OR
*							  if it is first month in the interval
*/
function get_months ($date_min, $date_max)
{
	$ret = array ();
	$date_min = get_month_start($date_min); // Just in case the first date is at end of the month (31), and adding 1 month would yeld invalid date

	if ($date_min and $date_max and $date_min <= $date_max)
	{
		$d = $date_min;
		while (date('Y_m',$d) <= date('Y_m',$date_max))
		{
			$obj = null;
			$obj->month_str = date ('M y', $d);
			$obj->month_str_short = date ('M', $d);
			$obj->month_start = get_month_start ($d);
			$obj->month_end = strtotime('+1 month', $obj->month_start) - 1;
			$obj->is_current = ($obj->month_start<=time() and $obj->month_end>=time());
			$obj->is_year_start = (date('m',$obj->month_start)=='01');

			$ret[] = $obj;
			$d = strtotime('+1 month', $d);
		}
		$ret[0]->is_year_start = true;
	}
	return $ret;
}

/** Given a value composed of a sum of flags (powers of 2), adds the new flag to the value, if it doesn't contain it already */
function val_flag_add ($value, $flag)
{
	if (($value & $flag) != $flag) $value+= $flag;
	return $value;
}

/** Given a value composed of a sum of flags (powers of 2), subsctracts the specified flag from the value, if the value contains it */
function val_flag_sub ($value, $flag)
{
	if (($value & $flag) == $flag) $value-= $flag;
	return $value;
}

/** Logs different messages to a file */
function do_log ($msg = '', $level = LOG_LEVEL_DEBUG)
{
	if ($msg and $level <= LOG_LEVEL)
	{
		// Check first if log rotation is needed
		if (file_exists(LOGFILE))
		{
			if (filesize(LOGFILE) > LOGFILES_MAX_SIZE)
			{
				if (file_exists(LOGFILE.'.'.LOGFILES_MAX_KEEP)) @unlink (LOGFILE.'.'.LOGFILES_MAX_KEEP);
				for ($i=LOGFILES_MAX_KEEP-1; $i>=1; $i--)
				{
					if (file_exists(LOGFILE.'.'.$i)) @rename (LOGFILE.'.'.$i, LOGFILE.'.'.($i+1));
				}

				@rename (LOGFILE, LOGFILE.'.1');
				@touch (LOGFILE);
				@chmod (LOGFILE, 0666);
			}
		}

		$fp = @fopen (LOGFILE, 'a');
		if ($fp)
		{
			$msg = '['.date('d-m-y H:i:s').' '.$level.'] '.$msg;
			fwrite ($fp, $msg."\n");
			fclose ($fp);
		}

		// For errors, log them to the error log as well
		if ($level == LOG_LEVEL_ERRORS)
		{
			if (file_exists(LOGFILE_ERR))
			{
				if (filesize(LOGFILE_ERR) > LOGFILES_MAX_SIZE)
				{
					if (file_exists(LOGFILE_ERR.'.'.LOGFILES_MAX_KEEP)) @unlink (LOGFILE_ERR.'.'.LOGFILES_MAX_KEEP);
					for ($i=LOGFILES_MAX_KEEP-1; $i>=1; $i--)
					{
						if (file_exists(LOGFILE_ERR.'.'.$i)) @rename (LOGFILE_ERR.'.'.$i, LOGFILE_ERR.'.'.($i+1));
					}

					@rename (LOGFILE_ERR, LOGFILE_ERR.'.1');
					@touch (LOGFILE_ERR);
					@chmod (LOGFILE_ERR, 0666);
				}
			}

			$fp = @fopen (LOGFILE_ERR, 'a');
			if ($fp)
			{
				$msg = '['.date('d-m-y H:i:s').' '.$level.'] '.$msg;
				fwrite ($fp, $msg."\n");
				fclose ($fp);
			}
		}
	}
}

/** Returns a float representation of the time, with microseconds */
function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/** Useful for debugging, will show in the page how much time (in microseconds) the processing took so far */
function show_elapsed ($msg = '')
{
return true;
	echo round((microtime_float() - $GLOBALS['start_time']), 4).' '.$msg.'<br>';
}


/** Returns the details for a subnet, or a set of subnets. If multiple IPs/net masks
* are passed in the parameters, the number of IPs must match the number of net masks.
* Any invalid IPs or masks are silently discarded.
* @param	string			$ip_addr	An IP address in the subnet, or multiple IPs separated by commas
* @param	string			$subnet_mask	One or more net masks, separated by commas
* @return	array					Array of associative arrays, one for each IP/mask pair. The associative
*							arrays have the following fields: network_address, broadcast_address,
*							hosts_number, ip_min, ip_max
*/
function get_subnets ($ip_addr, $subnet_mask)
{
	$ret = array ();
	if ($ip_addr and $subnet_mask)
	{
		$ip_addrs = preg_split ('/\s*,\s*/', trim($ip_addr));
		$subnet_masks = preg_split ('/\s*,\s*/', trim($subnet_mask));

		if (count($ip_addrs)==count($subnet_masks))
		{
			for ($i=0; $i<count($ip_addrs); $i++)
			{
				$ip_num = ip2long (trim($ip_addrs[$i]));
				$mask_num = ip2long (trim($subnet_masks[$i]));

				if ($ip_num!=-1 and $mask_num!=-1 and $ip_num!=0 and $mask_num!=0)
				{
					$net_addr = ($ip_num & $mask_num);
					$broadcast = ($net_addr | (~$mask_num));
					$ret[] = array (
						'network_address' => long2ip ($net_addr),
						'network_mask' => long2ip ($mask_num),
						'broadcast_address' => long2ip ($broadcast),
						'hosts_number' => $broadcast - $net_addr - 1,
						'ip_min' => long2ip ($net_addr + 1),
						'ip_max' => long2ip ($broadcast - 1)
					);
				}
			}
		}

	}
	return $ret;
}


/** Given a start an an end IP address, calculates the number of IPs in the interval
* @param	string					The first IP in the range
* @param	string					The last IP in the range
* @return	int					The number of IPs in range, or 0 if the IPs are invalid
*/
function count_ips ($ip_min, $ip_max)
{
	$ret = 0;
	$ip_min_num = ip2long ($ip_min);
	$ip_max_num = ip2long ($ip_max);
	if ($ip_min_num!=-1 and $ip_max_min_num!=-1 and $ip_max_num>=$ip_min_num) $ret = $ip_max_num - $ip_min_num + 1;

	return $ret;
}


/** Returns an asset number for a computer, which is built from the computer ID based on
* the computer type (server or workstation)
* @param	int		$id		The ID of the computer
* @param	int		$type		The type of the computer (COMP_TYPE_SERVER, COMP_TYPE_WORKSTATION)
* @return	string
*/
function get_asset_no_comp ($id, $type)
{
	$ret = '';
	if ($id)
	{
		if ($type == COMP_TYPE_SERVER) $ret = ASSET_PREFIX_SERVER . str_pad ($id, ASSET_NUM_LENGTH, '0', STR_PAD_LEFT);
		else $ret = ASSET_PREFIX_WORKSTATION . str_pad ($id, ASSET_NUM_LENGTH, '0', STR_PAD_LEFT);
	}
	return $ret;
}

/** Returns an asset number for a peripheral, which is built from the peripheral ID and
* the prefix defined by ASSET_PREFIX_PERIPHERAL
* @param	int		$id		The ID of the peripheral
* @return	string
*/
function get_asset_no_periph ($id)
{
	return ASSET_PREFIX_PERIPHERAL . str_pad ($id, ASSET_NUM_LENGTH, '0', STR_PAD_LEFT);
}

/** Returns an asset number for an AD printer. Unlike the asset numbers for computers and peripherals,
* which are built on the fly from the object ID, asset numbers for AD Printers are randomly generated
* and are stored in database, being associated with the printer's canonical name. If a request is
* made to this function with a canonical name for which an asset number was not generated already,
* then a new asset number will be created and stored in the database
* @param	string		$canonical_name		The canonical name of the AD printer
* @param	int		$customer_id		The ID of the customer to which this printer belongs to.
*							If not specified and a record needs to be created in
*							the ad_printers_extras table, then it will be determined
*							automatically (first by trying the ad_printers_warranties
*							table and then computers_items)
* @return	string
*/
function get_asset_no_ad_printer ($canonical_name, $customer_id = 0)
{
	$ret = '';
	if ($canonical_name)
	{
		// See if we have a canonical name already
		$q = 'SELECT asset_number FROM '.TBL_AD_PRINTERS_EXTRAS.' WHERE canonical_name="'.db::db_escape($canonical_name).'"';
		$ret = db::db_fetch_field ($q, 'asset_number');

		if (!$ret)
		{
			// An asset number doesn't exist for this printer, so create one
			$max_no = db::db_fetch_field ('SELECT max(id) as max FROM '.TBL_AD_PRINTERS_EXTRAS, 'max') + 1;

			// Check as well in the table with removed AD Printers, to avoid overlapping IDs/asset numbers
			if (db::db_fetch_field ('SELECT id FROM '.TBL_REMOVED_AD_PRINTERS.' WHERE id='.$max_no.' LIMIT 1', 'id'))
			{
				$max_removed_no = db::db_fetch_field ('SELECT max(id) as max FROM '.TBL_REMOVED_AD_PRINTERS, 'max') + 1;
				$max_no = max ($max_no, $max_removed_no);
			}

			$ret = ASSET_PREFIX_AD_PRINTER . str_pad ($max_no, 5, '0', STR_PAD_LEFT);

			// If a customer ID was not specified, try first to determine it from ad_printers_warranties
			if (!$customer_id)
			{
				$q = 'SELECT customer_id FROM '.TBL_AD_PRINTERS_WARRANTIES.' WHERE canonical_name="'.db::db_escape($canonical_name).'"';
				$customer_id = db::db_fetch_field ($q, 'customer_id');
			}
			// If still we don't have a customer ID, try the computers_items table
			if (!$customer_id)
			{
				$q = 'SELECT c.customer_id FROM '.TBL_COMPUTERS_ITEMS.' ci INNER JOIN '.TBL_COMPUTERS.' c ';
				$q.= 'ON ci.computer_id=c.id AND ci.item_id='.ADPRINTERS_ITEM_ID.' AND ci.field_id='.FIELD_ID_AD_PRINTER_CANONICAL_NAME.' ';
				$q.= 'WHERE ci.value="'.db::db_escape($canonical_name).'" LIMIT 1';
				$customer_id = db::db_fetch_field ($q, 'customer_id');
			}

			$q = 'INSERT INTO '.TBL_AD_PRINTERS_EXTRAS.' (canonical_name, asset_number, id, customer_id) VALUES ';
			$q.= '("'.db::db_escape($canonical_name).'", "'.db::db_escape($ret).'",'.$max_no.', '.intval($customer_id).')';
			db::db_query ($q);
		}
	}
	return $ret;
}

/** Checks is the MAC is a valid one that can be used for identifying computers.
* Bogus MACs include 00:00:00:00:00:00 and 54:55:43:44:52:XX (used by some VPN clients) */
function is_bogus_mac ($mac)
{
	if (empty($mac)) return true;
	elseif ($mac == '00:00:00:00:00:00') return true;
	elseif (preg_match('/^54:55:43:44:52:/', $mac)) return true;  	// Used by some VPN clients
	elseif (preg_match('/^54:/', $mac)) return true;			// Used by Check Point Virtual Network Adapter For SecureClient - SecuRemote Miniport
	elseif ($mac == '00:F1:D0:00:F1:D0') return true;               // GlobeTrotter HSxPA - Network Interface - SecuRemote Miniport
	elseif ($mac == '80:00:60:0F:E8:00') return true;		// Used by Microsoft RNDIS virtual adapter
	//elseif ( preg_match('/^00:50:56:/',$mac)) return true;	//vmware virtual adapters
	else return false;
}


/** Simple helper function, a shortcut for print_r() */
function debug($var = null)
{
	echo '<pre>';
	print_r($var);
	echo '</pre>';
}

/** Removes a directory, regardless if it is empty or not
* @param	string		$dir		The path to the directory to remove
* @return	bool				True or False if the operation succeeded or not
*/
function remove_directory($dir)
{
	$ret = false;
	if (file_exists($dir) and $handle = opendir("$dir"))
	{
		$ret = true;
		while (false !== ($item = readdir($handle)))
		{
			if ($item != "." && $item != "..")
			{
				if (is_dir("$dir/$item"))
				{
					if (!remove_directory("$dir/$item")) $ret = false;
				}
				else
				{
					if (!@unlink("$dir/$item")) $ret = false;
				}
			}
		}
		closedir($handle);
		$ret = ($ret and @rmdir($dir));
	}
	return $ret;
}


/** "Flatterns a directory by moving all files from its subdirectories into the directory and removing the subdirectories
* @param	string		$dir		The path to the directory to "flattern"
* @param	string		$main_dir	Doesn't need to be passed, it is only used internally for recursion
* @return	bool				True or False if the operation succeeded or not
*/
function flattern_directory ($dir, $main_dir = '')
{
	if (!$main_dir) $main_dir = $dir;
	$ret = false;
	if ($handle = opendir("$dir"))
	{
		$ret = true;
		while (false !== ($item = readdir($handle)))
		{
			if ($item != "." && $item != "..")
			{
				if (is_dir("$dir/$item"))
				{
					if (!flattern_directory("$dir/$item", "$main_dir")) $ret = false;
				}
				else
				{
					if (!@rename("$dir/$item", "$main_dir/$item")) $ret = false;
				}
			}
		}
		closedir($handle);
		if ($dir != $main_dir) $ret = ($ret and @rmdir($dir));
	}
	return $ret;
}


function set_error_die_function ($function_name)
{
	if ($function_name and function_exists($function_name))
	{
		$GLOBALS['error_die_function'] = $function_name;
	}
}

function get_error_die_function ()
{
	$ret = '';
	if ($GLOBALS['error_die_function'] and function_exists($GLOBALS['error_die_function'])) $ret = $GLOBALS['error_die_function'];
	return $ret;
}

/**
* Custom error handler. Currently implemented only for kawacs.php entry point.
* It traps all errors, logs them to error log, raises them through error_msg(). For critical
* errors, it also stops the execution.
* If a custom exit function has been defined with set_error_die_function(), then that
* function will be called prior to exiting.
* @param	int		$errno		The PHP error number
* @param	string		$errmsg		The error message
* @param	string		$filename	The name of the file in which the error occured
* @param	int		$linenum	The line number at which the error occured
* @param 	array		$vars		The error context
*/
function ks_error_handler ($errno, $errmsg, $filename, $linenum, $vars)
{
	// User-friendly names for the PHP error numbers
	$error_types = array (
		E_ERROR			=> 'Error',
		E_WARNING		=> 'Warning',
		E_PARSE			=> 'Parsing Error',
		E_NOTICE		=> 'Notice',
		E_CORE_ERROR		=> 'Core Error',
		E_CORE_WARNING		=> 'Core Warning',
		E_COMPILE_ERROR		=> 'Compile Error',
		E_COMPILE_WARNING	=> 'Compile Warning',
		E_USER_ERROR		=> 'User Error',
		E_USER_WARNING		=> 'User Warning',
		E_USER_NOTICE		=> 'User Notice',
		E_STRICT		=> 'Runtime Notice'
	);

	// 0: Error, 1: Warning, 2: Trace; -1: (no logging)
	$error_levels = array (
		E_ERROR			=> LOG_LEVEL_ERRORS,
		E_WARNING		=> LOG_LEVEL_ERRORS,
		E_PARSE			=> LOG_LEVEL_ERRORS,
		E_NOTICE		=> -1,
		E_CORE_ERROR		=> LOG_LEVEL_ERRORS,
		E_CORE_WARNING		=> LOG_LEVEL_ERRORS,
		E_COMPILE_ERROR		=> LOG_LEVEL_ERRORS,
		E_COMPILE_WARNING	=> LOG_LEVEL_ERRORS,
		E_USER_ERROR		=> LOG_LEVEL_ERRORS,
		E_USER_WARNING		=> LOG_LEVEL_ERRORS,
		E_USER_NOTICE		=> -1,
		//E_STRICT		=> LOG_LEVEL_ERRORS
		E_STRICT			=> -1
	);

	// The erorrs on which the system should die
	$die_on_errors = array (E_ERROR, E_WARNING, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, E_USER_ERROR, E_USER_WARNING, /*E_STRICT*/);

	if (isset($error_levels[$errno]) and $error_levels[$errno] >= 0)
	{
		$msg = '['.$error_types[$errno].'] '.$errmsg.'; in '.$filename.', line '.$linenum;

		//we  must assure compatibility with php5,
		//php5 will complain that var is deprecated, and one should use access specificators when declaring class
		//variable members and this function will die
		if (function_exists('debug_backtrace') and
                        !preg_match ('/fsockopen.*unable to connect to/', $errmsg) and
                        !preg_match('/eprecated/',$errmsg))
                        
		{

			$trace = debug_backtrace();
			$msg.= "\n".'  CALL TRACE: ';
			for ($i=count($trace)-1; $i>=0; $i--)
			{
				$msg.= "\n    ".$trace[$i]['class'].$trace[$i]['type'].$trace[$i]['function'].' ';
				$msg.= '('.$trace[$i]['line'].': '.$trace[$i]['file'].')';
			}
		}

		if (mysql_error())
		{
			$msg.= "\n".'  MySQL ERROR: '.mysql_error ();
		}

		do_log ($msg, $error_levels[$errno]);
		error_msg ('Server error ['.date('d-m-y H:i:s').']['.$error_types[$errno].'] in '.$filename.', please check server error log.');

		// Check if the system should die and see if a gracefully die function has been set
		// Fsockopen errors do not cause the execution to stop
		// nor the var: deprecated warnings
		if (in_array ($errno, $die_on_errors) and !preg_match ('/fsockopen.*unable to connect to/', $errmsg) and !preg_match('/eprecated/',$errmsg))
		{
			$die_func = get_error_die_function ();
			if ($die_func) $die_func ();
			exit (1);
		}
	}
}


function make_word_ml ($xml = '', $xsl_file = '', $out_name = 'report', $return_file = false)
{
	$ret = false;

	$title = $out_name;
	$tmp_file_prefix = dirname(__FILE__).'/tmpKEYOS_';

	$xsl_file = dirname(__FILE__).'/../templates/'.$xsl_file;
	$del_xml = true;
	$del_xsl = false;

	if ($xml and file_exists(XSLTPROC))
	{
		// Put the XML data into a temporary file for processing
		$xml_file = tempnam(KEYOS_TEMP_FILE, $tmp_file_prefix."XML_");
		$fw = fopen($xml_file, 'w');
		if ($fw)
		{
			fwrite($fw, $xml);
			fclose($fw);
		}

		// Use the XSLT processor to generate the WordML file
		if ($xml_file and $xsl_file and file_exists($xml_file) and file_exists($xsl_file))
		{
			$dest_path = tempnam(KEYOS_TEMP_FILE, $tmp_file_prefix."WORDML_DEST_");
			@unlink ($dest_path);
			$dest_path.= '.xml';

			$cmd = XSLTPROC . ' -o '.$dest_path.' '.$xsl_file.' '.$xml_file .' 2>&1';
			unset($cmd_res);
			exec($cmd, $cmd_res, $error);

			if (!$error)
			{
				// In the resulting WordML we need to load the BASE64 representation of images
				$dest_path_img = $dest_path.'.imgs';
				$fp = fopen ($dest_path, 'r');
				$fw = fopen ($dest_path_img, 'w');

				while ($s = fgets($fp, 1024))
				{
					if (!preg_match ('/^\<ext_image_url\>/', $s)) fwrite ($fw, $s);
					else
					{
						// Fetch the image, convert it to BASE64 and write it to file
						$url = html_entity_decode(trim(preg_replace ('/\<[^\<]+\>/', '', $s)));
						$img_data = file_get_contents ($url);

						$img_data = base64_encode($img_data);


						fwrite ($fw, $img_data."\n");
					}
				}
				fclose ($fp);
				fclose ($fw);
				@rename ($dest_path_img, $dest_path);

				//<ext_image_url>{graph_url}</ext_image_url>

		exec ('/usr/bin/unix2dos '.$dest_path);
				session_write_close ();
				if (!$return_file)
				{
					// Send the file directly to the browser and stop execution
					$filesize=filesize($dest_path);
					header("Pragma: public");
					header("Expires: 0"); // set expiration time
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Content-Type: application/mso-application");
					header("Content-Length: ".$filesize);
					header("Content-Disposition: attachment; filename=\"".strip_tags($title).".xml\"");
					header("Content-Transfer-Encoding: binary");

					readfile($dest_path);

					if ($del_xml) @unlink($xml_file);
					if ($del_xsl) @unlink($xsl_file);
					@unlink($dest_path);

					// Exit now, to be sure there is nothing else sent to the browser
					exit;
				}
				else
				{
					// Cleanup and return the PDF file name
					if ($del_xml) @unlink($xml_file);
					if ($del_xsl) @unlink($xsl_file);
					return $dest_path;
				}
			}
			else
			{
				// There was an error in creating the PDF file

				//$error_msg = 'Report generation failed: '.$cmd.':<br>';
				$error_msg = 'Report generation failed:<br>';
				foreach ($cmd_res as $line) $error_msg.= '&nbsp;&nbsp;' . htmlentities($line) . '<br>';
				error_msg ($error_msg);
			}
		}
		else error_msg ('Error in generating report, not all the files were found.');
	}
	elseif (!file_exists(XSLTPROC))
	{
		error_msg ('The XSLT processor could not be located.');
	}
	else
	{
		error_msg ('No XML data was specified');
	}
	return $ret;
}


/** PDF generation - this is only a temporaty solution
* @param	string		$xml			The XML data
* @param	string		$xsl_file		The name of the XSL-FO file
* @param	string		$out_name		The name to use for the file, if it will be sent to the browser
* @param	bool		$return_file		If False, the function will send the file directly to the browser
*							If True, the function will return the name of the generated PDF file,
*							instead of sending it to the browser
*/
function make_pdf_xml ($xml = '', $xsl_file = '', $out_name = 'report', $return_file = false)
{
	$title = $out_name;
	$tmp_file_prefix = dirname(__FILE__).'/tmpKEYOS_';

	$xsl_file = dirname(__FILE__).'/../templates/'.$xsl_file;

	$del_xml = true;
	$del_xsl = false;

	if ($xml)
	{
		/*
		$xml = str_replace('&nbsp;', ' ', $xml);
		$xml = str_replace('&', '&amp;', $xml);
		//$xml = str_replace('<-', '&lt;-', $xml);
		//$xml = str_replace('->', '-&gt;', $xml);
		$xml = str_replace('<*', '&lt;*', $xml);
		$xml = str_replace('*>', '*&gt;', $xml);
//		$xml = str_replace('"', '\'', $xml);
		$xml = str_replace('&amp;#', '&#', $xml);
		$xml = eregi_replace("&amp;([a-z])[a-z0-9]{3,};", "\\1", $xml);
		$xml = ereg_replace("<([-a-z0-9.!#$%&\'*+/=?^_{|}~]+)@([.a-zA-Z0-9_/-]+)*>","&lt;\\1@\\2&gt;",$xml);
		//eregi_replace("<([a-z0-9._])@([a-z0-9.])>", "&lt;\\1&gt;", $xml);
		$xml = ereg_replace("<!--([-a-z0-9.!#$%&\'*+/=?^_{|}~]\s+)--([-a-z0-9.!#$%&\'*+/=?^_{|}~\s]+)-->", "\\1-\\2",$xml);
		//debug($xml);
		*/
		$xml = ereg_replace("<([-a-z0-9.!#$%&\'*+/=?^_{|}~]+)@([.a-zA-Z0-9_/-]+)*>","&lt;\\1@\\2&gt;",$xml);
		$xml = str_replace('<br>', '<br/>', $xml);
		$xml = iconv("ISO-8859-1", "ISO-8859-1//IGNORE", $xml);
		if (is_array($xml))
		{
		}
		else
		{
			// A single XML file was passed
			//$xml_file = tempnam("", $tmp_file_prefix."XML_");
			$xml_file = tempnam(KEYOS_TEMP_FILE, $tmp_file_prefix.'XML_');
			$xml_file.=".xml";
			$fw = fopen($xml_file, 'w');
			if ($fw)
			{
				fwrite($fw, $xml);
				fclose($fw);
				$del_xml = true;
			}
		}
	}
	/*
	if ($xsl)
	{
		$xsl_file = tempnam('', $this->tmp_file_prefix."XSL_");
		$fw = fopen($xsl_file, 'w');
		if ($fw)
		{
			fwrite($fw, $xsl);
			fclose($fw);
			$del_xsl = true;
		}
	}
	*/

	if ($xml_file and $xsl_file and file_exists($xml_file) and file_exists($xsl_file))
	{
		//if(file_exists($xml_file)) echo "am gasit fisierul: ".$xml_file." cu dimensiunea: ".filesize($xml_file);
		$dest_path = tempnam(KEYOS_TEMP_FILE, $tmp_file_prefix.'PDF_DEST_');
		@unlink ($dest_path);
		$dest_path.= '.pdf';

		$cmd_pdf = ' export JAVA_HOME='.JAVA_HOME.'; ';
		$cmd_pdf.= FOP_PARSER." ";
		$cmd_pdf.= " -xml {$xml_file} ";
		//$cmd_pdf.= " -xsl {$xsl_file} ";
		$cmd_pdf.= ' -xsl '.ereg_replace(' ','\ ',$xsl_file).' ';
		$cmd_pdf.= " -pdf {$dest_path} ";
		$cmd_pdf.= " 2>&1";

		//debug($cmd_pdf);

		unset($cmd_res);
		exec($cmd_pdf, $cmd_res, $error);

		//debug("error: ".$error);

		if (!$error)
		{
			if (!$return_file)
			{
				// Send the file directly to the browser and stop execution
				$filesize=filesize($dest_path);
				header("Pragma: public");
				header("Expires: 0"); // set expiration time
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/pdf");
				header("Content-Length: ".$filesize);
				header("Content-Disposition: attachment; filename=\"".strip_tags($title).".pdf\"");
				header("Content-Transfer-Encoding: binary");

				readfile($dest_path);

				if ($del_xml) @unlink($xml_file);
				if ($del_xsl) @unlink($xsl_file);
				//@unlink($dest_path);

				// Exit now, to be sure there is nothing else sent to the browser
				exit;
			}
			else
			{
				// Cleanup and return the PDF file name
				if ($del_xml) @unlink($xml_file);
				if ($del_xsl) @unlink($xsl_file);

				//debug($dest_path);

				return $dest_path;
			}
		}
		else
		{
			// There was an error in creating the PDF file
			echo $cmd_pdf;
			debug ($cmd_res);
		}
	}
}

function random_color(){
    mt_srand((double)microtime()*1000000);
    $c = '';
    while(strlen($c)<6){
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return $c;
}


function downloadFile( $fullPath ){ 

  // Must be fresh start 
  if( headers_sent() ) 
    die('Headers Sent'); 

  // Required for some browsers 
  if(ini_get('zlib.output_compression')) 
    ini_set('zlib.output_compression', 'Off'); 

  // File Exists? 
  if( file_exists($fullPath) ){ 
    
    // Parse Info / Get Extension 
    $fsize = filesize($fullPath); 
    $path_parts = pathinfo($fullPath); 
    $ext = strtolower($path_parts["extension"]); 
    
    // Determine Content Type 
    switch ($ext) { 
      case "pdf": $ctype="application/pdf"; break; 
      case "exe": $ctype="application/octet-stream"; break; 
      case "zip": $ctype="application/zip"; break; 
      case "doc": $ctype="application/msword"; break; 
      case "docx": $ctype="application/msword"; break; 
      case "xls": $ctype="application/vnd.ms-excel"; break; 
      case "xlsx": $ctype="application/vnd.ms-excel"; break; 
      case "ppt": $ctype="application/vnd.ms-powerpoint"; break; 
      case "gif": $ctype="image/gif"; break; 
      case "png": $ctype="image/png"; break; 
      case "jpeg": 
      case "jpg": $ctype="image/jpg"; break; 
      default: $ctype="application/force-download"; 
    } 

    header("Pragma: public"); // required 
    header("Expires: 0"); 
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
    header("Cache-Control: private",false); // required for certain browsers 
    header("Content-Type: $ctype"); 
    header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" ); 
    header("Content-Transfer-Encoding: binary"); 
    header("Content-Length: ".$fsize); 
    ob_clean(); 
    flush(); 
    readfile( $fullPath ); 

  } else 
    die('File Not Found'); 

}

/**
 * Function for parsing nice urls like : /cl/op/id-[some-text-for-seo].html
 * @return     array            With cl, op, [id], [params]
 */
function do_parse_url() {
    $ret = array();
    if (isset($_SERVER['PATH_INFO'])) {
        $url = $_SERVER['PATH_INFO'];
        $url = trim($url, '/');
        $url = preg_replace('/([\/]+)/', '/', $url);

        $url = explode('/', $url);
        $i = 0;
        if (isset($url[$i])) {
            $ret['cl'] = $url[$i];
            $i++;
        }
        if ($i > 0 and isset($url[$i])) {
            $params = explode(':', $url[$i]);
            if (count($params) > 1) {
                $ret['op'] = '';
                if (count($params) > 2) {
                    for ($index = 2; $index < count($params); $index++) {
                        $params[1] .= ':' . $params[$index];
                    }
                }
                if (($params[0] == 'goto') OR ($params[0] == 'return')) {
                    $params[1] = str_replace('+', '/', $params[1]);
                }
                $ret[$params[0]] = $params[1];
                $i++;
            } else {
                $ret['op'] = $url[$i];
                $i++;
            }
        }
        if ($i > 1 and isset($url[$i])) {
            $index = $i;
            for ($i = $index; $i < count($url); $i++) {
                $params = explode(':', $url[$i]);
                if (count($params) > 1) {
                    if (count($params) > 2) {
                        for ($index = 2; $index < count($params); $index++) {
                            $params[1] .= ':' . $params[$index];
                        }
                    }
                    if (($params[0] == 'goto') OR ($params[0] == 'return')) {
                        $params[1] = str_replace('+', '/', $params[1]);
                    }
                    $ret[$params[0]] = $params[1];
                } else {
                    $ret['id'] = $url[$i];
                }
            }
        }
    }
    if (isset($_SERVER['QUERY_STRING'])) {
        $ret = array_merge($ret, $_GET);
        if (isset($ret['goto'])) {
            $ret['goto'] = str_replace('+', '/', $ret['goto']);
        }
        if (isset($ret['return'])) {
            $ret['return'] = str_replace('+', '/', $ret['return']);
        }
    }
    if (!empty($_POST)) {
        $ret = array_merge($ret, $_POST);
        if (isset($ret['goto'])) {
            $ret['goto'] = urldecode($ret['goto']);
            $ret['goto'] = str_replace('+', '/', $ret['goto']);
        }
        if (isset($ret['return'])) {
            $ret['return'] = urldecode($ret['return']);
            $ret['return'] = str_replace('+', '/', $ret['return']);
        }
    }
    if (!isset($ret['cl'])) {
        $ret['cl'] = DEFAULT_CLASS;
    }
    if (!isset($ret['op'])) {
        $ret['op'] = ''; // Empty for default operations
    }
    if (isset($ret['id'])) {
        $ret['id'] = str2int($ret['id'], 'end'); //intval($url[$i]);
    }
    return $ret;
}

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}
function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}


?>

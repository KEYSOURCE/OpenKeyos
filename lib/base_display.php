<?php

/**
* Base class for displaying system pages
*
* While the class can be used on its own, its main use is to be inherited by any 
* other class which needs to handle display operations
*
*/
require_once __DIR__ . "/cache_wrapper.php";

class BaseDisplay extends Smarty
{
	/** Stores various value relevant to the module, e.g. the URL attributes
	* @var	array */
	var $vars = array();
    var $cacheObj = null;
    var $use_caching = false;
    var $do_not_cache_ops = array();

	/**
	* Class constructor. Initializes the Smarty engine and loads the URL attrbutes
	*/
	function BaseDisplay()
	{
        /*
		$this->Smarty($resource_name);
		
		$this->caching = false;
		$this->force_compile = true;
		
		$this->template_dir = SMARTY_TEMPLATE_DIR;                
		$this->compile_dir = SMARTY_COMPILE_DIR;
		$this->config_dir = SMARTY_CONFIG_DIR;
		$this->cache_dir = SMARTY_CACHE_DIR;
        */

        parent::__construct();

        if(USE_CACHING) {
            $this->cacheObj =  CacheWrapper::getInstance(CACHE_ENGINE, array('server' => CACHE_DEFAULT_SERVER, 'port' => CACHE_DEFAULT_PORT));
            $this->use_caching = true;
        }

        $this->caching = false;
        $this->force_compile = false; //set to false on prod
        $this->compile_check = true;
        $this->error_reporting = 1; // 1 - only erros | 0 - no errors
        $this->allow_php_tag = 1;

        $this->template_dir = SMARTY_TEMPLATE_DIR;
        $this->compile_dir = SMARTY_COMPILE_DIR;
        $this->config_dir = SMARTY_CONFIG_DIR;
        $this->cache_dir = SMARTY_CACHE_DIR;

		// Get rid of magic_quotes
        $this->do_not_cache_ops[] = 'login';
        $this->do_not_cache_ops[] = 'logout';

        $this->vars = $_REQUEST;
        if (is_array ($this->vars))
		{
			foreach ($this->vars as $key=>$val)
			{
				if (is_array($val))
				{
					foreach ($val as $key1=>$val1)
					{
						if (is_array ($val1))
						{
							foreach ($val1 as $key2=>$val2)
							{
								if (!is_array($val2))
								{
									$this->vars[$key][$key1][$key2] = stripslashes ($val2);
								}
							}
						}
						else
						{
							$this->vars[$key][$key1] = stripslashes ($val1);
						}
					}
				}
				else
				{
					$this->vars[$key] = stripslashes ($val);
				}
			}
		}

		// Set and load the currently logged in user, if any.
		$uid = get_uid();
		if ($uid)
		{
			$user = new User($uid);
			$this->current_user = $user;
			
			$GLOBALS['CURRENT_USER'] = $user;
			
			if (!$this->current_user->is_customer_user ())
			{
				// Load additional statistics for Keyos users
				if ($_SESSION['locked_customer_id'])
				{
					class_load ('Customer');
					$locked_customer = new Customer ($_SESSION['locked_customer_id']);
				}
				else
				{
					$locked_customer = null;
				}
				$this->assign ('locked_customer', $locked_customer);
				$this->locked_customer = $locked_customer;
				
				// Check for unread notifications
				class_load ('NotificationRecipient');
				$this->cnt_unread_notifications = NotificationRecipient::get_unread_notifs_count ($this->current_user->id);
			}
			else
			{
				// Set the customer of this customer user
				class_load ('Customer');
				$user_customers = $this->current_user->get_users_customer_list(); 			
				//if(count($user_customers) == 0) $user_customers[0] = $this->current_user->customer_id;
				if(is_array($user_customers) and empty($user_customers)) $user_customers[0] = $this->current_user->customer_id;
                                if(!is_array($user_customers)){
                                    $user_customers = array();
                                    $user_customers[0] = $this->current_user->customer_id;
                                }
                                $user_customer = array();                             
				foreach($user_customers as $uc)
				{                                            
                                            $user_customer[] = new Customer($uc);                                            
				}
				//$this->assign ('user_customer', new Customer ($this->current_user->customer_id));                                
				$this->assign ('user_customer', $user_customer);
			}
		}
		
		// Set the language to be used, if it was not previously set
		// $_SESSION['USER_LANG'] can also be changed by manual selection, and in the
		// page with saving personal details, if the user selects another language
		if (!isset($_SESSION['USER_LANG']))
		{

			if (isset($this->current_user) and  $this->current_user->id and $this->current_user->language) $_SESSION['USER_LANG'] = $this->current_user->language;
			else
			{
				if (preg_match('/^fr/', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) $_SESSION['USER_LANG'] = LANG_FR;
				else $_SESSION['USER_LANG'] = LANG_EN;
			}
		}
		
		// Load language-specific constants, if needed
		if ($_SESSION['USER_LANG'] == LANG_FR) require_once (dirname(__FILE__).'/const.fr.php');
		
		$this->assign ('ret_url', urlencode ('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
		$this->assign ('http_base_url', 'http://'.get_base_url());                                
	}


    public function preDispatch(){
        $ret = false;

//        if(substr($this->vars['op'], -6) == "submit") {
//            return false;
//        }

        if(in_array($this->vars['op'], $this->do_not_cache_ops)) return false;
        if($this->vars['op'] != "login_submit" && $this->vars['op'] != "logout_submit"){
            //debug($this->vars['op']);
            check_auth();
        }



        $lang = isset($this->vars['lang']) ? $this->vars['lang'] : $GLOBALS['LANGUAGE_CODES'][LANG_FR];
        $cache_key_hdr = CACHE_KEY_PREFIX . ':' . $this->get_extension ('header.tpl', $lang) . ':' . 'HDR';
        if(isset($this->current_user)) $cache_key_hdr .= ":" . $this->current_user->id;
        $cache_key_ftr = CACHE_KEY_PREFIX . ':' . $this->get_extension ('header.tpl', $lang) . ':' . 'FTR';
        if(isset($this->current_user)) $cache_key_ftr .= ":" . $this->current_user->id;

        $cache_key_content = $this->generateCacheKey();
        if(isset($_SESSION['cache_dirty'])){
            if($this->vars['op'] == $_SESSION['cache_dirty'])
                if($this->cacheObj) $this->cacheObj->delete_key($this->vars['cache_key']);
            unset($_SESSION['cache_dirty']);
        }

        if($this->use_caching and $this->cacheObj->key_exists($cache_key_content)){
            //debug($cache_key_content);
            if($this->cacheObj->key_exists($cache_key_hdr)){
                echo $this->cacheObj->get_cache($cache_key_hdr);
            }
            if($this->cacheObj->key_exists($cache_key_content)){
                echo $this->cacheObj->get_cache($cache_key_content);
                $ret = true;
            }
            if($this->cacheObj->key_exists($cache_key_ftr)){
                echo $this->cacheObj->get_cache($cache_key_ftr);
            }
        }
        return $ret;
    }

    public function postDispatch(){
        if($this->use_caching){
            if(!empty($this->vars['cache_key'])){
                if(isset($_SESSION['cache_dirty'])){
                    //if($this->vars['op'] == $_SESSION['cache_dirty']['op'] . "_submit")
                    if($this->cacheObj) $this->cacheObj->delete_key($this->vars['cache_key']);
                    unset($_SESSION['cache_dirty']);
                }
            }
        }
        return true;
    }


	/** Returns the extension (.e.g. '.fr') to append for a specific language. If the language is EN or
	* if there is no localized version of the template, an empty string is returned */
	function get_extension ($tpl, $lang = null)
	{
		if (!$lang) $lang = $_SESSION['USER_LANG'];
		$lang_code = $GLOBALS['LANGUAGE_CODES'][$lang];
		$lang_ext = '';
		if ($lang_code != 'en')
		{
			$lang_ext = '.'.$lang_code;
			$localize_tpl = SMARTY_TEMPLATE_DIR.$tpl.$lang_ext;
			if (file_exists($localize_tpl)) $tpl = $localize_tpl;
			else $lang_ext = '';
		}
		return $lang_ext;
	}

    function generateCacheKey(){
        if(isset($this->vars['sbm_ckey'])){
            $req = $this->vars['sbm_ckey'];
        }
        else $req = md5(json_encode($_REQUEST));
        $cache_key = CACHE_KEY_PREFIX . ":" . $this->vars['cl'] . ':' . $this->vars['op'] . ':' . $req;
        return $cache_key;
    }

    function clear_request_cache($page_req){
        $_SESSION['CLEAR_NEXT_CACHE'] = TRUE;
        header('Location: ' . $page_req);
        die;
    }

	/**
	* Overloads the Smarty's standard display() method.
	*
	* It loads the data for the current logged in user (if any) and also takes care of displaying the page header and footer
	* 
	* @param	string $tpl	The template to be used for displaying
	*/
	function display($tpl = '', $lang = null, $cache_id=NULL, $compile_id=NULL)
	{

        $cache_key_hdr = CACHE_KEY_PREFIX . ':' . $this->get_extension ('header.tpl', $lang) . ':' . 'HDR';
        if(isset($this->current_user)) $cache_key_hdr .= ":" . $this->current_user->id;
        $cache_key_ftr = CACHE_KEY_PREFIX . ':' . $this->get_extension ('header.tpl', $lang) . ':' . 'FTR';
        if(isset($this->current_user)) $cache_key_ftr .= ":" . $this->current_user->id;
        $cache_key_content = $this->generateCacheKey();
        if(isset($_SESSION['CLEAR_NEXT_CACHE'])) {
            $_SESSION['CLEAR_NEXT_CACHE'] = FALSE;
            if($this->cacheObj) $this->cacheObj->deleteKey($cache_key_hdr);
            if($this->cacheObj) $this->cacheObj->deleteKey($cache_key_content);
            if($this->cacheObj) $this->cacheObj->deleteKey($cache_key_ftr);
        }

		if (empty($tpl))
		{
			if (isset($this->sec))
			{
				// A static page has been requested
				$this->page = ($this->page ? $this->page : 'index');
				$tpl = STATIC_PAGES_DIR.'/'.$this->sec.'/'.$this->page.'.html';
			}
			else
			{
				$tpl = DEFAULT_TEMPLATE;
			}
		}
		
		$lang_ext = $this->get_extension ($tpl, $lang);
		$lang_ext_generic = $this->get_extension ('header.tpl', $lang);

        if (isset($this->current_user))
        {
            $this->assign ('current_user', $this->current_user);
            if (!$this->current_user->is_customer_user ())
            {
                // Load additional statistics for Keyos users
                if(PluginBase::status('computer') == PLUGIN_STATUS_ENABLED){
                    class_load ('Computer');
                    $computer_alerts = Computer::get_computer_alerts_stat ();
                }
                if(PluginBase::status('krifs') == PLUGIN_STATUS_ENABLED){
                    class_load ('Ticket');
                    $user_tickets = Ticket::get_tickets_stats ($this->current_user->id);
                }
                class_load ('Notification');
                $user_notifications = Notification::get_user_notifs_stat ($this->current_user->id);

                if(PluginBase::status('computer') == PLUGIN_STATUS_ENABLED){
                    class_load ('ComputerBlackout');
                    $active_blackouts = ComputerBlackout::get_active_blackouts ();
                }
                if(PluginBase::status('customer') == PLUGIN_STATUS_ENABLED){
                    class_load ('Customer');
                    $suspended_customers_alerts = Customer::get_suspended_customers_alerts_count ();
                }

                $this->assign ('computer_alerts', $computer_alerts);
                $this->assign ('active_blackouts', $active_blackouts);
                $this->assign ('suspended_customers_alerts', $suspended_customers_alerts);
                $this->assign ('user_tickets', $user_tickets);
                $this->assign ('user_notifications', $user_notifications);
                $this->assign ('cnt_unread_notifications', $this->cnt_unread_notifications);
                $this->assign ('ALERT_NAMES', $GLOBALS['ALERT_NAMES']);
                $this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
            }
        }
        $this->assign ('LANGUAGES', $GLOBALS['LANGUAGES']);
        $this->assign ('CURENT_LANG', $_SESSION['USER_LANG']);
        $this->assign ('LANG_EXT', $lang_ext);


        $this->assign ('MAIN_MODULES', json_encode($GLOBALS['MAIN_MODULES']));
        $customer_modules = array();
        foreach ($GLOBALS['MAIN_CUSTOMER_MODULES'] as $module){
            if(!$this->current_user){
                if(isset($module['requires_auth']) && $module['requires_auth']==FALSE){
                    $customer_modules[] = $module;
                }
            } else {
                $customer_modules[] = $module;
            }
        }
        $this->assign ('MAIN_CUSTOMER_MODULES', json_encode($customer_modules));
        $this->assign ('MAIN_CUSTOMER_ADMINISTRATOR_MODULES', json_encode($GLOBALS['MAIN_CUSTOMER_ADMINISTRATOR_MODULES']));

        $this->assign ('MENU', json_encode($GLOBALS['MENU']));
        $this->assign ('MENU_CUSTOMER', json_encode($GLOBALS['MENU_CUSTOMER']));
        $this->assign ('MENU_CUSTOMER_ADMINISTRATOR', json_encode($GLOBALS['MENU_CUSTOMER_ADMINISTRATOR']));

        // Load the page header (which might also include additional menus)
        if($this->cacheObj){
            if($this->use_caching and !in_array($this->vars['op'], $this->do_not_cache_ops)){
                if(!$this->cacheObj->key_exists($cache_key_hdr)) $this->cacheObj->set_cache($cache_key_hdr, $this->fetch('header.tpl'.$lang_ext_generic), CACHE_DEFAULT_TTL);
                if(!$this->cacheObj->key_exists($cache_key_content)) $this->cacheObj->set_cache($cache_key_content, $this->fetch($tpl.$lang_ext), CACHE_DEFAULT_TTL);
                if(!$this->cacheObj->key_exists($cache_key_ftr)) $this->cacheObj->set_cache($cache_key_ftr, $this->fetch('footer.tpl'.$lang_ext_generic), CACHE_DEFAULT_TTL);

            }
        }
//        /echo "not cached";
        parent::display('header.tpl'.$lang_ext_generic);
        // Show the page content
        parent::display ($tpl.$lang_ext);
        // Show the page footer
        parent::display('footer.html'.$lang_ext_generic);

	}
	
	/**
	* Displays only a single template, with minimal header and footer - e.g. for popups
	*/
	function display_template_limited ($tpl = '', $lang = null)
	{
		$lang_ext = $this->get_extension ($tpl, $lang);
		parent::display('header_limited.html'.$lang_ext);
		parent::display($tpl.$lang_ext);
		parent::display('footer_limited.html'.$lang_ext);
	}
	
	/**
	* Displays only the specified template, without headers and footers
	* @param	string $tpl	The template to be used for displaying
	*/
	function display_template_only ($tpl = '')
	{
		parent::display($tpl);
	}
	
	
	/**
	* Creates and inserts in the HTML code the hidden fields for specifying the class and 
	* method which should process the form submission.
	*
	* @param	string $op		The name of the method which should be invoked
	* @param	array $params		Additional attributes which will be added in the form as hidden fields
	* @param	string $cl		The name of the class to be invoked. If not specified, the current class name is used
	* @param	string $var_name	The Smarty template var which will be used for holding the generated fields
	*/
	function set_form_redir($op, $params = array(), $cl = '', $var_name = 'form_redir')
	{

        if(!in_array($this->vars['op'], $this->do_not_cache_ops)){
            $params['cache_key'] = $this->generateCacheKey();
        }
		$this->assign($var_name, $this->mk_form_redir($op, $params, $cl));
	}

    function refresh_page_cache(){
        //if($this->use_caching && $this->cacheObj){
        //   $this->cacheObj->delete_key($this->generateCacheKey());
        //    return $_SERVER['REQUEST_URI'];
        //}
    }
	
	/**
	* Creates in the HTML code the hidden fields for specifying the class and 
	* method which should process the form submission.
	*
	* @param	string $op		The name of the method which should be invoked
	* @param	array $params		Additional attributes which will be added in the form as hidden fields
	* @param	string $cl		The name of the class to be invoked. If not specified, the current class name is used
	*
	* @return	string			The  generated HTML code
	*/
	function mk_form_redir($op, $params = array(), $cl = '')
	{
		$ret = '';
		$ret.= '<input type="hidden" name="cl" value="'.($cl ? $cl : $this->vars['cl']).'">';
		$ret.= '<input type="hidden" name="op" value="'.$op.'">';
		if (is_array($params) and !empty($params))
		{
			foreach ($params as $key => $val)
			{
				if (!is_array($val))
				{
					$ret.= '<input type="hidden" name="'.$key.'" value="'.$val.'">';
				}
				else
				{
					foreach ($val as $key1=>$val1)
					{
						$ret.= '<input type="hidden" name="'.$key.'['.$key1.']" value="'.$val1.'">';
					}
				}
			}
		}
		return $ret;
	}
	
	
	/**
	* Generates an URL to a specific location on the system
	*
	* @param	string $op		It can be either the name of a method in a display class to which to redirect, or a static URL (in which case $op must start with a "/")
	* @param	array $params		Additional attributes to add to the URL (if $op is a method name)
	* @param	string $cl		The name of the class (if $op is a method name)
	* @param	string $protocol	The name of a protocol (HTTP/HTTPS) to use in URL
	*
	* @return	string			The generated URL
	*/
	function mk_redir($op, $params = array(), $cl = '', $protocol = '')
	{
		$ret = '';

        //if(!in_array($op, $this->do_not_cache_ops)){
            if(substr($_REQUEST['op'], -6) == "submit"){
                $pp = array();
                foreach($_REQUEST as $kk=>$vv)
                {
                    if($kk!='sbm_ckey' and $kk!='cache_key') $pp[$kk] = $vv;
                }
                $params['sbm_ckey'] = md5(json_encode($pp));
            }
        //}

		if (preg_match('/^\//', $op))
		{
			// This is a request to go to a specific URL on the site
			$protocol = ($protocol ? $protocol : ($_SERVER['HTTPS']=='on' ? 'https' : 'http'));
			$ret = $protocol.'://'.$_SERVER['HTTP_HOST'].$op;
		}
		else
		{
			// This is request for composing an URL to a specific class and method
            //$_req_class = (!empty($this->vars['cl'])) ? $this->vars['cl'] : $_REQUEST['cl'];
            $_req_class = (!empty($_REQUEST['cl'])) ? $_REQUEST['cl'] : DEFAULT_CLASS;
            if(empty($_req_class)) $_req_class = DEFAULT_CLASS;
			$cl = ($cl ? $cl : $_req_class);
			if (!preg_match('/^plot_/', $op))
				$protocol = ($protocol ? $protocol : ($_SERVER['HTTPS']=='on' ? 'https' : 'http'));
			else
				$protocol = 'http';	//Always fetch images over HTTP, because PDF can't handle HTTPS

			//$ret = $protocol.'://'.get_base_url().'/'.$cl.'/'.$op;


            $ret = get_link($cl, $op, $params);

//			if (is_array($params) and !empty($params))
//			{
//                $first = true;
//				foreach ($params as $key=>$val)
//                {
//                    if($first) { $ret.= '?'.urlencode(trim($key)).'='.urlencode($val); $first = false; }
//                    else $ret.= '&'.urlencode(trim($key)).'='.urlencode($val);
//                }
//			}
        }
		return $ret;
	}
	
	
	function set_carry_fields ($carry_fields = array(), $extra_fields = array ())
	{
		$ret = array ();
		
		foreach ($carry_fields as $field)
		{
			if (isset($this->vars[$field])) $ret[$field] = $this->vars[$field];
		}
		
		foreach ($extra_fields as $field=>$value)
		{
			$ret[$field] = $value;
		}
		
		return $ret;
	}
	
	
	/** Loads the 'strings' attribute with the array of strings defined for this class - if any and if not already loaded */
	function load_strings ($lang = null, $force = false)
	{
		// Load global strings
		/*
		$file = dirname(__FILE__).'/../../'.MODULES_DIR_TEMPLATES.'/strings.ini';
		if (file_exists($file))
		{
			$this->strings = @parse_ini_file($file);
			$this->strings_loaded = true;
		}
		*/
		
		if (!$lang) $lang = $_SESSION['USER_LANG'];
		if (!$lang) $lang = LANG_EN;
		$lang_ext = '.'.$GLOBALS['LANGUAGE_CODES'][$lang];
		
		if (!$this->strings_loaded or $force or ($this->strings_loaded and $this->strings_lang!=$lang))
		{
			// Load the class-specific strings
			$class_name = strtolower(get_class ($this));
			if (isset($GLOBALS['CLASSES_STRINGS_FILES'][$class_name]))
			{
				$file = $GLOBALS['CLASSES_STRINGS_FILES'][$class_name].$lang_ext;
				if (file_exists($file))
				{
					$this->strings = array_merge ($this->strings, @parse_ini_file ($file));
					$this->strings_loaded = true;
					$this->strings_lang = $lang;
				}
			}
		}
	}

	
	/** 
	* Returns a string from $this->strings, optionally replacing parts of it
	* @param	string	$name 			The name (key in $this->strings) of the string to get
	* @param	string	$_any_			You can pass any number of additional strings, which will
	*						replace, in the specified order, any %s markers in the string
	* @return	string				The matched string 
	*/
	function get_string ($name)
	{
		$this->load_strings ();
	
		$ret = '';
		if (isset($this->strings[$name]))
		{
			$ret = $this->strings[$name];
			
			$args = func_get_args();
			if (count($args) > 1)
			{
				// There are extra params to replace in the string
				$patterns = array ();
				for ($i=0; $i<count($args); $i++) $patterns[] = '/\%s/';
				unset ($args[0]);
				$ret = preg_replace ($patterns, $args, $ret, 1);
			}
		}
		
		// Just in case the string is not present, return at least the string's name
		$ret = trim($ret);
		if (!$ret) $ret = $name;
		
		return $ret;
	}
	
	
	/** Updates or sets the counter of unread notifications - only for Keysource users */
	function update_unread_notifs ()
	{
		$this->cnt_unread_notifications = 0;
		if ($this->current_user->id and !$this->current_user->is_customer_user())
		{
			$this->cnt_unread_notifications = NotificationRecipient::get_unread_notifs_count ($this->current_user->id);
		}
	}
}

?>
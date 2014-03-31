<?php
class PluginController extends BaseDisplay{
    protected $plugin_name = '';
    protected $base_plugin_dir = '';
    function __construct() {
        parent::BaseDisplay();
        $this->strings = array();
        $this->load_strings();        
    }
    
    public function display($tpl='', $lang=null,  $cache_id = NULL, $compile_id = NULL){

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
                        $tpl = $GLOBALS['PLUGIN_TEMPLATES'][$this->plugin_name];
                }
        } else {            
            $tpl = $GLOBALS['PLUGIN_TEMPLATES'][$this->plugin_name]."/".$tpl;
        }        

        $this->assign('base_plugin_dir', $this->base_plugin_dir);
        $this->assign('base_plugin_url', BASE_URL.'/plugins/'.$this->plugin_name.'/');
        parent::display ($tpl);

    }
    
    public function display_template_limited ($tpl = '', $lang = null){            
        $tpl = $GLOBALS['PLUGIN_TEMPLATES'][$this->plugin_name]."/".$tpl;
        $this->assign('base_plugin_dir', $this->base_plugin_dir);
        $this->assign('base_plugin_url', BASE_URL.'/plugins/'.$this->plugin_name.'/');
        parent::display_template_limited($tpl);    
    }
    
    public function display_template_only($tpl = '') {
        $tpl = $GLOBALS['PLUGIN_TEMPLATES'][$this->plugin_name]."/".$tpl;
        $this->assign('base_plugin_dir', $this->base_plugin_dir);
        $this->assign('base_plugin_url', BASE_URL.'/plugins/'.$this->plugin_name.'/');
        parent::display_template_only($tpl);
    }
    
    public function load_strings($lang = null, $force = false) {
        if (!$lang) $lang = $_SESSION['USER_LANG'];
        if (!$lang) $lang = LANG_EN;
        $lang_ext = '.'.$GLOBALS['LANGUAGE_CODES'][$lang];                
        if (!$this->strings_loaded or $force or ($this->strings_loaded and $this->strings_lang!=$lang))
        {                
            // Load the class-specific strings
            $class_name = strtolower(get_class ($this));                
            if (isset($GLOBALS['CLASSES_STRINGS_FILES'][$class_name]))
            {                        
                $file = $GLOBALS['CLASSES_STRINGS_FILES'][$class_name];                          
                if (file_exists($file))
                {

                    $this->strings = array_merge($this->strings, @parse_ini_file ($file));
                    $this->strings_loaded = true;
                    $this->strings_lang = $lang;
                }
            }
        }
    }
    
    public function get_string($name){           
        if (!$lang) $lang = $_SESSION['USER_LANG'];
        if (!$lang) $lang = LANG_EN;
        $lang_ext = $GLOBALS['LANGUAGE_CODES'][$lang]; 
        
        $ret = '';        
        if (isset($this->strings[strtoupper($name."@".$lang_ext)]))
        {
                $ret = $this->strings[strtoupper($name."@".$lang_ext)];

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
}
?>

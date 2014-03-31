<?php
class_load("Customer");
/**
 * Class that permits the customer to set their own templates
 * colors, images etc...
 *
 */
class CustomerTemplateStyle extends Base 
{
	/**
	 * Unique id for the templates
	 *
	 * @var int
	 */
	var $id = null;
	
	/**
	 * the id of the customer foe which we set the template
	 *
	 * @var int
	 */
	var $customer_id = null;
	
	/**
	 * default font size
	 *
	 * @var int
	 */
	var $default_font_size = null;
	
	/**
	 * Default background color
	 *
	 * @var string (in fact is an uint like #ffffff)
	 */
	var $default_bg_color = "";
	
	/**
	 * h1, h2, h3, h4 html tags text decoration
	 *
	 * @var string  - possible values are "none" and "underline"
	 */
	var $header_text_decoration = "";
	
	/**
	 * h... border color
	 *
	 * @var string
	 */
	var $header_text_border_color = "";
	
	/**
	 * The color of the header text
	 *
	 * @var string
	 */
	var $header_text_color = "";
	
	/**
	 * the color of the topheader menu
	 *
	 * @var string
	 */
	var $topheader_bg_color = "";
	
	/**
	 * the color of the header menu text
	 *
	 * @var string
	 */
	var $topheader_menu_text_color = "";
	
	/**
	 * the color of the menu text
	 *
	 * @var string
	 */
	var $menu_text_color = "";
	
	/**
	 * table header background color
	 *
	 * @var string
	 */
	var $table_header_bg_color = "";
	
	/**
	 * Table highlight color
	 *
	 * @var string
	 */
	var $table_highlight_bg_color = "";
	
	/**
	 * left menu text color
	 *
	 * @var string
	 */
	var $left_menu_text_color = "";
	
	/**
	 * left menu background color
	 *
	 * @var string
	 */
	var $left_menu_bg_color = "";
	
	/**
	 * tab header text color
	 *
	 * @var string
	 */
	var $tab_header_text_color = "";
	
	var $table = TBL_CUSTOMER_TEMPLATE_STYLE;
	
	var $fields = array('id', 'customer_id', 'default_font_size', 'default_bg_color', 'header_text_decoration', 'header_text_border_color',
						'header_text_color', 'topheader_bg_color', 'topheader_menu_text_color', 'menu_text_color', 'table_header_bg_color',
						'table_highlight_bg_color', 'left_menu_text_color', 'left_menu_bg_color', 'tab_header_text_color');
	
	public function __construct($id = null) {
		if($id!=null)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	function __destruct() {
	
	}
	
	function load_data()
	{
		$ret = false;
		if($this->id)
		{
			parent::load_data();
			if($this->id) $ret = true;
		}
		return $ret;
	}
	
	public static function getByCustomerId($customer_id)
	{		
		$ret = null;
		if($customer_id)
		{
			$query = "select id from ".TBL_CUSTOMER_TEMPLATE_STYLE." where customer_id = ".$customer_id;
			//debug($query);
			$template_id = db::db_fetch_field($query, 'id');
			if($template_id)
			{
				$cust_template = new CustomerTemplateStyle($template_id);
				if($cust_template->id) $ret = $cust_template;
			}
		}
		return $ret;	
	}
	
	public static function getByUserId($user_id)
	{
		$ret = null;
                if($user_id and $user_id>0){
                    $query = "select customer_id from ".TBL_USERS." where id = ".$user_id;
                    //debug($query);
                    $cid = db::db_fetch_field($query, 'customer_id');
                    //debug($cid);
                    return CustomerTemplateStyle::getByCustomerId($cid);
                }
                return $ret;
	}
	
	public function load_defaults()
	{
		$this->default_font_size = 11;
		$this->default_bg_color = "#FFFFFF";
		$this->header_text_decoration = "none";
		$this->header_text_color = "#709D19";
		$this->header_text_border_color = "#709D19";
		$this->topheader_bg_color = "#A6D110";
		$this->topheader_menu_text_color = "#000000";
		$this->menu_text_color = "#FFFFFF";
		$this->table_header_bg_color = "#EEEEEE";
		$this->table_highlight_bg_color = "#F7F7F7";
		$this->left_menu_text_color = "#FFFFFF";
		$this->left_menu_bg_color = "#A6D110";
		$this->tab_header_text_color = "#709D19";
	}
}

?>

<?php
  //ini_set('display_errors', 1);
  require_once(dirname(__FILE__).'/lib/lib.php');

  if(class_load("CustomerTemplateStyle")){

      $id = -1;

      if(isset($_REQUEST['id']) and $id > 0) $id = $_REQUEST['id'];

      $customer_style = CustomerTemplateStyle::getByUserId($id);
  }
  
  if(!$customer_style)
  {
    //load and send the default main style
    $css = file_get_contents("main.css");
    //probably this would be 
    //$css = file_get_contents("../../main.css");
    header("Content-type: text/css");
    print($css);
  }
  else
  {
  
  $photo_file = "/images/logos/logo2.gif";
  $photo_search = "/images/logos/logo2_".$customer_style->customer_id.".gif";
  if(file_exists($photo_search)) $photo_file = $photo_search;
  
  //now we have the style for this customer, create the css dinamically
  $css = "";
  $css .= "/*********************************************/";
  $css .= "/* General styles 			     	*/";
  $css .= "/*********************************************/";

  $css .= "BODY, TABLE, TH, TR, TD, UL, OL, LI, P, FONT, TEXTAREA, DIV, SMALL, PRE, A, BLOCKQUOTE, LABEL {";
  $css .= "	font-family: Verdana, Sans-serif, Arial, Helvetica, Tahoma;";
  $css .= "	font-size: ".$customer_style->default_font_size."px;";
  $css .= "}";
  
  $css .= "body {";
  $css .= "	background: ".$customer_style->default_bg_color.";";
  $css .= "	border-width: 0px; margin: 0px; border-spacing: 0px; padding: 0px;";
  $css .= "}";

  $css .= "pre {";
  $css .= "    font-family: Verdana, Courier-New, Courier, Verdana, Arial;";
  $css .= "    margin: 0px;";
  $css .= "}";

  $css .= "img {margin: 0px; padding: 0px; vertical-align:top; border-width:0px;}";
  
  $css .= "div.logo { ";
  $css .= "margin: 0px;"; 
  $css .= "padding: 0px; ";
  $css .= "vertical-align:top;"; 
  $css .= "border-width:1px solid black;";
  $css .= "width: 240px; ";
  $css .= "height: 58px;";
  $css .= "background-position: center;";
  $css .= "background-repeat: no-repeat;";  
  $css .= "background-image:url('".$photo_file."');";
  $css .= "}";
  
  $css .= "h1 {";
  $css .= "	text-decoration: ".$customer_style->header_text_decoration.";";
  $css .= "	font-family: Helvetica, Verdana, Arial, sans-serif;";
  $css .= "	font-size: 18pt; letter-spacing: 2px;  font-stretch: expanded;";
  $css .= "	margin-bottom: 0px; margin-top: 20px;";
  $css .= "	vertical-align: middle;";
  $css .= "	padding:0px; padding-left: 3px;";
  $css .= "	border-bottom: 1px ".$customer_style->header_text_border_color." solid;";
  $css .= "	display: block; color: ".$customer_style->header_text_color.";";
  $css .= "}";

  $css .= "h2 {";
  $css .= "	text-decoration: ".$customer_style->header_text_decoration.";";
  $css .= "	font-family: Helvetica, Verdana, Arial, sans-serif;";
  $css .= "	font-size: 13pt; letter-spacing: 2px;  font-stretch: expanded;";
  $css .= "	margin-bottom: 0px; margin-top: 20px;";
  $css .= "	vertical-align: middle;";
  $css .= "	padding:0px;";
  $css .= "	border-bottom: 1px ".$customer_style->header_text_border_color." solid;";
  $css .= "	display: block; color: ".$customer_style->header_text_color.";";
  $css .= "}";

  $css .= "h3, h4 {";
  $css .= "	font-size: 10pt; font-weight: 900; margin: 0px; margin-bottom: 5px; margin-top: 5px;";
  $css .= "	color: ".$customer_style->header_text_color.";";
  $css .= "	border-bottom: 1px ".$customer_style->header_text_border_color." solid;";
  $css .= "}";

  $css .= "table {";
  $css .= "	border-collapse: collapse; empty-cells: show; border-spacing: 0px;";	
  $css .= "}";

  $css .= "td{";
  $css .= "	vertical-align: top; border: 0px; padding: 2px;";
  $css .= "	border: 0px;";
  $css .= "}";

  $css .= "input, textarea {";
  $css .= "	border: 1px solid #cccccc; border-style: flat;";
  $css .= "	font-size: 10px; padding: 0px;";
  $css .= "}";
  $css .= "option, select	{font-size: 8pt;}";

  $css .= "input.checkbox{";
  $css .= "vertical-align: top; border: none; padding: 0px; margin: 0px; width: 15px; height: 15px;";
  $css .= "}";

  $css .= "input.radio {";
  $css .= "	border: none; vertical-align: middle; margin:0px; padding: 0px; height: 12px; width: 12px;}";

  $css .= "form {margin: 0px;}";

  $css .= ".error {color: red; font-weight: 800;}";
  $css .= ".warning {color: #EE6622; font-weight: 800;}";
  $css .= ".new {color: green; font-weight: 800;}";

  $css .= ".paging {width: 100%; display: block; padding: 0px; margin-bottom: 15px;}";
  $css .= ".paging a {text-decoration: none; color: #666666;}";

  $css .= "a {color: #0000bb;}";

  $css .= ".light_text {color: #888888;}";

  $css .= "span.comments_block {font-family: Courier, Courier New}";

  $css .= ".border_box {border: 1px solid #cccccc;}";
  $css .= ".border_box_hard {border: 1px solid #999999; padding: 5px; margin-top: 5px; display: block;}";
  $css .= ".border_bottom {border: 0px; border-bottom: 1px solid #cccccc;}";

  $css .= ".unread {background-color: #c8ebff;}";
  $css .= ".unread_text {color: #109cec;}";
  
  $css .= "/*********************************************/";
  $css .= "/* Top header and footer		     	*/";
  $css .= "/*********************************************/";

  $css .= "table.topheader {";
  $css .= "	width: 100%; border-bottom: 1px solid black;";
  $css .= "}";

  $css .= "table.topheader td {";
  $css .= "	padding: 0px;";
  $css .= "	background: ".$customer_style->topheader_bg_color.";";
  $css .= "	vertical-align: bottom;";
  $css .= "}";

  $css .= "table.topheader a {";
  $css .= "	margin: 0px; padding: 5px; padding-right: 20px;";
  $css .= "	display: block; min-width: 60px; width: 100%;";
  $css .= "	height 30px;";
  $css .= "	color: ".$customer_style->topheader_menu_text_color."; font-size: 10px; text-decoration: none;";
  $css .= "	vertical-align: bottom;";
  $css .= "	white-space: nowrap;";
  $css .= "}";
  
  $css .= "table.topheader a:hover {text-decoration: none; color: ".$customer_style->topheader_menu_text_color.";}";
  $css .= "table.topheader a:visited {text-decoration: none; color: ".$customer_style->topheader_menu_text_color.";}";

  $css .= "table.topheader td.menu_separ {";
  $css .= "width: 6px; background: url('/images/menu_separ.gif');";
  $css .= "}";
  
  $css .= "table.topheader td.menu_separ_r {";
  $css .= "	width: 3px; background: url('/images/menu_separ_r.gif');";
  $css .= "}";
  
  $css .= "table.topheader td.menu_separ_l {";
  $css .= "	width: 3px; background: url('/images/menu_separ_l.gif');";
  $css .= "}";

  $css .= "table.topheader td.menu_top_item {";
  $css .= "	width: 80px;";
  $css .= "	background: url('/images/menu_bk.gif') repeat-x top left; overflow: hidden;";
  $css .= "}";

  $css .= "table.footer {";
  $css .= "	border-top: 1px solid black; border-bottom: 1px solid black;";
  $css .= "	width: 100%;";
  $css .= "}";
  
  $css .= "@media print {";
  $css .= "	table.topheader 	{display: none;}";
  $css .= "	table.footer	 	{display: none;}";
  $css .= "}";

  $css .= "div.menu {";
  $css .= "	background: ".$customer_style->topheader_bg_color."; margin: 0px;";
  $css .= "	border: 1px solid black; border-top: none;";
  $css .= "	padding: 0px; margin: 0px; font-weight: 700; spacing: 0px;";
  $css .= "	height: auto; width: auto;";
  $css .= "	position: absolute; visibility: hidden; display: block;";
  $css .= "	border-collapse: collapse;";
  $css .= "	overflow: visible;";
  $css .= "	width: 190px;";
  $css .= " z-index: 10000;";
  $css .= "}";

  $css .= "div.menu a {";
  $css .= "	display: block;"; 
  $css .= "	color: ".$customer_style->menu_text_color.";"; 
  $css .= "	border: none; border-top: 1px solid black;";
  $css .= "	font-weight: 400; text-decoration: none;";	// color: white;
  $css .= "	padding: 3px; padding-left: 10px; padding-right: 5px;";
  $css .= "	border-collapse: collapse;";
  $css .= "	white-space: nowrap;";
  $css .= "	width: @inherit;";
  $css .= "}";

  $css .= "div.menu a:hover {";
  $css .= "	background-color: white; color: black; text-decoration: none;";
  $css .= "}";

  $css .= "a.activated_menu, a.activated_menu:visited {";
  $css .= "	background-color: white; color: black; text-decoration: none;";
  $css .= "}";

  $css .= "div.menusepar {";
  $css .= "	padding: 2px; padding-left: 10px; padding-right: 5px; ";
  $css .= "	margin: 0px; border-top: 1px solid black;";
  $css .= "	color: ".$customer_style->menu_text_color.";";
  $css .= "	width: @inherit;";
  $css .= "}";

  $css .= "/*********************************************/";
  $css .= "/* Table styles 			     	*/";
  $css .= "/*********************************************/";

  $css .= "table.list thead td {";
  $css .= "	font-weight: 800; background-color: ".$customer_style->table_header_bg_color."; ";
  $css .= "	border-top: 1px solid #cccccc;";
  $css .= "}";

  $css .= "thead input 	{font-weight: 800;}";

  $css .= "table.list .head {";
  $css .= "	font-weight: 800; background-color: ".$customer_style->table_header_bg_color."; ";
  $css .= "	border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc; }";

  $css .= ".head input 	{font-weight: 800;}";

  $css .= "table.list td{";
  $css .= "	border-bottom: 1px solid #dddddd; ";
  $css .= "	padding: 2px; padding-right: 6px;";
  $css .= "}";

  $css .= "table.list .cathead {";
  $css .= "	font-weight: 800; background-color: #F6F6F6;";
  $css .= "	border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc; ";
  $css .= "	padding: 6px; padding-left: 15px;";
  $css .= "	color: #666666;";
  $css .= "}";

  $css .= "table.list .main_row {";
  $css .= "	font-weight: 400; background-color: #F6F6F6; ";
  $css .= "	border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc;";
  $css .= "}";

  $css .= "table.list .no_bottom_border td { border-bottom: 0px; }";

  $css .= ".pre_list 	{border: 1px solid #cccccc; border-bottom-width: 0px; width: 100%;}";
  $css .= ".pre_list td	{vertical-align: baseline; white-space: nowrap;}";
  $css .= ".pre_list a	{font-weight: bold;}";
  $css .= ".pre_list h1,h2,h3 {border: 0px; margin: 0px;}";

  $css .= "table.no_borders td {border: 0px;}";

  $css .= "table.list .highlight {background: ".$customer_style->table_highlight_bg_color.";}";
  $css .= "table.list td.post_highlight {padding-left: 20px;}";

  $css .= "table.list_wide thead td {font-weight: 400; swriting-mode: tb-rl; sfilter: flipv fliph; vertical-align: bottom; }";
  $css .= "table.list_wide td {padding: 1px;}";

  $css .= "table.grid td {border:1px solid #cccccc;}";
  $css .= "table.grid_light td {border:1px solid #EEEEEE;}";
  $css .= "table.grid_light thead td {border:1px solid #BBBBBB;}";
  $css .= "table.grid_light td.m {width:5px; height: 1px; text-align:center;}";
  $css .= "table.grid_light td img {width:5px; height:1px;}";
  $css .= "table.grid_light td.mh {background-image:url('/images/hashing.gif');}";
  $css .= "table.grid_light td.my {border-left:2px solid black;}";

  $css .= "table.grid_light td.m1 {width:5px; text-align:center; background-color: #00BB00}";
  $css .= "table.grid_light td.m2 {width:5px; text-align:center; background-color: #666666}";
  $css .= "table.grid_light td.m3 {width:5px; text-align:center; background-color: #DDDDDD}";
  $css .= "table.grid_light td.m4 {width:5px; text-align:center; background-color: #99CC00}";
  $css .= "table.grid_light td.m5 {width:5px; text-align:center; background-color: orange}";
  $css .= "table.grid_light td.m6 {width:5px; text-align:center; background-color: red}";
  
  $css .= "/*********************************************/";
  $css .= "/* Left-side menu			     	*/";
  $css .= "/*********************************************/";

  $css .= "td.left_menu {";
  $css .= "	padding: 0px; vertical-align: top;";
  $css .= "	width: 120px;";
  $css .= "	border: 1px solid #666666; padding: 0px;";
  $css .= "	background: ".$customer_style->left_menu_bg_color.";";
  $css .= "	height: 200px;";
  $css .= "	overflow: hidden;";
  $css .= "}";

  $css .= "td.left_menu .spacer {width: 120px; height: 1px;}";

  $css .= "td.left_menu a.menu_link {";
  $css .= "	display: block; width: 120px;";
  $css .= "	font-weight: 400; text-decoration: none; color: ".$customer_style->left_menu_text_color.";";
  $css .= "	padding: 2px; padding-left: 5px; padding-right: 3px;";
  $css .= "	border-bottom: 1px solid #666666;";
  $css .= "	margin: 0px;";
  $css .= "}";
  
  $css .= "td.left_menu a.menu_link:hover {background: white; color: black;}";
  $css .= ".menu_link_hover {background: white; color: black;}";

  $css .= "td.left_menu div {padding: 10px; font-size: 10px; width: 100%; display: block; margin: 0px}";
  $css .= "td.left_menu div.text {";
  $css .= "	padding-top: 4px; padding-left: 5px; padding-right: 3px; padding-bottom: 9px;";
  $css .= "	margin: 0px; border-bottom: 1px solid #666666;";
  $css .= "	color: ".$customer_style->left_menu_text_color.";";
  $css .= "}";

  $css .= "td.left_menu input {margin: 0px; vertical-align: baseline; padding: 0px; height: 12px;}";

  $css .= "td.left_menu .menu_separ {";
  $css .= "	display: block; ";
  $css .= "	font-weight: 400; text-decoration: none; color: ".$customer_style->left_menu_text_color.";";
  $css .= "	padding: 2px; padding-left: 10px;";
  $css .= "	border-bottom: 1px solid #666666;";
  $css .= "}";

  $css .= "td.left_menu a {";
  $css .= "	color: ".$customer_style->left_menu_text_color."; font-size: 10px;";
  $css .= "}";

  $css .= "td.left_menu td {";
  $css .= "padding: 0px; padding-top: 1px; padding-right: 1px; font-size: 10px;";
  $css .= "color: ".$customer_style->left_menu_text_color.";";
  $css .= "}";

  $css .= "/*********************************************/";
  $css .= "/* Table styles - sorting headers	     	*/";
  $css .= "/*********************************************/";

  $css .= "table.list td.sort {";
  $css .= "	padding-right: 4px; text-align: right; ";
  $css .= "	vertical-align: baseline; width: 11px; white-space: nowrap;";
  $css .= "}";

  $css .= "table.list td.sort_text {";
  $css .= "	padding-right: 5px;"; 
  $css .= "	white-space: nowrap;";
  $css .= "width: 100px;";
  $css .= "}";

  $css .= "table.list td.sort img {";
  $css .= "	width: 11px; height: 6px; vertical-align: baseline; border: none; padding: 0px; margin: 0px; padding-top: 1px;";
  $css .= "}";

  $css .= "table.list td.sort a {";
  $css .= "	margin: 0px; border: none; padding: 0px; display: block;";
  $css .= "	text-decoration: none; color: black;";
  $css .= "}";

  $css .= "table.list td.sort_text a {";
  $css .= "	margin: 0px; border: none; padding: 0px;";
  $css .= "	text-decoration: none; color: black;";
  $css .= "	display: inline;";
  $css .= "	white-space: nowrap;";
  $css .= "}";

  $css .= "table.list td.sort_text img {";
  $css .= "	margin: 0px;";
  $css .= "	border: none; padding: 0px;";
  $css .= "	vertical-align: baseline;";
  $css .= "	display: inline;";
  $css .= "}";
  
  $css .= "/*********************************************/";
  $css .= "/* Styles for tabs			     			*/";
  $css .= "/*********************************************/";

  $css .= ".tab_content {border: 1px solid #999999; margin: 0px; display: block; width: 98%; padding: 5px;}";

  $css .= ".tab_content h2 {margin-top: 10px;}";

  $css .= ".tab_header {margin: 0px;}";

  $css .= ".tab_header td {";
  $css .= "	padding: 0px; vertical-align: bottom;";
  $css .= "	height: 22px; padding-top: 0px;";
  $css .= "}";

  $css .= ".tab_header td a {";
  $css .= "	border: 1px solid #999999; border-bottom: none;";
  $css .= "	white-space: nowrap;";
  $css .= "	height: 22px; width: 100px;";
  $css .= "	padding: 5px; padding-top: 2px; padding-right: 20px;";
  $css .= "	display:block;"; 
  $css .= "	text-decoration: none;"; 
  $css .= "	color: ".$customer_style->tab_header_text_color."; font-size: 9pt; font-weight: bold;";
  $css .= "}";

  $css .= ".tab_header td.tab_inactive {";
  $css .= "	padding-top: 10px;";
  $css .= "}";
  
  $css .= ".tab_header td.tab_inactive a { height: 15px; background-color: #EEEEEE; }";

  $css .= ".task_head {border: 1px solid #CCCCCC; display: block; width:95%; background-color: #EEEEEE; padding: 3px; font-weight: 800; }";
  $css .= ".task_item {border: 1px solid #CCCCCC; display: block; width:95%; border-top: 0px; padding: 3px; padding-bottom: 4px; }";

  $css .= ".snmp_icon { margin: 0px; margin-right:4px; float:left; width:16px; height: 14px; }";
  $css .= ".snmp_descr { ";
  $css .= "	display: none; border: 1px solid #999999; position: absolute; white-space: normal;";
  $css .= "	background-color: white; padding: 5px; padding-bottom: 10px; margin-top: 14px; width: 400px; height: auto;";
  $css .= "}";

  $css .= ".row_hover:hover {background-color: #EEEEEE; }";
  
  $css .= "/*********************************************/";
  $css .= "/* Layout for info pop-ups		     	*/";
  $css .= "/*********************************************/";

  $css .= "div.info_box {border: 1px solid #999999; position: absolute; background-color: white; padding: 5px; margin-top: 10px; width: 400px; max-width:500px;}";


  $css .= "/*********************************************/";
  $css .= "/* Printed layout styles		        */";
  $css .= "/*********************************************/";

  $css .= ".print_only		{display: none;}";

  $css .= "@media print {";
  $css .= "	.no_print	{display: none;}";
  $css .= "	.print_only	{display: block;}";
  $css .= "a	{color: black; text-decoration: none;}";
	  
  $css .= "td.left_menu	{display: none; visibility: hidden;}";
  $css .= "div.menu	{display:none; background-color: transparent; visibility: hidden;}";
  $css .= "iframe		{display:none; background-color: transparent; border: 0px;}"; 
  $css .= "	#DivShim	{display:none; background-color: transparent; border: 0px; visibility: hidden;}";
  $css .= "	#toolbox_menu	{display:none; background-color: transparent; border: 0px;}";
  $css .= "	#toolbox_menu a {display:none; background-color: transparent; border: 0px;}";
  $css .= "	#toolbox_menu div {display:none; visibility: hidden;}";
  $css .= "	#menu_holder	{display:none; visibility: hidden;}";
	  
  $css .= "table.list	{width: 100%;}";
	  
  $css .= ".tab_header	{display: none;}";
  $css .= "}"; 
  
  /* approval console infobox */
  $css .= ".grid_ira{";
  $css .= "width: 100%;";
  $css .= "padding: 5px 0 5px 0;";
  $css .= "}";
    
  $css .= ".grid_ira table{";
  $css .= "width: 100%;";
  $css .= "text-align: left;";
  $css .= "background: #F6F6F6;";
  $css .= "border: 2px solid white;";
  $css .= "border-collapse: collapse;";
  $css .= "}";
  $css .= ".grid_ira th, .grid_ira td{";
  $css .= "text-align: left;";
  $css .= "padding: 2px;";
  $css .= "}";

  $css .= ".grid_ira thead{";
  $css .= "background: #FFFFFF;";
  $css .= "}";
  $css .= ".grid_ira tbody{";
  $css .= "font-size: .8em;";
  $css .= "}";

  $css .= ".grid_ira tbody td{";
  $css .= "vertical-align: top;";
  $css .= "padding: 5px;";
  $css .= "font-size: .8em;";
  $css .= "}";
  $css .= ".grid_ira tbody tr{";
  $css .= "border-bottom: 1px solid white;";
  $css .= "}";
  $css .= ".grid_ira tbody a{";
  $css .= "font-size: .8em;";
  $css .= "}";

  $css .= ".grid_ira tfoot{";
  $css .= "background: #ffffff;";
  $css .= "text-transform: uppercase;";
  $css .= "font-weight: bold;";
  $css .= "font-size: .8em;";
  $css .= "}";

  $css .= ".grid_ira .headlight{";
  $css .= "font-weight: bold;";
  $css .= "font-size: .9em;";
  $css .= "background: white;";
  $css .= "}";
  
  $css .= ".raphael_holder{";
  $css .= "font: 100.01% 'Fontin Sans', Fontin-Sans, 'Myriad Pro', 'Lucida Grande', 'Lucida Sans Unicode', Lucida, Verdana, Helvetica, sans-serif;";
  $css .= "color: #000;";
  $css .= "-moz-border-radius: 10px;";
  $css .= "-webkit-border-radius: 10px;";
  $css .= "-webkit-box-shadow: 0 1px 3px #666;";
  $css .= "background: #eee url(http://raphaeljs.com/images/bg.png);";
  $css .= "width: 640px;";
  $css .= "height: 480px; ";
  $css .= "} ";
  
  header ('Content-type: text/css');
  header ('Content-length: '+strlen($css));
  echo $css;
  die;
  }	
?>

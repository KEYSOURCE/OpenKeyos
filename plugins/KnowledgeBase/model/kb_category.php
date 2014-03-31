<?php
  /**
   *	Class to handle the KB categories 
   **/
  class KBCategory extends Base
  {
    /**
     *  @var int - the id for each category
     *  */
    var $id = null;
    
    /**
     *  @var string - the URI for this resource, it'll be the URL to the page of the category
     *  */
    var $URI = "";
    
    /**
     * @var int - the id of the parent category
     * */
    var $hasParent = 0;
    
    /**
     * @var string - the title to be displayed for this category
     * */
    var $hasTitle = "";
    
    /**
     * @var string - the description for this category
     * */
    var $hasDescription = "";
    
    /**
     *  @var string - keywords to help the search
     *  */
    var $hasKeywords = "";
    
    /**
     * @var int - the Id of the user that created this category registration
     * */
    var $hasAuthor = null;
    
    /**
     *  @var string - URI of the image associated with this category
     *  */
    var $hasImage = "";
    
    /**
     * @var int - unix time stamp for the creation moment
     * */
    var $wasCreatedOn = null;
    
    /**
     * @var array(int) - list of all the subcategories ids
     * */
    var $subcategories = array();
    /**
     * @var array(int) - list of all theb articles ids
     * */
    var $articles = array();
    
    var $cnt_subcats = 0;
    var $cnt_articles = 0;
    
    var $fields = array('id', 'URI', 'hasParent', 'hasTitle', 'hasDescription', 'hasKeywords', 'hasAuthor', 'hasImage', 'wasCreatedOn');
    
    var $table = TBL_KB_CATEGORIES;
    
    function KBCategory($id=null)
    {
      if($id)
      {
        $this->id = $id;
        $this->load_data();
      }
    }
    
    function load_data()
    {
      parent::load_data();
      if($this->id)
      {
        //load different things in here
        //load the subcategories
        $query = "select id from ".TBL_KB_CATEGORIES." where hasParent=".$this->id;
        $this->subcategories = $this->db_fetch_vector($query);
        $this->cnt_subcats = sizeof($this->subcategories);
	
	$query = "select id from ".TBL_KB_ARTICLES." where category=".$this->id;
	$this->articles = $this->db_fetch_vector($query);
	$this->cnt_articles = sizeof($this->articles);
      }
    }
    
    function is_valid_data()
    {
      $ret = true;
      if(!$this->hasTitle) { error_msg("You must specify a title for the category"); $ret = false;}
      if(!$this->hasDescription) { error_msg("You must add a description for the category"); $ret = false;}
      return $ret;
    }
    
    function save_data()
    {
      //remove all the new lines from the title
      $this->hasTitle = preg_replace ('/\r\n|\n/', ' ', $this->hasTitle);
      parent::save_data();
      
      
	  /*//now we must update the URI of this category
      if($this->id)
        $query = "update ".TBL_KB_CATEGORIES." set URI='./?cl=kb&op=view_category&id=".$this->id."' where id=".$this->id;
      else
      {
        $this->load_data();
        if($this->id)
          $query = "update ".TBL_KB_CATEGORIES." set URI='./?cl=kb&op=view_category&id=".$this->id."' where id=".$this->id;
      }
      */
	 
    }
    
    /**
     *  gets an array with all the subcategories of this category
     *  if no category was found returns an empty array
     *  @return array(KBCategory)
     *  */
    function get_subcategories()
    {
      $ret = array();
      foreach($this->subcategories as $subcategory_id)
      {
        $ret[] = new KBCategory($subcategory_id);
      }
      return $ret;
    }
    
    /**
     *  gets an array with all the articles in this category
     *  if no article is found returns an empty string
     *
     *  @return array(KBArticle)
     **/
    function get_articles()
    {
      $ret = array();
      foreach($this->articles as $article_id)
      {
        $ret[] = new KBArticle($article_id);
      }
      return $ret;
    }
	
	function get_articles_list($parent = 0)
    {
      $ret = array();
      $query = "select id, hasTitle from ".TBL_KB_ARTICLES." where category=".$this->id." and hasParent=".$parent;
	  $ret = $this->db_fetch_list($query);	  
      return $ret;
    }
    
    /**
     *  returns the parent object for this category
     *
     *  @return KBCategory or NULL
     *  */
    function get_parent()
    {
      if($this->hasParent == 0) return NULL;
      $parent = new KBCategory($this->hasParent);
      if($parent->id) return $parent;
      else
        return NULL;
    }
    
    /**
     *  [Class Method] gets an array with all the main categories
     *  The whole tree for each subcategory can be infered from here
     *
     *  @param array() - the search filter
     *  @return array(KBCategory) or empty array if none was found
     *    
     * */
    function get_root_categories($filter = array())
    {
        $ret = array();
        $query = "select id from ".TBL_KB_CATEGORIES." where hasParent = 0";
        $ids = db::db_fetch_vector($query);
        foreach($ids as $id)
        {
          $ret[] = new KBCategory($id);
        }
        return $ret;
    }
	
	function getCategoriesList($parent_category = 0)
	{
		$ret = array();
		$query = "select id, hasTitle from ".TBL_KB_CATEGORIES." where hasParent = ".$parent_category;
		//debug($query);
		$ret = db::db_fetch_list($query);
		return $ret;
	}
    
    /**
     *  [Class Method] gets a list of all the categories
     *
     *  @param array()
     *  @return array()
     **/
    function get_root_categories_list($filter=array())
    {
        $query = "select id, hasTitle from ".TBL_KB_CATEGORIES." where hasParent=0";
        $ret = db::db_fetch_list($query);
        return $ret;
    }
    
    /**
     *  [Class Method] gets all the categories
     *
     *  @return string - an xml to present the categories and subcategories tree
     *  */
    function get_categories_xml($filter = array())
    {
        $dom_xml = new DOMDocument("1.0", "iso-8859-1");
        $root_element = $dom_xml->createElement("categories");
        $dom_xml->appendChild($root_element);
        $roots = KBCategory::get_root_categories();
        if(!empty($roots))
        {
          foreach($roots as $root)
          {
            KBCategory::print_category_xml($root_element, $dom_xml, $root);
          }
        }
        $xml = $dom_xml->saveXML();
        return $xml;
    }
    
    function print_category_xml($parent_node, $dom_xml, $category)
    {
        $cat_elem = $dom_xml->createElement("category");
        $parent_node->appendChild($cat_elem);
        
        $cat_elem->appendChild($dom_xml->createElement("URI", $category->URI));
        $cat_elem->appendChild($dom_xml->createElement("hasParent", $category->hasParent));
        $cat_elem->appendChild($dom_xml->createElement("hasTitle", $category->hasTitle));
        $cat_elem->appendChild($dom_xml->createElement("hasDescription", $category->hasDescription));
        $cat_elem->appendChild($dom_xml->createElement("hasKeywords", $category->hasKeywords));
        $cat_elem->appendChild($dom_xml->createElement("hasAuthor", $category->hasAuthor));
        $cat_elem->appendChild($dom_xml->createElement("hasImage", $category->hasImage));
        $cat_elem->appendChild($dom_xml->createElement("wasCreatedOnh", $category->wasCreatedOn));
        if(!empty($category->subcategories))
        {
          $subcat_elem = $dom_xml->createElement("subcategories");
          $cat_elem->appendChild($subcat_elem);
          $subcats = $category->get_subcategories();
          foreach($subcats as $subcat)
          {
            KBCategory::print_category_xml($subcat_elem, $dom_xml, $subcat);
          }
        }
    }
  }
?>
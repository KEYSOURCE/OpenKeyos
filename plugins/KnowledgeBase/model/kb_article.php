<?php
	class_load("KBArticleSection");
    /**
     * Class to describe a KB article
     **/
    class KBArticle extends Base
    {
      /**
      * @var int
      **/
      var $id = null;
      /**
      * @var string
      **/
      var $URI = "";
      /**
      * @var int
      **/
      var $category = null;
      /**
      * @var int
      **/
      var $hasParent = null;
      /**
      * @var string
      **/
      var $hasTitle = "";
      /**
      * @var string
      **/
      var $hasDescription = "";
      /**
      * @var string
      **/
      var $hasKeywords = "";
      /**
      * @var int
      **/
      var $hasAuthor = null;
      /**
      * @var string
      **/
      var $originalURI = "";
      /**
      * @var int
      **/
      var $wasCreatedOn = null;
      /**
      * @var int
      **/
      var $lastEditedOn = null;
      /**
      * @var int
      **/
      var $lastEditedBy = null;
      
      /**
      *	Object that contains all the sections and the content for this article
      * @var array(KBArticleSection)
      **/
      var $sections = null;
      
      /**
       * List of possible article ids that can resolve this problem. Can be more than one 
       * @var array(int)
       **/
      var $follow_ups = array();
      
      
      var $fields = array("id", "URI", "category", "hasParent", "hasTitle", "hasDescription", "hasAuthor", "hasKeywords", "originalURI", "wasCreatedOn", "lastEditedOn", "lastEditedBy");
      var $table = TBL_KB_ARTICLES;
      
      function KBArticle($id=null)
      {
        if($id)
        {
          $this->id=$id;
          $this->load_data();
        }
      }
      
      function load_data()
      {
        if($this->id)
        {
          parent::load_data();
          $this->load_article_sections();
          $this->load_follow_ups();
        }
      }
      
	  function is_valid_data()
	  {
	  	$ret = true;
	    if(!$this->hasTitle) { error_msg("You must specify a title for the article"); $ret = false;}
	    if(!$this->hasDescription) { error_msg("You must add a description for the article"); $ret = false;}
	    return $ret;
	  }	    
	  
	  function save_data()
	  {
	  	$this->hasTitle = preg_replace ('/\r\n|\n/', ' ', $this->hasTitle);
	    parent::save_data();
	  }
	  
      function load_article_sections()
      {
        $this->sections = KbArticleSection::getSections($this->id);
      }
      
      function load_follow_ups()
      {
        $ret = array();
        $query = "select id from ".TBL_KB_ARTICLES." where hasParent=".$this->id;
        $ret = $this->db_fetch_vector($query);
        $this->follow_ups = $ret;
      }  
	 
	  function isFollwUp()
	  {
	  	if($this->hasParent) return TRUE;
		return FALSE;
	  }
		
	  function hasFollwUps()
	  {
	  	if($this->follow_ups!=null and !empty($this->follow_ups)) return TRUE;
		return FALSE;
	  }
    }
    
?>
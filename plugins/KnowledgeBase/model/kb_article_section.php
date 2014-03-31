<?php

/**
 * class to habdle the different sections of a kb article
 * this is where all the information is kept
 */
class KBArticleSection extends Base
{
	/**
	 * @var id int
	 */
	var $id = null;
	
	/**
	 * @var URI string
	 */
	var $URI = "";
	
	/**
	 * @var article int
	 */
	var $article = null;
	
	/**
	 * @var hasTitle string
	 */
	var $hasTitle = "";
	
	/**
	 * @var hasBody string
	 */
	var $hasBody = "";
	
	var $table = TBL_KB_ARTICLES_SECTIONS;
	var $fields = array('id', 'URI', 'article', 'hasTitle', "hasBody");
	
	function KBArticleSection($id = null)
	{
		if($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	function save_data()
	  {
	  	$this->hasTitle = preg_replace ('/\r\n|\n/', ' ', $this->hasTitle);
		parent::save_data();
	  }
	/**
	 * if the article_id parameter is specified it acts as a class method
	 * otherwise it has to be called as an object method and uses the article member
	 * 
	 * @return array(KbArticleSection) or an empty array if no section was found
	 * @param object $article_id
	 */
	function getSections($article_id = null)
	{
		$ret = array();
		$aid = null;
		if($article_id != null and is_numeric($article_id)) 
			$aid = $article_id;
		else if($this!=null and $this->article != null) 
			$aid = $this->article;
				
		if($aid != null)
		{
			$query = "select id from ".TBL_KB_ARTICLES_SECTIONS." where article=".$aid;
			$ids = db::db_fetch_vector($query);
			
			foreach($ids as $id)
				$ret[] = new KBArticleSection($id);
		}
		return $ret;
	}
	
	function getSectionsList($article_id = null)
	{
		$ret = array();
		$aid = null;
		if($article_id != null and is_numeric($article_id)) 
			$aid = $article_id;
		else if($this!=null and $this->article != null) 
			$aid = $this->article;
				
		if($aid != null)
		{
			$query = "select id, hasTitle from ".TBL_KB_ARTICLES_SECTIONS." where article=".$aid;
			$ret[] = db::db_fetch_list($query);
		}
		return $ret;
	}
}

?>
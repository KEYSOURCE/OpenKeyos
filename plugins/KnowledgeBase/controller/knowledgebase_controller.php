<?php
  class_load('KBCategory');
  class_load('KBArticle');
  class_load('KBArticleSection');
  class_load('User');

  class KnowledgebaseController extends PluginController
  {
      protected $plugin_name = "KnowledgeBase";
      function __construct() {
          $this->base_plugin_dir = dirname(__FILE__).'/../';
          parent::__construct();
      }
    
    //function KnowledgebaseDisplay()
    //{
    //    parent::BaseDisplay ();
    //}
    
    function manage_kb_categories()
    {
        check_auth();
        $tpl = "knowledgebase/manage_kb_categories.html";
        $root_categories = KBCategory::get_root_categories();
	
        $this->assign('cat_xml', $cat_xml);
		$this->assign('root_categories', $root_categories);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('manage_kb_categories_submit');
        $this->display($tpl);
    }
    function manage_kb_categories_submit()
    {
                
    }
    
    function view_category()
    {
        check_auth(array('id' => $this->vars['id']));        
        
        $tpl = "knowledgebase/view_category.html";
        $kb_cat = new KBCategory($this->vars['id']);
        if(!isset($kb_cat) or !$kb_cat->id) return $this->mk_redir('manage_kb_categories');
        
        $subcategories = $kb_cat->get_subcategories();
        $articles = $kb_cat->get_articles();
        $parent = $kb_cat->get_parent();
        $users_list = User::get_users_list();
        
        $this->assign('error_msg', error_msg());
        $this->assign('kb_cat', $kb_cat);
        $this->assign('subcategories', $subcategories);
        $this->assign('articles', $articles);
        $this->assign('parent', $parent);
        $this->assign('users_list', $users_list);
        $this->set_form_redir('view_category_submit', array('id'=>$kb_cat->id));
        $this->display($tpl);
    }
    function view_category_submit()
    {
        
    }
    
    function add_category()
    {
        check_auth();
        $tpl = "knowledgebase/add_category.html";
        
		if($this->vars['pid']) 
			$category = new KBCategory($this->vars["pid"]);
		if($category->id) 
			$categories_list = KBCategory::getCategoriesList($category->id);
		else
			$categories_list = KBCategory::getCategoriesList();
		
		$saved_data = array();
		//if(isset($_SESSION['kb_add_category']))
		//	$saved_data = $_SESSION['kb_add_category'];
		
		unset($_SESSION['kb_add_category']);
		if($category->id)
			$this->assign('category', $category);
		$this->assign('categories_list', $categories_list);
		$this->assign('saved_data', $saved_data);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('add_category_submit');
        $this->display($tpl);
    }
    function add_category_submit()
    {
    	check_auth();
        $ret = $this->mk_redir('manage_kb_categories');
		if($this->vars['save'])
		{
			//here we save the new category
			if(isset($_SESSION['kb_add_category'])) unset($_SESSION['kb_add_category']);
			$_SESSION['kb_add_category'] = $this->vars['kbcat'];
			$new_category = new KBCategory();
			$categ_data = $this->vars['kbcat'];
			if($categ_data['hasParent'] == -1) $categ_data['hasParent'] = 0;
			$categ_data['hasParent'] = $this->vars['kbCatParent'];
			$categ_data['hasAuthor'] = $this->current_user->id;
			$categ_data['wasCreatedOn'] = time();
			$new_category->load_from_array($categ_data);
			if($new_category->is_valid_data())
			{
				$new_category->save_data();
				$new_category->URI = "./?cl=kb&op=view_category&id=".$new_category->id;
				//debug("new category _id: ".$new_category->id);
				if($_FILES['photo_file']['name'])
				{
					$tp = basename($_FILES['photo_file']['name']);
					$file_ext = strtolower(substr($tp,strrpos($tp,".")));
					
					if(!empty_error_msg()) error_msg();
					
					$new_dest = 'images/kb/catim_'.$new_category->id.$file_ext;
					if(file_exists($new_dest)) @unlink($new_dest);
					if(!move_uploaded_file($_FILES['photo_file']['tmp_name'], $new_dest)) 
					{
						error_msg("There was an error while uploading the file");	
						return $ret;
					}				
					$new_category->hasImage = $new_dest;						
				}
				$new_category->save_data();
			}
				
			$ret = $this->mk_redir('add_category');
		}
		return $ret;
    }
	
	function view_article()
	{
		check_auth(array('cat_id'=>$this->vars['cat_id'],'id'=>$this->vars['id']));
		$tpl = "knowledgebase/view_article.html";
		
		$category = new KBCategory($this->vars['cat_id']);
		if(!$category->id) $this->mk_redir('manage_kb_categories');
		
		$article = new KBArticle($this->vars['id']);
		if(!$article->id) return $this->mk_redir('view_category', array('id'=>$category->id));
		
		
		$this->assign('article', $article);
		$this->assign('error_msg', error_msg());
		$this->set_form_redir('view_article_submit', array("id" => $this->vars['id']));
		$this->display($tpl);
	}
	function view_article_submit()
	{
		
	}
	
	function add_article()
	{
		check_auth(array('catid'=>$this->vars['catid']));
        $tpl = "knowledgebase/add_article.html";
		
		$category = new KBCategory($this->vars['catid']);
		if(!$category->id) return $this->mk_redir('manage_kb_categories');
		
		$articles_list = array();
		if($this->vars['pid'])
		{
			$parent_article = new KBArticle($this->vars['pid']);
			if($parent_article->id)
			{
				$articles_list = $category->get_articles_list($parent_article->id);
				$this->assign('parent_article', $parent_article);
			}
			else	
				$articles_list = $category->get_articles_list();
		}		
		$articles_list = $category->get_articles_list();
		
		$this->assign('articles_list', $articles_list);
		$this->assign('category', $category);
		$this->assign('error_msg', error_msg());
		$this->set_form_redir('add_article_submit', array('catid'=>$category->id));
		$this->display($tpl);
	}
	function add_article_submit()
	{
		check_auth(array('catid'=>$this->vars['catid']));
		$ret = $this->mk_redir('view_category', array('id'=>$this->vars['catid']));
		if($this->vars['save'])
		{
			//here we save the new category
			if(isset($_SESSION['kb_add_article'])) unset($_SESSION['kb_add_article']);
			$_SESSION['kb_add_article'] = $this->vars['kbart'];
			$new_article = new KBArticle();
			$article_data = $this->vars['kbart'];
			if($article_data['hasParent'] == -1) $article_data['hasParent'] = 0;
			$article_data['hasParent'] = $this->vars['kbArtParent'];
			$article_data['hasAuthor'] = $this->current_user->id;
			$article_data['wasCreatedOn'] = time();
			$article_data['lastEditedOn'] = time();
			$article_data['lastEditedBy'] = $this->current_user->id;
			$article_data['category'] = $this->vars['catid'];
			$new_article->load_from_array($article_data);
			if($new_article->is_valid_data())
			{
				$new_article->save_data();
				$new_article->URI = "./?cl=kb&op=view_article&cat_id=".$this->vars["catid"]."&id=".$new_article->id;
				$new_article->save_data();	
				$ret = $this->mk_redir('edit_article', array('id'=>$new_article->id));
			}
			else
				$ret = $this->mk_redir('add_article');			
		}
		return $ret;
	}	
	
	function edit_article()
	{
		check_auth(array('id'=>$this->vars['id']));
		$tpl = "knowledgebase/edit_article.html";
		
		$article = new KBArticle($this->vars['id']);
		if(!$article->id) return $this->mk_redir('manage_kb_articles');
		$category = new KBCategory($article->category);
		if(!$category->id)
		{
			//I should never get in this place, 
			//the only posibility to get here is a bad article added by hand in the database
			return $this->mk_redir('manage_kb_categories');
		}
		
		
		$this->assign('article', $article);
		$this->assign('category', $category);
		$this->assign('error_msg', error_msg());
		$this->set_form_redir('edit_article_submit', array('id' => $article->id, 'cat_id' => $category->id));
		$this->display($tpl);
	}
	
	function edit_article_submit()
	{
		check_auth(array('id'=>$this->vars['id'], 'cat_id' => $this->vars['cat_id']));
		$article = new KBArticle($this->vars['id']);
		if(!$article->id) $ret = $this->mk_redir('manage_kb_articles');
		$category = new KBCategory($article->category);
		if(!$category->id) $ret = $this->mk_redir('manage_kb_categories');
		$ret = $this->mk_redir('view_article', array('id' => $article->id, 'cat_id' => $category->id));
		if($this->vars['save'])
		{			
			if(isset($_SESSION['kb_edit_article'])) unset($_SESSION['kb_edit_article']);
			$_SESSION['kb_edit_article'] = $this->vars['kbart_sect'];
			$new_article_section = new KBArticleSection();
			$article_section_data = $this->vars['kbart_sect'];
			//if($article_data['hasParent'] == -1) $article_data['hasParent'] = 0;
			$article_section_data['article'] = $article->id;
			$article_section_data['URI'] = "./?cl=kb&op=view_article&id={$article->id}&cat_id={$category->id}";
			$new_article_section->load_from_array($article_section_data);
			$new_article_section->save_data();
		}
		return $ret;
		
	}
  }
?>
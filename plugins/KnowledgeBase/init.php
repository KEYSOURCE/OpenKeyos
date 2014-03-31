<?php

$plugin_init = array(
    'MODELS' => array(
        'KBArticle' => dirname(__FILE__).'/model/kb_article.php',
        'KBArticleSection' => dirname(__FILE__).'/model/kb_article_section.php',
        'KBCategory' => dirname(__FILE__).'/model/kb_category.php',
    ),
    'CONTROLLERS' => array(
        'knowledgebase' => array(
            'class' => 'KnowledgebaseController',
            'friendly_name' => 'KnowledgebaseController',
            'file'  => dirname(__FILE__).'/controller/knowledgebase_controller.php',
            'default_method' => 'manage_kb_categories',
            'requires_acl' => True
        )
    ),
    'VIEWS' => dirname(__FILE__).'/views',
    'STRINGS' => array(
        'KnowledgebaseController' => dirname(__FILE__).'/strings/knowledgebase.ini'
    ),
    'IS_MAIN_MODULE' => TRUE,
    'MAIN_MENU_MODULE' => array(
        'name' => 'knowledgebase',
        'display_name' => 'KnowledgeBase',
        'uri' => get_link('knowledgebase'),
    ),
    'MENU' => array(
        'manage_kb_categories' => array(
            'module' => 'knowledgebase',
            'submenu_of' => 'knowledgebase',
            'name' => 'manage_kb_cateogries',
            'display_name' => 'Manage KnowledgeBase categories',
            'uri' => get_link('knowledgebase', 'manage_kb_categories'),
            'add_separator_before' => FALSE
        ),
    )
);
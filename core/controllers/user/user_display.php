<?php

class_load ('Notification');
class_load ('Customer');
class_load ('UserPhone');

/**
* Class for handling the display of the user-related pages,
* including the pages for users administration.
*
*/

class UserDisplay extends BaseDisplay
{

	/** Class constructor, initializes the menu */
	function UserDisplay ()
	{
		parent::BaseDisplay ();
	}

	/** Displays the login page */
	function login ()
	{
		$tpl = 'user/login.html';
        $params = array();
        if(isset($this->vars['goto'])){
            $params['goto'] = $this->vars['goto'];
        }

		$this->set_form_redir('login_submit', $params);
		$this->assign('error_msg', error_msg());
		$this->display($tpl);
	}
	
	/** Processes the login request */
	function login_submit ()
	{
		extract($this->vars);

		$login = trim($this->vars['login']);
		$password = trim($this->vars['password']);


		if ($this->vars['lost_password']) return $this->mk_redir ('lost_password');
		
		$goto = array();
		if (isset($this->vars['goto'])){
            $goto = array('goto' => $this->vars['goto']);
        }
		$ret = $this->mk_redir('login', $goto);

		if (empty($login) or empty($password)) error_msg($this->get_string(NEED_LOGIN_AND_PASSWORD));
		else
		{
			$auth = new Auth();
			$valid = $auth->validate_login ($login, $password);
			if ($valid)
			{
				// Save in the session the new language, if it was changed
				$user = new User ($valid);
				$_SESSION['USER_LANG'] = $user->language;				
				if ($this->vars['goto']){
                    $ret = $this->mk_redir($this->vars['goto']);
                }
				else $ret = $this->mk_redir('user_area', array(), 'home');
			}
		}                
		return $ret;
	}
	
	
	/** Displays the page where the users can retrieve their lost password */
	function lost_password ()
	{
		$tpl = 'user/lost_password.html';
		
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('lost_password_submit');
		$this->display ($tpl);
	}
	
	
	/** Retrieves a user's password */
	function lost_password_submit ()
	{
		$login = trim ($this->vars['login']);
		$email = trim ($this->vars['email']);
		
		if ($login and $email)
		{
			if (Auth::get_password ($login, $email))
			{
				$ret = $this->mk_redir ('lost_password_sent');
			}
			else
			{
				error_msg ($this->get_string('CANT_FIND_EMAIL'));
				$ret = $this->mk_redir ('lost_password');
			}
		}
		else
		{
			error_msg ($this->get_string('NEED_LOGIN_AND_EMAIL'));
			$ret = $this->mk_redir ('lost_password');
		}
		
		return $ret;
	}
	
	
	/** Displays the page confirming the password was sent */
	function lost_password_sent ()
	{
		$tpl = 'user/lost_password_sent.html';
		$this->display ($tpl);
	}
	
	
	/** Performs a logout */
	function logout ()
	{
		$auth = new auth();
		$auth->logout();
		
		return $this->mk_redir('login');
	}
	
	
	/** Displays the 'Permission denied' page */
	function permission_denied ()
	{
		$tpl = 'user/permission_denied.html';
		$this->assign ('error_msg', error_msg ());
		$this->assign ('goto', $this->vars['goto']);
		$this->assign ('return', $this->vars['return']);
		$this->display ($tpl);
	}
	

	/** Displays the page for allowing user to modify their login and personal info */
	function account_edit ()
	{
		$tpl = 'user/account_edit.html';
		// This will allow editing the login only for the currently logged in user
		$user = new User (get_uid());
		if (!$user->id) return $this->mk_redir ('user_area', array(), 'home');
		
		$user->password_confirm = $user->password;
		
		$this->assign ('user', $user);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('account_edit_submit');
		
		$this->display ($tpl);
	}
	
	
	/** Saves the account information */
	function account_edit_submit ()
	{
		$user = new User (get_uid ());
		$ret = $this->mk_redir ('user_area', array(), 'home');
		
		if ($this->vars['save'] and $user->id)
		{
			$data = $this->vars['user'];
			$old_language = $user->language;
			
			if ($data['password'] == $data['password_confirm'])
			{
				// Allow only a limited set of properties to be edited by the user
				$user->login = $data['login'];
				$user->password = $data['password'];
				$user->fname = $data['fname'];
				$user->lname = $data['lname'];
				$user->email = $data['email'];
				$user->language = $data['language'];
				$user->newsletter = $data['newsletter'];
				
				if ($user->is_valid_data ())
				{
					$user->save_data ();
					// Save in the session the new language, if it was changed
					if ($user->language != $old_language) $_SESSION['USER_LANG'] = $user->language;
				}
				else
				{
					$ret = $this->mk_redir ('account_edit');
				}
			}
			else
			{
				error_msg ($this->get_string('PASSWORDS_DONT_MATCH'));
				$ret = $this->mk_redir ('account_edit');
			}
		}
		
		return $ret;
	}
	
	
	
	/** Displays a page for adding a phone number */
	function phone_add()
	{
		global $current_user;
		if (!$this->vars['user_id']) return $this->mk_redir('user_area', array(), 'home');
		$user_id = $this->vars['user_id'];
		
		// An user is allowed to edit its own phone numbers
		if (get_uid() != $user_id) check_auth();
		
		$tpl = 'user/phone_add.html';
		$this->assign('phone_types', $GLOBALS['PHONE_TYPES']);
		$this->assign('user_id', $user_id);
		$this->assign('error_msg', error_msg());
		
		$params = array('phone[user_id]' => $user_id);
		if ($this->vars['ret'] == 'user_edit')
		{
			$params['ret'] = 'user_edit';
			$this->assign ('ret', $params['ret']);
		}
		$this->set_form_redir('phone_add_submit', $params);
		
		$this->display($tpl);
	}
	
	
	/** Saves a new phone number */
	function phone_add_submit ()
	{
		$user_id = $this->vars['user_id'];
	
		// An user is allowed to edit its own phone numbers
		if (get_uid() != $user_id) check_auth();
		
		
		
		if ($this->vars['ret'] == 'user_edit')
			$ret = $this->mk_redir ('user_edit', array ('id'=>$user_id));
		else
			$ret = $this->mk_redir ('user_area', array(), 'home');
			
		if ($this->vars['save'])
		{
			$user_phone = new UserPhone ();
			$user_phone->load_from_array ($this->vars['phone']);
			
			if ($user_phone->is_valid_data())
			{
				$user_phone->save_data ();
			}
			else
			{
				$params = array ('user_id' => $user_id);
				if ($this->vars['ret'] == 'user_edit') $params['ret'] = 'user_edit';
				
				$ret = $this->mk_redir ('phone_add', $params);
			}
		}
		
		return $ret;
	}
	
	
	/** Displays a page for editing a phone number */
	function phone_edit()
	{
		if (!$this->vars['id']) return $this->mk_redir('user_area', array(), 'home');
		$phone = new UserPhone($this->vars['id']);
		$this->assign('phone', $phone);
		
		// An user is allowed to modify his phone numbers
		if (get_uid() != $phone->user_id) check_auth();
		
		$tpl = 'user/phone_edit.html';
		$this->assign('phone_types', $GLOBALS['PHONE_TYPES']);
		$this->assign('error_msg', error_msg());
		
		$params = array('phone[user_id]' => $phone->user_id);
		if ($this->vars['ret'] == 'user_edit')
		{
			$params['ret'] = 'user_edit';
			$this->assign ('ret', $params['ret']);
		}
		$this->set_form_redir('phone_edit_submit', $params);
		
		$this->display($tpl);
	}
	
	/** Processes and saves the details of a user phone number */
	function phone_edit_submit()
	{
		$id = $this->vars['id'];
		$user_phone = new UserPhone($id);
		
		// An user is allowed to modify his phone numbers
		if (get_uid() != $user_phone->user_id) check_auth();
		
		$params = array ();
		if ($this->vars['ret'] == 'user_edit')
			$ret = $this->mk_redir ('user_edit', array ('id'=>$user_phone->user_id));
		else
			$ret = $this->mk_redir('user_area', array(), 'home');
		
		if ($this->vars['save'] and $user_phone->id)
		{
			$user_phone->load_from_array($this->vars['phone']);
			if ($user_phone->is_valid_data())
			{
				$user_phone->save_data();
			}
			else
			{
				$params = array ('id'=>$user_phone->id);
				if ($this->vars['ret'] == 'user_edit') $params['ret'] = 'user_edit';
				
				$ret = $this->mk_redir ('phone_edit', $params);
			}
		}
		return $ret;
	}
	
	
	/** Deletes a phone number */
	function phone_delete ()
	{
		$phone = new UserPhone ($this->vars['id']);
	
		// An user is allowed to modify his phone numbers
		if (get_uid() != $phone->user_id) check_auth();
			
		if ($phone->id)
		{
			$phone->delete ();
		}
		
		if ($this->vars['ret'] == 'user_edit')
			$ret = $this->mk_redir ('user_edit', array ('id' => $phone->user_id));
		else
			$ret = $this->mk_redir ('user_area', array(), 'home');
		
		return $ret;
	}
	
	/****************************************************************/
	/* ACL categories						*/
	/****************************************************************/
	
	/** Shows the page for managing ACL categories */
	function manage_acl_categories ()
	{
		check_auth ();
		$tpl = 'user/manage_acl_categories.html';
		
		$acl_categories = AclCategory::get_categories ();
		
		$this->assign ('acl_categories', $acl_categories);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}

	
	/** Displays the page for creating a new ACL category */
	function acl_category_add ()
	{
		check_auth ();
		$tpl = 'user/acl_category_add.html';
		
		$category = new AclCategory ();
		
		if (!empty_error_msg()) $category->load_from_array (restore_form_data ('acl_category', false, $acl_category_data));
	
		$this->set_form_redir ('acl_category_add_submit');
		$this->assign ('category', $category);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	
	/** Saves the new ACL category data */
	function acl_category_add_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_acl_categories');
		
		if ($this->vars['save'])
		{
			$category_data = $this->vars['category'];
			$category = new AclCategory ();
			$category->load_from_array ($category_data);
			
			if ($category->is_valid_data())
			{
				$category->save_data ();
			}
			else
			{
				$ret = $this->mk_redir ('acl_category_add');
				save_form_data ($category_data, 'acl_category_data');
			}
		}
		
		return $ret;
	}
	
	
	/** Deletes an ACL category */
	function acl_category_delete ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_acl_categories');
		$category = new AclCategory ($this->vars['id']);
		
		if ($category->id)
		{
			if ($category->can_delete ())
			{
				$category->delete ();
			}
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* ACL items							*/
	/****************************************************************/
	
	/** Shows the page for managing ACL items */
	function manage_acl_items ()
	{
		check_auth ();
		$tpl = 'user/manage_acl_items.html';
		
		$categories_list = AclCategory::get_categories_list ();
		$categories_items = AclCategory::get_categories_items ();
		
		$this->assign ('categories_list', $categories_list);
		$this->assign ('categories_items', $categories_items);
		$this->assign ('acl_classes', get_acl_display_classes_list ());
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	
	/** Shows the page for creating a new ACL item */
	function acl_item_add ()
	{
		check_auth ();
		$tpl = 'user/acl_item_add.html';
		
		$categories = AclCategory::get_categories_list ();
		
		$item = new AclItem ();
		if (!empty_error_msg()) $item->load_from_array (restore_form_data ('acl_item_data', false, $acl_item_data));
		
		$this->set_form_redir ('acl_item_add_submit');
		$this->assign ('item', $item);
		$this->assign ('categories', $categories);
		$this->assign ('error_msg', error_msg());
		
		$this->display ($tpl);
	}
	
	
	/** Saves the new ACL item */
	function acl_item_add_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_acl_items');
		
		if ($this->vars['save'])
		{
			$item_data = $this->vars['item'];
			$item = new AclItem ();
			$item->load_from_array ($item_data);
			
			if ($item->is_valid_data ())
			{
				$item->save_data ();
				$ret = $this->mk_redir ('acl_item_edit', array ('id' => $item->id));
			}
			else
			{
				$ret = $this->mk_redir ('acl_item_add');
				save_form_data ($item_data, 'acl_item_data');
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing an ACL item */
	function acl_item_edit ()
	{
		check_auth ();
		$tpl = 'user/acl_item_edit.html';
		
		$item = new AclItem ($this->vars['id']);
		if (!$item->id) return $this->mk_redir ('manage_acl_items');
	
		if (!empty_error_msg()) $item->load_from_array (restore_form_data ('acl_item_data', false, $acl_item_data));

		// Load the list of methods for the specified class
		$class = ($this->vars['class'] ? $this->vars['class'] : $GLOBALS['CLASSES_DISPLAY_ACL'][0]);
		$class_methods = get_display_class_methods ($class);
		$selected_class_methods = $item->get_class_operations ($class);
		
		$this->assign ('class', $class);
		$this->assign ('class_methods', $class_methods);
		$this->assign ('selected_class_methods', $selected_class_methods);
		$this->assign ('acl_classes', get_acl_display_classes_list ());
		
		
		$this->set_form_redir ('acl_item_edit_submit', array ('id' => $item->id));
		$this->assign ('item', $item);
		$this->assign ('error_msg', error_msg ());
		$this->assign ('categories', AclCategory::get_categories_list ());
		
		$this->display ($tpl);
	}
	
	
	/** Saves the ACL item's data */
	function acl_item_edit_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_acl_items');
		$item = new AclItem ($this->vars['id']);
		
		if ($this->vars['save'] and $item->id)
		{
			$item_data = $this->vars['item'];
			$item->load_from_array ($item_data);
			
			if ($item->is_valid_data ())
			{
				// Now also set the operations that have been marked
				$item->set_class_operations ($this->vars['class'], $this->vars['operations']);
				$item->save_data ();
				
			}
			else
			{
				save_form_data ($item_data, 'acl_item_data');
			}
			$ret = $this->mk_redir ('acl_item_edit', array ('id'=>$item->id, 'class'=>$this->vars['class']));
			
		}
		elseif ($this->vars['class_change'])
		{
			// This is just a request to select a different class
			$ret = $this->mk_redir ('acl_item_edit', array ('id'=>$item->id, 'class'=>$this->vars['class']));
		}
		
		return $ret;
	}
	
	
	/** Deletes an ACL item */
	function acl_item_delete ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_acl_items');
		$item = new AclItem ($this->vars['id']);
		
		if ($item->id)
		{
			if ($item->can_delete ())
			{
				$item->delete ();
			}
		}
		
		return $ret;
	}
	
	
	
	/****************************************************************/
	/* ACL roles							*/
	/****************************************************************/
	
	/** Displays the page for managing ACL roles */
	function manage_acl_roles ()
	{
		check_auth ();
		$tpl = 'user/manage_acl_roles.html';
		
		$filter = $_SESSION['manage_acl_roles']['filter'];
		
		$roles = AclRole::get_roles ($filter);
		
		$this->assign ('roles', $roles);
		$this->assign ('filter', $filter);
		$this->assign ('ACL_ROLE_TYPES', $GLOBALS['ACL_ROLE_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('manage_acl_roles_submit');
		
		$this->display($tpl);
	}
	
	
	/** Saves the filtering criteria for Manage Acl Roles page */
	function manage_acl_roles_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_acl_roles');
		
		$_SESSION['manage_acl_roles']['filter'] = $this->vars['filter'];
		
		return $ret;
	}
	
	
	/** Displays the page for creating an ACL role */
	function acl_role_add ()
	{
		check_auth ();
		$tpl = 'user/acl_role_add.html';
		
		$role = new AclRole ();
		if (!empty_error_msg()) $role->load_from_array (restore_form_data ('acl_role_data', false, $acl_role_data));
		
		$this->assign ('role', $role);
		$this->assign ('error_msg', error_msg());
		$this->assign ('categories_list', AclCategory::get_categories_list ());
		$this->assign ('categories_items', AclCategory::get_categories_items ());
		$this->assign ('ACL_ROLE_TYPES', $GLOBALS['ACL_ROLE_TYPES']);
		$this->set_form_redir ('acl_role_add_submit');
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about the new role */
	function acl_role_add_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir('manage_acl_roles');
		
		if ($this->vars['save'])
		{
			$role_data = $this->vars['role'];
			$role = new AclRole ();
			$role->load_from_array ($role_data);
			
			if ($role->is_valid_data())
			{
				$role->save_data ();
				$ret = $this->mk_redir ('acl_role_edit', array ('id'=>$role->id));
			}
			else
			{
				save_form_data ($role_data, 'acl_role_data');
				$ret = $this->mk_redir ('acl_role_add');
			}
		}
		return $ret;
	}
	
	
	/** Displays the page for editing an ACL role */
	function acl_role_edit ()
	{
		check_auth ();
		$tpl = 'user/acl_role_edit.html';
		
		$role = new AclRole ($this->vars['id']);
		if (!$role->id) return $this->mk_redir ('manage_roles');
		
		if (!empty_error_msg()) $role->load_from_array (restore_form_data ('acl_role_data', false, $acl_role_data));
		
		$this->assign ('role', $role);
		$this->assign ('error_msg', error_msg());
		$this->assign ('categories_list', AclCategory::get_categories_list ());
		$this->assign ('categories_items', AclCategory::get_categories_items ());
		$this->assign ('ACL_ROLE_TYPES', $GLOBALS['ACL_ROLE_TYPES']);
		$this->set_form_redir ('acl_role_edit_submit', array ('id'=>$role->id));
		
		$this->display ($tpl);
	}
	
	/** Saves the ACL role data */
	function acl_role_edit_submit ()
	{
		check_auth ();
		$role = new AclRole ($this->vars['id']);
		$ret = $this->mk_redir ('manage_acl_roles');
		
		if ($role->id and $this->vars['save'])
		{
			$role_data = $this->vars['role'];
			$role->load_from_array ($role_data);
			
			if ($role->is_valid_data ())
			{
				$role->save_data ();
			}
			else
			{
				save_form_data ($role_data, 'acl_role_data');
			}
			$ret = $this->mk_redir ('acl_role_edit', array ('id'=>$role->id));
		}
		
		return $ret;
	}
	
	
	/** Deletes an ACL role */
	function acl_role_delete ()
	{
		check_auth ();
		$role = new AclRole ($this->vars['id']);
		
		if ($role->id and $role->can_delete ())
		{
			$role->delete ();
		}
		
		return $this->mk_redir ('manage_acl_roles');
	}
	
	
	/****************************************************************/
	/* Users							*/
	/****************************************************************/
	
	/** Displays the page for managing users */
	function manage_users ()
	{
		check_auth ();
		$tpl = 'user/manage_users.html';
		
		
		if ($this->vars['customer_id'])
		{
			// This is a request to view a specific customer users
			$_SESSION['manage_users']['filter'] = array (
				'customer_id' => $this->vars['customer_id'],
				'type' => USER_TYPE_CUSTOMER,
				'active' => USER_STATUS_ACTIVE
			);
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_users']['filter']['customer_id'] = $this->locked_customer->id;
		}
		
		$filter = $_SESSION['manage_users']['filter'];
		if (!isset($filter['type'])) $filter['type'] = USER_TYPE_KEYSOURCE;
		if (!isset($filter['active'])) $filter['active'] = USER_FILTER_ACTIVE_AWAY;
		if (!isset($filter['order_by'])) $filter['order_by'] = 'name';
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'ASC';
	
		if ($filter['type'] == USER_TYPE_CUSTOMER)
		{ 
			// Extract the list of customers, eventually restricting only to the customers assigned to 
			// the current user, if he has restricted customer access.
			$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
			if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
			$customers_list = Customer::get_customers_list ($customers_filter);

			$this->assign ('customers_list', $customers_list);
		}
		else
		{
			unset ($filter['customer_id']); 
		}
		
		if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
		if (!isset($filter['limit'])) $filter['limit'] = 20;
		
		// Check if the user has restricted access to customers
		if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
		 
		$users_count = 0;
		$users = User::get_users ($filter, $users_count);
		if ($users_count < $filter['start'])
		{
			$filter['start'] = 0;
			$_SESSION['manage_users']['filter']['start'] = 0;
			$users = User::get_users ($filter, $users_count);
		}
		$users_list = User::get_users_list (array('type'=>USER_TYPE_KEYSOURCE, 'active'=>USER_FILTER_ALL));
		$pages = make_paging ($filter['limit'], $users_count);
		
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
		
		$extra_params = array ();
		
		$this->assign ('users', $users);
		$this->assign ('users_count', $users_count);
		$this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
		$this->assign ('pages', $pages);
		$this->assign ('start_prev', $filter['start'] - $filter['limit']);
		$this->assign ('start_next', $filter['start'] + $filter['limit']);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('users_list', $users_list);
		$this->assign ('sort_url', $this->mk_redir ('manage_users_submit', $extra_params));
		$this->assign ('error_msg', error_msg());
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('USER_ONLY_TYPES', $GLOBALS['USER_ONLY_TYPES']);
		$this->assign ('USER_TYPES', $GLOBALS['USER_TYPES']);
		$this->assign ('USER_STATUSES', $GLOBALS['USER_STATUSES']);
		$this->set_form_redir ('manage_users_submit');
		
		$this->display ($tpl);
	}
	
	
	/** Saves the filtering criteria for users */
	function manage_users_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_users', array ('do_filter' => 1));
		
		if ($this->vars['order_by'] and $this->vars['order_dir'])
		{
			// This is a request to change the sorting order
			$_SESSION['manage_users']['filter']['order_by'] = $this->vars['order_by'];
			$_SESSION['manage_users']['filter']['order_dir'] = $this->vars['order_dir'];
		}
		else
		{
			$this->vars['filter']['order_by'] = $this->vars['order_by_bk'];
			$this->vars['filter']['order_dir'] = $this->vars['order_dir_bk'];
			$_SESSION['manage_users']['filter'] = $this->vars['filter'];
		}
		
		return $ret;
	}
	
	
	/** Displays the page for creating a new user account */
	function user_add ()
	{
		check_auth ();
		$tpl = 'user/user_add.html';
		
		$user = new User ();
		$user_data = restore_form_data ('user_data', false, $user_data);
		if (!isset($user_data['send_invitation_email'])) $user_data['send_invitation_email'] = 1;
		
		$user->load_from_array ($user_data);
		$user->send_invitation_email = $user_data['send_invitation_email'];
		$user->password_confirm = $user_data['password_confirm'];
		if (isset($this->vars['customer_id']))
		{
			$user->type = USER_TYPE_CUSTOMER;
			$user->customer_id = $this->vars['customer_id'];
		}
		
		$customers_filter = array ('favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers = Customer::get_customers_list ($customers_filter);
		
		$params = array ();
		if ($this->vars['ret'] == 'customer')
		{
			$params['ret'] = 'customer';
			$params['customer_id'] = $this->vars['customer_id'];
			
			$user->customer_id = $this->vars['customer_id'];
			$user->type = USER_TYPE_CUSTOMER;
			
			// Mark the potential customer for locking
			$_SESSION['potential_lock_customer_id'] = $this->vars['customer_id'];
		}
		
		$this->assign ('user', $user);
		$this->assign ('customers', $customers);
		$this->assign ('USER_TYPES', $GLOBALS['USER_TYPES']);
		$this->assign ('error_msg', error_msg());
		
		$this->set_form_redir ('user_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the data for a new user */
	function user_add_submit ()
	{
		if ($this->vars['save'] and $this->vars['user']['type']==USER_TYPE_CUSTOMER and $this->vars['user']['customer_id'])
			check_auth (array('customer_id' => $this->vars['user']['customer_id']));
		else 
			check_auth ();
		
		$params = array ();
		if ($this->vars['ret'] == 'customer')
		{
			$ret = $this->mk_redir ('customer_edit', array('id' => $this->vars['customer_id']), 'customer');
			$params['ret'] = 'customer';
			$params['customer_id'] = $this->vars['customer_id'];
		}
		else $ret = $this->mk_redir ('manage_users');
		
		if ($this->vars['save'])
		{
			$user_data = $this->vars['user'];
			$user_data['send_invitation_email'] = ($user_data['send_invitation_email'] ? 1 : 0);
			
			$user = new User();
			if ($user_data['type'] == USER_TYPE_KEYSOURCE) $user_data['allow_private'] = true;
			$user->load_from_array ($user_data);
			
			if ($user_data['password'] != $user_data['password_confirm'])
			{
				error_msg ($this->get_string('PASSWORDS_DONT_MATCH'));
			}
			
			if ($user->is_valid_data() and empty_error_msg())
			{
				$user->save_data ();
				$user->load_data ();
				
				if ($user->is_customer_user())
				{
					$user->roles_list = array (DEFAULT_CUSTOMER_ROLE);
					$user->save_data ();
				}
				
				// If requested, send the invitation e-mail to the user
				if ($user_data['send_invitation_email']) $user->send_invitation_email ();
				
				$params['id'] = $user->id;
				$ret = $this->mk_redir ('user_edit', $params);
				clear_form_data ('user_data');
			}
			else
			{
				save_form_data ($user_data, 'user_data');
				$ret = $this->mk_redir ('user_add', $params);
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a user account */
	function user_edit ()
	{
		check_auth ();
		class_load ('Alert');
		$tpl = 'user/user_edit.html';
		
		$user = new User ($this->vars['id']);
		if (!$user->id) return $this->mk_redir ('manage_users');
		$user->password_confirm = $user->password;
		
		if (!empty_error_msg()) $user->load_from_array (restore_form_data ('user_data', false, $user_data));
		
		$customers_filter = array ('favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers = Customer::get_customers_list ($customers_filter);
		$customers_list_all = Customer::get_customers_list ();
		
		$params = array ();
		if ($this->vars['ret'] == 'customer')
		{
			$params['ret'] = 'customer';
			$params['customer_id'] = $this->vars['customer_id'];
		}
		
		$favorite_customers = array ();
		$assigned_customers = array ();
		if (!$user->is_customer_user())
		{
			// This is a Keysource user
			$favorite_customers = $user->get_favorite_customers_list ();
			
			if ($user->restrict_customers)
			{
				// This is a Keysource user for whom access to customers is restricted
				$assigned_customers = $user->get_assigned_customers_list (true);		// Direct assigned customers
				$group_assigned_customers = $user->get_group_assigned_customers_list (); 	// Group assigned customers
			}
			
			// Load the types of notifications for which this user is recipient
			$notifs_generic_direct = $user->get_assigned_notifications_types ();
			$notifs_generic_group = $user->get_assigned_notifications_types (true);
			
			$notifs_customer_direct = $user->get_assigned_notifications_customers ();
			$notifs_customer_group = $user->get_assigned_notifications_customers (true);
			
			// Load the list of groups for this user
			$groups_list = $user->get_groups_list();
			
			// Load the list of alert definitions for which this user is recipient
			$assigned_alerts = Alert::get_user_assigned_alerts ($user->id);
			
			$this->assign ('notifs_generic_direct', $notifs_generic_direct);
			$this->assign ('notifs_generic_group', $notifs_generic_group);
			$this->assign ('notifs_customer_direct', $notifs_customer_direct);
			$this->assign ('notifs_customer_group', $notifs_customer_group);
			$this->assign ('groups_list', $groups_list);
			$this->assign ('assigned_alerts', $assigned_alerts);
			$this->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
		}
		else
		{
			// This is a customer user
			// Mark the potential customer for locking
			$_SESSION['potential_lock_customer_id'] = $user->customer_id;
		}
		
		$member_of_customers = $user->get_users_customer_list();
		
		$users_list = User::get_users_list (array('type'=>USER_TYPE_KEYSOURCE, 'active'=>USER_FILTER_ALL));

		$this->assign ('user', $user);
		$this->assign ('member_of_customers', $member_of_customers);
		$this->assign ('customers', $customers);
		$this->assign ('favorite_customers', $favorite_customers);
		$this->assign ('assigned_customers', $assigned_customers);
		$this->assign ('group_assigned_customers', $group_assigned_customers);
		$this->assign ('customers_list_all', $customers_list_all);
		$this->assign ('users_list', $users_list);
		$this->assign ('USER_TYPES', $GLOBALS['USER_TYPES']);
		$this->assign ('USER_STATUSES', $GLOBALS['USER_STATUSES']);
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('user_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about the user */
	function user_edit_submit ()
	{
		if ($this->vars['save'] and $this->vars['user']['type']==USER_TYPE_CUSTOMER and $this->vars['user']['customer_id'])
			check_auth (array('customer_id' => $this->vars['user']['customer_id']));
		else 
			check_auth ();
		
		$user = new User ($this->vars['id']);
		$params = array ();
		
		if ($this->vars['ret'] == 'customer')
		{
			$ret = $this->mk_redir ('customer_edit', array ('id'=>$this->vars['customer_id']), 'customer');
			$params['ret'] = 'customer';
			$params['customer_id'] = $this->vars['customer_id'];
		}
		else $ret = $this->mk_redir ('manage_users');
		
		if ($this->vars['save'] and $user->id)
		{
			$user_data = $this->vars['user'];
			if ($user_data['type'] == USER_TYPE_KEYSOURCE) $user_data['allow_private'] = true;
			$user->load_from_array ($user_data);
			
			if ($user->is_valid_data ())
			{
				$user->save_data ();
			}
			else
			{
				save_form_data ($user_data, 'user_data');
			}
			
			$params['id'] = $this->vars['id'];
			$ret = $this->mk_redir ('user_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing the Exchange connection information for an user */
	function user_edit_exchange ()
	{
		check_auth ();
		$tpl = 'user/user_edit_exchange.html';
		$user = new User ($this->vars['id']);
		if (!$user->id) return $this->mk_redir ('user_area', array (), 'home');
		
		if (!$user->exchange)
		{
			$user->exchange = new UserExchange ();
			$user->exchange->exch_login = preg_replace ('/@.*/', '', $user->email);
			$user->exchange->exch_email = $user->email;
		}
		else
		{
			$exIface = $user->exchange->getExchangeInterface ();
		}
		$params = $this->set_carry_fields (array('id', 'returl'));
		
		$this->assign ('user', $user);
		$this->assign ('login_res', $this->vars['login_res']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('user_edit_exchange_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the Exchange information for the user */
	function user_edit_exchange_submit ()
	{
		check_auth ();
		$user = new User ($this->vars['id']);
		
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('user_edit', array('id'=>$user->id)));
		//$ret = ($user->id ? $this->mk_redir("user_edit", $this->vars): $this->mk_redir ('user_area', array(), 'home'));
		$params = $this->set_carry_fields (array('id', 'returl'));
		
		if ($this->vars['save'] and $user->id)
		{
			$data = $this->vars['exch'];
			
			if (!$user->exchange) $user->exchange = new UserExchange ();
			$user->exchange->load_from_array ($data);
			
			if ($user->exchange->is_valid_data ())
			{
				$user->exchange->id = $user->id;
				$user->exchange->save_data ();
				$params['login_res'] = 'ok';
			}
			else
			{
				save_form_data ($data, 'exch_data');
			}
			$ret = $this->mk_redir ('user_edit_exchange', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page allowing the editing of the user's active status */
	function user_edit_active ()
	{
		check_auth ();
		$tpl = 'user/user_edit_active.html';
		
		$user = new User ($this->vars['id']);
		if (!$user->id) return $this->mk_redir ('manage_users');
		if ($user->is_customer_user())
		{
			error_msg ($this->get_string('KEYSOURCE_ONLY_OPPERATION'));
			return ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('user_edit', array('id'=>$user->id)));
		}
		
		$users_list = User::get_users_list (array('active'=>USER_STATUS_ACTIVE, 'type'=>USER_TYPE_KEYSOURCE));
		$away_recipient_for = $user->get_away_recipient_for ();
		
		$params = $this->set_carry_fields (array('id', 'returl'));
		
		$this->assign ('user', $user);
		$this->assign ('users_list', $users_list);
		$this->assign ('away_recipient_for', $away_recipient_for);
		$this->assign ('USER_STATUSES', $GLOBALS['USER_STATUSES']);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('user_edit_active_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the new active status for the user */
	function user_edit_active_submit ()
	{
		check_auth ();
		$user = new User ($this->vars['id']);
		
		$params = $this->set_carry_fields (array('id', 'returl'));
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('user_edit', array('id'=>$user->id)));
		
		if ($this->vars['save'] and $user->id)
		{
			$data = $this->vars['user'];
			$user->load_from_array ($data);
			
			if ($user->is_valid_data()) $user->save_data ();
			$ret = $this->mk_redir ('user_edit_active', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a user's permissions */
	function user_roles ()
	{
		check_auth ();
		$tpl = 'user/user_roles.html';
		
		$user = new User ($this->vars['id']);
		if (!$user->id) return $this->mk_redir ('manage_users');
		
		$this->assign ('user', $user);
		$this->assign ('roles', AclRole::get_roles ());
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('user_roles_submit', array ('id'=>$user->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the user's roles */
	function user_roles_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_users');
		$user = new User ($this->vars['id']);

		if ($user->id and $this->vars['save'])
		{
			$user_data = $this->vars['user'];
			if (!$user_data['administrator']) $user_data['administrator'] = 0;

			$user->load_from_array ($user_data);
			$user->save_data ();
			$ret = $this->mk_redir ('user_roles', array ('id'=>$user->id));
		}
		
		return $ret;
	}
	
	
	/** Deletes a user */
	function user_delete ()
	{
		$ret = $this->mk_redir ('manage_users');
		
		$user = new User ($this->vars['id']);
		
		if ($user->customer_id) check_auth (array('customer_id' => $user->customer_id));
		else check_auth ();
		
		if ($user->id and $user->can_delete())
		{
			$user->delete ();
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing the list of customers assigned to a user */
	function user_edit_assigned_customers ()
	{
		check_auth ();
		$tpl = 'user/user_edit_assigned_customers.html';
		
		$user = new User ($this->vars['id']);
		if (!$user->id) return $this->mk_redir ('manage_users');
		elseif ($user->is_customer_user() or !$user->restrict_customers)
		{
			error_msg($this->get_string('ACCESS_DENIED'));
			return $this->mk_redir ('user_edit', array ($user->id));
		}
		
		$customers_list = Customer::get_customers_list ();
		$assigned_customers_list = $user->get_assigned_customers_list (true);	// Only direct assigned customers
		
		$group_assigned_customers_list = $user->get_group_assigned_customers_list ();
		
		// Remove from the initial customers list the already assigned customers
		foreach ($assigned_customers_list as $customer_id => $name) unset($customers_list[$customer_id]);
		
		$this->assign ('user', $user);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('assigned_customers_list', $assigned_customers_list);
		$this->assign ('group_assigned_customers_list', $group_assigned_customers_list);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('user_edit_assigned_customers_submit', array('id' => $user->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the list of assigned customers */
	function user_edit_assigned_customers_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('user_edit', array ('id' => $this->vars['id']));
		$user = new User ($this->vars['id']);
		
		if ($this->vars['save'] and $user->id)
		{
			$user->set_assigned_customers ($this->vars['assigned_customers_list'], $user->id);
			$ret = $this->mk_redir ('user_edit_assigned_customers', array ('id' => $user->id));
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing the list of favorite customers for a user */
	function user_edit_favorite_customers ()
	{
		if (get_uid() != $this->vars['id']) check_auth ();	// A user is always allowed to edit his favorites
		
		$tpl = 'user/user_edit_favorite_customers.html';
		
		$user = new User ($this->vars['id']);
		if (!$user->id) return $this->mk_redir ('manage_users');
		
		// If the user has restricted customer access, then favorites can only be selected from the assigned customers.
		$customers_filter = ($user->restrict_customers ? array('assigned_user_id' => $user->id) : array());
		$customers_list = Customer::get_customers_list ($customers_filter);

		$favorite_customers_list = $user->get_favorite_customers_list ();
		
		// Remove from the initial customers list the already assigned customers
		foreach ($favorite_customers_list as $customer_id => $name) unset($customers_list[$customer_id]);
		
		$this->assign ('user', $user);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('favorite_customers_list', $favorite_customers_list);
		$this->assign ('ret', $this->vars['ret']);
		$this->assign ('error_msg', error_msg());
		
		$params = array ('id' => $user->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		$this->set_form_redir ('user_edit_favorite_customers_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Saves the list of favorite customers */
	function user_edit_favorite_customers_submit ()
	{
		if (get_uid() != $this->vars['id']) check_auth ();	// A user is always allowed to edit his favorites
		
		if ($this->vars['ret'] == 'user_area') $ret = $this->mk_redir ('user_area', array(), 'home');
		else $ret = $this->mk_redir ('user_edit', array ('id' => $this->vars['id']));
		
		$user = new User ($this->vars['id']);
		
		if ($this->vars['save'] and $user->id)
		{
			$user->set_favorite_customers ($this->vars['favorite_customers_list'], $user->id);
			
			$params = array ('id' => $user->id);
			if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
			
			$ret = $this->mk_redir ('user_edit_favorite_customers', $params);
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Groups							*/
	/****************************************************************/
	
	/** Displays the page for managing groups */
	function manage_groups ()
	{
		check_auth ();
		$tpl = 'user/manage_groups.html';
		
		$filter = $_SESSION['manage_groups']['filter'];
		if (!isset($filter['type'])) $filter['type'] = USER_TYPE_KEYSOURCE_GROUP;
		if (!isset($filter['active'])) $filter['active'] = 1;
		
		$groups = Group::get_groups ($filter, $nocount);
		
		if ($filter['type'] == USER_TYPE_KEYSOURCE_GROUP)
		{
			// Set the number of customers assigned to each group
			for ($i = 0; $i < count($groups); $i++)
			{
				$assigned_customers = $groups[$i]->get_assigned_customers_list ();
				$groups[$i]->assigned_customers_count = count ($assigned_customers);
			}
		}
		
		$this->assign ('groups', $groups);
		$this->assign ('filter', $filter);
		$this->assign ('error_msg', error_msg());
		$this->assign ('GROUP_ONLY_TYPES', $GLOBALS['GROUP_ONLY_TYPES']);
		$this->assign ('USER_TYPES', $GLOBALS['USER_TYPES']);
		$this->set_form_redir ('manage_groups_submit');
		
		$this->display ($tpl);
	}
	
	
	/** Saves the filtering criteria for groups */
	function manage_groups_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_groups');
		
		$_SESSION['manage_groups']['filter'] = $this->vars['filter'];
		
		return $ret;
	}
	
	
	/** Displays the page for creating a new group */
	function group_add ()
	{
		check_auth ();
		$tpl = 'user/group_add.html';
		
		$group = new Group ();
		if (!empty_error_msg())
		{
			$group->load_from_array (restore_form_data ('group_data', false, $group_data));
		}
		elseif ($_SESSION['manage_groups']['filter']['type'])
		{
			$group->type = $_SESSION['manage_groups']['filter']['type'];
		}
		
		$this->assign ('group', $group);
		$this->assign ('GROUP_ONLY_TYPES', $GLOBALS['GROUP_ONLY_TYPES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('group_add_submit');
		
		$this->display ($tpl);
	}
	
	
	/** Saves the information about the new group */
	function group_add_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_groups');
		
		if ($this->vars['save'])
		{
			$group_data = $this->vars['group'];
			$group = new Group ();
			$group->load_from_array ($group_data);
			
			if ($group->is_valid_data ())
			{
				$group->save_data ();
				$ret = $this->mk_redir ('group_edit', array ('id' => $group->id));
			}
			else
			{
				save_form_data ($group_data, 'group_data');
				$ret = $this->mk_redir ('group_add');
			}
		}
		
		return $ret;
	}
	
	
	/** Displays a page for editing group properties */
	function group_edit ()
	{
		check_auth ();
		$tpl = 'user/group_edit.html';
		
		$group = new Group ($this->vars['id']);
		if (!$group->id) return $this->mk_redir ('manage_groups');
		
		if (!empty_error_msg())
		{
			$group->load_from_array (restore_form_data ('group_data', false, $group_data));
		}
		
		$users_filter = array ('type' => ($group->type == USER_TYPE_KEYSOURCE_GROUP ? USER_TYPE_KEYSOURCE : USER_TYPE_KEYSOURCE+USER_TYPE_CUSTOMER));
		$available_users = User::get_users_list ($users_filter);
		
		// Remove from available users list those who are already members
		foreach ($group->members_list as $member_id) if (isset($available_users[$member_id])) unset ($available_users[$member_id]);
		
		$assigned_customers = $group->get_assigned_customers_list ();
		
		$this->assign ('group', $group);
		$this->assign ('available_users', $available_users);
		$this->assign ('assigned_customers', $assigned_customers);
		$this->assign ('error_msg', error_msg ());
		$this->assign ('GROUP_ONLY_TYPES', $GLOBALS['GROUP_ONLY_TYPES']);
		$this->set_form_redir ('group_edit_submit', array ('id' => $group->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the group information */
	function group_edit_submit ()
	{
		check_auth ();
		$group = new Group ($this->vars['id']);
		
		$ret = ($this->vars['returl'] ? $this->vars['returl'] : $this->mk_redir ('manage_groups'));
		$params = $this->set_carry_fields (array('id', 'returl'));
		
		if ($this->vars['save'] and $group->id)
		{
			$group_data = $this->vars['group'];
			if (!isset($group_data['members_list'])) $group_data['members_list'] = array ();
			
			$group->load_from_array ($group_data);
			
			if ($group->is_valid_data ()) $group->save_data ();
			else save_form_data ($group_data, 'group_data');
			
			$ret = $this->mk_redir ('group_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Deletes a group */
	function group_delete ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_groups');
		
		$group = new Group ($this->vars['id']);
		
		if ($group->id)
		{
			if ($group->can_delete())
			{
				$group->delete ();
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing the list of customers assigned to a group */
	function group_edit_assigned_customers ()
	{
		check_auth ();
		$tpl = 'user/group_edit_assigned_customers.html';
		
		$group = new Group ($this->vars['id']);
		if (!$group->id) return $this->mk_redir ('manage_groups');
		
		$customers_list = Customer::get_customers_list ();
		$assigned_customers_list = $group->get_assigned_customers_list ();
		
		// Remove from the initial customers list the already assigned customers
		foreach ($assigned_customers_list as $customer_id => $name) unset($customers_list[$customer_id]);
		
		$this->assign ('group', $group);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('assigned_customers_list', $assigned_customers_list);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('group_edit_assigned_customers_submit', array('id' => $group->id));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the list of assigned customers */
	function group_edit_assigned_customers_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('group_edit', array ('id' => $this->vars['id']));
		$group = new Group ($this->vars['id']);
		
		if ($this->vars['save'] and $group->id)
		{
			$group->set_assigned_customers ($this->vars['assigned_customers_list']);
			$ret = $this->mk_redir ('group_edit_assigned_customers', array ('id' => $group->id));
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Notification recipients - Generic				*/
	/****************************************************************/
	
	/** Displays the page for managing notification recipients */
	function manage_notification_recipients ()
	{
		check_auth ();
		class_load ('InfoRecipients');
		$tpl = 'user/manage_notification_recipients.html';
		
		$recipients = InfoRecipients::get_all_type_recipients ();
		$default_recipients = InfoRecipients::get_all_type_default_recipients ();
		
		$users = User::get_users_list (array('type' => (USER_TYPE_KEYSOURCE+USER_TYPE_KEYSOURCE_GROUP)));

		$this->assign ('recipients', $recipients);
		$this->assign ('default_recipients', $default_recipients);
		$this->assign ('users', $users);
		$this->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
		
		$this->display ($tpl);
	}
	
	
	/** Edit the recipients for a specific class of notifications */
	function notification_recipients_edit ()
	{
		check_auth ();
		class_load ('InfoRecipients');
		$tpl = 'user/notification_recipients_edit.html';
		
		if (!$GLOBALS['NOTIF_OBJ_CLASSES'][$this->vars['class_id']]) return $this->mk_redir ('manage_notification_recipients');
		
		$recipients = InfoRecipients::get_all_type_recipients ();
		$default_recipients = InfoRecipients::get_all_type_default_recipients ();

		$users = User::get_users (array('type' => USER_TYPE_KEYSOURCE), $nocount);
		$groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP), '');
	
		$this->assign ('class_id', $this->vars['class_id']);
		$this->assign ('recipients', $recipients);
		$this->assign ('default_recipients', $default_recipients);
		$this->assign ('users', $users);
		$this->assign ('groups', $groups);
		$this->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('notification_recipients_edit_submit', array ('class_id' => $this->vars['class_id']));
		
		$this->display ($tpl);
	}
	
	
	/** Saves the list of recipients for a class of notifications */
	function notification_recipients_edit_submit ()
	{
		check_auth ();
		class_load ('InfoRecipients');
		$ret = $this->mk_redir ('manage_notification_recipients');
		
		if ($this->vars['save'] and $this->vars['class_id'])
		{ 
			InfoRecipients::set_recipients($this->vars['class_id'], $this->vars['recipients'], $this->vars['is_default']);
			$ret = $this->mk_redir ('notification_recipients_edit', array ('class_id' => $this->vars['class_id']));
		}
		
		return $ret;
	}
	
	/****************************************************************/
	/* Customer notifications recipients - Keysource users		*/
	/****************************************************************/
	
	/** Displays the page for managing the Keysource recipients for notifications from specific customers */
	function manage_customer_recipients ()
	{
		check_auth ();
		class_load ('InfoRecipients');
		$tpl = 'user/manage_customer_recipients.html';
	
		if ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['customer_recipients']['filter']['customer_id'] = $this->locked_customer->id;
		}
			
		$filter = $_SESSION['customer_recipients']['filter'];
		
		if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
		if (!isset($filter['limit'])) $filter['limit'] = 25;
		
		$tot_recips = 0;
		
		// Check if the user has restricted access to customers
		if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
		
		$recipients = InfoRecipients::get_customer_recipients ($filter, $tot_recips);
		$default_recipients = InfoRecipients::get_customer_default_recipients ($customer->id);
		if ($tot_recips < ($filter['start']))
		{
			$filter['start'] = intval ($tot_recips / $filter['limit']) * $filter['limit'];
			$recipients = InfoRecipients::get_customer_recipients ($filter, $tot_recips);
		}
		
		$users_list = User::get_users_list (array('type' => (USER_TYPE_KEYSOURCE+USER_TYPE_KEYSOURCE_GROUP)));
		
		$customers_filter = array ('favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		//$customers_list = Customer::get_customers_list ();
 		
		$pages = make_paging ($filter['limit'], $tot_recips);
		
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
		
		$this->assign ('recipients', $recipients);
		$this->assign ('default_recipients', $default_recipients);
		$this->assign ('filter', $filter);
		$this->assign ('pages', $pages);
		$this->assign ('tot_recips', $tot_recips);
		$this->assign ('users_list', $users_list);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('classes_count', count($GLOBALS['NOTIF_OBJ_CLASSES']));
		$this->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
		if ($this->vars['do_filter']) $this->assign ('do_filter', $this->vars['do_filter']);
		$this->set_form_redir ('manage_customer_recipients_submit');
		
		$this->display ($tpl);
		
		class_load ('Computer');
	}
	
	
	/** Saves the filtering criteria for the customer notifications recipients page */
	function manage_customer_recipients_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_customer_recipients', array('do_filter' =>1));
		
		if ($this->vars['go'] == 'prev') $this->vars['filter']['start']-= $this->vars['filter']['limit'];
		elseif ($this->vars['go'] == 'next') $this->vars['filter']['start']+= $this->vars['filter']['limit'];
		
		$_SESSION['customer_recipients']['filter'] = $this->vars['filter'];
		
		return $ret;
	}
	
	
	/** Displays the page for selecting the recipients for a specific customer and a specific notification type */
	function notification_customer_recipients_edit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		class_load ('InfoRecipients');
		
		$tpl = 'user/notification_customer_recipients_edit.html';
		$customer = new Customer ($this->vars['customer_id']);
		
		if (!$GLOBALS['NOTIF_OBJ_CLASSES'][$this->vars['class_id']] or !$customer->id) return $this->mk_redir ('manage_customer_recipients');
		
		$recipients = InfoRecipients::get_customer_recipients (array('customer_id' => $customer->id, 'notif_obj_class' => $this->vars['class_id']), $no_total);
		$default_recipients = InfoRecipients::get_customer_default_recipients ($customer->id);
		
		$users = User::get_users (array('type' => USER_TYPE_KEYSOURCE), $nocount);
		$groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP), '');

		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		$this->assign ('customer', $customer);
		$this->assign ('ret', $this->vars['ret']);
		$this->assign ('class_id', $this->vars['class_id']);
		$this->assign ('recipients', $recipients);
		$this->assign ('default_recipients', $default_recipients);
		$this->assign ('users', $users);
		$this->assign ('groups', $groups);
		$this->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
		$this->assign ('error_msg', error_msg());
		
		$params = array ('class_id' => $this->vars['class_id'], 'customer_id' => $customer->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$this->set_form_redir ('notification_customer_recipients_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	function notification_customer_recipients_edit_submit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		class_load ('InfoRecipients');
		$customer = new Customer ($this->vars['customer_id']);
		
		if ($this->vars['ret'] == 'customer') $ret = $this->mk_redir ('customer_edit', array ('id'=>$customer->id), 'customer');
		else
		{
			$params = array ();
			if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
			$ret = $this->mk_redir ('manage_customer_recipients', $params);
		}

		if ($this->vars['save'] and $GLOBALS['NOTIF_OBJ_CLASSES'][$this->vars['class_id']] and $customer->id)
		{
			InfoRecipients::set_customer_recipients ($customer->id, $this->vars['class_id'], $this->vars['recipients'], $this->vars['is_default']); 
			
			$params = array ('class_id' => $this->vars['class_id'], 'customer_id' => $customer->id);
			if ($this->vars['ret'] == 'customer') $params['ret'] = 'customer';
			if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
			$ret = $this->mk_redir ('notification_customer_recipients_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for viewing and managing Keysource account managers */
	function account_managers ()
	{
		check_auth ();
		class_load ('InfoRecipients');
		$tpl = 'user/account_managers.html';
		
		$filter = $_SESSION['account_managers'];
		$accounts_managers = InfoRecipients::get_accounts_managers ();
		$users_list = User::get_users_list (array('type' => (USER_TYPE_KEYSOURCE+USER_TYPE_KEYSOURCE_GROUP)));
		$customers_list = Customer::get_customers_list (array('active' => -1));
		
		$this->assign ('accounts_managers', $accounts_managers);
		$this->assign ('filter', $filter);
		$this->assign ('users_list', $users_list);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('account_managers_submit');
		
		$this->display ($tpl);
	}
	
	function account_managers_submit ()
	{
		$_SESSION['account_managers'] = $this->vars['filter'];
		return $this->mk_redir ('account_managers');
	}
	
	/** Displays the page for specifying for which customers a user is account manager */
	function account_manager_edit ()
	{
		check_auth ();
		class_load ('InfoRecipients');
		$tpl = 'user/account_manager_edit.html';
		$user = new User ($this->vars['user_id']);
		if (!$user->id) return $this->mk_redir ('account_managers');
		
		$assigned_customers = InfoRecipients::get_accounts_managers ();
		if (isset($assigned_customers[$user->id])) $assigned_customers = $assigned_customers[$user->id];
		else $assigned_customers = array ();
		
		$customers_list = Customer::get_customers_list (array('active' => -1));
		$customers_list_active = Customer::get_customers_list (array('active' => 1));
		
		$params = $this->set_carry_fields (array('user_id'));
		$this->assign ('user', $user);
		$this->assign ('assigned_customers', $assigned_customers);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('customers_list_active', $customers_list_active);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('account_manager_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	function account_manager_edit_submit ()
	{
		check_auth ();
		class_load ('InfoRecipients');
		$user = new User ($this->vars['user_id']);
		$params = $this->set_carry_fields (array('user_id'));
		$ret = $this->mk_redir ('account_managers');
		
		if ($this->vars['save'] and $user->id)
		{
			//debug ($this->vars);
			if (is_array($this->vars['assigned_customers_list'])) $assigned_customers = $this->vars['assigned_customers_list'];
			else $assigned_customers = array ();
			if (is_array($this->vars['default_for'])) $default_for = $this->vars['default_for'];
			elseif (is_numeric($this->vars['default_for'])) $default_for = array ($this->vars['default_for']);
			else $default_for = array ();
			
			InfoRecipients::set_account_manager ($user->id, $assigned_customers, $default_for);
			$ret = $this->mk_redir ('account_manager_edit', $params);
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Customer notifications recipients - Customer users		*/
	/****************************************************************/
	
	/** Displays the page for managing which customer users will receive notifications for those customers */
	function manage_customer_recipients_customers ()
	{
		check_auth ();
		class_load ('InfoRecipients');
		$tpl = 'user/manage_customer_recipients_customers.html';
	
		if ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['customer_recipients_customers']['filter']['customer_id'] = $this->locked_customer->id;
		}
			
		$filter = $_SESSION['customer_recipients_customers']['filter'];
		
		if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
		if (!isset($filter['limit'])) $filter['limit'] = 25;
	
		$tot_recips = 0;
		
		// Check if the user has restricted access to customers
		if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
		
		$recipients = InfoRecipients::get_customer_recipients_customers ($filter, $tot_recips);
		$default_recipients = InfoRecipients::get_customer_default_recipients_customers ($customer->id);
		if ($tot_recips < ($filter['start']))
		{
			$filter['start'] = intval ($tot_recips / $filter['limit']) * $filter['limit'];
			$recipients = InfoRecipients::get_customer_recipients_customers ($filter, $tot_recips);
		}
		$customers_users_list = User::get_customers_users_list ();
		
		$users_list = User::get_users_list (array('type' => (USER_TYPE_CUSTOMER+USER_TYPE_GROUP)));
		
		
		$customers_filter = array ('favorites_first' => $this->current_user->id);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
 		
		$pages = make_paging ($filter['limit'], $tot_recips);
		
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
		
		$this->assign ('recipients', $recipients);
		$this->assign ('default_recipients', $default_recipients);
		$this->assign ('filter', $filter);
		$this->assign ('pages', $pages);
		$this->assign ('tot_recips', $tot_recips);
		$this->assign ('users_list', $users_list);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('customers_users_list', $customers_users_list);
		$this->assign ('error_msg', error_msg ());
		if ($this->vars['do_filter']) $this->assign ('do_filter', 1);
		$this->set_form_redir ('manage_customer_recipients_customers_submit');
	
		$this->display ($tpl);
	}
	
	
	/** Saves the filtering criteria for the customer notifications recipients page */
	function manage_customer_recipients_customers_submit ()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_customer_recipients_customers', array('do_filter' =>1));
		
		if ($this->vars['go'] == 'prev') $this->vars['filter']['start']-= $this->vars['filter']['limit'];
		elseif ($this->vars['go'] == 'next') $this->vars['filter']['start']+= $this->vars['filter']['limit'];
		
		$_SESSION['customer_recipients_customers']['filter'] = $this->vars['filter'];
		
		return $ret;
	}
	
	
	/** Displays the page for selecting the customer users recipients for a specific customer */
	function notification_customer_recipients_customers_edit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		class_load ('InfoRecipients');
		$tpl = 'user/notification_customer_recipients_customers_edit.html';
		$customer = new Customer ($this->vars['customer_id']);
		
		if (!$customer->id) return $this->mk_redir ('manage_customer_recipients_customers');
		
		$recipients = InfoRecipients::get_customer_recipients_customers (array('customer_id' => $customer->id), $no_total);
		$default_recipients = InfoRecipients::get_customer_default_recipients_customers ($customer->id);
		$users = User::get_users (array('type' => USER_TYPE_CUSTOMER, 'customer_id' => $customer->id), $nocount);

		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		$this->assign ('customer', $customer);
		$this->assign ('ret', $this->vars['ret']);
		$this->assign ('recipients', $recipients);
		$this->assign ('default_recipients', $default_recipients);
		$this->assign ('users', $users);
		$this->assign ('error_msg', error_msg());
		
		$params = array ('customer_id' => $customer->id);
		if ($this->vars['ret']) $params['ret'] = $this->vars['ret'];
		if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
		$this->set_form_redir ('notification_customer_recipients_customers_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	function notification_customer_recipients_customers_edit_submit ()
	{
		check_auth (array('customer_id' => $this->vars['customer_id']));
		class_load ('InfoRecipients');
		$customer = new Customer ($this->vars['customer_id']);
		
		if ($this->vars['ret'] == 'customer') $ret = $this->mk_redir ('customer_edit', array ('id'=>$customer->id), 'customer');
		else
		{
			$params = array ();
			if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
			$ret = $this->mk_redir ('manage_customer_recipients_customers', $params);
		}

		if ($this->vars['save'] and $customer->id)
		{
			InfoRecipients::set_customer_recipients_customers ($customer->id, $this->vars['recipients'], $this->vars['is_default']); 
			
			$params = array ('customer_id' => $customer->id);
			if ($this->vars['ret'] == 'customer') $params['ret'] = $this->vars['ret'];
			if ($this->vars['do_filter']) $params['do_filter'] = $this->vars['do_filter'];
			$ret = $this->mk_redir ('notification_customer_recipients_customers_edit', $params);
		}
		
		return $ret;
	}
	
	function add_more_customers()
	{
		check_auth();
		class_load("Customer");
		class_load("User");
		$tpl = "user/add_more_customers.html";
		$user = new User($this->vars['user_id']);
		if(!$user->id) return $this->mk_redir('manage_users');
		$current_customers = $user->get_users_customer_list();
		$customers_list = array();
		foreach($current_customers as $c_id)
		{
			$customers_list[] = new Customer($c_id);
		}
		$all_customers = Customer::get_customers_list();
		
		$this->assign("user", $user);
		$this->assign("current_customers", $current_customers);
		$this->assign("all_customers", $all_customers);
		$this->assign("customers_list", $customers_list);
		$this->assign("error_msg", error_msg());
		
		$this->set_form_redir("add_more_customers_submit", array("ret"=>$this->vars['ret'], "user_id"=>$this->vars['user_id']));	
		$this->display($tpl);
		
	}
	function add_more_customers_submit()
	{
		check_auth();
		if(!$this->vars['user_id'] or !$this->vars['ret']) $ret=$this->mk_redir('manage_users');
		else $ret = $this->mk_redir($this->vars['ret'], array('id'=>$this->vars['user_id']));
		if($this->vars['save'])
		{
			//XXX here we'll do all the needed actions to add more customer accounts to one user account
			$user = new User($this->vars['user_id']);
			$user->set_customers_account_list($this->vars['added_customers']);
			$user->log_action_user(SET_CUSTOMER_ACT, $this->current_user->id);
			return $this->mk_redir('add_more_customers', array('user_id'=>$this->vars['user_id'], 'ret'=>$this->vars['ret']));
		}
		return $ret;
	}
	
	function merge_accounts()
	{
		check_auth();
		class_load('Customer');
		class_load('User');
		$user = new User($this->vars['user_id']);
		if(!$user->id) return $this->mk_redir('manage_users');
		$tpl = "user/merge_accounts.html";
		
		$all_users = User::get_users_list();
		$leave = 2;
		if($this->vars['merged_action']) $leave=$this->vars['merged_action'];
		
		$this->assign("leave", $leave);
		$this->assign("user", $user);
		$this->assign("all_users", $all_users);
		$this->assign("error_msg", error_msg());
		
		$this->set_form_redir("merge_accounts_submit", array('user_id'=>$this->vars['user_id'], 'ret'=>$this->vars['ret']));
		$this->display($tpl);
	}
	function merge_accounts_submit()
	{
		check_auth();
		if(!$this->vars['user_id'] or !$this->vars['ret']) return $this->mk_redir('manage_users');
		$ret = $this->mk_redir($this->vars['ret'], array('id'=>$this->vars['user_id']));
		if($this->vars['save'])
		{
			//XXXX here we'll call the merge accounts function
			$user = new User($this->vars['user_id']);
			$user->merge_users_accounts($this->vars['selected_users'], $this->vars['merged_action']);
			$user->log_action_user(MERGE_USERS_ACT, $this->current_user->id);
			return $this->mk_redir("merge_accounts", array('user_id'=>$this->vars['user_id'], 'ret'=>$this->vars['ret'], 'merged_action'=>$this->vars['merged_action']));
		}
		return $ret;
	}
	
	function manage_removed_users()
	{
		check_auth();
		class_load("RemovedUser");
		class_load("Customer");
		$tpl = "user/manage_removed_users.html";
		
		if ($this->vars['customer_id'])
		{
			// This is a request to view a specific customer users
			$_SESSION['manage_removed_users']['filter'] = array (
				'customer_id' => $this->vars['customer_id'],
				'type' => USER_TYPE_CUSTOMER
			);
		}
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_removed_users']['filter']['customer_id'] = $this->locked_customer->id;
		}
		
		$filter = $_SESSION['manage_removed_users']['filter'];
		if (!isset($filter['type'])) $filter['type'] = USER_TYPE_CUSTOMER;
		if (!isset($filter['order_by'])) $filter['order_by'] = 'name';
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'ASC';
	
		if ($filter['type'] == USER_TYPE_CUSTOMER)
		{ 
			// Extract the list of customers, eventually restricting only to the customers assigned to 
			// the current user, if he has restricted customer access.
			$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
			if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
			$customers_list = Customer::get_customers_list ($customers_filter);

			$this->assign ('customers_list', $customers_list);
		}
		else
		{
			unset ($filter['customer_id']); 
		}
		
		if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
		if (!isset($filter['limit'])) $filter['limit'] = 20;
		
		// Check if the user has restricted access to customers
		if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;

		$users = RemovedUser::get_removed_users($filter);
				 
		$users_count = count($users);
		if ($users_count < $filter['start'])
		{
			$filter['start'] = 0;
			$_SESSION['manage_removed_users']['filter']['start'] = 0;
			$users = RemovedUser::get_removed_users($filter);
		}
		$users_list = RemovedUser::get_users_list (array('type'=>USER_TYPE_CUSTOMER));
		$pages = make_paging ($filter['limit'], $users_count);
		
		// Mark the potential customer for locking
		if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];
		
		$extra_params = array ();
		
		$this->assign ('users', $users);
		$this->assign ('users_count', $users_count);
		$this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
		$this->assign ('pages', $pages);
		$this->assign ('start_prev', $filter['start'] - $filter['limit']);
		$this->assign ('start_next', $filter['start'] + $filter['limit']);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('users_list', $users_list);
		$this->assign ('sort_url', $this->mk_redir ('manage_removed_users_submit', $extra_params));
		$this->assign ('error_msg', error_msg());
		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
		$this->assign ('USER_ONLY_TYPES', $GLOBALS['USER_ONLY_TYPES']);
		$this->assign ('USER_TYPES', $GLOBALS['USER_TYPES']);
		$this->assign ('USER_STATUSES', $GLOBALS['USER_STATUSES']);
		$this->assign('error_msg', error_msg());
		$this->set_form_redir("manage_removed_users_submit");
		$this->display($tpl);
	}
	
	function manage_removed_users_submit()
	{
		check_auth ();
		$ret = $this->mk_redir ('manage_removed_users', array ('do_filter' => 1));
		
		if ($this->vars['order_by'] and $this->vars['order_dir'])
		{
			// This is a request to change the sorting order
			$_SESSION['manage_removed_users']['filter']['order_by'] = $this->vars['order_by'];
			$_SESSION['manage_removed_users']['filter']['order_dir'] = $this->vars['order_dir'];
		}
		else
		{
			$this->vars['filter']['order_by'] = $this->vars['order_by_bk'];
			$this->vars['filter']['order_dir'] = $this->vars['order_dir_bk'];
			$_SESSION['manage_removed_users']['filter'] = $this->vars['filter'];
		}
		//debug($_SESSION['manage_removed_users']);
		return $ret;
	}
	
	/**
	 * restores a  removed user
	 *
	 */
	function restore_removed_user()
	{
		check_auth(array("ruid"=>$this->vars['id']));
		class_load("RemovedUser");
		$ruser = new RemovedUser($this->vars['id']);
		if(!$ruser->id) return $this->mk_redir('manage_removed_users', array('do_filter'=>1));
		$ret = $this->mk_redir('user_edit', array('id' => $ruser->user_id));
		if($ruser->restore()) {
			$ruser->log_action_user(RESTORE_USER_ACT, $this->current_user->id);
			return $ret;
		}
		else return $this->mk_redir('manage_removed_users', array('do_filter'=>1));
	}

        function get_addressbook()
        {
            check_auth();
            $users = User::get_users(array(), $count);
            $xmlstr = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE html SYSTEM \"xml/html4-all.ent\">\n<AddressBook />";
            $adb = new SimpleXMLElement($xmlstr);
            foreach($users as $user)
            {
                if($user->lname!="" and $user->fname!=""){
                $contact = $adb->addChild("Contact");
                $contact->addChild("LastName", htmlentities($user->lname, ENT_COMPAT, "ISO-8859-1"));
                $contact->addChild("FirstName", htmlentities($user->fname, ENT_COMPAT, "ISO-8859-1"));
                $ph = $contact->addChild("Phone");
                foreach($user->phones as $phone)
                {
                    $ph->addChild("phonenumber", $phone->phone);
                    $ph->addChild("accountindex", "0");
                }
                }
            }
            header("Content-type: text/xml");
            echo $adb->asXML();
            
        }
}


?>

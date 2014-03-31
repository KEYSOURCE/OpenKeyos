<?php

/**
* Class for handling the display of the "Home" pages.
*
*/

class_load ('Notification');

class HomeDisplay extends BaseDisplay
{

	/** Class constructor, initializes the menu */
	function HomeDisplay ()
	{
		parent::BaseDisplay ();
	}

	
	/** Shows the main user page */
	function user_area()
	{
        //debug("HERE"); die;
		check_auth();

		if(!class_load('Task')) debug("class Task could not be loaded");
		class_load('Customer');
		class_load('InterventionLocation');
		$tpl = 'home/user_area.html';
		
		$favorite_customers_list = $this->current_user->get_favorite_customers_list ();
		$assigned_customers_list = $this->current_user->get_assigned_customers_list ();
		$group_assigned_customers_list = $this->current_user->get_group_assigned_customers_list ();
		
		$tasks = Task::get_tasks (array('user_id'=>$this->current_user->id, 'date'=>time()));
		$customers_list = Customer::get_customers_list (array('active'=>-1));
		$locations_list = InterventionLocation::get_locations_list ();
		$users_list = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
		
		$this->assign ('favorite_customers_list', $favorite_customers_list);
		$this->assign ('assigned_customers_list', $assigned_customers_list);
		$this->assign ('group_assigned_customers_list', $group_assigned_customers_list);
		$this->assign ('users_list', $users_list);
		$this->assign ('tasks', $tasks);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('locations_list', $locations_list);

		$this->assign ('PHONE_TYPES', $GLOBALS['PHONE_TYPES']);
                $this->assign ('error_msg', error_msg());
		
		$this->display ($tpl);
	}
	
	/** Displays the notifications for users */
	function notifications ()
	{
		check_auth();
		class_load ('Notification');
		class_load ('Ticket');
		$tpl = 'home/notifications.html';
		
		$filter = $_SESSION['notifications_filter'];
		$filter['show_ignored'] = (isset($filter['show_ignored']) ? $filter['show_ignored'] : true);
		
		if (!isset($filter['user_id']) or $this->current_user->is_customer_user()) $filter['user_id'] = get_uid();
		$notif_groups = Notification::get_notifications_grouped ($filter);
		
		// For notifications that have associated tickets, populate the tickets  information
		foreach ($notif_groups as $idx => $group)
		{
			foreach ($group['notifications'] as $i => $notification)
			{
				if ($notification->ticket_id)
				{
					$notif_groups[$idx]['notifications'][$i]->ticket = new Ticket ($notification->ticket_id);
				}
			}
		}
		
		// Get the users list
		$users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
		$groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
		$users_list = $users + $groups; 
		
		$this->assign ('notifications', $notifications);
		$this->assign ('notif_groups', $notif_groups);
		$this->assign ('filter', $filter);
		$this->assign ('users', $users);
		$this->assign ('users_list', $users_list);
		$this->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
		$this->assign ('NOTIF_CODES_TEXTS', $GLOBALS['NOTIF_CODES_TEXTS']);
		$this->assign ('NOTIF_OBJ_URLS', $GLOBALS['NOTIF_OBJ_URLS']);
		$this->assign ('ALERT_COLORS', $GLOBALS['ALERT_COLORS']);
		$this->assign ('TICKET_STATUSES', $GLOBALS['TICKET_STATUSES']);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('notifications_submit');
		$this->display ($tpl);
	}
	
	
	function notifications_submit ()
	{
		check_auth ();
		class_load ('Notification');
		
		$ret = $this->mk_redir ('notifications');
		if ($this->vars['ignore'])
		{
			// This is a request to ignore the selected notifications
			if (!empty($this->vars['selected_notifs']) and is_array($this->vars['selected_notifs']))
			{
				$notif_ids = $this->vars['selected_notifs'];
				foreach ($notif_ids as $id)
				{
					$notification = new Notification ($id);
					$notification->suspend_email = 1;
					$notification->save_data ();
				}
			}
			else error_msg ($this->get_string('NEED_SELECT_NOTIFICATION'));
		}
		elseif ($this->vars['unignore'])
		{
			// This is a request to un-ignore the selected notifications
			if (!empty($this->vars['selected_notifs']) and is_array($this->vars['selected_notifs']))
			{
				$notif_ids = $this->vars['selected_notifs'];
				foreach ($notif_ids as $id)
				{
					$notification = new Notification ($id);
					$notification->suspend_email = 0;
					$notification->save_data ();
				}
			}
			else error_msg ($this->get_string('NEED_SELECT_NOTIFICATION'));
		}
		else
		{
			// This is a request to show/hide the ignored notifications
			$_SESSION['notifications_filter'] = $this->vars['filter'];
		}
		
		return $ret;
	}

    function clear_and_redir(){
        $redir_url = $_REQUEST['redir_url'];
        self::clear_request_cache($redir_url);
    }

	/** Display a notification's details */
	function notification_view ()
	{
		check_auth ();
		$tpl = 'home/notification_view.html';
		
		$notification = new Notification ($this->vars['id']);
		if (!$notification->id) return $this->mk_redir ('notifications');
		$notification->load_users ();
		$notification->load_ticket ();
		
		// Mark the notification as read for the current user, and also update the counter
		$notification->mark_read ($this->current_user->id);
		$this->cnt_unread_notifications = NotificationRecipient::get_unread_notifs_count ($this->current_user->id);
		
		// Get the users list
		$users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
		$groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
		$users_list = $users + $groups; 
		
		$params = $this->set_carry_fields (array('id', 'returl'));
		
		$this->assign ('notification', $notification);
		$this->assign ('TICKET_STATUSES', $GLOBALS['TICKET_STATUSES']);
		$this->assign ('users_list', $users_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('notification_view_submit', $params);
		
		$this->display ($tpl);
	}
	
	function notification_view_submit ()
	{
		check_auth ();
		$notification = new Notification ($this->vars['id']);
		
		//$params = $this->set_carry_fields (array('id', 'returl'));
		if ($this->vars['returl']) $ret = $this->vars['returl'];
		else $ret = $this->mk_redir ('notifications');
		
		return $ret;
	}
	
	
	/** Displays the page for reassigning a notification to a new user */
	function notification_reassign ()
	{
		check_auth ();
		
		if (!is_array($this->vars['selected_notifs']) or count($this->vars['selected_notifs'])==0)
		{
			error_msg ($this->get_string('NEED_SELECT_NOTIFICATION'));
			return $this->mk_redir ('notifications');
		}
		
		class_load ('Notification');
		$tpl = 'home/notification_reassign.html';
		
		$users = User::get_users (array('type' => USER_TYPE_KEYSOURCE), $nocount);
		
		$this->assign ('users', $users);
		$this->set_form_redir ('notification_reassign_submit', array ('selected_notifs' => $this->vars['selected_notifs']));
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	
	/** Reassigns the notifications to the specified user */
	function notification_reassign_submit ()
	{
		check_auth ();
		class_load ('Notification');
		$ret = $this->mk_redir ('notifications');
		
		if ($this->vars['assign'] and is_array($this->vars['selected_notifs']))
		{
			if (isset($this->vars['user_id']))
			{
				$user_id = $this->vars['user_id'];
				foreach ($this->vars['selected_notifs'] as $notif_id)
				{
					$notification = new Notification ($notif_id);
					$old_user_id = $notification->user_id;
					$notification->user_id = $user_id;
					
					// Check if there is a need to send e-mail
					if (!$notification->suspend_email and $user_id!=get_uid() and $user_id!=$old_user_id)
					{
						$notification->emailed_last = 0;
					}
					
					$notification->save_data ();
				}
			}
		}
		
		return $ret;
	}
	
	
	/** Deletes a notification */
	function notification_delete ()
	{
		check_auth ();
		class_load ('Notification');
		$ret = $this->mk_redir ('notifications');
		
		if (!empty($this->vars['selected_notifs']) and is_array($this->vars['selected_notifs']))
		{
			foreach ($this->vars['selected_notifs'] as $id)
			{
				$notification = new Notification ($id);
				$notification->delete ();
			}
		}
		
		return $ret;
	}
	
	
	/** Sets the display language for the current session */
	function set_language ()
	{
		if ($this->vars['lang'] and isset($GLOBALS['LANGUAGES'][$this->vars['lang']]))
		{
			$_SESSION['USER_LANG'] = $this->vars['lang'];
			session_write_close ();
		}
		return $this->vars['returl'];
	}
}
?>
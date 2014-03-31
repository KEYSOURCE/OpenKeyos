<?php

class_load('MonitorProfile');
class_load('Computer');
class_load('RemovedComputer');
class_load('RemovedComputerNote');
class_load('Peripheral');
class_load('RemovedPeripheral');
class_load('AD_Printer');
class_load('RemovedAD_Printer');


/**
* Class for handling the display of KAWACS pages related to removed computers and peripherals
*
*/
class KawacsRemovedDisplay extends BaseDisplay
{
	function KawacsRemovedDisplay ()
	{
		parent::BaseDisplay ();
	}
	
	/****************************************************************/
	/* Management of removed computers				*/
	/****************************************************************/
	
	/** Displays the page for viewing removed computers */
	function manage_computers ()
	{
		$tpl = 'kawacs_removed/manage_computers.html';
		if (isset($this->vars['customer_id'])) $_SESSION['manage_computers']['customer_id'] = $this->vars['customer_id'];
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_computers']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_computers'];
		$filter['order_by'] = 'type';

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			unset ($_SESSION['manage_computers']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_computers']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kawacs customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		if ($filter['customer_id'] > 0)
		{
			$computers = RemovedComputer::get_removed_computers ($filter, $no_count);
		}
		
		$this->assign ('computers', $computers);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());
		$this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
		$this->set_form_redir ('manage_computers_submit');
		$this->display ($tpl);
	}
	
	function manage_computers_submit ()
	{
		$_SESSION['manage_computers'] = $this->vars['filter'];
		return $this->mk_redir ('manage_computers', array ('do_filter' => 1));
	}
	
	
	/** Displays the page with the details for a removed computer */
	function computer_view ()
	{
		$tpl = 'kawacs_removed/computer_view.html';
		$computer = new RemovedComputer ($this->vars['id']);
		if (!$computer->id) return $this->mk_redir ('manage_computers');
		
		$customer = new Customer ($computer->customer_id);
		check_auth (array('customer_id' => $customer->id));
		
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		// Get the list of notes for this removed computer
		$notes = RemovedComputerNote::get_computer_notes ($computer->id);
		
		// Get the users list
		$users = User::get_users_list (array('type' => USER_TYPE_KEYSOURCE));
		$groups = Group::get_usergroups_list (array('type' => USER_TYPE_KEYSOURCE_GROUP));
		$users_list = $users + $groups; 
		
		// Load the reported data
		$items = $computer->get_reported_items();
		
		
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('notes', $notes);
		$this->assign ('users_list', $users_list);
		$this->assign ('items', $items);
		$this->assign ('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
		$this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
		$this->assign ('profiles_list', MonitorProfile::get_profiles_list());
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	
	/** Displays a specific monitor item for a computer */
	function computer_view_item ()
	{
		$tpl = 'kawacs_removed/computer_view_item.html';
		
		$filter = $_SESSION['computer_view_item']['filter'];
		if (!isset($filter['start']) or $filter['start']<0) $filter['start'] = 0;
		if (!isset($filter['limit'])) $filter['limit'] = 50;
		
		$computer = new RemovedComputer ($this->vars['id']);
		$item_id = $this->vars['item_id'];
		if (!$computer->id or !$item_id) return $this->mk_redir('manage_computers');
		$item = $computer->get_item_by_id ($item_id);
		 
		// Build the paging, if needed
		$pages = array ();
		if (count ($item->val) > 0)
		{
			$cnt = 0;
			$tot_items = count($item->val);
			$pages = make_paging ($filter['limit'], $tot_items);
			if ($filter['start'] > $tot_items) $filter['start'] = 0;
			foreach ($item->val as $idx => $value)
			{
				if ($cnt<$filter['start'] or $cnt>$filter['start']+$filter['limit']) unset ($item->val[$idx]);
				$cnt++;
			}
		}
		
		$params = $this->set_carry_fields (array ('id', 'item_id'));
		
		$this->assign ('computer', $computer);
		$this->assign ('item', $item);
		$this->assign ('filter', $filter);
		$this->assign ('pages', $pages);
		$this->assign ('tot_items', $tot_items);
		$this->assign ('MONITOR_CAT', $GLOBALS['MONITOR_CAT']);
		$this->assign ('COMP_TYPE_NAMES', $GLOBALS['COMP_TYPE_NAMES']);
		$this->assign ('EVENTLOG_TYPES_ICONS', $GLOBALS['EVENTLOG_TYPES_ICONS']);
		$this->assign ('PER_PAGE_OPTIONS', $GLOBALS['PER_PAGE_OPTIONS']);
		$this->set_form_redir ('computer_view_item_submit', $parms);
		$this->display ($tpl);
	}
	
	function computer_view_item_submit ()
	{
		$filter = $this->vars['filter'];
		if ($this->vars['go'] == 'prev') $filter['start']-= $filter['limit'];
		elseif ($this->vars['go'] == 'next') $filter['start']+= $filter['limit'];
		$_SESSION['computer_view_item']['filter'] = $filter;
		$params = $this->set_carry_fields (array ('id', 'item_id'));
		return $this->mk_redir ('computer_view_item', $params);
	}
	
	/** Displays the page for editing the start and end dates during which the computer was monitored in Keyos */
	function computer_dates ()
	{
		$tpl = 'kawacs_removed/computer_dates.html';
		$computer = new RemovedComputer ($this->vars['id']);
		if (!$computer->id) return $this->mk_redir ('manage_computers');
		$customer = new Customer ($computer->customer_id);
		check_auth (array('customer_id' => $customer->id));
		
		$params = $this->set_carry_fields (array('id'));
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('computer_dates_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Sets the computer creation and removal dates */
	function computer_dates_submit ()
	{
		$computer = new RemovedComputer ($this->vars['id']);
		$params = $this->set_carry_fields (array('id'));
		$ret = $this->mk_redir ('computer_view', $params);
		
		if ($this->vars['save'] and $computer->id)
		{
			$data = $this->vars['computer'];
			$data['date_created'] = js_strtotime ($data['date_created']);
			$data['date_removed'] = js_strtotime ($data['date_removed']);
			$computer->load_from_array ($data);
			$computer->save_data ();
		}
		
		return $ret;
	}
	
	/** Displays the page for adding a new note for a computer */
	function computer_note_add ()
	{
		$tpl = 'kawacs_removed/computer_note_add.html';
		$computer = new RemovedComputer ($this->vars['computer_id']);
		if (!$computer->id) return $this->mk_redir ('manage_computers');
		$customer = new Customer ($computer->customer_id);
		check_auth (array('customer_id' => $customer->id));
		
		$params = $this->set_carry_fields (array('computer_id'));
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('computer_note_add_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves the newly created note */
	function computer_note_add_submit ()
	{
		$computer = new RemovedComputer ($this->vars['computer_id']);
		check_auth (array('customer_id' => $computer->customer_id));
		$ret = $this->mk_redir ('computer_view', array ('id' => $computer->id));
		$params = $this->set_carry_fields (array('computer_id'));
		
		if ($this->vars['save'] and $computer->id)
		{
			$data = $this->vars['note'];
			$note = new RemovedComputerNote ();
			$note->load_from_array ($data);
			$note->computer_id = $computer->id;
			$note->user_id = $this->current_user->id;
			
			if ($note->is_valid_data ())
			{
				$note->save_data ();
				unset ($params['computer_id']);
				$params['id'] = $note->id;
				$ret = $this->mk_redir ('computer_note_edit', $params);
			}
			else $ret = $this->mk_redir ('computer_note_add', $params);
		}
		
		return $ret;
	}
	
	
	/** Displays the page for editing a computer note */
	function computer_note_edit ()
	{
		$tpl = 'kawacs_removed/computer_note_edit.html';
		$note = new RemovedComputerNote ($this->vars['id']);
		$computer = new RemovedComputer ($note->computer_id);
		$user = new User ($note->user_id);
		
		if (!$note->id or !$computer->id) return $this->mk_redir ('manage_computers');
		check_auth (array('customer_id' => $computer->customer_id));
		
		$params = $this->set_carry_fields (array('id'));
		
		$this->assign ('note', $note);
		$this->assign ('computer', $computer);
		$this->assign ('user', $user);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('computer_note_edit_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Saves a modified computer note */
	function computer_note_edit_submit ()
	{
		$note = new RemovedComputerNote ($this->vars['id']);
		$computer = new RemovedComputer ($note->computer_id);
		check_auth (array('customer_id' => $computer->customer_id));
		
		$ret = $this->mk_redir ('computer_view', array('id' => $note->computer_id));
		$params = $this->set_carry_fields (array('id'));
		
		if ($this->vars['save'] and $note->id)
		{
			$note->load_from_array ($this->vars['note']);
			if ($note->is_valid_data ()) $note->save_data ();
			$ret = $this->mk_redir ('computer_note_edit', $params);
		}
		
		return $ret;
	}
	
	
	/** Deletes a computer note */
	function computer_note_delete ()
	{
		$note = new RemovedComputerNote ($this->vars['id']);
		$computer = new RemovedComputer ($note->computer_id);
		check_auth (array('customer_id' => $computer->customer_id));
		$note = new RemovedComputerNote ($this->vars['id']);
		$ret = $this->mk_redir ('computer_view', array ('id' => $note->computer_id));
		
		if ($note->id and $note->can_delete ()) $note->delete ();
		
		return $ret;
	}
	
	
	/** Displays the page for permanently deleting a removed computer from the database */
	function computer_delete ()
	{
		$tpl = 'kawacs_removed/computer_delete.html';
		$computer = new RemovedComputer ($this->vars['id']);
		if (!$computer->id) return $this->mk_redir ('manage_computers');
		$customer = new Customer ($computer->customer_id);
		check_auth (array('customer_id' => $customer->id));
		
		$params = $this->set_carry_fields (array('id'));
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('computer_delete_submit', $params);
		
		$this->display ($tpl);
	}
	
	
	/** Deletes a removed computer from the database */
	function computer_delete_submit ()
	{
		$computer = new RemovedComputer ($this->vars['id']);
		$customer = new Customer ($computer->customer_id);
		check_auth (array('customer_id' => $customer->id));
		
		if ($this->vars['delete'] and $computer->id)
		{
			$computer->delete ();
			$ret = $this->mk_redir ('manage_computers');
		}
		else 
		{
			$ret = $this->mk_redir ('computer_view', array('id' => $computer->id));
		}
		
		return $ret;
	}
	
	
	/** Displays the page for restoring a removed computer back to active state */
	function computer_restore ()
	{
		$tpl = 'kawacs_removed/computer_restore.html';
		$computer = new RemovedComputer ($this->vars['id']);
		if (!$computer->id) return $this->mk_redir ('manage_computers');
		$customer = new Customer ($computer->customer_id);
		check_auth (array('customer_id' => $customer->id));
		
		$params = $this->set_carry_fields (array('id'));
		$this->assign ('computer', $computer);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg());
		$this->set_form_redir ('computer_restore_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Reactivates a removed computer */
	function computer_restore_submit ()
	{
		$computer = new RemovedComputer ($this->vars['id']);
		$params = $this->set_carry_fields (array('id'));
		check_auth (array('customer_id' => $computer->customer_id));
		$ret = $this->mk_redir ('computer_view', $params);
		
		if ($this->vars['save'] and $computer->id)
		{
			$computer->restore_computer ();
			$ret = $this->mk_redir ('computer_view', $params, 'kawacs');
		}
		
		return $ret;
	}
	
	
	/** Displays the page allowing users to remove multiple computers for a customer */
	function remove_multi_computers ()
	{
		$tpl = 'kawacs_removed/remove_multi_computers.html';
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_computers');
		check_auth (array('customer_id' => $customer->id));
		
		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id));
		
		$params = $this->set_carry_fields (array('customer_id'));
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('remove_multi_computers_submit', $params);
		
		$this->display ($tpl);
	}
	
	function remove_multi_computers_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array ('customer_id' => $customer->id));
		$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id), 'customer');
		$params = $this->set_carry_fields (array('customer_id'));
		
		if ($this->vars['save'] and $customer->id)
		{
			$ids = $this->vars['computer_id'];
			if (is_array($ids) and count($ids)>0)
			{
				$params['computer_id'] = implode('_', $ids);
				$ret = $this->mk_redir ('remove_multi_computers_confirm', $params);
			}
			else
			{
				error_msg ($this->get_string('NEED_SELECT_REMOVE_COMPUTERS'));
				$ret = $this->mk_redir ('remove_multi_computers', $params);
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for confirming multiple removing of computers */
	function remove_multi_computers_confirm ()
	{
		$tpl = 'kawacs_removed/remove_multi_computers_confirm.html';
		$customer = new Customer ($this->vars['customer_id']);
		$computers_ids = explode ('_', $this->vars['computer_id']);
		
		if (!$customer->id or !is_array($computers_ids) or count($computers_ids)==0) return $this->mk_redir ('manage_computers');
		check_auth (array('customer_id' => $customer->id));
		
		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id));
		
		// Will use a generic RemovedComputer for passing removal date and reason
		$removal = new RemovedComputer ();
		$data = array ();
		if (!empty_error_msg()) $removal->load_from_array(restore_form_data ('remove_multi_computers_confirm', false, $data));
		if (!$removal->date_removed) $removal->date_removed = time ();
		
		$params = $this->set_carry_fields (array('customer_id', 'computer_id'));
		$this->assign ('customer', $customer);
		$this->assign ('removal', $removal);
		$this->assign ('computers_ids', $computers_ids);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('remove_multi_computers_confirm_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Performs the removal of multiple computers */
	function remove_multi_computers_confirm_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array('customer_id' => $customer->id));
		$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id), 'customer');
		$params = $this->set_carry_fields (array('customer_id', 'computer_id'));
		$computers_ids = explode ('_', $this->vars['computer_id']);
		
		if ($this->vars['save'] and $customer->id and is_array($computers_ids) and count($computers_ids)>0)
		{
			$data = $this->vars['removal'];
			$data['date_removed'] = js_strtotime ($data['date_removed']);
			
			// Will use a generic RemovedComputer object for testing data validity
			$removal = new RemovedComputer ();
			$removal->load_from_array ($data);
			
			if ($removal->is_valid_data ())
			{
				foreach ($computers_ids as $computer_id)
				{
					$computer = new Computer ($computer_id);
					RemovedComputer::remove_computer ($computer, $this->current_user->id, $data['reason_removed'], $data['date_removed']);
				}
				$ret = $this->mk_redir ('manage_computers', array ('customer_id' => $customer->id));
			}
			else
			{
				save_form_data ($data, 'remove_multi_computers_confirm');
				$ret = $this->mk_redir ('remove_multi_computers_confirm', $params);
			}
			
		}
		
		return $ret;
	}
	
	/****************************************************************/
	/* Management of removed Peripherals				*/
	/****************************************************************/
	
	/** Displays the page for managing removed peripherals */
	function manage_peripherals ()
	{
		$tpl = 'kawacs_removed/manage_peripherals.html';
		
		if (isset($this->vars['customer_id'])) $_SESSION['manage_peripherals']['customer_id'] = $this->vars['customer_id'];
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_peripherals']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_peripherals'];

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			unset ($_SESSION['manage_peripherals']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_peripherals']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kawacs customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		if ($filter['customer_id'] > 0)
		{
			$peripherals = RemovedPeripheral::get_peripherals ($filter);
			$classes_list = PeripheralClass::get_classes_list ();
		}
		
		$params = $this->set_carry_fields (array('do_filter'));
		$this->assign ('peripherals', $peripherals);
		$this->assign ('classes_list', $classes_list);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('manage_peripherals_submit', $params);
		
		$this->display ($tpl);
	}
	
	function manage_peripherals_submit ()
	{
		$_SESSION['manage_peripherals'] = $this->vars['filter'];
		return $this->mk_redir ('manage_peripherals', array('do_filter' => 1));
	}
	
	/** Displays the page for viewing a removed peripheral */
	function peripheral_view ()
	{
		$tpl = 'kawacs_removed/peripheral_view.html';
		$peripheral = new RemovedPeripheral ($this->vars['id']);
		if (!$peripheral->id) return $this->mk_redir ('manage_peripherals');
		check_auth (array('customer_id' => $peripheral->customer_id));
		
		$customer = new Customer ($peripheral->customer_id);
		$peripheral_class = new PeripheralClass ($peripheral->class_id);
		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id));
		$profile = $peripheral->get_monitoring_profile ();
		
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id; 
		
		$this->assign ('peripheral', $peripheral);
		$this->assign ('peripheral_class', $peripheral_class);
		$this->assign ('profile', $profile);
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	
	/** Permanently deletes a removed peripheral */
	function peripheral_delete ()
	{
		$peripheral = new RemovedPeripheral ($this->vars['id']);
		check_auth (array('customer_id' => $peripheral->customer_id));
		
		if ($peripheral->can_delete ()) $peripheral->delete ();
		
		return $this->mk_redir ('manage_peripherals');
	}
	

	/** Displays the page allowing users to remove multiple peripherals for a customer */
	function remove_multi_peripherals ()
	{
		$tpl = 'kawacs_removed/remove_multi_peripherals.html';
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_peripherals');
		check_auth (array('customer_id' => $customer->id));
		
		$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $customer->id));
		
		$params = $this->set_carry_fields (array('customer_id'));
		$this->assign ('customer', $customer);
		$this->assign ('peripherals_list', $peripherals_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('remove_multi_peripherals_submit', $params);
		
		$this->display ($tpl);
	}
	
	function remove_multi_peripherals_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array ('customer_id' => $customer->id));
		$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id), 'customer');
		$params = $this->set_carry_fields (array('customer_id'));
		
		if ($this->vars['save'] and $customer->id)
		{
			$ids = $this->vars['peripheral_id'];
			if (is_array($ids) and count($ids)>0)
			{
				$params['peripheral_id'] = implode('_', $ids);
				$ret = $this->mk_redir ('remove_multi_peripherals_confirm', $params);
			}
			else
			{
				error_msg ($this->get_string('NEED_SELECT_REMOVE_PERIPHERALS'));
				$ret = $this->mk_redir ('remove_multi_peripherals', $params);
			}
		}
		
		return $ret;
	}
	
	
	/** Displays the page for confirming multiple removing of peripherals */
	function remove_multi_peripherals_confirm ()
	{
		$tpl = 'kawacs_removed/remove_multi_peripherals_confirm.html';
		$customer = new Customer ($this->vars['customer_id']);
		$peripherals_ids = explode ('_', $this->vars['peripheral_id']);
		
		if (!$customer->id or !is_array($peripherals_ids) or count($peripherals_ids)==0) return $this->mk_redir ('manage_peripherals');
		check_auth (array('customer_id' => $customer->id));
		
		$peripherals_list = Peripheral::get_peripherals_list (array('customer_id' => $customer->id));
		
		// Will use a generic RemovedPeripheral for passing removal date and reason
		$removal = new RemovedPeripheral ();
		if (!empty_error_msg()) $removal->load_from_array(restore_form_data ('remove_multi_peripherals_confirm', false, $data));
		if (!$removal->date_removed) $removal->date_removed = time ();
		
		$params = $this->set_carry_fields (array('customer_id', 'peripheral_id'));
		$this->assign ('customer', $customer);
		$this->assign ('removal', $removal);
		$this->assign ('peripherals_ids', $peripherals_ids);
		$this->assign ('peripherals_list', $peripherals_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('remove_multi_peripherals_confirm_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Performs the removal of multiple peripherals */
	function remove_multi_peripherals_confirm_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array('customer_id' => $customer->id));
		$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id), 'customer');
		$params = $this->set_carry_fields (array('customer_id', 'peripheral_id'));
		$peripherals_ids = explode ('_', $this->vars['peripheral_id']);
		
		if ($this->vars['save'] and $customer->id and is_array($peripherals_ids) and count($peripherals_ids)>0)
		{
			$data = $this->vars['removal'];
			$data['date_removed'] = js_strtotime ($data['date_removed']);
			
			// Will use a generic RemovedPeripheral object for testing data validity
			$removal = new RemovedPeripheral ();
			$removal->load_from_array ($data);
			
			if ($removal->is_valid_data ())
			{
				foreach ($peripherals_ids as $peripheral_id)
				{
					$peripheral = new Peripheral ($peripheral_id);
					RemovedPeripheral::remove_peripheral ($peripheral, $this->current_user->id, $data['reason_removed'], $data['date_removed']);
				}
				$ret = $this->mk_redir ('manage_peripherals', array ('customer_id' => $customer->id));
			}
			else
			{
				save_form_data ($data, 'remove_multi_peripherals_confirm');
				$ret = $this->mk_redir ('remove_multi_peripherals_confirm', $params);
			}
			
		}
		
		return $ret;
	}
	
	/****************************************************************/
	/* Management of removed AD Printers				*/
	/****************************************************************/
	
	/** Displays the page for viewing removed AD Printers */
	function manage_ad_printers ()
	{
		$tpl = 'kawacs_removed/manage_ad_printers.html';
		if (isset($this->vars['customer_id'])) $_SESSION['manage_ad_printers']['customer_id'] = $this->vars['customer_id'];
		elseif ($this->locked_customer->id and !$this->vars['do_filter'])
		{
			// If 'do_filter' is present in request, the locked customer is ignored
			$_SESSION['manage_ad_printers']['customer_id'] = $this->locked_customer->id;
		}
		$filter = $_SESSION['manage_ad_printers'];

		// Check authorization
		if ($filter['customer_id'] > 0)
		{
			// Remove first the filtering on customer, in case the user gets redirected to the "Permission Denied" page.
			// This way he can return to this page, without getting again "Permission Denied".
			unset ($_SESSION['manage_ad_printers']['customer_id']);
			check_auth (array('customer_id' => $filter['customer_id']));
			$_SESSION['manage_ad_printers']['customer_id'] = $filter['customer_id'];
		}
		else check_auth ();
		
		// Extract the list of Kawacs customers, eventually restricting only to the customers assigned to 
		// the current user, if he has restricted customer access.
		$customers_filter = array ('favorites_first' => $this->current_user->id, 'show_ids' => 1);
		if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
		$customers_list = Customer::get_customers_list ($customers_filter);
		
		if ($filter['customer_id'] > 0)
		{
			$ad_printers = RemovedAD_Printer::get_removed_ad_printers ($filter);
		}
		
		$this->assign ('ad_printers', $ad_printers);
		$this->assign ('filter', $filter);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('manage_ad_printers_submit');
		$this->display ($tpl);
	}
	
	function manage_ad_printers_submit ()
	{
		$_SESSION['manage_ad_printers'] = $this->vars['filter'];
		return $this->mk_redir ('manage_ad_printers', array('do_filter' => 1));
	}
	
	/** Displays the page for performing the removal of an AD Printer */
	function ad_printer_remove ()
	{
		$tpl = 'kawacs_removed/ad_printer_remove.html';
		$ad_printer = AD_Printer::get_by_id ($this->vars['id']);
		if (!$ad_printer->id) return $this->mk_redir ('manage_ad_printers', array(), 'kerm');
		check_auth (array('customer_id' => $ad_printer->customer_id));
		$customer = new Customer ($ad_printer->customer_id);
		
		// Use a dummy RemovedAD_Printer for preserving form data
		$removal = new RemovedAD_Printer ();
		$data = array ();
		if (!empty_error_msg()) $removal->load_from_array(restore_form_data ('ad_printer_remove', false, $data));
		if (!$removal->date_removed) $removal->date_removed = time ();
		
		$params = $this->set_carry_fields (array('id'));
		$this->assign ('ad_printer', $ad_printer);
		$this->assign ('removal', $removal);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('ad_printer_remove_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Performs the removal of an AD Printer */
	function ad_printer_remove_submit ()
	{
		$ad_printer = AD_Printer::get_by_id ($this->vars['id']);
		check_auth (array('customer_id' => $ad_printer->customer_id));
		$params = $this->set_carry_fields (array('id'));
		$ret = $this->mk_redir ('manage_ad_printers', array(), 'kerm');
		
		if ($this->vars['save'] and $ad_printer->id)
		{
			$data = $this->vars['removal'];
			$data['date_removed'] = js_strtotime ($data['date_removed']);
			$removal = new RemovedAD_Printer ();
			$removal->load_from_array ($data); 
			
			if ($removal->is_valid_data ())
			{
				RemovedAD_Printer::remove_ad_printer ($ad_printer, $this->current_user->id, $data['reason_removed'], $data['date_removed']);
				$ret = $this->mk_redir ('ad_printer_view', $params);
			}
			else
			{
				save_form_data ($data, 'ad_printer_remove');
				$ret = $this->mk_redir ('ad_printer_remove', $params);
			}
		}
		
		return $ret;
	}
	
	/** Displays the page for removing multiple AD Printers */
	function remove_multi_ad_printers ()
	{
		$tpl = 'kawacs_removed/remove_multi_ad_printers.html';
		$customer = new Customer ($this->vars['customer_id']);
		if (!$customer->id) return $this->mk_redir ('manage_ad_printers');
		check_auth (array('customer_id' => $customer->id));
		
		$ad_printers = AD_Printer::get_orphan_ad_printers ($customer->id);
		
		$params = $this->set_carry_fields (array('customer_id'));
		$this->assign ('customer', $customer);
		$this->assign ('ad_printers', $ad_printers);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('remove_multi_ad_printers_submit', $params);
		
		$this->display ($tpl);
	}
	
	function remove_multi_ad_printers_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array ('customer_id' => $customer->id));
		$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id), 'customer');
		$params = $this->set_carry_fields (array('customer_id'));
		
		if ($this->vars['save'] and $customer->id)
		{
			$ids = $this->vars['ad_printer_id'];
			if (is_array($ids) and count($ids)>0)
			{
				$params['ad_printer_id'] = implode('_', $ids);
				$ret = $this->mk_redir ('remove_multi_ad_printers_confirm', $params);
			}
			else
			{
				error_msg ($this->get_string('NEED_SELECT_REMOVE_COMPUTERS'));
				$ret = $this->mk_redir ('remove_multi_ad_printers', $params);
			}
		}
		
		return $ret;
	}
	
	/** Displays the page for confirming multiple removing of AD Printers */
	function remove_multi_ad_printers_confirm ()
	{
		$tpl = 'kawacs_removed/remove_multi_ad_printers_confirm.html';
		$customer = new Customer ($this->vars['customer_id']);
		$ad_printers_ids = explode ('_', $this->vars['ad_printer_id']);
		
		if (!$customer->id or !is_array($ad_printers_ids) or count($ad_printers_ids)==0) return $this->mk_redir ('manage_ad_printers');
		check_auth (array('customer_id' => $customer->id));
		
		$ad_printers = AD_Printer::get_orphan_ad_printers ($customer->id);
		
		// Will use a generic RemovedAD_Printer for passing removal date and reason
		$removal = new RemovedAD_Printer ();
		if (!empty_error_msg()) $removal->load_from_array(restore_form_data ('remove_multi_ad_printers_confirm', false, $data));
		if (!$removal->date_removed) $removal->date_removed = time ();
		
		$params = $this->set_carry_fields (array('customer_id', 'ad_printer_id'));
		$this->assign ('customer', $customer);
		$this->assign ('removal', $removal);
		$this->assign ('ad_printers_ids', $ad_printers_ids);
		$this->assign ('ad_printers', $ad_printers);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('remove_multi_ad_printers_confirm_submit', $params);
		
		$this->display ($tpl);
	}
	
	/** Performs the removal of multiple AD Printers */
	function remove_multi_ad_printers_confirm_submit ()
	{
		$customer = new Customer ($this->vars['customer_id']);
		check_auth (array('customer_id' => $customer->id));
		$ret = $this->mk_redir ('customer_edit', array ('id' => $customer->id), 'customer');
		$params = $this->set_carry_fields (array('customer_id', 'ad_printer_id'));
		$ad_printers_ids = explode ('_', $this->vars['ad_printer_id']);
		
		if ($this->vars['save'] and $customer->id and is_array($ad_printers_ids) and count($ad_printers_ids)>0)
		{
			$data = $this->vars['removal'];
			$data['date_removed'] = js_strtotime ($data['date_removed']);
			
			// Will use a generic RemovedComputer object for testing data validity
			$removal = new RemovedAD_Printer ();
			$removal->load_from_array ($data);
			
			if ($removal->is_valid_data ())
			{
				foreach ($ad_printers_ids as $ad_printer_id)
				{
					$ad_printer = AD_Printer::get_by_id ($ad_printer_id);
					RemovedAD_Printer::remove_ad_printer ($ad_printer, $this->current_user->id, $data['reason_removed'], $data['date_removed']);
				}
				$ret = $this->mk_redir ('manage_ad_printers', array ('customer_id' => $customer_id));
			}
			else
			{
				save_form_data ($data, 'remove_multi_ad_printers_confirm');
				$ret = $this->mk_redir ('remove_multi_ad_printers_confirm', $params);
			}
			
		}
		
		return $ret;
	}
	
	/** Displays a removed AD Printer */
	function ad_printer_view ()
	{
		class_load ('SupplierServicePackage');
		class_load ('ServiceLevel');
		$tpl = 'kawacs_removed/ad_printer_view.html';
		$ad_printer = new RemovedAD_Printer ($this->vars['id']);
		if (!$ad_printer->id) return $this->mk_redir ('manage_ad_printers');
		$customer = new Customer ($ad_printer->customer_id);
		
		// Mark the potential customer for locking
		$_SESSION['potential_lock_customer_id'] = $customer->id;
		
		// Load the monitoring profile, if any is set
		if ($ad_printer->profile_id) $monitor_profile = new MonitorProfilePeriph ($ad_printer->profile_id);
		
		$service_packages_list = SupplierServicePackage::get_service_packages_list (array('prefix_supplier'=>true));
		$service_levels_list = ServiceLevel::get_service_levels_list ();
		$computers_list = Computer::get_computers_list (array('customer_id' => $customer->id));
		$ad_printer->load_location ();
		
		$params = $this->set_carry_fields (array('id'));
		$this->assign ('ad_printer', $ad_printer);
		$this->assign ('customer', $customer);
		$this->assign ('monitor_profile', $monitor_profile);
		$this->assign ('customer', $customer);
		$this->assign ('computers_list', $computers_list);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	/** Displays the page for editing the managing dates and removal reason for an AD Printer */
	function ad_printer_dates ()
	{
		$tpl = 'kawacs_removed/ad_printer_dates.html';
		$ad_printer = new RemovedAD_Printer ($this->vars['id']);
		if (!$ad_printer->id) return $this->mk_redir ('manage_ad_printers');
		$customer = new Customer ($ad_printer->customer_id);
		check_auth (array('customer_id' => $customer->id));
		
		if (!empty_error_msg()) $ad_printer->load_from_array(restore_form_data ('ad_printer_dates', false, $data));
		
		$params = $this->set_carry_fields (array('id'));
		$this->assign ('ad_printer', $ad_printer);
		$this->assign ('customer', $customer);
		$this->assign ('error_msg', error_msg ());
		$this->set_form_redir ('ad_printer_dates_submit', $params);
		$this->display ($tpl);
	}
	
	/** Sets the dates and removal reason for an AD Printer */
	function ad_printer_dates_submit ()
	{
		$ad_printer = new RemovedAD_Printer ($this->vars['id']);
		$params = $this->set_carry_fields (array('id'));
		$ret = $this->mk_redir ('ad_printer_view', $params);
		check_auth (array('customer_id' => $ad_printer->customer_id));
		
		if ($this->vars['save'] and $ad_printer->id)
		{
			$data = $this->vars['ad_printer'];
			$data['date_created'] = js_strtotime ($data['date_created']);
			$data['date_removed'] = js_strtotime ($data['date_removed']);
			$ad_printer->load_from_array ($data);
			
			if ($ad_printer->is_valid_data ()) $ad_printer->save_data ();
			else
			{
				save_form_data ($data, 'ad_printer_dates');
				$ret = $this->mk_redir ('ad_printer_dates', $params);
			}
		}
		
		return $ret;
	}
	
	/** Permanently deletes a removed AD Printer */
	function ad_printer_delete ()
	{
		$ad_printer = new RemovedAD_Printer ($this->vars['id']);
		check_auth (array('customer_id' => $ad_printer->customer_id));
		$ret = $this->mk_redir ('manage_ad_printers');
		
		if ($ad_printer->can_delete ()) $ad_printer->delete ();
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Management of inactive customers with active devices		*/
	/****************************************************************/
	
	/** Displays the page showing inactive customers which still have computers */
	function customers_inactive_computers ()
	{
		check_auth ();
		$tpl = 'kawacs_removed/customers_inactive_computers.html';
		
		$customers_computers = RemovedComputer::get_inactive_customers_with_computers ();
		$customers_list = Customer::get_customers_list (array('active'=>0, 'append_id'=>1));
		
		// Calculate the total number of computers
		$tot_computers_count = 0;
		foreach ($customers_computers as $customer_id => $cnt) $tot_computers_count+= $cnt;
		
		$this->assign ('customers_computers', $customers_computers);
		$this->assign ('tot_computers_count', $tot_computers_count);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
	
	/** Displays the page showing inactive customers which still have peripherals */
	function customers_inactive_peripherals ()
	{
		check_auth ();
		$tpl = 'kawacs_removed/customers_inactive_peripherals.html';
		
		$customers_peripherals = RemovedPeripheral::get_inactive_customers_with_peripherals ();
		$customers_list = Customer::get_customers_list (array('active'=>0, 'append_id'=>1));
		
		// Calculate the total number of peripherals
		$tot_peripherals_count = 0;
		foreach ($customers_peripherals as $customer_id => $cnt) $tot_peripherals_count+= $cnt;
		
		$this->assign ('customers_peripherals', $customers_peripherals);
		$this->assign ('tot_peripherals_count', $tot_peripherals_count);
		$this->assign ('customers_list', $customers_list);
		$this->assign ('error_msg', error_msg ());
		
		$this->display ($tpl);
	}
}

?>
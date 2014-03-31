<?php 

class_load ('InterventionLocation');
class_load ('Location');
class_load ('ExchangeInterface');

/**
* Class for managing KRIFS scheduled tasks
*
* A task is associated to a ticket and a user for a specific date. 
*
*/

class Task extends Base
{
	/** Task ID
	* @var int */
	var $id = null;
	
	/** The user ID
	* @var int */
	var $user_id = null;
	
	/** The ticket ID
	* @var int */
	var $ticket_id = null;
	
	/** The date (day only, no time) when the task is scheduled [OBSOLETE]
	* @var timestamp */
	var $date = 0;
	
	/** The hour when the task is scheduled (hh:mm string) - optional [OBSOLETE]
	* @var string */
	var $hour = '';
	
	/** The estimated duration for the task (hh:mm string) - optional [OBSOLETE]
	* @var string */
	var $duration = '';
	
	
	/** The start time for the task 
	* @var timestamp */
	var $date_start = '';
	
	/** The end time for the task
	* @var timestamp */
	var $date_end = '';
	
	
	/** The completion status of the task (in percents)
	* @var int */
	var $completed = 0;
	
	/** The relative order (priority) of this task - unique for each user each day
	* @var int */
	var $ord = -1;
	
	/** The ID of the location (InterventionLocation) where the task is to take place
	* @var int */
	var $location_id = null;
	
	/** The ID of a customer location where the task is to take place
	* @var int */
	var $customer_location_id = null;
	
	/** Comments about the task
	* @var text */
	var $comments = '';
	
	/** The ID of the user who created the task
	* @var int */
	var $created_by_id = 0;
	
	/** The date when the task was created
	* @var timestamp */
	var $created_date = 0;
	
	/** The date when the task was modified
	* @var timestamp */
	var $modified_date = 0;
	
	/** The Exchange UID of the task - if it was created in Exchange
	* @var string */
	var $exchange_uid = '';
	
	/** Array with the user IDs of the attendees to this task 
	* @var array */
	var $attendees_ids = array ();
	
	
	/** The subject of the linked ticket
	* @var string */
	var $ticket_subject = '';
	
	/** The ID of the customer to whom the ticket belongs
	* @var int */
	var $customer_id = 0;
	
	/** The name of the customer location - if any 
	* @var string */
	var $customer_location_name = '';
	
	
	var $table = TBL_TASKS;
	var $fields = array ('id', 'user_id', 'ticket_id', 'date_start', 'date_end', 'completed', 'ord', 'location_id', 'customer_location_id', 'comments', 'created_by_id', 'created_date', 'modified_date', 'exchange_uid');


	/**
	* Constructor. Also loads a task detail data if an ID is provided
	* @param	int	$id		The ID of the ticket detail to load
	*/
	function Task ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	/** Loads the task data, as well as the subject and customer ID from the linked ticket */
	function load_data ()
	{
		parent::load_data ();
		if ($this->ticket_id)
		{
			// Using a trick in order to make a single request to database
			$q = 'SELECT customer_id, subject FROM '.TBL_TICKETS.' WHERE id='.$this->ticket_id;
			$data = $this->db_fetch_list ($q);
			foreach ($data as $customer_id => $subject)
			{
				$this->ticket_subject = $subject;
				$this->customer_id = $customer_id;
			}
			
			// Load the location name, if set
			if ($this->customer_location_id)
			{
				$this->customer_location_name = Location::get_location_str ($this->customer_location_id);
			}
			
			// Load the list of attendees IDs
			$q = 'SELECT a.user_id FROM '.TBL_TASKS_ATTENDEES.' a INNER JOIN '.TBL_USERS.' u ON a.user_id=u.id ';
			$q.= 'WHERE a.task_id='.$this->id.' ORDER BY u.fname, u.lname ';
			$this->attendees_ids = $this->db_fetch_vector($q);
		}
		// For backwards compatibility
		$this->date = $this->date_start;
	}
	
	
	function load_from_array ($data = array())
	{
		parent::load_from_array ($data);
		if (isset($data['attendees_ids']) and is_array($data['attendees_ids'])) $this->attendees_ids = $data['attendees_ids'];
	}
	
	/** Checks if the task data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->user_id) {$ret = false; error_msg ('Please specify the user.');}
		if (!$this->location_id) {$ret = false; error_msg ('Please specify the location.');}
		if (!$this->ticket_id) {$ret = false; error_msg ('Please specify the ticket.');}
		
		if ($this->date_start<=0 or $this->date_end<=0)
		{
			$ret = false;
			error_msg ('Please specify valid date, start and end times.');
		}
		else
		{
			if ($this->date_start >= $this->date_end)
			{
				$ret = false;
				error_msg ('The start time can\'t be after the end time.');
			}
			if ($this->date_start < time())
			{
				$ret = false;
				error_msg ('The start time can\'t be in the past.');
			}
		}
		
		if ($ret)
		{
			// Check uniqueness - organizer
			$q = 'SELECT id FROM '.TBL_TASKS.' WHERE user_id='.$this->user_id.' AND ticket_id='.$this->ticket_id.' AND ';
			$q.= '(date_start>='.get_first_hour($this->date_start).' AND date_start<='.get_last_hour($this->date_end).') ';
			if ($this->id) $q.= 'AND id<>'.$this->id.' ';
			if ($this->db_fetch_field ($q, 'id'))
			{
				$ret = false;
				error_msg ('There is already a scheduled task for the same organizer and same ticket on the same date.');
			}
			
			// Check uniqueness - attendees
			if (count($this->attendees_ids) > 0)
			{
				$conflict_attendees_ids = array ();
				foreach ($this->attendees_ids as $attendee_id)
				{
					$q = 'SELECT t.id FROM '.TBL_TASKS.' t LEFT OUTER JOIN '.TBL_TASKS_ATTENDEES.' a ON t.id=a.task_id ';
					$q.= 'WHERE (a.user_id='.$attendee_id.' OR t.user_id='.$attendee_id.') AND t.ticket_id='.$this->ticket_id.' AND ';
					$q.= '(t.date_start>='.get_first_hour($this->date_start).' AND t.date_start<='.get_last_hour($this->date_end).') ';
					if ($this->id) $q.= 'AND t.id<>'.$this->id.' ';
					if ($this->db_fetch_field ($q, 'id')) $conflict_attendees_ids[] = $attendee_id;
				}
				if (count($conflict_attendees_ids) > 0)
				{
					$q = 'SELECT id, concat(fname," ",lname) as name FROM '.TBL_USERS.' WHERE ';
					foreach ($conflict_attendees_ids as $attendee_id) $q.= 'id='.$attendee_id.' OR ';
					$q = preg_replace ('/OR\s*$/', '', $q);
					$conflict_attendees_names = $this->db_fetch_list($q);
				
					$ret = false;
					if (count($conflict_attendees_ids) == 1)
					{
						error_msg ('The attendee '.$conflict_attendees_names[$conflict_attendees_ids[0]].' already has a scheduled task for the same ticket on the same date.');
					}
					else
					{
						$msg = 'The following attendees already have scheduled tasks for the same ticket on the same date: ';
						foreach ($conflict_attendees_names as $name) $msg.= $name.', ';
						error_msg (preg_replace ('/\,\s*$/', '', $msg));
					}
				}
			}
		}
		
		return $ret;
	}
	
	/** Saves object data and initializes the creation date, if needed */
	function save_data ()
	{
		// First, check for previous organizer and attendees changes and see if any deletions need to be done from Exchange
		if ($this->id and $this->exchange_uid)
		{
			$q = 'SELECT user_id FROM '.TBL_TASKS.' WHERE id='.$this->id;
			$old_user_id = $this->db_fetch_field ($q, 'user_id');
			if ($this->user_id != $old_user_id)
			{
				// The organizer has been changed, delete the appointment completly from Exchange
				$this->delete_exchange ();
			}
			else
			{
				// Compare the list of attendees and delete from Exchange calendars for attendees which are not involved anymore
				$q = 'SELECT user_id FROM '.TBL_TASKS_ATTENDEES.' WHERE task_id='.$this->id;
				$old_attendees_ids = $this->db_fetch_vector ($q);
				
				foreach ($old_attendees_ids as $old_attendee_id)
				{
					if (!in_array($old_attendee_id, $this->attendees_ids)) $this->delete_exchange_attendee ($old_attendee_id);
				}
			}
		}
		
		if (!$this->created_date) $this->created_date = time ();
		$this->modified_date = time ();
		parent::save_data ();
		// Backup the list of attendees, because reloading the data will delete them
		$bk_attendees_ids = $this->attendees_ids;
		$this->load_data (); // Re-load all data, to make sure we have correct object info before saving to Exchange
		$this->attendees_ids = $bk_attendees_ids;
		
		if ($this->id)
		{
			// Save the list of attendees. First delete the old list.
			$this->db_query ('DELETE FROM '.TBL_TASKS_ATTENDEES.' WHERE task_id='.$this->id);
			if (count($this->attendees_ids) > 0)
			{
				// Make sure the organizer is not in the list
				if (in_array($this->user_id, $this->attendees_ids))
				{
					$pos = array_search ($this->user_id, $this->attendees_ids);
					unset ($this->attendees_ids[$pos]);
					ksort ($this->attendees_ids);
				}
				
				if (count($this->attendees_ids) > 0)
				{
					$q = 'INSERT INTO '.TBL_TASKS_ATTENDEES.' (task_id, user_id) VALUES ';
					foreach ($this->attendees_ids as $id) $q.= '('.$this->id.','.$id.'),';
					$q = preg_replace ('/\,\s*$/', '', $q);
					$this->db_query ($q);
				}
			}
			
			// Now attempt to save the appointment into exchange
			$exchange_uid = $this->exchange_uid;
			$attendees_no_exchange = array ();
			$res = ExchangeInterface::save_appointment ($this, $exchange_uid, $attendees_no_exchange);
			
			// If an Exchange UID was returned by the function, update it in the database
			if ($exchange_uid)
			{
				$this->exchange_uid = $exchange_uid;
				$q = 'UPDATE '.TBL_TASKS.' SET exchange_uid="'.mysql_escape_string($exchange_uid).'" WHERE id='.$this->id;
				$this->db_query ($q);
				
			}
			
			// See if there were any errors in creating the task in Exchange
			if (($res & EXCH_APP_RES_FAILED_CONNECT) == EXCH_APP_RES_FAILED_CONNECT)
			{
				error_msg ('Warning: Failed connecting to Exchange server.');
			}
			if (($res & EXCH_APP_RES_FAILED_CREATE) == EXCH_APP_RES_FAILED_CREATE)
			{
				error_msg ('Warning: Failed creating task in Exchange.');
			}
			if (($res & EXCH_APP_RES_FAILED_ORGANIZER_LOGIN) == EXCH_APP_RES_FAILED_ORGANIZER_LOGIN)
			{
				error_msg ('Warning: Failed connecting to organizer\'s Exchange Calendar.');
			}
			if (($res & EXCH_APP_RES_FAILED_ATTENDEES_LOGIN) == EXCH_APP_RES_FAILED_ATTENDEES_LOGIN)
			{
				$msg = 'Warning: Failed connecting to one or more attendees\' Exchange Calendars: ';
				foreach ($attendees_no_exchange as $attendee) $msg.= $attendee->get_name().', ';
				error_msg (preg_replace ('/\,\s*$/', '', $msg));
			}
		}
	}
	
	/** Deletes a task and the list of attendees */
	function delete ()
	{
		if ($this->id)
		{
			// Delete attendees
			$this->db_query ('DELETE FROM '.TBL_TASKS_ATTENDEES.' WHERE task_id='.$this->id);
			
			// Delete the task from Exchange
			$this->delete_exchange ();
			
			// Delete the object itself
			parent::delete ();
		}
	}
	
	/** Deletes a task from Exchange */
	function delete_exchange ()
	{
		if ($this->exchange_uid)
		{
			ExchangeInterface::delete_appointment ($this);
			$this->exchange_uid = '';
		}
	}
	
	/** Deletes a task from Exchange only for a specific attendee */
	function delete_exchange_attendee ($attendee_id)
	{
		if ($this->exchange_uid)
		{
			ExchangeInterface::delete_appointment ($this, $attendee_id);
		}
	}
	
	
	/** Resorts the tasks in the same day and for the same user as this one, to
	* ensure unicity of the ord field. Can be called as class method too, in which case the 
	* user ID and date must be specified */
	function resort ($user_id=null, $date=null)
	{
		if (!$user_id) $user_id = $this->user_id;
		if (!$date) $date = $this->date_start;
		
		if ($user_id and $date)
		{
			$d1 = get_first_hour ($date);
			$d2 = get_last_hour ($date);
			
			$q = 'SELECT id, ord FROM '.TBL_TASKS.' WHERE user_id='.$user_id.' AND ';
			$q.= 'date_start>='.$d1.' AND date_start<='.$d2.' ';
			$q.= 'ORDER BY ord, date_start, ticket_id ';
			$ids = DB::db_fetch_list ($q);
			
			$cnt = 1;
			foreach ($ids as $id => $ord)
			{
				if ($cnt!=$ord) DB::db_query ('UPDATE '.TBL_TASKS.' SET ord='.$cnt.' WHERE id='.$id);
				$cnt++;
			}
		}
	}
	
	/** [Class Method] Sets a new tasks order for a user and a day */
	function set_order ($user_id, $date, $order)
	{
		if ($user_id and $date and is_array ($order))
		{
			foreach ($order as $ord => $id)
			{
				DB::db_query ('UPDATE '.TBL_TASKS.' SET ord='.$ord.' WHERE id='.$id);
			}
		}
	}
	
	/** Checks if notifications need to be send to the user, by comparing with an older copy of the notification */
	function need_notif ($old_task)
	{
		return ($this->date_start!=$old_task->date_start or $this->date_end!=$old_task->date_end or $this->user_id!=$old_task->user_id or $this->location_id!=$old_task->location_id or $this->attendees_ids!=$old_task->attendees_ids);
	}
	
	
	/** Sends an e-mail to the user, informing him about a change to the task */
	function send_notification ($type = TASK_NOTIF_NEW, $modified_by_id = 0, $old_task = null, $recipient_id = null)
	{
		if ($this->id)
		{
			$recipients_ids = array ();
			if ($recipient_id)
			{
				if (is_array($recipient_id)) $recipients_ids = $recipient_id;
				else $recipients_ids[] = $recipient_id;
			}
			else
			{
				$recipients_ids = $this->attendees_ids;
				$recipients_ids[] = $this->user_id;
			}
			if ($modified_by_id)
			{
				if (in_array($modified_by_id, $recipients_ids))
				{
					$pos = array_search ($modified_by_id, $recipients_ids);
					unset ($recipients_ids[$pos]);
					$recipients_ids = array_values ($recipients_ids);
				}
			}
			
			if (count($recipients_ids) > 0)
			{
				class_load ('InterventionLocation');
				$parser = new BaseDisplay ();
				$parser->assign ('modified_by_id', $modified_by_id);
				$parser->assign ('customer', new Customer ($this->customer_id));
				$parser->assign ('locations_list', InterventionLocation::get_locations_list ());
				$parser->assign ('users_list', User::get_users_list (array('type' => USER_TYPE_KEYSOURCE)));
				$parser->assign ('task', $this);
				
				switch ($type)
				{
					case TASK_NOTIF_NEW:
						$tpl = '_classes_templates/krifs/msg_task_new.tpl';
						$subject = '[KeyOS] New task assigned - ticket #'.$this->ticket_id.': '.$this->ticket_subject;
						break;
					case TASK_NOTIF_MODIFIED:
						$tpl = '_classes_templates/krifs/msg_task_modified.tpl';
						$subject = '[KeyOS] Task updated - ticket #'.$this->ticket_id.': '.$this->ticket_subject;
						$parser->assign ('old_task', $old_task);
						break;
					case TASK_NOTIF_DELETED:
						$tpl = '_classes_templates/krifs/msg_task_deleted.tpl';
						$subject = '[KeyOS] Task deleted - ticket #'.$this->ticket_id.': '.$this->ticket_subject;
						break;
				}
				
				if ($subject)
				{
					foreach ($recipients_ids as $recipient_id)
					{
						$recipient = new User ($recipient_id);
						$parser->assign ('recipient', $recipient);
						$parser->assign ('not_involved', !($this->user_id==$recipient->id or in_array($recipient->id,$this->attendees_ids)));
						
						$msg = $parser->fetch ($tpl);
						$headers = 'From: '.SENDER_NAME.' <'.SENDER_EMAIL.'>'."\n";
						$headers.= 'Date: '.date("D, j M Y G:i:s O")."\n";
						
						@mail ($recipient->email, $subject, $msg, $headers);
					}
				}
			}
		}
	}
	
	
	/** [Class Method] Returns tasks by a specified criteria.
	* @param	array					$filter		Associative array with the filtering criteria. Can contain:
	*									- date: Returns only tasks in the specified date
	*									- user_id: Returns only tasks involving this user (organizer or attendee)
	*									- ticket_id: Returns only tasks for this ticket ID
	*									- order_by: Can be 'date_start' or 'ord'. Default is 'date_start'
	* @return	array(Task)						Array with the matched tasks.
	*/
	public static function get_tasks ($filter = array ())
	{
		$ret = array ();
		
		if ($filter['order_by'] == 'ord') $filter['order_by'] = 't.ord, t.date_start, t.user_id ';
		else $filter['order_by'] = 't.date_start, t.ord, t.user_id ';
		
		$q = 'SELECT DISTINCT t.id FROM '.TBL_TASKS.' t LEFT OUTER JOIN '.TBL_TASKS_ATTENDEES.' a ON t.id=a.task_id ';
		$q.= 'WHERE ';
		
		if ($filter['user_id'])
		{
			if ($filter['organizer_only']) $q.= 't.user_id='.$filter['user_id'].' AND ';
			else $q.= '(t.user_id='.$filter['user_id'].' OR a.user_id='.$filter['user_id'].') AND ';
		}
		if ($filter['ticket_id']) $q.= 't.ticket_id='.$filter['ticket_id'].' AND ';
		if ($filter['date']) 
		{
			$q.= '(t.date_start>='.get_first_hour($filter['date']).' AND t.date_end<='.get_last_hour($filter['date']).') AND '; 
		}
		
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		
		$q.= 'ORDER BY '.$filter['order_by'].' ';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Task ($id);
		
		return $ret;
	}
	
	/** [Class Method] Delete all tasks for a specified ticket */
	function delete_for_ticket ($ticket_id)
	{
		if ($ticket_id)
		{
			$q = 'SELECT id FROM '.TBL_TASKS.' WHERE ticket_id='.$ticket_id;
			$ids = DB::db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$task = new Task ($id);
				$task->delete ();
			}
		}
	}
	
	/** [Class Method] Daily checks, move unsolved tasks to the next day 
	* IMPORTANT NOTE: This should always be run shortly after midnight
	*/
	function check_tasks ()
	{
		$date = get_first_hour ();
		
		// Select all tasks which have been marked as completed
		$q = 'SELECT id FROM '.TBL_TASKS.' WHERE completed>=100';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id)
		{
			$task = new Task ($id);
			$task->delete ();
		}
		
		// Update all tasks from previous days
		$updates = array ();
		$q = 'SELECT id FROM '.TBL_TASKS.' WHERE date_start<'.$date;
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id)
		{
			$task = new Task ($id);
			$task->ord-= 9999;
			$task->date_start = strtotime ('+1 day', $task->date_start);
			$task->date_end = strtotime ('+1 day', $task->date_end);
			$task->save_data ();
			
			$updates[$task->user_id][] = get_first_hour ($task->date_start);
		}
		
		// Now resort the tasks
		foreach ($updates as $user_id => $dates)
		{
			$dates = array_unique ($dates);
			foreach ($dates as $date) Task::resort ($user_id, $date);
		}
	}
	
}
?>
<?php

class_load ('TicketDetail');
class_load ('TicketAttachment');
class_load ('Notification');
class_load ('Customer');
class_load ('InterventionLocation');
class_load ('CustomerOrder');

/**
* Class for managing KRIFS (technical support) tickets.
*
* A ticket can be a support request from a customer, an internal assignment,
* a task that is part of a project etc.
*
*/

class Ticket extends Base
{
	/** Ticket ID
	* @var int */
	var $id = null;

	/** The customer ID by whom or for which this ticket is created
	* @var int */
	var $customer_id = null;

	/** The ID of the user who created the ticket
	* @var int */
	var $user_id = null;

	/** The subject of the ticket
	* @var string */
	var $subject = '';

	/** The user designated as the responsible owner of the ticket
	* It must be a Keysource user (not a group)
	* @var int */
	var $owner_id = null;

	/** To whom the ticket is currently assigned - can be internal user, group or customer
	* The ID of the assigned user is updated by the last TicketDetail object added
	* @var int */
	var $assigned_id = null;

	/** The type of ticket - see $GLOBALS['TICKET_TYPES']
	* @var int */
	var $type = TICKET_TYPE_SUPPORT_REQUEST;

	/** The source of the ticket - see $GLOBALS ['TICKET_SOURCES']
	* @var int */
	var $source = TICKET_SOURCE_SITE;

	/** The priority of the ticket - see $GLOBALS ['TICKET_PRIORITIES']
	* @var int */
	var $priority = TICKET_PRIORITY_NORMAL;

	/** The deadline for solving the ticket, if applicable
	* @var time */
	var $deadline = null;

	/** Tells if notifications have been dispatched if the deadline has been exceeded
	* @var bool */
	var $deadline_notified = false;

	/** The ID of a project - if this ticket belongs to a project
	* @var int */
	var $project_id = null;

	/** The status of the ticket - see $GLOBALS['TICKET_STATUSES']
	* @var int */
	var $status = TICKET_STATUS_NEW;

	/** For closed tickets, this stores the ID of the manager user who seen/approved the ticket, if any.
	* When this was set, it means the ticket has been controlled by him. Once this has been set it means
	* that the ticket doesn't need to be linked anymore to Intervention Reports.
	* @var int */
	var $seen_manager_id = 0;

	/** The timestamp when the manager marked the ticket as "Seen"
	* @var timestamp */
	var $seen_manager_date = 0;

	/** Comments for "seen by manager"
	* @var text */
	var $seen_manager_comments = '';

	/** Specifies if this ticket is public or private (not visible to customer)
	* @var boolean */
	var $private = true;

	/** The time when the ticket was created
	* @var time */
	var $created = 0;

	/** The time when the ticket was modified last time. It is updated by the last TicketDetail object added
	* @var time */
	var $last_modified = 0;

	/** If set to non-zero, the ticket is considered escalated and the field contains the time when it was escalated
	* @var time */
	var $escalated = 0;

	/** A flag specifying if this ticket should be billed to the customer or not.
	* Do NOT confuse this with the is_billable() method, which determines if the ticket is of a billable type.
	* @var bool */
	var $billable = true;

	/** Specifies the ID of the customer order to which this ticket is linked (if any)
	* @var int */
	var $customer_order_id = 0;

	/** The list of user IDs of CC recipients for this ticket
	* @var array(int) */
	var $cc_list = array ();

	/**
	 * The list of manually added email addresses to the cc list
	 *
	 * @var array(string)
	 */
	var $cc_manual_list = array();

	/** The details included in this ticket
	* @var array(TicketDetail) */
	var $details = array ();

	/** The index in the $details array for last entry - for easy access
	* @var int */
	var $last_entry_index = 0;

	/** The customer object for this ticket
	* @var Customer */
	var $customer = null;

	/** The User object to whom this ticket is assigned
	* @var User */
	var $assigned = null;

	/** The User object who created this ticket
	* @var User */
	var $user = null;

	/** Array with attachment objects associate with this ticket
	* @var array(TicketAttachment) */
	var $attachments = array ();

	/** Array with the IDs of the objects associated with this object.
	* It is represented as an array of generic objects, with two attributes:
	* object_class and object_id.
	* @var array(generic Object)
	*/
	var $object_ids = array ();

	/** Array with the linked objects. The objects are of generic type, with
	* the following fields: id, url, name. The array is NOT populate when the
	* object is loaded. It is populated upon request, by the set_objects_display() method
	* @var array (generic Object) */
	var $objects_display = array ();

	/** If there are users currently working on the ticket, this array will store them. The
	* array keys are user IDs and the values are the times when they marked that they are working on it
	* @var array */
	var $now_working = array ();

	/** The linked customer order, if exists
	* @var CustomerOrder */
	var $customer_order = null;

	/** The User object associated with the manager who reviewed/approved the ticket, if any.
	* @var User */
	var $seen_manager = null;

	/** The earliest scheduled date, if any. Loaded on request with load_schedule_date()
	* @var timestamp */
	var $scheduled_date = 0;

	/**
	 * this is the possible PO code if an order is attached to this ticket
	 * */
	var $po = "";

	var $table = TBL_TICKETS;
	var $fields = array ('id', 'customer_id', 'user_id', 'subject', 'owner_id', 'assigned_id', 'type', 'source', 'priority', 'deadline', 'deadline_notified', 'project_id', 'status', 'seen_manager_id', 'seen_manager_date', 'seen_manager_comments', 'private', 'created', 'last_modified', 'escalated', 'billable', 'customer_order_id', 'po');


	/**
	* Constructor. Also loads a ticket data if an ID is provided
	* @param	int	$id		The ID of the ticket to load
	*/
	function Ticket ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
                        $this->verify_access();
		}
	}

	function __destruct()
	{
		if($this->id) $this->id = null;
		if($this->customer_id) $this->customer_id = null;
		if($this->user_id) $this->user_id = null;
		if($this->subject) $this->subject = null;
		if($this->owner_id) $this->owner_id = null;
		if($this->assigned_id) $this->assigned_id = null;
		if($this->type) $this->type= null;
		if($this->source) $this->source = null;
		if($this->priority) $this->priority = null;
		if($this->deadline) $this->deadline = null;
		if($this->deadline_notified) $this->deadline_notified = null;
		if($this->project_id) $this->project_id = null;
		if($this->status) $this->status = null;
		if($this->seen_manager_id) $this->seen_manager_id = null;
		if($this->seen_manager_date) $this->seen_manager_date = null;
		if($this->seen_manager_comments) $this->seen_manager_comments = null;
		if($this->private) $this->private = null;
		if($this->created) $this->created = null;
		if($this->last_modified) $this->last_modified = null;
		if($this->escalated) $this->escalated = null;
		if($this->billable) $this->billable = null;
		if($this->customer_order_id) $this->customer_order_id = null;
		if($this->cc_list) { $this->cc_list = null;}
		if($this->cc_manual_list) { $this->cc_manual_list = null;}
		if($this->details) {
			if(is_array($this->details))
			{
				foreach($this->details as $d){
                    $d = null;
                }
			}
			$this->details = null;
		}
		if($this->last_entry_index) $this->last_entry_index = null;
		if($this->customer) $this->customer=null;
		if($this->user) $this->user = null;
		if($this->assigned) $this->assigned = null;
		if($this->attachments)
		{
			if(is_array($this->attachments))
			{
				foreach($this->attachments as $a) $a = null;
			}
			$this->attachments = null;
		}
		if($this->object_ids)
		{
			if(is_array($this->object_ids))
			{
				foreach($this->object_ids as $oid) $oid = null;
			}
			$this->object_ids = null;
		}
		if($this->objects_display) $this->objects_display = null;
		if($this->now_working) $this->now_working = null;
		if($this->customer_order) $this->customer_order = null;
		if($this->seen_manager) $this->seen_manager = null;
		if($this->scheduled_date) $this->scheduled_date = null;
	}

	/**
	* Loads the ticket data, as well as the ticket details
	*/
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				// Load the ticket entries
				$q = 'SELECT id FROM '.TBL_TICKETS_DETAILS.' WHERE ticket_id='.$this->id.' ';
				$q.= 'ORDER BY created ASC ';
				$ids = self::db_fetch_array ($q);

				foreach ($ids as $id) $this->details[] = new TicketDetail ($id->id);
				$this->last_entry_index = count($this->details)-1;

				// Load customer info
				if ($this->customer_id) $this->customer = new Customer ($this->customer_id);

				// Load information for the user to whom the ticket is assinged
				if ($this->assigned_id) $this->assigned = new User ($this->assigned_id);

				// Load the information for the user who created this ticket
				if ($this->user_id) $this->user = new User ($this->user_id);

				// Load the owner user information
				if ($this->owner_id) $this->owner = new User ($this->owner_id);

				// Load the managaer who viewed/approved the ticket, if any - for closed tickets
				if ($this->seen_manager_id) $this->seen_manager = new User ($this->seen_manager_id);

				// Load the customer order
				if ($this->customer_order_id) $this->customer_order = new CustomerOrder ($this->customer_order_id);

				// Load the list of attachments
				$q = 'SELECT id FROM '.TBL_TICKETS_ATTACHMENTS.' WHERE ticket_id='.$this->id.' ORDER BY uploaded ';
				$ids = self::db_fetch_vector ($q);
				foreach ($ids as $id) $this->attachments[] = new TicketAttachment ($id);

				// Load the list of CC recipients
				$q = 'SELECT cc.user_id FROM '.TBL_TICKETS_CC.' cc ';
				$q.= 'LEFT JOIN '.TBL_USERS.' u ON cc.user_id=u.id ';
				$q.= 'WHERE cc.ticket_id = '.$this->id.' ORDER BY u.fname, u.lname ';
				$this->cc_list = self::db_fetch_vector ($q);

				//Load the list of CC manual recipients

				$q = "select email_address from ".TBL_TICKETS_MANUAL_CC." where ticket_id=".$this->id;
				$this->cc_manual_list = self::db_fetch_vector($q);

				// Load the list of object IDs associated with this ticket
				$q = 'SELECT object_class, object_id, object_id2 FROM '.TBL_TICKETS_OBJECTS.' WHERE ticket_id = '.$this->id;
				$this->object_ids = self::db_fetch_array ($q);
				// For objects with multi-field primary keys, generate composed "IDs"
				$multi_classes = array (TICKET_OBJ_CLASS_AD_COMPUTER, TICKET_OBJ_CLASS_AD_USER, TICKET_OBJ_CLASS_AD_GROUP, TICKET_OBJ_CLASS_AD_PRINTER);
				for ($i = 0; $i < count ($this->object_ids); $i++)
				{
					if (in_array($this->object_ids[$i]->object_class, $multi_classes))
					{
						$this->object_ids[$i]->object_id = $this->object_ids[$i]->object_id.'_'.$this->object_ids[$i]->object_id2;
					}
				}

				// Load user ID(s) which are working on this ticket, if any
				$q = 'SELECT user_id, since FROM '.TBL_NOW_WORKING.' WHERE ticket_id='.$this->id.' ORDER BY since ';
				$this->now_working = self::db_fetch_list ($q);

				// Check for the encoding
				//if (mb_detect_encoding($this->subject) == 'UTF-8') $this->subject = utf8_decode ($this->subject);
			}
		}
	}


	function load_from_array ($data = array ())
	{
		parent::load_from_array ($data);
		if ($this->customer_order_id) $this->customer_order = new CustomerOrder($this->customer_order_id);
		else $this->customer_order = null;
	}

	/** Loads the earliest scheduled date */
	function load_schedule_date ()
	{
		if ($this->id)
		{
			$q = 'SELECT min(date_start) as date FROM '.TBL_TASKS.' WHERE ticket_id='.$this->id;
			$this->scheduled_date = self::db_fetch_field ($q, 'date');
		}
	}

	/** Checks if the ticket data is valid */
	function is_valid_data ()
	{
		$ret = true;

		if (!$this->subject) {error_msg ($this->get_string('NEED_SUBJECT')); $ret = false;}
		if (!$this->type) {error_msg ($this->get_string('NEED_TICKET_TYPE')); $ret = false;}
		if (!$this->customer_id) {error_msg ($this->get_string('NEED_CUSTOMER')); $ret = false;}
		if (!$this->assigned_id) {error_msg ($this->get_string('NEED_ASSIGNED')); $ret = false;}

		if ($this->assigned_id and $this->customer_id)
		{
			// In case it's a user with restricted customer access, check permissions.
			if (!User::has_assigned_customer_ex($this->customer_id, false, $this->assigned_id))
			{
                $user = new User($this->assigned_id);
                $customers_list = $user->get_users_customer_list();
                if(!in_array($this->customer_id, $customers_list)){
                    $ret = false;
                    error_msg ($this->get_string('ASSIGNED_HASNT_ACCESS_TO_CUSTOMER'));
                }
			}
		}

		// If an order has been assigned, make sure it belongs to this customer
		if ($this->customer_order_id and $this->customer_id)
		{
			$order = new CustomerOrder ($this->customer_order_id);
			if ($order->customer_id != $this->customer_id)
			{
				$ret = false;
				error_msg ($this->get_string('ORDER_WRONG_CUSTOMER'));
			}
		}

		return $ret;
	}


	/** Saves the ticket data and the list of CC recipients */
	function save_data ()
	{
		// Clean all newlines from subject
		$this->subject = preg_replace('/\r\n|\n/', ' ', $this->subject);
		parent::save_data ();

		// Save the CC list
		if ($this->id and is_array ($this->cc_list))
		{
			self::db_query ('DELETE FROM '.TBL_TICKETS_CC.' WHERE ticket_id='.$this->id);
			$q = 'INSERT INTO '.TBL_TICKETS_CC.' (ticket_id, user_id) VALUES ';

			$q_ins = '';
			foreach ($this->cc_list as $user_id)
			{
				$q_ins.= '('.$this->id.', '.$user_id.'), ';
			}

			if ($q_ins != '')
			{
				$q.= preg_replace ('/\,\s*$/', '', $q_ins);
				self::db_query ($q);
			}
		}

		//save the cc manual list
		if($this->id and is_array($this->cc_manual_list))
		{
			self::db_query('delete from '.TBL_TICKETS_MANUAL_CC." where ticket_id=".$this->id);
			$q = "insert into ".TBL_TICKETS_MANUAL_CC.' (ticket_id, email_address) values ';
			$q_ins = "";
			foreach($this->cc_manual_list as $eml)
			{
				$q_ins .= "(".$this->id.", '".$eml."'), ";
			}
			if($q_ins != "")
			{
				$q.= preg_replace ('/\,\s*$/', '', $q_ins);
				self::db_query ($q);
			}
		}

		// For closed tickets, remove them from "Working now" table
		if ($this->id and $this->status==TICKET_STATUS_CLOSED)
		{
			self::db_query ('DELETE FROM '.TBL_NOW_WORKING.' WHERE ticket_id='.$this->id);
		}
	}


    public function can_delete(){
        //Do not allow the deletion of tickets
        return FALSE;
    }

	/** Deletes a ticket */
	function delete ()
	{
		// Ticket deletion IS NOT allowed anymore
		if ($this->id)
		{
			// Delete any notifications that might have been associated with this ticket
			$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_KRIFS.' AND object_id="'.$this->id.'" ';
			$ids = DB::db_fetch_vector ($q);
			foreach ($ids as $notif_id) {$n = new Notification($notif_id); $n->delete();}

			// Delete the details associated with this ticket
			for ($i=0; $i<count($this->details); $i++){
                $this->details[$i]->delete();
            }

			// Delete the ticket-related object references
			self::db_query ('DELETE FROM '.TBL_TICKETS_OBJECTS.' WHERE ticket_id='.$this->id);

			// Delete the list of CC users
			self::db_query ('DELETE FROM '.TBL_TICKETS_CC.' WHERE ticket_id='.$this->id);

			// Delete the list of CC manual users
			self::db_query ('DELETE FROM '.TBL_TICKETS_MANUAL_CC.' WHERE ticket_id='.$this->id);

			// Delete from the tickets the relations with this ticket
			self::db_query ('UPDATE '.TBL_NOTIFICATIONS.' SET ticket_id=0, suspend_email=0 WHERE ticket_id='.$this->id);

			// Delete the "working now" flags
			self::db_query ('DELETE FROM '.TBL_NOW_WORKING.' WHERE ticket_id='.$this->id);

			// Delete any linked tasks
			self::db_query ('DELETE FROM '.TBL_TASKS.' WHERE ticket_id='.$this->id);

			parent::delete ();
		}
	}


	/**
	* Dispatches the notifications for this ticket.
	*
	* Notifications for private tickets or for private comments are not sent to customer users.
	*
	* Customer users only receive notifications if they were the creators of the ticket. If the
	* ticket was created by a Keysource user, then customer users can receive notifications only
	* if the ticket is assigned to that customer user.
	*
	* NOTE 1: All previous notifications for this ticket will be removed
	* NOTE 2: The e-mails with the notifications are sent right away, to be sure that
	* all e-mails are sent, even in the case of update made one after another.
	*
	* @param	int		$type			The type of notification to dispatch.
	* @param	integer		$user			The user who performed the creation/modification, to be excluded
	*							from the list of notification recipients. If the notifications
	*							are trigered from crontab, the user ID should be left to 0.
	*/
	function dispatch_notifications ($type = TICKET_NOTIF_TYPE_UPDATED, $user = 0)
	{
		// Remove older notifications for this ticket
		$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_KRIFS.' AND object_id="'.$this->id.'" ';
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $notif_id) {$n = new Notification($notif_id); $n->delete();}

		// Compose the list of recipients, extracting the users from groups if needed
		$recipients = array ();

		// Reload the ticket data, to be sure we have accurate values
		$this->load_data ();

		if ($this->owner_id)
		{
			$owner = new User ($this->owner_id);
			if ($owner->is_group())
			{
				$owner = new Group ($this->owner_id);
				$recipients = array_merge ($recipients, $owner->get_member_ids ());
			}
			else
			{
				$recipients[] = $owner->id;
			}
		}

		if ($this->assigned_id)
		{
			$assigned_to = new User ($this->assigned_id);
			if ($assigned_to->is_group())
			{
				$assigned_to = new Group ($this->assigned_id);
				$recipients = array_merge ($recipients, $assigned_to->get_member_ids ());
			}
			else
			{
				$recipients[] = $assigned_to->id;
			}
		}

		if ($this->user_id)
		{
			// Add the ticket creator to the recipients list too
			$recipients[] = $this->user_id;
		}

		// Add the CC list to the recipients list. Notifications are not sent to CC list on closing tickets
		if (is_array ($this->cc_list) and $type != TICKET_NOTIF_TYPE_CLOSED)
		{
			$recipients = $recipients + $this->cc_list;
			foreach ($this->cc_list as $cc_id)
			{
				if (User::is_group ($cc_id))
				{
					$cc_group = new Group ($cc_id);
					$recipients = array_merge ($recipients, $cc_group->get_member_ids ());
				}
				else
				{
					$recipients[] = $cc_id;
				}
			}
		}

		$recipients = array_values(array_unique($recipients));

		// Select the event code and the e-mail templates to be used based
		// on the ticket notification type.
		$expires = time() + 60*60*EXPIRE_NOTIF_TICKETS;
		$no_repeat = true;	// By default, now all tickets notifications do NOT repeat
		switch ($type)
		{
			case TICKET_NOTIF_TYPE_NEW:
				$event_code = NOTIF_CODE_NEW_TICKET;
				$message_template = '_classes_templates/krifs/msg_ticket_created.tpl';
				$message_template_customer = '_classes_templates/krifs/msg_ticket_created.tpl';
				$level = ALERT_NOTICE;
				break;

			case TICKET_NOTIF_TYPE_CLOSED:
				$event_code = NOTIF_CODE_CLOSED_TICKET;
				$message_template = '_classes_templates/krifs/msg_ticket_closed.tpl';
				$message_template_customer = '_classes_templates/krifs/msg_customer_ticket_closed.tpl';
				$level = ALERT_NOTICE;
				$expires = time() + 60*60*EXPIRE_NOTIF_CLOSED_TICKETS;
				break;

			case TICKET_NOTIF_TYPE_ESCALATED:
				$event_code = NOTIF_CODE_ESCALATED_TICKET;
				$message_template = '_classes_templates/krifs/msg_ticket_escalated.tpl';
				$level = ALERT_CRITICAL;
				break;

			case TICKET_NOTIF_TYPE_OVER_DEADLINE:
				$event_code = NOTIF_CODE_OVER_DEADLINE_TICKET;
				$message_template = '_classes_templates/krifs/msg_ticket_over_deadline.tpl';
				$level = ALERT_CRITICAL;
				break;

			case TICKET_NOTIF_TYPE_UPDATED:
			default:
				$event_code = NOTIF_CODE_UPDATED_TICKET;
				$message_template = '_classes_templates/krifs/msg_ticket_updated.tpl';
				$message_template_customer = '_classes_templates/krifs/msg_customer_ticket_updated.tpl';
				$level = ALERT_NONE;
				break;
		}

		// Create a notification with all the relevant users as recipients
		$need_notifying_ids = array ();
		foreach ($recipients as $recip_id)
		{
			if ($recip_id != $user)
			{
				$recipient = new User ($recip_id);
				$do_notify = true;

				// For customers, make sure they receive only the notifications they need to see
				if ($recipient->customer_id)
				{
					$is_private = ($this->private or $this->details[count($this->details)-1]->private);

					if (($is_private and !$recipient->allow_private) or $type == TICKET_NOTIF_TYPE_ESCALATED)
					{
						// This is a private entry or an escalation, don't notify
						$do_notify = false;
					}
					$tpl = $message_template_customer;
				}
				else
				{
					$tpl = $message_template;
				}

				if ($do_notify) $need_notifying_ids[$recip_id] = array ('no_repeat'=> $no_repeat, 'tpl'=>$tpl);
				{
				//echo 'xxx';
				//function raise_notification ($event_code, $level, $object_class, $object_id, $object_event_code, $item_id=0, $user_ids=array(), $text='', $no_increment = false, $template = '', $expires = 0, $no_repeat = false)

				//	 Notification::raise_notification ($event_code, $level, NOTIF_OBJ_CLASS_KRIFS,
				//	 $this->id, 0, 0, $recip_id, '', false, true, $tpl, $expires, $no_repeat);
				}
			}
		}

		//now we need to set the same things for the manual cc list
		//we are treating them as customers so it's on need to see only basis
		$bPrv = ($this->private or $this->details[count($this->details)-1]->private);
		$dnt = true;
		if($bPrv or $type == TICKET_NOTIF_TYPE_ESCALATED)
		{
			$dnt = false;
		}
		$tplx = $message_template_customer;


		if (count($need_notifying_ids) > 0)
		{
			$notification_id = Notification::raise_notification ($event_code, $level, NOTIF_OBJ_CLASS_KRIFS, $this->id, 0, 0,
			array_keys($need_notifying_ids), '', false, $tpl, $expires, $no_repeat);
			$notification = new Notification ($notification_id);
			foreach ($need_notifying_ids as $recip_id => $setting)
			{
				$notification->set_notification_recipient_text ($recip_id, '', $setting['no_repeat'], $setting['tpl']);

				// Also e-mail the notification right away
				if ($notification->needs_emailing($recip_id) and User::is_active_strict_ex($recip_id))
					$notification->send_email ($recip_id);
			}
		}

		if($dnt)
		{
			//here we need to make the sending for the manual cc list
			$nid = Notification::raise_notification ($event_code, $level, NOTIF_OBJ_CLASS_KRIFS, $this->id, 0, 0, array(), '', false, $tplx, $expires, false);
			$notif = new Notification ($nid);
			$notif->send_email_to_list($this->cc_manual_list);
		}
	}


	/** Marks that the specified user is now working on this ticket. Can be called as class method too - in which case specify $ticket_id */
	public static function mark_now_working($user_id, $ticket_id = null)
	{
		//if (!$ticket_id) $ticket_id = $this->id;

		if ($ticket_id and $user_id)
		{
			// Check if the user isn't already working on this ticket or another
			$q = 'SELECT ticket_id FROM '.TBL_NOW_WORKING.' WHERE user_id='.$user_id;
			$prev_ticket = DB::db_fetch_field ($q, 'ticket_id');

			if (!$prev_ticket)
			{
				// The user was not previously working on a ticket
				$q = 'INSERT INTO '.TBL_NOW_WORKING.'(user_id, ticket_id, since) VALUES ';
				$q.= '('.$user_id.', '.$ticket_id.', '.time().')';
				DB::db_query ($q);
			}
			elseif ($prev_ticket != $ticket_id)
			{
				// Update only if the user was not working on this ticket before
				$q = 'UPDATE '.TBL_NOW_WORKING.' SET ticket_id='.$ticket_id.', since='.time().' WHERE user_id='.$user_id;
				DB::db_query ($q);
			}
		}
	}


	/** Removes the mark that the user is working now on something. Can be called as class method too */
	public static function unmark_now_working($user_id)
	{
		if ($user_id)
		{
			DB::db_query ('DELETE FROM '.TBL_NOW_WORKING.' WHERE user_id='.$user_id);
		}
	}

	/** [Class Method] Returns the details of whow is working on what now */
	public static function get_now_working ()
	{
		$ret = array ();

		$q = 'SELECT * FROM '.TBL_NOW_WORKING.' ORDER BY since DESC ';

		$working = DB::db_fetch_array ($q);

		for ($i=0; $i<count($working); $i++)
		{
			$ret[] = array (
				'user' => new User ($working[$i]->user_id),
				'ticket' => new Ticket ($working[$i]->ticket_id),
				'since' => $working[$i]->since
			);
		}

		return $ret;
	}


	/** Marks the ticket has heving been read by a certain user */
	function mark_read ($user_id = null)
	{
		if ($this->id and $user_id)
		{
			// Delete all notifications for this ticket and this user
			$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_KRIFS.' AND object_id="'.$this->id.'" ';
			$ids = DB::db_fetch_vector ($q);
			foreach ($ids as $notif_id)
			{
				$n = new Notification($notif_id);
				$n->remove_recipient ($user_id);
			}
		}
	}


	/** Marks the ticket as being closed and delete all associated tasks */
	function mark_closed ()
	{
		if ($this->id)
		{
			$this->status = TICKET_STATUS_CLOSED;
			$q = 'DELETE FROM '.TBL_TASKS.' WHERE ticket_id='.$this->id;
			DB::db_query ($q);
		}
	}


	/** Re-open the ticket */
	function reopen ()
	{
		if ($this->id)
		{
			$this->status = TICKET_STATUS_ASSIGNED;
		}
	}


	/** Checks if a specified detail ID is the last one created for this ticket */
	function is_last_entry ($ticket_detail_id)
	{
		$ret = false;
		if (is_array($this->details))
		{
			$ret = ($this->details[count($this->details)-1]->id == $ticket_detail_id);
		}
		return $ret;
	}


	/**
	* Get the owner who should be the default one for this ticket.
	* @param	int		$intended_owner		(By Ref) If the owner is currently in "Away" status, then the
	*							function will returned the ID of the defined alternate recipient.
	*							This parameter, if passed, will be loaded with the ID of the original owner found.
	* @return	int					The ID of the user who should own the ticket, either the
	*							default owner assigned to the specific customer, or the
	*							generic owner for new tickets.
	*/
	function get_default_owner (&$intended_owner)
	{
		class_load ('InfoRecipients');
		$ret = 0;

		if ($this->customer_id)
		{
			// Check first for recipients specifically assigned to this customer
			$default_customer_recipients = InfoRecipients::get_customer_default_recipients ();
			$ret = $default_customer_recipients[$this->customer_id][NOTIF_OBJ_CLASS_KRIFS];

			if (!$ret)
			{
				// Try the generic default recipients
				$default_recipients = InfoRecipients::get_all_type_default_recipients ();
				$ret = $default_recipients[NOTIF_OBJ_CLASS_KRIFS];
			}

			// If the found user is away, get the alternative recipient
                        $alternate_id = User::is_away($ret);
			if ($alternate_id)
			{
				if (isset($intended_owner)) $intended_owner = $ret;
				$ret = $alternate_id;
			}
		}

		return $ret;
	}


	/**
	* Get the default list of CC recipients
	*
	* @param	array		Array of numeric user IDs who should be in the CC list for
	*				this ticket, either because they have been specifically assigned
	*				to this customer or because they are listed as generic recipients
	*/
	function get_default_cc_list ()
	{
		class_load ('InfoRecipients');
		$ret = array ();

		if ($this->customer_id)
		{
			// Check first for recipients specifically assigned to this customer
			$customer_recipients = InfoRecipients::get_customer_recipients (array('customer_id' => $this->customer_id), $no_count);
			$ret = $customer_recipients[$this->customer_id][NOTIF_OBJ_CLASS_KRIFS];

			if (empty($ret))
			{
				// Try the generic default recipients
				$ret = InfoRecipients::get_type_recipients (NOTIF_OBJ_CLASS_KRIFS);
			}

			$intended_owner = 0;
			$default_recipient = $this->get_default_owner ($intended_owner);
			// If the default owner was "Away", add him to the CC list
			// We know that the default owner was away because $intended_owner is made non-zero by get_default_owner()
			if ($intended_owner>0) $ret[] = $intended_owner;

			if (count($ret) > 0)
			{
				$ret = array_unique ($ret);
				if ($default_recipient)
				{
					// Make sure that the default recipient is not included in the CC list
					for ($i=0; $i<count($ret); $i++)
					{
						if ($ret[$i]==$default_recipient) unset ($ret[$i]);
					}
				}
			}
		}

		return $ret;
	}


	/**
	* Escalates the ticket. This means notifying the escalation recipients, changing the ticket
	* status and priority. The user comments are added to the ticket as a new TicketDetail object.
	* The escalation recipients are automatically added to the ticket's CC list, if they
	* are not in there already.
	*
	* IMPORTANT NOTE: this method does not automatically dispatch the related notifications,
	* nor does it save the actual ticket object.
	*
	* @param	int	$uid		The ID of the user who requested the escalation
	* @param	string	$comments	The reason for escalation entered by the user.
	* @param	bool	$private	If True (default), the ticket entry will be created as private
	*/
	function escalate ($uid, $comments, $private = true)
	{
		if ($this->id)
		{
			$this->priority = TICKET_PRIORITY_HIGH;
			$this->escalated = time ();

			// Add the escalation recipients to the ticket CC list
			$escalate_recips = $this->get_escalation_recipients_list ();
			foreach (array_keys ($escalate_recips) as $id)
			{
				if (!in_array ($id, $this->cc_list))
				{
					$this->cc_list[] = $id;
					// If the user is away, make sure to also add the alternate recipient
                    $alternate_id = User::is_away($id);
					if ($alternate_id) $this->cc_list[] = $alternate_id;
				}
			}
			$this->cc_list = array_unique ($this->cc_list);

			// Create the TicketDetail object for storing the user comments
			$ticket_detail = new TicketDetail ();
			$ticket_detail->ticket_id = $this->id;
			$ticket_detail->private = $private;
			$ticket_detail->assigned_id = $this->assigned_id;
			$ticket_detail->comments = $comments;
			$ticket_detail->created = time ();
			$ticket_detail->user_id = $uid;
			$ticket_detail->status = $this->status;
			$ticket_detail->escalated = $this->escalated;

			$ticket_detail->save_data ();
		}
	}


	/**
	* Marks that the ticket is not escalated anymore
	*/
	function unescalate ($uid, $comments, $private = true)
	{
		if ($this->id)
		{
			//$this->priority = TICKET_PRIORITY_NORMAL;
			$this->escalated = 0;

			// Create the TicketDetail object for storing the user comments
			$ticket_detail = new TicketDetail ();
			$ticket_detail->ticket_id = $this->id;
			$ticket_detail->private = $private;
			$ticket_detail->assigned_id = $this->assigned_id;
			$ticket_detail->comments = $comments;
			$ticket_detail->created = time ();
			$ticket_detail->user_id = $uid;
			$ticket_detail->status = $this->status;
			$ticket_detail->escalated = 0;

			$ticket_detail->save_data ();
		}
	}


	/**
	* Notifies that the deadline for this ticket has been exceeded. It changes the ticket
	* priority and status. It also adds the escalation recipients to the CC list.
	*/
	function notify_deadline ()
	{
		if ($this->id and $this->deadline and $this->deadline < time() and !$this->deadline_notified or 1)
		{
			$this->priority = TICKET_PRIORITY_HIGH;
			$this->status = TICKET_STATUS_OVER_DEADLINE;
			$this->escalated = time ();

			// Add the escalation recipients to the ticket CC list
			$escalate_recips = $this->get_escalation_recipients_list ();
			foreach (array_keys ($escalate_recips) as $id)
			{
				if (!in_array ($id, $this->cc_list)) $this->cc_list[] = $id;
			}

			// Create the TicketDetail object for storing the status change
			$ticket_detail = new TicketDetail ();
			$ticket_detail->ticket_id = $this->id;
			$ticket_detail->private = 1;
			$ticket_detail->assigned_id = $this->assigned_id;
			$ticket_detail->comments = '';
			$ticket_detail->created = time ();
			$ticket_detail->user_id = 0;
			$ticket_detail->status = TICKET_STATUS_OVER_DEADLINE;
			$ticket_detail->status = $this->status;
			$ticket_detail->escalated = $this->escalated;

			$ticket_detail->save_data ();

			// Generate the notifications and update the ticket
			$this->deadline_notified = true;
			$this->last_modified = time ();
			$this->save_data ();
			$this->dispatch_notifications (TICKET_NOTIF_TYPE_OVER_DEADLINE, 0);
		}
	}


	/**
	* Adds object links to the ticket
	* @param	int	$object_class		The type of object being referenced. See $GLOBALS['TICKET_OBJECT_CLASSES']
	* @param	int	$object_ids		Array with the IDs of object being added. For objects with composed primary
	*						keys, the IDs are passed as strings, the individual numeric IDs being concatenated
	*						with "_" characters
	*/
	function add_objects ($object_class, $object_ids)
	{
		if ($this->id and $object_class and is_array ($object_ids) and count($object_ids)>0)
		{
			$q = 'REPLACE INTO '.TBL_TICKETS_OBJECTS.' (ticket_id, object_class, object_id, object_id2) VALUES ';
			for ($i = 0; $i < count($object_ids); $i++)
			{
				if (ereg('_', $object_ids[$i]))
				{
					list ($id, $id2) = split ('_', $object_ids[$i]);
				}
				else
				{
					$id = $object_ids[$i];
					$id2 = 0;
				}
				$q.= '('.$this->id.', '.$object_class.', '.$id.', '.$id2.'), ';
			}
			$q = preg_replace ('/\,\s*$/', '', $q);

			db::db_query ($q);
		}
	}


	/** Sets all the linked ticket details to have the same customer order ID as the ticket itself.
	* Usually this is called when the ticket's order ID is modified.
	*/
	function reset_details_customer_orders ()
	{
		if ($this->id and count($this->details)>0)
		{
			for ($i=0; $i<count($this->details); $i++)
			{
				$this->details[$i]->customer_order_id = $this->customer_order_id;
				$this->details[$i]->save_data ();
			}
		}
	}


	/** Populates the objects attributes with the needed field values */
	function set_objects_display ()
	{
		class_load ('RemovedComputer');
		class_load ('AD_Computer');
		class_load ('AD_User');
		class_load ('AD_Group');
		class_load ('AD_Printer');
		class_load ('MonitoredIP');
		class_load ('Peripheral');
		class_load ('CustomerInternetContract');

		if ($this->id and is_array ($this->object_ids))
		{
			$this->objects_display = array ();
			for ($i = 0; $i < count ($this->object_ids); $i++)
			{
				$this->objects_display[$i]->id = $this->object_ids[$i]->object_id;
				$this->objects_display[$i]->object_class = $this->object_ids[$i]->object_class;

				// Generate the display name and object URLs for each of the linked objects
				switch ($this->object_ids[$i]->object_class)
				{
					case TICKET_OBJ_CLASS_COMPUTER :
						$comp = new Computer ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $comp->netbios_name;
						$this->objects_display[$i]->url = './?cl=kawacs/computer_view&id='.$comp->id;
						break;

					case TICKET_OBJ_CLASS_REMOVED_COMPUTER :
						$comp = new RemovedComputer ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $comp->netbios_name;
						$this->objects_display[$i]->url = './?cl=kawacs_removed/computer_view&id='.$comp->id;
						break;

					case TICKET_OBJ_CLASS_PERIPHERAL:
						$periph = new Peripheral ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $periph->name;
						$this->objects_display[$i]->url = './?cl=kawacs/peripheral_edit&id='.$periph->id;
						break;

					case TICKET_OBJ_CLASS_REMOVED_PERIPHERAL:
						$periph = new Peripheral ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $periph->name;
						$this->objects_display[$i]->url = './?cl=kawacs_removed/peripheral_view&id='.$periph->id;
						break;

					case TICKET_OBJ_CLASS_MONITORED_IP :
						$monitored_ip = new MonitoredIP ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $monitored_ip->remote_ip.'/'.$monitored_ip->target_ip;
						$this->objects_display[$i]->url = './?cl=kawacs/monitored_ip_edit&id='.$monitored_ip->id;
						break;

					case TICKET_OBJ_CLASS_USER :
						$user = new User ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $user->fname.' '.$user->lname.' ('.$user->login.')';
						$this->objects_display[$i]->url = './?cl=user/user_edit&id='.$user->id;
						break;

					case TICKET_OBJ_CLASS_AD_COMPUTER :
						list ($computer_id, $nrc) = split ('_', $this->object_ids[$i]->object_id);
						$ad_computer = new AD_Computer ($computer_id, $nrc);
						$this->objects_display[$i]->name = $ad_computer->cn;
						$this->objects_display[$i]->url = './?cl=kerm/ad_computer_view&computer_id='.$computer_id.'&nrc='.$nrc;
						break;

					case TICKET_OBJ_CLASS_AD_USER :
						list ($computer_id, $nrc) = split ('_', $this->object_ids[$i]->object_id);
						$ad_user = new AD_User ($computer_id, $nrc);
						$this->objects_display[$i]->name = $ad_user->sam_account_name;
						$this->objects_display[$i]->url = './?cl=kerm/ad_user_view&computer_id='.$computer_id.'&nrc='.$nrc;
						break;

					case TICKET_OBJ_CLASS_AD_GROUP :
						list ($computer_id, $nrc) = split ('_', $this->object_ids[$i]->object_id);
						$ad_group = new AD_Group ($computer_id, $nrc);
						$this->objects_display[$i]->name = $ad_group->name;
						$this->objects_display[$i]->url = './?cl=kerm/ad_group_view&computer_id='.$computer_id.'&nrc='.$nrc;
						break;

					case TICKET_OBJ_CLASS_AD_PRINTER :
						list ($computer_id, $nrc) = split ('_', $this->object_ids[$i]->object_id);
						$ad_printer = new AD_Printer ($computer_id, $nrc);
						$this->objects_display[$i]->name = $ad_printer->name;
						$this->objects_display[$i]->url = './?cl=kerm/ad_printer_view&computer_id='.$computer_id.'&nrc='.$nrc;
						break;

					case TICKET_OBJ_CLASS_REMOVED_AD_PRINTER :
						$ad_printer = new RemovedAD_Printer ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $ad_printer->name;
						$this->objects_display[$i]->url = './?cl=kawacs_removed/ad_printer_view&id='.$ad_printer->id;
						break;

					case TICKET_OBJ_CLASS_INTERNET_CONTRACT :
						$contract = new CustomerInternetContract ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $contract->get_name ();
						$this->objects_display[$i]->url = './?cl=klara/customer_internet_contract_edit&id='.$contract->id;
						break;
				}
			}
		}
	}

        /** Populates the objects attributes with the needed field values */
	function set_objects_display_additional_info ()
	{
		class_load ('RemovedComputer');
		class_load ('AD_Computer');
		class_load ('AD_User');
		class_load ('AD_Group');
		class_load ('AD_Printer');
		class_load ('MonitoredIP');
		class_load ('Peripheral');
		class_load ('CustomerInternetContract');

		if ($this->id and is_array ($this->object_ids))
		{
			$this->objects_display = array ();
			for ($i = 0; $i < count ($this->object_ids); $i++)
			{
				$this->objects_display[$i]->id = $this->object_ids[$i]->object_id;
				$this->objects_display[$i]->object_class = $this->object_ids[$i]->object_class;

				// Generate the display name and object URLs for each of the linked objects
				switch ($this->object_ids[$i]->object_class)
				{
					case TICKET_OBJ_CLASS_COMPUTER :
						$comp = new Computer ($this->object_ids[$i]->object_id);
                        $_info = $comp->get_additional_info();
                        if(stripos($_info['computer_brand'], 'Dell') !== FALSE){
                            $info = $_info['computer_model'] . ' - ' . $_info['computer_brand'] . ' | Service Tag: ' . $_info['computer_sn'];
                        } else {
                            $info = $_info['computer_model'] . ' - ' . $_info['computer_brand'] . ' | Serial No: ' . $_info['computer_sn'];
                        }
                        $this->objects_display[$i]->info = $info;
                        $this->objects_display[$i]->name = $comp->netbios_name;
						$this->objects_display[$i]->url = './?cl=kawacs/computer_view&id='.$comp->id;
						break;

					case TICKET_OBJ_CLASS_REMOVED_COMPUTER :
						$comp = new RemovedComputer ($this->object_ids[$i]->object_id);
                        $_info = $comp->get_additional_info();
                        if(stripos($_info['computer_brand'], 'Dell') !== FALSE){
                            $info = $_info['computer_model'] . ' - ' . $_info['computer_brand'] . ' | Service Tag: ' . $_info['computer_sn'];
                        } else {
                            $info = $_info['computer_model'] . ' - ' . $_info['computer_brand'] . ' | Serial No: ' . $_info['computer_sn'];
                        }
                        $this->objects_display[$i]->info = $info;
						$this->objects_display[$i]->name = $comp->netbios_name;
						$this->objects_display[$i]->url = './?cl=kawacs_removed/computer_view&id='.$comp->id;
						break;

					case TICKET_OBJ_CLASS_PERIPHERAL:
						$periph = new Peripheral ($this->object_ids[$i]->object_id);
                        $ip = $periph->get_net_access_ip();
                        $this->objects_display[$i]->info = '';
                        if(!empty($ip)) {
                            $url = $periph->get_access_url();
                            if(!empty($url)) {
                                $this->objects_display[$i]->info .= 'IP: <a href="'.$url.'" target="_blank">' . $ip . '</a>';
                            } else {
                                $this->objects_display[$i]->info .= 'IP: ' . $ip;
                            }
                            $login = $periph->get_login();
                            if(!empty($login)) {
                                $this->objects_display[$i]->info .= ', login: ' . $login;
                            }
                            $password = $periph->get_password();
                            if(!empty($password)) {
                                $this->objects_display[$i]->info .= ', password: ' . $login;
                            }
                        }
                        $sn = $periph->get_sn();
                        if(!empty($sn)) {
                            if(!empty($this->objects_display[$i]->info)) {
                                $this->objects_display[$i]->info .= ' | Serial No: ' . $sn;
                            } else {
                                $this->objects_display[$i]->info .= 'Serial No: ' . $sn;
                            }
                        }
                                                
						$this->objects_display[$i]->name = $periph->name;
						$this->objects_display[$i]->url = get_link('kawacs', 'peripheral_edit', array('id' => $periph->id)); //'./?cl=kawacs/peripheral_edit&id='.$periph->id;
						break;

					case TICKET_OBJ_CLASS_REMOVED_PERIPHERAL:
						$periph = new Peripheral ($this->object_ids[$i]->object_id);
                        $ip = $periph->get_net_access_ip();
                        $this->objects_display[$i]->info = '';
                        if(!empty($ip)) {
                            $url = $periph->get_access_url();
                            if(!empty($url)) {
                                $this->objects_display[$i]->info .= 'IP: <a href="'.$url.'" target="_blank">' . $ip . '</a>';
                            } else {
                                $this->objects_display[$i]->info .= 'IP: ' . $ip;
                            }
                            $login = $periph->get_login();
                            if(!empty($login)) {
                                $this->objects_display[$i]->info .= ', login: ' . $login;
                            }
                            $password = $periph->get_password();
                            if(!empty($password)) {
                                $this->objects_display[$i]->info .= ', password: ' . $login;
                            }
                        }
                        $sn = $periph->get_sn();
                        if(!empty($sn)) {
                            if(!empty($this->objects_display[$i]->info)) {
                                $this->objects_display[$i]->info .= ' | Serial No: ' . $sn;
                            } else {
                                $this->objects_display[$i]->info .= 'Serial No: ' . $sn;
                            }
                        }

						$this->objects_display[$i]->name = $periph->name;
						$this->objects_display[$i]->url = get_link('kawacs_removed', 'peripheral_view', array('id'=>$periph->id));//'./?cl=kawacs_removed/peripheral_view&id='.$periph->id;
						break;

					case TICKET_OBJ_CLASS_MONITORED_IP :
						$monitored_ip = new MonitoredIP ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $monitored_ip->remote_ip.'/'.$monitored_ip->target_ip;
						$this->objects_display[$i]->url = get_link('kawacs', 'monitored_ip_edit', array('id'=>$monitored_ip->id));//'./?cl=kawacs/monitored_ip_edit&id='.$monitored_ip->id;
						break;

					case TICKET_OBJ_CLASS_USER :
						$user = new User ($this->object_ids[$i]->object_id);
                        $this->objects_display[$i]->info = '';
                        if(!empty($user->email)) {
                            $this->objects_display[$i]->info .= 'Email: ' . $user->email;
                        }
                        if(!empty($user->phones) and is_array($user->phones)) {
                            foreach($user->phones as $phone) {
                                $this->objects_display[$i]->info .= ' | ' . $GLOBALS['PHONE_TYPES'][$phone->type] . ': ' . $phone->phone;
                            }
                        }
						$this->objects_display[$i]->name = $user->fname.' '.$user->lname.' ('.$user->login.')';
						$this->objects_display[$i]->url = get_link('user', 'user_edit', array('id'=>$user->id));//'./?cl=user/user_edit&id='.$user->id;
						break;

					case TICKET_OBJ_CLASS_AD_COMPUTER :
						list ($computer_id, $nrc) = preg_split ('/_/', $this->object_ids[$i]->object_id);
						$ad_computer = new AD_Computer ($computer_id, $nrc);
						$this->objects_display[$i]->name = $ad_computer->cn;
						$this->objects_display[$i]->url = get_link('kerm', 'ad_computer_view', array('computer_id'=>$computer_id, 'nrc'=>$nrc));//'./?cl=kerm/ad_computer_view&computer_id='.$computer_id.'&nrc='.$nrc;
						break;

					case TICKET_OBJ_CLASS_AD_USER :
						list ($computer_id, $nrc) = split ('_', $this->object_ids[$i]->object_id);
						$ad_user = new AD_User ($computer_id, $nrc);
                        $this->objects_display[$i]->info = '';
                        if(!empty($ad_user->email)) {
                            $this->objects_display[$i]->info .= 'Email: ' . $ad_user->email;
                        }
                        if(!empty($ad_user->telephone)) {
                            $this->objects_display[$i] .= ' | Telephone: ' . $ad_user->telephone;
                        }
						$this->objects_display[$i]->name = $ad_user->sam_account_name;
						$this->objects_display[$i]->url = get_link('kerm', 'ad_user_view', array('computer_id'=>$computer_id, 'nrc' => $nrc));//'./?cl=kerm/ad_user_view&computer_id='.$computer_id.'&nrc='.$nrc;
						break;

					case TICKET_OBJ_CLASS_AD_GROUP :
						list ($computer_id, $nrc) = split ('_', $this->object_ids[$i]->object_id);
						$ad_group = new AD_Group ($computer_id, $nrc);
						$this->objects_display[$i]->name = $ad_group->name;
						$this->objects_display[$i]->url = get_link('kerm', 'ad_group_view', array('computer_id' => $computer_id, 'nrc'=>$nrc));//'./?cl=kerm/ad_group_view&computer_id='.$computer_id.'&nrc='.$nrc;
						break;

					case TICKET_OBJ_CLASS_AD_PRINTER :
						list ($computer_id, $nrc) = split ('_', $this->object_ids[$i]->object_id);
						$ad_printer = new AD_Printer ($computer_id, $nrc);
                                                $this->objects_display[$i]->info = 'Port: ' . $ad_printer->port_name;
						$this->objects_display[$i]->name = $ad_printer->name;
						$this->objects_display[$i]->url = get_link('kerm','ad_printer_view', array('computer_id'=>$computer_id, 'nrc'=>$nrc));//'./?cl=kerm/ad_printer_view&computer_id='.$computer_id.'&nrc='.$nrc;
						break;

					case TICKET_OBJ_CLASS_REMOVED_AD_PRINTER :
						$ad_printer = new RemovedAD_Printer ($this->object_ids[$i]->object_id);
						$this->objects_display[$i]->name = $ad_printer->name;
						$this->objects_display[$i]->url = get_link('kawacs_removed', 'ad_printer_view', array('id'=>$ad_printer->id));//'./?cl=kawacs_removed/ad_printer_view&id='.$ad_printer->id;
						break;

					case TICKET_OBJ_CLASS_INTERNET_CONTRACT :
						$contract = new CustomerInternetContract ($this->object_ids[$i]->object_id);
                                                $this->objects_display[$i]->info = 'Client No: ' . $contract->client_number . ' | ADSL No: ' . $contract->adsl_line_number . ' | Type: ' . $GLOBALS['LINE_TYPES'][$contract->line_type];
						$this->objects_display[$i]->name = $contract->get_name ();
						$this->objects_display[$i]->url = get_link('klara','customer_internet_contract_edit', array('id'=>$contract->id));//'./?cl=klara/customer_internet_contract_edit&id='.$contract->id;
						break;
				}
			}
		}
	}

	/** Deletes an object reference from a ticket */
	function delete_object ($object_class, $object_id)
	{
		if ($this->id and $object_class and $object_id)
		{
			$q = 'DELETE FROM '.TBL_TICKETS_OBJECTS.' WHERE ticket_id='.$this->id.' AND object_class='.$object_class.' AND ';
			if (preg_match ('/[0-9]+_[0-9]+/', $object_id))
			{
				// This is an object id composed of two fields
				$ids = preg_split('/_/', $object_id, 2);
				$q.= 'object_id='.$ids[0].' AND object_id2='.$ids[1].' ';
			}
			else $q.= 'object_id='.$object_id.' ';
			self::db_query ($q);
		}
	}


	/** Checks if the ticket can be billed to a customer - based on the ticket type.
	* Do NOT confuse this with the 'billable' attribute, which is a flag specifying if the
	* ticket should be invoiced or not. */
	function is_billable ()
	{
		$ret = false;
		if ($this->id)
		{
			$billable_type = self::db_fetch_field ('SELECT is_billable FROM '.TBL_TICKETS_TYPES.' WHERE id='.$this->type, 'is_billable');
			/*modified at keysource request to include ticket details from closed tickets into IRs*/
			/*$ret = ($this->status != TICKET_STATUS_CLOSED and $billable_type);*/
			$ret = $billable_type;
		}
		return $ret;
	}


	/** Checks if the customer can be changed for this ticket - meaning that it doesn't have any special linked objects*/
	function can_change_customer ()
	{
		$ret = true;

		if ($this->id)
		{
			// Check for linked objects
			if (count($this->object_ids) > 0)
			{
				$ret = false;
				error_msg ('Can\'t change customer, there are already customer-specific objects linked to the ticket.');

			}

			// Check for linked customer orders
			if ($this->customer_order_id)
			{
				$ret = false;
				error_msg ('Can\'t change customer, there is already a customer order linked to the ticket.');

			}

			// Check for intervention reports
			$q = 'SELECT id FROM '.TBL_TICKETS_DETAILS.' WHERE ticket_id='.$this->id.' AND intervention_report_id<>0 LIMIT 1';
			if (self::db_fetch_field($q, 'id'))
			{
				$ret = false;
				error_msg ('Can\'t change customer, there is already one or more intervention reports linked to the ticket.');
			}

			// Check for customer-specific users
			$linked_users_ids = array ($this->owner_id, $this->assigned_id);
			$q = 'SELECT DISTINCT user_id FROM '.TBL_TICKETS_DETAILS.' WHERE ticket_id='.$this->id;
			$ids = self::db_fetch_vector ($q);
			foreach ($ids as $id) if (!in_array($id, $linked_users_ids)) $linked_users_ids[] = $id;
			$q = 'SELECT DISTINCT assigned_id FROM '.TBL_TICKETS_DETAILS.' WHERE ticket_id='.$this->id;
			$ids = self::db_fetch_vector ($q);
			foreach ($ids as $id) if (!in_array($id, $linked_users_ids)) $linked_users_ids[] = $id;
			$linked_customer_user = false;
			for ($i=0; $i<count($linked_users_ids) and !$linked_customer_user; $i++)
			{
				$user = new User ($linked_users_ids[$i]);
				if ($user->customer_id)
				{
					$ret = false;
					$linked_customer_user = true;
					error_msg ('Can\'t change customer, there is one or more customer users linked to the ticket.');
				}
			}
		}

		return $ret;
	}

	/** Logs an access action for this ticket into a TBL_TICKETS_ACCESS_y_m table.
	* @param	int				$user_id		The ID of the user who made the action
	* @param	int				$action_id		The ID of the action - see
	* @param	int				$ticket_detail_id	The ID of the ticket detail, if this action refers to a detail
	*/
	function log_action ($user_id, $action_id, $ticket_detail_id = 0)
	{
		if ($this->id and $user_id and $action_id)
		{

			$tbl = TBL_TICKETS_ACCESS.'_'.get_date('Y_m');
			$q = 'INSERT INTO '.$tbl.'(ticket_id,ticket_detail_id,user_id,date,action_id) VALUES (';
			$q.= $this->id.','.$ticket_detail_id.','.$user_id.','.time().','.$action_id.')';
			self::db_query ($q);
		}
	}


	/** [Class Method]
	* Checks for tickets that have exceeded the deadline. Normally is invoked from crontab.
	* The deadline field doesn't store the hour, only the date. The hour on which the notifications
	* are raised is controlled by setting the crontab to be run on a specific hour.
	*/
	function check_deadlines ()
	{
		$q = 'SELECT id FROM '.TBL_TICKETS.' WHERE deadline>0 AND deadline < '.time ().' AND deadline_notified=0 ';
		$q.= 'AND status<>'.TICKET_STATUS_CLOSED;
		$ids = db::db_fetch_vector ($q);

		foreach ($ids as $id)
		{
			$ticket = new Ticket ($id);
			$ticket->notify_deadline ();
			if($ticket) $ticket=null;
		}
	}


	/** [Class Method] Checks the SLA times and escalate the needed tickets  */
	function check_sla_times ()
	{
		$q = 'SELECT distinct t.id FROM '.TBL_TICKETS.' t INNER JOIN '.TBL_CUSTOMERS.' c ';
		$q.= 'ON t.customer_id=c.id WHERE ';
		// Select only tickets for customers which have the SLA time defined,
		// only tickets which are still "New" and tickets which have not been already escalated
		$q.= 'c.sla_hours > 0 AND t.status='.TICKET_STATUS_NEW.' AND t.escalated=0 AND ';
		$q.= time().'-t.created > (c.sla_hours * 60 * 60) ';

		$ids = db::db_fetch_vector ($q);
		for ($i = 0; $i<count($ids); $i++)
		{
			$ticket = new Ticket ($ids[$i]);
			$ticket->escalate (0, '['.$ticket->get_string('EXCEEDED_SLA_TIME').']');
			$ticket->save_data ();
			$ticket->dispatch_notifications (TICKET_NOTIF_TYPE_ESCALATED, 0);
			if($ticket) $ticket=null;
		}
	}


	/**
	* [Class Method] Checks for tickets that have exceeded their allowed time for the current status and need to be escalated.
	*/
	function check_escalation_conditions ()
	{
		$q = 'SELECT t.id FROM '.TBL_TICKETS_STATUSES.' s INNER JOIN '.TBL_TICKETS.' t ';
		$q.= 'ON s.id=t.status and s.escalate_after > 0 ';
		$q.= 'WHERE t.escalated=0 AND '.time().'-t.last_modified > s.escalate_after';

		$ids = DB::db_fetch_vector ($q);

		for ($i = 0; $i<count($ids); $i++)
		{
			$ticket = new Ticket ($ids[$i]);
			$ticket->escalate (0, '['.$this->get_string('TIME_LIMIT_EXCEEDED').']');
			$ticket->save_data ();
			$ticket->dispatch_notifications (TICKET_NOTIF_TYPE_ESCALATED, 0);
			if($ticket) $ticket=null;
		}
	}

	/**
	* [Class Method] Returns a list of tickets according to the specified filtering criteria
	* @param	array	$filter			Filtering criteria. Possible key/values are:
	*						- order_by, order_dir: the tickets fields by which to sort the results. Can
	*						  also be: customer, assigned_to, owner.
	*						- user_id : Return only tickets for the specified user.
	*						- view : In conjunction with user_id, specified what involvment the user needs
	*						  to have. Can be: 1-Any, 2-Assigned to, 3-Owned by, 4-Created by.
	*						- status, type, private : Return tickets of specified type and/or status.
	*						- types_main_only; Return only tickets of types which have not been marked
	*						  as 'ignored in totals'
	*						- escalated_only: Return only escalated tickets
	*						- unscheduled_only: Only tickets without schedule
	*						- customer_id : Return tickets for the specified customer.
	*						- assigned_user_id : Return only tickets from customers assigned to this user ID.
	*						- keywords : Search tickets containing the specified keyword(s).
	*						- keywords_phrase : If True, treat the keywords as a single phrase, otherwise search individual keywords.
	*						- in_subject, in_comments : If to search the keywords in subjects and/or comments.
	*						- date_from, date_to : Return tickets created in this interval.
	*						- billable_only: If True, return only tickets of a billable type
	*						- customer_order_id: Return only tickets belonging to this customer order. If -1, then
	*						  return only tickets which have not been yet assigned to a customer order.
	*						- not_linked_ir: Return only tickets for which NONE of the details is linked to IR
	*						- not_seen_manager: Return only tickets not seen by manager
	*						- days_no_update: Return only tickets that have not been updated in this number of days
	*						- load_schedule: Load the schedule date for the tickets
	*						- limit, start : How many computers to return and from where to start
	*						  the counting.
	* @param	int	$count			(By referenced) If set when function is called, it will be
	*						set to the total number of tickets that would match the
	*						filtering criteria.
	* @return	array(Ticket)			Array of Ticket objects that match the criteria.
	*/
	public static function get_tickets ($filter = array(), &$count=0)
	{
		$ret = array ();

		// Set some defaults
		if (!isset ($filter['order_by'])) $filter['order_by'] = 'last_modified';
		if (!isset ($filter['order_dir'])) $filter['order_dir'] = 'DESC';

		$q = 'FROM '.TBL_TICKETS.' t ';

		$scheduled_linked = false;
		if (($filter['order_by'] == 'customer') or isset($filter['account_manager']))
		{
			$q.= 'LEFT JOIN '.TBL_CUSTOMERS.' c ON t.customer_id = c.id ';
			if($filter['order_by'] == 'customer'){
				$filter['order_by'] = 'c.name';
			}
		}
		elseif ($filter['order_by'] == 'assigned_to')
		{
			$q.= 'LEFT OUTER JOIN '.TBL_USERS.' u ON t.assigned_id = u.id ';
			$filter['order_by'] = 'u.fname '.$filter['order_dir'].', u.lname '.$filter['order_dir'];
			$filter['order_dir'] = '';
		}
		elseif ($filter['order_by'] == 'owner')
		{
			$q.= 'LEFT JOIN '.TBL_USERS.' u ON t.user_id = u.id ';
			$filter['order_by'] = 'u.fname '.$filter['order_dir'].', u.lname '.$filter['order_dir'];
			$filter['order_dir'] = '';
		}
		elseif ($filter['order_by'] == 'scheduled')
		{
			$scheduled_linked = true;
			$q.= 'LEFT JOIN '.TBL_TASKS.' tsk ON t.id=tsk.ticket_id ';
			$filter['order_by'] = 'tsk.date '.$filter['order_dir'];
			$filter['order_dir'] = '';
		}
		else
		{
			$filter['order_by'] = 't.'.$filter['order_by'];
		}

		if (!$scheduled_linked and $filter['unscheduled_only']) $q.= 'LEFT JOIN '.TBL_TASKS.' tsk ON t.id=tsk.ticket_id ';

		if ($filter['user_id'])
		{
			if ($filter['view'] == 1 and $filter['user_id'])
			{
				$q.= 'LEFT OUTER JOIN '.TBL_TICKETS_CC.' cc ON t.id=cc.ticket_id ';
			}

			$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' g ON ';
			switch ($filter['view'])
			{
				case 1:	$q.= '(t.owner_id=g.group_id OR t.assigned_id=g.group_id OR t.user_id=g.group_id OR cc.user_id=g.group_id) '; break;
				case 2:	$q.= 't.assigned_id=g.group_id '; break;
				case 3:	$q.= 't.owner_id=g.group_id '; break;
				case 4:	$q.= 't.user_id=g.group_id '; break;
			}
		}

		if (($filter['keywords'] and $filter['in_comments']))
		{
			$q.= 'INNER JOIN '.TBL_TICKETS_DETAILS.' td ON t.id=td.ticket_id ';
		}
		if ($filter['assigned_user_id'])
		{
			// Check both direct user assignment and group assignment
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_CUSTOMERS_ASSIGNED.' ac ON t.customer_id=ac.customer_id ';
			$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' ug ON ac.user_id=ug.group_id ';
		}

		if ($filter['billable_only'] or $filter['types_main_only']) $q.= 'INNER JOIN '.TBL_TICKETS_TYPES.' tt ON t.type=tt.id ';

		$q.= 'WHERE ';


		if ($filter['not_seen_manager_or_not_ir'])
		{
			// We have to use queries in order to retrieve only tickets where NONE of the details are linked to IRs
			//$q.= ' (0=(SELECT max(td1.intervention_report_id) FROM '.TBL_TICKETS_DETAILS.' td1 WHERE td1.ticket_id=t.id) OR ';
			//$q.= 't.seen_manager_id=0) AND ';
			$q.= ' NOT (0<(SELECT max(td1.intervention_report_id) FROM '.TBL_TICKETS_DETAILS.' td1 WHERE td1.ticket_id=t.id) OR ';
			$q.= '0<t.seen_manager_id) AND ';
		}
		else
		{
			if ($filter['not_linked_ir'])
			{
				// We have to use sub-queries in order to retrieve only tickets where NONE of the details are linked to IRs
				$q.= ' 0=(SELECT max(td1.intervention_report_id) FROM '.TBL_TICKETS_DETAILS.' td1 WHERE td1.ticket_id=t.id) AND ';
			}
			if ($filter['not_seen_manager']) $q.= 't.seen_manager_id=0 AND ';
		}

		if (intval($filter['days_no_update']) > 0)
		{
			$filter['days_no_update'] = intval($filter['days_no_update']);
			$q.= ' '.(time() - ($filter['days_no_update']*24*3600)).'>=';
			$q.= '(SELECT max(td2.created) FROM '.TBL_TICKETS_DETAILS.' td2 WHERE td2.ticket_id=t.id) AND ';
		}

		if ($filter['unscheduled_only']) $q.= 'tsk.id IS NULL AND ';

		if (isset($filter['user_id']))
		{
			if ($filter['user_id'])
			{
				if (!is_array($filter['user_id'])) $filter['user_id'] = array ($filter['user_id']);
				if (count($filter['user_id']) > 0)
				{
					$q.= '(';
					switch ($filter['view'])
					{
						case 1:
							for ($i = 0; $i<count($filter['user_id']); $i++)
							{
								$user_id = $filter['user_id'][$i];
								$q.= '(t.owner_id='.$user_id.' OR t.assigned_id='.$user_id.' OR t.user_id='.$user_id.' ';
								$q.= 'OR cc.user_id='.$user_id.' OR g.user_id='.$user_id.') OR ';
							}
							break;
						case 2:
							for ($i = 0; $i<count($filter['user_id']); $i++)
							{
								$q.= '(t.assigned_id='.$filter['user_id'][$i].' OR g.user_id='.$filter['user_id'][$i].') OR ';
							}
							break;
						case 3:
							for ($i = 0; $i<count($filter['user_id']); $i++)
							{
								$q.= '(t.owner_id='.$filter['user_id'][$i].' OR g.user_id='.$filter['user_id'][$i].') OR ';
							}
							break;
						case 4:
							for ($i = 0; $i<count($filter['user_id']); $i++)
							{
								$q.= '(t.user_id='.$filter['user_id'][$i].' OR g.user_id='.$filter['user_id'][$i].') OR ';
							}
							break;
					}
					$q = preg_replace('/OR\s*$/', '', $q).') AND ';
				}
			}
		}

		if (isset($filter['status']))
		{
			if ($filter['status'] == -1) $q.= 't.status<>'.TICKET_STATUS_CLOSED.' AND ';
			else
			{
				if (!is_array ($filter['status'])) $filter['status'] = array ($filter['status']);

				$q.= '(';
				for ($i=0; $i<count($filter['status']); $i++)
				{
					if ($filter['status'][$i]>0) $q.= 't.status = '.$filter['status'][$i].' OR ';
				}
				$q = preg_replace ('/OR\s*$/', '', $q).') AND ';
			}
		}
		if ($filter['escalated_only']) $q.= 't.escalated>0 AND ';

		if ($filter['type'])
		{
			if (!is_array ($filter['type'])) $filter['type'] = array ($filter['type']);

			$q.= '(';
			for ($i=0; $i<count($filter['type']); $i++)
			{
				if ($filter['type'][$i]>0) $q.= 't.type = '.$filter['type'][$i].' OR ';
			}
			$q = preg_replace ('/OR\s*$/', '', $q).') AND ';
		}

		if($filter['customer_id']){
            if(!isset($filter['customer_ids'])) $filter['customer_ids'] = array();
            else{
                if(!is_array($filter['customer_ids'])) $filter['customer_id'] = array($filter['customer_ids']);                    
            }
            if(is_array($filter['customer_ids'])){
                if(!is_array($filter['customer_id'])){
                    $filter['customer_id'] = array($filter['customer_id']);    
                }
                $filter['customer_ids'] = array_merge($filter['customer_ids'], $filter['customer_id']);
            }
        }
        if ($filter['customer_ids'])
		{
			if (!is_array ($filter['customer_ids'])) $filter['customer_ids'] = array ($filter['customer_ids']);

			$q.= '(';
			for ($i=0; $i<count($filter['customer_ids']); $i++)
			{
				if ($filter['customer_ids'][$i]>0) $q.= 't.customer_id='.$filter['customer_ids'][$i].' OR ';
			}
			$q = preg_replace ('/OR\s*$/', '', $q).') AND ';
		}

		if ($filter['assigned_user_id'])
		{
			$q.= '(ac.user_id='.$filter['assigned_user_id'].' OR ';
			$q.= '(ug.group_id IS NOT NULL AND ug.user_id='.$filter['assigned_user_id'].')) AND ';
		}


		if (isset($filter['private']) and $filter['private']!=-2) $q.= 't.private='.$filter['private'].' AND ';

		if ($filter['keywords'])
		{
			if (!$filter['keywords_phrase']) $filter['keywords'] = preg_split ('/\s+/', $filter['keywords']);
			if (is_string($filter['keywords'])) $filter['keywords'] = array ($filter['keywords']);

			$group_with = ($filter['keywords_and'] ? 'AND' : 'OR');
			$q.= '(';
			for ($i=0; $i<count($filter['keywords']); $i++)
			{
				if ($filter['in_subject']) $q.= 't.subject like "%'.db::db_escape ($filter['keywords'][$i]).'%" '.$group_with.' ';
				if ($filter['in_comments']) $q.= 'td.comments like "%'.db::db_escape ($filter['keywords'][$i]).'%" '.$group_with.' ';
			}
			$q = preg_replace ('/'.$group_with.'\s*$/', '', $q).') AND ';
		}

		if(isset($filter['account_manager']) and $filter['account_manager']!='') $q.= " c.account_manager=".$filter['account_manager']." AND ";

		if ($filter['billable_only']) $q.= 'tt.is_billable=1 AND ';
		if ($filter['types_main_only']) $q.= 'tt.ignore_count=0 AND ';
		if ($filter['date_from']) $q.= 't.created>='.$filter['date_from'].' AND ';
		if ($filter['date_to']) $q.= 't.created<='.$filter['date_to'].' AND ';

		if (isset($filter['customer_order_id']))
		{
			if ($filter['customer_order_id'] > 0) $q.= 't.customer_order_id='.$filter['customer_order_id'].' AND ';
			else $q.= 't.customer_order_id=0 AND ';
		}

		$q = preg_replace ('/\(\s*\)\s*AND\s*/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);

		if (isset ($count))
		{
			$q_count = 'SELECT count(distinct t.id) AS cnt '.$q;
			$count = db::db_fetch_field ($q_count, 'cnt');
		}

		if ($filter['order_by'] == 't.updated' or $filter['order_by'] == 'updated') $filter['order_by'] = 't.last_modified';
		$q = 'SELECT DISTINCT t.id '.$q.' ORDER BY '.$filter['order_by'].' '.$filter['order_dir'].' ';

		if (isset ($filter['start']) and isset ($filter['limit']))
		{
			$q.= 'LIMIT '.$filter['start'].', '.$filter['limit'].' ';
		}


		$ids = db::db_fetch_array ($q);

		foreach ($ids as $id)
		{
			$ret[] = new Ticket ($id->id);
		}

		if ($filter['load_schedule'])
		{
			for ($i=0; $i<count($ret); $i++) $ret[$i]->load_schedule_date ();
		}

		return $ret;
	}




	public static function get_tickets_by_PO($filter = array(), &$count=0)
	{
		$ret = array();
		if (!isset ($filter['order_by'])) $filter['order_by'] = 'last_modified';
		if (!isset ($filter['order_dir'])) $filter['order_dir'] = 'DESC';

		$query = "FROM ".TBL_TICKETS;
		$query .= " WHERE po like '%".$filter['keyword']."%' ";
		//alternatively we could use this type of search for approximative
		//$query .= "WHERE MATCH(po) AGAINST(".$filter['keyword']." in boolean mode)";


		if (isset ($count))
		{
			$q_count = 'SELECT count(distinct id) AS cnt '.$query;
			//debug($q_count);
			$count = db::db_fetch_field ($q_count, 'cnt');
		}

		$q = "select id ".$query;

		if (isset ($filter['start']) and isset ($filter['limit']))
		{
			$q.= 'LIMIT '.$filter['start'].', '.$filter['limit'].' ';
		}

		//debug($q);
		$ids = db::db_fetch_array ($q);

		foreach ($ids as $id)
		{
			$ret[] = new Ticket ($id->id);
		}

		return $ret;
	}


	/**
	* [Class Method] Returns a list with the tickets according to a specified criteria
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- customer_id: Return tickets for a customer
	* @param	array					Associative array, keys being ticket IDs and values being ticket subjects
	*/
	public static function get_tickets_list ($filter = array ())
	{
		$ret = array ();

		$q = 'SELECT t.id, t.subject FROM '.TBL_TICKETS.' t WHERE ';

		if ($filter['customer_id']) $q.= 't.customer_id='.$filter['customer_id'].' AND ';
                if($filter['status']){
                    if(!is_array($filter['status']) and is_numeric($filter['status'])){
                        $q .= " t.status=".$filter['status']." AND ";
                    } else if(is_array($filter['status'])) {
                        $stats = "(";
                        foreach($filter['status'] as $stat){
                            $stats .= $stat.",";
                        }
                        $stats = preg_replace('/,$/', ')', $stats);
                        if($stats!='()'){
                            $q.=' t.status in '.$stats.' AND ';
                        }
                    }
                }
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		if(isset($filter['order_by']))
                {
                    $q.= " ORDER BY ".$filter['order_by'];
                    if(isset($filter['order_dir'])) $q.=" ".$filter['order_dir'];
                } else {
                    $q.= 'ORDER BY t.id ';
                }
		$ret = DB::db_fetch_list ($q);

		return $ret;
	}


	/**
	* [Class Method] Returns a list of tickets that can be billed - meaning they are not closed and they are of a billable type
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	* 							- customer_id: Return only tickets for the specified customer
	*							- details_user_id: Return only tickets in which the specified user
	*							  has made contributions (added lines)
	*/
	public static function get_billable_tickets_list ($filter = array ())
	{
		$ret = array ();

		$q = 'SELECT DISTINCT t.id, t.subject FROM '.TBL_TICKETS.' INNER JOIN '.TBL_TICKETS_TYPES.' tt ';
		$q.= 'ON t.type=tt.id ';
		if ($filter['details_user_id'])
		{
			$q.= 'INNER JOIN '.TBL_TICKETS_DETAILS.' d ON t.id=d.ticket_id ';
		}
		$q.= 'WHERE t.status<>'.TICKET_STATUS_CLOSED.' AND tt.type.is_billable=1 ';

		if ($filter['customer_id']) $q.= 'AND t.customer_id='.$filter['customer_id'].' ';
		if ($filter['details_user_id']) $q.= 'AND d.user_id='.$filter['details_user_id'].' ';

		$q.= 'ORDER BY t.id DESC ';

		$ret = DB::db_fetch_list ($q);
		return $ret;
	}


	/**
	* [Class Method] Returns all tickets (not closed) associated with a specific computer
	* @param	int	$computer_id		The computer ID
	* @return	array(Ticket)			Array with the found tickets (if any)
	*/
	public static function get_computer_tickets ($computer_id)
	{
		$ret = array ();

		if ($computer_id)
		{
			$q = 'SELECT DISTINCT t.id FROM '.TBL_TICKETS_OBJECTS.' o INNER JOIN ';
			$q.= TBL_TICKETS.' t ON o.ticket_id=t.id ';
			$q.= 'WHERE o.object_class='.TICKET_OBJ_CLASS_COMPUTER.' AND object_id='.$computer_id.' AND ';
			$q.= 't.status<>'.TICKET_STATUS_CLOSED.' ';
			$q.= 'ORDER BY t.last_modified';
			$ids = DB::db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new Ticket ($id);
		}

		return $ret;
	}


	/**
	* [Class Method] Returns the tickets history for a specific computer.
	* @param	int			$computer_id		The computer ID
	* @return	array						Array of generic objects with information about the found
	*								tickets, with the fields: id, subject, status, assigned_id, created, last_modified
	*/
	public static function get_computer_tickets_history ($computer_id)
	{
		$ret = array ();

		if ($computer_id)
		{
			$q = 'SELECT DISTINCT t.id, t.subject, t.status, t.assigned_id, t.created, t.last_modified ';
			$q.= 'FROM '.TBL_TICKETS_OBJECTS.' o INNER JOIN '.TBL_TICKETS.' t ON o.ticket_id=t.id ';
			$q.= 'WHERE o.object_class='.TICKET_OBJ_CLASS_COMPUTER.' AND object_id='.$computer_id.' ';
			$q.= 'ORDER BY t.last_modified DESC';
			$ret = DB::db_fetch_array ($q);
		}

		return $ret;
	}


	/**
	* [Class Method] Returns the tickets statistics for an user
	* @param	int	$user_id		The ID of the user for whom to return the stats
	* @param	array				Associative array with the number of not closed tickets, the
	*						keys being: cnt_involved, cnt_assigned, cnt_all and cnt_tasks (number of tasks for the day)
	*/
	public static function get_tickets_stats ($user_id)
	{
		$ret = array ();

		$q = 'SELECT count(distinct t.id) as cnt_involved_all FROM '.TBL_TICKETS.' t ';
		$q.= 'INNER JOIN '.TBL_TICKETS_TYPES.' types on t.type=types.id ';
		$q.= 'LEFT OUTER JOIN '.TBL_TICKETS_CC.' cc ON t.id=cc.ticket_id ';
		$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' g ON ';
		$q.= '(t.owner_id=g.group_id OR t.assigned_id=g.group_id OR t.user_id=g.group_id OR cc.user_id=g.group_id) ';
		$q.= 'WHERE ((t.owner_id='.$user_id.' OR t.assigned_id='.$user_id.' OR t.user_id='.$user_id.' OR ';
		$q.= 'cc.user_id='.$user_id.' OR g.user_id='.$user_id.')) ';
		$q.= 'AND t.status<>'.TICKET_STATUS_CLOSED;
		$ret['cnt_involved_all'] = db::db_fetch_field ($q, 'cnt_involved_all');

		$q = 'SELECT count(distinct t.id) as cnt_involved FROM '.TBL_TICKETS.' t ';
		$q.= 'INNER JOIN '.TBL_TICKETS_TYPES.' types on t.type=types.id ';
		$q.= 'LEFT OUTER JOIN '.TBL_TICKETS_CC.' cc ON t.id=cc.ticket_id ';
		$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' g ON ';
		$q.= '(t.owner_id=g.group_id OR t.assigned_id=g.group_id OR t.user_id=g.group_id OR cc.user_id=g.group_id) ';
		$q.= 'WHERE ((t.owner_id='.$user_id.' OR t.assigned_id='.$user_id.' OR t.user_id='.$user_id.' OR ';
		$q.= 'cc.user_id='.$user_id.' OR g.user_id='.$user_id.')) ';
		$q.= 'AND t.status<>'.TICKET_STATUS_CLOSED.' AND types.ignore_count<>1 ';
		$ret['cnt_involved'] = db::db_fetch_field ($q, 'cnt_involved');

		$q = 'SELECT count(DISTINCT t.id) as cnt_assigned_all, sum(if(types.ignore_count=0,1,0)) as cnt_assigned ';
		$q.= 'FROM '.TBL_TICKETS.' t ';
		$q.= 'INNER JOIN '.TBL_TICKETS_TYPES.' types on t.type=types.id ';
		$q.= 'LEFT OUTER JOIN '.TBL_USERS_GROUPS.' g ';
		$q.= 'ON t.assigned_id=g.group_id ';
		$q.= 'WHERE ((t.assigned_id='.$user_id.' OR g.user_id='.$user_id.')) ';
		$q.= 'AND t.status<>'.TICKET_STATUS_CLOSED.' ';
		$res = db::db_fetch_row($q);
		$ret['cnt_assigned'] = $res['cnt_assigned'];
		$ret['cnt_assigned_all'] = $res['cnt_assigned_all'];

		$q = 'SELECT count(distinct t.id) as cnt_all_all, sum(if(types.ignore_count=0,1,0)) as cnt_all ';
		$q.= 'FROM '.TBL_TICKETS.' t ';
		$q.= 'INNER JOIN '.TBL_TICKETS_TYPES.' types on t.type=types.id ';
		$q.= 'WHERE t.status<>'.TICKET_STATUS_CLOSED;
		$res = db::db_fetch_row($q);
		$ret['cnt_all'] = $res['cnt_all'];
		$ret['cnt_all_all'] = $res['cnt_all_all'];

		$q = 'SELECT count(*) as cnt_escalated FROM '.TBL_TICKETS.' WHERE escalated>0 AND status<>'.TICKET_STATUS_CLOSED;
		$ret['cnt_escalated'] = db::db_fetch_field ($q, 'cnt_escalated');

		//$q = 'SELECT count(*) as cnt_tasks FROM '.TBL_TASKS.' WHERE user_id='.$user_id.' AND date='.get_first_hour();
		$q = 'SELECT count(*) as cnt_tasks FROM '.TBL_TASKS.' WHERE user_id='.$user_id.' AND ';
		$q.= 'date_start>='.get_first_hour().' AND date_end<='.get_last_hour();
		$ret['cnt_tasks'] = db::db_fetch_field ($q, 'cnt_tasks');

		return $ret;
	}


	/****************************************************************/
	/* Statuses management						*/
	/****************************************************************/

	/** [Class Method] Returns a list with the possible statuses for tickets */
	public static function get_statuses_list ()
	{
		return Ticket::get_ticket_statuses_list ();
	}


	/**
	* [Class Method] Returns a list with the escalation intervals for each status (where defined)
	* @param	bool		$nonzero		If True (default), return only for statuses where interval is defined
	* @return	array					Associative array, they keys being statuses IDs and
	*							the values being escalation intervals (in seconds),
	*							for those statuses where escalation intervals are defined.
	*/
	public static function get_statuses_escalation_intervals ($nonzero = true)
	{
		$ret = array ();

		$q = 'SELECT id, escalate_after FROM '.TBL_TICKETS_STATUSES.' ';
		if ($nonzero) $q.= 'WHERE escalate_after>0 ';
		$ret = DB::db_fetch_list ($q);

		return $ret;
	}


	/** [Class Method] Adds a new status the list of possible statuses for tickets */
	function add_status ($status_name)
	{
		$ret = null;
		if ($status_name)
		{
			$q = 'INSERT INTO '.TBL_TICKETS_STATUSES.' (name) VALUES ("'.db::db_escape($status_name).'")';
			db::db_query ($q);

			$ret = db::db_insert_id ();
		}
		return $ret;
	}


	/** [Class Method] Renames a ticket status and optionally sets its escalation interval */
	function rename_status ($id, $status_name, $interval = null)
	{
		if ($id and $status_name)
		{
			$q = 'UPDATE '.TBL_TICKETS_STATUSES.' SET name="'.db::db_escape ($status_name).'" ';
			if (!is_null ($interval)) $q.= ', escalate_after='.$interval.' ';
			$q.= 'WHERE id='.$id;
			db::db_query ($q);
		}
	}


	/** [Class Method] Checks if an escalation interval can be defined for a status */
	function can_escalate_status ($id)
	{
		$ret = true;
		if ($id)
		{
			if ($id==TICKET_STATUS_CLOSED) $ret = false;
		}
		return $ret;
	}


	/** [Class Method] Checks if a status can be deleted */
	function can_delete_status ($id, $raise_error = true)
	{
		$ret = false;
		if ($id)
		{
			$ret = true;
			// Check if the status is not one of the standard ones
			if ($id==TICKET_STATUS_NEW or $id==TICKET_STATUS_ASSIGNED or $id==TICKET_STATUS_WAITING_CUSTOMER or $id==TICKET_STATUS_CLOSED)
			{
				$ret = false;
				if ($raise_error) error_msg ($this->get_string('ASSIGNED_HASNT_ACCESS_TO_CUSTOMER'));
			}

			// Check if the status is not already in use
			$cnt = db::db_fetch_field ('SELECT count(*) as cnt FROM '.TBL_TICKETS.' WHERE status='.$id, 'cnt');
			if ($cnt > 0)
			{
				if ($raise_error) error_msg ($this->get_string('CANT_DELETE_USED_STAT'));
			}
		}
		return $ret;
	}

	/** [Class Method] Deletes a status from the list of possible statuses */
	function delete_status ($id)
	{
		if ($id)
		{
			$q = 'DELETE FROM '.TBL_TICKETS_STATUSES.' WHERE id='.$id;
			db::db_query ($q);
		}
	}


	/****************************************************************/
	/* Types management						*/
	/****************************************************************/

	/** [Class Method] Returns the ID of the default type for customer created tickets */
	public static function get_default_customer_ticket_type ()
	{
		$q = 'SELECT id FROM '.TBL_TICKETS_TYPES.' WHERE is_customer_default=1';
		$ret = db::db_fetch_field ($q, 'id');

		return $ret;
	}


	/** [Class Method] Sets the ID of the default type for customer created tickets */
	public static function set_default_customer_ticket_type ($id)
	{
		db::db_query ('UPDATE '.TBL_TICKETS_TYPES.' SET is_customer_default=0');
		db::db_query ('UPDATE '.TBL_TICKETS_TYPES.' SET is_customer_default=1 WHERE id='.db::db_escape($id));
	}

	/** [Class Method] Returns a list with the possible types for tickets */
	public static function get_types_list ($filter = array ())
	{
		return self::get_ticket_types_list($filter);
	}


	/** [Class Method] Returns the definition for a specific ticket type */
	public static function get_type_def ($id)
	{
		if ($id)
		{
			$q = 'SELECT * FROM '.TBL_TICKETS_TYPES.' WHERE id='.$id;
			$ret = DB::db_fetch_array ($q);
		}
		return $ret[0];
	}

	/** [Class Method] Returns the definitions for all ticket types */
    public static function get_types_defs ()
	{
		$q = 'SELECT * FROM '.TBL_TICKETS_TYPES.' ORDER BY name ';
		$ret = DB::db_fetch_array ($q);

		return $ret;
	}

	/** [Class Method] Adds a new type to the list of possible types for tickets */
	public static function add_type ($type_name, $ignore_count = 0, $is_billable = 0)
	{
		$ret = null;
		if ($type_name)
		{
			$q = 'INSERT INTO '.TBL_TICKETS_TYPES.' (name, ignore_count, is_billable) VALUES ';
			$q.= '("'.db::db_escape($type_name).'", '.$ignore_count.','.$is_billable.')';
			db::db_query ($q);
			$ret = db::db_insert_id ();
		}
		return $ret;
	}


	/** [Class Method] Renames a ticket type */
	public static function rename_type ($id, $type_name, $ignore_count, $is_billable)
	{
		if ($id and $type_name)
		{
			$q = 'UPDATE '.TBL_TICKETS_TYPES.' SET name="'.db::db_escape ($type_name).'", ignore_count='.$ignore_count.', ';
			$q.= 'is_billable='.$is_billable.' WHERE id='.$id;
			db::db_query ($q);
		}
	}


	/** [Class Method] Checks if a type can be deleted */
    public static function can_delete_type ($id)
	{
		$ret = true;
		if ($id)
		{
			// Check if the type is not already in use
			$cnt = db::db_fetch_field ('SELECT count(*) as cnt FROM '.TBL_TICKETS.' WHERE type='.$id, 'cnt');
			if ($cnt > 0)
			{
				//error_msg (self::get_string('CANT_DELETE_USER_TYPE'));
				$ret = false;
			}
		}
		return $ret;
	}


	/** [Class Method] Deletes a type from the list of possible types */
    public static function delete_type($id)
	{
		if ($id)
		{
			$q = 'DELETE FROM '.TBL_TICKETS_TYPES.' WHERE id='.$id;
			db::db_query ($q);
		}
	}


	/** [Class Method] Returns the list of current escalation recipients */
    public static function get_escalation_recipients_list ()
	{
		$ret = array ();
		$q = 'SELECT r.user_id FROM '.TBL_TICKETS_ESCALATION_RECIPIENTS.' r ';
		$q.= 'LEFT JOIN '.TBL_USERS.' u on r.user_id = u.id ';
		$q.= 'ORDER BY u.fname, u.lname ';

		$ids = db::db_fetch_vector ($q);
		$users_list = User::get_users_list ();

		foreach ($ids as $id)
		{
			$ret[$id] = $users_list[$id];
		}
		return $ret;
	}


	/**
	* [Class Method] Sets the list of escalation recipients
	* @param	array(int)	$recips		Array with the user IDs of the escalation recipients
	*/
    public static function set_escalation_recipients ($recips = array ())
	{
		if (is_array ($recips))
		{
			db::db_query ('DELETE FROM '.TBL_TICKETS_ESCALATION_RECIPIENTS.' ');

			$q = 'INSERT INTO '.TBL_TICKETS_ESCALATION_RECIPIENTS.' (user_id) VALUES ';
			foreach ($recips as $id)
			{
				$q.= '('.$id.'), ';
			}
			$q = preg_replace ('/\,\s*$/', '', $q);
			db::db_query ($q);
		}
	}
	/**
	* [Class Method] checks if a number is valid ticket id
	* @param	int $id		The number that has to be checked
	* @return       bool
	*/
    public static function isValidID($id)
	{
		$ret = 0;
		$query = "select count(*) as cnt from ".TBL_TICKETS." where id=".$id;
		$cnt = db::db_fetch_field($query, 'cnt');
		if($cnt != 0) $ret = 1;
		return $ret;
	}

	/**
	 * [Class Method]
	 * Gets the total billable time allready included in ir's for this ticket
	 *
	 * @param int $id - the id of the ticket to get this info for.
	 * 					If this method is used from inside an object, the id of that object will be used
	 * @return int or false - returns the time in minutes or false if no id was specified
	 */
    public static function get_ir_tbbtime($id = null)
	{
		//if($this->id and get_class($this)=="Ticket") $id = $this->id;
		if($id == null) return false;
		//1. get a list of all the details id's for this ticket
		$query = "select id from ".TBL_TICKETS_DETAILS." where ticket_id=".$id;

		$details_ids = array();
		//if($this->id)
		//	$details_ids = self::db_fetch_vector($query);
		//else
		$details_ids = db::db_fetch_vector($query);

		$total_time = 0;
		//2. get the billable time for the details that have a intervention_report_id set
		if(is_array($details_ids) and !empty($details_ids))
		{
			$query = "select tbb_time from ".TBL_TICKETS_DETAILS." where  intervention_report_id<>0 and ";
			if(count($details_ids) == 1)
			{
				$query.="id = ".$details_ids[0];
			}
			else
			{
				$query.=" id in (";
				for($i=0; $i<count($details_ids);$i++)
				{
					if($i!=count($details_ids)-1) $query.=$details_ids[$i].", ";
					else $query.=$details_ids[$i];
				}
				$query.=")";
			}
			$times = array();
			//if($this->id)
			//	$times = self::db_fetch_vector($query);
			//else
			$times = db::db_fetch_vector($query);

			foreach($times as $t)
			{
				$total_time+=$t;
			}
		}

		return $total_time;
	}

	/**
	 * [Class Method]
	 * Gets the total billable time for this ticket
	 *
	 * @param int $id - the id of the ticket to get this info for.
	 * 					If this method is used from inside an object, the id of that object will be used
	 * @return int or false - returns the time in minutes or false if no id was specified
	 */
	function get_tot_tbbtime($id = null)
	{
//		if($this->id and get_class($this)=="Ticket") $id = $this->id;
		if($id == null) return false;
		//1. get a list of all the details id's for this ticket
		$query = "select id from ".TBL_TICKETS_DETAILS." where ticket_id=".$id;

		$details_ids = array();
//		if($this->id)
//			$details_ids = self::db_fetch_vector($query);
//		else
			$details_ids = db::db_fetch_vector($query);

		$total_time = 0;
		//2. get the billable time for the details that have a intervention_report_id set
		if(is_array($details_ids) and !empty($details_ids))
		{
			$query = "select tbb_time from ".TBL_TICKETS_DETAILS." where ";
			if(count($details_ids) == 1)
			{
				$query.="id = ".$details_ids[0];
			}
			else
			{
				$query.=" id in (";
				for($i=0; $i<count($details_ids);$i++)
				{
					if($i!=count($details_ids)-1) $query.=$details_ids[$i].", ";
					else $query.=$details_ids[$i];
				}
				$query.=")";
			}
			$times = array();
//			if($this->id)
//				$times = self::db_fetch_vector($query);
//			else
				$times = db::db_fetch_vector($query);

			foreach($times as $t)
			{
				$total_time+=$t;
			}
		}

		return $total_time;
	}

	/**
	 * [Class Method] gets a list of tickets groupped by their status
	 *
	 * @param array $filter 	- 	possible values are customer_id
	 * @return array
     * */
	public static function get_tickets_list_by_status($filter=array())
	{
		$q = "select distinct status from ".TBL_TICKETS;
		$stats = db::db_fetch_vector($q);
		$ret = array();
		foreach($stats as $stat)
		{
			$query = "select id, subject from ".TBL_TICKETS." where status=".$stat;
			if(isset($filter['customer_id'])) $query.=" AND customer_id=".$filter['customer_id'];
			$tl = db::db_fetch_list($query);
			if(count($tl) > 0)
				$ret[$stat] = $tl;
		}
		return $ret;
	}
    
    public static function get_lm_tickets_evo($customer_id = null, $no_days_ago=15){
        $ret = array();
        $wdays = array(0=>'Su', 1=>'Mo', 2=>'Tu', 3=>'We', 4=>'Th', 5=>'Fr', 6=>'Sa');
        $current_time = mktime(0,0,0,date('m'), date('d'), date('y'));
        $last_period = mktime(0,0,0,date('m'), date('d')-$no_days_ago, date('y'));
        while($last_period <= $current_time){
            $lday = getdate($last_period);
            $end_per = mktime(0,0,0, $lday['mon'], $lday['mday']+1, $lday['year']);
            $query = 'select count(id) as cnt from '.TBL_TICKETS." where last_modified between ".$last_period." AND ".$end_per." AND status=10 ";
            if($customer_id){
                $query.=" AND customer_id=".$customer_id;
            }             
            $ret['closed'][] = intval(db::db_fetch_field($query, 'cnt'));
             $query = 'select count(id) as cnt from '.TBL_TICKETS." where created between ".$last_period." AND ".$end_per;
            if($customer_id){
                $query.=" AND customer_id=".$customer_id;
            } 
            $ret['new'][] = intval(db::db_fetch_field($query, 'cnt'));
            $query = 'select count(id) as cnt from '.TBL_TICKETS." where last_modified between ".$last_period." AND ".$end_per." AND status <> 10 ";
            if($customer_id){
                $query.=" AND customer_id=".$customer_id;
            } 
            $ret['not_closed'][] = intval(db::db_fetch_field($query, 'cnt'));
            $ret['days'][] = $wdays[$lday['wday']]." ".$lday['mday'];
            $last_period = $end_per;
        }        
        return $ret;
    }    
    public static function get_user_tickets_activity($no_days_ago=15, $customer_id=0, $user_id=0){
        $ret = array();
        $wdays = array(0=>'Sun', 1=>'Mon', 2=>'Tue', 3=>'Wed', 4=>'Thu', 5=>'Fri', 6=>'Sat');
        $current_time = mktime(0,0,0,date('m'), date('d'), date('y'));
        $last_period = mktime(0,0,0,date('m'), date('d')-$no_days_ago, date('y'));
        while($last_period <= $current_time){
            $lday = getdate($last_period);
            $end_per = mktime(0,0,0, $lday['mon'], $lday['mday']+1, $lday['year']);
            $query = 'select count(td.id) as cnt from '.TBL_TICKETS_DETAILS." td ";
            if($customer_id!=0){
                $query.=" inner join ".TBL_TICKETS." t ON td.ticket_id=t.id ";
            }
            $query.= " where td.created between ".$last_period." AND ".$end_per." ";
            if($customer_id!=0){
                $query.=" AND t.customer_id=".$customer_id;
            } 
            if($user_id!=0){
                $query .= " AND td.user_id=".$user_id; 
            }
            $ret['details'][] = intval(db::db_fetch_field($query, 'cnt'));
            
            //now select the sum of the bill_time in minutes from the tickets details 
            $query = 'select sum(td.bill_time) as smx from '.TBL_TICKETS_DETAILS." td ";
            if($customer_id!=0){
                $query.=" inner join ".TBL_TICKETS." t ON td.ticket_id=t.id ";
            }
            $query.= " where td.created between ".$last_period." AND ".$end_per." ";
            if($customer_id!=0){
                $query.=" AND t.customer_id=".$customer_id;
            } 
            if($user_id!=0){
                $query .= " AND td.user_id=".$user_id; 
            }
            $ret['td_bill_time'][] =  intval(db::db_fetch_field($query, 'smx'));
            
            $query = 'select sum(td.bill_time) as smx from '.TBL_TICKETS_DETAILS." td ";
            if($customer_id!=0){
                $query.=" inner join ".TBL_TICKETS." t ON td.ticket_id=t.id ";
            }
            $query.= " where td.intervention_report_id<>0 AND td.created between ".$last_period." AND ".$end_per." ";
            if($customer_id!=0){
                $query.=" AND t.customer_id=".$customer_id;
            } 
            if($user_id!=0){
                $query .= " AND td.user_id=".$user_id; 
            }
            $ret['ir_bill_time'][] =  intval(db::db_fetch_field($query, 'smx'));
            
            $ret['days'][] = $lday['month'].", ".$wdays[$lday['wday']]." ".$lday['mday'];
            $last_period = $end_per; 
        }
        return $ret;    
    }
    
    function get_work_time_stats($start_date, $end_date, $customer_id=0, $user_id=0){
        $ret = array();
        $wdays = array(0=>'Sun', 1=>'Mon', 2=>'Tue', 3=>'Wed', 4=>'Thu', 5=>'Fri', 6=>'Sat');       
        $std = getdate($start_date);
        $etd = getdate($end_date);
        $last_period = $start_date;
        while($last_period <= $end_date){
            $lday = getdate($last_period);
            $end_per = mktime(0,0,0, $lday['mon'], $lday['mday']+1, $lday['year']);
            
            //total time completed in ticket details for this day
            $query = 'select sum(td.bill_time) as smx from '.TBL_TICKETS_DETAILS." td ";
            if($customer_id!=0){
                $query.=" inner join ".TBL_TICKETS." t ON td.ticket_id=t.id ";
            }
            $query.= " where td.created between ".$last_period." AND ".$end_per." ";
            if($customer_id!=0){
                $query.=" AND t.customer_id=".$customer_id;
            } 
            if($user_id!=0){
                $query .= " AND td.user_id=".$user_id; 
            }
            $ret['td_bill_time'][] =  intval(db::db_fetch_field($query, 'smx'));
            
            //total bill time included in ir's for this day
            $query = 'select sum(td.bill_time) as smx from '.TBL_TICKETS_DETAILS." td ";
            if($customer_id!=0){
                $query.=" inner join ".TBL_TICKETS." t ON td.ticket_id=t.id ";
            }
            $query.= " where td.created between ".$last_period." AND ".$end_per." AND intervention_report_id<>0";
            if($customer_id!=0){
                $query.=" AND t.customer_id=".$customer_id;
            } 
            if($user_id!=0){
                $query .= " AND td.user_id=".$user_id; 
            }
            $ret['td_bill_time_ir'][] =  intval(db::db_fetch_field($query, 'smx'));
            
            //total bill time not included in ir's for this day
            $query = 'select sum(td.bill_time) as smx from '.TBL_TICKETS_DETAILS." td ";
            if($customer_id!=0){
                $query.=" inner join ".TBL_TICKETS." t ON td.ticket_id=t.id ";
            }
            $query.= " where td.created between ".$last_period." AND ".$end_per." AND intervention_report_id=0";
            if($customer_id!=0){
                $query.=" AND t.customer_id=".$customer_id;
            } 
            if($user_id!=0){
                $query .= " AND td.user_id=".$user_id; 
            }
            $ret['td_bill_time_nir'][] =  intval(db::db_fetch_field($query, 'smx'));
            
            
            $ret['days'][] = $lday['month'].", ".$wdays[$lday['wday']]." ".$lday['mday']; 
            
            $last_period = $end_per;
        }
        return $ret;
    }

    function get_work_times_by_op($start_date, $end_date,  $customer_id=0){
        $ret = array(); 
        //times in tickets not in IR's
        $query = "select td.user_id, sum(td.bill_time) as sxx from ".TBL_TICKETS_DETAILS." td INNER JOIN ".TBL_USERS." u on td.user_id=u.id ";
        if($customer_id){
            $query .= " INNER JOIN ".TBL_TICKETS." t ON td.ticket_id=t.id ";
        }
        $query .= " WHERE td.created between ".$start_date." AND ".$end_date." AND intervention_report_id=0 ";
        $query .= " AND u.customer_id=0 AND u.type=".USER_TYPE_KEYSOURCE." ";
        if($customer_id){
            $query .= " td.customer_id=".$customer_id;
        }
        $query .= " group by td.user_id ";

        $ret['tot_times_nir'] = db::db_fetch_list($query);

        $query = "select td.user_id, sum(td.bill_time) as sxx from ".TBL_TICKETS_DETAILS." td INNER JOIN ".TBL_USERS." u on td.user_id=u.id ";
        if($customer_id){
            $query .= " INNER JOIN ".TBL_TICKETS." t ON td.ticket_id=t.id ";
        }
        $query .= " WHERE td.created between ".$start_date." AND ".$end_date." AND intervention_report_id<>0 ";
        $query .= " AND u.customer_id=0 AND u.type=".USER_TYPE_KEYSOURCE." ";
        if($customer_id){
            $query .= " td.customer_id=".$customer_id;
        }
        $query .= " group by td.user_id ";

        $ret['tot_times_ir'] = db::db_fetch_list($query);

        return $ret;
    }
    
    function get_last_ks_handler(){
        class_load('User');
        $ret = 0;
        $comments = array_reverse($this->details);
        foreach($comments as $det){
            $usr = new User($det->assigned_id);
            if($usr->id){
                if(!$usr->is_customer_user()){
                    $ret = $usr->id;
                    break;
                }
            }
        }
        return $ret;
    }
    
    function verify_access() {
        $uid = get_uid();
        class_load('User');
        $user = new User($uid);
        if($user->type == USER_TYPE_CUSTOMER) {
            $customers_list = $user->get_users_customer_list();          
            if(!in_array($this->customer_id, $customers_list)){
                $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
		header("Location: $url\n\n");
                exit;
            }
        }
    }

    public static function get_ticket_statuses_list ()
    {
        $q = 'SELECT id, name FROM '.TBL_TICKETS_STATUSES.' ORDER BY name ';
        return db::db_fetch_list ($q);
    }


    /** Returns the list of ticket statuses names
     * @param	array		$filter		Associative array with filtering criteria. Can contain:
     *						- is_billable: If True, return only billable tickets types.
     * @return	array				Associative array, they keys being type IDs and the values being
     *						type names.
     */
    public static function get_ticket_types_list($filter = array ())
    {
        $q = 'SELECT id, name FROM '.TBL_TICKETS_TYPES.' ';

        if (isset($filter['is_billable']) and $filter['is_billable']) $q.= 'WHERE is_billable=1 ';

        $q.= 'ORDER BY name ';
        return db::db_fetch_list ($q);
    }
}
?>

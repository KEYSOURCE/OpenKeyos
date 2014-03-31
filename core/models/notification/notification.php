<?php

class_load ('NotificationRecipient');

/**
* Class for managing notifications
*
* The Notification class is the central point for creating and managing notifications, used
* by any other module that needs to send notifications to users.
*
* The most important method is the class method raise_notification(), which is used for creating
* notifications.
*
* For notifications associated with objects, it's the task of those objects to delete their
* associated recipients when the objects themselves are deleted.
*
* In order to inform the user about new notifications and notifications getting old, there
* will be a crontab task running externally and checking for what notifications there is
* a need to send e-mails.
*
* Starting from version 4.0, the notifications don't store user (recipient) info. Instead, there
* will be a single Notification object per event. The notification recipients are stored separately, in
* NotificationRecipient objects.
*
*/

class Notification extends Base
{
	/** Notification ID
	* @var int */
	var $id = null;
	
	/** The alert/error code - see $GLOBALS['NOTIF_CODES_TEXTS']
	* @var int */
	var $err_code = null;
	
	/** Severity level - see $GLOBALS['ALERT_NAMES']
	* @var int */
	var $level = ALERT_NONE;
	
	/** The time when the notification was created
	* @var int */
	var $raised = 0;
	
	/** The time when the notification was raised last time
	* @var int */
	var $raised_last = 0;
	
	/** The number of times this notification has been raised
	* @var int */
	var $raised_count = 0;

	//XXXXXXXXX
	/** When was the last e-mail notification sent 
	* @var int */
	var $emailed_last = 0;
	
	/** If e-mails for this notification are suspended
	* @var boolean */
	var $suspend_email = false;
	
	/** The object ID to which this item is related - if any
	* @var int */
	var $object_id = null;
	
	/** The class (type) of associated object - see $GLOBALS['NOTIF_OBJ_CLASSES']
	* @var int */
	var $object_class = null;

	/** The event code/ID specific to the related object (if any)
	* @var int */
	var $object_event_code = null;
	
	/** The sub-item of the object (if any) to which this notification is related
	* @var int */
	var $item_id = null;
	
	// XXXXXXXX
	/** The user to which this notification is addressed. If empty, this is a general notification
	* @var int */
	var $user_id = null;
	
	/** Additional text information about this error.
	* Associated NotificationRecipient can have a text fields as well, in which case that value
	* has priority.
	* @var string */
	var $text = null;
	
	/** The template to use when sending e-mail notifications - if it's not the default template.
	* Associated NotificationRecipient objects can enforce a different template for specific users.
	* @var string */
	var $template = '';
	
	/** If set, signifies that the notification expires at the specified date,
	* meaning it is automatically deleted 
	* @var time */
	var $expires = 0;
	
	/** If true, e-mails will not be generated when the notification is "repeated".
	* Associated NotificationRecipient can also block repeated notifications individually.
	* @var int */
	var $no_repeat = false;
	
	/** The Krifs ticket ID, if a ticket has been created for this notification 
	* @var int */
	var $ticket_id = null;
	
	
	/** Associative array with the recipients of this notifications. The keys are user IDs
	* and the values are NotificationRecipient objects.
	* @var array(NotificationRecipient) */
	var $recipients = array ();
	
	/** The name of the linked object (if there is a linked object) 
	* @var string */
	var $object_name = '';
	
	/** The URL for the linked object (if there is a linked object)
	* @var string */
	var $object_url = '';
	
	/** The object linked to this notification (if there is a linked object)
	* @var mixed */
	var $linked_object = null;
	
	/** If the notification is linked to a ticket, this array will store the list of users which
	* are currently working on that ticket (if any). The array keys are user IDs and the values
	* are the time when they marked that they are working on it 
	* @var array */
	var $now_working = array ();
	
	/** The ticket associated with this notification, if any. Note that this is loaded only on request with load_ticket ()
	* @var Ticket */
	var $ticket = null;
	
	var $show_in_console = 1;
	
	var $table = TBL_NOTIFICATIONS;
	var $fields = array ('id', 'event_code', 'level', 'raised', 'raised_last', 'raised_count', 'emailed_last', 'suspend_email', 'object_class', 'object_id', 'object_event_code', 'item_id', 'user_id', 'text', 'template', 'expires', 'no_repeat', 'ticket_id', 'show_in_console');


	/**
	* Constructor. Also loads a notification data if an ID is provided
	* @param	int	$id		The ID of the notification object to load
	*/
	function __construct($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	function __destruct()
	{
		if($this->id) $this->id = null;
		if($this->err_code) $this->err_code = null;
		if($this->level) $this->level = null;
		if($this->raised) $this->raised = null;
		if($this->raised_last) $this->raised_last = null;
		if($this->raised_count) $this->raised_count = null;
		if($this->emailed_last) $this->emailed_last = null;
		if($this->suspend_email) $this->suspenf_email = null;
		if($this->object_id) $this->object_id = null;
		if($this->object_class) $this->object_class = null;
		if($this->object_event_code) $this->object_event_code = null;
		if($this->item_id) $this->item_id = null;
		if($this->user_id) $this->user_id = null;
		if($this->text) $this->text = null;
		if($this->template) $this->template = null;
		if($this->expires) $this->expires = null;
		if($this->no_repeat) $this->no_repeat = null;
		if($this->ticket_id) $this->ticket_id = null;
		if($this->recipients) $this->recipients = null;
		if($this->object_name) $this->object_name = null;
		if($this->object_url) $this->object_url = null;
		if($this->linked_object) $this->linked_object = null;
		if($this->now_working) $this->now_working = null;
		if($this->ticket) $this->ticket=null;
	}
	

    public function save_data(){
        parent::save_data();
    }

	/** Loads the object's data */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				if (!$this->text) $this->text = $this->get_text ();
				
				// Load the notification's recipients
				$this->recipients = NotificationRecipient::get_notification_recipients ($this->id);
				
				if ($this->object_id and $this->object_class)
				{
					$this->object_name = '['.$GLOBALS['NOTIF_OBJ_CLASSES_SHORT'][$this->object_class].': ';
					// Initially, this was done by actually instantiating the actual objects. 
					// But now direct SQL is used, for speed.
					switch ($this->object_class)
					{
						case NOTIF_OBJ_CLASS_COMPUTER:
							$q_name = 'SELECT comp.netbios_name, c.name FROM '.TBL_COMPUTERS.' comp INNER JOIN '.TBL_CUSTOMERS.' c ';
							$q_name.= 'ON comp.customer_id=c.id WHERE comp.id='.$this->object_id;
							$d = $this->db_fetch_array ($q_name); $d=$d[0];
							$this->object_name.= '#'.$this->object_id.'] '.$d->netbios_name.' ('.$d->name.')';
							break;
							
						case NOTIF_OBJ_CLASS_CUSTOMER:
							$q_name = 'SELECT name FROM '.TBL_CUSTOMERS.' c WHERE id='.$this->object_id;
							$this->object_name.= '#'.$this->object_id.'] '.$this->db_fetch_field ($q_name, 'name');
							break;
							
						case NOTIF_OBJ_CLASS_KRIFS:
							$q_subject = 'SELECT subject FROM '.TBL_TICKETS.' WHERE id='.$this->object_id;
							$this->object_name.= '#'.$this->object_id.'] '.$this->db_fetch_field($q_subject, 'subject');
							$q_name = $q_name = 'SELECT c.name FROM '.TBL_TICKETS.' t INNER JOIN '.TBL_CUSTOMERS.' c ';
							$q_name.= 'ON t.customer_id=c.id WHERE t.id='.$this->object_id;
							$this->object_name.= ' ('.$this->db_fetch_field ($q_name, 'name').')';
							break;
							
						case NOTIF_OBJ_CLASS_INTERNET:
							$q_subject = 'SELECT concat("#",c.id,"] ",c.name," (",m.remote_ip,")") as subject FROM ';
							$q_subject.= TBL_MONITORED_IPS.' m INNER JOIN '.TBL_CUSTOMERS.' c ON m.customer_id=c.id ';
							$q_subject.= 'WHERE m.id='.$this->object_id;
							$this->object_name.= $this->db_fetch_field ($q_subject, 'subject');
							break;
							
						case NOTIF_OBJ_CLASS_INTERNET_CONTRACT:
							$q_subject = 'SELECT concat(c.name," (",cust.name,")") as name FROM '.TBL_CUSTOMERS_INTERNET_CONTRACTS.' ic ';
							$q_subject.= 'INNER JOIN '.TBL_PROVIDERS_CONTRACTS.' c ON ic.contract_id=c.id ';
							$q_subject.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON ic.customer_id=cust.id ';
							$q_subject.= 'WHERE ic.id='.$this->object_id.' ';
							$this->object_name = $this->db_fetch_field ($q_subject, 'name');
							break;
						
						case NOTIF_OBJ_CLASS_SOFTWARE:
							$q_subject = 'SELECT concat(s.name," (",cust.name,")") as name FROM '.TBL_SOFTWARE_LICENSES.' l ';
							$q_subject.= 'INNER JOIN '.TBL_SOFTWARE.' s ON l.software_id=s.id ';
							$q_subject.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON l.customer_id=cust.id ';
							$q_subject.= 'WHERE l.id='.$this->object_id;
							$this->object_name = $this->db_fetch_field ($q_subject, 'name');
							break;
							
						case NOTIF_OBJ_CLASS_PERIPHERAL:
							$q_name = 'SELECT p.name, c.name as cust_name FROM '.TBL_PERIPHERALS.' p ';
							$q_name.= 'INNER JOIN '.TBL_CUSTOMERS.' c ON p.customer_id=c.id WHERE p.id='.$this->object_id;
							$d = $this->db_fetch_array ($q_name); $d = $d[0];
							$this->object_name.= '#'.$this->object_id.'] '.$d->name.' ('.$d->cust_name.')';
							break;
							
						case NOTIF_OBJ_CLASS_AD_PRINTER:
							$q_name = 'SELECT aw.canonical_name, c.name as cust_name FROM '.TBL_AD_PRINTERS_EXTRAS.' a ';
							$q_name.= 'INNER JOIN '.TBL_AD_PRINTERS_WARRANTIES.' aw on a.canonical_name=a.canonical_name ';
							$q_name.= 'INNER JOIN '.TBL_CUSTOMERS.' c ON aw.customer_id=c.id WHERE a.id='.$this->object_id;
							$d = $this->db_fetch_array ($q_name); $d = $d[0];
							$d->name = preg_replace ('/^.*\//', '', $d->canonical_name);
							$this->object_name.= '#'.$this->object_id.'] '.$d->name.' ('.$d->cust_name.')';
							break;
							
						default:
							$this->object_name = '# '.$this->object_id;
							break;
					}
					
					// Compose the URL based on object class and id, where needed
					if ($this->object_class==NOTIF_OBJ_CLASS_CUSTOMER and $this->event_code==NOTIF_CODE_UNMATCHED_DISCOVERIES)
					{
						$this->object_url = get_base_url().'/?cl=discovery&op=manage_discoveries&customer_id='.$this->object_id;
					}
					else
					{
						$this->object_url = get_base_url().'/'.$GLOBALS['NOTIF_OBJ_URLS'][$this->object_class].$this->object_id;
					}
					
				}
				
				
				// If there is an associated ticket, load user ID(s) which are working on it, if any
				if ($this->ticket_id)
				{
					$q = 'SELECT user_id, since FROM '.TBL_NOW_WORKING.' WHERE ticket_id='.$this->ticket_id.' ORDER BY since ';
					$this->now_working = $this->db_fetch_list ($q);
				}
			}
		}
	}
	
	
	/** Loads the linked object */
	function load_linked_object ()
	{
		if ($this->id and $this->object_class and $this->object_id)
		{
			switch ($this->object_class)
			{
				case NOTIF_OBJ_CLASS_COMPUTER:
					class_load ('Computer');
					class_load ('Customer');
					$this->linked_object = new Computer ($this->object_id);
					break;
				case NOTIF_OBJ_CLASS_KRIFS:
					class_load ('Ticket');
					$this->linked_object = new Ticket ($this->object_id);
					// Replace the codes with names
					$this->linked_object->priority = $GLOBALS ['TICKET_PRIORITIES'][$this->linked_object->priority];
					$this->linked_object->type = $GLOBALS ['TICKET_TYPES'][$this->linked_object->type];
					$this->linked_object->status = $GLOBALS ['TICKET_STATUSES'][$this->linked_object->status];
					$this->linked_object->source = $GLOBALS ['TICKET_SOURCES'][$this->linked_object->source];
					break;
				case NOTIF_OBJ_CLASS_INTERNET:
					class_load ('MonitoredIP');
					$this->linked_object = new MonitoredIP ($this->object_id);
					break;
				case NOTIF_OBJ_CLASS_INTERNET_CONTRACT:
					class_load ('CustomerInternetContract');
					$this->linked_object = new CustomerInternetContract ($this->object_id);
					break;
				case NOTIF_OBJ_CLASS_SOFTWARE:
					class_load ('SoftwareLicense');
					$this->linked_object = new SoftwareLicense ($this->object_id);
					break;
				case NOTIF_OBJ_CLASS_PERIPHERAL:
					class_load ('Peripheral');
					$this->linked_object = new Peripheral ($this->object_id);
					break;
				case NOTIF_OBJ_CLASS_AD_PRINTER:
					class_load ('AD Printer');
					$this->linked_object = AD_Printer::get_by_id ($this->object_id);
					break;
					
				default:
					$this->object_name = '# '.$this->object_id;
					break;
			}
		}
	}
	
	
	/** Loads, on request, the User objects for all attached notifications recipients */
	function load_users ()
	{
		if ($this->id)
		{
			foreach ($this->recipients as $user_id => $recip) $this->recipients[$user_id]->load_user ();
		}
	}
	
	/** Loads, on request, the ticket associated with this notification */
	function load_ticket ()
	{
		class_load('Ticket');
		if ($this->ticket_id) $this->ticket = new Ticket ($this->ticket_id);
	}
	
	/** Deletes a notification and all the attached notification recipients */
	function delete ()
	{
		if ($this->id)
		{
			if (is_array($this->recipients))
			{
				// Delete the recipients
				foreach ($this->recipients as $recip) $recip->delete ();
			}
			
			// Delete the notification itself
			parent::delete ();
		}
	}
	
	
	/** Returns the notification text for this notification */
	function get_text ()
	{
		$ret = '';
		if ($this->id)
		{
			if ($this->event_code)
			{
				$ret = $GLOBALS['NOTIF_CODES_TEXTS'][$this->event_code];
				if ($this->text and $ret != $this->text) $ret.= ' : '.$this->text;
			}
			else
			{
				$ret = $this->text;
			}
		}
		
		return $ret;
	}
	
	/** Returns true if the specified user is a recipient for this notification an he has not "read" the notification yet */
	function is_unread ($user_id)
	{
		$ret = false;
		if (isset($this->recipients[$user_id]))
		{
			$ret = $this->recipients[$user_id]->date_read <= 0;
		}
		return $ret;
	}
	
	/** Marks the notification as being "read" for a given user */
	function mark_read ($user_id)
	{
		if ($this->id and $user_id)
		{
			$q = 'UPDATE '.TBL_NOTIFICATIONS_RECIPIENTS.' SET date_read='.time().' ';
			$q.= 'WHERE notification_id='.$this->id.' AND user_id='.$user_id;
			$this->db_query ($q);
		}
	}
	
	/** [Class Method] Returns an array with all the unread notification IDs for a user */
	public static function get_unread_notifs_ids ($user_id)
	{
		$ret = array();
		if ($user_id)
		{
			$q = 'SELECT DISTINCT n.id FROM '.TBL_NOTIFICATIONS.' n INNER JOIN '.TBL_NOTIFICATIONS_RECIPIENTS.' r ';
			$q.= 'ON n.id=r.notification_id WHERE r.user_id='.$user_id.' AND r.date_read=0 ORDER BY raised DESC';
			$ret = DB::db_fetch_vector ($q);
		}
		return $ret;
	}
	
	
	/**
	* Returns a text suitable for being used as subject when creating a ticket from a notification. It
	* starts with the notification text and, if an object is linked to the notification, adds relevant information
	*/
	function get_subject_for_ticket ()
	{
		$ret = $this->text;
		if ($this->id and $this->object_id) $ret.= ': '.$this->object_name;
		return $ret;
	}
	
	/** Returns the URL for creating a ticket for this notification */
	function get_ticket_create_url ()
	{
		$ret = '';
		if ($this->id and $this->object_id)
		{
			$ticket_object_class = $GLOBALS['NOTIFS_TICKETS_OBJ_CLASS_TRANSLATE'][$this->object_class];
			$ret = './?cl=krifs&op=ticket_add&notification_id='.$this->id.'&subject='.urlencode($this->get_subject_for_ticket());
			if ($this->object_class==NOTIF_OBJ_CLASS_COMPUTER or $this->object_class==NOTIF_OBJ_CLASS_INTERNET or $this->object_class==NOTIF_OBJ_CLASS_INTERNET_CONTRACT or $this->object_class==NOTIF_OBJ_CLASS_PERIPHERAL)
			{
				$ret.= '&object_class='.$ticket_object_class.'&object_id='.$this->object_id;
			}
			elseif ($this->object_class==NOTIF_OBJ_CLASS_AD_PRINTER)
			{
				class_load ('AD_Printer');
				$ad_printer = AD_Printer::get_by_id ($this->object_id);
				$ret.= '&object_class='.$ticket_object_class.'&object_id='.$ad_printer->computer_id.'_'.$ad_printer->nrc;
			}
			elseif ($this->object_class==NOTIF_OBJ_CLASS_SOFTWARE)
			{
				$q = 'SELECT customer_id FROM '.TBL_SOFTWARE_LICENSES.' WHERE id='.$this->object_id;
				$ret.= '&customer_id='.$this->db_fetch_field ($q, 'customer_id');
			}
		}
		return $ret;
	}
	
	/** Checks if there is a need to send again e-mail for this notification to a specific user */
	function needs_emailing ($user_id)
	{
		$ret = false;

		// Include only valid existing notifications
		if ($this->id and $user_id and $this->object_class and !$this->suspend_email and isset($this->recipients[$user_id]))
		{
			// Exclude repetitions of notifications which don't need repetitions
			$recip = &$this->recipients[$user_id];
			if (!$recip->emailed_last or ($recip->emailed_last and !$this->no_repeat and !$recip->no_repeat))
			{
				// For repetitions, make sure they are within the repetition interval
				if ($recip->emailed_last<(time() - $GLOBALS['NOTIF_REPEAT_INTERVALS'][$this->level]) )
				{
					// For Kawacs alerts, check if the customer is not set to "suspend alert emails"
					if ($this->object_class == NOTIF_OBJ_CLASS_COMPUTER)
					{
						$q = 'SELECT cust.no_email_alerts FROM '.TBL_COMPUTERS.' c ';
						$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
						$q.= 'WHERE c.id='.$this->object_id;

						$ret = !($this->db_fetch_field ($q, 'no_email_alerts'));
					}
					else $ret = true;
				}
			}
		}

		return $ret;
	}
	
	function send_email_to_list($eml_list = array())
	{
		if($eml_list == null) return false;
		if($this->id and !$this->suspend_email)
		{
			if(count($eml_list)>0)
			{
                $is_reminder = 1;
				$parser = new BaseDisplay ();
				$parser->assign ('notification', $this);
				$parser->assign ('is_reminder', $is_reminder);
				$parser->assign ('ALERT_NAMES', $GLOBALS['ALERT_NAMES']);
				$parser->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
				$parser->assign ('NOTIF_CODES_TEXTS', $GLOBALS['NOTIF_CODES_TEXTS']);
				$parser->assign ('NOTIF_OBJ_URLS', $GLOBALS['NOTIF_OBJ_URLS']);
				
				$this->load_linked_object ();
				$users_list = User::get_users_list();
				$lang_ext = '';
				
				if (!$this->template)
				{
					// No template was specified, so use the default ones
					if ($this->emailed_last)
					{
						// This is a reminder for an existing notification
						$tpl = '_classes_templates/notification/msg_notification_reminder.tpl';
						$tpl_subject = '_classes_templates/notification/msg_notification_reminder_subject.tpl';
						$is_reminder = true;
					}
					else
					{
						// This is an e-mail about a new notifications
						$tpl = '_classes_templates/notification/msg_notification.tpl';
						$tpl_subject = '_classes_templates/notification/msg_notification.tpl';
						$is_reminder = false;
					}
				}
				else
				{
					// This notification has a different notification specified
					$tpl = $this->template;
					$tpl_subject = preg_replace ('/\.tpl/', '_subject.tpl', $tpl);
					$is_reminder = false;
				}
				
				//$parser->assign ('recipient', $recipient);
				$parser->assign ('notification', $this);
				$parser->assign ('base_url', get_base_url());
				$parser->assign ('users_list', $users_list);
				
				if ($this->object_class==NOTIF_OBJ_CLASS_COMPUTER and $this->object_event_code) // and $recipient->customer_id)
				{
					// This is a computer alert set for a customer, load the subject and message body 
					// from the alert definition
					class_load ('Alert');
					$alert = new Alert ($this->object_event_code);
					$parser->assign ('alert', $alert); 
				}

				$msg = $parser->fetch ($tpl.$lang_ext);
				$subject = $parser->fetch ($tpl_subject.$lang_ext);

				$headers = 'From: '.SENDER_NAME.' <'.SENDER_EMAIL.'>'."\n";
				$headers.= 'Date: '.date("D, j M Y G:i:s O")."\n";
				
				$random_hash = md5(date('r', time()));
				$mime_boundary = "----KEYOS alt----".$random_hash;
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
								
				$msg_snd = "--$mime_boundary\n";
				$msg_snd .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
				$msg_snd .= "Content-Transfer-Encoding: 7bit\n\n";
				$msg_snd .= $msg."\n";
				$msg_snd .= "--$mime_boundary\n";
				$msg_snd .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
				$msg_snd .= "Content-Transfer-Encoding: 7bit\n\n";
				$msg_snd .= "<html>\n<head><title></title></head>\n<body>\n".nl2br($msg)."\n</body>\n</html>\n";
				$msg_snd .= "--$mime_boundary--\n\n";
				
				// Send the e-mail message
				foreach($eml_list as $eml)
				{
					@mail ($eml, $subject, $msg_snd, $headers);								
				}
			}
		}
	}
	
	/** Sends the e-mail alerts for this notification */
	function send_email ($user_id = null)
	{
		class_load ('InfoRecipients');
		if ($this->id and !$this->suspend_email)
		{
			// Determine the list of recipients
			$recip_ids = array();
			if ($user_id) $recip_ids = array ($user_id);
			elseif ($this->object_class) $recip_ids = InfoRecipients::get_type_recipients ($this->object_class);
			
			// Expand the group members, if there are groups in the recipients list
			$expanded_recip_ids = array ();
			foreach ($recip_ids as $recip_id)
			{
				if (User::is_group ($recip_id))
				{
					$expanded_recip_ids = $expanded_recip_ids + Group::get_member_ids ($recip_id);
				}
				else
				{
					$expanded_recip_ids[] = $recip_id;
				}
			}
			$recip_ids = $expanded_recip_ids;
			
			
			if (count($recip_ids) > 0)
			{
                $is_reminder = 1;
				$parser = new BaseDisplay ();
				$parser->assign ('notification', $this);
				$parser->assign ('is_reminder', $is_reminder);
				$parser->assign ('ALERT_NAMES', $GLOBALS['ALERT_NAMES']);
				$parser->assign ('NOTIF_OBJ_CLASSES', $GLOBALS['NOTIF_OBJ_CLASSES']);
				$parser->assign ('NOTIF_CODES_TEXTS', $GLOBALS['NOTIF_CODES_TEXTS']);
				$parser->assign ('NOTIF_OBJ_URLS', $GLOBALS['NOTIF_OBJ_URLS']);
				
				$this->load_linked_object ();
				$users_list = User::get_users_list();
				 
				foreach ($recip_ids as $recip_id)
				{
					$recipient = new User ($recip_id);
					$lang_ext = $GLOBALS['LANGUAGE_CODES'][$recipient->language];
					$lang_ext = ((!$lang_ext or $lang_ext=='en') ? '' : '.'.$lang_ext);
					
					// Don't send e-mails to users which are not active or away
					if ($recipient->email and isset($this->recipients[$recip_id]) and $recipient->is_active_strict())
					{
						if (!$this->template and !$this->recipients[$recip_id]->template)
						{
							// No template was specified, so use the default ones
							if ($this->emailed_last)
							{
								// This is a reminder for an existing notification
								$tpl = '_classes_templates/notification/msg_notification_reminder.tpl';
								$tpl_subject = '_classes_templates/notification/msg_notification_reminder_subject.tpl';
								$is_reminder = true;
							}
							else
							{
								// This is an e-mail about a new notifications
								$tpl = '_classes_templates/notification/msg_notification.tpl';
								$tpl_subject = '_classes_templates/notification/msg_notification.tpl';
								$is_reminder = false;
							}
						}
						elseif ($this->recipients[$recip_id]->template)
						{
							// This notification recipient has a different notification specified
							$tpl = $this->recipients[$recip_id]->template;
							$tpl_subject = preg_replace ('/\.tpl/', '_subject.tpl', $tpl);
							$is_reminder = (!empty($this->recipients[$recip_id]->emailed_last));
						}
						else
						{
							// This notification has a different notification specified
							$tpl = $this->template;
							$tpl_subject = preg_replace ('/\.tpl/', '_subject.tpl', $tpl);
							$is_reminder = (!empty($this->recipients[$recip_id]->emailed_last));
						}
						
						$parser->assign ('recipient', $recipient);
						$parser->assign ('notification', $this);
						$parser->assign ('base_url', get_base_url());
						$parser->assign ('users_list', $users_list);
						
						if ($this->object_class==NOTIF_OBJ_CLASS_COMPUTER and $this->object_event_code) // and $recipient->customer_id)
						{
							// This is a computer alert set for a customer, load the subject and message body 
							// from the alert definition
							class_load ('Alert');
							$alert = new Alert ($this->object_event_code);
							$parser->assign ('alert', $alert); 
						}

						$msg = $parser->fetch ($tpl.$lang_ext);
						$subject = $parser->fetch ($tpl_subject.$lang_ext);

						$headers = 'From: '.SENDER_NAME.' <'.SENDER_EMAIL.'>'."\n";
						$headers.= 'Date: '.date("D, j M Y G:i:s O")."\n";
						
						$random_hash = md5(date('r', time()));
	    					$mime_boundary = "----KEYOS alt----".$random_hash;
						$headers .= "MIME-Version: 1.0\n";
						$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
								
						$msg_snd = "--$mime_boundary\n";
						$msg_snd .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
						$msg_snd .= "Content-Transfer-Encoding: 7bit\n\n";
						$msg_snd .= $msg."\n";
						$msg_snd .= "--$mime_boundary\n";
						$msg_snd .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
						$msg_snd .= "Content-Transfer-Encoding: 7bit\n\n";
						$msg_snd .= "<html>\n<head><title></title></head>\n<body>\n".nl2br($msg)."\n</body>\n</html>\n";
						$msg_snd .= "--$mime_boundary--\n\n";
										
						
						// Send the e-mail message
						@mail ($recipient->email, $subject, $msg_snd, $headers);
						if (isset($this->recipients[$recip_id]))
						{
							$this->recipients[$recip_id]->emailed_last = time ();
							$this->recipients[$recip_id]->save_data ();
						}
						
						// Log the e-mail message that has been sent - only for customer users
						if ($recipient->is_customer_user ())
						{
							$log_table = TBL_MESSAGES_LOG.'_'.date ('Y_m');
							$q = 'INSERT INTO '.$log_table.'(notification_id, date_sent, user_id, customer_id, email, subject, msg_body) VALUES (';
							$q.= $this->id.', '.time().', '.$recipient->id.', '.$recipient->customer_id.', ';
							$q.= '"'.db::db_escape($recipient->email).'", "'.db::db_escape(trim($subject)).'", ';
							$q.= '"'.db::db_escape($msg).'")';
							$this->db_query ($q);
						}
					}
				}
			}
		}
	}
	
	
	/**
	* [Class Method] This is only a wrapper for raise_notification(), to allow passing the parameters
	* in an associative array instead of passing them directly in the function call.
	* @param	array		$params			Associative array with the parameters to pass to raise_notification(), 
	*							where the keys are the names of the raise_notification() parameters
	* @return	int					The ID of the notification - either the newly created one or the one which
	*							already existed for this event.
	*/
	public static function raise_notification_array ($params = array())
	{
		$ret = null;
		
		if (!isset ($params['item_id'])) $params['item_id'] = 0;
		if (!isset ($params['user_ids'])) $params['user_ids'] = array();
		if (!isset ($params['text'])) $params['text'] = '';
		if (!isset ($params['no_increment'])) $params['no_increment'] = false;
		if (!isset ($params['template'])) $params['template'] = '';
		if (!isset ($params['expires'])) $params['expires'] = 0;
		if (!isset ($params['no_repeat'])) $params['no_repeat'] = false;
		
		$ret = Notification::raise_notification (
			$params['event_code'], $params['level'], $params['object_class'], $params['object_id'], $params['object_event_code'], 
			$params['item_id'], $params['user_ids'], $params['text'], $params['no_increment'], $params['template'],
			$params['expires'], $params['no_repeat']
		);
		
		return $ret;
	}
	
	/**
	* [Class Method] Creates a new notification with the specified details
	* @param	integer		$event_code		The generic event code - see $GLOBALS['NOTIF_CODES_TEXTS']
	* @param	integer		$level			The severity level - see $GLOBALS['ALERT_NAMES']
	* @param	integer		$object_class		The type of object associated with the notification - see 
	*							$GLOBALS['NOTIF_OBJ_CLASSES'] (if any)
	* @param	integer		$object_id		The ID of the associated object (if any)
	* @param	integer		$object_event_code	The object-specific event code (if it has associated object)
	* @param	integer		$item_id		The object item which genereated the notification
	* @param	integer/mixed	$user_ids		Array with the user IDs which should receive this notification.
	*							can be a single user ID or an array of IDs.
	* @param	string		$text			(Optional) Additional text information about the notification
	* @param	boolean		$no_increment		(Optional) If False and the notification already exists, the 
	*							repeat counter is incremented. If True, it's not incremented.
	* @param	string		$template		(Optional) A template to use when sending e-mails for this 
	*							notification. The parameter must include the full path (relative
	*							to the templates directory). Besides the template file, the directory 
	*							must also contain a "subject" template file, e.g. if the template
	*							is my_message.tpl, the subject file should be my_message_subject.tpl.
	*							The '.tpl' extension is mandatory.
	* @param	time		$expires		(Optional) An expiration time at which the notification is deleted
	* @param	boolean		$no_repeat		(Optional) If True, e-mails about notification will be sent only once, at its creation
	* @return	int					The ID of the notification - either the newly created one or the one which
	*							already existed for this event.
	*/
	public static function raise_notification ($event_code, $level, $object_class, $object_id, $object_event_code, $item_id=0, $user_ids=array(), $text='', $no_increment = false, $template = '', $expires = 0, $no_repeat = false)
	{
		// Check if there isn't already a notification for the same event
		$unique_fields = array ('event_code', 'object_class', 'object_id', 'object_event_code', 'item_id');
		$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE ';
		foreach ($unique_fields as $field) $q.= $field.'='.$$field.' AND ';
		$q = preg_replace ('/AND $/', '', $q);
		$existing_id = DB::db_fetch_field ($q, 'id');
		
		$notif_time = time ();
		if ($existing_id)
		{
			// A notification for this event has already been raised, so only update it
			// Update to the database directly, for speed.
			$q = 'UPDATE '.TBL_NOTIFICATIONS.' SET ';
			$q.= 'level='.$level.', text="'.db::db_escape($text).'", raised_last='.$notif_time.' ';
			if (!$no_increment) $q.= ', raised_count=raised_count+1 ';
			$q.= 'WHERE id='.$existing_id;
			DB::db_query ($q);
			$ret = $existing_id;
		}
		else
		{
			// There is no notification for this event, one needs to be created
			$notification = new Notification();
			$notification->event_code = $event_code;
			$notification->level = $level;
			$notification->raised = $notif_time;
			$notification->raised_last = $notif_time;
			$notification->object_class = $object_class;
			$notification->object_id = $object_id;
			$notification->object_event_code = $object_event_code;
			$notification->item_id = $item_id;
			$notification->text = $text;
			$notification->template = $template;
			$notification->expires = $expires;
			$notification->no_repeat = $no_repeat;
			$notification->save_data();
			
			// Make sure that this notification ID doesn't exist in the log table, so the notification ID
			// can be primary key in the logs as well. If the ID already exists in the log, make a new
			// ID for this notification.
			$log_table = TBL_NOTIFICATIONS.'_'.date ('Y_m', $notif_time);
			$existing_id = DB::db_fetch_field ('SELECT id FROM '.$log_table.' WHERE id='.$notification->id, 'id');
			if ($existing_id)
			{
				$new_id = DB::db_fetch_field('SELECT max(id)+1 as id FROM '.$log_table, 'id');
				DB::db_query ('UPDATE '.TBL_NOTIFICATIONS.' SET id='.$new_id.' WHERE id='.$notification->id);
				$notification->id = $new_id;
			}
			
			$ret = $notification->id;
			
			// Create the notification recipients
			// XXXXXX TO DO: If there are no recipients, use some default recipient
			// XXXXXX TO DO: Check and expand group members: if (User::is_group ($recip_id))
			// Make sure there are unique IDs
			$user_ids = array_values(array_unique ($user_ids));
			for ($i=0; $i<count($user_ids); $i++)
			{
				$notification_recipient = new NotificationRecipient ();
				$notification_recipient->notification_id = $notification->id;
				$notification_recipient->user_id = $user_ids[$i];
				$notification_recipient->save_data ();
				$notification->recipients[$user_ids[$i]] = $notification_recipient;
			}
			
			// Also create the notification in the log
			$q = 'INSERT INTO '.$log_table.' (id, event_code, level, raised, ended, object_class, object_id, object_event_code, item_id, text) VALUES (';
			$q.= $notification->id.', '.$event_code.', '.$level.', '.$notif_time.', 0, ';
			$q.= $object_class.', '.$object_id.', '.$object_event_code.', '.$item_id.', "'.db::db_escape($text).'") ';
			DB::db_query ($q);
		}
		
		return $ret;
	}
	
	
	/** Set user-specific parameters for a notification
	* @param	int		$user_id	The user ID of the recipient for which the details are set
	* @param	string 		$text		The notification text for this user
	* @param	bool		$no_repeat	The "no_repeat" setting for this user
	* @param	string		$template	The template to use for emailing this user
	*/
	function set_notification_recipient_text ($user_id, $text, $no_repeat, $template)
	{
		if (isset($this) and $this->id and isset($this->recipients[$user_id]))
		{
			$this->recipients[$user_id]->text = $text;
			$this->recipients[$user_id]->no_repeat = $no_repeat;
			$this->recipients[$user_id]->template = $template;
			$this->recipients[$user_id]->save_data ();
		}
	}
	
	
	/** Removes a user "header" for this notification. If that was the last recipient, delete the notification alltogether */
	function remove_recipient ($user_id)
	{
		if (isset($this) and $this->id and isset($this->recipients[$user_id]))
		{
			$this->recipients[$user_id]->delete ();
			unset ($this->recipients[$user_id]);
			
			// Delete the notification if this was the last recipient
			if (count($this->recipients) == 0) $this->delete ();
		}
	}
	
	/** [Class Method] Comparator to be used on an array of notifications that needs to be sorted by creation date */
	public static function cmp_notifs_raised ($a, $b)
	{
		if ($a->raised == $b->raised) return 0;
		return (($a->raised > $b->raised) ? 1 : -1);
	}
	
	
	/** [Class Method] Returns existing notifications according to the specified criteria */
	public static function get_notifications ($filter = array(), $current_user = null)
	{
		$ret = array();
		
		if (!$filter['order_dir']) $filter['order_dir'] = 'DESC';
		if ($filter['order_by'] == 'raised') $filter['order_by'] = 'raised '.$filter['order_dir'].', level DESC';
		else $filter['order_by'] = 'level DESC , raised_last '.$filter['order_dir'];
		
		$q = 'SELECT n.id FROM '.TBL_NOTIFICATIONS.' n ';

        $current_user = $GLOBALS['CURRENT_USER'];
		if($current_user!=null)
		{
			if($current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
			{
				
				$cc = $current_user->get_assigned_customers_list();
				$qrx = 'cust.id in (';
				$i=0;
				foreach($cc as $k=>$name)
				{
					if($i!=count($cc)-1) $qrx.=$k.", ";
					else $qrx.=$k;
				}
				$qrx = trim (preg_replace ('/,\s*$/', '', $qrx));
				$qrx.=") AND ";
				$ff = false;
				
				if($filter['object_class']  and $filter['object_class']==NOTIF_OBJ_CLASS_COMPUTER)
				{
					$q.=" inner join ".TBL_COMPUTERS." c on n.object_id=c.id inner join ".TBL_CUSTOMERS." cust on cust.id=c.customer_id ";
					$q.=" where ";
					$q.=$qrx;	
					$ff = true;
				}
				if($filter['object_class']  and $filter['object_class']==NOTIF_OBJ_CLASS_KRIFS)
				{
					$q.=" inner join ".TBL_TICKETS." t on n.object_id=t.id inner join ".TBL_CUSTOMERS." cust on cust.id=t.customer_id ";
					$q.=" where ";
					$q.=$qrx;	
					$ff = true;
				}
				if($filter['object_class']  and $filter['object_class']==NOTIF_OBJ_CLASS_INTERNET_CONTRACT)
				{
					$q.=" inner join ".TBL_CUSTOMERS_INTERNET_CONTRACTS." cic on n.object_id=cic.id inner join ".TBL_CUSTOMERS." cust on cust.id=cic.customer_id ";
					$q.=" where ";
					$q.=$qrx;	
					$ff = true;
				}
				if($filter['object_class']  and $filter['object_class']==NOTIF_OBJ_CLASS_INTERNET)
				{
					$q.=" inner join ".TBL_MONITORED_IPS." mip on n.object_id=mip.id inner join ".TBL_CUSTOMERS." cust on cust.id=mip.customer_id ";
					$q.=" where ";
					$q.=$qrx;	
					$ff = true;
				}
				if($filter['object_class']  and $filter['object_class']==NOTIF_OBJ_CLASS_SOFTWARE)
				{
					$q.=" inner join ".TBL_SOFTWARE_LICENSES." sl on n.object_id=sl.id inner join ".TBL_CUSTOMERS." cust on cust.id=sl.customer_id ";
					$q.=" where ";
					$q.=$qrx;	
					$ff = true;
				}
				if($filter['object_class']  and $filter['object_class']==NOTIF_OBJ_CLASS_PERIPHERAL)
				{
					$q.=" inner join ".TBL_PERIPHERALS." p on n.object_id=p.id inner join ".TBL_CUSTOMERS." cust on cust.id=p.customer_id ";
					$q.=" where ";
					$q.=$qrx;	
					$ff = true;
				}
				
				//until I implement for all classes like above
				if(!$ff)
				{
					$q.=" where ";
				}
			}
			else 
			{
				$q.=' WHERE ';
			}
		}
		else 
		{
			$q.=' WHERE ';
		}
		//debug($current_user);
		if ($filter['object_id']) $q.= 'object_id='.$filter['object_id'].' AND ';
		if ($filter['object_class']) $q.= 'object_class='.$filter['object_class'].' AND ';
		if ($filter['object_event_code']) $q.= 'object_event_code='.$filter['object_event_code'].' AND ';
		if ($filter['event_code']) $q.= 'event_code='.$filter['event_code'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		$unique_fields = array ('event_code', 'object_class', 'object_id', 'object_event_code', 'item_id');
		
		if ($filter['object_unique'])
		{
			// Returns the notifications for an object, one per object even if there are 
			// multiple notifications of the same time assigned to different users
			$q.= 'GROUP BY event_code, object_class, object_id, object_event_code, item_id ';
		}
		
		$q.= 'ORDER BY '.$filter['order_by'].' ';
		$ids = db::db_fetch_array ($q);
	
		foreach ($ids as $id) $ret[] = new Notification($id->id);
		return $ret;
	}
	
	
	/** [Class Method] Returns a list of notifications according to some criteria
	* @param	array		$filter		Associative array with filtering criteria. Can contain:
	*						- object_class: Return only notifications for the specified class of objects
	*						- object_id: Return only notifications for the specified object
	*						- object_event_code: Return only notifications with the specified object event code
	*						- event_code: Return only notifications with the specified event code
	* @return	array				Associative array, the keys being notification IDs and the values being
	*						the ID of the object to which the notification is linked
	*/
	public static function get_notifications_list ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id, object_id FROM '.TBL_NOTIFICATIONS.' WHERE ';
		if ($filter['object_class']) $q.= 'object_class='.$filter['object_class'].' AND ';
		if ($filter['object_id']) $q.= 'object_id='.$filter['object_id'].' AND ';
		if ($filter['object_event_code']) $q.= 'object_event_code='.$filter['object_event_code'].' AND ';
		if ($filter['event_code']) $q.= 'event_code='.$filter['event_code'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY level DESC, raised_last DESC ';
		$ret = db::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns the number of notifications for a given user
	* @param	int	$user_id		The ID of the user
	* @return	array				Associative array with the keys 'new' - the number of
	*						new notifications (notifications for which e-mails have not
	*						been sent) and 'total' - the total number of notifications
	*/
	public static function get_user_notifications_count ($user_id = null)
	{
		$ret = array ('new'=>0, 'total'=>0);
		
		if ($user_id)
		{
			$total = 0;
			$new = 0;

			$q = 'SELECT count(distinct id) AS cnt FROM '.TBL_NOTIFICATIONS.' n LEFT JOIN '.TBL_NOTIFICATIONS_GENERAL_RECIPIENTS.' r ';
			$q.= 'ON n.object_class = r.notif_obj_class ';
			$q.= 'LEFT JOIN '.TBL_USERS_GROUPS.' g ON n.user_id = g.group_id ';
			$q.= 'WHERE ';
			$q.= '(n.user_id='.$user_id.' OR (n.user_id=0 AND r.user_id='.$user_id.') OR (g.group_id IS NOT NULL and g.user_id='.$user_id.')) ';
			
			$ret['total'] = db::db_fetch_field ($q, 'cnt');
			
			$q.= 'AND emailed_last=0 AND suspend_email=0 ';
			$ret['new'] = db::db_fetch_field ($q, 'cnt');
		}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns notification statistics for a user
	*/
	public static function get_user_notifs_stat ($user_id = null)
	{
		$ret = array ();
		if ($user_id and is_numeric($user_id))
		{
			$q = 'SELECT n.level, count(distinct n.id) AS cnt FROM '.TBL_NOTIFICATIONS.' n ';
			$q.= 'INNER JOIN '.TBL_NOTIFICATIONS_RECIPIENTS.' nr ';
			$q.= 'ON n.id=nr.notification_id AND nr.user_id='.$user_id.' GROUP BY n.level';
			$ret = db::db_fetch_list ($q);
		}
		return $ret;
	}
	
	
	/**
	* Marks this notification that a ticket has been created from it. 
	* NOTE: It will replace any other associated tickets that might have been before.
	* @param	int	$ticket_id			The ID of the ticket associated with this notification
	*/
	function mark_ticket_created ($ticket_id)
	{
		if ($this->id and $ticket_id)
		{
			$this->ticket_id = $ticket_id;
			$this->suspend_email = 1;
			$this->save_data ();
		
			// Get all notifications of the same type for all users
			//$notifs = $this->get_related_notifications ($this->id);
		}
	}
	
	/**
	* [Class Method] Will remove from the notifications table all references to tickets
	* that don't exist anymore
	*/
	public static function clear_missing_tickets ()
	{
		$q = 'SELECT n.id FROM '.TBL_NOTIFICATIONS.' n LEFT OUTER JOIN '.TBL_TICKETS.' t ';
		$q.= 'ON n.ticket_id=t.id ';
		$q.= 'WHERE n.ticket_id<>0 and t.id IS NULL ';
		$ids = DB::db_fetch_vector ($q);
		
		foreach ($ids as $id)
		{
			$q = 'UPDATE '.TBL_NOTIFICATIONS.' SET ticket_id=0, suspend_email=0 WHERE id='.$id;
			DB::db_query ($q);
		}
	}
	
	
	
	/**
	* [Class Method] Given a notification ID, it will retur all notifications, for all users
	* that relate to the same object and event.
	* @param	int	$notification_id		The ID of the notification for which to retrieve the related ones
	* @return	array(Notification)			Array with the related notification objects, including the notification
	*							whose ID was passed as parameter.
	*/
	// XXXXXXXX Check everything related to this, normally it shouldn't be required anymore
	public static function get_related_notifications ($notification_id)
	{
		$ret = array ();
		if ($notification_id)
		{
			$notif = new Notification ($notification_id);
			$groups = Notification::get_notifications_grouped (array(
				'object_class' => $notif->object_class,
				'object_id' => $notif->object_id,
				'object_event_code' => $notif->object_event_code,
				'event_code' => $notif->event_code,
				'show_ignored' => true
			));
			
			foreach ($groups as $idx => $notifs_group)
			{
				foreach ($notifs_group['notifications'] as $n) $ret[] = $n;
			}
		}
		return $ret;
	}
	
		
	/** 
	* [Class Method] Returns a list of existing notifications according to the specified criteria,
	* grouped by event type.
	* @param	array	$filter		Associative array with the filtering conditions
	* @return	array			Array with the notifications grouped by event type.
	*					The array elements are generic objects representing
	*					the notification groups
	*					
	*/
	public static function get_notifications_grouped ($filter = array() )
	{
		$ret = array();
		
		if ($filter['user_id'])
		{
			$q = 'SELECT distinct n.id FROM '.TBL_NOTIFICATIONS.' n ';
			$q.= 'INNER JOIN '.TBL_NOTIFICATIONS_RECIPIENTS.' nr ON n.id=nr.notification_id WHERE ';
		}
		else
		{
			$q = 'SELECT distinct id FROM '.TBL_NOTIFICATIONS.' n WHERE ';
		}
		
		if ($filter['object_class']) $q.= 'n.object_class='.$filter['object_class'].' AND ';
		if ($filter['object_id']) $q.= 'n.object_id='.$filter['object_id'].' AND ';
		if (is_numeric($filter['object_event_code'])) $q.= 'n.object_event_code='.$filter['object_event_code'].' AND ';
		if ($filter['event_code']) $q.= 'n.event_code='.$filter['event_code'].' AND ';
		
		if (!$filter['show_ignored']) $q.= 'n.suspend_email = 0 AND ';
		if ($filter['user_id']) $q.= 'nr.user_id='.$filter['user_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY level DESC, ';
		$q.= 'object_class, object_event_code, event_code, ';
		$q.= 'raised DESC, object_id ';
		$ids = db::db_fetch_array ($q);
		
		$c_object_class = null;
		$c_object_event_code = null;
		$c_event_code = null;
		$counter = 0;
		
		foreach ($ids as $id)
		{
			$notif = new Notification ($id->id);
			
			if ($notif->object_class!=$c_object_class or $notif->object_event_code!=$c_object_event_code or $notif->event_code!=$c_event_code)
			{
				$c_object_class = $notif->object_class;
				$c_object_event_code = $notif->object_event_code;
				$c_event_code = $notif->event_code;
				$counter++;
				
				$ret[$counter] = array (
					'object_class' => $notif->object_class,
					'object_event_code' => $notif->object_event_code,
					'event_code' => $notif->event_code,
					'level' => $notif->level,
					'text' => ($notif->text ? $notif->text : $GLOBALS['NOTIF_CODES_TEXTS'][$notif->event_code]),
					'notifications' => array()
				);
			}
			
			$ret[$counter]['notifications'][] = $notif;
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns a brief list of notifications related to computers
	* @return	array								Associative array, the keys being computer IDs and the
	*										values being array of generic objects with notifications info
	*										for each computer. The generic object have the following fields:
	*										id, object_id, event_code, level, raised, text
	*/
	public static function get_computers_notifs_brief ($swic = -1)
	{
		$ret = array ();
		$q = 'SELECT id, object_id, event_code, level, raised, text, ticket_id FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_COMPUTER.' ';
		if($swic == 0 or $swic == 1)
		{
			//get all that do not show up in console
			$q.=" AND show_in_console=".$swic." ";
			
		}	
		$q.= 'ORDER BY level DESC, raised DESC ';
		$data = DB::db_fetch_array ($q);
		foreach ($data as $d)
		{
			if ($d->event_code)
			{
				$txt = $GLOBALS['NOTIF_CODES_TEXTS'][$d->event_code];
				if ($d->text and $txt != $d->text) $txt.= ' : '.$d->text;
				$d->text = $txt;
			}
			$ret[$d->object_id][] = $d;
		}
		return $ret;
	}

	
	/** [Class Method] Returns the months for which there are logged notifications */
	public static function get_log_months ()
	{
		$ret = array ();
		$q = 'SHOW TABLES like "'.TBL_NOTIFICATIONS.'_2%" ';
		$months = DB::db_fetch_vector ($q);
		$this_month = date('Y_m');
		foreach ($months as $month)
		{
			$month = preg_replace ('/^'.TBL_NOTIFICATIONS.'_/', '', $month);
			// Make sure we don't return months from the future
			if ($month <= $this_month) $ret[$month] = $month;
		}
		arsort($ret);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Retrieves log information about notifications
	* @param	array		$filter			Array with filtering criteria. Can contain:
	*							- month_start, month_end: The log interval, in the form YYYY_DD. If 
	*							  not specified, the current month is used.
	*							- customer_id: An ID of a customer.
	*							- computer_id: A computer ID. Note that if you specify both a computer_id
	*							  and a customer_id, then customer_id is ignored.
	*							- show: 1=Group results by event; 2=Group results by computer; 3=No grouping
	*/
	public static function get_notifications_log ($filter = array ())
	{
		class_load ('Computer');
		class_load ('Ticket');
		$ret = array ();
		
		if (!$filter['month_start']) $filter['month_start'] = date ('Y_m');
		if (!$filter['month_end']) $filter['month_end'] = date ('Y_m');
		if (!$filter['show']) $filter['show'] = 1;
		
		if ($filter['show'] == 3)
		{
			if (!$filter['order_dir']) $filter['order_dir'] = 'DESC';
			$filter['order_by'] = 'l.raised '.$filter['order_dir'].', l.level DESC';
		}
		
		// Make sure that 'computer_id' and 'customer_id' are not both specified
		if ($filter['computer_id']) unset ($filter['customer_id']);

		// Set the list of months in the interval
		$months = array ();
		$month = $filter['month_start'];
        $runaway_check = 0;
		while ($month <= $filter['month_end'] and ($runaway_check++ < 100))
		{
			$months[] = $month;
			list ($year, $month) = preg_split('/_/', $month, 2);
			if ($month++ > 11) {$year++; $month=1;}
			$month = $year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT);
		}
		rsort ($months);
		
		foreach ($months as $month)
		{
			$log_table = TBL_NOTIFICATIONS.'_'.$month;
			$q = 'SELECT l.*, count(*) AS cnt FROM '.$log_table.' l ';
			if ($filter['customer_id'])
			{
				$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS.' c ';
				$q.= 'ON l.object_class='.NOTIF_OBJ_CLASS_COMPUTER.' AND l.object_id=c.id AND c.customer_id='.$filter['customer_id'].' ';
				$q.= 'LEFT OUTER JOIN '.TBL_TICKETS.' t ';
				$q.= 'ON l.object_class='.NOTIF_OBJ_CLASS_KRIFS.' AND l.object_id=t.id AND t.customer_id='.$filter['customer_id'].' ';
				
				$q.= 'LEFT OUTER JOIN '.TBL_MONITORED_IPS.' mi ';
				$q.= 'ON l.object_class='.NOTIF_OBJ_CLASS_INTERNET.' AND l.object_id=mi.id AND mi.customer_id='.$filter['customer_id'].' ';
				
				$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS_INTERNET_CONTRACTS.' ic ';
				$q.= 'ON l.object_class='.NOTIF_OBJ_CLASS_INTERNET_CONTRACT.' AND l.object_id=ic.id AND ic.customer_id='.$filter['customer_id'].' ';
				
				$q.= 'LEFT OUTER JOIN '.TBL_SOFTWARE_LICENSES.' lics ';
				$q.= 'ON l.object_class='.NOTIF_OBJ_CLASS_SOFTWARE.' AND l.object_id=lics.id AND lics.customer_id='.$filter['customer_id'].' ';
			}
			
			$q.= 'WHERE ';
			
			if ($filter['show']==2 and !$filter['computer_id']) $q.= 'l.object_class='.NOTIF_OBJ_CLASS_COMPUTER.' AND ';
			if ($filter['object_class']) $q.= 'l.object_class='.$filter['object_class'].' AND ';
			if ($filter['computer_id']) $q.= '(l.object_class='.NOTIF_OBJ_CLASS_COMPUTER.' and l.object_id='.$filter['computer_id'].') AND ';
			elseif ($filter['customer_id']) $q.= '(c.id IS NOT NULL OR t.id IS NOT NULL OR mi.id IS NOT NULL OR ic.id IS NOT NULL OR lics.id IS NOT NULL) AND ';
			
			
			$q = preg_replace ('/AND\s*$/', ' ', $q);
			$q = preg_replace ('/WHERE\s*$/', ' ', $q);
			
			$q.= 'GROUP BY l.event_code, l.raised, l.object_class, l.object_id, l.object_event_code, l.item_id ';
			$q.= 'ORDER BY ';
			if ($filter['show'] == 3)
			{
				$q.= $filter['order_by'].' ';
			}
			else
			{
				if ($filter['show'] == 1) $q.= 'l.level DESC, ';
				$q.= 'l.raised desc, l.object_class, l.object_id, l.raised, l.object_id ';
			}
			
			$ret_month = DB::db_fetch_array ($q);
			
			// Calculate the durations (in seconds) and fix texts
			for ($i=0; $i<count($ret_month); $i++)
			{
				if ($ret_month[$i]->ended) $ret_month[$i]->duration = $ret_month[$i]->ended - $ret_month[$i]->raised;
				else $ret_month[$i]->duration = time() - $ret_month[$i]->raised;
				
				$ret_month[$i]->text = ($ret_month[$i]->text ? $ret_month[$i]->text : $GLOBALS['NOTIF_CODES_TEXTS'][$ret_month[$i]->event_code]);
			}
			
			$ret = array_merge ($ret, $ret_month);
		}
		
		// Now parse the raw results
		if ($filter['show'] == 1)
		{
			// Group notifications by event
			$parsed_events = array ();
			
			for ($i=0; $i<count($ret); $i++)
			{
				$parsed = false;
				for ($idx = 0; $idx<count($parsed_events) and !$parsed; $idx++)
				{
					$parsed = ($parsed_events[$idx]->event_code==$ret[$i]->event_code and $parsed_events[$idx]->object_event_code==$ret[$i]->object_event_code and $parsed_events[$idx]->item_id==$ret[$i]->item_id);
				}
				if ($parsed) $idx--;

                $parsed_events[$idx] = new StdClass;
				$parsed_events[$idx]->count++;
				$parsed_events[$idx]->event_code = $ret[$i]->event_code;
				$parsed_events[$idx]->object_event_code = $ret[$i]->object_event_code;
				$parsed_events[$idx]->item_id = $ret[$i]->item_id;
				$parsed_events[$idx]->object_class = $ret[$i]->object_class;
				$parsed_events[$idx]->level = $ret[$i]->level;
				$parsed_events[$idx]->text = $ret[$i]->text;
				$parsed_events[$idx]->notifications[] = $ret[$i];
			}
			$ret = $parsed_events;
		}
		elseif ($filter['show'] == 2)
		{
			// Group notifications by computers
			$parsed_computers = array ();
			// Make sure the computers are sorted by name
			$computers_list = Computer::get_computers_list (array ('customer_id' => $filter['customer_id']));
			foreach ($computers_list as $id => $name) $parsed_computers[$id] = false;
			
			for ($i=0; $i<count($ret); $i++)
			{
				$id = $ret[$i]->object_id;
				
				$parsed_computers[$id]->count++;
				$parsed_computers[$id]->object_id = $ret[$i]->object_id;
				$parsed_computers[$id]->notifications[] = $ret[$i];
			}
			
			// Eliminate computers that don't have notifications
			foreach ($computers_list as $id => $name) if (!$parsed_computers[$id]) unset ($parsed_computers[$id]);
			
			$ret = $parsed_computers;
		}
		
		return $ret;
	}
	
	
	
	/**
	* [Class Method] Records notifications and their modifications or deletions to the notifications logs
	*/
	public static function log_notifications ()
	{
		// Force taking into account only notifications starting April 2005
		$first_time = mktime (0, 0, 0, 4, 1, 2005);
	
		// Fetch the list of existing logs tables
		$q = 'SHOW TABLES like "'.TBL_NOTIFICATIONS.'_2%" ';
		$logs = DB::db_fetch_vector ($q);
		
		// Make the list of notification log tables which should be used
		// This is determined based on the 'raised' attribute of existing notifications
		$q = 'SELECT distinct year(from_unixtime(raised)) as year, month(from_unixtime(raised)) as month, ';
		$q.= 'min(raised) as raised_min, max(raised) as raised_max ';
		$q.= 'FROM '.TBL_NOTIFICATIONS.' ';
		$q.= 'WHERE raised>='.$first_time.' GROUP BY 1,2 order by 1, 2 ';
		$log_months = DB::db_fetch_array ($q);
		
		$processed_logs = array ();
		foreach ($log_months as $log_month)
		{
			$log_table = TBL_NOTIFICATIONS.'_'.$log_month->year.'_'.str_pad ($log_month->month, 2, '0', STR_PAD_LEFT);
			$processed_logs[] = $log_table;
			
			// Add to the log all new notifications that don't exist in the log yet
			// This action normally shouldn't find new notifications, since raise_notification 
			// automatically logs every new notification
			$q = 'SELECT n.* FROM '.TBL_NOTIFICATIONS.' n LEFT OUTER JOIN '.$log_table.' l ';
			$q.= 'ON n.id=l.id ';
			$q.= 'WHERE l.id IS NULL AND (n.raised>='.$log_month->raised_min.' AND n.raised<='.$log_month->raised_max.') ';
			
			$new_notifs = DB::db_fetch_array($q);
			if (count($new_notifs) > 0)
			{
				$copy_fields = array ('id', 'event_code', 'level', 'raised', 'ended', 'object_class', 'object_id', 'object_event_code', 'item_id', 'text', 'emailed_last', 'ticket_id');
				
				$q_ins = 'INSERT INTO '.$log_table.' (';
				foreach ($copy_fields as $field) $q_ins.= $field.', ';
				$q_ins = preg_replace ('/\,\s*$/', '', $q_ins).') VALUES ';
				
				foreach ($new_notifs as $new_notif)
				{
					$q_ins.=' (';
					foreach ($copy_fields as $field) $q_ins.= '"'.db::db_escape($new_notif->$field).'", ';
					$q_ins = preg_replace ('/\,\s*$/', '', $q_ins).'), ';
				}
				$q_ins = preg_replace ('/\,\s*$/', '', $q_ins);
			
				DB::db_query ($q_ins);
			}
			
			// Now check for notifications that have been removed, to mark them as ended in the notifications log
			$q = 'SELECT distinct l.id, l.raised FROM '.$log_table.' l LEFT OUTER JOIN '.TBL_NOTIFICATIONS.' n ';
			$q.= 'ON l.id=n.id AND l.raised=n.raised ';
			$q.= 'WHERE l.ended=0 AND n.id IS NULL ';
			
			$ended_notifs = DB::db_fetch_array ($q);
			$closed_time = time ();
			foreach ($ended_notifs as $ended_notif)
			{
				$q_upd = 'UPDATE '.$log_table.' SET ended='.$closed_time.' ';
				$q_upd.= 'WHERE id='.$ended_notif->id.' AND raised='.$ended_notif->raised;
				DB::db_query ($q_upd);
			}
			
			// Now, for notifications that have not been closed yet, check if there are any changes
			$q = 'SELECT n.id, n.raised, n.emailed_last, n.text, n.ticket_id FROM '.TBL_NOTIFICATIONS.' n INNER JOIN '.$log_table.' l ';
			$q.= 'ON n.id=l.id AND n.raised=l.raised ';
			$q.= 'WHERE l.ended=0 AND (n.emailed_last<>l.emailed_last OR n.text<>l.text OR n.ticket_id<>l.ticket_id) ';
			$changed_notifs = DB::db_fetch_array ($q);
			foreach ($changed_notifs as $changed_notif)
			{
				$q_upd = 'UPDATE '.$log_table.' SET emailed_last='.$changed_notif->emailed_last.', ';
				$q_upd.= 'text="'.$changed_notif->text.'", ticket_id='.$changed_notif->ticket_id.' ';
				$q_upd.= 'WHERE id='.$changed_notif->id.' AND raised='.$changed_notif->raised;
				DB::db_query ($q_upd); 
			}
		}
		
		// Now look through all the notification logs tables that haven't been processed above.
		// Any not closed notifications in those should be closed.
		foreach ($logs as $log_table)
		{
			$close_time = time ();
			if (!in_array($log_table, $processed_logs))
			{
				$q = 'SELECT id FROM '.$log_table.' WHERE ended=0 LIMIT 1';
				$exists = DB::db_fetch_field ($q, 'id');
				if ($exists)
				{
					$q = 'UPDATE '.$log_table.' SET ended='.$close_time.' WHERE ended=0';
					DB::db_query ($q);
				}
			}
		}
	}
	
	
	/** [Class Method] Checks if the specified notifications log table exists and, if not, creates it
	* @param	string		$log_table		The name of the table to check/create
	*/
	public static function check_exists_log_table ($log_table)
	{
		if ($log_table and preg_match('/^'.TBL_NOTIFICATIONS.'_/', $log_table))
		{
			$q = 'SHOW TABLES like "'.$log_table.'" ';
			$tbls = DB::db_fetch_vector ($q);
			
			if (count($tbls) == 0)
			{
				$q = "CREATE TABLE ".$log_table." ( ";
				$q.= "`id` int(11) NOT NULL default '0', `event_code` int(11) NOT NULL default '0', `level` int(11) NOT NULL default '0',";
				$q.= "`raised` int(11) NOT NULL default '0',`ended` int(11) NOT NULL default '0',`object_class` int(11) NOT NULL default '0',";
				$q.= "`object_id` int(11) NOT NULL default '0',`object_event_code` int(11) NOT NULL default '0',";
				$q.= "`object_name` varchar(255) NOT NULL default '',`item_id` int(11) NOT NULL default '0',`text` text NOT NULL,";
				$q.= "`emailed_last` int(11) NOT NULL default '0',`ticket_id` int(11) NOT NULL default '0',`user_id` int(11) NOT NULL default '0',";
				$q.= "PRIMARY KEY  (`id`,`raised`),KEY `ticket_id` (`ticket_id`),KEY `item_id` (`item_id`),";
				$q.= "KEY `object_class` (`object_class`),KEY `object_id` (`object_id`),KEY `user_id` (`user_id`),KEY `event_code` (`event_code`),";
				$q.= "KEY `object_event_code` (`object_event_code`),KEY `ended` (`ended`),KEY `raised` (`raised`),";
				$q.= "KEY `emailed_last` (`emailed_last`),KEY `level` (`level`))";
				

				DB::db_query ($q);
			}
		}
	}
}

?>
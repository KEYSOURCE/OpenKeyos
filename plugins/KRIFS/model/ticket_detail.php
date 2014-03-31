<?php

class_load ('Ticket');
class_load ('ActionType');
class_load ('InterventionLocation');

/**
* Class for managing KRIFS (technical support) ticket detail.
*
* A ticket detail store details entered by a user at one time or reassignments made.
*
*/

class TicketDetail extends Base
{
	/** Ticket detail ID
	* @var int */
	var $id = null;

	/** The ID of the ticket to which this detail belongs to
	* @var int */
	var $ticket_id = null;

	/** The comment entered by the user
	* @var text */
	var $comments = '';

	/** The type of action performed
	* @var integer */
	var $activity_id = null;

	/** The amount of time put in by the user (in minutes)
	* @var int */
	var $work_time = 0;

	/** The time when the work has started
	* @var time */
	var $time_in = 0;

	/** The time when the work has finished. Normally, this should be equal to $work_time+$time_in (minutes)
	* @var time */
	var $time_out = 0;

	/** The calculated amount of billable time (minutes)
	* @var int */
	var $bill_time = 0;

	/** The actual amount of time to be billed to the customer (minutes)
	* @var int */
	var $tbb_time = 0;

	/** Specifies if this ticket detail is billable or not. Normally this is inherited from the parent ticket.
	* @var bool */
	var $billable = false;

	/** If the action type for this ticket detail is a fixed-price one, then this
	* specifies if this should be counted as a new item or a continuation of an
	* old one, in which case it will not be invoiced.
	* @var bool */
	var $is_continuation = false;

	/** Specifies if the billing time has been individually set yet. If false, the bill time
	* will always be the same as the work time.
	* @var bool */
	var $bill_time_set = false;

	/** The ID of the intervention report, if this detail is part of one
	* @var int */
	var $intervention_report_id = null;

	/** The ID of the location where the action was done (if any)
	* @var int */
	var $location_id = 0;

	/** The time when this entry was created
	* @var time */
	var $created = null;

	/** The ID of the user who created this ticket
	* @var int */
	var $user_id = null;

	/** The ID of the user to whom this was assigned
	* @var int */
	var $assigned_id = null;

	/** If this is a public or private entry. If private, it can't be viewed by the customer
	* @var boolean */
	var $private = 1;

	/** Records the ticket status at the time when the ticket was created
	* @var int */
	var $status = null;

	/** If non zero, this entry marks the moment when the ticket was escalated
	* @var time */
	var $escalated = 0;

	/** The ID of the customer order to which this ticket detail is linked (if any). Normally
	* this is inherited from the ticket when the ticket detail is created.
	* @var int */
	var $customer_order_id = 0;

	/** The start time for the travel to customer - if any
	* @var timestamp */
	var $time_start_travel_to = 0;

	/** The end time for the travel to customer - if any
	* @var timestamp */
	var $time_end_travel_to = 0;

	/** The start time of the travel from customer - if any
	* @var timestamp */
	var $time_start_travel_from = 0;

	/** The end time of the travel from customer - if any
	* @var timestamp */
	var $time_end_travel_from = 0;


	/** The User object who created this entry
	* @var User */
	var $user = null;

	/** The User/Group object to whom this was assigned
	* @var User */
	var $assigned = null;

	/** The action type object performed
	* @var ActionType */
	var $action_type = null;

	/** The location object
	* @var InterventionLocation */
	var $location = null;

	/** The customer order, if exists
	* @var CustomerOrder */
	var $customer_order = null;


	var $table = TBL_TICKETS_DETAILS;
	var $fields = array ('id', 'ticket_id', 'comments', 'activity_id', 'work_time', 'bill_time', 'tbb_time', 'bill_time_set', 'billable', 'is_continuation', 'created', 'user_id', 'assigned_id', 'private', 'status', 'escalated', 'time_in', 'time_out', 'intervention_report_id', 'location_id', 'customer_order_id', 'time_start_travel_to', 'time_end_travel_to', 'time_start_travel_from', 'time_end_travel_from');


	/**
	* Constructor. Also loads a ticket detail data if an ID is provided
	* @param	int	$id		The ID of the ticket detail to load
	*/
	function TicketDetail ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}


	/** Loads the object data */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				// Load the User object who created this entry
				if ($this->user_id) $this->user = new User ($this->user_id);

				// Load the User object for which this entry was re-assigned
				if ($this->assigned_id) $this->assigned = new User ($this->assigned_id);

				// Load the Activiy object
				if ($this->activity_id) $this->action_type = new ActionType ($this->activity_id);

				// Load the Location object
				if ($this->location_id) $this->location = new InterventionLocation ($this->location_id);

				// Load the customer order
				if ($this->customer_order_id) $this->customer_order = new CustomerOrder ($this->customer_order_id);

				// Check for the encoding
				if ($this->comments)
				{
					//we must prepare here for the validity of the xml comments
                    $this->comments = str_replace('<p>&nbsp;</p>', '', $this->comments);
                    $this->comments = preg_replace('/(<br>)+/', '<br />', $this->comments);
					$this->comments = str_replace('&rdquo;', '"', $this->comments);
					$this->comments = str_replace('&ldquo;', '"', $this->comments);
					$this->comments = str_replace('&rsquo;', '\'', $this->comments);
					$this->comments = str_replace('&lsquo;', '\'', $this->comments);
					$this->comments = str_replace('&hellip;', '&#133;', $this->comments);
					/*
					$this->comments = str_replace('&nbsp;', ' ', $this->comments);
					$this->comments = str_replace('&', '&amp;', $this->comments);
					$this->comments = str_replace('<-', '&lt;-', $this->comments);
					$this->comments = str_replace('->', '-&gt;', $this->comments);
					$this->comments = str_replace('<*', '&lt;*', $this->comments);
					$this->comments = str_replace('*>', '*&gt;', $this->comments);
					//$xml = str_replace('"', '\'', $xml);
					$this->comments = str_replace('&amp;#', '&#', $this->comments);
					$this->comments = eregi_replace("&amp;([a-z])[a-z0-9]{3,};", "\\1", $this->comments);
					$this->comments = ereg_replace("<([-a-z0-9.!#$%&\'*+/=?^_{|}~]+)@([.a-zA-Z0-9_/-]+)*>","&lt;\\1@\\2&gt;",$this->comments);					
					*/
					//if (mb_detect_encoding($this->comments) == 'UTF-8') $this->comments = utf8_decode ($this->comments);
					//$this->comments = htmlspecialchars($this->comments, ENT_NOQUOTES);
				}
			}
		}
	}


	/** Loads the ticket detail data from an array */
	function load_from_array ($data = array())
	{
		$old_activity_id = $this->activity_id;
		parent::load_from_array ($data);

		// If the action type has been changed, make sure to reload the new ActionType object
		if ($old_activity_id != $this->activity_id) $this->action_type = new ActionType ($this->activity_id);
	}


	/** Checks if the detail data is valid */
	function is_valid_data ()
	{
		$ret = true;

		if ($this->private and $this->assigned_id)
		{
			$user = new User ($this->assigned_id);
			if ($user->customer_id)
			{
				error_msg ('If you assign a ticket to a customer, you can\'t mark the entry as private');
				$ret = false;
			}
		}
		if (!$this->assigned_id)
		{
			error_msg ('The ticket must be asigned to a valid user.');
			$ret = false;
		}

		if ($this->work_time and !$this->activity_id)
		{
			error_msg ('If you specify the work time, you need to specify the action type as well');
			$ret = false;
		}
		if ($this->work_time and !$this->location_id)
		{
			error_msg ('If you specify the work time, you need to specify the location as well');
			$ret = false;
		}
		if ($this->work_time<=0 and $this->intervention_report_id)
		{
			error_msg ('If you specify an intervention report, you need to specify an activity and work time as well.');
			$ret = false;
		}

		return $ret;
	}


	/** Saves the object data, syncronizing the bill time with the work time, if needed */
	function save_data ()
	{
		// If the bill time has not bee specifically set, make it the same as the work time
		if (!$this->bill_time_set)
		{
			if ($this->action_type->price_type == PRICE_TYPE_FIXED)
			{
				$this->bill_time = 0;
				$this->tbb_time = 0;
			}
			else
			{
				$this->bill_time = $this->work_time;
				$this->tbb_time = $this->bill_time;
			}
		}

		// If the action type is not a fixed price one, make sure that "is_continuation" is set to false
		if ($this->activity_id)
		{
			if ($this->action_type->price_type != PRICE_TYPE_FIXED) $this->is_continuation = false;
		}
		else $this->is_continuation = false;

		// If this ticket detail was been linked to a timesheet, check if the time_in date still falls into
		// the same date. If not, remove the ticket detail from that timesheet. Whatever new date the ticket detail
		// falls into, the timesheet for that date will automatically pick it up when it is loaded.
		if ($this->id)
		{
			class_load ('TimesheetDetail');
			$q = 'SELECT id FROM '.TBL_TIMESHEETS_DETAILS.' WHERE ticket_detail_id='.$this->id.' AND detail_special_type=0';
			$ts_id = $this->db_fetch_field ($q, 'id');
			if ($ts_id)
			{
				$timesheet_detail = new TimesheetDetail ($ts_id);
				$timesheet = new Timesheet ($timesheet_detail->timesheet_id);
				if ($this->time_in==0 or $this->time_in<$timesheet->date or $this->time_in>=strtotime('+1 day', $timesheet->date))
				{
					$timesheet_detail->delete ();
					// Make sure to delete all associated timesheets details
					$ids = $this->db_fetch_vector ('SELECT id FROM '.TBL_TIMESHEETS_DETAILS.' WHERE ticket_detail_id='.$this->id);
					foreach ($ids as $id)
					{
						$timesheet_detail = new TimesheetDetail ($id);
						$timesheet_detail->delete ();
					}
				}

				if ($this->time_start_travel_to==0 or $this->time_end_travel_to==0)
				{
					// If there is no "Travel to" time, make sure to delete any such timesheet detail that might have existed
					$q = 'SELECT id FROM '.TBL_TIMESHEETS_DETAILS.' WHERE ticket_detail_id='.$this->id.' AND ';
					$q.= 'detail_special_type='.TS_SPECIAL_TRAVEL_TO;
					$ts_id = $this->db_fetch_field ($q, 'id');
					if ($ts_id)
					{
						$timesheet_detail = new TimesheetDetail ($ts_id);
						$timesheet_detail->delete ();
					}
				}
				if ($this->time_start_travel_from==0 or $this->time_end_travel_from==0)
				{
					// If there is no "Travel to" time, make sure to delete any such timesheet detail that might have existed
					$q = 'SELECT id FROM '.TBL_TIMESHEETS_DETAILS.' WHERE ticket_detail_id='.$this->id.' AND ';
					$q.= 'detail_special_type='.TS_SPECIAL_TRAVEL_FROM;
					$ts_id = $this->db_fetch_field ($q, 'id');
					if ($ts_id)
					{
						$timesheet_detail = new TimesheetDetail ($ts_id);
						$timesheet_detail->delete ();
					}
				}
			}
		}

		parent::save_data ();
	}


	/** Checks if this ticket detail can be edited, e.g. check if the ticket or the intervention report (if linked to one) is not closed
	* @param	bool		$raise_errors		If true, error messages will be raised through error_msg()
	* @return	bool					True or False if the ticket detail can be modified or not
	*/
	function can_modify ($raise_errors = false)
	{
		$ret = true;

		// Check if the ticket is not closed
		/* Not needed anymore. It was requested by Keysource to allow editing details for closed tickets
		if ($this->ticket_id)
		{
			$q = 'SELECT status FROM '.TBL_TICKETS.' WHERE id='.$this->ticket_id;
			$stat = $this->db_fetch_field ($q, 'status');
			if ($stat == TICKET_STATUS_CLOSED)
			{
				$ret = false;
				if ($raise_errors) error_msg ('This ticket detail cannot be modified, the ticket to which it belongs has been closed.');
			}
		}
		*/

		// Check if the intervention report (if any) is not closed
		if ($this->intervention_report_id)
		{
			$q = 'SELECT status FROM '.TBL_INTERVENTION_REPORTS.' WHERE id='.$this->intervention_report_id;
			$stat = $this->db_fetch_field ($q, 'status');
			if ($stat != INTERVENTION_STAT_OPEN)
			{
				$ret = false;
				if ($raise_errors) error_msg ('This ticket detail cannot be modified, the intervention report to which it belongs has been closed.');
			}
		}

		// Check if the timesheet to which it belongs (if any) is not closed
		if ($this->user_id and $this->time_in)
		{
			// Check to see if there is a timesheet defined
			$q = 'SELECT id, status FROM '.TBL_TIMESHEETS.' WHERE user_id='.$this->user_id.' AND date<='.$this->time_in.' AND date>'.($this->time_in-(24*60*60));
			$res = $this->db_fetch_array ($q);
			if (count($res)>0)
			{
				// There is a timesheet for that date
				if ($res[0]->status != TIMESHEET_STAT_OPEN)
				{
					$ret = false;
					if ($raise_errors) error_msg ('This ticket detail cannot be modified, the timesheet to which it belongs has been closed.');
				}
			}
		}

		return $ret;
	}


	/** Checks if a ticket detail is valid for inclusion in an intervention report
	* @param	int		$intervention_report_id		If specified, the function will check if the ticket detail
	*								can be added to that specific intervention report - which means
	*								that the ticket detail should be either not assigned to an intervention report
	*								already, or assigned to this particular intervention report. If it is not
	*								specified, then the ticket detail should not have been assigned to an intervention.
	* @return	bool						True or False if the ticket detail can be included or not in an intervention report.
	*/
	function is_valid_for_intervention ($intervention_report_id = false)
	{
		$ret = ($this->user_id and !$this->user->is_customer_user() and ($this->comments and $this->work_time));
		if ($ret)
		{
			if ($intervention_report_id) $ret = (!$this->intervention_report_id or $this->intervention_report_id==$intervention_report_id);
			else $ret = (!$this->intervention_report_id);
		}
		return $ret;
	}


	/** Logs an access action for this ticket detail into a TBL_TICKETS_ACCESS_y_m table.
	* @param	int				$user_id		The ID of the user who made the action
	* @param	int				$action_id		The ID of the action - see
	*/
	function log_action ($user_id, $action_id)
	{
		if ($this->id and $user_id and $action_id)
		{
			$tbl = TBL_TICKETS_ACCESS.'_'.date ('Y_m');
			$q = 'INSERT INTO '.$tbl.'(ticket_id,ticket_detail_id,user_id,date,action_id) VALUES (';
			$q.= $this->ticket_id.','.$this->id.','.$user_id.','.time().','.$action_id.')';
			$this->db_query ($q);
		}
	}

	function get_ticket_subject()
	{
		$ticket = new Ticket($this->ticket_id);
		return $ticket->subject;
	}
    
    function br2nl_comment(){
        return  preg_replace('/\<br[^>]*\/?\>|\<\/?p[^>]*\>|\<\/?div[^>]*\>/i', "\n", $this->comments);
    }
}
?>
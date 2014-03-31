<?php 

class_load ('Ticket');
class_load ('Activity');
class_load ('ActionType');
class_load ('Timesheet');
class_load ('TimesheetDetail');

/**
* Class for managing timesheets
*
*/

class Timesheet extends Base
{
	/** Timesheet ID
	* @var int */
	var $id = null;
	
	/** The user to whom the timesheet belongs
	* @var int */
	var $user_id = null;
	
	/** The date of the timesheet (with the time set to 00:00)
	* @var time */
	var $date = null;
	
	/** The status of the timesheet - see $GLOBALS['TIMESHEET_STATS']
	* @var int */
	var $status = TIMESHEET_STAT_NONE;
	
	/** If the timesheet has been closed, store the timestamp when it was closed
	* @var timestamp */
	var $close_time = 0;
	
	/** If the timesheet has been closed, store the ID of the user who closed it
	* @var int */
	var $closed_by_id = 0;
	
	/** If the timesheet is approved, the date when it was approved
	* @var timestamp */
	var $approved_date = 0;
	
	/** If the timesheet is approved, the ID of the user who approved it
	* @var int */
	var $approved_by_id = 0;

	
	/** The user object to which the timesheet belongs. It is loaded on request, with load_user() method
	* @var User */
	var $user = null;
	
	/** It the timesheet is closed, the user object who closed the timesheet. It is loaded on 
	* request by load_user() method
	* @var User */
	var $closed_by = null;
	
	/** If the timesheet is approved, the user object who approved the timesheet. It is loaded on request,
	* by load_user() method
	* @var User */
	var $approved_by = null;
	
	
	/** The timesheet details, ordered by time in. It contains both defined details, as well as 
	* "blank" details initialized from ticket details which belong to the same user and day as the timesheet,
	* and which have not yet been linked to a timesheet detail
	* @var array(TimesheetDetail) */
	var $details = array ();
	
	/** The hour intervals in the timesheet's day. It will store the empty intervals, as well as the intervals for
	* which there are details. It is an array of generic objects with the following fields: 'time_in', 'time_out',
	* 'detail_idx' - an array of indexes from $this->details (if there are details in this interval) and 'overlaps',
	* if the detail for this interval overlaps with the previous interval.
	* Note that this is loaded only on request, with load_hours() method.
	* @var array */
	var $hours = array ();
	
	
	var $table = TBL_TIMESHEETS;
	var $fields = array ('id', 'user_id', 'date', 'status', 'close_time', 'closed_by_id', 'approved_date', 'approved_by_id');


	/**
	* Constructor. Also loads the data if an ID has been specified
	* @param	int	$id		The ID of the object to load
	*/
	function Timesheet ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	/**
	* [Class Method] Given a user ID and a date, return a timesheet for that user and date,
	* either a blank one (if a timesheet has not been created for that user and date) or a full one
	* @param	int		$user_id		The ID of the user
	* @param	time		$date			The date
	* @return	Timesheet
	*/
    public static function get_timesheet ($user_id, $date)
	{
		$ret = null;
		if ($user_id and $date > 0)
		{
			// Make sure the date is at the 00:00
			$date = strtotime (date('d M Y ', $date).' 00:00');
			$q = 'SELECT id FROM '.TBL_TIMESHEETS.' WHERE user_id='.$user_id.' and date='.$date;
			$id = db::db_fetch_field ($q, 'id');
			if ($id) $ret = new Timesheet($id);
			else
			{
				$ret = new Timesheet();
				$ret->user_id = $user_id;
				$ret->date = $date;
			}
		}
		
		return $ret;
	}
	
	
	/** Loads the timesheet data, as well as its details */
	function load_data ()
	{
		parent::load_data ();
		$this->details = array ();
		if ($this->id)
		{
			// Load the defined details
			$q = 'SELECT id FROM '.TBL_TIMESHEETS_DETAILS.' WHERE timesheet_id='.$this->id.' ORDER BY time_in, time_out ';
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id) $this->details[] = new TimesheetDetail ($id);
		}
		
		// Check for ticket details which have not been linked yet
		$this->load_unassigned_details ();
	}
	
	
	/** Load ticket details which should belong to this timesheet and have not been linked yet */
	function load_unassigned_details ()
	{
		if ($this->date and $this->user_id)
		{
			// Delete the previously discovered unlinked ticket details
			for ($i=count($this->details)-1; $i>=0; $i--)
			{
				if (!$this->details[$i]->id) array_splice ($this->details, $i, 1);
				//unset ($this->details[$i]);
			}
		
			$time_start = $this->date;
			$time_end = strtotime ('+1 day', $time_start);
			
			// Fetch the ticket details which have not been linked yet
			$q = 'SELECT DISTINCT td.id FROM '.TBL_TICKETS_DETAILS.' td LEFT OUTER JOIN '.TBL_TIMESHEETS_DETAILS.' tsd ';
			$q.= 'ON tsd.ticket_detail_id = td.id AND tsd.detail_special_type=0 ';
			$q.= 'WHERE td.user_id='.$this->user_id.' AND td.time_in>='.$time_start.' AND td.time_in<'.$time_end.' ';
			$q.= 'AND tsd.id IS NULL ORDER BY td.time_in, td.time_out';
			$ids = db::db_fetch_vector ($q);
			
			foreach ($ids as $id)
			{
				$ticket_detail = new TicketDetail ($id);
				$detail = new TimesheetDetail ();
				$detail->timesheet_id = $this->id;
				$detail->time_in = $ticket_detail->time_in;
				$detail->time_out = $ticket_detail->time_out;
				$detail->ticket_detail_id = $ticket_detail->id;
				$detail->load_data ();
				
				// Now see the position in $this->details where this should be inserted
				for ($i=0; ($i<count($this->details) and $this->details[$i]->time_in <= $detail->time_in); $i++);
				if ($i>=count($this->details)) $this->details[] = $detail;
				else array_splice ($this->details, $i, 0, array($detail));
			}
			
			// Check for "Travel to" times not linked from tickets yet
			$q = 'SELECT DISTINCT td.id FROM '.TBL_TICKETS_DETAILS.' td LEFT OUTER JOIN '.TBL_TIMESHEETS_DETAILS.' tsd ';
			$q.= 'ON tsd.ticket_detail_id = td.id AND tsd.detail_special_type='.TS_SPECIAL_TRAVEL_TO.' ';
			$q.= 'WHERE td.user_id='.$this->user_id.' AND td.time_start_travel_to>='.$time_start.' AND td.time_end_travel_to<'.$time_end.' ';
			$q.= 'AND tsd.id IS NULL ORDER BY td.time_start_travel_to, td.time_end_travel_to';
			$ids = db::db_fetch_vector ($q);
			
			foreach ($ids as $id)
			{
				$ticket_detail = new TicketDetail ($id);
				$detail = new TimesheetDetail ();
				$detail->timesheet_id = $this->id;
				$detail->time_in = $ticket_detail->time_start_travel_to;
				$detail->time_out = $ticket_detail->time_end_travel_to;
				$detail->ticket_detail_id = $ticket_detail->id;
				$detail->detail_special_type = TS_SPECIAL_TRAVEL_TO;
				$detail->load_data ();
				
				// Now see the position in $this->details where this should be inserted
				for ($i=0; ($i<count($this->details) and $this->details[$i]->time_in <= $detail->time_in); $i++);
				if ($i>=count($this->details)) $this->details[] = $detail;
				else array_splice ($this->details, $i, 0, array($detail));
			}
			
			// Check for "Travel from" times not linked from tickets yet
			$q = 'SELECT DISTINCT td.id FROM '.TBL_TICKETS_DETAILS.' td LEFT OUTER JOIN '.TBL_TIMESHEETS_DETAILS.' tsd ';
			$q.= 'ON tsd.ticket_detail_id = td.id AND tsd.detail_special_type='.TS_SPECIAL_TRAVEL_FROM.' ';
			$q.= 'WHERE td.user_id='.$this->user_id.' AND td.time_start_travel_from>='.$time_start.' AND td.time_end_travel_from<'.$time_end.' ';
			$q.= 'AND tsd.id IS NULL ORDER BY td.time_start_travel_from, td.time_end_travel_from';
			$ids = db::db_fetch_vector ($q);
			
			foreach ($ids as $id)
			{
				$ticket_detail = new TicketDetail ($id);
				$detail = new TimesheetDetail ();
				$detail->timesheet_id = $this->id;
				$detail->time_in = $ticket_detail->time_start_travel_from;
				$detail->time_out = $ticket_detail->time_end_travel_from;
				$detail->ticket_detail_id = $ticket_detail->id;
				$detail->detail_special_type = TS_SPECIAL_TRAVEL_FROM;
				$detail->load_data ();
				
				// Now see the position in $this->details where this should be inserted
				for ($i=0; ($i<count($this->details) and $this->details[$i]->time_in <= $detail->time_in); $i++);
				if ($i>=count($this->details)) $this->details[] = $detail;
				else array_splice ($this->details, $i, 0, array($detail));
			}
		}
	}
	
	
	/** Loads the user for this timesheet */
	function load_user ()
	{
		if ($this->user_id) $this->user = new User ($this->user_id);
		if ($this->closed_by_id) $this->closed_by = new User ($this->closed_by_id);
		if ($this->approved_by_id) $this->approved_by = new User ($this->approved_by_id);
	}
	
	
	/** Initializes $this->hours */
	function load_hours ()
	{
		$this->hours = array ();
		
		if ($this->user_id and $this->date)
		{
			$time_in_min = $this->date + DAY_HOUR_START;
			$time_out_max = $this->date + DAY_HOUR_END;
		
			if (count($this->details) > 0)
			{
				// Calculate the min and max times from the details
				for ($i=0; $i<count($this->details); $i++)
				{
					if ($time_in_min > $this->details[$i]->time_in) $time_in_min = $this->details[$i]->time_in;
					if ($time_out_max < $this->details[$i]->time_out) $time_out_max = $this->details[$i]->time_out;
				}
				
				$last_time_in = $time_in_min;
				$last_time_out = $time_in_min;
				if ($time_in_min < $this->details[0]->time_in)
				{
					// There is an empty interval between the beginning of the work day and the first detail in the timesheet
                    if(!$this->hours[0]) $this->hours[0] = new StdClass;
					$this->hours[0]->time_in = $time_in_min;
					$this->hours[0]->time_out = $this->details[0]->time_in;
					$last_time_in = $time_in_min;
					$last_time_out = $this->details[0]->time_in;
				}
				
				for ($i=0; $i<count($this->details); $i++)
				{
					$interval = null;
					$space = null;
					
					// Add the detail as an interval
					$interval->time_in = $this->details[$i]->time_in;
					$interval->time_out = $this->details[$i]->time_out;
					$interval->detail_idx = $i;
					
					if ($interval->time_in < $last_time_out)
					{
						// There is an overlapping with the previos interval
						$interval->overlaps = true;
						$this->hours[count($this->hours)-1]->overlaps = true;
					}
					
					if ($interval->time_in > $last_time_out)
					{
						// There is an empty time interval before this detail
						$space->time_in = $last_time_out;
						$space->time_out = $this->details[$i]->time_in;
						$this->hours[] = $space;
					}
					
					$last_time_in = $interval->time_in;
					$last_time_out = $interval->time_out;
					$this->hours[] = $interval;
				}
				
				if ($last_time_out < $time_out_max)
				{
					$space = null;
					// There is an empty interval at the end of the last day
					$space->time_in = $last_time_out;
					$space->time_out = $time_out_max;
					$this->hours[] = $space;
				}
			}
			else
			{
                if(!$this->hours[0]) $this->hours[0] = new StdClass;
				$this->hours[0]->time_in = $time_in_min;
				$this->hours[0]->time_out = $time_out_max;
			}
		}
	}
	
	
	/**
	* Save the object to the database, optionally saving the details as well
	* @param	$bool		$save_details		If True, it will also save the details from this timesheet
	*/
	function save_data ($save_details = false)
	{
		if ($this->status == TIMESHEET_STAT_NONE) $this->status = TIMESHEET_STAT_OPEN;
		parent::save_data ();
		
		if ($save_details)
		{
			for ($i=0; $i<count($this->details); $i++)
			{
				$this->details[$i]->timesheet_id = $this->id;
				$this->details[$i]->save_data ();
			}
		}
	}
	
	
	/** Returns the total number of work minutes - only for linked details.
	* Note that it takes into account the overlapping intervals, which are NOT counted. */
	function get_defined_work_time ()
	{
		$ret = 0;
		if (count($this->details) > 0)
		{
			// First add all duration, including overlappings
			$duration = 0; // duration in seconds
			for ($i=0; $i<count($this->details); $i++) 
			{
				if ($this->details[$i]->id) $duration+= ($this->details[$i]->time_out - $this->details[$i]->time_in);
			}
			
			// Next, check all overlapping intervals and substract the durations of overlaps
			for ($i=1; $i<count($this->details); $i++) 
			{
				if ($this->details[$i]->id)
				{
					$time_in = $this->details[$i]->time_in;
					$time_out = $this->details[$i]->time_out;
					for ($j=0; $j<$i; $j++)
					{
						if ($this->details[$i]->id)
						{
							$comp_time_in = $this->details[$j]->time_in;
							$comp_time_out = $this->details[$j]->time_out;
							if ($time_in<$comp_time_out and $time_out>$comp_time_in)
							{
								// Calculate the length of the overlapping interval
								$over_duration = min($time_out,$comp_time_out)-max($time_in,$comp_time_in);
								$duration-= $over_duration;
							}
						}
					}
				}
			}
			
			$ret = intval($duration / 60);
		}
		return $ret;
	}
	
	
	/** Return the total number of work_minutes - both for linked an un-linked details.
	* Note that it takes into account the overlapping intervals, which are NOT counted. */
	function get_work_time ()
	{
		$ret = 0;
		if (count($this->details) > 0)
		{
			// First add all duration, including overlappings
			$duration = 0; // duration in seconds
			for ($i=0; $i<count($this->details); $i++) $duration+= ($this->details[$i]->time_out-$this->details[$i]->time_in);

			// Next, check all overlapping intervals and substract the durations of overlaps
			for ($i=1; $i<count($this->details); $i++) 
			{
				$time_in = $this->details[$i]->time_in;
				$time_out = $this->details[$i]->time_out;
				for ($j=0; $j<$i; $j++)
				{
					$comp_time_in = $this->details[$j]->time_in;
					$comp_time_out = $this->details[$j]->time_out;
					if ($time_in<$comp_time_out and $time_out>$comp_time_in)
					{
						// Calculate the length of the overlapping interval
						$over_duration = min($time_out,$comp_time_out)-max($time_in,$comp_time_in);
						$duration-= $over_duration;
					}
				}
			}
			
			$ret = intval($duration / 60);
		}
		return $ret;
	}

	
	/** Checks if the timesheet can be closed */
	function can_close_timesheet ()
	{
		$ret = true;
		
		$total_time = $this->get_work_time();
		if (($total_time/60) < MIN_TIMESHEET_HOURS)
		{
			$ret = false;
			error_msg ('Sorry, but a timesheet can\'t be closed if it contains less than '.MIN_TIMESHEET_HOURS.' hours.');
		}
		
		return $ret;
	}
	
	/** Closed the timesheet, after which it is ready to be imported by the ERP system 
	* @param	int		$user_id	The ID of the user who is performing the closing
	*/
	function close_timesheet ($user_id)
	{
		if ($this->id and $this->status == TIMESHEET_STAT_OPEN)
		{
			$this->status = TIMESHEET_STAT_CLOSED;
			$this->closed_by_id = $user_id;
			$this->close_time = time ();
			$this->save_data ();
		}
	}
	
	/** Checks if the timesheet can be reopened */
	function can_reopen_timesheet ()
	{
		$ret = true;
		
		if ($this->status == TIMESHEET_STAT_CENTRALIZED or $this->status == TIMESHEET_STAT_PENDING_CENTRALIZE) 
		{error_msg ('This timesheet has been imported in the ERP system, it can\'t be re-opened.'); $ret=false;}
		elseif ($this->status != TIMESHEET_STAT_CLOSED) {error_msg ('Sorry, this timesheet can\'t be re-opened.'); $ret = false;}
		
		return $ret;
	}
	
	/** Request a reopening of the timesheet */
	function reopen_timesheet ()
	{
		if ($this->id and $this->status == TIMESHEET_STAT_CLOSED)
		{
			$this->status = TIMESHEET_STAT_OPEN;
			$this->closed_by_id = 0;
			$this->close_time = 0;
			$this->save_data ();
		}
	}
	
	/** Checks if the timesheet can be approved */
	function can_approve_timesheet ()
	{
		return ($this->id and $this->status == TIMESHEET_STAT_CLOSED);
	}
	
	
	/** Marks the timesheet as being approved */
	function approve_timesheet ($user_id)
	{
		if ($this->id)
		{
			$this->status = TIMESHEET_STAT_APPROVED;
			$this->approved_date = time ();
			$this->approved_by_id = $user_id;
			$this->save_data ();
		}
	}
	
	/** Checks if the approval can be canceled */
	function can_cancel_approval ()
	{
		return ($this->id and $this->status == TIMESHEET_STAT_APPROVED);
	}
	
	
	/** Cancels the approval of the timesheet */
	function cancel_approval ()
	{
		if ($this->id)
		{
			$this->status = TIMESHEET_STAT_CLOSED;
			$this->approved_date = 0;
			$this->approved_by_id = 0;
			$this->save_data ();
		}
	}
	
	/** Cancels the "Centralized" status */
	function cancel_centralization ()
	{
		if ($this->id and ($this->status == TIMESHEET_STAT_CENTRALIZED or $this->status == TIMESHEET_STAT_PENDING_CENTRALIZE))
		{
			$this->status = TIMESHEET_STAT_APPROVED;
			$this->save_data ();
		}
	}
	
	
	/** [Class Method] Returns timesheets according to some criteria 
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- user_id: return all timesheets for the specified user
	*							- status: return all timesheets with the specified status
	*							- start, limit: the start page and number of items per page
	* @param	int		$count			(By reference) If set, it will be loaded with the total number 
	*							of items matched by the rules
	*/
    public static function get_timesheets($filter = array (), &$count)
	{
		$ret = array ();
		
		$q = ' FROM '.TBL_TIMESHEETS.' t ';
		if (!$filter['user_id']) $q.= 'INNER JOIN '.TBL_USERS.' u ON t.user_id=u.id ';
		$q.= 'WHERE ';
		
		if ($filter['user_id']) $q.= 't.user_id='.$filter['user_id'].' AND ';
		if ($filter['status']) $q.= 't.status='.$filter['status'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		if (isset($filter['start']) and isset($filter['limit']) and isset($count))
		{
			$q_count = 'SELECT count(*) as cnt '.$q;
			$count = db::db_fetch_field ($q_count, 'cnt');
		}
		
		$q = 'SELECT DISTINCT t.id '.$q;
		if (!$filter['user_id']) $q.= 'ORDER BY t.date DESC, u.fname, u.lname ';
		else $q.= 'ORDER BY t.date DESC ';
		
		if (isset($filter['start']) and isset($filter['limit'])) $q.= 'LIMIT '.$filter['start'].','.$filter['limit'].' ';
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Timesheet ($id);
		
		return $ret;
	}
	
}
?>
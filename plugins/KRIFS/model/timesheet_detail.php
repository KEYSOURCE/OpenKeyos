<?php

class_load ('Ticket');
class_load ('Activity');
class_load ('Timesheet');
class_load ('InterventionLocation');

/**
* Class for managing timesheets
*
*/

class TimesheetDetail extends Base
{
	/** Timesheet detail ID
	* @var int */
	var $id = null;
	
	/** Timesheet ID 
	* @var int */
	var $timesheet_id = null;
	
	/** The ID of the ticket detail - if this is linked to a ticket detail
	* @var int */
	var $ticket_detail_id = 0;
	
	/** The start hour of the detail. If this is linked to a ticket detail,
	* the time in will be automatically syncronized by the ticket detail.
	* @var time */
	var $time_in = 0;
	
	/** The end hour of the detail. If this is linked to a ticket detail,
	* the time in will be automatically syncronized by the ticket detail.
	* @var time */
	var $time_out = 0;
	
	/** The ID of the activity. If this is linked to a ticket detail,
	* it will be automatically syncronized by the ticket detail.
	* @var int */
	var $activity_id = 0;
	
	/** The ID of the location. If this is linked to a ticket detail,
	* it will be automatically syncronized by the ticket detail 
	* @var int */
	var $location_id = DEFAULT_TS_LOCATION;
	
	/** Free-text comments (if not linked to a ticket detail ID)
	* @var text */
	var $comments = '';
	
	/** The ID of the customer, if this is not linked to ticket detail 
	* @var int */
	var $customer_id = DEFAULT_TS_CUSTOMER;
	
	/** Specifies if there is a special type of relation (e.g. "Travel to" time) - see $GLOBALS ['TS_SPECIALS']
	* @var int */
	var $detail_special_type = 0;
	
	
	/** If this is linked to a ticket detail, this field stores the ticket detail
	* @var TicketDetail */
	var $ticket_detail = null;
	
	/** If this is linked to a ticket detail, this fields stores the ticket. Loaded only on request, with load_ticket() method
	* @var Ticket */
	var $ticket = null;
	
	/** The associated activity object, if any
	* @var Activity */
	var $activity = null;
	
	/** The associated InterventionLocation object, if any
	* @var InterventionLocation */
	var $location = null;
	
	
	var $table = TBL_TIMESHEETS_DETAILS;
	var $fields = array ('id', 'timesheet_id', 'ticket_detail_id', 'time_in', 'time_out', 'activity_id', 'location_id', 'comments', 'customer_id', 'detail_special_type');


	/**
	* Constructor. Also loads the data if an ID has been specified
	* @param	int	$id		The ID of the object to load
	*/
	function TimesheetDetail ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	
	/** Load the object data, as well the linked ticket detail - if any */
	function load_data ()
	{
		parent::load_data ();
		
		if ($this->ticket_detail_id)
		{
			$this->ticket_detail = new TicketDetail ($this->ticket_detail_id);
			// Syncronize the times, activity and location
			$this->location_id = $this->ticket_detail->location_id;
			if (!$this->detail_special_type)
			{
				$this->time_in = $this->ticket_detail->time_in;
				$this->time_out = $this->ticket_detail->time_out;
				$this->activity_id = $this->ticket_detail->activity_id;
			}
			elseif ($this->detail_special_type == TS_SPECIAL_TRAVEL_TO)
			{
				$this->time_in = $this->ticket_detail->time_start_travel_to;
				$this->time_out = $this->ticket_detail->time_end_travel_to;
				$this->activity_id = 2; //XXXXX Not nice to use magic numbers!!!
				$this->activity = new Activity ($this->activity_id);
			}
			elseif ($this->detail_special_type == TS_SPECIAL_TRAVEL_FROM)
			{
				$this->time_in = $this->ticket_detail->time_start_travel_from;
				$this->time_out = $this->ticket_detail->time_end_travel_from;
				$this->activity_id = 2; //XXXXX Not nice to use magic numbers!!!
				$this->activity = new Activity ($this->activity_id);
			}
			
			// Fetch the customer ID as well
			// XXXX: There were cases when the customer ID could not be determined because of invalid
			// ticket_detail_id in the timesheet detail line. Must be investigated.
			if ($this->ticket_detail->ticket_id)
			{
				$q = 'SELECT customer_id FROM '.TBL_TICKETS.' WHERE id='.$this->ticket_detail->ticket_id;
				$this->customer_id = $this->db_fetch_field ($q, 'customer_id');
			}
		}
		elseif ($this->activity_id)
		{
			$this->activity = new Activity ($this->activity_id);
		}
				
		if ($this->location_id) $this->location = new InterventionLocation ($this->location_id);
//		$this->comments = htmlspecialchars($this->comments, ENT_NOQUOTES);
	}
	
	
	/** Load the ticket associated with this detail */
	function load_ticket ()
	{
		if ($this->ticket_detail->id)
		{
			$this->ticket = new Ticket ($this->ticket_detail->ticket_id);
		}
	}
	
	
	/** Checks if the timesheet detail data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->timesheet_id) {error_msg ('Please specify the timesheet.'); $ret = false;}
		
		if (!$timesheet->ticket_detail_id)
		{
			if ($this->time_in <= 0) {error_msg ('Please specify the time in.'); $ret = false;}
			if ($this->time_out <= 0) {error_msg ('Please specify the time out.'); $ret = false;}
			if ($this->time_in > 0 and $this->time_out > 0 and $this->time_out < $this->time_in)
			{
				error_msg ('The time out can\'t be before the time in.');
				$ret = false;
			}
			if (!$this->activity_id) {error_msg ('Please specify the activity.'); $ret = false;}
			if (!$this->location_id) {error_msg ('Please specify the location.'); $ret = false;}
			if (!$this->customer_id) {error_msg ('Please specify the customer.'); $ret = false;}
		}
		
		return $ret;
	}
	
	/** Gets the duration of the activity, in hours */
	function get_duration_hours ()
	{
		$ret = 0;
		if ($this->time_in>0 and $this->time_out>0)
		{
			$ret = round((($this->time_out-$this->time_in)/3600), 2);
		}
		return $ret;
	}
}
?>
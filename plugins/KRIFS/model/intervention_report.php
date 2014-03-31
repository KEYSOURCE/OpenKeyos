<?php

class_load ('Ticket');
class_load ('ActionType');
class_load ('InterventionReportDetail');
class_load ('InterventionLocation');

/**
* Class for managing intervention reports
*
* An intervention report represents a set of (billable) action types performed for a
* a customer. An InterventionReport object will store the "header" of the intervention
* report, while the data for intervention report lines will actually be stored in
* the ticket details which belong to this intervention report.
*
*/

class InterventionReport extends Base
{
	/** Intervention report ID
	* @var int */
	var $id = null;

	/** The subject of the intervention report
	* @var string */
	var $subject = '';

	/** The ID of the customer to which the intervention report belongs to
	* @var int */
	var $customer_id = null;

	/** The date when the intervention report was created. Note that this is not the same
	* as the date(s) on which the actual activities from this report were performed
	* @var time */
	var $created = 0;

	/** The ID of the user who created the the intervention report
	* @var int */
	var $user_id = null;

	/** Specifies if the status of this intervention report - see $GLOBALS['INTERVENTION_STATS']
	* @var int */
	var $status = INTERVENTION_STAT_OPEN;

	/** Some comments about the intervention report
	* @var text */
	var $comments = '';

	/** If the intervention report has been approved, specifies the date when it was closed
	* @var time */
	var $approved_date = 0;

	/** If the intervention report has been approved, specify the user ID of the user who approved it
	* @var int */
	var $approved_by_id = 0;


	/** Stores the total work time from the ticket details belonging to this intervention report (minutes)
	* @var int */
	var $work_time = 0;

	/** Stores the total billable amount for this intervention report (minutes). It is calculated upon
	* loading, either from the ticket details (if the IR is open) or from invoicing lines (if the IR is closed).
	* This is used only for displaying in summaries.
	* @var int */
	var $bill_amount = 0;

	/** Stores the total TBB amount for this intervention report (minutes). It is calculated upon loading,
	* either from the ticket details or from invoicing lines (if the IR is closed). This is used only for
	* displaying in summaries.
	* @var int */
	var $tbb_amount = 0;


	/** Stores all the tickets details which are related to this intervention report. This is loaded
	* every time the object is loaded
	* @var array (TicketDetail) */
	var $details = array ();

	/** If the ticket has been closed, stores all the intervention report detail lines (summaries by action type).
	* This is loaded every time the object is loaded.
	* @var array (InterventionReportDetail) */
	var $lines = array ();

	/** Stores the tickets to which the related ticket details belong to. This is loaded on request, with load_tickets()
	* It is an associative array, the keys being ticket IDs and the values being Ticket objects
	* @var array (Ticket) */
	var $tickets = array ();

	/** If the intervention report has been approved, this stores the User object who did the approval
	* @var User */
	var $approved_by = null;


	var $table = TBL_INTERVENTION_REPORTS;
	var $fields = array ('id', 'subject', 'customer_id', 'created', 'user_id', 'status', 'comments', 'approved_date', 'approved_by_id');


	/**
	* Constructor. Also loads the data if an ID has been specified
	* @param	int	$id		The ID of the object to load
	*/
	function InterventionReport ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
                        $this->verify_access();
		}
	}


	/** Loads the object data, as well as the related ticket details */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->id)
			{
				// Load the related ticket details
				$q = 'SELECT id FROM '.TBL_TICKETS_DETAILS.' WHERE intervention_report_id='.$this->id.' ORDER BY time_in DESC, ticket_id DESC, id DESC ';
				$ids = $this->db_fetch_vector ($q);
				foreach ($ids as $id) $this->details[] = new TicketDetail ($id);

				// If the ticket is not open anymore, load the intervention report detail lines
				if ($this->status != INTERVENTION_STAT_OPEN)
				{
					$q = 'SELECT DISTINCT d.id FROM '.TBL_INTERVENTION_REPORTS_DETAILS.' d LEFT OUTER JOIN '.TBL_ACTION_TYPES.' t ON d.action_type_id=t.id ';
					$q.= 'WHERE d.intervention_report_id='.$this->id.' ORDER BY intervention_date DESC, t.special_type, t.erp_id, d.user_id ';
					$ids = $this->db_fetch_vector ($q);
					foreach ($ids as $id) $this->lines[] = new InterventionReportDetail ($id);
				}

				// Calculate the work, billable and TBB times. For closed internvention reports, use the amounts from
				// invoicing lines. For open intervention reports use the ticket details.
				if ($this->status == INTERVENTION_STAT_OPEN)
				{
					for ($i=0; $i<count($this->details); $i++)
					{
						$this->work_time+= $this->details[$i]->work_time;
						if ($this->details[$i]->billable)
						{
							if ($this->details[$i]->action_type->price_type == PRICE_TYPE_FIXED)
							{
								if (!$this->details[$i]->is_continuation) $this->bill_amount+= 1;
							}
							else
							{
								$billing_unit = $this->details[$i]->action_type->billing_unit;
								if($billing_unit == 0) $billing_unit=1;
								$billable_time = ceil($this->details[$i]->work_time / $billing_unit) * $billing_unit;
								$this->bill_amount+= ceil($this->details[$i]->work_time / $billing_unit);
							}
						}
					}
					$this->tbb_amount = $this->bill_amount;
				}
				else
				{
					for ($i=0; $i<count($this->lines); $i++)
					{
						$this->work_time+= $this->lines[$i]->work_time;
						// Add in summary only billable lines and lines NOT related to travel or other special actions
						if ($this->lines[$i]->billable and $this->lines[$i]->action_type->special_type == 0)
						{
							if ($this->lines[$i]->action_type->price_type == PRICE_TYPE_FIXED)
							{
								$this->bill_amount+= $this->lines[$i]->bill_amount;
								$this->tbb_amount+= $this->lines[$i]->tbb_amount;
							}
							else
							{
								$this->bill_amount+= ($this->lines[$i]->bill_amount / $this->lines[$i]->action_type->billing_unit);
								$this->tbb_amount+= ($this->lines[$i]->tbb_amount / $this->lines[$i]->action_type->billing_unit);
							}
						}
					}
				}

				// If this has been approved, load the user who did the approval
				if ($this->approved_by_id) $this->approved_by = new User ($this->approved_by_id);
			}
		}
	}


	/** Loads the tickets for the associated ticket details and calculate totals */
	function load_tickets ()
	{
		if ($this->id and is_array ($this->details))
		{
			for ($i=0; $i<count($this->details); $i++)
			{
				if (!isset($this->tickets[$this->details[$i]->ticket_id]))
				{
					$this->tickets[$this->details[$i]->ticket_id] = new Ticket ($this->details[$i]->ticket_id);
				}
			}
		}
	}


	/** Saves the intervention report data, updating related tickets details, if needed */
	function save_data ()
	{
		if (!$this->created) $this->created = time ();
		parent::save_data ();
	}


	/** Sets the ticket details which belong to this intervention report
	* @param	array		$details		Array with the IDs of the ticket details which
	*							should be linked to this intervention report
	* @param	bool		$append			If True, then this list of IDs will be appended
	*							to the ticket. If False, then the list will replace
	*							all current detail IDs. Default is False.
	*/
	function set_details ($details = array (), $append = false)
	{
		if ($this->id and is_array ($details))
		{
			if (!$append)
			{
				$this->db_query ('UPDATE '.TBL_TICKETS_DETAILS.' SET intervention_report_id=0 WHERE intervention_report_id='.$this->id);
			}

			foreach ($details as $id)
			{
				$this->db_query ('UPDATE '.TBL_TICKETS_DETAILS.' SET intervention_report_id='.$this->id.' WHERE id='.$id);
			}
		}
	}

	/** Checks if the detail data is valid */
	function is_valid_data ()
	{
		$ret = true;

		if (!$this->subject) {error_msg ('Please specify the subject.'); $ret = false;}
		if (!$this->customer_id) {error_msg ('Please specify the customer.'); $ret = false;}

		return $ret;
	}


	/** Checks if the intervention report can be modified */
	function can_modify ()
	{
		$ret = true;

		if ($this->status != INTERVENTION_STAT_OPEN) $ret = false;

		return $ret;
	}

	/** Tells if a detail can be removed from an intervention report */
	function can_remove_detail ($detail_id)
	{
		$ret = false;

		if ($this->id and $detail_id)
		{
			$ret = true;
			if ($this->status != INTERVENTION_STAT_OPEN)
			{
				error_msg ('You can\'t remove details from intervention reports which are not open.');
				$ret = false;
			}
		}

		return $ret;
	}


	/** Removes a detail from the intervention report. Note that this means only
	* removing the link between the intervention report and the ticket details,
	* it doesn't mean actually deleting the ticket detail
	*/
	function remove_detail ($detail_id)
	{
		if ($this->id and $detail_id)
		{
			$q = 'UPDATE '.TBL_TICKETS_DETAILS.' SET intervention_report_id=0 WHERE id='.$detail_id;
			$this->db_query ($q);
		}
		return $ret;
	}


	/** Checks if the intervention report can be deleted */
	function can_delete ()
	{
		$ret = false;

		if ($this->id)
		{
			$ret = true;
			// Check if the intervention report is opened
			if ($this->status != INTERVENTION_STAT_OPEN)
			{
				error_msg ('Only open intervention reports can be deleted.');
				$ret = false;
			}
			$query = "select ticket_id from ".TBL_TICKETS_DETAILS." where intervention_report_id=".$this->id;
			$ids = db::db_fetch_vector($query);
			if(count($ids) > 0)
			{
			    error_msg('There are still tickets linked to this IR, delete them first.');
			    $ret=false;
			}
		}
		return $ret;
	}


	/** Deletes an intervention report, as well as all its references from ticket details */
	function delete ()
	{
		if ($this->id)
		{
			// Delete the references from the ticket details
			$q = 'UPDATE '.TBL_TICKETS_DETAILS.' SET intervention_report_id=0 WHERE intervention_report_id='.$this->id;
			$this->db_query ($q);

			// Delete the lines with intervention reports, if any
			$q = 'DELETE FROM '.TBL_INTERVENTION_REPORTS_DETAILS.' WHERE intervention_report_id='.$this->id;
			$this->db_query ($q);

			// Delete the intervention report itself
			parent::delete ();
		}
	}

	/** Checks if the intervention report has complete info, e.g. if all the details have the time and the
	* details set.
	* @param	bool		$raise_errors		If True, the function will also raise error messages through error_msg()
	* @return	bool					True or False if the intervention report has complete info.
	*/
	function has_complete_info ($raise_errors = false)
	{
		$ret = false;
		if ($this->id)
		{
			$ret = true;
			$times_ok = true;
			$users_ok = true;
			$users_erp_ok = true;

			// Check if all the details have the times set and if all users and actions have the ERP info set
			if (count($this->details) > 0)
			{
				for ($i=0; ($i<count($this->details) and $times_ok and $users_ok and $users_erp_ok); $i++)
				{
					// Check times - we allow details without time if they are marked as not billable
					if ($this->details[$i]->billable and (!$this->details[$i]->activity_id or $this->details[$i]->time_in<=0)) $times_ok = false;
					// Check user
					if ($this->details[$i]->user_id)
					{
						$usr = $this->details[$i]->user;
						if ($this->details[$i]->billable and (!$usr->erp_id or !$usr->erp_id_travel or !$usr->erp_id_service)) $users_erp_ok = false;
					}
					else $users_ok = false;
				}

				if (!$times_ok)
				{
					$ret = false;
					if ($raise_errors) error_msg ('WARNING: Not all the details have the times and/or action type set.');
				}

				if (!$users_ok)
				{
					$ret = false;
					if ($raise_errors) error_msg ('WARNING: Not all the details have the users set.');
				}
				if (!$users_erp_ok)
				{
					$ret = false;
					if ($raise_errors) error_msg ('WARNING: Not all the users have complete ERP info.');
				}
			}
			else
			{
				$ret = false;
				if ($raise_errors) error_msg ('WARNING: There are no details entered for this intervention report.');
			}

			// Check if the customer has a Mercator code
			$customer = new Customer ($this->customer_id);
			if (!$customer->erp_id)
			{
				$ret = false;
				if ($raise_errors) error_msg ('WARNING: The customer doesn\'t exist in ERP system.');
			}
		}

		return $ret;
	}


	/**
	* Perform the operation of closing the intervention report and generating the associated InterventionReportDetail objects.
	* Note that this method also saves the object to the database.
	* Furthermore, note that the billable amount, billable flag, action type etc. can be further edit (and recalculated)
	* event after intervention is closed.
	* @param	int		$user_id		The ID of the user who is performing the import.
	* @param	bool		$simulation		If True, then the invoicing lines are created only in $this->lines,
	*							without being actually saved to database; the intervention report
	*							itself is not modified either.
	*/
	function close_intervention_report ($user_id = 0, $simulation = false)
	{
		if ($this->id and $this->status == INTERVENTION_STAT_OPEN and $user_id)
		{
			$this->lines = array ();
			if (!$simulation)
			{
				// Mark on the intervention report that has been closed
				//$this->approved_date = time ();
				//$this->approved_by_id = $user_id;
				$this->status = INTERVENTION_STAT_CLOSED;
				$this->save_data ();
			}

			// Just in case, delete any conflicting data that there might be in database
			$q = 'DELETE FROM '.TBL_INTERVENTION_REPORTS_DETAILS.' WHERE intervention_report_id='.$this->id;
			$this->db_query ($q);

			// Summarize for invoicing the intervention details
			$q = 'SELECT count(*) as cnt, d.activity_id, d.user_id, from_days(to_days(from_unixtime(d.time_in))) as intervention_date, ';
			$q.= 'd.customer_order_id, sum(d.work_time) as work_time, d.billable, d.location_id, ';
			$q.= 'sum(if(d.billable=1,d.work_time,0)) as bill_amount, ';
			$q.= 'sum(if(d.billable=1 AND d.is_continuation=0,1,0)) as cnt_fixed_billable, '; // The fixed-price actions which are neither billable nor continuations
			$q.= 'group_concat(d.id) as ticket_detail_ids '; // Keep track of the ticket details from which each line comes from
			$q.= 'FROM '.TBL_TICKETS_DETAILS.' d LEFT OUTER JOIN '.TBL_ACTION_TYPES.' t ON d.activity_id=t.id ';
			$q.= 'WHERE d.intervention_report_id='.$this->id.' ';
			$q.= 'GROUP BY intervention_date, d.activity_id, d.user_id, d.customer_order_id, d.location_id, d.billable ';
			$q.= 'ORDER BY intervention_date DESC, t.erp_code, d.user_id ';
			$lines = $this->db_fetch_array ($q);

			foreach ($lines as $line)
			{
				$detail = new InterventionReportDetail ();
				$detail->intervention_report_id = $this->id;
				$detail->intervention_date = strtotime($line->intervention_date);
				$detail->action_type_id = $line->activity_id;
				$detail->billable = $line->billable;
				$detail->location_id = $line->location_id;

				$detail->user_id = $line->user_id;
				$detail->customer_order_id = $line->customer_order_id;
				$detail->for_subscription = $line->for_subscription;
				$detail->work_time = $line->work_time;
				$detail->action_type = new ActionType ($detail->action_type_id);
				$detail->user = new User ($detail->user_id);
				if ($detail->customer_order_id) $detail->customer_order = new CustomerOrder ($detail->customer_order_id);
				if ($detail->action_type->price_type == PRICE_TYPE_HOURLY)
				{
					//$detail->bill_amount = ceil ($line->bill_amount/60) * 60;
					$detail->bill_amount = ceil ($line->bill_amount/$detail->action_type->billing_unit) * $detail->action_type->billing_unit;
					$detail->tbb_amount = $detail->bill_amount;
				}
				else
				{
					$detail->bill_amount = $line->cnt_fixed_billable;
					$detail->tbb_amount = $detail->bill_amount;
				}
				$detail->ticket_detail_ids = split(',', $line->ticket_detail_ids);
				if (!$simulation) $detail->save_data ();

				$this->lines[] = $detail;
			}

			// Calculate how many activities have been done on-site
			// There will be one travel cost per day per engineer and per location.
			// We include travel costs even for non-billable items, following Serge's request.
			$q = 'SELECT count(*) as cnt, sum(if(td.billable=1,1,0)) as cnt_billable, ';
			$q.= 'min(td.time_in) as time_in, td.user_id, ';
			$q.= 'td.location_id, to_days(from_unixtime(time_in)) as day ';
			$q.= 'FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_INTERVENTION_LOCATIONS.' l ';
			$q.= 'ON td.location_id = l.id ';
			$q.= 'WHERE td.intervention_report_id='.$this->id.' AND l.on_site=1 ';//AND td.billable=1 ';
			$q.= 'GROUP BY td.user_id, td.location_id, day ORDER BY td.user_id';
			$travels = $this->db_fetch_array ($q);

			for ($i=0; $i<count($travels); $i++)
			{
				$detail = new InterventionReportDetail ();
				$detail->intervention_report_id = $this->id;
				$detail->intervention_date = get_first_hour($travels[$i]->time_in);
				$detail->user_id = $travels[$i]->user_id;
				$detail->user = new User ($detail->user_id);
				$detail->location_id = $travels[$i]->location_id;
				$detail->location = new InterventionLocation($detail->location_id);

				$action_type = ActionType::get_user_travel_cost ($detail->user_id);
				if ($action_type->id)
				{
					$detail->action_type_id = $action_type->id;
					$detail->action_type = new ActionType ($detail->action_type_id);
				}
				$detail->work_time = 0;
				$detail->bill_amount = 1; // One travel cost per day per engineer and per location
				// If there was at least one billable ticket detail, make this travel billable
				if ($travels[$i]->cnt_billable > 0) $detail->tbb_amount = 1;
				else $detail->tbb_amount = 0;

				if (!$simulation) $detail->save_data ();

				$this->lines[] = $detail;
			}
		}
	}


	function recheck_travel_lines ()
	{
		if ($this->id)
		{
			// Calculate how many activities have been done on-site
			// There will be one travel cost per day per engineer and per location.
			// The procedure is simlar with the one from close_intervention_report()
			// We include travel costs even for non-billable items, following Serge's request
			$q = 'SELECT count(*) as cnt, sum(if(td.billable=1,1,0)) as cnt_billable, ';
			$q.= 'min(td.time_in) as time_in, td.user_id, ';
			$q.= 'td.location_id, to_days(from_unixtime(time_in)) as day ';
			$q.= 'FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_INTERVENTION_LOCATIONS.' l ';
			$q.= 'ON td.location_id = l.id ';
			$q.= 'WHERE td.intervention_report_id='.$this->id.' AND l.on_site=1 ';//AND td.billable=1 ';
			$q.= 'GROUP BY td.user_id, td.location_id, day ORDER BY td.user_id';
			$travels = $this->db_fetch_array ($q);

			$travel_details = array ();
			for ($i=0; $i<count($travels); $i++)
			{
				// These details will not be actually saved to the database, unless it is
				// determined that it is a new travel line which should be added to the IR, e.g.
				// when another invoicing line is changed from not billable to billable.
				$detail = new InterventionReportDetail ();
				$detail->intervention_report_id = $this->id;
				$detail->intervention_date = get_first_hour($travels[$i]->time_in);
				$detail->user_id = $travels[$i]->user_id;
				$detail->user = new User ($detail->user_id);
				$detail->location_id = $travels[$i]->location_id;
				$detail->location = new InterventionLocation($detail->location_id);

				$action_type = ActionType::get_user_travel_cost ($detail->user_id);
				if ($action_type->id)
				{
					$detail->action_type_id = $action_type->id;
					$detail->action_type = new ActionType ($detail->action_type_id);
				}
				$detail->work_time = 0;
				$detail->bill_amount = 1;
				if ($travels[$i]->cnt_billable > 0) $detail->tbb_amount = 1;
				else $detail->tbb_amount = 0;

				$travel_details[] = $detail;
			}

			// Now compare each of the travel details in the IR with the ones in $travel_details
			$to_delete = array (); 	// Indexes of current travel lines which should be deleted
			$to_add = array (); 	// Indexes with found travels. It will be initialized with all the ones found above,
						// and then the ones already existing will be removed from array. Whatever is left are added to IR
			for ($i=0; $i<count($travel_details); $i++) $to_add[$i] = $i;

			for ($i=0; $i<count($this->lines); $i++)
			{
				$line = &$this->lines[$i];
				if ($line->action_type->special_type == ACTYPE_SPECIAL_TRAVEL)
				{
					$found = false;
					for ($j=0; $j<count($travel_details) and !$found; $j++)
					{
						$det = &$travel_details[$j];
						if ($line->user_id==$det->user_id and $line->intervention_date==$det->intervention_date and $line->location_id==$det->location_id)
						{
							$found = true;
							unset($to_add[$j]);
						}
					}
					if (!$found) $to_delete[] = $i;
				}
			}

			foreach ($to_delete as $idx) $this->lines[$idx]->delete ();
			foreach ($to_add as $idx)
			{
				$travel_details[$idx]->save_data ();
				$this->lines[] = $travel_details[$idx];
			}
		}
	}

	/** Performs the operation of re-opening a closed intervention report. This implies
	* changing the intervention report status and deleting all the previously generated
	* detail lines for invoicing. Note that this method also saves the object to the
	* database.
	* @param	$int		$user_id		The ID of the user who re-opened the intervention report
	*/
	function reopen_intervention_report ($user_id = 0)
	{
		if ($this->id and $this->status == INTERVENTION_STAT_CLOSED and $user_id)
		{
			// Delete the detail lines and the ticket IDs references
			$this->db_query ('DELETE FROM '.TBL_INTERVENTION_REPORTS_DETAILS.' WHERE intervention_report_id='.$this->id);
			$q = 'DELETE FROM '.TBL_INTERVENTION_REPORTS_DETAILS_IDS.' USING '.TBL_INTERVENTION_REPORTS_DETAILS_IDS.' ';
			$q.= 'LEFT OUTER JOIN '.TBL_INTERVENTION_REPORTS_DETAILS.' rd ';
			$q.= 'ON '.TBL_INTERVENTION_REPORTS_DETAILS_IDS.'.intervention_report_detail_id=rd.id WHERE rd.id IS NULL';
			$this->db_query ($q);

			$this->status = INTERVENTION_STAT_OPEN;
			$this->approved_by_id = 0;
			$this->approved_date = 0;
			$this->save_data ();
			$this->lines = array ();
		}
	}


	/** Perform the operation of approving an intervention report. Once approved, an
	* intervention report is ready for centralization by the ERP system. Note that this
	* object also saves the object to the database.
	* @param	$int		$user_id		The ID of the user who has done the approving
	*/
	function approve_intervention_report ($user_id = 0)
	{
		if ($this->id and $this->status == INTERVENTION_STAT_CLOSED and $user_id)
		{
			$this->status = INTERVENTION_STAT_APPROVED;
			$this->approved_by_id = $user_id;
			$this->approved_date = time ();
			$this->save_data ();
		}
	}


	/** Cancels the approval of the intervention report. This means reverting to "Closed" status,
	* without deleting the invoicing lines. Note that this method will save the object to the
	* database.
	* @param	$int		$user_id		The ID of the user who cancelled the approval
	*/
	function cancel_approval ($user_id = 0)
	{
		if ($this->id and $this->status == INTERVENTION_STAT_APPROVED and $user_id)
		{
			$this->status = INTERVENTION_STAT_CLOSED;
			$this->approved_by_id = 0;
			$this->approved_date = 0;
			$this->save_data ();
		}
	}

	/** Cancels the centralization status of the intervention report and reverts to "Approved" status */
	function cancel_centralization ()
	{
		if ($this->id and ($this->status == INTERVENTION_STAT_PENDING_CENTRALIZE or $this->status == INTERVENTION_STAT_CENTRALIZED))
		{
			$this->status = INTERVENTION_STAT_APPROVED;
			$this->save_data ();
		}
	}


	/** [Class Method] Returns a list of intervention reports according to some critera
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- ticket_id: Return all intervention reports which are linked to this ticket
	*							- customer_id: Return all intervention reports for this customer
	*							- status: Only return intervention reports of this status
	*							- show_ids: If true, then the subject of the tickets will be
	*							  prefixed with the intervention report ID.
	* @return	array					Associative array, they keys being intervention report IDs and
	*							the values being intervention report subjects.
	*/
	public static function get_interventions_list ($filter = array())
	{
		$ret = array ();

		$q = 'SELECT DISTINCT i.id, i.subject FROM '.TBL_INTERVENTION_REPORTS.' i ';
		if ($filter['ticket_id'])
		{
			$q.= 'INNER JOIN '.TBL_TICKETS_DETAILS.' td ON i.id=td.intervention_report_id ';
		}
		$q.= 'WHERE ';

		if ($filter['ticket_id']) $q.= 'td.ticket_id='.$filter['ticket_id'].' AND ';
		if ($filter['customer_id']) $q.= 'i.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['status']) $q.= 'i.status='.$filter['status'].' AND ';

		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY i.created DESC ';

		$ret = DB::db_fetch_list ($q);

		if ($filter['show_ids'])
		{
			foreach ($ret as $id=>$subject)
			{
				$ret[$id] = '[#'.$id.'] '.$ret[$id];
			}
		}

		return $ret;
	}


	/** [Class Method] Returns intervention reports according to some criteria
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	*							- customer_id: Return only interventions for this customer
	*							- status: Return only interventions of the specified status
	*							- search_text: Search intervention reports with the specified text in the subject or comments of
	*							  the intervention report
	*							- user_id: Return only intervention reports which have details
	*							  entered by this user.
	* @param	int		$cnt			(By reference) If set, it will be loaded with the total
	* 							number of intervention reports that matched the criteria.
	*/
	public static function get_interventions ($manager, $filter = array (), &$cnt)
	{
		$ret = array ();


		// Unless a specific customer and a specific status was requested, limit by default the results
		if (!$filter['customer_id'] and !$filter['status'])
		{
			if (!$filter['start']) $filter['start'] = 0;
			if (!$filter['limit']) $filter['limit'] = 50;
		}

		$q = 'FROM '.TBL_INTERVENTION_REPORTS.' i ';
		if($manager) $q.= 'INNER JOIN '.TBL_CUSTOMERS.' c on c.id=i.customer_id ';
		if ($filter['user_id']) $q.= 'INNER JOIN '.TBL_TICKETS_DETAILS.' td ON i.id=td.intervention_report_id ';
		$q.= 'WHERE ';

		/*if ($filter['customer_id'] and (!filter['']))
		{
			$q.= 'i.customer_id='.$filter['customer_id'].' AND ';
		}*/

        $current_user = $GLOBALS['CURRENT_USER'];
		if($manager) $q.='c.account_manager='.$manager.' AND ';
		if($current_user and $current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $current_user->get_assigned_customers_list();
			$q.= 'i.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$q.=") AND ";
		}

		if($filter['customer_ids'])
		{
			if(!is_array($filter['customer_ids'])) $filter['customer_ids'] = array($filter['customer_ids']);
			$tot_cust = count($filter['customer_ids']);
			$q.='i.customer_id in (';
			for($i=0; $i<$tot_cust; $i++)
			{
				$q.=$filter['customer_ids'][$i];
				//if($i!=$tot_cust-1)
				$q.=", ";
				//if($i==$tot_cust-1) $q.=") ";
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$q.=") AND ";
		}
		if ($filter['status']) $q.= 'i.status='.$filter['status'].' AND ';
		if ($filter['user_id']) $q.= 'td.user_id='.$filter['user_id'].' AND ';
		if ($filter['search_text']) $q.= '(i.subject like "%'.db::db_escape($filter['search_text']).'%" OR i.comments like "%'.db::db_escape($filter['search_text']).'%") AND ';

		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);

		if (isset($cnt))
		{
			$q_cnt = 'SELECT count(DISTINCT i.id) AS cnt '.$q;
			$cnt = DB::db_fetch_field ($q_cnt, 'cnt');
		}

		$q = 'SELECT DISTINCT i.id '.$q.' ORDER BY i.created DESC ';
		if ($filter['limit']) $q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];

		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new InterventionReport ($id);

		return $ret;
	}


	/** [Class Method] Returns the total number of intervention reports grouped by status
	* @return	array					Associative array, keys being status codes and the values being totals
	*/
	public static function get_totals ()
	{
		$ret = array ();

		$q = 'SELECT status, count(*) FROM '.TBL_INTERVENTION_REPORTS.' GROUP BY status ORDER BY status';
		$ret = DB::db_fetch_list ($q);
		return $ret;
	}

	/**
	 * [Class Method] gets a list of IR's groupped by their status
	 *
	 * @param array(mixed) $filter	- 	possible values are customer_id
	 * @return array(mixed)
	 * */
	public static function get_interventions_list_by_status($filter = array())
	{
		$q = "select distinct status from ".TBL_INTERVENTION_REPORTS;
		$stats = db::db_fetch_vector($q);
		$ret = array();
		foreach($stats as $stat)
		{
			$query = "select id, subject from ".TBL_INTERVENTION_REPORTS." where status=".$stat;
			if(isset($filter['customer_id'])) $query.=" AND customer_id=".$filter['customer_id'];
			$tl = db::db_fetch_list($query);
			if(count($tl) > 0)
				$ret[$stat] = $tl;
		}
		return $ret;		
	}
    
    public static function get_monthly_intervention_stats($customer_id)
    {
        $ret = array();
        $query = 'select min(created) as min_crd from '.TBL_INTERVENTION_REPORTS.' where created > 0';
        if($customer_id){
            $query .= " AND customer_id=".$customer_id;
        }
        $min_crd = db::db_fetch_field($query, 'min_crd');
        
        $mk = getdate($min_crd);
        $current = mktime(0,0,0,date("m"), date("d"), date("Y"));
        $last_date = mktime(0,0,0, $mk['mon'], 1, $mk['year']);
        while($last_date < $current){
            $ld = getdate($last_date);
            $ret['months'][] =  date('M Y', $last_date);
            $end_date = mktime(0,0,0, $ld['mon']+1, 1, $ld['year']);    
            
            $query = "select count(id) as cnt from ".TBL_INTERVENTION_REPORTS." where created < ".$end_date." AND created >=".$last_date." AND status=".INTERVENTION_STAT_CENTRALIZED;
            if($customer_id){
                $query .= " AND customer_id=".$customer_id;
            } 
            $ret['centralized'][] = intval(db::db_fetch_field($query, 'cnt'));
            
             $query = "select count(id) as cnt from ".TBL_INTERVENTION_REPORTS." where created < ".$end_date." AND created >=".$last_date." AND status=".INTERVENTION_STAT_OPEN;
            if($customer_id){
                $query .= " AND customer_id=".$customer_id;
            } 
            $ret['open'][] = intval(db::db_fetch_field($query, 'cnt'));
                    
            $query = "select count(id) as cnt from ".TBL_INTERVENTION_REPORTS." where created < ".$end_date." AND created >=".$last_date." AND status=".INTERVENTION_STAT_CLOSED;
            if($customer_id){
                $query .= " AND customer_id=".$customer_id;
            } 
            $ret['closed'][] = intval(db::db_fetch_field($query, 'cnt'));
                    
            $query = "select id from ".TBL_INTERVENTION_REPORTS." where created < ".$end_date." AND created >=".$last_date." AND status=".INTERVENTION_STAT_CENTRALIZED;
            if($customer_id){
                $query .= " AND customer_id=".$customer_id;
            } 
            $ids = db::db_fetch_vector($query);
            $sx = 0;
            foreach($ids as $id){
                $intervention = new InterventionReport($id);
                $sx += $intervention->tbb_amount;
            }
            $ret["billed_hours"][] = $sx;
                    
            $last_date = $end_date;
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
}
?>

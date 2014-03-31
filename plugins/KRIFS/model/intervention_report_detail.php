<?php
class_load ('InterventionReport');
class_load ('InterventionLocation');
class_load ('CustomerOrder');

/**
* Class for storing detail lines for the intervention report
*
* The detail lines for intervention reports store the summaries (by action type) of the
* activities performed for an intervention report. The are generated when the intervention
* report is closed and can be modified by users with proper permissions to store a
* "to be billed" time which is different from the "billable" time which was calcultated
* automatically by the system.
*
* The "billable" time is generating by adding the work time for each action type present
* in the intervention report - for hourly priced items. For fixed-price items, the "billable"
* and "to be billed" fields will actually store an amount of actions performed, and not a
* duration.
*
* The detail lines for an intervention report are generated when the intervention report
* is closed. This operation is performed inside the corresponding InterventionReport
* object.
*
* Besides the table TBL_INTERVENTION_REPORTS_DETAILS, the class also uses the
* TBL_INTERVENTION_REPORTS_DETAILS_IDS for storing the IDS of the ticket details from
* which each invoicing line comes.
*
*/

class InterventionReportDetail extends Base
{
	/** Intervention report detail ID
	* @var int */
	var $id = null;

	/** The ID of the intervention report to which this detail belongs
	* @var int */
	var $intervention_report_id = null;

	/** The ID of the action type
	* @var int */
	var $action_type_id = null;

	/** The total work amounts (in minutes) from which this line was generated - including non-billable details
	* @var int */
	var $work_time = 0;

	/** Specifies if this invoicing line is actually billable
	* @var bool */
	var $billable = true;

	/** The "billable" amount calculated by the system. It can be a duration (minutes)
	* or an amount of items - depending on the action type
	* @var int */
	var $bill_amount = 0;

	/** The "to be billed" amount specified by the user. By default it is the same
	* as the "billable" amount. It can contain a duration (minutes) or an amount of items,
	* depending on action type
	* @var int */
	var $tbb_amount = 0;

	/** The date (day) on which this action was performed
	* @var time */
	var $intervention_date = 0;

	/** The ID of the user who performed the activity
	* @var int */
	var $user_id = null;

	/** The customer order to which this activity is linked (if any)
	* @var int */
	var $customer_order_id = 0;

	/** Specifies if this activity is linked to a subscription or not
	* @var bool */
	var $for_subscription = false;

	/** Specifies the ID of the location (if any)
	* @var int */
	var $location_id = null;


	/** The ActionType object for this intervention report line
	* @var ActionType */
	var $action_type = null;

	/** The customer order for this intervention report line, if any
	* @var CustomerOrder */
	var $customer_order = null;

	/** The location object, if any
	* @var InterventionLocation */
	var $location = null;

	/** The ticket detail IDs from which this invervention comes from. This
	* is generated and saved into the database when the intervention report is closed.
	* @var array(int) */
	var $ticket_detail_ids = array ();


	var $table = TBL_INTERVENTION_REPORTS_DETAILS;
	var $fields = array ('id', 'intervention_report_id', 'action_type_id', 'work_time', 'billable', 'bill_amount', 'tbb_amount', 'intervention_date', 'user_id', 'customer_order_id', 'for_subscription', 'location_id');


	/**
	* Constructor. Also loads the data if an ID has been specified
	* @param	int	$id		The ID of the object to load
	*/
	function InterventionReportDetail ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}


	/** Load the object's data */
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data ();
			if ($this->action_type_id) $this->action_type = new ActionType ($this->action_type_id);
			if ($this->user_id) $this->user = new User ($this->user_id);
			if ($this->customer_order_id) $this->customer_order = new CustomerOrder ($this->customer_order_id);
			if ($this->location_id) $this->location = new InterventionLocation ($this->location_id);

			// Load the list of ticket detail IDs for this invoicing line
			$q = 'SELECT ticket_detail_id FROM '.TBL_INTERVENTION_REPORTS_DETAILS_IDS.' WHERE ';
			$q.= 'intervention_report_detail_id='.$this->id.' ORDER BY 1';
			$this->ticket_detail_ids = $this->db_fetch_vector ($q);
		}
	}


	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		if (!$this->action_type_id) {error_msg('Please specify a valid action type'); $ret = false;}

		return $ret;
	}

	/** Save the object data, together with the list of ticket detail IDs (if defined) */
	function save_data ()
	{
		parent::save_data ();
		if ($this->id)
		{
			// Delete existing IDs
			$q = 'DELETE FROM '.TBL_INTERVENTION_REPORTS_DETAILS_IDS.' WHERE intervention_report_detail_id='.$this->id;
			$this->db_query ($q);

			// Save the new list
			if (count($this->ticket_detail_ids) > 0)
			{
				$q = 'INSERT INTO '.TBL_INTERVENTION_REPORTS_DETAILS_IDS.' VALUES ';
				foreach ($this->ticket_detail_ids as $id) $q.= '('.$this->id.', '.$id.'), ';
				$q = preg_replace ('/,\s*$/', '', $q);
				$this->db_query ($q);
			}
		}
	}


	/** Updates the billable flag and the action type in all linked ticket details. */
	function update_ticket_details ()
	{
		if ($this->id and count($this->ticket_detail_ids)>0)
		{
			// Update each linked ticket detail
			$details = array ();
			$price_type_changed = false;
			foreach ($this->ticket_detail_ids as $id)
			{
				$detail = new TicketDetail ($id);
				if ($this->action_type->price_type != $detail->action_type->price_type) $price_type_changed = true;
				$detail->billable = $this->billable;
				$detail->activity_id  = $this->action_type_id;
				$detail->save_data ();
				$detail->load_data ();
				$details[] = $detail;
			}
			// Now recalculate the billing and billable amounts (in case the action type changed)
			if (!$this->billable) $this->bill_amount = 0;
			else
			{
				$bill_amount = 0;
				if ($detail->action_type->price_type == PRICE_TYPE_HOURLY)
				{
					foreach ($details as $detail) $bill_amount+= $detail->work_time;
					$this->bill_amount = ceil ($bill_amount/$detail->action_type->billing_unit) * $detail->action_type->billing_unit;
				}
				else
				{
					foreach ($details as $detail) if (!$detail->is_continuation) $bill_amount++;
					$this->bill_amount = $bill_amount;
				}
			}

			// If the price type has changed, copy the billable amount into TBB amount
			if ($price_type_changed) $this->tbb_amount = $this->bill_amount;

			// Save the object
			$this->save_data ();
		}
	}


	/** Returns the work time represented in hours */
	function get_work_time_hours ()
	{
		return round ($this->work_time/60, 2);
	}


	/** Returns the billable amount in hours if this is an hourly priced item. For fix priced items,
	* it returns the billable amount unchanged.*/
	function get_bill_amount_hours ()
	{
		$ret = $this->bill_amount;
		if ($this->action_type->price_type == PRICE_TYPE_HOURLY)
		{
			//$ret = round ($this->bill_amount/60, 2);
			$ret = round ($this->bill_amount/$this->action_type->billing_unit);
		}
		return $ret;
	}

	/** Returns the TBB amount in hours if this is an hourly priced item. For fix priced items,
	* it returns the TBB amount unchanged.*/
	function get_tbb_amount_hours ()
	{
		$ret = $this->tbb_amount;
		if ($this->action_type->price_type == PRICE_TYPE_HOURLY)
		{
			//$ret = round ($this->tbb_amount/60, 2);
			$ret = round ($this->tbb_amount/$this->action_type->billing_unit, 2);
		}
		return $ret;
	}
}
?>
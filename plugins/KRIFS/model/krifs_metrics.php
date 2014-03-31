<?php 

class_load ('Ticket');

/**
* Class for obtaining various KRIFS metrics.
*
* All methods are class methods, there should be no need to instantiate objects of this class.
*
*/

class KrifsMetrics
{
	/** Returns the date of the oldest ticket in the database (either open or closed) */
	function get_oldest_date ()
	{
		return (DB::db_fetch_field('SELECT min(created) as min FROM '.TBL_TICKETS.' WHERE created>0', 'min'));
	}
	
	/****************************************************************/
	/* Users metrics						*/
	/****************************************************************/
	
	/** Get per-user count of assigned tickets, counting or not tickets of "ignored" types */
	function get_assigned_tickets_count ($count_ignored_types = true, $user_id = null)
	{
		if ($count_ignored_types)
		{
			$q = 'SELECT assigned_id, count(t.id) AS cnt FROM '.TBL_TICKETS.' t ';
			$q.= 'INNER JOIN '.TBL_TICKETS_TYPES.' types on t.type=types.id ';
			$q.= 'WHERE t.status<>'.TICKET_STATUS_CLOSED.' AND types.ignore_count<>1 ';
			if ($user_id) $q.= 'AND t.assigned_id='.$user_id.' ';
			$q.= 'GROUP BY 1';
		}
		else
		{
			$q = 'SELECT assigned_id, count(id) AS cnt FROM '.TBL_TICKETS.'  ';
			$q.= 'WHERE status<>'.TICKET_STATUS_CLOSED.' ';
			if ($user_id) $q.= 'AND assigned_id='.$user_id.' ';
			$q.= 'GROUP BY 1';
		}
		return DB::db_fetch_list ($q);
	}
	
	function get_tot_tickets_times($filter)
	{
		$q = "select sum(td.work_time) as work_time, sum(td.bill_time) as bill_time, sum(td.billable) as billable, sum(td.tbb_time) as TBB from ".TBL_TICKETS_DETAILS." td inner join ".TBL_TICKETS." t on td.ticket_id=t.id where t.customer_id = ".$filter['customer_id']." and td.created between ".$filter['d_start']." and ".$filter['d_end'];
		$ret =  db::db_fetch_row($q);
		return $ret;
	}
	
	function get_tot_irs_times($filter)
	{
		$q = "select sum(ird.work_time) as work_time, sum(ird.bill_amount) as bill_amount, sum(ird.tbb_amount) as TBB, sum(ird.billable) as billable from ".TBL_INTERVENTION_REPORTS." ir inner join ".TBL_INTERVENTION_REPORTS_DETAILS." ird on ir.id = ird.intervention_report_id and ir.customer_id = ".$filter['customer_id']." and ird.intervention_date between ".$filter['d_start']." and ".$filter['d_end'];
		$ret = db::db_fetch_row($q);
		return $ret;
	}
	
	/** Get the oldest open ticket for each user */
	function get_oldest_tickets_dates ($user_id = null)
	{
		$q = 'SELECT assigned_id, min(created) as oldest FROM '.TBL_TICKETS.' ';
		$q.= 'WHERE status<>'.TICKET_STATUS_CLOSED.' GROUP BY 1';
		return DB::db_fetch_list ($q);
	}
	
	/** Get the number of ticket details created by the user in the interval */
	function get_created_tickets_details ($date_start, $date_end)
	{
		$ret = array ();
		
		$date_start = get_first_hour ($date_start);
		$date_end = get_last_hour ($date_end);
		
		$q = 'SELECT user_id, count(*) AS cnt FROM '.TBL_TICKETS_DETAILS.' WHERE ';
		$q.= 'created>='.$date_start.' AND created<='.$date_end.' ';
		$q.= 'GROUP BY user_id';
		return DB::db_fetch_list ($q);
	}
	
	/** Get the number of tickets closed by the user in the interval */
	function get_closed_tickets ($date_start, $date_end, &$tot_closed_tickets)
	{
		$ret = array ();
		
		$date_start = get_first_hour ($date_start);
		$date_end = get_last_hour ($date_end);
		
		$q = 'SELECT count(distinct td.ticket_id) AS cnt FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id=t.id ';
		$q.= 'WHERE td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' AND t.status='.TICKET_STATUS_CLOSED;
		$tot_closed_tickets = DB::db_fetch_field ($q, 'cnt');
		
		$q = 'SELECT td.user_id, count(distinct td.ticket_id) AS cnt FROM ';
		$q.= TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id=t.id WHERE ';
		$q.= 'td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' AND t.status='.TICKET_STATUS_CLOSED.' ';
		$q.= 'GROUP BY user_id';
		return DB::db_fetch_list ($q);
	}
	
	/** Get the total of work times (minutes) for each user */
	function get_work_times ($date_start, $date_end)
	{
		$ret = array ();
		
		$date_start = get_first_hour ($date_start);
		$date_end = get_last_hour ($date_end);
		
		$q = 'SELECT user_id, sum(work_time) AS tot FROM '.TBL_TICKETS_DETAILS.' WHERE ';
		$q.= 'time_in>='.$date_start.' AND time_in<='.$date_end.' ';
		$q.= 'GROUP BY user_id';
		return DB::db_fetch_list ($q);
	}
	
	function get_total_work_times($date_start, $date_end)
	{
		class_load('Timesheet');
		$wts = KrifsMetrics::get_work_times($date_start, $date_end);
		$timesheets = array();
		foreach($wts as $uid=>$wt)
		{
			$um = KrifsMetrics::get_user_metrics($uid, $date_start, $date_end);
			$uts = array();
			foreach($um as $m)
			{
				$uts[$m->date] = Timesheet::get_timesheet($uid, $m->date);
				if (!$uts[$m->date]->id) {
				 	$uts[$m->date]->load_unassigned_details ();
				}
			}
			$total_wt = 0;
			foreach($uts as $ts)
			{
				$total_wt+=$ts->get_work_time();
			}
			$timesheets[$uid] = $total_wt;
		}
		return $timesheets;
	}
	
	
	/** Get average ticket closing time (in seconds) per user, counting tickets closed in the interval */
	function get_average_closing_time ($date_start, $date_end)
	{
		$ret = array ();
		$date_start = get_first_hour ($date_start);
		$date_end = get_last_hour ($date_end);
		
		$q = 'SELECT td.user_id, t.id, from_unixtime(t.created), from_unixtime(td.created) ';
		$q.= 'FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id = t.id ';
		$q.= 'WHERE ';
		$q.= 'td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' ';
		$q.= 'AND td.user_id=2';
		
		
		
		$q = 'SELECT td.user_id, round(avg(td.created-t.created)) AS avg ';
		$q.= 'FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id = t.id ';
		$q.= 'WHERE ';
		$q.= 'td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' ';
		$q.= 'GROUP BY user_id';
		
		$q = 'SELECT td.user_id, round(avg(td.created-t.created)) AS avg ';
		$q.= 'FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id = t.id ';
		$q.= 'WHERE ';
		$q.= 'td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' ';
		$q.= 'GROUP BY user_id';
		
		$q = 'SELECT td.user_id, td.ticket_id, round((max(td.created)-t.created)/60) AS duration ';
		$q.= 'FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id=t.id WHERE ';
		$q.= 'td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' AND t.status='.TICKET_STATUS_CLOSED.' ';
		$q.= 'GROUP BY user_id, td.ticket_id ORDER BY td.user_id, td.ticket_id ';
		
		$last_user_id = 0;
		$h = DB::db_query ($q);
		while ($d = DB::db_get_next($h))
		{
			if ($d->user_id != $last_user_id)
			{
				if ($last_user_id) $ret[$last_user_id] = intval($tot_time / $cnt)*60;
				$tot_time = 0;
				$cnt = 0;
				$last_user_id = $d->user_id;
			}
			$tot_time+= intval($d->duration);
			$cnt++;
		}
		if ($last_user_id) $ret[$last_user_id] = intval($tot_time / $cnt)*60;
		
		return $ret;
	}
	
	
	function get_user_metrics ($user_id, $date_start, $date_end)
	{
		$q = 'SELECT week(from_unixtime(td.time_in)) as week, from_days(to_days(from_unixtime(td.time_in))) as date, ';
		$q.= 'count(distinct td.id) as cnt_ticket_details, count(distinct td.ticket_id) as cnt_tickets, ';
		$q.= 'sum(if(td.status='.TICKET_STATUS_CLOSED.' AND t.status='.TICKET_STATUS_CLOSED.',1,0)) as cnt_tickets_closed, ';
		$q.= 'sum(work_time) as work_time, group_concat(distinct t.id," ") as tickets, group_concat(distinct c.name," ") as customers ';
		$q.= 'FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id=t.id ';
		$q.= 'INNER JOIN '.TBL_CUSTOMERS.' c ON t.customer_id=c.id ';
		$q.= 'WHERE td.user_id='.$user_id.' AND td.time_in>='.$date_start.' AND td.time_in<='.$date_end.' ';
		$q.= 'GROUP BY 2 ORDER BY 2 DESC ';
		$ret = DB::db_fetch_array ($q);
		
		for ($i=0; $i<count($ret); $i++) $ret[$i]->date = strtotime($ret[$i]->date);
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Customers metrics						*/
	/****************************************************************/
	
	/** Get per-customer count of open tickets, counting or not tickets of "ignored" types */
	function get_cust_open_tickets_count ($count_ignored_types = true)
	{
		if ($count_ignored_types)
		{
			$q = 'SELECT customer_id, count(DISTINCT t.id) AS cnt FROM '.TBL_TICKETS.' t ';
			$q.= 'INNER JOIN '.TBL_TICKETS_TYPES.' types on t.type=types.id ';
			$q.= 'WHERE t.status<>'.TICKET_STATUS_CLOSED.' AND types.ignore_count<>1 ';
			$q.= 'GROUP BY 1';
		}
		else
		{
			$q = 'SELECT customer_id, count(id) AS cnt FROM '.TBL_TICKETS.'  ';
			$q.= 'WHERE status<>'.TICKET_STATUS_CLOSED.' ';
			$q.= 'GROUP BY 1';
		}
		return DB::db_fetch_list ($q);
	}
	
	/** Returns the number of created tickets, per customer, in the given interval */
	function get_cust_created_tickets ($date_start, $date_end)
	{
		$ret = array ();
		
		$date_start = get_first_hour ($date_start);
		$date_end = get_last_hour ($date_end);
		
		$q = 'SELECT customer_id, count(id) AS cnt FROM '.TBL_TICKETS.' WHERE ';
		$q.= 'created>='.$date_start.' AND created<='.$date_end.' ';
		$q.= 'GROUP BY customer_id';
		return DB::db_fetch_list ($q);
	}
	
	/** Get the number of tickets closed for the customer in the given interval  */
	function get_cust_closed_tickets ($date_start, $date_end, &$tot_closed_tickets)
	{
		$ret = array ();
		
		$date_start = get_first_hour ($date_start);
		$date_end = get_last_hour ($date_end);
		
		// Count all the closed tickets
		$q = 'SELECT count(distinct td.ticket_id) AS cnt FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id=t.id ';
		$q.= 'WHERE td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' AND t.status='.TICKET_STATUS_CLOSED;
		$tot_closed_tickets = DB::db_fetch_field ($q, 'cnt');
		
		// Count closed tickets per customer
		$q = 'SELECT t.customer_id, count(distinct td.ticket_id) AS cnt FROM ';
		$q.= TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id=t.id WHERE ';
		$q.= 'td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' AND t.status='.TICKET_STATUS_CLOSED.' ';
		$q.= 'GROUP BY t.customer_id';
		return DB::db_fetch_list ($q);
	}
	
	/** Get the oldest open ticket for each customer */
	function get_cust_oldest_tickets_dates ()
	{
		$q = 'SELECT customer_id, min(created) as oldest FROM '.TBL_TICKETS.' ';
		$q.= 'WHERE status<>'.TICKET_STATUS_CLOSED.' GROUP BY 1';
		return DB::db_fetch_list ($q);
	}
	
	/** Get average ticket closing time (in seconds) per customers, counting tickets closed in the interval */
	function get_cust_average_closing_time ($date_start, $date_end)
	{
		$ret = array ();
		$date_start = get_first_hour ($date_start);
		$date_end = get_last_hour ($date_end);
		
		$q = 'SELECT t.customer_id, td.ticket_id, round((max(td.created)-t.created)/60) AS duration ';
		$q.= 'FROM '.TBL_TICKETS_DETAILS.' td INNER JOIN '.TBL_TICKETS.' t ON td.ticket_id=t.id WHERE ';
		$q.= 'td.created>='.$date_start.' AND td.created<='.$date_end.' AND td.status='.TICKET_STATUS_CLOSED.' AND t.status='.TICKET_STATUS_CLOSED.' ';
		$q.= 'GROUP BY t.customer_id, td.ticket_id ORDER BY td.user_id, td.ticket_id ';
		
		$last_customer_id = 0;
		$h = DB::db_query ($q);
		while ($d = DB::db_get_next($h))
		{
			if ($d->customer_id != $last_customer_id)
			{
				if ($last_customer_id) $ret[$last_customer_id] = intval($tot_time / $cnt)*60;
				$tot_time = 0;
				$cnt = 0;
				$last_customer_id = $d->customer_id;
			}
			$tot_time+= intval($d->duration);
			$cnt++;
		}
		if ($last_customer_id) $ret[$last_customer_id] = intval($tot_time / $cnt)*60;
		
		return $ret;
	}
	
	/** Returns the number of tickets by customer and by status */
	function get_cust_tickets_stats ()
	{
		$ret = array ();
		$tmp_stats = array ();
		foreach ($GLOBALS ['TICKET_STATUSES'] as $stat_code => $stat_name) $tmp_stats[$stat_code] = 0;
		
		$q = 'SELECT customer_id, status, count(DISTINCT id) as cnt FROM '.TBL_TICKETS.' WHERE status<>'.TICKET_STATUS_CLOSED.' ';
		$q.= 'GROUP BY 1, 2';
		$data = DB::db_fetch_array ($q);
		
		foreach ($data as $d)
		{
			if (!isset($ret[$d->customer_id])) $ret[$d->customer_id] = $tmp_stats;
			$ret[$d->customer_id][$d->status] = $d->cnt;
		}
		
		return $ret;
	}
}
?>
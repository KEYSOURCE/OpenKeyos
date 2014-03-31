{assign var="paging_titles" value="Krifs Metrics"}
{assign var="paging_urls" value="/krifs_metrics"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script type="text/javascript" src="/javascript/jquery.min.js"></script>
<script language="JavaScript" src="/javascript/highcharts/highcharts.js" type="text/javascript"></script>
<script type="text/javascript" src="/javascript/highcharts/modules/exporting.js"></script>
<script type="text/javascript" src="/javascript/jsdatepick/jquery.1.4.2.js"></script>
<script type="text/javascript" src="/javascript/jsdatepick/jsDatePick.jquery.min.1.3.js"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

var dates_start = new Array ();
var dates_end = new Array ();
{foreach from=$predefined_intervals key=idx item=interval}
	dates_start[{$idx}] = '{$interval.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR}';
	dates_end[{$idx}] = '{$interval.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}';
{/foreach}

{literal}

function loadPresetInterval ()
{
	frm = document.forms['data_frm'];
	lst = frm.elements['preset_intervals'];
	c_idx = lst.options[lst.selectedIndex].value;
	
	if (c_idx != '')
	{
		frm.elements['filter[date_start]'].value = dates_start[c_idx];
		frm.elements['filter[date_end]'].value = dates_end[c_idx];
		frm.submit ();
	}
}

{/literal}
//]]>
</script>

<h1>Krifs Metrics</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="data_frm">
{$form_redir}

<table>
	<tr>
		<td nowrap="nowrap">
			<b>View by:</b>
			{if !$customer_admin}
			<select name="filter[view_by]" onchange="document.forms['data_frm'].submit();">
				<option value="users">Users</option>
				<option value="customers" {if $filter.view_by=='customers'}selected{/if}>Customers</option>
			</select>
                            {if $filter.view_by=='customers'}
                            <select name="filter[customer_id]">
                                <option value="0">All</option>
                                {foreach from=$full_customers_list key='id' item='name'}
                                    {if isset($open_tickets.$id)}
                                    <option value="{$id}" {if $filter.customer_id==$id}selected{/if}>{$name}</option>
                                    {/if}
                                {/foreach}
                            </select>
                            {/if}
			{else}
			<select name="filter[view_by]" onchange="document.forms['data_frm'].submit();">				
				<option value="customers" {if $filter.view_by=='customers'}selected{/if}>Customers</option>
			</select>
			{/if}
			&nbsp;&nbsp;&nbsp;
			
			<b>Interval:</b>
			
			<input type="text" size="12" name="filter[date_start]" 
			value="{$filter.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" />
			{literal}
			<a href="#" onclick="showCalendarSelector('data_frm', 'filter[date_start]'); return false;" name="anchor_calendar" id="anchor_calendar"
			><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
			&nbsp;&nbsp;-&nbsp;&nbsp;
			<input type="text" size="12" name="filter[date_end]" 
			value="{$filter.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" />
			{literal}
			<a href="#" onclick="showCalendarSelector('data_frm', 'filter[date_end]'); return false;" name="anchor_calendar" id="anchor_calendar"
			><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" name="save" value="Apply &#0187;" class="button" />
		</td>
		<td align="right" nowrap="nowrap">&nbsp;&nbsp;&nbsp;
                        <select name="preset_intervals" onchange="loadPresetInterval()">
				<option value="">-- Predefined intervals --</option>
				{foreach from=$predefined_intervals key=idx item=interval}
					<option value="{$idx}" {if $interval.selected}selected{/if}>{$interval.name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
</table>
</form>
<p/>

{if $filter.view_by=='users'}
	<!-- Show users metrics -->
	Click on a user name to get more information.
	<table class="list" width="800">
		<thead>
		<tr>
			<td width="180">User</td>
			<td align="right" width="120" colspan="2">Currently assigned tickets</td>
			<td align="right" width="80">Created tickets details</td>
			<td align="right" width="80">Closed tickets</td>
			<td align="right" width="80">Work time<br/>(hh:mm)</td>
			<td align="right" width="100">Timesheet work time<br/>(hh:mm)</td>
			<td align="right" width="100">Oldest open<br/>ticket date</td>
			<td align="right" width="130">Average closing time<br/>(days h:m:s)</td>
		</tr>
		</thead>
		
		{foreach from=$users item=user}
		
		{assign var="user_id" value=$user->id}
		{assign var="user_name" value=$user->get_name()}
		{if $user->is_active() or (!$user->is_active() and isset($assigned_tickets.$user_id))}
		<tr>
			<td class="highlight">
				<a href="/?cl=krifs_metrics&amp;op=metrics_user&amp;user_id={$user_id}&amp;date_start={$filter.date_start}&amp;date_end={$filter.date_end}">{$user_name|escape}</a>
				{if !$user->is_active()}<br/><i>[Inactive]</i>{/if}
				{if $user->customer_id}<br/><i>[{$user->customer->name} ({$user->customer_id})]</i>{/if}
			</td>
			<td align="right">
				{if $assigned_tickets_ignored.$user_id}
					<a href="/?cl=krifs&amp;op=manage_tickets&amp;user_id={$user_id}&amp;view=2&amp;customer_id=-1&amp;escalated_only=0&type=0&types_main_only=0"
					>{$assigned_tickets_ignored.$user_id}</a>
				{else}-
				{/if}
			</td>
			<td align="right">
				{if $assigned_tickets.$user_id}
					<a href="/?cl=krifs&amp;op=manage_tickets&amp;user_id={$user_id}&amp;view=2&amp;customer_id=-1&amp;escalated_only=0&type=0&types_main_only=1"
					>{$assigned_tickets.$user_id}</a>
				{else}-
				{/if}
			</td>
			<td align="right">
				{if $created_tds.$user_id}{$created_tds.$user_id}
				{else}-
				{/if}
			</td>
			<td align="right">
				{if $closed_tickets.$user_id}{$closed_tickets.$user_id}
				{else}-
				{/if}
			</td>
			<td align="right">
				{if $work_times.$user_id}{$work_times.$user_id|format_interval_minutes}
				{else}-
				{/if}
			</td>
			<td align="right">
				{if $total_work_times.$user_id}{$total_work_times.$user_id|format_interval_minutes}
				{else}-
				{/if}
			</td>
			<td align="right" nowrap="nowrap">
				{if $oldest_tickets_dates.$user_id}{$oldest_tickets_dates.$user_id|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}
				{else}-
				{/if}
			</td>
			<td align="right">
				{if $avg_closing_times.$user_id}{$avg_closing_times.$user_id|format_interval}
				{else}-
				{/if}
			</td>
		</tr>
		{/if}
		{/foreach}
		
		<tr class="head">
			<td>TOTAL</td>
			<td align="right">{$tot_assigned_tickets_ignored}</td>
			<td align="right">{$tot_assigned_tickets}</td>
			<td align="right">{$tot_created_tds}</td>
			<td align="right">{$tot_closed_tickets}</td>
			<td align="right">{$tot_work_times|format_interval_minutes}</td>
			<td align="right">{$tt_wt|format_interval_minutes}</td>
			<td align="right" nowrap="nowrap">
				{if $oldest_ticket_date<time()}
					{$oldest_ticket_date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}
				{else} -
				{/if}
			</td>
			<td align="right" nowrap="nowrap">
				{if $avg_closing_time}{$avg_closing_time|format_interval}
				{else} -
				{/if}
			</td>
		</tr>
	</table>

<div id="chart_container" style="height: 600px;"></div>

{literal}
<script type="text/javascript">
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'chart_container'
      },
      title: {
         text: 'Metrics'
      },
      xAxis: {
         categories: [{/literal}{$chart_categories}{literal}],
         labels: {
            rotation: -45,
            align: 'right',
            style: {
                font: 'normal 13px Verdana, sans-serif'
            }
         }
      }, yAxis: [{
         labels: {
            formatter: function() {
               return this.value;
            },
            style: {
               color: '#89A54E'
            }
         },
         title: {
            text: 'Units',
            style: {
               color: '#89A54E'
            }
         },

      }, {
         labels: {
            formatter: function() {
               return this.value + ' minutes';
            },
            style: {
               color: '#89A54E'
            }
         },
         title: {
            text: 'Minutes',
            style: {
               color: '#89A54E'
            }
         },
         opposite: true

      }],
      tooltip: {
         formatter: function() {
            var s;
            if(this.point.unit) {
               s = ''+
                  this.x  +': '+ this.y + ' ' + this.point.unit;
            } else {
                s = ''+
                  this.x  +': '+ this.y;
            }
            return s;
         }
      },
      series: [{
         type: 'column',
         name: 'Created ticket details',
         data: [{/literal}{$chart_created_tds}{literal}],
         yAxis: 0
      }, {
         type: 'column',
         name: 'Closed tickets',
         data: [{/literal}{$chart_closed_tickets}{literal}],
         yAxis: 0
      }, {
         type: 'spline',
         name: 'Work times',
         data: [{/literal}{$chart_work_times}{literal}],
         yAxis: 1
      }, {
         type: 'spline',
         name: 'Timesheet work times',
         data: [{/literal}{$chart_tt_work_times}{literal}],
         yAxis: 1
      }]
   });
});
</script>
{/literal}

<div id="chart_container2" style="height: 500px;"></div>

{literal}
<script type="text/javascript">
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'chart_container2'
      },
      title: {
         text: 'Metrics'
      },
      xAxis: {
         categories: [{/literal}{$chart_categories}{literal}]
      },
      tooltip: {
         formatter: function() {
            var s;
            if (this.point.name) { // the pie chart
               s = ''+
                  this.point.name +': '+ this.point.hours +' (hh:mm)';
            } else {
                s = ''+
                  this.x  +': '+ this.y;
            }
            return s;
         }
      },
      labels: {
         items: [{
            html: 'Work times',
            style: {
               left: '190px',
               top: '0px',
               color: 'black',
               fontSize: '22px'
            }
         },{
            html: 'Timesheet work times',
            style: {
               left: '700px',
               top: '0px',
               color: 'black',
               fontSize: '22px'
            }
         }]
      },
      plotOptions: {
         pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
               enabled: true
            },
            showInLegend: false
         }
      },
      series: [{
         type: 'pie',
         name: 'Work times',
         data: [{/literal}{$chart_pie_data1}{literal}],
         center: [250, 220],
         size: 300
         //showInLegend: true,
         /*dataLabels: {
            enabled: false
         }*/
      }, {
         type: 'pie',
         name: 'Timesheet work times',
         data: [{/literal}{$chart_pie_data2}{literal}],
         center: [800, 220],
         size: 300
         //showInLegend: false,
         /*dataLabels: {
            enabled: false
         }*/
      }]
   });
});
</script>
{/literal}
	
	<b>NOTES:</b>
	<ul>
		<li>For assigned tickets, the second column does't count the tickets of 'ignore in counts' types.</li>
		<li>Also for assigned tickets, note that the totals for each user does NOT take into account the tickets assigned through groups.</li>
		<li>The total number of closed tickets might be lower than the sum of user totals, since a ticket might be closed, re-opened and then closed again by more users. Also, there might be tickets which were closed by customer users.</li>
		<li>The average closing time refers to tickets closed in the specified interval.</li>
	</ul>

{else}

	<!-- Show customers metrics -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="10" rowspan="2">ID</td>
			<td rowspan="2">Customer</td>
			<td colspan="2" rowspan="2" align="right">Currently<br/>open tickets</td>
			<td align="right" rowspan="2">Created tickets</td>
			<td align="right" rowspan="2">Closed tickets</td>
			<td align="right" rowspan="2">Oldest ticket</td>
			<td align="right" rowspan="2" nowrap="nowrap">Closing time<br/>(days h:m:s)</td>
			<td colspan="{$TICKET_STATUSES|@count}">Number of tickets by status</td>
			{if $current_user->is_manager}<td colspan=4>Billable info</td>{/if}
		</tr>
		<tr>
			{foreach from=$TICKET_STATUSES key=stat_id item=stat_name}
			{if $stat_id!=$smarty.const.TICKET_STATUS_CLOSED}
				<td width="20" style="font-weight:400" align="right">{$stat_name|escape}</td>
			{/if}
			{/foreach}
			{if $current_user->is_manager}<td colspan=4 align="center">From Tickets/<font color="blue">IRs</font></td>{/if}
		</tr>
		<tr>
			<td colspan="2">TOTAL:</td>
			<td align="right">{$tot_open_tickets_ignored}</td>
			<td align="right">{$tot_open_tickets}</td>
			<td align="right">
				{if $tot_created_tickets}{$tot_created_tickets}
				{else}-{/if}
			</td>
			<td align="right">
				{if $tot_closed_tickets}{$tot_closed_tickets}
				{else}-{/if}
			</td>
			<td align="right">{$oldest_ticket_date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}</td>
			<td align="right" nowrap="nowrap">
				{if $avg_closing_time}{$avg_closing_time|format_interval}
				{else}-{/if}
			</td>
			
			{foreach from=$TICKET_STATUSES key=stat_id item=stat_name}
			{if $stat_id!=$smarty.const.TICKET_STATUS_CLOSED}
				<td align="right">{$tot_stats.$stat_id|escape}</td>
			{/if}
			{/foreach}
			{if $current_user->is_manager}
			<td align="right">TWT</td>
			<td align="right">Bill time</td>
			<td align="right">Billable</td>
			<td align="right">TBB</td>
			{/if}
		</tr>
		</thead>
		
		{foreach from=$customers_list key=customer_id item=customer_name}
		{if isset($open_tickets.$customer_id)}
		<tr>
			<td class="highlight">
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customer_id}</a>
			</td>
			<td class="highlight">
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customer_name|escape}</a>
			</td>
			<td align="right">
				{if $open_tickets_ignored.$customer_id}
					<a href="/?cl=krifs&amp;op=manage_tickets&amp;customer_id={$customer_id}&amp;status=-1&amp;user_id=0&amp;escalated_only=0"
					>{$open_tickets_ignored.$customer_id}</a>
				{else}-{/if}
			</td>
			<td align="right">
				{if $open_tickets.$customer_id}
					<a href="/?cl=krifs&amp;op=manage_tickets&amp;customer_id={$customer_id}&amp;status=-1&amp;user_id=0&amp;escalated_only=0"
					>{$open_tickets.$customer_id}</a>
				{else}-{/if}
			</td>
			<td align="right">
				{if $created_tickets.$customer_id}{$created_tickets.$customer_id}
				{else}-{/if}
			</td>
			<td align="right">
				{if $closed_tickets.$customer_id}{$closed_tickets.$customer_id}
				{else}-{/if}
			</td>
			<td align="right" nowrap="nowrap">
				{if $oldest_tickets_dates.$customer_id}
					{$oldest_tickets_dates.$customer_id|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}
				{else}-{/if}
			</td>
			<td align="right" nowrap="nowrap">
				{if $avg_closing_times.$customer_id}{$avg_closing_times.$customer_id|format_interval}
				{else}-
				{/if}
			</td>
			{foreach from=$tickets_by_status.$customer_id key=stat_id item=cnt}
			{if $stat_id!=$smarty.const.TICKET_STATUS_CLOSED}
			<td align="right" title="{$TICKET_STATUSES.$stat_id}">
				{if $cnt}
					<a href="/?cl=krifs&amp;op=manage_tickets&amp;customer_id={$customer_id}&amp;status={$stat_id}&amp;user_id=0&amp;escalated_only=0"
					>{$cnt}</a>
				{else}-{/if}
			</td>
			{/if}
			{/foreach}
			{if $current_user->is_manager}
			{assign var="cwt" value=$values.$customer_id}
			{assign var="cwt_irs" value=$values_irs.$customer_id}
			<td align="right">{$cwt.work_time|format_interval_minutes}<b>/</b><font color="blue">{$cwt_irs.work_time|format_interval_minutes}</font></td>
			<td align="right">{$cwt.bill_time|format_interval_minutes}<b>/</b><font color="blue">{$cwt_irs.bill_amount|format_interval_minutes}</font></td>
			<td align="right">{$cwt.billable}<b>/</b><font color="blue">{$cwt_irs.billable}</font></td>
			<td align="right">{$cwt.TBB|format_interval_minutes}<b>/</b><font color="blue">{$cwt_irs.TBB|format_interval_minutes}</font></td>
			{/if}
		</tr>
	
		{/if}
		
		{/foreach}
		{if !$filter.customer_id}
		<tr class="head">
			<td colspan="2" rowspan="2">TOTAL:</td>
			<td align="right" rowspan="2">{$tot_open_tickets_ignored}</td>
			<td align="right" rowspan="2">{$tot_open_tickets}</td>
			<td align="right" rowspan="2">
				{if $tot_created_tickets}{$tot_created_tickets}
				{else}-{/if}
			</td>
			<td align="right" rowspan="2">
				{if $tot_closed_tickets}{$tot_closed_tickets}
				{else}-{/if}
			</td>
			<td align="right" rowspan="2">{$oldest_ticket_date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}</td>
			<td align="right" rowspan="2" nowrap="nowrap">
				{if $avg_closing_time}{$avg_closing_time|format_interval}
				{else}-{/if}
			</td>
			{foreach from=$TICKET_STATUSES key=stat_id item=stat_name}
			{if $stat_id!=$smarty.const.TICKET_STATUS_CLOSED}
				<td align="right">{$tot_stats.$stat_id|escape}</td>
			{/if}
			{/foreach}
		</tr>
		<tr class="head">
			{foreach from=$TICKET_STATUSES key=stat_id item=stat_name}
			{if $stat_id!=$smarty.const.TICKET_STATUS_CLOSED}
				<td style="font-weight:400" align="right">{$stat_name|escape}</td>
			{/if}
			{/foreach}
		</tr>
                {/if}
	</table>
	<p/>
	
	<b>NOTES:</b>
	<ul>
		<li>For open tickets, the second column does't count the tickets of 'ignore in counts' types.</li>
		<li>The average closing time refers to tickets closed in the specified interval.</li>
	</ul>
{/if}

<p/>
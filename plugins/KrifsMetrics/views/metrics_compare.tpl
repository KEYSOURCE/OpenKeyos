{assign var="paging_titles" value="Krifs Comparative Metrics"}
{assign var="paging_urls" value="/?cl=krifs_metrics"}
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

var compare_dates_start = new Array ();
var compare_dates_end = new Array ();
{foreach from=$compare_predefined_intervals key=idx item=interval}
	compare_dates_start[{$idx}] = '{$interval.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR}';
	compare_dates_end[{$idx}] = '{$interval.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}';
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

function loadComparePresetInterval ()
{
	frm = document.forms['compare_data_frm'];
	lst = frm.elements['compare_preset_intervals'];
	c_idx = lst.options[lst.selectedIndex].value;

	if (c_idx != '')
	{
		frm.elements['filter[compare_date_start]'].value = compare_dates_start[c_idx];
	 	frm.elements['filter[compare_date_end]'].value = compare_dates_end[c_idx];
		frm.submit ();
	}
}

{/literal}
//]]>
</script>

<h1>Krifs Comparative Metrics</h1>

<p class="error">{$error_msg}</p>

<table style="width: 100%;">
    <tr>
        <td width="45%">
            <form action="" method="POST" name="data_frm">
            {$form_redir}

            <table style="margin-right: 10px;">
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

                                    <b>Interval:</b>

                                    <input type="text" size="12" name="filter[date_start]"
                                    value="{$filter.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" />
                                    {literal}
                                    <a href="#" onclick="showCalendarSelector('data_frm', 'filter[date_start]'); return false;" name="anchor_calendar" id="anchor_calendar"
                                    ><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
                                    {/literal}
                                    <input type="text" size="12" name="filter[date_end]"
                                    value="{$filter.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" />
                                    {literal}
                                    <a href="#" onclick="showCalendarSelector('data_frm', 'filter[date_end]'); return false;" name="anchor_calendar" id="anchor_calendar"
                                    ><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
                                    {/literal}
                                    <input type="submit" name="save" value="Apply &#0187;" class="button" />
                            </td>
                    </tr>
                    <tr>
                            <td align="right" nowrap="nowrap">
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
        </td>
        {if $filter.view_by=='users'}
        <td width="45%">
            <form action="" method="POST" name="compare_data_frm">
            {$form_redir}

            <table style="margin-left: 10px;">
                    <tr>
                            <td nowrap="nowrap">
                                    <b>Compare interval:</b>

                                    <input type="text" size="12" name="filter[compare_date_start]"
                                    value="{$filter.compare_date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" />
                                    {literal}
                                    <a href="#" onclick="showCalendarSelector('compare_data_frm', 'filter[compare_date_start]'); return false;" name="compare_anchor_calendar" id="compare_anchor_calendar"
                                    ><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
                                    {/literal}
                                    <input type="text" size="12" name="filter[compare_date_end]"
                                    value="{$filter.compare_date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" />
                                    {literal}
                                    <a href="#" onclick="showCalendarSelector('compare_data_frm', 'filter[compare_date_end]'); return false;" name="compare_anchor_calendar" id="compare_anchor_calendar"
                                    ><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
                                    {/literal}
                                    <input type="submit" name="save" value="Apply &#0187;" class="button" />
                            </td>
                    </tr>
                    <tr>
                            <td align="right" nowrap="nowrap">
                                    <select name="compare_preset_intervals" onchange="loadComparePresetInterval()">
                                            <option value="">-- Predefined intervals --</option>
                                            {foreach from=$compare_predefined_intervals key=idx item=interval}
                                                    <option value="{$idx}" {if $interval.selected}selected{/if}>{$interval.name}</option>
                                            {/foreach}
                                    </select>
                            </td>
                    </tr>
            </table>
            </form>
        </td>
        {/if}
    </tr>
</table>

{if $filter.view_by=='users'}
{literal}
<script type="text/javascript">
    var last_tab = '#initial_tab';
    $(document).ready(function() {
       $('.tablink').click(function() {
           $(last_tab).parent().addClass('tab_inactive');
           $(this).parent().removeClass('tab_inactive')

           $($(last_tab).attr('href')).hide();
           $($(this).attr('href')).show();

           last_tab = '#' + $(this).attr('id');
           return false;
       });
    });
</script>
{/literal}
<table class="tab_header">
    <tr>
        <td width="160"><a id="initial_tab" href="#tabtable" style="width: 160px;" class="tablink">Show table comparison</a></td>
        <td class="tab_inactive"><a id="second_tab" href="#tabcharts" class="tablink">Show charts</a></td>
    </tr>
</table>
<!-- Show users metrics -->
<div id="tabtable" class="tab_content">
<table style="width: 100%">
    <tr>
        <td width="45%">
            <table class="list" style="margin-right: 10px;">
                        <thead>
                        <tr>
                                <td width="180">User</td>
                                <td align="right" width="80">Created tickets details</td>
                                <td align="right" width="80">Closed tickets</td>
                                <td align="right" width="80">Work time<br/>(hh:mm)</td>
                                <td align="right" width="100">Timesheet work time<br/>(hh:mm)</td>
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
                        </tr>
                        {/if}
                        {/foreach}

                        <tr class="head">
                                <td>TOTAL</td>
                                <td align="right">{$tot_created_tds}</td>
                                <td align="right">{$tot_closed_tickets}</td>
                                <td align="right">{$tot_work_times|format_interval_minutes}</td>
                                <td align="right">{$tt_wt|format_interval_minutes}</td>
                        </tr>
                </table>
        </td>
        <td width="45%" style="border-left: 1px #666666 solid;">
            <table class="list" style="margin-left: 10px;">
                        <thead>
                        <tr>
                                {*<td width="180">User</td>*}
                                <td align="right" width="80">Created tickets details</td>
                                <td align="right" width="80">Closed tickets</td>
                                <td align="right" width="80">Work time<br/>(hh:mm)</td>
                                <td align="right" width="100">Timesheet work time<br/>(hh:mm)</td>
                        </tr>
                        </thead>

                        {foreach from=$users item=user}

                        {assign var="user_id" value=$user->id}
                        {assign var="user_name" value=$user->get_name()}
                        {if $user->is_active() or (!$user->is_active() and isset($assigned_tickets.$user_id))}
                        <tr>
                                {*<td class="highlight">
                                        <a href="/?cl=krifs_metrics&amp;op=metrics_user&amp;user_id={$user_id}&amp;date_start={$filter.date_start}&amp;date_end={$filter.date_end}">{$user_name|escape}</a>
                                        {if !$user->is_active()}<br/><i>[Inactive]</i>{/if}
                                        {if $user->customer_id}<br/><i>[{$user->customer->name} ({$user->customer_id})]</i>{/if}
                                </td>*}
                                <td align="right">
                                        {if $compare_created_tds.$user_id}{$compare_created_tds.$user_id}
                                        {else}-
                                        {/if}
                                </td>
                                <td align="right">
                                        {if $compare_closed_tickets.$user_id}{$compare_closed_tickets.$user_id}
                                        {else}-
                                        {/if}
                                </td>
                                <td align="right">
                                        {if $compare_work_times.$user_id}{$compare_work_times.$user_id|format_interval_minutes}
                                        {else}-
                                        {/if}
                                </td>
                                <td align="right">
                                        {if $compare_total_work_times.$user_id}{$compare_total_work_times.$user_id|format_interval_minutes}
                                        {else}-
                                        {/if}
                                </td>
                        </tr>
                        {/if}
                        {/foreach}

                        <tr class="head">
                                <td align="right">{$compare_tot_created_tds}</td>
                                <td align="right">{$compare_tot_closed_tickets}</td>
                                <td align="right">{$compare_tot_work_times|format_interval_minutes}</td>
                                <td align="right">{$compare_tt_wt|format_interval_minutes}</td>
                        </tr>
                </table>
        </td>
    </tr>
</table>
</div>

<div id="tabcharts" class="tab_content" style="display: none;">
<div id="chart_container" style="width: 85%"></div>
<br />

{literal}
<script type="text/javascript">
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'chart_container'
      },
      title: {
         text: 'Metrics: Created ticket details comparation'
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
      }, 
      tooltip: {
         formatter: function() {
            var s;
            s = ''+ this.x  +': '+ this.y + ' created ticket detail(s)';
            return s;
         }
      },
      series: [{
         type: 'spline',
         name: 'Period: {/literal}{$filter.date_start|date_format:"%d %b %Y"} - {$filter.date_end|date_format:"%d %b %Y"}{literal}',
         data: [{/literal}{$chart_created_tds}{literal}]
      }, {
         type: 'spline',
         name: 'Period: {/literal}{$filter.compare_date_start|date_format:"%d %b %Y"} - {$filter.compare_date_end|date_format:"%d %b %Y"}{literal}',
         data: [{/literal}{$chart_compare_created_tds}{literal}]
      }]
   });
});
</script>
{/literal}

<div id="chart_container_2" style="width: 85%"></div>
<br />

{literal}
<script type="text/javascript">
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'chart_container_2'
      },
      title: {
         text: 'Metrics: Closed tickets'
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
      },
      tooltip: {
         formatter: function() {
            var s;
            s = ''+ this.x  +': '+ this.y + ' closed ticket(s)';
            return s;
         }
      },
      series: [{
         type: 'spline',
         name: 'Period: {/literal}{$filter.date_start|date_format:"%d %b %Y"} - {$filter.date_end|date_format:"%d %b %Y"}{literal}',
         data: [{/literal}{$chart_closed_tickets}{literal}]
      }, {
         type: 'spline',
         name: 'Period: {/literal}{$filter.compare_date_start|date_format:"%d %b %Y"} - {$filter.compare_date_end|date_format:"%d %b %Y"}{literal}',
         data: [{/literal}{$chart_compare_closed_tickets}{literal}]
      }]
   });
});
</script>
{/literal}

<div id="chart_container_3" style="width: 85%"></div>
<br />

{literal}
<script type="text/javascript">
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'chart_container_3'
      },
      title: {
         text: 'Metrics: Work time'
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
      },
      tooltip: {
         formatter: function() {
            var s;
            s = ''+ this.x  +': '+ this.y + ' minute(s)';
            return s;
         }
      },
      series: [{
         type: 'spline',
         name: 'Period: {/literal}{$filter.date_start|date_format:"%d %b %Y"} - {$filter.date_end|date_format:"%d %b %Y"}{literal}',
         data: [{/literal}{$chart_work_times}{literal}]
      }, {
         type: 'spline',
         name: 'Period: {/literal}{$filter.compare_date_start|date_format:"%d %b %Y"} - {$filter.compare_date_end|date_format:"%d %b %Y"}{literal}',
         data: [{/literal}{$chart_compare_work_times}{literal}]
      }]
   });
});
</script>
{/literal}

<div id="chart_container_4" style="width: 85%"></div>

{literal}
<script type="text/javascript">
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'chart_container_4'
      },
      title: {
         text: 'Metrics: Timesheet work time'
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
      },
      tooltip: {
         formatter: function() {
            var s;
            s = ''+ this.x  +': '+ this.y + ' minute(s)';
            return s;
         }
      },
      series: [{
         type: 'spline',
         name: 'Period: {/literal}{$filter.date_start|date_format:"%d %b %Y"} - {$filter.date_end|date_format:"%d %b %Y"}{literal}',
         data: [{/literal}{$chart_tt_work_times}{literal}]
      }, {
         type: 'spline',
         name: 'Period: {/literal}{$filter.compare_date_start|date_format:"%d %b %Y"} - {$filter.compare_date_end|date_format:"%d %b %Y"}{literal}',
         data: [{/literal}{$chart_compare_tt_work_times}{literal}]
      }]
   });
});
</script>
{/literal}
</div>

{else}
        {if $filter.customer_id}
            {literal}
            <script type="text/javascript">
                var last_tab = '#initial_tab';
                $(document).ready(function() {
                   $('.tablink').click(function() {
                       $(last_tab).parent().addClass('tab_inactive');
                       $(this).parent().removeClass('tab_inactive')

                       $($(last_tab).attr('href')).hide();
                       $($(this).attr('href')).show();

                       last_tab = '#' + $(this).attr('id');
                       return false;
                   });
                });
            </script>
            {/literal}
            <table class="tab_header">
                <tr>
                    <td width="160"><a id="initial_tab" href="#tabtable" style="width: 160px;" class="tablink">Show table comparison</a></td>
                    <td class="tab_inactive"><a id="second_tab" href="#tabcharts" class="tablink">Show charts</a></td>
                </tr>
            </table>
        {/if}
        <div class="tab_content" id="tabtable" style="display: block; width: 99%;">
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
        <br />
        {if $filter.customer_id}
        <form action="" method="POST" name="compare_data_frm">
            <input type="hidden" name="filter[customer_id]" value="{$filter.customer_id}"/>
            {$form_redir}

            <table style="margin-right: 10px;">
                    <tr>
                            <td nowrap="nowrap">
                                    <b>Compare:</b>
                                    {if !$customer_admin}
                                        {if $filter.view_by=='customers'}
                                        <select name="filter[compare_customer_id]">
                                            {foreach from=$full_customers_list key='id' item='name'}
                                                {if isset($open_tickets.$id)}
                                                <option value="{$id}" {if $filter.compare_customer_id==$id}selected{/if}>{$name}</option>
                                                {/if}
                                            {/foreach}
                                        </select>
                                        {/if}
                                    {/if}

                                    <b>Interval:</b>

                                    <input type="text" size="12" name="filter[compare_date_start]"
                                    value="{$filter.compare_date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" />
                                    {literal}
                                    <a href="#" onclick="showCalendarSelector('compare_data_frm', 'filter[compare_date_start]'); return false;" name="anchor_calendar" id="anchor_calendar"
                                    ><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
                                    {/literal}
                                    <input type="text" size="12" name="filter[compare_date_end]"
                                    value="{$filter.compare_date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" />
                                    {literal}
                                    <a href="#" onclick="showCalendarSelector('compare_data_frm', 'filter[compare_date_end]'); return false;" name="anchor_calendar" id="anchor_calendar"
                                    ><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
                                    {/literal}
                                    <input type="submit" name="save" value="Apply &#0187;" class="button" />
                            </td>
                    </tr>
                    <tr>
                            <td align="right" nowrap="nowrap">
                                    <select name="compare_preset_intervals" onchange="loadComparePresetInterval()">
                                            <option value="">-- Predefined intervals --</option>
                                            {foreach from=$compare_predefined_intervals key=idx item=interval}
                                                    <option value="{$idx}" {if $interval.selected}selected{/if}>{$interval.name}</option>
                                            {/foreach}
                                    </select>
                            </td>
                    </tr>
            </table>
            </form>
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
                            <td align="right">{$compare_tot_open_tickets_ignored}</td>
                            <td align="right">{$compare_tot_open_tickets}</td>
                            <td align="right">
                                    {if $compare_tot_created_tickets}{$compare_tot_created_tickets}
                                    {else}-{/if}
                            </td>
                            <td align="right">
                                    {if $compare_tot_closed_tickets}{$compare_tot_closed_tickets}
                                    {else}-{/if}
                            </td>
                            <td align="right">{$compare_oldest_ticket_date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}</td>
                            <td align="right" nowrap="nowrap">
                                    {if $compare_avg_closing_time}{$compare_avg_closing_time|format_interval}
                                    {else}-{/if}
                            </td>

                            {foreach from=$TICKET_STATUSES key=stat_id item=stat_name}
                            {if $stat_id!=$smarty.const.TICKET_STATUS_CLOSED}
                                    <td align="right">{$compare_tot_stats.$stat_id|escape}</td>
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

                    {foreach from=$compare_customers_list key=customer_id item=customer_name}
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
                                    {if $compare_created_tickets.$customer_id}{$compare_created_tickets.$customer_id}
                                    {else}-{/if}
                            </td>
                            <td align="right">
                                    {if $compare_closed_tickets.$customer_id}{$compare_closed_tickets.$customer_id}
                                    {else}-{/if}
                            </td>
                            <td align="right" nowrap="nowrap">
                                    {if $oldest_tickets_dates.$customer_id}
                                            {$oldest_tickets_dates.$customer_id|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}
                                    {else}-{/if}
                            </td>
                            <td align="right" nowrap="nowrap">
                                    {if $compare_avg_closing_times.$customer_id}{$compare_avg_closing_times.$customer_id|format_interval}
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
                            {assign var="cwt" value=$compare_values.$customer_id}
                            {assign var="cwt_irs" value=$compare_values_irs.$customer_id}
                            <td align="right">{$cwt.work_time|format_interval_minutes}<b>/</b><font color="blue">{$cwt_irs.work_time|format_interval_minutes}</font></td>
                            <td align="right">{$cwt.bill_time|format_interval_minutes}<b>/</b><font color="blue">{$cwt_irs.bill_amount|format_interval_minutes}</font></td>
                            <td align="right">{$cwt.billable}<b>/</b><font color="blue">{$cwt_irs.billable}</font></td>
                            <td align="right">{$cwt.TBB|format_interval_minutes}<b>/</b><font color="blue">{$cwt_irs.TBB|format_interval_minutes}</font></td>
                            {/if}
                    </tr>

                    {/if}

                    {/foreach}
            </table>
        </div>
        <div class="tab_content" id="tabcharts" style="display: none;">
            <div id="chart_container" style="height: 600px; width: 85%"></div>
            <div id="chart_container2" style="height: 600px; width: 85%"></div>

            {literal}
            <script type="text/javascript">
            var chart;
            $(document).ready(function() {
               chart = new Highcharts.Chart({
                  chart: {
                     renderTo: 'chart_container'
                  },
                  title: {
                     text: 'Customer Metrics'
                  },
                  xAxis: {
                     categories: [{/literal}{$chart_categories}{literal}]
                  },
                  tooltip: {
                     formatter: function() {
                        var s;
                        if(this.point.time) {
                            s = '' + this.point.name  +': '+ this.x + ' - ' + this.point.time + ' (hh:mm)';
                        } else if(this.point.name) {
                            s = '' + this.point.name  +': '+ this.y + ' ' + this.x;
                        } else {
                            s = ''+
                              this.x  +': '+ this.y;
                        }
                        return s;
                     }
                  },
                  series: [{
                     type: 'column',
                     name: '{/literal}{$chart_customer} (Period: {$filter.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}',
                     data: [{/literal}{$customer_data}{literal}]
                  }, {
                     type: 'column',
                     name: '{/literal}{$chart_compare_customer} (Period: {$filter.compare_date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.compare_date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}',
                     data: [{/literal}{$compare_customer_data}{literal}]
                  }]
               });
               {/literal}{if $filter.customer_id!=$filter.compare_customer_id}{literal}
                chart = new Highcharts.Chart({
                  chart: {
                     renderTo: 'chart_container2',
                  },
                  title: {
                     text: 'Tickets by status'
                  },
                  xAxis : {
                    categories: [
                        '{/literal}{$chart_customer} (Period: {$filter.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}',
                        '{/literal}{$chart_compare_customer} (Period: {$filter.compare_date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.compare_date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}'
                    ]
                  },
                  tooltip: {
                     formatter: function() {
                        return '<b>'+ this.point.name +'</b>: ' + this.point.val + ' ('+ this.y +' %)';
                     }
                  },
                  plotOptions: {
                     pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                           enabled: true,
                           formatter: function() {
                              return '<b>'+ this.point.name +'</b>: ' + this.point.val + ' ('+ this.y +' %)';
                           }
                        }
                     }
                  },
                  labels: {
                     items: [{
                        html: '{/literal}{$chart_customer} (Period: {$filter.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}',
                        style: {
                           left: '50px',
                           top: '75px',
                           color: 'black',
                           fontSize: '16px'
                        }
                     },{
                        html: '{/literal}{$chart_compare_customer} (Period: {$filter.compare_date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.compare_date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}',
                        style: {
                           left: '600px',
                           top: '75px',
                           color: 'black',
                           fontSize: '16px'
                        }
                     }]
                  },
                   series: [{
                     type: 'pie',
                     name: 'Browser share',
                     data: [{/literal}{$pie_data}{literal}],
                     center: [270, 250],
                     size: 250
                  },{
                     type: 'pie',
                     name: 'Browser share',
                     data: [{/literal}{$compare_pie_data}{literal}],
                     center: [800, 250],
                     size: 250
                  }]
               });
               {/literal}{else}{literal}
               chart = new Highcharts.Chart({
                  chart: {
                     renderTo: 'chart_container2',
                  },
                  title: {
                     text: '{/literal}{$chart_customer} (Period: {$filter.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}'
                  },
                  xAxis : {
                    categories: [
                        '{/literal}{$chart_customer} (Period: {$filter.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}'
                    ]
                  },
                  tooltip: {
                     formatter: function() {
                        return '<b>'+ this.point.name +'</b>: ' + this.point.val + ' ('+ this.y +' %)';
                     }
                  },
                  plotOptions: {
                     pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                           enabled: true,
                           formatter: function() {
                              return '<b>'+ this.point.name +'</b>: ' + this.point.val + ' ('+ this.y +' %)';
                           }
                        }
                     }
                  },
                   series: [{
                     type: 'pie',
                     name: '{/literal}{$chart_customer} (Period: {$filter.date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR} - {$filter.date_end|date_format:$smarty.const.DATE_FORMAT_SELECTOR}){literal}',
                     data: [{/literal}{$pie_data}{literal}]
                  }]
               });
               {/literal}{/if}{literal}
            });
            </script>
            {/literal}
        </div>
        {/if}
{/if}

<p/>
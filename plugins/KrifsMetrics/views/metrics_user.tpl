{assign var="paging_titles" value="Krifs Metrics, User Metrics"}
{assign var="paging_urls" value="/?cl=krifs_metrics"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<h1>Krifs Metrics: {$user->get_name()|escape}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="data_frm">
{$form_redir}

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

</form>
<p/>

<a href="/?cl=krifs_metrics">&#0171; Back to general metrics</a>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="100">Date</td>
		<td width="40" align="center">Week</td>
		<td width="40" align="right">Tickets details created</td>
		<td width="40" align="right">Tickets closed</td>
		<td width="50" align="right">Work time<br/>(hh:mm)</td>
		<td width="100" align="right">Timeheets work time<br/>(hh:mm)</td>
		<td>Tickets</td>
		<td>Customers</td>
	</tr>
	</thead>
	
	{foreach from=$user_metrics item=metric}
	{assign var="day" value=$metric->date}
	{assign var="timesheet" value=$timesheets.$day}
	<tr>
		<td class="highlight">{$metric->date|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
		<td align="center">{$metric->week}</td>
		<td align="right">
			{if $metric->cnt_ticket_details}{$metric->cnt_ticket_details}
			{else}-
			{/if}
		</td>
		<td align="right">
			{if $metric->cnt_tickets_closed}{$metric->cnt_tickets_closed}
			{else}-
			{/if}
		</td>
		<td align="right">
			{if $metric->work_time}{$metric->work_time|format_interval_minutes}
			{else}-
			{/if}
		</td>
		<td align="right">
			{if $timesheet->get_work_time()}{$timesheet->get_work_time()|format_interval_minutes}
			{else}-
			{/if}
		</td>
		<td>{$metric->tickets}</td>
		<td>{$metric->customers}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7">[No activity recorded]</td>
	</tr>
	{/foreach}
	
</table>
<p/>

<a href="/?cl=krifs_metrics">&#0171; Back to general metrics</a>
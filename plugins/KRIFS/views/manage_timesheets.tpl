{assign var="paging_titles" value="KRIFS, Manage Timesheets"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
// Overloading of setDateString in CalendarPopup.js, to allow updating
// the date fields after the calendar popup is selected
function setDateString (y,m,d)
{
	fels = document.forms['filter_frm'].elements;
	for (i = 0; i < fels.length; i++)
	{
		if (fels[i].name == elname)
		{
			el = fels[i];
		}
	}
	if (m < 10) m = '0'+m;
	el.value=d+"."+m+"."+y;

	document.forms['filter_frm'].submit();
}
{/literal}
//]]>
</script>

<h1>Manage Timesheets</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter_frm">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="30%">User</td>
		<td width="30%">Date</td>
		<td width="40%" align="right"> </td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="filter[user_id]" onchange="document.forms['filter_frm'].submit();">
				{html_options options=$users_list selected=$filter.user_id}
			</select>
		</td>
		<td nowrap="nowrap">
            {assign var="p" value="date:"|cat:$prev_date}
			<a href="{'krifs'|get_link:'manage_timesheets':$p:'template'}">&#0171; Prev</a>&nbsp;&nbsp;&nbsp;
			
			<input type="text" name="filter[date]" value="{$filter.date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" size="12"
				 onchange="document.forms['filter_frm'].submit();"
			/>
			{literal}
			<a HREF="#" onClick="showCalendarSelector('filter_frm', 'filter[date]', 'anchor_calendar'); return false;" 
				name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border="0" style="vertical-align: middle"/></a>
			{/literal}
			
			&nbsp;&nbsp;&nbsp;
            {assign var="p" value="date:"|cat:$next_date}
			<a href="{'krifs'|get_link:'manage_timesheets':$p:'template'}">Next &#0187;</a>
		</td>
		<td align="right" nowrap="nowrap">
			<a href="{'krifs'|get_time:'timesheets_filter'}">Filtered view &#0187;</a><br/>
			<a href="{'erp'|get_link:'manage_timesheets_exports'}">Exports &#0187;</a>
		</td>
	</tr>
</table>
</form>
<p/>

<table class="list" width="80%">
	<thead>
	<tr>
		<td colspan="2" width="18%">Date</td>
		<td width="9%">ID</td>
		<td width="18%">Status</td>
		<td width="25%">Defined time</td>
		<td width="30%">Total time</td>
	</tr>
	</thead>
	

	{foreach from=$timesheets key=day item=timesheet}
	<tr>
		<td width="1%" {if $day==$filter.date}style="font-weight:800"{/if} nowrap="nowrap">
			<a
				{if $timesheet->id}
                    {assign var="p" value="id:"|cat:$timesheet->id}
					href="{'krifs'|get_link:'timesheet_edit':$p:'template'}"
				{else}
                    {assign var="p" value='date:'|cat:$day|cat:',user_id:'|cat:$filter.user_id}
					href="{'krifs'|get_link:'timesheet_add':$p:'template'}"
				{/if}
			>{$day|date_format:"%a"}</a>			
		</td>
		<td {if $day==$filter.date}style="font-weight:800"{/if}>
			<a 
				{if $timesheet->id}
                    {assign var="p" value="id:"|cat:$timesheet->id}
					href="{'krifs'|get_link:'timesheet_edit':$p:'template'}"
				{else}
                    {assign var="p" value='date:'|cat:$day|cat:',user_id:'|cat:$filter.user_id}
					href="{'krifs'|get_link:'timesheet_edit':$p:'template'}"
				{/if}
			>{$day|date_format:$smarty.const.DATE_FORMAT_SMARTY}
			
			{if $day==$filter.date}&nbsp;&#0171;{/if}
			
			</a>
		</td>
		<td>
			{if $timesheet->id}
				[{$timesheet->id}]
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td {if !$timesheet->id}class="light_text"{/if}>
			{assign var="status" value=$timesheet->status}
			{$TIMESHEET_STATS.$status}
		</td>
		<td {if !$timesheet->id}class="light_text"{/if}>
			{assign var="defined_work_time" value=$timesheet->get_defined_work_time()}
			{if $defined_work_time}
				{$defined_work_time|@format_interval_minutes}
			{else}
				--
			{/if}
		</td>
		<td {if !$timesheet->id}class="light_text"{/if}>
			{assign var="work_time" value=$timesheet->get_work_time()}
			{if $work_time}
				{$work_time|@format_interval_minutes}
			{else}
				--
			{/if}
		</td>
	</tr>
	{/foreach}
</table>

<p>
<b>Note:</b> The difference between <b>Defined time</b> and <b>Total time</b>
is that <b>Total time</b> also takes into account ticket details which have
not yet been linked to the timesheet.</p>
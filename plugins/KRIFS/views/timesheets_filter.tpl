{assign var="paging_titles" value="KRIFS, Manage Timesheets - Filtered View"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<h1>Manage Timesheets - Filtered View</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter_frm">
{$form_redir}

<table class="list" width="80%">
	<thead>
	<tr>
		<td width="20%">User</td>
		<td width="20%">Status</td>
		<td width="10%">Per page</td>
		<td width="40%" align="right"> </td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="filter[user_id]" onchange="document.forms['filter_frm'].submit();">
				<option value="0">[All users]</option>
				{html_options options=$users_list selected=$filter.user_id}
			</select>
		</td>
		<td nowrap="nowrap">
			<select name="filter[status]" onchange="document.forms['filter_frm'].submit();">
				<option value="0">[All statuses]</option>
				{html_options options=$TIMESHEET_STATS selected=$filter.status}
			</select>
		</td>
		<td>
			<select name="filter[limit]" onchange="document.forms['filter_frm'].submit();">
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
			</select>
		</td>
		<td align="right" nowrap="nowrap">
			<a href="{'krifs'|get_link:'manage_timesheets'}">Days view &#0187;</a>
		</td>
	</tr>
</table>
<p/>

{if $tot_timesheets > $filter.limit}
<input type="hidden" name="go" value="" />
<table width="80%">
	<tr>
	<td width="50%">
		{if $filter.start > 0} 
			<a href="" onClick="document.forms['filter_frm'].elements['go'].value='prev'; document.forms['filter_frm'].submit(); return false;">&#0171; Previous</a>
		{else}
			<font class="light_text">&#0171; Previous</font>
		{/if}
	</td>
	<td width="50%" align="right">
		<select name="filter[start]" onChange="document.forms['filter_frm'].submit()">
			{html_options options=$pages selected=$filter.start}
		</select>
		{if $filter.start + $filter.limit < $tot_timesheets}
			<a href="" onClick="document.forms['filter_frm'].elements['go'].value='next'; document.forms['filter_frm'].submit(); return false;">Next &#0187;</a>
		{else}
			<font class="light_text">Next &#0187;</font>
		{/if}
	</td>
	</tr>
</table>
{/if}
</form>

<table class="list" width="80%">
	<thead>
	<tr>
		<td width="10%">Date</td>
		<td width="25%">User</td>
		<td width="5%">ID</td>
		<td width="20%">Status</td>
		<td width="15%">Defined time</td>
		<td width="15%">Total time</td>
	</tr>
	</thead>
	

	{foreach from=$timesheets item=timesheet}
	<tr>
		<td width="1%">
            {assign var="p" value="id:"|cat:$timesheet->id|cat:",returl:"|cat:$ret_url}
			<a href="{'krifs'|get_link:'timesheet_edit':$p:'template'}"
			>{$timesheet->date|date_format:$smarty.const.DATE_FORMAT_SMARTY}</a>
		</td>
		<td nowrap="nowrap">
			{assign var="user_id" value=$timesheet->user_id}
			{$users_list.$user_id}
		</td>
		<td>[{$timesheet->id}]</td>
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
	{foreachelse}
	<tr>
		<td colspan="6" class="light_text">[No timesheets]</td>
	</tr>
	{/foreach}
</table>

<p>
<b>Note:</b> The difference between <b>Defined time</b> and <b>Total time</b>
is that <b>Total time</b> also takes into account ticket details which have
not yet been linked to the timesheet.</p>
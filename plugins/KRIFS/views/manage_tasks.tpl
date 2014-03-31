{assign var="paging_titles" value="Krifs, Scheduled Tasks"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
//<[CDATA[

var filter_user_id = '{$filter.user_id}';

{literal}
function canChangeOrder()
{
	ret = true;
	
	if (filter_user_id == '')
	{
		ret = false;
		alert ('You need to select a single user.');
	}
	
	return ret;
}

// Overloading of setDateString() in js file, to trigger form submission
function setDateString(y,m,d)
{
	fels = document.forms[frm_name].elements;
	for (i = 0; i < fels.length; i++)
	{
		if (fels[i].name == elname)
		{
			el = fels[i];
		}
	}
	if (m < 10) m = '0'+m;
	el.value=d+"/"+m+"/"+y;
	document.forms[frm_name].submit ();
}
{/literal}

//]]>
</script>



<h1>Scheduled Tasks</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="data_frm">
{$form_redir}

<table class="list" width="480">
	<thead>
	<tr>
		<td width="200">User</td>
		<td width="120">Sort by</td>
		<td width="50"> </td>
		<td width="80">Date</td>
		<td width="100"> </td>
		<td width="50">Days</td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="filter[user_id]" onchange="document.forms['data_frm'].submit();">
				<option value="">[All users]</option>
				{html_options options=$users_list selected=$filter.user_id}
			</select>
		</td>
		<td>
			<select name="filter[order_by]" onchange="document.forms['data_frm'].submit();">
				<option value="date_start">Time</option>
				<option value="ord" {if $filter.order_by=="ord"}selected{/if}>Order</option>
			</select>
		</td>
		
		<td align="right" nowrap="nowrap" width="80">
			{if $date_prev}
				<a href="#"
					onclick="document.forms['data_frm'].elements['filter[date]'].value='{$date_prev|date_format:$smarty.const.DATE_FORMAT_SELECTOR}'; document.forms['data_frm'].submit();"
				>&#0171; Previous</a>
			{else}
				<font class="light_text">&#0171; Prev</font>
			{/if}
		</td>
		
		<td nowrap="nowrap">
			<input type="text" size="12" name="filter[date]" value="{$filter.date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}" 
				onchange="document.forms['data_frm'].submit();"
			/>
			{literal}
			<a href="#" onclick="showCalendarSelector('data_frm', 'filter[date]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
		</td>
		<td nowrap="nowrap">
			<a href="#"
				onclick="document.forms['data_frm'].elements['filter[date]'].value='{$date_next|date_format:$smarty.const.DATE_FORMAT_SELECTOR}'; document.forms['data_frm'].submit();"
			>Next &#0187;</a>
		</td>
		<td>
			<select name="filter[days]" onchange="document.forms['data_frm'].submit();">
				<option value="1">1</option>
				<option value="3" {if $filter.days==3}selected{/if}>3</option>
				<option value="7" {if $filter.days==7}selected{/if}>7</option>
				<option value="14" {if $filter.days==14}selected{/if}>14</option>
				<option value="30" {if $filter.days==30}selected{/if}>30</option>
			</select>
		</td>
	</tr>
</table>
</form>

<table class="list" width="98%">
{foreach from=$tasks key=day item=users_tasks}
	<tr>
		<td colspan="5">
			<h2>{$day|date_format:$smarty.const.DATE_FORMAT_LONG_DAY_SMARTY}</h2>
		</td>
		<td colspan="2" align="right">
            {assign var="p" value="user_id:"|cat:$filter.user_id|cat:",date:"|cat:$day|cat:",returl:"|cat:$ret_url}
			<h2><a href="{'krifs'|get_link:'manage_tasks_order':$p:'template'}"
			onclick="return canChangeOrder();"
			>Edit tasks order &#0187;</a></h2>
		</td>
	</tr>
	<tr class="head">
		<td width="80">User</td>
		<td width="60">Time</td>
		<td width="60" align="center">Ord.</td>
		<td width="60">Location</td>
		<td>Ticket / Comments</td>
		<td>Customer</td>
		<td> </td>
	</tr>
		
	{foreach from=$users_tasks key=user_id item=user_tasks}
	{foreach from=$user_tasks item=task}
		<tr>
			<td nowrap="nowrap">
                {assign var="p" value="id:"|cat:$task->id|cat:",returl:"|cat:$ret_url}
				<a href="{'krifs'|get_link:'task_edit':$p:'template'}">{$logins_list.$user_id}</a>
				[{$task->completed}%]
			</td>
			<td nowrap="nowrap">
				{$task->date_start|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}
				-
				{$task->date_end|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}
			</td>
			<td align="center">{$task->ord}</td>
			<td>
				{assign var="location_id" value=$task->location_id}
				{$locations_list.$location_id}
			</td>
			<td>
                {assign var="p" value="id:"|cat:$task->ticket_id|cat:",returl:"|cat:$ret_url}
				<a href="{'krifs'|get_link:'ticket_edit':$p:'temaplte'}"># {$task->ticket_id}</a>:
				{$task->ticket_subject|escape}
				{if $task->customer_location_id}
					<br/>Loc.: {$task->customer_location_name|escape}
				{/if}
				{if $task->attendees_ids}
				{/if}
				{if $task->comments}
					<br/><i>{$task->comments|escape|nl2br}</i>
				{/if}
			</td>
			<td>
				{assign var="customer_id" value=$task->customer_id}
				{$customers_list.$customer_id} ({$customer_id})
			</td>
			<td align="right" nowrap="nowrap">
                {assign var="p" value="id:"|cat:$task->id|cat:",returl:"|cat:$ret_url}
				<a href="{'krifs'|get_link:'task_delete':$p:'template'}"
					onclick="return confirm('Are you really sure you want to delete this task?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{/foreach}
	{foreachelse}
		<tr>
			<td colspan="7" class="light_text">[No tasks scheduled]</td>
		</tr>
	{/foreach}
{/foreach}
</table>


<table class="list">

</table>

<p/>

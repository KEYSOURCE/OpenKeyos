{assign var="paging_titles" value="KRIFS, Timesheets, Edit Timesheet"}
{assign var="paging_urls" value="/krifs, /krifs/manage_timesheets"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var last_popup_window = false;
var timesheet_id = {$timesheet->id};

{literal}
function showGapsPopup (anchor_name)
{
	frm = document.forms['frm_t'];
	if (last_popup_window) last_popup_window.close ();
	
	popup_url = '/krifs/popup_fill_ts_gaps?id='+timesheet_id;
	position = getAnchorPosition (anchor_name);
	x = position.x;
	y = position.y - 500;
	if (!isNaN(window.screenX)) x = x+window.screenX;
	x = x - 200;
	last_browse_window = window.open (popup_url, 'Fill_Gaps', 'dependent, scrollbars=yes, resizable=yes, width=100, height=100, left='+x+', top='+y);
	return false;
}

{/literal}
//]]>
</script>

<h1>Edit Timesheet: {$timesheet->date|date_format:$smarty.const.DATE_FORMAT_SMARTY}, {$timesheet->user->get_name()}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<table width="40%" class="list">
	<thead>
	<tr>
		<td width="25%">Status:</td>
		<td width="75%" class="post_highlight">
			{assign var="status" value=$timesheet->status}
			{$TIMESHEET_STATS.$status}
		</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">ID:</td>
		<td class="post_highlight">{$timesheet->id}</td>
	</tr>
	<tr>
		<td class="highlight">Total hours:</td>
		<td class="post_highlight">
			{assign var="defined_work_time" value=$timesheet->get_work_time()}
			{if $defined_work_time}
				{$defined_work_time|@format_interval_minutes}
			{else}
				--
			{/if}
		</td>
	</tr>
	
	{if $timesheet->closed_by_id}
	<tr>
		<td class="highlight">Closed by:</td>
		<td class="post_highlight">
			{if $timesheet->closed_by}
			{$timesheet->closed_by->get_name()},
			{$timesheet->close_time|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{/if}
		</td>
	</tr>
	{/if}
	
	{if $timesheet->approved_by_id}
	<tr>
		<td class="highlight">Approved by:</td>
		<td class="post_highlight">
			{if $timesheet->approved_by}
			{$timesheet->approved_by->get_name()},
			{$timesheet->approved_date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{/if}
		</td>
	</tr>
	{/if}
</table>

<p>
<input type="submit" name="cancel" value="Exit" class="button" />&nbsp;&nbsp;&nbsp;
{if $timesheet->status==$smarty.const.TIMESHEET_STAT_OPEN}
<input type="submit" name="close_timesheet" value="Close timesheet" class="button"
	onclick="return confirm('Are you really sure you want to close this timesheet?');"
/>
&nbsp;&nbsp;&nbsp;
<input type="submit" name="show_gaps_popup" value="Fill gaps" class="button" id="button_gaps"
	onclick="showGapsPopup('button_gaps'); return false;"
/>

{/if}
{if $timesheet->status==$smarty.const.TIMESHEET_STAT_CLOSED}
	<input type="submit" name="approve_timesheet" value="Approve" class="button" 
		onclick="return confirm('Are you sure you want to approve this timesheet? Once approved, it will be automatically imported by the ERP system');"
	/>
	<input type="submit" name="reopen_timesheet" value="Re-open" class="button"
		onclick="return confirm('Are you really sure you want to re-open this timesheet?');"
	/>
{elseif $timesheet->status==$smarty.const.TIMESHEET_STAT_APPROVED}
	<input type="submit" name="cancel_approval" value="Cancel approval" class="button"
		onclick="return confirm('Are you really sure you want to cancel the approval of this timesheet?');"
	/>
{elseif $timesheet->status==$smarty.const.TIMESHEET_STAT_CENTRALIZED or $timesheet->status==$smarty.const.TIMESHEET_STAT_PENDING_CENTRALIZE}
	<input type="submit" name="cancel_centralization" value="Cancel centralization" class="button"
		onclick="return confirm('Are you really sure you want to cancel the centralization?');"
	/>
{/if}


</p>

<table width="98%" class="list">
	<thead>
	<tr>
		<td colspan="2" width="5%">Hour</td>
		<td width="20%">Activity/Action type</td>
		<td width="10%">Location</td>
		<td width="20%">Customer</td>
		<td width="45%" colspan="2">Ticket detail / Comments</td>
	</tr>
	</thead>
	
	{foreach from=$timesheet->hours item=interval}
	<tr>
		{if isset($interval->detail_idx)}
			{assign var="idx" value=$interval->detail_idx}
			{assign var="detail" value=$timesheet->details.$idx}
		{else}
			{assign var="idx" value=null}
			{assign var="detail" value=null}
		{/if}
	
		<td width="1%" nowrap="nowrap"
			{if !isset($interval->detail_idx)}class="light_text"
			{elseif $interval->overlaps}class="error"{/if}
		>
			{$interval->time_in|date_format:$smarty.const.HOUR_FORMAT_SMARTY}
		</td>
		<td width="4%" nowrap="nowrap"
			{if !isset($interval->detail_idx)}class="light_text"
			{elseif $interval->overlaps}class="error"{/if}
		>
			- {$interval->time_out|date_format:$smarty.const.HOUR_FORMAT_SMARTY}
		</td>
		
		{if isset($interval->detail_idx)}
			<td>
				{if $detail->ticket_detail_id and !$detail->detail_special_type}
					{assign var="action_type_id" value=$detail->ticket_detail->activity_id}
					{$action_types_list.$action_type_id}
				{else}
					{if $detail->activity and $detail->activity->category_id}
						{assign var="category_id" value=$detail->activity->category_id}
						[{$categories_list.$category_id}] 
					{/if} 
					
					{assign var="activity_id" value=$detail->activity_id}
					{$activities.$activity_id}
				{/if}
			</td>
			<td>
				{assign var="location_id" value=$detail->location_id}
				{$locations_list.$location_id}
			</td>
			<td>
				{assign var="customer_id" value=$detail->customer_id}
				{$customers_list.$customer_id} (# {$customer_id})
			</td>
			
			{if $detail->ticket_detail_id}
				<td>
					<i>
                    {assign var="p" value="id:"|cat:$detail->ticket_detail_id|cat:",returl:"|cat:$ret_url}
					<a href="{'krifs'|get_link:'ticket_detail_edit':$p:'template'}"
					># {$detail->ticket_detail->ticket_id}</a>:
					{$detail->ticket->subject|escape}
					</i>
					<br/>
					
					{if !$detail->detail_special_type}
						{$detail->ticket_detail->comments|nl2br}
					{elseif $detail->detail_special_type == $smarty.const.TS_SPECIAL_TRAVEL_TO}
						[ Travel to customer ]
					{elseif $detail->detail_special_type == $smarty.const.TS_SPECIAL_TRAVEL_FROM}
						[ Travel from customer ]
					{/if}
				</td>
				<td width="5%" nowrap="nowrap" align="right">
                    {assign var="p" value="id:"|cat:$detail->ticket_detail->ticket_id|cat:",returl:"|cat:$ret_url}
                    <a href="{'krifs'|get_link:'ticket_edit':$p:'template'}"
					>Ticket &#0187;</a>
				</td>
			{else}
				<td>{$detail->comments|escape|nl2br}</td>
				<td width="5%" align="right" nowrap="nowrap">
					{if $timesheet->status==$smarty.const.TIMESHEET_STAT_OPEN}
                    {assign var="p" value="id:"|cat:$detail->id}
                    <a href="{'krifs'|get_link:'timesheet_detail_edit':$p:'template'}">Edit &#0187;</a>
					<br/>
                    {assign var="p" value="id:"|cat:$detail->id}
                    <a href="{'krifs'|get_link:'timesheet_detail_delete':$p:'template'}"
						onclick="return confirm('Are you sure you want to delete this timesheet detail?');"
					>Delete &#0187;</a>
					{/if}
				</td>
			{/if}
		{else}
			<td colspan="5" align="right" nowrap="nowrap">
				{if $timesheet->status==$smarty.const.TIMESHEET_STAT_OPEN}
                {assign var="p" value="timesheet_id:"|cat:$timesheet->id|cat:",start:"|cat:$interval->time_in|cat:",end:"|cat:$interval->time_out}
                <a href="{'krifs'|get_link:'timesheet_detail_add':$p:'template'}" >Add &#0187;</a>
				{/if}
			</td>
		{/if}
	</tr>
	{/foreach}
	
</table>
<p/>

</form>
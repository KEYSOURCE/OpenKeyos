{assign var="paging_titles" value="KRIFS, Timesheets, Add Timesheet"}
{assign var="paging_urls" value="/krifs, /krifs/manage_timesheets"}
{include file="paging.html"}

<h1>[No Timesheet]: {$timesheet->date|date_format:$smarty.const.DATE_FORMAT_SMARTY}, {$timesheet->user->get_name()}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<p>Your timesheet for this day has not been initialized yet. To start it, click on
the <b>Initialize</b> button.</p>

{if $timesheet->hours|@count>1}
<p>Below you have the activities for the day which were automatically collected from 
your tickets.</p>
{/if}

<table width="98%" class="list">
	<thead>
	<tr>
		<td colspan="2" width="5%">Hour</td>
		<td width="20%">Activity/Action type</td>
		<td width="10%">Location</td>
		<td width="20%">Customer</td>
		<td width="44%" colspan="2">Ticket detail / Comments</td>
	</tr>
	</thead>
	
	{foreach from=$timesheet->hours item=interval}
	<tr>
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
			{assign var="idx" value=$interval->detail_idx}
			{assign var="detail" value=$timesheet->details.$idx}
			<td>
				{if $detail->ticket_detail_id}
					{assign var="action_type_id" value=$detail->ticket_detail->activity_id}
					{$action_types_list.$action_type_id}
				{else}
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
			<td>
				{if $detail->ticket_detail_id}
					<i>
                    {assign var="p" value="id:"|cat:$detail->ticket_detail_id|cat:",returl:"|cat:$ret_url}
					<a href="{'krifs'|get_link:'ticket_detail_edit':$p:'template'}"
					># {$detail->ticket_detail->id}</a>:
					{$detail->ticket->subject|escape}
					</i>
					<br/>
					{$detail->ticket_detail->comments|nl2br}
				{else}
					{$detail->comments|nl2br}
				{/if}
			</td>
		{else}
			<td colspan="4"> </td>
		{/if}
		
	</tr>
	{/foreach}
	
</table>
<p/>

<input type="submit" name="save" value="Initialize" />
<input type="submit" name="cancel" value="Cancel" />
</form>

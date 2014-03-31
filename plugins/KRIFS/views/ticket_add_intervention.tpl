{assign var="paging_titles" value="KRIFS, Create Intervention Report"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}


<h1>Create Intervention Report</h1>

<p class="error">{$error_msg}</p>


<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="3">Intervention Report</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%">Customer:</td>
		<td colspan="2">#{$customer->id}: {$customer->name}</td>
	</tr>
	<tr>
		<td>Ticket:</td>
		<td colspan="2">#{$ticket->id}: {$ticket->subject}</td>
	</tr>
	<tr>
		<td>Subject:</td>
		<td colspan="2"><input type="text" name="intervention[subject]" value="{$intervention->subject|escape}" size="60"/></td>
	</tr>

	<tr>
		<td>Details:</td>
		<td colspan="2">
			<i>Select below the ticket details that you want to include in the report:</i>
		</td>
	</tr>
	
	{assign var="details_shown" value=0}
	{foreach from=$ticket->details item=detail}
	{if $detail->user_id and ($detail->comments and $detail->work_time) and !$detail->intervention_report_id}
		{assign var="details_shown" value=1}
		<tr>
			<td> </td>
			<td width="1%">
				<input type="checkbox" name="include_details[]" value="{$detail->id}" checked />
			</td>
			<td width="84%">
				{if $detail->activity_id}
					{assign var="activity_id" value=$detail->activity_id}
					<b>Activity: {$activities.$activity_id}</b><br/>
				{/if}
				{if $detail->user_id}
					<b>User: {$detail->user->get_short_name()}</b><br/>
				{/if}
				{if $detail->work_time}
					<b>
					Work time:
					{$detail->work_time|@format_interval_minutes} hrs., on
					{$detail->time_in|date_format:$smarty.const.DATE_FORMAT_SELECTOR}
					{$detail->time_in|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}
					</b>
					<br/>
				{/if}
				{$detail->comments}
			</td>
		</tr>
	{/if}
	{/foreach}
	
	{if !$details_shown}
	<tr>
		<td> </td>
		<td colspan="2" class="light_text">
			[No suitable details available]
		</td>
	</tr>
	{/if}

</table>
<p/>

<input type="submit" name="save" value="Create" />
<input type="submit" name="cancel" value="Cancel" />


</form>
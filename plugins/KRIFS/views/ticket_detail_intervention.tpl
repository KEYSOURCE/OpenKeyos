{assign var="ticket_id" value=$ticket->id}
{assign var="detail_id" value=$ticket_detail->id}
{assign var="paging_titles" value="KRIFS, Ticket, Edit Entry, Set Intervention Report"}
{assign var="paging_urls" value="/krifs, /krifs&ticket_edit/"|cat:$ticket_id|cat:", /krifs&ticket_detail_edit/"|cat:$ticket_detail_id}
{include file="paging.html"}

<h1>Set Intervention Report</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Ticket information</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%">Customer:</td>
		<td>{$customer->name} (#{$customer->id})</td>
	</tr>
	<tr>
		<td>Ticket ID:</td>
		<td># {$ticket->id}</td>
	</tr>
	<tr>
		<td>Ticket subject:</td>
		<td>{$ticket->subject|escape}</td>
	</tr>
	<tr>
		<td>Ticket detail:</td>
		<td>
			<b>
			{if $ticket_detail->user_id}
				{$ticket_detail->user->get_name()}, 
			{/if}
			{$ticket_detail->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			</b>
			<br/>
			{$ticket_detail->comments|escape|nl2br}
		</td>
	</tr>
	
	<tr class="head">
		<td colspan="2">Available intervention reports</td>
	</tr>
	
	{if $current_intervention and $current_intervention->status != $smarty.const.INTERVENTION_STAT_OPEN}
		<!-- There is already a linked intervention report and it is not open -->
		<tr>
			<td></td>
			<td class="error">
				Sorry, this ticket detail is already linked to an intervention report, and that intervention report is not open.
			</td>
		</tr>
	{else}
		
		{foreach from=$interventions item=intervention}
		<tr>
			<td align="right">
				<input type="radio" clas="radio" name="intervention_id" value="{$intervention->id}" 
					{if $intervention->id==$ticket_detail->intervention_report_id}checked{/if}
				/>
			</td>
			<td>
                {assign var="p" value="id:"|cat:$intervention->id}
				<a href="{'krifs'|get_link:'intervention_edit':$p:'template'}"
				>#{$intervention->id}: {$intervention->subject|escape}</a>
			</td>
		</tr>
		{foreachelse}
		<tr>
			<td></td>
			<td class="light_text">[No available open intervention reports]</td>
		</tr>
		{/foreach}
	{/if}
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Cancel" />

</form>
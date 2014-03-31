{assign var="paging_titles" value="Krifs, Configure Statuses"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}


<h1>Configure Statuses</h1>

<p class="error">{$error_msg}</p>

<a href="{'krifs'|get_link:'status_add'}">Add status &#0187;</a>
<p/>

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td width="45%">Name</td>
		<td width="45%">Escalate after</td>
		<td width="10"> </td>
	</tr>
	</thead>
	
	{foreach from=$statuses_list item=status_name key=status_id}
		<tr>
            {assign var="p" value="id:"|cat:$status->id}
			<td><a href="{'krifs'|get_link:'status_edit':$p:'template'}">{$status_id}</a></td>
			<td><a href="{'krifs'|get_link:'status_edit':$p:'template'}">{$status_name}</a></td>
			
			<td>
				{if $escalate_intervals.$status_id}
					{$escalate_intervals.$status_id}
				{else}
					-
				{/if}
			</td>
			<td align="right">
				{if $ticket_class->can_delete_status($status_id, false) }
                {assign var="p" value="id:"|cat:$status->id}
				<a href="{'krifs'|get_link:'status_delete':$p:'template'}"
					onClick="return confirm ('Are you sure you want to delete this status?');"
				>Delete&nbsp;&#0187;</a>
				{/if}
			</td>
		</tr>
	{/foreach}

</table>

<p>
<b>Note:</b> For <b>New</b> tickets the SLA time defined for customers is also
taken into account.<br/>
If a customer has an SLA time defined that is shorter than the <b>Escalate after</b>
setting for <b>New</b> status,<br/>
then the SLA time takes precedence when escalating tickets.
</p>

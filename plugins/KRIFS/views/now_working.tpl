{assign var="paging_titles" value="Krifs, Who is doing what"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}


<h1>Who is doing what</h1>

<p class="error">{$error_msg}</p>

<table class="list" width="90%">
	<thead>
	<tr>
		<td width="20%">User</td>
		<td width="15%">Since</td>
		<td width="55%">Ticket</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$now_working item=working}
		<tr>
			<td>
                {assign var="p" value="id:"|cat:$working.user->id}
                <a href="{'user'|get_link:'user_edit':$p:'template'}">{$working.user->get_name()}</a></td>
			<td>{$working.since|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td>
                {assign var="p" value="id:"|cat:$working.ticket->id}
				<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">#{$working.ticket->id}</a>:
				{$working.ticket->subject}
			</td>
			<td align="right" nowrap="nowrap">
                {assign var="p" value="user_id:"|cat:$working.user->id|cat:',returl:'|cat:$ret_url}
				<a href="{'krifs'|get_link:'ticket_unmark_working':$p:'template'}"
					onclick="return confirm('Are you sure you want to cancel this mark?');"
				>Cancel &#0187;</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="4" class="light_text">[No marks currently set]</td>
		</tr>
	{/foreach}
</table>
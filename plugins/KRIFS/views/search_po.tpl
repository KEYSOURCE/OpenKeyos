{assign var="paging_titles" value="KRIFS, Search PO"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<h1>Search PO code</h1>

<p class="error">{$error_msg}</p>

<form action="" method="GET">
{$form_redir}
<table width="95%">
	<tr><td width="40%" nowrap="nowrap">
		Search: <input type="text" name="search_text" value="{$search_code|escape}" size="20" />
		<input type="submit" name="search" value="Search again &#0187;" class="button" />
	</td><td align="right" nowrap="nowrap">
		{if $tickets_count > $show_limit}
		The search returned over {$show_limit} results, only the first {$show_limit} are displayed
		{/if}
	</td></tr>
</table>
</form>
<p/>
<table width="95%" class="list">
	<thead>
	<tr>
		<td width="20">ID</td>
		<td>Subject</td>
		<td width="15%">Customer</td>
		<td width="100">Status</td>
		<td width="100">PO code</td>
		<td width="100">Created</td>		
	</tr>
	</thead>
	{foreach from=$tickets item=ticket}
	<tr>
        {assign var="p" value="id:"|cat:$ticket->id}
		<td><a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->id}</a></td>
		<td><a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->subject|escape}</a></td>
		<td>
			{assign var="customer_id" value=$ticket->customer_id}
            {assign var="p" value="id:"|cat:$customer_id}
			<a href="{'customer'|get_link:'customer_edit':$p:'template'}">#{$customer_id}: {$customers_list.$customer_id}</a>
		</td>
		<td>
			{assign var="status" value=$ticket->status}
			{$TICKET_STATUSES.$status}
		</td>
		<td>
			{$ticket->po}
		</td>
		<td nowrap="nowrap">
			{$ticket->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No tickets found]</td>
	</tr>
	{/foreach}
</table>
<p />
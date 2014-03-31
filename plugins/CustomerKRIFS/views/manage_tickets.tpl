{assign var="paging_titles" value="Technical Support"}
{include file="paging.html"}

<h1>Technical Support</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="tickets_frm">
{$form_redir}

View tickets: 
<select name="filter[status]" onChange="document.forms['tickets_frm'].submit()">
	<option value="-2">[All]</option>
	<option value="-1" {if $filter.status==-1}selected{/if}>[Not closed]</option>
	{html_options options=$TICKET_STATUSES selected=$filter.status}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Customer: 
<select name="filter[customer]" onChange="document.forms['tickets_frm'].submit()">
	<option value="-1">[All]</option>	
	{html_options options=$tc_ulist selected=$filter.customer}
</select>
<p>

<table width="98%">
	<tr>
		<td colspan="2" width="50%">
			<a href="/?cl=customer_krifs&op=ticket_add">Create new ticket &#0187;</a>
		</td>
		<td align="right">
			{if $tot_tickets > $filter.limit}
				{if $filter.start > 0} 
					<a href="/?cl=customer_krifs&op=manage_tickets_submit" 
						onClick="document.forms['tickets_frm'].elements['go'].value='prev'; document.forms['tickets_frm'].submit(); return false;"
					>&#0171; Previous</a>
				{else}
					<font class="light_text">&#0171; Previous</font>
				{/if}
				<select name="filter[start]" onChange="document.forms['tickets_frm'].submit()">
					{html_options options=$pages selected=$filter.start}
				</select>
				{if $filter.start + $filter.limit < $tot_tickets}
					<a href="/?cl=customer_krifs&op=manage_tickets_submit" 
						onClick="document.forms['tickets_frm'].elements['go'].value='next'; document.forms['tickets_frm'].submit(); return false;" 
					>Next &#0187;</a>
				{else}
					<font class="light_text">Next &#0187;</font>
				{/if}
			{/if}
		</td>
	</tr>
</table>
<input type="hidden" name="go" value="">
<input type="hidden" name="filter[limit]" value="{$filter.limit}">
<p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td class="sort_text" style="width: 16px; text-align: left;"> 
			<a href="{$sort_url}&order_by=priority&order_dir={if $filter.order_by=='priority' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			><img src="/images/logo_icon_16.gif" style="width:16px; height:16px; vertical-align: middle;" alt="Priority" title="Priority"
			></a>{if $filter.order_by=='priority'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
		</td>
		
		<td width="5" style="padding:0px;"></td>
		
		<td class="sort_text" nowrap style="width: 50px; min-width:40px; white-space: no-wrap;"> 
			<a href="{$sort_url}&order_by=id&order_dir={if $filter.order_by=='id' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>ID</a>&nbsp;{if $filter.order_by=='id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
		</td>
		
		<td nowrap class="sort_text" style="width: 40%">
			<a href="{$sort_url}&order_by=subject&order_dir={if $filter.order_by=='subject' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Subject</a>
			{if $filter.order_by=='subject'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}
		</td>
		{if $tot_cust!=1}
		<td nowrap class="sort_text" style="width: 15%">
			<a href="{$sort_url}&order_by=customer&order_dir={if $filter.order_by=='customer' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Customer</a>
			{if $filter.order_by=='customer'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}
		</td>
		{/if}
		
		<td nowrap class="sort_text" style="width: 15%">
			<a href="{$sort_url}&order_by=status&order_dir={if $filter.order_by=='status' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Status</a>
			{if $filter.order_by=='status'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}
		</td>
		
		<td nowrap class="sort_text" style="width: 15%">
			<a href="{$sort_url}&order_by=created&order_dir={if $filter.order_by=='created' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Created</a>
			{if $filter.order_by=='created'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}
		</td>
		
		<td nowrap class="sort_text" style="width: 15%">
			<a href="{$sort_url}&order_by=last_modified&order_dir={if $filter.order_by=='last_modified' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Updated</a>
			{if $filter.order_by=='last_modified'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}
		</td>
                <td>&nbsp;</td>
	</tr>
	</thead>
	
	{foreach from=$tickets item=ticket}
		<tr>
			<td style="text-align: left;  width: 16px;" class="no_print">
				{assign var="priority_color" value=$ticket->priority}
				{if $priority_color}
					<img src="/images/logo_icon_16.gif" style="background: {$TICKETS_PRIORITIES_COLORS.$priority_color}" width="16" height="16"
					alt="Priority: {$TICKET_PRIORITIES.$priority_color}" title="Priority: {$TICKET_PRIORITIES.$priority_color}"
					>
				{/if}
			</td>
			
			<td width="5" style="padding:0px; ">{if $ticket->escalated}<font class="error" style="font-size:12pt">!</font>{/if}</td>
			
			<td><a href="/?cl=customer_krifs&op=ticket_edit&id={$ticket->id}">{$ticket->id}</a></td>
			<td><a href="/?cl=customer_krifs&op=ticket_edit&id={$ticket->id}">{$ticket->subject}</a></td>
			{if $tot_cust!=1}
			<td><a href="/?cl=customer_krifs&op=ticket_edit&id={$ticket->id}">{assign var="cid" value=$ticket->customer_id}{$customers_list.$cid}</a></td>
			{/if}
			<td>
				{assign var="ticket_status"  value=$ticket->status}
				{$TICKET_STATUSES.$ticket_status}
			</td>
			<td>{$ticket->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td>{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
                        <td><a href="/?cl=customer_krifs&op=customer_satisfaction&ticket_id={$ticket->id}">Feedback</a></td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="11">[No tickets found]</td>
		</tr>
	{/foreach}

</table>
<p>

</form>
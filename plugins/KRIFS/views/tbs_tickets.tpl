{assign var="paging_titles" value="KRIFS, TBS Tickets"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}


{literal}
<script language="JavaScript">

function selectAll ()
{
	frm = document.forms['tickets_frm']
	if (frm.elements['filter[user_id][]'])
	{
		users_list = frm.elements['filter[user_id][]']
		customers_list = frm.elements['filter[customer_id][]']
		
		for (i=0; i<users_list.options.length; i++)
		{
			users_list.options[i].selected = true
		}
		for (i=0; i<customers_list.options.length; i++)
		{
			customers_list.options[i].selected = true
		}
	}
}

function addUser ()
{
	frm = document.forms['tickets_frm']
	sel_list = frm.elements['filter[user_id][]']
	users_list = frm.elements['available_users']
	
	if (users_list.selectedIndex >= 0)
	{
		opt = new Option (users_list.options[users_list.selectedIndex].text, users_list.options[users_list.selectedIndex].value, false, false)
		
		sel_list.options[sel_list.options.length] = opt
		users_list.options[users_list.selectedIndex] = null
	}
}

function removeUser ()
{
	frm = document.forms['tickets_frm']
	sel_list = frm.elements['filter[user_id][]']
	users_list = frm.elements['available_users']
	
	if (sel_list.selectedIndex >= 0)
	{
		opt = new Option (sel_list.options[sel_list.selectedIndex].text, sel_list.options[sel_list.selectedIndex].value, false, false)
		
		users_list.options[users_list.options.length] = opt
		sel_list.options[sel_list.selectedIndex] = null
	}
}

function addCustomer ()
{
	frm = document.forms['tickets_frm']
	sel_list = frm.elements['filter[customer_id][]']
	users_list = frm.elements['available_customers']
	
	if (users_list.selectedIndex >= 0)
	{
		if (users_list.options[users_list.selectedIndex].value != " ")
		{
			opt = new Option (users_list.options[users_list.selectedIndex].text, users_list.options[users_list.selectedIndex].value, false, false)
			
			sel_list.options[sel_list.options.length] = opt
			users_list.options[users_list.selectedIndex] = null
		}
	}
}

function removeCustomer ()
{
	frm = document.forms['tickets_frm']
	sel_list = frm.elements['filter[customer_id][]']
	users_list = frm.elements['available_customers']
	
	if (sel_list.selectedIndex >= 0)
	{
		opt = new Option (sel_list.options[sel_list.selectedIndex].text, sel_list.options[sel_list.selectedIndex].value, false, false)
		
		users_list.options[users_list.options.length] = opt
		sel_list.options[sel_list.selectedIndex] = null
	}
}

function checkRunSearch()
{
	frm = document.forms['tickets_frm']
	srcs_list = frm.elements['search_id']
	ret = true;
	
	if (srcs_list.length > 1)
	{
		srcs_list = srcs_list[1]
	}
	
	src_id = srcs_list.options[srcs_list.selectedIndex].value
	
	if (src_id == '')
	{
		ret = false;
		alert ('Please select a saved search from the list');
	}
	
	return ret;
}

</script>
{/literal}


<h1>TBS Tickets</h1>

<p class="error">{$error_msg}</o>

<form action="" method="POST" name="tickets_frm">
<input type="hidden" name="go" value="">
{$form_redir}

<div class="no_print">

<table width="98%">
	<tr>
		<td width="50%" nowrap="nowrap">
			Show:
			<select name="filter[unscheduled_only]" onchange="document.forms['tickets_frm'].submit();">
				<option value="1">Not scheduled only</option>
				<option value="0" {if !$filter.unscheduled_only}selected{/if}>All TBS tickets</option>
			</select>
			&nbsp;&nbsp;&nbsp;
			Customer:
			<select name="filter[customer_id]" onchange="document.forms['tickets_frm'].submit();" style="width:200px;">
				<option value="">[All customers]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
		</td>
		<td align="right" nowrap="nowrap">
			Per page:
			<select name="filter[limit]" onchange="document.forms['tickets_frm'].submit();">
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
			</select>
			{if $tickets_count > $filter.limit}
				&nbsp;&nbsp;&nbsp;
				{if $filter.start > 0} 
					<a href="{'krifs'|get_link:'manage_tickets_submit'}"
						onClick="document.forms['tickets_frm'].elements['go'].value='prev'; document.forms['tickets_frm'].submit(); return false;"
					>&#0171; Previous</a>
				{else}
					<font class="light_text">&#0171; Previous</font>
				{/if}
				<select name="filter[start]" onChange="document.forms['tickets_frm'].submit()">
					{html_options options=$pages selected=$filter.start}
				</select>
				{if $filter.start + $filter.limit < $tickets_count}
					<a href="{'krifs'|get_link:'manage_tickets_submit'}"
						onClick="document.forms['tickets_frm'].elements['go'].value='next'; document.forms['tickets_frm'].submit(); return false;" 
					>Next &#0187;</a>
				{else}
					<font class="light_text">Next &#0187;</font>
				{/if}
			{/if}
		</td>
	</tr>
</table>
<p/>
</div>

<table class="list" width="98%">
	<thead>
	<tr>

		<td class="sort_text" style="width: 16px; text-align: left;">
			<a href="{$sort_url}&order_by=priority&order_dir={if $filter.order_by=='priority' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			><img src="/images/logo_icon_16.gif" style="width:16px; height:16px; vertical-align: middle;" alt="Priority" title="Priority"
			>{if $filter.order_by=='priority'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
		</td>
		<td width="1"></td>
		
		<td class="sort_text" nowrap width="30" style="width: 30px; white-space: no-wrap;"> 
			<a href="{$sort_url}&order_by=id&order_dir={if $filter.order_by=='id' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>ID</a>&nbsp;{if $filter.order_by=='id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
		</td>

		<td nowrap class="sort_text" style="width: 30%">
			<a href="{$sort_url}&order_by=subject&order_dir={if $filter.order_by=='subject' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Subject
			{if $filter.order_by=='subject'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>

		<td nowrap class="sort_text" style="width: 30px">
			<a href="{$sort_url}&order_by=type&order_dir={if $filter.order_by=='type' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Type
			{if $filter.order_by=='type'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		<td nowrap class="sort_text" style="width: 30px">
			<a href="{$sort_url}&order_by=customer&order_dir={if $filter.order_by=='customer' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Customer
			{if $filter.order_by=='customer'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		<td class="sort_text" style="width: 20px; text-align: center;">
			<a href="{$sort_url}&order_by=private&order_dir={if $filter.order_by=='private' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Priv.
			{if $filter.order_by=='private'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		<td nowrap class="sort_text" style="width: 30px">
			<a href="{$sort_url}&order_by=assigned_to&order_dir={if $filter.order_by=='assigned_to' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Assigned
			{if $filter.order_by=='assigned_to'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		<td nowrap class="sort_text" style="width: 30px">
			<a href="{$sort_url}&order_by=created&order_dir={if $filter.order_by=='created' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Created
			{if $filter.order_by=='created'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		<td nowrap class="sort_text" style="width: 30px">
			<a href="{$sort_url}&order_by=last_modified&order_dir={if $filter.order_by=='last_modified' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Updated
			{if $filter.order_by=='last_modified'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
	</tr>
	</thead>
	
	{foreach from=$tickets item=ticket}
		<tr>
			<td style="text-align: left; width: 16px;" class="no_print">
				{assign var="priority_color" value=$ticket->priority}
				{if $priority_color}
					<img src="/images/logo_icon_16.gif" style="background: {$TICKETS_PRIORITIES_COLORS.$priority_color}" width="16" height="16"
					alt="Priority: {$TICKET_PRIORITIES.$priority_color}" title="Priority: {$TICKET_PRIORITIES.$priority_color}"
					>
				{/if}
			</td>
			
			<td width="5" style="padding:0px; ">{if $ticket->escalated}<font class="error" style="font-size:12pt">!</font>{/if}</td>
			
			<td class="print_only">{$TICKET_PRIORITIES.$priority_color}</td>
			
			<td>
                {assign var="p" value="id:"|cat:$ticket->id}
                <a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->id}</a></td>
			<td>
				<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->subject|escape}</a>
				{if $ticket->scheduled_date}
					<br/><b>Scheduled: {$ticket->scheduled_date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</b>
				{/if}
			</td>
			<td style="width: 30px">
				{assign var="ticket_type" value=$ticket->type}
				{$TICKET_TYPES.$ticket_type}
			</td>
			<td>
				{assign var="customer_id" value=$ticket->customer_id}
				{$all_customers_list.$customer_id}
			</td>
			<td style="width: 20px; text-align: center;">
				{if $ticket->private} Y {else} N {/if}
			</td>
			<td>
				{if $ticket->assigned_id}
					{$ticket->assigned->get_short_name()}
					
					{if $ticket->assigned->customer_id}
						{assign var="user_customer_id" value=$ticket->assigned->customer_id}
						({$customers_list.$user_customer_id})
					{/if} 
				{/if}
			</td>
			<td nowrap="nowrap">{$ticket->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td nowrap="nowrap">{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="10" class="light_text">[No TBS tickets found]</td>
		</tr>
	{/foreach}

</table>
</p>

</form>
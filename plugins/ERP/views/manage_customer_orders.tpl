{assign var="paging_titles" value="ERP, Customer Orders / Subscriptions"}
{assign var="paging_urls" value="/?cl=erp, /?cl=erp&op=manage_customer_orders"}
{include file="paging.html"}

<h1>Customer Orders / Subscriptions </h1>

<p class="error">{$error_msg}</p>


<form action="" method="POST" name="filter_frm">
{$form_redir}
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="25%">Customer</td>
		<td width="10%">Status</td>
		<td width="65%">Per page</td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="filter[customer_id]" style="width: 250px;" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All customers]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
		</td>
		<td>
			<select name="filter[status]" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All]</option>
				{html_options options=$ORDER_STATS selected=$filter.status}
		</td>
		<td>
			<select name="filter[limit]" onchange="document.forms['filter_frm'].submit();">
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
			</select>
		</td>
	</tr>
</table>

<table width="98%" style="margin-top:10px; margin-bottom:15px;">
	<tr>
		<td width="50%">
			<a href="/?cl=erp&amp;op=customer_order_add&amp;customer_id={$filter.customer_id}{if $do_filter}&amp;do_filter=1{/if}">New Customer Order &#0187;</a>
		</td>
		<td width="50%" align="right">
			{if $tot_customer_orders > $filter.limit}
				{if $filter.start > 0} 
					<a href="" onClick="document.forms['filter_frm'].elements['go'].value='prev'; document.forms['filter_frm'].submit(); return false;">&#0171; Previous</a>
				{else}
					<font class="light_text">&#0171; Previous</font>
				{/if}
				<select name="filter[start]" onChange="document.forms['filter_frm'].submit()">
					{html_options options=$pages selected=$filter.start}
				</select>
				{if $filter.start + $filter.limit < $tot_customer_orders}
					<a href="" onClick="document.forms['filter_frm'].elements['go'].value='next'; document.forms['filter_frm'].submit(); return false;">Next &#0187;</a>
				{else}
					<font class="light_text">Next &#0187;</font>
				{/if}
			{/if}
		</td>
	</tr>
</table>
<input type="hidden" name="go" value="" />

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="10%">ERP number</td>
		<td width="5%">Type</td>
		
		<td width="25%">Subject</td>
		
		{if !$filter.customer_id}<td width="15%">Customer</td>{/if}
		<td width="10%">Date</td>
		<td width="5%">Status</td>
		<td width="5%">Billable</td>
		
		<td {if !$filter.customer_id}width="20%" {else} width="30%"{/if}>Tickets</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	
	{foreach from=$customer_orders item=customer_order}
	<tr>
		<td><a href="/?cl=erp&amp;op=customer_order_edit&amp;id={$customer_order->id}{if $do_filter}&amp;do_filter=1{/if}">{$customer_order->get_erp_num()|escape}</a></td>
		<td>
			{if $customer_order->for_subscription}Subscr.
			{else}Order
			{/if}
		</td>
		<td><a href="/?cl=erp&amp;op=customer_order_edit&amp;id={$customer_order->id}{if $do_filter}&amp;do_filter=1{/if}">{$customer_order->subject|escape}</a></td>
		
		{if !$filter.customer_id}
		<td>
			{assign var="customer_id" value=$customer_order->customer_id}
			{$customers_list.$customer_id}
		</td>
		{/if}
		
		<td>{$customer_order->date|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
		<td>
			{assign var="stat_id" value=$customer_order->status}
			{$ORDER_STATS.$stat_id}
		</td>
		<td>
			{if $customer_order->billable}Yes
			{else}No
			{/if}
		</td>
		
		<td>
			{if $customer_order->tickets}
				{foreach from=$customer_order->tickets item=ticket}
					<a href="/?cl=krifs&amp;op=ticket_edit&amp;id={$ticket->id}&amp;returl={$ret_url}"># {$ticket->id}</a>:
					{$ticket->subject|escape}
					<br/>
				{/foreach}
			{else}
				<font class="light_text">--</if>
			{/if}
		</td>
		<td align="right" nowrap="nowrap">
			<a href="/?cl=erp&amp;op=customer_order_delete&amp;id={$customer_order->id}{if $do_filter}&amp;do_filter=1{/if}"
				onclick="return confirm('Are you sure you want to delete this customer order?');"
			>Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text" {if $filter.customer_id > 0} colspan="9" {else} colspan="10" {/if}>[No matching intervention reports]</td>
	</tr>
	{/foreach}
</table>
</form>
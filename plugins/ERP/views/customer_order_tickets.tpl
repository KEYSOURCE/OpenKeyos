{assign var="order_id" value=$order->id}
{assign var="paging_titles" value="ERP, Customer Orders, Edit Customer Orders, Add Tickets"}
{assign var="paging_urls" value="/?cl=erp, /?cl=erp&op=manage_customer_order, /?cl=erp&op=customer_order_edit&id=$order_id"}
{include file="paging.html"}


<h1>Add Tickets</h1>

<p class="error">{$error_msg}</p>

<p>Select the tickets that you want to add to this order.</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%"> </td>
		<td width="5%">ID</td>
		<td width="10%">Status</td>
		<td width="85%">Subject</td>
	</tr>
	</thead>
	
	{foreach from=$tickets item=ticket}
		<tr>
			<td>
				<input type="checkbox" class="checkbox" name="tickets[]" value="{$ticket->id}">
			</td>
			<td nowrap="nowrap">
				<a href="/?cl=krifs&amp;op=ticket_edit&amp;id={$ticket->id}&amp;returl={$ret_url}">#&nbsp;{$ticket->id}</a>
			</td>
			<td>
				{assign var="status" value=$ticket->status}
				{$TICKET_STATUSES.$status}
			</td>
			<td>{$ticket->subject|escape}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="4" class="light_text">[No open tickets for this customer]</td>
		</tr>
	{/foreach}
</table>
<p/>

<input type="submit" name="save" value="Add selected" onclick="return confirm('Are you sure you want to add these tickets to this customer order?');"/>
<input type="submit" name="cancel" value="Cancel" />

</form>
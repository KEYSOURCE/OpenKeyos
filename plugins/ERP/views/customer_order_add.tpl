{assign var="paging_titles" value="ERP, Customer Orders, Add Customer Order"}
{assign var="paging_urls" value="/?cl=erp, /?cl=erp&op=manage_customer_orders"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function check_for_subscription ()
{
	frm = document.forms['frm_order'];
	elm = frm.elements['customer_order[for_subscription]'];
	elm_row_erp_id = document.getElementById ('row_erp_id');
	elm_row_subscription_num = document.getElementById ('row_subscription_num');
	
	if (elm.options[elm.selectedIndex].value  == '0')
	{
		frm.elements['customer_order[subscription_num]'].value = '';
		elm_row_erp_id.style.display = '';
		elm_row_subscription_num.style.display = 'none';
	}
	else
	{
		frm.elements['customer_order[erp_id]'].value = '';
		elm_row_erp_id.style.display = 'none';
		elm_row_subscription_num.style.display = '';
	}
}
{/literal}

//]]>
</script>

<h1>Add Customer Order</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_order">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2">Customer Order</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%" class="highlight">Customer:</td>
		<td width="85%" class="post_highlight">
			<select name="customer_order[customer_id]">
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$customer_order->customer_id}
			</select>
		</td>
	</tr>
	
	{if $ticket}
	<tr>
		<td class="highlight">Ticket:</td>
		<td class="post_highlight">
			[#{$ticket->id}] {$ticket->subject|escape}
		</td>
	</tr>
	{/if}
	
	<tr>
		<td class="highlight">Type:</td>
		<td class="post_highlight">
			<select name="customer_order[for_subscription]" onchange="check_for_subscription ();">
				<option value="0">Order</option>
				<option value="1" {if $customer_order->for_subscription}selected{/if}>Subscription</option>
			</select>
		</td>
	</tr>
	<tr id="row_erp_id">
		<td class="highlight">ERP order num.:</td>
		<td class="post_highlight">
			<input type="text" name="customer_order[erp_id]" value="{$customer_order->erp_id|escape}" size="20"/>
		</td>
	</tr>
	<tr id="row_subscription_num">
		<td class="highlight">Subscription num.:</td>
		<td class="post_highlight">
			<input type="text" name="customer_order[subscription_num]" value="{$customer_order->subscription_num|escape}" size="20"/>
		</td>
	</tr>
	<tr>
		<td class="highlight">Status:</td>
		<td class="post_highlight">
			<select name="customer_order[status]">
				{html_options options=$ORDER_STATS selected=$customer_order->status}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Date:</td>
		<td class="post_highlight">
			<input type="text" size="12" name="customer_order[date]" 
				value="{if $customer_order->date}{$customer_order->date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"
			>
			
			{literal}
			<a HREF="#" onClick="showCalendarSelector('frm_order', 'customer_order[date]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
			
		</td>
	</tr>
	<tr>
		<td class="highlight">Billable</td>
		<td class="post_highlight">
			<select name="customer_order[billable]">
				<option value="0">No</option>
				<option value="1" {if $customer_order->billable}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Subject:</td>
		<td class="post_highlight">
			<input type="text" name="customer_order[subject]" value="{$customer_order->subject|escape}" size="40" />
		</td>
	</tr>
	<tr>
		<td class="highlight">Comments:</td>
		<td class="post_highlight">
			<textarea name="customer_order[comments]" rows="4" cols="40">{$customer_order->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<!-- IE workaround -->
<input type="text" name="workaround" value="" style="display:none;" />

<input type="submit" name="save" value="Add" />
<input type="submit" name="cancel" value="Cancel" />
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
check_for_subscription ()
//]]>
</script>
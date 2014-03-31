{assign var="paging_titles" value="Customers, Messages Logs"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

{assign var="computer_id" value=$filter.computer_id}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var last_window = false;

{literal}
function openPopupMessage (id, month)
{
	if (last_window) last_window.close();
	
	popup_url = '/?cl=customer&op=popup_log_message&id='+id+'&month='+month;
	last_window = window.open (popup_url, 'Customer_Message', 'dependent, scrollbars=yes, resizable=yes, width=650, height=400');
	
	return false;
}
{/literal}
//]]>
</script>

<h1>Messages Logs</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter">
{$form_redir}

<table width="98%">
	<tr>
		<td width="300"><b>Customer:</b></td>
		<td width="100"><b>Month:</b></td>
		<td width="180"><b>Per page:</b></td>
		<td> </td>
	</tr>
	<tr>
		<td>
			<select name="filter[customer_id]"  
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				<option value="">[All customers]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>

		<td>
			<select name="filter[month]"
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				{html_options options=$log_months selected=$filter.month}
			</select>
		</td>
		<td>
			<select name="filter[limit]"
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
			</select>
		</td>
		<td align="right" nowrap="nowrap">
			{if count($pages) > 1}
				<input type="hidden" name="go" value="" />
				
				{if $filter.start > 0} 
					<a href="#" 
						onClick="document.forms['filter'].elements['go'].value='prev'; document.forms['filter'].submit(); return false;"
					>&#0171; Previous</a>
				{else}
					<font class="light_text">&#0171; Previous</font>
				{/if}
			
				<select name="filter[start]"
					onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
				>
					{html_options options=$pages selected=$filter.start}
				</select>
				
				{if $filter.start + $filter.limit < $messages_count}
					<a href="#" 
						onClick="document.forms['filter'].elements['go'].value='next'; document.forms['filter'].submit(); return false;" 
					>Next &#0187;</a>
				{else}
					<font class="light_text">Next &#0187;</font>
				{/if}
			{/if}
		</td>
		
	</tr>
</table>
</form>
<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="100">Date</td>
		<td width="50%">Subject</td>
		<td>User</td>
		<td>Customer</td>
	</tr>
	</thead>
	
	{foreach from=$messages item=message}
	<tr>
		<td><a href=""
		onclick="return openPopupMessage ({$message->id}, '{$message->month}');"
		>{$message->date_sent|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a></td>
		<td><a href="" onclick="return openPopupMessage ({$message->id}, '{$message->month}');">{$message->subject|escape}</a></td>
		<td>
			{if $message->user_id}
				{assign var="user_id" value=$message->user_id}
				{$users_list.$user_id}
			{else}-{/if}
		</td>
		<td>
			{if $message->customer_id}
				{assign var="customer_id" value=$message->customer_id}
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customers_list.$customer_id}</a>
			{else}-{/if}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="5" class="light_text">[No messages]</td>
	</tr>
	{/foreach}

</table>

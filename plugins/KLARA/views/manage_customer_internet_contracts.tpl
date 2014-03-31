{assign var="paging_titles" value="KLARA, Customer Internet Contracts"}
{assign var="paging_urls" value="/?cl=klara"}
{include file="paging.html"}


<h1>Customer Internet Contracts</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter"> 
{$form_redir}


<table width="98%">
	<tr>
		<td width="50%">
			<b>Customer:</b>
			
			<select name="filter[customer_id]"  
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				<option value="">[All customers]</option>
				<option value="-1" {if $filter.customer_id==-1}selected{/if}>[In notice period or expired]</option>
				<option value="-2" {if $filter.customer_id==-2}selected{/if}>[In notice period or expired, including suspended notifications]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
		<td width="50%" align="right">
			{if $customer->id}
				<a href="/?cl=klara&amp;op=customer_internet_contract_add&amp;customer_id={$customer->id}&amp;returl={$ret_url}">Add contract &#0187;</a>
			{/if}
		</td>
	</tr>
</table>
</form>
<p/>

Click on a contract dates to see its details.
<table width="98%" class="list">
	<thead>
	<tr>
		<td width="20">ID</td>
		<td width="200">Contract dates</td>
		<td>Active</td>
		{if !$customer->id}
			<td width="20%">Customer</td>
		{/if}
		<td width="30%">Provider / Contract</td>
		<td width="15%">Client number</td>
		<td width="15%">ADSL number</td>
		<td>Type</td>
		{if $customer->id}
			<td>Comments</td>
		{/if}
		<td width="5%"> </td>
	</tr>
	</thead>

	{foreach from=$contracts item=contract}
		<tr>
			<td><a href="/?cl=klara&amp;op=customer_internet_contract_edit&amp;id={$contract->id}&amp;returl={$ret_url}">{$contract->id}</a></td>
			<td nowrap="nowrap">
				<a href="/?cl=klara&amp;op=customer_internet_contract_edit&amp;id={$contract->id}&amp;returl={$ret_url}"
				{if $contract->start_date}>{$contract->start_date|date_format:$smarty.const.DATE_FORMAT_SHORT_SMARTY}
				{else}>[unspecified]
				{/if}{if $contract->end_date} - {$contract->end_date|date_format:$smarty.const.DATE_FORMAT_SHORT_SMARTY}{/if}</a>
				
				{if $contract->is_in_notice_period(true)}
					<br/><b><font color="{if $contract->is_expired()}red{else}orange{/if}">{$contract->get_expiration_string()}</font></b>
					{if $contract->suspend_notifs}
					<br/><font class="light_text">[Notif. suspended]</font>
					{/if}
				{/if}
			</td>
			<td>{if $contract->is_closed}Closed{else}Active{/if}</td>
			
			{if !$customer->id}
			<td>
				{assign var="customer_id" value=$contract->customer_id}
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customers_list.$customer_id}</a>
			</td>
			{/if}
			<td>
				<a href="/?cl=klara&amp;op=provider_edit&amp;id={$contract->provider->id}&amp;returl={$ret_url}">{$contract->provider->name}</a> :
				<a href="/?cl=klara&amp;op=provider_contract_edit&amp;id={$contract->provider_contract->id}&amp;returl={$ret_url}"
				>{$contract->provider_contract->name}</a>
			</td>
			
			<td nowrap="nowrap">{$contract->client_number}</td>
			<td nowrap="nowrap">{$contract->adsl_line_number}</td>
			<td>
				{assign var="line_type" value=$contract->line_type}
				{$LINE_TYPES.$line_type}
			</td>
			
			
			{if $customer->id}
			<td>{$contract->comments|escape|nl2br}</td>
			{/if}
			
			<td nowrap="nowrap" align="right">
				<a href="/?cl=klara&amp;op=customer_internet_contract_delete&amp;id={$contract->id}&amp;returl={$ret_url}"
					onclick="return confirm('Are you really sure you want to delete this contract?');"
				>Delete&nbsp;&#0187;</a>
			</td>
		</tr>
	
	{foreachelse}
		<tr>
			<td colspan="8" class="light_text">[No contracts matched]</td>
		</tr>
	{/foreach}
	
</table>

{assign var="paging_titles" value="KAMS, Manage Assets"}
{assign var="paging_urls" value="/?cl=kams"}
{include file="paging.html"}

<h1> Manage Assets
{if $customer->id} : {$customer->name} ({$customer->id})
{/if}
</h1>

<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="GET" name="filter"> 
{$form_redir}

<b>Customer: </b>
<select name="customer_id" onChange="document.forms['filter'].submit()">
	<option value="">[Select customer]</option>
	{foreach from=$customers item=cust key=id}
		<option value="{$id}" {if $customer->id==$id}selected{/if}>
			{$cust} {if $id!=' '}({$id}){/if}
		</option>
	{/foreach}
</select>
</form>
<p>


{if $customer->id}

<!-- Show this informtion only if a customer has been specified -->

<p>
	<a href="/?cl=kams&op=asset_add&customer_id={$customer->id}">Add asset &#0187;</a>
</p>

<table class="list" width="98%">
	<thead>
		<tr>
	
			<td class="sort_text" style="width: 16px; text-align: left;"></td>			
			<td class="sort_text" nowrap width="30" style="width: 30px; white-space: no-wrap;"> 
				<a href="{$sort_url}&order_by=id&order_dir={if $filter.order_by=='id' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
				>ID</a>&nbsp;{if $filter.order_by=='id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
			</td>
	
			<td nowrap class="sort_text" style="width: 30%">
				<a href="{$sort_url}&order_by=name&order_dir={if $filter.order_by=='name' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
				>Name
				{if $filter.order_by=='name'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
	
			<td nowrap class="sort_text" style="width: 30px">
				<a href="{$sort_url}&order_by=category&order_dir={if $filter.order_by=='category' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
				>Category
				{if $filter.order_by=='type'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
			
			<td nowrap class="sort_text" style="width: 30px">
				<a href="{$sort_url}&order_by=Supplier&order_dir={if $filter.order_by=='supplier' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
				>Supplier
				{if $filter.order_by=='supplier'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
			
			<td nowrap class="sort_text" style="width: 30px">
				<a href="{$sort_url}&order_by=invoice_no&order_dir={if $filter.order_by=='invoice_no' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
				>Invoice no.
				{if $filter.order_by=='invoice_no'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
			
			<td class="sort_text" style="width: 20px;">
				<a href="{$sort_url}&order_by=invoice_date&order_dir={if $filter.order_by=='invoice_date' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
				>Invoice date
				{if $filter.order_by=='invoice_date'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
		</tr>
	</thead>
	{foreach from=$assets item=asset}
	{assign var="fin_infos" value=$asset->get_asset_financial_infos()}
	<tr>
		<td>
			<img src="/images/logo_icon_16.gif" style="background: {if $asset->is_managed}blue{else}#cccccc{/if}" width="16" height="16"
				alt="Asset {if $asset->is_managed}managed{else}not managed{/if} by keyos" title="Asset {if $asset->is_managed}managed{else}not managed{/if} by keyos" />
		</td>
		<td>
			<a href="/?cl=kams&op=asset_edit&id={$asset->id}">{$asset->id}</a>
		</td>
		<td>
			<a href="/?cl=kams&op=asset_edit&id={$asset->id}">{$asset->name}</a>
		</td>
		<td>
			{assign var="category" value=$asset->category}
			{$category->name}
		</td>
		<td>
			{foreach from=$fin_infos item=fin_info}
			{assign var="supplier_name" value=$fin_info->get_supplier_name()}
			{if $supplier_name}
				{$supplier_name} <br />
			{else}
				-- <br />
			{/if}
			{/foreach}
		</td>
		<td>
			{foreach from=$fin_infos item=fin_info}
			{if $fin_info->invoice_number}
				{$fin_info->invoice_number} <br />
			{else}
				-- <br />
			{/if}
			{/foreach}
		</td>
		<td>
			{foreach from=$fin_infos item=fin_info}
			{if $fin_info->invoice_date}
				{$fin_info->invoice_date|date_format:$smarty.const.DATE_FORMAT_SMARTY} <br />
			{else}
			-- <br />
			{/if}
			{/foreach}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7">[No assets defined yet]</td>
	</tr>
	{/foreach}
</table>

<p>
	<a href="/?cl=kams&op=asset_add&customer_id={$customer->id}">Add asset &#0187;</a>
</p>

{/if}
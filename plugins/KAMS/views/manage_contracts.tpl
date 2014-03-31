{assign var="paging_titles" value="KAMS, Manage Contracts"}
{assign var="paging_urls" value="/?cl=kams"}
{include file="paging.html"}

<h1> Manage Contracts
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
<p>
	<a href="/?cl=kams&op=contract_add&customer_id={$customer->id}">Add contract &#0187;</a>
</p>
	<table class="list" width="95%">
		<thead>
			<tr>
				<td class="sort_text" nowrap style="width: 10px; white-space: nowrap;">ID</td>
				<td class="sort_text" nowrap style="width: 20px; white-space: nowrap;">Number</td>
				<td class="sort_text" nowrap style="width: 80px; white-space: nowrap;">Name</td>
				<td class="sort_text" nowrap style="width: 30px; white-space: nowrap;">Contract type</td>
				<td class="sort_text" nowrap style="width: 20px; white-space: nowrap;">Start date</td>
				<td class="sort_text" nowrap style="width: 200px; white-space: nowrap;">Notes</td>
			</tr>
		</thead>
		{foreach from=$contracts item=contract}
			<tr>
				<td style="width: 10px;">
				<a href="/?cl=kams&op=contract_view&customer_id={$customer->id}&contract_id={$contract->id}">{$contract->id}</a>
				</td>
				<td style="width: 20px;">
				<a href="/?cl=kams&op=contract_view&customer_id={$customer->id}&contract_id={$contract->id}">{$contract->contract_number}</a>
				</td>
				<td style="width: 80px;">
				<a href="/?cl=kams&op=contract_view&customer_id={$customer->id}&contract_id={$contract->id}">{$contract->name}</a>
				</td>
				{assign var="type" value=$contract->type}
				<td style="width: 30px;">{$type->name}</td>
				<td style="width: 20px;">{$contract->start_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
				<td style="width: 200px;">{$contract->notes}</td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="6">[No contracts added yet]</td>
			</tr>
		{/foreach}
	</table>
<p>
	<a href="/?cl=kams&op=contract_add&customer_id={$customer->id}">Add contract &#0187;</a>
</p>
{/if}


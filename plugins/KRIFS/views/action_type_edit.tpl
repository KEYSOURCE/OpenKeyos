{assign var="paging_titles" value="KRIFS, Configure Action Types, Edit Action Type"}
{assign var="paging_urls" value="/krifs, /krifs/manage_action_types"}
{include file="paging.html"}

<h1>Edit Action Type</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td colspan="2">Action type definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%" class="highlight">Name:</td>
		<td class="post_highlight"><input type="text" name="action_type[name]" value="{$action_type->name|escape}" size="70"/></td>
	</tr>
	<tr>
		<td class="highlight">Active:</td>
		<td class="post_highlight">
			<select name="action_type[active]">
				<option value="0">No</option>
				<option value="1" {if $action_type->active}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Helpdesk:</td>
		<td class="post_highlight">
			<select name="action_type[helpdesk]">
				<option value="0">No</option>
				<option value="1" {if $action_type->helpdesk}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">ERP name:</td>
		<td class="post_highlight">{$action_type->name|escape}</td>
	</tr>
	<tr>
		<td class="highlight">ERP code:</td>
		<td class="post_highlight">{$action_type->erp_code|escape}</td>
	</tr>
	<tr>
		<td class="highlight">ERP id:</td>
		<td class="post_highlight">{$action_type->erp_id|escape}</td>
	</tr>
	{if !$action_type->special_type}
	<tr>
		<td class="highlight">Category:</td>
		<td class="post_highlight">
			{assign var="category" value=$action_type->category}
			{$actypes_categories_list.$category}
		</td>
	</tr>
	{else}
	<tr>
		<td class="highlight">Special category:</td>
		<td class="post_highlight">
			{assign var="special_type" value=$action_type->special_type}
			{$ACTYPE_SPECIALS.$special_type}
		</td>
	</tr>
	{/if}
	<tr>
		<td class="highlight">Price type:</td>
		<td class="post_highlight">
			{assign var="price_type" value=$action_type->price_type}
			{$PRICE_TYPES.$price_type}
		</td>
	</tr>
	{if $action_type->price_type == $smarty.const.PRICE_TYPE_HOURLY}
	<tr>
		<td class="highlight">Billing unit:</td>
		<td class="post_highlight">
			<input type="text" name="action_type[billing_unit]" value="{$action_type->billing_unit}" size="4"/> minutes
		</td>
	</tr>
	{/if}
	
	{if !$action_type->special_type}
	<tr>
		<td class="highlight">Type:</td>
		<td class="post_highlight">
			{assign var="contract_types" value=$action_type->contract_types}
			{$CONTRACT_TYPES.$contract_types}
		</td>
	</tr>
	<tr>
		<td class="highlight">Sub-type:</td>
		<td class="post_highlight">
			{assign var="contract_sub_type" value=$action_type->contract_sub_type}
			{$CUST_SUBTYPES.$contract_sub_type}
		</td>
	</tr>
	{/if}
	<tr>
		<td class="highlight">Billable:</td>
		<td class="post_highlight">
			{if $action_type->billable}Yes
			{else}No
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Comments:</td>
		<td class="post_highlight">
			<textarea name="action_type[comments]" rows="4" cols="70">{$action_type->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>

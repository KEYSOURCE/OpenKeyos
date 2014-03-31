{assign var="paging_titles" value="Krifs, Configure Action Types"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}


<h1>Configure Action Types</h1>

<p class="error">{$error_msg}</p>

<p>These are the action types that can be assigned to a ticket detail which will eventually appear in an Intervention Report.</p>

<form action="" method="POST" name="filter_frm">
{$form_redir}
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="15%">Group by</td>
		<td width="15%">Order by</td>
		<td width="15%">Contract</td>
		<td width="15%">[Specials]</td>
		<td> </td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="filter[group_by]" onchange="document.forms['filter_frm'].submit();">
				<option value="category">Category</option>
				<option value="contract" {if $filter.group_by=='contract'}selected{/if}>Contract</option>
			</select>
		</td>
		<td>
			<select name="filter[order_by]" onchange="document.forms['filter_frm'].submit();">
				<option value="name">Name</option>
				<option value="erp_id" {if $filter.order_by=='erp_id'}selected{/if}>ERP ID</option>
			</select>
		</td>
		<td>
			<select name="filter[contract_type]" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All]</option>
				{html_options options=$CONTRACT_TYPES selected=$filter.contract_type}
			</select>
		</td>
		<td nowrap="nowrap">
			[ <a href="/?cl=krifs&amp;op=manage_action_types_special">Manage special types &#0187;</a> ]
		</td>
		<td nowrap="nowrap" align="right">
			<a href="/?cl=krifs&amp;op=action_type_add">Add action type &#0187;</a>
		</td>
	</tr>
</table>
</form>


<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="5%">ID</td>
		<td width="5%">ERP&nbsp;code</td>
		<td width="35%">Name</td>
		<td width="15%">Category<br/>(Sub-family)</td>
		<td width="5%">Active</td>
		<td width="5%">Pricing</td>
		<td width="10%">Type</td>
		<td width="10%">Sub-type</td>
		<td width="10%">Family</td>
		<td width="5%">Helpdesk</td>
		<!--
		<td width="10%"> </td>
		-->
	</tr>
	</thead>
	
	
	{foreach from=$action_types key=group_id item=actions}
		<tr class="main_row">
			<td colspan="10">
				{if $filter.group_by=='category'}
					{$actypes_categories_list.$group_id}
				{elseif $filter.group_by=='contract'}
					{$CONTRACT_TYPES.$group_id}
				{/if}
			</td>
		</tr>
		{foreach from=$actions item=action_type}
			<tr>
				<td><a href="/?cl=krifs&amp;op=action_type_edit&amp;id={$action_type->id}">{$action_type->id}</a></td>
				<td nowrap="nowrap"><a href="/?cl=krifs&amp;op=action_type_edit&amp;id={$action_type->id}">{$action_type->erp_code}</a></td>
				<td>
					<a href="/?cl=krifs&amp;op=action_type_edit&amp;id={$action_type->id}">{$action_type->name}</a>
					{if $action_type->comments}
						<br/>{$action_type->comments|escape|nl2br}
					{/if}
				</td>
				<td>
					{assign var="category" value=$action_type->category}
					{$actypes_categories_list.$category}
				</td>
				<td>
					{if $action_type->active}Yes
					{else}No
					{/if}
				</td>
				<td nowrap="nowrap">
					{assign var="price_type" value=$action_type->price_type}
					{$PRICE_TYPES.$price_type}
					{if $price_type==$smarty.const.PRICE_TYPE_HOURLY}
						<br/>
						({$action_type->billing_unit} min.)
					{/if}
				</td>
				<td>
					{assign var="contract_type" value=$action_type->contract_types}
					{$CONTRACT_TYPES.$contract_type}
				</td>
				<td>
					{if $action_type->contract_sub_type}
						{assign var="contract_sub_type" value=$action_type->contract_sub_type}
						{$CUST_SUBTYPES.$contract_sub_type}
					{else}<font class="light_text">--</font>
					{/if}
					
				</td>
				<td>
					{if $action_type->family} {$action_type->family|escape}
					{else}<font class="light_text">--</font>
					{/if}
				</td>
				<td align="center">
					{if $action_type->helpdesk}Yes
					{else}<font class="light_text">--</font>
					{/if}
				</td>

				<td align="right" nowrap="nowrap">
					<a href="/?cl=krifs&amp;op=action_type_delete&amp;id={$action_type->id}"
						onclick="return confirm ('Are you sure you want to delete this action type?');"
					>Delete &#0187;</a>
				</td>

			</tr>
		{foreachelse}
			<tr>
				<td colspan="10" class="light_text">[No action types defined yet]</td>
			</tr>
		{/foreach}
	{/foreach}
</table>
<p/>

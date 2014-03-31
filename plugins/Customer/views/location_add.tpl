{assign var="paging_titles" value="Customers, Manage Customers Locations, Add Customer Location"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_locations"}
{include file="paging.html"}

<h1>Add Customer Location</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td>Customer:</td>
		<td class="post_highlight">
			{if !$location->parent_id}
				<select name="location[customer_id]">
					<option value="">[Select customer]</option>
					{html_options options=$customers_list selected=$location->customer_id}
				</select>
			{else}
				{assign var="customer_id" value=$location->customer_id}
				{$customers_list.$customer_id}
			{/if}
		</td>
	</tr>
	<tr>
		<td>Town:</td>
		<td class="post_highlight">
			{if !$location->parent_id}
				<select name="location[town_id]">
					<option value="">[Select town]</option>
					{html_options options=$towns_list selected=$location->town_id}
				</select>
			{else}
				{assign var="town_id" value=$location->town_id}
				{$towns_list.$town_id}
			{/if}
		</td>
	</tr>
	{if $location->parent_id}
	<tr>
		<td>Parent location:</td>
		<td class="post_highlight">
			{foreach from=$location->parents item=parent name=parents_loop}
				{$parent->name}
				{if !$smarty.foreach.parents_loop.last} &#0187; {/if}
			{/foreach}
		</td>
	</tr>
	{/if}
	</thead>
	
	<tr>
		<td class="highlight" width="20%">Name:</td>
		<td class="post_highlight" width="80%">
			<input type="text" name="location[name]" value="{$location->name|escape}" size="40" />
		</td>
	</tr>
	<tr>
		<td class="highlight">Type:</td>
		<td class="post_highlight">
			<select name="location[type]">
				<option value="">[Select type]</option>
				{if $location->parent_id}
					{html_options options=$LOCATION_TYPES selected=$location->type}
				{else}
					{html_options options=$LOCATION_TYPES_TOP selected=$location->type}
				{/if}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight" nowrap="nowrap">Street address:</td>
		<td class="post_highlight">
			{if !$location->parent_id}
				<textarea name="location[street_address]" rows="4" cols="40">{$location->street_address|escape}</textarea>
			{else}
				{$location->street_address|escape|nl2br}
			{/if}
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>
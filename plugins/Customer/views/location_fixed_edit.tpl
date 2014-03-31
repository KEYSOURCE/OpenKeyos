{assign var="paging_titles" value="Customers, Manage Fixed Locations, Edit Fixed Location"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_locations_fixed"}
{include file="paging.html"}

<h1>Edit Fixed Location</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		{if $location->parent_id}
			<td>Parent:</td>
			<td class="post_highlight">{$parent->name|escape}</td>
		{else}
			<td>Type:</td>
			<td class="post_highlight">Country</td>
		{/if}
	</tr>
	</thead>
	
	{if $location->parent_id}
	<tr>
		<td class="highlight">Type:</td>
		<td class="post_highlight">
			{assign var="type" value=$location->type}
			{$LOCATION_FIXED_TYPES.$type}
		</td>
	</tr>
	{/if}
	
	<tr>
		<td class="highlight" width="20%">Name:</td>
		<td class="post_highlight" width="80%">
			<input type="text" name="location[name]" value="{$location->name|escape}" size="40" />
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />

</form>
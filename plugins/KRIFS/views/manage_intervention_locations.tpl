{assign var="paging_titles" value="Krifs, Configure Intervention Locations"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>Configure Intervention Locations</h1>

<p class="error">{$error_msg}</p>

<a href="/?cl=krifs&amp;op=intervention_location_add">Add location &#0187;</a>
<p/>

<table class="list" width="40%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="49%">Name</td>
		<td width="20%" align="center">On site</td>
		<td width="20%" align="center">Helpdesk</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$locations item=location}
		<tr>
			<td><a href="/?cl=krifs&amp;op=intervention_location_edit&amp;id={$location->id}">{$location->id}</a></td>
			<td><a href="/?cl=krifs&amp;op=intervention_location_edit&amp;id={$location->id}">{$location->name|escape}</a></td>
			<td align="center">
				{if $location->on_site}Yes
				{else}<font class="light_text">--</font>
				{/if}
			</td>
			<td align="center">
				{if $location->helpdesk}Yes
				{else}<font class="light_text">--</font>
				{/if}
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=krifs&amp;op=intervention_location_delete&amp;id={$location->id}"
					onclick="return confirm ('Are you sure you want to delete this location?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{/foreach}
</table>
<p/>
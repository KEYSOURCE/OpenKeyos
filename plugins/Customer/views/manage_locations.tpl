{assign var="paging_titles" value="Customers, Manage Customers Locations"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<h1>Manage Customers Locations</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}
<table width="95%">
	<tr><td width="50%">
		<select name="filter[customer_id]" onchange="document.forms['frm_t'].submit();">
			<option value="">[Select customer]</option>
			{html_options options=$customers_list selected=$filter.customer_id}
		</select>
	</td>
	<td align="right" nowrap="nowrap">
		|
		{if $filter.customer_id}<a href="/?cl=customer&amp;op=location_add&amp;customer_id={$filter.customer_id}"
		>Add Top Location</a> |{/if}
		<a href="/?cl=customer&amp;op=manage_locations_fixed">Manage Fixed Locations</a> |
	</td></tr>
</table>
</form>
<p/>

{if $filter.customer_id}
{assign var="last_town_id" value=0}
<table class="list" width="95%">
	<thead>
	<tr>
		<td width="25%">Name</td>
		<td width="5%">Type</td>
		<td width="65%">Computers, Peripherals</td>
		<td width="5%"> </td>
	</tr>
	</thead>
	
	{foreach from=$locations item=location}
		{if $location->town_id!=$last_town_id}
			{assign var="last_town_id" value=$location->town_id}
			<tr class="head">
				<td colspan="4" nowrap="nowrap">[{$towns_list.$last_town_id}]</td>
			</tr>
		{/if}
	
		{assign var="indent" value="1"}
		{include file="customer/manage_locations_line.html"}
	{foreachelse}
	<tr>
		<td class="light_text" colspan="4">[No location defined]</td>
	</tr>
	{/foreach}
</table>
<p />
{if $destinations_count > 0}
<table class="list" width="95%">
	<thead>
	<tr>
		<td width="100%">Check maps for routes and GPS coordinates</td>
	</tr>
	</thead>
	<tr>
		<td width="100%">
			<ul>
			{foreach from=$end_locations item="loc"}
				<li><a target="_blank" href="{$BASE_URL}/customer_map/index.php?from={$start_location}&to={$loc}">{$loc}</a>
				</li>
			{/foreach}
		</td>
	</tr>		
</table>
<div style="width: 100%;">
	<iframe src="{$BASE_URL}/customer_map/index.php?from={$start_location}&to={$primary_destination}" width="98%" height="700" />
</div>
{/if}	
</ul>
{/if}
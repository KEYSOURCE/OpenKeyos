{assign var="paging_titles" value="Customers, Manage Fixed Location, Customers Locations"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_locations"}
{include file="paging.html"}

<h1>Town : Customers Location</h1>

<p class="error">{$error_msg}</p>

<a href="/?cl=customer&amp;op=manage_locations_fixed">&#0171; Back to locations</a><p/>

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="15%">Town:</td>
		<td class="post_highlight" width="85%">{$town->name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight" nowrap="nowrap">Customer locations:</td>
		<td class="post_highlight">
		
		{foreach from=$customers_locations item=location}
			{assign var="customer_id" value=$location->customer_id}
			<a href="/?cl=customer&amp;op=customer_edit&amp;id={$location->customer_id}&amp;returl={$ret_url}"
			>{$customers_list.$customer_id} ({$customer_id})</a>
			&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;
			
			<a href="/?cl=customer&amp;op=location_edit&amp;id={$location->id}&amp;returl={$ret_url}">{$location->name|escape}</a><br/>
		{/foreach}
		</td>
	</tr>

</table>

<p><a href="/?cl=customer&amp;op=manage_locations_fixed">&#0171; Back to locations</a></p>
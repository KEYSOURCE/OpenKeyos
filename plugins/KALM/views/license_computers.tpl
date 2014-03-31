{assign var="paging_titles" value="KALM, Manage Licenses, Used Licenses"}
{assign var="customer_id" value=$license->customer_id}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_licenses&customer_id=$customer_id"}
{include file="paging.html"}

<h1>Used Licenses: {$license->software->name}</h1>
<p>
<font class="error">{$error_msg}</font>
<p>
<b>Customer: {$customer->name}</b>
<p>

<a href="/?cl=kalm&op=manage_licenses&customer_id={$customer->id}">&#0171 Back to licenses</a>
<p>

<table class="list" width="75%">
	<thead>
		<tr>
			<td width="10">ID</td>
			<td>Computer</td>
			<td>Matched software names</td>
		</tr>
	</thead>
	
	{foreach from=$computers item=computer}
	<tr>
		<td><a href="/?cl=kawacs&op=computer_view&id={$computer->id}">{$computer->id}</a></td>
		<td><a href="/?cl=kawacs&op=computer_view&id={$computer->id}">{$computer->netbios_name|escape}</a></td>
		<td>
			{if $license->matches_name ($computer->get_item('os_name'))}
				{$computer->get_item('os_name')}<br>
			{/if}
			
			{foreach from=$computer->get_item('software') item=soft}
				{if $license->matches_name($soft)}
					{$soft}<br>
				{/if}
			{/foreach}
		</td>
	</tr>
	{/foreach}
</table>
<p>
<a href="/?cl=kalm&op=manage_licenses&customer_id={$customer->id}">&#0171 Back to licenses</a>
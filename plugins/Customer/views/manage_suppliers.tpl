{assign var="paging_titles" value="Customers, Manage Suppliers"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}


<h1>Manage Suppliers</h1>

<p class="error">{$error_msg}</p>

<p><a href="/?cl=customer&amp;op=supplier_add">Add supplier &#0187;</a></p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="20%">Name</td>
		<td width="79%">Service packages</td>
		<td> </td>
	</tr>
	</thead>

	{foreach from=$suppliers item=supplier}	
	<tr>
		<td><a href="/?cl=customer&amp;op=supplier_edit&amp;id={$supplier->id}">{$supplier->id}</a></td>
		<td><a href="/?cl=customer&amp;op=supplier_edit&amp;id={$supplier->id}">{$supplier->name|escape}</a></td>
		<td>
			{foreach from=$supplier->service_packages item=package}
				{$package->name|escape}<br/>
			{foreachelse}
				<font class="light_text">[No service packages]</font>
			{/foreach}
		</td>
		<td align="right" nowrap="nowrap">
			<a href="/?cl=customer&amp;op=supplier_delete&amp;id={$supplier->id}"
				onclick="return confirm('Are you sure you want to delete this supplier?');"
			>Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="4" class="light_text">[No suppliers defined]</td>
	</tr>
	{/foreach}
</table>
<p/>

<h2>Service levels</h2>

<p><a href="/?cl=customer&amp;op=service_level_add">Add service level &#0187;</a></p>

<table class="list" width="80%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="20%">Name</td>
		<td width="60%">Description</td>
		<td width="20%"> </td>
	</tr>
	</thead>
	
	{foreach from=$service_levels item=level}
	<tr>
		<td><a href="/?cl=customer&amp;op=service_level_edit&amp;id={$level->id}">{$level->id}</a></td>
		<td><a href="/?cl=customer&amp;op=service_level_edit&amp;id={$level->id}">{$level->name}</a></td>
		<td>{$level->description|escape|nl2br}</td>
		<td align="right" nowrap="nowrap">
			<a href="/?cl=customer&amp;op=service_level_delete&amp;id={$level->id}"
				onclick="return confirm('Are you really sure you want to delete this service level?');"
			>Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="4" class="light_text">[No service levels defined yet]</td>
	</tr>
	{/foreach}
</table>
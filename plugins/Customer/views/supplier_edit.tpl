{assign var="paging_titles" value="Customers, Manage Suppliers, Edit Supplier"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_suppliers"}
{include file="paging.html"}

<h1>Edit Supplier</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2">Supplier information</td>
	</tr>
	</thead>
	
	<tr>
		<td>Name: </td>
		<td><input type="text" name="supplier[name]" value="{$supplier->name}" size="30"></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />
</form>
<p/>

<h2>Service packages</h2>

<p><a href="/?cl=customer&amp;op=service_package_add&amp;supplier_id={$supplier->id}">Add service package &#0187;</a></p>

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="30%">Name</td>
		<td width="50%">Comments</td>
		<td width="20%"> </td>
	</tr>
	</thead>
	
	{foreach from=$supplier->service_packages item=package}
	<tr>
		<td><a href="/?cl=customer&amp;op=service_package_edit&amp;id={$package->id}">{$package->name}</a></td>
		<td>{$package->description|escape|nl2br}</td>
		<td align="right" nowrap="nowrap">
			<a href="/?cl=customer&amp;op=service_package_delete&amp;id={$package->id}"
				onclick="return confirm('Are you really sure you want to delete this package?');"
			>Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3">[No service packages]</td>
	</tr>
	{/foreach}

</table>
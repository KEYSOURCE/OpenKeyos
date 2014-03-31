{assign var="supplier_id" value=$supplier->id}
{assign var="paging_titles" value="Customers, Manage Suppliers, Edit Supplier, Add Service Package"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_suppliers, /?cl=customer&op=supplier_edit&id=$supplier_id"}
{include file="paging.html"}

<h1>Add Service Package</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2">Define service package</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Supplier:</td>
		<td>{$supplier->name}</td>
	</tr>
	<tr>
		<td>Package name: </td>
		<td><input type="text" name="package[name]" value="{$package->name}" size="30"></td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="package[description]" rows="6" cols="30">{$package->description|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" />
<input type="submit" name="cancel" value="Cancel" />

</form>
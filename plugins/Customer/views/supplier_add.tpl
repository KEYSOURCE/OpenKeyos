{assign var="paging_titles" value="Customers, Manage Suppliers, Add Supplier"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_suppliers"}
{include file="paging.html"}

<h1>Add Supplier</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2">Define supplier</td>
	</tr>
	</thead>
	
	<tr>
		<td>Name: </td>
		<td><input type="text" name="supplier[name]" value="{$supplier->name}" size="30"></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" />
<input type="submit" name="cancel" value="Cancel" />

</form>
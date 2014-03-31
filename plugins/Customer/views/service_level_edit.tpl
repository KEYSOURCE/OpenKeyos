{assign var="paging_titles" value="Customers, Manage Suppliers, Edit Service Level"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_suppliers"}
{include file="paging.html"}

<h1>Edit Service Level</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2">Define service level</td>
	</tr>
	</thead>
	
	<tr>
		<td>Name: </td>
		<td><input type="text" name="level[name]" value="{$level->name}" size="30"></td>
	</tr>
	<tr>
		<td>Description:</td>
		<td>
			<textarea name="level[description]" rows="6" cols="30">{$level->description|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>
{assign var="paging_titles" value="KAWACS, Computers Roles, Edit Role"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_roles"}
{include file="paging.html"}


<h1>Edit Role</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<table width="60%" class="list">
	<thead>
	<tr>
		<td colspan="2">Role definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">ID:</td>
		<td>{$role->id}</td>
	</tr>
	<tr>
		<td>Name:</td>
		<td><input type="text" name="role[name]" size="40" value="{$role->name}" /></td>
	</tr>	
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />
</form>
{assign var="paging_titles" value="KAWACS, Computers Roles, Add Role"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_roles"}
{include file="paging.html"}


<h1>Add Role</h1>

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
		<td width="20%">Name:</td>
		<td><input type="text" name="role[name]" size="40" value="{$role->name}" /></td>
	</tr>	
</table>
<p/>

<input type="submit" name="save" value="Add" />
<input type="submit" name="cancel" value="Cancel" />
</form>
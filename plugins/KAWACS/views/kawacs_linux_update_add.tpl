{assign var="paging_titles" value="KAWACS, Manage Kawacs Linux Agent Updates, Create "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_kawacs_linux_updates"}
{include file="paging.html"}


<h1>Create Kawacs Linux Agent Release</h1>
<p>

<font class="error">{$error_msg}</font>
<p>

<form action="" method="post">
{$form_redir}


<table width="80%">
	<tr>
		<td width="20%">Version: </td>
		<td><input type="text" name="update[version]" value="{$update->version}" size="15"></td>
	</tr>
	<tr>
		<td>Comments: </td>
		<td><textarea name="update[comments]" rows="6" cols="40">{$update->comments}</textarea></td>
	</tr>
	<tr>
</table>


<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>
<p>
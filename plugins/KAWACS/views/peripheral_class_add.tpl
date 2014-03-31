{assign var="paging_titles" value="KAWACS, Manage Peripheral Classes, Add Class"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripheral_classes"}
{include file="paging.html"}


<h1>Add Peripheral Class</h1>
<p>

<font class="error">{$error_msg}</font>
<p>

<form action="" method="post">
{$form_redir}

<p>
<table width="80%">
	<tr>
		<td>Name:</td>
		<td><input type="text" name="peripheral_class[name]" size="40" value="{$peripheral_class->name}"></td>
	</tr>
</table>

<p>
<input type="submit" name="save" value="Add">
<input type="submit" name="cancel" value="Cancel">
</form>

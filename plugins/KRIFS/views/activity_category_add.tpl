{assign var="paging_titles" value="KRIFS, Configure Activities Categories, Add Category"}
{assign var="paging_urls" value="/krifs, /manage_activities"}
{include file="paging.html"}

<h1>Add Activity Category</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td colspan="2">Category definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Name:</td>
		<td><input type="text" name="category[name]" value="{$category->name|escape}" /></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" />
<input type="submit" name="cancel" value="Cancel" />

</form>

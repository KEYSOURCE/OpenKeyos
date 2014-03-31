{assign var="license_id" value=$license->id}
{assign var="paging_titles" value="KALM, Manage Licenses, Edit License, Add File"}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_licenses, /?cl=kalm&op=license_edit&id=$license_id"}
{include file="paging.html"}

<h1>Add File</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="300000000" />
{$form_redir}


<table class="list" width="80%">
	<thead>
	<tr>
		<td colspan="2">File details</td>
	</tr>
	</thead>
	
	<tr>
		<td>File:</td>
		<td><input type="file" name="upload" value="Choose file..." /></td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td><textarea name="file[comments]" rows="5" cols="50">{$file->comments|escape}</textarea></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Upload" />
<input type="submit" name="cancel" value="Cancel" />

</form>

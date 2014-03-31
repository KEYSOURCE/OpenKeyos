{assign var="license_id" value=$license->id}
{assign var="paging_titles" value="KALM, Manage Licenses, Edit License, Edit File"}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_licenses, /?cl=kalm&op=license_edit&id=$license_id"}
{include file="paging.html"}

<h1>Edit File</h1>

<p class="error">{$error_msg}</p>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}

function check_replace ()
{
	frm = document.forms['upload_frm'];
	
	ret = true;
	if (frm.elements['upload'].value != '')
	{
		ret = confirm ('Are you sure you want to replace the existing file?');
		if (!ret)
		{
			old_comments = frm.elements['file[comments]'].value;
			frm.reset();
			frm.elements['file[comments]'].value = old_comments;
		}
	}

	return ret;
}

{/literal}
//]]>
</script>


<form action="" method="POST" enctype="multipart/form-data" name="upload_frm" onsubmit="return check_replace();">
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
		<td>
			{if $file->local_filename}
				<a href="/?cl=kalm&amp;op=license_file_open&amp;id={$file->id}">{$file->original_filename}</a>
				<p/>
			{/if}
			<input type="file" name="upload" value="Choose file..." />
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td><textarea name="file[comments]" rows="5" cols="50">{$file->comments|escape}</textarea></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>

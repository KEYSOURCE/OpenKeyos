{assign var="paging_titles" value="KAWACS, Manage Kawacs Linux Agent Updates, Edit "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_kawacs_linux_updates"}
{include file="paging.html"}


<h1>Edit Kawacs Linux Agent Release</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

{if $update->published}
	<b>This release has been published, therefore you can only edit the comments</b>
{/if}
<p>

<form action="" method="post" enctype="multipart/form-data">
{$form_redir}


<table width="95%">
	<tr>
		<td width="20%">Version: </td>
		<td>
			{if !$update->published}
				<input type="text" name="update[version]" value="{$update->version}" size="15">
			{else}
				{$update->version}
			{/if}
		</td>
	</tr>
	<tr>
		<td>Comments: </td>
		<td><textarea name="update[comments]" rows="6" cols="40">{$update->comments}</textarea></td>
	</tr>
	<tr>
		<td>Installer: </td>
		<td>
			{if $update->get_installer_url()}
				<a href="http://{$update->get_installer_url()}">{$update->get_installer_url()}</a><br>
				MD5: {$update->md5}<br>
			{/if}
			
			{if !$update->published}
				<input type="file" name="installer">
			{/if}
		</td>
	</tr>
</table>
<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>
<p>


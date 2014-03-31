{assign var="paging_titles" value="KAWACS, Manage Kawacs Agent Updates, Edit "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_kawacs_updates"}
{include file="paging.html"}


<h1>Edit Kawacs Agent Release</h1>
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
		<td width="20%">General version: </td>
		<td>
			{if !$update->published}
				<input type="text" name="update[gen_version]" value="{$update->gen_version}" size="15">
			{else}
				{$update->gen_version}
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
			{/if}
			<input type="file" name="installer">
		</td>
	</tr>
</table>

<h2>Files</h2>

<table>

<table class="list" width="95%">
	<thead>
	<tr>
		<td>File</td>
		<td>Version</td>
		<td>MD5 checksum</td>
		<td>Download URL</td>
		{if !$update->published}
			<td>Upload</td>
			<td> </td>
		{/if}
	</thead>
	<tr>
	{foreach from=$KAWACS_AGENT_FILES item=file_name key=file_id}
		<tr>
			<td>{$file_name}</td>
			<td>
				{if !$update->published}
					<input type="text" name="file_versions[{$file_id}]" size="10" value="{$update->files.$file_id->version}">
				{else}
					{$update->files.$file_id->version}
				{/if}
			</td>
			<td>{$update->files.$file_id->md5}</td>
			<td>
				{if $update->files.$file_id}
					{assign var="download_url" value=$update->files.$file_id->get_download_url()}
					{if $download_url}
						<a href="http://{$download_url}">{$download_url}</a>
					{else}
						[n/a]
					{/if}
				{else}
					[n/a]
				{/if}
			</td>
			{if !$update->published}
				<td>
					<input type="file" name="uploads[{$file_id}]">
				</td>
				<td>
					{if $download_url}
                        {assign var="p" value="version_id:"|cat:$update->id|cat:",file_id:"|cat:$file_id}
						<a href="{'kawacs'|get_link:'kawacs_update_remove_file':$p:'template'}">Remove</a>
					{/if}
				</td>
			{/if}
		</tr>
	{/foreach}
</table>
<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>
<p/>

<h2>Pre-release Computers</h2>
{assign var="p" value="update_id:"|cat:$update->id}
[ <a href="{'kawacs'|get_link:'kawacs_update_preview_add':$p:'template'}">Add computer &#0187;</a> ]
<p/>

<table class="list" width="80%">
	<thead>
	<tr>
		<td width="30%">Computer</td>
		<td width="25%">Customer</td>
		<td width="25%">Current version</td>
		<td width="10%">Last contact</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$update->previews item=preview}
	<tr>
		<td>
            {assign var="p" value="id:"|cat:$preview->computer_id}
            <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">#{$preview->computer_id}: {$preview->computer->netbios_name|escape}</a>
		</td>
		<td>#{$preview->computer->customer_id}: {$preview->customer->name|escape}</td>
		<td> 
			{foreach from=$preview->current_version key=file_id item=version}
				{$KAWACS_AGENT_FILES.$file_id|escape} : {$version|escape}
				<br/>
			{/foreach}
		</td>
		<td nowrap="nowrap">{$preview->computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
		<td align="right" nowrap="nowrap">
            {assign var="p" value="update_id:"|cat:$update->id|cat:",id:"|cat:$preview->id}
			<a href="{'kawacs'|get_link:'kawacs_update_preview_delete':$p:'template'}">Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No computers set for pre-release]</td>
	</tr>
	{/foreach}

</table>

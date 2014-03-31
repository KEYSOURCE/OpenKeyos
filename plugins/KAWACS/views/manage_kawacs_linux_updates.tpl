{assign var="paging_titles" value="KAWACS, Manage Kawacs Linux Agent Updates"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Manage Kawacs Linux Agent Updates</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<a href="{'kawacs'|get_link:'kawacs_linux_update_add'}">Create new release &#0187;</a>
<p>
<table class="list" width="90%">
	<thead>
	<tr>
		<td>Version</td>
		<td>Installer</td>
		<td>Created</td>
		<td>Comments</td>
		<td>Published</td>
		<td>Publish date</td>
		<td> </td>
	</thead>
	<tr>
	
	{foreach from=$updates item=update}
		<tr>
			<td nowrap>{$update->version}</td>
			<td>
				{if $update->get_installer_url()}
					<a href="http://{$update->get_installer_url()}">Download</a>
				{else}
					[n/a]
				{/if}
			</td>
			
			<td nowrap>{$update->date_created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td>{$update->comments|nl2br}</td>
			<td>
				{if $update->published}Yes{else}No{/if}
			</td>
			<td>
				{if $update->published}
					{$update->date_published|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
				{/if}
			</td>
			<td nowrap>
				{if !$update->published}
                    {assign var="p" value="id:"|cat:$update->id}
					<a href="{'kawacs'|get_link:'kawacs_linux_update_edit':$p:'template'}">Edit</a><br>
					<a href="{'kawacs'|get_link:'kawacs_linux_update_delete':$p:'template'}"
						onClick="return confirm('Are you sure you want to delete the release {$update->gen_version}?');"
					>Delete</a>
					<br>
					<a href="{'kawacs'|get_link:'kawacs_linux_update_publish':$p:'template'}"
						onClick="return confirm('Are you sure you want to publish the release {$update->gen_version}?');"
					>Publish</a><br>
				{else}
					<a href="{'kawacs'|get_link:'kawacs_linux_update_edit':$p:'template'}">View</a>
				{/if}
				
			</td>
		</tr>
	{foreachelse}
	
		<tr><td colspan=7>
		[No releases defined so far]
		</td></tr>
	
	{/foreach}

</table>
<p>
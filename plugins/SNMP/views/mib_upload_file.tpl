{assign var="mib_id" value=$mib->id}
{assign var="paging_titles" value="KAWACS, MIBs Management, Edit MIB, Upload New File"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=snmp&op=manage_mibs, /?cl=snmp&op=mib_edit&id=$mib_id"}
{include file="paging.html"}

<h1>Upload New File</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t" enctype="multipart/form-data">
{$form_redir}

<p>
Uploading a new file will replace all previous MIB data.
</p>

<table class="list" width="600">
	<thead>
	<tr>
		<td width="140">MIB:</td>
		<td class="post_highlight">{$mib->name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Current file:</td>
		<td class="post_highlight">{$mib->orig_fname|escape}</td>
	</tr>
	<tr>
		<td class="highlight">New file:</td>
		<td class="post_highlight">
			<input type="file" name="mib_file" size="40" />
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Upload" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>

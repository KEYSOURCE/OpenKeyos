{assign var="paging_titles" value="KAWACS, MIBs Management, Upload New MIB"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=snmp&op=manage_mibs"}
{include file="paging.html"}

<h1>Set MIB Main File</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

Specify which of the files from the uploaded archive you want to process:<p/>

{assign var="cols" value=4}
<table width="98%">
	{foreach from=$mib->files_list key=file_id item=file_name name="mib_files"}
		{if ($smarty.foreach.mib_files.index % $cols)==0}<tr>{/if}
		<td nowrap="nowrap" width="33%"><input type="radio" name="main_file_id" value="{$file_id}" 
		{if $mib->main_file_id == $file_id}checked{/if}
		/> {$file_name|escape}</td>
	{/foreach}
</table>
<p/>

<input type="submit" name="save" value="Set file" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>

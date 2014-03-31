{assign var="paging_titles" value="KAWACS, MIBs Management, Upload New MIB"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=snmp&op=manage_mibs"}
{include file="paging.html"}

<h1>Upload New MIB</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t" enctype="multipart/form-data">
{$form_redir}

<p>
Specify below the MIB file to upload. If the MIB references multiple files,
please upload the entire package as a Zip archive (if it contains subfolders,
the structure will be "flattern" automatically).
</p>

<input type="file" name="mib_file" size="40" />
<p/>

<input type="submit" name="save" value="Upload" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>

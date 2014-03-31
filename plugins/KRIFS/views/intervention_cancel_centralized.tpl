{assign var="paging_titles" value="KRIFS, Edit Intervention Report, Cancel Centralization"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>Cancel Centralization</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<p>Are you ABSOLUTELY sure you want to cancel the <b>Centralized</b> status of
this intervention report and revert it to <b>Approved</b>? If you do this,
the next ERP import operation will fetch this intervention report again.</p>

<p class="error">This is a dangerous feature and should be use with extreme care!</p>

<input type="submit" name="save" value="Ok, revert status" class="button" 
	onclick="return confirm('Last chance to quit. Are you ABSOLUTELY sure you want to continue?');"
/>
<input type="submit" name="cancel" value="Abandon operation" class="button" />

</form>

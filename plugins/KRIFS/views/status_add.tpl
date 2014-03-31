{assign var="paging_titles" value="KRIFS, Configure Statuses, Add Status"}
{assign var="paging_urls" value="/krifs, /krifs/manage_statuses"}
{include file="paging.html"}

<h1>Add Status</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

Status name:
<input type="text" name="status_name">
<p>


<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel">

</form>

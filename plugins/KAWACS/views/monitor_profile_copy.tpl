{assign var="paging_titles" value="KAWACS, Manage Profiles, Copy Monitor Profile "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles"}
{include file="paging.html"}

<h1>Copy Monitor Profile</h1>
<p>
<font class="error">{$error_msg}</font>
<p>
<form action="" method="post">
{$form_redir}

Enter the name for the new monitor profile:
<p>
<input type="text" name="new_name" value="">
<p>
<input type="submit" name="copy" value="Copy">
<input type="submit" name="cancel" value="Cancel">
<p>

</form>

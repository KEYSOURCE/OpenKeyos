{assign var="paging_titles" value="KAWACS, Manage Profiles, Copy Monitor Profile "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles_periph"}
{include file="paging.html"}

<h1>Copy Peripherals Monitor Profile</h1>
<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

Enter the name for the new monitor profile:
<p/>
<input type="text" name="new_name" value="" size="50" />
<p/>
<input type="submit" name="copy" value="Copy" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
<p/>

</form>

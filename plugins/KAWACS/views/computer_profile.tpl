{assign var="paging_titles" value="KAWACS, Manage Computers, Computer Profile "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers"}
{include file="paging.html"}


<h1>Assign Profile to Computer</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

Please select the monitoring profile you want to assign to this computer:
<p>

{foreach from=$profiles item=profile}
	<input type="radio" name="profile" value="{$profile->id}" {if $profile->id==$computer->profile_id}checked{/if}>
	{$profile->name}<p>
{/foreach}

<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>

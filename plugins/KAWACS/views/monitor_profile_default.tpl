{assign var="paging_titles" value="KAWACS, Manage Profiles, Default Profile "}
{assign var="paging_urls" value="/=kawacs, /kawacs/manage_profiles"}
{include file="paging.html"}


<h1>Default Monitoring Profile</h1>
<p>

<font class="error">{$error_msg}</font>
<p>

Please select from the list below what profile should be used
as the default profile for new computers.
<p>


<form action="" method="post">
{$form_redir}

Available profiles:<p>

{foreach from=$profiles item=profile}

	<input type="radio" name="default_profile" value="{$profile->id}"
		{if $profile->id==$default_profile->id}checked{/if}
	> {$profile->name}<br>

{foreachelse}
	
	[No profiles have been defined yet. Please define first at least one profile.]

{/foreach}


<p>
{if $profiles}
	<input type="submit" name="save" value="Set">
{/if}
<input type="submit" name="cancel" value="Cancel">
</form>

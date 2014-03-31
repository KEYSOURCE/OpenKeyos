{assign var="paging_titles" value="KAWACS, Manage Profiles, Add Monitor Profile "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles"}
{include file="paging.html"}

<h1>Add Monitor Profile</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<table class="list" width="80%">
	<thead>
	<tr>
		<td colspan="2">Enter the details for the computers monitor profile</td>
	</tr>
	</thead>
	
	<tr>
		<td width="120" class="highlight">Name:</td>
		<td class="post_highlight"><input type="text" name="profile[name]" size="20" /></td>
	</tr>
	<tr>
		<td class="highlight">Description:</td>
		<td class="post_highlight"><textarea name="profile[description]" rows="6" cols="40"></textarea></td>
	</tr>
</table>

<p/>
<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>

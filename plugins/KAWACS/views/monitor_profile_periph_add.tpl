{assign var="paging_titles" value="KAWACS, Manage Profiles, Add Peripherals Monitor Profile "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles_periph"}
{include file="paging.html"}

<h1>Add Peripherals Monitor Profile</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="2">Enter the monitor profile details</td>
	</tr>
	</thead>
	
	<tr>
		<td width="120" class="highlight">Name:</td>
		<td class="post_highlight"><input type="text" name="profile[name]" size="40" /></td>
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

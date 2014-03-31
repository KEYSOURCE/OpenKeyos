{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Computer Location"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}

<h1>Set Computer Location</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

Please select the location for this computer:
<p/>
<select name="location_id">
	<option value="0">[No location]</option>
	{html_options options=$locations_list selected=$computer->location_id}
</select>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>

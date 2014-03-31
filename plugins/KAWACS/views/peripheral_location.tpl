{assign var="peripheral_id" value=$peripheral->id}
{assign var="paging_titles" value="KAWACS, Manage Peripherals, Edit Peripheral, Peripheral Locatin"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripherals, /?cl=kawacs&amp;op=peripheral_edit&amp;id=$peripheral_id"}
{include file="paging.html"}

<h1>Set Peripheral Location</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

Please select the location for this peripheral:
<p/>
<select name="location_id">
	<option value="0">[No location]</option>
	{html_options options=$locations_list selected=$peripheral->location_id}
</select>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>

{assign var="computer_id" value=$ad_printer->computer_id}
{assign var="nrc" value=$ad_printer->nrc}
{assign var="paging_titles" value="KERM, AD Printers, View AD Printer, AD Printer Location"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=manage_ad_printers, /?cl=kerm&op=ad_printer_view&computer_id=$computer_id&nrc=$nrc"}
{include file="paging.html"}


<h1>Set AD Printer Location</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

Please select the location for this AD Printer:
<p/>
<select name="location_id">
	<option value="0">[No location]</option>
	{html_options options=$locations_list selected=$location->id}
</select>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>

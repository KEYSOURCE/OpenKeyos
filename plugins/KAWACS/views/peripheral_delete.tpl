{assign var="paging_titles" value="KAWACS, Manage Peripherals, Delete or Remove Peripheral"}
{assign var="peripheral_id" value=$peripheral->id}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripherals, /?cl=kawacs&op=peripheral_edit&id=$peripheral_id"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<h1>Delete or Remove Peripheral: {$peripheral->name|escape}</h1>

<p class="error">{$error_msg}</p>

<p><b>Deleting</b> a peripheral means deleting the peripheral entirely from the database. <b>No information about it will be preserved.</b></p>

<p><b>Removing</b> a peripheral means the peripheral will be removed from the 
active database, but the information from it <b>will be archived.</b>. This is the option to use
for peripherals which are no longer in use by the customer, but for which you want to preserve the 
information in Keyos.</p>

<form action="" method="POST" name="frm_t">
{$form_redir}
<table class="list" width="80%">
	<thead>
	<tr>
		<td colspan="2">Please select below the option you prefer:</td>
	</tr>
	</thead>
	
	<tr>
		<td width="140">
			<input type="radio" class="radio" name="delete_action" value="do_delete" />
			<b>Delete peripheral</b>
		</td>
		<td style="color: red;">
			This will <b>PERMANENTLY</b> delete the peripheral from the database.
		</td>
	</tr>
	<tr>
		<td width="100">
			<input type="radio" class="radio" name="delete_action" value="do_remove" />
			<b>Remove peripheral</b>
		</td>
		
		<td>
			This will deactivate the peripheral, preserving its information in the 
			removed peripherals database.<p/>
			
			Removal date:
			<input type="text" size="12" name="removal[date_removed]" 
				value="{$removal->date_removed|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
			/>
			{literal}
			<a HREF="#" onClick="showCalendarSelector('frm_t', 'removal[date_removed]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
			
			<br/>
			Reason:
			<textarea name="removal[reason_removed]" rows="4" cols="40" style="vertical-align:top;">{$removal->reason_removed}</textarea>
		</td>
	</tr>
</table>
<p/>


<input type="submit" name="save" value="Proceed" class="button"
onclick="return confirm('Are you really sure you want to proceed with the selected action?');"
/>
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>
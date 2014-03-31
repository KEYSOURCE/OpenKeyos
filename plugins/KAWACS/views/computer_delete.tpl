{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Delete or Remove Computer"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="paging_urls" value="/kawacs, /kawacs&op=manage_computers, "|cat:"kawacs"|get_link:"computer_view":$p:"template"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<h1>Delete or Remove Computer</h1>

<p class="error">{$error_msg}</p>

<p><b>Deleting</b> a computer means deleting the computer entirely from the database. <b>No information about it will be preserved.</b></p>

<p><b>Removing</b> a computer means the computer will be removed from the 
active database, but the information from it <b>will be archived.</b>. This is the option to use
for computers which are no longer in use by the customer, but for which you want to preserve the 
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
			<b>Delete computer</b>
		</td>
		<td style="color: red;">
			This will <b>PERMANENTLY</b> delete the computer from the database.
		</td>
	</tr>
	<tr>
		<td width="100">
			<input type="radio" class="radio" name="delete_action" value="do_remove" />
			<b>Remove computer</b>
		</td>
		
		<td>
			This will deactivate the computer, preserving its information in the 
			removed computers database.<p/>
			
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
			
			
			<p/>
			<b>Important note:</b> Before removing the computer, make sure that
			it is indeed not functioning anymore. If the computer is left running
			with Kawacs Agent on it, at the next reporting it will be 
			<b>recreated as a new computer</b> in Keyos.
		</td>
	</tr>
</table>
<p/>


<input type="submit" name="save" value="Proceed" class="button"
onclick="return confirm('Are you really sure you want to proceed with the selected action?');"
/>
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>
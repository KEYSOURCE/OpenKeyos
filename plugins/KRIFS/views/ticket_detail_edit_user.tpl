{assign var="paging_titles" value="KRIFS, Ticket, Edit Ticket Detail, Change User"}
{assign var="ticket_id" value=$ticket->id}
{assign var="ticket_detail_id" value=$ticket_detail->id}
{assign var="paging_urls" value="/krifs, /krifs/ticket_edit/"|cat:$ticket_id:", /krifs/ticket_detail_edit/"|cat:$ticket_detail_id}
{include file="paging.html"}

<h1>Change Ticket Detail User</h1>

<p class="error">{$error_msg}</p>

<p>
If you change the user, the work time will be attributed to the new user,
and the activity will appear in that user's timesheet.
</p>

<form action="" method="POST" name="frm_t">
{$form_redir}
New user:
<select name="ticket_detail[user_id]">
	{html_options options=$users selected=$ticket_detail->user_id}
</select>
<p/>

<input type="submit" name="save" value="Change" class="button" />
<input type="submit" name="cancel" value="cancel" class="button" />

</form>
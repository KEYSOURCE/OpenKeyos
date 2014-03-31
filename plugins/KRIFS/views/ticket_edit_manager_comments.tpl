{assign var="ticket_id" value=$ticket->id}
{assign var="paging_titles" value="KRIFS, View Ticket, Edit Manager Comments"}
{assign var="paging_urls" value="/krifs, //ticket_edit/"|cat:$ticket_id}
{include file="paging.html"}

<h1>Edit Manager Comments</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}
<table class="list" width="80%">
	<thead>
	<tr>
		<td width="120">Ticket:</td>
		<td class="post_highlight">#{$ticket->id}: {$ticket->subject|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Comments</td>
		<td class="post_highlight"><textarea name="comments" rows="6" cols="50">{$ticket->seen_manager_comments|escape}</textarea></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>
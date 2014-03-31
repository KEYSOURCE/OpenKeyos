{assign var="paging_titles" value="Technical Support, Escalate Ticket"}
{assign var="paging_urls" value="/?cl=customer_krifs"}
{include file="paging.html"}


<h1>Escalate Ticket</h1>
<p class="error">{$error_msg}</p>

<p>If you are sure you want to escalate this ticket,
then please enter your comments and click the <b>Escalate</b>
button below:</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="100">Ticket:</td>
		<td># {$ticket->id}: {$ticket->subject|escape}</td>
	</tr>
	</thead>

	<tr>
		<td>Comments: </td>
		<td>
			<textarea name="comments" rows="10" cols="100"></textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Escalate" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>

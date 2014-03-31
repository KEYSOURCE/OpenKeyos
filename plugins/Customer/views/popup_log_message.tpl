
<div style="disply:block; padding: 10px;">

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="120">Subject:</td>
		<td class="post_highlight">{$message->subject|escape}</td>
		<td nowrap="nowrap" align="right">
			[ <a href="#" onclick="window.close(); return false;"><b>Close</b></a> ]
		</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Sent:</td>
		<td class="post_highlight" colspan="2">{$message->date_sent|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
	<tr>
		<td class="highlight">User:</td>
		<td class="post_highlight" colspan="2"><a href="/?cl=user&amp;op=user_edit&amp;id={$user->id}">{$user->get_name()|escape}</td>
	</tr>
	<tr>
		<td class="highlight">Customer:</td>
		<td class="post_highlight" colspan="2"><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer->id}">#{$customer->id}: {$customer->name|escape}</td>
	</tr>
	<tr>
		<td class="highlight">E-mail:</td>
		<td class="post_highlight" colspan="2">{$message->email}</td>
	</tr>
	<tr>
		<td class="highlight">Message:</td>
		<td class="post_highlight" colspan="2">{$message->msg_body|escape|nl2br}</td>
	</tr>
	<tr class="head">
		<td colspan="3" align="right">
			[ <a href="#" onclick="window.close(); return false;"><b>Close</b></a> ]
		</td>
	</tr>
</table>
<p/>

</div>

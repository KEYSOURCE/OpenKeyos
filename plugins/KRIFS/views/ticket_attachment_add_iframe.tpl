{assign var="paging_titles" value="KRIFS, Ticket, Add Attachment"}
{assign var="ticket_id" value=$ticket->id}
{assign var="paging_urls" value="/krifs, /krifs/ticket_edit/"|cat:$ticket_id}
{include file="paging.html"}

<h1>Add Attachment</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" enctype="multipart/form-data">
{$form_redir}

File:
<input type="file" name="attachment" value="Choose file...">
<p>


<input type="submit" name="save" value="Upload">
<input type="submit" name="cancel" value="Cancel" onclick="javascript: top.$.fancybox.close(); return false;">

</form>

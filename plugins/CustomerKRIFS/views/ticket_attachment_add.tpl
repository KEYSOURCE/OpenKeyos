{assign var="paging_titles" value="Technical Support, Ticket, Add Attachment"}
{assign var="ticket_id" value=$ticket->id}
{assign var="paging_urls" value="/?cl=krifs, /?cl=krifs&op=ticket_edit&id=$ticket_id"}
{include file="paging.html"}

<h1>Add Attachment</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" enctype="multipart/form-data">
{$form_redir}

File:
<input type="file" name="attachment" value="Choose file...">
<p/>

<input type="submit" name="save" value="Upload" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>

{assign var="paging_titles" value="KRIFS, Create Ticket, Select Notification"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}


<h1>Select Related Notification</h1>

<p class="error">{$error_msg}</p>

<p>This computer currently has a number of notifications associated with it.</p>

<p>Please specify if any of these notifications should be associated with the ticket you
are about to create:
</p>


<form action="" method="POST">
{$form_redir}

<input type="radio" name="notification_id" value="" checked> [ Don't associate with a notification ] <br/>

{foreach from=$notifications item=notification}

	<input type="radio" name="notification_id" value="{$notification->id}"> {$notification->text}<br/>

{/foreach}


<p/>
<input type="submit" name="proceed" value="Proceed">
<input type="submit" name="cancel" value="Cancel">

</form>

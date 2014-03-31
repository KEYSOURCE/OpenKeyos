{assign var="paging_titles" value="KAWACS, Manage Peripheral Classes, Delete Class"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripheral_classes"}
{include file="paging.html"}


<h1>Delete Peripheral Class</h1>
<p>

<font class="error">{$error_msg}</font>
<p>

<form action="" method="post">
{$form_redir}

<p>
Are you really sure that you want to delete the peripheral class
<b>{$peripheral_class->name}</b> (ID: <b>{$peripheral_class->id})</b>?
<p>

Please note that if you delete this class it will delete <b>ALL</b> the peripherals of this class, 
from <b>ALL</b> the customers.
<p>


<input type="submit" name="delete" value="Delete">
<input type="submit" name="cancel" value="Cancel">
</form>

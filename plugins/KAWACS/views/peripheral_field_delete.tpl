{assign var="paging_titles" value="KAWACS, Manage Peripheral Classes, Edit Class, Delete Field"}
{assign var="peripheral_class_id" value=$peripheral_class->id}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripheral_classes, /?cl=kawacs&op=peripheral_class_edit&id=$peripheral_class_id"}
{include file="paging.html"}


<h1>Delete Peripheral Class Field</h1>
<p>

<font class="error">{$error_msg}</font>
<p>

<form action="" method="post">
{$form_redir}

<p>
Are you really sure that you want to delete the field
<b>{$peripheral_class_field->name}</b> from the peripherals class
<b>{$peripheral_class->name}</b>?
<p>

Please note that if you delete this field it will delete <b>ALL</b> these fields, from 
<b>ALL</b> the peripherals of this class, 
from <b>ALL</b> the customers.
<p>


<input type="submit" name="delete" value="Delete">
<input type="submit" name="cancel" value="Cancel">
</form>

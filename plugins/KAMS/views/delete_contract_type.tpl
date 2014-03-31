{assign var="paging_titles" value="KAMS, Manage Contract Types, Edit contract type"}
{assign var="paging_urls" 	value="/?cl=kams, /?cl=kams&op=manage_contract_types"}
{assign var="fpaging_urls" value=$contract_type->id|string_format:"/?cl=kams, /?cl=kams&op=manage_contract_types, /?cl=kams&op=contract_type_edit&type_id=%d,"}
{include file="paging.html"}

<h1>Delete contract type - {$contract_type->name})</h1>
<p>
	<font class="error">{$error_msg}</font>
</p>
<p>

Are you really sure you want to delete the contract type <b>{$contract_type->name|escape} (#{$contract_type->id})</b>?
<p>

If you delete this contract type, all the contracts of this type will be set to "no type"
<p>
</p>

<form action="" method="POST">
{$form_redir}
<input type="submit" name="delete" value="Delete">
<input type="submit" name="cancel" value="Cancel">
</form>
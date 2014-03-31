{assign var="paging_titles" value="KAMS, Manage Assets, Edit Asset, Delete Asset"}
{assign var="first_title" value=$customer->id|string_format:"/?cl=kams, /?cl=kams&op=manage_assets&customer_id=%d,"}
{assign var="second_title" value=$asset->id|string_format:"/?cl=kams&op=asset_edit&id=%d"}
{assign var="paging_urls" value=$first_title|cat:$second_title}
{include file="paging.html"}

<h1>Delete asset ({$asset->name})</h1>
<p>
	<font class="error">{$error_msg}</font>
</p>
<p>

Are you really sure you want to delete the asset <b>{$asset->name|escape} ({$asset->id})</b>?
<p>

If you delete a asset, all items associated with this computer will be deleted from 
the database. 
<p>
This means associated items managed by KeyOS, financial informations and contracts where this asset is the sole object.
<p>

<form action="" method="POST">
{$form_redir}
<input type="submit" name="delete" value="Delete">
<input type="submit" name="cancel" value="Cancel">
</form>
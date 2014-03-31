{assign var="paging_titles" value="KAMS, Manage Contract Types"}
{assign var="paging_urls" value="/?cl=kams"}
{include file="paging.html"}

<h1> Manage Contract Types</h1>

<p>
<font class="error">{$error_msg}</font>
<p>

<p>
	<a href="/?cl=kams&op=contract_type_add">Add contract type &#0187;</a>
</p>

<form action="" method="POST" name="frm_contract_types"> 
{$form_redir}
<table class="list" width="98%">
<thead>
	<tr>
		<td class="sort_text" style="width: 5%; text-align: left;">Waranty</td>
		<td class="sort_text" style="width: 5%; text-align: left;">ID</td>
		<td class="sort_text" style="width: 30%; text-align: left;">Name</td>
		<td class="sort_text" style="width: 60%; text-align: left;">Description</td> 
	</tr>
</thead>
{foreach from=$contract_types item=ctype}
	<tr>
		<td>{if $ctype->is_warranty_contract}W{else}-{/if}</td>
		<td>
			<a href="/?cl=kams&op=contract_type_edit&type_id={$ctype->id}">{$ctype->id}</a></td>
		<td>
			<a href="/?cl=kams&op=contract_type_edit&type_id={$ctype->id}">{$ctype->name}</a></td>
		<td>{$ctype->description}</td>
	</tr>
{/foreach}
</table>
</form>

<p>
	<a href="/?cl=kams&op=contract_type_add">Add contract type &#0187;</a>
</p>
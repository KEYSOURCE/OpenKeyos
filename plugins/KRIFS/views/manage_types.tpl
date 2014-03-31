{assign var="paging_titles" value="Krifs, Configure Types"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}


<h1>Configure Types</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<a href="/?cl=krifs/type_add">Add type &#0187;</a>
<p>

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td>Name</td>
		<td>Billable</td>
		<td>Ignore in totals</td>
		<td width="10"> </td>
	</tr>
	</thead>
	
	{foreach from=$types item=type}
		<tr>
			<td>
                {assign var="p" value="id:"|cat:$type->id}
                <a href="{'krifs'|get_link:'type_edit':$p:'template'}">{$type->id}</a></td>
			<td><a href="{'krifs'|get_link:'type_edit':$p:'template'}">{$type->name}</a></td>
			<td>
				{if $type->is_billable}Yes{/if}
			</td>
			<td>
				{if $type->ignore_count}Yes{/if}
			</td>
			<td>
				<a href="{'krifs'|get_link:'type_delete':$p:'template'}"
					onClick="return confirm ('Are you sure you want to delete this type?');"
				>Delete</a>
			</td>
		</tr>
	{/foreach}
</table>
<p>

<form action="" method="POST">
{$form_redir}
Default type for customer created tickets:
<select name="default_customer_type">
	{html_options options=$types_list selected=$default_customer_ticket_type}
</select>
<p>
<input type="submit" class="button" name="save" value="Save">
<input type="submit" class="button" name="cancel" value="Close">
</form>
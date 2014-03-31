{assign var="paging_titles" value="KRIFS, Configure Types, Edit Type"}
{assign var="paging_urls" value="/krifs, /krifs/manage_types"}
{include file="paging.html"}

<h1>Edit Type</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td colspan="2">Ticket type definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Name:</td>
		<td><input type="text" name="type[name]" value="{$type->name}" /></td>
	</tr>
	<tr>
		<td>Ignore in counts:</td>
		<td>
			<input type="checkbox" class="checkbox" name="type[ignore_count]" {if $type->ignore_count}checked{/if} value="1"/>
		</td>
	</tr>
	<tr>
		<td>Billable:</td>
		<td>
			<input type="checkbox" class="checkbox" name="type[is_billable]" {if $type->is_billable}checked{/if} value="1"/>
		</td>
	</tr>
</table>
<p/>


<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>

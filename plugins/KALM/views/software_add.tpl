{assign var="paging_titles" value="KALM, Manage Software, Add Software Package"}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_software"}
{include file="paging.html"}

<h1>Add Software Package</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Software package definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Name: </td>
		<td><input type="text" name="software[name]" value="{$software->name}" size="40">
	</tr>
	<tr>
		<td>Manufacturer: </td>
		<td><input type="text" name="software[manufacturer]" value="{$software->manufacturer}" size="40">
	</tr>
	<tr>
		<td>Allowed licensing types: </td>
		<td>
			{foreach from=$LIC_TYPES_NAMES key=type_id item=type_name}
				<input type="checkbox" name="licensing_types[]" value="{$type_id}" {if ($software->license_types&$type_id)==$type_id}checked{/if}>
				{$type_name}<br>
			{/foreach}
		</td>
	</tr>
	<tr>
		<td>Included in reports: </td>
		<td>
			<select name="software[in_reports]">
				<option value="1">Yes</option>
				<option value="0" {if !$software->in_reports}selected{/if}>No</option>
			</select>
		</td>
	</tr>
	
</table>

<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel">

</form>
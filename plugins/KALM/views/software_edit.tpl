{assign var="paging_titles" value="KALM, Manage Software, Edit Software Package"}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_software"}
{include file="paging.html"}

<h1>Edit Software Package</h1>
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
				<input type="checkbox" name="licensing_types[]" value="{$type_id}" {if (($software->license_types&$type_id) == $type_id)}checked{/if}>
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
	
	
	{if ($software->license_types & $smarty.const.LIC_TYPE_SEAT) == $smarty.const.LIC_TYPE_SEAT}
		<!-- This section is shown only for 'Per seat' licenseses -->
	
		<tr class="head">
			<td colspan="2">Name matching rules | <a href="/?cl=kalm&op=software_rule_add&software_id={$software->id}">Add rule &#0187;</a> </td>
		</tr>

		{foreach from=$software->match_rules item=rule}

			<tr>
				<td>
					<a href="/?cl=kalm&op=software_rule_edit&id={$rule->id}">Edit</a> |
					<a href="/?cl=kalm&op=software_rule_delete&id={$rule->id}"
						onClick="return confirm('Are you sure you want to delete this rule?');"
					>Remove</a>
				</td>
				<td>
					{assign var="match_type" value=$rule->match_type}
					{$NAMES_MATCH_TYPES.$match_type} : 
					{$rule->expression}
				</td>
			</tr>

		{foreachelse}
			<tr>
				<td colspan="2">[No rules defined yet]</td>
			</tr>
		{/foreach}
		
	{/if}
	
</table>

<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">

</form>
<p>

{if ($software->license_types & $smarty.const.LIC_TYPE_SEAT) == $smarty.const.LIC_TYPE_SEAT}

	<h2>Matching Names</h2>
	<p>
	Below you have the list of current matching software names from the database.<br>
	<b>Note:</b> The name matching will be used only for "Per seat" licenses.
	<p>

	<ul>
	{foreach from=$matching_names item=match_name}
		<li>{$match_name}</li>
	{foreachelse}
		<li>[No matches found in the current database for this criteria]</li>
	{/foreach}
	</ul>
{/if}


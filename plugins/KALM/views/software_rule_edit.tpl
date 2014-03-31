{assign var="paging_titles" value="KALM, Manage Software, Edit Software Package, Edit Name Rule"}
{assign var="software_id" value=$software->id}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_software, /?cl=kalm&op=software_edit&id=$software_id"}
{include file="paging.html"}

<h1>Edit Name Matching Rule</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Software package: {$software->name} - {$software->manufacturer}</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Criteria: </td>
		<td>
			<select name="rule[match_type]">
				{html_options options=$NAMES_MATCH_TYPES selected=$rule->match_type}
			</select>
		</td>
	</tr>
	<tr>
		<td>Expression: </td>
		<td><input type="text" name="rule[expression]" value="{$rule->expression}" size="40">
	</tr>
	
</table>
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

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">

</form>

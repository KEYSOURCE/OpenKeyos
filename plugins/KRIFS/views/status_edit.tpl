{assign var="paging_titles" value="KRIFS, Configure Statuses, Edit Status"}
{assign var="paging_urls" value="/krifs, ./krifs/manage_statuses"}
{include file="paging.html"}

<h1>Edit Status</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}


<table width="60%">
	<tr>
		<td width="20%">Status name:</td>
		<td><input type="text" name="status_name" value="{$statuses_list.$id}" size="30"/>
	</tr>
	{if $ticket_class->can_escalate_status ($id)}
	<tr>
		<td>Escalate after:</td>
		<td>
			<input type="text" name="interval" value="{$interval}" size="6"/>
			<select name="unit">
				<option value="3600">hours</option>
				<option value="86400" {if $unit==86400}selected{/if}>days</option>
			</select>
		</td>
	</tr>
	{/if}
</table>
<p/>


<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>

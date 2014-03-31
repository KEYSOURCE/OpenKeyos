{assign var="paging_titles" value="KRIFS, Escalation Recipients"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>Escalation Recipients</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

Specify below the users who should be notified whe tickets are escalated.

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="10"> </td>
		<td>User</td>
	</tr>
	</thead>
	
	{foreach from=$users_list key=user_id item=user_name}
	<tr>
		<td>
			<input type="checkbox" name="recipients[]" value="{$user_id}"
				{if $escalation_recips_list.$user_id} checked {/if}
			>
		</td>
		<td>{$user_name}</td>
	</tr>
	{/foreach}
</table>
<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">

</form>
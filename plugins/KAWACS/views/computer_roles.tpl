{assign var="computer_id" value=$computer->id}
{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Edit Computer Roles"}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}

<h1>Edit Computer Roles</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<table width="60%" class="list">
	<thead>
	<tr>
		<td width="1%"> </td>
		<td width="99%">Role</td>
	</tr>
	</thead>
	
	{foreach from=$roles_list key=role_id item=role_name}
	<tr>
		<td>
			<input type="checkbox" name="roles[]" value="{$role_id}"
			{if $computer->roles.$role_id}checked{/if} />
		</td>
		<td>{$role_name}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="2" class="light_text">[No roles defined yet]</td>
	</tr>
	{/foreach}
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />
</form>
{assign var="paging_titles" value="KAWACS, Computers Roles"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>Computers Roles</h1>

<p class="error">{$error_msg}</p>

<p>
<a href="{'kawacs'|get_link:'role_add'}">Add role &#0187;</a>
</p>

<table class="list" width="60%">
		<thead>
		<tr>
			<td width="1%">ID</td>
			<td width="79%">Name</td>
			<td width="20%"> </td>
		</tr>
		</thead>
		
		{foreach from=$roles item=role}
		<tr>
            {assign var="p" value="id:"|cat:$role->id}
			<td><a href="{'kawacs'|get_link:'role_edit':$p:'template'}">{$role->id}</a></td>
			<td><a href="{'kawacs'|get_link:'role_edit':$p:'template'}">{$role->name}</a></td>
			<td align="right" nowrap="nowrap">
				<a href="{'kawacs'|get_link:'role_delete':$p:'template'}"
					onclick="return confirm ('Are you really sure you want to delete this role?');"
				>Delete&nbsp;&#0187</a>
			</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="3" class="light_text">[No roles defined yet]</td>
		</tr>
		{/foreach}
</table>
{assign var="paging_titles" value="Krifs, Configure Special Action Types"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}


<h1>Configure Special Action Types</h1>

<p class="error">{$error_msg}</p>

<p>
[ <a href="/?cl=krifs&amp;op=manage_action_types">Manage normal action types &#0187;</a> ]
</p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="5%">ERP ID</td>
		<td width="29%">Name</td>
		<td width="30%">User</td>
		<td width="20%"> </td>
	</tr>
	</thead>
	
	
	{foreach from=$action_types key=group_id item=actions}
		<tr class="main_row">
			<td colspan="5">{$ACTYPE_SPECIALS.$group_id}</td>
		</tr>
		{foreach from=$actions item=action_type}
			<tr>
				<td><a href="/?cl=krifs&amp;op=action_type_edit&amp;id={$action_type->id}">{$action_type->id}</a></td>
				<td><a href="/?cl=krifs&amp;op=action_type_edit&amp;id={$action_type->id}">{$action_type->erp_id}</a></td>
				<td>
					<a href="/?cl=krifs&amp;op=action_type_edit&amp;id={$action_type->id}">{$action_type->name}</a>
					{if $action_type->comments}
						<br/>{$action_type->comments|escape|nl2br}
					{/if}
				</td>

				<td>
					{if $action_type->user_id}
						{$action_type->user->get_name()}
					{/if}
				</td>
				
				<td align="right" nowrap="nowrap">
					<!--
					<a href="/?cl=krifs&amp;op=action_type_delete&amp;id={$action_type->id}"
						onclick="return confirm ('Are you sure you want to delete this action type?');"
					>Delete &#0187;</a>
					-->
				</td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="3" class="light_text">[No action types defined yet]</td>
			</tr>
		{/foreach}
	{/foreach}
</table>
<p/>

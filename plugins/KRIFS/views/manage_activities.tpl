{assign var="paging_titles" value="Krifs, Configure Activities"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}


<h1>Configure Activities</h1>

<p class="error">{$error_msg}</p>

<p>These are the actitivities that can be assigned to timesheet details which are not linked to a ticket detail.</p>

<table class="list" width="70%">
	<thead>
	<tr>
		<td width="2%">ID</td>
		<td width="18%">ERP ID</td>
		<td width="40%">Name</td>
		<td width="20%">Category</td>
		<td width="10%">Travel</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$activities item=activity}
		<tr>
			<td><a href="/?cl=krifs&amp;op=activity_edit&amp;id={$activity->id}">{$activity->id}</a></td>
			<td><a href="/?cl=krifs&amp;op=activity_edit&amp;id={$activity->id}">{$activity->erp_id}</a></td>
			<td><a href="/?cl=krifs&amp;op=activity_edit&amp;id={$activity->id}">{$activity->name}</a></td>
			<td>
				{if $activity->category_id}
					{assign var="category_id" value=$activity->category_id}
					{$categories_list.$category_id}
				{else}
					<font class="light_text">--</font>
				{/if}
			</td>
			<td>
				{if $activity->is_travel}Yes
				{else}<font class="light_text">--</font>
				{/if}
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=krifs&amp;op=activity_delete&amp;id={$activity->id}"
					onclick="return confirm('Are you really sure you want to delete this activity?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{/foreach}
</table>
<p/>
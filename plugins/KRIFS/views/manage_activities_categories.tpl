{assign var="paging_titles" value="Krifs, Configure Activities Categories"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>Configure Activities Categories</h1>

<p class="error">{$error_msg}</p>

<p>[ <a  href="/?cl=krifs&amp;op=activity_category_add">Add Category</a> ]</p>

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="2%">ID</td>
		<td width="33%">Name</td>
		<td width="55%">Activities</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$categories item=category}
		<tr>
			<td><a href="/?cl=krifs&amp;op=activity_category_edit&amp;id={$category->id}">{$category->id}</a></td>
			<td><a href="/?cl=krifs&amp;op=activity_category_edit&amp;id={$category->id}">{$category->name|escape}</a></td>
			<td>
				{foreach from=$category->activities key=activity_id item=activity_name}
					{$activity_name|escape}<br/>
				{foreachelse}
					<font class="light_text">--</font>
				{/foreach}
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=krifs&amp;op=activity_category_delete&amp;id={$category->id}"
					onclick="return confirm('Are you really sure you want to delete this category?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="4" class="light_text">[No categories defined]</td>
		</tr>
	{/foreach}
</table>
<p/>
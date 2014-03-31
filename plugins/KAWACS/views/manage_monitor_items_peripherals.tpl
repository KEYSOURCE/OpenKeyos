{assign var="paging_titles" value="KAWACS, Manage Monitor Items - Peripherals"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Manage Monitor Items - Peripherals</h1>

<p>Click on an item name to edit its properties.</p>

<p>
    {assign var="p" value="peripheral_item:"|cat:"1"}
    <a href="{'kawacs'|cat:'monitor_item_add':$p:'template'}">Add new item &#0187;</a></p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td>Id</td>
		<td>Name</td>
		<td>Short name</td>
		<td>Type</td>
		<td>Single/Multi</td>
		<td>Default logging</td>
		<td>Default update</td>
		<td> </td>
	</tr>
	</thead>
	
	{foreach from=$items item=item key=id}
	<tr>
		<td>{$item->id}</td>
		<td>
            {assign var="p" value="id:"|cat:$item->id}
            <a href="{'kawacs'|get_link:'monitor_item_edit':$p:'template'}">{$item->name}</a></td>
		<td>{$item->short_name}</td>
		<td>
			{$item->type_display}
			
			{foreach from=$item->struct_fields item=struct_field}
				<br/>&nbsp;- {$struct_field->name} : {$struct_field->short_name}
			{/foreach}
		</td>
		<td>{$item->multi_values_display}</td>
		<td>{$item->default_log_display}</td>
		<td>{$item->default_update} min.</td>
		
		
		<td>
			<a href="{'kawacs'|get_link:'monitor_item_delete':$p:'template'}"
			onClick="return confirm('Are you REALLY sure you want to delete the item \'{$item->name}\'?');">Delete</a>
		</td>
	</tr>
	{foreachelse}
	
	<tr>
		<td colspan="8" class="light_text">[No monitoring items have been defined so far]</td>
	</tr>
	
	{/foreach}

</table>

<p/>
{assign var="p" value="peripheral_item:"|cat:"1"}
<a href="{'kawacs'|get_link:'monitor_item_add':$p:'template'}">Add new item &#0187;</a>

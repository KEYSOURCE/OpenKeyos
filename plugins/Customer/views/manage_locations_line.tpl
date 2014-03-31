<tr>
	<td style="padding-left:{$indent++}0px;">
		<a href="/?cl=customer&amp;op=location_edit&amp;id={$location->id}"
		>{if !$location->parent_id}<b>{/if}{$location->name|escape}{if !$location->parent_id}</b>{/if}</a>
		{if $location->comments}
			<br/><i>(Comments: {$location->comments|@count})</i>
		{/if}
		{if !$location->parent_id and $location->street_address}
			<br/>
			{$location->street_address|escape|nl2br}
		{/if}
	</td>
	<td>
		{assign var="type" value=$location->type}
		{$LOCATION_TYPES.$type}
	</td>
	
	<td>
		{if $location->computers_list or $location->peripherals_list or $location->ad_printers_list}
			{if $location->computers_list}
				Computers:
				{foreach from=$location->computers_list key=id item=name name="comps_list"}
					<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$id}">#{$id}:&nbsp;{$name|escape}</a>
					{if !$smarty.foreach.comps_list.last},{/if}
				{/foreach}
				<br/>
			{/if}
			{if $location->peripherals_list}
				Peripherals:
				{foreach from=$location->peripherals_list key=id item=name name="periphs_list"}
					<a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$id}" style="white-space:nowrap;"
					>#{$id}:&nbsp;{$name|escape}</a>
					{if !$smarty.foreach.periphs_list.last},{/if}
				{/foreach}
				<br/>
			{/if}
			{if $location->ad_printers_list}
				AD Printers:
				{foreach from=$location->ad_printers_list key=cn item=name name="printers_list"}
					{assign var="computer_id" value=$printers_cn_ids.$cn->computer_id}
					{assign var="nrc" value=$printers_cn_ids.$cn->nrc}
					<a href="/?cl=kerm&amp;op=ad_printer_view&amp;computer_id={$computer_id}&amp;nrc={$nrc}">{$name|escape}</a>
					{if !$smarty.foreach.printers_list.last},{/if}
				{/foreach}
				<br/>
			{/if}
		{else}
			<font class="light_text">--</font>
		{/if}
		<br/>
	</td>
	
	<td align="right" nowrap="nowrap">
		<a href="/?cl=customer&amp;op=location_delete&amp;id={$location->id}" 
			onclick="return confirm('Are you REALLY sure you want to delete this location? This will delete all sub-locations and referencese to assigned computers and peripherals.');"
		>Delete &#0187;</a>
	</td>
</tr>
{if $location->children}
	{foreach from=$location->children item=location}
			{include file="customer/manage_locations_line.html"}
	{/foreach}
{/if}
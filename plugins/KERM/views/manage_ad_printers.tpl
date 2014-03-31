{assign var="paging_titles" value="KERM, AD Printers"}
{assign var="paging_urls" value="/?cl=kerm"}
{include file="paging.html"}

<h1>AD Printers</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter"> 
{$form_redir}

Customer:
<select name="filter[customer_id]" onChange="document.forms['filter'].submit()">
	<option value="">[Select one]</option>
	{html_options options=$customers_list selected=$filter.customer_id}
</select>
<p>
</form>

<p>
<h2>AD Printers</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="60">Asset&nbsp;No.</td>
		<td width="40%">Name</td>
		<td width="20%">SNMP</td>
		<td width="20%">Customer location</td>
		<td width="20%" align="right">AD Server</td>
	</tr>
	</thead>
	
	{foreach from=$ad_printers item=printer}
	<tr>
		<td><a href="/?cl=kerm&op=ad_printer_view&computer_id={$printer->computer_id}&nrc={$printer->nrc}">{$printer->asset_no}</a></td>
		<td><a href="/?cl=kerm&op=ad_printer_view&computer_id={$printer->computer_id}&nrc={$printer->nrc}">{$printer->name}</a></td>
		<td nowrap="nowrap">
			{if $printer->snmp_enabled}
				{assign var="snmp_computer_id" value=$printer->snmp_computer_id}
				Yes:
				<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$printer->snmp_computer_id}"
				>#{$printer->snmp_computer_id}: {$computers_list.$snmp_computer_id}</a>
			{else}--
			{/if}
		</td>
		<td>
			{if $printer->customer_location}
				<a href="/?cl=customer&amp;op=location_edit&amp;id={$printer->customer_location->id}&amp;returl={$ret_url}">
				{foreach from=$printer->customer_location->parents item=parent}
					{$parent->name} &#0187;
				{/foreach}
				{$printer->customer_location->name|escape}</a>
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right">
			{assign var="computer_id" value=$printer->computer_id}
			<a href="/?cl=kawacs&op=computer_view&id={$printer->computer_id}"
			>#{$printer->computer_id}: {$ad_servers_list.$computer_id}</a>
		</td> 
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3">[No AD Printers]</td>
	</tr>
	{/foreach}
	
</table>
<p>

{if count($orphan_printers) > 0}
<h2>Orphan AD Printers</h2>

<p>These are AD Printers which were found in AD at some point but they don't
exist in there anymore. This could mean one of the following:
<ul>
	<li>They are not in use anymore, in which case they can be marked
	as removed (if you want to keep them in the Keyos archive) or they
	can be completly deleted from Keyos.</li>
	<li>They have been renamed or deleted in AD, in which case they 
	should be completly removed.</li>
</ul>
</p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="60">Asset&nbsp;No.</td>
		<td width="40%">Name</td>
		<td>Serial number</td>
		<td width="220"> </td>
	</tr>
	</thead>
	
	{foreach from=$orphan_printers item=printer}
	<tr>
		<td>{$printer->asset_no}</td>
		<td>{$printer->name|escape}</td>
		<td>
			{if $printer->sn}{$printer->sn|escape}
			{else}--
			{/if}
		</td>
		<td nowrap="nowrap" align="right">
			<a href="/?cl=kerm&amp;op=ad_printer_delete&amp;id={$printer->id}"
			onclick="return confirm('This will delete it from Keyos, without archiving any data.\n\nAre you sure you want to delete it permanently?');"
			>Delete permanently</a>
			&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
			<a href="/?cl=kawacs_removed&amp;op=ad_printer_remove&amp;id={$printer->id}">Mark as removed</a>
		</td>
	</tr>
	{/foreach}

</table>


{/if}
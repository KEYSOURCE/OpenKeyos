{assign var="paging_titles" value="KERM, AD Printers, View AD Printer"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=manage_ad_printers"}
{include file="paging.html"}

<h1>AD Printer : {$ad_printer->name} ({$ad_printer->asset_no})</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="10%">Customer:</td>
		<td width="40%"><a href="/?cl=customer&op=customer_edit&id={$customer->id}">{$customer->name} ({$customer->id})</a></td>
	
		<td width="10%">AD Server:</b></td>
		<td width="40%">
			<a href="/?cl=kawacs&op=computer_view&id={$computer->id}"
			>#{$computer->id}: {$computer->get_item('netbios_name')}</a>
		</td>
	</tr>
	</thead>
	<tr>
		<td><b>Location:</b></td>
		<td>
			{if $ad_printer->customer_location}
				<a href="/?cl=customer&amp;op=location_edit&amp;id={$ad_printer->customer_location->id}&amp;returl={$ret_url}">
				{foreach from=$ad_printer->customer_location->parents item=parent}
					{$parent->name} &#0187;
				{/foreach}
				{$ad_printer->customer_location->name|escape}</a>
			{else}
				<font class="light_text">--</font>
			{/if}
			
			&nbsp;&nbsp;<a href="/?cl=kerm&op=ad_printer_location&amp;computer_id={$ad_printer->computer_id}&amp;nrc={$ad_printer->nrc}"
			><img src="/images/icons/edit_16_grey.png" alt="Change location" title="Change location" border="0" width="16" height="16"
			/></a>
		</td>
		<td nowrap="nowrap"><b>Managing since:</b></td>
		<td>
			{if $ad_printer->date_created}
				{$ad_printer->date_created|date_format:$smarty.const.DATE_FORMAT_SMARTY}
				&nbsp;&nbsp;<a href="/?cl=kerm&op=ad_printer_date&amp;computer_id={$ad_printer->computer_id}&amp;nrc={$ad_printer->nrc}"
				><img src="/images/icons/edit_16_grey.png" alt="Edit date" title="Edit date" border="0" width="16" height="16"
				/></a>
			{else}--{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2"></td>
		<td><b>Last updated:</b></td>
		<td>{$item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
	
	{if $notifications}
	<tr>
		<td>Notifications</td>
		<td colspan="3">
			<ul style="margin-top:0px; margin-bottom:4px;">
			{foreach from=$notifications item=notif name=periph_notifs}
					
				{assign var="notif_color" value=$notif->level}
				<li style="color: {$ALERT_COLORS.$notif_color}; margin-left: -20px; ">
						<font color="black">
						<a href="/?cl=home&op=notification_view&id={$notif->id}">{$notif->get_text()}</a>
						(#{$notif->id}; Raised: {$notif->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY})
					{* <!-- Show associated tickets, if any --> *}
					
					{if $notif->ticket_id}
					<div style="display:block; margin-left:10px; border-left:1px solid #dddddd; padding: 3px;">
						{assign var="ticket_id" value=$notif->ticket_id}
						<a href="/?cl=krifs&op=ticket_edit&id={$notif->ticket_id}">Ticket #{$notif->ticket_id}</a>: {$notifications_tickets.$ticket_id->subject}
						<br/>
						{assign var="status" value=$notifications_tickets.$ticket_id->status}
						<b>Status:</b> {$TICKET_STATUSES.$status}
						&nbsp;&nbsp;&nbsp;
						
						{assign var="assigned_id" value=$notifications_tickets.$ticket_id->assigned_id}
						<b>Assigned to:</b> {$users_list.$assigned_id}
						<br/>
					</div>
					{/if}
					</font>
				</li>
			{/foreach}
			</ul>
		<td>
	</tr>
	{/if}
	
</table>
<p>
<a href="/?cl=kerm&op=manage_ad_printers">&#0171; Back to AD Printers</a>
<p/>


{if $item->itemdef->type != $smarty.const.MONITOR_TYPE_STRUCT}

	<!-- Not a structure -->
	<table class="list" width="98%">
	<thead><td>{$item->itemdef->name}</td></thead>
	{foreach from=$item->val item=val key=nrc}
		<tr><td>
			{$item->get_formatted_value($nrc)} 
		</td></tr>
	{/foreach}
	</table>
	
{elseif count($item->itemdef->struct_fields) <= 8}

	<!-- Structure, but with not many fields, so fields can be displayed in columns -->
	<table class="list" width="98%">
		<thead>
		<tr>
			{foreach from=$item->itemdef->struct_fields item=field_def}
			<td>
				{$field_def->name}
			</td>
			{/foreach}
		</tr>
		</thead>
		
		{foreach from=$item->val item=val key=nrc}
			<tr>
			<!-- foreach from=$val->value item=val_field key=val_key} -->
			{foreach from=$item->itemdef->struct_fields item=field_def}
				{assign var="val_key" value=$field_def->id}
				<td>{$item->get_formatted_value($nrc, $val_key)|replace:" , ":"<br>"}</td>
			{/foreach}
			</tr>
		{/foreach}
	</table>
{else}
	<table width="98%">
	<tr><td width="50%">
	<!-- Show warranty information for AD Printers -->
	<table class="list" width="90%">
		<thead>
		<tr>
			<td width="120">Warranty information</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=kerm&op=ad_printer_warranty_edit&computer_id={$item->computer_id}&nrc={$ad_printer->nrc}">Edit warranty &#0187;</a>
			</td>
		</tr>
		</thead>
		
		<tr>
			<td class="highlight">Asset number:</td>
			<td class="post_highlight">{$ad_printer->asset_no}</td>
		</tr>
		<tr>
			<td class="highlight">Serial number:</td>
			<td class="post_highlight">{$ad_printer->sn}</td>
		</tr>
		<tr>
			<td class="highlight">Warranty starts:</td>
			<td class="post_highlight">
				{if $ad_printer->warranty_starts}
					{$ad_printer->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
				{else}
					-
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">Warranty ends:</td>
			<td class="post_highlight">
				{if $ad_printer->warranty_ends}
					{$ad_printer->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}
				{else}
					-
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">Service package:</td>
			<td class="post_highlight">
				{assign var="service_package_id" value=$ad_printer->service_package_id}
				{$service_packages_list.$service_package_id}
			</td>
		</tr>
		<tr>
			<td class="highlight">Service level:</td>
			<td class="post_highlight">
				{assign var="service_level_id" value=$ad_printer->service_level_id}
				{$service_levels_list.$service_level_id}
			</td>
		</tr>
		<tr>
			<td class="highlight">Contract number:</td>
			<td class="post_highlight">{$ad_printer->contract_number|escape}</td>
		</tr>
		<tr>
			<td class="highlight">HW product ID:</td>
			<td class="post_highlight">{$ad_printer->hw_product_id|escape}</td>
		</tr>
		<tr>
			<td class="highlight">Product number:</td>
			<td class="post_highlight">{$ad_printer->product_number|escape}</td>
		</tr>
	</table>
	<td><td width="50%">
	<!-- Show SNMP information -->
	<table class="list" width="100%">
		<thead>
		<tr>
			<td width="120">SNMP Monitoring</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=kerm&amp;op=ad_printer_edit_snmp&amp;computer_id={$ad_printer->computer_id}&amp;nrc={$ad_printer->nrc}"
				>Edit SNMP settings &#0187;</a>
			</td>
		</tr>
		</thead>
		
		<tr>
			<td class="highlight">SNMP enabled:</td>
			<td class="post_highlight">{if $ad_printer->snmp_enabled}Yes{else}No{/if}</td>
		</tr>
		<tr>
			<td class="highlight">Monitor profile:</td>
			<td class="post_highlight">
				{if $ad_printer->profile_id}
					<a href="/?cl=kawacs&amp;op=monitor_profile_periph_edit&amp;id={$monitor_profile->id}">{$monitor_profile->name|escape}</a>
				{else}--
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">Monitoring computer:</td>
			<td class="post_highlight">
				{if $ad_printer->snmp_computer_id}
					{assign var="snmp_computer_id" value=$ad_printer->snmp_computer_id}
					<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$snmp_computer_id}">#{$snmp_computer_id}: {$computers_list.$snmp_computer_id}</a>
				{else}--
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">SNMP IP:</td>
			<td class="post_highlight">
				{if $ad_printer->snmp_ip}{$ad_printer->snmp_ip}
				{else}--
				{/if}
			</td>
		</td>
		<tr>
			<td class="highlight">Last SNMP contact:</td>
			<td class="post_highlight">
				{if $ad_printer->last_contact}{$ad_printer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				{else}--
				{/if}
			</td>
		</tr>
		
		{foreach from=$discoveries item=discovery}
		<tr class="head">
			<td>Matching discovery:</td>
			<td class="post_highlight">
				{assign var="detail_id" value=$discovery->detail_id}
				By: #{$disc_details.$detail_id->computer_id}: {$disc_details.$detail_id->computer_name}, &nbsp;&nbsp;
				{$disc_details.$detail_id->ip_start}&nbsp;-&nbsp;{$disc_details.$detail_id->ip_end}
			</td>
		</tr>
		<tr>
			<td class="highlight">Last discovered:</td>
			<td class="post_highlight">
				<a href="/?cl=discovery&amp;op=discovery_details&amp;id={$discovery->id}"
				>{$discovery->last_discovered|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a>
			</td>
		</tr>
		<tr>
			<td class="highlight">Name:</td>
			<td class="post_highlight">{$discovery->get_name()|escape}</td>
		</tr>
		<tr>
			<td class="highlight">IP Address:</td>
			<td class="post_highlight">{$discovery->ip}</td>
		</tr>
		{/foreach}
	</table>
	
	</td></tr></table>
	<p/>

	{if $ad_printer->snmp_enabled}
	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="6">SNMP Data</td>
		</tr>
		<tr>
			<td width="30">ID</td>
			<td width="160">Name/Updated</td>
			<td width="30%">Value</td>
			<td width="30">ID</td>
			<td width="160">Name/Updated</td>
			<td width="30%">Value</td>
		</tr>
		</thead>
		
		{assign var="cols" value="2"}
		{foreach from=$ad_printer->values_snmp key=snmp_item_id item=snmp_item name=all_snmp_items}
			{if ($smarty.foreach.all_snmp_items.index % $cols) == 0}<tr>{/if}
		
			<td width="30"><b>{$snmp_item_id}</b></td>
			<td width="120">
				{$snmp_item->itemdef->name}
				{if $snmp_item->reported}
					<br/>{$snmp_item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				{/if}
			</td> 
			<td>
				{foreach from=$snmp_item->val key=nrc item=val name=snmp_vals}
					{if $smarty.foreach.snmp_vals.index >= 1}<p/>{/if}
					{if is_array($val->value)}
						{assign var="item_fields" value=$snmp_item->itemdef->struct_fields}
						{assign var="cnt" value=0}
						
						{foreach from=$val->value item=val_field key=val_key name=snmp_vals_multi}
							{$snmp_item->fld_names.$val_key}:&nbsp;{$snmp_item->get_formatted_value($nrc, $val_key)|escape}
							{if !$smarty.foreach.snmp_vals_multi.last}<br/>{/if}
						{/foreach}
					{else}{$snmp_item->get_formatted_value($nrc)|escape}{/if}
					
				{/foreach}
			</td>
		{/foreach}
		{if ($smarty.foreach.all_snmp_items.index % $cols) == 0}<td></td><td></td><td></td></tr>{/if}
	</table>
	<p/>
	{/if}

	<!-- Structure with many fields, fields will be displayed in rows -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="2">Active Directory data</td>
		</tr>
		</thead>
		
		{assign var="nrc" value=$index_nrc}
		{assign var="val" value=$item->val.$nrc}
	
		{if is_array($val->value)}
		
			{foreach from=$val->value item=val_field key=val_key}
			<tr>
				<td width="15%">{$item->fld_names.$val_key}:</td>
				<td>
					{$item->get_formatted_value($nrc, $val_key)|replace:" , ":"<br>"}
				</td>
			</tr>
			{/foreach}
		{/if}
		
		{if !$item->itemdef->main_field_id}
			<tr class="head"><td colspan="3"> </td></tr>
		{/if}
		
	</table>
	 

{/if}
<p>
<a href="/?cl=kerm&op=manage_ad_printers">&#0171; Back to AD Printers</a>
<p>
{assign var="paging_titles" value="KAWACS, SNMP Monitored Devices"}
{assign var="paging_urls" value="/?cl=kawacs"}
{include file="paging.html"}


<h1>SNMP Monitored Devices</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

Customer:
<select name="filter[customer_id]" onchange="document.forms['frm_t'].submit();">
	<option value="">[All Customers]</option>
	{html_options options=$customers_list selected=$filter.customer_id}
</select>
&nbsp;&nbsp;&nbsp;

Objects type:
<select name="filter[obj_class]" onchange="document.forms['frm_t'].submit();">
	<option value="">[All Types]</option>
	{html_options options=$SNMP_OBJ_CLASSES selected=$filter.obj_class}
</select>
<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td>Customer</td>
		<td>Type</td>
		<td>Device</td>
		<td>IP Address</td>
		<td>Monitored by</td>
		<td>Monitor Profile</td>
	</tr>
	</thead>
	
	{foreach from=$snmp_devices key=customer_id item=customer_devices}
		{foreach from=$customer_devices key=obj_class item=devices}
			{foreach from=$devices item=device}
			<tr>
				<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customers_list.$customer_id} ({$customer_id})</a></td>
				<td>{$SNMP_OBJ_CLASSES.$obj_class}</td>
				<td>
					{if $obj_class==$smarty.const.SNMP_OBJ_CLASS_COMPUTER}
						<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$device.obj_id}">#{$device.obj_id}: {$device.obj_name}</a>
					{elseif $obj_class==$smarty.const.SNMP_OBJ_CLASS_PERIPHERAL}
						<a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$device.obj_id}">#{$device.obj_id}: {$device.obj_name}</a>
					{elseif $obj_class==$smarty.const.SNMP_OBJ_CLASS_AD_PRINTER}
						<a href="/?cl=kerm&amp;op=ad_printer_view&amp;id={$device.obj_id}">{$device.obj_name}</a>
					{/if}
				</td>
				<td>
					{if $device.snmp_ip}{$device.snmp_ip|escape}
					{else}-
					{/if}
				</td>
				<td>
					{if $device.computer_id}
						<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$device.computer_id}">#{$device.computer_id}: {$device.computer_name}</a>
					{else}-
					{/if}
				</td>
				<td>
					{if $obj_class==$smarty.const.SNMP_OBJ_CLASS_COMPUTER}
						<a href="/?cl=kawacs&amp;op=monitor_profile_edit&amp;id={$device.profile_id}">{$device.profile_name}</a>
					{else}
						<a href="/?cl=kawacs&amp;op=monitor_profile_periph_edit&amp;id={$device.profile_id}">{$device.profile_name}</a>
					{/if}
				</td>
			</tr>
			{/foreach}
		{/foreach}
	{foreachelse}
	<tr>
		<td class="light_text" colspan="5">[No SNMP devices]</td>
	</tr>
	{/foreach}
</table>

</form>
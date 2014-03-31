{assign var="paging_titles" value="KAWACS, Computer Quick Contacts"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Computer Quick Contacts</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="filter"> 
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td>ID</td>
		<td>Name</td>
		<td>Contact</td>
		<td>Customer</td>
		<td>Computer info</td>
		<td>Network info</td>
		<td> </td>
	</thead>
	<tr>
	
	{foreach from=$contacts item=contact}
		<tr>
			<td>
				{if $contact->computer->id}
                {assign var="p" value="id:"|cat:$contact->computer->id}
                <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$contact->computer->id}</a>
				{elseif $contact->computer_id}
					{$contact->computer_id}<br>
					[unknown ID]
				{else}
					[n/a]
				{/if}
			</td>
			<td>
				{if $contact->computer->id}
                    {assign var="p" value="id:"|cat:$contact->computer->id}
                    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$contact->computer_name}</a>
				{else}
					{$contact->computer_name}
				{/if}
			</td>
			<td>{$contact->contact_time|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td>{$contact->customer->name}</td>
			
			<td nowrap>
				User name: {$contact->user_name}<br>
				Manufacturer: {$contact->computer_manufacturer}<br>
				Model: {$contact->computer_model}<br>
				SN: {$contact->computer_sn}
			</td>
			
			<td nowrap>
				IP address: {$contact->net_local_ip}<br>
				Gateway: {$contact->net_gateway_ip}<br>
				Remote IP: {$contact->net_remote_ip}<br>
				MAC: {$contact->net_mac_address}
			</td>
			
			<td>
                {assign var="p" value="id:"|cat:$contact->id}
                <a href="{'kawacs'|get_link:'quick_contact_delete':$p:'template'}">Remove</a>
			</td>
		</tr>
	{/foreach}

</table>
<p>

{* This is the template normally shown to a customer user *}

{assign var="paging_titles" value="Customer Internet Contract"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<h1>Edit Customer Internet Contract</h1>

<p class="error">{$error_msg}</p>

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="140">Customer:</td>
		<td width="40%" class="post_highlight">#{$customer->id}: {$customer->name}</td>
		<td width="140"> </td>
		<td> </td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Provider/Contract:</td>
		<td class="post_highlight">
			{assign var="contract_id" value=$contract->contract_id}
			{$contracts_list.$contract_id}
		</td>
		<td class="highlight">Line type:</td>
		<td class="post_highlight">
			{assign var="line_type" value=$contract->line_type}
			{$LINE_TYPES.$line_type}
		</td>
	</tr>
	<tr>
		<td class="highlight">Client number:</td>
		<td class="post_highlight">
			{if $contract->client_number}{$contract->client_number|escape}
			{else}--{/if}
		</td>
		<td class="highlight">ADSL line number:</td>
		<td class="post_highlight">
			{if $contract->adsl_line_number}{$contract->adsl_line_number|escape}
			{else}--{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Start date:</td>
		<td class="post_highlight">
			{if $contract->start_date}{$contract->start_date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}
			{else}--{/if}
		</td>
		<td class="highlight">End date:</td>
		<td class="post_highlight">
			{if $contract->end_date}{$contract->end_date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}
			{else}--{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Contract active:</td>
		<td class="post_highlight">
			{if $contract->is_closed}No{else}Yes{/if}
		</td>
		<td class="highlight">Notice period:</td>
		<td class="post_highlight" nowrap="nowrap">
			{if $contract->notice_months}{$contract->notice_months} months
			{else}--{/if}
			
			{if $contract->date_notified}
				<br/>
				Notified: {$contract->date_notified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{/if}
		</td>
	</tr>
	{if $contract->date_notified}
	<tr>
		<td colspan="2"> </td>
		<td class="highlight">Notify again:</td>
		<td class="post_highlight" nowrap="nowrap">
			{if $contract->notice_days_again==15}15 days before expiration
			{elseif $contract->notice_days_again==30}>1 month before expiration
			{elseif $contract->notice_days_again==60}2 months before expiration
			{else}--{/if}
			
			{if $contract->notice_again_sent}
				<br/>
				Notified: {$contract->notice_again_sent|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{/if}
		</td>
	</tr>
	{/if}
	
	<tr>
		<td colspan="4"><h2>Technical details</h2></td>
	</tr>
	<tr>
		<td class="highlight">Speed maxim:</td>
		<td class="post_highlight">
			Down: {$contract->speed_max_down|number_format:0} KB /
			Up: {$contract->speed_max_up|number_format:0} KB
		</td>
		<td class="highlight">Speed guaranteed:</td>
		<td class="post_highlight">
			Down: {$contract->speed_guaranteed_down|number_format:0} KB /
			Up: {$contract->speed_guaranteed_up|number_format:0} KB
		</td>
	</tr>
	<tr>
		<td class="highlight">Contract or login:</td>
		<td class="post_highlight">{$contract->contract_or_login|escape}</td>
		<td class="highlight">Password:</td>
		<td class="post_highlight">{$contract->password|escape}</td>
	</tr>
	<tr>
		<td class="highlight">IP range:</td>
		<td class="post_highlight">{$contract->ip_range|escape}</td>
		<td class="highlight">IP address:</td>
		<td class="post_highlight">{$contract->ip_address|escape}</td>
	</tr>
	
	<tr>
		<td rowspan="5" class="highlight">Comments:</td>
		<td rowspan="5" class="post_highlight">{$contract->comments|escape|nl2br}</td>
		
		<td class="highlight">Lan - IP:</td>
		<td class="post_highlight">{$contract->lan_ip|escape}</td>
	</tr>
	
	<tr>
		<td class="highlight">Netmask:</td>
		<td class="post_highlight">{$contract->netmask|escape}</td>
	</tr>
	<tr>
		<td class="highlight">Has router:</td>
		<td class="post_highlight">{if $contract->has_router}Yes{else}No{/if}</td>
	</tr>
	<tr>
		<td class="highlight">Has SMTP feed:</td>
		<td class="post_highlight">{if $contract->has_smtp_feed}Yes{else}No{/if}</td>
	</tr>
	<tr>
		<td class="highlight">Managed by Keysource:</td>
		<td class="post_highlight">{if $contract->is_keysource_managed}Yes{else}No{/if}</td>
	</tr>
</table>
<p/>

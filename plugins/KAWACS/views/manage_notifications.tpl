{assign var="paging_titles" value="KAWACS, Notifications Status"}
{assign var="paging_urls" value="/customer"}
{include file="paging.html"}

{assign var="computer_id" value=$filter.computer_id}

<h1>Notifications Status</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter_frm">
{$form_redir}

<table width="98%">
	<tr><td width="70%" nowrap="nowrap">
		<b>Interval:</b>
		<select name="filter[interval]" onchange="document.forms['filter_frm'].submit();">
			<option value="">[All open notifications]</option>
			{html_options options=$log_months selected=$filter.interval}
		</select>
		&nbsp;&nbsp;&nbsp;
		<b>Linked to:</b>
		<select name="filter[object_class]" onchange="document.forms['filter_frm'].submit();">
			{html_options options=$types selected=$filter.object_class}
		</select>
		&nbsp;&nbsp;&nbsp;
		<b>Sort:</b>
		<select name="filter[order_dir]" onchange="document.forms['filter_frm'].submit();">
			<option value="ASC">Oldest first</option>
			<option value="DESC" {if $filter.order_dir=='DESC'}selected{/if}>Newest first</option>
		</select>
	</td><td width="30%" nowrap="nowrap" align="right">
		<b>Total:</b> {$notifications|@count} notifications
	</td></tr>
</table>
<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="20">ID</td>
		<td>Subject</td>
		<td width="100">Raised</td>
		{if $filter.interval}
			<td width="100">Ended</td>
			<td width="100">Duration</td>
		{/if}
		<td>Linked to</td>
		{if $filter.object_class != $smarty.const.NOTIF_OBJ_CLASS_KRIFS}
			<td>Ticket</td>
		{/if}
	</tr>
	</thead>
	
	{foreach from=$notifications item=notification}
	<tr>
		{assign var="level" value=$notification->level}
		<td style="color: {$ALERT_COLORS.$level}">{$notification->id}</td>
		<td>
			{if $notification->ended}
				{$notification->text|escape}
			{else}
                {assign var="p" value="id:"|cat:$notification->id}
				<a href="{'home'|get_link:'notification_view':$p:'template'}">{$notification->text|escape}</a>
			{/if}
		</td>
		<td nowrap="nowrap">{$notification->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
		
		{if $filter.interval}
		<td nowrap="nowrap">
			{if $notification->ended}
				{$notification->ended|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}
				--
			{/if}
		</td>
		<td nowrap="nowrap">
			{$notification->duration|format_interval}
		</td>
		{/if}
		
		<td>
			{if !$filter.interval}
				{* Current notifications *}
				{if $notification->object_id and $notification->object_class}
					<a href="http://{$notification->object_url}">{$notification->object_name}</a>
				{else}
					--
				{/if}
			{else}
				{* Notifications log *}
				{if $notification->object_class == $smarty.const.NOTIF_OBJ_CLASS_COMPUTER}
					{assign var="computer_id" value=$notification->object_id}
					{assign var="customer_id" value=$computers.$computer_id->customer_id}
                    {assign var="p" value="id:"|cat:$notification->object_id}
                    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}"
					>#{$computer_id}: {$computers_list.$computer_id} ({$customers_list.$customer_id})</a>
					
				{elseif $notification->object_class == $smarty.const.NOTIF_OBJ_CLASS_INTERNET}
					{assign var="ip_id" value=$notification->object_id}
					{assign var="customer_id" value=$ips.$ip_id->customer_id}

                    {assign var="p" value="id:"|cat:$ip_id}
					<a href="{'kawacs'|get_link:'monitored_ip_edit':$p:'template'}">#{$customer_id}: {$customers_list.$customer_id} ({$ips.$ip_id->target_ip})</a>
					
				{elseif $notification->object_class == $smarty.const.NOTIF_OBJ_CLASS_KRIFS}
					{assign var="ticket_id" value=$notification->object_id}

                    {assign var="p" value="id:"|cat:$ticket_id}
					<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">#{$ticket_id}: {$tickets.$ticket_id->subject|escape}</a>
				{/if}
			{/if}
		</td>
		{if $filter.object_class != $smarty.const.NOTIF_OBJ_CLASS_KRIFS}
		<td>
			{if $notification->ticket_id}
                {assign var="p" value="id:"|cat:$notification->ticket_id}
				<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">#{$notification->ticket_id}&nbsp;&#0187;</a>
			{else}
				--
			{/if}
		</td>
		{/if}
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7" class="light_text">[No notifications]</td>
	</tr>
	{/foreach}
	
</table>

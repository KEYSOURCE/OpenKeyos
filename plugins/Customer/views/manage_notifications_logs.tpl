{assign var="paging_titles" value="Customers, Notifications Logs"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

{assign var="computer_id" value=$filter.computer_id}

<h1>Notifications Logs {if $computer_id} : {$computers_list.$computer_id}{/if}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter">
{$form_redir}

<table width="98%">
	<tr>
		<td><b>Customer:</b></td>
		{if $filter.customer_id}
			<td><b>Interval:</b></td>
			<td><b>Show:</b></td>
			<td><b>Computer:</b></td>
			<td> </td>
		{/if}
	</tr>
	<tr>
		<td width="40%">
			<select name="filter[customer_id]"  
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
		{if $filter.customer_id}
			<td width="20%" nowrap="nowrap">
				<select name="filter[month_start]">
					{html_options options=$log_months selected=$filter.month_start}
				</select>
				-
				<select name="filter[month_end]">
					{html_options options=$log_months selected=$filter.month_end}
				</select>
			</td>
			<td>
				<select name="filter[show]">
					<option value="1">By event</option>
					<option value="2" {if $filter.show==2}selected{/if}>By computer</option>
				</select>
			</td>
			<td>
				<select name="filter[computer_id]">
					<option value="">[All]</option>
					{html_options options=$computers_list selected=$filter.computer_id}
				</select>
			</td>
			<td align="right">
				<input type="submit" value="Apply filter">
			</td>
		{/if}
	</tr>
</table>
</form>
<p/>

<script language="JavaScript">
	var groups = new Array ({$notifications|@count})
	var groups_stat = new Array ({$notifications|@count})
	var all_collapsed = false
</script>

{if $customer->id and $filter.show==1}
	
	<!-- Show notifications by event type -->
	
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="10"><a href="#" onClick="action_all ();"><img src="/images/expand.gif" width="10" height="11" id="img_all"></a></td>
			<td colspan="5" onClick="action_all ();">Notifications</td>
		</tr>
		</thead>
		
		{foreach from=$notifications key=group_id item=notifs_group}
			{assign var="alert_color" value=$notifs_group->level}
			{assign var="alert_color" value=$ALERT_COLORS.$alert_color}
			
			<script language="JavaScript">
				var i = 0;
				groups[{$group_id}] = new Array ();
				groups_stat[{$group_id}] = false;
			</script>
			
			<tr class="cathead">
				<td bgcolor="{$alert_color}" 
					width="10"><a href="#" onClick="group_action({$group_id});"><img src="/images/expand.gif" width="10" height="11" id="img_{$group_id}"></a
				></td>
				<td width="15%" nowrap="nowrap" onClick="group_action({$group_id});">
					{$notifs_group->text}
				</td>
				<td colspan="3">
					Count: {$notifs_group->count}
				</td>
			</tr>
			
	
			<tr id="group_row_{$group_id}_a" style="display: none;">
	
					<script language="JavaScript">
					groups[{$group_id}][i++] = '{$group_id}_a'
					</script>
				
				<td bgcolor="{$alert_color}" width="10">  </td>
				<td align="right">[Duration]</td>
				<td align="right">[Raised]</td>
				<td align="right">[Ended]</td>
				<td>
					{if $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_COMPUTER}
						[Computer]
					{elseif $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_KRIFS}
						[Ticket]
					{elseif $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_INTERNET}
						[Monitored IP/Target IP]
					{elseif $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_INTERNET_CONTRACT}
						[Internet Contract]
					{elseif $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_SOFTWARE}
						[Software License]
					{/if}
				</td>
			</tr>
			{foreach from=$notifs_group->notifications key=notification_id item=notification}
				<tr id="group_row_{$group_id}_{$notification_id}" style="display: none;">
	
					<script language="JavaScript">
					groups[{$group_id}][i++] = '{$group_id}_{$notification_id}'
					</script>
				
					<td bgcolor="{$alert_color}"> </td>
				
					<td align="right" width="15%">{$notification->duration|format_interval}</td>
					<td align="right" width="15%">{$notification->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
					<td align="right" width="15%">
						{if $notification->ended}
							{$notification->ended|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
						{else}
							-
						{/if}
					</td>
					
					<td width="55%">
						{assign var="object_id" value=$notification->object_id}
						{if $notification->object_class == $smarty.const.NOTIF_OBJ_CLASS_COMPUTER}
							<a href="/?cl=kawacs&op=computer_view&id={$object_id}"
							>{$computers_list.$object_id}</a>
						{elseif $notification->object_class == $smarty.const.NOTIF_OBJ_CLASS_KRIFS}
							<a href="/?cl=krifs&op=ticket_edit&id={$object_id}"
							>#{$notification->object_id}: {$tickets_list.$object_id}</a>
						{elseif $notification->object_class == $smarty.const.NOTIF_OBJ_CLASS_INTERNET}
							<a href="/?cl=kawacs&amp;op=monitored_ip_edit&amp;id={$object_id}&amp;returl={$ret_url}"
							>#{$notification->object_id}: {$monitored_ips_list.$object_id}</a>
						{elseif $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_INTERNET_CONTRACT}
							<a href="/?cl=klara&amp;op=customer_internet_contract_edit&amp;id={$object_id}&amp;returl={$ret_url}"
							>#{$notification->object_id}: {$internet_contracts_lists.$object_id}</a>
						{elseif $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_SOFTWARE}
							<a href="/?cl=kalm&amp;op=manage_licenses&amp;license_id={$object_id}"
							>{$software_list.$object_id}</a>
						{/if}
					</td>
				</tr>
			{/foreach}
		{foreachelse}
			<tr>
				<td colspan="6">[No notifications]</td>
			</tr>
		{/foreach}
	</table>
{/if}

{if $customer->id and $filter.show==2}
	
	<!-- Show notifications by computers -->
	
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="10"><a href="#" onClick="action_all ();"><img src="/images/expand.gif" width="10" height="11" id="img_all"></a></td>
			<td colspan="5" onClick="action_all ();">Notifications</td>
		</tr>
		</thead>
		
		{foreach from=$notifications key=group_id item=notifs_group}
			<tr class="cathead">
				<script language="JavaScript">
					var i = 0;
					groups[{$group_id}] = new Array ();
					groups_stat[{$group_id}] = false;
				</script>
				
				<td width="10"><a href="#" onClick="group_action({$group_id});"><img src="/images/expand.gif" width="10" height="11" id="img_{$group_id}"></a></td>
				<td width="30%" nowrap="nowrap" onClick="group_action({$group_id});" colspan="2">
					
					{$computers_list.$group_id}
				
				</td>
				<td colspan="2">
					Count: {$notifs_group->count}
				</td>
			</tr>
			
	
			<tr id="group_row_{$group_id}_a" style="display: none;">
	
					<script language="JavaScript">
					groups[{$group_id}][i++] = '{$group_id}_a'
					</script>
				
				<td width="10"> </td>
				<td align="right">[Duration]</td>
				<td align="right">[Raised]</td>
				<td align="right">[Ended]</td>
				<td>
					{if $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_COMPUTER}
						[Computer]
					{elseif $notifs_group->object_class == $smarty.const.NOTIF_OBJ_CLASS_KRIFS}
						[Ticket]
					{/if}
				</td>
			</tr>
			{foreach from=$notifs_group->notifications key=notification_id item=notification}
				{assign var="alert_color" value=$notification->level}
				{assign var="alert_color" value=$ALERT_COLORS.$alert_color}
				<tr id="group_row_{$group_id}_{$notification_id}" style="display: none;">
	
					<script language="JavaScript">
					groups[{$group_id}][i++] = '{$group_id}_{$notification_id}'
					</script>
				
					<td width="10"></td>
				
					<td align="right" width="15%">{$notification->duration|format_interval}</td>
					<td align="right" width="15%">{$notification->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
					<td align="right" width="15%">
						{if $notification->ended}
							{$notification->ended|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
						{else}
							-
						{/if}
					</td>
					
					<td width="55%">
						<li style="color: {$alert_color}; margin-left: 15px;"><font color="black">{$notification->text}</font></li>
					</td>
				</tr>
			{/foreach}
		{foreachelse}
			<tr>
				<td colspan="6">[No notifications]</td>
			</tr>
		{/foreach}
	</table>
{/if}

{if $customer->id}
{literal}
<script language="JavaScript">

function action_all ()
{
	img = document.getElementById('img_all');
	if (all_collapsed)
	{
		img.src = '/images/collapse.gif';
		all_collapsed = false;
	}
	else
	{
		img.src = '/images/expand.gif';
		all_collapsed = true;
	}

	for (gr=0; gr<groups.length; gr++)
	{
		stat = groups_stat[gr];
		if (stat == all_collapsed)
		{
			group_action (gr);
		}
	}
}

action_all ()

function group_action (id)
{
	stat = groups_stat[id];
	groups_stat[id] = (!groups_stat[id]);

	img = document.getElementById('img_'+id);
	if (stat)
	{
		img.src = '/images/expand.gif';
	}
	else
	{
		img.src = '/images/collapse.gif';
	}


	for (i=0; i<groups[id].length; i++)
	{
	
		if (line = document.getElementById('group_row_'+groups[id][i]))
		{
		
			if (stat)
			{
				line.style.display = 'none';
			}
			else
			{
				line.style.display = '';
			}
		}
	}
}

</script>

{/literal}
{/if}
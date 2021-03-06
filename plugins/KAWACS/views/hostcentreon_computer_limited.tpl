
<link rel="stylesheet" href="main.css" type="text/css" />
<h1>View Computer : {$computer->netbios_name|escape} (#{$computer->id} : {$computer->asset_no})</h1>
{if $computer->is_manual}
	<h4 style="border:0px;">
		[Note: manually created computer
        {assign var="p" value="id:"|cat:$computer->id|cat:',returl:'|cat:$ret_url}
		<a target="_blank" href="{'kawacs'|get_link:'computer_edit':$p:'template'}"
		><img src="/images/icons/edit_16_grey.png" alt="Edit Computer" title="Edit Computer" border="0" width="16" height="16"
		/></a> ]
	</h4>
{/if}

<p>
<font class="error">{$error_msg}</font>
<p>

{if $blackout}
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="30%">NOTE: This computer is blacked out</td>
		<td>
			Start date:
			{if $blackout->start_date}
				{$blackout->start_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}
			{else}
				-
			{/if}
			&nbsp;&nbsp;&nbsp;&nbsp;
			End date:
			{if $blackout->end_date}
				{$blackout->end_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}
			{else}
				-
			{/if}
		</td>
		<td align="right">
            {assign var="p" value="customer_id:"|cat:$computer->customer_id}
			<a target="_blank" href="{'kawacs'|get_link:'blackouts_edit':$p:'template'}">Edit blackout &#0187;</a>
		</td>
	</tr>
	</thead>
</table>
<p/>
{/if}

<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="30%">
			Customer:<br/>
            {assign var="p" value="id:"|cat:$customer->id}
			<a target="_blank" href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customer->name} ({$customer->id})</a>&nbsp;
            {assign var="p" value="id:"|cat:$computer->id}
            <a href="{'kawacs'|get_link:'computer_customer':$p:'template'}"
			><img src="/images/icons/edit_16_grey.png" alt="Change customer" title="Change customer" border="0" width="16" height="16"
			/></a>
			<br/>
		</td>
		<td width="30%">
			Location:<br/>
			{if $computer->location}
                {assign var="p" value="id:"|cat:$computer->location->id|cat:",returl:"|cat:$ret_url}
			<a target="_blank" href="{'customer'|get_link:'location_edit':$p:'template'}"
			alt="{foreach from=$computer->location->parents item=parent}{$parent->name|escape} &#0187; {/foreach}{$computer->location->name|escape}"
			title="{foreach from=$computer->location->parents item=parent}{$parent->name|escape} &#0187; {/foreach}{$computer->location->name|escape}"
			>{$computer->location->name|escape}</a>
			{else}
				--
			{/if}
            {assign var="p" value="id:"|cat:$computer->id}
			&nbsp;<a target="_blank" href=".{'kawacs'|get_link:'computer_location':$p:'template'}"
			><img src="/images/icons/edit_16_grey.png" alt="Change location" title="Change location" border="0" width="16" height="16"
			/></a>
		</td>
		<td width="20%" nowrap="nowrap">
			Profile:
			{if $computer->profile_id}
				{assign var="profile_id" value=$computer->profile_id}
                {assign var="p" value="id:"|cat:$profile_id}
				<a target="_blank" href="{'kawacs'|get_link:'monitor_profile_edit':$p:'template'}">{$profiles_list.$profile_id}</a>
			{else}
				<font class="error">[None yet!]</font>
			{/if}
            {assign var="p" value="id:"|cat:$computer->id}
			&nbsp;<a target="_blank" href="{'kawacs'|get_link:'computer_profile':$p:'template'}"
			><img src="/images/icons/edit_16_grey.png" alt="Change profile" title="Change profile" border="0" width="16" height="16"
			/></a>
			<br/>

			Type:
			{if $computer->type}
				{assign var="computer_type" value=$computer->type}
				{$COMP_TYPE_NAMES.$computer_type}
			{else}
				[None]
			{/if}
            {assign var="p" value="id:"|cat:$computer->id}
			&nbsp;<a target="_blank" href="{'kawacs'|get_link:'computer_type':$p:'template'}"
			><img src="/images/icons/edit_16_grey.png" alt="Change computer type" title="Change computer type" border="0" width="16" height="16"
			/></a>
		</td>
		<td width="10%" nowrap="nowrap">
			Last contact:<br/>
			{if $computer->last_contact}{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}--
			{/if}
		</td>
		<td width="10%" nowrap="nowrap" align="right">
                        {if $current_user->type == $smarty.const.USER_TYPE_KEYSOURCE}
                        <a target="_blank" href="https://secure.logmein.com{if $logmein->logmein_id}/mycomputers_connect.asp?hostid={$logmein->logmein_id}{/if}" target="_blank">
                            <img src="/images/icons/logmein_icon.png" alt="LogMeIn" title="LogMeIn" border="0"/>
                        <a/>
                        {/if}
            {assign var="p" value="id:"|cat:$computer->id}
			<a target="_blank" href="{'kawacs'|get_link:'computer_remote_access':$p:'template'}"
			><img src="/images/icons/gear_connection_32.png" alt="Remote access" title="Remote access" border="0"/></a>
		</td>
	</tr>
	</thead>
</table>

{assign var="current_user_id" value=$current_user->id}
<table width="98%" class="list">
	{if $computer->roles}
	<tr>
		<td width="15%"><b>Roles:</b></td>
		<td colspan="2">
			{foreach from=$computer->roles key=role_id item=role_name name=computer_roles}
				{$role_name}{if !$smarty.foreach.computer_roles.last}, {/if}
			{/foreach}
		</td>
	</tr>
	{/if}

	{if $computer->comments}
	<tr>
		<td width="15%"><b>Comments:</b></td>
		<td colspan="2">{$computer->comments|escape|nl2br}<br></td>
	</tr>
	{/if}

	{if $computer->internet_down and $monitored_ip}
	<tr>
		<td><b>Internet down:</b></td>
		<td align="right" style="color:red; width:1%; text-align: right;">
			{if $monitored_ip->notification->now_working}
				<a target="_blank"
				{if $monitored_ip->notification->now_working.$current_user_id}
                    {assign var="p" value="id:"|cat:$monitored_ip->notification->ticket_id}
					href="{'krifs'|get_link:'ticket_edit':$p:'template'}"
				{else}
                    {assign var="p" value="id:"|cat:$monitored_ip->notification->ticket_id|cat:',returl:'|cat:$ret_url}
					href="{'krifs'|get_link:'ticket_mark_working':$p:'template'}"
					onclick="return confirm('Do you want to mark that you are working now on this issue?');"
				{/if}
				><img src="/images/wavelan-locked-16.gif" width="16" height="16" alt="" title="" border="0"></a>
				{foreach from=$monitored_ip->notification->now_working key=user_id item=since}
					<br/>{$users_logins_list.$user_id|upper}
				{/foreach}
			{else}
				<a target="_blank"
				{if $monitored_ip->notification->ticket_id}
                    {assign var="p" value="id:"|cat:$monitored_ip->notification->ticket_id|cat:',returl:'|cat:$ret_url}
                    href="{'krifs'|get_link:'ticket_mark_working':$p:'template'}"
				{else}
                    {assign var="p" value="object_id:"|cat:$monitored_ip->id|cat:",object_class:"|cat:$smarty.const.TICKET_OBJ_CLASS_MONITORED_IP|cat:",notification_id:"|cat:$monitored_ip->notification->id|cat:",subject:"|cat:$monitored_ip->notification->get_text()|urlencode|cay:",mark_now_working:"|cat:"1"}
                    href="{'krifs'|get_link:'ticket_add':$p:'template'}"
				{/if}

				onclick="return confirm('Do you want to mark that you are working now on this issue?');"
				><img src="/images/wavelan-grey-16.gif" style="background-color: white;" width="16" height="16"
				alt="Mark working now" title="Mark working now" border="0"></a>
			{/if}
		</td>
		<td>
			<ul style="margin-top:0px; margin-bottom:4px;">
				{assign var="notif_color" value=$monitored_ip->notification->level}
				<li style="color: {$ALERT_COLORS.$notif_color}; margin-left: -20px; ">
					<font color="black">
					<a target="_blank" href="/?cl=home&op=notification_view&id={$monitored_ip->notification->id}">{$monitored_ip->notification->get_text()}</a>
					(#{$monitored_ip->notification->id}; Raised: {$monitored_ip->notification->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY})
				{* <!-- Show associated tickets, if any --> *}

				{if $monitored_ip->notification->ticket_id}
				<div style="display:block; margin-left:10px; border-left:1px solid #dddddd; padding: 3px;">
					{assign var="ticket_id" value=$monitored_ip->notification->ticket_id}
					<a target="_blank" href="/?cl=krifs&op=ticket_edit&id={$monitored_ip->notification->ticket_id}">Ticket #{$monitored_ip->notification->ticket_id}</a>: {$notifications_tickets.$ticket_id->subject}
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
			</ul>
		</td>
	</tr>
	{/if}

	{if $notifications}
		{foreach from=$notifications item=notif name=computer_notifs}
			<tr {if !$smarty.foreach.computer_notifs.last}class="no_bottom_border"{/if}>
				<td>{if $smarty.foreach.computer_notifs.first}<b>Notifications:</b>{/if}</td>
				<td align="right" style="color:red; width:1%; text-align: right;">
					{if $notif->now_working}
						<a target="_blank"
						{if $notif->now_working.$current_user_id}
							href="/?cl=krifs&op=ticket_edit&id={$notif->ticket_id}"
						{else}
							href="/?cl=krifs&amp;op=ticket_mark_working&amp;id={$notif->ticket_id}&amp;returl={$ret_url}"
							onclick="return confirm('Do you want to mark that you are working now on this issue?');"
						{/if}
						><img src="/images/wavelan-locked-16.gif" width="16" height="16" alt="" title="" border="0"></a>
						{foreach from=$notif->now_working key=user_id item=since}
							<br/>{$users_logins_list.$user_id|upper}
						{/foreach}
					{else}

						<a target="_blank"
						{if $notif->ticket_id}
							href="/?cl=krifs&amp;op=ticket_mark_working&amp;id={$notif->ticket_id}&amp;returl={$ret_url}"
						{else}
							href="/?cl=krifs&amp;op=ticket_add&amp;object_id={$computer->id}&amp;object_class=1&amp;notification_id={$notif->id}&amp;subject={$notif->get_text()|urlencode}&amp;mark_now_working=1"
						{/if}

						onclick="return confirm('Do you want to mark that you are working now on this issue?');"
						><img src="/images/wavelan-grey-16.gif" style="background-color: white;" width="16" height="16"
						alt="Mark working now" title="Mark working now" border="0"></a>
					{/if}

				</td>
				<td>
					<ul style="margin-top:0px; margin-bottom:4px;">
						{assign var="notif_color" value=$notif->level}
						<li style="color: {$ALERT_COLORS.$notif_color}; margin-left: -20px; ">
							<font color="black">
							<a target="_blank" href="/?cl=home&op=notification_view&id={$notif->id}">{$notif->get_text()}</a>
							(#{$notif->id}; Raised: {$notif->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY})
						{* <!-- Show associated tickets, if any --> *}

						{if $notif->ticket_id}
						<div style="display:block; margin-left:10px; border-left:1px solid #dddddd; padding: 3px;">
							{assign var="ticket_id" value=$notif->ticket_id}
							<a target="_blank" href="/?cl=krifs&op=ticket_edit&id={$notif->ticket_id}">Ticket #{$notif->ticket_id}</a>: {$notifications_tickets.$ticket_id->subject}
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
					</ul>
				</td>
			</tr>
		{/foreach}
	{/if}


	{if $tickets}
		{foreach from=$tickets item=ticket name=computer_tickets}
			<tr {if !$smarty.foreach.computer_tickets.last}class="no_bottom_border"{/if}>
				<td>{if $smarty.foreach.computer_tickets.first}<b>Tickets:</b>{/if}</td>
				<td style="color:red; width: 1%; text-align: right;">
					{if $ticket->now_working}
						<a target="_blank"
						{if $ticket->now_working.$current_user_id}
							href="/?cl=krifs&op=ticket_edit&id={$ticket->id}"
						{else}
							href="/?cl=krifs&amp;op=ticket_mark_working&amp;id={$ticket->id}&amp;returl={$ret_url}"
							onclick="return confirm('Do you want to mark that you are working now on this issue?');"
						{/if}
						><img src="/images/wavelan-locked-16.gif" width="16" height="16" alt="" title="" border="0"></a>
						{foreach from=$ticket->now_working key=user_id item=since}
							<br/>{$users_logins_list.$user_id|upper}
						{/foreach}
					{else}
						<a target="_blank" href="/?cl=krifs&amp;op=ticket_mark_working&amp;id={$ticket->id}&amp;returl={$ret_url}"
						onclick="return confirm('Do you want to mark that you are working now on this issue?');"
						><img src="/images/wavelan-grey-16.gif" style="background-color: white;" width="16" height="16"
						alt="Mark working now" title="Mark working now" border="0"></a>
					{/if}

				</td>
				<td>
					<ul style="margin-top:0px; margin-bottom:4px;">
						{assign var="notif_color" value=$notif->level}
						<li style="margin-left: -20px;">
							<a target="_blank" href="/?cl=krifs&op=ticket_edit&id={$ticket->id}">Ticket #{$ticket->id}</a>:
							{$ticket->subject}<br/>

							{assign var="status" value=$ticket->status}
							<b>Status:</b> {$TICKET_STATUSES.$status}
							&nbsp;&nbsp;&nbsp;

							{assign var="assigned_id" value=$ticket->assigned_id}
							<b>Assigned to:</b> {$users_list.$assigned_id}
							<br/>
						</li>
					</ul>
				</td>
			</tr>
		{/foreach}
	{/if}

	{if $computer_peripherals}
	<tr>
		<td width="15%"><b>Peripherals:</b></td>
		<td colspan="2">
			{foreach from=$computer_peripherals key=class_id item=peripherals}
				<b>{$peripherals_classes_list.$class_id}:</b><br>
				{foreach from=$peripherals item=peripheral}
					<a target="_blank" href="/?cl=kawacs&op=peripheral_edit&id={$peripheral->id}">{$peripheral->name}</a><br>
				{/foreach}
			{/foreach}
		</td>
	</tr>
	{/if}
</table>
<p>


<script language="JavaScript" type="text/javascript">
//<![CDATA

// The names of available tabs
var tabs = new Array ('reported_info', 'tools', 'notes', 'tickets', 'communications', 'reporting');

var computer_id = {$computer->id};

{literal}
// Retrieve a cookie value by name
function getCookie (cookie_name)
{
	var nameEQ = cookie_name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

// Set the active tab
function showTab (tab_name)
{
	// Hide all tabs first. Also make sure the tab is in the list
	found = false;
	for (i=0; i<tabs.length; i++)
	{
		document.getElementById('tab_' + tabs[i]).style.display = 'none';
		document.getElementById('tab_head_' + tabs[i]).className = 'tab_inactive';
		if (tabs[i] == tab_name) found = true;
	}

	if (!found) tab_name = tabs[0];

	document.getElementById('tab_'+tab_name).style.display = 'block';
	document.getElementById('tab_head_'+tab_name).className = '';

	document.cookie = 'computer_view_tab='+tab_name;

	return false;
}

// Open the traceroute window
var last_traceroute_window = false;
function runTraceroute ()
{
	if (last_traceroute_window) last_traceroute_window.close ();

	popup_url = '/?cl=kawacs&op=popup_traceroute&computer_id='+computer_id;
	last_traceroute_window = window.open (popup_url, 'Traceroute', 'dependent, scrollbars=yes, resizable=yes, width=100, height=100');
	return false;
}


{/literal}
//]]>
</script>

<table class="tab_header"><tr>
	<td id="tab_head_reported_info" class="tab_inactive"><a href="#" onclick="return showTab('reported_info');">Reported Info</a></td>
	<td id="tab_head_tools" class="tab_inactive"><a href="#" onclick="return showTab('tools');">Tools</a></td>
	<td id="tab_head_notes" class="tab_inactive"><a href="#" onclick="return showTab('notes');">Notes [{$notes|@count}]</a></td>
	<td id="tab_head_tickets" class="tab_inactive"><a href="#" onclick="return showTab('tickets');" style="width:120px;">Tickets History [{$tickets_history|@count}]</a></td>
	<td id="tab_head_communications" class="tab_inactive"><a href="#" onclick="return showTab('communications');">Communications</a></td>
	<td id="tab_head_reporting" class="tab_inactive"><a href="#" onclick="return showTab('reporting');" style="width:160px"
	>Reporting &amp; Discoveries [{$discoveries|@count}]</a></td>
</tr></table>

<!-- Tab with computer reported information -->
<div id="tab_reported_info" class="tab_content" style="display:none;">
{assign var="category" value=""}
<h2>Reported Information</h2>
<br/>
<table class="list" width="100%">
	<thead>
	<tr>
		<td width="2%">ID</td>
		<td width="15%">Name/Updated</td>
		<td width="33%">Value</td>
		<td width="2%">ID</td>
		<td width="15%">Name/Updated</td>
		<td width="33%">Value</td>
	</tr>
	</thead>

	<!-- Two-columns format -->

	{foreach from=$items item=item key=category_id name=items}
		{if $category!=$item->itemdef->category_id}
			{assign var="category" value=$item->itemdef->category_id}
			</tr>
			<tr class="head">
				<td colspan="6">[ {$MONITOR_CAT[$category]} ]</td>
			</tr>
			<tr>
			{if $smarty.foreach.items.iteration%2} {assign var="pass_line" value="0"}
			{else} {assign var="pass_line" value="1"}
			{/if}
		{else}{if ($smarty.foreach.items.iteration+$pass_line)%2}<tr>{/if}{/if}
			{assign var="rows" value=$item->val|@count}
			{if $rows==0}{assign var="rows" value=1}{/if}

			{assign var="fields_count" value=$item->itemdef->struct_fields|@count}
			{if $fields_count==0}{assign var="fields_count" value=1}{/if}

			{if $fields_count*$rows > 15}
				{* <!-- If there are too many lines, put them in a textarea box --> *}
				<td><b>{$item->item_id}</b></td>
				<td>
					{if $item->itemdef->is_editable() or $computer->is_manual}
						<a target="_blank" href="/?cl=kawacs&op=computer_edit_item&computer_id={$computer->id}&item_id={$item->itemdef->id}"><b>{$item->itemdef->name}</b></a>
					{else}{$item->itemdef->name}{/if}<br/>
					{$item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
					{if $item->log_enabled}
						<br>{if $item->has_logs} <a target="_blank" href="/?cl=kawacs&op=computer_view_log&computer_id={$computer->id}&item_id={$item->item_id}">Logs &#0187;</a>
						{else} <a target="_blank" href="/?cl=kawacs&op=computer_view_log&computer_id={$computer->id}&item_id={$item->item_id}">[No logs yet]</a>
						{/if}
					{/if}
				</td>
				<td>
<textarea rows="7" cols="40" readonly wrap=off style="overflow: auto;"
	onDblClick="document.location = '/?cl=kawacs&op=computer_view_item&id={$computer->id}&item_id={$item->itemdef->id}';"
>{assign var="cnt_items" value=0}{foreach from=$item->val item=val key=nrc}{if $cnt_items++ < $smarty.const.MAX_COMPUTER_ITEMS_SHOWN}
{if is_array($val->value)}
-{foreach from=$val->value item=val_field key=val_key name=array_list}
{if $smarty.foreach.array_list.iteration>1} {/if}{strip}
		{$item->fld_names.$val_key}:&nbsp;{$item->get_formatted_value($nrc, $val_key)|escape}
{/strip}
{/foreach}
{else}
{$item->get_formatted_value($nrc)}
{/if}
{/if}{/foreach}{if count($item->val)>$smarty.const.MAX_COMPUTER_ITEMS_SHOWN}

[There are over {$smarty.const.MAX_COMPUTER_ITEMS_SHOWN} items, double-click to see the rest]{/if}</textarea>
				</td>

			{else}
				{*<!-- There are only a few lines, show them as table rows -->*}
				<td><b>{$item->item_id}</b></td>
				<td>
					{if $computer->is_manual or $item->itemdef->is_editable()}
						<a target="_blank" href="/?cl=kawacs&op=computer_edit_item&computer_id={$computer->id}&item_id={$item->itemdef->id}">{$item->itemdef->name}</a>
					{else}{$item->itemdef->name}{/if}<br/>
					{$item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
					{if $item->log_enabled}
						<br/>{if $item->has_logs} <a target="_blank" href="/?cl=kawacs&op=computer_view_log&computer_id={$computer->id}&item_id={$item->item_id}">Logs &#0187;</a>
						{else} <a target="_blank" href="/?cl=kawacs&op=computer_view_log&computer_id={$computer->id}&item_id={$item->item_id}">[No logs yet]</a>{/if}
					{/if}
				</td>

				<td onDblClick="document.location = '/?cl=kawacs&op=computer_view_item&id={$computer->id}&item_id={$item->itemdef->id}';">
				{foreach from=$item->val item=val key=nrc}
					{if is_array($val->value)}
						{assign var="item_fields" value=$item->itemdef->struct_fields}
						{assign var="cnt" value=0}

						{foreach from=$val->value item=val_field key=val_key}
							{if $item_fields.$cnt->type==$smarty.const.MONITOR_TYPE_FILE}
								{* Don't escape the fields of type file, so the URL is correctly visible *}
								{$item->fld_names.$val_key}:&nbsp;{$item->get_formatted_value($nrc, $val_key)}
							{else}{$item->fld_names.$val_key}:&nbsp;{$item->get_formatted_value($nrc, $val_key)|escape}{/if}<br/><!-- {$cnt++} -->
						{/foreach}
					{else}{$item->get_formatted_value($nrc)|escape}{/if}
					<br>
				{/foreach}
				</td>
			{/if}
	{/foreach}
</table>
<p/>
</div>

<!-- Tab with computer tools -->
<div id="tab_tools" class="tab_content" style="display:none;">
	<h2>Tools</h2>
	<br/>

	<table width="100%">
		<tr>
		<td width="250">
			<b>Common Tools:</b><br/>
			<a target="_blank" href="/?cl=kawacs&op=computer_roles&id={$computer->id}">Edit roles &#0187;</a><br/>
			<a target="_blank" href="/?cl=kawacs&op=computer_comments&id={$computer->id}">Edit comments &#0187;</a><br/>
			<a target="_blank" href="/?cl=krifs&op=ticket_add&object_id={$computer->id}&object_class={$smarty.const.TICKET_OBJ_CLASS_COMPUTER}&check_notifs=1">Create ticket &#0187;</a><br/>

			{if $blackout}
				<a target="_blank" href="/?cl=kawacs&op=blackout_computer_remove&id={$computer->id}">Remove computer blackout &#0187;</a>
			{else}
				<a target="_blank" href="/?cl=kawacs&op=blackout_computer&id={$computer->id}">Blackout computer &#0187;</a>
			{/if}
			<br/>

			<a target="_blank" href="/?cl=customer&op=manage_notifications_logs&computer_id={$computer->id}">View notifications log &#0187;</a><p/>

			<b>Full update:</b><br/>
			{if $computer->request_full_update}
				Pending... |
				<a target="_blank" href="/?cl=kawacs&op=computer_view_submit&cancel_full_update=now&id={$computer->id}">Cancel &#0187;</a>
			{else}
				None pending |
				<a target="_blank" href="/?cl=kawacs&op=computer_view_submit&request_full_update=now&id={$computer->id}">Request now &#0187;</a>
			{/if}
			<p/>

			<b>Merging:</b><br/>
			<a target="_blank" href="/?cl=kawacs&op=computers_merge&id={$computer->id}">Merge computers &#0187;</a>
			<p/>

			<b>Delete or Remove:</b><br/>
			<a target="_blank" href="/?cl=kawacs&amp;op=computer_delete&amp;id={$computer->id}">Delete or Remove &#0187;</a><br />

			<p />
			{if $stolen_computer}
			<b style="color: red;">This computer is stolen: </b>
			{else}
			<b>This computer is stolen: </b>
			{/if}
			<input type="checkbox" name="stolen_computer" id="stolen_computer" onchange="submit()" {if $stolen_computer}checked="checked"{/if} />
		</td>
		<td>
			{if $is_logging_partitions or $is_logging_backup or $is_logging_av}
				<b>Reports:</b><br/>
				{if $is_logging_partitions}
					<a target="_blank" href="/?cl=kawacs&op=computer_report_partitions&id={$computer->id}">Disk space &#0187;</a>
					<br/>
				{/if}
				{if $is_logging_backup}
					<a target="_blank" href="/?cl=kawacs&op=computer_report_backup_sizes&id={$computer->id}">Backups size &#0187;</a>
					<br/>
					<a target="_blank" href="/?cl=kawacs&op=computer_report_backups&id={$computer->id}">Backups age &#0187;</a>
					<br/>
				{/if}
				{if $is_logging_av}
					<a target="_blank" href="/?cl=kawacs&op=computer_report_av&id={$computer->id}">AV updates age &#0187;</a>
					<br/>
				{/if}
				<br/>
			{/if}

			{if $is_requesting_events}
				<b>Computer events log:</b><br/>
				<a target="_blank" href="/?cl=kawacs&amp;op=computer_view_item&amp;id={$computer->id}&amp;item_id={$smarty.const.EVENTS_ITEM_ID}">View Events Log &#0187;</a>
				<br/>
				<a target="_blank" href="/?cl=kawacs&amp;op=computer_events_settings&amp;id={$computer->id}">Edit settings &#0187;</a>
				<br/><br/>
			{/if}

			<b>Computer photos:</b>
			&nbsp;|&nbsp;
			<a target="_blank" href="/?cl=customer&amp;op=customer_photo_add&amp;computer_id={$computer->id}&amp;returl={$ret_url}">Add photo &#0187;</a><p/>

			<table width="100%">
			{foreach from=$computer->photos item=photo}
			<tr>
			<td width="100">
				<a target="_blank" href="/?cl=customer&amp;op=customer_photo_view&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->get_thumb_tag()}</a></td>
			</td><td width="90%">
				<a target="_blank" href="/?cl=customer&amp;op=customer_photo_view&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->subject|escape}</a><br/>
				{if $photo->comments}
					{$photo->comments|escape|nl2br}
				{/if}
			</td>
			</tr>
			{foreachelse}
				<tr><td class="light_text">[No photos uploaded yet]</td></tr>
			{/foreach}
			</table>
		</td>
		</tr>
	</table>
</div>



<!-- Tab with computer notes -->
<div id="tab_notes" class="tab_content" style="display:none;">
	<h2>Notes</h2>
	<br/>

	[<a target="_blank" href="/?cl=kawacs&amp;op=computer_note_add&amp;computer_id={$computer->id}">Add Note &#0187;</a>]
	<p/>

	<table class="list" width="100%">
		<thead>
		<tr>
			<td width="15%">Created</td>
			<td width="20%">Created by</td>
			<td width="55%">Note</td>
			<td width="10%"> </td>
		</tr>
		</thead>

		{foreach from=$notes item=note}
		<tr>
			<td nowrap="nowrap">
				<a target="_blank" href="/?cl=kawacs&amp;op=computer_note_edit&amp;id={$note->id}"
				>{$note->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a>
			</td>
			<td>
				{assign var="user_id" value=$note->user_id}
				{$users_list.$user_id}
			</td>
			<td>{$note->note|escape|nl2br}</td>
			<td align="right" nowrap="nowrap">
				<a target="_blank" href="/?cl=kawacs&amp;op=computer_note_delete&amp;id={$note->id}"
					onclick="return confirm('Are you really sure you want to delete this note?');"
				>Delete &#0187;</a>
			</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="3" class="light_text">[No notes entered yet for this computer]</td>
		</tr>
		{/foreach}
	</table>
</div>

<!-- Tab with tickets history for this computer -->
<div id="tab_tickets" class="tab_content" style="display:none;">
	<h2>Tickets History</h2>
	<br/>

	<table class="list">
		<thead>
		<tr>
			<td width="2%">ID</td>
			<td width="48%">Subject</td>
			<td width="10%">Status</td>
			<td width="20%">Assigned to</td>
			<td width="10%">Created</td>
			<td width="10%">Updated</td>
		</tr>
		</thead>

		{foreach from=$tickets_history item=ticket}
		<tr>
			<td><a target="_blank" href="/?cl=krifs&op=ticket_edit&id={$ticket->id}">{$ticket->id}</a></td>
			<td><a target="_blank" href="/?cl=krifs&op=ticket_edit&id={$ticket->id}">{$ticket->subject|escape}</a></td>
			<td>
				{assign var="status" value=$ticket->status}
				{$TICKET_STATUSES.$status}
			</td>
			<td>
				{assign var="assigned_id" value=$ticket->assigned_id}
				{$users_list.$assigned_id}
			</td>
			<td>{$ticket->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td>{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="6" class="light_text">[No tickets]</td>
		</tr>
		{/foreach}
	</table>
</div>

<!-- Tab with communitation checking tools -->
<div id="tab_communications" class="tab_content" style="display:none;">
	<h2>Communications</h2>
	<br/>
	| <a target="_blank" href="#" onclick="return runTraceroute();"><b>Run Traceroute</b></a>
	| <a target="_blank" href="/?cl=kawacs&amp;op=computer_remote_access&amp;id={$computer->id}"><b>Remote Access &#0187;</b></a>
	| <a target="_blank" href="/?cl=kawacs&op=set_logmein&computer_id={$computer->id}" style="font-weight: bold;">Set LogMeIn ID &#0187;</a>
        |

	<p/>

	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="2">IP Addresses</td>
		</tr>
		</thead>

		<tr>
			<td width="15%" class="highlight">Remote IP:</td>
			<td width="85%" class="post_highlight">{$computer->remote_ip}</td>
		</tr>
		<tr>
			<td class="highlight">Local IPs:</td>
			<td class="post_highlight">
				{foreach from=$ips item=ip}
					{$ip.ip} - {$ip.adapter}<br/>
				{/foreach}
			</td>
		</tr>

		<tr class="head">
			<td colspan="2">Internet Monitoring</td>
		</tr>
		{if $monitored_ip}
		<tr>
			<td class="highlight">Monitored IP:</td>
			<td class="post_highlight" nowrap="nowrap">
				<a target="_blank" href="/?cl=kawacs&amp;op=monitored_ip_edit&amp;id={$monitored_ip->id}&amp;returl={$ret_url}"
				>{$monitored_ip->remote_ip} / {$monitored_ip->target_ip}</a>
			</td>
		</tr>
		<tr>
			<td class="highlight">Status:</td>
			<td class="post_highlight">
				{assign var="status" value=$monitored_ip->status}
				{$MONITOR_STATS.$status}
			</td>
		</tr>
		<tr>
			<td class="highlight">Last traceroute:</td>
			<td class="post_highlight">
				<pre>{$monitored_ip->last_traceroute|escape}</pre>
			</td>
		</tr>
		{else}
		<tr>
			<td class="highlight">Monitored IP:</td>
			<td class="post_highlight"><font class="light_text">[No monitoring defined]</font></td>
		</tr>
		{/if}
	</table>
	<p/>
</div>

<!-- Tab with reporting and discoveries info -->
<div id="tab_reporting" class="tab_content" style="display:none;">
	<h2>Reporting:</h2>

	{if !$computer->is_manual}
	<p/>
	<table class="list" width="60%">
		<thead>
		<tr>
			<td width="120">Last contact:</td>
			<td class="post_highlight">
				{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			</td>
		</tr>
		</thead>

		<tr>
			<td class="highlight">Remote IP:</td>
			<td class="post_highlight">{$computer->remote_ip}</td>
		</tr>
		<tr>
			<td class="highlight">Identification MAC:</td>
			<td class="post_highlight">
				{$computer->mac_address|escape}
				{if !$computer->is_manual}
					&nbsp;&nbsp;|&nbsp;&nbsp;
					<a target="_blank" href="/?cl=kawacs&amp;op=computer_mac_edit&amp;id={$computer->id}">Edit &#0187;</a>
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">Managing since:</td>
			<td class="post_highlight">
				{if $computer->date_created}{$computer->date_created|date_format:$smarty.const.DATE_FORMAT_SMARTY}
				{else}--
				{/if}
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<a target="_blank" href="/?cl=kawacs&amp;op=computer_date_created&amp;id={$computer->id}">Edit &#0187;</a>
			</td>
		</tr>
	</table>
	<p/>
	{else}
		<p class="light_text">[Manually created computer]</p>
	{/if}

	<h2>Discoveries</h2>
	{if count($discoveries)>0}
	<p/>
	<table class="list" width="60%">
		{foreach from=$discoveries item=discovery}
		<tr class="head">
			<td width="120">Discovered from:</td>
			<td class="post_highlight">
				{assign var="detail_id" value=$discovery->detail_id}
				#{$disc_details.$detail_id->computer_id}: {$disc_details.$detail_id->computer_name}, &nbsp;&nbsp;&nbsp;
				{$disc_details.$detail_id->ip_start} - {$disc_details.$detail_id->ip_end}
			</td>
		</tr>
		<tr>
			<td class="highlight">Last discovered:</td>
			<td class="post_highlight">
				<a target="_blank" href="/?cl=discovery&amp;op=discovery_details&amp;id={$discovery->id}"
				>{$discovery->last_discovered|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a>
				{if $discovery->is_computer_late_reporting()}
					<br/>
					<b class="warning">WARNING:</b>
					The computer's last contact ({$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY})
					is older with more than {$smarty.const.DISCOVERY_REPORTING_ISSUE_INTERVAL/3600} hours
					than the last discovery date.
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">Name:</td>
			<td class="post_highlight">{$discovery->get_name()|escape}</td>
		</tr>
		<tr>
			<td class="highlight">IP address:</td>
			<td class="post_highlight">{$discovery->ip}</td>

		</tr>
		{/foreach}
	</table>
	{else}
		<p class="light_text">[No matches in networks discoveries]</p>
	{/if}

</div>

</form>


<script language="JavaScript" type="text/javascript">
//<![CDATA

// Check what was the last selected tab, if any
if (!(last_tab = getCookie('computer_view_tab'))) last_tab = tabs[0];
showTab (last_tab);

//]]>
</script>
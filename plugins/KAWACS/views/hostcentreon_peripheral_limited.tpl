
<link rel="stylesheet" type="text/css" href="main.css" />
<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function move_item (src_list_name, dest_list_name)
{
	frm = document.forms['edit_frm']
	src_list = frm.elements[src_list_name]
	dest_list = frm.elements[dest_list_name]

	if (src_list.selectedIndex >= 0)
	{
		opt = new Option (src_list.options[src_list.selectedIndex].text, src_list.options[src_list.selectedIndex].value, false, false)

		dest_list.options[dest_list.options.length] = opt
		src_list.options[src_list.selectedIndex] = null
	}
}

function prepare_submit ()
{
	frm = document.forms['edit_frm']
	lists_list = new Array ('computers[]');

	for (i=0; i<lists_list.length; i++)
	{
		element = frm.elements[lists_list[i]];
		if (element)
		{
			for (j=0; j<element.options.length; j++)
			{
				element.options[j].selected = true;
			}
		}
	}

	return true;
}

//]]>
{/literal}
</script>


<h1>Edit Peripheral : {$peripheral->class_def->name} ({$peripheral->asset_no})</h1>
<p>

<font class="error">{$error_msg}</font>
<p>

<form action="" method="post" name="edit_frm" onSubmit="return prepare_submit();">
{$form_redir}

<p/>
<table width="98%">
<tr><td width="50%">
<table width="95%" class="list">
	<thead>
	<tr>
		<td width="120">Customer:</td>
		<td class="post_highlight">{$customer->name} ({$customer->id})</td>
	</tr>
	</thead>

	<tr>
		<td class="highlight">Name:</td>
		<td class="post_highlight"><input type="text" name="peripheral[name]" size="40" value="{$peripheral->name}"></td>
	</tr>
	<tr>
		<td class="highlight">Asset No.:</td>
		<td class="post_highlight">{$peripheral->asset_no}</td>
	</tr>
	<tr>
		<td class="highlight">Peripheral class:</td>
		<td class="post_highlight">{$peripheral_class->name}</td>
	</tr>

	{if $peripheral->get_access_url()}
		{assign var="access_url" value=$peripheral->get_access_url()}
		<tr>
			<td class="highlight">Remote Web connect:</td>
			<td class="post_highlight">
				<a href="{$peripheral->get_access_url()}" target="_blank">Connect&nbsp;&#0187;</a>
			</td>
		</tr>
	{/if}


	{if $peripheral->get_net_access_ip() or $peripheral->get_net_access_port()}
	<tr>
		<td class="highlight">Network connect:</td>
		<td class="post_highlight">
			{assign var="access_ip" value=$peripheral->get_net_access_ip()}
			{assign var="access_port" value=$peripheral->get_net_access_port()}

			{if $access_ip}{$access_ip}
			{else}<font class="light_text">--</font>
			{/if}
			:
			{if $access_port}{$access_port}
			{else}<font class="light_text">--</font>
			{/if}

			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a target="_blank" href="/?cl=kawacs&amp;op=peripheral_plink&amp;id={$peripheral->id}">Plink &#0187;</a>
		</td>
	</tr>
	{/if}

	<tr>
		<td class="highlight">Photos:</td>
		<td class="post_highlight">
			<a target="_blank" href="/?cl=customer&amp;op=customer_photo_add&amp;peripheral_id={$peripheral->id}&amp;returl={$ret_url}">Add photo &#0187;</a>

			{if $peripheral->photos}
				<p/>
				{foreach from=$peripheral->photos item=photo}
					<a href="/?cl=customer&amp;op=customer_photo_view&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->subject|escape}</a><br/>
					<a href="/?cl=customer&amp;op=customer_photo_view&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->get_thumb_tag()}</a>
					<br/>
				{/foreach}
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Managing since:</td>
		<td class="post_highlight">
			<input type="text" size="12" name="peripheral[date_created]"
			{if $peripheral->date_created}value="{$peripheral->date_created|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"{/if}
			/>
			{literal}
			<a target="_blank" href="#" onclick="showCalendarSelector('edit_frm', 'peripheral[date_created]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
		</td>
	</tr>
	<tr>
		<td class="highlight">Location:</td>
		<td class="post_highlight">
			{if $peripheral->location}
				<a target="_blank" href="/?cl=customer&amp;op=location_edit&amp;id={$peripheral->location->id}&amp;returl={$ret_url}">
				{foreach from=$peripheral->location->parents item=parent}
					{$parent->name} &#0187;
				{/foreach}
				{$peripheral->location->name|escape}</a>
			{else}
				<font class="light_text">--</font>
			{/if}
			&nbsp;&nbsp;<a target="_blank" href="/?cl=kawacs&op=peripheral_location&id={$peripheral->id}"
			><img src="/images/icons/edit_16_grey.png" alt="Change location" title="Change location" border="0" width="16" height="16"
			/></a>
		</td>
	</tr>

	{if $notifications}
	<tr>
		<td class="highlight">Notifications</td>
		<td>
			<ul style="margin-top:0px; margin-bottom:4px;">
			{foreach from=$notifications item=notif name=periph_notifs}

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
			{/foreach}
			</ul>
		<td>
	</tr>
	{/if}
</table>

</td><td>
<table class="list" width="100%">
		<thead>
			<tr>
				<td width="120">SNMP&nbsp;Monitoring</td>
				<td align="right" nowrap="nowrap">
					{if $peripheral_class->can_snmp_monitor()}
					<a target="_blank" href="/?cl=kawacs&amp;op=peripheral_edit_snmp&amp;id={$peripheral->id}&amp;returl={$ret_url}">Edit SNMP settings &#0187;</a>
					{/if}
				</td>
			</tr>
		</thead>

		{if $peripheral_class->can_snmp_monitor()}
		<tr>
			<td class="highlight">SNMP enabled:</td>
			<td class="post_highlight">{if $peripheral->snmp_enabled}Yes{else}No{/if}</td>
		</tr>
		<tr>
			<td class="highlight">Monitor profile:</td>
			<td class="post_highlight">
				{if $peripheral->profile_id}
					<a target="_blank" href="/?cl=kawacs&amp;op=monitor_profile_periph_edit&amp;id={$profile->id}">{$profile->name|escape}</a>
				{else}<font class="light_text">--</font>
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">Monitoring computer:</td>
			<td class="post_highlight">
				{if $peripheral->snmp_computer_id}
					{assign var="snmp_computer_id" value=$snmp_computer->id}
					<a target="_blank" href="/?cl=kawacs&amp;op=computer_view&amp;id={$snmp_computer_id}"
					>#{$snmp_computer_id}: {$computers_list.$snmp_computer_id}</a>
				{else}<font class="light_text">--</font>
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">SNMP IP:</td>
			<td class="post_highlight">
				{if $peripheral->snmp_ip}{$peripheral->snmp_ip}
				{else}--
				{/if}
			</td>
		</tr>
		<tr>
			<td class="highlight">Last SNMP contact:</td>
			<td class="post_highlight">
				{if $peripheral->last_contact}{$peripheral->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				{else}--
				{/if}
			</td>
		</tr>
		{else}
		<tr>
			<td colspan="2" class="light_text">[SNMP monitoring not available for this peripheral class]</td>
		</tr>
		{/if}

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
				<a target="_blank" href="/?cl=discovery&amp;op=discovery_details&amp;id={$discovery->id}"
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

<table width="98%" class="list">
	<tr class="head">
		<td colspan="4">Peripheral data</td>
	</tr>

	{assign var="cols" value="2"}
	{foreach from=$peripheral->class_def->field_defs key=idx item=field_def name=peripheral_fields}
		{assign var="field_id" value=$field_def->id}
		{if ($smarty.foreach.peripheral_fields.index % $cols) == 0}<tr>{/if}
		<td width="10%">
			{$field_def->name}:
			{if $peripheral->is_snmp_field($field_id) and $peripheral->fields_last_updated.$field_id}
				<br/>
				{$peripheral->fields_last_updated.$field_id|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{/if}
		</td>
		<td width="40%">
			{if $peripheral->is_snmp_field($field_id)}
				{if count($peripheral->values_snmp.$field_id)>1}
					<ol style="margin-top:0px; margin-bottom: 0px;">
					{foreach from=$peripheral->values_snmp.$field_id item=val}
					<li>{$val}</li>
					{/foreach}
					</ol>
				{elseif count($peripheral->values_snmp.$field_id)==1}
					{$peripheral->values_snmp.$field_id.0}
				{/if}
			{else}

				{if $field_def->type == $smarty.const.MONITOR_TYPE_TEXT}
					<textarea name="values[{$field_id}]" rows="6" cols="45">{$peripheral->values.$idx}</textarea>
				{elseif $field_def->type == $smarty.const.MONITOR_TYPE_DATE}
					{assign var="time" value="0000-00-00"}
					{if $peripheral->values.$idx > 0} {assign var="time" value=$peripheral->values.$idx}
					{else} {assign var="time" value="1000--00"}
					{/if}

					{html_select_date
						field_array="values[$field_id]"
						start_year="+10"
						end_year="-20"
						reverse_years=true
						time=$time
						year_empty="--" month_empty="--" day_empty="--"
						field_order="DMY"
					}
				{elseif $field_def->type == $smarty.const.MONITOR_TYPE_MEMORY}

					<input type="text" name="values[{$field_id}][size]" value="{$peripheral->values.$idx|get_memory_string_num}" size="10">
					<select name="values[{$field_id}][multiplier]">
						{html_options options=$CRIT_MEMORY_MULTIPLIERS_NAMES selected=$peripheral->values.$idx|get_memory_string_multiplier}
					</select>
				{else}
					{if $field_id == $peripheral->class_def->warranty_service_package_field}
						<select name="values[{$field_id}]">
							<option value="">[Select]</option>
							{html_options options=$service_packages_list selected=$peripheral->values.$idx}
						</select>
					{elseif $field_id == $peripheral->class_def->warranty_service_level_field}
						<select name="values[{$field_id}]">
							<option value="">[Select]</option>
							{html_options options=$service_levels_list selected=$peripheral->values.$idx}
						</select>
					{else}
						<input type="text" name="values[{$field_id}]" value="{$peripheral->values.$idx}" size="50">
					{/if}
				{/if}
			{/if}
		</td>
		{if ($smarty.foreach.peripheral_fields.index % $cols) == 1}</tr>{/if}
	{/foreach}
	{if ($smarty.foreach.peripheral_fields.index % $cols) == 0}<td></td><td></td></tr>{/if}

	{if $peripheral->class_def->link_computers}
	<tr>
		<td>Linked to computers:</td>
		<td colspan="3">
			<table>
				<tr>
					<td width="50%">
						Included computers:<br>
						<select name="computers[]" multiple size="6" style="width: 250px;"
						onDblClick="move_item('computers[]', 'computers_list[]')"
						>
							{foreach from=$peripheral->computers item=computer_id}
								<option value="{$computer_id}">{$computers_list.$computer_id}</option>
							{/foreach}
						</select>
					</td>
					<td width="50%">
						Available computers:<br>
						<select name="computers_list[]" multiple size="6" style="width: 250px;"
						onDblClick="move_item('computers_list[]', 'computers[]')"
						>
							{html_options options=$available_computers_list}
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}

</table>

<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>
<p/>

<b>NOTE:</b>
The fields which are not shown as editable are automatically collected through SNMP.
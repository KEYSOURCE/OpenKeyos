<div style="disply:block; padding: 10px;">

<h2 style="margin-top: 0px;">Item Details</h2>

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="120">Computer:</td>
		<td>#{$computer->id}: {$computer->netbios_name|escape}</td>
	</tr>
	<tr>
		<td>Item:</td>
		<td>{$item->itemdef->id}: {$item->itemdef->name|escape}</td>
	</tr>
	<tr>
		<td><b>Nrc.</b></td>
		<td><b>{$nrc}</b></td>
	</tr>
	<tr>
		<td><b>Last updated:</b></td>
		<td><b>{$item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</b></td>
	</tr>
	</thead>

	{if $item->itemdef->type != $smarty.const.MONITOR_TYPE_STRUCT}
		<tr>
			<td>Value:</td>
			<td>{$item->get_formatted_value($idx)}</td>
		</tr>
	{else}
		{foreach from=$item->itemdef->struct_fields item=field_def}
		<tr>
			<td>{$field_def->name|escape}:</td>
			<td>
				{if $item->itemdef->id==$smarty.const.EVENTS_ITEM_ID and $field_def->id==$smarty.const.FIELD_ID_EVENTS_LOG_TYPE}
					{assign var="event_type_field" value=$smarty.const.FIELD_ID_EVENTS_LOG_TYPE}
					{assign var="event_type" value=$item->val.$idx->value.$event_type_field}
					
					{if $EVENTLOG_TYPES_ICONS.$event_type}
						<img src="/images/icons/{$EVENTLOG_TYPES_ICONS.$event_type}" width="16" height="16" alt="" title=""/>
					{/if}
				{/if}
				
				{assign var="val_key" value=$field_def->id}
				{if $field_def->type==$smarty.const.MONITOR_TYPE_TEXT}
					{$item->get_formatted_value($idx, $val_key)}
				{else}
					{$item->get_formatted_value($idx, $val_key)|escape}
				{/if}
			</td>
		</tr>
		{/foreach}
	{/if}
</table>
<p/>
[ <a href="#" onclick="window.close();">Close window</a> ]
<p/>
</div>
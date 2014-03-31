{assign var="paging_titles" value="KAWACS, Manage Peripherals, Add Peripheral"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_peripherals"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<h1>Add Peripheral : {$peripheral_class->name}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}

<p/>
<table width="80%" class="list">
	<thead>
	<tr>
		<td>Customer:</td>
		<td>{$customer->name} ({$customer->id})</td>
	</tr>
	</thead>
	
	<tr>
		<td><b>Name:</b></td>
		<td><input type="text" name="peripheral[name]" size="40" value="{$peripheral->name}"></td>
	</tr>
	<tr>
		<td><b>Managing since:</b></td>
		<td>
			<input type="text" size="12" name="peripheral[date_created]"
			{if $peripheral->date_created}value="{$peripheral->date_created|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"{/if}
			/>
			{literal}
			<a href="#" onclick="showCalendarSelector('frm_t', 'peripheral[date_created]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
		</td>
	</tr>

	<tr><td colspan="2">&nbsp;</td></tr>
		
	{foreach from=$peripheral_class->field_defs key=idx item=field_def}
	<tr>
		<td>{$field_def->name}</td>
		<td>
			{assign var="field_id" value=$field_def->id}
			{if $field_def->type == $smarty.const.MONITOR_TYPE_TEXT}
				<textarea name="values[{$field_id}]" rows="6" cols="60">{$peripheral->values.$idx}</textarea>
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
					<input type="text" name="values[{$field_id}]" value="{$peripheral->values.$idx}" size="60">
				{/if}
			{/if}
		</td>
	</tr>
	{/foreach}
	
</table>

<p>
<input type="submit" name="save" value="Add">
<input type="submit" name="cancel" value="Cancel">
</form>

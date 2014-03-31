<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/activity.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

var show_location = {if $show_location} true {else} false {/if};

// Initialize the Activity object
var activity = new Activity ({$activity_id}, {$is_continuation}, {$billable}, {$time_in}, {$duration}, {$time_out}, {$location_id});
activity.set_travel_times ({$time_start_travel_to}, {$time_end_travel_to}, {$time_start_travel_from}, {$time_end_travel_from});

// Create the array with fixed-price action types
var fixed_price_actions = new Array ();
{foreach from=$action_types_fixed_list key=action_type_id item=action_type_name}
	fixed_price_actions[{$action_type_id}] = true;
{/foreach}

// Create the array with the 'Helpdesk' and 'On-site' locations
var locations_helpdesk = new Array ();
var locations_onsite = new Array ();
{foreach from=$locations_list_helpdesk key=loc_id item=loc_name}
	locations_helpdesk[{$loc_id}] = true;
{/foreach}
{foreach from=$locations_list_onsite key=loc_id item=loc_name}
	locations_onsite[{$loc_id}] = true;
{/foreach}

// Store the last selected action type
var selected_action_type_id = '{$activity_id}';

{literal}

// Set the name of form elements containing dates and times
var frm_name = 'frm_t';
var activity_name = 'detail[activity_id]';
var is_continuation_name = 'detail[is_continuation]';
var billable_name = 'detail[billable]';
var date_in_name = 'detail[time_in_date]';
var hour_in_name = 'detail[time_in_hour]';
var duration_name = 'detail[work_time]';
var date_out_name = 'detail[time_out_date]';
var hour_out_name = 'detail[time_out_hour]';
var location_name = 'detail[location_id]';
var start_travel_to_name = 'detail[time_start_travel_to]';
var end_travel_to_name = 'detail[time_end_travel_to]';
var start_travel_from_name = 'detail[time_start_travel_from]';
var end_travel_from_name = 'detail[time_end_travel_from]';

var do_validation = true;

// Set the window size
window.resizeTo (550, 400);

// Called when the location is changed, rebuilds the list of options for the action types drop-down
function check_location ()
{
	frm = document.getElementById(frm_name);
	elm_locations = frm.elements[location_name];
	elm_actions = frm.elements[activity_name];
	elm_row_travel_head = document.getElementById ('row_travel_head');
	elm_row_travel_to = document.getElementById ('row_travel_to');
	elm_row_travel_from = document.getElementById ('row_travel_from');
	selected_location = elm_locations.options[elm_locations.selectedIndex].value;
	
	// Delete first all the current options
	for (i=elm_actions.options.length-1; i>=0; i--) elm_actions.options[i] = null;
	
	// Copy the options from the 'source' lists
	if (locations_helpdesk[selected_location]) elm_src = frm.elements['activities_helpdesk_list_source'];
	else elm_src = frm.elements['activities_list_source'];
	for (i=0; i<elm_src.options.length; i++) elm_actions.options[i] = new Option (elm_src.options[i].text, elm_src.options[i].value);
	
	// Now select the proper action type from the list
	if (selected_action_type_id != '')
	{
		for (i=0; i<elm_actions.options.length; i++) if (elm_actions.options[i].value==selected_action_type_id) elm_actions.options[i].selected=true;
	}
	
	// Check if the location is "on-site" and show or hide the travel hours lines
	if (locations_onsite[selected_location])
	{
		elm_row_travel_head.style.display = '';
		elm_row_travel_to.style.display = '';
		elm_row_travel_from.style.display = '';
	}
	else
	{
		elm_row_travel_head.style.display = 'none';
		elm_row_travel_to.style.display = 'none';
		elm_row_travel_from.style.display = 'none';
		// Also make sure to clear the times if they were set
		frm.elements[start_travel_to_name].value = '';
		frm.elements[end_travel_to_name].value = '';
		frm.elements[start_travel_from_name].value = '';
		frm.elements[end_travel_from_name].value = '';
	}
}

// Called when a new activity is selected, checks if this is a fixed-price item or not,
// and shows or hide the "is_continuation" select
function check_is_continuation ()
{
	elm_row = document.getElementById ('is_continuation_row');
	sel_action_type_id = get_selected_action_type ();
	
	if (sel_action_type_id > 0)
	{
		if (fixed_price_actions[sel_action_type_id]) elm_row.style.display = '';
		else elm_row.style.display = 'none';
	}
	else elm_row.style.display = 'none';
	
	// Store the last selected action
	//alert (selected_action_type_id+' : '+sel_action_type_id);
	if (sel_action_type_id) selected_action_type_id = sel_action_type_id;
}

// Returns the ID of the currently selected action type, 
// or 0 if no action type has been selected
function get_selected_action_type ()
{
	ret = 0;
	frm = document.getElementById(frm_name);//document.forms[frm_name];
	elm = frm.elements[activity_name];
	
	if (elm.selectedIndex > 0 && elm.options[elm.selectedIndex].value != '')
	{
		ret = elm.options[elm.selectedIndex].value;
	}
	
	return ret;
}

// Removes all entered data
function clear_all_data ()
{
	if (confirm('Are you sure you want to delete all data?'))
	{
		frm = document.getElementById(frm_name);//document.forms[frm_name];
		frm.elements[activity_name].selectedIndex = 0;
		frm.elements[is_continuation_name].selectedIndex = 0;
		frm.elements[billable_name].selectedIndex = 0;
		frm.elements[location_name].selectedIndex = 0;
		frm.elements[date_in_name].value = '';
		frm.elements[hour_in_name].value = '';
		frm.elements[duration_name].value = '';
		frm.elements[date_out_name].value = '';
		frm.elements[hour_out_name].value = '';
		frm.elements[start_travel_to_name].value = '';
		frm.elements[end_travel_to_name].value = '';
		frm.elements[start_travel_from_name].value = '';
		frm.elements[end_travel_from_name].value = '';
		
		check_is_continuation ();
	}
	return false;
}

// Called when the "Set" button is clicked, to "save" the values in the calling parent window
function do_save (activity)
{

        top.pass_data_duration (activity);
	do_close ();
        return false;
}

// Called when the "Cancel" button is closed, to close the current window without "saving" any data to calling window
function do_close ()
{
	top.$.fancybox.close ();
}


// Use a separate instance of CalendarPopup, to be able to customize the return function
var cal_activity = new CalendarPopup(); 
cal_activity.setReturnFunction('setDateStringActivity'); 
function showCalendarSelectorActivity (name_form, name_element, anchor_name)
{
	elname = name_element;
	frm_name = name_form;
	cal_activity.showCalendar(anchor_name,getDateString());
}

{/literal}

//]]>
</script>

<div style="display:block; padding: 10px;">
<form action="" method="POST" id="frm_t" name="frm_t" onsubmit="return validate_form();">
{$form_redir}

<!-- Hidden lists from which to copy the options -->
<select id="activities_list_source" name="activities_list_source" style="display:none;">
	<option value="">[Select action type]</option>
	{foreach from=$action_types_customer key=group_id item=actions}
		<option value="" style="font-weight:800;">[{$actypes_categories_list.$group_id}]</option>
		{foreach from=$actions item=action_type}
		<option value="{$action_type->id}" {if $action_type->id==$activity_id}selected{/if}>[{$action_type->erp_code}] {$action_type->name|escape}</option>
		{/foreach}
		<option value=""> </option>
	{/foreach}
</select>
<select id="activities_helpdesk_list_source" name="activities_helpdesk_list_source" style="display:none;">
	<option value="">[Select action type]</option>
	{foreach from=$action_types_customer_helpdesk key=group_id item=actions}
	{foreach from=$actions item=action_type}
		<option value="{$action_type->id}" {if $action_type->id==$activity_id}selected{/if}>[{$action_type->erp_code}] {$action_type->name|escape}</option>
	{/foreach}
	{/foreach}
</select>


<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Activity</td>
	</tr>
	</thead>
	
	{if $show_location}
	<tr>
		<td class="highlight">Location:</td>
		<td class="post_highlight" >
			<select id="detail[location_id]" name="detail[location_id]" onchange="check_location();">
				<option value="">[Select location]</option>
				{html_options options=$locations_list selected=$location_id}}
			</select>
		</td>
	</td>
	{/if}
	
	<tr>
		<td class="highlight" width="100">Action type: </td>
		<td class="post_highlight">
			<select name="detail[activity_id]" id="detail[activity_id]" onchange="check_is_continuation ();" style="width:350px;">
			</select>
		</td>
	</tr>
	<tr id="is_continuation_row">
		<td class="highlight">Is continuation?</td>
		<td class="post_highlight">
			<select name="detail[is_continuation]" id="detail[is_continuation]">
				<option value="0">No</option>
				<option value="1" {if $is_continuation}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Billable?</td>
		<td class="post_highlight">
			<select name="detail[billable]" id="detail[billable]">
				<option value="0">No</option>
				<option value="1" {if $billable}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	
	<tr class="head">
		<td colspan="2">Work time</td>
	</tr>
	<tr>
		<td nowrap="nowrap" class="highlight">Time in:</td>
		<td style="vertical-align: top" nowrap="nowrap" class="post_highlight">
			<input type="text" size="12" name="detail[time_in_date]" id=="detail[time_in_date]" onchange="date_in_changed();"
				value="{$time_in|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
			/>
			
			{literal}
			<a HREF="#" onClick="showCalendarSelectorActivity (frm_name, date_in_name, 'anchor_calendar_in'); return false;" 
				name="anchor_calendar_in" id="anchor_calendar_in"
				><img src="/images/icon_cal.gif" alt="calendar" border="0" /></a>
			{/literal}
			
			<input type="text" name="detail[time_in_hour]" id="detail[time_in_hour]" size="6" onchange="hour_in_changed();"
				value="{$time_in|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}"
			/> (h:m)
			
		</td>
	</tr>
	<tr>
		<td nowrap="nowrap" class="highlight">Duration:</td>
		<td nowrap="nowrap" class="post_highlight">
			<input type="text" name="detail[work_time]" id="detail[work_time]"  size="8" onchange="duration_changed();"
				value="{$duration|format_interval_minutes}" 
			/> (h:m)
		</td>
	</tr>
	<tr>
		<td nowrap="nowrap" class="highlight">Time out:</td>
		<td nowrap="nowrap" class="post_highlight">
			<input type="text" size="12" name="detail[time_out_date]" id="detail[time_out_date]" onchange="date_out_changed();"
				value="{$time_out|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
			/>
			
			{literal}
			<a HREF="#" onClick="showCalendarSelector(frm_name, 'detail[time_out_date]', 'anchor_calendar_out'); return false;" 
				name="anchor_calendar_out" id="anchor_calendar_out"
				><img src="/images/icon_cal.gif" alt="calendar" border="0" /></a>
			{/literal}
			
			<input type="text" name="detail[time_out_hour]" id="detail[time_out_hour]" size="6" onchange="hour_out_changed();"
				value="{$time_out|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}"
			/> (h:m)
		</td>
	</tr>
	
	<tr class="head" id="row_travel_head">
		<td colspan="2">Travel times (hh:mm) - Optional</td>
	</tr>
	<tr id="row_travel_to">
		<td class="highlight" nowrap="nowrap">Travel to customer:</td>
		<td class="post_highlight">
			<input type="text" name="detail[time_start_travel_to]"  id="detail[time_start_travel_to]"
				value="{$time_start_travel_to|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}" size="6" /> -
			<input type="text" name="detail[time_end_travel_to]" id="detail[time_end_travel_to]"
				value="{$time_end_travel_to|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}" size="6" />
			
			&nbsp;&nbsp;&nbsp;&nbsp;
			[ <a href="#" 
				onclick="document.getElementById('frm_t').elements['detail[time_end_travel_to]'].value = document.getElementById('frm_t').elements['detail[time_in_hour]'].value;"
			>Copy "Time in"</a> ]
		</td>
	</tr>
	<tr id="row_travel_from">
		<td class="highlight" nowrap="nowrap">Travel from customer:</td>
		<td class="post_highlight">
			<input type="text" name="detail[time_start_travel_from]"  id="detail[time_start_travel_from]"
				value="{$time_start_travel_from|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}" size="6" /> -
			<input type="text" name="detail[time_end_travel_from]"  id="detail[time_end_travel_from]"
				value="{$time_end_travel_from|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}" size="6" />
			
			&nbsp;&nbsp;&nbsp;&nbsp;
			[ <a href="#" 
				onclick="document.getElementById('frm_t').elements['detail[time_start_travel_from]'].value = document.getElementById('frm_t').elements['detail[time_out_hour]'].value;"
			>Copy "Time out"</a> ]
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" id="save" value="&nbsp;&nbsp;Set&nbsp;&nbsp;" />
<input type="submit" name="cancel" id="cancel" value="Cancel" onclick="do_validation=false;" />
&nbsp;&nbsp;&nbsp;
<input type="submit" name="clear_all" id="clear_all" value="Clear all" onclick="return clear_all_data();" />

</form>
</div>


<script language="JavaScript" type="text/javascript">
//<![CDATA[
check_location ();
check_is_continuation ();
//]]>
</script>
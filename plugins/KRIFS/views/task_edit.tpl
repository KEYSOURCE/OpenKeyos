{assign var="ticket_id" value=$task->ticket_id}
{assign var="paging_titles" value="KRIFS, Ticket, Edit Schedule"}
{assign var="paging_urls" value="/krifs, /krifs/ticket_edit/"|cat:$ticket_id}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/ajax.js" type="text/javascript"></script>

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var str_date_today = '{$date_today|date_format:$smarty.const.DATE_FORMAT_SELECTOR}';
var str_date_tomorrow = '{$date_tomorrow|date_format:$smarty.const.DATE_FORMAT_SELECTOR}';
var str_date_day_after = '{$date_day_after|date_format:$smarty.const.DATE_FORMAT_SELECTOR}';
var ret_url = '{$ret_url}';
var ticket_id = {$task->ticket_id};

{literal}
// Return function after selecting date in calendar pop-up. Overloads the default function in order to update the schedules too
function setDateString (y,m,d)
{
	fels = document.forms[frm_name].elements;
	for (i = 0; i < fels.length; i++)
	{
		if (fels[i].name == elname)
		{
			el = fels[i];
		}
	}
	if (m < 10) m = '0'+m;
	el.value=d+"/"+m+"/"+y;
	
	// Reload the schedules, to use the newly selected date
	set_date (el.value);
}


// If a location is selected, send the browser to the page with location details
function goLocationDetails ()
{
	ret = false;
	frm = document.forms['data_frm'];
	elm = frm.elements['task[customer_location_id]'];
	
	if (elm.selectedIndex > 0)
	{
		loc_id = elm.options[elm.selectedIndex].value;
		url = '/customer/location_edit?id='+loc_id+'&returl='+ret_url;
		document.location = url;
	}
	else
	{
		alert ('Select a customer location first');
		ret = false;
	}
	return ret;
}

function checkCustomerLocation()
{
	ret = true;
	frm = document.forms['data_frm'];
	elm = frm.elements['task[customer_location_id]'];
	
	if (elm.selectedIndex > 0)
	{
		if (elm.options[elm.selectedIndex].value < 0)
		{
			alert ('Please select an actual location, not a town.');
			elm.options[0].selected = true;
			ret = false;
		}
	}
	
	return ret;
}

// Display the area for selecting attendees
function showEdit ()
{
	elm = document.getElementById ('tr_edit_attendees');
	elm.style.display = '';
	elm = document.getElementById ('link_edit');
	elm.style.display = 'none';
	return false;
}

// Hide the area for selecting attendees
function hideEdit ()
{
	elm = document.getElementById ('tr_edit_attendees');
	elm.style.display = 'none';
	elm = document.getElementById ('link_edit');
	elm.style.display = '';
	return false;
}


// Add an attendee to the list of selected attendees
function addAttendee ()
{
	frm = document.forms['data_frm'];
	elm_selected = frm.elements['task[attendees_ids][]'];
	elm_available = frm.elements['available_users'];
	
	if (elm_available.selectedIndex >=0 )
	{
		opt = new Option ();
		opt.value = elm_available.options[elm_available.selectedIndex].value;
		opt.text = elm_available.options[elm_available.selectedIndex].text;
		
		elm_selected.options[elm_selected.options.length] = opt;
		elm_available.options[elm_available.selectedIndex] = null;
		
		refreshAttendeesList ();
	}
}

// Remove an attendee from the list
function removeAttendee ()
{
	frm = document.forms['data_frm'];
	elm_selected = frm.elements['task[attendees_ids][]'];
	elm_available = frm.elements['available_users'];
	
	if (elm_selected.selectedIndex >=0 )
	{
		opt = new Option ();
		opt.value = elm_selected.options[elm_selected.selectedIndex].value;
		opt.text = elm_selected.options[elm_selected.selectedIndex].text;
		
		elm_available.options[elm_available.options.length] = opt;
		elm_selected.options[elm_selected.selectedIndex] = null;
		
		refreshAttendeesList ();
	}
}

// Refresh the text line containing the names of the selected attendees for this task
function refreshAttendeesList ()
{
	frm = document.forms['data_frm'];
	elm_selected = frm.elements['task[attendees_ids][]'];
	elm_list = document.getElementById ('div_attendees_list');
	
	if (elm_selected.options.length > 0)
	{
		str = elm_selected.options[0].text;
		for (i=1; i<elm_selected.options.length; i++) str = str + ", "+ elm_selected.options[i].text;
		elm_list.firstChild.nodeValue = str;
	}
	else
	{
		elm_list.firstChild.nodeValue = '[No attendees]';
	}
	
	// Refresh also the schedules for the selected users
	fetchSchedules ();
}

// Called upon form submission, make sure that all lines from the attendees list are selected
function finishForm ()
{
	ret = true;
	frm = document.forms['data_frm'];
	elm_selected = frm.elements['task[attendees_ids][]'];
	
	for (i=0; i<elm_selected.options.length; i++) elm_selected.options[i].selected = true;
	
	return ret;
}


// Display the pop-up window for selecting the hours
function showHoursPopup (anchor_name)
{
	position = getAnchorPosition (anchor_name);
	x = position.x;
	y = position.y - 500;
	if (!isNaN(window.screenX)) x = x+window.screenX;
	x = x - 200;
	
	popup_url = '/krifs/popup_hours';
	window.open (popup_url, 'Duration', 'dependent, scrollbars=yes, resizable=yes, width=500, height=500, left='+x+', top='+y);
	
	return false;
}

// Call-back function to be called from the hours selection window for setting the hour
function pass_data_hour (hour_start, hour_end)
{
	frm = document.forms['data_frm'];
	frm.elements['task[date_start]'].value = hour_start;
	frm.elements['task[date_end]'].value = hour_end;
}

//---------- Ajax fetching of users schedules ------------

var requester = false;

// Fetch the schedules for all selected users
function fetchSchedules (move)
{
	// Make sure there is no other operation already in progress
	if (requester) return;
	
	requester = getXmlRequester ();
	// Clear all previously existing schedules
	elm = document.getElementById ('tr_schedules');
	elm_head = document.getElementById ('tr_schedules_head');
	clearAllChildren (elm);
	clearAllChildren (elm_head);
	
	if (!requester) showTextIndicator ('[Sorry, Ajax is not available for loading schedules.]');
	else
	{
		showTextIndicator ('[Loading schedules...]');
		
		// Build the request URL and send the request to server
		frm = document.forms['data_frm'];
		usr_list = frm.elements['task[user_id]'];
		user_id = usr_list.options[usr_list.selectedIndex].value;
		
		date = frm.elements['date_start_schedule'].value;
		if (date == '') date = frm.elements['task[date]'].value;
		
		url = '/krifs/xml_get_schedules?ticket_id='+ticket_id+'&date='+date;
		url = url+'&user_id='+user_id;
		
		if (move) url = url + '&move='+move;
		
		elm_selected = frm.elements['task[attendees_ids][]'];
		for (i=0; i<elm_selected.options.length; i++) url = url + '&attendees_id[]='+elm_selected.options[i].value;
		
		requester.open ('GET', url);
		requester.send ('');
		requester.onreadystatechange = stateHandler;
	}
}

// Handler to process the XML data with the tasks
function stateHandler ()
{
	if (requester)
	{
		if (requester.readyState == 4)
		{
			try
			{
				elm = document.getElementById ('tr_schedules');
				elm_head = document.getElementById ('tr_schedules_head');
				clearAllChildren (elm);
				clearAllChildren (elm_head);
				if (requester.status == 200 && requester.responseXML)
				{
					days_count = 0;
					daysNodes = requester.responseXML.getElementsByTagName('day');
					if (!daysNodes.length) showTextIndicator ('ERROR: Failed fetching tasks schedules'+daysNodes.length);
					else
					{
						// Set the start date for schedule display
						document.forms['data_frm'].elements['date_start_schedule'].value = daysNodes[0].getAttribute ('date_str');
						
						// Show the columns for each day
						days_count = daysNodes.length;
						for (i=0; i<days_count; i++)
						{
							nodeDay = requester.responseXML.getElementsByTagName('day')[i];
							date_str_long = nodeDay.getAttribute ('date_str_long');
							date_str = nodeDay.getAttribute ('date_str');
							
							elm_td = document.createElement ('td');
							elm_td.width = '25%';
							elm_date = document.createElement ('a');
							elm_date.appendChild (document.createTextNode('['+date_str_long+']'));
							elm_date.href = '#';
							elm_date.id = date_str;
							elm_date.onclick = function() {set_scheduled_date(this.id); return false;};
							elm_td.appendChild (elm_date);
							elm_head.appendChild (elm_td);
							
							usersNodes = nodeDay.getElementsByTagName('user');
							if (usersNodes.length == 0)
							{
								// This will also automatically add a TD element to the TR
								showTextIndicator ('[No schedules]');
							}
							else
							{
								elm_td = document.createElement ('td');
								for (j=0; j<usersNodes.length; j++)
								{
									user_name = usersNodes[j].getAttribute('name');
									elm_div = document.createElement('div');
									elm_div.className = 'task_head';
									elm_div.appendChild (document.createTextNode(user_name));
									elm_td.appendChild (elm_div);
									
									tasksNodes = usersNodes[j].getElementsByTagName('task');
									for (k=0;k<tasksNodes.length;k++)
									{
										hour_start = tasksNodes[k].getElementsByTagName('hour_start_str')[0].firstChild.nodeValue;
										hour_end = tasksNodes[k].getElementsByTagName('hour_end_str')[0].firstChild.nodeValue;
										ticket_subject = tasksNodes[k].getElementsByTagName('ticket_subject')[0].firstChild.nodeValue;
										task_location = tasksNodes[k].getElementsByTagName('location')[0].firstChild.nodeValue;
										customer = tasksNodes[k].getElementsByTagName('customer')[0].firstChild.nodeValue;
										nodeCustLocation = tasksNodes[k].getElementsByTagName('customer_location')[0];
										if (nodeCustLocation.firstChild)
										{
											task_customer_location = nodeCustLocation.firstChild.nodeValue;
											if (task_customer_location != '') task_customer_location = ' : '+task_customer_location;
										}
										else task_customer_location = '';
										ticket_id = tasksNodes[k].getAttribute ('ticket_id');
										
										elm_a = document.createElement ('a');
										elm_a.href = '/krifs/ticket_edit?id='+ticket_id;
										elm_a.appendChild (document.createTextNode('#'+ticket_id+': '+ticket_subject));
										
										elm_div = document.createElement('div');
										elm_div.className = 'task_item';
										elm_div.appendChild (document.createTextNode(hour_start+'-'+hour_end+': '));
										elm_div.appendChild (document.createTextNode(customer));
										elm_div.appendChild (document.createTextNode(' (Loc.: '+task_location+task_customer_location+')'));
										elm_div.appendChild (document.createElement('br'));
										elm_div.appendChild (elm_a);
										
										elm_td.appendChild (elm_div);
									}
									elm_td.appendChild (document.createElement('br'));
								}
								elm.appendChild (elm_td);
							}
						}
					}
				}
				else
				{
					showTextIndicator ('ERROR: Failed reading schedules data from server');
				}
			}
			catch (error)
			{
				showTextIndicator ('ERROR: Internal browser error. Click "Refresh". :: (Error message: '+error+')');
			}
			requester = false;
		}
	}
	return true;
}

// Called when a date is clicked in the schedule, sets it as the date for the task
function set_scheduled_date (date)
{
	frm = document.forms['data_frm'];
	frm.elements['task[date]'].value = date;
}

// Sets the task date and the start date for showing schedules, also reloads the schedules
function set_date (date)
{
	frm = document.forms['data_frm'];
	frm.elements['date_start_schedule'].value = date;
	frm.elements['task[date]'].value = date;
	fetchSchedules ();
}

// Shows an indicator text in the schedules table (passed in the the 'elm' parameter)
function showTextIndicator (msg)
{
	e = document.getElementById ('tr_schedules');
	elm_td = document.createElement('td');
	elm_td.appendChild(document.createTextNode(msg));
	elm_td.className = 'light_text';
	e.appendChild (elm_td);
}

{/literal}

//]]>
</script>

<h1>Edit Task Schedule</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="data_frm" onsubmit="return finishForm();">
<input type="hidden" name="date_start_schedule" value=""/>
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="120">Ticket: </td>
		<td width="40%" class="post_highlight"># {$task->ticket_id} : {$task->ticket_subject|escape}</td>
		<td width="120">Customer:</td>
		<td width="40%" class="post_highlight"># {$customer->id} : {$customer->name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Date:</td>
		<td class="post_highlight">
			<input type="text" size="12" name="task[date]" onchange="fetchSchedules();"
				{if $task->date_start}value="{$task->date_start|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
				{elseif $last_selected_date}value="{$last_selected_date}"
				{else}value=""
				{/if}
			>
			{literal}
			<a href="#" onclick="showCalendarSelector('data_frm', 'task[date]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
			
			&nbsp;&nbsp;
			
			[<a href="#" onclick="set_date(str_date_today); return false;">Today</a>]
			[<a href="#" onclick="set_date(str_date_tomorrow); return false;">Tomorrow</a>]
			[<a href="#" onclick="set_date(str_date_day_after); return false;">Day after</a>]
		</td>
		
		<td class="highlight" nowrap="nowrap">Start/End hour:</td>
		<td class="post_highlight">
			<input type="text" name="task[date_start]" size="6"
			value="{if $task->date_start}{$task->date_start|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}{/if}"
			/>
			 - 
			<input type="text" name="task[date_end]" size="6"
			value="{if $task->date_end}{$task->date_end|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}{/if}"
			/>
			(hh:mm)
			<a href="#" onclick="return showHoursPopup(this.id);" style="margin-left: 10px;" id="show_hours"
			><img src="/images/icons/edit_16_grey.png" width="16" height="16" alt="Select hours" title="Select hours" /></a>
		</td>
	</tr>
	<tr>
		<td class="highlight">Organizer:</td>
		<td class="post_highlight">
			<select name="task[user_id]" onchange="fetchSchedules();">
				<option value="">[Select user]</option>
				{html_options options=$users_list selected=$task->user_id}
			</select>
		</td>
		<td class="highlight">Attendees:</td>
		<td class="post_highlight">
			<div id="div_attendees_list" style="display: inline;">&nbsp;</div>
			<a href="#" onclick="return showEdit();" style="margin-left: 10px;" id="link_edit"
			><img src="/images/icons/edit_16_grey.png" width="16" height="16" alt="Edit attendees" title="Edit attendees" /></a>
		</td>
	</tr>
	<tr id="tr_edit_attendees" style="display:none;">
		<td colspan="3"> </td>
		<td class="post_highlight">
			<table id="tbl_edit_attendees">
				<tr><td style="padding-right: 20px;">
					Selected attendees:<br/>
					<select name="task[attendees_ids][]" multiple="multiple" size="6" style="width: 120px;" ondblclick="removeAttendee();">
						{foreach from=$users_list key=usr_id item=usr_name}
						{if in_array($usr_id,$task->attendees_ids)}
							<option value="{$usr_id}">{$usr_name|escape}</option>
						{/if}
						{/foreach}
					</select>
				</td><td style="padding-right: 10px;">
					Available users:<br/>
					<select name="available_users" size="6" style="width: 120px;" ondblclick="addAttendee();">
						{foreach from=$users_list key=usr_id item=usr_name}
						{if !in_array($usr_id,$task->attendees_ids)}
							<option value="{$usr_id}">{$usr_name|escape}</option>
						{/if}
						{/foreach}
					</select>
				</td><td nowrap="nowrap">[ <a href="#" onclick="return hideEdit();">Hide</a> ]</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="highlight">Created by:</td>
		<td class="post_highlight">
			{if $task->created_by_id}
				{assign var="created_by" value=$task->created_by_id}
				{$users_list.$created_by}
				
				{if $task->created_date}
					, {$task->created_date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				{/if}
			{else}
				--
			{/if}
		</td>
		<td class="highlight">Percent completed:</td>
		<td class="post_highlight">
			<select name="task[completed]">
				{html_options options=$TASK_COMPLETED_OPTS selected=$task->completed}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Location:</td>
		<td class="post_highlight">
			<select name="task[location_id]">
				<option value="">[Select location]</option>
				{html_options options=$locations_list selected=$task->location_id}
			</select>
			<br/>
			Customer location:<br/>
			{if $customers_locations_list}
				<select name="task[customer_location_id]" onchange="return checkCustomerLocation();">
					<option value="">---</option>
					{html_options options=$customers_locations_list selected=$task->customer_location_id}
				</select>
				<a href="#" onclick="return goLocationDetails();">Details &#0187;</a>
			{else}
				<font class="light_text">[No customer locations defined]</font>
			{/if}
		</td>
		<td class="highlight">Comments:</td>
		<td class="post_highlight"><textarea name="task[comments]" rows="3" cols="50" />{$task->comments|escape}</textarea></td>
	</tr>
	
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
&nbsp;&nbsp;&nbsp;
<input type="submit" name="delete" value="Delete" class="button" 
	onclick="return confirm('Are you really sure you want to delete this task?');"
/>
</form>
<p/>

{* Showing the schedule for 3 days for any selected users *}
<table width="98%">
	<tr>
	<td width="200" nowrap="nowrap"><h2 style="margin:0px; border:none;">Current Schedules</h2></td>
	<td nowrap="nowrap" align="right">
	
		<a href="#" onclick="fetchSchedules('prev'); return false;">&#0171; Previous</a>
		&nbsp;&nbsp;|&nbsp;&nbsp;
		[ <a href="#" onclick="frm.elements['date_start_schedule'].value = str_date_today; fetchSchedules(); return false;">Today</a> ]
		[ <a href="#" onclick="fetchSchedules(); return false;">Refresh</a> ]
		&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="#" onclick="fetchSchedules('next'); return false;">Next &#0187;</a>
	
	</td>
	</tr>
</table>
<table class="list" width="98%">
<thead><tr id="tr_schedules_head"></tr></thead>
<tr id="tr_schedules"></tr>
</table>
<p/>
The list of schedules is automatically updated when you modify the user, the attendees or the date. <br/>
Click on a date header in the schedule to select that day for the task.


<script language="JavaScript" type="text/javascript">
//<![CDATA[
refreshAttendeesList ();
//]]>
</script>
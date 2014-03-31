{assign var="paging_titles" value="KRIFS, Ticket, Edit Ticket Detail"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/activity.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/tiny_mce/tiny_mce.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/fancybox/jquery.easing-1.3.pack.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/fancybox/jquery.fancybox-1.3.4.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[


// The ticket ID
var ticket_id = {$ticket->id}
// The name of last selected activity
{assign var="activity_id" value=$ticket_detail->activity_id}
var activity_name = "{$action_types.$activity_id}";
/// The name of last selected location
{assign var="location_id" value=$ticket_detail->location_id}
var location_name = "{$locations_list.$location_id}";
// The billable status of the ticket
var ticket_billable = {if $ticket->billable}true{else}false{/if};

{literal}

tinyMCE.init
(
	{
		//General options
		mode: "specific_textareas",
		theme: "advanced",
		plugins: "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect", 
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor", 
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen", 
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage", 
		theme_advanced_toolbar_location : "top", 
		theme_advanced_toolbar_align : "left", 
		theme_advanced_statusbar_location : "bottom", 
		theme_advanced_resizing : true,
        force_br_newlines: true,
        force_p_newlines: false,
        forced_root_block: ''
	}
);

var last_browse_window = false;
function show_duration_popup (anchor_name)
{
	frm = document.forms['frm_t'];
	
	if (last_browse_window) last_browse_window.close ();
	
	// If an work time has not been previously set and if the ticket is billable, mark this as billable
	if (frm.elements['ticket_detail[work_time]'].value == 0 && ticket_billable) frm.elements['ticket_detail[billable]'].value = 1;
	
	popup_url = '/krifs/popup_activity?show_location=1&title=' + escape('Action type') + '&ticket_id=' + ticket_id;
	pass_vars = new Array ('activity_id', 'is_continuation', 'billable', 'intervention_report_id', 'time_in', 'work_time', 'time_out', 'location_id', 'time_start_travel_to', 'time_end_travel_to', 'time_start_travel_from', 'time_end_travel_from');
	for (i=0; i<pass_vars.length; i++)
	{
		if (frm.elements['ticket_detail['+pass_vars[i]+']'])
		{
			popup_url = popup_url + '&'+pass_vars[i]+'=' + escape(frm.elements['ticket_detail['+pass_vars[i]+']'].value);
		}
	}
	
	position = getAnchorPosition (anchor_name);
	x = position.x;
	y = position.y - 500;
	if (!isNaN(window.screenX)) x = x+window.screenX;
	x = x - 200;
	last_browse_window = window.open (popup_url, 'Duration', 'dependent, scrollbars=yes, resizable=yes, width=100, height=100, left='+x+', top='+y);
	return false;
}

$(document).ready(function () {

    $("#anchor_new_worktime_fb").click(function() {
        frm = document.forms['frm_t'];

	// If an work time has not been previously set and if the ticket is billable, mark this as billable
	if (frm.elements['ticket_detail[work_time]'].value == 0 && ticket_billable) frm.elements['ticket_detail[billable]'].value = 1;

	var popup_url = '/krifs/popup_activity?show_location=1&title=' + escape('Action type') + '&ticket_id=' + ticket_id;
	var pass_vars = new Array ('activity_id', 'is_continuation', 'billable', 'intervention_report_id', 'time_in', 'work_time', 'time_out', 'location_id', 'time_start_travel_to', 'time_end_travel_to', 'time_start_travel_from', 'time_end_travel_from');
	for (i=0; i<pass_vars.length; i++)
	{
		if (frm.elements['ticket_detail['+pass_vars[i]+']'])
		{
			popup_url = popup_url + '&'+pass_vars[i]+'=' + escape(frm.elements['ticket_detail['+pass_vars[i]+']'].value);
		}
	}

        $.fancybox({
                'transitionIn'	:	'elastic',
                'transitionOut'	:	'elastic',
                'type'          :       'iframe',
                'href'          :       popup_url
        });

	return false;
    });

});

// Needed to receive data from the child duration popup window
function pass_data_duration (activity)
{
	load_frm_activity (activity);
}


// Loads a form and the display fields with data from an Activity object
function load_frm_activity (activity)
{
	frm = document.forms['frm_t'];
	frm.elements['ticket_detail[activity_id]'].value = activity.activity_id;
	frm.elements['ticket_detail[is_continuation]'].value = activity.is_continuation;
	frm.elements['ticket_detail[billable]'].value = activity.billable;
	frm.elements['ticket_detail[time_in]'].value = activity.time_in;
	frm.elements['ticket_detail[work_time]'].value = activity.work_time;
	frm.elements['ticket_detail[time_out]'].value = activity.time_out;
	frm.elements['ticket_detail[location_id]'].value = activity.location_id;
	
	frm.elements['ticket_detail[time_start_travel_to]'].value = activity.time_start_travel_to;
	frm.elements['ticket_detail[time_end_travel_to]'].value = activity.time_end_travel_to;
	frm.elements['ticket_detail[time_start_travel_from]'].value = activity.time_start_travel_from;
	frm.elements['ticket_detail[time_end_travel_from]'].value = activity.time_end_travel_from;
	
	elm = document.getElementById ('action_type_div');
	if (elm)
	{
		if (activity.activity_id > 0) elm.lastChild.nodeValue = activity.activity_name;
		else elm.lastChild.nodeValue = '--';
	}
	
	elm = document.getElementById ('is_continuation_div');
	if (elm)
	{
		if (activity.is_continuation == 1) elm.style.display = 'block';
		else elm.style.display = 'none';
	}
	
	//xxxxxxxxxxxxx
	elm = document.getElementById ('work_time_div');
	if (elm)
	{
		if (activity.work_time > 0)
		{
			str = activity.get_duration_string () + ' hrs., on ';
			str = str + activity.get_time_in_date_string() + ' ' + activity.get_time_in_time_string() + '; ';
			str = str + activity.location_name;
			elm.lastChild.nodeValue = str;
		}
		else elm.lastChild.nodeValue = '--';
	}
	
	elm = document.getElementById ('billable_div');
	if (elm)
	{
		if (activity.billable == 1) elm.lastChild.nodeValue = 'Yes';
		else elm.lastChild.nodeValue = 'No';
	}
	
	elm = document.getElementById ('travel_to_div');
	if (elm)
	{
		if (activity.time_start_travel_to > 0)
		{
			str = '- Travel to customer: ';
			str = str + ts_to_time_string (activity.time_start_travel_to) + ' - ' + ts_to_time_string (activity.time_end_travel_to);
			elm.lastChild.nodeValue = str;
			elm.style.display = 'block';
		}
		else elm.style.display = 'none';
	}
	
	elm = document.getElementById ('travel_from_div');
	if (elm)
	{
		if (activity.time_start_travel_from > 0)
		{
			str = '- Travel from customer: ';
			str = str + ts_to_time_string (activity.time_start_travel_from) + ' - ' + ts_to_time_string (activity.time_end_travel_from);
			elm.lastChild.nodeValue = str;
			elm.style.display = 'block';
		}
		else elm.style.display = 'none';
	}
}

// Initializes an Activity object with the data from the form
function load_activity_obj ()
{
	activity = new Activity ();
	frm = document.forms['frm_t'];
	activity.activity_id = frm.elements['ticket_detail[activity_id]'].value;
	activity.activity_name = activity_name;
	activity.set_times (frm.elements['ticket_detail[time_in]'].value, frm.elements['ticket_detail[work_time]'].value, frm.elements['ticket_detail[time_out]'].value);
	activity.set_travel_times (
		frm.elements['ticket_detail[time_start_travel_to]'].value, frm.elements['ticket_detail[time_end_travel_to]'].value,
		frm.elements['ticket_detail[time_start_travel_from]'].value, frm.elements['ticket_detail[time_end_travel_from]'].value
	);
	activity.location_id = frm.elements['ticket_detail[location_id]'].value;
	activity.location_name = location_name;
	activity.is_continuation = frm.elements['ticket_detail[is_continuation]'].value;
	activity.billable = frm.elements['ticket_detail[billable]'].value;
	
	return activity;
}

{/literal}
//]]>
</script>

<h1>Edit Ticket Detail</h1>

<p class="error">{$error_msg}</p>
{if !$is_editable}
<p>[This ticket detail cannot be modified, the intervention report or timesheet to which it belongs has been closed]</p>
{elseif $ticket->status==$smarty.const.TICKET_STATUS_CLOSED}
<p>[NOTE: The ticket to which detail belongs to is closed]</p>
{/if}

<form action="" method="POST" name="frm_t">
{$form_redir}

<input type="hidden" name="ticket_detail[activity_id]" value="{$ticket_detail->activity_id}" />
<input type="hidden" name="ticket_detail[is_continuation]" value="{$ticket_detail->is_continuation}" />
<input type="hidden" name="ticket_detail[billable]" value="{$ticket_detail->billable}" />
<input type="hidden" name="ticket_detail[time_in]" value="{$ticket_detail->time_in}" />
<input type="hidden" name="ticket_detail[work_time]" value="{$ticket_detail->work_time}" />
<input type="hidden" name="ticket_detail[time_out]" value="{$ticket_detail->time_out}" />
<input type="hidden" name="ticket_detail[location_id]" value="{$ticket_detail->location_id}" />

<input type="hidden" name="ticket_detail[time_start_travel_to]" value="{$ticket_detail->time_start_travel_to}" />
<input type="hidden" name="ticket_detail[time_end_travel_to]" value="{$ticket_detail->time_end_travel_to}" />
<input type="hidden" name="ticket_detail[time_start_travel_from]" value="{$ticket_detail->time_start_travel_from}" />
<input type="hidden" name="ticket_detail[time_end_travel_from]" value="{$ticket_detail->time_end_travel_from}" />

<table class="list" width="95%">
	<thead>
	<tr>
		<td>Ticket:</td>
		<td colspan="3">
			# {$ticket->id} : {$ticket->subject}
		</td>
	</tr>
	</thead>
	
	<tr>
		<td width="10%" class="highlight">User:</td>
		<td width="50%" class="post_highlight">
			{if $ticket_detail->user_id}
				{$ticket_detail->user->get_name()}
				{if $is_editable}
                {assign var="p" value="id:"|cat:$ticket_detail->id|cat:",returl:"|cat:$ret_url}
				<a href="{'krifs'|get_link:'ticket_detail_edit_user':$p:'template'}" class="no_print"
				>[ Change &#0187; ]</a>
				{/if}
			
			{else}
				--
			{/if}
		</td>
		<td width="10%" class="highlight">Date:</td>
		<td width="40%" class="post_highlight">{$ticket_detail->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
	<tr>
		<td class="highlight">Reassigned to:</td>
		<td class="post_highlight">
			{if $is_editable}
				<select name="ticket_detail[assigned_id]">
					<option value="0">[None]</option>
					{html_options options=$users selected=$ticket_detail->assigned_id}
				</select>
			{else}
				{assign var="assigned_id" value=$ticket_detail->assigned_id}
				{$users.$assigned_id}
			{/if}
		</td>
		<td class="highlight">Private: </td>
		<td class="post_highlight">
			{if $is_editable}
				<input type="checkbox" class="checkbox" name="ticket_detail[private]" value="1" {if $ticket_detail->private}checked{/if} />
			{else}
				{if $ticket_detail->private}Private {else} Public {/if}
			{/if}
		</td>
	</tr>
	
	{if $ticket_detail->user_id}
	<tr>
		<td class="highlight">Work time:</td>
		<td class="post_highlight">
			<div id="work_time_div" style="display:inline;">--</div>
			<div id="travel_to_div" style="display:none;">&nbsp;</div>
			<div id="travel_from_div" style="display:none;">&nbsp;</div>
		</td>
		<td class="highlight">Billable:</td>
		<td class="post_highlight">
			<div id="billable_div" style="display:inline;">--</div>
		</td>
	</tr>
	<tr>
		<td class="highlight" nowrap="nowrap">
			Action type:&nbsp;&nbsp;
			{if $is_editable}
				{* <a href="" onclick="return show_duration_popup('anchor_new_worktime');" id="anchor_new_worktime">[ Edit &#0187; ]</a> *}
                                <a href="" id="anchor_new_worktime_fb">[ Edit &#0187; ]</a>
			{/if}
		</td>
		<td class="post_highlight" colspan="3">
			<div id="action_type_div" style="display:block;">--</div>
			<div id="is_continuation_div" style="display:none; font-style:italic;">[Continuation]</div>
		</td>
	</tr>
	
	{* Even if the ticket is of non-billable type, check if an intervention report hasn't been already assigned *}
	{if $ticket->is_billable() or $ticket_detail->intervention_report_id}
	<tr>
		<td class="highlight">Interv. report:</td>
		<td class="post_highlight" colspan="3">
			{if $is_editable and !$ticket_detail->intervention_report_id}
				{if $available_interventions_list}
					<select name="ticket_detail[intervention_report_id]" style="width:500px;">
						<option value="">[None]</option>
						{html_options options=$available_interventions_list selected=$ticket_detail->intervention_report_id}
					</select>
				{else}
					<font class="light_text">[None available]</font>
				{/if}
			{else}
				{if $ticket_detail->intervention_report_id}
                    {assign var="p" value="id:"|cat:$intervention_report->id|cat:',returl:'|cat:$ret_url}
					<a href="{'krifs'|get_link:'intervention_edit':$p:'template'}"
					>[#{$intervention_report->id}] {$intervention_report->subject|escape}</a>
				{else}
					<font class="light_text">--</font>
				{/if}
			{/if}
		</td>
	</tr>
	{/if}
	{/if}
	
	<tr>
		<td class="highlight">Comments: </td>
		<td colspan="3" class="post_highlight">
			{if $is_editable}
				<textarea name="ticket_detail[comments]" rows="10" cols="100">{$ticket_detail->comments}</textarea>
			{else}
				{$ticket_detail->comments}
			{/if}
		</td>
	</tr>
</table>
<p>

{if $is_editable}
	<input type="submit" name="save" value="Save" class="button" />
	<input type="submit" name="save_no_notifs" value="Save, no notifs" class="button" />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
<input type="submit" name="cancel" value="Close" />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

{if $is_editable}
<input type="submit" name="delete" value="Delete entry"
	onClick="return confirm('Are you sure you want to delete this entry?');"
>
{/if}

<p>

</form>


<script language="JavaScript" type="text/javascript">
{literal}
//<![CDATA[
load_frm_activity (load_activity_obj ());
//]]>
{/literal}
</script>
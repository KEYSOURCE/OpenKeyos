{assign var="paging_titles" value="KRIFS, Ticket"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/activity.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/tiny_mce/tiny_mce.js" type="text/javascript"></script>
<script language="JavaScript" src="https://www.google.com/jsapi?key=ABQIAAAAFglAWfKCouzkGGGywkxlBBRkDAko8vJmYRCUPvAJklUfOe-zpxQoYmhIGfjVhH78JNTWyMjWt1ZR0w" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/fancybox/jquery.easing-1.3.pack.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/fancybox/jquery.fancybox-1.3.4.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

var orders_billable = new Array ();
{foreach from=$orders_billable_list key=order_id item=billable}
orders_billable[{$order_id}] = {if $billable}true{else}false{/if};
{/foreach}

{literal}

tinyMCE.init
(
	{
		//General options
		mode: "textareas",
		editor_selector : "mceEditor",
		theme: "advanced",
		plugins: "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect", 
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor", 
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen", 
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage", 
		theme_advanced_toolbar_location : "top", 
		theme_advanced_toolbar_align : "left", 
		theme_advanced_blockformats : "pre,p,div,h1,h2,h3,h4,h5,h6,blockquote,dt,dd,code,samp",
		theme_advanced_statusbar_location : "bottom", 
		theme_advanced_resizing : true,
        force_br_newlines: true,
        force_p_newlines: false,
        forced_root_block: ''
	}
);


function goToAddObject (ticket_id)
{
	frm = document.forms['frm_t'];
	cls_list = frm.elements['object_classes'];
	
	obj_class = cls_list.options[cls_list.selectedIndex].value;
	
	if (obj_class != '')
	{
        //alert('/krifs/ticket_object_add/'+ticket_id+'/'+obj_class);
		document.location = '/krifs/ticket_object_add/'+ticket_id+'/'+obj_class;
	}
	else
	{
		alert ('Please select the type of object to add');
	}
	
	return false;
}

$(document).ready(function () {
    $('#add_object_iframe').click(function () {
        frm = document.forms['frm_t'];
	cls_list = frm.elements['object_classes'];

	obj_class = cls_list.options[cls_list.selectedIndex].value;

	if (obj_class != '')
	{
		link = '/krifs/ticket_object_add_iframe/'+ticket_id+'/'+obj_class;
                $.fancybox({
                    'transitionIn'	:	'elastic',
                    'transitionOut'	:	'elastic',
                    'type'              :       'iframe',
                    'href'              :       link
                });
	}
	else
	{
		alert ('Please select the type of object to add');
	}

	return false;
    });

    $('#add_attachment_iframe').fancybox({
        'transitionIn'	:	'elastic',
        'transitionOut'	:	'elastic',
        'type'          :       'iframe'
    });
    return false;
});

// Called when a new order/subscription is selected, to update the ticket billable status
function orderChanged ()
{
	frm = document.forms['frm_t'];
	orders_list = frm.elements['ticket[customer_order_id]'];
	billable_list = frm.elements['ticket[billable]'];
	selected_order_id = orders_list.options[orders_list.selectedIndex].value;
	
	if (selected_order_id != '')
	{
		if (orders_billable[selected_order_id]) billable_list.options[1].selected = true;
		else billable_list.options[0].selected = true;
	}
}

// Called if the user wants to close the ticket, to ask for confirmation and check if comments have been entered.
function checkCloseTicket ()
{
	frm = document.forms['frm_t'];
	elm_comments = frm.elements['ticket_detail[comments]'];
	
	msg = 'Are you sure you want to mark this ticket as closed?';
	
	if (elm_comments.value == '')
	{
		msg = msg + '\n\nWARNING! You have not entered any comments.\n\nClick on "Cancel" to add some comments before closing or "OK" to close without adding any comments.';
	}
	
	ret = confirm(msg);
	if (!ret) elm_comments.focus();
	
	return ret;
}

function stop_work_marker(detail_id)
{
	dts = document.forms['frm_t'].elements['detail_to_stop'];
	dts.value = detail_id;
	return confirm('Do you wnt to stop working on this ticket detail?');
}

google.load("language", 1);

function setTranslation(comment_id)
{
	var select_elem = document.getElementById("langTranslate_"+comment_id);
	var to_lang = select_elem.value;
	translate_comment(comment_id, '', to_lang);
}

function translate_comment(comment_id, from_lang, to_lang)
{
	//alert("translating "+comment_id+" from "+from_lang+" to "+to_lang);
	var comment_elem = document.getElementById("comment_"+comment_id);
	var translation_elem = document.getElementById("comment_"+comment_id+"_translate");
	var text = comment_elem.innerHTML;	
	
	google.language.translate(text, from_lang, to_lang, function(res)
				{
				      if(res.translation)
				      {										
					      translation_elem.innerHTML = "<b>Translation</b><p />"+res.translation;
					      translation_elem.style.display = "block";
				      }
				}
	);	
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

{/literal}
//]]>
</script>


<h1>Ticket # {$ticket->id} : {$ticket->subject}</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="10%">
			Customer:
		</td>
		<td width="40%" class="post_highlight">
            {assign var="p" value="id:"|cat:$ticket->customer_id}
			<a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$ticket->customer->name} ({$ticket->customer_id})</a>
            {assign var="p" value="id:"|cat:$ticket->id|cat:',returl:'|cat:$ret_url}
			<a href="{'krifs'|get_link:'ticket_edit_customer':$p:'template'}" class="no_print"
			><img src="/images/icons/edit_16_grey.png" width="16" height="16" alt="Change Customer" title="Change Customer" /></a>
		</td>
		<td width="10%">
			Assigned to:
		</td>
		<td width="40%" class="post_highlight">
			{if $ticket->assigned_id}
                {assign var="p" value="id:"|cat:$ticket->assigned_id}
				<a href="{'user'|get_link:'user_edit':$p:'template'}"
				>{$ticket->assigned->get_name()|escape} ({$ticket->assigned->customer_name|escape})</a>
			{else}
				[None]
			{/if}
		</td>
	</tr>
	
	{if $ticket->now_working}
	<tr>
		<td colspan="2"> </td>
		<td>
			Currently working:
		</td>
		<td style="font-weight: 400;">
			{foreach from=$ticket->now_working key=working_user_id item=working_since}
				{$users.$working_user_id}:
				{$working_since|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				<br/>
			{/foreach}
		</td>
	</tr>
	{/if}
	</thead>

	<tr>
		<td class="highlight">Subject:</td>
		<td class="post_highlight">
			<input type="text" name="ticket[subject]" value="{$ticket->subject|escape}" size="60" />
		</td>
		<td class="highlight">Created by:</td>
		<td class="post_highlight">
			{if $ticket->user_id}
                {assign var="p" value="id:"|cat:$ticket->user_id}
                <a href="{'user'|get_link:'user_edit':$p:'template'}"
				>{$ticket->user->get_name()|escape} ({$ticket->user->customer_name|escape})</a>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Status: </td>
		<td class="post_highlight">
			{assign var="status" value=$ticket->status}
			<select name='ticket[status]'>
				{html_options options=$TICKET_STATUSES selected=$ticket->status}
			</select>
		
		{if $ticket->escalated}
			<div class="error" style="padding-left: 15px; display:inline;">
				Escalated: {$ticket->escalated|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			</div>
		{/if}
		</td>
		<td class="highlight">Source: </td>
		<td class="post_highlight">
			{assign var="source" value=$ticket->source}
			{$TICKET_SOURCES.$source}
		</td>
	</tr>
	<tr>
		<td class="highlight">Type: </td>
		<td class="post_highlight">
			<select name="ticket[type]">
			{html_options options=$TICKET_TYPES selected=$ticket->type}
			</select>
		</td>
		<td class="highlight">CC Users:</td>
		<td class="post_highlight">
			{foreach from=$ticket->cc_list item=cc_user_id name=cc_list}
				{$users_all.$cc_user_id}{if !$smarty.foreach.cc_list.last}, {/if} 
			{/foreach}
            {assign var="p" value="id:"|cat:$ticket->id}
            <a href="{'krifs'|get_link:'ticket_edit_cc':$p:'template'}"  class="no_print"
			><img src="/images/icons/edit_16_grey.png" width="16" height="16" alt="Edit CC Users" title="Edit CC Users"/></a>
			<br />
			{assign var="emails" value=$ticket->cc_manual_list}
			{foreach from=$emails item='eml'}
			{$eml},
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="highlight">Owner:</td>
		<td class="post_highlight">
			<select name="ticket[owner_id]">
				{html_options options=$users selected=$ticket->owner_id}
			</select>
		</td>
		
		<td class="highlight">Priority: </td>
		<td class="post_highlight">
			<select name="ticket[priority]">
			{html_options options=$TICKET_PRIORITIES selected=$ticket->priority}
			</select>
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Private:</td>
		<td class="post_highlight">
			<select name="ticket[private]">
				<option value="0">Public</option>
				<option value="1" {if $ticket->private}selected{/if}>Private</option>
			</select>
		</td>
		<td class="highlight">Deadline: </td>
		<td class="post_highlight" style="vertical-align: bottom">
			<input type="text" size="12" name="ticket[deadline]" 
				value="{if $ticket->deadline}{$ticket->deadline|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"
			>
			
			{literal}
			<a HREF="#" onClick="showCalendarSelector('frm_t', 'ticket[deadline]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
			
			{if $ticket->deadline_notified}
				<b>[Notifications sent]</b>
			{/if}
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Billable:</td>
		<td class="post_highlight">
			<select name="ticket[billable]">
				<option value="0">No</option>
				<option value="1" {if $ticket->billable}selected{/if}>Yes</option>
			</select>
			{if $ticket->customer_order_id}
				&nbsp;&nbsp;
				Order:
                {assign var="p" value="id:"|cat:$ticket->customer_order_id|cat:",returl:"|cat:$ret_url}
                <a href="{'erp'|get_link:'customer_order_edit':$p:'template'}"
				>#{$ticket->customer_order->get_erp_num()}: {$ticket->customer_order->subject|escape}</a>
			{/if}
		</td>
		<td class="highlight">Last updated:</td>
		<td class="post_highlight">
			{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
		</td>
	</tr>
	<tr>
		<td class="highlight">Order/Subscr.:</td>
		<td class="post_highlight">
			<select name="ticket[customer_order_id]" style="width:280px" onchange="orderChanged()">
				<option value="">[None]</option>
				{html_options options=$available_orders_list selected=$ticket->customer_order_id} 
			</select>
            {assign var="p" value="customer_id:"|cat:$ticket->customer_id|cat:",ticket_id:"|cat:$ticket->id}
			<a href="{'erp'|get_link:'customer_order_add':$p:'template'}"
			>[Create new &#0187;]</a>
		</td>
		<td class="highlgiht">PO Code: </td>
		<td class="post_highlight">
			<input type="text" name="ticket[po]" value="{$ticket->po}" />
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Scheduled tasks:</td>
		<td class="post_highlight" colspan="3">
			{foreach from=$tasks item=task}
                {assign var="p" value="id:"|cat:$task->id}
				<a href="{'krifs'|get_link:'task_edit':$p:'template'}">
				{$task->date_start|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}&nbsp;{$task->hour}&nbsp;[{$task->completed}%]</a>:
				
				{assign var="task_user_id" value=$task->user_id}
				{$users.$task_user_id} -
				{assign var="task_location_id" value=$task->location_id}
				{$locations_list.$task_location_id}
				{if $task->customer_location_id}
					: {$task->customer_location_name}
				{/if}
				{if $task->attendees_ids}
					<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;Attendees:
					{foreach from=$task->attendees_ids item=attendee_id name="attendees"}
						{$users.$attendee_id}{if !$smarty.foreach.attendees.last}, {/if}
					{/foreach}
				{/if}
				{if $task->comments}
					<br/>&nbsp;&nbsp;&nbsp;&nbsp;<font><i>{$task->comments|escape}</i></font>
				{/if}
				<br/>
			{/foreach}
            {assign var="p" value="ticket_id:"|cat:$ticket->id}
            <a href="{'krifs'|get_link:'task_add':$p:'template'}">[Add schedule &#0187;]</a>
		</td>
		
	</tr>
	
	{if $ticket->is_billable() or $interventions_list}
	<tr class="head">
		<td colspan="4">
			Intervention reports &nbsp;&nbsp;&nbsp;
            {assign var="p" value="ticket_id:"|cat:$ticket->id}
            <a href="{'krifs'|get_link:'ticket_add_intervention':$p:'template'}" class="no_print">[Create &#0187;]</a>
			&nbsp;&nbsp;&nbsp;
            {assign var="p" value="ticket_id:"|cat:$ticket->id}
            <a href="{'krifs'|get_link:'ticket_add_blank_intervention':$p:'template'}" class="no_print">[Create blank &#0187;]</a>
		</td>
	</tr>
	{if $interventions_list}
	<tr>
		<td class="highlight"> </td>
		<td class="post_highlight" colspan="3">
			{foreach from=$interventions_list key=intervention_id item=intervention_subject}
                {assign var="p" value="id:"|cat:$intervention_id|cat:",ret:"|cat:"ticket"|cat:",ticket_id:"|cat:$ticket->id}
                <a href="{'krifs'|get_link:'intervention_edit':$p:'template'}"
				>#{$intervention_id}: {$intervention_subject}</a><br/>
			{/foreach}
		</td>
	</tr>
	{/if}
	{/if}	
	
	
	<tr class="head">
		<td nowrap="nowrap">Linked objects</td>
		<td class="post_highlight">
			<select name="object_classes">
				<option value="">[Select]</option>
				{html_options options=$TICKET_OBJECT_CLASSES}
			</select>
			{* <a href="#" onClick="return goToAddObject({$ticket->id});">[Add &#0187;]</a> *}
                        <a href="#" id="add_object_iframe">[Add &#0187;]</a>
		</td>
		<td colspan="2">
			Attachments
			{* <a href="/?cl=krifs/ticket_attachment_add&ticket_id={$ticket->id}">[Add &#0187;]</a> *}
            {assign var="p" value="ticket_id:"|cat:$ticket->id}
            <a href="{'krifs'|get_link:'ticket_attachment_add_iframe':$p:'template'}" id="add_attachment_iframe">[Add &#0187;]</a>
		</td>
	</tr>
	
	{if $ticket->objects_display or $ticket->attachments}
	<tr>
		<td colspan="2">
			{foreach from=$ticket->objects_display item=object}
				{assign var="object_class" value=$object->object_class}
				{$TICKET_OBJECT_CLASSES.$object_class}:
				<a href="{$object->url}&amp;returl={$ret_url}">#{$object->id}: {$object->name}</a>
				{if $object->info}
                                    ({$object->info})
                                {/if}
				&nbsp;&nbsp;
                {assign var="p" value="ticket_id:"|cat:$ticket->id|cat:",object_class:"|cat:$object->object_class|cat:",object_id"|cat:$object->id}
                <a href="{'krifs'|get_link:'ticket_object_delete':$p:'template'}"
						onClick="return confirm ('Are you sure you want to delete this object reference?');"
						class="no_print"
					>[Delete]</a>
				<br/>
			{/foreach}
		</td>
		
		<td colspan="2">
			{foreach from=$ticket->attachments item=attachment}
                {assign var="p" value="id:"|cat:$attachment->id}
                <a href="{'krifs'|get_link:'ticket_attachment_open':$p:'template'}"
				>{$attachment->original_filename} ({$attachment->get_size_str()})</a>,
				{if $attachment->user_id}
					{$attachment->user->get_name()}, 
				{/if}
				{$attachment->uploaded|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				
				&nbsp;&nbsp;
                {assign var="p" value="id:"|cat:$attachment->id}
                <a href="{'krifs'|get_link:'ticket_attachment_delete':$p:'template'}"
					onClick="return confirm('Are you sure you want to delete this attachment?');"
					class="no_print"
				>[Delete]</a>
				<br/>
			{/foreach}
		</td>
	</tr>
	{/if}
	
	<tr class="no_print">
		<td nowrap="nowrap">
			<br/><br/>
			<input type="submit" name="save" value="Save" class="button" />
			<input type="submit" name="save_no_notifs" value="Save, no notifs" class="button" />
			
			<br/><br/>
		</td>
		<td class="post_highlight" nowrap="nowrap">
			<br><br>
			<input type="submit" name="cancel" value="Exit" class="button" />
			&nbsp;&nbsp;&nbsp;
			{if $ticket->status == $smarty.const.TICKET_STATUS_CLOSED}
				<input type="submit" name="mark_reopen" value="Re-open"
					onClick="return confirm('Are you sure you want to re-open this ticket?');"
				>
			{else}
				<input type="submit" name="mark_closed" value="Mark closed"
					onClick="return checkCloseTicket();"
				/>
				<input type="submit" name="mark_closed_no_notifs" value="Mark closed, no notifs"
					onClick="return checkCloseTicket();"
				/>
			{/if}
			{* Ticket deletion is not allowed anymore *}
			{*
			<input type="submit" name="delete" value="Delete"
				onClick="return confirm('Are you sure you want to delete this ticket?');"
			>
			*}
			<br><br>
		</td>
		<td colspan="2" nowrap="nowrap">
			<br/><br/>
			{assign var="current_user_id" value=$current_user->id}
			{if $ticket->now_working.$current_user_id}
				<input type="submit" name="unmark_now_working" value="Un-Mark working" 
					onclick="return confirm('Are you sure you want to remove the mark that you are working on this ticket?');"
				/>
			{else}
				<input type="submit" name="mark_now_working" value="Mark working" 
					onclick="return confirm('Are you sure you want to mark that you are working on this ticket?');"
				/>
			{/if}
			
			{if $ticket->escalated}
				<input type="submit" name="mark_unescalated" value="Un-escalate"
					onClick="return confirm('Are you sure you want to remove the escalated flag?');"
				>
			{else}
				<input type="submit" name="mark_escalated" value="Escalate"
					onClick="return confirm('Are you sure you want to escalate the ticket?');"
				>
			{/if}
                        <input type="submit" name="print_report" id="print_report"  value="Print ticket" />
		</td>
	</tr>
</table>
	
	
<!-- List of ticket details -->

<h2>Ticket Details</h2>
<input type="hidden" name="detail_to_stop" value="0">
{assign var="last_escalated" value=0}
<table class="list" width="98%">
{foreach from=$ticket->details item=detail name=tickets_details}
	<tr class="main_row">
		<td nowrap width="15%">
            {assign var="p" value="id:"|cat:$detail->id}
            <a href="{'krifs'|get_link:'ticket_detail_edit':$p:'template'}"
				>{$detail->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a>
		</td>
		<td width="30%">
			{if $detail->user_id}
                {assign var="p" value="id:"|cat:$detail->user->id}
                <a href="{'user'|get_link:'user_edit':$p:'template'}"
				>{$detail->user->get_name()|escape} ({$detail->user->customer_name|escape})</a>
			{/if}
		</td>
		<td width="30%">
			{if $detail->assigned_id != $last_assigned}
				{if $smarty.foreach.tickets_details.first}Assigned to:
				{else}Reassigned to:
				{/if}
				
				{if $detail->assigned_id }
                    {assign var="p" value="id:"|cat:$detail->assigned_id}
                    <a href="{'user'|get_link:'user_edit':$p:'template'}"
					>{$detail->assigned->get_name()|escape} ({$detail->assigned->customer_name|escape})</a>
				{else}
					[None]
				{/if}
			{/if}
		</td>
		<td width="15%" align="right" nowrap="nowrap">
			{if $detail->user_id}
				{if !$detail->user->is_customer_user()}
					{if $detail->work_time} Work time: {$detail->work_time|@format_interval_minutes}
					{else} --:--
					{/if}
                                        
                        {if $detail->is_editable}
                        {assign var="p" value="ticket_detail_id:"|cat:$detail->id|cat:",show_location:"|cat:"1"|cat:",title:"|cat:"Action%20type"|cat:",activity_id:"|cat:$detail->activity_id|cat:",is_continuation:"|cat:$detail->is_continuation|cat:",billable:"|cat:$detail->billable|cat:",time_in:"|cat:$detail->time_in|cat:",work_time:"|cat:$detail->work_time|cat:",time_out:"|cat:$detail->time_out|cat:",location_id:"|cat:$detail->location_id|cat:",time_start_travel_to:"|cat:$detail->time_start_travel_to|cat:",time_end_travel_to:"|cat:$detail->time_end_travel_to|cat:",time_start_travel_from:"|cat:$detail->time_start_travel_from|cat:",time_end_travel_from:"|cat:$detail->time_end_travel_from}
                        <a href="{'krifs'|get_link:'popup_activity_ajax_home':$p:'template'}" class="iframe_links_edit">[Edit &raquo;]</a>
                        {/if}
				{/if}
			{/if}
		</td>
	</tr>
	
	<tr {if $detail->private}style="color:blue;"{/if}>
		<td style="padding-bottom:15px;" id="comment_{$detail->id}_info">
			
			{if $detail->comments}
				{if $detail->private}Private {else}Public {/if}
			{/if}
			
			{if $detail->status}
				{if $detail->comments}<br/>{/if}
				{if $last_status != $detail->status}
					{assign var="last_status" value=$detail->status}
					Status: {$TICKET_STATUSES.$last_status}
				{/if}
			{/if}
			
			{if $detail->escalated != $last_escalated}
				<br/>
				{assign var="last_escalated" value=$detail->escalated}
				{if $detail->escalated}
					<font class="error">Escalated</font>
					
				{else}
					<font class="light_text">Un-escalated</font>
				{/if}
			{/if}<br /><br />
			{foreach from=$markers item="marker"}
				{if $detail->id == $marker}
					<input type="submit" name="work_marker_stop" value="Stop working" onclick="stop_work_marker({$detail->id})" />					
				{/if}
			{/foreach}
			<br /><br />
			<b>Translate to</b><p />
			<select id="langTranslate_{$detail->id}" onchange="setTranslation({$detail->id})">
				<option value="-1" selected="selected">[Select a language]</option>
				<option value='en'>English</option>
				<option value='fr'>French</option>
				<option value='nl'>Dutch</option>
				<option value='de'>German</option>
				<option value='es'>Spanish</option>
				<option value='ro'>Romanian</option>
			</select>
			
		</td>
		
		
		<td colspan="2" style="padding-bottom:15px; {if $has_prv}background-color: #f8f5b0;{/if}">
			{if $detail->intervention_report_id}
				<div style="border-bottom:1px dotted grey; display:block;">
					<b>Intervention report:</b>
					<a href="/?cl=krifs&amp;op=intervention_edit&amp;id={$detail->intervention->id}&amp;ret=ticket&amp;ticket_id={$ticket->id}"
						>#{$detail->intervention->id}: {$detail->intervention->subject|escape}</a>
				</div>
			{/if}
			<div id="comment_{$detail->id}" style="display: block;">
			{$detail->comments}
			</div>
			<div id="comment_{$detail->id}_translate" style="display: none; border: 1px dashed black; padding-right: 20px; font-style: italic;"><b>Translation</b><p /></div>
		</td>                                                     
		<td align="right" style="padding-bottom:15px;">
			{if $detail->activity_id}
				{assign var="action_type_id" value=$detail->activity_id}
				{$action_types.$action_type_id}<br/>
				
				{if $detail->is_continuation}
					[Continuation]<br/>
				{/if}
				
				{if $detail->location_id}
					{assign var="location_id" value=$detail->location_id}
					Location: {$locations_list.$location_id}<br/>
				{/if}
			{/if}
			{if $detail->time_in}
				Time in: {$detail->time_in|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br/>
			{/if}
			{if $detail->time_out}
				Time out: {$detail->time_out|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br/>
			{/if}
			{if $detail->time_start_travel_to}
				Travel to: 
				{$detail->time_start_travel_to|date_format:$smarty.const.HOUR_FORMAT_SELECTOR} - 
				{$detail->time_end_travel_to|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}<br/>
			{/if}
			{if $detail->time_start_travel_to}
				Travel from: 
				{$detail->time_start_travel_from|date_format:$smarty.const.HOUR_FORMAT_SELECTOR} - 
				{$detail->time_end_travel_from|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}<br/>
			{/if}
		</td>
	</tr>
	
	{assign var="last_assigned" value=$detail->assigned_id}
	<script type="text/javascript">
	//<![CDATA[
	//	{if $detail->comments !=""}
	//		setLanguageOptions('{$detail->id}');
	//	{/if}
	//]]>
	</script>
{/foreach}
</table>



<!-- Create a new entry for this ticket -->

<script language="JavaScript" type="text/javascript">
//<![CDATA[
//load languages

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

var last_browse_window = false;
function show_duration_popup (anchor_name)
{
	frm = document.forms['frm_t'];
	if (last_browse_window) last_browse_window.close ();
	
	// If an work time has not been previously set and if the ticket is billable, mark this as billable
	if (frm.elements['ticket_detail[work_time]'].value == 0 && ticket_billable) frm.elements['ticket_detail[billable]'].value = 1;
	
	popup_url = '/?cl=krifs/popup_activity&show_location=1&title=' + escape('Action type') + '&ticket_id=' + ticket_id;
	pass_vars = new Array ('activity_id', 'is_continuation', 'billable', 'intervention_report_id', 'time_in', 'work_time', 'time_out', 'location_id', 'time_start_travel_to', 'time_end_travel_to', 'time_start_travel_from', 'time_end_travel_from');
	for (i=0; i<pass_vars.length; i++)
	{
		popup_url = popup_url + '&'+pass_vars[i]+'=' + escape(frm.elements['ticket_detail['+pass_vars[i]+']'].value);
	}
	
	position = getAnchorPosition (anchor_name);
	x = position.x;
	y = position.y - 500;
	if (!isNaN(window.screenX)) x = x+window.screenX;
	x = x - 200;
	last_browse_window = window.open (popup_url, 'Duration', 'dependent, width=200, height=100, scrollbars=yes, resizable=yes, left='+x+', top='+y);
    
	return false;
}

$(document).ready(function () {

    $(".iframe_links_edit").click(function() {
        popup_url = $(this).attr('href') + '&ticket_id={/literal}{$ticket->id}{literal}';

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
	if (activity.activity_id > 0) elm.lastChild.nodeValue = activity.activity_name;
	else elm.lastChild.nodeValue = '--';
	
	elm = document.getElementById ('is_continuation_div');
	if (activity.is_continuation == 1) elm.style.display = 'block';
	else elm.style.display = 'none';
	
	elm = document.getElementById ('work_time_div');
	if (activity.work_time > 0)
	{
		str = activity.get_duration_string () + ' hrs., on ';
		str = str + activity.get_time_in_date_string() + ' ' + activity.get_time_in_time_string() + '; ';
		str = str + activity.location_name;
		elm.lastChild.nodeValue = str;
	}
	else elm.lastChild.nodeValue = '--';
	
	elm = document.getElementById ('billable_div');
	if (activity.billable == 1) elm.lastChild.nodeValue = 'Yes';
	else elm.lastChild.nodeValue = 'No';
	
	elm = document.getElementById ('travel_to_div');
	if (activity.time_start_travel_to > 0)
	{
		str = '- Travel to customer: ';
		str = str + ts_to_time_string (activity.time_start_travel_to) + ' - ' + ts_to_time_string (activity.time_end_travel_to);
		elm.lastChild.nodeValue = str;
		elm.style.display = 'block';
	}
	else elm.style.display = 'none';
	
	elm = document.getElementById ('travel_from_div');
	if (activity.time_start_travel_from > 0)
	{
		str = '- Travel from customer: ';
		str = str + ts_to_time_string (activity.time_start_travel_from) + ' - ' + ts_to_time_string (activity.time_end_travel_from);
		elm.lastChild.nodeValue = str;
		elm.style.display = 'block';
	}
	else elm.style.display = 'none';
	
	//alert("time_in: "+eval(activity));
	if(eval(activity.time_in) != 0 && eval(activity.time_out) != 0)
	{
		frm.elements['tdt[time_in_date]'].value = activity.get_time_in_date_string();
		frm.elements['tdt[time_in_hour]'].value = activity.get_time_in_time_string();
		
		frm.elements['tdt[time_out_date]'].value = activity.get_time_out_date_string();
		frm.elements['tdt[time_out_hour]'].value = activity.get_time_out_time_string();
		
		frm.elements['tdt[work_time]'].value = activity.get_duration_string ();		
	}
	if(activity.activity_id!=frm.elements['tdt[activity_id]'].value || activity.location_id != frm.elements['tdt[location_id]'].value)
	{
		document.getElementById('act_defaults').style.display = 'none';
	}
	else
	{
		elm = document.getElementById ('action_type_div');
		elm.lastChild.nodeValue = {/literal}'{$acttype->erp_code} {$acttype->erp_name}'{literal};
		elm = document.getElementById ('work_time_div');
		elm.lastChild.nodeValue += {/literal}'{$location->name}'{literal};
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

//sets quick defaults and passes all the data to needed values
function set_quick_defaults()
{	
	document.getElementById('act_defaults').style.display = "block";
	var frm = document.forms['frm_t'];
	frm.elements['ticket_detail[activity_id]'].value = frm.elements['tdt[activity_id]'].value;	
	frm.elements['ticket_detail[billable]'].value = 1;
	
	if(is_valid_date(frm.elements['tdt[time_in_date]'].value) && is_valid_hour(frm.elements['tdt[time_in_hour]'].value))
	{
		frm.elements['ticket_detail[time_in]'].value = date_time_to_ts(frm.elements['tdt[time_in_date]'].value, frm.elements['tdt[time_in_hour]'].value);
	}
	if(is_valid_duration(frm.elements['tdt[work_time]'].value))
	{
		frm.elements['ticket_detail[work_time]'].value = get_minutes(frm.elements['tdt[work_time]'].value);
	}
	if(is_valid_date(frm.elements['tdt[time_out_date]'].value) && is_valid_hour(frm.elements['tdt[time_out_hour]'].value))
	{
		frm.elements['ticket_detail[time_out]'].value = date_time_to_ts(frm.elements['tdt[time_out_date]'].value, frm.elements['tdt[time_out_hour]'].value);
	}
	frm.elements['ticket_detail[location_id]'].value = frm.elements['tdt[location_id]'].value;
	
	var act = load_activity_obj();
	act.time_start_travel_to = 0;
	act.time_end_travel_to = 0;
	act.time_start_travel_from = 0;
	act.time_end_travel_from = 0;
	load_frm_activity(act);
	
}

var cal_activity = new CalendarPopup(); 
cal_activity.setReturnFunction('setDateStringActivityQuick'); 
function showCalendarSelectorActivityQuick (name_form, name_element, anchor_name)
{
	elname = name_element;
	frm_name = name_form;
	cal_activity.showCalendar(anchor_name,getDateString());
}

{/literal}
//]]>
</script>

<div class="no_print">

<input type="hidden" name="ticket_detail[activity_id]" value="{$ticket_detail->activity_id}" />
<input type="hidden" name="ticket_detail[is_continuation]" value="{$ticket_detail->is_continuation}" />
<input type="hidden" name="ticket_detail[billable]" value="{$ticket_detail->billable}" />
<input type="hidden" name="ticket_detail[intervention_report_id]" value="{$ticket_detail->intervention_report_id}" />
<input type="hidden" name="ticket_detail[time_in]" value="{$ticket_detail->time_in}" />
<input type="hidden" name="ticket_detail[work_time]" value="{$ticket_detail->work_time}" />
<input type="hidden" name="ticket_detail[time_out]" value="{$ticket_detail->time_out}" />
<input type="hidden" name="ticket_detail[location_id]" value="{$ticket_detail->location_id}" />

<input type="hidden" name="ticket_detail[time_start_travel_to]" value="{$ticket_detail->time_start_travel_to}" />
<input type="hidden" name="ticket_detail[time_end_travel_to]" value="{$ticket_detail->time_end_travel_to}" />
<input type="hidden" name="ticket_detail[time_start_travel_from]" value="{$ticket_detail->time_start_travel_from}" />
<input type="hidden" name="ticket_detail[time_end_travel_from]" value="{$ticket_detail->time_end_travel_from}" />

<h2>Add ticket detail</h2>
<input type="hidden" name="ticket_detail[customer_order_id]" value="{$ticket_detail->customer_order_id}"/>
<input type="hidden" name="ticket_detail[for_subscription]" value="{$ticket_detail->for_subscription}"/>
<table class="list" width="98%">	
	<tr class="main_row">
		<td width="15%" class="highlight">Reassign to:</td>
		<td width="35%" class="post_highlight">
			<select name="ticket_detail[assigned_id]">
				<option value="0">[None]</option>
				{html_options options=$users selected=$ticket_detail->assigned_id}
				
				{if $customer_users}
					<option value="" noselect>-----------</option>
					{html_options options=$customer_users selected=$ticket_detail->assigned_id}
				{/if}
			</select>
		</td>
		<td width="10%" class="highlight">Private:</td>
		<td width="40%" class="post_highlight">
			<input type="checkbox" name="ticket_detail[private]" class="checkbox" value="1" {if $ticket_detail->private}checked{/if}> 
		</td>
	</tr>
	
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
			{*<a href="" onclick="return show_duration_popup('anchor_new_worktime');" id="anchor_new_worktime">[ Edit &#0187; ]</a>*}
                        <a href="#" id="anchor_new_worktime_fb">[ Edit &#0187; ]</a>
		</td>
		<td class="post_highlight" colspan="3">
			<div id="action_type_div" style="display:block;">--</div>
			<div id="is_continuation_div" style="display:none; font-style:italic;">[Continuation]</div>
		</td>
	</tr>
	
	<tr>
		<td class="highlight" nowrap="nowrap">
			Quick activity edit: &nbsp;&nbsp;
		</td>
		<td class="post_highlight" colspan="3">
			<ul style="list-style-type: none; display: block;">
				<li>
					Time in:
					<input type="text" size="12" name="tdt[time_in_date]" onchange="date_in_changed_quick();"
						value="{$time_in|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
					/>
					
					{literal}
					<a HREF="#" onClick="showCalendarSelectorActivityQuick ('frm_t', 'tdt[time_in_date]', 'anchor_calendar_in'); return false;" 
						name="anchor_calendar_in" id="anchor_calendar_in"
						><img src="/images/icon_cal.gif" alt="calendar" border="0" /></a>
					{/literal}
					
					<input type="text" name="tdt[time_in_hour]" size="6" onchange="hour_in_changed_quick();"
						value="{$time_in|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}"
					/> (h:m)
				</li>
				<li>
					Duration:					
					<input type="text" name="tdt[work_time]" size="8" onchange="duration_changed_quick();"
					value="{$duration|format_interval_minutes}"
					/> (h:m)
				</li>
				<li>
					Time out:
					<input type="text" size="12" name="tdt[time_out_date]" onchange="date_out_changed_quick();"
						value="{$time_out|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
					/>
					
					{literal}
					<a HREF="#" onClick="showCalendarSelectorActivityQuick ('frm_t', 'tdt[time_out_date]', 'anchor_calendar_out'); return false;" 
						name="anchor_calendar_out" id="anchor_calendar_out"
						><img src="/images/icon_cal.gif" alt="calendar" border="0" /></a>
					{/literal}
					
					<input type="text" name="tdt[time_out_hour]" size="6" onchange="hour_out_changed_quick();"
						value="{$time_out|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}"
					/> (h:m)
				</li>
			</ul>
			<ul id="act_defaults" style="list-style-type: none; display: none;">
				<li>										
					<b>Location:</b> {$location->name}
					<input type="hidden" name="tdt[location_id]" value="{$location->id}" />
					
				</li>
				<li>
					<b>Action type:</b> [{$acttype->erp_code}] {$acttype->erp_name}
					<input type="hidden" name="tdt[activity_id]" value="{$acttype->id}" />
				</li>
			</ul>			
			<input type="button" value="Set" onclick="set_quick_defaults()" />
		</td>
	</tr>
	
	<tr>
		<td class="highlight" nowrap="nowrap">
			Work marker: &nbsp;&nbsp;
		</td>
		<td class="post_highlight" colspan="3">
			<input type="checkbox" name="work_marker" class="checkbox" />
		</td>
	</tr>
	
	{if $ticket->is_billable()}
	<tr>
		<td class="highlight">Interv. report:</td>
		<td class="post_highlight" colspan="3">
			{if $available_interventions_list}
				<select name="ticket_detail[intervention_report_id]" style="width:500px;">
					<option value="">[None]</option>
					{html_options options=$available_interventions_list selected=$ticket_detail->intervention_report_id}
				</select>
			{else}
				<font class="light_text">[None available]</font>
			{/if}
		</td>
	</tr>
	{/if}
	
	<tr>
		<td class="highlight">Comments: </td>
		<td class="post_highlight" colspan="3">
			<textarea class="mceEditor" name="ticket_detail[comments]" rows="10" cols="100" {if $has_prv}style="background-color: #f8f5b0;"{/if}>{$ticket_detail->comments|escape}</textarea>
			
			<p>
			<input type="submit" name="add_entry" value="Add entry / Reassign">
			&nbsp;&nbsp;&nbsp;
			
			{if $ticket->escalated}
				<input type="submit" name="mark_unescalated" value="Un-escalate"
					onClick="return confirm('Are you sure you want to remove the escalated flag?');"
				>
			{else}
				<input type="submit" name="mark_escalated" value="Escalate"
					onClick="return confirm('Are you sure you want to escalate the ticket?');"
				>
			{/if}
			
			{if $ticket->status != $smarty.const.TICKET_STATUS_CLOSED}
				<input type="submit" name="mark_closed" value="Mark closed"
					onClick="return checkCloseTicket();"
				/>
				<input type="submit" name="mark_closed_no_notifs" value="Mark closed, no notifs"
					onClick="return checkCloseTicket();"
				/>
			{/if}

		</td>
	</tr>
	
</table>
</div>
<p>

</form>

<script language="JavaScript" type="text/javascript">
{literal}
//<![CDATA[
load_frm_activity (load_activity_obj ());
//]]>
{/literal}
</script>
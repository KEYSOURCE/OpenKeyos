{assign var="paging_titles" value="KRIFS, View Ticket"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

{literal}
<script language="JavaScript" src="/javascript/CalendarPopup.js">
</script>
<script language="JavaScript" src="http://www.google.com/jsapi?key=ABQIAAAAFglAWfKCouzkGGGywkxlBBRkDAko8vJmYRCUPvAJklUfOe-zpxQoYmhIGfjVhH78JNTWyMjWt1ZR0w" type="text/javascript"></script>
<script language="JavaScript">

function goToAddObject (ticket_id)
{
	frm = document.forms['frm_t'];
	cls_list = frm.elements['object_classes'];
	
	obj_class = cls_list.options[cls_list.selectedIndex].value;
	
	if (obj_class != '')
	{
		document.location = '/?cl=krifs/ticket_object_add&ticket_id='+ticket_id+'&object_class='+obj_class;
	}
	else
	{
		alert ('Please select the type of object to add');
	}
	
	return false;
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

</script>

{/literal}


<h1>View Ticket # {$ticket->id} : {$ticket->subject}</h1>
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
			<a href="/?cl=customer&amp;op=customer_edit&amp;id={$ticket->customer_id}">{$ticket->customer->name} ({$ticket->customer_id})</a>
		</td>
		<td width="10%">
			Assigned to:
		</td>
		<td width="40%" class="post_highlight">
			{if $ticket->assigned_id}
				<a href="/?cl=user/user_edit&id={$ticket->assigned_id}"
				>{$ticket->assigned->get_name()|escape} ({$ticket->assigned->customer_name|escape})</a>
			{else}
				[None]
			{/if}
		</td>
	</tr>
	</thead>

	<tr>
		<td class="highlight">Subject:</td>
		<td class="post_highlight">{$ticket->subject|escape}</td>
		<td class="highlight">Created by:</td>
		<td class="post_highlight">
			{if $ticket->user_id}
				<a href="/?cl=user/user_edit&id={$ticket->user_id}"
				>{$ticket->user->get_name()|escape} ({$ticket->user->customer_name|escape})</a>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Status: </td>
		<td class="post_highlight">
			{assign var="status" value=$ticket->status}
			{$TICKET_STATUSES.$status}
		
			{if $ticket->escalated}
				<div class="error" style="padding-left: 15px; display:inline;">
					Escalated: {$ticket->escalated|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				</div>
			{/if}
			
			{if $ticket->seen_manager_id}
				<br/>
				<b>Seen by manager: {$ticket->seen_manager->get_name()}{if $ticket->seen_manager_date}, {$ticket->seen_manager_date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}{/if}</b>
				<br/>
				Comments {if $current_user->is_manager}
				<a href="/?cl=krifs&amp;op=ticket_edit_manager_comments&amp;id={$ticket->id}"
				><img src="/images/icons/edit_16.png" width="16" height="16" alt="Edit comments" title="Edit comments"/></a>{/if}:
				
				{if $ticket->seen_manager_comments}
					{$ticket->seen_manager_comments|escape|nl2br}
				{else}
					[--]
				{/if}
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
			{assign var="ticket_type" value=$ticket->type}
			{$TICKET_TYPES.$ticket_type}
		</td>
		<td class="highlight">CC Users:</td>
		<td class="post_highlight">
			{foreach from=$ticket->cc_list item=cc_user_id name=cc_list}
				{$users_all.$cc_user_id}{if !$smarty.foreach.cc_list.last}, {/if} 
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="highlight">Owner:</td>
		<td class="post_highlight">
			{assign var="owner_id" value=$ticket->owner_id}
			{$users.$owner_id}
		</td>
		
		<td class="highlight">Priority: </td>
		<td class="post_highlight">
			{assign var="ticket_priority" value=$ticket->priority}
			{$TICKET_PRIORITIES.$ticket_priority}
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Private:</td>
		<td class="post_highlight">{if $ticket->private}Private{else}Public{/if}</td>
		<td class="highlight">Deadline: </td>
		<td class="post_highlight" style="vertical-align: bottom">
			{if $ticket->deadline}{$ticket->deadline|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Billable:</td>
		<td class="post_highlight">
			{if $ticket->billable}Yes{else}No{/if}			
		</td>
		<td class="highlight">Last updated:</td>
		<td class="post_highlight">
			{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
		</td>
	</tr>
	<tr>
		<td class="highlight">Order/Subscr.:</td>
		<td class="post_highlight">
			{if $ticket->customer_order_id}
                {assign var="p" value="id:"|cat:$ticket->customer_order_id|cat:",returl"|cat:$ret_url}
				<a href="{'erp'|get_link:'customer_order_edit':$p:'template'}"
				>#{$ticket->customer_order->get_erp_num()}: {$ticket->customer_order->subject|escape}</a>
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td class="highlight">PO Code: </td>
		<td class="post_highlight">
			<b>{$ticket->po}</b>
		</td>
	</tr>
	
	{if $ticket->is_billable() or $interventions_list}
	<tr class="head">
		<td colspan="4">
		{*Intervention reports*}
		Intervention reports &nbsp;&nbsp;&nbsp;
            {assign var="p" value="ticket_id:"|cat:$ticket->id}
            <a href="{'krifs'|get_link:'ticket_add_intervention':$p:'template'}" class="no_print">[Create &#0187;]</a>
		</td>
	</tr>
	{if $interventions_list}
	<tr>
		<td class="highlight"> </td>
		<td class="post_highlight" colspan="3">
			{foreach from=$interventions_list key=intervention_id item=intervention_subject}
                {assign var="p" value="id:"|cat:$interventionr_id|cat:",ret:"|cat:'ticket'|cat:",ticket_id:"|cat:$ticket->id}
                <a href="{'krifs'|get_link:'intervention_edit':$p:'template'}"
				>#{$intervention_id}: {$intervention_subject}</a><br/>
			{/foreach}
		</td>
	</tr>
	{/if}
	{/if}
	{if $ticket->objects_display or $ticket->attachments}
	<tr class="head">
		<td nowrap="nowrap">Linked objects</td>
		<td class="post_highlight" />
		<td colspan="2">Attachments</td>
	</tr>

	<tr>
		<td colspan="2">
			{foreach from=$ticket->objects_display item=object}
				{assign var="object_class" value=$object->object_class}
				{$TICKET_OBJECT_CLASSES.$object_class}:
				<a href="{$object->url}">#{$object->id}: {$object->name}</a><br/>
			{/foreach}
		</td>
		
		<td colspan="2">
			{foreach from=$ticket->attachments item=attachment}
                {assign var="p" value="id:"|cat:$attachment->id}
                <a href="{'krifs'|get_link:'ticket_attachment_open':$p:'template'}">{$attachment->original_filename} ({$attachment->get_size_str()})</a>,
				{if $attachment->user_id}
					{$attachment->user->get_name()}, 
				{/if}
				{$attachment->uploaded|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br/>
			{/foreach}
		</td>
	</tr>
	{/if}

	<tr>
		<td style="border-bottom:none;">
			<br><br>
			<input type="submit" name="cancel" value="Exit">
		</td>
		<td style="border-bottom:none;" colspan="3" nowrap="nowrap">
			<br><br>
			{if $ticket->status == $smarty.const.TICKET_STATUS_CLOSED and !$customer->status->onhold}
				<input type="submit" name="mark_reopen" value="Re-open"
					onClick="return confirm('Are you sure you want to re-open this ticket?');"
				>
				<input type="submit" name="mark_reopen_no_notifs" value="Re-open, no notifs"
					onClick="return confirm('Are you sure you want to re-open this ticket?');"
				>
				
				{if $current_user->is_manager}
					&nbsp;&nbsp;&nbsp;
					{if !$ticket->seen_manager_id}
						<input type="submit" name="mark_seen_manager" value="Mark seen by manager" />
					{else}
						<input type="submit" name="unmark_seen_manager" value="Un-mark seen by manager"
							onclick="return confirm('Are you sure you want to remove the \'Seen by manager\' mark?');"
						/>
					{/if}
				{/if}
			{/if}
		</td>
	</tr>
	
</table>



<!-- List of ticket details -->

<h2>Ticket Details</h2>
{assign var="last_escalated" value=0}
<table class="list" width="98%">
{foreach from=$ticket->details item=detail}
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
				Reassigned to:
				{if $detail->assigned_id }
                    {assign var="p" value="id:"|cat:$detail->assigned->id}
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
				{/if}
			{/if}
		</td>
	</tr>
	
	<tr {if $detail->private}style="color:blue;"{/if}>
		<td style="padding-bottom:15px;">
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
			{/if}
			<br />
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
		
		
		<td colspan="2" style="padding-bottom:15px">
			{if $detail->intervention_report_id}
				<div style="border-bottom:1px dotted grey; display:block;">
					<b>Intervention report:</b>
                    {assign var="p" value="id:"|cat:$detail->intervention->id|cat:",ret:"|cat:"ticket"|cat:",ticket_id:"|cat:$ticket->id}
                    <a href="{'krifs'|get_link:'intervention_edit':$p:'template'}"
						>#{$detail->intervention->id}: {$detail->intervention->subject|escape}</a>
				</div>
			{/if}
			<div id="comment_{$detail->id}" style="display: block;">
			{$detail->comments|nl2br}
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
		</td>
	</tr>
	
	{assign var="last_assigned" value=$detail->assigned_id}
	{/foreach}

	
	<tr>
		<td style="border-bottom:none;">
			<br><br>
			<input type="submit" name="cancel" value="Exit">
		</td>
		<td colspan="3" style="border-bottom:none;">
			<br><br>
			{if $ticket->status == $smarty.const.TICKET_STATUS_CLOSED and !$customer->status->onhold}
				<input type="submit" name="mark_reopen" value="Re-open"
					onClick="return confirm('Are you sure you want to re-open this ticket?');"
				>
				<input type="submit" name="mark_reopen_no_notifs" value="Re-open, no notifs"
					onClick="return confirm('Are you sure you want to re-open this ticket?');"
				>
			{/if}
                        {if $ticket->status != $smarty.const.TICKET_STATUS_CLOSED}
                        <input type="submit" name="mark_closed" value="Mark closed" />
                        <input type="submit" name="mark_closed_no_notifs" value="Mark closed, no notifs" />
                        {/if}
		</td>
	</tr>
	
</table>

<p>

</form>

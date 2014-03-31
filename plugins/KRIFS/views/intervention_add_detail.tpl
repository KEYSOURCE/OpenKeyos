{assign var="intervention_id" value=$intervention->id}
{assign var="paging_titles" value="KRIFS, Edit Intervention Report, Add Detail"}
{assign var="paging_urls" value="/krifs, /krifs/intervention_edit/"|cat:$intervention_id}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var details_count = 0;

{literal}

// Show or hide all the tickets details
function all_details_display (show)
{
	elm_show = document.getElementById ('link_show_all');
	elm_hide = document.getElementById ('link_hide_all');
	if (show)
	{
		elm_show.style.display = 'none';
		elm_hide.style.display = '';
	}
	else 
	{
		elm_show.style.display = '';
		elm_hide.style.display = 'none';
	}

	for (i=0; i<details_count; i++)
	{
		details_display (i, show);
	}
	
	
	return false;
}

function details_display (idx, show)
{
//details_link_{$dets_cnt}" onclick="return details_display({$cnt});">Show details &#0187;</a>
	elm = document.getElementById ('details_'+idx);
	elm_link_show = document.getElementById ('details_link_show_'+idx);
	elm_link_hide = document.getElementById ('details_link_hide_'+idx);
	if (show)
	{
		elm.style.display = '';
		elm_link_show.style.display = 'none';
		elm_link_hide.style.display = '';
	}
	else
	{
		elm.style.display = 'none';
		elm_link_show.style.display = '';
		elm_link_hide.style.display = 'none';
	}
}

{/literal}

//]]>
</script>

<h1>Add Detail</h1>

<p class="error">{$error_msg}</p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2">Intervention Report: # {$intervention->id}</td>
	</tr>
	</thead>
	
	<tr>
		<td width="10%">Subject:</td>
		<td>{$intervention->subject}</td>
	</tr>
	<tr>
		<td>Customer:</td>
		<td>{$customer->name} ({$customer->id})</td>
	</tr>
</table>
<p/>

<h2>Available Open Tickets</h2>

<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="5%">Type</td>
		<td width="29%">Subject</td>
		<td width="65%" nowrap="nowrap">
		Details &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="#" onclick="return all_details_display(true);" id="link_show_all">Show all &#0187;</a>
		<a href="#" onclick="return all_details_display(false);" id="link_hide_all" style="display:none;">Hide all &#0187;</a>
		</td>
	</tr>
	</thead>
	
	
	{assign var="dets_cnt" value=0}
	{foreach from=$tickets item=ticket}
	<tr>
		<td>
            {assign var="p" value="id:"|cat:$ticket->id}
            <a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->id}</a></td>
		<td>
			{assign var="type" value=$ticket->type}
			{$TICKET_TYPES.$type}
		</td>
		<td>{$ticket->subject|escape}</td>
		<td>
			<a href="#" id="details_link_show_{$dets_cnt}" onclick="return details_display({$dets_cnt}, true);">Show details &#0187;</a>
			<a href="#" id="details_link_hide_{$dets_cnt}" onclick="return details_display({$dets_cnt}, false);" style="display:none;">&#0171; Hide details </a>
			<table width="100%" id="details_{$dets_cnt}" style="display:none;">
			{foreach from=$ticket->details item=detail}
			{if $detail->is_valid_for_intervention()}
			<tr>
				<td width="1%"><input type="checkbox" name="detail_ids[]" value="{$detail->id}"/></td>
				<td width="99%">
					{if $detail->user_id}
						<b>User: {$detail->user->get_short_name()}</b>
						[{$detail->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}]
						<br/>
					{/if}
					{if $detail->activity_id}
						{assign var="action_type_id" value=$detail->activity_id}
						<b>Action type: {$action_types.$action_type_id}</b><br/>
					{/if}
					
					{if $detail->work_time}
						<b>
						Work time:
						{$detail->work_time|@format_interval_minutes} hrs., on
						{$detail->time_in|date_format:$smarty.const.DATE_FORMAT_SELECTOR}
						{$detail->time_in|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}
						</b>
						<br/>
					{/if}
					{*{$detail->comments|escape|nl2br}*}
					{$detail->comments|nl2br}
					
				</td>
			</tr>
			{/if}
			{/foreach}
			</table>
			
			
			<!-- {$dets_cnt++} -->
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No available tickets]</td>
	</tr>
	{/foreach}
</table>
<p/>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
details_count = {$dets_cnt};
//]]>
</script>

<input type="submit" name="save" value="Add selected" />
<input type="submit" name="cancel" value="Cancel" />
</form>

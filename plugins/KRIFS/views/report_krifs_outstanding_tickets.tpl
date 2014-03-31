{assign var="paging_titles" value="KRIFS, Tickets Reports"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

{literal}
<script language="JavaScript" src="/javascript/CalendarPopup.js">
</script>

<script language="JavaScript">
function set_interval (d_from, d_to)
{
	f = document.forms['frm'];
	f.elements['filter[date_from]'].value = d_from;
	f.elements['filter[date_to]'].value = d_to;
	return false;
}
</script>
{/literal}


<h1>Tickets Reports {if $filter.escalated_only}: Escalated Only{/if}</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="frm">
{$form_redir}

<div class="no_print">
<table width="98%" class="list">
	<tr class="head">
		<td>Customer</td>
		<td>Interval</td>
		<td>Status</td>
		<td>Type</td>
		<td colspan="2">Columns</td>
		<td> </td>
	</tr>
	<tr>
		<td>
			<select name="filter[customer_id]" style="width: 120px;">
				<option value="">[-- All --]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
		</td>
		<td>
			<input type="text" size="10" name="filter[date_from]" 
				value="{if $filter.date_from}{$filter.date_from|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"
			>
			{literal}
			<a HREF="#" onClick="showCalendarSelector('frm', 'filter[date_from]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
			-
			<input type="text" size="10" name="filter[date_to]" 
				value="{if $filter.date_to}{$filter.date_to|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"
			>
			{literal}
			<a HREF="#" onClick="showCalendarSelector('frm', 'filter[date_to]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
			<br>
			|
			<a href="" onClick="return set_interval ('{$mtd_from|date_format:$smarty.const.DATE_FORMAT_SELECTOR}', '{$mtd_to|date_format:$smarty.const.DATE_FORMAT_SELECTOR}');">MTD</a> | 
			<a href="" onClick="return set_interval ('{$ytd_from|date_format:$smarty.const.DATE_FORMAT_SELECTOR}', '{$ytd_to|date_format:$smarty.const.DATE_FORMAT_SELECTOR}');">YTD</a> |
			<a href="" onClick="return set_interval ('{$last_month_from|date_format:$smarty.const.DATE_FORMAT_SELECTOR}', '{$last_month_to|date_format:$smarty.const.DATE_FORMAT_SELECTOR}');">Last month</a> |
			<a href="" onClick="return set_interval ('', '');">None</a> |
		</td>
		
		<td nowrap="nowrap">
			<select name="filter[status]" style="width: 100px;">
				<!-- <option value="-2">[All]</option> -->
				<option value="-1" {if $filter.status==-1}selected{/if}>[Not closed]</option>
				<option valie="">[All]</option>
				{html_options options=$TICKET_STATUSES selected=$filter.status}
			</select>
			<br/>
			<input type="checkbox" class="checkbox" name="filter[escalated_only]" value="1" {if $filter.escalated_only}checked{/if}>
			Escalated only
		</td>
		
		<td>
			<select name="filter[type]" style="width: 110px;">
				<option value="">[All]</option>
				{html_options options=$TICKET_TYPES selected=$filter.type}
			</select>
			<br>
			<select name="filter[private]" style="width: 110px;">
				<option value="-2">Public & Private</option>
				<option value="0" {if $filter.private==0}selected{/if}>Public only</option>
			</select>
		</td>

		<td nowrap>
			<input type="checkbox" class="checkbox" name="filter[show_assigned]" value=1 {if $filter.show_assigned}checked{/if}> Assigned<br>
			<input type="checkbox" class="checkbox" name="filter[show_private]" value=1 {if $filter.show_private}checked{/if}> Private
		</td>
		<td nowrap>
			<input type="checkbox" class="checkbox" name="filter[show_created]" value=1 {if $filter.show_created}checked{/if}> Created<br>
			<input type="checkbox" class="checkbox" name="filter[show_updated]" value=1 {if $filter.show_updated}checked{/if}> Updated<br>
			<input type="checkbox" class="checkbox" name="filter[show_escalated]" value=1 {if $filter.show_escalated}checked{/if}> Escalated
		</td>
		
		<td align="right" style="vertical-align: middle">
			<input type="submit" name="do_filter" value="Generate">
		</td>
	</tr>
</table>
<p>
<b>Summary list:</b>
{assign var="p" value="format:"|cat:'pdf'|cat:',do_filter:'|cat:'1'}
<a href="{'krifs'|get_link:'report_krifs_outstanding_tickets':$p:'template'}">View as PDF &#0187;</a> |
{assign var="p" value="format:"|cat:'xml'|cat:',do_filter:'|cat:'1'}
<a href="{'krifs'|get_link:'report_krifs_outstanding_tickets':$p:'template'}">View as XML &#0187;</a>
<br>
<b>Detailed list:</b>
{assign var="p" value="format:"|cat:'pdf'|cat:',do_filter:'|cat:'1'}
<a href="{'krifs'|get_link:'report_krifs_outstanding_tickets':$p:'template'}">View as PDF &#0187;</a> |
{assign var="p" value="format:"|cat:'xml'|cat:',do_filter:'|cat:'1'}
<a href="{'krifs'|get_link:'report_krifs_outstanding_tickets':$p:'template'}">View as XML &#0187;</a>

</div>
<p>

<div class="print_only">
	<table width="60%">
		<thead>
		<tr>
			<td width="20%"><b>Customer:</b></td>
			<td>
				{if $filter.customer_id}
					{assign var="customer_id" value=$filter.customer_id}
					{$customers_list.$customer_id}
				{else}
					[All customers]
				{/if}
			</td>
		</tr>
		</thead>
		
		<tr>
			<td><b>Interval:</b></td>
			<td>
				{if $filter.date_from}
					From: {$filter.date_from|date_format:$smarty.const.DATE_FORMAT_SELECTOR}
				{/if}
				
				{if $filter.date_to}
					To: {$filter.date_to|date_format:$smarty.const.DATE_FORMAT_SELECTOR}
				{/if}
			</td>
		</tr>
		
		<tr>
			<td><b>Status:</b></td>
			<td>
				{if $filter.status==-2}
					[All statuses]
				{elseif $filter.status==-1}
					[Not closed]
				{else}
					{assign var="filter_status" value=$filter.status}
					{$TICKET_STATUSES.$filter_status}
				{/if}
			</td>
		</tr>
		<tr>
			<td><b>Tickets:</b></td>
			<td>
				{$tickets|@count}
			</td>
		</tr>
		<tr>
			<td><b>Generated:</b></td>
			<td>{$filter.generated|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
		</tr>
	</table>
	<br><br>
</div>

<table class="list" width="98%">
	<thead>
	<tr>
		<td nowrap class="sort_text" style="width: 30px; max-width: 30px;">
            {if $filter.order_by=='id' and $filter.order_dir=='ASC'}{assign var="id_sort" value="DESC"}{else}{assign var="id_sort" value="ASC"}{/if}
            {assign var="p" value="order_by:"|cat:"id"|cat:",order_dir:"|cat:$id_sort}
			<a href="{$sort_url|add_extra_get_params:$p:'template'}"
			>ID
			{if $filter.order_by=='id'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		<td style="width:3px;"></td>
		
		<td nowrap class="sort_text" style="width: 70%; max-width: 70%;">
            {if $filter.order_by=='subject' and $filter.order_dir=='ASC'}{assign var="subject_sort" value="DESC"}{else}{assign var="subject_sort" value="ASC"}{/if}
            {assign var="p" value="order_by:"|cat:"subject"|cat:",order_dir:"|cat:$subject_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
               
			>Subject
			{if $filter.order_by=='subject'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		{if !$filter.customer_id}
			<td nowrap class="sort_text" style="width: 15%">
                {if $filter.order_by=='customer' and $filter.order_dir=='ASC'}{assign var="customer_sort" value="DESC"}{else}{assign var="customer_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"customer"|cat:",order_dir:"|cat:$customer_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
				>Customer
				{if $filter.order_by=='customer'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
		{/if}
		
		<td nowrap class="sort_text" style="width: 10%">
            {if $filter.order_by=='type' and $filter.order_dir=='ASC'}{assign var="type_sort" value="DESC"}{else}{assign var="type_sort" value="ASC"}{/if}
            {assign var="p" value="order_by:"|cat:"type"|cat:",order_dir:"|cat:$type_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
			>Type
			{if $filter.order_by=='type'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		<td nowrap class="sort_text" style="width: 10%">
            {if $filter.order_by=='priority' and $filter.order_dir=='ASC'}{assign var="priority_sort" value="DESC"}{else}{assign var="priority_sort" value="ASC"}{/if}
            {assign var="p" value="order_by:"|cat:"priority"|cat:",order_dir:"|cat:$priority_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
			>Priority
			{if $filter.order_by=='priority'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		{if $filter.show_private}
			<td nowrap class="sort_text" style="width: 10px; text-align: center;">
                {if $filter.order_by=='private' and $filter.order_dir=='ASC'}{assign var="private_sort" value="DESC"}{else}{assign var="private_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"private"|cat:",order_dir:"|cat:$private_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
				
				>Priv.
				{if $filter.order_by=='private'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
		{/if}
		
		{if $filter.show_assigned}
			<td nowrap class="sort_text">
                {if $filter.order_by=='assigned_to' and $filter.order_dir=='ASC'}{assign var="assigned_to_sort" value="DESC"}{else}{assign var="assigned_to_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"assigned_to"|cat:",order_dir:"|cat:$assigned_to_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
                
				>Assigned
				{if $filter.order_by=='assigned_to'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
		{/if}
		
		<td nowrap class="sort_text" style="width: 10%">
            {if $filter.order_by=='status' and $filter.order_dir=='ASC'}{assign var="status_sort" value="DESC"}{else}{assign var="status_sort" value="ASC"}{/if}
            {assign var="p" value="order_by:"|cat:"status"|cat:",order_dir:"|cat:$status_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
               			
			>Status
			{if $filter.order_by=='status'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}</a>
		</td>
		
		{if $filter.show_created}
			<td nowrap class="sort_text" style="width: 25px">
                {if $filter.order_by=='created' and $filter.order_dir=='ASC'}{assign var="created_sort" value="DESC"}{else}{assign var="created_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"created"|cat:",order_dir:"|cat:$created_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
                
				>Created
				{if $filter.order_by=='created'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
		{/if}
		
		{if $filter.show_updated}
			<td nowrap class="sort_text" style="width: 25px">
                {if $filter.order_by=='last_modified' and $filter.order_dir=='ASC'}{assign var="last_modified_sort" value="DESC"}{else}{assign var="last_modified_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"last_modified"|cat:",order_dir:"|cat:$last_modified_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Updated
				{if $filter.order_by=='last_modified'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
		{/if}
		
		{if $filter.show_escalated}
			<td nowrap class="sort_text" style="width: 25px">
                {if $filter.order_by=='escalated' and $filter.order_dir=='ASC'}{assign var="escalated_sort" value="DESC"}{else}{assign var="escalated_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"escalated"|cat:",order_dir:"|cat:$escalated_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Escalated
				{if $filter.order_by=='escalated'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>
		{/if}
	</tr>
	</thead>
	
	{foreach from=$tickets item=ticket}
		<tr>
			<td style="width: 30px; max-width: 30px;">
                {assign var="p" value="id:"|cat:$ticket->id}
                <a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->id}</a></td>
			<td style="width:3px; padding:0px;">{if $ticket->escalated}<font class="error" style="font-size:12pt">!</font>{/if}</td>
			<td><a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->subject}</a></td>
			
			{if !$filter.customer_id}
				<td>
					{assign var="customer_id" value=$ticket->customer_id}
					{$customers_list.$customer_id}
				</td>
			{/if}
			
			<td>
				{assign var="ticket_type" value=$ticket->type}
				{$TICKET_TYPES.$ticket_type}
			</td>
			<td>
				{assign var="ticket_priority" value=$ticket->priority}
				{$TICKET_PRIORITIES.$ticket_priority}
			</td>
			
			{if $filter.show_private}
				<td style="width: 10px; text-align: center;">
					{if $ticket->private} Y {else} N {/if}
				</td>
			{/if}
			
			{if $filter.show_assigned}
				<td>
					{if $ticket->assigned_id}
						{$ticket->assigned->get_short_name()}
						
						{if $ticket->assigned->customer_id}
							{assign var="user_customer_id" value=$ticket->assigned->customer_id}
							({$customers_list.$user_customer_id})
						{/if} 
					{/if}
				</td>
			{/if}
			
			<td style="width: 30px">
				{assign var="ticket_status"  value=$ticket->status}
				{$TICKET_STATUSES.$ticket_status}
			</td>
			
			{if $filter.show_created}
				<td nowrap>{$ticket->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			{/if}
			
			{if $filter.show_updated}
				<td nowrap>{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			{/if}
			
			{if $filter.show_escalated}
				<td nowrap>
					{if $ticket->escalated}
						{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
					{/if}
				</td>
			{/if}
		</tr>
	{foreachelse}
		<tr>
			<td colspan="9">[No tickets found]</td>
		</tr>
	{/foreach}

</table>
<p>

</form>
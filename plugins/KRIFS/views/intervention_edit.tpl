{assign var="paging_titles" value="KRIFS, Edit Intervention Report"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

{if $intervention->can_modify() or $intervention->status==$smarty.const.INTERVENTION_STAT_CLOSED}
	<h1>Edit Intervention Report: # {$intervention->id}</h1>
{else}
	<h1>View Intervention Report: # {$intervention->id}</h1>
{/if}

<p class="error">{$error_msg}</p>
<p class="warning">{$warning_msg}</p>

<form action="" method="POST" name="intervention_edit">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td>Customer:</td>
        {assign var="p" value="id:"|cat:$customer->id}
		<td colspan="3" class="post_highlight"><a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customer->name} (#{$customer->id})</a></td>
	</tr>
	</thead>
	
	<tr>
		<td width="10%" class="highlight">Subject:</td>
		<td width="50%" class="post_highlight">
			{if $intervention->can_modify()}
				<input type="text" name="intervention[subject]" value="{$intervention->subject|escape}" size="60" />
			{else}
				{$intervention->subject|escape}
			{/if}
		</td>
		<td width="10%" class="highlight">Created:</td>
		<td width="30%" class="post_highlight">
			{if $intervention->status==$smarty.const.INTERVENTION_STAT_OPEN}
				<input type="text" size="12" name="intervention[created]" 
					value="{$intervention->created|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
				/>
				{literal}
				<a HREF="#" onClick="showCalendarSelector('intervention_edit', 'intervention[created]'); return false;"
					name="anchor_calendar" id="anchor_calendar"
					><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
				{/literal}
			{else}
				{$intervention->created|date_format:$smarty.const.DATE_FORMAT_SMARTY}
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Status:</td>
		<td class="post_highlight">
			{assign var="status" value=$intervention->status}
			{$INTERVENTION_STATS.$status}
		</td>
		<td class="highlight">Interval:</td>
		<td class="post_highlight" colspan="3">
			<div id="interval_div" style="display:inline">
			{if $intervention->time_in and $intervention->time_out}
				{$intervention->time_in|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				- 
				{$intervention->time_out|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}--{/if}
			{if $intervention->location_id}
				{assign var="location_id" value=$intervention->location_id}
				; {$locations_list.$location_id}
			{/if}
			</div>
		</td>
	</tr>
	<tr>
		<td class="highlight">Comments:</td>
		<td class="post_highlight">
			{if $intervention->can_modify()}
				<textarea name="intervention[comments]" rows="4" cols="60">{$intervention->comments|escape}</textarea>
			{else}
				{$intervention->comments|escape|nl2br}
			{/if}
		</td>
		<td class="highlight">Approved by:</td>
		<td class="post_highlight">
			{if $intervention->approved_by}
				{$approved_by->get_name()},
				{$intervention->approved_date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
	</tr>
</table>

<p>
<input type="submit" name="save" value="Save" />&nbsp;&nbsp;
<input type="submit" name="cancel" value="Exit" />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

{if $intervention->status == $smarty.const.INTERVENTION_STAT_OPEN}
	<input type="submit" name="close" value="Close intervention"
	onclick="return confirm('Are you really sure you want to close this intervention report?');"/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{elseif $intervention->status == $smarty.const.INTERVENTION_STAT_CLOSED}
	<input type="submit" name="approve" value="Approve"
	onclick="return confirm('Are you sure you want to approve the intervention report? Once approved and centralized by ERP you can NOT re-open it again.');"
	/>
	&nbsp;&nbsp;
	
	<input type="submit" name="reopen" value="Re-open"
	onclick="return confirm('Are you sure you want to re-open the intervention? ALL manually entered data will be lost.');" />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{elseif $intervention->status == $smarty.const.INTERVENTION_STAT_APPROVED}
	<!-- The intervention has been approved, but was not loaded yet in the ERP system -->
	<input type="submit" name="cancel_approval" value="Cancel approval"
	onclick="return confirm('Are you sure you want to cancel the approval.');" />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{elseif $intervention->status==$smarty.const.INTERVENTION_STAT_CENTRALIZED or $intervention->status==INTERVENTION_STAT_PENDING_CENTRALIZE}
	<!-- The intervention has been centralized in the ERP system -->
	<input type="submit" name="cancel_centralization" value="Cancel centralization"
	onclick="return confirm('Are you sure you want to cancel the centralization?');" />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{/if}

<input type="submit" name="print" value="Print / E-mail" />
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="print_blank" value="Print blank" />
{if $intervention->status < $smarty.const.INTERVENTION_STAT_APPROVED}
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="make_non_billable" value = "Non billable" />
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="adjust_tbb" value = "Adjust billing time" />
{/if}
</p>


{capture name="invoicing_lines"}
{if $intervention->lines}
<!-- The intervention has been closed and there are lines generated for invoicing -->
<h2>Invoicing Lines {if $intervention->status == $smarty.const.INTERVENTION_STAT_OPEN} - Pre-calculation{/if}
{if $intervention->status == $smarty.const.INTERVENTION_STAT_CLOSED}
	|
    {assign var="p" value="id:"|cat:$intervention->id|cat:',returl'|cat:$ret_url}
	<a href="{'krifs'|get_link:'intervention_lines_edit':$p:'template'}">Edit &#0187;</a>
{/if}
</h2>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="10%">Date / User</td>
		<td width="5%" align="right">Work</td>
		<td width="5%">Location</td>
		<td width="5%" align="center">Billable</td>
		<td width="10%">Order/Subscr.</td>
		<td width="45%">Action type</td>
		<td width="5%" align="center">Type</td>
		<td width="10%" align="right" nowrap="nowrap">Bill amount</td>
		<td width="5%" align="right">TBB</td>
	</tr>
	</thead>

	{foreach from=$intervention->lines item=detail_line}
	{assign var="action_id" value=$detail_line->action_type_id}
	<tr {if $detail_line->action_type->special_type} style="font-style:italic;"{/if}>
		<td nowrap="nowrap">
			{if $detail_line->intervention_date > 0}
				{$detail_line->intervention_date|date_format:$smarty.const.DATE_FORMAT_SHORT_SMARTY}
			{else}
				<font class="light_text">--</font>
			{/if}
			<br/>
			{if $detail_line->user_id}
				{$detail_line->user->get_short_name()}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right">
			{if $detail_line->work_time}
				{$detail_line->work_time|@format_interval_minutes}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td>
			{if $detail_line->location_id}
				{assign var="location_id" value=$detail_line->location_id}
				{$locations_list.$location_id}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="center">
			{if $detail_line->billable}Y
			{else}N
			{/if}
		</td>
		<td>
			{if $detail_line->customer_order_id}
				{$detail_line->customer_order->get_erp_num()}<br/>
				{if $detail_line->customer_order->for_subscription}
					<font class="light_text">(<i>Subscr.</i>)</font>
				{else}
					<font class="light_text">(<i>Order</i>)</font>
				{/if}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td>
			{if $detail_line->action_type_id}
				{if $detail_line->action_type->special_type==$smarty.const.ACTYPE_SPECIAL_TRAVEL}
					[{$smarty.const.ERP_TRAVEL_CODE}]
				{else}
					[{$detail_line->action_type->erp_code}]
				{/if}
				{$detail_line->action_type->name|escape}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="center">
			{if $detail_line->action_type_id}
				{if $detail_line->action_type->price_type == $smarty.const.PRICE_TYPE_HOURLY} Hourly
				{else} Fixed
				{/if}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right">
			{if $detail_line->action_type_id}
				{if $detail_line->action_type->price_type == $smarty.const.PRICE_TYPE_HOURLY}
					{$detail_line->bill_amount|@format_interval_minutes}
				{else}
					{$detail_line->bill_amount}
				{/if}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right">
			{if $detail_line->action_type_id}
				{if $detail_line->action_type->price_type == $smarty.const.PRICE_TYPE_HOURLY}
					{$detail_line->tbb_amount|@format_interval_minutes}
				{else}
					{$detail_line->tbb_amount}
				{/if}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
	</tr>
	{/foreach}
</table>
{/if}
{/capture}



{if $intervention->status != INTERVENTION_STAT_OPEN}
	<!-- If the intervention is not open, then show the invoicing lines before the details -->
	{$smarty.capture.invoicing_lines}
{/if}

<h2>Details 
{if $intervention->can_modify ()}
	    |
    {assign var="p" value="intervention_id:"|cat:$intervention->id|cat:',returl'|cat:$ret_url}
	<a href="{'krifs'|get_link:'intervention_add_detail':$p:'template'}">Add detail &#0187;</a>
{/if}
</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="10%">Time in / User</td>
		<td width="5%" align="right">Work</td>
		<td width="5%">Location</td>
		<td width="5%" align="center">Billable</td>
		<td width="10%">Order/Subscr.</td>
		
		<td width="25%">Action type</td>
		
		<td {if $intervention->can_modify()} width="35%" {else} width="40%" {/if}>Ticket ID / Comments</td>
		
		{if $intervention->can_modify()}
			<td width="5%"> </td>
		{/if}
	</tr>
	</thead>
	
	{foreach from=$intervention->details item=detail}
	<tr {if $detail->private} style="color:blue;" {/if}>
		<td nowrap="nowrap">
            {assign var="p" value="id:"|cat:$detail->id|cat:',returl'|cat:$ret_url}
			<a href="{'krifs'|get_link:'ticket_detail_edit':$p:'template'}"
				{if $detail->time_in}>{$detail->time_in|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a>
				{else}>[n/a]</a>
				{/if}
			<br/>
			{if $detail->user_id}{$detail->user->get_short_name()}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right">
			{if $detail->work_time}
				{$detail->work_time|@format_interval_minutes}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td>
			{if $detail->location_id}
				{assign var="location_id" value=$detail->location_id}
				{$locations_list.$location_id}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="center">
			{if $detail->billable}Y
			{else}N
			{/if}
		</td>
		<td>
			{if $detail->customer_order_id}
				{$detail->customer_order->get_erp_num()}<br/>
				{if $detail->customer_order->for_subscription}
					<font class="light_text">(<i>Subscr.</i>)</font>
				{else}
					<font class="light_text">(<i>Order</i>)</font>
				{/if}
			{else}<font class="light_text">--</font>
			{/if}
			
		</td>
		<td>
			{if $detail->activity_id}
				[{$detail->action_type->erp_code}] {$detail->action_type->name|escape}
				{if $detail->is_continuation}
					<font class="light_text">(<i>Continuation</i>)</font>
				{/if}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td>
			{assign var="ticket_id" value=$detail->ticket_id}
            {assign var="p" value="id:"|cat:$detail->ticket_id|cat:',returl'|cat:$ret_url}
			<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}"
				alt="Ticket #{$ticket_id}: {$intervention->tickets.$ticket_id->subject|escape}"
				title="Ticket #{$ticket_id}: {$intervention->tickets.$ticket_id->subject|escape}">#{$detail->ticket_id}</a>:
			{*{$detail->comments|escape|nl2br}*}
			{$detail->comments|nl2br}
		</td>
		{if $intervention->can_modify()}
			<td align="right" nowrap="nowrap">
                {assign var="p" value="id:"|cat:$intervention->id|cat:',detail_id:'|cat:$detail->id|cat:',returl'|cat:$ret_url}
				<a href="{'krifs'|get_link:'intervention_remove_detail':$p:'template'}"
					onclick="return confirm('Are you really sure you want to remove this from the intervention report?')"
				>[Remove]</a>
			</td>
		{/if}
	</tr>
	{foreachelse}
	<tr>
		<td {if $intervention->can_modify()} colspan="8" {else} colspan="7" {/if} class="light_text">[No details]</td>
	</tr>
	{/foreach}
</table>
<p/>

{if $intervention->status == INTERVENTION_STAT_OPEN}
	<!-- If the intervention is open, then show the invoicing lines after the details -->
	{$smarty.capture.invoicing_lines}
{/if}

</form>

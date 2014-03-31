{assign var="intervention_id" value=$intervention->id}
{assign var="paging_titles" value="KRIFS, Edit Intervention Report, Edit Invoiving Lines"}
{assign var="paging_urls" value="/krifs, /krifs/intervention_edit/"|cat:$intervention_id}
{include file="paging.html"}

<h1>Edit Invoicing Lines - Intervention Report: # {$intervention->id}</h1>

<p class="error">{$error_msg}</p>
<p class="warning">{$warning_msg}</p>

<form action="" method="POST" name="intervention_lines_edit">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td>Customer:</td>
		<td colspan="3" class="post_highlight">
            {assign var="p" value="id:"|cat:$customer->id}
            <a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customer->name} (#{$customer->id})</a>
        </td>
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
		<td width="30%" class="post_highlight">{$intervention->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
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
	</tr>
</table>
</p>


<script language="JavaScript" type="text/javascript">
//<![CDATA[

var last_good_values = new Array ();
var cnt = 0;

{literal}
function check_valid_time (elm_name, idx)
{
	frm = document.forms['intervention_lines_edit'];
	elm = frm.elements[elm_name];
	duration_string = elm.value;
	
	is_valid = false;
	if (duration_string.match(/^[0-9]{1,}\s*:\s*[0-9]{1,2}$/))
	{
		arr = duration_string.split (/\s*:\s*/);
		arr[0] = parseInt(arr[0]);
		arr[1] = parseInt(arr[1]);
		
		is_valid = (arr[0]>=0 && arr[1]>=0 && arr[1]<=59);
	}
	
	if (is_valid)
	{
		last_good_values[idx] = duration_string;
	}
	else
	{
		elm.value = last_good_values[idx];
		alert ('Please enter a valid duration, in the form hh:mm.');
		elm.focus;
	}
	
	return is_valid;
}

function check_valid_amount (elm_name, idx)
{
	frm = document.forms['intervention_lines_edit'];
	elm = frm.elements[elm_name];
	amount_string = elm.value;
	
	is_valid = amount_string.match(/^(-){0,1}[0-9]+$/);
	
	if (is_valid)
	{
		last_good_values[idx] = amount_string;
	}
	else
	{
		elm.value = last_good_values[idx];
		alert ('Please enter a valid amount.');
		elm.focus;
	}
	
	return is_valid;
}

// Called when the billable flag is changed, to see if the TBB needs to be made 0
function update_tbb_amount (line_id)
{
	frm = document.forms['intervention_lines_edit'];
	elm_billable = frm.elements['lines['+line_id+'][billable]'];
	elm_bill_amount = frm.elements['bill_amount['+line_id+']'];
	elm_tbb = frm.elements['lines['+line_id+'][tbb_amount]'];
	
	if (elm_billable.options[elm_billable.selectedIndex].value == "0")
	{
		elm_tbb.value = 0;
	}
	else
	{
		elm_tbb.value = elm_bill_amount.value;
	}
}

// Copy the billable amount into the tbb amount
function copy_bill_amount (line_id)
{
	frm = document.forms['intervention_lines_edit'];
	frm.elements['lines['+line_id+'][tbb_amount]'].value = frm.elements['bill_amount['+line_id+']'].value;
	return false;
}

{/literal}
//]]>
</script>

<h2>Invoicing Lines</h2>
<b>NOTE:</b> Click on the "Bill amount" value link to copy it into the "TBB amount" field.<p/>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="10%">Date</td>
		<td width="45%">Action type</td>
		<td width="10%">Work</td>
		<td width="5%">Billable</td>
		<td width="1%" align="center">Type</td>
		<td width="10%" align="right">Bill amount</td>
		<td width="9%" align="right">TBB</td>
	</tr>
	</thead>

	{assign var="cnt" value=0}
	{foreach from=$intervention->lines item=detail_line}
	{assign var="action_id" value=$detail_line->action_type_id}
	<tr {if $detail_line->action_type_id == $smarty.const.ACTYPE_SPECIAL_TRAVEL} style="font-style:italic;"{/if}>
		<td nowrap="nowrap">
			{$detail_line->intervention_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}
		</td>
		{if count($detail_line->ticket_detail_ids)>0}
			<td>                        
			{if $detail_line->location->helpdesk}
				<select name="lines[{$detail_line->id}][action_type_id]" style="width: 400px;">
					<option value="">[Select action type]</option>
					{foreach from=$action_types_helpdesk key=group_id item=actions}                                           
						<option value="" style="font-weight:800;">[{$actypes_categories_list.$group_id}]</option>
						{foreach from=$actions item=action_type}
						<option value="{$action_type->id}" {if $action_type->id==$detail_line->action_type_id}selected{/if}
						>[{$action_type->erp_code}] {$action_type->name|escape}</option>
						{/foreach}
						<option value=""> </option>
					{/foreach}
				</select>
			{else}                                
				<select name="lines[{$detail_line->id}][action_type_id]" style="width: 400px;">
					<option value="">[Select action type]</option>
					{foreach from=$action_types_nonhelpdesk key=group_id item=actions}                                                
						<option value="" style="font-weight:800;">[{$actypes_categories_list.$group_id}]</option>
						{foreach from=$actions item=action_type}
						<option value="{$action_type->id}" {if $action_type->id==$detail_line->action_type_id}selected{/if}
						>[{$action_type->erp_code}] {$action_type->name|escape}</option>
						{/foreach}
						<option value=""> </option>
					{/foreach}
				</select>
			{/if}
			</td>
		{else}
			<td>
				{if $detail_line->action_type_id}
					{if $detail_line->action_type->special_type==$smarty.const.ACTYPE_SPECIAL_TRAVEL}
						[{$smarty.const.ERP_TRAVEL_CODE}]
					{else}
						[{$detail_line->action_type->erp_code}]
					{/if}
					{$detail_line->action_type->name}
				{else}
					<font class="light_text">--</font>
				{/if}
			</td>
		{/if}
		
		</td>
		<td>{$detail_line->work_time|@format_interval_minutes}</td>
		<td>
			{if count($detail_line->ticket_detail_ids)>0 or $detail_line->action_type->special_type==$smarty.const.ACTYPE_SPECIAL_TRAVEL}
			<select name="lines[{$detail_line->id}][billable]" onchange="update_tbb_amount({$detail_line->id})">
				<option value="0">No</option>
				<option value="1" {if $detail_line->billable}selected{/if}>Yes</option>
			</select>
			{else}
				{if $detail_line->billable}Yes
				{else}No
				{/if}
			{/if}
		</td>
		<td align="center">
			{if $detail_line->action_type->price_type == $smarty.const.PRICE_TYPE_HOURLY} Hourly
			{else} Fixed
			{/if}
		</td>
		<td align="right">
			{if $detail_line->action_type->price_type == $smarty.const.PRICE_TYPE_HOURLY}
				<a href="#" onclick="return copy_bill_amount({$detail_line->id})" 
				>{$detail_line->bill_amount|@format_interval_minutes}</a>
				<input type="hidden" name="bill_amount[{$detail_line->id}]" value="{$detail_line->bill_amount|@format_interval_minutes}"/>
			{else}
				<a href="#" onclick="return copy_bill_amount({$detail_line->id})">&nbsp;&nbsp;{$detail_line->bill_amount}</a>
				<input type="hidden" name="bill_amount[{$detail_line->id}]" value="{$detail_line->bill_amount}"/>
			{/if}
		</td>
		<td align="right">
			{if $detail_line->action_type->price_type == $smarty.const.PRICE_TYPE_HOURLY}
				<input type="text" name="lines[{$detail_line->id}][tbb_amount]" 
					value="{$detail_line->tbb_amount|@format_interval_minutes}" size="10" 
					style="text-align:right"
					onchange="return check_valid_time(this.name, {$cnt});"
				/>
				
			{else}
				<input type="text" name="lines[{$detail_line->id}][tbb_amount]" 
					value="{$detail_line->tbb_amount}" size="10"
					style="text-align:right"
					onchange="return check_valid_amount(this.name, {$cnt});"
				/>
			{/if}
			
			<script language="JavaScript" type="text/javascript">
			//<![CDATA[
			{if $detail_line->action_type->price_type == $smarty.const.PRICE_TYPE_HOURLY}
				last_good_values[{$cnt++}] = "{$detail_line->tbb_amount|@format_interval_minutes}";
			{else}
				last_good_values[{$cnt++}] = "{$detail_line->tbb_amount}";
			{/if}
			//]]>
			</script>
		</td>
	</tr>
	{/foreach}
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>
<p/>


<h2>Details</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="10%">Time in</td>
		<td width="3%" align="right">Work</td>
		<td width="10%">Location</td>
		<td width="10%">User</td>
		<td width="34%">Action type</td>
		<td width="31%">Ticket ID / Comments</td>
	</tr>
	</thead>
	
	{foreach from=$intervention->details item=detail}
	<tr>
		<td>
            {assign var="p" value="id:"|cat:$detail->id|cat:',returl'|cat:$ret_url}
			<a href="{'krifs'|get_link:'ticket_detail_edit':$p:'template'}"
				{if $detail->time_in}>{$detail->time_in|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a>
				{else}>[n/a]</a>
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
		<td>
			{if $detail->user_id}
				{$detail->user->get_short_name()}
			{else}
				--
			{/if}
		</td>
		<td>
			{if $detail->activity_id}
				[{$detail->action_type->erp_code}] {$detail->action_type->name|escape}
				{if $detail->is_continuation}
					(<i>Continuation</i>)
				{/if}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td>
			{assign var="ticket_id" value=$detail->ticket_id}
            {assign var="p" value="id:"|cat:$detail->ticket_id}
			<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}"
				alt="{$intervention->tickets.$ticket_id->subject|escape}"
				title="{$intervention->tickets.$ticket_id->subject|escape}">#{$detail->ticket_id}</a>:
			{$detail->comments|nl2br}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td {if !$intervention->centralized} colspan="7" {else} colspan="6" {/if} class="light_text">[No details]</td>
	</tr>
	{/foreach}
	
	{if $intervention->details}
	<tr class="head">
		<td>TOTAL:</td>
		<td align="right">{$intervention->work_time|@format_interval_minutes}</td>
		
		<td {if !$intervention->centralized} colspan="5" {else} colspan="4" {/if} class="light_text"></td>
	</tr>
	{/if}
	
</table>
<p/>

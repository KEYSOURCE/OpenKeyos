{assign var="paging_titles" value="KRIFS, Intervention Approval Console"}
{assign var="paging_urls" value="/?cl=krifs, /?cl=krifs/intervention_approval_console"}
{include file="paging.html"}


<script language="JavaScript" type="text/javascript">
//<![CDATA[
var f_display_ir = 0;
var ir_ids = new Array();
var cnt = 0;
{foreach from=$interventions item="intervention"}
    ir_ids[cnt++] = {$intervention->id};
{/foreach}
{literal}

        function showInfos(irid, event){
            var _target = (window.event) ? event.srcElement : event.target;
            if(_target.id != "div_IR_"+irid){
                return;
            }
            var reltg = (event.relatedTarget) ? event.relatedTarget : event.toElement;
            while(reltg != _target && reltg.nodeName != 'BODY'){
                reltg = reltg.parentNode;
            }
            if(reltg.nodeName == "BODY"){
                var elm = document.getElementById('div_IR_'+irid);
                elm.style.display = 'none';
            }
        }

        function hideAllInfos(){
            for(i=0; i<ir_ids.length;i++){
                document.getElementById('div_IR_'+ir_ids[i]).style.display = 'none';
            }
        }

	function sel_all_approve()
	{
		var gensel = document.filter_frm.appr_sel_all;
		var appr_list = document.filter_frm.elements['appr_sel[]'];
		for(i=0; i< appr_list.length; i++)
			appr_list[i].checked = gensel.checked;
	}
	
	function change_display_IR(irid)
	{
		var ir_display = document.getElementById('IR_view_div');
		ir_display.innerHTML= document.getElementById('div_IREX_'+irid).innerHTML;
	}
{/literal}
//]]>
</script>


<h1>Intervention Reports</h1>
<p class="error">{$error_msg}</p>
<form action="" method="POST" name="filter_frm">
{$form_redir}
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="20%">Customer</td>
		<td width="15%">Account Manager</td>
		<td width="10%">Status</td>
		<td width="10%">User</td>
		<td width="10%">Per page</td>
		<td width="15%" align="right">Totals</td>
	</tr>
	</thead>
	<tr>
		<td>
			<select name="filter[customer_id]" style="width: 250px;" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All customers]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
		</td>
		<td>
			<select name="filter[manager]" style="width: 150px;" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All]</option>
				{html_options options=$ACCOUNT_MANAGERS selected=$filter.manager}
			</select>
		</td>
		<td>
			<select name="filter[status]" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All]</option>
				{html_options options=$INTERVENTION_STATS selected=$filter.status}
		</td>
		<td>
			<select name="filter[user_id]" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All]</option>
				{html_options options=$users_list selected=$filter.user_id}
			</select>
		</td>
		<td>
			<select name="filter[limit]" onchange="document.forms['filter_frm'].submit();">
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
			</select>
		</td>
		<td align="right" nowrap="nowrap">
			{foreach from=$totals key=status item=total}
				<a href="#" onclick="return set_status({$status});">{$INTERVENTION_STATS.$status}</a>:&nbsp;{$total}<br/>
			{/foreach}
		</td>
	</tr>
</table>
<p/>
<div id='console_contents' style="width: 98%; float: center; border: 0px solid red;">
	<div id="interventions_display" style="float: left; width: 60%; display: inline; border: 0px solid black;">
		<table width="98%">
			<tr>
				<td width="80%" align="left">
					{if $tot_interventions > $filter.limit}
						{if $filter.start > 0}
							<a href="" onClick="document.forms['filter_frm'].elements['go'].value='prev'; document.forms['filter_frm'].submit(); return false;">&#0171; Previous</a>
						{else}
							<font class="light_text">&#0171; Previous</font>
						{/if}
						<select name="filter[start]" onChange="document.forms['filter_frm'].submit()">
							{html_options options=$pages selected=$filter.start}
						</select>
						{if $filter.start + $filter.limit < $tot_interventions}
							<a href="" onClick="document.forms['filter_frm'].elements['go'].value='next'; document.forms['filter_frm'].submit(); return false;">Next &#0187;</a>
						{else}
							<font class="light_text">Next &#0187;</font>
						{/if}
					{/if}
				</td>				
				<td width="20%" align="right">
					<input type="submit" name="bulk_approve" id="bulk_approve" value="Approve selected IR's" />
				</td>				
			</tr>
		</table>
		<input type="hidden" name="go" value="" />
		
		<table class="list"  width="98%">
			<thead>
			<tr >
				<td style="width: 5%;">&nbsp;</td>
				<td width="1%">ID</td>
				<td {if $filter.customer_id > 0} width="49%" {else} width="29%" {/if}>Subject</td>
				{if !$filter.customer_id}
					<td width="20%">Customer</td>
				{/if}
				<td width="10%">Status</td>
				<td width="10%">Created</td>
                                <td width="7%" align="right">Work time</td>
                                <td width="7%" align="right">Billable amount</td>
                                <td width="7%" align="right">TBB amount</td>
				<td style="width: 10%; text-align: center;" >
					<input type='checkbox' name="appr_sel_all" id="appr_sel_all" onclick="sel_all_approve();"  />
				</td>				
			</tr>
			</thead>

                       
                        {assign var="cnt" value=0}
                        
			{foreach from=$interventions item=intervention}                        
			<tr {if $intervention->tickets}class="no_bottom_border"{/if}>
				<td>
                                <input type="radio" name="radio_irshow" id="radio_irshow"  value="{$intervention->id}" {if $ir_to_select>0}{if $ir_to_select==$intervention->id}checked{/if}{else}{if $cnt==0}checked{/if}{/if} onclick="change_display_IR({$intervention->id});" />
                                    {if $cnt==0}
                                    <script language="JavaScript" type="text/javascript">
                                        //<[CDATA[
                                        f_display_ir = {$intervention->id};
                                        //]]>
                                    </script>
                                    {/if}
                                    {assign var="cnt" value=$cnt+1}
				</td>
				<td><a target="_blank" href="/?cl=krifs&amp;op=intervention_edit&amp;id={$intervention->id}{if $do_filter}&amp;do_filter=1{/if}"
				>{$intervention->id}</a>{if !$intervention->has_complete_info()}&nbsp;<font class="warning">!</font>{/if}</td>

				<td>
                                    <div id="div_IR_{$intervention->id}" class="info_box" style="display: none; width: 500px; margin-top:-2px; margin-left: -2px; "
                                         onmouseout="document.getElementById('div_IR_{$intervention->id}').style.display = 'none'"
                                         onmouseover ="showInfos({$intervention->id}, event);">

                                        <div class="grid_ira">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <th colspan="7">
                                                            <a target="_blank" href="/?cl=krifs/intervention_edit&id={$intervention->id}">
                                                            {$intervention->id}: {$intervention->subject|escape}
                                                            </a>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="headlight" colspan="2">Customer</td>
                                                        {assign var="customer_id" value=$intervention->customer_id}
                                                        <td colspan="5">{$customers_list.$customer_id}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="headlight" colspan="2">Status</td>
                                                        <td colspan="5">
                                                            {assign var="status" value=$intervention->status}
                                                            {$INTERVENTION_STATS.$status}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="headlight" colspan="2">Created</td>
                                                        <td colspan="5">{$intervention->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
                                                    </tr>
                                                    {assign var="il_no" value=$intervention->lines|@count}
                                                    <tr>
                                                        <td class="headlight" colspan="7">
                                                           &nbsp;
                                                        </td>
                                                    </tr>
                                                     {assign var="cnt_tbb_tickets" value=$intervention->tickets|@count}
                                                    {if $cnt_tbb_tickets > 0}
                                                    <tr>
                                                        <td class="headlight" colspan="2">TBB in tickets</td>
                                                        <td width="100%" colspan="5">
                                                            <table width="100%">
                                                                <thead>
                                                                    <tr width="100%">
                                                                        <th class="headlght">Ticket ID</th>
                                                                        <th class="headlight">Total</th>
                                                                        <th class="headlight">In IR's</th>
                                                                        <th class="headlight">Not included</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    {foreach from=$intervention->tickets item="tick"}
                                                                    {assign var="tid" value=$tick->id}
                                                                    {assign var="tbb_time" value=$tickets_tbb.$tid}
                                                                    <tr>
                                                                        <td><a href="/?cl=krifs/ticket_edit&id={$tick->id}" target="_blank">{$tick->id}</a></td>
                                                                        <td>{$tbb_time.tot}</td>
                                                                        <td>{$tbb_time.ir}</td>
                                                                        <td><font color="{$tbb_time.color}">{$tbb_time.dif}</font></td>
                                                                    </tr>
                                                                    {/foreach}
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    {/if}
                                                    <tr>
                                                        <td class="headlight" colspan="7" style="text-decoration: underline;">
                                                           Invoicing Lines
                                                        </td>
                                                    </tr>
                                                    {if $il_no>0}
                                                    <tr>
                                                        <td class="headlight">Date/User</td>
                                                        <td class="headlight">Work</td>
                                                        <td class="headlight">Location</td>
                                                        <td class="headlight">Action</td>
                                                        <td class="headlight">Type</td>
                                                        <td class="headlight">Bill ammount</td>
                                                        <td class="headlight">TBB</td>
                                                    </tr>
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
                                                            <td>
                                                                    {if $detail_line->action_type_id}
                                                                            {if $detail_line->action_type->special_type==$smarty.const.ACTYPE_SPECIAL_TRAVEL}
                                                                                    {$smarty.const.ERP_TRAVEL_CODE}
                                                                            {else}
                                                                                    {$detail_line->action_type->erp_code}
                                                                            {/if}
                                                                            {*$detail_line->action_type->name|escape*}
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
                                                    {else}
                                                    <tr><td colspan="7">[no invoicing lines yet]</tr>
                                                    {/if}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <a target="_blank" href="/?cl=krifs&amp;op=intervention_edit&amp;id={$intervention->id}{if $do_filter}&amp;do_filter=1{/if}"
                                       onmouseover="hideAllInfos(); document.getElementById('div_IR_{$intervention->id}').style.display = '';"
                                    >{$intervention->subject}</a>
				</td>

				{if !$filter.customer_id}
					<td>
						
						<a target="_blank" href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customers_list.$customer_id}</a>
					</td>
				{/if}

				<td>
					{assign var="status" value=$intervention->status}
					{$INTERVENTION_STATS.$status}
				</td>
				<td nowrap="nowrap">{$intervention->created|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
                                <td align="right" style="width: 7%;">{$intervention->work_time|@format_interval_minutes}</td>
                                <td align="right" style="width: 7%;">{$intervention->bill_amount}</td>
                                <td align="right" style="width: 7%; font-weight: bold;">{$intervention->tbb_amount}</td>
				<td align="center" nowrap="nowrap">
					{if $status == $smarty.const.INTERVENTION_STAT_CLOSED}
					<input type="checkbox" name="appr_sel[]" id="appr_sel[]" value="{$intervention->id}" />
					{else}
                                        &nbsp;
                                        {/if}

				</td>				
			</tr>

			{if $intervention->tickets}
			<tr>
				<td> </td>
				<td {if $filter.customer_id > 0} colspan="8" {else} colspan="9" {/if}>
					<ul style="margin-top: 0px; margin-bottom: 10px; border-top: 1px dashed #666">
						{foreach from=$intervention->tickets item=ticket}
						<li>
						<a href="/?cl=krifs&amp;op=ticket_edit&amp;id={$ticket->id}">#{$ticket->id}</a>:
						{$ticket->subject|escape}
						</li>
						{/foreach}
					</ul>
				</td>
			</tr>
			{/if}
                        <tr style="display: none;">
                            <td style="display: none;">
                                <div id="div_IREX_{$intervention->id}" style="display: none;">
                                    <div class="grid_ira">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th colspan="4">
                                                        <a target="_blank" href="/?cl=krifs/intervention_edit&id={$intervention->id}">
                                                            #{$intervention->id}: {$intervention->subject|escape}
                                                        </a>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="headlight" style="width: 20%;">Customer</td>
                                                    <td style="width: 30%;">{$customers_list.$customer_id}</td>
                                                    <td class="headlight" style="width: 20%;">Status</td>
                                                    <td style="width: 30%;">{$INTERVENTION_STATS.$status}</td>
                                                </tr>
                                                <tr>
                                                    <td class="headlight" style="width: 20%;">Created</td>
                                                    <td style="width: 30%;">{$intervention->created|date_format:$smarty.const.DATE_FORMAT_SELECTOR}</td>
                                                    <td class="headlight" style="width: 20%;">Interval</td>
                                                    <td style="width: 30%;">
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
                                                    <td class="headlight">Approved by</td>
                                                    <td colspan="3">
                                                        {if $intervention->approved_by}
                                                            {assign var="approved_by" value=$intervention->approved_by}
                                                            {$approved_by->get_name()},
                                                            {$intervention->approved_date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
                                                        {else}
                                                                <font class="light_text">--</font>
                                                        {/if}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="headlight">Comments</td>
                                                    <td colspan="3">
                                                        {$intervention->comments|escape|nl2br}
                                                    </td>
                                                </tr>
                                                {assign var="cnt_tbb_tickets" value=$intervention->tickets|@count}
                                                {if $cnt_tbb_tickets > 0}
                                                <tr>
                                                    <td class="headlight">TBB in tickets</td>
                                                    <td width="100%" colspan="3">
                                                        <table width="100%">
                                                            <thead>
                                                                <tr width="100%">
                                                                    <th class="headlght">Ticket ID</th>
                                                                    <th class="headlight">Total</th>
                                                                    <th class="headlight">In IR's</th>
                                                                    <th class="headlight">Not included</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {foreach from=$intervention->tickets item="tick"}
                                                                {assign var="tid" value=$tick->id}
                                                                {assign var="tbb_time" value=$tickets_tbb.$tid}
                                                                <tr>
                                                                    <td><a href="/?cl=krifs/ticket_edit&id={$tick->id}" target="_blank">{$tick->id}</a></td>
                                                                    <td>{$tbb_time.tot}</td>
                                                                    <td>{$tbb_time.ir}</td>
                                                                    <td><font color="{$tbb_time.color}">{$tbb_time.dif}</font></td>
                                                                </tr>
                                                                {/foreach}
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                {/if}
                                            </tbody>
                                        </table>
                                        <table>
                                            <thead>
                                                {assign var="il_no" value=$intervention->lines|@count}                                                
                                                <tr>
                                                    <th colspan="7" style="margin-top: 10px; margin-bottom: 5px;">
                                                        <p>                                                        
                                                            <a target="_blank" href="/?cl=krifs/intervention_print&id={$intervention->id}" title="Print / Email this Intervention Report">Print &raquo;</a>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;                                                        
                                                        {if $intervention->status < $smarty.const.INTERVENTION_STAT_APPROVED}
                                                            &nbsp;&nbsp;
                                                            <input type="submit" name="make_non_billable" value = "Non billable" />
                                                            &nbsp;&nbsp;
                                                            <input type="submit" name="adjust_tbb" value = "Adjust billing time" />
                                                        {/if}
                                                        </p>
                                                    </th>
                                                </tr>                                                
                                                <tr>
                                                    <th colspan="7" style="margin-top:10px; margin-bottom: 5px; font-size: 1.3em;">Invoicing lines</th>
                                                </tr>                                                
                                                {if $il_no>0}
                                                <tr>
                                                    <td>Date/User</td>
                                                    <td>Work</td>
                                                    <td>Location</td>
                                                    <td>Action</td>
                                                    <td>Type</td>
                                                    <td>Bill ammount</td>
                                                    <td>TBB</td>
                                                </tr>
                                                {/if}
                                                
                                            </thead>
                                            <tbody>
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
                                                        <td>
                                                                {if $detail_line->action_type_id}
                                                                        {if $detail_line->action_type->special_type==$smarty.const.ACTYPE_SPECIAL_TRAVEL}
                                                                                {$smarty.const.ERP_TRAVEL_CODE}
                                                                        {else}
                                                                                {$detail_line->action_type->erp_code}
                                                                        {/if}
                                                                        {*$detail_line->action_type->name|escape*}
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
                                                {if $il_no==0}
                                                <tr><td colspan="7">[no invoicing lines yet]</tr>
                                                {/if}
                                            </tbody>

                                        </table>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th style="font-size: 1.3em; margin-top: 10px; margin-bottom: 5px">Details</th>
                                                </tr>
                                                <tr>
                                                    <td>Time in / User</td>
                                                    <td align="right">Work</td>
                                                    <td>Location</td>
                                                    <td align="center">Billable</td>
                                                    <td>Action type</td>
                                                    <td>Ticket</td>
                                                    {if $intervention->can_modify()}
                                                    <td>&nbsp;</td>
                                                    {/if}
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {foreach from=$intervention->details item=detail}
                                                <tr {if $detail->private} style="color:blue;" {/if}>
                                                        <td nowrap="nowrap">
                                                                <a target="_blank" href="/?cl=krifs&amp;op=ticket_detail_edit&amp;id={$detail->id}&amp;returl={$ret_url}"
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
                                                                {if $detail->activity_id}
                                                                        {$detail->action_type->erp_code}
                                                                        {if $detail->is_continuation}<br />
                                                                                <font class="light_text">(<i>Continuation</i>)</font>
                                                                        {/if}
                                                                {else}
                                                                        <font class="light_text">--</font>
                                                                {/if}
                                                        </td>
                                                        <td>
                                                                {assign var="ticket_id" value=$detail->ticket_id}
                                                                <a target="_blank" href="/?cl=krifs&amp;op=ticket_edit&amp;id={$detail->ticket_id}&amp;returl={$ret_url}"
                                                                        alt="Ticket #{$ticket_id}: {$intervention->tickets.$ticket_id->subject|escape}"
                                                                        title="Ticket #{$ticket_id}: {$intervention->tickets.$ticket_id->subject|escape}">#{$detail->ticket_id}</a> /<br />Det. <a target="_blank" href="/?cl=krifs&amp;op=ticket_detail_edit&amp;id={$detail->id}&amp;returl={$ret_url}">{$detail->id}</a>
                                                        </td>
                                                        {if $intervention->can_modify()}
                                                                <td align="right" nowrap="nowrap">
                                                                        <a href="/?cl=krifs&amp;op=intervention_remove_detail&amp;id={$intervention->id}&amp;detail_id={$detail->id}&amp;returl={$ret_url}"
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
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </td>
                        </tr>
			{foreachelse}
			<tr>
				<td class="light_text" {if $filter.customer_id > 0} colspan="5" {else} colspan="6" {/if}>[No matching intervention reports]</td>
			</tr>
			{/foreach}
		</table>
	</div>
	
	<div name="IR_view_div" id='IR_view_div' style="width: 39%; border: 1px dashed black; float: right; display: inline;" />

    <script language="JavaScript" type="text/javascript">
        //<[CDATA[
            var f_display_irx = {$ir_to_select};
            {literal}
            if(f_display_irx == 0){
                if(f_display_ir!=0)
                    change_display_IR(f_display_ir);
            }
            else change_display_IR(f_display_irx);
            {/literal}
        //]]>
    </script>
</div>
</form>

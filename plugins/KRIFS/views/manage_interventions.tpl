{assign var="paging_titles" value="KRIFS, Intervention Reports"}
{assign var="paging_urls" value="/krifs, /krifs/manage_interventions"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function set_status (status)
{
	frm = document.forms['filter_frm'];
	elm = frm.elements['filter[status]'];
	for (i=0; i<elm.options.length; i++)
	{
		if (elm.options[i].value == status)
		{
			elm.options[i].selected = true;
		}
	}
	frm.submit ();
	return false;
}

function sel_all_approve()
{
    var gensel = document.filter_frm.sel_appr_all;
    var appr_list = document.filter_frm.elements['appr_sel[]'];

    for(i=0;i<appr_list.length;i++)
    {
        appr_list[i].checked = gensel.checked;
    }

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
		<td width="15%">Account manager</td>
		<td width="10%">Status</td>
		<td width="10%">User</td>
		<td width="15%">Per page</td>
		<td width="15%">Exports</td>
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
			<select name="filter[manager]" style="width: 100px;" onchange="document.forms['filter_frm'].submit();">
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

		<td nowrap="nowrap">
			<a href="{'erp'|get_link:'manage_interventions_exports'}">View Exports &#0187;</a>
		</td>
		<td align="right" nowrap="nowrap">
			{foreach from=$totals key=status item=total}
				<a href="#" onclick="return set_status({$status});">{$INTERVENTION_STATS.$status}</a>:&nbsp;{$total}<br/>
			{/foreach}
		</td>
	</tr>
</table>
<p/>

<table width="98%">
	<tr>
		<td width="50%">
            {assign var="p" value="customer_id:"|cat:$filter.customer_id|cat:",do_filter:"|cat:"1"}
			<a href="{'krifs'|get_link:'intervention_add':$p:'template'}">New Intervention Report &#0187;</a>
		</td>
		<td width="50%" align="right">
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
	</tr>
</table>
<input type="hidden" name="go" value="" />

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td {if $filter.customer_id > 0} width="49%" {else} width="29%" {/if}>Subject</td>
		{if !$filter.customer_id > 0}
			<td width="20%">Customer</td>
		{/if}
		<td width="15%">Status</td>
                {assign var="sel_state" value=$filter.status}
                {if $sel_state == $smarty.const.INTERVENTION_STAT_CLOSED}
                <td style="text-align: center; white-space: nowrap;"><input type="submit" name="bulk_approve" id="bulk_approve" value="Approve" /><br /><input type="checkbox" name="sel_appr_all" onclick="sel_all_approve();" /></td>
                {/if}
		<td width="10%">Created</td>
		<td width="8%" align="right" nowrap="nowrap">Work time</td>
		<td width="7%" align="right" nowrap="nowrap">Billable amount</td>
		<td width="7%" align="right" nowrap="nowrap">TBB amount</td>
		<td width="10%"> </td>
	</tr>
	</thead>

	{foreach from=$interventions item=intervention}
	<tr {if $intervention->tickets}class="no_bottom_border"{/if}>
        {assign var="p" value="id:"|cat:$intervention->id|cat:",do_filter:"|cat:"1"}
		<td><a href="{'krifs'|get_link:'intervention_edit':$p:'template'}"
		>{$intervention->id}</a>{if !$intervention->has_complete_info()}&nbsp;<font class="warning">!</font>{/if}</td>

		<td>
            {assign var="p" value="id:"|cat:$intervention->id|cat:",do_filter:"|cat:"1"}
			<a href="{'krifs'|get_link:'intervention_edit':$p:'template'}">{$intervention->subject}</a>
		</td>

		{if !$filter.customer_id > 0}
			<td>
				{assign var="customer_id" value=$intervention->customer_id}
                {assign var="p" value="id:"|cat:$customer_id}
				<a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customers_list.$customer_id}</a>
			</td>
		{/if}

		<td>
			{assign var="status" value=$intervention->status}
			{$INTERVENTION_STATS.$status}
		</td>
                {if $sel_state == $smarty.const.INTERVENTION_STAT_CLOSED}
                <td style="text-align: center;">
                    <input type="checkbox" name="appr_sel[]" id="appr_sel[]" value="{$intervention->id}" />
                 </td>
                {/if}
		<td nowrap="nowrap">{$intervention->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>

		<td align="right">{$intervention->work_time|@format_interval_minutes}</td>
		<td align="right">{$intervention->bill_amount}</td>
		<td align="right">{$intervention->tbb_amount}</td>

		<td align="right" nowrap="nowrap">
            {assign var="p" value="id:"|cat:$intervention->id|cat:",do_filter:"|cat:"1"}
			<a href="{'krifs'|get_link:'intervention_delete':$p:'template'}"
				onclick="return confirm('Are you sure you want to delete this intervention report?');"
			>Delete &#0187;</a>
		</td>
	</tr>

	{if $intervention->tickets}
	<tr>
		<td> </td>
		<td {if $filter.customer_id > 0} colspan="8" {else} colspan="9" {/if}>
			<ul style="margin-top: 0px; margin-bottom: 2px">
				{foreach from=$intervention->tickets item=ticket}
				<li>
                {assign var="p" value="id:"|cat:$ticket->id}
				<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">#{$ticket->id}</a>:
				{$ticket->subject|escape}
				</li>
				{/foreach}
			</ul>
		</td>
	</tr>
	{/if}

	{foreachelse}
	<tr>
		<td class="light_text" {if $filter.customer_id > 0} colspan="9" {else} colspan="10" {/if}>[No matching intervention reports]</td>
	</tr>
	{/foreach}
</table>

</form>

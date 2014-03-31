{assign var="paging_titles" value="Interventions Reports"}
{include file="paging.html"}

<h1>Interventions Reports</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="interventions_frm">
{$form_redir}

View interventions: 
<select name="filter[status]" onChange="document.forms['interventions_frm'].submit()">
    <option value="">[All]</option>
    {html_options options=$INTERVENTION_STATS selected=$filter.status}
</select>
<p>

<table width="98%">
    <tr>
        <td colspan="2" width="50%">&nbsp;
        </td>
        <td align="right">
        {if $tot_interventions > $filter.limit}
            {if $filter.start > 0} 
                <a href="/?cl=customer_krifs&op=manage_interventions_submit" 
                        onClick="document.forms['interventions_frm'].elements['go'].value='prev'; document.forms['interventions_frm'].submit(); return false;"
                >&#0171; Previous</a>
            {else}
                <font class="light_text">&#0171; Previous</font>
            {/if}
            <select name="filter[start]" onChange="document.forms['interventions_frm'].submit()">
                {html_options options=$pages selected=$filter.start}
            </select>
            {if $filter.start + $filter.limit < $tot_interventions}
                <a href="/?cl=customer_krifs&op=manage_interventions_submit" 
                        onClick="document.forms['interventions_frm'].elements['go'].value='next'; document.forms['interventions_frm'].submit(); return false;" 
                >Next &#0187;</a>
            {else}
                <font class="light_text">Next &#0187;</font>
            {/if}
        {/if}
        </td>
    </tr>
</table>
<input type="hidden" name="go" value="">
<input type="hidden" name="filter[limit]" value="{$filter.limit}">
<p>

<table class="list" width="98%">
    <thead>
        <tr>
            <td width="1%">ID</td>
            <td {if $tot_cust == 1} width="49%" {else} width="29%" {/if}>Subject</td>
            {if $tot_cust > 1}
                <td width="20%">Customer</td>
            {/if}

            <td width="15%">Status</td>
            <td width="10%">Created</td>
            <td width="8%" align="right" nowrap="nowrap">Work time</td>
            <td width="7%" align="right" nowrap="nowrap">Billable amount</td>
            <td width="7%" align="right" nowrap="nowrap">TBB amount</td>
        </tr>
    </thead>
	
    {foreach from=$interventions item=intervention}
    <tr {if $intervention->tickets}class="no_bottom_border"{/if}>
        <td>{$intervention->id}{if !$intervention->has_complete_info()}&nbsp;<font class="warning">!</font>{/if}</td>

        <td>
            {$intervention->subject}
        </td>

        {if $tot_cust > 1}
        <td>
            {assign var="customer_id" value=$intervention->customer_id}
            {$customers_list.$customer_id}
        </td>
        {/if}
        <td>
            {assign var="status" value=$intervention->status}
            {$INTERVENTION_STATS.$status}
        </td>
        <td nowrap="nowrap">{$intervention->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
        <td align="right">{$intervention->work_time|@format_interval_minutes}</td>
        <td align="right">{$intervention->bill_amount}</td>
        <td align="right">{$intervention->tbb_amount}</td>
    </tr>

    {if $intervention->tickets}
    <tr>
        <td> </td>
        <td {if $filter.customer_id > 0} colspan="8" {else} colspan="9" {/if}>
            <ul style="margin-top: 0px; margin-bottom: 2px">
                {foreach from=$intervention->tickets item=ticket}
                <li>
                    <a href="/?cl=customer_krifs&op=ticket_edit&id={$ticket->id}">#{$ticket->id}:</a>
                    <a href="/?cl=customer_krifs&op=ticket_edit&id={$ticket->id}">{$ticket->subject|escape}</a>
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
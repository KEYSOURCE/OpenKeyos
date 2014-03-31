{assign var="paging_titles" value="ERP, Intervention Export"}
{assign var="paging_urls" value="/?cl=erp, /?cl=ERP&op=manage_interventions_exports"}
{include file="paging.html"}

<h1>Intervention Export</h1>

<p class="error">{$error_msg}</p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td {if $filter.customer_id > 0} width="49%" {else} width="29%" {/if}>Subject</td>
		{if !$filter.customer_id > 0}
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
		<td><a href="/?cl=krifs&amp;op=intervention_edit&amp;id={$intervention->id}{if $do_filter}&amp;do_filter=1{/if}"
		>{$intervention->id}</a>{if !$intervention->has_complete_info()}&nbsp;<font class="warning">!</font>{/if}</td>
		
		<td>
			<a href="/?cl=krifs&amp;op=intervention_edit&amp;id={$intervention->id}{if $do_filter}&amp;do_filter=1{/if}">{$intervention->subject}</a>
		</td>
		
		{if !$filter.customer_id > 0}
			<td>
				{assign var="customer_id" value=$intervention->customer_id}
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customers_list.$customer_id}</a>
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
	{/foreach}
</table>
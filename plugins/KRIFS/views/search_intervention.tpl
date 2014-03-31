{assign var="paging_titles" value="KRIFS, Search Intervention Report"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<h1>Search Intervention Report</h1>

<p class="error">{$error_msg}</p>

<form action="" method="GET">
{$form_redir}

<table width="95%">
	<tr><td width="40%" nowrap="nowrap">
		Search: <input type="text" name="search_text" value="{$search_text|escape}" size="20" />
		<input type="submit" name="search" value="Search again &#0187;" class="button" />
	</td><td align="right" nowrap="nowrap">
		{if $tot_interventions > $show_limit}
		The search returned over {$show_limit} results, only the first {$show_limit} are displayed
		{/if}
	</td></tr>
</table>

</form>

<p/>
<table width="95%" class="list">
	<thead>
	<tr>
		<td width="20">ID</td>
		<td>Subject</td>
		<td width="15%">Customer</td>
		<td width="100">Status</td>
		<td width="100">Created</td>
	</tr>
	</thead>
	
	{foreach from=$interventions item=intervention}
	<tr>
        {assign var="p" value="id:"|cat:$intervention->id}
		<td><a href="{'krifs'|get_link:'intervention_edit':$p:'template'}">{$intervention->id}</a></td>
		<td><a href="{'krifs'|get_link:'intervention_edit':$p:'template'}">{$intervention->subject|escape}</a></td>
		<td>
			{assign var="customer_id" value=$intervention->customer_id}
            {assign var="p" value="id:"|cat:$customer_id}
			<a href="{'customer'|get_link:'customer_edit':$p:'template'}">#{$customer_id}: {$customers_list.$customer_id}</a>
		</td>
		<td>
			{assign var="status" value=$intervention->status}
			{$INTERVENTION_STATS.$status}
		</td>
		<td nowrap="nowrap">
			{$intervention->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No intervention reports found]</td>
	</tr>
	{/foreach}
</table>
<p/>

{assign var="paging_titles" value="KRIFS, Intervention Reports, Exports"}
{assign var="paging_urls" value="/?cl=erp, /?cl=erp&manage_intervention_reports"}
{include file="paging.html"}


<h1>Exports</h1>

<p class="error">{$error_msg}</p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="5%">ID</td>
		<td width="10%">Created</td>
		<td width="10%">Status</td>
		
		<td>Actions</td>
		<td width="10%" align="right">XML Data</td>
	</tr>
	</thead>
	
	
	{foreach from=$exports item=export}
	<tr>
		<td><a href="/?cl=erp&op=interventions_exports_showirs&id={$export->id}">{$export->id}</a></td>
		<td nowrap="nowrap">{$export->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
		<td nowrap="nowrap">
			{assign var="status" value=$export->status}
			{$INTERVENTIONS_EXPORTS_STATS.$status}
		</td>
		<td nowrap="nowrap">
			{foreach from=$export->actions item=action}
				{$action->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}:
				{$action->request_url|escape}
				<br/>
			{foreachelse}
				<font class="light_text">--</font>
			{/foreach}
		</td>
		
		<td align="right" nowrap="nowrap">
			<a href="/?cl=erp&amp;op=intervention_export_show&amp;id={$export->id}">View XML</a>
		</td>
	</tr>
	{/foreach}
</table>
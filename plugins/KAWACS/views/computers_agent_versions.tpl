{assign var="paging_titles" value="KAWACS, Computers Agent Versions"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Computers Agent Versions</h1>
<p>
<font class="error">{$error_msg}</font>
<p>


The list belows shows what versions of Kawacs Agent files are currently in use:
<p>

<table class="list" width="50%">
	{foreach from=$versions_stats item=versions key=file_id}
	
	<tr class="head">
		<td nowrap="nowrap" width="160">{$KAWACS_AGENT_FILES.$file_id}</td>
		<td>Active customers</td>
		<td>All customers</td>
	</tr>
		{foreach from=$versions key=version item=count}
		<tr>
			<td>{$version} : </td>
			<td>
                {assign var="p" value="file_id:"|cat:$file_id|cat:",version:"|cat:$version|cat:",active_only:"|cat:"1"}
                <a href="{'kawacs'|get_link:'computers_agent_versions_details':$p:'template'}"
				>{$versions_stats_active.$file_id.$version} computers</a>
			</td>
			
			<td>
    {assign var="p" value="file_id:"|cat:$file_id|cat:",version:"|cat:$version}
                <a href="{'kawacs'|get_link:'computers_agent_versions_details':$p:'template'}">{$count} computers</a></td>
		</tr>
		{/foreach}
	
	{/foreach}

</table>

<p>
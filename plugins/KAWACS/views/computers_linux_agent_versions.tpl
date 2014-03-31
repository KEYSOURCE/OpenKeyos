{assign var="paging_titles" value="KAWACS, Computers Linux Agent Versions"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Computers Linux Agent Versions</h1>
<p>
<font class="error">{$error_msg}</font>
<p>


The list belows shows what versions of Kawacs Linux Agents are currently in use:
<p>

<table class="list" width="50%">
	<tr class="head">
		<td width="20%">Version</td>
		<td>Computers</td>
	</tr>
	
	{foreach from=$versions_stats key=version item=count}
	<tr>
		<td>{$version} : </td>
		<td>
            {assign var="p" value="version:"|cat:$version}
            <a href="{'kawacs'|get_link:'computers_linux_agent_versions_details':$p:'template'}">{$count} computers</a></td>
	</tr>
	{/foreach}
	
</table>

<p>
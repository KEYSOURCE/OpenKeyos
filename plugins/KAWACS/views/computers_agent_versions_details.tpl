{assign var="paging_titles" value="KAWACS, Computers Agent Versions, Details"}
{assign var="paging_urls" value="/kawacs, /kawacs/computers_agent_versions"}
{include file="paging.html"}


<h1>Computers Using {$KAWACS_AGENT_FILES.$file_id} {$version} {if $active_only}(Active cust. only){/if}</h1>
<p>
<font class="error">{$error_msg}</font>
<p>


<a href="{'kawacs'|get_link:'computers_agent_versions'}">&#0171; Back</a>
<p>

<table class="list" width="70%">
	<thead>
	<tr>
		<td colspan="2">Computer</td>
		<td width="120">Last contact</td>
		<td>Customer</td>
	</tr>
	</thead>
	
	{foreach from=$computers_version item=computer}
	
	<tr>
        {assign var="p" value="id:"|cat:$computer->id}
		<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->id}</a></td>
		<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->name}</a></td>
		<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_LONG_SMARTY}</td>
		<td>
            {assign var="p" value="id:"|cat:$computer->customer_id}
            <a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$computer->customer_name} ({$computer->customer_id})</a></td>
	</tr>
	{/foreach}

</table>

<p>

{assign var="paging_titles" value="KAWACS, Computers Linux Agent Versions, Details"}
{assign var="paging_urls" value="/=kawacs, /kawacs/computers_linux_agent_versions"}
{include file="paging.html"}

<h1>Computers Using Linux Agent v. {$version}</h1>
<p>
<font class="error">{$error_msg}</font>
<p>


<a href="/?cl=kawacs&op=computers_linux_agent_versions">&#0171; Back</a>
<p>

<table class="list" width="50%">
	<thead>
	<tr>
		<td colspan="2">Computer</td>
		<td>Customer</td>
	</tr>
	</thead>
	
	{foreach from=$computers_version item=computer}
	
	<tr>
        {assign var="p" value="id:"|cat:$computer->id}
		<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->id}</a></td>
		<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->name}</a></td>
		<td>
            {assign var="p" value="id:"|cat:$computer->customer_id}
			<a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$computer->customer_name} ({$computer->customer_id})</a>
		</td>
	</tr>
	{/foreach}

</table>

<p>

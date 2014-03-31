{assign var="paging_titles" value="KAWACS, Oldest Contacts"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Oldest Contacts</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="filter"> 
{$form_redir}

Below you have the list of computers who have not sent reports 
for more than 1 day.

<p>
<table class="list" width="98%">
	<thead>
	<tr>
		<td>ID</td>
		<td>Name</td>
		<td>Last contact</td>
		<td>Days missed</td>
		<td>Customer</td>
	</thead>
	<tr>
	
	{foreach from=$contacts item=computer}
		<tr>
			<td>
                {assign var="p" value="id:"|cat:$computer->id}
                <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->id}</a>
			</td>
			<td>
                {assign var="p" value="id:"|cat:$computer->id}
                <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->netbios_name|escape}</a>
			</td>
			<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td>{$computer->days_missed}</td>
			<td>
				{assign var="customer_id" value=$computer->customer_id}
				{$customers_list.$customer_id} ({$customer_id})
			</td>
		</tr>
	{/foreach}

</table>
<p>

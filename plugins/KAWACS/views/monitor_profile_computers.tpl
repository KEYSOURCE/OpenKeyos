{assign var="paging_titles" value="KAWACS, Manage Profiles, Computers"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles"}
{include file="paging.html"}


<h1>Computers Using Profile: {$profile->name}</h1>
<p/>
<font class="error">{$error_msg}</font>
<p/>

<a href="{'kawacs'|get_link:'manage_profiles'}">&#0171; Back to profiles</a>
<p/>

<table class="list" width="50%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td width="50%">Name</td>
		<td width="50%">Customer</td>
	</tr>
	</thead>
	{foreach from=$computers_list item=computer_name key=computer_id}
		{assign var="customer_id" value=$computers_customer_ids.$computer_id}
		<tr>
			<td width="10">
                {assign var="p" value="id:"|cat:$computer_id}
                <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer_id}</a>
			</td>
			<td>
                {assign var="p" value="id:"|cat:$computer_id}
                <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer_name}</a>
			</td>
			<td>
                {assign var="p" value="id:"|cat:$customer_id}
                <a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customers_list.$customer_id} ({$customer_id})</a>
			</td>
		</tr>
	
	{/foreach}

</table>

<p>
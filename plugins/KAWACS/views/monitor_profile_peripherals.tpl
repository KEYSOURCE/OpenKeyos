{assign var="paging_titles" value="KAWACS, Manage Profiles, Peripherals"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles_periph"}
{include file="paging.html"}


<h1>Peripherals Using Profile: {$profile->name}</h1>

<p class="error">{$error_msg}</p>

<a href="{'kawacs'|get_link:'manage_profiles_periph'}">&#0171; Back to profiles</a>
<p/>

<h3>Peripherals</h3>

<table class="list" width="50%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td width="50%">Name</td>
		<td width="50%">Customer</td>
	</tr>
	</thead>
	{foreach from=$peripherals_list item=peripheral_name key=peripheral_id}
		{assign var="customer_id" value=$peripherals_customer_ids.$peripheral_id}
		<tr>
            {assign var="p" value="id:"|cat:$peripheral_id}
			<td width="10">
				<a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripheral_id}</a>
			</td>
			<td>
				<a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripheral_name}</a>
			</td>
			<td>
                {assign var="p" value="id:"|cat:$customer_id}
				<a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customers_list.$customer_id} ({$customer_id})</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="3" class="light_text">[No peripherals]</td>
		</tr>
	{/foreach}
</table>
<p/>

<h3>AD Printers</h3>


<table class="list" width="50%">
	<thead>
	<tr>
		<td width="50%">Name</td>
		<td width="50%">Customer</td>
	</tr>
	</thead>
	{foreach from=$ad_printers_list item=ad_printer_name key=ad_printer_id}
		{assign var="customer_id" value=$ad_printers_customer_ids.$ad_printer_id}
		<tr>
			<td>
                {assign var="p" value="id:"|cat:$ad_printer_id}
				<a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$ad_printer_name}</a>
			</td>
			<td>
                {assign var="p" value="id:"|cat:$customer_id}
                <a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customers_list.$customer_id} ({$customer_id})</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="2" class="light_text">[No AD Printers]</td>
		</tr>
	{/foreach}
</table>

<p/>
<a href="{'kawacs'|get_link:'manage_profiles_periph'}">&#0171; Back to profiles</a>
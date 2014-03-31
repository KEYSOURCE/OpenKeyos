{assign var="paging_titles" value="KAWACS, Manage Peripheral Classes, Class Usage"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripheral_classes"}
{include file="paging.html"}


<h1>Customers Using : {$peripheral_class->name}</h1>
<p class="error">{$error_msg}</p>

<p><a href="/?cl=kawacs&amp;op=manage_peripherals_classes">&#0171; Back to classes</a></p>

<table width="60%" class="list">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td>Customer name</td>
		<td width="30" align="right">Peripherals</td>
	</tr>
	</thead>
	
	{foreach from=$customers_peripheral key=customer_id item=peripherals_count}
	<tr>
		<td><a href="/?cl=kawacs&op=manage_peripherals&customer_id={$customer_id}">{$customer_id}</a></td>
		<td><a href="/?cl=kawacs&op=manage_peripherals&customer_id={$customer_id}">{$customers_list.$customer_id}</a></td>
		<td align="right">{$peripherals_count}</td>
	</tr>
	{/foreach}
</table>

<p><a href="/?cl=kawacs&amp;op=manage_peripherals_classes">&#0171; Back to classes</a></p>

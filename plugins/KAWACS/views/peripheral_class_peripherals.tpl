{assign var="paging_titles" value="KAWACS, Manage Peripheral Classes, Class Usage"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripheral_classes"}
{include file="paging.html"}

<h1>Peripherals of Class : {$peripheral_class->name}</h1>

<p class="error">{$error_msg}</p>


<p><a href="/?cl=kawacs&amp;op=manage_peripherals_classes">&#0171; Back to classes</a></p>

<table width="70%" class="list">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td width="50%">Peripheral</td>
		<td>Customer</td>
	</tr>
	</thead>
	
	{foreach from=$peripherals_list key=customer_id item=cust_peripherals_list}
		{foreach from=$cust_peripherals_list key=peripheral_id item=peripheral_name}
		<tr>
			<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral_id}">{$peripheral_id}</a></td>
			<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral_id}">{$peripheral_name|escape}</a></td>
			<td>
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">#{$customer_id}: {$customers_list.$customer_id}</a>
			</td>
		</tr>
		{/foreach}
	{foreachelse}
	<tr>
		<td colspan="3" class="ligh_text">[No peripherals of this class]</td>
	</tr>
	{/foreach}
	
	
</table>


<p><a href="/?cl=kawacs&amp;op=manage_peripherals_classes">&#0171; Back to classes</a></p>
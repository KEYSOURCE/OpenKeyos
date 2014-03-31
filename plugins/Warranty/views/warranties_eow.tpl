<h1>Out of Warranty List 
	{if $customer_id>0}: {$customers_list.$customer_id|escape}
	{elseif $customer_id==-1}: [All Customers]
	{/if}
</h1>

<p class="error">{$error_msg}</p>

<div class="no_print">
	<form action="" method="POST" name="filter"> 
	{$form_redir}

	<b>Customer:</b>
	<select name="filter[customer_id]"  
		onchange="document.forms['filter'].submit();"
	>
		<option value="">[Select customer]</option>
		<option value="-1" {if $filter.customer_id==-1}selected{/if}>[All Customers]</option>
		{html_options options=$customers_list selected=$filter.customer_id}
	</select>
	&nbsp;&nbsp;&nbsp;
	
	<b>Expired only:</b>
	<input type="checkbox" name="filter[expired_only]" value="1" onChange="document.forms['filter'].submit();" 
		{if $filter.expired_only}checked{/if}
		{if $customer_id==-1}disabled{/if}
	/>
</div>
<p/>

{if $customer_id}
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="20">ID</td>
			<td>Computer</td>
			{if $customer_id==-1}
				<td>Customer</td>
			{/if}
			<td>Serial number</td>
			<td>Warranty starts</td>
			<td>Warranty ends</td>
			<td>Service level</td>
			<td>Service package</td>
			<td>Product</td>
		</tr>
		</thead>
		
		{foreach from=$shown_warranties key=type item=type_warranties}
			{if $type=='eow' and !$filter.expired_only}
			<tr class="head">
				<td colspan="8">Expired Warranties:</td>
			</tr>
			{elseif $type=='active' and count($type_warranties)>0}
			<tr class="head">
				<td colspan="8">Active Warranties:</td>
			</tr>
			{elseif $type=='unknown' and count($type_warranties)>0}
			<tr class="head">
				<td colspan="8"><b>Computers without warranties info:</b></td>
			</tr>
			{/if}
		
			{foreach from=$type_warranties key=cust_id item=comps_warranties}
			{foreach from=$comps_warranties key=computer_id item=warranties}
				{assign var="comp_list" value=$computers_list.$cust_id}
				{foreach from=$warranties item=warranty}
				
				<tr>
                    {assign var="p" value="id:"|cat:$computer_id}
                    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer_id}</a></td>
					<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$comp_list.$computer_id|escape}</a></td>
					{if $customer_id==-1}
						<td>
                            {assign var="p" value="id:"|cat:$cust_id}
                            <a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customers_list.$cust_id}</a></td>
					{/if}
					<td>{$warranty->sn|escape}</td>
					<td>
						{if $warranty->warranty_starts}{$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
						{else}-{/if}
					</td>
					<td>
						{if $warranty->warranty_ends}{$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}
						{else}-{/if}
					</td>
					
					<td>
						{if $warranty->service_level_id}
							{assign var="service_level_id" value=$warranty->service_level_id}
							{$service_levels_list.$service_level_id}
						{else}-{/if}
					</td>
					<td>
						{if $warranty->service_package_id}
							{assign var="service_package_id" value=$warranty->service_package_id}
							{$service_packages_list.$service_package_id}
						{else}-{/if}
					</td>
					<td>{$warranty->product|escape}</td>
				</tr>
				{/foreach}
			{/foreach}
			{foreachelse}
				{if $type=='eow'}
				<tr>
					<td colspan="8" class="light_text">[No expired warranties]</td>
					{if $customer_id==-1}<td></td>{/if}
				</tr>
				{/if}
			{/foreach}
		{/foreach}
	
	</table>
{/if}

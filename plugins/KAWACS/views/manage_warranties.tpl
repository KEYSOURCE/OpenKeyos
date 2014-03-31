{assign var="paging_titles" value="KAWACS, Manage Warranties"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Manage Warranties</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="filter"> 
{$form_redir}

<b>Customer:</b>

<select name="filter[customer_id]"  
	onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
>
	<option value="">[Select customer]</option>
	{html_options options=$customers_list selected=$filter.customer_id}
</select>
<input type="hidden" name="do_filter_hidden" value="0">
<p/>

{if $customer->id}
<h2>Computers Warranties</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="19%">Computer</td>
		<td width="20%">Product</td>
		<td width="20%">Serial number</td>
		<td width="30%">Contract</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$computers_warranties key=computer_id item=warranties}
		{foreach from=$warranties item=warranty}
		<tr>
			<td>{$computer_id}</td>
			<td>{$computers_list.$computer_id}</td>
			<td>{$warranty->product}</td>
			<td>{$warranty->sn}</td>
			<td>
				{if $warranty->warranty_starts or $warranty->warranty_ends}
					Interval:
					{if $warranty->warranty_starts} {$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
					{else} -
					{/if}
					to
					{if $warranty->warranty_ends} {$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}
					{else} -
					{/if}
					<br/>
				{/if}
				
				{if $warranty->service_package_id or $warranty->service_level_id}
					{assign var="service_package_id" value=$warranty->service_package_id}
					{assign var="service_level_id" value=$warranty->service_level_id}
					Service level: {$service_packages_list.$service_package_id}, {$service_levels_list.$service_level_id}
					<br/>
				{/if}
				
				{if $warranty->contract_number}
					Contract: {$warranty->contract_number|escape}<br/>
				{/if}
				
				{if $warranty->hw_product_id}
					HW product ID: {$warranty->hw_product_id}<br/>
				{/if}
				
				{if $warranty->product_number}
					Product number: {$warrany->product_number}<br/>
				{/if}
			</td>
			
			<td align="right">
                {assign var="p" value="computer_id:"|cat:$computer_id|cat:",item_id:"|cat:$warranty_item_id|cat:",ret:"|cat:"manage_warranties"}
				<a href="{'kawacs'|get_link:'computer_edit_item':$p:'template'}">Edit</a>
			</td>
		</tr>
		{/foreach}
	{foreachelse}
	<tr>
		<td colspan="7">[No computer warranties]</td>
	</tr>
	{/foreach}
</table>
<p/>

<h2>AD Printers Warranties</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="40%">Name</td>
		<td width="20%">Serial number</td>
		<td width="30%">Contract</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$ad_printers_warranties item=warranty}
		{assign var="canonical_name" value=$warranty->canonical_name}
		<tr>
			<td>{$ad_printers_list.$canonical_name}</td>
			<td>{$warranty->sn}</td>
			<td>
				{if $warranty->warranty_starts or $warranty->warranty_ends}
					Interval:
					{if $warranty->warranty_starts} {$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
					{else} -
					{/if}
					to
					{if $warranty->warranty_ends} {$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}
					{else} -
					{/if}
					<br/>
				{/if}
				
				{if $warranty->service_package_id or $warranty->service_level_id}
					{assign var="service_package_id" value=$warranty->service_package_id}
					{assign var="service_level_id" value=$warranty->service_level_id}
					Service level: {$service_packages_list.$service_package_id}, {$service_levels_list.$service_level_id}
					<br/>
				{/if}
				
				{if $warranty->contract_number}
					Contract: {$warranty->contract_number|escape}<br/>
				{/if}
				
				{if $warranty->hw_product_id}
					HW product ID: {$warranty->hw_product_id}<br/>
				{/if}
				
				{if $warranty->product_number}
					Product number: {$warranty->product_number}<br/>
				{/if}
			</td>
			<td align="right">
                {assign var="p" value="canonical_name:"|cat:$canonical_name|urlencode|cat:",ret:"|cat:"manage_warranties"}
				<a href="{'kerm'|get_link:'ad_printer_warranty_edit':$p:'template'}">Edit</a>
			</td>
		</tr>
	{foreachelse}
	<tr>
		<td colspan="7">[No peripheral warranties]</td>
	</tr>
	{/foreach}
</table>


<h2>Peripherals Warranties</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="50%">Name</td>
		<td width="20%">Serial number</td>
		<td width="10%">Starts</td>
		<td width="10%">Ends</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{assign var="last_class" value=""}
	{foreach from=$peripherals_warranties key=class_id item=warranties}
		{if $class_id != $last_class}
		<tr>
			<td colspan="5"><b>{$peripherals_classes_list.$class_id}</b></td>
		</tr>
		{/if}
	
		{foreach from=$warranties key=peripheral_id item=warranty}
		<tr>
			<td>{$warranty->product}</td>
			<td>{$warranty->sn}</td>
			<td>
				{if $warranty->warranty_starts}
					{$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
				{else}
					-
				{/if}
			</td>
			<td>
				{if $warranty->warranty_ends}
					{$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}
				{else}
					-
				{/if}
			</td>
			<td align="right">
                {assign var="p" value="id:"|cat:$peripheral_id|cat:",ret:"|cat:"manage_warranties"}
				<a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">Edit</a>
			</td>
		</tr>
		{/foreach}
	{foreachelse}
	<tr>
		<td colspan="5">[No peripheral warranties]</td>
	</tr>
	{/foreach}
</table>

{/if}

<p>
</form>
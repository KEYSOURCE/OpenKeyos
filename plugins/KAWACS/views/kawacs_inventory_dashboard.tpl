{assign var="paging_titles" value="KAWACS, KAWACS Inventory Dashboard"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>Computers inventory search tool</h1>
<p>
<font class="error">{$error_msg}</font>
</p>

<form name='filter' method="POST">
{$form_redir}
{if $filter.current_computer_items}
	{foreach item=ci from=$filter.current_computer_items}
		<input type="hidden" name="filter[current_computer_items][]" value="{$ci}"> 
	{/foreach}
{/if}
{if $filter.current_peripheral_class}
	{foreach item=cp from=$filter.current_peripheral_class}
		<input type="hidden" name="filter[current_peripheral_class][]" value="{$cp}"> 
	{/foreach}
{/if}
<table width="98%" class="list">
	<tr class="head">
		<td style="width: 300px;">Customer</td>
		<td style="width: 200px;">Supplier</td>
		<td style="width: 200px;">Type</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
		<select name="filter[customer_id]">
			<option value='0'>[All]</option>
			{html_options options=$customers_all selected=$filter.customer_id}
		</select>
		</td>
		<td>
			<select name="filter[supplier_id]">
				<option value="0">[All]</option>
				{html_options options=$suppliers selected=$filter.supplier_id}
			</select>
		</td>
		<td>
			<input type="radio" name="filter[itype]" id="filter[itype]" value="0" {if $filter.itype==0}checked="checked"{/if}>Computer<br />
			<input type="radio" name="filter[itype]" id="filter[itype]" value="1" {if $filter.itype==1}checked="checked"{/if}>Peripheral<br />
			<input type="radio" name="filter[itype]" id="filter[itype]" value="2" {if $filter.itype==2}checked="checked"{/if}>AD Printers
		</td>
		<td>
			<input type="submit" name="advanced" value="Advanced">
		</td>
		<td align="right" style="vertical-align: middle">
			<input type="hidden" name="do_filter_hidden" value="0">
			<input type="submit" name="do_filter" value="Apply filter">
		</td>
	</tr>
	
</table>

<div>
{if $count}
	{if $filter.itype==0}
		<table class="list" width="98%">
		<tr class='head'>
			<td style="width: 10%;">ID</td>
			<td style="width: 20%;">Name</td>
			<td style="width: 20%">Customer</td>
			<td style="width: 48%;">On what</td>
		</tr>
		{foreach from=$computers_array item=computer}
			<tr>
				<td width="10%">{$computer->id}</td>
				<td width="20%">
                    {assign var="p" value="id:"|cat:$computer->id}
                    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->netbios_name}</a></td>
				<td width="20%">{assign var="cust_id" value=$computer->customer_id}{$customers_all.$cust_id}</td>
				<td width="48%">
					{assign var=cid value=$computer->id}
					{foreach item=citem from=$items.$cid}
						{$citem.item_id}. {$citem.item_name} -- <b>{$citem.value}</b> <br />
					{/foreach}
				</td>
			</tr>
		{/foreach}
		</table>
	{/if}
	{if $filter.itype==1}
		<table class="list" width="98%">
		<tr class='head'>
			<td style="width: 10%;">ID</td>
			<td style="width: 40%;">Name</td>
			<td style="width: 50%">Customer</td>
		</tr>
			{foreach from=$peripherals_array item=peripheral}
			<tr>
				<td width="10%">{$peripheral->id}</td>
				<td width="40%">
                    {assign var="p" value="id:"|cat:$peripheral->id}
                    <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripheral->name}</a></td>
				<td width="50%">{assign var="cust_id" value=$peripheral->customer_id}{$customers_all.$cust_id}</td>
			</tr>
			{/foreach}
		</table>
	{/if}
	
	{if $filter.itype==2}
		<table class="list" width="98%">
		<thead>
		<tr>
			<td width="60">Asset&nbsp;No.</td>
			<td width="40%">Name</td>
			<td width="20" align="left">Customer</td>
			<td width="20%" align="right">AD server</td>
		</tr>
		</thead>
		
		{foreach from=$ad_printers item=printer}
		<tr>
			<td>{$printer->asset_no}</td>
			<td>
                {assign var="p" value="id:"|cat:$printer->computer_id|cat:",nrc:"|cat:$printer->nrc}
                <a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$printer->name}</a></td>
			<td align="left">{assign var="cust_id" value=$printer->customer_id}{$customers_all.$cust_id}</td>
			<td align="right">
				{assign var="computer_id" value=$printer->computer_id}
                {assign var="p" value="id:"|cat:$printer->computer_id}
                <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$printer->computer_id}:&nbsp;{$computers_list.$computer_id}&nbsp;&#0187;</a>
			</td> 
		</tr>
		{/foreach}
		
		</table>
	{/if}	
{else}
<table class="list" width="98%">
<tr></td>
[No items matched your search criteria]
</td></tr>
</table>
{/if}

</div>


</form>
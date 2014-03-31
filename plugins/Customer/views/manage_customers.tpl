{assign var="paging_titles" value="Customers, Manage Customers"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

{literal}
<script language="JavaScript">

function changePage (start_page)
{
	frm = document.forms['filter_frm']
	
	if (start_page < 0)
	{
		pages = frm.elements['filter_start']
		start_page = pages.options[pages.selectedIndex].value
	}
	
	frm.elements['filter[start]'].value = start_page
	frm.submit ()
}

</script>

{/literal}

<h1>Manage Customers</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="filter_frm">
{$form_redir}
<input type="hidden" name="filter[start]" value="{$filter.start}">

<table class="list" width="98%">
	<thead>
	<tr>
		<td>Go to</td>
		<td>Type</td>
		<td>Active</td>
		<td>On-hold</td>
		<td>Kawacs</td>
		<td>Krifs</td>
		<td>Per Page</td>
		<td> </td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="filter[customer_id]" onChange="document.forms['filter_frm'].submit()">
				<option value="">[Select]</option>
				{html_options options=$all_customers_list}
			</select>
		</td>
		<td>
			<select name="filter[contract_type]">
				{html_options options=$CONTRACT_TYPES selected=$filter.contract_type}
			</select>
		</td>
		<td>
			<select name="filter[active]">
				<option value="-1">[Any]</option>
				<option value="0" {if $filter.active==0 and is_numeric ($filter.active)}selected{/if}>No</option>
				<option value="1" {if $filter.active==1}selected{/if}>Yes</option>
			</select>
		</td>
		<td>
			<select name="filter[onhold]">
				<option value="-1">[Any]</option>
				<option value="0" {if $filter.onhold==0 and is_numeric ($filter.onhold)}selected{/if}>No</option>
				<option value="1" {if $filter.onhold==1}selected{/if}>Yes</option>
			</select>
		</td>
		<td>
			<select name="filter[has_kawacs]">
				<option value="">[Any]</option>
				<option value="0" {if $filter.has_kawacs==0 and is_numeric ($filter.has_kawacs)}selected{/if}>No</option>
				<option value="1" {if $filter.has_kawacs==1}selected{/if}>Yes</option>
			</select>
		</td>
		<td>
			<select name="filter[has_krifs]">
				<option value="">[Any]</option>
				<option value="0" {if $filter.has_krifs==0 and is_numeric ($filter.has_krifs)}selected{/if}>No</option>
				<option value="1" {if $filter.has_krifs==1}selected{/if}>Yes</option>
			</select>
		</td>
		<td>
			<select name="filter[limit]">
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit} 
			</select>
		</td>
		<td align="right">
			<input type="submit" value="Apply">
		</td>
	</tr>
</table>


<p>
<table width="98%">
	<tr>
		<td width="50%">
			{if !$current_user->restrict_customers}
				<a href="/?cl=customer&op=customer_add">Add new customer &#0187;</a>
			{/if}
		</td>
		<td width="50%" align="right">
			{if $customers_count > $filter.limit}
				{if $filter.start > 0}
					<a href="#" onClick="changePage({$start_prev});">&#0171; Previous</a>
				{else}
					<font class="light_text">&#0171; Previous</font>
				{/if}
				
				<select name="filter_start" onChange="changePage (-1)">
					{html_options options=$pages selected=$filter.start}
				</select>
				
				{if $filter.start + $filter.limit < $customers_count}
					<a href="#" onClick="changePage({$start_next});">Next &#0187;</a>
				{else}
					<font class="light_text">Next &#0187;</font>
				{/if}
			{/if}
		
		</td>
	</tr>
</table>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="30%">Name</td>
		
		<td width="10%">Type</td>
		<td width="10%">Sub-type</td>
		<td width="10%">Price&nbsp;type</td>
		
		<td width="10%">ERP ID</td>
		<td width="5%">Active</td>
		<td width="5%">On hold</td>
		<td width="5%">Kawacs</td>
		<td width="5%">Krifs</td>
		<td width="10%"> </td>
	</thead>
	<tr>
	
	{foreach from=$customers item=customer}
		<tr>
			<td><a href="/?cl=customer&op=customer_edit&id={$customer->id}">{$customer->id}</a></td>
			<td><a href="/?cl=customer&op=customer_edit&id={$customer->id}">{$customer->name}</a></td>
			
			<!--
			$this->assign ('CONTRACT_TYPES', $GLOBALS['CONTRACT_TYPES']);
		$this->assign ('ERP_CUST_SUBTYPES', $GLOBALS['ERP_CUST_SUBTYPES']);
		$this->assign ('CUST_PRICETYPES', $GLOBALS['CUST_PRICETYPES']);
			-->
			
			<td>
				{assign var="contract_type" value=$customer->contract_type}
				{if $contract_type}{$CONTRACT_TYPES.$contract_type}
				{else}--
				{/if}
			</td>
			<td>
				{assign var="contract_sub_type" value=$customer->contract_sub_type}
				{if $contract_sub_type}{$CUST_SUBTYPES.$contract_sub_type}
				{else}--
				{/if}
			</td>
			<td>
				{assign var="price_type" value=$customer->price_type}
				{if $price_type}{$CUST_PRICETYPES.$price_type}
				{else}--
				{/if}
			</td>
			
			<td nowrap="nowrap">{$customer->erp_id|escape}</td>
			<td>
				{if !$customer->active} Disabled {/if}
			</td>
			<td>
				{if $customer->onhold} Y {else} N {/if}
			</td>
			<td>
				{if $customer->has_kawacs} Y {else} N {/if}
			</td>
			<td>
				{if $customer->has_krifs} Y {else} N {/if}
			</td>
			<td align="right">
				<a href="/?cl=customer&op=customer_delete&id={$customer->id}"
					onClick="return confirm('Are you really sure you want to delete this customer and ALL its associated data?');"
				>Delete</a>
			</td>
		</tr>
	{foreachelse}
	
		<tr><td colspan=4>
		[No customers]
		</td></tr>
	
	{/foreach}

</table>
<p>
{if !$current_user->restrict_customers}
	<a href="/?cl=customer&op=customer_add">Add new customer &#0187;</a>
{/if}
</form>
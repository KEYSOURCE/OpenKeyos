{assign var="paging_titles" value="Krifs, ERP Synchronization: Customers"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>ERP Syncronization: Customers</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}
<table width="98%">
	<tr>
		<td width="40%">
			<input type="submit" name="do_synchronize" value="Syncronize"
				onclick="return confirm('Are you sure you want to proceed with the synchronization?');"
			>
			<p/>
			<b>Note:</b> The synchronization will automatically create in Keyos all new customers from ERP (where
			all needed information is available) and will updated the Keyos details for existing customers where needed.
			<p/>
			Customers which exist only in Keyos (<font style="class:blue;">in blue</font>) should either be manually disabled in 
			Keyos, or created in ERP and their ERP ID must be manually entered in Keyos.
		</td>
		<td width="60%">
			<ul style="margin-top: 0px; margin-bottom: 0px;">
				<li><font style="color:orange;">Orange</font>: The customer is defined only in ERP.</li>
				<li><font style="color:red;">Red</font>: The customer is defined only in ERP, but the information is incomplete.</li>
				<li><font style="color:blue;">Blue</font>: The customer is defined only in Keyos (active customers only).</li>
				<li><font style="color:green;">Green</font>: The customer details differ between ERP and Keyos.</li>
			</ul>
		</td>
	</tr>
</table>
</form>
<p/>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="5%">KS&nbsp;ID</td>
		<td width="5%">ERP ID</td>
		<td width="30%">KS Name</td>
		<td width="30%">ERP Name</td>
		<td width="10%" nowrap="nowrap">Type</td>
		<td width="10%">Sub-type</td>
		<td width="10%">Price type</td>
		<!--
		<td>c_cat2</td>
		<td>c_tarif</td>
		-->
	</tr>
	</thead>
	
	{foreach from=$erp_customers item=customer}
	<tr
		{if $customer->sync_stat==$smarty.const.ERP_SYNC_STAT_KS_NEW} style="color: blue;"
		{elseif $customer->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_NEW} style="color: orange;"
		{elseif $customer->sync_stat==$smarty.const.ERP_SYNC_STAT_MODIFIED} style="color: green;"
		{elseif $customer->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_INCOMPLETE} style="color: red;"
		{/if}
	>
		<td>
			{if $customer->customer_id}
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer->customer_id}">{$customer->customer_id}</a>
			{else}--
			{/if}
		</td>
		<td nowrap>
			{if $customer->erp_id}{$customer->erp_id|escape}
			{else}--
			{/if}
		</td>
		<td>
			{if $customer->customer_id}
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer->customer_id}">{$customer->customer->name|escape}</a>
			{else}--
			{/if}
		</td>
		<td>
			{if $customer->erp_name}{$customer->erp_name|escape}
			{else}--
			{/if}
		</td>
		<!--
		<td>
			{if $customer->c_adresse}{$customer->c_adresse|escape}<br/>{/if}
			{if $customer->c_adresse2}{$customer->c_adresse2}<br/>{/if}
			{if $customer->c_codep or $customer->c_ville}
				{$customer->c_codep|escape} {$customer->c_ville},
			{/if}
			{$customer->c_pays}
		</td>
		-->
		<td>
			{if $customer->contract_type}
				{assign var="contract_type" value=$customer->contract_type}
				{$CONTRACT_TYPES.$contract_type}
			{else}
				{if $customer->c_cat1}[unknown code: "{$customer->c_cat1}"]
				{else}--
	    			{/if}
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $customer->contract_sub_type}
				{assign var="contract_sub_type" value=$customer->contract_sub_type}
				{$CUST_SUBTYPES.$contract_sub_type}
			{else}
				{if $customer->c_cat2}[unknown code: "{$customer->c_cat2}"]
				{else}--
				{/if}
			{/if}
		</td>
		<td>
			{if $customer->price_type}
				{assign var="price_type" value=$customer->price_type}
				{$CUST_PRICETYPES.$price_type}
			{else}
				{if $customer->c_tarif}[unknown code: "{$customer->c_tarif}"]
				{else}--
				{/if}
			{/if}
		</td>
		
		<!--
		<td>{$customer->c_cat2}</td>
		<td>{$customer->c_tarif}</td>
		-->
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text">[No records]</td>
	</tr>
	{/foreach}

</table>
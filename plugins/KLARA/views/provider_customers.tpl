{assign var="paging_titles" value="KLARA, Manage Internet Providers, Provider Customers"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_providers"}
{include file="paging.html"}

<h1>Provider Customers: {$provider->name}</h1>

<p class="error">{$error_msg}</p>

<p><a href="/?cl=klara&amp;op=manage_providers">&#0171 Back to providers</a></p>

<table width="70%" class="list">
	<thead>
	<tr>
		<td width="30%">Customers</td>
		<td width="40%">Provider contract</td>
		<td widht="30%">Customer contract</td>
	</tr>
	</thead>

	{foreach from=$provider_customers_list key=customer_id item=contracts}
		<tr>
			<td {if $contracts|@count>1} rowspan="{$contracts|@count}" {/if}>
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}&amp;returl={$ret_url}">{$customers_list.$customer_id} ({$customer_id})</a>
			</td>
		
			{foreach from=$contracts key=customer_internet_contract_id item=contract_id name=contracts}
			{if $smarty.foreach.contracts.iteration>1}<tr>{/if}
				<td>{$contracts_list.$contract_id}</td>
				<td>
					<a href="/?cl=klara&amp;op=customer_internet_contract_edit&amp;id={$customer_internet_contract_id}&amp;returl={$ret_url}">Contract details &#0187;</a>
				</td>
			
			{foreachelse}
				<td class="light_text" colspan="2">--</td>
			{/foreach}
		</tr>
	
	{foreachelse}
		<tr>
			<td colspan="6">[No Internet Providers defined yet]</td>
		</tr>
	{/foreach}
	
</table>

<p><a href="/?cl=klara&amp;op=manage_providers">&#0171 Back to providers</a></p>
{assign var="paging_titles" value="Krifs, ERP Synchronization: Action Types"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>ERP Synchronization: Action Types</h1>

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
			<b>Note:</b> The synchronization will automatically create in Keyos all the new action types from ERP
			(where all needed information is available) and will update the existing ones if they have been modified
			in ERP.
			<p/>
			If an action type exists in Keyos but doesn't exist anymore in ERP, it will be automatically disabled in 
			Keyos.
		</td>
		<td width="60%">
			<ul style="margin-top: 0px; margin-bottom: 0px;">
				<li><font style="color:orange;">Orange</font>: The article is defined only in ERP.</li>
				<li><font style="color:red;">Red</font>: The article is defined only in ERP, but the information is incomplete.</li>
				<li><font style="color:blue;">Blue</font>: The article is defined only in Keyos.</li>
				<li><font style="color:green;">Green</font>: The article details differ between ERP and Keyos.</li>
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
		<td width="7%">ERP ID</td>
		<td width="7%">ERP code</td>
		<td width="20%">KS&nbsp;Name</td>
		<td width="20%">ERP&nbsp;Name</td>
		<td width="15%">Category<br/>(Sub-family)</td>
		<td width="5%">Customer&nbsp;type</td>
		<td width="5%">Sub-type</td>
		<td width="8%">Pricing</td>
		<td width="8%">Family</td>
	</tr>
	</thead>

	{foreach from=$erp_actypes item=actype}
	<tr
		{if $actype->sync_stat==$smarty.const.ERP_SYNC_STAT_KS_NEW} style="color: blue;"
		{elseif $actype->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_NEW} style="color: orange;"
		{elseif $actype->sync_stat==$smarty.const.ERP_SYNC_STAT_MODIFIED} style="color: green;"
		{elseif $actype->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_INCOMPLETE} style="color: red;"
		{/if}
	>
		<td>
			{if $actype->action_type_id}{$actype->action_type_id}
			{else}--
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $actype->erp_id}{$actype->erp_id|escape}
			{else}--
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $actype->erp_code}{$actype->erp_code|escape}
			{else}--
			{/if}
		</td>
		<td>
			{if $actype->action_type_id}{$actype->action_type->name|escape}
			{else}--
			{/if}
		</td>
		<td>
			{if $actype->erp_name}{$actype->erp_name|escape}
			{else}--
			{/if}
		</td>
		<td>
			{if $actype->category}
				{assign var="category_id" value=$actype->category}
				{$actypes_categories_list.$category_id}
			{else}
				{if $actype->s_id_ssfam}[unknown code: "{$actype->s_id_ssfam}"]
				{else}--
				{/if}
			
			
			{/if}
		</td>
		
		<td nowrap="nowrap">
			{if $actype->contract_types}
				{assign var="contract_type_id" value=$actype->contract_types}
				{$CONTRACT_TYPES.$contract_type_id}
			{else}
				{if $actype->s_cat2}[unknown code: "{$actype->s_cat2}"]
				{else}--
				{/if}
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $actype->contract_sub_type}
				{assign var="contract_sub_type" value=$actype->contract_sub_type}
				{$CUST_SUBTYPES.$contract_sub_type}
			{else}
				{if $actype->s_cat2}[unknown code: "{$actype->s_cat2}"]
				{else}--
				{/if}
			{/if}
		</td>
		<td nowrap="nowrap">
			<!-- Price type: hourly/fixed -->
			{if $actype->price_type}
				{assign var="price_type" value=$actype->price_type}
				{$PRICE_TYPES.$price_type}
			{else}
				{if $actype->s_cat3}[unknown code: "{$actype->s_cat3}"]
				{else}--
				{/if}
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $actype->family}
				{$actype->family|escape}
			{else}
				--
			{/if}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text" colspan="9">[No records]</td>
	</tr>
	{/foreach}

</table>
<p/>
Total action types: {$erp_actypes|@count}
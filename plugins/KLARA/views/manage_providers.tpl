{assign var="paging_titles" value="KLARA, Manage Internet Providers"}
{assign var="paging_urls" value="/?cl=klara"}
{include file="paging.html"}

<h1>Manage Internet Providers</h1>

<p class="error">{$error_msg}</p>

<p><a href="/?cl=klara&amp;op=provider_add">Add Internet Provider &#0187;</a></p>

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td width="20%">Name</td>
		<td width="20%">Contracts</td>
		<td width="30%">Contacts</td>
		<td width="20%">Customers</td>
		<td width="10%"> </td>
	</tr>
	</thead>

	{foreach from=$providers item=provider}
		<tr>
			<td><a href="/?cl=klara&amp;op=provider_edit&amp;id={$provider->id}">{$provider->id}</a></td>
			<td><a href="/?cl=klara&amp;op=provider_edit&amp;id={$provider->id}">{$provider->name}</a></td>
			<td>
				{foreach from=$provider->contracts item=contract}
					{$contract->name}<br/>
				{foreachelse}
					<font class="light_text">[no contracts defined]</font>
				{/foreach}
			</td>
			
			<td>
				{foreach from=$provider->contacts item=contact}
					{$contact->fname} {$contact->lname}:<br/>
					{foreach from=$contact->phones item=phone}
						&nbsp;-&nbsp;{$phone->phone} 
						{if $phone->type}
							{assign var="phone_type" value=$phone->type}
							({$PHONE_TYPES.$phone_type})
						{/if}
						{if $phone->comments}
							<br/>&nbsp;&nbsp;&nbsp;
							<i>{$phone->comments}</i>
						{/if}
						<br/>
					{/foreach}
				{foreachelse}
					<font class="light_text">--</font>
				{/foreach}
			</td>
			
			<td>
				{assign var="customers_count" value=$provider->get_customers_count()}
				{if $customers_count}
					<a href="/?cl=klara&amp;op=provider_customers&amp;id={$provider->id}"
					>{$customers_count}:&nbsp;view&nbsp;list&nbsp;&#0187;</a>
				{else}
					<font class="light_text">--</font>
				{/if}
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=klara&amp;op=provider_delete&amp;id={$provider->id}"
					onclick="return confirm('Are you sure you want to delete this provider?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	
	{foreachelse}
		<tr>
			<td colspan="6">[No Internet Providers defined yet]</td>
		</tr>
	{/foreach}
	
</table>

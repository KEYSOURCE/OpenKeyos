{assign var="paging_titles" value="KLARA, Manage Internet Providers, Edit Internet Provider"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_providers"}
{include file="paging.html"}

<h1>Edit Internet Provider</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="add_frm">
{$form_redir}

<table width="98%" class="list">
	<thead>
	<tr>
		<td colspan="2">Enter Internet Provider information</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Name:</td>
		<td>
			<input type="text" name="provider[name]" value="{$provider->name}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>Website:</td>
		<td>
			<input type="text" name="provider[website]" value="{$provider->website}" size="40"/>
			{if $provider->website}
			<a href="{$provider->website}">Open website &#0187;</a>
			{/if}
		</td>
	</tr>
	<tr>
		<td>Address:</td>
		<td>
			<textarea name="provider[address]" rows="5" cols="40">{$provider->address|escape}</textarea>
		</td>
	</tr>
		
</table>
<p/>

<input type="submit" name="save" value="Save"/>
<input type="submit" name="cancel" value="Close"/>
</form>


<h2>Contacts &nbsp;|&nbsp;
<a href="/?cl=klara&amp;op=provider_contact_add&amp;provider_id={$provider->id}&amp;returl={$returl}">Add contact &#0187;</a></h2>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="20%">Name</td>
		<td width="20%">E-mail</td>
		<td width="20%">Phones</td>
		<td width="20%">Comments</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$provider->contacts item=contact}
		<tr>
			<td><a href="/?cl=klara&amp;op=provider_contact_edit&amp;id={$contact->id}&amp;returl={$returl}">{$contact->id}</a></td>
			<td><a href="/?cl=klara&amp;op=provider_contact_edit&amp;id={$contact->id}&amp;returl={$returl}">{$contact->fname} {$contact->lname}</a></td>
			<td>
				{if $contact->email}
				<a href="mailto:{$contact->email}">{$contact->email}</a>
				{/if}
			</td>
			<td>
				{foreach from=$contact->phones item=phone}
					{assign var="phone_type" value=$phone->type}
					{$phone->phone} ({$PHONE_TYPES.$phone_type})<br/>
					
					{if $phone->comments}
						<i>&nbsp;&nbsp;&nbsp;{$phone->comments|escape}</i><br/>
					{/if}
				{/foreach}
			</td>
			<td>{$contact->comments|escape|nl2br}</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=klara&amp;op=provider_contact_delete&amp;id={$contact->id}&amp;returl={$returl}"
					onclick="return confirm('Are you really sure you want to delete this contact?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="5">[No contacts defined]</td>
		</tr>
	{/foreach}
	
</table>


<h2>Contracts Offered &nbsp;|&nbsp;
<a href="/?cl=klara&amp;op=provider_contract_add&amp;provider_id={$provider->id}&amp;returl={$returl}">Add contract &#0187;</a></h2>

<table class="list" width="70%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td width="30%">Name</td>
		<td width="60%">Comments</td>
		<td width="10%"> </td>
	</thead>
	</tr>
	
	{foreach from=$provider->contracts item=contract}
		<tr>
			<td><a href="/?cl=klara&amp;op=provider_contract_edit&amp;id={$contract->id}&amp;returl={$returl}">{$contract->id}</a></td>
			<td><a href="/?cl=klara&amp;op=provider_contract_edit&amp;id={$contract->id}&amp;returl={$returl}">{$contract->name}</a></td>
			<td>{$contract->comments|escape|nl2br}</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=klara&amp;op=provider_contract_delete&amp;id={$contract->id}&amp;returl={$returl}"
					onclick="return confirm('Are you sure you want to delete this contract?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="4">[No contracts defined yet]</td>
		</tr>
	{/foreach}
	
</table>
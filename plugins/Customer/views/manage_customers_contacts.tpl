{assign var="paging_titles" value="Customers, Manage Customers Contacts"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}


<h1>Manage Customers Contacts</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="manage_contacts">
{$form_redir}

<table width="98%">
	<tr>
		<td width="50%">
			Customer:
			<select name="filter[customer_id]"
				onChange="document.forms['manage_contacts'].elements['do_filter_hidden'].value=1; document.forms['manage_contacts'].submit();"
			>
				<option value="">[Select]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
		<td width="50%" align="right">
			{if $filter.customer_id}
				<a href="/?cl=customer&op=customer_contact_add&customer_id={$filter.customer_id}&returl={$returl}">Add contact &#0187;</a>
			{/if}
		</td>
</table>
</form>
<p/> 

<table width="98%" class="list">
	<thead>
	<tr>
		<td>Name / Comments</td>
		<td>E-mail</td>
		<td>Position</td>
		<td>Phones</td>
		<td> </td>
	</tr>
	</thead>
	
	{foreach from=$contacts item=contact}
		<tr>
			<td>
				<a href="/?cl=customer&op=customer_contact_edit&id={$contact->id}&returl={$returl}">{$contact->fname} {$contact->lname}</a>
				{if $contact->comments}
					<br/>
					{$contact->comments|escape|nl2br}
				{/if}
			</td>
			<td>{$contact->email}</td>
			<td>{$contact->position}</td>
			<td>
				{foreach from=$contact->phones item=phone}
					{assign var="type" value=$phone->type}
					<b>{$phone->phone}</b> ({$PHONE_TYPES.$type})
					{if $phone->comments}
						<br/>
						-&nbsp;{$phone->comments|escape}
					{/if}
					<br/>
				{/foreach}
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=customer&op=customer_contact_delete&id={$contact->id}&returl={$returl}"
					onClick="return confirm ('Are you really sure you want to delete this contact?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="5">[No contacts defined yet]</td>
		</tr>
	{/foreach}

</table>


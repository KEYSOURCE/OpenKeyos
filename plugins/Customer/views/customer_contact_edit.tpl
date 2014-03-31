{assign var="customer_id" value=$customer->id}
{assign var="paging_titles" value="Customers, Manage Customers, Edit Customer, Edit Contact"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer, /?cl=customer&op=customer_edit&id=$customer_id"}
{include file="paging.html"}

<h1>Edit Contact</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="20%">Customer</td>
		<td>{$customer->name} ({$customer->id})</td>
	</tr>
	</thead>
	
	<tr>
		<td>First name: </td>
		<td><input type="text" name="contact[fname]" value="{$contact->fname}" size="30"/></td>
	</tr>
	<tr>
		<td>Last name: </td>
		<td><input type="text" name="contact[lname]" value="{$contact->lname}" size="30"/></td>
	</tr>
	<tr>
		<td>E-mail: </td>
		<td><input type="text" name="contact[email]" value="{$contact->email}" size="30"/></td>
	</tr>
	<tr>
		<td>Position/Function: </td>
		<td><input type="text" name="contact[position]" value="{$contact->position}" size="30"/></td>
	</tr>
	<tr>
		<td>Comments: </td>
		<td>
			<textarea name="contact[comments]" rows="4" cols="60">{$contact->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>
<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>

<p/>

<h2>Phone numbers</h2>


<p><a href="/?cl=customer&op=customer_contact_phone_add&contact_id={$contact->id}&returl={$returl}">Add phone number &#0187;</a></p>

<table class="list" width="60%">
	<thead>
	<tr>
		<td>Phone</td>
		<td>Type</td>
		<td>Comments</td>
		<td> </td>
	</tr>
	</thead>
	
	{foreach from=$contact->phones item=phone} 
		<tr>
			<td><a href="/?cl=customer&op=customer_contact_phone_edit&id={$phone->id}&returl={$returl}">{$phone->phone}</a></td>
			<td>
				{assign var="phone_type" value=$phone->type}
				{$PHONE_TYPES.$phone_type}
			</td>
			<td>{$phone->comments|escape}</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=customer&op=customer_contact_phone_delete&id={$phone->id}&returl={$returl}"
					onClick="return confirm('Are you sure you want to delete this phone number?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="4">[No phone numbers defined yet]</td>
		</tr>
	{/foreach}
	
</table>
<p/>

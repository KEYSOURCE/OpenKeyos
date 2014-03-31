{assign var="provider_id" value=$provider->id}
{assign var="paging_titles" value="KLARA, Manage Internet Providers, Edit Internet Provider, Edit Contact"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_providers, /?cl=klara&op=provider_edit&id=$provider_id"}
{include file="paging.html"}

<h1>Edit Contact</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="add_frm">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td width="20%">Provider:</td>
		<td>{$provider->name}</td>
	</tr>
	</thead>
	
	<tr>
		<td>First name/Name:</td>
		<td>
			<input type="text" name="contact[fname]" value="{$contact->fname}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>Last name:</td>
		<td>
			<input type="text" name="contact[lname]" value="{$contact->lname}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>E-mail:</td>
		<td>
			<input type="text" name="contact[email]" value="{$contact->email}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="contact[comments]" rows="5" cols="40">{$contact->comments|escape}</textarea>
		</td>
	</tr>
		
</table>
<p/>
<input type="submit" name="save" value="Save"/>
<input type="submit" name="cancel" value="Close"/>
</form>

<h2>Phone numbers</h2>

<p><a href="/?cl=klara&amp;op=provider_contact_phone_add&amp;contact_id={$contact->id}&amp;returl={$ret_url}">Add phone &#0187;</a></p>

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
			<td><a href="/?cl=klara&amp;op=provider_contact_phone_edit&amp;id={$phone->id}&amp;returl={$ret_url}">{$phone->phone}</a></td>
			<td>
				{assign var="phone_type" value=$phone->type}
				{$PHONE_TYPES.$phone_type}
			</td>
			<td>{$phone->comments|escape}</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=klara&amp;op=provider_contact_phone_delete&id={$phone->id}&amp;returl={$ret_url}"
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
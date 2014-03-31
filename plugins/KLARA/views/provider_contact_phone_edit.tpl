{assign var="provider_id" value=$provider->id}
{assign var="contact_id" value=$contact->id}
{assign var="paging_titles" value="KLARA, Manage Internet Providers, Edit Internet Provider, Edit Phone Number"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_providers, /?cl=klara&op=provider_edit&id=$provider_id"}

{include file="paging.html"}

<h1>Edit Phone Number</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="20%">Contact:</td>
		<td>{$contact->fname} {$contact->lname} - {$provider->name}</td>
	</tr>
	</thead>
	
	<tr>
		<td>Phone number: </td>
		<td><input type="text" name="phone[phone]" value="{$phone->phone}" size="20"/></td>
	</tr>
	<tr>
		<td>Type: </td>
		<td>
			<select name="phone[type]">
				<option value="">[Select]</option>
				{html_options options=$PHONE_TYPES selected=$phone->type}
			</select>
		</td>
	</tr>
	<tr>
		<td>Comments: </td>
		<td><input type="text" name="phone[comments]" value="{$phone->comments}" size="50"/></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>
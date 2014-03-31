{assign var="customer_id" value=$customer->id}
{assign var="contact_id" value=$contact->id}
{assign var="paging_titles" value="Customers, Manage Customers, Edit Customer, Edit Contact, Add Phone Number"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer, /?cl=customer&op=customer_edit&id=$customer_id, /?cl=customer&op=customer_contact_edit&id=$contact_id"}
{include file="paging.html"}

<h1>Add Phone Number</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="20%">Contact:</td>
		<td>{$contact->fname} {$contact->lname} - {$customer->name} ({$customer->id})</td>
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
<input type="submit" name="cancel" value="Cancel" />

</form>

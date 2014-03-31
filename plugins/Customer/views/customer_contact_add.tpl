{assign var="customer_id" value=$customer->id}
{assign var="paging_titles" value="Customers, Manage Customers, Edit Customer, Add Contact"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer, /?cl=customer&op=customer_edit&id=$customer_id"}
{include file="paging.html"}

<h1>Add Contact</h1>

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
<input type="submit" name="cancel" value="Cancel" />

</form>

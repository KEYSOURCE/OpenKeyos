{assign var="customer_id" value=$customer->id}
{assign var="paging_titles" value="Customers, Manage Customers, Edit Customer, Edit Comment"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer, /?cl=customer&op=customer_edit&id=$customer_id"}
{include file="paging.html"}

<h1>Edit Comment</h1>

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
		<td>Subject: </td>
		<td><input type="text" name="comment[subject]" value="{$comment->subject}" size="30"/></td>
	</tr>
	<tr>
		<td>Comments: </td>
		<td>
			<textarea name="comment[comments]" rows="8" cols="60">{$comment->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>

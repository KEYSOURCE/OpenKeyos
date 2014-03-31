{assign var="paging_titles" value="Customers, Lock Customer"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}


<h1>Lock Customer</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="frm">
{$form_redir}

Customer:<br>
<select name="customer_id"">
	<option value="">[Select]</option>
	{html_options options=$customers_list selected=$preselect_id}
</select>

<p>
<input type="submit" name="lock" value="Lock">
<input type="submit" name="cancel" value="Cancel">

</form>

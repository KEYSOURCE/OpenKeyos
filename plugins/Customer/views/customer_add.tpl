{assign var="paging_titles" value="Clients, Manage Customers, Add Customer"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<h1>Add Customer</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Customer information</td>
	</tr>
	</thead>

	<tr>
		<td>Name: </td>
		<td><input type="text" name="customer[name]" value="{$customer->name}" size="30"></td>
	</tr>

	<tr class="head">
		<td colspan="2">Services</td>
	</tr>

	<tr>
		<td>Kawacs: </td>
		<td>
			<select name="customer[has_kawacs]">
				<option value="0">No</option>
				<option value="1" {if $customer->has_kawacs}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Krifs: </td>
		<td>
			<select name="customer[has_krifs]">
				<option value="0">No</option>
				<option value="1" {if $customer->has_krifs}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>SLA time: </td>
		<td><input type="text" name="customer[sla_hours]" value="{$customer->sla_hours}" size="6"> hours</td>
	</tr>
	<tr>
		<td>Account Manager: </td>
		<td>
			<select name="customer[account_manager]">
				{html_options options=$ACCOUNT_MANAGERS selected=$DEFAULT_ACCOUNT_MANAGER}
			</select>
		</td>
	</tr>
</table>

<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel">

</form>

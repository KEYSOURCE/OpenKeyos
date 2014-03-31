{assign var="paging_titles" value="KAMS, Manage Contract Types"}
{assign var="paging_urls" 	value="/?cl=kams, /?cl=kams&op=manage_contract_types"}
{include file="paging.html"}

<h1>
	Edit contract type: {$contract_type->name} (#{$contract_type->id})
</h1>
<p>
	<font class="error">{$error_msg}</font>
</p>

{literal}
	<script language="JavaScript" type="text/javascript">
	//<![CDATA[
	//]]>
	</script>
{/literal}
<form method="POST" name="contract_type_frm">
{$form_redir}
<table class="list" width="95%">
	<thead>
		<tr>
			<td colspan="2">Contract data</td>
		</tr>
	</thead>
	<tr>
		<td width="20%">Name</td>
		<td width="80%">
			<input type="text" name="contract_type[name]" value="{$contract_type->name}">
		</td>
	</tr>
	<tr>
		<td width="20%">Description</td>
		<td width="80%">
			<textarea name="contract_type[description]" rows="4" cols="60">{$contract_type->description}</textarea>
		</td>
	</tr>
	<tr><td colspan="2"></td></tr>
	<thead>
	<tr>
		<td colspan="2">Properties</td>
	</tr>
	</thead>
	<tr>
		<td width="20%">Has quantity</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[quantity]" value="1" {if $contract_type->quantity}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">Has total price</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[total_price]" value="1" {if $contract_type->total_price}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">Has recurring payments</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[recurring_payments]" value="1" {if $contract_type->recurring_payments}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">Has expiration date</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[end_date]" value="1" {if $contract_type->end_date}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">Has vendor</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[vendor]" value="1" {if $contract_type->vendor}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">Has supplier</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[supplier]" value="1" {if $contract_type->supplier}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">Represents a warranty contract</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[is_warranty_contract]" value="1" {if $contract_type->is_warranty_contract}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">Send notifications</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[send_period_notifs]" value="1" {if $contract_type->send_period_notifs}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">Send notification at the expiration of contract</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[send_expiration_notifs]" value="1" {if $contract_type->send_expiration_notifs}checked="checked"{/if} /></td>
	</tr>
	<tr>
		<td width="20%">This contract supports renewals</td>
		<td width="80%"><input type="checkbox" class="checkbox" name="contract_type[supports_renewals]" value="1" {if $contract_type->supports_renewals}checked="checked"{/if} /></td>
	</tr>
</table>
<p></p>
<input type="submit" name="save" value="Save">
<input type="submit" name="delete" value="Delete this contract type">
<input type="submit" name="cancel" value="Close">
</form>
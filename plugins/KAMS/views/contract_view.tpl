{assign var="paging_titles" value="KAMS, Manage Contracts, Add new contract"}
{assign var="paging_urls" value=$customer->id|string_format:"/?cl=kams, /?cl=kams&op=manage_contracts&customer_id=%d"}
{include file="paging.html"}

<h1>
	Edit contract: {$contract->contract_number} (#{$contract->id})
</h1>
<p>
	<font class="error">{$error_msg}</font>
</p>
<script language="JavaScript" src="/javascript/ajax_kams.js"></script>
<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
{literal}

//<![CDATA[

	function changeContractType()
	{
		var type_id = document.getElementById('contract[contract_type]');
		
		changeTypeOfContract(type_id.value);		
	}
	function change_currency_symbol()
	{
		var currency_id = document.getElementById('contract[currency]');
		contractsChangeCurrency(currency_id.value)
	}
//]]>
{/literal}
</script>

<form method="POST" name="contract_add_frm">
{$form_redir}
	<table class="list" width="95%">
		<thead>
			<tr>
				<td style="width: 20%; text-align: left;">Customer:</td>
				<td style="width: 80%; text-align: left;" colspan="2">{$customer->name}
				<input type="hidden" name="contract[customer_id]" value="{$customer->id}">
				<input type="hidden" name="contract[id]" value="{$contract->id}">
				</td>
			</tr>
			<tr>
				<td colspan="3">Generic data</td>
			</tr>
		</thead>
		<tr>
			<td width="20%">Contract name</td>
			<td width="70%">
				<input type="text" size="60" name="contract[name]" value="{$contract->name}" />
			</td>
			<td width="10%">&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Contract number</td>
			<td width="70%">
				<input type="text" name="contract[contract_number]" value="{$contract->contract_number}" />
			</td>
			<td width="10%">&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Contract type</td>
			<td width="70%">
				<select name="contract[contract_type]" id="contract[contract_type]" onchange="changeContractType()">
					<option value="0">[Select a contract type]</option> 
					{html_options options=$contract_types selected=$contract->contract_type}
				</select>
			</td>
			<td width="10%">&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Notes</td>
			<td width="80%" colspan="2">
				<textarea name="contract[notes]" rows="4" cols="60">{$contract->notes}</textarea>
			</td>
		</tr>
		<thead>
			<tr>
				<td colspan="3">Specific data (based on the contract type)</td>
			</tr>
		</thead>
		<!-- on the select item for the contract types we must have an ajax call lso we can get the properities for each type -->
		<tr>
				<!-- display this item only if the type of contract has the "quantity flag set" -->
				<td width="20%">Start date</td>
				<td width="70%">
					<input type="text" name="contract[start_date]" value="{$contract->start_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}">
			
			{literal}
			<a HREF="#" onClick="showCalendarSelector('contract_add_frm', 'contract[start_date]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="./images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
				</td>
				<td width="10%">&nbsp;	
				</td>
		</tr>
	</table>
	<div id="currency" style="{if $type_contract->total_price || $type_contract->recurring_payments}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Currency</td>
					<td width="70%">
						<select id="contract[currency]" name="contract[currency]" onchange="change_currency_symbol()">
						{html_options options=$currencies selected=$contract->currency}
						</select>
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	{assign var="cs" value=$contract->currency}
	<div id="qty" style="{if $type_contract->quantity}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Quantity</td>
					<td width="70%">
						<input type="text" name="contract[quantity]" value="{$contract->quantity}" />
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	<div id="total_price" style="{if $type_contract->total_price}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Total price</td>
					<td width="70%">
						<input type="text" name="contract[total_price]" value="{$contract->total_price}" />
						<div style="display: inline;" id="currency_sy_tp">{if $currencies_symbols[$cs]}{$currencies_symbols[$cs]}{else}$currencies_symbols[1]{/if}</div>
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	<div id="recurring_payments" style="{if $type_contract->recurring_payments}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Payment periods</td>
					<td width="70%">
						<select name="contract[payment_periods]">
						<option value="0">[Select a payment period]</option>
						{html_options options=$payment_periods selected=$contract->payment_periods}
						</select>
					</td>
					<td width="10%">&nbsp;	
					</td>	
			</tr>
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Price per period</td>
					<td width="70%">
						<input type="text" name="contract[price_per_period]" value="{$contract->price_per_period}" />
						<div style="display: inline;" id="currency_sy_pp">{if $currencies_symbols[$cs]}{$currencies_symbols[$cs]}{else}$currencies_symbols[1]{/if}</div>
					</td>
					<td width="10%">&nbsp;	
					</td>	
			</tr>
			
		</table>
	</div>
	<div id="end_date" style="{if $type_contract->end_date}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Expiration date</td>
					<td width="70%">
						<input type="text" name="contract[end_date]" value="{$contract->end_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}">
			
			{literal}
			<a HREF="#" onClick="showCalendarSelector('contract_add_frm', 'contract[end_date]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="./images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	<div id="vendor" style="{if $type_contract->vendor}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Vendor</td>
					<td width="70%">
						<select name="contract[vendor]">
							<option value="0">[Select a vendor]</option>
							{html_options options=$suppliers selected=$contract->vendor}
						</select>
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	<div id="supplier" style="{if $type_contract->supplier}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Supplier</td>
					<td width="70%">
						<select name="contract[supplier]">
							<option value="0">[Select a supplier]</option>
							{html_options options=$suppliers selected=$contract->supplier}
						</select>
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	<div id="notice_period" style="{if $type_contract->send_period_notifs}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Notice period</td>
					<td width="70%">
						<input type="text" name="contract[notice_period]" value="{$contract->notice_period}" /> days
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	<div id="additional_notifiable" style="{if $type_contract->send_period_notifs || $type_contract->send_expiration_notifs}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Additional notifiable customer</td>
					<td width="70%">
						<select name="contract[additional_notifiable_customer_id]">
							<option value="">[Select customer]</option>
							{html_options options=$customers selected=$contract->additional_notifiable_customer_id}
						</select>
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	<div id="renewal_period" style="{if $type_contract->supports_renewals}display: block;{else}display: none;{/if}">
		<table width="95%" class = "list">	
			<tr>
					<!-- display this item only if the type of contract has the "quantity flag set" -->
					<td width="20%">Renewal period</td>
					<td width="70%">
						<input type="text" name="contract[renewal_period]" value="{$contract->renewal_period}" /> days
					</td>
					<td width="10%">&nbsp;	
					</td>
			</tr>
		</table>
	</div>
	
<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</p>
</form>
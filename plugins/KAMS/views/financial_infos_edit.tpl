{assign var="paging_titles" value="KAMS, Manage Assets, Edit Asset, Edit Financial Information"}
{assign var="first_title" value=$customer->id|string_format:"/?cl=kams, /?cl=kams&op=manage_assets&customer_id=%d,"}
{assign var="second_title" value=$asset->id|string_format:"/?cl=kams&op=asset_edit&id=%d"}
{assign var="paging_urls" value=$first_title|cat:$second_title}
{include file="paging.html"}
<script language="JavaScript" type="text/javascript" src="/javascript/ajax_kams.js"> </script>
<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
	//<![CDATA[
	{literal}
		function change_currency_symbol()
		{
			var sy = document.getElementById('currency').value;
			financialInfoCurrency(sy);
		}
		function setDateString (y,m,d)
		{
			fels = document.forms[frm_name].elements;
			for (i = 0; i < fels.length; i++)
			{
				if (fels[i].name == elname)
				{
					el = fels[i];
				}
			}
			if (m < 10) m = '0'+m;
			if (d < 10) d = '0'+d;
			el.value=d+"/"+m+"/"+y;
		}

	{/literal}
	//]]>
</script>


<h1>{$asset->name} - Financial Informations 
</h1>
<p>
	<font class="error">{$error_msg}</font>
</p>
<form action="" method="POST" name="frm_fin_infos">
{$form_redir}
<table class="list" width="95%">
	<thead>
		<tr>
			<td colspan="3">
			Customer: {$customer->name}
			</td> 
		</tr>
	</thead>
	<tr>
		<td width="20%">Invoice number</td>
		<td width="70%"><input type="text" name="fin_infos[invoice_number]" value="{$financial_info->invoice_number}" /></td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="20%">Invoice date</td>
		<td width="70%"><input type="text" size="12" name="fin_infos[invoice_date]" value="{$financial_info->invoice_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}">
			
			{literal}
			<a HREF="#" onClick="showCalendarSelector('frm_fin_infos', 'fin_infos[invoice_date]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="./images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
		</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="20%">Supplier</td>
		<td width="70%">
			<select name="fin_infos[supplier_id]">
			<option value="0">[Select a supplier]</option>
			{html_options options=$suppliers selected=$financial_info->supplier_id}
			</select>
		</td>
	</tr>
	<tr>
		<td width="20%">Currency</td>
		<td width="70%">
			<select id='currency' name="fin_infos[currency]" onchange="change_currency_symbol()">
				{html_options options=$currencies selected=$financial_info->currency}
			</select>
		</td>
	</tr>
	<tr>
		<td width="20%">Purchase value</td>
		<td	width="70%"><input type="text" class="currency_input" name="fin_infos[purchase_value]" value="{$financial_info->purchase_value}" />  <div style="display: inline;" id="currency_sy1">{$currency_symbol}</div></td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="20%">Write-off value</td>
		<td width="70%"><input type="text" class="currency_input" name="fin_infos[writeoff_value]" value="{$financial_info->writeoff_value}" />  <div style="display: inline;" id="currency_sy2">{$currency_symbol}</div></td>
	</tr>
	<tr>
		<td width="20%">Amortization period</td>
		<td width="70%"><input type="text" style="text-align: right; width: 80px;" name="fin_infos[amortization_period]" value="{$financial_info->amortization_period}" />  days</td>
		<td width="10%">&nbsp;</td> 
	</tr>
	

</table>
<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">

</form>
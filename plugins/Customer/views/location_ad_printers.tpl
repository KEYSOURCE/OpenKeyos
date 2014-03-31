{assign var="paging_titles" value="Customers, Manage Fixed Locations, Edit Customer Location, Assign AD Printers"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_locations"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function selectAllADPrinters ()
{
	frm = document.forms['frm_t']
	ad_printers_list = frm.elements['assigned_ad_printers[]']
	
	for (i=0; i<ad_printers_list.options.length; i++)
	{
		ad_printers_list.options[i].selected = true
	}
}

function addADPrinter ()
{
	frm = document.forms['frm_t']
	ad_printers_list = frm.elements['assigned_ad_printers[]']
	available_list = frm.elements['available_ad_printers']
	
	if (available_list.selectedIndex >= 0)
	{
		opt = new Option (available_list.options[available_list.selectedIndex].text, available_list.options[available_list.selectedIndex].value, false, false)
		
		ad_printers_list.options[ad_printers_list.options.length] = opt
		available_list.options[available_list.selectedIndex] = null
	}
}

function removeADPrinter ()
{
	frm = document.forms['frm_t']
	ad_printers_list = frm.elements['assigned_ad_printers[]']
	available_list = frm.elements['available_ad_printers']
	
	if (ad_printers_list.selectedIndex >= 0)
	{
		opt = new Option (ad_printers_list.options[ad_printers_list.selectedIndex].text, ad_printers_list.options[ad_printers_list.selectedIndex].value, false, false)
		
		available_list.options[available_list.options.length] = opt
		ad_printers_list.options[ad_printers_list.selectedIndex] = null
	}
}
{/literal}
//]]>
</script>


<h1>Customer Location : Assign AD Printers</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t" onSubmit="selectAllADPrinters(); return true;">
{$form_redir}


<table class="list" width="95%">
	<thead>
	<tr>
		<td width="15%">Customer:</td>
		<td width="85%" class="post_highlight">
			{assign var="customer_id" value=$location->customer_id}
			{$customers_list.$customer_id} (# {$customer_id})
		</td>
	</tr>
	<tr>
		<td>Location:</td>
		<td class="post_highlight">
			{foreach from=$location->parents item=parent}
				{$parent->name|escape} &#0187;
			{/foreach}
			{$location->name|escape}
		</td>
	</tr>
	</thead>
</table>
<p/>

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="50%">Assigned AD Printers</td>
		<td width="50%">Available AD Printers</td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="assigned_ad_printers[]" size="16" multiple="multiple" style="width: 250px"  ondblclick="removeADPrinter();">
				{foreach from=$location->ad_printers_list key=ad_printer_cn item=ad_printer_name}
				<option value="{$ad_printer_cn}">{$ad_printer_name}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<select name="available_ad_printers" size="16" multiple="multiple" style="width: 250px" ondblclick="addADPrinter();">
				{foreach from=$ad_printers_list key=ad_printer_cn item=ad_printer_name}
				<option value="{$ad_printer_cn}">{$ad_printer_name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />


</form>
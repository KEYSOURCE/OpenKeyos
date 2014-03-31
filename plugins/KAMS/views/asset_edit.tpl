{assign var="paging_titles" value="KAMS, Manage Assets, Edit Asset"}
{assign var="paging_urls" value=$customer->id|string_format:"/?cl=kams, /?cl=kams&op=manage_assets&customer_id=%d"}
{include file="paging.html"}
<script language="JavaScript" src="/javascript/ajax_kams.js" type="text/javascript"></script>
<h1>{$asset->name} #{$asset->id} 
{if $asset->is_managed} (
	{assign var="category" value=$asset->category}
		{$category->name}: #{$asset->associated_id})
	{/if} 
</h1>
<p>
	<font class="error">{$error_msg}</font>
</p>

{literal}
	<script language="JavaScript" type="text/javascript">
	//<![CDATA[
	function change_assoc_asset()
	{
		var asid = document.getElementById("assoc_id_sel").value;
		var category_name =  document.getElementById('cat_id_sel').value;
		var func = "";
		//alert('cat_name'+category_name);
		switch(category_name)
		{
			case '2': 
				func = "/?cl=kawacs&op=computer_view&id="+asid;
				break;
			case '3':
				func = "/?cl=kawacs&op=peripheral_edit&id="+asid;
				break;
			case '4':
					//var printer_id = xml.getElementsByTagName('id')[0].firstChild.nodeValue;
					var ppid = asid.split('_');
					for(i=0; i<ppid.length;i++)
					{	
						comp_id = ppid[0];
						pid = ppid[1];
						nrc = ppid[2];	
					}
				func = "/?cl=kerm&op=ad_printer_view&computer_id="+comp_id+'&nrc='+nrc;		
				break;
		}
		var dlink = document.getElementById("asset_view");
		dlink.href = func;
	} 
	
	function change_managed_state()
	{
		var type = document.getElementById('sel_asset_type').value;
		var categ = document.getElementById('cat_id_sel');
		var _assoc = document.getElementById("assoc_ids_display");
		if(type == 0)
		{
			categ.value = 1;
			_assoc.style.display = 'none';
		}
		else
		{
			categ.value = {/literal}{$asset->category_id}{literal};
			_assoc.style.display = 'block';
			assetsChangeCat('{/literal}{$asset->id}{literal}', 'cat_id_sel');
		}
	}
	//]]>
	</script>
{/literal}

<form action="" method="POST" name="asset_frm">
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
		<td width="20%">Asset type: </td>
		<td width="70%">
			<select id='sel_asset_type' name="asset[is_managed]" onchange="change_managed_state()">
				{html_options options=$asset_types selected=$asset->is_managed}
			</select>	
		</td>
		<td width="10%"> </td>
	</tr>
	<tr>
		<td width="20%">Name: </td>
		<td width="70%">
			<input type="text" name="asset[name]" value="{$asset->name}" />
		</td>
		<td width="10%"> </td>
	</tr>
	<tr>
		<td width="20%">Category: </td>
		<td width="70%">
			<select name="asset[category_id]" id="cat_id_sel" onchange="assetsChangeCat('{$asset->id}', 'cat_id_sel')">
				<!-- <option value="">[Select a category]</option> -->
				{html_options options=$categories selected=$asset->category_id}
			</select>
		</td>
		<td width="10%"></td>
	</tr>
	<tr>
		<td width="20%">Comment: </td>
		<td width="70%">
		<textarea name="asset[comments]" rows="4" cols="60">{$asset->comments}</textarea>
		</td>
		<td width="10%">&nbsp;</td>
	</tr>
</table>
	<div id="assoc_ids_display" style="display: {if $asset->is_managed}block{else}none{/if};">
	<table class="list" width="95%">
	<tr>
		<td id='assoc_name' width="20%">Associated {$category->name}: </td>
		<td width="70%">
			<select id="assoc_id_sel" name="asset[associated_id]" onchange="change_assoc_asset()">
				<!-- <option value="">[Select an associated item]</option> -->
				{html_options options=$associated_list selected=$asset->associated_id}
			</select>
		</td>
		<td width="10%">
			{if $category->name == 'Computer'}
				<a id="asset_view" href="/?cl=kawacs&op=computer_view&id={$asset->associated_id}">
					View &#0187;
				</a>
			{elseif $category->name == 'Peripheral'}
				<a id="asset_view" href="/?cl=kawacs&op=peripheral_edit&id={$asset->associated_id}">
					View &#0187;
				</a>
			{elseif $category->name == 'AD Printer'}
				<a id="asset_view" href="/?cl=kerm&op=ad_printer_view&computer_id={$comp_id}&nrc={$nrc}">
					View &#0187;
				</a>
			{else}
				<a id="asset_view" href="/?cl=kerm&op=computer_view&id={$asset->associated_id}">
					View &#0187;
				</a>
			{/if}  
			
		</td>
	</tr>
	</table>	
	</div>
<table class="list" width="95%">
<thead>
	<tr>
		<td colspan="3">Financial informations</td>
	</tr>
</thead>
<tr>
	<td width="20%"></td>
	<td width="70%"></td>
	<td width="10%"></td>
</tr>
</table>
{foreach from=$finInfos item=info}
<table class="list" width="95%">	
	<thead>
		<tr>
			<td width="5%">&nbsp;</td>
			<td colspan="2" width="95%">Invoice no. {$info->invoice_number}</td>
			<td width="10%">
				<a href="/?cl=kams&op=financial_infos_edit&asset_id={$asset->id}&fin_info_id={$info->id}">Edit&#0187;</a> |
				<a href="/?cl=kams&op=financial_infos_delete&asset_id={$asset->id}&fin_info_id={$info->id}" onClick="return confirm('Are you sure you want to delete this?');">Delete&#0187;</a>
			</td>
		</tr>
	</thead>
	<tr>
		<td width="5%">&nbsp;</td>
		<td width="20%">Invoice date</td>
		<td width="65%">{$info->invoice_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="5%">&nbsp;</td>
		<td width="20%">Purchase value</td>
		<td width="65%">{$info->get_currency_symbol()} {$info->purchase_value}</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="5%">&nbsp;</td>
		<td width="20%">Writeoff value</td>
		<td width="65%">{$info->get_currency_symbol()} {$info->writeoff_value}</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="5%">&nbsp;</td>
		<td width="20%">Amortization period</td>
		<td width="65%">{$info->amortization_period} days</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="5%">&nbsp;</td>
		<td width="20%">Supplier</td>
		<td width="65%">{$info->get_supplier_name()}</td>
		<td width="10%">&nbsp;</td>
	</tr>
</table>
{foreachelse}
<table class="list" width="95%">
	<tr>
		<td>
			[No financial informations added]
		</td>
	</tr>
</table>
{/foreach}
<p>
<p>
	<a href="/?cl=kams&op=financial_infos_add&asset_id={$asset->id}"> Add financial informations &#0187;</a>
</p>

<input type="submit" name="save" value="Save">
<input type="submit" name="delete" value="Delete asset">
<input type="submit" name="cancel" value="Close">


</form>
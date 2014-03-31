{assign var="paging_titles" value="KAMS, Manage Assets, Add Asset"}
{assign var="paging_urls" value=$customer->id|string_format:"/?cl=kams, /?cl=kams&op=manage_assets&customer_id=%d"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/ajax_kams.js"></script>
<script language="JavaScript" type="text/javascript">
{literal}
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
	function change_assoc_category()
	{
		var cat = document.getElementById('cat_id_sel').value;
		associatedAssetsChange('{/literal}{$customer->id}{literal}', cat);
		
	}
	function change_managed_state()
	{
		var type = document.getElementById('sel_asset_type').value;
		var assoc_ids_display = document.getElementById('assoc_ids_display');
		var asset_category = document.getElementById('cat_id_sel');
		if(type == 0)
		{
			assoc_ids_display.style.display = 'none';
			asset_category.value = 1
		}
		else
		{
			assoc_ids_display.style.display = 'block';
			asset_category.value = 2;
			change_assoc_category();
		}
	}
	//]]>
{/literal}
</script>
<h1>Add Asset</h1>
<p>
<font class="error">{$error_msg}</font>
<p>


<form action="" method="POST" name="asset_frm">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="3">Customer: {$customer->name}</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Asset type: </td>
		<td width="70%">
			<select name="asset[is_managed]" id="sel_asset_type" onchange="change_managed_state()">
			{html_options options=$asset_types selected=0}
			</select>
		</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="20%">Name: </td>
		<td width="70%">
			<input type="text" asset="asset_name" name="asset[name]" value="" />
		</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="20%">Category: </td>
		<td width="70%">
			<select name="asset[category_id]" id="cat_id_sel" onchange="change_assoc_category()">
				<!-- <option value="" selected="selected">[Select a category]</option> -->
				{html_options options=$categories selected=0}
			</select>
		</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td width="20%">Comments: </td>
		<td width="70%"><textarea name="asset[comments]" id="asset_comments" rows="4" cols="60"></textarea></td>
		<td width="10%">&nbsp;</td>
	</tr>
</table>
<div id="assoc_ids_display" style="display: none;">
<table class="list" width="95%">
	<tr>
		<td id='assoc_name' width="20%">Associated item: </td>
		<td width="70%">
			<select id="assoc_id_sel" name="asset[associated_id]" onchange="change_assoc_asset()">
				<!-- <option value="">[Select an associated item]</option> -->
				{html_options options=$associated_list selected=$asset->associated_id}
			</select>
		</td>
		<td width="10%">
			<a id="asset_view" href="">
					View &#0187;
			</a>
		</td>
	</tr>
</table>	
</div>

<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</p>
</form>
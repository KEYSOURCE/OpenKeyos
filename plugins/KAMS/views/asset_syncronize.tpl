{assign var="paging_titles" value="KAMS, Syncronize"}
{assign var="paging_urls" value="/?cl=kams"}
{include file="paging.html"}
<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
	function selectAllSync()
	{
		frm = document.forms['filter']
		sync_list = frm.elements['categories[sync][]']
		
		for (i=0; i<sync_list.options.length; i++)
		{
			sync_list.options[i].selected = true
		}
	}
	function removeCategory()
	{
		frm = document.forms['filter'];
		sync_list = frm.elements["categories[sync][]"];
		cat_list = frm.elements["categories[available][]"];
		
		if (sync_list.selectedIndex >= 0)
		{
			opt = new Option(sync_list.options[sync_list.selectedIndex].text, sync_list.options[sync_list.selectedIndex].value, false, false)
			
			cat_list.options[cat_list.options.length] = opt
			sync_list.options[sync_list.selectedIndex] = null
		}
	}
	function addCategory()
	{
		frm = document.forms['filter'];
		sync_list = frm.elements["categories[sync][]"];
		cat_list = frm.elements["categories[available][]"];
		
		if (cat_list.selectedIndex >= 0)
		{
			opt = new Option (cat_list.options[cat_list.selectedIndex].text, cat_list.options[cat_list.selectedIndex].value, false, false)
			
			sync_list.options[sync_list.options.length] = opt
			cat_list.options[cat_list.selectedIndex] = null
		}
	}
{/literal}
//]]>
</script>
<h1> Syncronize assets
{if $customer->id} : {$customer->name} ({$customer->id})
{/if}
</h1>

<p>
<font class="error">{$error_msg}</font>
<p>

<form name='filter' method="POST" action="" onsubmit="selectAllSync(); return true;">
{$form_redir}

<b>Customer: </b>
<select name="customer_id" onChange="document.forms['filter'].submit()">
	<option value="">[Select customer]</option>
	{foreach from=$customers item=cust key=id}
		<option value="{$id}" {if $customer->id==$id}selected{/if}>
			{$cust} {if $id!=' '}({$id}){/if}
		</option>
	{/foreach}
</select>
<p>



{if $customer->id}
<table class="list" width="95%">
	<thead>
		<tr>
			<td colspan="3">Members:</td>
		</tr>
	</thead>
	<tr>
		<td width="40%" align="center">
			Current categories:
			<br>
			
			<select name="categories[sync][]" size=16 style="width: 200px;" multiple onDblClick="removeCategory();">
				{html_options options=$categories.sync}
			</select>
		</td>
		<td>&nbsp;</td>
		<td width="40%" align="center">
			Available categories:
			<br>
			
			<select name="categories[available][]" size=16  style="width: 200px;" multiple onDblClick="addCategory();">
				{html_options options=$categories.available}
			</select>
		
		</td>
	
	</tr>
</table>
<p>
<input type="submit" name="syncronize" value="Syncronize">
<input type="submit" name="cancel" value="Close">


{/if}
</form>
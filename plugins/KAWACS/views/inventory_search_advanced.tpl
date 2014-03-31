{assign var="paging_titles" value="KAWACS, KAWACS Inventory Dashboard Advanced"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}
<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
	function selectAllSync()
	{
		frm = document.forms['advset']
		{/literal}
		{if $filter.itype == 0}
		{literal}
		sync_list = frm.elements['filter[current_computer_items][]'];
		{/literal}
		{elseif $filter.itype == 1}
		{literal}
		sync_list = frm.elements['filter[current_peripheral_class][]'];
		{/literal}
		{elseif $filter.itype == 2}
		{literal}
		sync_list = frm.elements['filter[current_adcomputer][]'];
		{/literal}{/if}{literal}
		
		for (i=0; i<sync_list.options.length; i++)
		{
			sync_list.options[i].selected = true
		}
	}
	function removeCategory()
	{
		frm = document.forms['advset'];
		
		{/literal}
		{if $filter.itype == 0}
		{literal}
		sync_list = frm.elements['filter[current_computer_items][]'];
		{/literal}
		{elseif $filter.itype == 1}
		{literal}
		sync_list = frm.elements['filter[current_peripheral_class][]'];
		{/literal}
		{elseif $filter.itype == 2}
		{literal}
		sync_list = frm.elements['filter[current_adcomputer][]'];
		{/literal}{/if}{literal}
		
		cat_list = frm.elements["filter[available_computer_items][]"];
		
		if (sync_list.selectedIndex >= 0)
		{
			opt = new Option(sync_list.options[sync_list.selectedIndex].text, sync_list.options[sync_list.selectedIndex].value, false, false)
			
			cat_list.options[cat_list.options.length] = opt
			sync_list.options[sync_list.selectedIndex] = null
		}
	}
	function addCategory()
	{
		frm = document.forms['advset'];
		{/literal}
		{if $filter.itype == 0}
		{literal}
		sync_list = frm.elements['filter[current_computer_items][]'];
		{/literal}
		{elseif $filter.itype == 1}
		{literal}
		sync_list = frm.elements['filter[current_peripheral_class][]'];
		{/literal}
		{elseif $filter.itype == 2}
		{literal}
		sync_list = frm.elements['filter[current_adcomputer][]'];
		{/literal}{/if}{literal}
		cat_list = frm.elements["filter[available_computer_items][]"];
		
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
<h1> Advanced search settings
</h1>

<p>
<font class="error">{$error_msg}</font>
<p>

<form name='advset' method="POST" action="" onsubmit="selectAllSync(); return true;">
{$form_redir}
{foreach  item=flt from=$filter key=fk}
	{if $fk != 'current_computer_items' and $fk != 'current_peripheral_class'}
		<input type="hidden" name="filter[{$fk}]" value="{$filter.$fk}">
	{/if}
{/foreach}
<table class="list" width="95%">
	<thead>
		<tr>
			<td colspan="3">Select search categories:</td>
		</tr>
	</thead>
	<tr>
		<td width="40%" align="center">
			Current categories:
			<br>
			{if $filter.itype == 0}
			<select name="filter[current_computer_items][]" size=16 style="width: 200px;" multiple onDblClick="removeCategory();">
				{html_options options=$mitems.current}
			</select>
			{elseif $filter.itype == 1}
			<select name="filter[current_peripheral_class][]" size=16 style="width: 200px;" multiple onDblClick="removeCategory();">
				{html_options options=$mitems.current}
			</select>
			{elseif $filter.itype == 2}
			<select name="filter[current_adcomputers][]" size=16 style="width: 200px;" multiple onDblClick="removeCategory();">
				{html_options options=$mitems.current}
			</select>
			{/if}
		</td>
		<td>&nbsp;</td>
		<td width="40%" align="center">
			Available categories:
			<br>
			
			<select name="filter[available_computer_items][]" size=16  style="width: 200px;" multiple onDblClick="addCategory();">
				{html_options options=$mitems.available}
			</select>
		</td>
	
	</tr>
</table>
<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">

</form>
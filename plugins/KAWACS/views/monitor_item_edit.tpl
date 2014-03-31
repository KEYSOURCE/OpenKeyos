{assign var="paging_titles" value="KAWACS, Manage monitor items, Edit item "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_monitor_items"}
{include file="paging.html"}


<script language="JavaScript" type="text/javascript" src="/javascript/ajax.js"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

var type_memory = {$smarty.const.MONITOR_TYPE_MEMORY};
var type_value_list = {$smarty.const.MONITOR_TYPE_LIST};

var list_ids = new Array ();
cnt = 0;
{foreach from=$AVAILABLE_ITEMS_LISTS key=list_id item=list_values}
	list_ids[cnt++] = {$list_id};
{/foreach}

{literal}

function checkTypeChange ()
{
	var frm = document.forms['frm_t'];
	var elm_types = frm.elements['item[type]'];
	var selected_type = elm_types.options[elm_types.selectedIndex].value;
	
	var elm_row = document.getElementById ('treshold_row');
	var elm_treshold = frm.elements['item[treshold]'];
	var elm_row_lists = document.getElementById ('lists_row');
	
	if (selected_type == type_memory)
	{
		elm_row.style.display = '';
		elm_row_lists.style.display = 'none';
	}
	else if (selected_type == type_value_list)
	{
		elm_row.style.display = 'none';
		elm_treshold.value = '0';
		elm_row_lists.style.display = '';
	}
	else
	{
		elm_row.style.display = 'none';
		elm_row_lists.style.display = 'none';
		elm_treshold.value = '0';
	}
}

function checkListTypeChange ()
{
	var frm = document.forms['frm_t'];
	var elm_types = frm.elements['item[type]'];
	var selected_type = elm_types.options[elm_types.selectedIndex].value;
	
	if (selected_type == type_value_list)
	{
		var elm_lists = frm.elements['item[list_type]'];
		var selected_list_type = elm_lists.options[elm_lists.selectedIndex].value;
		
		for (var i=0; i<list_ids.length; i++)
		{
			var elm_values = document.getElementById ('div_list_values_' + list_ids[i]);
			elm_values.style.display = 'none';
		}
		
		if (selected_list_type != '')
		{
			var elm_values = document.getElementById ('div_list_values_' + selected_list_type);
			elm_values.style.display = '';
		}
	}
}

/** Displays the pop-up window for selecting an OID from the available MIBs */
var popup_window = false;
function showOidsPopup ()
{
	var frm = document.forms['frm_t'];
	var c_oid_id = frm.elements['item[snmp_oid_id]'].value;
	
	if (popup_window) popup_window.close ();
	
	var url = '/snmp/popup_oids';
	if (c_oid_id != '') url = url + '?oid_id=' + c_oid_id + '#a' + c_oid_id;
	
	popup_window = window.open (url, 'Select_OIDs', 'dependent, scrollbars=yes, resizable=yes, width=100, height=100');
	return false;
}

/** Used as return point for the pop-up window to specify the selected OID */
function setOid (oid, oid_id, oid_name)
{
	var frm = document.forms['frm_t'];
	frm.elements['item[snmp_oid]'].value = "." + oid;
	frm.elements['item[snmp_oid_id]'].value = oid_id;
	
	var elm = document.getElementById ('div_oid_name');
	clearAllChildren (elm);
	elm.appendChild (document.createTextNode (oid_name));
}

{/literal}

//]]>
</script>


<h1>Edit {if $item->is_peripheral_snmp_item()}Peripheral{else}Computer{/if} Monitor Item</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post"  name="frm_t">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td width="160">ID:</td>
		<td class="post_highlight">{$item->id}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">
			SNMP item:
			{if $item->is_snmp}
			<a href="#" onclick="return showOidsPopup();">[Select OID]</a>
			{/if}
		</td>
		<td class="post_highlight">
			{if !$item->is_snmp}No
			{else}
				<input type="text" name="item[snmp_oid]" size="40" value="{$item->snmp_oid|escape}" />
				&nbsp;&nbsp;&nbsp;
				<div id="div_oid_name" style="display:inline">{if $oid->id}{$oid->name|escape}{/if}</div>
				<input type="hidden" name="item[snmp_oid_id]" size="5" value="{$item->snmp_oid_id}" />
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Descriptive name:</td>
		<td class="post_highlight"><input type="text" name="item[name]" size="40" value="{$item->name|escape}" /></td>
	</tr>
	<tr>
		<td class="highlight">Short name:</td>
		<td class="post_highlight"><input type="text" name="item[short_name]" size="20" value="{$item->short_name|escape}" /></td>
	</tr>
	<tr>
		<td class="highlight">Type:</td>
		<td class="post_highlight">
			<select name="item[type]"  onchange="checkTypeChange()">
				<option value="">[Select]</option>
				{html_options options=$MONITOR_TYPES selected=$item->type}
			</select>
		</td>
	</tr>
	
	<tr id="treshold_row" style="display:none;">
		<td class="highlight">Treshold:</td>
		<td class="post_highlight">
			<input type="text" name="item[treshold]" size="8" value="{$item->treshold}" />
			<select name="item[treshold_type]">
				{html_options options=$CRIT_TYPES_NAMES selected=$item->treshold_type}
			</select>
		</td>
	</tr>
	
	<tr id="lists_row" style="display:none;">
		<td class="highlight">List type:</td>
		<td class="post_highlight">
			<select name="item[list_type]" onchange="checkListTypeChange();">
				<option value="">[Select list type]</option>
				{html_options options=$AVAILABLE_ITEMS_LISTS_NAMES selected=$item->list_type}
			</select>
			
			{foreach from=$AVAILABLE_ITEMS_LISTS key=list_id item=list_values}
			<div id="div_list_values_{$list_id}" style="display:none;">
				<b>Values:</b><br/>
				{foreach from=$list_values key=k item=v}
					&nbsp;&nbsp;&nbsp;{$k|escape} : {$v|escape}<br/>
				{/foreach}
			</div>
			{/foreach}
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Single/Multi values:</td>
		<td class="post_highlight">
			<select name="item[multi_values]">
				<option value="">[Select]</option>
				{html_options options=$MONITOR_MULTI selected=$item->multi_values}
			</select>
		</td>
	</tr>

	
	{if ($item->type==MONITOR_TYPE_STRUCT) }
	<tr>
		<td class="highlight">Main field:</td>
		<td class="post_highlight">
			<select name="item[main_field_id]">
				<option value="0">[None]</option>
				{foreach from=$item->struct_fields item=struct_item}
					<option value="{$struct_item->id}" {if $item->main_field_id==$struct_item->id}selected{/if}>{$struct_item->name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	{elseif ($item->multi_values==$smarty.const.MONITOR_MULTI_YES) }
	<tr>
		<td class="highlight">Sort values:</td>
		<td class="post_highlight">
			<select name="item[main_field_id]">
				<option value="0">No</option>
				<option value="1" {if $item->main_field_id}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	{/if}
	
	{if !$item->is_peripheral_snmp_item()}
	<tr>
		<td class="highlight">Category:</td>
		<td class="post_highlight">
			<select name="item[category_id]">
				<option value="">[Select]</option>
				{html_options options=$MONITOR_CAT selected=$item->category_id}
			</select>
		</td>
	</tr>
	{/if}
	<tr>
		<td class="highlight">Default logging:</td>
		<td class="post_highlight">
			<select name="item[default_log]">
				<option value="">[Select]</option>
				{html_options options=$MONITOR_LOG selected=$item->default_log}
			</select>
		</td>
	</tr>
	
	{if $item->id>=$smarty.const.ITEM_ID_COLLECTED_MIN and $item->id <= $smarty.const.ITEM_ID_COLLECTED_MAX}
	<tr>
		<td class="highlight">Default reporting interval:</td>
		<td class="post_highlight"><input type=text name="item[default_update]" size="10" value="{$item->default_update}"/> minutes</td>
	</tr>
	{/if}
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
<p/>


{if ($item->type==MONITOR_TYPE_STRUCT) }
	
	<h2>Structure definition</h2>
    {assign var="p" value="parent_id:"|cat:$item->id}
	<p>[ <a href="{'kawacs'|get_link:'monitor_item_add':$p:'template'}">Add field &#0187;</a> ]</p>
	
	<table class="list" width="80%">
		<thead>
		<tr>
			<td width="1%">ID</td>
			<td>Name</td>
			<td width="29%">Short name</td>
			<td width="20%">Type</td>
			<td width="10%">Treshold</td>
			{if $item->is_snmp}<td>SNMP OID</td>{/if}
			<td width="10%"></td>
		</tr>
		</thead>
		
		{foreach from=$item->struct_fields item=struct_item}
			<tr>
				<td>
                    {assign var="p" value="id:"|cat:$struct_item->id}
                    <a href="{'kawacs'|get_link:'monitor_item_edit':$p:'template'}">{$struct_item->id}</a></td>
				<td nowrap="nowrap">
                    {assign var="p" value="id:"|cat:$struct_item->id}
                    <a href="{'kawacs'|get_link:'monitor_item_edit':$p:'template'}">{$struct_item->name}</a></td>
				<td>{$struct_item->short_name}</td>
				<td>{$struct_item->type_display}</td>
				<td nowrap="nowrap">
					{if $struct_item->type==$smarty.const.MONITOR_TYPE_MEMORY and $struct_item->treshold}
						{assign var="multiplier" value=$struct_item->treshold_type}
						{$struct_item->treshold} {$CRIT_TYPES_NAMES.$multiplier}
					{else}
						<font class="light_text">--</font>
					{/if}
				</td>
				{if $item->is_snmp}
				<td nowrap="nowrap">{$struct_item->snmp_oid|escape}</td>
				{/if}
				<td align="right" nowrap="nowrap">
                    {assign var="p" value="id:"|cat:$struct_item->id}
                    <a href="{'kawacs'|get_link:'monitor_item_delete':$p:'template'}"
					onClick="return confirm('Are you REALLY sure you want to delete the field \'{$struct_item->name}\'?');">Delete</a>
				</td>
			</tr>
		{/foreach}
		
	</table>

    {assign var="p" value="parent_id:"|cat:$item->id}
    <p>[ <a href="{'kawacs'|get_link:'monitor_item_add':$p:'template'}">Add field &#0187;</a> ]</p>
{/if}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
checkTypeChange ();
checkListTypeChange ();
//]]>
</script>

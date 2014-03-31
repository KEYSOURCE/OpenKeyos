{assign var="paging_titles" value="KAWACS, Manage monitor items, Add item "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_monitor_items"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript" src="/javascript/ajax.js"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

var type_memory = {$smarty.const.MONITOR_TYPE_MEMORY};
var type_value_list = {$smarty.const.MONITOR_TYPE_LIST};

var periph_snmp_min = {$smarty.const.ITEM_ID_PERIPHERAL_SNMP_MIN};
var periph_snmp_max = {$smarty.const.ITEM_ID_PERIPHERAL_SNMP_MAX};

var list_ids = new Array ();
cnt = 0;
{foreach from=$AVAILABLE_ITEMS_LISTS key=list_id item=list_values}
	list_ids[cnt++] = {$list_id};
{/foreach}

{literal}

function checkIdChange ()
{
	frm = document.forms['frm_t'];
	c_id = frm.elements['item[id]'].value;
	c_id = parseInt (c_id);
	if (isNaN(c_id)) c_id = 0;
	elm_row_category = document.getElementById ('row_category');
	elm_snmp = frm.elements['item[is_snmp]'];
	
	if (c_id >= periph_snmp_min && c_id <= periph_snmp_max)
	{
		elm_row_category.style.display = 'none';
		elm_snmp.options[1].selected = true;
		checkSnmpChange ();
	}
	else elm_row_category.style.display = '';
}

function checkTypeChange ()
{
	frm = document.forms['frm_t'];
	elm_types = frm.elements['item[type]'];
	selected_type = elm_types.options[elm_types.selectedIndex].value;
	
	elm_row = document.getElementById ('treshold_row');
	elm_treshold = frm.elements['item[treshold]'];
	elm_row_lists = document.getElementById ('lists_row');
	
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
	frm = document.forms['frm_t'];
	elm_types = frm.elements['item[type]'];
	selected_type = elm_types.options[elm_types.selectedIndex].value;
	
	if (selected_type == type_value_list)
	{
		elm_lists = frm.elements['item[list_type]'];
		selected_list_type = elm_lists.options[elm_lists.selectedIndex].value;
		
		for (i=0; i<list_ids.length; i++)
		{
			elm_values = document.getElementById ('div_list_values_' + list_ids[i]);
			elm_values.style.display = 'none';
		}
		
		if (selected_list_type != '')
		{
			elm_values = document.getElementById ('div_list_values_' + selected_list_type);
			elm_values.style.display = '';
		}
	}
}

function checkSnmpChange ()
{
	var frm = document.forms['frm_t'];
	var elm_snmp = frm.elements['item[is_snmp]'];
	var selected_snmp = elm_snmp.options[elm_snmp.selectedIndex].value;
	var elm_oid = document.getElementById ('div_oid');
	var elm_sel_oid = document.getElementById ('lnk_sel_oid');
	
	if (selected_snmp == '1')
	{
		elm_oid.style.display = 'inline';
		elm_sel_oid.style.display = '';
	}
	else
	{
		var c_id = frm.elements['item[id]'].value;
		c_id = parseInt (c_id);
		if (isNaN(c_id)) c_id = 0;
		
		if (c_id >= periph_snmp_min && c_id <= periph_snmp_max)
		{
			alert ('Items with this ID are mandatory to be SMTP items.');
			elm_snmp.options[1].selected = true;
			return false;
		}
		else
		{
			elm_oid.style.display = 'none';
			elm_sel_oid.style.display = 'none';
			frm.elements['item[snmp_oid]'].value = '';
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


<h1>Add Monitor Item</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}

ID range for automatic computers collected items: <b>{$smarty.const.ITEM_ID_COLLECTED_MIN} - {$smarty.const.ITEM_ID_COLLECTED_MAX}</b>
(For SNMP, recommended over <b>{$smarty.const.ITEM_ID_COLLECTED_SNMP_MIN}</b>)
<br>
ID range for manually collected items: <b>{$smarty.const.ITEM_ID_MANUAL_MIN} - {$smarty.const.ITEM_ID_MANUAL_MAX}</b><br/>
ID range for computer events items: <b>{$smarty.const.ITEM_ID_EVENTS_MIN} - {$smarty.const.ITEM_ID_EVENTS_MAX}</b><br/>
ID range for automatic peripherals SNMP collected items: <b>{$smarty.const.ITEM_ID_PERIPHERAL_SNMP_MIN} - {$smarty.const.ITEM_ID_PERIPHERAL_SNMP_MAX}</b>

<p>
<table width="98%" class="list">
	<thead>
	<tr>
		<td colspan="2">Monitor item definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="140" class="highlight">ID:</td>
		<td class="post_highlight"><input type="text" name="item[id]" size="10" value="{$item->id|escape}" onchange="checkIdChange();"/></td>
	</tr>
	<tr>
		<td class="highlight">
			SNMP item:
			<a href="#" onclick="return showOidsPopup();" id="lnk_sel_oid" style="display:none;">[Select OID]</a>
		</td>
		<td class="post_highlight" nowrap="nowrap">
			<select name="item[is_snmp]" onchange="checkSnmpChange ();">
				<option value="0">No</option>
				<option value="1" {if $item->is_snmp}selected{/if}>Yes</option>
			</select>
			&nbsp;&nbsp;&nbsp;
			<div id="div_oid" style="display:none;">
			OID: <input type="text" name="item[snmp_oid]" size="50" value="{$item->snmp_oid|escape}" />
			&nbsp;&nbsp;&nbsp;
			<div id="div_oid_name" style="display:inline">{if $oid->id}{$oid->name|escape}{/if}</div>
			<input type="hidden" name="item[snmp_oid_id]" size="5" value="{$item->snmp_oid_id}" />
			
			</div>
		</td>
	</tr>
	<tr>
		<td class="highlight">Descriptive name:</td>
		<td class="post_highlight"><input type="text" name="item[name]" size="40" value="{$item->name|escape}"/></td>
	</tr>
	<tr>
		<td class="highlight">Short name:</td>
		<td class="post_highlight"><input type="text" name="item[short_name]" size="20" value="{$item->short_name|escape}" /></td>
	</tr>
	<tr>
		<td class="highlight">Type:</td>
		<td class="post_highlight">
			<select name="item[type]" onchange="checkTypeChange()">
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
	<tr id="row_category" style="display:none;">
		<td class="highlight">Category:</td>
		<td class="post_highlight">
			<select name="item[category_id]">
				<option value="">[Select]</option>
				{html_options options=$MONITOR_CAT selected=$item->category_id}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Default logging:</td>
		<td class="post_highlight">
			<select name="item[default_log]">
				<option value="">[Select]</option>
				{html_options options=$MONITOR_LOG selected=$item->default_log}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Default reporting interval:</td>
		<td class="post_highlight"><input type=text name="item[default_update]" size="10" value="{$item->default_update}" /> minutes (only for automatic collected items)</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
checkIdChange();
checkTypeChange ();
checkListTypeChange ();
checkSnmpChange ();
//]]>
</script>

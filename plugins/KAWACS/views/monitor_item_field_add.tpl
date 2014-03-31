{assign var="paging_titles" value="KAWACS, Manage monitor items, Edit item, Add field "}
{assign var="parent_id" value=$parent_item->id}
{assign var="p" value="id:"|cat:$parent_id}
{assign var="monitor_item_edit_link" value="kawacs"|get_link:"monitor_item_edit":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_monitor_items, "|cat:$monitor_item_edit_link}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript" src="/javascript/ajax.js"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

var type_memory = {$smarty.const.MONITOR_TYPE_MEMORY};
var type_value_list = {$smarty.const.MONITOR_TYPE_LIST};
var type_date = {$smarty.const.MONITOR_TYPE_DATE};

var list_ids = new Array ();
cnt = 0;
{foreach from=$AVAILABLE_ITEMS_LISTS key=list_id item=list_values}
	list_ids[cnt++] = {$list_id};
{/foreach}

{literal}

function checkTypeChange ()
{
	frm = document.forms['frm_t'];
	elm_types = frm.elements['item[type]'];
	selected_type = elm_types.options[elm_types.selectedIndex].value;
	
	elm_row = document.getElementById ('treshold_row');
	elm_treshold = frm.elements['item[treshold]'];
	elm_row_lists = document.getElementById ('lists_row');
	elm_row_date = document.getElementById ('date_row');
	
	if (selected_type == type_memory)
	{
		elm_row.style.display = '';
		elm_row_lists.style.display = 'none';
		elm_row_date.style.display = 'none';
	}
	else if (selected_type == type_value_list)
	{
		elm_row.style.display = 'none';
		elm_treshold.value = '0';
		elm_row_lists.style.display = '';
		elm_row_date.style.display = 'none';
	}
	else if (selected_type == type_date)
	{
		elm_row.style.display = 'none';
		elm_row_lists.style.display = 'none';
		elm_row_date.style.display = '';
	}
	else
	{
		elm_row.style.display = 'none';
		elm_row_lists.style.display = 'none';
		elm_treshold.value = '0';
		elm_row_date.style.display = 'none';
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

<h1>Add Monitoring Item Field</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}

<table width="98%" class="list">
	<thead>
	<tr>
		<td colspan="2">Field definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="140" class="highlight">Parent item:</td>
		<td class="post_highlight"><b>{$parent_item->id} : {$parent_item->name|escape}</b></td>
	</tr>
	
	{if $parent_item->is_snmp}
	<tr>
		<td class="highlight">SNMP item:</td>
		<td class="post_highlight">Yes</td>
	</tr>
	<tr>
		<td class="highlight">
			SNMP OID:
			<a href="#" onclick="return showOidsPopup();">[Select OID]</a>
		</td>
		<td class="post_highlight">
			<input type="text" name="item[snmp_oid]" size="40" value="{$item->snmp_oid|escape}" />
			&nbsp;&nbsp;&nbsp;
			<div id="div_oid_name" style="display:inline">{if $oid->id}{$oid->name|escape}{/if}</div>
			<input type="hidden" name="item[snmp_oid_id]" size="5" value="{$item->snmp_oid_id}" />
		</td>
	</tr>
	{/if}
	
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
	
	<tr id="date_row" style="display:none;">
		<td class="highlight">Show:</td>
		<td class="post_highlight">
			<input type="checkbox" name="item[date_show_hour]" value="1" class="checkbox" {if $item->date_show_hour}checked{/if} /> Hour<br/>
			<input type="checkbox" name="item[date_show_second]" value="1" class="checkbox" {if $item->date_show_second}checked{/if} /> Seconds<br/>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
checkTypeChange ();
checkListTypeChange ();
//]]>
</script>

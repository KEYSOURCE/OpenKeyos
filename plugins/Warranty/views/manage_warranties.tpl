{assign var="paging_titles" value="KAWACS, Manage Warranties"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<script language="JavaScript" type="text/javascript">
//<![CDATA[

// The names of available tabs
var tabs = new Array ('chart_computers', 'chart_adprinters', 'chart_peripherals', 'summary', 'details');

var sel_customer_id = "{$filter.customer_id}";

{literal}

// Retrieve a cookie value by name
function getCookie (cookie_name)
{
	var nameEQ = cookie_name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

// Set the active tab
function showTab (tab_name, no_alert)
{
	if ((sel_customer_id == '-1' || sel_customer_id == '-2') && tab_name != 'summary')
	{
		tab_name = 'summary';
		if (!no_alert) alert ('For this option only the "Summary" tab is available.');
	}

	// Hide all tabs first. Also make sure the tab is in the list
	found = false;
	
	for (i=0; i<tabs.length; i++)
	{
		document.getElementById('tab_' + tabs[i]).style.display = 'none';
		document.getElementById('tab_head_' + tabs[i]).className = 'tab_inactive';
		if (tabs[i] == tab_name) found = true;
	}
	
	if (!found) tab_name = tabs[0];
	
	document.getElementById('tab_'+tab_name).style.display = 'block';
	document.getElementById('tab_head_'+tab_name).className = '';
	
	document.cookie = 'warranties_view_tab='+tab_name;
	
	return false;
}

// Removes the interval filtering
function clearMonthsFilter ()
{
	var frm = document.forms['filter'];
	frm.elements['filter[month_start]'].options[0].selected = true;
	frm.elements['filter[month_end]'].options[0].selected = true;
	frm.elements['do_filter_hidden'].value = 1;
	frm.submit ();
	
	return false;
}

function showInfo(what)
{
	elm = document.getElementById ('di_' + what);
	elm.style.display = 'block';
}

function hideInfo(what)
{
	elm = document.getElementById ('di_' + what);
	elm.style.display = 'none';
}

function highlightRow (elm_name)
{
	elm = document.getElementById (elm_name);
	elm.style.background = "#EEEEEE";
	elm.childNodes[0].style.color = "red";
}
function highlightRowOff (elm_name)
{
	elm = document.getElementById (elm_name);
	elm.style.background = "";
	elm.childNodes[0].style.color = "";
}

{/literal}

//]]>
</script>


<h1>Manage Warranties</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter"> 
<input type="hidden" name="do_filter_hidden" value="0" />
{$form_redir}

<b>Customer:</b>
<select name="filter[customer_id]"  
	onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
>
	<option value="">[Select customer]</option>
	<option value="-1" {if $filter.customer_id==-1}selected{/if}>[Servers without warranties or SN]</option>
	<option value="-2" {if $filter.customer_id==-2}selected{/if}>[Workstations without warranties or SN]</option>
	{html_options options=$customers_list selected=$filter.customer_id}
</select>
{if $all_months and $filter.customer_id}
	&nbsp;&nbsp;&nbsp;
	Interval:
	<select name="filter[month_start]" onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();">
		<option value="">-</option>
		{html_options options=$all_months selected=$filter.month_start}
	</select>
	-
	<select name="filter[month_end]" onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();">
		<option value="">-</option>
		{html_options options=$all_months selected=$filter.month_end}
	</select>
	{if $filter.month_start or $filter.month_end}
	[ <a href="#" onclick="return clearMonthsFilter();">Clear filter</a> ]
	{/if}
{/if}
<p/>


{capture name="notes_charts"}


<p class="no_print">
Place the mouse over a product link to see its details. 
Pleace the mouse over a table cell to see the corresponding month.
<p/>

The meaning of the colors is:
<ul>
	<li><div style="background-color: {$smarty.const.WAR_COL_OK}; width: 10px; display: inline;"
	>&nbsp;&nbsp;&nbsp;&nbsp;</div> - More than 6 months remaining</li>
	<li><div style="background-color: {$smarty.const.WAR_COL_6_MONTHS}; width: 10px; display: inline;"
	>&nbsp;&nbsp;&nbsp;&nbsp;</div> - 6 months or less remaining</li>
	<li><div style="background-color: {$smarty.const.WAR_COL_3_MONTHS}; width: 10px; display: inline;"
	>&nbsp;&nbsp;&nbsp;&nbsp;</div> - 3 months or less remaining</li>
	<li><div style="background-color: {$smarty.const.WAR_COL_1_MONTH}; width: 10px; display: inline;"
	>&nbsp;&nbsp;&nbsp;&nbsp;</div> - 1 month or less remaining</li>
	<li><div style="background-color: {$smarty.const.WAR_COL_EXPIRED}; width: 10px; display: inline;"
	>&nbsp;&nbsp;&nbsp;&nbsp;</div> - Warranty expired</li>
	<li><div style="background-color: {$smarty.const.WAR_COL_REPLACED}; width: 10px; display: inline;"
	>&nbsp;&nbsp;&nbsp;&nbsp;</div> - Replaced or ignored warranty</li>
</ul>

<div class="no_print">
<b>NOTE:</b> To print the chart directly from the browser (although you should use the reports generator for printing tasks):
<ul>
	<li>Internet Explorer: Go to <i>Tools &gt; Internet Options &gt; Advanced</i> and in the <i>Printing</i>
	section enable the <i>Print background colors and images</i> option.</li>
	<li>Firefox: Go to <i>Files &gt; Page Setup</i> and enable the option <i>Print Background</i>
</ul>
</div>

{/capture}

<table class="tab_header"><tr>
	<td id="tab_head_chart_computers" class="tab_inactive"><a href="#" onclick="return showTab('chart_computers');" style="width: 130px;">Lifecycle: Computers</a></td>
	<td id="tab_head_chart_adprinters" class="tab_inactive"><a href="#" onclick="return showTab('chart_adprinters');" style="width: 140px;">Lifecycle: AD Printers</a></td>
	<td id="tab_head_chart_peripherals" class="tab_inactive"><a href="#" onclick="return showTab('chart_peripherals');" style="width: 130px;">Lifecycle: Peripherals</a></td>
	<td id="tab_head_summary" class="tab_inactive"><a href="#" onclick="return showTab('summary');">Summary</a></td>
	<td id="tab_head_details" class="tab_inactive"><a href="#" onclick="return showTab('details');">Details</a></td>
</tr></table>

<div id="tab_chart_computers" class="tab_content" style="display:none;">
	<h2>Lifecycle: Computers</h2>
	<p/>
	
	{if trim($filter.customer_id)}
		<table class="list list_wide grid_light" rules="groups" id="tbl_computers_chart">
			<thead>
			<tr>
				<td rowspan="2"> </td>
				{foreach from=$computers_warranties_head key=year item=y}
				<td colspan="{$y->months_count}" style="border-left:2px solid black;">{$year}</td>
				{/foreach}
			</tr>
			<tr>
				{foreach from=$computers_warranties_head key=year item=y}
					{foreach from=$y->groups item=g}
						<td colspan="{$g->months_count}" {if $g->is_year_start}style="border-left: 2px solid black;"{/if}
						>{if $g->months_count>1}{$g->month_str}{/if}</td>
					{/foreach}
				{/foreach}
			</tr>
			</thead>
			
			{assign var="last_type" value=-1}
			{foreach from=$computers_warranties item=warranty name="computers_warranties"}
				{assign var="computer_id" value=$warranty->id}
{if $last_type!=$computers_types.$computer_id}
<tr>
	{assign var="last_type" value=$computers_types.$computer_id}
	<td><b>{$COMP_TYPE_NAMES.$last_type}s ({$cnt_computers.$last_type})</b></td>
	{foreach from=$computers_warranties_months item=month}{strip}
	{assign var="class" value=""}
	{if $month->is_current}{assign var="class" value="$class mh"}{/if}
	{if $month->is_year_start}{assign var="class" value="$class my"}{/if}
{/strip}<td{if $class} class="{$class}"{/if}{if $smarty.foreach.computers_warranties.first}><img src="/images/spacer.gif"/></td>{else}/>{/if}{/foreach}
</tr>
{/if}
				<tr onmouseover="highlightRow ('tc_{$warranty->id}_{$warranty->id2}');" onmouseout="highlightRowOff ('tc_{$warranty->id}_{$warranty->id2}');">
					
					<td nowrap="nowrap" id="tc_{$warranty->id}_{$warranty->id2}">
                        {assign var="p" value="id:"|cat:$computer_id}
                        <a href="{'kawacs'|get_link:'computer_view':$p:'template'}" onmouseover="showInfo('c_{$computer_id}_{$warranty->id2}');" onmouseout="hideInfo('c_{$computer_id}_{$warranty->id2}');">#{$computer_id}: {$computers_list.$computer_id}</a>
<div id="di_c_{$warranty->id}_{$warranty->id2}" class="info_box" style="display:none;">
<table width="100%">
<tr>
	<td width="100"><b>Computer:</b></td><td>#{$computer_id}: {$computers_list.$computer_id}</td>
</tr><tr>
	<td>Product:</td><td>{$warranty->product}</td>
</tr><tr>
	<td>Serial number:</td><td>{if $warranty->sn}{$warranty->sn|escape}{else}--{/if}</td>
</tr><tr>
	<td>Interval:</td>
	<td>{if $warranty->warranty_starts}{$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if} to
	{if $warranty->warranty_ends}{$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if}</td>
</tr><tr>
	<td>Expires in:</td><td>{$warranty->get_expiration_str()}</td>
</tr><tr>
	<td>Service level:</td>
	<td>{if $warranty->service_package_id or $warranty->service_level_id}
		{assign var="service_package_id" value=$warranty->service_package_id}
		{assign var="service_level_id" value=$warranty->service_level_id}
		{$service_packages_list.$service_package_id}, {$service_levels_list.$service_level_id}
	{else}--{/if}</td>
</tr><tr>
	<td>Contract:</td><td>{if $warranty->contract_number}{$warranty->contract_number|escape}{else}--{/if}</td>
</tr><tr>
	<td>HW product ID:</td><td>{if $warranty->hw_product_id}{$warranty->hw_product_id|escape}{else}--{/if}</td>
</tr><tr>
	<td>Product number:</td><td>{if $warranty->product_number}{$warrany->product_number}{else}--{/if}</td>
</tr><tr>
	<td>Replaced/Ignored:</td><td>{if $warranty->replaced_ignored}Yes{else}-{/if}</td>
</tr>
</table>
</div>
					</td>
{* Use minimal space, as there are LOTS of TD elements *}
{foreach from=$computers_warranties_months item=month}{strip}
	{assign var="class" value=""}
	{assign var="color_code" value=$warranty->get_color_code()}
	{if $warranty->has_month($month)}{assign var="class" value="m$color_code"}{/if}
	{if $month->is_current}{assign var="class" value="$class mh"}{/if}
	{if $month->is_year_start}{assign var="class" value="$class my"}{/if}
{/strip}<td{if $class} class="{$class}"{/if}{if $smarty.foreach.computers_warranties.first}><img src="/images/spacer.gif"/></td>{else}/>{/if}{/foreach}
				</tr>
			{foreachelse}
				<tr>
					<td class="light_text" nowrap="nowrap">[No warranties info]</td>
{foreach from=$computers_warranties_months item=month}<td class="m"/>{/foreach}
				</tr>
			{/foreach}
			{if $cnt_computers_all > 0}
			<tr>
				<td><b>Total: {$cnt_computers_all} computers</b></td>
				<td colspan="{$computers_warranties_months|@count}"> </td>
			</tr>
			{/if}
		</table>
		
		{$smarty.capture.notes_charts}
	{else}
		<font class="light_text">[No customer selected]</font>
	{/if}
</div>

<div id="tab_chart_adprinters" class="tab_content" style="display:none;">
	<h2>Lifecycle: AD Printers</h2>
	<p/>
	
	{if trim($filter.customer_id)}
		<table class="list list_wide grid_light" width="100" id="tbl_ad_printers_chart">
			<thead>
			<tr>
				<td rowspan="2"> </td>
				{foreach from=$ad_printers_warranties_head key=year item=y}
				<td colspan="{$y->months_count}" style="border-left: 2px solid black;">{$year}</td>
				{/foreach}
			</tr>
			<tr>
				{foreach from=$ad_printers_warranties_head key=year item=y}
					{foreach from=$y->groups item=g}
						<td colspan="{$g->months_count}" {if $g->is_year_start}style="border-left: 2px solid black;"{/if}
						>{if $g->months_count>1}{$g->month_str}{/if}</td>
					{/foreach}
				{/foreach}
			</tr>
			</thead>
			
			{foreach from=$ad_printers_warranties item=warranty name=ad_printers_warranties}
				<tr onmouseover="highlightRow ('td_adprinter_{$warranty->id}_{$warranty->id2}');" onmouseout="highlightRowOff ('td_adprinter_{$warranty->id}_{$warranty->id2}');">
				
					<td nowrap="nowrap" id="td_adprinter_{$warranty->id}_{$warranty->id2}"
						>
                        {assign var="p" value="computer_id:"|cat:$warranty->id|cat:",nrc:"|cat:$warranty->id2}
                        <a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}"
						onmouseover="showInfo('ap_{$warranty->id}_{$warranty->id2}');" onmouseout="hideInfo('ap_{$warranty->id}_{$warranty->id2}');">{$warranty->product}</a>

<div id="di_ap_{$warranty->id}_{$warranty->id2}" class="info_box" style="display:none;">
<table width="100%">
<tr>
	<td width="100"><b>Printer:</b></td> <td><b>{$warranty->product}</b></td>
</tr><tr>
	<td>Serial number:</td> <td>{if $warranty->sn}{$warranty->sn|escape}{else}--{/if}</td>
</tr><tr>
	<td>Interval:</td>
	<td>{if $warranty->warranty_starts}{$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if} to
	{if $warranty->warranty_ends}{$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if}</td>
</tr><tr>
	<td>Expires in:</td><td>{$warranty->get_expiration_str()}</td>
</tr><tr>
	<td>Service level:</td>
	<td>{if $warranty->service_package_id or $warranty->service_level_id}
	{assign var="service_package_id" value=$warranty->service_package_id}
	{assign var="service_level_id" value=$warranty->service_level_id}
	{$service_packages_list.$service_package_id}, {$service_levels_list.$service_level_id}
	{else}--{/if}
	</td>
</tr><tr>
	<td>Contract:</td><td>{if $warranty->contract_number} {$warranty->contract_number|escape}{else}--{/if}</td>
</tr><tr>
	<td>HW product ID:</td><td>{if $warranty->hw_product_id}{$warranty->hw_product_id|escape}{else}--{/if}</td>
</tr><tr>
	<td>Product number:</td><td>{if $warranty->product_number}{$warrany->product_number|escape}{else}--{/if}</td>
</tr>
</table>
</div>
					</td>
{* Use minimal space, as there are LOTS of TD elements *}
{foreach from=$ad_printers_warranties_months item=month}{strip}
	{assign var="class" value=""}
	{assign var="color_code" value=$warranty->get_color_code()}
	{if $warranty->has_month($month)}{assign var="class" value="m$color_code"}{/if}
	{if $month->is_current}{assign var="class" value="$class mh"}{/if}
	{if $month->is_year_start}{assign var="class" value="$class my"}{/if}
{/strip}<td{if $class} class="{$class}"{/if}{if $smarty.foreach.ad_printers_warranties.first}><img src="/images/spacer.gif"/></td>{else}/>{/if}{/foreach}
				</tr>
			{foreachelse}
				<tr>
					<td class="light_text" nowrap="nowrap">[No warranties info]</td>
{foreach from=$ad_printers_warranties_months item=month}<td class="m"/>{/foreach}
				</tr>
			{/foreach}
		</table>
		
		{$smarty.capture.notes_charts}
	{else}
		<font class="light_text">[No customer selected]</font>
	{/if}
</div>


<div id="tab_chart_peripherals" class="tab_content" style="display:none;">
	<h2>Lifecycle: Peripherals</h2>
	<p/>
	
	{if trim($filter.customer_id)}
		<table class="list list_wide grid_light" width="100" id="tbl_peripherals_chart">
			<thead>
			<tr>
				<td rowspan="2"> </td>
				{foreach from=$peripherals_warranties_head key=year item=y}
				<td colspan="{$y->months_count}" style="border-left: 2px solid black;">{$year}</td>
				{/foreach}
			</tr>
			<tr>
				{foreach from=$peripherals_warranties_head key=year item=y}
					{foreach from=$y->groups item=g}
						<td colspan="{$g->months_count}" {if $g->is_year_start}style="border-left: 2px solid black;"{/if}
						>{if $g->months_count>1}{$g->month_str}{/if}</td>
					{/foreach}
				{/foreach}
			</tr>
			</thead>
			                                                         
			{foreach from=$peripherals_warranties item=warranty name=peripherals_warranties}
				{assign var="peripheral_id" value=$warranty->id}
				{assign var="class_id" value=$warranty->id2}
				<tr onmouseover="highlightRow ('td_peripheral_{$warranty->id}_{$warranty->id2}');" onmouseout="highlightRowOff ('td_peripheral_{$warranty->id}_{$warranty->id2}');">
				
					<td nowrap="nowrap" id="td_peripheral_{$warranty->id}_{$warranty->id2}">
                        {assign var="p" value="id:"|cat:$warranty->id}
                        <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}" onmouseover="showInfo('p_{$peripheral_id}_{$warranty->id2}');" onmouseout="hideInfo('p_{$peripheral_id}_{$warranty->id2}');">{$peripherals_list.$peripheral_id}</a>

<div id="di_p_{$warranty->id}_{$warranty->id2}" class="info_box" style="display:none;">
<table width="100%">
<tr>
	<td width="100"><b>Peripheral:</b></td><td>{$peripherals_list.$peripheral_id}</td>
</tr><tr>
	<td>Class:</td><td>{$peripherals_classes_list.$class_id}</td>
</tr><tr>
	<td>Serial number:</td><td>{if $warranty->sn}{$warranty->sn|escape}{else}--{/if}</td>
</tr><tr>
	<td>Interval:</td>
	<td>{if $warranty->warranty_starts} {$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if} to
	{if $warranty->warranty_ends} {$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if}</td>
</tr><tr>
	<td>Expires in:</td><td>{$warranty->get_expiration_str()}</td>
</tr><tr>
	<td>Service level:</td>
	<td>{if $warranty->service_package_id or $warranty->service_level_id}
		{assign var="service_package_id" value=$warranty->service_package_id}
		{assign var="service_level_id" value=$warranty->service_level_id}
		{$service_packages_list.$service_package_id}, {$service_levels_list.$service_level_id}
	{else}--{/if}</td>
</tr><tr>
	<td>Contract:</td><td>{if $warranty->contract_number} {$warranty->contract_number|escape}{else}--{/if}</td>
</tr><tr>
	<td>HW product ID:</td><td>{if $warranty->hw_product_id}{$warranty->hw_product_id|escape}{else}--{/if}</td>
</tr>
</table>
</div>
					</td>
{* Use minimal space, as there are LOTS of TD elements *}
{foreach from=$peripherals_warranties_months item=month}{strip}
	{assign var="class" value=""}
	{assign var="color_code" value=$warranty->get_color_code()}
	{if $warranty->has_month($month)}{assign var="class" value="m$color_code"}{/if}
	{if $month->is_current}{assign var="class" value="$class mh"}{/if}
	{if $month->is_year_start}{assign var="class" value="$class my"}{/if}
{/strip}<td{if $class} class="{$class}"{/if}{if $smarty.foreach.peripherals_warranties.first}><img src="/images/spacer.gif"/></td>{else}/>{/if}{/foreach}
				</tr>
			{foreachelse}
				<tr>
					<td class="light_text" nowrap="nowrap">[No warranties info]</td>
{foreach from=$peripherals_warranties_months item=month}<td class="m"/>{/foreach}
				</tr>
			{/foreach}
		</table>
		
		{$smarty.capture.notes_charts}
	{else}
		<font class="light_text">[No customer selected]</font>
	{/if}
</div>


<div id="tab_summary" class="tab_content" style="display:none;">
	
	{if trim($filter.customer_id) and $filter.customer_id>=0}
		
		<h2>Warranties Summary: Computers</h2>
		<table class="list" width="100%">
		<thead>
		<tr>
			<td colspan="4">Will expire</td>
			<td width="16%" rowspan="2">Expired</td>
			<td width="16%" rowspan="2">No warranty dates</td>
		</tr>
		<tr>
			<td width="17%">In 1 month</td>
			<td width="17%">In 3 months</td>
			<td width="17%">In 6 months</td>
			<td width="17%">Over 6 months</td>
		</tr>
		</thead>
		
		<tr>
			<td>
				<!-- Expires 1 month -->
				{assign var="last_id" value=""}
{foreach from=$computers_warranties item=warranty}
{if $warranty->days_remaining>0 and $warranty->months_remaining==1 and !$warranty->replaced_ignored}
{assign var="computer_id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="id:"|cat:$computer_id}
	<a href="{'kawacs'|get_link:'computer_view':$p:'template'}">#{$computer_id}: {$computers_list.$computer_id}</a>
	({$warranty->days_remaining}&nbsp;days)<br/>
	{assign var="last_id" value=$computer_id}
{/if}
{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires 3 months -->
				{assign var="last_id" value=""}
{foreach from=$computers_warranties item=warranty}
{if $warranty->months_remaining>1 and $warranty->months_remaining<=3 and !$warranty->replaced_ignored}
{assign var="computer_id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="id:"|cat:$computer_id}
    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">#{$computer_id}: {$computers_list.$computer_id}</a><br/>
	{assign var="last_id" value=$computer_id}
{/if}
{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires 6 month -->
				{assign var="last_id" value=""}
{foreach from=$computers_warranties item=warranty}
{if $warranty->months_remaining>3 and $warranty->months_remaining<=6 and !$warranty->replaced_ignored}
{assign var="computer_id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="id:"|cat:$computer_id}
    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">#{$computer_id}: {$computers_list.$computer_id}</a><br/>
	{assign var="last_id" value=$computer_id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires over 6 months -->
				{assign var="last_id" value=""}
{foreach from=$computers_warranties item=warranty}
{if $warranty->months_remaining>6 and !$warranty->replaced_ignored}
{assign var="computer_id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="id:"|cat:$computer_id}
    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">#{$computer_id}: {$computers_list.$computer_id}</a><br/>
	{assign var="last_id" value=$computer_id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expired -->
				{assign var="last_id" value=""}
{foreach from=$computers_warranties item=warranty}
{if $warranty->is_expired() and !$warranty->replaced_ignored}
{assign var="computer_id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="id:"|cat:$computer_id}
    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">#{$computer_id}: {$computers_list.$computer_id}</a><br/>
	{assign var="last_id" value=$computer_id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Dates not set -->
				{assign var="last_id" value=""}
{foreach from=$computers_warranties item=warranty}
{if !$warranty->has_dates() and !$warranty->replaced_ignored}
{assign var="computer_id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="id:"|cat:$computer_id}
    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">#{$computer_id}: {$computers_list.$computer_id}</a><br/>
	{assign var="last_id" value=$computer_id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
		</tr>
		</table>
		<p/>
		
		<h2>Warranties Summary: AD Printers</h2>
		<table class="list" width="100%">
		<thead>
		<tr>
			<td colspan="4">Will expire</td>
			<td width="16%" rowspan="2">Expired</td>
			<td width="16%" rowspan="2">No warranty dates</td>
		</tr>
		<tr>
			<td width="17%">In 1 month</td>
			<td width="17%">In 3 months</td>
			<td width="17%">In 6 months</td>
			<td width="17%">Over 6 months</td>
		</tr>
		</thead>
		
		<tr>
			<td>
				<!-- Expires 1 month -->
				{assign var="last_id" value=""}
{foreach from=$ad_printers_warranties item=warranty}
{if $warranty->days_remaining>0 and $warranty->months_remaining==1 and !$warranty->replaced_ignored}
{assign var="id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="computer_id:"|cat:$warranty->id|cat:",nrc:":$warranty->id2}
    <a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$warranty->product}</a>({$warranty->days_remaining}&nbsp;days)<br/>
	{assign var="last_id" value=$id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires 3 months -->
				{assign var="last_id" value=""}
{foreach from=$ad_printers_warranties item=warranty}
{if $warranty->months_remaining>1 and $warranty->months_remaining<=3 and !$warranty->replaced_ignored}
{assign var="id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="computer_id:"|cat:$warranty->id|cat:",nrc:":$warranty->id2}
    <a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}" >{$warranty->product}</a><br/>
	{assign var="last_id" value=$id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires 6 month -->
				{assign var="last_id" value=""}
{foreach from=$ad_printers_warranties item=warranty}
{if $warranty->months_remaining>3 and $warranty->months_remaining<=6 and !$warranty->replaced_ignored}
{assign var="id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="computer_id:"|cat:$warranty->id|cat:",nrc:":$warranty->id2}
    <a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$warranty->product}</a><br/>
	{assign var="last_id" value=$id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires over 6 months -->
				{assign var="last_id" value=""}
{foreach from=$ad_printers_warranties item=warranty}
{if $warranty->months_remaining>6 and !$warranty->replaced_ignored}
{assign var="id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="computer_id:"|cat:$warranty->id|cat:",nrc:":$warranty->id2}
    <a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$warranty->product}</a><br/>
	{assign var="last_id" value=$id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expired -->
				{assign var="last_id" value=""}
{foreach from=$ad_printers_warranties item=warranty}
{if $warranty->is_expired() and !$warranty->replaced_ignored}
{assign var="id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="computer_id:"|cat:$warranty->id|cat:",nrc:":$warranty->id2}
    <a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$warranty->product}</a><br/>
	{assign var="last_id" value=$id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Dates not set -->
				{assign var="last_id" value=""}
{foreach from=$ad_printers_warranties item=warranty}
{if !$warranty->has_dates() and !$warranty->replaced_ignored}
{assign var="id" value=$warranty->id}
{if $computer_id != $last_id}
    {assign var="p" value="computer_id:"|cat:$warranty->id|cat:",nrc:":$warranty->id2}
    <a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$warranty->product}</a><br/>
	{assign var="last_id" value=$id}
{/if}{/if}{/foreach}
				{if !$last_id}<font class="light_text">[None]</font>{/if}
			</td>
		</tr>
		</table>
		
		<h2>Warranties Summary: Peripherals</h2>
		<table class="list" width="100%">
		<thead>
		<tr>
			<td colspan="4">Will expire</td>
			<td width="16%" rowspan="2">Expired</td>
			<td width="16%" rowspan="2">No warranty dates</td>
		</tr>
		<tr>
			<td width="17%">In 1 month</td>
			<td width="17%">In 3 months</td>
			<td width="17%">In 6 months</td>
			<td width="17%">Over 6 months</td>
		</tr>
		</thead>
		
		<tr>
			<td>
				<!-- Expires 1 month -->
				{assign var="peripheral_id" value=""}
{foreach from=$peripherals_warranties item=warranty}
{if $warranty->days_remaining>0 and $warranty->months_remaining==1 and !$warranty->replaced_ignored}
	{assign var="peripheral_id" value=$warranty->id}
    {assign var="p" value="id:"|cat:$peripheral_id}
    <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripherals_list.$peripheral_id}</a>
	({$warranty->days_remaining}&nbsp;days)<br/>
{/if}{/foreach}
				{if !$peripheral_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires 3 months -->
				{assign var="peripheral_id" value=""}
{foreach from=$peripherals_warranties item=warranty}
{if $warranty->months_remaining>1 and $warranty->months_remaining<=3 and !$warranty->replaced_ignored}
	{assign var="peripheral_id" value=$warranty->id}
    {assign var="p" value="id:"|cat:$peripheral_id}
    <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripherals_list.$peripheral_id}</a><br/>
{/if}{/foreach}
				{if !$peripheral_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires 6 month -->
				{assign var="peripheral_id" value=""}
{foreach from=$peripherals_warranties item=warranty}
{if $warranty->months_remaining>3 and $warranty->months_remaining<=6 and !$warranty->replaced_ignored}
	{assign var="peripheral_id" value=$warranty->id}
    {assign var="p" value="id:"|cat:$peripheral_id}
    <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripherals_list.$peripheral_id}</a><br/>
{/if}{/foreach}
				{if !$peripheral_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expires over 6 months -->
				{assign var="peripheral_id" value=""}
{foreach from=$peripherals_warranties item=warranty}
{if $warranty->months_remaining>6 and !$warranty->replaced_ignored}
	{assign var="peripheral_id" value=$warranty->id}
    {assign var="p" value="id:"|cat:$peripheral_id}
    <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripherals_list.$peripheral_id}</a><br/>
{/if}{/foreach}
				{if !$peripheral_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Expired -->
				{assign var="peripheral_id" value=""}
{foreach from=$peripherals_warranties item=warranty}
{if $warranty->is_expired() and !$warranty->replaced_ignored}
	{assign var="peripheral_id" value=$warranty->id}
    {assign var="p" value="id:"|cat:$peripheral_id}
    <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripherals_list.$peripheral_id}</a><br/>
{/if}{/foreach}
				{if !$peripheral_id}<font class="light_text">[None]</font>{/if}
			</td>
			<td>
				<!-- Dates not set -->
				{assign var="peripheral_id" value=""}
{foreach from=$peripherals_warranties item=warranty}
{if !$warranty->has_dates() and !$warranty->replaced_ignored}
	{assign var="peripheral_id" value=$warranty->id}
    {assign var="p" value="id:"|cat:$peripheral_id}
    <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripherals_list.$peripheral_id}</a><br/>
{/if}{/foreach}
				{if !$peripheral_id}<font class="light_text">[None]</font>{/if}
			</td>
		</tr>
		</table>
		<p/>
	{elseif $filter.customer_id < 0}
	
		<h2>{if $filter.customer_id==-1}Servers{else}Workstations{/if} without warranties or serial numbers</h2>
		<p/>
		
		<table class="list" width="100%">
			<thead>
			<tr>
				<td>{if $filter.customer_id==-1}Server{else}Workstation{/if}</td>
				<td>Customer</td>
				<td>Warranty start</td>
				<td>Warranty end</td>
				<td>SN</td>
			</tr>
			</thead>
			
			{foreach from=$missing_warranties_sn item=missing}
			<tr>
				{assign var="warranties" value=$missing->warranties}
				{if count($warranties)>0} {assign var="has_warranties" value=true}
				{else} {assign var="has_warranties" value=false}
				{/if}
				<td {if $has_warranties} rowspan="{$warranties|@count}" {/if}>
                    {assign var="p" value="id:"|cat:$missing->id}
                    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}"
					>#{$missing->id}: {$missing->computer_name|escape}</a>
				</td>
				<td {if $has_warranties} rowspan="{$warranties|@count}" {/if}>
					#{$missing->customer_id}: {$missing->customer_name|escape}
				</td>
				
				{if $has_warranties}
					{foreach from=$warranties item=warranty name=loop_warranty}
						{if !$smarty.foreach.loop_warranty.first} <tr> {/if}
						<td>
							{if $warranty->warranty_starts}{$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
							{else}--
							{/if}
						</td>
						<td>
							{if $warranty->warranty_ends}{$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
							{else}--
							{/if}
						</td>
						<td>
							{if $warranty->sn}{$warranty->sn|escape}
							{else}--
							{/if}
						</td>
						</tr>
					{/foreach}
				{else}
					<td class="light_text" colspan="3">[No warranties information]</td>
				{/if}
			</tr>
			{foreachelse}
			{/foreach}
		</table>
	
	{else}
		<h2>Warranties Summaries</h2>
		<p/>
		<font class="light_text">[No customer selected]</font>
	{/if}
</div>


<div id="tab_details" class="tab_content" style="display:none;">
{if $customer->id}
<h2>Computers Warranties</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="19%">Computer</td>
		<td width="20%">Product</td>
		<td width="20%">Serial number</td>
		<td width="30%">Contract</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$computers_warranties item=warranty}
{assign var="computer_id" value=$warranty->id}
<tr>
<td>{$computer_id}</td>
<td>{$computers_list.$computer_id}</td>
<td>{$warranty->product}</td>
<td>{$warranty->sn}</td>
<td>
{if $warranty->warranty_starts or $warranty->warranty_ends}
Interval: {if $warranty->warranty_starts}{$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if} to
{if $warranty->warranty_ends}{$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if}<br/>
{/if}
{if $warranty->service_package_id or $warranty->service_level_id}
{assign var="service_package_id" value=$warranty->service_package_id}
{assign var="service_level_id" value=$warranty->service_level_id}
Service level: {$service_packages_list.$service_package_id}, {$service_levels_list.$service_level_id}<br/>
{/if}
{if $warranty->contract_number}
Contract: {$warranty->contract_number|escape}<br/>
{/if}
{if $warranty->hw_product_id}
HW product ID: {$warranty->hw_product_id}<br/>
{/if}
{if $warranty->product_number}
Product number: {$warrany->product_number}<br/>
{/if}
</td>
<td align="right">
    {assign var="p" value="computer_id:"|cat:$computer_id|cat:",item_id:"|cat:$warranty_item_id|cat:",ret:"|cat:"manage_warranties"}
    <a href="{'kawacs'|get_link:'computer_edit_item':$p:'template'}">Edit</a></td>
</tr>
	{foreachelse}
	<tr>
		<td colspan="7">[No computer warranties]</td>
	</tr>
	{/foreach}
</table>
<p/>

<h2>AD Printers Warranties</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="40%">Name</td>
		<td width="20%">Serial number</td>
		<td width="30%">Contract</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
{foreach from=$ad_printers_warranties item=warranty}
{assign var="canonical_name" value=$warranty->canonical_name}
<tr>
<td>{$ad_printers_list.$canonical_name}</td>
<td>{$warranty->sn}</td>
<td>
{if $warranty->warranty_starts or $warranty->warranty_ends}
Interval: {if $warranty->warranty_starts} {$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if} to
{if $warranty->warranty_ends} {$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if}<br/>
{/if}
{if $warranty->service_package_id or $warranty->service_level_id}
{assign var="service_package_id" value=$warranty->service_package_id}
{assign var="service_level_id" value=$warranty->service_level_id}
Service level: {$service_packages_list.$service_package_id}, {$service_levels_list.$service_level_id}<br/>
{/if}
{if $warranty->contract_number}
Contract: {$warranty->contract_number|escape}<br/>
{/if}
{if $warranty->hw_product_id}
HW product ID: {$warranty->hw_product_id}<br/>
{/if}
{if $warranty->product_number}
Product number: {$warranty->product_number}<br/>
{/if}
</td>
<td align="right">
    {assign var="p" value="canonical_name:"|cat:$canonical_name|urlencode|cat:",ret:"|cat:"manage_warranties"}
    <a href="{'kerm'|get_link:'ad_printer_warranty_edit':$p:'template'}">Edit</a></td>
</tr>
	{foreachelse}
	<tr>
		<td colspan="7">[No peripheral warranties]</td>
	</tr>
	{/foreach}
</table>


<h2>Peripherals Warranties</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="50%">Name</td>
		<td width="20%">Serial number</td>
		<td width="10%">Starts</td>
		<td width="10%">Ends</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
{assign var="last_class" value=""}
{foreach from=$peripherals_warranties item=warranty}
{assign var="class_id" value=$warranty->id2}
{assign var="peripheral_id" value=$warranty->id}
{if $class_id != $last_class}
<tr>
	{assign var="last_class" value=$class_id}
	<td colspan="5"><b>{$peripherals_classes_list.$class_id}</b></td>
</tr>
{/if}

<tr>
	<td>{$peripherals_list.$peripheral_id}</td>
	<td>{$warranty->sn}</td>
	<td>{if $warranty->warranty_starts}{$warranty->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if}</td>
	<td>{if $warranty->warranty_ends}{$warranty->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}{else}-{/if}</td>
	<td align="right">
        {assign var="p" value="id:"|cat:$peripheral_id|cat:",ret:"|cat:"manage_warranties"}
        <a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">Edit</a></td>
</tr>
{foreachelse}
<tr><td colspan="5">[No peripheral warranties]</td></tr>
{/foreach}
</table>
{/if}
</div>
<p/>
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA

// Check what was the last selected tab, if any
if (!(last_tab = getCookie('warranties_view_tab'))) last_tab = tabs[0];
showTab (last_tab, true);

// We are using Javascript to set the titles in order to minimalize the size of the HTML document

chart_tables = new Array ('tbl_computers_chart', 'tbl_ad_printers_chart', 'tbl_peripherals_chart')
months_names = new Array (new Array(''), new Array(''), new Array(''));
cnt = 1;
{foreach from=$computers_warranties_months item=month}
months_names[0][cnt++]="{$month->month_str}";
{/foreach}
cnt = 1;
{foreach from=$ad_printers_warranties_months item=month}
months_names[1][cnt++]="{$month->month_str}";
{/foreach}
cnt = 1;
{foreach from=$peripherals_warranties_months item=month}
months_names[2][cnt++]="{$month->month_str}";
{/foreach}


{literal}

if (document.childNodes)
{
	for (idx=0; idx<chart_tables.length; idx++)
	{
		elm = document.getElementById (chart_tables[idx]);
		
		if (elm)
		{
			for (i=0; i<elm.childNodes.length; i++)
			{
			if (elm.childNodes[i].nodeName == 'TBODY')
			{
				for (j=0; j<elm.childNodes[i].childNodes.length; j++)
				{
				if (elm.childNodes[i].childNodes[j].nodeName == 'TR')
				{
					elm_row = elm.childNodes[i].childNodes[j];
					col_idx = 0;
					for (k=1; k<elm_row.childNodes.length; k++)
					{
					if (elm_row.childNodes[k].nodeName == 'TD')
					{
						elm_row.childNodes[k].title = months_names[idx][col_idx++];
					}
					}
				}
				}
			}
			}
		}
	}
}
{/literal}

//]]>
</script>
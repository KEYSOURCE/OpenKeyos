{assign var="paging_titles" value="KAWACS, KAWACS Console"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<script language="javascript" src=/javascript/ajax.js" type="tex/javascript"></script>

<script language="javascript" type="tex/javascript">
//<![CDATA[
var comp_ids = new Array ();
cnt = 0;

comp_ids={$clist};
var time = null;
timer = setTimeout('doReload()', {$filter.reload_seconds}000);
var returl = '{$ret_url|urldecode}';

{literal}
function showInfo (comp_id, e)
{
	var tg = (window.event) ? e.srcElement : e.target;

	if (tg.id != 'div_comp_'+comp_id) return;
	var reltg = (e.relatedTarget) ? e.relatedTarget : e.toElement;
	while (reltg != tg && reltg.nodeName != 'BODY') reltg = reltg.parentNode

	if (reltg.nodeName == 'BODY')
	{
		// Mouseout took place when mouse actually left layer
		elm = document.getElementById('div_comp_'+comp_id);
		elm.style.display = 'none';
	}
}

function hideAllInfos ()
{
	for (i=0; i<comp_ids.length; i++){
        document.getElementById('div_comp_'+comp_ids[i]).style.display = 'none';
    }
}

function doReload ()
{
	window.location = returl;
}

{/literal}
//]]>
</script>

<form action="" method="POST" name="filter">
{$form_redir}
<input type="hidden" name="go" value="">
<input type="hidden" name="order_by_bk" value="{$filter.order_by}">
<input type="hidden" name="order_dir_bk" value="{$filter.order_dir}">

<h1 style="width: 98%; padding: 0px;">
<table width="100%"><tr>
	<td style="width: 400px; padding: 0px;"><h1 style="border:0px; margin:0px; padding: 0px;">KAWACS Console</h1></td>
	<td style="text-align:right; white-space:nowrap; vertical-align: bottom;">
		Reload every:
		<select name="filter[reload_seconds]" onchange="document.forms['filter'].submit();">
			<option value="30" {if $filter.reload_seconds==30}selected{/if}>30 sec.</option>
			<option value="60" {if $filter.reload_seconds==60}selected{/if}>1 min.</option>
			<option value="120" {if $filter.reload_seconds==120}selected{/if}>2 min.</option>
			<option value="300" {if $filter.reload_seconds==300}selected{/if}>5 min.</option>
		</select>
	</td>
</tr></table>
</h1>
<p class="error">{$error_msg}</p>

<script language="JavaScript">

{literal}
// Shows or hides the details for the extra DIVs (connections down, internet contracts etc.)
function rollDetails (s, force_hide)
{
	elm_list = document.getElementById ('list_'+s);
	elm_pre_list = document.getElementById ('pre_list_'+s);
	elm_link = document.getElementById ('link_'+s);
	if (elm_list.style.display == '' || force_hide)
	{
		elm_list.style.display = 'none';
		elm_pre_list.style.borderBottomWidth = '1px';
		elm_link.removeChild (elm_link.firstChild);
		elm_link.appendChild (document.createTextNode('Show'));
		document.cookie = 'console_'+s+'_hide=yes';
	}
	else
	{
		elm_list.style.display = '';
		elm_pre_list.style.borderBottomWidth = '0px';
		elm_link.removeChild (elm_link.firstChild);
		elm_link.appendChild (document.createTextNode('Hide'));
		document.cookie = 'console_'+s+'_hide=no';
	}
	return false;
}

function roll_computers_list(anchor,list)
{
	var an_elem = document.getElementById(anchor);
	var list_elem = document.getElementById(list);
	if(list_elem.style.display != 'none')
	{
		list_elem.style.display = 'none';
		an_elem.innerHTML = "<b>Show</b>";
	}
	else{
		list_elem.style.display = 'block';
		an_elem.innerHTML = "<b>Hide</b>";
	}

}

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

{/literal}

</script>

{capture name="main_peripherals_notifs"}
{if $has_peripherals_notifs}
<div class="BostonPostCard">
<table class="pre_list" id="pre_list_peripherals_notifs"><tr>
	<td width="50%"><h3>Peripherals Alerts [{$peripherals_notifs|@count}]</h3></td>
	<td align="right">
		<a href="#" onclick="return rollDetails('peripherals_notifs');" id="link_peripherals_notifs">Hide</a> |
		<a href="{'kawacs'|get_link:'manage_peripherals'}">Details &#0187;</a>
	</td>
</tr></table>
<table class="list border_box" style="width: 100%" id="list_peripherals_notifs">
	<thead>
	<tr>
		<td>Peripheral</td>
		<td width="30">Raised</td>
	</tr>
	</thead>

	{foreach from=$peripherals_notifs item=peripheral_notif}
	<tr id="periph_notif_{$peripheral_notif_id}"
	{if $peripheral_notif->is_unread($current_user->id)}
		class="unread"
		ondblclick="markNotifsRead ({$current_user->id}, '{$peripheral_notif->id}', 'periph_notif_{$peripheral_notif_id}');"
	{/if}
	>
		{assign var="notif_color" value=$peripheral_notif->level}
		<td><a href="http://{$peripheral_notif->object_url}">{$peripheral_notif->object_name}</a></td>
		<td nowrap="nowrap"
			style="font-weight:bold; background-color:white; {if is_numeric($notif_color)} color:{$ALERT_COLORS.$notif_color}{/if}"
		>{$peripheral_notif->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
	{/foreach}
</table>
</div>
<script language="JavaScript">
	if (getCookie('console_peripherals_notifs_hide')=='yes') rollDetails ('peripherals_notifs', true);
</script>
{/if}
{/capture}

{capture name="main_connections_down"}
{if $has_connections_down}
<div class="BostonPostCard">
<table class="pre_list" id="pre_list_connections_down"><tr>
	<td width="50%"><h3>Internet Connections Down [{$connections_down|@count}]</h3></td>
	<td align="right">
		<a href="#" onclick="return rollDetails('connections_down');" id="link_connections_down">Hide</a> |
		<a href="{'kawacs'|get_link:'manage_monitored_ips'}">Details &#0187;</a>
	</td>
</tr></table>
<table class="list border_box" style="width: 100%" id="list_connections_down">
	<thead>
	<tr>
		<td width="100">Monitored&nbsp;IP</td>
		<td>Customer</td>
		<td>Raised</td>
	</tr>
	</thead>

	{foreach from=$connections_down item=monitored_ip}
	<tr id="connection_{$customer_id}"
	{if $monitored_ip->notification->is_unread($current_user->id)}
		class="unread"
		ondblclick="markNotifsRead ({$current_user->id}, '{$monitored_ip->notification->id}', 'connection_{$customer_id}');"
	{/if}
	>
		<td>
            {assign var="p" value='id:'|cat:$monitored_ip->id|cat:',returl:'|cat:$ret_url}
            <a href="{'kawacs'|get_link:'monitored_ip_edit':$p:'template'}" class="error">{$monitored_ip->remote_ip}</a></td>
		<td>
            {assign var="p" value='id:'|cat:$monitored_ip->customer_id}
		    <a href="{'customer'|get_link:'customer_edit':$p:'template'}">#{$monitored_ip->customer->id}: {$monitored_ip->customer->name|escape}</a>
		</td>
		{*<td nowrap="nowrap">{$monitored_ip->get_computers()|@count} computers</td>*}
		<td nowrap="nowrap">{$monitored_ip->notification->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
	{/foreach}
</table>
</div>
<script language="JavaScript">
	if (getCookie('console_connections_down_hide')=='yes') rollDetails ('connections_down', true);
</script>
{/if}
{/capture}

{capture name="main_expired_contracts"}
{if $has_expired_contracts}
<div class="BostonPostCard">
<table class="pre_list" id="pre_list_expired_contracts"><tr>
	<td width="50%"><h3>Expiring Internet Contracts [{$expiring_internet_contracts|@count}]</h3></td>
	<td nowrap="nowrap" align="right">
		<a href="#" onclick="return rollDetails('expired_contracts');" id="link_expired_contracts">Hide</a> |
		<a href="{'klara'|get_link:'manage_customer_internet_contracts'}">Details &#0187;</a>
	</td>
</table>
<table class="list border_box" style="width: 100%;" id="list_expired_contracts">
	<thead>
	<tr>
		<td width="60">Expires</td>
		<td>Customer</td>
		<td>Provider/Contract</td>
	</tr>
	</thead>

	{foreach from=$expiring_internet_contracts item=contract}
	<tr id="contract_{$customer_id}"
	{if $contract->notification and $contract->notification->is_unread($current_user->id)}
		class="unread"
		ondblclick="markNotifsRead ({$current_user->id}, '{$contract->notification->id}', 'contract_{$customer_id}');"
	{/if}
	>
		<td nowrap="nowrap">
            {assign var="p" value='id:'|cat:$contract->id|cat:',returl:'|cat:$ret_url}
			<a href="{'klara'|get_link:'customer_internet_contract_edit':$p:'template'}">{$contract->end_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}</a>
		</td>
		<td>
			{assign var="customer_id" value=$contract->customer_id}
            {assign var="p" value='id:'|cat:$customer_id}
			<a href="{'customer'|get_link:'customer_edit':$p:'template'}">#{$customer_id}: {$customers.$customer_id}</a>
		</td>
		<td>{$contract->get_name()|escape}</td>
	</tr>
	{/foreach}
</table>
</div>
<script language="JavaScript">
	if (getCookie('console_expired_contracts_hide')=='yes') rollDetails ('expired_contracts', true);
</script>
{/if}
{/capture}

{capture name="main_unassigned_disc_notifs"}
{if $has_unassigned_disc_notifs}
<div class="BostonPostCard">
<table class="pre_list" id="pre_list_unassigned_disc_notifs"><tr>
	<td width="50%"><h3>Unmatched Discoveries [{$unassigned_disc_notifs|@count}]</h2></td>
	<td nowrap="nowrap" align="right">
		<a href="#" onclick="return rollDetails('unassigned_disc_notifs');" id="link_unassigned_disc_notifs">Hide</a> |
		<a href="{'discovery'|get_link:'manage_discoveries'}">Details &#0187;</a>
	</td>
</table>
<table class="list border_box" width="100%" id="list_unassigned_disc_notifs">
	<thead>
	<tr>
		<td>Customer</td>
	</tr>
	</thead>

	{foreach from=$unassigned_disc_notifs item=disc_notif}
		{assign var="customer_id" value=$disc_notif->object_id}
		{assign var="has_unread_discs" value=false}
		{if $disc_notif->is_unread($current_user->id)}
			{assign var="has_unread_discs" value=true}
		{/if}
		<tr id="discs_{$disc_notif->id}"
			{if $has_unread_discs}
				class="unread"
				ondblclick="markNotifsRead ({$current_user->id}, '{$disc_notif->id}', 'discs_{$disc_notif->id}');"
			{/if}
		>
			<td>
                {assign var="p" value="customer_id:"|cat:$disc_notif->object_id}
				<a href="{'discovery'|get_link:'manage_discoveries':$p:'template'}">#{$customer_id}:&nbsp;{$customers_all.$customer_id}</a>
			</td>
		</tr>
	{/foreach}
</table>
</div>
<script language="JavaScript">
	if (getCookie('console_unassigned_disc_notifs_hide')=='yes') rollDetails ('unassigned_disc_notifs', true);
</script>
{/if}
{/capture}

{capture name="main_exceeded_licenses"}
{if $has_exceeded_licenses}
<div class="BostonPostCard">
<table class="pre_list" id="pre_list_exceeded_licenses"><tr>
	<td width="50%"><h3>Exceeded Licenses [{$exceeded_lic_notifs|@count}]</h2></td>
	<td nowrap="nowrap" align="right">
		<a href="#" onclick="return rollDetails('exceeded_licenses');" id="link_exceeded_licenses">Hide</a> |
		<a href="{'kalm'|get_link:'exceeded_licenses'}">Details &#0187;</a>
	</td>
</table>
<table class="list border_box" width="100%" id="list_exceeded_licenses">
	<thead>
	<tr>
		<td>Customer</td>
		<td>Software</td>
	</tr>
	</thead>

	{foreach from=$exceeded_lic_notifs key=customer_id item=licenses}
	{assign var="has_unread_lics" value=false}
	{assign var="unread_ids" value=""}
	{foreach from=$licenses item=license}
		{if $license->notification and $license->notification->is_unread($current_user->id)}
			{assign var="has_unread_lics" value=true}
			{assign var="id" value=$license->notification->id}
			{assign var="unread_ids" value="$unread_ids $id"}
		{/if}
	{/foreach}
	<tr id="lics_{$customer_id}"
		{if $has_unread_lics}
			class="unread"
			ondblclick="markNotifsRead ({$current_user->id}, '{$unread_ids}', 'lics_{$customer_id}');"
		{/if}
	>
		<td>
            {assign var="p" value="customer_id:"|cat:$customer_id}
			<a href="{'kalm'|get_link:'manage_licenses':$p:'template'}">#{$customer_id}:&nbsp;{$customers_all.$customer_id}</a>
		</td>
		<td>
			{foreach from=$licenses item=license name="licenses"}
			{$license->software->name}{if !$smarty.foreach.licenses.last}, {/if}
			{/foreach}
		</td>
	</tr>
	{/foreach}
</table>
</div>
<script language="JavaScript">
	if (getCookie('console_exceeded_licenses_hide')=='yes') rollDetails ('exceeded_licenses', true);
</script>
{/if}
{/capture}

{if $cnt_extra_divs > 0}
	{assign var="cnt" value=1}
	<table width="98%"><tr>
		{if $has_peripherals_notifs}
			<td width="{$extra_divs_width}%"
			style="{if $cnt==1}padding-left:0px;{/if}; padding-right:{if $cnt++ < $cnt_extra_divs}1{/if}0px;">
				{$smarty.capture.main_peripherals_notifs}
			</td>
		{/if}
		{if $has_connections_down}
			<td width="{$extra_divs_width}%"
			style="{if $cnt==1}padding-left:0px;{/if}; padding-right:{if $cnt++ < $cnt_extra_divs}1{/if}0px;">
				{$smarty.capture.main_connections_down}
			</td>
		{/if}
		{if $has_expired_contracts}
			<td width="{$extra_divs_width}%"
			style="{if $cnt==1}padding-left:0px;{/if}; padding-right:{if $cnt++ < $cnt_extra_divs}1{/if}0px;">
				{$smarty.capture.main_expired_contracts}
			</td>
		{/if}
		{if $has_unassigned_disc_notifs}
			<td width="{$extra_divs_width}%"
			style="{if $cnt==1}padding-left:0px;{/if}; padding-right:{if $cnt++ < $cnt_extra_divs}1{/if}0px;">
				{$smarty.capture.main_unassigned_disc_notifs}
			</td>
		{/if}
		{if $has_exceeded_licenses}
			<td width="{$extra_divs_width}%"
			style="{if $cnt==1}padding-left:0px;{/if}; padding-right:{if $cnt++ < $cnt_extra_divs}1{/if}0px;">
				{$smarty.capture.main_exceeded_licenses}
			</td>
		{/if}
	</tr></table>
	<p/>
{/if}

<table class="pre_list" style="width: 98%;">
<tr>
	<td><h3>Computers</h3></td>
	<td align="right" nowrap="nowrap"> Account Manager:
		<select name="filter[account_manager]" onchange="document.forms['filter'].submit();">
			<option value="">[All]</option>
			{html_options options=$ACCOUNT_MANAGERS selected=$filter.account_manager}
		</select>
	</td>
	<td align="right" nowrap="nowrap"> Alert acknowledge
		<select name="filter[show_in_console]" onchange="document.forms['filter'].submit();">
			<option value="-1">[All]</option>
			<option value="1" {if $filter.show_in_console==1}selected="selected"{/if}>Not Acknowledged</option>
			<option value="0" {if $filter.show_in_console==0}selected="selected"{/if}>Acknowledged</option>
		</select>
	</td>
	<td align="right" nowrap="nowrap">
		Per page:
		<select name="filter[limit]" onChange="document.forms['filter'].submit();">
			{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
		</select>

		{if $tot_computers > $filter.limit}
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

			{if $filter.start > 0}
				<a href="{'kawacs'|get_link:'kawacs_console_submit'}" onClick="document.forms['filter'].elements['go'].value='prev'; document.forms['filter'].submit(); return false;"
				>&#0171; Previous</a>
			{else}
				<font class="light_text">&#0171; Previous</font>
			{/if}

			<select name="filter[start]" onChange="document.forms['filter'].submit()">
				{html_options options=$pages selected=$filter.start}
			</select>

			{if $filter.start + $filter.limit < $tot_computers}
				<a href="{'kawacs'|get_link:'kawacs_console_submit'}" onClick="document.forms['filter'].elements['go'].value='next'; document.forms['filter'].submit(); return false;"
				>Next &#0187;</a>
			{else}
				<font class="light_text">Next &#0187;</font>
			{/if}
		{/if}
	</td>
</tr>
</table>

<table class="list border_box" style="width: 98%;">
	<thead>
	<tr>
		<td class="sort_text" style="width: 1%; white-space: no-wrap;" colspan="2">{strip}
            {if $filter.order_by=='id' and $filter.order_dir=='ASC'}{assign var="id_sort" value='DESC'}{else}{assign var='id_sort' value='ASC'}{/if}
            {assign var="p" value="order_by:"|cat:"id"|cat:",order_dir:"|cat:$id_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
			>ID</a>{if $filter.order_by=='id'}&nbsp;<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
		{/strip}</td>

		<td nowrap="nowrap" class="sort_text" style="width:20%">
            {if $filter.order_by=='netbios_name' and $filter.order_dir=='ASC'}{assign var="netbios_name_sort" value="DESC"}{else}{assign var="netbios_name_sort" value="ASC"}{/if}
            {assign var="p" value="order_by:"|cat:"netbios_name"|cat:",order_dir:"|cat:$netbios_name_sort}
			<a href="{$sort_url|add_extra_get_params:$p:'template'}"
			>Name&nbsp;{if $filter.order_by=='netbios_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6"
			>{/if}</a>
		</td>

		<td nowrap class="sort_text">
            {if $filter.order_by=='customer' and $filter.order_dir=='ASC'}{assign var="customer_sort" value='DESC'}{else}{assign var="customer_sort" value='ASC'}{/if}
            {assign var="p" value="order_by:"|cat:"customer"|cat:",order_dir:"|cat:$customer_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
			>Customer</a>
			{if $filter.order_by=='customer'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}
		</td>

		<td nowrap class="sort_text" style="width:80px; min-width:80px;">
            {if $filter.order_by=='last_contact' and $filter.order_dir=='ASC'}{assign var="last_contact_sort" value='DESC'}{else}{assign var="last_contact_sort" value='ASC'}{/if}
            {assign var="p" value="order_by:"|cat:"last_contact"|cat:",order_dir:"|cat:$last_contact_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
			>Last contact</a>
			{if $filter.order_by=='last_contact'}
			<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
			{/if}
		</td>

		<td class="sort_text" style="width: auto">
            {if $filter.order_by=='alert' and $filter.order_dir=='ASC'}{assign var="alert_sort" value='DESC'}{else}{assign var="alert_sort" value='ASC'}{/if}
            {assign var="p" value="order_by:"|cat:"alert"|cat:",order_dir:"|cat:$alert_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
			style="display: inline;">Alert level {if $filter.order_by=='alert'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
		</td>

		<td class="sort_text" style="width: auto">
            {if $filter.order_by=='alert_raised' and $filter.order_dir=='ASC'}{assign var="alert_raised_sort" value='DESC'}{else}{assign var="alert_raised_sort" value='ASC'}{/if}
            {assign var="p" value="order_by:"|cat:"alert_raised"|cat:",order_dir:"|cat:$alert_raised_sort}
            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
			style="display: inline;">Alert raised {if $filter.order_by=='alert_raised'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>

	</thead>



	<tr>
		{assign var="ft" value=0}
		<td colspan="9">
			<div class="BostonPostCard">
			<a href="#" style="float:right;" id="anchor_{$ft}" onclick="roll_computers_list('anchor_{$ft}','list_servers_{$ft}')"><b>Hide</b><p /></a>
			<table id="list_servers_{$ft}" class="list border_box" width="100%">

	{assign var="last_type" value=""}
	{foreach from=$computers item=computer}
		{assign var="computer_type" value=$computer->type}
        {assign var="gpar" value="id:"|cat:$computer->id}
        {assign var="mpe_par" value="id:"|cat:$computer->profile_id}
        {assign var="cuvw_par" value="id:"|cat:$computer->customer_id}


		{if $computer->type != $last_type}
		{if $last_type != ""}
			{assign var="ft" value=$ft+1}
			</table></div></td></tr>
			<tr><td colspan="9">
			<div class="BostonPostCard">
			<a href="#" style="float:right;" id="anchor_{$ft}" onclick="roll_computers_list('anchor_{$ft}','list_servers_{$ft}')"><b>Show</b><p /></a>
			<table id="list_servers_{$ft}" class="list border_box" width="100%" style="display: none;">
		{/if}
		<tr class="cathead">

			<td colspan="8" class="cathead" style="padding-left: 0px;">{$COMP_TYPE_NAMES.$computer_type}</td>
			{assign var="last_type" value=$computer_type}
			<td class="cathead" style="padding-left: 0px;"><input type="submit" value="Acknowledge" name="clear"></td>
		</tr>
		{/if}

		<tr>
			<td style="width: 1%; white-space: no-wrap; padding-right: 4px;"
			{if count($computer->notifications)>1}rowspan="{$computer->notifications|@count}"{/if}
			>
            <a href="{'kawacs'|get_link:'computer_view':$gpar:'template'}" {if $computer->contact_lost}class="error"{/if}>{$computer->id}</a></td>
			{assign var="alert_color" value=$computer->alert}
			<td style="width: 1%; padding-left: 4px; padding-right: 4px; text-align: right;"
			{if count($computer->notifications)>1}rowspan="{$computer->notifications|@count}"{/if}
			>{if is_numeric($alert_color)}<img src="/images/logo_icon_16.gif" style="background: {$ALERT_COLORS.$alert_color}" width="16" height="16">{/if}</td>

			<td {if count($computer->notifications)>1}rowspan="{$computer->notifications|@count}"{/if}>
				<!-- The extra information to show about computers -->
				<div id="div_comp_{$computer->id}" class="info_box" style="display:none; width:500px; margin-top:-2px; margin-left-2px; "
				sonmouseout="document.getElementById('div_comp_{$computer->id}').style.display='none';"
				onmouseout="showInfo({$computer->id}, event);">
				<table width="100%" class="grid">
					<thead>
					<tr><td colspan="2">
                            <a href="{'kawacs'|get_link:'computer_view':$gpar:'template'}">#{$computer->id}: {$computer->netbios_name|escape}</a></td></tr>
					</thead>
					<tr><td width="100">Profile:</td>
					{assign var="profile_id" value=$computer->profile_id}

					<td><a href="{'kawacs'|get_link:'monitor_profile_edit':$mpe_par}">{$profiles.$profile_id}</a></td>
					</tr>
					{if $computer->roles}
						<tr><td>Roles:</td>
						<td>{foreach from=$computer->roles key=role_id item=role_name name=computer_roles}
							{$role_name}{if !$smarty.foreach.computer_roles.last}, {/if}{/foreach}</td></tr>
					{/if}
					{if $computer->comments}
						<tr><td>Comments:</td>
						<td>{$computer->comments|escape|nl2br}</td></tr>
					{/if}

					<tr><td>Computer brand:</td>
					<td>{$computer->get_item('computer_brand')|escape}
					{$computer->get_item('computer_model')}</td></tr>

					<tr><td>OS:</td><td>{$computer->get_item('os_name')}</td></tr>
					<tr><td>Serial number:</td>
					{assign var="computer_sn" value=$computer->get_item('computer_sn')}
					<td>{if $computer_sn!='None' and $computer_sn!='null'}{$computer_sn}{else}<font class="light_text">--</font>{/if}</td></tr>

					<tr><td>Current user:</td>
					<td>
						{assign var="login" value=$computer->current_user}
						{if $login}
							{if $ad_users.$login->computer_id}
                                {assign var="auv_par" value="computer_id:"|cat:$ad_users.$login->computer_id|cat:',nrc:'|cat:$ad_users.$login->nrc}
								<a href="{'kerm'|get_link:'ad_user_view':$auv_par:'template'}"
								title="{$ad_users.$login->display_name}, {$ad_users.$login->email}"
								>{$login}</a><br/>
								<b>{$ad_users.$login->display_name}</b><br/>
								<a href="mailto:{$ad_users.$login->email}">{$ad_users.$login->email}</a>
							{else}{$login}{/if}
						{else}<font class="light_text">--</font>{/if}
					</td></tr>

					<tr><td>Tickets:</td>
					<td>
						{if $computer->tickets}
						<ul style="margin-top:0px; margin-bottom:4px;">
						{foreach from=$computer->tickets item=ticket name=computer_tickets}
							<li><a href="{'krifs'|get_link:'ticket_edit':'id:'|cat:$ticket->id}:'template'">Ticket #{$ticket->id}</a>: {$ticket->subject|escape}<br/>
							{assign var="status" value=$ticket->status}
							<b>Status:</b> {$TICKET_STATUSES.$status} &nbsp;&nbsp;&nbsp;
							{assign var="assigned_id" value=$ticket->assigned_id}
							<b>Assigned to:</b> {$users_list.$assigned_id}
							</li>
						{/foreach}
						</ul>
						{else}<font class="light_text">--</font>{/if}
					</td>
					</tr>
				</table>
				[ <a href="#" onclick="document.getElementById('div_comp_{$computer->id}').style.display='none'; return false;">Close</a> ]
				</div>

				<a href="{'kawacs'|get_link:'computer_view':$gpar:'template'}" {if $computer->contact_lost}style="color:red;"{/if}
				onmouseover="hideAllInfos(); document.getElementById('div_comp_{$computer->id}').style.display='';"
				><b>{$computer->netbios_name|escape}</b></a>
			</td>

			<td {if $computer->contact_lost}class="error"{/if} {if count($computer->notifications)>1}rowspan="{$computer->notifications|@count}"{/if}>
				{assign var="customer_id" value=$computer->customer_id}
				<a href="{'customer'|get_link:'customer_edit':$cuvw_par:'template'}">#{$customer_id}: {$customers.$customer_id}</a>
			</td>

			<td style="width:100px; white-space:nowrap;" {if count($computer->notifications)>1}rowspan="{$computer->notifications|@count}"{/if}
			>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>

			{* Don't forget that these notifications are just generic objects, not actual Notification objects *}
			{foreach from=$computer->notifications item=notif name=computer_notifs}
				{if !$smarty.foreach.computer_notifs.first}<tr>{/if}
				{assign var="notif_color" value=$notif->level}
				<td><ul style="margin-top:0px; margin-bottom:0px; margin-left:1.2em; padding-left:0px;">
				<li style="color: {$ALERT_COLORS.$notif_color};" id="comp_notif_{$notif->id}"
				{if in_array($notif->id,$unread_notifs_ids)}
					class="unread"
					ondblclick="markNotifsRead({$current_user->id}, '{$notif->id}', 'comp_notif_{$notif->id}');"
				{/if}



				><font color="black">{$notif->text|escape}</font></li></ul></td>
				<td style="white-space:nowrap;">
                    {assign var="p" value='id:'|cat:$notif->id}
                    <a href="{'home'|get_link:'notification_view':$p:'template'}">{$notif->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a></td>
				<td>
					{if $notif->ticket_id>0}
                    {assign var="p" value='id:'|cat:$notif->ticket_id}
					<a target="_blank" href="{'krifs'|get_link:'ticket_edit':$p:'template'}">Ticket #{$notif->ticket_id} &#0187;</a>
					{else}
                    {assign var="p" value='id:'|cat:$notif->id}
					<a target="_blank" href="{'kawacs'|get_link:'create_notif_ticket_submit':$p:'template'}" onclick="document.forms['filter'].submit();">Create ticket &#0187;</a>
					{/if}
				</td>
				<td><input type='checkbox' name="notif_ids[]" value="{$notif->id}" /></td>
			</tr>
			{/foreach}
	{/foreach}
	</table></div></td></tr>
</table>
<p>

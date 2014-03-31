{assign var="paging_titles" value="KAWACS, Manage Computers"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}
<style type="text/css">
    @import url('/kawacs_slide.css');
</style>
<script language="JavaScript" type="text/javascript" src="/javascript/jquery.min.js"></script>
<script language="JavaScript" type="text/javascript" src="/javascript/highcharts/highcharts.js"></script>
<script language="JavaScript" type="text/javascript" src="/javascript/highcharts/modules/exporting.js"></script>
 <script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
    jQuery.noConflict();
    Highcharts.theme = { colors: ['#75E5A7'] };// prevent errors in default theme
    var highchartsOptions = Highcharts.getOptions();
    $(document).ready(function() {     
        $("a.tab").click(function(){
            $("div#panel").animate({
                height: "500px"
            })
            .animate({
                height: "450px"
            }, "fast");
            document.getElementById('hdr').style.display='none';
            document.getElementById('hide_button').style.display='block';
            var show_tab = $(this).attr('title');
            
            $("#panel_buttons").css('border', '1px solid black');
            $("#"+show_tab).css('display', 'block');
        
        });    
        
       $("div#hide_button").click(function(){
            $("div#panel").animate({
                height: "0px"
            }, "fast");
            document.getElementById('hdr').style.display='block';
            document.getElementById('hide_button').style.display='none';
            $("div.cont").css('display', 'none');
            $("#panel_buttons").css('border', '0px solid black');
       });   
     
        
    });

    function showserverschart(){
        var chart;
        $(document).ready(function() {
           chart = new Highcharts.Chart({
              chart: {
                 renderTo: 'servers_repo',
                 plotBackgroundColor: null,
                 plotBorderWidth: null,
                 plotShadow: false
              },
              title: {
                 text: 'Servers reporting stats'
              },
              tooltip: {
                 formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y;
                 }
              },
              plotOptions: {
                 pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                       enabled: true,
                       color: Highcharts.theme.textColor || '#000000',
                       connectorColor: Highcharts.theme.textColor || '#000000',
                       formatter: function() {
                          return '<b>'+ this.point.name +'</b>: '+ this.y;
                       }
                    }
                 }
              },
               series: [{
               type: 'pie',
               name: 'Servers reporting', 
               data: {/literal}{if $servers_repo!=""}{$servers_repo}{else}[]{/if}{literal}
               }]
           });
        });
    }
    function showwkschart(){
       var chart;
        $(document).ready(function() {
           chart = new Highcharts.Chart({
              chart: {
                 renderTo: 'workstations_repo',
                 plotBackgroundColor: null,
                 plotBorderWidth: null,
                 plotShadow: false
              },
              title: {
                 text: 'Workstations reporting stats'
              },
              tooltip: {
                 formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y;
                 }
              },
              plotOptions: {
                 pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                       enabled: true,
                       color: Highcharts.theme.textColor || '#000000',
                       connectorColor: Highcharts.theme.textColor || '#000000',
                       formatter: function() {
                          return '<b>'+ this.point.name +'</b>: '+ this.y;
                       }
                    }
                 }
              },
               series: [{
               type: 'pie',
               name: 'Workstations reporting', 
               data: {/literal}{if $workstations_repo!=""}{$workstations_repo}{else}[]{/if}{literal}
               }]
           });
        });
    }
    showserverschart();
    showwkschart();
{/literal}
//]]>
</script>
<div id="toppanel">
    <div id="panel_buttons">
    <div id="panel" style="text-align: center;">
        <div id="icd_panel" class="cont" style="text-align: center;">
        {if $connections_down}
        {assign var="current_user_id" value=$current_user->id}
        <h2>Internet Connections Down</h2>
        <table class="list" width="98%">
	        <thead>
	        <tr>
		        <td width="5%"> </td>
		        <td width="15%">Monitored IP</td>
		        <td width="15%">Target IP</td>
		        <td width="35%">Customer</td>
		        <td width="15%">Computers</td>
		        <td width="15%">Raised</td>
	        </tr>
	        </thead>

	        {foreach from=$connections_down item=monitored_ip}
            {assign var="k_te_pars" value="id:"|cat:$monitored_ip->notification->ticket_id}
            {assign var="k_mw_pars" value=$k_te_pars|cat:",returl:"|cat:$ret_url}
            {assign var="k_ta_pars" value="object_id:"|cat:$monitored_ip->id|cat:",object_class:"|cat:$smarty.const.TICKET_OBJ_CLASS_MONITORED_IP|cat:",notification_id:"|cat:$monitored_ip->notification->id|cat:",subject:"|cat:$monitored_ip->notification->get_text()|urlencode|cat:",mark_now_working:"|cat:"1"}
            {assign var="k_mie_pars" value="id:"|cat:$monitored_ip->id|cat:',returl:'|cat:$ret_url}

	        <tr>
		        <td {if $monitored_ip->ticket}rowspan="2"{/if} nowrap="nowrap" style="color:red;">
			        {if $monitored_ip->notification->now_working}
				        <a
				        {if $monitored_ip->notification->now_working.$current_user_id}
                            href="{'krifs'|get_link:'ticket_edit':$k_te_pars:'template'}"
				        {else}
					        href="{'krifs'|get_link:'ticket_mark_working':$k_mw_pars:'template'}"
					        onclick="return confirm('Do you want to mark that you are working now on this issue?');"
				        {/if}
				        ><img src="/images/wavelan-locked-16.gif" width="16" height="16" alt="" title="" border="0"></a>
				        {if $monitored_ip->notification->now_working}
					        {foreach from=$monitored_ip->notification->now_working key=user_id item=since}
						        <br/>{$users_logins_list.$user_id|upper}
					        {/foreach}
				        {/if}
			        {else}
				        <a
				        {if $monitored_ip->notification->ticket_id}
					        href="{'krifs'|get_link:'ticket_mark_working':$k_mw_pars:'template'}"
				        {else}
					        href="{'krifs'|get_link:'ticket_add':$k_ta_pars:'template'}"
				        {/if}

				        onclick="return confirm('Do you want to mark that you are working now on this issue?');"
				        ><img src="/images/wavelan-grey-16.gif" style="background-color: white;" width="16" height="16"
				        alt="Mark working now" title="Mark working now" border="0"></a>
			        {/if}
		        </td>

		        <td><a href="{'kawacs'|get_link:'monitored_ip_edit':$k_mie_pars:'template'}" class="error">{$monitored_ip->remote_ip}</a></td>
		        <td><a href="{'kawacs'|get_link:'monitored_ip_edit':$k_mie_pars:'template'}" class="error">{$monitored_ip->target_ip}</a></td>
		        <td>
                    {assign var="p" value="id:"|cat:$monitored_ip->customer_id}
			        <a href="{'customer'|get_link:'customer_edit':$p:'template'}">#{$monitored_ip->customer->id}: {$monitored_ip->customer->name|escape}</a>
		        </td>
		        <td nowrap="nowrap">{$monitored_ip->get_computers()|@count} computers</td>
		        <td>{$monitored_ip->notification->raised|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	        </tr>
	        {if $monitored_ip->notification->now_working or $monitored_ip->ticket}
	        <tr>
		        <td colspan="5">
			        <ul style="margin-top:0px; margin-bottom:4px;">
				        {assign var="notif_color" value=$monitored_ip->notification->level}
				        <li style="color: {$ALERT_COLORS.$notif_color}; margin-left: -20px; ">
					        <font color="black">
                            {assign var="p" value="id:"|cat:$monitored_ip->ticket->id}
					        <a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">Ticket #{$monitored_ip->ticket->id}: {$monitored_ip->ticket->subject|escape}</a>
					        <br/>
					        {assign var="status" value=$monitored_ip->ticket->status}
					        <b>Status:</b> {$TICKET_STATUSES.$status}
					        &nbsp;&nbsp;&nbsp;

					        {assign var="assigned_id" value=$monitored_ip->ticket->assigned_id}
					        <b>Assigned to:</b> {$users_list.$assigned_id}
				        </li>
			        </ul>
		        </td>
	        </tr>
	        {/if}
	        {/foreach}
        </table>
        <p/>
        {/if}
        </div>
        <div id="rep_panel" class="cont">
                    <div id="servers_repo" style="text-align: left; width: 47%; max-width: 520px; height: 420px; float: left;"></div>   
                    <div id="workstations_repo" style="text-align: left; width: 47%; max-width: 520px; height: 420px; float: right;"></div>                      
        </div>
    </div>
    <ul class="tabs" id="hdr">
        <li><a href="#" title="icd_panel" class="tab">Internet connections down</a></li>
        <li><a href="#" title="rep_panel" class="tab">Reporting stats</a></li>
    </ul> 
    <div class="panel_button" id="hide_button" style="display: none;"><a href="#">Hide</a></div>           
    </div>
</div>
<div id="content">
<h1>Manage Computers</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter">
{$form_redir}

<table width="98%" class="list">
	<tr class="head">
		<td>Customer</td>
		<td>Account Manager</td>
		<td>Type</td>
		<td>Profile</td>
		<td colspan="2" width="10%">Columns</td>
		<td>Per page</td>

		{if $tot_computers > $filter.limit}
			<td>Go to page:</td>
		{/if}

		<td align="right" colspan="2"> </td>
	</tr>
	<tr>
		<td>
			<select name="filter[customer_id]" style="width: 220px;"
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				{html_options options=$COMPUTERS_FILTER_SPECIAL selected=$filter.customer_id}

				{foreach from=$customers item=customer key=id}
					<option value="{$id}" {if $filter.customer_id==$id}selected{/if}>
						{$customer} {if $id!=' '} ({$id}) {/if}
					</option>
				{/foreach}
			</select>
		</td>
		<td>
			<select name="filter[account_manager]" style="width: 100px;" onchange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();">
				<option value="">[All]</option>
				{html_options options=$ACCOUNT_MANAGERS selected=$filter.account_manager}
			</select>
		</td>

		<td>
			<select name="filter[type]" style="width: 100px;">
				<option value="-1">[All]</option>
				{html_options options=$COMP_TYPE_NAMES selected=$filter.type}
			</select>
			<br>
			<input type="checkbox" name="filter[group_by_type]" value="1" {if $filter.group_by_type}checked{/if} class="checkbox">
			Group by type
		</td>

		<td>
			<select name="filter[profile_id]" style="width: 100px;">
				<option value="">[All]</option>
				{html_options options=$profiles selected=$filter.profile_id}
			</select>
		</td>

		<td nowrap>
			<input type="checkbox" name="filter[show_brand]" value=1 {if $filter.show_brand}checked{/if} class="checkbox"> Brand<br/>
			<input type="checkbox" name="filter[show_user]" value=1 {if $filter.show_user}checked{/if} class="checkbox"> User<br/>
			<input type="checkbox" name="filter[show_ad_user]" value=1 {if $filter.show_ad_user}checked{/if} class="checkbox"> AD info
		</td>
		<td nowrap>
			<input type="checkbox" name="filter[show_os]" value=1 {if $filter.show_os}checked{/if} class="checkbox"> OS<br>
			<input type="checkbox" name="filter[show_contact]" value=1 {if $filter.show_contact}checked{/if} class="checkbox"> Contact<br>
			<input type="checkbox" name="filter[show_serial]" value=1 {if $filter.show_serial}checked{/if} class="checkbox"> Serial
		</td>

		<td>
			<select name="filter[limit]" onChange="document.forms['filter'].submit();">
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
			</select>
		</td>

		{if $tot_computers > $filter.limit}
		<td>
			<select name="filter[start]" onChange="document.forms['filter'].submit()">
				{html_options options=$pages selected=$filter.start}
			</select>
		</td>
		{/if}

		<td align="right" style="vertical-align: middle">
			<input type="hidden" name="do_filter_hidden" value="0">
			<input type="submit" name="do_filter" value="Apply filter">
		</td>
	</tr>
</table>
<p>


{if $tot_computers > $filter.limit}
<table width="98%">
	<tr>
		<td width="50%">
			{if $filter.start > 0}
				<a href="{'kawacs'|get_link:'manage_computers_submit'}"
					onClick="document.forms['filter'].elements['go'].value='prev'; document.forms['filter'].submit(); return false;"
				>&#0171; Previous</a>
			{else}
				<font class="light_text">&#0171; Previous</font>
			{/if}
		</td>
		<td width="50%" align="right">
			{if $filter.start + $filter.limit < $tot_computers}
				<a href="{'kawacs'|get_link:'manage_computers_submit'}"
					onClick="document.forms['filter'].elements['go'].value='next'; document.forms['filter'].submit(); return false;"
				>Next &#0187;</a>
			{else}
				<font class="light_text">Next &#0187;</font>
			{/if}
		</td>
	</tr>
</table>
<input type="hidden" name="go" value="">
{/if}
<input type="hidden" name="order_by_bk" value="{$filter.order_by}">
<input type="hidden" name="order_dir_bk" value="{$filter.order_dir}">

{assign var="current_user_id" value=$current_user->id}
{assign var="colspan" value=2}
<!-- Syncronize the columns span depending on the columns selected to be shown -->
<!--
{if $filter.customer_id < 0 }{$colspan++}{/if}
{if $filter.show_user or $filter.show_ad_user}{$colspan++}{/if}
{if $filter.show_brand}{$colspan++}{$colspan++}{/if}
{if $filter.show_os or $filter.show_serial}{$colspan++}{/if}
{if $filter.show_contact}{$colspan++}{/if}
-->

<table class="list" width="98%">

	<tr class="head">
		<td class="sort_text" style="width: 1%; white-space: no-wrap;" colspan="2">{strip}
			<a href="{$sort_url}&order_by=id&order_dir={if $filter.order_by=='id' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>ID</a>{if $filter.order_by=='id'}&nbsp;<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
		{/strip}</td>

		<td nowrap="nowrap" class="sort_text" style="width:20%">
			<a href="{$sort_url}&order_by=netbios_name&order_dir={if $filter.order_by=='netbios_name' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Name&nbsp;{if $filter.order_by=='netbios_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6"
			>{/if}</a>&nbsp;|&nbsp;<a
			href="{$sort_url}&order_by=alert&order_dir={if $filter.order_by=='alert' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
			>Alert&nbsp;{if $filter.order_by=='alert'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
		</td>

		{if $filter.customer_id < 0 }
			{if $filter.customer_id == -1}
				<td nowrap class="sort_text">
                    {if $filter.order_by=='missed_cycles' and $filter.order_dir=='ASC'}{assign var="missed_cycles_sort" value="DESC"}{else}{assign var="missed_cycles_sort" value="ASC"}{/if}
                    {assign var="p" value="order_by:"|cat:"missed_cycles"|cat:",order_dir:"|cat:$missed_cycles_sort}
					<a href="{$sort_url|add_extra_get_params:$p:'template'}"
					>Missed beats</a>
					{if $filter.order_by=='missed_cycles'}
					<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
					{/if}
				</td>
			{/if}

			<td nowrap class="sort_text">
                {if $filter.order_by=='customer' and $filter.order_dir=='ASC'}{assign var="customer_sort" value="DESC"}{else}{assign var="customer_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"customer"|cat:",order_dir:"|cat:$customer_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
				>Customer</a>
				{if $filter.order_by=='customer'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}
			</td>
		{/if}

		{if !$filter.profile_id}
			<td nowrap class="sort_text">
                {if $filter.order_by=='profile' and $filter.order_dir=='ASC'}{assign var="profile_sort" value="DESC"}{else}{assign var="profile_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"profile"|cat:",order_dir:"|cat:$profile_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
				>Monitor profile</a>
				{if $filter.order_by=='profile'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}
			</td>
		{/if}

		{if $filter.show_user or $filter.show_ad_user}
			<td nowrap class="sort_text">
                {if $filter.order_by=='current_user' and $filter.order_dir=='ASC'}{assign var="current_user_sort" value="DESC"}{else}{assign var="current_user_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"current_user"|cat:",order_dir:"|cat:$current_user_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
				>User</a>
				{if $filter.order_by=='current_user'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}
			</td>
		{/if}

		{if $filter.show_brand}
			<td nowrap class="sort_text">
                {if $filter.order_by=='computer_brand' and $filter.order_dir=='ASC'}{assign var="computer_brand_sort" value="DESC"}{else}{assign var="computer_brand_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"computer_brand"|cat:",order_dir:"|cat:$computer_brand_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
				>Brand</a>
				{if $filter.order_by=='computer_brand'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}
			</td>

			<td nowrap class="sort_text">
                {if $filter.order_by=='computer_model' and $filter.order_dir=='ASC'}{assign var="computer_model_sort" value="DESC"}{else}{assign var="computer_model_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"computer_model"|cat:",order_dir:"|cat:$computer_model_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
				>Model</a>
				{if $filter.order_by=='computer_model'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}
			</td>
		{/if}

		{if $filter.show_os or $filter.show_serial}
			<td nowrap class="sort_text">
				{if $filter.show_os and $filter.show_serial}
                    {if $filter.order_by=='os_name' and $filter.order_dir=='ASC'}{assign var="os_name_sort" value="DESC"}{else}{assign var="os_name_sort" value="ASC"}{/if}
                    {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:$os_name_sort}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"
					>OS / Serial number</a>
					{if $filter.order_by=='os_name'}
					<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
					{/if}
				{elseif $filter.show_os}
                    {if $filter.order_by=='os_name' and $filter.order_dir=='ASC'}{assign var="os_name_sort" value="DESC"}{else}{assign var="os_name_sort" value="ASC"}{/if}
                    {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:$os_name_sort}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"
					>OS</a>
					{if $filter.order_by=='os_name'}
					<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
					{/if}
				{else}
                    {if $filter.order_by=='computer_sn' and $filter.order_dir=='ASC'}{assign var="computer_sn_sort" value="DESC"}{else}{assign var="computer_sn_sort" value="ASC"}{/if}
                    {assign var="p" value="order_by:"|cat:"computer_sn"|cat:",order_dir:"|cat:$computer_sn_sort}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"
					>Serial number</a>
					{if $filter.order_by=='computer_sn'}
					<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
					{/if}
				{/if}
			</td>
		{/if}

		{if $filter.show_contact}
			<td nowrap class="sort_text">
                {if $filter.order_by=='last_contact' and $filter.order_dir=='ASC'}{assign var="last_contact_sort" value="DESC"}{else}{assign var="last_contact_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"last_contact"|cat:",order_dir:"|cat:$last_contact_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
				>Last contact</a>
				{if $filter.order_by=='last_contact'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}
			</td>
		{/if}
	</tr>

	{assign var="last_type" value=""}

	{foreach from=$computers item=computer}
		{assign var="computer_type" value=$computer->type}

		{if $filter.group_by_type}
			{if $computer->type != $last_type}
			<tr class="cathead">
				<td colspan="9" class="cathead" style="padding-left: 0px;">{$COMP_TYPE_NAMES.$computer_type}</td>
				{assign var="last_type" value=$computer_type}
			</tr>
			{/if}
		{/if}

		<tr {if count($computer->notifications)>0 or count($computer->tickets)>0} class="no_bottom_border" {/if}>
			<td style="width: 1%; white-space: no-wrap; padding-right: 4px;">
            {assign var="p" value="id:"|cat:$computer->id}
            <a href="{'kawacs'|get_link:'computer_view':$p:'template'}" {if $computer->contact_lost}class="error"{/if}>{$computer->id}</a></td>
			{assign var="alert_color" value=$computer->alert}
			<td style="width: 1%; padding-left: 4px; padding-right: 4px; text-align: right;"
			>{if is_numeric($alert_color)}<img src="/images/logo_icon_16.gif" style="background: {$ALERT_COLORS.$alert_color}" width="16" height="16">{/if}</td>

			<td>
				<a href="{'kawacs'|get_link:'computer_view':$p:'template'}" {if $computer->contact_lost}style="color:red;"{/if}
				><b>{$computer->netbios_name|escape}</b></a>

				{if $computer->roles}
					<br/><b>
					{foreach from=$computer->roles key=role_id item=role_name name=computer_roles}
						{$role_name}{if !$smarty.foreach.computer_roles.last}, {/if}
					{/foreach}
					</b>
				{/if}

				{if $computer->comments}
					<br>{$computer->comments|escape|nl2br}
				{/if}
				{if !$filter.group_by_type}
					<br>
					{assign var="computer_type" value=$computer->type}
					Type:&nbsp;{$COMP_TYPE_NAMES.$computer_type}
				{/if}
				{if $computer->notifications and false}
					<br>
					<b>Notifications:</b>
					{foreach from=$computer->notifications item=notif}
						{assign var="notif_color" value=$notif->level}
						<li style="color: {$ALERT_COLORS.$notif_color}; margin-left: 15px;">
						<font color="black">{$notif->get_text()|escape}</font>
						</li>
					{/foreach}
				{/if}

			</td>

			{if $filter.customer_id < 0 }
				{if $filter.customer_id == -1 }
					<td>
					{$computer->missed_cycles}
					</td>
				{/if}
				<td {if $computer->contact_lost}class="error"{/if}>
					{assign var="customer_id" value=$computer->customer_id}
					{$customers.$customer_id}
				</td>
			{/if}

			{if !$filter.profile_id}
				{assign var="profile_id" value=$computer->profile_id}
				<td {if $computer->notifications}rowspan="2"{/if}>
                    {assign var="p" value="id:"|cat:$computer->profile_id}
                    <a href="{'kawacs'|get_link:'monitor_profile_edit':$p:'template'}">{$profiles.$profile_id}</a>
                </td>
			{/if}

			{if $filter.show_user or $filter.show_ad_user}
				<td {if $computer->notifications}rowspan="2"{/if}>
					{assign var="login" value=$computer->current_user}
					{if $login}
						{if $ad_users.$login->computer_id}
							{assign var="p" value="computer_id:"|cat:$ad_users.$login->computer_id|cat:",nrc:"|cat:$ad_users.$login->nrc}
                            <a href="{'kerm'|get_link:'ad_user_view':$p:'template'}"
							title="{$ad_users.$login->display_name}, {$ad_users.$login->email}"
							>{$login}</a>
							{if $filter.show_ad_user}
								<br/>
								<b>{$ad_users.$login->display_name}</b>
								<br/>
								<a href="mailto:{$ad_users.$login->email}">{$ad_users.$login->email}</a>
							{/if}
						{else}
							{$login}
						{/if}
					{/if}
				</td>
			{/if}

			{if $filter.show_brand}
				<td {if $computer->notifications}rowspan="2"{/if}>{$computer->get_item('computer_brand')}</td>
				<td {if $computer->notifications}rowspan="2"{/if}>{$computer->get_item('computer_model')}</td>
			{/if}

			{if $filter.show_os or $filter.show_serial}
				<td {if $computer->notifications}rowspan="2"{/if}>
					{if $filter.show_os}
						{$computer->get_item('os_name')}
					{/if}
					{if $filter.show_serial}
						{assign var="computer_sn" value=$computer->get_item('computer_sn')}
						{if $computer_sn!='None' and $computer_sn!='null'}
							{if $filter.show_os}<br/>{/if}
							{$computer_sn}
						{/if}
					{/if}
				</td>
			{/if}

			{if $filter.show_contact}
				<td {if $computer->notifications}rowspan="2"{/if}>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			{/if}
		</tr>

		<!-- Show notifications on separate rows, to avoid wraping -->
		{if $computer->notifications or $computer->tickets}
			{foreach from=$computer->notifications item=notif name=computer_notifs}
			<tr {if !$smarty.foreach.computer_notifs.last or $computer->tickets}class="no_bottom_border"{/if}>
				<td colspan="2" style="padding-left: 0px; padding-right: 4px; text-align: right; color: red;">
					{if $notif->now_working}
						<a
						{if $notif->now_working.$current_user_id}
                            {assign var="p" value="id:"|cat:$notif->ticket_id}
							href="{'krifs'|get_link:'ticket_edit':$p:'template'}"
						{else}
                            {assign var="p" value="id:"|cat:$notif->ticket_id|cat:",returl:"|cat:$ret_url}
							href="{'krifs'|get_link:'ticket_mark_working':$p:'template'}"
							onclick="return confirm('Do you want to mark that you are working now on this issue?');"
						{/if}
						><img src="/images/wavelan-locked-16.gif" width="16" height="16" alt="" title="" border="0"></a>
						{foreach from=$notif->now_working key=user_id item=since}
							<br/>{$users_logins_list.$user_id|upper}
						{/foreach}
					{else}

						<a
						{if $notif->ticket_id}
                            {assign var="p" value="id:"|cat:$notif->ticket_id|cat:",returl:"|cat:$ret_url}
                            href="{'krifs'|get_link:'ticket_mark_working':$p:'template'}"
						{else}
                            {assign var="object_id:"|cat:$computer->id|cat:",object_class:"|cat:"1"|cat:",notification_id:"|cat:$notif->id|cat:",subject:"|cat:$notif->get_text()|urlencode|cat:",mark_now_working:"|cat:"1"}
							href="{'krifs'|get_link:'ticket_add':$p:'template'}"
						{/if}

						onclick="return confirm('Do you want to mark that you are working now on this issue?');"
						><img src="/images/wavelan-grey-16.gif" style="background-color: white;" width="16" height="16"
						alt="Mark working now" title="Mark working now" border="0"></a>
					{/if}
				</td>

				<td colspan="{$colspan}">
						{assign var="notif_color" value=$notif->level}
					<ul style="margin-bottom:4px; margin-top: 0px;">
						<li style="color: {$ALERT_COLORS.$notif_color};">
						<font color="black">{$notif->get_text()} - {$notif->raised|date_format:$smarty.const.DATE_FORMAT_SMARTY}
						{if $notif->ticket_id}
							<div style="display:block; border-left:1px solid #dddddd; margin-left:10px; padding: 3px;
							{if $notif->ticket->status==$smarty.const.TICKET_STATUS_CLOSED} color:grey; {/if}"
							>
								{assign var="ticket_id" value=$notif->ticket_id}
                                {assign var="p" value="id:"|cat:$ticket_id}
								<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">Ticket #{$notif->ticket_id}</a>: {$notif->ticket->subject}
								<br/>
								{assign var="status" value=$notif->ticket->status}
								<b>Status:</b> {$TICKET_STATUSES.$status}
								&nbsp;&nbsp;&nbsp;

								{assign var="assigned_id" value=$notif->ticket->assigned_id}
								<b>Assigned to:</b> {$users_list.$assigned_id}
							</div>
						{/if}
						</font>
						</li>
					</ul>
				</td>
			</tr>
			{/foreach}

			{if $computer->tickets}
				<tr class="no_bottom_border">
					<td colspan="2"> </td>
					<td colspan="{$colspan}"><b>Tickets:</b></td>
				</tr>

				{foreach from=$computer->tickets item=ticket name=computer_tickets}
				<tr {if !$smarty.foreach.computer_tickets.last}class="no_bottom_border"{/if}>
					<td colspan="2" style="padding-left: 0px; padding-right: 4px; text-align: right; color: red;">
						{if $ticket->now_working}
							<a
							{if $ticket->now_working.$current_user_id}
                                {assign var="p" value="id:"|cat:$ticket->id}
								href="{'krifs'|get_link:'ticket_edit':$p:'template'}"
							{else}
                                {assign var="p" value="id:"|cat:$ticket->id|cat:",returl:"|cat:$ret_url}
								href="{'krifs'|get_link:'ticket_mark_working':$p:'template'}"
								onclick="return confirm('Do you want to mark that you are working now on this issue?');"
							{/if}
							><img src="/images/wavelan-locked-16.gif" width="16" height="16" alt="" title="" border="0"></a>
							{foreach from=$ticket->now_working key=user_id item=since}
								<br/>{$users_logins_list.$user_id|upper}
							{/foreach}
						{else}
                            {assign var="p" value="id:"|cat:$ticket->id|cat:",returl:"|cat:$ret_url}
                            href="{'krifs'|get_link:'ticket_mark_working':$p:'template'}
							onclick="return confirm('Do you want to mark that you are working now on this issue?');"
							><img src="/images/wavelan-grey-16.gif" style="background-color: white;" width="16" height="16"
							alt="Mark working now" title="Mark working now" border="0"></a>
						{/if}
					</td>
					<td colspan="{$colspan}">
						<ul style="margin-top:0px; margin-bottom:4px;">
							<li>
                                {assign var="p" value="id:"|cat:$ticket->id}
                                <a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">Ticket #{$ticket->id}</a>: {$ticket->subject}
							<br/>
							{assign var="status" value=$ticket->status}
							<b>Status:</b> {$TICKET_STATUSES.$status}
							&nbsp;&nbsp;&nbsp;

							{assign var="assigned_id" value=$ticket->assigned_id}
							<b>Assigned to:</b> {$users_list.$assigned_id}
							</li>
						{/foreach}
						</ul>
					</td>
				</tr>
			{/if}
		{/if}
	{/foreach}

</table>
<p>
</div>

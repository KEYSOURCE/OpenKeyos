{assign var="paging_titles" value="KAWACS, Customer computers"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}
<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
var tabs = new Array ('current', 'old', 'blackout');
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
function showTab (tab_name)
{
	//alert(tab_name);
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
	
	document.cookie = 'customers_computer_count_tab='+tab_name;
	
	return false;
}
{/literal}
//]]>
</script>
<h1>Customer computers</h1>
<p />
<font class="error">{$error_msg}</font>
<p />
<form method="POST" action="" name="filter">
{$form_redir}
<table class="list" width="98%">
	<tr class="head">
		<td>Customer</td>
		<td>Type</td>
		<td>Profile</td>
		<td colspan="2" width="10%">Columns</td>
		<td>Total</td>
		<td>&nbsp;</td>
	</tr>
	<td>
			<select name="filter[customer_id]" style="width: 220px;" 
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				<option value="">[Select customer]</option>
				{foreach from=$customers item=customer key=id}
					<option value="{$id}" {if $filter.customer_id==$id}selected{/if}>
						{$customer} {if $id!=' '} ({$id}) {/if}
					</option>
				{/foreach}
			</select>
		</td>
		
		<td>
			<select name="filter[type]" style="width: 100px;">
				<option value="-1">[All]</option>
				{html_options options=$COMP_TYPE_NAMES selected=$filter.type}
			</select>
			<br>
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
			<input type="checkbox" name="filter[show_os]" value=1 {if $filter.show_os}checked{/if} class="checkbox"> OS<br>
		</td>
		<td nowrap>
			<input type="checkbox" name="filter[show_contact]" value=1 {if $filter.show_contact}checked{/if} class="checkbox"> Contact<br>
			<input type="checkbox" name="filter[show_serial]" value=1 {if $filter.show_serial}checked{/if} class="checkbox"> Serial
		</td>
		<td>{assign var="tot" value=$tot_comps_old+$tot_comps_current}
		{$tot}</td>
		<td>
			<input type="hidden" name="do_filter_hidden" value="0">
			<input type="submit" name="do_filter" value="Apply filter">
		</td>
	
</table>
<input type="hidden" name="order_by_bk" value="{$filter.order_by}">
<input type="hidden" name="order_dir_bk" value="{$filter.order_dir}">
<input type="hidden" name="go" value="">
</form>
<table class="tab_header">
	<td id="tab_head_current" class="tab_inactive"><a href="#" onclick="return showTab('current');" style="width: 250px;">Available computers ({$tot_comps_current})</a></td>
	<td id="tab_head_old" class="tab_inactive"><a href="#" onclick="return showTab('old');" style="width: 250px;">Computers not reporting ({$tot_comps_old})</a></td>
	<td id="tab_head_blackout" class="tab_inactive"><a href="#" onclick="return showTab('blackout');" style="width: 250px;">Blackout computers ({$tot_comps_blackout})</a></td>
</table>
<div id="tab_current" class="tab_content" style="display: none;">
	<h2>Available computers (less than 2 month since last report) ( Total: {$tot_comps_current})</h2>
	<p />
	<table class="list" width="98%">
		<thead>
			<tr>
				<td class="sort_text" style="width: 1%; white-space: no-wrap;">{strip}
					<a href="{$sort_url}&order_by=id&order_dir={if $filter.order_by=='id' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
					>ID</a>{if $filter.order_by=='id'}&nbsp;<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				{/strip}</td>
				<td nowrap="nowrap" class="sort_text" style="width:20%">
					<a href="{$sort_url}&order_by=netbios_name&order_dir={if $filter.order_by=='netbios_name' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
					>Name&nbsp;{if $filter.order_by=='netbios_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6"
					>{/if}</a>
				</td>
				{if !$filter.profile_id}
					<td nowrap class="sort_text">
						<a href="{$sort_url}&customer_id={$filter.customer_id}&order_by=profile&order_dir={if $filter.order_by=='profile' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Monitor profile</a>
						{if $filter.order_by=='profile'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}
				
				{if $filter.show_user}
					<td nowrap class="sort_text">
						<a href="{$sort_url}&order_by=current_user&order_dir={if $filter.order_by=='current_user' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
						>User</a>
						{if $filter.order_by=='current_user'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}
				
				{if $filter.show_brand}
					<td nowrap class="sort_text">
						<a href="{$sort_url}&order_by=computer_brand&order_dir={if $filter.order_by=='computer_brand' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
						>Brand</a>
						{if $filter.order_by=='computer_brand'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
					
					<td nowrap class="sort_text">
						<a href="{$sort_url}&order_by=computer_model&order_dir={if $filter.order_by=='computer_model' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
						>Model</a>
						{if $filter.order_by=='computer_model'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}
				
				{if $filter.show_os or $filter.show_serial}
					<td nowrap class="sort_text">
						{if $filter.show_os and $filter.show_serial}
							<a href="{$sort_url}&order_by=os_name&order_dir={if $filter.order_by=='os_name' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
							>OS / Serial number</a>
							{if $filter.order_by=='os_name'}
							<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
							{/if}
						{elseif $filter.show_os}
							<a href="{$sort_url}&order_by=os_name&order_dir={if $filter.order_by=='os_name' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
							>OS</a>
							{if $filter.order_by=='os_name'}
							<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
							{/if}
						{else}
							<a href="{$sort_url}&order_by=computer_sn&order_dir={if $filter.order_by=='computer_sn' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
							>Serial number</a>
							{if $filter.order_by=='computer_sn'}
							<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
							{/if}
						{/if}
					</td>
				{/if}
				
				{if $filter.show_contact}
					<td nowrap class="sort_text">
						<a href="{$sort_url}&order_by=last_contact&order_dir={if $filter.order_by=='last_contact' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
						>Last contact</a>
						{if $filter.order_by=='last_contact'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}
			</tr>
		</thead>
		{assign var="last_type" value=""}

		{foreach from=$computers_current item=computer}
			{assign var="computer_type" value=$computer->type}
			<tr>
				<td>
                    {assign var="p" value="id:"|cat:$computer->id}
                    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->id}</a></td>
				<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->netbios_name|escape}</a></td>
				{if !$filter.profile_id}
				{assign var="profile_id" value=$computer->profile_id}
				<td {if $computer->notifications}rowspan="2"
                {/if}>
                    {assign var="p" value='id:'|cat:$computer->profile_id}
                    <a href="{'kawacs'|get_link:'monitor_profile_edit':$p:'template'}">{$profiles.$profile_id}</a></td>
				{/if}
				{if $filter.show_user}
				<td>
					{assign var="login" value=$computer->current_user}
					{if $login}
						{if $ad_users.$login->computer_id}
                            {assign var="p" value="computer_id:"|cat:$ad_users.$login->computer_id|cat:',nrc:'|cat:$ad_users.$login->nrc}
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
				<td>{$computer->get_item('computer_brand')}</td>
				<td>{$computer->get_item('computer_model')}</td>
				{/if}
				
				{if $filter.show_os or $filter.show_serial}
					<td>
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
					<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
				{/if} 
				
			</tr>
			
		{/foreach}
	</table>
</div>
<div id="tab_old" class="tab_content" style="display: none;">
	<h2>Old computers (more than 2 month since last report) ( Total: {$tot_comps_old})</h2>
	<p />
	<table class="list" width="98%">
		<thead>
			<tr>
				<td class="sort_text" style="width: 1%; white-space: no-wrap;">{strip}
                    {if $filter.order_by=='id' and $filter.order_dir=='ASC'}
                    {assign var="p" value="order_by:"|cat:"id"|cat:",order_dir:"|cat:"DESC"}
                    {else}{assign var="p" value="order_by:"|cat:"id"|cat:",order_dir:"|cat:"ASC"}{/if}
					<a href="{$sort_url|add_extra_get_params:$p:'template'}"
					>ID</a>{if $filter.order_by=='id'}&nbsp;<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				{/strip}</td>
				<td nowrap="nowrap" class="sort_text" style="width:20%">
                    {if $filter.order_by=='netbios_name' and $filter.order_dir=='ASC'}
                        {assign var="p" value="order_by:"|cat:"netbios_name"|cat:",order_dir:"|cat:"DESC"}
                    {else}
                        {assign var="p" value="order_by:"|cat:"netbios_name"|cat:",order_dir:"|cat:"ASC"}
                    {/if}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"
					>Name&nbsp;{if $filter.order_by=='netbios_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6"
					>{/if}</a>
				</td>
				{if !$filter.profile_id}
					<td nowrap class="sort_text">
                        {if $filter.order_by=='profile' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"profile"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"profile"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
                        <a href="{$sort_url|add_extra_get_params:$p:'template'}">Monitor profile</a>
						{if $filter.order_by=='profile'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}
				
				{if $filter.show_user}
					<td nowrap class="sort_text">
                        {if $filter.order_by=='current_user' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"current_user"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"current_user"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
                        <a href="{$sort_url|add_extra_get_params:$p:'template'}"
						>User</a>
						{if $filter.order_by=='current_user'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}
				
				{if $filter.show_brand}
					<td nowrap class="sort_text">
                        {if $filter.order_by=='computer_brand' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"computer_brand"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"computer_brand"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
                        <a href="{$sort_url|add_extra_get_params:$p:'template'}"
						>Brand</a>
						{if $filter.order_by=='computer_brand'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
					
					<td nowrap class="sort_text">
                        {if $filter.order_by=='computer_model' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"computer_model"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"computer_model"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
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
                            {if $filter.order_by=='os_name' and $filter.order_dir=='ASC'}
                                {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:"DESC"}
                            {else}
                                {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:"ASC"}
                            {/if}
                            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
							>OS / Serial number</a>
							{if $filter.order_by=='os_name'}
							<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
							{/if}
						{elseif $filter.show_os}
                            {if $filter.order_by=='os_name' and $filter.order_dir=='ASC'}
                                {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:"DESC"}
                            {else}
                                {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:"ASC"}
                            {/if}
                            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
							>OS</a>
							{if $filter.order_by=='os_name'}
							<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
							{/if}
						{else}
                            {if $filter.order_by=='computer_sn' and $filter.order_dir=='ASC'}
                                {assign var="p" value="order_by:"|cat:"computer_sn"|cat:",order_dir:"|cat:"DESC"}
                            {else}
                                {assign var="p" value="order_by:"|cat:"computer_sn"|cat:",order_dir:"|cat:"ASC"}
                            {/if}
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
                        {if $filter.order_by=='last_contact' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"last_contact"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"last_contact"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
                        <a href="{$sort_url|add_extra_get_params:$p:'template'}"
						>Last contact</a>
						{if $filter.order_by=='last_contact'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}&#0187;
				<td nowrap>&nbsp;</td>
			</tr>
		</thead>
		{assign var="last_type" value=""}

		{foreach from=$computers_old item=computer}
			{assign var="computer_type" value=$computer->type}
			<tr>
                {assign var="p" value="id:"|cat:$computer->id}
				<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->id}</a></td>
				<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->netbios_name|escape}</a></td>
				{if !$filter.profile_id}
				{assign var="profile_id" value=$computer->profile_id}
				<td {if $computer->notifications}rowspan="2"{/if}
				  >
                    {assign var="p" value="id:"|cat:$computer->profile_id}
                    <a href="{'kawacs'|get_link:'monitor_profile_edit':$p:'template'}">{$profiles.$profile_id}</a></td>
				{/if}
				{if $filter.show_user}
				<td>
					{assign var="login" value=$computer->current_user}
					{if $login}
						{if $ad_users.$login->computer_id}
                            {assign var='p' value="computer_id:"|cat:$ad_users.$login->computer_id|cat:",nrc:"|cat:$ad_users.$login->nrc }
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
				<td>{$computer->get_item('computer_brand')}</td>
				<td>{$computer->get_item('computer_model')}</td>
				{/if}
				
				{if $filter.show_os or $filter.show_serial}
					<td>
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
					<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
				{/if} 
				<td>
                    {assign var="p" value="id:"|cat:$computer->id}
					<a href="{'kawacs'|get_link:'blackout_computer':$p:'template'}">Blackout computer&#0187;</a>
				</td>
			</tr>
			
		{/foreach}
	</table>
</div>
<div id="tab_blackout" class="tab_content" style="display: none;">
	<h2>Blackout computers ( Total: {$tot_comps_blackout})</h2>
	<p />
	<table class="list" width="98%">
		<thead>
			<tr>
				<td class="sort_text" style="width: 1%; white-space: no-wrap;">{strip}
                    {if $filter.order_by=='id' and $filter.order_dir=='ASC'}
                        {assign var="p" value="order_by:"|cat:"id"|cat:",order_dir:"|cat:"DESC"}
                    {else}
                        {assign var="p" value="order_by:"|cat:"id"|cat:",order_dir:"|cat:"ASC"}
                    {/if}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"
					>ID</a>{if $filter.order_by=='id'}&nbsp;<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				{/strip}</td>
				<td nowrap="nowrap" class="sort_text" style="width:20%">
					<a href="{$sort_url}&order_by=netbios_name&order_dir={if $filter.order_by=='netbios_name' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}"
					>Name&nbsp;{if $filter.order_by=='netbios_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6"
					>{/if}</a>
				</td>
				{if !$filter.profile_id}
					<td nowrap class="sort_text">
                        {if $filter.order_by=='profile' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"profile"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"profile"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
                        <a href="{$sort_url|add_extra_get_params:$p:'template'}">Monitor profile</a>
						{if $filter.order_by=='profile'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}
				
				{if $filter.show_user}
					<td nowrap class="sort_text">
                        {if $filter.order_by=='current_user' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"current_user"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"current_user"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
                        <a href="{$sort_url|add_extra_get_params:$p:'template'}"
						>User</a>
						{if $filter.order_by=='current_user'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}
				
				{if $filter.show_brand}
					<td nowrap class="sort_text">
                        {if $filter.order_by=='computer_brand' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"computer_brand"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"computer_brand"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
                        <a href="{$sort_url|add_extra_get_params:$p:'template'}"
						>Brand</a>
						{if $filter.order_by=='computer_brand'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
					
					<td nowrap class="sort_text">
                        {if $filter.order_by=='computer_model' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"computer_model"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"computer_model"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
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
                            {if $filter.order_by=='os_name' and $filter.order_dir=='ASC'}
                                {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:"DESC"}
                            {else}
                                {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:"ASC"}
                            {/if}
                            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
							>OS / Serial number</a>
							{if $filter.order_by=='os_name'}
							<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
							{/if}
						{elseif $filter.show_os}
                            {if $filter.order_by=='os_name' and $filter.order_dir=='ASC'}
                                {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:"DESC"}
                            {else}
                                {assign var="p" value="order_by:"|cat:"os_name"|cat:",order_dir:"|cat:"ASC"}
                            {/if}
                            <a href="{$sort_url|add_extra_get_params:$p:'template'}"
							>OS</a>
							{if $filter.order_by=='os_name'}
							<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
							{/if}
						{else}
                            {if $filter.order_by=='computer_sn' and $filter.order_dir=='ASC'}
                                {assign var="p" value="order_by:"|cat:"computer_sn"|cat:",order_dir:"|cat:"DESC"}
                            {else}
                                {assign var="p" value="order_by:"|cat:"computer_sn"|cat:",order_dir:"|cat:"ASC"}
                            {/if}
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
                        {if $filter.order_by=='last_contact' and $filter.order_dir=='ASC'}
                            {assign var="p" value="order_by:"|cat:"last_contact"|cat:",order_dir:"|cat:"DESC"}
                        {else}
                            {assign var="p" value="order_by:"|cat:"last_contact"|cat:",order_dir:"|cat:"ASC"}
                        {/if}
                        <a href="{$sort_url|add_extra_get_params:$p:'template'}"
						>Last contact</a>
						{if $filter.order_by=='last_contact'}
						<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
						{/if}
					</td>
				{/if}&#0187;
				<td nowrap>&nbsp;</td>
			</tr>
		</thead>
		{assign var="last_type" value=""}
		
		{foreach from=$computers_blackout item=blackout}
			{assign var="computer" value=$blackout->computer}
			{assign var="computer_type" value=$computer->type}
			<tr>
                {assign var="p" value="id:"|cat:$computer->id}
				<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->id}</a></td>
				<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer->netbios_name|escape}</a></td>
				{if !$filter.profile_id}
				{assign var="profile_id" value=$computer->profile_id}
				<td {if $computer->notifications}rowspan="2"{/if}>
                    {assign var="p" value="id:"|cat:$computer->profile_id}
                    <a href="{'kawacs'|get_link:'monitor_profile_edit':$p:'template'}">{$profiles.$profile_id}</a></td>
				{/if}
				{if $filter.show_user}
				<td>
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
				<td>{$computer->get_item('computer_brand')}</td>
				<td>{$computer->get_item('computer_model')}</td>
				{/if}
				
				{if $filter.show_os or $filter.show_serial}
					<td>
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
					<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
				{/if} 
				<td>
                    {assign var="p" value="id:"|cat:$computer->id}
					<a href="{'kawacs'|get_link:'blackout_computer_remove':$p:'template'}">Remove computer blackout&#0187;</a>
				</td>
			</tr>
			
		{/foreach}
	</table>
</div>





<script language="JavaScript" type="text/javascript">
//<![CDATA
// Check what was the last selected tab, if any
if (!(last_tab = getCookie('customers_computer_count_tab'))) last_tab = tabs[0];
showTab (last_tab);
//]]>
</script>
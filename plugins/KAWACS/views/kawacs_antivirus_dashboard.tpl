{assign var="paging_titles" value="KAWACS, KAWACS Antivirus Statuses Dashboard"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>Computers antivirus statuses dashboard</h1>
<p>
<font class="error">{$error_msg}</font>
</p>
<script language="javascript" type="text/javascript">
//<![CDATA[
{literal}
	var groups_stat = new Array(true, true, true, true);

	function expand_group(id)
	{
		var stat = groups_stat[id];
		groups_stat[id] = (!groups_stat[id]);
		var group = document.getElementById('group_'+id);
		
		img = document.getElementById('img_'+id);
		if (stat)
		{
			img.src = '/images/expand.gif';
		}
		else
		{
			img.src = '/images/collapse.gif';
		}
		
		if(stat)
		{
			group.style.display = 'none';
		}
		else
		{
			group.style.display = 'block';
		}
	}
{/literal}
//]]>
</script>
<form name='filter' method="POST">
{$form_redir}
<table width="98%" class="list">
	<tr class="head">
		<td style="width: 300px;">Customer</td>
		<td style="width: 200px;">Profile</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
		<select name="filter[customer_id]">
			<option value='0'>[All]</option>
			{html_options options=$customers_all selected=$filter.customer_id}
		</select>
		</td>
		<td>
			<select name="filter[profile_id]">
				<option value="0">[All]</option>
				{html_options options=$profiles selected=$filter.profile_id}
			</select>
		</td>
		<td align="right" style="vertical-align: middle">
			<input type="hidden" name="do_filter_hidden" value="0">
			<input type="submit" name="do_filter" value="Apply filter">
		</td>
	</tr>
	
</table>

<div>
{if !$filter.customer_id}{assign var=is_customer value=0}{else}{assign var=is_customer value=1}{/if}
{if $count_r>0}
<div onclick="expand_group(0)" style="cursor: hand;">
	<table class="list" width="98%">
	<tr class='head'>
		<td style="width: 10px; background-color: red;"><img src="/images/collapse.gif" width="10" height="11" id="img_0"></td>
		<td>Antivirus updates older than 1 week	   [{$count_r} computers]</td>
	</tr>
	</table>
</div>
<div id='group_0'>
<table class="list" width="98%">
<tr class='head'>
{if $is_customer}
	<td class="sort_text" style="width: 10%;">
	<a href="{$sort_url}&order_by=computer_id&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">ID
	{if $filter.order_by=='computer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 40%;">
	<a href="{$sort_url}&order_by=computer_name&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Name
	{if $filter.order_by=='computer_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Last udate</td>
{else}
	<td class="sort_text" style="width: 10%;">
	<a href="{$sort_url}&order_by=computer_id&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">ID
	{if $filter.order_by=='computer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 30%;">
	<a href="{$sort_url}&order_by=computer_name&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Name
	{if $filter.order_by=='computer_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 20%;">
	<a href="{$sort_url}&order_by=customer&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Customer
	{if $filter.order_by=='customer'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 20%;">Last update</td>
{/if}
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$computers_red item=computer}
<tr>
		{assign var=computerr value=$computer.computer}
		{assign var=stats value=$computer.av_infos}
		{assign var=td value='<td style="color: red;">'}
		{assign var=cid value=$computerr->customer_id}
		{$td}{$computerr->id}</td>
        {assign var="p" value="id:"|cat:$computerr->id}
		{$td}<a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computerr->netbios_name}</a></td>
		{if !$is_customer}{$td}{$customers_all.$cid}</td>{/if}
		<td>{assign var=pid value=$computerr->profile_id}{$profiles[$pid]}</td>
		{$td}
		{foreach from=$stats item=valobj}
			{$valobj->value[23]} -> Last update: {$valobj->value[24]|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br />
		{/foreach}
		</td>
	</tr>
{/foreach}
</table>
</div>
{/if}

{if $count_o>0}
<div onclick="expand_group(1)" style="cursor: hand;">
	<table class="list" width="98%">
	<tr class='head'>
		<td style="width: 10px; background-color: orange;"><img src="/images/collapse.gif" width="10" height="11" id="img_1"></td>
		<td>Antivirus updates older than 1 day	   [{$count_o} computers]</td>
	</tr>
	</table>
</div>
<div id='group_1'>
<table class="list" width="98%">
<tr class='head'>
{if $is_customer}
	<td class="sort_text" style="width: 10%;">
	<a href="{$sort_url}&order_by=computer_id&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">ID
	{if $filter.order_by=='computer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 40%;">
	<a href="{$sort_url}&order_by=computer_name&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Name
	{if $filter.order_by=='computer_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Last udate</td>
{else}
	<td class="sort_text" style="width: 10%;">
	<a href="{$sort_url}&order_by=computer_id&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">ID
	{if $filter.order_by=='computer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 30%;">
	<a href="{$sort_url}&order_by=computer_name&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Name
	{if $filter.order_by=='computer_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 20%;">
	<a href="{$sort_url}&order_by=customer&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Customer
	{if $filter.order_by=='customer'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 20%;">Last update</td>
{/if}
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$computers_orange item=computer}
<tr>
		{assign var=computerr value=$computer.computer}
		{assign var=stats value=$computer.av_infos}
		{assign var=td value='<td style="color: orange;">'}
		{assign var=cid value=$computerr->customer_id}
		{$td}{$computerr->id}</td>
		{$td}
            {assign var="p" value="id:"|cat:$computerr->id}
            <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computerr->netbios_name}</a></td>
		{if !$is_customer}{$td}{$customers_all.$cid}</td>{/if}
		<td>{assign var=pid value=$computerr->profile_id}{$profiles[$pid]}</td>
		{$td}
		{foreach from=$stats item=valobj}
			{$valobj->value[23]} -> Last update: {$valobj->value[24]|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br />
		{/foreach}
		</td>
	</tr>
{/foreach}
</table>
</div>
{/if}

{if $count_g>0}
<div onclick="expand_group(2)" style="cursor: hand;">
	<table class="list" width="98%">
	<tr class='head'>
		<td style="width: 10px; background-color: green;"><img src="/images/collapse.gif" width="10" height="11" id="img_2"></td>
		<td>Antivirus updates are up to date	   [{$count_g} computers]</td>
	</tr>
	</table>
</div>
<div id='group_2'>
<table class="list" width="98%">
<tr class='head'>
{if $is_customer}
	<td class="sort_text" style="width: 10%;">
	<a href="{$sort_url}&order_by=computer_id&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">ID
	{if $filter.order_by=='computer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 40%;">
	<a href="{$sort_url}&order_by=computer_name&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Name
	{if $filter.order_by=='computer_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Last udate</td>
{else}
	<td class="sort_text" style="width: 10%;">
	<a href="{$sort_url}&order_by=computer_id&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">ID
	{if $filter.order_by=='computer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 30%;">
	<a href="{$sort_url}&order_by=computer_name&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Name
	{if $filter.order_by=='computer_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 20%;">
	<a href="{$sort_url}&order_by=customer&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Customer
	{if $filter.order_by=='customer'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 20%;">Last update</td>
{/if}
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$computers_green item=computer}
<tr>
		{assign var=computerr value=$computer.computer}
		{assign var=stats value=$computer.av_infos}
		{assign var=td value='<td style="color: green;">'}
		{assign var=cid value=$computerr->customer_id}
		{$td}{$computerr->id}</td>
		{$td}
            {assign var="p" value="id:"|cat:$computerr->id}
            <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computerr->netbios_name}</a></td>
		{if !$is_customer}{$td}{$customers_all.$cid}</td>{/if}
		<td>{assign var=pid value=$computerr->profile_id}{$profiles[$pid]}</td>
		{$td}
		{foreach from=$stats item=valobj}
			{$valobj->value[23]} -> Last update: {$valobj->value[24]|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br />
		{/foreach}
		</td>
	</tr>
{/foreach}
</table>
</div>
{/if}

{if $count_gr>0}
<div onclick="expand_group(3)" style="cursor: hand;">
	<table class="list" width="98%">
	<tr class='head'>
		<td style="width: 10px; background-color: gray;"><img src="/images/collapse.gif" width="10" height="11" id="img_3"></td>
		<td>Computer not reporting antivirus status	   [{$count_gr} computers]</td>
	</tr>
	</table>
</div>
<div id='group_3'>
<table class="list" width="98%">
<tr class='head'>
{if $is_customer}
	<td class="sort_text" style="width: 10%;">
	<a href="{$sort_url}&order_by=computer_id&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">ID
	{if $filter.order_by=='computer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 40%;">
	<a href="{$sort_url}&order_by=computer_name&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Name
	{if $filter.order_by=='computer_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Last udate</td>
{else}
	<td class="sort_text" style="width: 10%;">
	<a href="{$sort_url}&order_by=computer_id&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">ID
	{if $filter.order_by=='computer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 30%;">
	<a href="{$sort_url}&order_by=computer_name&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Name
	{if $filter.order_by=='computer_name'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td class="sort_text" style="width: 20%;">
	<a href="{$sort_url}&order_by=customer&order_dir={if $filter.order_dir=='ASC'}DESC{else}ASC{/if}">Customer
	{if $filter.order_by=='customer'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
	</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 20%;">Last update</td>
{/if}
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$computers_gray item=computer}
<tr>
		{assign var=computerr value=$computer.computer}
		{assign var=stats value=$computer.av_infos}
		{assign var=td value='<td style="color: gray;">'}
		{assign var=cid value=$computerr->customer_id}
		{$td}{$computerr->id}</td>
		{$td}
            {assign var="p" value="id:"|cat:$computerr->id}
            <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computerr->netbios_name}</a></td>
		{if !$is_customer}{$td}{$customers_all.$cid}</td>{/if}
		<td>{assign var=pid value=$computerr->profile_id}{$profiles[$pid]}</td>
		{$td}
		{foreach from=$stats item=valobj}
			{$valobj->value[23]} -> Last update: {$valobj->value[24]|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br />
		{/foreach}
		</td>
	</tr>
{/foreach}
</table>
</div>
{/if}
</div>


</form>
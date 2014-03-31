{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Disk Space Report"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>Disk Space Report</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<a href="{$computer_view_link}">&#0171; Back to computer</a>

<p>
<form action="" method="POST" name="filter_form">
{$form_redir}

Interval:
<select name="filter[month_start]" onChange="document.forms['filter_form'].submit()">
	{html_options values=$months output=$months selected=$filter.month_start}
</select> - 
<select name="filter[month_end]" onChange="document.forms['filter_form'].submit()">
	{html_options values=$months output=$months selected=$filter.month_end}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
By:
<select name="filter[interval]" onChange="document.forms['filter_form'].submit()">
	{html_options options=$intervals selected=$filter.interval}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
Unit:
<select name="filter[unit]" onChange="document.forms['filter_form'].submit()">
	{html_options options=$units selected=$filter.unit}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
Dates:
<select name="filter[sort_dir]" onChange="document.forms['filter_form'].submit()">
	<option value="DESC">Descending</option>
	<option value="ASC" {if $filter.sort_dir=="ASC"}selected{/if}>Ascending</option>
</select>

<p>
{foreach from=$partitions item=partition}
    {assign var="p" value="computer_id:"|cat:$computer->id|cat:",partition:"|cat:$partition|cat:",start:"|cat:$filter.month_start|cat:",end:"|cat:$filter.month_end}
	<img src="{'kawacs'|get_link:'plot_free_disk':$p:'template'}" width="600" height="400">
	<p>
{/foreach}

<p>
<table class="list" width="98%" id="report" name="report">
	<thead>
	<tr>
		<td width="10%">Date</td>
		{foreach from=$partitions item=partition}
			<td width="20%" align="right">{$partition}<br>{$history.$partition->size}</td>
		{/foreach}
	</tr>
	</thead>

	{foreach from=$dates item=date}
	<tr>
		<td nowrap>
			{if $filter.interval=='hour'}
				{$date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}
				{$date|date_format:$smarty.const.DATE_FORMAT_SMARTY}
			{/if}
		</td>
		{foreach from=$partitions item=partition}
		<td align="right">
			{$history.$partition->log.$date|string_format:"%.2f"}
		</td>
		{/foreach}
	</tr>
	{foreachelse}
	<tr>
		<td colspan="2">[There are currently no logged values in the database]</td>
	</tr>
	{/foreach}
</table>

</form>